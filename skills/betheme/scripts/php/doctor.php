<?php
/**
 * doctor.php — diagnóstico de solo lectura del estado de Betheme/Témini en el sitio.
 * SAFETY: read-only
 *
 * Uso: wp eval-file doctor.php
 *
 * Comprueba lo que condiciona el resto de scripts: versiones, tema activo, opciones que
 * disparan procesos (static-css, hold-cache, builder-storage, google-font-mode), directorios
 * de uploads/betheme, frescura de static.css, registro del purchase code, revisiones,
 * packs de iconos, plantillas del builder, multilenguaje.
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

$theme  = wp_get_theme();
$parent = $theme->parent();
$opts   = be_opts();

$r = [
	'site'      => home_url(),
	'wp'        => get_bloginfo( 'version' ),
	'php'       => PHP_VERSION,
	'wp_cli'    => defined( 'WP_CLI_VERSION' ) ? WP_CLI_VERSION : null,
	'theme'     => [
		'active'   => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) . ' (' . $theme->get_stylesheet() . ')',
		'parent'   => $parent ? $parent->get( 'Name' ) . ' ' . $parent->get( 'Version' ) : null,
		'betheme'  => be_theme_version(),
		'is_betheme_child' => $parent ? $parent->get_template() === 'betheme' : $theme->get_template() === 'betheme',
	],
	'options'   => [
		'betheme_keys'     => count( $opts ),
		'catalog_fields'   => count( be_fields() ),
		'static-css'       => mfn_opts_get( 'static-css' ),
		'hold-cache'       => mfn_opts_get( 'hold-cache' ),
		'builder-storage'  => mfn_opts_get( 'builder-storage' ),
		'builder-visibility' => mfn_opts_get( 'builder-visibility' ),
		'google-font-mode' => mfn_opts_get( 'google-font-mode' ),
		'minify-css'       => mfn_opts_get( 'minify-css' ),
		'css-location'     => mfn_opts_get( 'css-location' ),
		'post-type-disable'=> mfn_opts_get( 'post-type-disable' ),
		'theme-disable'    => mfn_opts_get( 'theme-disable' ),
		'translate'        => mfn_opts_get( 'translate' ),
		'last_tab'         => $opts['last_tab'] ?? null,
	],
];

// uploads/betheme
$dirs = [];
foreach ( [ '', 'css', 'fonts', 'icons', 'websites' ] as $sub ) {
	$p = be_uploads_betheme( $sub )['path'];
	$dirs[ $sub ?: '.' ] = [ 'path' => $p, 'exists' => is_dir( $p ), 'writable' => is_dir( $p ) && is_writable( $p ) ];
}
$r['uploads_betheme'] = $dirs;

// static.css
$path    = be_static_css_path();
$current = file_exists( $path ) ? file_get_contents( $path ) : '';
$fresh   = be_static_css_build();
$r['static_css'] = [
	'path'       => $path,
	'exists'     => $current !== '',
	'enabled'    => (bool) mfn_opts_get( 'static-css' ),
	'up_to_date' => $current === $fresh,
	'bytes'      => strlen( $current ),
	'mtime'      => file_exists( $path ) ? gmdate( 'c', filemtime( $path ) ) : null,
];

// .htaccess cache block
$ht = ABSPATH . '.htaccess';
$r['htaccess'] = [
	'exists'        => file_exists( $ht ),
	'betheme_block' => file_exists( $ht ) && strpos( file_get_contents( $ht ), '# BEGIN BETHEME' ) !== false,
];

// registro / updates
$r['registration'] = [
	'registered'      => function_exists( 'mfn_is_registered' ) ? (bool) mfn_is_registered() : null,
	'code_hidden'     => function_exists( 'mfn_get_purchase_code_hidden' ) ? mfn_get_purchase_code_hidden() : null,
	'updates_history' => get_site_option( 'betheme_updates_history' ),
	'automatic-updates' => mfn_opts_get( 'automatic-updates' ),
];

// revisiones
$rev = [];
foreach ( [ 'update', 'revision', 'backup' ] as $t ) {
	$v = get_option( 'betheme_revision_' . $t );
	$rev[ $t ] = is_array( $v ) ? count( $v ) : 0;
}
$r['revisions'] = $rev;

// builder
$r['bebuilder'] = [
	'form_uid'   => get_option( 'betheme_form_uid' ),
	'js_file'    => get_template_directory() . '/visual-builder/assets/js/forms/bebuilder-' . be_theme_version() . '.js',
	'js_exists'  => file_exists( get_template_directory() . '/visual-builder/assets/js/forms/bebuilder-' . be_theme_version() . '.js' ),
	'be_classes' => strlen( (string) get_option( 'be_classes' ) ),
	'be_css_variables' => strlen( (string) get_option( 'be_css_variables' ) ),
	'css_db_update'    => get_option( 'mfn-css-db-update' ),
];

// contenido
$r['content'] = [
	'icon_packs' => (int) ( wp_count_posts( 'icons' )->publish ?? 0 ),
	'templates'  => (int) ( wp_count_posts( 'template' )->publish ?? 0 ),
	'layouts'    => post_type_exists( 'layout' ) ? (int) ( wp_count_posts( 'layout' )->publish ?? 0 ) : 'cpt no registrado',
	'portfolio'  => post_type_exists( 'portfolio' ) ? (int) ( wp_count_posts( 'portfolio' )->publish ?? 0 ) : 'cpt no registrado',
];

// multilenguaje
$r['i18n'] = [
	'locale'  => get_locale(),
	'wpml'    => defined( 'ICL_SITEPRESS_VERSION' ) ? ICL_SITEPRESS_VERSION : false,
	'wpml_default_lang' => function_exists( 'apply_filters' ) && defined( 'ICL_SITEPRESS_VERSION' ) ? apply_filters( 'wpml_default_language', null ) : null,
	'polylang' => function_exists( 'pll_current_language' ),
];

// plugins relevantes
$r['plugins'] = [
	'acf'         => class_exists( 'ACF' ),
	'woocommerce' => class_exists( 'WooCommerce' ),
	'w3tc'        => function_exists( 'w3tc_flush_all' ),
	'cf7'         => class_exists( 'WPCF7' ),
	'revslider'   => class_exists( 'RevSlider' ),
];

// avisos
$warn = [];
if ( ! $r['theme']['is_betheme_child'] && $theme->get_template() !== 'betheme' ) {
	$warn[] = 'El tema activo no es Betheme ni un hijo de Betheme.';
}
if ( $r['static_css']['enabled'] && ! $r['static_css']['up_to_date'] ) {
	$warn[] = 'static-css activo y static.css DESACTUALIZADO: ejecuta be-static-css-regenerate.sh';
}
if ( ! $r['static_css']['enabled'] && $r['static_css']['exists'] && ! $r['static_css']['up_to_date'] ) {
	$warn[] = 'static.css existe pero está desactualizado (inofensivo mientras static-css siga desactivado).';
}
if ( mfn_opts_get( 'hold-cache' ) && ! $r['htaccess']['betheme_block'] ) {
	$warn[] = 'hold-cache activo pero .htaccess no tiene el bloque BETHEME: be-cache-htaccess.sh mode=on';
}
if ( ! mfn_opts_get( 'hold-cache' ) && $r['htaccess']['betheme_block'] ) {
	$warn[] = 'hold-cache desactivado pero .htaccess conserva el bloque BETHEME: be-cache-htaccess.sh mode=off';
}
foreach ( $dirs as $k => $d ) {
	if ( $k !== 'websites' && ( ! $d['exists'] || ! $d['writable'] ) ) {
		$warn[] = "uploads/betheme/$k no existe o no es escribible.";
	}
}
if ( $r['registration']['registered'] === false ) {
	$warn[] = 'Betheme no registrado: sin actualizaciones ni plugins premium (be-purchase-code-set.sh).';
}
$r['warnings'] = $warn;

be_out( $r );
