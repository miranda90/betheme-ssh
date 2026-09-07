<?php
/**
 * icons-pack-list.php — lista los packs de iconos personalizados (CPT `icons`) con sus metas,
 * su carpeta en disco y lo que el theme imprime/lee de ellos.
 * SAFETY: read-only
 *
 * Fuente (Betheme 28.4.3): functions/post-types/class-mfn-post-type-icons.php
 *   load_icons() l.180-202 (imprime <link> en wp_footer/admin_footer), get_list_of_icons() l.208-230.
 *
 * Uso: wp eval-file icons-pack-list.php
 *      wp eval-file icons-pack-list.php icons=1      (incluye la lista de nombres de icono de cada pack)
 *      wp eval-file icons-pack-list.php status=any   (también borradores/papelera; por defecto publish, como el theme)
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

if ( ! class_exists( 'Mfn_Post_Type_Icons' ) ) {
	be_err( "La clase Mfn_Post_Type_Icons no está cargada: el CPT icons está desactivado con theme-disable[custom-icons] (functions.php:117-121)." );
}

$status     = $a['status'] ?? 'publish';
$with_icons = be_flag( $a, 'icons' );
$icons_dir  = be_uploads_betheme( 'icons' ); // uploads/betheme/icons (make_dir(), l.145-155)

$posts = get_posts( [
	'post_type'      => 'icons',
	'post_status'    => $status,
	'posts_per_page' => -1,
	'orderby'        => 'ID',
	'order'          => 'ASC',
] );

$packs = [];
foreach ( $posts as $p ) {
	$name   = get_post_meta( $p->ID, 'mfn-icon-name', true );
	$parsed = get_post_meta( $p->ID, 'mfn-icon-name-parsed', true );
	$prefix = get_post_meta( $p->ID, 'mfn-icon-prefix', true );
	$upload = get_post_meta( $p->ID, 'mfn-icon-upload', true );
	$url    = get_post_meta( $p->ID, 'mfn-icon-url', true );
	$titles = get_post_meta( $p->ID, 'mfn-icon-titles-array', true );

	// carpeta que borraría single_post_remove() (l.164-168): path_icons/<name-parsed>
	$dir_expected = $parsed !== '' ? $icons_dir['path'] . '/' . $parsed : '';
	$dir_exists   = $dir_expected !== '' && is_dir( $dir_expected );

	$row = [
		'id'                 => $p->ID,
		'title'              => $p->post_title,
		'status'             => $p->post_status,
		'name'               => $name,
		'name_parsed'        => $parsed,
		'prefix'             => $prefix,
		'upload_path'        => $upload,
		'url'                => $url,
		'dir_expected'       => $dir_expected,
		'dir_exists'         => $dir_exists,
		'upload_path_ok'     => $upload !== '' && wp_normalize_path( $upload ) === $dir_expected && is_dir( $upload ),
		'style_css'          => $dir_exists && file_exists( $dir_expected . '/style.css' ),
		'selection_json'     => $dir_exists && file_exists( $dir_expected . '/selection.json' ),
		'icons_count'        => is_array( $titles ) ? max( 0, count( $titles ) - 2 ) : 0,
		'in_front'           => ( $p->post_status === 'publish' && $upload !== '' ),   // condición de load_icons() l.186,196
		'in_picker'          => ( $p->post_status === 'publish' && ! empty( $titles ) ), // condición de get_list_of_icons() l.214,224
		'warnings'           => [],
	];
	if ( $parsed === '' ) {
		$row['warnings'][] = 'mfn-icon-name-parsed vacío: al enviar a la papelera single_post_remove() borraría TODO uploads/betheme/icons';
	}
	if ( $upload !== '' && ! is_dir( $upload ) ) {
		$row['warnings'][] = 'mfn-icon-upload apunta a una ruta inexistente (¿migrado de otro servidor?); el front funciona por mfn-icon-url, el admin dirá "This is not the Icomoon zip file"';
	}
	if ( ! $row['in_picker'] && $row['in_front'] ) {
		$row['warnings'][] = 'sin mfn-icon-titles-array: el CSS carga pero el pack no aparece en el selector';
	}
	if ( $with_icons && is_array( $titles ) ) {
		$row['icons'] = array_slice( $titles, 2 );
	}
	$packs[] = $row;
}

// salida real de load_icons() (lo que va a wp_footer/admin_footer con prioridad 1)
ob_start();
Mfn_Post_Type_Icons::load_icons();
$links = ob_get_clean();

$picker = [];
foreach ( Mfn_Post_Type_Icons::get_list_of_icons() as $bundle ) {
	$picker[] = [ 'name' => $bundle[0] ?? '', 'prefix' => $bundle[1] ?? '', 'icons' => count( $bundle ) - 2 ];
}

be_out( [
	'icons_dir'          => $icons_dir,
	'packs'              => $packs,
	'load_icons_output'  => $links,
	'get_list_of_icons'  => $picker,
] );
