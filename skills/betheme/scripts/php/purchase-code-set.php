<?php
/**
 * purchase-code-set.php — registro del tema (purchase code de Envato) y estado de updates.
 * Replica Mfn_Setup::register()/deregister() (functions/admin/setup/class-mfn-setup.php:514-604):
 * site option `envato_purchase_code_7758048` + purga de site transients. Sin remote=1 NO se llama
 * a api.muffingroup.com/register.php, así que Muffin no sabe de la activación y el Dashboard, al
 * refrescar `betheme_expires` (class-mfn-dashboard.php:192-232), BORRARÁ el code si la API lo rechaza.
 * SAFETY: read-only con status=1; reversible con code=/remove=1 (se imprime el code anterior oculto;
 *         guárdalo antes con show-previous=1 si quieres poder volver)
 *
 * Uso: wp eval-file purchase-code-set.php status=1 [no-remote=1]
 *      wp eval-file purchase-code-set.php code=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx [remote=1] [dry-run=1]
 *      wp eval-file purchase-code-set.php remove=1 yes=1 [remote=1]
 *      ... show-previous=1   imprime el code anterior completo (por stderr)
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

define( 'BE_CODE_OPTION', 'envato_purchase_code_7758048' );
define( 'BE_REGISTER_URL', 'https://api.muffingroup.com/register.php' ); // Mfn_API::$url['register'] class-mfn-api.php:14

/** Transients que borran register()/deregister() (class-mfn-setup.php:552-555) y refresh_update_version() (class-mfn-api.php:153-156) */
function be_code_transients(): array {
	return [ 'betheme_update', 'betheme_expires', 'betheme_plugins', 'betheme_update_plugins', 'update_themes' ];
}

/** Mfn_Setup::validate() (class-mfn-setup.php:494-506): 8-4-4-4-12 alfanumérico */
function be_code_validate( string $code ) {
	$code = trim( $code );
	if ( ! $code || ! preg_match( '/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/', $code ) ) {
		return false;
	}
	return $code;
}

/** POST a register.php como Mfn_Setup::register()/deregister() (l.520-530 / 576-586). Devuelve array|WP_Error */
function be_code_remote( string $code, string $action ) {
	$response = wp_remote_post( BE_REGISTER_URL, [
		'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . network_site_url(),
		'timeout'    => 30,
		'body'       => [ 'code' => urlencode( $code ), $action => 1 ],
	] );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( isset( $data['error'] ) ) {
		return new WP_Error( 'invalid_response', $data['error'] ); // Mfn_API::remote_post() l.69-73
	}
	return is_array( $data ) ? $data : [];
}

function be_code_purge_transients(): array {
	foreach ( be_code_transients() as $t ) {
		delete_site_transient( $t );
	}
	return be_code_transients();
}

// ---------------------------------------------------------------------------
// status=1
// ---------------------------------------------------------------------------
if ( be_flag( $a, 'status' ) ) {
	$installed = wp_get_theme( get_template() )->get( 'Version' );
	$available = get_site_transient( 'betheme_update' );
	$source    = 'site transient betheme_update';

	if ( ! $available && ! be_flag( $a, 'no-remote' ) ) {
		// Mfn_API::get_update_version() (class-mfn-api.php:125-137): lee el transient y, si falta,
		// consulta api.muffingroup.com/theme/version.php (sin purchase code) y lo cachea 1 h.
		$api_file = get_template_directory() . '/functions/admin/class-mfn-api.php';
		if ( ! class_exists( 'Mfn_API' ) && file_exists( $api_file ) ) {
			require_once $api_file;
		}
		if ( class_exists( 'Mfn_API' ) ) {
			$available = ( new Mfn_API() )->version;
			$source    = 'Mfn_API::get_update_version() (remoto)';
		}
	}

	$update_transient = get_site_transient( 'update_themes' );
	be_out( [
		'registered'          => (bool) mfn_is_registered(),
		'code_length'         => (int) mfn_is_registered(), // el Dashboard avisa de "corrupted" si != 36 (class-mfn-dashboard.php:598)
		'code_hidden'         => mfn_get_purchase_code_hidden() ?: null,
		'expires_transient'   => get_site_transient( 'betheme_expires' ) ?: null,
		'version_installed'   => $installed,
		'version_available'   => $available ?: null,
		'version_source'      => $source,
		'update_available'    => $available && (string) $available !== '-1' ? version_compare( $installed, (string) $available, '<' ) : null, // -1 = API sin respuesta (class-mfn-api.php:149)
		'wp_update_transient' => [
			'has_betheme'  => isset( $update_transient->response['betheme'] ),
			'last_checked' => isset( $update_transient->last_checked ) ? date( 'Y-m-d H:i:s', $update_transient->last_checked ) : null,
			'note'         => 'En CLI class-mfn-update.php no se carga (functions.php:217-243): sin él, wp theme update no ve Betheme',
		],
		'updates_history'     => array_map( function ( $u ) {
			return [ 'version' => $u['version'] ?? null, 'date' => isset( $u['time'] ) ? date( 'Y-m-d H:i:s', $u['time'] ) : null ];
		}, (array) get_site_option( 'betheme_updates_history' ) ),
		'transients'          => array_combine( be_code_transients(), array_map( function ( $t ) {
			$v = get_site_transient( $t );
			return $v === false ? null : ( is_scalar( $v ) ? $v : gettype( $v ) );
		}, be_code_transients() ) ),
	] );
	return;
}

