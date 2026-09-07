<?php
/**
 * temini-popup-set.php — asigna páginas a un popup de Betheme vía el campo ACF `paginas_mostradas`
 * de Témini (inc/utils/popup-tmpl-by-page.php) y borra el transient del índice página → popups.
 * SAFETY: reversible (la salida muestra el valor anterior; vuelve a pasarlo en pages= para restaurar)
 *
 * Uso: wp eval-file temini-popup-set.php list=1                       lista popups y sus páginas (solo lectura)
 *      wp eval-file temini-popup-set.php template=210 pages=28,95     asigna
 *      wp eval-file temini-popup-set.php template=210 pages=          quita la asignación (borra la meta)
 *      ... dry-run=1   muestra qué cambiaría sin escribir
 *      ... force=1     permite un template que no sea popup o IDs que no sean páginas
 *
 * Qué replica (Témini 1.1.0, inc/utils/popup-tmpl-by-page.php):
 *  - campo ACF `paginas_mostradas`, key `field_paginas_mostradas`, post_object múltiple de `page`, return id  (:27-53)
 *  - lectura del índice: get_post_meta('paginas_mostradas') y get_field() de respaldo                         (:183-187)
 *  - transient `temini_popup_by_page_v1[_<lang>]`, invalidado por temini_flush_popup_page_map_cache()          (:9, :81-106)
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

const TP_META  = 'paginas_mostradas';
const TP_FIELD = 'field_paginas_mostradas';
const TP_TRANS = 'temini_popup_by_page_v1';

/** IDs de templates de tipo popup (cualquier estado), con título y estado */
function tp_popups(): array {
	$ids = get_posts( [
		'post_type'      => 'template',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => 'mfn_template_type',
		'meta_value'     => 'popup',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	] );
	$out = [];
	foreach ( $ids as $id ) {
		$out[ (int) $id ] = [
			'id'     => (int) $id,
			'title'  => get_the_title( $id ),
			'status' => get_post_status( $id ),
			'pages'  => tp_pages_of( (int) $id ),
		];
	}
	return $out;
}

/** Páginas asignadas a un popup, tal y como las lee el tema (meta cruda; get_field de respaldo) */
function tp_pages_raw( int $popup_id ): array {
	$pages = get_post_meta( $popup_id, TP_META, true );
	if ( function_exists( 'get_field' ) && ( empty( $pages ) || ! is_array( $pages ) ) ) {
		$pages = get_field( TP_META, $popup_id, false );
	}
	if ( empty( $pages ) || ! is_array( $pages ) ) {
		return [];
	}
	return array_values( array_filter( array_map( 'intval', $pages ) ) );
}

function tp_pages_of( int $popup_id ): array {
	$out = [];
	foreach ( tp_pages_raw( $popup_id ) as $pid ) {
		$out[] = [ 'id' => $pid, 'title' => get_the_title( $pid ), 'type' => get_post_type( $pid ), 'status' => get_post_status( $pid ) ];
	}
	return $out;
}

/** Transients del índice presentes en wp_options (con object cache persistente pueden no estar aquí) */
function tp_transients_in_db(): array {
	global $wpdb;
	$like = $wpdb->esc_like( '_transient_' . TP_TRANS ) . '%';
	$rows = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
	return array_map( 'strval', (array) $rows );
}

