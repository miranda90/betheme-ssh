<?php
/**
 * icons-pack-add.php — da de alta un pack de iconos IcoMoon como lo hace el campo `upload_icon`
 * del admin: descomprime, filtra extensiones, reescribe `icon-` → `<prefix>-` en style.css,
 * crea el post `icons` y escribe las 6 metas.
 * SAFETY: reversible (be-icons-pack-remove id=<ID> --yes deshace todo)
 *
 * Fuente (Betheme 28.4.3): muffin-options/fields/upload_icon/field_upload_icon.php
 *   upload_icons() l.46-137, add_prefix_on_upload() l.13-28, parse_str_on_upload() l.34-40,
 *   set_icons_name() l.143-166. Directorios: class-mfn-post-type-icons.php make_dir() l.145-155.
 *
 * Uso: wp eval-file icons-pack-add.php zip=/ruta/icomoon.zip name="Mi Pack" prefix=mipack
 *      wp eval-file icons-pack-add.php dir=/ruta/carpeta-ya-descomprimida name="Mi Pack" prefix=mipack
 *      ... dry-run=1            muestra el plan (nombre parseado, carpeta, ficheros que se conservarían) sin tocar nada
 *      ... no-prefix-rewrite=1  no reescribe `icon-` en style.css (si el zip ya trae el prefijo definitivo)
 *      ... status=draft         crea el post sin publicar (por defecto publish; solo publish cuenta para el theme)
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

if ( ! class_exists( 'Mfn_Post_Type_Icons' ) ) {
	be_err( 'El CPT icons está desactivado (theme-disable[custom-icons], functions.php:117-121). Actívalo antes de añadir packs.' );
}

$name   = trim( (string) ( $a['name'] ?? '' ) );
$prefix = trim( (string) ( $a['prefix'] ?? '' ) );
$zip    = (string) ( $a['zip'] ?? '' );
$dir    = (string) ( $a['dir'] ?? '' );
$dry    = be_flag( $a, 'dry-run' );
$status = $a['status'] ?? 'publish';

if ( $name === '' || $prefix === '' ) {
	be_err( 'Faltan name= y/o prefix= (upload_icons() no hace nada si falta alguno, l.69-71).' );
}
if ( ( $zip === '' ) === ( $dir === '' ) ) {
	be_err( 'Indica exactamente uno: zip=/ruta.zip o dir=/ruta/carpeta' );
}
if ( $zip !== '' && ! is_readable( $zip ) ) {
	be_err( "No se puede leer $zip" );
}
if ( $dir !== '' && ! is_dir( $dir ) ) {
	be_err( "No existe la carpeta $dir" );
}

/** Réplica exacta de parse_str_on_upload() (l.34-40): quita todo lo que no sea [A-Za-z0-9_] y pasa espacios a "_" */
function be_icons_parse( string $s ): string {
	return preg_replace( '[\s]', '_', preg_replace( '[\W]', '', $s ) );
}

$name_parsed   = sanitize_file_name( be_icons_parse( $name ) ); // l.60 + l.75
$prefix_parsed = be_icons_parse( $prefix );                     // l.63 (el prefijo también pierde guiones y acentos)

if ( $name_parsed === '' || validate_file( $name_parsed ) !== 0 ) { // l.76-78
	be_err( "Nombre inválido tras parsear: '$name' → '$name_parsed'" );
}
if ( $prefix_parsed === '' ) {
	be_err( "Prefijo inválido tras parsear: '$prefix'" );
}
if ( $prefix_parsed !== $prefix ) {
	be_msg( "aviso: el prefijo se guarda parseado como '$prefix_parsed' (parse_str_on_upload elimina guiones/acentos); las clases serán {$prefix_parsed}-<icono>" );
}
if ( $name_parsed !== $name ) {
	be_msg( "aviso: carpeta = '$name_parsed' (nombre parseado)" );
}

