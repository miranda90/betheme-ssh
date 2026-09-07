<?php
/**
 * icons-pack-remove.php — elimina un pack de iconos (post `icons` + carpeta en uploads/betheme/icons).
 * SAFETY: destructive (borra la carpeta del pack; no hay papelera para los ficheros) — exige yes=1
 *
 * Fuente (Betheme 28.4.3): class-mfn-post-type-icons.php single_post_remove() l.161-174, enganchado a
 * `trashed_post` (l.40): borra path_icons/<mfn-icon-name-parsed> y luego wp_delete_post($id, true).
 * Por eso aquí se usa wp_trash_post(): así lo hace el propio hook del theme.
 *
 * Uso: wp eval-file icons-pack-remove.php id=123                (muestra qué borraría; no toca nada sin yes=1)
 *      wp eval-file icons-pack-remove.php id=123 yes=1          (papelera → single_post_remove borra carpeta y post)
 *      wp eval-file icons-pack-remove.php id=123 yes=1 keep-files=1   (solo el post, con wp_delete_post(force); la carpeta queda)
 *      ... dry-run=1  equivale a no pasar yes=1
 */
require_once __DIR__ . '/_lib.php';
$a = be_args( $args );

$id = (int) ( $a['id'] ?? 0 );
if ( ! $id ) {
	be_err( 'Falta id=<ID del post icons>' );
}
$post = get_post( $id );
if ( ! $post || $post->post_type !== 'icons' ) {
	be_err( "El post $id no existe o no es del tipo icons" );
}

$keep_files = be_flag( $a, 'keep-files' );
$icons_dir  = be_uploads_betheme( 'icons' );
$parsed     = (string) get_post_meta( $id, 'mfn-icon-name-parsed', true );
$upload     = (string) get_post_meta( $id, 'mfn-icon-upload', true );
$target     = $parsed !== '' ? $icons_dir['path'] . '/' . $parsed : ''; // l.164-165: path_icons . '/' . name-parsed

$plan = [
	'post_id'      => $id,
	'title'        => $post->post_title,
	'status'       => $post->post_status,
	'name_parsed'  => $parsed,
	'upload_meta'  => $upload,
	'dir_to_delete'=> $keep_files ? null : $target,
	'dir_exists'   => $target !== '' && is_dir( $target ),
	'method'       => $keep_files ? 'wp_delete_post(force) — no dispara trashed_post' : 'wp_trash_post → single_post_remove()',
	'hook_active'  => class_exists( 'Mfn_Post_Type_Icons' ) && EMPTY_TRASH_DAYS > 0,
];

// Trampa: con name-parsed vacío, single_post_remove() haría delete(path_icons . '/', true) = borrar TODOS los packs
if ( ! $keep_files && $parsed === '' ) {
	be_out( [ 'aborted' => true, 'plan' => $plan ] );
	be_err( 'mfn-icon-name-parsed está vacío: enviar este post a la papelera borraría TODA la carpeta uploads/betheme/icons. Usa keep-files=1 y borra la carpeta a mano.' );
}
if ( ! $keep_files && $upload !== '' && wp_normalize_path( $upload ) !== $target ) {
	be_msg( "aviso: mfn-icon-upload ($upload) no coincide con la carpeta que borra el hook ($target); se borra la segunda" );
}

if ( be_flag( $a, 'dry-run' ) || ! be_flag( $a, 'yes' ) ) {
	be_out( [ 'dry_run' => true, 'plan' => $plan ] );
	if ( ! be_flag( $a, 'dry-run' ) ) {
		be_require_yes( $a, 'Eliminar el pack de iconos' );
	}
	return;
}

$result = $plan;
if ( $keep_files ) {
	$deleted = wp_delete_post( $id, true );
	$result['post_deleted'] = (bool) $deleted;
} else {
	// single_post_remove() llama a Mfn_Helper::filesystem() (class-mfn-post-type-icons.php:166); Mfn_Helper
	// solo se carga en admin (functions.php:221) → en CLI hay que cargarla antes o el hook lanza un fatal.
	be_load_admin_classes();
	$trashed = wp_trash_post( $id ); // dispara trashed_post → single_post_remove (l.40, l.161)
	$result['wp_trash_post'] = (bool) $trashed;
	$result['post_deleted']  = get_post( $id ) === null;

	// Si el hook no corrió (EMPTY_TRASH_DAYS=0 → wp_trash_post borra directo sin trashed_post; o CPT desactivado)
	// replicamos su cuerpo: delete(dir, true) + wp_delete_post(force).
	if ( is_dir( $target ) || get_post( $id ) !== null ) {
		$fs = be_filesystem();
		if ( is_dir( $target ) ) {
			$fs->delete( $target, true );
		}
		if ( get_post( $id ) !== null ) {
			wp_delete_post( $id, true );
		}
		$result['fallback_manual'] = true;
		$result['post_deleted']    = get_post( $id ) === null;
	}
}
$result['dir_exists'] = $target !== '' && is_dir( $target );
be_out( $result );
