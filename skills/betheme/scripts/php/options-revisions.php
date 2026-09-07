<?php
/**
 * options-revisions.php — lista, compara, exporta, crea y restaura revisiones de Theme Options
 * (options betheme_revision_update|revision|backup, formato [timestamp => base64(serialize)], máx. 5;
 * MFN_Options::set_revision() muffin-options/options.php:132-166, restauración l.1119-1135).
 * SAFETY: read-only por defecto; restore=1 y save=1 son reversibles (restore guarda antes la actual en `backup`)
 *
 * Uso: wp eval-file options-revisions.php                              (lista las 3 colas)
 *      wp eval-file options-revisions.php type=update ts=1787238636     (diff revisión → estado actual)
 *      wp eval-file options-revisions.php type=update ts=1787238636 export=/ruta/rev.json
 *      wp eval-file options-revisions.php type=update ts=1787238636 restore=1 [dry-run=1]
 *      wp eval-file options-revisions.php save=1 [type=revision]        (como el botón "Save revision")
 *      ... full=1     en el diff, valores completos en vez de recortados
 */
require_once __DIR__ . '/_lib.php';
require_once __DIR__ . '/_options-backup.php';
$a = be_args( $args );

// --- save=1: guardar el estado actual como revisión (options.js:455-456 → _revision_save() l.100-126)
if ( be_flag( $a, 'save' ) ) {
	$type = $a['type'] ?? 'revision';
	be_revisions_raw( $type ); // valida el tipo
	$current = be_opts();
	unset( $current['last_tab'], $current['import_code'], $current['import_link'] ); // options.php:117-119
	if ( be_flag( $a, 'dry-run' ) ) {
		be_out( [ 'dry_run' => true, 'type' => $type, 'keys' => count( $current ) ] );
		return;
	}
	be_options_obj()->set_revision( $type, $current );
	be_out( [ 'saved' => $type, 'revisions' => be_revisions_summary()[ $type ] ] );
	return;
}

// --- sin type/ts: listado
if ( empty( $a['type'] ) && empty( $a['ts'] ) ) {
	be_out( [ 'current_keys' => count( be_opts() ), 'revisions' => be_revisions_summary() ] );
	return;
}
if ( empty( $a['type'] ) || empty( $a['ts'] ) ) {
	be_err( 'Indica type=update|revision|backup y ts=<timestamp> (ver listado sin argumentos)' );
}

$type = $a['type'];
$ts   = (int) $a['ts'];
$raw  = be_revisions_raw( $type );
if ( ! isset( $raw[ $ts ] ) ) {
	be_err( "No existe la revisión $type/$ts. Disponibles: " . ( $raw ? implode( ', ', array_keys( $raw ) ) : 'ninguna' ) );
}
$rev = be_revision_decode( (string) $raw[ $ts ] );

// --- export=ruta
if ( ! empty( $a['export'] ) ) {
	list( $path, $bytes ) = be_write_json_file( $a['export'], be_export_array( $rev, true, false ) );
	be_out( [ 'type' => $type, 'ts' => $ts, 'date' => be_revision_date( $ts ), 'path' => $path, 'bytes' => $bytes, 'keys' => count( $rev ) ] );
	return;
}

// --- restore=1: el panel devuelve la revisión tal cual desde _validate_options() (options.php:1129-1132)
//     y el JS crea antes una revisión `backup` (options.js:519-521); be_opts_save hace lo mismo.
if ( be_flag( $a, 'restore' ) ) {
	$dry  = be_flag( $a, 'dry-run' );
	$diff = be_opts_save( $rev, [
		'dry'           => $dry,
		'revision_type' => 'backup',
		'static'        => ! be_flag( $a, 'no-static' ),
	] );
	be_out( [
		'dry_run'  => $dry,
		'restored' => [ 'type' => $type, 'ts' => $ts, 'date' => be_revision_date( $ts ) ],
		'summary'  => [ 'changed' => count( $diff['changed'] ), 'added' => count( $diff['added'] ), 'removed' => count( $diff['removed'] ) ],
		'diff'     => $diff,
	] );
	return;
}

// --- por defecto: diff revisión → estado actual (lo que cambiaría al restaurar)
$diff = be_diff( be_opts(), $rev );
if ( ! be_flag( $a, 'full' ) ) {
	$cut = function ( $v ) {
		$s = is_scalar( $v ) || $v === null ? (string) $v : be_json( $v, false );
		return strlen( $s ) > 120 ? substr( $s, 0, 117 ) . '...' : $v;
	};
	foreach ( $diff['changed'] as $k => $pair ) {
		$diff['changed'][ $k ] = [ 'old' => $cut( $pair['old'] ), 'new' => $cut( $pair['new'] ) ];
	}
	foreach ( [ 'added', 'removed' ] as $s ) {
		foreach ( $diff[ $s ] as $k => $v ) {
			$diff[ $s ][ $k ] = $cut( $v );
		}
	}
}
be_out( [
	'revision' => [ 'type' => $type, 'ts' => $ts, 'date' => be_revision_date( $ts ), 'keys' => count( $rev ) ],
	'summary'  => [ 'changed' => count( $diff['changed'] ), 'added' => count( $diff['added'] ), 'removed' => count( $diff['removed'] ) ],
	'diff'     => $diff,
] );
