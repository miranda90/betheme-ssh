<?php
/**
 * temini-legal-set.php — escribe las metas de las páginas legales de Témini (aviso legal y
 * declaración de accesibilidad) replicando el guardado de sus metaboxes.
 * SAFETY: reversible (la salida muestra old/new por campo; vuelve a pasar el valor anterior para restaurar)
 *
 * Uso: wp eval-file temini-legal-set.php post=79 show=1                                   valores actuales (solo lectura)
 *      wp eval-file temini-legal-set.php post=79 razon_social='X, S.L.' identificacion_tipo=CIF identificacion_numero=B00000000
 *      wp eval-file temini-legal-set.php post=81 fecha_de_revision=2026-09-01
 *      ... campo=            valor vacío ⇒ borra la meta (igual que el metabox)
 *      ... translations=1    aplica también a las traducciones WPML de la página
 *      ... dry-run=1         muestra el diff sin escribir
 *      ... force=1           escribe aunque la página no use la plantilla o el valor no valide
 *
 * Qué replica (Témini 1.1.0):
 *  - aviso-legal.php (Template Name: Legal Warning): metas razon_social, nombre_comercial, direccion,
 *    identificacion_tipo (CIF|NIF|NIE|DNI), identificacion_numero, correo_electronico, juzgado
 *    (inc/utils/aviso-legal-meta.php:25-44; guardado :86-125: vacío ⇒ delete_post_meta, sanitize_text_field)
 *  - accesibilidad.php (Template Name: Accesibility Statement): meta fecha_de_revision, input type=date ⇒ YYYY-MM-DD
 *    (inc/utils/accesibilidad-meta.php:28-31, :49-58)
 *  - si la página no usa la plantilla, el metabox borra las metas al guardar (aviso-legal-meta.php:96-101,
 *    accesibilidad-meta.php:41-45): por eso sin force=1 no se escribe en una página sin plantilla.
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

/** campo => plantilla que lo activa */
function tl_fields(): array {
	$aviso = function_exists( 'temini_get_aviso_legal_meta_keys' )
		? temini_get_aviso_legal_meta_keys()
		: [ 'razon_social', 'nombre_comercial', 'direccion', 'identificacion_tipo', 'identificacion_numero', 'correo_electronico', 'juzgado' ];
	$map = [];
	foreach ( $aviso as $k ) {
		$map[ $k ] = 'aviso-legal.php';
	}
	$map['fecha_de_revision'] = 'accesibilidad.php';
	return $map;
}

function tl_tipos(): array {
	return function_exists( 'temini_get_identificacion_tipo_options' )
		? array_keys( temini_get_identificacion_tipo_options() )
		: [ 'CIF', 'NIF', 'NIE', 'DNI' ];
}

/** Traducciones WPML de una página (incluida ella misma); sin WPML devuelve solo el id */
function tl_translations( int $post_id ): array {
	$trid = apply_filters( 'wpml_element_trid', null, $post_id, 'post_page' );
	if ( ! $trid ) {
		return [ $post_id ];
	}
	$tr  = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_page' );
	$ids = [];
	foreach ( (array) $tr as $t ) {
		if ( ! empty( $t->element_id ) ) {
			$ids[] = (int) $t->element_id;
		}
	}
	return $ids ? array_values( array_unique( array_merge( [ $post_id ], $ids ) ) ) : [ $post_id ];
}

function tl_current( int $post_id ): array {
	$out = [];
	foreach ( array_keys( tl_fields() ) as $k ) {
		$v = get_post_meta( $post_id, $k, true );
		if ( '' !== $v && null !== $v && false !== $v ) {
			$out[ $k ] = $v;
		}
	}
	return $out;
}

function tl_info( int $post_id ): array {
	$post = get_post( $post_id );
	if ( ! $post || 'page' !== $post->post_type ) {
		be_err( "El post $post_id no existe o no es una página." );
	}
	$lang = apply_filters( 'wpml_post_language_details', null, $post_id );
	return [
		'id'       => $post_id,
		'title'    => $post->post_title,
		'status'   => $post->post_status,
		'template' => (string) get_page_template_slug( $post_id ),
		'lang'     => is_array( $lang ) ? ( $lang['language_code'] ?? null ) : null,
		'metas'    => tl_current( $post_id ),
	];
}

$post_id = (int) ( $a['post'] ?? 0 );
if ( $post_id <= 0 ) {
	be_err( 'Falta post=<id de la página>' );
}
$fields = tl_fields();

