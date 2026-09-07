<?php
/**
 * opt-get.php — lee opciones de Betheme (fila wp_options.betheme).
 * SAFETY: read-only
 *
 * Uso: wp eval-file opt-get.php key=logo-img
 *      wp eval-file opt-get.php key=logo-img,retina-logo-img
 *      wp eval-file opt-get.php prefix=font-
 *      wp eval-file opt-get.php all=1
 *      ... format=raw     (un solo key escalar → imprime el valor sin comillas)
 *      ... meta=1         (añade tipo, sección y std de cada campo)
 */
require_once __DIR__ . '/_lib.php';
$a    = be_args( $args );
$opts = be_opts();

$keys = [];
if ( ! empty( $a['key'] ) ) {
	$keys = array_filter( array_map( 'trim', explode( ',', $a['key'] ) ) );
} elseif ( ! empty( $a['prefix'] ) ) {
	foreach ( array_keys( $opts ) as $k ) {
		if ( strpos( $k, $a['prefix'] ) === 0 ) {
			$keys[] = $k;
		}
	}
} elseif ( be_flag( $a, 'all' ) ) {
	$keys = array_keys( $opts );
} else {
	be_err( 'Indica key=<id>[,<id>...], prefix=<texto> o all=1' );
}

$fields = be_flag( $a, 'meta' ) ? be_fields() : [];
$out    = [];
foreach ( $keys as $k ) {
	if ( ! array_key_exists( $k, $opts ) ) {
		$out[ $k ] = null;
		be_msg( "aviso: '$k' no existe en la opción betheme" . ( isset( be_fields()[ $k ] ) ? ' (campo válido, sin valor guardado → se usa el default de mfn_opts_get)' : ' (id desconocido)' ) );
		continue;
	}
	$out[ $k ] = $opts[ $k ];
	if ( $fields ) {
		$f = $fields[ $k ] ?? null;
		$out[ $k ] = [
			'value'   => $opts[ $k ],
			'type'    => $f['type'] ?? null,
			'shape'   => $f ? be_shape( $f['type'] ) : null,
			'std'     => $f['std'] ?? null,
			'section' => $f['section'] ?? null,
			'tab'     => $f['tab'] ?? null,
		];
	}
}

if ( ( $a['format'] ?? 'json' ) === 'raw' && count( $out ) === 1 ) {
	$v = reset( $out );
	echo is_scalar( $v ) || $v === null ? (string) $v : be_json( $v );
	echo "\n";
	return;
}
be_out( $out );
