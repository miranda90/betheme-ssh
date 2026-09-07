<?php
/**
 * _tmpl.php — helpers compartidos por template-list / template-assign / template-unassign
 * (CPT `template` de Betheme: Header/Footer Builder, popups, plantillas de single/archive).
 *
 * No se ejecuta directamente: lo incluyen los scripts template-*.php.
 *
 * Fuente de verdad (Betheme 28.4.3):
 *  - tipos y guardado de condiciones:  visual-builder/visual-builder.php:308-320 (tipos deprecated), 357-383
 *  - compiladores de condiciones:      functions/builder/class-mfn-builder-admin.php
 *        set_addons_templates_conditions()      l.3443   (popup → mfn_popup_addons_{archives,singular}[_<lang>])
 *        set_post_types_templates_conditions()  l.3746   (resto → mfn_<type>_template[_<lang>])
 *        reset_global_templates_conditions()    l.3883
 *        set_global_templates_conditions()      l.3919   (header/footer → mfn_<type>[_<lang>]_entire_site | _<pt>_single | _<pt>_arch | _search_page + postmeta/termmeta)
 *  - carga en CLI: Mfn_Builder_Admin solo se incluye con is_admin() (functions/builder/class-mfn-builder.php:37-40);
 *    su constructor con $ajax=true sale antes de necesitar Mfn_Builder_Fields (class-mfn-builder-admin.php:54-63).
 */

require_once __DIR__ . '/_lib.php';

/** Tipos deprecated que el builder reescribe al guardar (visual-builder/visual-builder.php:308-320) */
function be_tmpl_normalize_type( string $type ): string {
	static $map = [ 'shop-archive' => 'archive-product', 'portfolio' => 'archive-portfolio', 'blog' => 'archive-post' ];
	return $map[ $type ] ?? $type;
}

/**
 * Familia de compilación según el tipo (visual-builder/visual-builder.php:370-383):
 *  global    → header, footer                    set_global_templates_conditions()
 *  addons    → popup                             set_addons_templates_conditions()
 *  used      → cart, checkout, thanks, search    option mfn_<type>_template_used (+ set_post_types_templates_conditions)
 *  posttypes → single-*, archive-*, sidemenu, megamenu, section, wrap, default…  set_post_types_templates_conditions()
 */
function be_tmpl_family( string $type ): string {
	if ( in_array( $type, [ 'header', 'footer' ], true ) ) {
		return 'global';
	}
	if ( $type === 'popup' ) {
		return 'addons';
	}
	if ( in_array( $type, [ 'cart', 'checkout', 'thanks', 'search' ], true ) ) {
		return 'used';
	}
	return 'posttypes';
}

/** Instancia de Mfn_Builder_Admin utilizable desde wp-cli (sin hooks de admin) */
function be_tmpl_admin(): Mfn_Builder_Admin {
	if ( ! class_exists( 'Mfn_Builder_Admin' ) ) {
		$file = get_template_directory() . '/functions/builder/class-mfn-builder-admin.php';
		if ( ! file_exists( $file ) ) {
			be_err( "No existe $file" );
		}
		require_once $file;
	}
	return new Mfn_Builder_Admin( true );
}

/** Recompila las options/metas de un tipo llamando al método real del builder */
function be_tmpl_compile( string $type ): string {
	$admin  = be_tmpl_admin();
	$family = be_tmpl_family( $type );
	if ( $family === 'global' ) {
		$admin->set_global_templates_conditions( $type );
		return 'set_global_templates_conditions';
	}
	if ( $family === 'addons' ) {
		$admin->set_addons_templates_conditions( $type );
		return 'set_addons_templates_conditions';
	}
	// 'used' y 'posttypes': el builder también llama a este método cuando no hay tmpl_confirmation (visual-builder.php:381-382)
	$admin->set_post_types_templates_conditions( $type );
	return 'set_post_types_templates_conditions';
}

/** Idioma del template (WPML / Polylang) o '' */
function be_tmpl_lang( int $id ): string {
	if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
		$d = apply_filters( 'wpml_post_language_details', null, $id );
		return is_array( $d ) && ! empty( $d['language_code'] ) ? $d['language_code'] : '';
	}
	if ( function_exists( 'pll_get_post_language' ) ) {
		return (string) pll_get_post_language( $id );
	}
	return '';
}

function be_tmpl_get( int $id ): array {
	$p = get_post( $id );
	if ( ! $p || $p->post_type !== 'template' ) {
		be_err( "El post $id no existe o no es un template (post_type=template)." );
	}
	$type = (string) get_post_meta( $id, 'mfn_template_type', true );
	$raw  = get_post_meta( $id, 'mfn_template_conditions', true );
	$cond = $raw ? json_decode( $raw, true ) : [];
	return [
		'id'         => $id,
		'title'      => $p->post_title,
		'status'     => $p->post_status,
		'type'       => $type,
		'lang'       => be_tmpl_lang( $id ),
		'conditions' => is_array( $cond ) ? $cond : [],
	];
}

