<?php
/**
 * font-custom-remove.php — elimina una fuente propia (vacía nombre/woff/ttf de su slot y compacta los
 * dinámicos como hace _register_custom_fonts()). No borra los ficheros de la Media Library.
 * SAFETY: reversible (el estado anterior queda en betheme_revision_backup; ver be-options-revisions)
 *
 * Uso: wp eval-file font-custom-remove.php name="Avenir Heavy"
 *      wp eval-file font-custom-remove.php slot=3            ('' o 1 = font-custom, 2 = font-custom2, N≥3 dinámicos)
 *      ... dry-run=1   muestra el diff sin escribir
 *      ... delete-media=1   además envía a la papelera los adjuntos (#id) del woff/ttf
 *
 * Lista los roles font-* (y button-font-family) que referencian la fuente: el valor del rol es el nombre
 * literal (sin #) y NO se actualiza solo; el CSS quedaría con una font-family sin @font-face.
 */
require_once __DIR__ . '/_lib.php';
$a   = be_args( $args );
$dry = be_flag( $a, 'dry-run' );

/** Igual que en font-custom-add.php (candidato a _lib.php) */
function be_font_slots( array $o ): array {
	$slots = [ '', '2' ];
	$n     = intval( $o['font-custom-fields'] ?? 0 );
	for ( $i = 3; $i < 3 + $n; $i++ ) {
		$slots[] = (string) $i;
	}
	return $slots;
}
function be_font_compact( array $o ): array {
	$n = intval( $o['font-custom-fields'] ?? 0 );
	if ( $n <= 0 ) {
		return $o;
	}
	$loaded = 3;
	for ( $loop = 3; $loop < 3 + $n; $loop++ ) {
		$nm = $o[ 'font-custom' . $loop ] ?? '';
		if ( $nm !== '' ) {
			$o[ 'font-custom' . $loaded ]           = $nm;
			$o[ 'font-custom' . $loaded . '-woff' ] = $o[ 'font-custom' . $loop . '-woff' ] ?? '';
			$o[ 'font-custom' . $loaded . '-ttf' ]  = $o[ 'font-custom' . $loop . '-ttf' ] ?? '';
			$loaded++;
		} else {
			$o[ 'font-custom' . $loop ]           = '';
			$o[ 'font-custom' . $loop . '-woff' ] = '';
			$o[ 'font-custom' . $loop . '-ttf' ]  = '';
		}
	}
	for ( $loop = $loaded; $loop < 3 + $n; $loop++ ) {
		$o[ 'font-custom' . $loop ]           = '';
		$o[ 'font-custom' . $loop . '-woff' ] = '';
		$o[ 'font-custom' . $loop . '-ttf' ]  = '';
	}
	$o['font-custom-fields'] = (string) ( $loaded - 3 );
	return $o;
}

$old  = be_opts();
$slot = null;

if ( array_key_exists( 'slot', $a ) ) {
	$slot = trim( (string) $a['slot'] );
	if ( $slot === '1' ) {
		$slot = '';
	}
	if ( ! in_array( $slot, be_font_slots( $old ), true ) ) {
		be_err( "slot '$slot' no existe (slots actuales: 1, 2" . ( intval( $old['font-custom-fields'] ?? 0 ) ? ', 3..' . ( 2 + intval( $old['font-custom-fields'] ) ) : '' ) . ')' );
	}
} elseif ( ! empty( $a['name'] ) ) {
	$name = ltrim( trim( (string) $a['name'] ), '#' );
	foreach ( be_font_slots( $old ) as $s ) {
		if ( ( $old[ 'font-custom' . $s ] ?? '' ) === $name ) {
			$slot = $s;
			break;
		}
	}
	if ( $slot === null ) {
		be_err( "No hay ninguna fuente propia llamada '$name'. Fuentes: " . implode( ', ', array_filter( array_map( fn( $s ) => $old[ 'font-custom' . $s ] ?? '', be_font_slots( $old ) ) ) ) );
	}
} else {
	be_err( 'Indica name= o slot=' );
}

$name = $old[ 'font-custom' . $slot ] ?? '';
$woff = $old[ 'font-custom' . $slot . '-woff' ] ?? '';
$ttf  = $old[ 'font-custom' . $slot . '-ttf' ] ?? '';
if ( $name === '' && $woff === '' && $ttf === '' ) {
	be_err( "El slot '" . ( $slot === '' ? '1' : $slot ) . "' ya está vacío." );
}

// roles que la usan (font_select guarda el nombre literal; style.php:112 tolera '#')
$roles = [ 'font-content', 'font-lead', 'font-menu', 'font-title', 'font-headings', 'font-headings-small', 'font-blockquote', 'font-decorative', 'button-font-family' ];
$used  = [];
foreach ( $roles as $r ) {
	if ( $name !== '' && ltrim( (string) ( $old[ $r ] ?? '' ), '#' ) === $name ) {
		$used[] = $r;
	}
}
if ( $used ) {
	be_msg( "AVISO: '$name' sigue asignada en: " . implode( ', ', $used ) . '. Reasígnalos con be-font-role-set.sh o el CSS pedirá una font-family sin @font-face.' );
}

$new = $old;
$new[ 'font-custom' . $slot ]           = '';
$new[ 'font-custom' . $slot . '-woff' ] = '';
$new[ 'font-custom' . $slot . '-ttf' ]  = '';
$new = be_font_compact( $new );

$media = [];
foreach ( [ $woff, $ttf ] as $v ) {
	if ( strpos( (string) $v, '#' ) !== false ) {
		$id = intval( explode( '#', $v, 2 )[1] );
		if ( $id ) {
			$media[] = $id;
		}
	}
}

$diff = be_opts_save( $new, [ 'dry' => $dry ] );

$trashed = [];
if ( be_flag( $a, 'delete-media' ) && ! $dry ) {
	foreach ( $media as $id ) {
		if ( wp_trash_post( $id ) ) {
			$trashed[] = $id;
		}
	}
}

be_out( [
	'dry_run'   => $dry,
	'removed'   => [ 'slot' => $slot === '' ? '1' : $slot, 'name' => $name, 'woff' => $woff, 'ttf' => $ttf ],
	'still_used_in_roles' => $used,
	'font_custom_fields'  => $new['font-custom-fields'] ?? '',
	'media_ids' => $media,
	'media_trashed' => $trashed,
	'diff'      => $diff,
] );
