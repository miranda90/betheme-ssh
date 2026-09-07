<?php
/**
 * cache-htaccess.php — bloque "# BEGIN BETHEME … # END BETHEME" (cabeceras Expires) en .htaccess,
 * lo que el panel escribe al activar Performance > Cache assets (`hold-cache`).
 * SAFETY: reversible (copia previa .htaccess.bak-<fecha>; off deshace on)
 *
 * Fuente (Betheme 28.4.3): muffin-options/options.php _cache_manager() l.961-984, _setup_cache() l.986-999,
 *   _remove_cache() l.1001-1010; texto del bloque Mfn_Helper::get_cache_text() (functions/admin/class-mfn-helper.php:762-811).
 *   En el admin solo corre si el transient betheme_hold-cache vale 'changed' (lo pone el JS del switch,
 *   fields/switch/field_switch.js:38) y con ?settings-updated; por SSH nada de eso ocurre → este script.
 *
 * Uso: wp eval-file cache-htaccess.php mode=status
 *      wp eval-file cache-htaccess.php mode=on  [with-option=1]   (añade el bloque; with-option también pone hold-cache=1)
 *      wp eval-file cache-htaccess.php mode=off [with-option=1]   (quita el bloque; with-option también pone hold-cache=0)
 *      ... dry-run=1   muestra el .htaccess resultante sin escribir
 *      ... force=1     permite on aunque hold-cache no sea 1 (el theme exige hold-cache=1, l.988)
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );
be_load_admin_classes();
require_once ABSPATH . 'wp-admin/includes/file.php'; // get_home_path()

$mode  = $a['mode'] ?? 'status';
$dry   = be_flag( $a, 'dry-run' );
$fs    = be_filesystem();
$path  = get_home_path() . '.htaccess'; // l.993, l.1004
$regex = '/(# BEGIN BETHEME)(.|\n)*?(# END BETHEME)/'; // l.1007

$exists  = file_exists( $path );
$content = $exists ? (string) $fs->get_contents( $path ) : '';
$blocks  = preg_match_all( $regex, $content );
$opt     = (string) mfn_opts_get( 'hold-cache', '0' );

$status = [
	'htaccess'      => $path,
	'exists'        => $exists,
	'writable'      => $exists ? is_writable( $path ) : is_writable( dirname( $path ) ),
	'block_present' => $blocks > 0,
	'block_count'   => $blocks,
	'hold_cache'    => $opt,
	'transient_betheme_hold-cache' => get_transient( 'betheme_hold-cache' ),
	'consistent'    => ( $blocks > 0 ) === ( $opt === '1' ),
];
if ( $blocks > 1 ) {
	$status['warning'] = '_setup_cache() añade el bloque sin comprobar si ya existe: hay duplicados; usa mode=off y luego mode=on';
}

if ( $mode === 'status' ) {
	be_out( $status );
	return;
}
if ( ! in_array( $mode, [ 'on', 'off' ], true ) ) {
	be_err( 'mode= debe ser status | on | off' );
}

if ( $mode === 'on' ) {
	if ( $opt !== '1' && ! be_flag( $a, 'force' ) && ! be_flag( $a, 'with-option' ) ) {
		be_err( "hold-cache vale '$opt': el theme solo escribe el bloque con hold-cache=1 (l.988). Usa with-option=1 (pone la opción a 1) o force=1." );
	}
	if ( $blocks > 0 && ! be_flag( $a, 'force' ) ) {
		be_msg( 'El bloque ya existe: no se añade otro (Betheme lo duplicaría). Usa force=1 si quieres duplicarlo igualmente.' );
		$new = $content;
	} else {
		$new = $content . Mfn_Helper::get_cache_text(); // l.996: concatena al final tal cual
	}
} else {
	$new = preg_replace( $regex, '', $content ); // l.1007
}

$changed = $new !== $content;
$result  = [
	'mode'      => $mode,
	'dry_run'   => $dry,
	'changed'   => $changed,
	'bytes'     => [ 'before' => strlen( $content ), 'after' => strlen( $new ) ],
	'status_before' => $status,
];

if ( $dry ) {
	$result['preview_tail'] = substr( $new, -1200 );
	if ( be_flag( $a, 'with-option' ) ) {
		$result['option_diff'] = be_opts_save( array_merge( be_opts(), [ 'hold-cache' => $mode === 'on' ? '1' : '0' ] ), [ 'dry' => true ] );
	}
	be_out( $result );
	return;
}

if ( $changed ) {
	if ( $exists ) {
		$bak = $path . '.bak-' . gmdate( 'Ymd-His' );
		if ( ! copy( $path, $bak ) ) {
			be_err( "No se pudo crear la copia $bak" );
		}
		$result['backup'] = $bak;
	}
	if ( ! $fs->put_contents( $path, $new, 0644 ) ) { // l.998 / l.1009 (permisos 0644)
		be_err( "No se pudo escribir $path" );
	}
	delete_transient( 'betheme_hold-cache' ); // l.971 / l.977
	@clearstatcache();                         // l.972 / l.978
}

if ( be_flag( $a, 'with-option' ) ) {
	$result['option_diff'] = be_opts_save( array_merge( be_opts(), [ 'hold-cache' => $mode === 'on' ? '1' : '0' ] ), [ 'revision_type' => 'backup' ] );
}

clearstatcache();
$after = file_exists( $path ) ? (string) $fs->get_contents( $path ) : '';
$result['block_count_after'] = preg_match_all( $regex, $after );
$result['hold_cache_after']  = (string) mfn_opts_get( 'hold-cache', '0' );
be_out( $result );