/** Todos los templates (cualquier estado salvo auto-draft), opcionalmente de un tipo */
function be_tmpl_all( string $type = '' ): array {
	global $wpdb;
	$sql = "SELECT p.ID, p.post_title, p.post_status, m.meta_value AS type
	        FROM {$wpdb->posts} p
	        LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = 'mfn_template_type'
	        WHERE p.post_type = 'template' AND p.post_status <> 'auto-draft'";
	if ( $type !== '' ) {
		$sql .= $wpdb->prepare( ' AND m.meta_value = %s', $type );
	}
	$sql .= ' ORDER BY p.ID ASC';
	return (array) $wpdb->get_results( $sql );
}

// ---------------------------------------------------------------------------
// Snapshot / restore de lo que compila un tipo (para que assign/unassign sean reversibles)
// ---------------------------------------------------------------------------

function be_tmpl_prefix_like( string $type ): array {
	global $wpdb;
	$t = $wpdb->esc_like( 'mfn_' . $type );
	return [
		'options'  => $t . '\_%',          // mfn_<type>_...  (incluye _<lang>_..., _addons_..., _template, _template_used)
		'postmeta' => $t . '%\_post%',     // mfn_<type>[_<lang>]_post, _post_excluded
		'termmeta' => $t . '%\_term%',     // mfn_<type>[_<lang>]_term, _term_excluded
	];
}

/** Estado actual de options/postmeta/termmeta compiladas para un tipo + conditions de sus templates */
function be_tmpl_snapshot( string $type ): array {
	global $wpdb;
	$like = be_tmpl_prefix_like( $type );

	$options = [];
	foreach ( (array) $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", $like['options'] ) ) as $r ) {
		$options[ $r->option_name ] = maybe_unserialize( $r->option_value );
	}
	$postmeta = (array) $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $like['postmeta'] ), ARRAY_A );
	$termmeta = (array) $wpdb->get_results( $wpdb->prepare( "SELECT term_id, meta_key, meta_value FROM {$wpdb->termmeta} WHERE meta_key LIKE %s", $like['termmeta'] ), ARRAY_A );

	$conditions = [];
	foreach ( be_tmpl_all( $type ) as $t ) {
		$conditions[ (int) $t->ID ] = (string) get_post_meta( $t->ID, 'mfn_template_conditions', true );
	}

	return [
		'type'       => $type,
		'created_at' => gmdate( 'c' ),
		'site'       => home_url(),
		'options'    => $options,
		'postmeta'   => $postmeta,
		'termmeta'   => $termmeta,
		'conditions' => $conditions,
	];
}

/** Escribe el snapshot en uploads/betheme/backups/ y devuelve la ruta */
function be_tmpl_snapshot_save( array $snap ): string {
	$dir = be_uploads_betheme( 'backups' )['path'];
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	$path = $dir . '/template-' . sanitize_key( $snap['type'] ) . '-' . gmdate( 'Ymd-His' ) . '.json';
	if ( file_put_contents( $path, be_json( $snap ) ) === false ) {
		be_err( "No se pudo escribir el backup en $path" );
	}
	return $path;
}

/** Restaura un snapshot: borra lo compilado actual del tipo y repone options/metas/conditions guardadas */
function be_tmpl_restore( array $snap ): array {
	global $wpdb;
	$type = (string) ( $snap['type'] ?? '' );
	if ( $type === '' ) {
		be_err( 'Backup inválido: falta "type".' );
	}
	$like = be_tmpl_prefix_like( $type );

	// limpiar estado actual
	foreach ( (array) $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like['options'] ) ) as $name ) {
		delete_option( $name );
	}
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $like['postmeta'] ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE %s", $like['termmeta'] ) );

	// reponer
	foreach ( (array) ( $snap['options'] ?? [] ) as $name => $value ) {
		update_option( $name, $value );
	}
	foreach ( (array) ( $snap['postmeta'] ?? [] ) as $row ) {
		add_post_meta( (int) $row['post_id'], $row['meta_key'], $row['meta_value'] );
	}
	foreach ( (array) ( $snap['termmeta'] ?? [] ) as $row ) {
		add_term_meta( (int) $row['term_id'], $row['meta_key'], $row['meta_value'] );
	}
	foreach ( (array) ( $snap['conditions'] ?? [] ) as $id => $json ) {
		if ( $json === '' ) {
			delete_post_meta( (int) $id, 'mfn_template_conditions' );
		} else {
			update_post_meta( (int) $id, 'mfn_template_conditions', $json );
		}
	}
	wp_cache_flush();

	return [
		'type'       => $type,
		'options'    => count( (array) ( $snap['options'] ?? [] ) ),
		'postmeta'   => count( (array) ( $snap['postmeta'] ?? [] ) ),
		'termmeta'   => count( (array) ( $snap['termmeta'] ?? [] ) ),
		'conditions' => count( (array) ( $snap['conditions'] ?? [] ) ),
	];
}

