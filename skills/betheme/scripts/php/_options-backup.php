<?php
/**
 * _options-backup.php — helpers compartidos por options-export / options-import / options-reset / options-revisions.
 * No se ejecuta solo: lo incluyen esos scripts después de _lib.php.
 *
 * Fuente de verdad (Betheme 28.4.3):
 *  - feed de export:        MFN_Options::_download_options()   muffin-options/options.php:868-897
 *  - textarea "Copy":       options.php:2198-2202  ('###' . serialize($options) . '###')
 *  - import:                MFN_Options::_validate_options()   options.php:1141-1171
 *  - revisiones:            set_revision() l.132-166, get_revisions() l.194-221, restore l.1119-1135
 *  - demo options.txt:      Mfn_Importer_Helper::options()    functions/importer/class-mfn-importer-helper.php:570-620
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 2 );
}

/** Marca que el feed y la textarea añaden al export (options.php:880 y 2199) */
define( 'BE_BACKUP_FLAG', 'mfn-opts-backup' );

/** Tipos de cola de revisiones, en el orden en que los lista el panel (options.php:196-200) */
function be_revision_types(): array {
	return [ 'update', 'revision', 'backup' ];
}

/**
 * Array de opciones tal y como lo sirve el feed `?feed=mfn-opts-betheme` (options.php:879-881):
 * la opción entera + 'mfn-opts-backup' => '1'. Con $strip se quita `last_tab` (clave de UI).
 */
function be_export_array( array $opts, bool $flag = true, bool $strip = false ): array {
	if ( $strip ) {
		unset( $opts['last_tab'] );
	}
	if ( $flag ) {
		$opts[ BE_BACKUP_FLAG ] = '1';
	}
	return $opts;
}

/**
 * Interpreta cualquier formato de backup que produce Betheme y devuelve el array de opciones:
 *   1. JSON (feed / Download / `wp option get --format=json`)
 *   2. '###' . serialize() . '###' (botón Copy de la pestaña Backup & Reset, options.php:2200)
 *   3. serialize() a pelo
 *   4. base64(serialize()) (options.txt de las demos y revisiones betheme_revision_*)
 * El panel solo acepta de verdad el JSON: su fallback base64 (options.php:1159) compara
 * json_decode() === false, pero json_decode devuelve NULL, así que nunca entra.
 * Devuelve [array, 'formato'] o aborta.
 */
function be_backup_parse( string $raw ): array {
	$raw = trim( $raw );
	if ( $raw === '' ) {
		be_err( 'Contenido vacío.' );
	}

	// 1. JSON
	$data = json_decode( $raw, true );
	if ( is_array( $data ) ) {
		return [ $data, 'json' ];
	}

	// 2. ###serialize###
	$ser = $raw;
	if ( substr( $ser, 0, 3 ) === '###' && substr( $ser, -3 ) === '###' ) {
		$ser = substr( $ser, 3, -3 );
	}
	// 3. serialize a pelo
	if ( $ser !== '' && $ser[0] === 'a' ) {
		$data = @unserialize( $ser, [ 'allowed_classes' => false ] );
		if ( is_array( $data ) ) {
			return [ $data, substr( $raw, 0, 3 ) === '###' ? 'textarea' : 'serialize' ];
		}
	}
	// 4. base64(serialize)
	$decoded = base64_decode( $ser, true );
	if ( $decoded !== false ) {
		$data = @unserialize( $decoded, [ 'allowed_classes' => false ] );
		if ( is_array( $data ) ) {
			return [ $data, 'base64' ];
		}
	}

	be_err( 'No se reconoce el formato: se aceptan JSON, ###serialize###, serialize o base64(serialize).' );
}

/**
 * Cuenta cuántas claves del array son ids reales de Theme Options (be_fields()) o claves extra válidas.
 * Devuelve [conocidas, desconocidas[]]
 */
function be_backup_known_keys( array $data ): array {
	$fields  = be_fields();
	$extra   = array_merge( be_extra_keys(), [ BE_BACKUP_FLAG ] );
	$known   = 0;
	$unknown = [];
	foreach ( array_keys( $data ) as $k ) {
		if ( isset( $fields[ $k ] ) || in_array( $k, $extra, true ) ) {
			$known++;
		} else {
			$unknown[] = $k;
		}
	}
	return [ $known, $unknown ];
}

/**
 * Reemplazo de cadena en todos los valores string (solo primer nivel, como hace el importador de demos
 * en class-mfn-importer-helper.php:609-616 con la URL de origen → home_url()).
 * Devuelve [array, nº de valores tocados].
 */
function be_backup_replace_url( array $data, string $old, string $new ): array {
	$n = 0;
	foreach ( $data as $k => $v ) {
		if ( is_string( $v ) && $old !== '' && strpos( $v, $old ) !== false ) {
			$data[ $k ] = str_replace( $old, $new, $v );
			$n++;
		}
	}
	return [ $data, $n ];
}

/** Cola de revisiones de un tipo: [ts => base64(serialize)] o [] */
function be_revisions_raw( string $type ): array {
	if ( ! in_array( $type, be_revision_types(), true ) ) {
		be_err( "type= debe ser uno de: " . implode( ', ', be_revision_types() ) );
	}
	$r = get_option( 'betheme_revision_' . $type );
	return is_array( $r ) ? $r : [];
}

/** Decodifica una revisión (base64(serialize), options.php:138 y 1131) */
function be_revision_decode( string $blob ): array {
	$data = @unserialize( base64_decode( $blob ), [ 'allowed_classes' => false ] );
	if ( ! is_array( $data ) ) {
		be_err( 'La revisión no se puede decodificar (base64 + unserialize).' );
	}
	return $data;
}

/**
 * Fecha legible de un timestamp de revisión. set_revision() usa current_time('timestamp')
 * (options.php:159), que ya lleva el desfase de la zona horaria de WP, así que se formatea con
 * date() a secas, igual que get_revisions() (options.php:213).
 */
function be_revision_date( int $ts ): string {
	return date( 'Y-m-d H:i:s', $ts );
}

/** Resumen de las tres colas: type → [ [ts, date, keys, bytes], ... ] */
function be_revisions_summary(): array {
	$out = [];
	foreach ( be_revision_types() as $type ) {
		$out[ $type ] = [];
		foreach ( be_revisions_raw( $type ) as $ts => $blob ) {
			$data = @unserialize( base64_decode( (string) $blob ), [ 'allowed_classes' => false ] );
			$out[ $type ][] = [
				'ts'    => (int) $ts,
				'date'  => be_revision_date( (int) $ts ),
				'keys'  => is_array( $data ) ? count( $data ) : null,
				'bytes' => strlen( (string) $blob ),
			];
		}
	}
	return $out;
}

/** Escribe JSON en un fichero (crea el directorio) y devuelve [path, bytes] */
function be_write_json_file( string $path, array $data ): array {
	$dir = dirname( $path );
	if ( ! is_dir( $dir ) && ! @mkdir( $dir, 0755, true ) ) {
		be_err( "No se puede crear el directorio $dir" );
	}
	$json  = be_json( $data, true ) . "\n";
	$bytes = @file_put_contents( $path, $json );
	if ( $bytes === false ) {
		be_err( "No se puede escribir $path" );
	}
	return [ $path, $bytes ];
}