$icons_dir  = be_uploads_betheme( 'icons' );
$path_icons = $icons_dir['path'];
$dest       = $path_icons . '/' . $name_parsed;                 // $new_icon_path, l.82
$dest_url   = $icons_dir['url'] . '/' . $name_parsed;            // l.83 usa get_home_url().'/wp-content/uploads/...' a pelo; aquí baseurl real
$allowed    = [ 'svg', 'ttf', 'woff', 'woff2', 'eot', 'json', 'css' ]; // l.113

// duplicados: mismo name-parsed en cualquier post icons (cualquier estado) o carpeta ya existente
$dupes = get_posts( [
	'post_type'      => 'icons',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'meta_key'       => 'mfn-icon-name-parsed',
	'meta_value'     => $name_parsed,
	'fields'         => 'ids',
] );
if ( $dupes ) {
	be_err( "Ya existe un pack con name-parsed '$name_parsed' (post " . implode( ',', $dupes ) . '). Bórralo con be-icons-pack-remove o usa otro name.' );
}
if ( is_dir( $dest ) ) {
	be_err( "Ya existe la carpeta $dest sin post asociado. Bórrala a mano (rm -rf) o usa otro name." );
}
foreach ( get_posts( [ 'post_type' => 'icons', 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => 'mfn-icon-prefix', 'meta_value' => $prefix_parsed, 'fields' => 'ids' ] ) as $pid ) {
	be_msg( "aviso: el post $pid ya usa el prefijo '$prefix_parsed'; las clases colisionarán" );
}

// listado previo (para dry-run y para validar que es IcoMoon)
$entries = [];
if ( $zip !== '' ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		be_msg( 'aviso: sin ZipArchive no se puede listar el zip en dry-run; unzip_file() usará PclZip' );
	} else {
		$za = new ZipArchive();
		if ( $za->open( $zip ) !== true ) {
			be_err( "No se puede abrir el zip $zip" );
		}
		for ( $i = 0; $i < $za->numFiles; $i++ ) {
			$n = $za->getNameIndex( $i );
			if ( substr( $n, -1 ) !== '/' ) {
				$entries[] = $n;
			}
		}
		$za->close();
	}
} else {
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		$entries[] = ltrim( str_replace( wp_normalize_path( $dir ), '', wp_normalize_path( $f->getPathname() ) ), '/' );
	}
}
$keep = $drop = [];
foreach ( $entries as $e ) {
	$ext = strtolower( pathinfo( $e, PATHINFO_EXTENSION ) );
	if ( in_array( $ext, $allowed, true ) ) {
		$keep[] = $e;
	} else {
		$drop[] = $e;
	}
}
$has_style = in_array( 'style.css', $keep, true );
$has_sel   = in_array( 'selection.json', $keep, true );
if ( $entries && ( ! $has_style || ! $has_sel ) ) {
	be_err( 'No parece un zip de IcoMoon: faltan style.css y/o selection.json en la raíz (set_icons_name() lee selection.json, l.147).' );
}

$plan = [
	'name'          => $name,
	'name_parsed'   => $name_parsed,
	'prefix'        => $prefix_parsed,
	'source'        => $zip !== '' ? $zip : $dir,
	'dest_path'     => $dest,
	'dest_url'      => $dest_url,
	'post_status'   => $status,
	'files_kept'    => $keep,
	'files_dropped' => $drop,
	'prefix_rewrite'=> ! be_flag( $a, 'no-prefix-rewrite' ),
];
if ( $dry ) {
	be_out( [ 'dry_run' => true, 'plan' => $plan ] );
	return;
}

// ---- ejecución ---------------------------------------------------------------------------
$fs = be_filesystem(); // Mfn_Helper::filesystem() (FS_METHOD direct) + wp-admin/includes/file.php (unzip_file, copy_dir)

foreach ( [ $icons_dir['path'] ] as $d ) { // make_dir(): wp_mkdir_p crea también uploads/betheme
	if ( ! file_exists( $d ) ) {
		wp_mkdir_p( $d );
	}
}

$rollback = function ( string $why ) use ( $fs, $dest ) {
	if ( is_dir( $dest ) ) {
		$fs->delete( $dest, true );
	}
	be_err( $why );
};

