<?php
/**
 * favicon-set.php — escribe favicon-img y apple-touch-icon (Theme Options → Global → General)
 * replicando el "Save" del panel (revisión + static.css + reset JS BeBuilder).
 * SAFETY: reversible (el estado anterior queda en betheme_revision_backup; ver be-options-revisions)
 *
 * Cada icono se pasa como ruta local (se importa a la Media Library) o como URL ya subida (http...).
 * Valor guardado: `URL#attachment_id` (fields/upload/field_upload.js:31). El front solo usa la parte
 * anterior al '#' (functions/theme-head.php:239-253).
 *
 * Uso: wp eval-file favicon-set.php favicon=/tmp/favicon-32.png apple=/tmp/apple-touch-180.png
 *      wp eval-file favicon-set.php favicon=https://sitio.com/wp-content/uploads/favicon.ico
 *      wp eval-file favicon-set.php clear=1            (vacía ambos → manda el Site Icon de WP si existe)
 *      ... dry-run=1      muestra el diff sin escribir (las rutas locales NO se importan)
 *      ... no-static=1    no regenerar static.css
 */
require_once __DIR__ . '/_lib.php';
$a   = be_args( $args );
$dry = be_flag( $a, 'dry-run' );

$map = [ 'favicon' => 'favicon-img', 'apple' => 'apple-touch-icon' ];

/** Igual que be_logo_upload_arg() en logo-set.php (candidato a helper en _lib.php: be_upload_arg) */
function be_favicon_upload_arg( string $raw, bool $dry ): string {
	$raw = trim( $raw );
	if ( $raw === '' ) {
		return '';
	}
	if ( preg_match( '#^https?://#i', $raw ) ) {
		if ( strpos( $raw, '#' ) !== false ) {
			return $raw;
		}
		$id = attachment_url_to_postid( $raw );
		return $id ? $raw . '#' . $id : $raw;
	}
	if ( $dry ) {
		return '(import) ' . $raw;
	}
	$m = be_media_import( $raw );
	be_msg( "importado $raw → adjunto {$m['id']}" );
	return $m['value'];
}

$old = be_opts();
$new = $old;
$written = [];

if ( be_flag( $a, 'clear' ) ) {
	foreach ( $map as $id ) {
		$new[ $id ] = '';
		$written[ $id ] = '';
	}
}
foreach ( $map as $arg => $id ) {
	if ( array_key_exists( $arg, $a ) ) {
		$new[ $id ]     = be_favicon_upload_arg( (string) $a[ $arg ], $dry );
		$written[ $id ] = $new[ $id ];
	}
}
if ( ! $written ) {
	be_err( 'Nada que cambiar. Argumentos: favicon= apple= clear=1' );
}

// Avisos (theme-head.php:239: favicon-img vacío → manda has_site_icon(); si tampoco hay, images/favicon.ico del tema)
if ( empty( $new['favicon-img'] ) ) {
	be_msg( has_site_icon()
		? 'AVISO: favicon-img vacío: WordPress pintará el Site Icon (option site_icon = ' . get_option( 'site_icon' ) . ').'
		: 'AVISO: favicon-img vacío y sin Site Icon: se servirá betheme/images/favicon.ico.' );
} elseif ( has_site_icon() ) {
	be_msg( 'Nota: WordPress también imprime el Site Icon (wp_site_icon en wp_head); el navegador suele quedarse con el último <link rel=icon>.' );
}

$diff = be_opts_save( $new, [ 'dry' => $dry, 'static' => ! be_flag( $a, 'no-static' ) ] );
be_out( [ 'dry_run' => $dry, 'written' => $written, 'diff' => $diff ] );
