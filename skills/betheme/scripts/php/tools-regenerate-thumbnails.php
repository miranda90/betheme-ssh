<?php
/**
 * tools-regenerate-thumbnails.php — Betheme > Tools > "Regenerate Thumbnails": regenera los tamaños
 * de imagen de todos los adjuntos y restaura width/height de los SVG en su metadata.
 * SAFETY: destructive (sobrescribe las miniaturas existentes y la meta _wp_attachment_metadata; sin backup) — exige yes=1
 *
 * Equivalente nativo de wp-cli (preferible, con --dry-run y filtros): `wp media regenerate --yes`.
 * Este script replica exactamente el handler del theme, incluido el tratamiento de SVG que wp-cli no hace.
 *
 * Fuente (Betheme 28.4.3): functions/builder/class-mfn-builder-ajax.php _regenerate_thumbnails() l.92-164
 *   (progreso en la option `be_regenerate_thumbnails`, l.107-109 y 156-161; SVG l.128-139).
 *
 * Uso: wp eval-file tools-regenerate-thumbnails.php yes=1
 *      wp eval-file tools-regenerate-thumbnails.php yes=1 ids=12,34    (solo esos adjuntos)
 *      wp eval-file tools-regenerate-thumbnails.php yes=1 resume=1     (continúa desde be_regenerate_thumbnails, como el botón)
 *      wp eval-file tools-regenerate-thumbnails.php dry-run=1          (lista adjuntos y qué haría con cada uno)
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php'; // wp_generate_attachment_metadata()

$dry    = be_flag( $a, 'dry-run' );
$resume = be_flag( $a, 'resume' );
$basedir = wp_upload_dir()['basedir'];

if ( ! empty( $a['ids'] ) ) {
	$attachments = get_posts( [ 'post_type' => 'attachment', 'posts_per_page' => -1, 'post__in' => array_map( 'intval', explode( ',', $a['ids'] ) ), 'orderby' => 'post__in' ] );
} else {
	$attachments = get_posts( [ 'post_type' => 'attachment', 'posts_per_page' => -1 ] ); // l.100-103
}

$offset = 0;
if ( $resume && ! empty( get_option( 'be_regenerate_thumbnails' ) ) ) { // l.107-109
	$offset = (int) get_option( 'be_regenerate_thumbnails' );
}

if ( ! $dry ) {
	be_require_yes( $a, 'Regenerar miniaturas de ' . count( $attachments ) . ' adjuntos' );
}

$svg_types = [ 'image/svg', 'image/svg+xml', 'font/svg' ]; // l.130
$rows = [];
$stats = [ 'total' => count( $attachments ), 'offset' => $offset, 'regenerated' => 0, 'svg' => 0, 'missing_file' => 0 ];

foreach ( $attachments as $i => $at ) {
	if ( $i < $offset ) {
		continue;
	}
	$img  = get_post_meta( $at->ID, '_wp_attached_file', true ); // l.118
	$path = $basedir . '/' . $img;                               // l.120
	$mime = get_post_mime_type( $at->ID );
	$row  = [ 'id' => $at->ID, 'file' => $img, 'mime' => $mime, 'action' => 'skip (fichero ausente)' ];

	if ( $img && file_exists( $path ) ) {
		$row['action'] = in_array( $mime, $svg_types, true ) ? 'svg: width/height desde el XML' : 'wp_generate_attachment_metadata';
		if ( ! $dry ) {
			$data = wp_generate_attachment_metadata( $at->ID, $path ); // l.124
			if ( empty( $data['width'] ) || empty( $data['height'] ) ) { // l.128
				if ( in_array( $mime, $svg_types, true ) ) {
					$svg = @simplexml_load_file( $path );                // l.134
					if ( ! empty( $svg ) ) {
						$attr = $svg->attributes();
						$data['width']  = (string) $attr->width[0];
						$data['height'] = (string) $attr->height[0];
					}
					$stats['svg']++;
				}
			}
			wp_update_attachment_metadata( $at->ID, $data ); // l.151
			$row['width']  = $data['width'] ?? null;
			$row['height'] = $data['height'] ?? null;
			$stats['regenerated']++;
		}
	} else {
		$stats['missing_file']++;
	}
	$rows[] = $row;

	if ( ! $dry && empty( $a['ids'] ) ) {
		update_option( 'be_regenerate_thumbnails', $i ); // l.156 (progreso reanudable)
	}
}

if ( $dry ) {
	be_out( [ 'dry_run' => true, 'stats' => $stats, 'attachments' => $rows ] );
	return;
}

delete_option( 'be_regenerate_thumbnails' ); // l.161
be_out( [ 'stats' => $stats, 'attachments' => $rows ] );
