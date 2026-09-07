<?php
/**
 * plugins-required.php — lista los plugins que Betheme ofrece en Betheme → Plugins (TGMPA:
 * functions/admin/tgm/class-mfn-tgmpa.php:15-58 + premium desde api.muffingroup.com l.145-176)
 * con su estado, y opcionalmente instala/activa los que falten con `wp plugin install`.
 * SAFETY: read-only por defecto; install=1 es reversible (wp plugin deactivate/uninstall <slug>)
 *
 * Uso: wp eval-file plugins-required.php                       (listado)
 *      wp eval-file plugins-required.php install=1 dry-run=1   (imprime los comandos)
 *      wp eval-file plugins-required.php install=1 only=contact-form-7,duplicate-post
 *      ... activate=0      instala sin activar (el panel activa siempre: is_automatic=true, l.94)
 *      ... update=1        también actualiza los que tengan versión nueva en la lista de Muffin
 *      ... no-remote=1     no consulta la API (solo lista estática + transient betheme_plugins)
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/** Lista estática de class-mfn-tgmpa.php:15-58, por si el fichero no se puede cargar */
function be_tgmpa_static_list(): array {
	return [
		[ 'name' => 'Contact Form 7', 'slug' => 'contact-form-7', 'required' => false ],
		[ 'name' => 'Duplicate Post', 'slug' => 'duplicate-post', 'required' => false ],
		[ 'name' => 'Elementor', 'slug' => 'elementor', 'required' => false ],
		[ 'name' => 'HubSpot', 'slug' => 'leadin', 'required' => false ],
		[ 'name' => 'WooCommerce', 'slug' => 'woocommerce', 'required' => false ],
	];
}

/**
 * Lista completa = estática + premium (transient betheme_plugins o API), como Mfn_TGMPA::get_plugins_list()
 * (l.127-142). En CLI la clase no está cargada (functions.php:217-243): se carga a mano en el ámbito
 * del script (class-mfn-tgmpa.php crea $Mfn_TGMPA al final, l.181; solo define la clase si ningún
 * plugin cargó ya TGM_Plugin_Activation, l.6).
 */
$be_tgmpa_obj = null;
if ( ! be_flag( $a, 'no-remote' ) ) {
	$be_dir = get_template_directory();
	foreach ( [ '/functions/admin/class-mfn-api.php', '/functions/admin/tgm/class-mfn-tgmpa.php' ] as $be_f ) {
		if ( file_exists( $be_dir . $be_f ) ) {
			require_once $be_dir . $be_f;
		}
	}
	if ( class_exists( 'Mfn_TGMPA' ) ) {
		$be_tgmpa_obj = ( isset( $Mfn_TGMPA ) && $Mfn_TGMPA instanceof Mfn_TGMPA ) ? $Mfn_TGMPA : new Mfn_TGMPA();
	} else {
		be_msg( 'aviso: Mfn_TGMPA no disponible (¿otro plugin ya carga TGMPA?); se usa la lista estática + transient betheme_plugins' );
	}
}

function be_tgmpa_list( $obj ): array {
	if ( $obj instanceof Mfn_TGMPA && is_array( $obj->plugins ) ) {
		return $obj->plugins;
	}
	$premium = get_site_transient( 'betheme_plugins' );
	return array_merge( be_tgmpa_static_list(), is_array( $premium ) ? $premium : [] );
}

/** TGM_Plugin_Activation::_get_plugin_basename_from_slug() (class-tgm-plugin-activation.php:1551-1561) */
function be_plugin_basename( string $slug, array $installed ): ?string {
	foreach ( array_keys( $installed ) as $key ) {
		if ( preg_match( '|^' . preg_quote( $slug, '|' ) . '/|', $key ) ) {
			return $key;
		}
	}
	return null;
}

$installed  = get_plugins();
$registered = (bool) mfn_is_registered();
$only       = ! empty( $a['only'] ) ? array_filter( array_map( 'trim', explode( ',', $a['only'] ) ) ) : [];
$rows       = [];

