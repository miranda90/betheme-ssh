<?php
/**
 * font-role-set.php — asigna una familia a uno o varios roles (Theme Options → Fonts → Family) y/o cambia
 * google-font-mode, replicando el "Save" del panel (revisión + static.css + reset JS BeBuilder).
 * SAFETY: reversible (el estado anterior queda en betheme_revision_backup; ver be-options-revisions)
 *
 * Valor guardado en un font_select = nombre literal de la fuente, SIN '#' también para las propias
 * (fields/font_select/field_font_select.php:61-62; style.php:112 hace str_replace('#','')).
 *
 * Uso: wp eval-file font-role-set.php role=headings font="Inter"
 *      wp eval-file font-role-set.php role=content,lead,menu font="Avenir"         (custom: sin #; se acepta con # y se quita)
 *      wp eval-file font-role-set.php role=font-decorative font="Georgia"          (id completo también vale)
 *      wp eval-file font-role-set.php role=button font="Poppins"                   (button-font-family)
 *      wp eval-file font-role-set.php mode=local | mode=disabled | mode=            (google-font-mode)
 *      ... force=1     permite un nombre que no está en mfn_fonts() (system/custom/all)
 *      ... dry-run=1   muestra el diff sin escribir
 *
 * Roles (functions/theme-head.php:308-315 y style.php:98-107):
 *   content=font-content  lead=font-lead  menu=font-menu  title=font-title  headings=font-headings
 *   headings-small=font-headings-small  blockquote=font-blockquote  decorative=font-decorative  button=button-font-family
 */
require_once __DIR__ . '/_lib.php';
$a   = be_args( $args );
$dry = be_flag( $a, 'dry-run' );

$role_map = [
	'content'        => 'font-content',
	'lead'           => 'font-lead',
	'menu'           => 'font-menu',
	'title'          => 'font-title',
	'headings'       => 'font-headings',
	'headings-small' => 'font-headings-small',
	'blockquote'     => 'font-blockquote',
	'decorative'     => 'font-decorative',
	'button'         => 'button-font-family',
];

$old = be_opts();
$new = $old;
$written = [];

// google-font-mode ('' | local | disabled), theme-options.php:11581
if ( array_key_exists( 'mode', $a ) ) {
	$mode = trim( (string) $a['mode'] );
	if ( ! in_array( $mode, [ '', 'local', 'disabled' ], true ) ) {
		be_err( "mode= admite '' (Google), local, disabled" );
	}
	$new['google-font-mode']     = $mode;
	$written['google-font-mode'] = $mode;
	if ( $mode === 'local' ) {
		be_msg( 'AVISO: mode=local sirve uploads/betheme/fonts/mfn-local-fonts.css (theme-head.php:501-502). Ejecuta be-fonts-local-regenerate.sh --yes después de cualquier cambio de familia/peso/subset.' );
	}
}

if ( ! empty( $a['role'] ) ) {
	if ( ! array_key_exists( 'font', $a ) ) {
		be_err( 'Falta font= (nombre de la familia; "" para volver al default)' );
	}
	$font = ltrim( trim( (string) $a['font'] ), '#' );

	// validación contra la lista del selector (muffin-options/fonts.php:14-1980)
	$lists  = mfn_fonts();
	$custom = array_map( fn( $f ) => ltrim( $f, '#' ), $lists['custom'] );
	$kind   = null;
	if ( $font === '' ) {
		$kind = 'default';
	} elseif ( in_array( $font, $lists['system'], true ) ) {
		$kind = 'system';
	} elseif ( in_array( $font, $custom, true ) ) {
		$kind = 'custom';
	} elseif ( in_array( $font, $lists['all'], true ) ) {
		$kind = 'google';
	}
	if ( $kind === null ) {
		$msg = "'$font' no está en mfn_fonts() (system, custom ni Google)";
		if ( ( $new['google-font-mode'] ?? '' ) === 'disabled' ) {
			$msg .= " — con google-font-mode=disabled la lista de Google está vacía (fonts.php:1971-1973)";
		}
		if ( ! be_flag( $a, 'force' ) ) {
			be_err( $msg . '. Usa force=1 si es intencionado.' );
		}
		be_msg( 'aviso (force): ' . $msg );
		$kind = 'unknown';
	}

	foreach ( array_filter( array_map( 'trim', explode( ',', (string) $a['role'] ) ) ) as $r ) {
		$id = $role_map[ $r ] ?? ( in_array( $r, $role_map, true ) ? $r : null );
		if ( ! $id ) {
			be_err( "rol '$r' desconocido. Roles: " . implode( ', ', array_keys( $role_map ) ) . ' (o su id font-*)' );
		}
		$new[ $id ]     = $font;
		$written[ $id ] = $font;
	}

	if ( $kind === 'google' && ( $new['google-font-mode'] ?? '' ) === 'local' ) {
		be_msg( "AVISO: '$font' es Google Font y google-font-mode=local: hay que regenerar la caché local (be-fonts-local-regenerate.sh --yes) o no se cargará." );
	}
	if ( $kind === 'google' && ( $new['google-font-mode'] ?? '' ) === 'disabled' ) {
		be_msg( "AVISO: google-font-mode=disabled: '$font' no se encolará desde Google (theme-head.php:442-446)." );
	}
	if ( $kind === 'custom' ) {
		be_msg( "'$font' es fuente propia: el @font-face lo imprime inline mfn_styles_custom_font() (theme-head.php:730,947); siempre font-weight:normal." );
	}
	$written['_font_kind'] = $kind;
}

if ( ! $written ) {
	be_err( 'Nada que cambiar. Usa role= font= y/o mode=' );
}

$diff = be_opts_save( $new, [ 'dry' => $dry ] );
be_out( [ 'dry_run' => $dry, 'written' => $written, 'diff' => $diff ] );