// ---------------------------------------------------------------------------
// show=1 (solo lectura)
// ---------------------------------------------------------------------------
if ( be_flag( $a, 'show' ) ) {
	$out = [ 'page' => tl_info( $post_id ), 'fields' => $fields ];
	$tr  = tl_translations( $post_id );
	if ( count( $tr ) > 1 ) {
		$out['translations'] = array_map( 'tl_info', array_values( array_diff( $tr, [ $post_id ] ) ) );
	}
	be_out( $out );
	return;
}

// ---------------------------------------------------------------------------
// Cambios pedidos
// ---------------------------------------------------------------------------
$changes = [];
foreach ( $a as $k => $v ) {
	if ( isset( $fields[ $k ] ) ) {
		$changes[ $k ] = sanitize_text_field( (string) $v );
	}
}
if ( ! $changes ) {
	be_err( 'Nada que cambiar: pasa pares campo=valor (' . implode( ', ', array_keys( $fields ) ) . ') o show=1' );
}

$force  = be_flag( $a, 'force' );
$errors = [];
$info   = tl_info( $post_id );

// Plantilla: cada campo exige la suya
$needed = [];
foreach ( array_keys( $changes ) as $k ) {
	$needed[ $fields[ $k ] ] = $fields[ $k ];
}
foreach ( $needed as $tpl ) {
	if ( $info['template'] !== $tpl ) {
		$errors[] = "La página $post_id usa la plantilla '" . ( $info['template'] ?: 'por defecto' ) . "', no '$tpl': el metabox no la muestra y al guardar en admin borraría estas metas. Asigna la plantilla (wp post update $post_id --page_template=$tpl)";
	}
}

// Valores
if ( isset( $changes['identificacion_tipo'] ) && '' !== $changes['identificacion_tipo'] && ! in_array( $changes['identificacion_tipo'], tl_tipos(), true ) ) {
	$errors[] = 'identificacion_tipo debe ser uno de ' . implode( '|', tl_tipos() ) . ' (el metabox lo borraría)';
}
if ( isset( $changes['correo_electronico'] ) && '' !== $changes['correo_electronico'] && ! is_email( $changes['correo_electronico'] ) ) {
	$errors[] = 'correo_electronico no es un email válido';
}
if ( isset( $changes['fecha_de_revision'] ) && '' !== $changes['fecha_de_revision'] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $changes['fecha_de_revision'] ) ) {
	$errors[] = 'fecha_de_revision debe ser YYYY-MM-DD (input type=date; la plantilla lo imprime tal cual)';
}

if ( $errors && ! $force ) {
	be_err( implode( "\n       ", $errors ) . "\n       Usa force=1 para escribir de todos modos." );
}
foreach ( $errors as $e ) {
	be_msg( 'aviso (force): ' . $e );
}

// Destinos: la página y, con translations=1, sus traducciones WPML
$targets = [ $post_id ];
if ( be_flag( $a, 'translations' ) ) {
	$targets = tl_translations( $post_id );
	if ( count( $targets ) === 1 ) {
		be_msg( 'translations=1: la página no tiene traducciones (o WPML no está activo).' );
	}
}

$dry    = be_flag( $a, 'dry-run' );
$result = [ 'dry_run' => $dry, 'pages' => [] ];

foreach ( $targets as $pid ) {
	$diff = [];
	foreach ( $changes as $k => $new ) {
		$old = (string) get_post_meta( $pid, $k, true );
		if ( $old === $new ) {
			continue;
		}
		$diff[ $k ] = [ 'old' => $old, 'new' => $new ];
		if ( $dry ) {
			continue;
		}
		if ( '' === $new ) {
			delete_post_meta( $pid, $k );           // aviso-legal-meta.php:118-121
		} else {
			update_post_meta( $pid, $k, $new );     // aviso-legal-meta.php:123
		}
	}
	if ( ! $dry && $diff ) {
		clean_post_cache( $pid );
	}
	$result['pages'][] = [
		'id'       => $pid,
		'template' => (string) get_page_template_slug( $pid ),
		'diff'     => $diff ?: 'sin cambios',
	];
}

if ( ! $dry && function_exists( 'w3tc_flush_post' ) ) {
	foreach ( $targets as $pid ) {
		w3tc_flush_post( $pid );
	}
}

be_out( $result );
