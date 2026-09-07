<?php
/**
 * tools-regenerate-css.php — Betheme > Tools > "Local CSS · Regenerate files": vuelve a escribir
 * uploads/betheme/css/post-<ID>.css a partir de la meta `mfn-page-local-style` de cada post.
 * SAFETY: reversible (solo sobrescribe ficheros derivados; la BD no se toca)
 *
 * Fuente (Betheme 28.4.3): functions/builder/class-mfn-builder-ajax.php _tool_regenerate_css() l.436-465
 *   (SELECT ID de posts publish no attachment → json_decode(mfn-page-local-style) → Mfn_Helper::generate_css()).
 *   Mfn_Helper::generate_css(): functions/admin/class-mfn-helper.php l.568-685.
 *
 * Uso: wp eval-file tools-regenerate-css.php                 (todos los publicados con mfn-page-local-style, como el botón)
 *      wp eval-file tools-regenerate-css.php ids=28,122,175  (solo esos posts, en cualquier estado)
 *      wp eval-file tools-regenerate-css.php preview=1       (además post-<ID>-preview.css desde mfn-builder-preview-local-style)
 *      wp eval-file tools-regenerate-css.php dry-run=1       (lista qué se escribiría)
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );
be_load_admin_classes(); // Mfn_Helper no está cargada en CLI (functions.php:216-243)

global $wpdb;
$dry     = be_flag( $a, 'dry-run' );
$preview = be_flag( $a, 'preview' );
$css_dir = be_uploads_betheme( 'css' );

if ( ! empty( $a['ids'] ) ) {
	$ids = array_filter( array_map( 'intval', explode( ',', $a['ids'] ) ) );
} else {
	// misma consulta que el handler (l.446)
	$ids = array_map( 'intval', $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type NOT LIKE 'attachment'" ) );
}

$done = $skipped = [];
foreach ( $ids as $id ) {
	$post = get_post( $id );
	if ( ! $post ) {
		$skipped[] = [ 'id' => $id, 'why' => 'no existe' ];
		continue;
	}
	$jobs = [ [ 'meta' => 'mfn-page-local-style', 'preview' => false, 'file' => "post-$id.css" ] ];
	if ( $preview ) {
		$jobs[] = [ 'meta' => 'mfn-builder-preview-local-style', 'preview' => 'preview', 'file' => "post-$id-preview.css" ];
	}
	foreach ( $jobs as $job ) {
		$raw = get_post_meta( $id, $job['meta'], true ); // l.451 lee meta_value crudo
		if ( empty( $raw ) ) {
			if ( $job['meta'] === 'mfn-page-local-style' ) {
				$skipped[] = [ 'id' => $id, 'why' => 'sin mfn-page-local-style' ];
			}
			continue;
		}
		$styles = is_array( $raw ) ? $raw : json_decode( $raw, true ); // l.454
		if ( ! is_array( $styles ) ) {
			$skipped[] = [ 'id' => $id, 'why' => $job['meta'] . ' no es JSON válido' ];
			continue;
		}
		$path = $css_dir['path'] . '/' . $job['file'];
		$row  = [
			'id'    => $id,
			'type'  => $post->post_type,
			'title' => $post->post_title,
			'file'  => $path,
			'existed' => file_exists( $path ),
			'bytes_before' => file_exists( $path ) ? filesize( $path ) : 0,
		];
		if ( ! $dry ) {
			Mfn_Helper::generate_css( $styles, $id, $job['preview'] ); // l.455
			clearstatcache( true, $path );
			$row['bytes_after'] = file_exists( $path ) ? filesize( $path ) : 0;
		}
		$done[] = $row;
	}
}

be_out( [
	'dry_run'    => $dry,
	'css_dir'    => $css_dir['path'],
	'written'    => count( $done ),
	'files'      => $done,
	'skipped'    => count( $skipped ),
	'skipped_list' => $skipped,
	'note'       => 'El front encola post-<ID>.css solo si existe mfn-page-local-style Y el fichero (class-mfn-builder-front.php:224-229); con local-styles-location=inline el CSS va inline y este fichero no se usa.',
] );
