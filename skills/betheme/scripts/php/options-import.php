<?php
/**
 * options-import.php — importa un backup de Theme Options replicando el "Import" del panel
 * (MFN_Options::_validate_options(), muffin-options/options.php:1141-1171): reemplaza la opción
 * `betheme` entera, añade imported=1 y last_tab=false. A diferencia del panel, acepta JSON,
 * ###serialize###, serialize y base64(serialize), y guarda antes una revisión `backup`.
 * SAFETY: destructive (sustituye TODAS las opciones; revisión previa en betheme_revision_backup)
 *
 * Uso: wp eval-file options-import.php in=/ruta/backup.json dry-run=1        (solo diff)
 *      wp eval-file options-import.php in=/ruta/backup.json yes=1
 *      wp eval-file options-import.php in=@- yes=1 < backup.json               (stdin)
 *      ... merge=1                        fusiona sobre lo actual (array_merge) en vez de reemplazar
 *      ... replace-url=https://a.com,https://b.com   reemplazo en todos los valores string (logos, fuentes...)
 *      ... min-known=100                  mínimo de ids reconocidos por be_fields() (def. 100); force=1 lo ignora
 *      ... no-static=1                    no regenerar static.css
 */
require_once __DIR__ . '/_lib.php';
require_once __DIR__ . '/_options-backup.php';
$a = be_args( $args );

if ( empty( $a['in'] ) ) {
	be_err( 'Falta in=/ruta/backup.json (o in=@- para stdin)' );
}
$raw = $a['in'];
if ( strlen( $raw ) < 4096 && is_readable( $raw ) ) {
	$raw = file_get_contents( $raw );
}

list( $data, $format ) = be_backup_parse( $raw );
be_msg( "Formato detectado: $format (" . count( $data ) . ' claves)' );

// Validación: array con un mínimo de ids de Theme Options reales
list( $known, $unknown ) = be_backup_known_keys( $data );
$min = (int) ( $a['min-known'] ?? 100 );
if ( $known < $min && ! be_flag( $a, 'force' ) ) {
	be_err( "Solo $known claves son ids de Theme Options en Betheme " . be_theme_version() . " (mínimo $min). ¿Es un backup de Betheme? Usa min-known=N o force=1." );
}
if ( $unknown ) {
	be_msg( 'aviso: ' . count( $unknown ) . ' claves no son campos del panel actual (se conservan): ' . implode( ', ', array_slice( $unknown, 0, 15 ) ) . ( count( $unknown ) > 15 ? ', …' : '' ) );
}

// Lo mismo que hace el panel al importar (options.php:1165-1168), más quitar la marca del export
unset( $data[ BE_BACKUP_FLAG ] );
$data['imported'] = 1;
$data['last_tab'] = false;

// replace-url=old,new (equivalente a class-mfn-importer-helper.php:609-616)
if ( ! empty( $a['replace-url'] ) ) {
	$pair = explode( ',', $a['replace-url'], 2 );
	if ( count( $pair ) !== 2 || $pair[0] === '' ) {
		be_err( 'replace-url= debe ser old,new' );
	}
	list( $data, $n ) = be_backup_replace_url( $data, trim( $pair[0] ), trim( $pair[1] ) );
	be_msg( "replace-url: $n valores modificados" );
}

$current = be_opts();
$new     = be_flag( $a, 'merge' ) ? array_merge( $current, $data ) : $data;

$dry = be_flag( $a, 'dry-run' );
if ( ! $dry ) {
	be_require_yes( $a, 'Importar Theme Options' );
}

$diff = be_opts_save( $new, [
	'dry'    => $dry,
	'static' => ! be_flag( $a, 'no-static' ),
] );

be_out( [
	'dry_run' => $dry,
	'mode'    => be_flag( $a, 'merge' ) ? 'merge' : 'replace',
	'format'  => $format,
	'summary' => [
		'changed' => count( $diff['changed'] ),
		'added'   => count( $diff['added'] ),
		'removed' => count( $diff['removed'] ),
	],
	'diff'    => $diff,
] );