foreach ( be_tgmpa_list( $be_tgmpa_obj ) as $p ) {
	$slug = $p['slug'] ?? '';
	if ( ! $slug || ( $only && ! in_array( $slug, $only, true ) ) ) {
		continue;
	}
	$path    = be_plugin_basename( $slug, $installed );
	$source  = $p['source'] ?? 'repo';
	$premium = $source !== 'repo' && $source !== '';
	$v_inst  = $path && ! empty( $installed[ $path ]['Version'] ) ? $installed[ $path ]['Version'] : null;
	$v_avail = ! empty( $p['version'] ) ? (string) $p['version'] : null;

	// misma lógica que Mfn_Plugins::template() (class-mfn-plugins.php:119-146)
	if ( $path && is_plugin_active( $path ) ) {
		$action = ( $v_avail && $v_inst && version_compare( $v_avail, $v_inst, '>' ) ) ? 'update' : 'ok';
	} elseif ( $path ) {
		$action = 'activate';
	} else {
		$action = 'install';
	}

	$rows[ $slug ] = [
		'name'              => str_replace( 'DEPRECATED', '', $p['name'] ?? $slug ),
		'slug'              => $slug,
		'required'          => ! empty( $p['required'] ),
		'premium'           => $premium,
		'path'              => $path,
		'installed'         => (bool) $path,
		'active'            => $path ? is_plugin_active( $path ) : false,
		'version_installed' => $v_inst,
		'version_available' => $v_avail,
		'action'            => $action,
		'source'            => $premium ? preg_replace( '/code=[^&]+/', 'code=***', $source ) : 'wordpress.org',
	];
	if ( $premium ) {
		$rows[ $slug ]['_source_full'] = $source;
	}
}

if ( ! be_flag( $a, 'install' ) ) {
	foreach ( $rows as &$r ) {
		unset( $r['_source_full'] );
	}
	unset( $r );
	be_out( [ 'registered' => $registered, 'plugins' => array_values( $rows ) ] );
	return;
}

// ---------------------------------------------------------------------------
// install=1
// ---------------------------------------------------------------------------
$activate = ! ( isset( $a['activate'] ) && ! be_flag( $a, 'activate' ) );
$dry      = be_flag( $a, 'dry-run' );
$commands = [];
$skipped  = [];

foreach ( $rows as $slug => $r ) {
	if ( $r['action'] === 'install' ) {
		if ( $r['premium'] ) {
			if ( ! $registered ) {
				$skipped[] = "$slug: premium, el tema no está registrado (la URL de descarga lleva el purchase code)";
				continue;
			}
			// TGMPA instala los premium desde su 'source' (URL de api.muffingroup.com/plugins/download.php?code=&plugin=)
			$commands[] = 'plugin install ' . escapeshellarg( $r['_source_full'] ) . ( $activate ? ' --activate' : '' );
		} else {
			$commands[] = 'plugin install ' . escapeshellarg( $slug ) . ( $activate ? ' --activate' : '' );
		}
	} elseif ( $r['action'] === 'activate' && $activate ) {
		$commands[] = 'plugin activate ' . escapeshellarg( $slug );
	} elseif ( $r['action'] === 'update' && be_flag( $a, 'update' ) ) {
		$commands[] = $r['premium']
			? 'plugin install ' . escapeshellarg( $r['_source_full'] ) . ' --force'
			: 'plugin update ' . escapeshellarg( $slug );
	}
}

foreach ( $skipped as $s ) {
	be_msg( 'omitido: ' . $s );
}

if ( ! $commands ) {
	be_out( [ 'dry_run' => $dry, 'commands' => [], 'note' => 'Nada que instalar/activar.' ] );
	return;
}

$printable = array_map( function ( $c ) {
	return 'wp ' . preg_replace( '/code=[^&\']+/', 'code=***', $c );
}, $commands );

if ( $dry || ! class_exists( 'WP_CLI' ) ) {
	be_out( [ 'dry_run' => true, 'commands' => $printable, 'note' => $dry ? 'dry-run: nada ejecutado' : 'Sin WP_CLI: ejecuta estos comandos a mano (los premium necesitan la URL completa con el code)' ] );
	return;
}

$results = [];
foreach ( $commands as $i => $cmd ) {
	be_msg( 'ejecutando: ' . $printable[ $i ] );
	$r = WP_CLI::runcommand( $cmd, [ 'return' => 'all', 'exit_error' => false, 'launch' => true ] );
	$results[] = [
		'command' => $printable[ $i ],
		'exit'    => $r->return_code,
		'stdout'  => trim( (string) $r->stdout ),
		'stderr'  => trim( (string) $r->stderr ),
	];
}
be_out( [ 'dry_run' => false, 'results' => $results ] );
