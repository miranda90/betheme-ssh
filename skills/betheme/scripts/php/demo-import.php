<?php
/**
 * demo-import.php — importa una "pre-built website" de Betheme encadenando los métodos públicos de
 * Mfn_Importer_Helper en el mismo orden que el Setup Wizard, sin AJAX ni nonce.
 * SAFETY: destructive (con reset=1 TRUNCA posts/postmeta/terms/comments…; sin reset añade contenido y SOBRESCRIBE
 *         Theme Options, menús, widgets y páginas de inicio). Exige yes=1 y really=1. Hace `wp db export` antes.
 *
 * Uso: wp eval-file demo-import.php list=1                     (catálogo local: RO)
 *      wp eval-file demo-import.php list=1 filter=shop          (filtra por id/nombre/categoría/layout)
 *      wp eval-file demo-import.php demo=doctor2 dry-run=1
 *      wp --user=<admin> eval-file demo-import.php demo=doctor2 reset=1 media=1 yes=1 really=1
 *      ... builder=elementor        (paquete <demo>_el)
 *      ... complete=0               (no toca menús/widgets/páginas de inicio: solo contenido + Theme Options)
 *      ... media=0                  (no importa adjuntos; con reset=1 tampoco borra los existentes)
 *      ... skip=slider,css          (salta pasos: reset,download,menus,content,options,menu,widgets,slider,pages,css,cleanup)
 *      ... blogname="Mi web" blogdescription="..."
 *      ... no-backup=1              (sin wp db export previo; NO recomendado)
 *
 * Orden replicado del wizard (functions/admin/setup/assets/setup.js: reset → download → content → options → slider → settings;
 * handlers en functions/admin/setup/class-mfn-setup.php: _database_reset l.607, _download_package l.682, _content l.710,
 * _options l.748, _slider l.803, _settings l.831):
 *   1. Mfn_Importer_Helper::database_reset($media)      class-mfn-importer-helper.php:64-146   [reset=1]
 *   2. ->download_package()                              l.153  (API https://api.muffingroup.com/websites/download.php, purchase code)
 *   3. ->remove_menus()                                  l.1319 [complete=1]
 *   4. ->content($media)                                 l.202  (custom_import + replace_builder)
 *   5. ->options()                                       l.570  (update_option('betheme') SIN validar + conditions + be_classes)
 *   6. ->menu() / ->widgets()                            l.979 / l.1015 [complete=1]
 *   7. ->slider()                                        l.1038 (solo demos con RevSlider)
 *   8. blogname/blogdescription                          class-mfn-setup.php:860-869
 *   9. ->set_pages() / ->regenerate_CSS()                l.1114 / l.1193 [complete=1]
 *  10. ->delete_temp_dir() + flush_rewrite_rules(false)  l.183 / class-mfn-setup.php:922-929
 * No instala plugins (paso "plugins" del wizard): usa `wp plugin install --activate` con los slugs que lista `list=1`.
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

// --- catálogo local (functions/importer/demos.php) --------------------------
$demos_file = get_template_directory() . '/functions/importer/demos.php';
if ( ! file_exists( $demos_file ) ) {
	be_err( "No existe $demos_file" );
}
$categories = $layouts = $plugins = $demos = [];
require $demos_file; // define $categories, $layouts, $plugins, $demos

if ( be_flag( $a, 'list' ) ) {
	$filter = strtolower( trim( $a['filter'] ?? '' ) );
	$out    = [];
	foreach ( $demos as $key => $d ) {
		$row = [
			'id'            => $key,
			'name'          => $d['name'] ?? '',
			'url'           => $d['url'] ?? '',
			'layouts'       => array_map( static fn( $l ) => $layouts[ $l ] ?? $l, (array) ( $d['layouts'] ?? [] ) ),
			'categories'    => array_map( static fn( $c ) => $categories[ $c ] ?? $c, (array) ( $d['categories'] ?? [] ) ),
			'plugins'       => array_map( static fn( $p ) => $plugins[ $p ]['slug'] ?? $p, (array) ( $d['plugins'] ?? [] ) ),
			'theme_version' => $d['theme_version'] ?? null,
			'elementor'     => in_array( 'ele', (array) ( $d['layouts'] ?? [] ), true ),
		];
		if ( $filter !== '' && stripos( json_encode( $row ), $filter ) === false ) {
			continue;
		}
		$out[] = $row;
	}
	be_out( [ 'count' => count( $out ), 'demos' => $out ] );
	return;
}

// --- parámetros ---------------------------------------------------------------
$demo = trim( $a['demo'] ?? '' );
if ( $demo === '' ) {
	be_err( 'Indica demo=<id> (ver list=1).' );
}
if ( ! isset( $demos[ $demo ] ) ) {
	be_err( "La demo '$demo' no está en functions/importer/demos.php (ver list=1)." );
}
$builder  = ( $a['builder'] ?? '' ) === 'elementor' ? 'elementor' : false;
$reset    = be_flag( $a, 'reset' );
$media    = be_flag( $a, 'media' );
$complete = ! isset( $a['complete'] ) || be_flag( $a, 'complete' );
$skip     = array_filter( array_map( 'trim', explode( ',', $a['skip'] ?? '' ) ) );
$dry      = be_flag( $a, 'dry-run' );
$steps    = [ 'reset', 'download', 'menus', 'content', 'options', 'menu', 'widgets', 'slider', 'pages', 'css', 'cleanup' ];
$run      = static function ( string $s ) use ( $skip, $reset, $complete ): bool {
	if ( in_array( $s, $skip, true ) ) {
		return false;
	}
	if ( $s === 'reset' && ! $reset ) {
		return false;
	}
	if ( in_array( $s, [ 'menus', 'menu', 'widgets', 'pages', 'css' ], true ) && ! $complete ) {
		return false;
	}
	return true;
};

be_load_admin_classes(); // Mfn_Helper + Mfn_Importer_Helper (functions.php:221,227: solo se cargan en admin)
$dir = get_template_directory();
if ( ! class_exists( 'Mfn_API' ) && file_exists( $dir . '/functions/admin/class-mfn-api.php' ) ) {
	require_once $dir . '/functions/admin/class-mfn-api.php';           // padre de Mfn_Importer_API (functions.php:97-98)
}
if ( ! class_exists( 'MfnLocalCssCompability' ) && file_exists( $dir . '/visual-builder/classes/helpers/local-css-compability.php' ) ) {
	require_once $dir . '/visual-builder/classes/helpers/local-css-compability.php'; // lo usa Mfn_Helper::bebuilder_data_updater()
}

$importer  = new Mfn_Importer_Helper( $demo, $builder );
$demo_path = $importer->demo_path;
$package   = file_exists( $demo_path . '/content.xml.gz' );
$code      = function_exists( 'mfn_get_purchase_code' ) ? (string) mfn_get_purchase_code() : '';
$disable   = (array) mfn_opts_get( 'theme-disable' );

$plan = [
	'demo'         => $demo,
	'name'         => $demos[ $demo ]['name'] ?? '',
	'builder'      => $builder ?: 'bebuilder',
	'package_dir'  => $demo_path,
	'package_present' => $package,
	'purchase_code'   => $code !== '',
	'plugins_required'=> array_map( static fn( $p ) => $plugins[ $p ]['slug'] ?? $p, (array) ( $demos[ $demo ]['plugins'] ?? [] ) ),
	'reset'        => $reset,
	'media'        => $media,
	'complete'     => $complete,
	'steps'        => array_values( array_filter( $steps, $run ) ),
	'bebuilder_access' => (bool) apply_filters( 'bebuilder_access', false ),
];
if ( $run( 'download' ) && $code === '' ) {
	be_msg( 'aviso: no hay purchase code (site option envato_purchase_code_7758048); la descarga fallará. Regístralo con be-purchase-code-set.' );
}
if ( isset( $disable['demo-data'] ) ) {
	be_msg( 'aviso: theme-disable[demo-data] está activo (oculta el wizard en admin; el importador funciona igual).' );
}
foreach ( $plan['plugins_required'] as $slug ) {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$active = false;
	foreach ( (array) get_option( 'active_plugins', [] ) as $p ) {
		if ( strpos( $p, $slug . '/' ) === 0 ) {
			$active = true;
		}
	}
	if ( ! $active ) {
		be_msg( "aviso: el plugin requerido '$slug' no está activo (wp plugin install $slug --activate)." );
	}
}

if ( $dry ) {
	$plan['dry_run'] = true;
	be_out( $plan );
	return;
}

// --- doble confirmación ---------------------------------------------------------
be_require_yes( $a, 'Importar la demo ' . $demo . ( $reset ? ' con RESET de la base de datos' : '' ) );
if ( ! be_flag( $a, 'really' ) ) {
	be_err( 'Segunda confirmación: añade really=1. ' . ( $reset ? 'reset=1 TRUNCA posts, postmeta, terms, comments, links (y tablas RevSlider/Woo).' : 'Sobrescribirá Theme Options, menús, widgets y páginas de inicio.' ), 3 );
}

// --- backup wp db export --------------------------------------------------------
$backup = null;
if ( ! be_flag( $a, 'no-backup' ) ) {
	if ( ! class_exists( 'WP_CLI' ) ) {
		be_err( 'Sin WP_CLI no puedo hacer `wp db export`; haz el backup a mano y pasa no-backup=1.' );
	}
	$bdir = be_uploads_betheme( 'backups' )['path'];
	if ( ! file_exists( $bdir ) ) {
		wp_mkdir_p( $bdir );
	}
	$backup = $bdir . '/pre-demo-' . sanitize_key( $demo ) . '-' . gmdate( 'Ymd-His' ) . '.sql';
	$r = WP_CLI::runcommand( 'db export ' . escapeshellarg( $backup ), [ 'launch' => true, 'exit_error' => false, 'return' => 'all' ] );
	if ( ! is_object( $r ) || (int) $r->return_code !== 0 || ! file_exists( $backup ) ) {
		be_err( 'wp db export falló: ' . ( is_object( $r ) ? trim( $r->stderr . ' ' . $r->stdout ) : '?' ) );
	}
	be_msg( "Backup SQL: $backup (" . size_format( filesize( $backup ) ) . ')' );
	be_msg( "Restaurar: wp db import $backup" );
}

// --- ejecución ------------------------------------------------------------------
@set_time_limit( 0 );
$log    = [];
$result = [];
$step   = static function ( string $name, callable $fn ) use ( &$log, &$result ): void {
	be_msg( "→ $name" );
	ob_start();
	try {
		$ret = $fn();
	} catch ( Throwable $e ) {
		$out = ob_get_clean();
		$log[ $name ] = trim( wp_strip_all_tags( str_replace( '<br />', "\n", $out ) ) );
		$result[ $name ] = 'ERROR: ' . $e->getMessage();
		be_err( "Paso '$name' falló: " . $e->getMessage() . ( $out ? "\n" . $out : '' ) );
	}
	$out            = ob_get_clean();
	$log[ $name ]   = trim( wp_strip_all_tags( str_replace( '<br />', "\n", $out ) ) );
	$result[ $name ] = $ret;
};

if ( $run( 'reset' ) ) {
	$step( 'reset', static fn() => Mfn_Importer_Helper::database_reset( $media ) );
}
if ( $run( 'download' ) ) {
	$step( 'download', static function () use ( $importer ) {
		$ok = $importer->download_package();
		if ( ! $ok ) {
			throw new RuntimeException( 'download_package() devolvió false (API/purchase code/permisos de uploads/betheme/websites).' );
		}
		return true;
	} );
} elseif ( ! $package ) {
	be_err( "No hay paquete en $demo_path y se ha saltado 'download'." );
}
if ( $run( 'menus' ) ) {
	$step( 'menus', static fn() => $importer->remove_menus() );
}
if ( $run( 'content' ) ) {
	$step( 'content', static fn() => $importer->content( $media ) );
}
if ( $run( 'options' ) ) {
	$step( 'options', static fn() => $importer->options() );
}
if ( $run( 'menu' ) ) {
	$step( 'menu', static fn() => $importer->menu() );
}
if ( $run( 'widgets' ) ) {
	$step( 'widgets', static fn() => $importer->widgets() );
}
if ( $run( 'slider' ) ) {
	$step( 'slider', static fn() => $importer->slider( $media ) );
}
if ( ! empty( $a['blogname'] ) || ! empty( $a['blogdescription'] ) ) {
	$step( 'blogname', static function () use ( $a ) {
		global $wpdb; // el wizard escribe con $wpdb->update por un hook de Elementor (class-mfn-setup.php:860-869)
		foreach ( [ 'blogname', 'blogdescription' ] as $k ) {
			if ( ! empty( $a[ $k ] ) ) {
				$wpdb->update( $wpdb->options, [ 'option_value' => sanitize_option( $k, $a[ $k ] ) ], [ 'option_name' => $k ] );
			}
		}
		wp_cache_delete( 'alloptions', 'options' );
		return true;
	} );
}
if ( $run( 'pages' ) ) {
	$step( 'pages', static fn() => $importer->set_pages() );
}
if ( $run( 'css' ) ) {
	$step( 'css', static fn() => $importer->regenerate_CSS() );
}
if ( $run( 'cleanup' ) ) {
	$step( 'cleanup', static function () use ( $importer ) {
		// delete_temp_dir() llama a Mfn_Helper::generate_bebuilder_items(), que con bebuilder_access=true (--user admin)
		// necesita MfnVisualBuilder (solo cargado en admin): lo desactivamos y aplicamos el reset ligero de _lib.
		$guard = ! class_exists( 'MfnVisualBuilder' );
		if ( $guard ) {
			add_filter( 'bebuilder_access', '__return_false', 999 );
		}
		$ok = $importer->delete_temp_dir();
		if ( $guard ) {
			remove_filter( 'bebuilder_access', '__return_false', 999 );
			be_bebuilder_reset();
		}
		flush_rewrite_rules( false );
		return $ok;
	} );
}

// static.css y cachés (el importador escribe `betheme` con update_option a pelo: class-mfn-importer-helper.php:620)
$post = be_post_save( true, false );

be_out( [
	'demo'    => $demo,
	'backup'  => $backup,
	'steps'   => $result,
	'log'     => array_filter( $log ),
	'post_save' => $post,
	'next'    => 'Revisa el front, wp option get page_on_front, y si hace falta be-template-list.sh',
] );
