<?php
/**
 * template-list.php — lista los templates de Betheme (CPT `template`) con su tipo, condiciones
 * y todo lo que apunta a cada uno (options mfn_*, postmeta, termmeta).
 * SAFETY: read-only
 *
 * Uso: wp eval-file template-list.php
 *      wp eval-file template-list.php type=header
 *      wp eval-file template-list.php used=1          (solo los que están realmente en uso)
 *      wp eval-file template-list.php refs=0          (sin buscar referencias: más rápido)
 *      wp eval-file template-list.php options=1       (añade el volcado de todas las options mfn_*)
 *
 * "En uso" = publicado y referenciado por alguna option mfn_* (mfn_<type>_entire_site, mfn_<type>_template{,_used},
 * mfn_popup_addons_*…), por un postmeta (mfn_header_template, mfn_footer_template, mfn_popup_included,
 * mfn_<type>[_<lang>]_post, mfn_single-*_template, mfn_dedicated_header_template, mfn_template_id) o por un termmeta
 * (mfn_<type>[_<lang>]_term); los sidemenu también cuentan si mfn_sidemenu_visibility=always-visible
 * (functions/theme-functions.php:1205-1220).
 */
require_once __DIR__ . '/_tmpl.php';
$a = be_args( $args );

$type      = isset( $a['type'] ) ? be_tmpl_normalize_type( trim( $a['type'] ) ) : '';
$only_used = be_flag( $a, 'used' );
$do_refs   = ! isset( $a['refs'] ) || be_flag( $a, 'refs' );

global $wpdb;

// --- options mfn_* (una sola consulta) ------------------------------------
$mfn_options = [];
if ( $do_refs ) {
	$rows = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'mfn\\_%'" );
	foreach ( (array) $rows as $r ) {
		if ( strpos( $r->option_name, 'mfn_fav_items_' ) === 0 || $r->option_name === 'mfn_fake_sale' ) {
			continue; // no referencian templates
		}
		$mfn_options[ $r->option_name ] = maybe_unserialize( $r->option_value );
	}
}

/** true si $needle (ID) aparece como valor escalar o como hoja de un array */
function be_tmpl_value_has( $value, int $needle ): bool {
	if ( is_array( $value ) ) {
		foreach ( $value as $v ) {
			if ( be_tmpl_value_has( $v, $needle ) ) {
				return true;
			}
		}
		return false;
	}
	return is_scalar( $value ) && is_numeric( $value ) && (int) $value === $needle;
}

// --- postmeta / termmeta que apuntan a templates (una consulta cada una) ---
$postmeta_refs = [];
$termmeta_refs = [];
if ( $do_refs ) {
	$pm = $wpdb->get_results(
		"SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta}
		 WHERE meta_value REGEXP '^[0-9]+$' AND meta_value <> '0' AND (
		   meta_key LIKE 'mfn\\_%\\_template' OR meta_key LIKE 'mfn\\_%\\_post' OR meta_key LIKE 'mfn\\_%\\_post\\_excluded'
		   OR meta_key IN ('mfn_popup_included','mfn_template_id','mfn_dedicated_header_template','mfn_single_product_template') )"
	);
	foreach ( (array) $pm as $r ) {
		$postmeta_refs[ (int) $r->meta_value ][ $r->meta_key ][] = (int) $r->post_id;
	}
	$tm = $wpdb->get_results(
		"SELECT term_id, meta_key, meta_value FROM {$wpdb->termmeta}
		 WHERE meta_key LIKE 'mfn\\_%\\_term%' AND meta_value REGEXP '^[0-9]+$'"
	);
	foreach ( (array) $tm as $r ) {
		$termmeta_refs[ (int) $r->meta_value ][ $r->meta_key ][] = (int) $r->term_id;
	}
}

// --- recorrido ----------------------------------------------------------------
$out = [];
foreach ( be_tmpl_all( $type ) as $t ) {
	$id   = (int) $t->ID;
	$item = be_tmpl_get( $id );
	$item['publication_options'] = ( $po = get_post_meta( $id, 'mfn_publication_options', true ) ) ? json_decode( $po, true ) : null;

	if ( $do_refs ) {
		$opt_refs = [];
		foreach ( $mfn_options as $name => $value ) {
			if ( be_tmpl_value_has( $value, $id ) ) {
				$opt_refs[] = $name;
			}
		}
		$item['refs'] = [
			'options'  => $opt_refs,
			'postmeta' => $postmeta_refs[ $id ] ?? [],
			'termmeta' => $termmeta_refs[ $id ] ?? [],
		];
		$always_visible = $item['type'] === 'sidemenu' && get_post_meta( $id, 'mfn_sidemenu_visibility', true ) === 'always-visible';
		if ( $always_visible ) {
			$item['refs']['sidemenu_visibility'] = 'always-visible';
		}
		$item['used'] = $item['status'] === 'publish'
			&& ( $opt_refs || $item['refs']['postmeta'] || $item['refs']['termmeta'] || $always_visible );
		if ( $only_used && ! $item['used'] ) {
			continue;
		}
	}
	$out[] = $item;
}

if ( be_flag( $a, 'options' ) ) {
	be_out( [ 'templates' => $out, 'mfn_options' => $mfn_options ] );
} else {
	be_out( $out );
}