// ---------------------------------------------------------------------------
// remove=1 yes=1
// ---------------------------------------------------------------------------
$previous = (string) mfn_get_purchase_code();
if ( be_flag( $a, 'show-previous' ) && $previous ) {
	be_msg( 'code anterior: ' . $previous );
}
$dry = be_flag( $a, 'dry-run' );

if ( be_flag( $a, 'remove' ) ) {
	if ( ! $previous ) {
		be_err( 'El tema no está registrado: nada que quitar.' );
	}
	if ( $dry ) {
		be_out( [ 'dry_run' => true, 'would_remove' => mfn_get_purchase_code_hidden(), 'remote' => be_flag( $a, 'remote' ) ] );
		return;
	}
	be_require_yes( $a, 'Desregistrar el tema (borra el purchase code)' );
	$remote = null;
	if ( be_flag( $a, 'remote' ) ) {
		$r      = be_code_remote( $previous, 'deregister' ); // deregister() ignora la respuesta (l.586)
		$remote = is_wp_error( $r ) ? 'error: ' . $r->get_error_message() : 'ok';
	}
	update_site_option( BE_CODE_OPTION, '' ); // class-mfn-setup.php:590
	be_out( [
		'removed'          => true,
		'previous_hidden'  => substr( $previous, 0, 13 ) . '-****-****-************',
		'remote'           => $remote,
		'transients_purged'=> be_code_purge_transients(),
		'revert'           => 'wp eval-file purchase-code-set.php code=<code anterior>',
	] );
	return;
}

// ---------------------------------------------------------------------------
// code=...
// ---------------------------------------------------------------------------
if ( empty( $a['code'] ) ) {
	be_err( 'Indica status=1, code=<purchase code> o remove=1 yes=1' );
}
$code = be_code_validate( $a['code'] );
if ( ! $code ) {
	be_err( 'Formato inválido: se espera 8-4-4-4-12 caracteres alfanuméricos (Mfn_Setup::validate, class-mfn-setup.php:502)' );
}
if ( $code === $previous ) {
	be_msg( 'El code ya está registrado; solo se purgan los transients.' );
}

if ( $dry ) {
	be_out( [
		'dry_run'         => true,
		'would_set'       => substr( $code, 0, 13 ) . '-****-****-************',
		'previous_hidden' => $previous ? substr( $previous, 0, 13 ) . '-****-****-************' : null,
		'remote'          => be_flag( $a, 'remote' ),
		'transients'      => be_code_transients(),
	] );
	return;
}

$remote  = null;
$expires = null;
if ( be_flag( $a, 'remote' ) ) {
	// Mfn_Setup::register() l.530-548: solo guarda si la API devuelve success; success = fecha de expiración
	$r = be_code_remote( $code, 'register' );
	if ( is_wp_error( $r ) ) {
		be_err( 'La API de Muffin no acepta el code: ' . $r->get_error_message() );
	}
	if ( empty( $r['success'] ) ) {
		be_err( 'La API de Muffin no acepta el code (respuesta sin success): ' . be_json( $r, false ) );
	}
	$remote  = 'ok';
	$expires = $r['success'];
} else {
	be_msg( 'aviso: sin remote=1 no se avisa a api.muffingroup.com; si el code no es válido allí, el Dashboard lo borrará al refrescar betheme_expires (class-mfn-dashboard.php:209-213).' );
}

update_site_option( BE_CODE_OPTION, $code ); // class-mfn-setup.php:547
$purged = be_code_purge_transients();
if ( $expires ) {
	set_site_transient( 'betheme_expires', $expires, WEEK_IN_SECONDS ); // l.548
}

be_out( [
	'registered'        => (bool) mfn_is_registered(),
	'code_hidden'       => mfn_get_purchase_code_hidden(),
	'previous_hidden'   => $previous ? substr( $previous, 0, 13 ) . '-****-****-************' : null,
	'remote'            => $remote,
	'expires'           => $expires,
	'transients_purged' => $purged,
	'revert'            => $previous ? 'wp eval-file purchase-code-set.php code=<code anterior>' : 'wp eval-file purchase-code-set.php remove=1 yes=1',
] );
