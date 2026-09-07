<?php
/**
 * font-custom-add.php — da de alta una fuente propia (Theme Options → Fonts → Custom):
 * nombre + .woff + .ttf en un slot font-custom{,2,N}, replicando el "Save" del panel.
 * SAFETY: reversible (el estado anterior queda en betheme_revision_backup; ver be-options-revisions)
 *
 * Slots (muffin-options/fonts.php:34-56, functions/theme-head.php:951-981):
 *   ''  → font-custom / font-custom-woff / font-custom-ttf
 *   2   → font-custom2 / font-custom2-woff / font-custom2-ttf
 *   N≥3 → font-customN / -woff / -ttf, y `font-custom-fields` = cuántos dinámicos hay (N max = 2 + font-custom-fields)
 * Si no se pasa slot= se usa el primero libre (nombre vacío), en ese orden.
 *
 * Los ficheros se pasan como ruta local (se importan a la Media Library; mimes woff/ttf ya permitidos por
 * mfn_upload_mimes(), muffin-options/theme-options.php:12058) o como URL http(s) ya subida.
 *
 * Uso: wp eval-file font-custom-add.php name="Avenir Heavy" woff=/tmp/Avenir-Heavy.woff ttf=/tmp/Avenir-Heavy.ttf
 *      wp eval-file font-custom-add.php name="Mi Fuente" woff=https://sitio.com/wp-content/uploads/2026/01/mi.woff
 *      wp eval-file font-custom-add.php name=... woff=... slot=2       (fuerza el slot; sobreescribe si estaba ocupado con force=1)
 *      ... dry-run=1   muestra el diff sin escribir (las rutas locales NO se importan)
 */
require_once __DIR__ . '/_lib.php';
$a   = be_args( $args );
$dry = be_flag( $a, 'dry-run' );

$name = trim( (string) ( $a['name'] ?? '' ) );
if ( $name === '' ) {
	be_err( 'Falta name= (nombre de la fuente, sin #)' );
}
$name = ltrim( $name, '#' );
if ( empty( $a['woff'] ) && empty( $a['ttf'] ) ) {
	be_err( 'Pasa al menos woff= o ttf= (ruta local o URL)' );
}

/** ruta local → import (URL#id); URL → tal cual (+#id si el adjunto existe). Candidato a _lib.php: be_upload_arg */
function be_font_upload_arg( string $raw, bool $dry ): string {
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

/** Lista de slots existentes: ['' , '2', '3', ...] según font-custom-fields (fonts.php:50-56) */
function be_font_slots( array $o ): array {
	$slots = [ '', '2' ];
	$n     = intval( $o['font-custom-fields'] ?? 0 );
	for ( $i = 3; $i < 3 + $n; $i++ ) {
		$slots[] = (string) $i;
	}
	return $slots;
}

/**
 * Compacta los slots dinámicos (3..N) como hace MFN_Options::_register_custom_fonts()
 * (muffin-options/options.php:1072-1107): descarta los de nombre vacío, renumera desde 3
 * y recalcula font-custom-fields. Devuelve el array de opciones ya compactado.
 */
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
	// los slots que quedaron por encima del último cargado se vacían (evita restos)
	for ( $loop = $loaded; $loop < 3 + $n; $loop++ ) {
		$o[ 'font-custom' . $loop ]           = '';
		$o[ 'font-custom' . $loop . '-woff' ] = '';
		$o[ 'font-custom' . $loop . '-ttf' ]  = '';
	}
	$o['font-custom-fields'] = (string) ( $loaded - 3 );
	return $o;
}

$old = be_opts();
$new = be_font_compact( $old );
if ( $new !== $old ) {
	be_msg( 'Nota: los slots dinámicos estaban sin compactar; se compactan como haría _register_custom_fonts() en init.' );
}

// nombre duplicado
foreach ( be_font_slots( $new ) as $s ) {
	if ( ( $new[ 'font-custom' . $s ] ?? '' ) === $name && (string) ( $a['slot'] ?? '' ) !== $s ) {
		be_err( "Ya existe una fuente llamada '$name' en el slot '" . ( $s === '' ? '1' : $s ) . "'. Usa font-custom-remove o slot=$s con force=1." );
	}
}

// slot destino
$slot = array_key_exists( 'slot', $a ) ? trim( (string) $a['slot'] ) : null;
if ( $slot === '1' ) {
	$slot = '';
}
if ( $slot === null ) {
	$slot = null;
	foreach ( be_font_slots( $new ) as $s ) {
		if ( ( $new[ 'font-custom' . $s ] ?? '' ) === '' ) {
			$slot = $s;
			break;
		}
	}
	if ( $slot === null ) {
		// nuevo slot dinámico
		$slot = (string) ( 3 + intval( $new['font-custom-fields'] ?? 0 ) );
	}
} elseif ( ! ( $slot === '' || $slot === '2' || ( ctype_digit( $slot ) && intval( $slot ) >= 3 ) ) ) {
	be_err( "slot= admite '' (o 1), 2, o N≥3" );
}

if ( ( $new[ 'font-custom' . $slot ] ?? '' ) !== '' && ! be_flag( $a, 'force' ) ) {
	be_err( "El slot '" . ( $slot === '' ? '1' : $slot ) . "' ya contiene '{$new['font-custom' . $slot]}'. Añade force=1 para sobreescribir." );
}

// slot dinámico nuevo por encima de los existentes → sube font-custom-fields (y rellena huecos intermedios vacíos)
if ( $slot !== '' && intval( $slot ) >= 3 ) {
	$need = intval( $slot ) - 2;
	if ( $need > intval( $new['font-custom-fields'] ?? 0 ) ) {
		if ( $need - 1 > intval( $new['font-custom-fields'] ?? 0 ) ) {
			be_msg( "AVISO: slot $slot deja huecos vacíos por debajo; _register_custom_fonts() lo compactará al slot " . ( 3 + intval( $new['font-custom-fields'] ?? 0 ) ) . ' en la siguiente carga.' );
		}
		$new['font-custom-fields'] = (string) $need;
	}
}

$new[ 'font-custom' . $slot ]           = $name;
$new[ 'font-custom' . $slot . '-woff' ] = be_font_upload_arg( (string) ( $a['woff'] ?? '' ), $dry );
$new[ 'font-custom' . $slot . '-ttf' ]  = be_font_upload_arg( (string) ( $a['ttf'] ?? '' ), $dry );

$diff = be_opts_save( $new, [ 'dry' => $dry ] );
be_out( [
	'dry_run' => $dry,
	'slot'    => $slot === '' ? '1' : $slot,
	'ids'     => [ 'font-custom' . $slot, 'font-custom' . $slot . '-woff', 'font-custom' . $slot . '-ttf', 'font-custom-fields' ],
	'font'    => [ 'name' => $name, 'woff' => $new[ 'font-custom' . $slot . '-woff' ], 'ttf' => $new[ 'font-custom' . $slot . '-ttf' ] ],
	'font_custom_fields' => $new['font-custom-fields'] ?? '',
	'use_in_roles'       => "be-font-role-set.sh role=headings font=\"$name\"  (valor guardado sin #)",
	'diff'    => $diff,
] );
