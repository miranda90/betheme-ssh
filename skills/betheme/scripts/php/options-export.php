<?php
/**
 * options-export.php — exporta Theme Options en JSON, idéntico al feed `?feed=mfn-opts-betheme`
 * (MFN_Options::_download_options(), muffin-options/options.php:868-897): la opción `betheme`
 * entera + 'mfn-opts-backup' => '1'. El fichero resultante se puede pegar en el panel
 * (Backup & Reset → Import from file) o pasar a be-options-import.
 * SAFETY: read-only
 *
 * Uso: wp eval-file options-export.php                         (JSON por stdout)
 *      wp eval-file options-export.php out=/ruta/backup.json   (escribe fichero, resumen por stdout)
 *      ... strip=1          quita `last_tab` (pestaña abierta en el panel, no es una opción)
 *      ... backup-flag=0    no añade 'mfn-opts-backup' (queda igual que `wp option get betheme --format=json`)
 *      ... compact=1        JSON en una línea
 */
require_once __DIR__ . '/_lib.php';
require_once __DIR__ . '/_options-backup.php';
$a = be_args( $args );

$opts = be_opts();
if ( ! $opts ) {
	be_err( 'La opción betheme está vacía o no existe.' );
}

$data = be_export_array(
	$opts,
	! ( isset( $a['backup-flag'] ) && ! be_flag( $a, 'backup-flag' ) ),
	be_flag( $a, 'strip' )
);

if ( ! empty( $a['out'] ) ) {
	list( $path, $bytes ) = be_write_json_file( $a['out'], $data );
	be_out( [
		'path'    => $path,
		'bytes'   => $bytes,
		'keys'    => count( $data ),
		'version' => be_theme_version(),
		'site'    => home_url(),
	] );
	return;
}

be_out( $data, ! be_flag( $a, 'compact' ) );
