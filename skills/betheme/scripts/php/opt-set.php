<?php
/**
 * opt-set.php — escribe una o varias opciones de Betheme replicando el "Save" del panel
 * (revisión de seguridad + static.css + reset del JS del BeBuilder).
 * SAFETY: reversible (el estado anterior queda en betheme_revision_backup; ver be-options-revisions)
 *
 * Uso: wp eval-file opt-set.php key=logo-height value=80
 *      wp eval-file opt-set.php key=font-size-h1 json=1 value='{"size":"48px","line_height":"56px","weight_style":"400","letter_spacing":"0"}'
 *      wp eval-file opt-set.php set=@cambios.json          (objeto JSON {id: valor, ...})
 *      ... dry-run=1      muestra el diff sin escribir
 *      ... force=1        permite ids desconocidos o formas de valor no esperadas
 *      ... no-static=1    no regenerar static.css
 *      ... no-revision=1  no guardar revisión previa
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

$changes = [];
if ( ! empty( $a['set'] ) ) {
	$changes = json_decode( $a['set'], true );
	if ( ! is_array( $changes ) ) {
		be_err( 'set= debe ser un objeto JSON {id: valor, ...} (usa set=@fichero.json)' );
	}
}
if ( ! empty( $a['key'] ) ) {
	if ( ! array_key_exists( 'value', $a ) ) {
		be_err( 'Falta value=' );
	}
	$changes[ $a['key'] ] = be_value_parse( $a['value'], be_flag( $a, 'json' ) );
}
if ( ! $changes ) {
	be_err( 'Nada que cambiar: usa key=/value= o set=@fichero.json' );
}

$fields = be_fields();
$force  = be_flag( $a, 'force' );
$errors = [];
foreach ( $changes as $id => $value ) {
	if ( ! isset( $fields[ $id ] ) && ! in_array( $id, be_extra_keys(), true ) ) {
		$errors[] = "'$id' no es un campo de Theme Options en Betheme " . be_theme_version() . ' (usa force=1 si es intencionado)';
		continue;
	}
	if ( ! isset( $fields[ $id ] ) ) {
		continue;
	}
	$shape = be_shape( $fields[ $id ]['type'] );
	if ( strpos( $shape, 'no persiste' ) === 0 ) {
		$errors[] = "'$id' es un campo de tipo {$fields[$id]['type']}: no guarda valor";
	} elseif ( be_shape_is_array( $shape ) && ! is_array( $value ) ) {
		$errors[] = "'$id' espera $shape; pasa el valor con json=1";
	} elseif ( ! be_shape_is_array( $shape ) && is_array( $value ) ) {
		$errors[] = "'$id' espera $shape, no un array";
	}
}
if ( $errors && ! $force ) {
	be_err( implode( "\n       ", $errors ) );
}
foreach ( $errors as $e ) {
	be_msg( 'aviso (force): ' . $e );
}

$new = array_merge( be_opts(), $changes );
$dry = be_flag( $a, 'dry-run' );
$diff = be_opts_save( $new, [
	'dry'      => $dry,
	'static'   => ! be_flag( $a, 'no-static' ),
	'revision' => ! be_flag( $a, 'no-revision' ),
] );

be_out( [ 'dry_run' => $dry, 'diff' => $diff ] );
