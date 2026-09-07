<?php
/**
 * logo-set.php — escribe los logos del header clásico (logo-* de Theme Options → Global → Logo)
 * replicando el "Save" del panel (revisión + static.css + reset JS BeBuilder).
 * SAFETY: reversible (el estado anterior queda en betheme_revision_backup; ver be-options-revisions)
 *
 * Cada imagen se pasa como ruta local (se importa a la Media Library) o como URL ya subida (http...).
 * El valor guardado es el mismo que escribe el panel: `URL#attachment_id` (fields/upload/field_upload.js:31).
 *
 * Uso: wp eval-file logo-set.php main=/tmp/logo.svg retina=/tmp/logo@2x.png
 *      wp eval-file logo-set.php main=https://sitio.com/wp-content/uploads/2026/01/logo.svg
 *      wp eval-file logo-set.php sticky=... sticky-retina=... mobile=... mobile-retina=... mobile-sticky=... mobile-sticky-retina=...
 *      wp eval-file logo-set.php height=70 padding=10 text="Mi marca" link=link,h1-home
 *      wp eval-file logo-set.php width=180 width-tablet=140 width-mobile=110 valign=top
 *      wp eval-file logo-set.php clear=1                (vacía los 8 logo-*-img)
 *      ... dry-run=1      muestra el diff sin escribir (las rutas locales NO se importan)
 *      ... no-static=1    no regenerar static.css
 *
 * Mapa de argumentos → ids (includes/include-logo.php:107-115):
 *   main=logo-img  retina=retina-logo-img  sticky=sticky-logo-img  sticky-retina=sticky-retina-logo-img
 *   mobile=responsive-logo-img  mobile-retina=responsive-retina-logo-img
 *   mobile-sticky=responsive-sticky-logo-img  mobile-sticky-retina=responsive-sticky-retina-logo-img
 *   height=logo-height  padding=logo-vertical-padding  text=logo-text  link=logo-link (csv → array checkbox)
 *   width=logo-width  width-tablet=logo-width-tablet  width-mobile=logo-width-mobile  valign=logo-vertical-align
 */
require_once __DIR__ . '/_lib.php';
$a   = be_args( $args );
$dry = be_flag( $a, 'dry-run' );

$image_map = [
	'main'                 => 'logo-img',
	'retina'               => 'retina-logo-img',
	'sticky'               => 'sticky-logo-img',
	'sticky-retina'        => 'sticky-retina-logo-img',
	'mobile'               => 'responsive-logo-img',
	'mobile-retina'        => 'responsive-retina-logo-img',
	'mobile-sticky'        => 'responsive-sticky-logo-img',
	'mobile-sticky-retina' => 'responsive-sticky-retina-logo-img',
];
$scalar_map = [
	'height'       => 'logo-height',
	'padding'      => 'logo-vertical-padding',
	'text'         => 'logo-text',
	'width'        => 'logo-width',
	'width-tablet' => 'logo-width-tablet',
	'width-mobile' => 'logo-width-mobile',
	'valign'       => 'logo-vertical-align',
];

/**
 * Convierte el argumento de una imagen en el valor que guarda un campo `upload`.
 *  - ruta local  → import a Media Library → 'URL#id'
 *  - URL http(s) con '#id' → tal cual
 *  - URL http(s) sin '#'   → se busca el adjunto (attachment_url_to_postid) y se añade '#id' si existe
 *  - ''          → vacía el campo
 * (candidato a helper en _lib.php: be_upload_arg)
 */
function be_logo_upload_arg( string $raw, bool $dry ): string {
	$raw = trim( $raw );
	if ( $raw === '' ) {
		return '';
	}
	if ( preg_match( '#^https?://#i', $raw ) ) {
		if ( strpos( $raw, '#' ) !== false ) {
			return $raw;
		}
		$id = attachment_url_to_postid( $raw );
		return $id ? $raw . '#' . $id : $raw;
	}
	if ( $dry ) {
		return '(import) ' . $raw;
	}
	$m = be_media_import( $raw );
	be_msg( "importado $raw → adjunto {$m['id']}" );
	return $m['value'];
}

$old = be_opts();
$new = $old;
$written = [];

if ( be_flag( $a, 'clear' ) ) {
	foreach ( $image_map as $id ) {
		$new[ $id ] = '';
		$written[ $id ] = '';
	}
}

foreach ( $image_map as $arg => $id ) {
	if ( array_key_exists( $arg, $a ) ) {
		$new[ $id ]     = be_logo_upload_arg( (string) $a[ $arg ], $dry );
		$written[ $id ] = $new[ $id ];
	}
}

foreach ( $scalar_map as $arg => $id ) {
	if ( array_key_exists( $arg, $a ) ) {
		$v = trim( (string) $a[ $arg ] );
		if ( in_array( $id, [ 'logo-height', 'logo-vertical-padding', 'logo-width', 'logo-width-tablet', 'logo-width-mobile' ], true ) && $v !== '' && ! is_numeric( $v ) ) {
			be_err( "$id espera un número en px (sin unidad), recibido '$v'" );
		}
		if ( $id === 'logo-vertical-align' && ! in_array( $v, [ '', 'top', 'bottom' ], true ) ) {
			be_err( "valign admite top | bottom | '' (middle)" );
		}
		$new[ $id ]     = $v;
		$written[ $id ] = $v;
	}
}

// logo-link: checkbox → solo las claves marcadas, ['k' => 'k'] (theme-options.php:1133-1146).
// El panel añade además 'post-meta' => '1' (fields/checkbox/field_checkbox.php:50); se conserva si ya estaba.
if ( array_key_exists( 'link', $a ) ) {
	$allowed = [ 'link', 'h1-home', 'h1-all' ];
	$arr     = [];
	if ( ! empty( $old['logo-link']['post-meta'] ) ) {
		$arr['post-meta'] = '1';
	}
	foreach ( array_filter( array_map( 'trim', explode( ',', (string) $a['link'] ) ) ) as $k ) {
		if ( ! in_array( $k, $allowed, true ) ) {
			be_err( "link= admite " . implode( ',', $allowed ) . " (recibido '$k')" );
		}
		$arr[ $k ] = $k;
	}
	$new['logo-link']     = $arr;
	$written['logo-link'] = $arr;
}

if ( ! $written ) {
	be_err( 'Nada que cambiar. Argumentos: main= retina= sticky= sticky-retina= mobile= mobile-retina= mobile-sticky= mobile-sticky-retina= height= padding= text= link= width= width-tablet= width-mobile= valign= clear=1' );
}

// Avisos de contexto (no bloquean)
if ( get_option( 'mfn_header_entire_site' ) ) {
	be_msg( 'AVISO: hay un template de Header Builder asignado a todo el sitio (option mfn_header_entire_site). '
		. 'Con header de builder el logo lo pinta el elemento header_logo (atributo image); logo-img solo se usa como fallback si image está vacío (functions/theme-shortcodes.php:1847-1848).' );
}
if ( empty( $new['logo-img'] ) && empty( $new['logo-text'] ) && has_custom_logo() ) {
	be_msg( 'AVISO: logo-img y logo-text vacíos y hay custom_logo en el Customizer: el header clásico pintará the_custom_logo() (include-logo.php:120,149).' );
}

$diff = be_opts_save( $new, [ 'dry' => $dry, 'static' => ! be_flag( $a, 'no-static' ) ] );
be_out( [ 'dry_run' => $dry, 'written' => $written, 'diff' => $diff ] );
