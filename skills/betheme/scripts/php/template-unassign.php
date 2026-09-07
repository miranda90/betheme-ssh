<?php
/**
 * template-unassign.php — vacía las condiciones de un template y recompila las options/metas
 * `mfn_<type>_*` de su tipo, con lo que deja de aplicarse en ningún sitio.
 * SAFETY: reversible (backup JSON previo en uploads/betheme/backups/; restaurar con template-assign restore=@fichero)
 *
 * Uso: wp eval-file template-unassign.php id=93
 *      wp eval-file template-unassign.php id=93 dry-run=1
 *      wp eval-file template-unassign.php id=93 clear-post-overrides=1   (además borra mfn_<type>_template=93 en posts)
 *
 * Replica el guardado del modal sin condiciones (visual-builder/visual-builder.php:363: delete_post_meta) y la
 * recompilación (l.370-383). Para cart/checkout/thanks/search borra mfn_<type>_template_used si apunta a este id (l.372-373).
 * No borra el template: para eso `wp post delete <id>`.
 */
require_once __DIR__ . '/_tmpl.php';
$a   = be_args( $args );
$dry = be_flag( $a, 'dry-run' );

$id = (int) ( $a['id'] ?? 0 );
if ( $id <= 0 ) {
	be_err( 'Indica id=<ID del template>.' );
}
$tmpl = be_tmpl_get( $id );
$type = be_tmpl_normalize_type( $tmpl['type'] );
if ( $type === '' ) {
	be_err( "El template $id no tiene mfn_template_type: no hay nada compilado que limpiar." );
}
$family = be_tmpl_family( $type );

global $wpdb;
$override_keys = [ 'mfn_' . $type . '_template' ];               // mfn_header_template, mfn_footer_template, mfn_single-post_template…
if ( $type === 'popup' ) {
	$override_keys = [ 'mfn_popup_included' ];
}
$overrides = (array) $wpdb->get_col( $wpdb->prepare(
	"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('" . implode( "','", array_map( 'esc_sql', $override_keys ) ) . "') AND meta_value = %s",
	(string) $id
) );

$plan = [
	'id'                  => $id,
	'title'               => $tmpl['title'],
	'type'                => $type,
	'family'              => $family,
	'conditions_before'   => $tmpl['conditions'],
	'template_used_now'   => $family === 'used' ? (int) get_option( 'mfn_' . $type . '_template_used' ) : null,
	'post_overrides'      => $overrides,
	'clear_post_overrides'=> be_flag( $a, 'clear-post-overrides' ),
];
if ( $overrides && ! be_flag( $a, 'clear-post-overrides' ) ) {
	be_msg( 'aviso: ' . count( $overrides ) . " posts siguen apuntando a este template por meta (" . implode( ',', $override_keys ) . '); esos overrides tienen prioridad sobre las condiciones (theme-functions.php:926-928). Añade clear-post-overrides=1 para borrarlos.' );
}

if ( $dry ) {
	$plan['dry_run'] = true;
	be_out( $plan );
	return;
}

$backup = null;
if ( ! be_flag( $a, 'no-backup' ) ) {
	$snap = be_tmpl_snapshot( $type );
	if ( be_flag( $a, 'clear-post-overrides' ) && $overrides ) {
		$snap['post_overrides'] = [ 'keys' => $override_keys, 'post_ids' => $overrides, 'value' => $id ]; // informativo
	}
	$backup = be_tmpl_snapshot_save( $snap );
	be_msg( "Backup: $backup  (revertir: wp eval-file template-assign.php restore=@$backup)" );
}

delete_post_meta( $id, 'mfn_template_conditions' );
if ( $family === 'used' && (int) get_option( 'mfn_' . $type . '_template_used' ) === $id ) {
	delete_option( 'mfn_' . $type . '_template_used' );
}
if ( be_flag( $a, 'clear-post-overrides' ) ) {
	foreach ( $overrides as $pid ) {
		foreach ( $override_keys as $k ) {
			if ( (string) get_post_meta( (int) $pid, $k, true ) === (string) $id ) {
				delete_post_meta( (int) $pid, $k );
			}
		}
	}
}
$method = be_tmpl_compile( $type );
wp_cache_flush();

$after = be_tmpl_snapshot( $type );
be_out( [
	'id'       => $id,
	'type'     => $type,
	'compiled' => $method,
	'backup'   => $backup,
	'options'  => $after['options'],
	'postmeta' => count( $after['postmeta'] ),
	'termmeta' => count( $after['termmeta'] ),
	'post_overrides_cleared' => be_flag( $a, 'clear-post-overrides' ) ? $overrides : [],
] );
