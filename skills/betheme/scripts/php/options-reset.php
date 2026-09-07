<?php
/**
 * options-reset.php — restablece TODAS las Theme Options a su `std`, como el botón
 * "Reset to default" + código r3s3t del panel (muffin-options/options.php:2262-2274, JS
 * muffin-options/js/options.js:580-586 → betheme[defaults]='Resetting...' → _validate_options()
 * l.1175-1178 → _default_values() l.640-663). Guarda antes una revisión `backup`.
 * SAFETY: destructive (revisión previa en betheme_revision_backup; ver be-options-revisions)
 *
 * Uso: wp eval-file options-reset.php dry-run=1
 *      wp eval-file options-reset.php yes=1
 *      ... keep=custom-css,custom-js     ids cuyo valor actual se conserva (el panel no lo permite)
 *      ... no-static=1                   no regenerar static.css
 */
require_once __DIR__ . '/_lib.php';
require_once __DIR__ . '/_options-backup.php';
$a = be_args( $args );

$mfn      = be_options_obj();
$current  = be_opts();
$defaults = $mfn->_default_values(); // incluye last_tab => false (options.php:662)

$kept = [];
if ( ! empty( $a['keep'] ) ) {
	foreach ( array_filter( array_map( 'trim', explode( ',', $a['keep'] ) ) ) as $id ) {
		if ( array_key_exists( $id, $current ) ) {
			$defaults[ $id ] = $current[ $id ];
			$kept[]          = $id;
		} else {
			be_msg( "aviso: keep '$id' no existe en la opción actual, se ignora" );
		}
	}
}

$dry = be_flag( $a, 'dry-run' );
if ( ! $dry ) {
	be_require_yes( $a, 'Reset de todas las Theme Options' );
}

$diff = be_opts_save( $defaults, [
	'dry'    => $dry,
	'static' => ! be_flag( $a, 'no-static' ),
] );

be_out( [
	'dry_run' => $dry,
	'kept'    => $kept,
	'summary' => [
		'changed' => count( $diff['changed'] ),
		'added'   => count( $diff['added'] ),
		'removed' => count( $diff['removed'] ),
		'total'   => count( $defaults ),
	],
	'diff'    => $diff,
] );
