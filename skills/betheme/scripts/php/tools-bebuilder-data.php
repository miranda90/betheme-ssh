<?php
/**
 * tools-bebuilder-data.php — las tres operaciones de Tools que tocan los datos del BeBuilder:
 *   op=rerender  "Re-render Builder data": borra visual-builder/assets/js/forms/bebuilder-<ver>.js y cambia betheme_form_uid
 *                (MfnVisualBuilder::removeBeDataFile(), visual-builder/classes/visual-builder-class.php:1721-1727).
 *                El admin regenera el fichero al abrir el BeBuilder (l.212-215).
 *   op=rewrite   "CSS update · Update": Mfn_Helper::bebuilder_data_updater() (functions/admin/class-mfn-helper.php:707-730):
 *                pasa MfnLocalCssCompability->render() por cada post con mfn-page-items; marca mfn-css-db-update.
 *   op=items     regenera ya el fichero JS con Mfn_Helper::generate_bebuilder_items() (class-mfn-helper.php:687-705).
 *                REQUIERE --user=<admin>: fieldsToJS() solo incluye el formulario de Theme Options si
 *                current_user_can('edit_theme_options') && mfn_is_registered() (visual-builder-class.php:636).
 * SAFETY: rerender/items reversibles (fichero derivado); rewrite reversible (guarda mfn-page-items-backup la primera vez) — rewrite exige yes=1
 *
 * Uso: wp eval-file tools-bebuilder-data.php op=rerender
 *      wp --user=admin eval-file tools-bebuilder-data.php op=items
 *      wp eval-file tools-bebuilder-data.php op=rewrite dry-run=1
 *      wp eval-file tools-bebuilder-data.php op=rewrite yes=1 [ids=28,122] [resume=1]
 */
require_once __DIR__ . '/_lib.php';
$a  = be_args( $args );
$op = $a['op'] ?? '';

/** Clases del builder que Betheme solo carga en admin (class-mfn-builder.php:37-45, functions.php:239-241) */
function be_load_builder_classes(): void {
	be_load_admin_classes();
	$t = get_template_directory();
	foreach ( [
		'Mfn_Builder_Fields'      => '/functions/builder/class-mfn-builder-fields.php',
		'Mfn_Icons'               => '/muffin-options/icons.php',
		'MfnLocalCssCompability'  => '/visual-builder/classes/helpers/local-css-compability.php',
		'MfnVisualBuilder'        => '/visual-builder/classes/visual-builder-class.php',
	] as $class => $file ) {
		if ( ! class_exists( $class ) && file_exists( $t . $file ) ) {
			require_once $t . $file;
		}
	}
}

$js_file = get_template_directory() . '/visual-builder/assets/js/forms/bebuilder-' . be_theme_version() . '.js';

switch ( $op ) {

	case 'rerender':
		$existed = file_exists( $js_file );
		$old_uid = get_option( 'betheme_form_uid' );
		be_bebuilder_reset(); // = removeBeDataFile(): wp_delete_file + update_option('betheme_form_uid', unique_ID())
		be_out( [
			'op'       => 'rerender',
			'file'     => $js_file,
			'existed'  => $existed,
			'exists_now' => file_exists( $js_file ),
			'form_uid' => [ 'old' => $old_uid, 'new' => get_option( 'betheme_form_uid' ) ],
			'note'     => 'Se regenera al abrir el BeBuilder en el admin (visual-builder-class.php:212-215) o con op=items.',
		] );
		break;

	case 'items':
		be_load_builder_classes();
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			be_err( 'Ejecuta con --user=<administrador>: sin él fieldsToJS() genera el fichero SIN el formulario de Theme Options (visual-builder-class.php:636) y el BeBuilder queda cojo hasta que un admin lo regenere.' );
		}
		if ( ! function_exists( 'mfn_is_registered' ) || ! mfn_is_registered() ) {
			be_msg( 'aviso: tema sin registrar (mfn_is_registered() false): el fichero se genera sin el formulario de Theme Options, igual que en el admin.' );
		}
		if ( ! class_exists( 'MfnVisualBuilder' ) || ! class_exists( 'Mfn_Builder_Fields' ) ) {
			be_err( 'No se pudieron cargar MfnVisualBuilder / Mfn_Builder_Fields.' );
		}
		// generate_bebuilder_items() aborta si el filtro bebuilder_access devuelve false (l.689-690);
		// mfn_bebuilder_access() (theme-head.php:2635) mira roles del usuario actual y builder-visibility=hide.
		add_filter( 'bebuilder_access', '__return_true', 99 );
		$old_uid = get_option( 'betheme_form_uid' );
		$made    = Mfn_Helper::generate_bebuilder_items();
		clearstatcache();
		be_out( [
			'op'        => 'items',
			'file'      => $js_file,
			'written'   => (bool) $made,
			'bytes'     => file_exists( $js_file ) ? filesize( $js_file ) : 0,
			'form_uid'  => [ 'old' => $old_uid, 'new' => get_option( 'betheme_form_uid' ) ],
			'has_themeoptions_form' => file_exists( $js_file ) && strpos( file_get_contents( $js_file ), 'themeoptions_fields' ) !== false,
		] );
		break;

	case 'rewrite':
		be_load_builder_classes();
		if ( ! class_exists( 'MfnLocalCssCompability' ) || ! class_exists( 'Mfn_Builder_Fields' ) ) {
			be_err( 'No se pudieron cargar MfnLocalCssCompability / Mfn_Builder_Fields.' );
		}
		global $wpdb;
		$dry = be_flag( $a, 'dry-run' );
		if ( ! empty( $a['ids'] ) ) {
			$ids = array_map( 'intval', explode( ',', $a['ids'] ) );
		} else {
			$ids = array_map( 'intval', $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'mfn-page-items'" ) ); // l.716
		}
		$progress = be_flag( $a, 'resume' ) ? (int) get_option( 'mfn-css-db-update-status' ) : 0; // l.712
		$rows = [];
		foreach ( $ids as $i => $id ) {
			$p = get_post( $id );
			$rows[] = [
				'id' => $id,
				'type' => $p ? $p->post_type : null,
				'title' => $p ? $p->post_title : null,
				'has_backup' => ! empty( get_post_meta( $id, 'mfn-page-items-backup', true ) ),
				'skipped_by_resume' => ( $progress && $progress > $i ),
			];
		}
		if ( $dry ) {
			be_out( [ 'op' => 'rewrite', 'dry_run' => true, 'posts' => count( $ids ), 'list' => $rows, 'css_db_update' => get_option( 'mfn-css-db-update' ) ] );
			break;
		}
		be_require_yes( $a, 'Reescribir mfn-page-items de ' . count( $ids ) . ' posts (CSS update)' );

		// cuerpo de bebuilder_data_updater() l.711-728
		update_option( 'mfn-css-db-update', 'pending' );
		$css = new MfnLocalCssCompability();
		foreach ( $ids as $i => $id ) {
			if ( $progress && $progress > $i ) {
				continue;
			}
			$css->render( $id ); // crea mfn-page-items-backup si no existía (l.28); solo reescribe si detecta formato antiguo (l.151)
			update_option( 'mfn-css-db-update-status', $i );
		}
		update_option( 'mfn-css-db-update', '1' );
		delete_option( 'mfn-css-db-update-status' );
		be_out( [ 'op' => 'rewrite', 'posts' => count( $ids ), 'list' => $rows, 'css_db_update' => get_option( 'mfn-css-db-update' ) ] );
		break;

	default:
		be_err( 'op= debe ser rerender | rewrite | items' );
}