/** Borra el índice en todos los idiomas: función del tema + LIKE en wp_options + object cache */
function tp_flush(): array {
	global $wpdb;
	$before = tp_transients_in_db();
	if ( function_exists( 'temini_flush_popup_page_map_cache' ) ) {
		temini_flush_popup_page_map_cache();
	}
	delete_transient( TP_TRANS );
	$langs = apply_filters( 'wpml_active_languages', null, [ 'skip_missing' => 0 ] );
	foreach ( is_array( $langs ) ? array_keys( $langs ) : [] as $code ) {
		delete_transient( TP_TRANS . '_' . $code );
	}
	$deleted = 0;
	foreach ( [ '_transient_', '_transient_timeout_' ] as $prefix ) {
		$like     = $wpdb->esc_like( $prefix . TP_TRANS ) . '%';
		$deleted += (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
	}
	wp_cache_flush();
	return [ 'rows_before' => $before, 'rows_deleted' => $deleted ];
}

// ---------------------------------------------------------------------------
// list=1 (solo lectura)
// ---------------------------------------------------------------------------
if ( be_flag( $a, 'list' ) ) {
	be_out( [
		'acf_active'   => function_exists( 'get_field' ),
		'field_exists' => function_exists( 'acf_get_field' ) && (bool) acf_get_field( TP_FIELD ),
		'popups'       => array_values( tp_popups() ),
		'transients'   => tp_transients_in_db(),
	] );
	return;
}

// ---------------------------------------------------------------------------
// template= pages=
// ---------------------------------------------------------------------------
$template = (int) ( $a['template'] ?? 0 );
if ( $template <= 0 ) {
	be_err( 'Falta template=<id del popup> (o usa list=1)' );
}
if ( ! array_key_exists( 'pages', $a ) ) {
	be_err( 'Falta pages=1,2,3 (o pages= vacío para quitar la asignación)' );
}

$force = be_flag( $a, 'force' );
$post  = get_post( $template );
if ( ! $post || 'template' !== $post->post_type ) {
	be_err( "El post $template no existe o no es un template de Betheme (post_type template)." );
}
if ( 'popup' !== get_post_meta( $template, 'mfn_template_type', true ) ) {
	$msg = "El template $template no es de tipo popup (mfn_template_type=" . get_post_meta( $template, 'mfn_template_type', true ) . '); el tema solo lee paginas_mostradas en popups.';
	$force ? be_msg( 'aviso (force): ' . $msg ) : be_err( $msg . ' Usa force=1 si es intencionado.' );
}
if ( 'publish' !== $post->post_status ) {
	be_msg( "aviso: el popup $template está en estado '{$post->post_status}'; el índice solo incluye popups publicados (popup-tmpl-by-page.php:151)." );
}

$pages = array_values( array_filter( array_map( 'intval', preg_split( '/[\s,]+/', (string) $a['pages'] ) ?: [] ) ) );
$pages = array_values( array_unique( $pages ) );
$bad   = [];
foreach ( $pages as $pid ) {
	if ( 'page' !== get_post_type( $pid ) ) {
		$bad[] = "$pid no es una página (post_type=" . ( get_post_type( $pid ) ?: 'inexistente' ) . ')';
	}
}
if ( $bad ) {
	$force ? be_msg( 'aviso (force): ' . implode( '; ', $bad ) ) : be_err( implode( "\n       ", $bad ) . "\n       El campo ACF solo admite páginas. Usa force=1 si es intencionado." );
}

$before = tp_pages_raw( $template );
$dry    = be_flag( $a, 'dry-run' );
$result = [
	'dry_run'  => $dry,
	'template' => [ 'id' => $template, 'title' => $post->post_title, 'status' => $post->post_status ],
	'before'   => $before,
	'after'    => $pages,
	'method'   => null,
];

if ( $before === $pages ) {
	be_msg( 'Sin cambios: la asignación ya es esa.' );
}

if ( ! $dry && $before !== $pages ) {
	if ( ! $pages ) {
		// Quitar: sin meta, temini_build_popup_page_map() salta el popup (:189-191)
		if ( function_exists( 'delete_field' ) ) {
			delete_field( TP_FIELD, $template );
			$result['method'] = 'acf:delete_field';
		} else {
			delete_post_meta( $template, TP_META );
			delete_post_meta( $template, '_' . TP_META );
			$result['method'] = 'delete_post_meta';
		}
	} elseif ( function_exists( 'update_field' ) && function_exists( 'acf_get_field' ) && acf_get_field( TP_FIELD ) ) {
		// update_field con field key: ACF escribe el valor y la meta _paginas_mostradas = field key
		update_field( TP_FIELD, $pages, $template );
		$result['method'] = 'acf:update_field';
	} else {
		// Sin ACF: misma forma que guarda ACF (array de IDs como strings) + pareja _campo = field key
		update_post_meta( $template, TP_META, array_map( 'strval', $pages ) );
		update_post_meta( $template, '_' . TP_META, TP_FIELD );
		$result['method'] = 'update_post_meta';
	}
	clean_post_cache( $template );
	$result['after']      = tp_pages_raw( $template );
	$result['transients'] = tp_flush();
} elseif ( ! $dry ) {
	// Sin cambios en la meta, pero vaciar el índice por si se escribió por SSH antes
	$result['transients'] = tp_flush();
}

be_out( $result );
