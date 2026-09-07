<?php
/**
 * opt-unset.php — elimina una o varias claves de la opción `betheme` replicando el "Save" del panel
 * (revisión de seguridad + static.css + reset del JS del BeBuilder), vía be_opts_save().
 * SAFETY: reversible (el estado anterior queda en betheme_revision_backup; ver be-options-revisions)
 *
 * Efecto de eliminar una clave: mfn_opts_get('<id>', $default) devuelve $default (el 2º argumento de
 * cada llamada del tema), NO el `std` del campo (MFN_Options::get(), muffin-options/options.php:625-627;
 * el std solo se escribe la primera vez, options.php:670-673). Detalle en 01-almacenamiento-y-guardado.md §2-3.
 *
 * Uso: wp eval-file opt-unset.php key=logo-height
 *      wp eval-file opt-unset.php key=logo-height,logo-width,logo-width-tablet
 *      ... dry-run=1      muestra qué claves se eliminarían (y cuáles no existen) sin escribir
 *      ... force=1        permite eliminar `last_tab`/`imported` y claves que no son campos del panel
 *      ... no-static=1    no regenerar static.css
 *      ... no-revision=1  no guardar revisión previa
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

if ( empty( $a['key'] ) ) {
	be_err( 'Indica key=<id>[,<id>...]' );
}
$keys = array_values( array_unique( array_filter( array_map( 'trim', explode( ',', $a['key'] ) ) ) ) );
if ( ! $keys ) {
	be_err( 'key= está vacío' );
}

$opts   = be_opts();
$fields = be_fields();
$force  = be_flag( $a, 'force' );
$dry    = be_flag( $a, 'dry-run' );

$remove  = [];   // claves que se van a eliminar
$missing = [];   // claves pedidas que no existen en la fila
$errors  = [];
foreach ( $keys as $k ) {
	if ( ! array_key_exists( $k, $opts ) ) {
		$missing[] = $k;
		continue;
	}
	$is_field = isset( $fields[ $k ] );
	if ( ! $is_field && ! $force ) {
		$what = in_array( $k, be_extra_keys(), true )
			? "'$k' es una clave interna del panel (" . implode( '/', be_extra_keys() ) . ')'
			: "'$k' no es un campo de Theme Options en Betheme " . be_theme_version();
		$errors[] = $what . ' (usa force=1 si es intencionado)';
		continue;
	}
	$remove[ $k ] = [
		'old'  => $opts[ $k ],
		'type' => $is_field ? $fields[ $k ]['type'] : null,
		// lo que el panel volvería a poner si alguien pulsa Reset; NO es lo que verá el front al leer la clave ausente
		'std'  => $is_field ? ( $fields[ $k ]['std'] ?? '' ) : null,
	];
}

if ( $errors ) {
	be_err( implode( "\n       ", $errors ) );
}
foreach ( $missing as $k ) {
	be_msg( "aviso: '$k' no existe en la opción betheme; nada que eliminar"
		. ( isset( $fields[ $k ] ) ? '' : ' (id desconocido)' ) );
}
if ( ! $remove ) {
	be_out( [ 'dry_run' => $dry, 'removed' => [], 'missing' => $missing ] );
	return;
}

foreach ( $remove as $k => $info ) {
	if ( $info['type'] !== null && $info['std'] !== '' && $info['std'] !== null ) {
		be_msg( "aviso: '$k' tiene std=" . be_json( $info['std'], false )
			. '; al eliminar la clave el front usará el default de cada llamada a mfn_opts_get(), no este std' );
	}
}

$new = $opts;
foreach ( array_keys( $remove ) as $k ) {
	unset( $new[ $k ] );
}

$diff = be_opts_save( $new, [
	'dry'      => $dry,
	'static'   => ! be_flag( $a, 'no-static' ),
	'revision' => ! be_flag( $a, 'no-revision' ),
] );

be_out( [
	'dry_run' => $dry,
	'removed' => $remove,
	'missing' => $missing,
	'diff'    => $diff,
] );