// ---------------------------------------------------------------------------
// Construcción de condiciones desde atajos de línea de comandos
// ---------------------------------------------------------------------------

/**
 * Familia global/addons (header, footer, popup). Forma que guarda el modal
 * (visual-builder/partials/modal-conditions.php:167-216): {rule, var, archives, singular, other}.
 *   everywhere=1              → var=everywhere
 *   singular=all|post,page,product:12   → var=singular, singular='' | 'post' | 'product:12' (post_type[:term_id])
 *   archive=all|product,post:5          → var=archives, archives='' | 'product' | 'post:5'
 *   search=1                  → var=other, other=search-page
 *   exclude-singular=…, exclude-archive=…  → rule=exclude
 */
function be_tmpl_build_global( array $a ): array {
	$out = [];
	$row = static function ( string $rule, string $var, string $archives = '', string $singular = '' ): array {
		return [ 'rule' => $rule, 'var' => $var, 'archives' => $archives, 'singular' => $singular, 'other' => 'search-page' ];
	};
	if ( be_flag( $a, 'everywhere' ) ) {
		$out[] = $row( 'include', 'everywhere' );
	}
	foreach ( [ 'singular' => 'include', 'exclude-singular' => 'exclude' ] as $key => $rule ) {
		if ( isset( $a[ $key ] ) && $a[ $key ] !== '' ) {
			foreach ( array_filter( array_map( 'trim', explode( ',', $a[ $key ] ) ) ) as $v ) {
				$out[] = $row( $rule, 'singular', '', $v === 'all' ? '' : $v );
			}
		}
	}
	foreach ( [ 'archive' => 'include', 'exclude-archive' => 'exclude' ] as $key => $rule ) {
		if ( isset( $a[ $key ] ) && $a[ $key ] !== '' ) {
			foreach ( array_filter( array_map( 'trim', explode( ',', $a[ $key ] ) ) ) as $v ) {
				$out[] = $row( $rule, 'archives', $v === 'all' ? '' : $v, '' );
			}
		}
	}
	if ( be_flag( $a, 'search' ) ) {
		$out[] = $row( 'include', 'other' );
	}
	return $out;
}

/**
 * Familia posttypes (single-*, archive-*): forma del modal (modal-conditions.php:74-95): {rule, var, <taxonomy>}.
 *   all=1 (o everywhere=1)        → var=all
 *   tax=portfolio-types:all,category:12   → var=<taxonomy>, <taxonomy>='all'|<term_id>
 *   exclude-tax=…                 → rule=exclude
 *   wishlist=1 | products-search=1  (solo archive-product)
 */
function be_tmpl_build_posttypes( array $a ): array {
	$out = [];
	if ( be_flag( $a, 'all' ) || be_flag( $a, 'everywhere' ) ) {
		$out[] = [ 'rule' => 'include', 'var' => 'all' ];
	}
	foreach ( [ 'tax' => 'include', 'exclude-tax' => 'exclude' ] as $key => $rule ) {
		if ( isset( $a[ $key ] ) && $a[ $key ] !== '' ) {
			foreach ( array_filter( array_map( 'trim', explode( ',', $a[ $key ] ) ) ) as $v ) {
				$parts = explode( ':', $v, 2 );
				$tax   = $parts[0];
				$term  = $parts[1] ?? 'all';
				if ( ! taxonomy_exists( $tax ) ) {
					be_msg( "aviso: la taxonomía '$tax' no está registrada en este sitio" );
				}
				$out[] = [ 'rule' => $rule, 'var' => $tax, $tax => $term ];
			}
		}
	}
	if ( be_flag( $a, 'wishlist' ) ) {
		$out[] = [ 'rule' => 'include', 'var' => 'wishlist' ];
	}
	if ( be_flag( $a, 'products-search' ) ) {
		$out[] = [ 'rule' => 'include', 'var' => 'products-search' ];
	}
	return $out;
}

/** Validación mínima de un array de condiciones (lo que el compilador lee) */
function be_tmpl_validate( array $conditions, string $family ): void {
	foreach ( $conditions as $i => $c ) {
		if ( ! is_array( $c ) || empty( $c['rule'] ) || empty( $c['var'] ) ) {
			be_err( "Condición #$i inválida: necesita 'rule' (include|exclude) y 'var'." );
		}
		if ( ! in_array( $c['rule'], [ 'include', 'exclude' ], true ) ) {
			be_err( "Condición #$i: rule debe ser include|exclude." );
		}
		if ( in_array( $family, [ 'global', 'addons' ], true ) && ! in_array( $c['var'], [ 'everywhere', 'archives', 'singular', 'other' ], true ) ) {
			be_err( "Condición #$i: para header/footer/popup var debe ser everywhere|archives|singular|other." );
		}
	}
}
