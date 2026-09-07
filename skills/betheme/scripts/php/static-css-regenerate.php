<?php
/**
 * static-css-regenerate.php — regenera uploads/betheme/css/static.css como lo hace el panel
 * al guardar Theme Options (MFN_Options::_static_CSS(true), options.php:903-933).
 * SAFETY: read-only sobre la BD; sobrescribe un fichero derivado.
 *
 * Uso: wp eval-file static-css-regenerate.php
 *      wp eval-file static-css-regenerate.php check=1    compara el fichero actual con el CSS calculado, sin escribir
 *      wp eval-file static-css-regenerate.php print=1    vuelca el CSS calculado por stdout
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

if ( be_flag( $a, 'print' ) ) {
	echo be_static_css_build();
	return;
}

if ( be_flag( $a, 'check' ) ) {
	$path    = be_static_css_path();
	$current = file_exists( $path ) ? file_get_contents( $path ) : '';
	$fresh   = be_static_css_build();
	be_out( [
		'path'         => $path,
		'exists'       => file_exists( $path ),
		'enabled'      => (bool) mfn_opts_get( 'static-css' ),
		'up_to_date'   => $current === $fresh,
		'bytes_file'   => strlen( $current ),
		'bytes_fresh'  => strlen( $fresh ),
		'md5_file'     => md5( $current ),
		'md5_fresh'    => md5( $fresh ),
	] );
	return;
}

be_out( be_static_css_regenerate() );