if ( $zip !== '' ) {
	$tmp_zip = wp_normalize_path( $dest . '.zip' ); // l.85: mueve el zip a path_icons/<parsed>.zip
	if ( ! copy( $zip, $tmp_zip ) ) {
		be_err( "No se pudo copiar el zip a $tmp_zip" );
	}
	$unzip = unzip_file( $tmp_zip, $dest ); // l.101
	$fs->delete( $tmp_zip );                // l.126
	if ( is_wp_error( $unzip ) ) {
		$rollback( 'unzip_file: ' . $unzip->get_error_message() );
	}
} else {
	wp_mkdir_p( $dest );
	$copy = copy_dir( $dir, $dest );
	if ( is_wp_error( $copy ) ) {
		$rollback( 'copy_dir: ' . $copy->get_error_message() );
	}
}
if ( ! is_dir( $dest ) ) {
	$rollback( "Tras descomprimir no existe $dest (l.107)" );
}

// whitelist de extensiones (l.113-122): borra ficheros, deja carpetas (p. ej. demo-files/ queda vacía o con demo.css)
$removed = [];
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dest, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $f ) {
	if ( $f->isDir() ) {
		continue;
	}
	$ext = strtolower( pathinfo( $f->getFilename(), PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, $allowed, true ) ) {
		unlink( $f->getRealPath() );
		$removed[] = $f->getFilename();
	}
}

if ( ! file_exists( $dest . '/style.css' ) || ! file_exists( $dest . '/selection.json' ) ) {
	$rollback( 'El paquete no contiene style.css y selection.json en la raíz: no es un zip de IcoMoon.' );
}

// icon- → <prefix>- en style.css (add_prefix_on_upload, l.13-28)
$rewritten = 0;
if ( $plan['prefix_rewrite'] ) {
	$css = $fs->get_contents( $dest . '/style.css' );
	$rewritten = preg_match_all( '/icon-/', $css );
	if ( $rewritten === 0 ) {
		be_msg( 'aviso: style.css no contiene "icon-": el zip no viene con el prefijo por defecto de IcoMoon; las clases NO se han reescrito' );
	}
	$fs->delete( $dest . '/style.css' );
	$fs->put_contents( $dest . '/style.css', preg_replace( '/icon-/', $prefix_parsed . '-', $css ) );
}

// titles-array desde selection.json (set_icons_name, l.143-166)
$json = json_decode( (string) $fs->get_contents( $dest . '/selection.json' ), true );
if ( ! is_array( $json ) || empty( $json['icons'] ) ) {
	$rollback( 'selection.json no es JSON de IcoMoon (sin clave icons).' );
}
$titles = [ $name, $prefix_parsed ]; // [0] nombre sin parsear, [1] prefijo
foreach ( $json['icons'] as $icon ) {
	$titles[] = $icon['properties']['name'] ?? '';
}

// post + metas (l.89-92, 134, 164)
$post_id = wp_insert_post( [
	'post_type'   => 'icons',
	'post_status' => $status,
	'post_title'  => $name,
], true );
if ( is_wp_error( $post_id ) ) {
	$rollback( 'wp_insert_post: ' . $post_id->get_error_message() );
}
update_post_meta( $post_id, 'mfn-icon-name', $name );
update_post_meta( $post_id, 'mfn-icon-name-parsed', $name_parsed );
update_post_meta( $post_id, 'mfn-icon-prefix', $prefix_parsed );
update_post_meta( $post_id, 'mfn-icon-upload', $dest );
update_post_meta( $post_id, 'mfn-icon-url', $dest_url );
update_post_meta( $post_id, 'mfn-icon-titles-array', $titles );

be_out( [
	'post_id'          => $post_id,
	'status'           => $status,
	'name'             => $name,
	'name_parsed'      => $name_parsed,
	'prefix'           => $prefix_parsed,
	'path'             => $dest,
	'url'              => $dest_url,
	'style_css_url'    => $dest_url . '/style.css',
	'classes_rewritten'=> $rewritten,
	'files_removed'    => $removed,
	'icons'            => array_slice( $titles, 2 ),
	'icons_count'      => count( $titles ) - 2,
	'example_class'    => $prefix_parsed . '-' . ( $titles[2] ?? '<icono>' ),
] );
