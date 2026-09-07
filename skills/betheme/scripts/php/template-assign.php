<?php
/**
 * template-assign.php — asigna condiciones de visualización a un template de Betheme y recompila
 * las options/metas `mfn_<type>_*` llamando al método real del builder (Mfn_Builder_Admin).
 * SAFETY: reversible (backup JSON previo en uploads/betheme/backups/; restaurar con restore=@fichero)
 *
 * Uso: wp eval-file template-assign.php id=93 everywhere=1
 *      wp eval-file template-assign.php id=93 singular=page,post archive=product search=1
 *      wp eval-file template-assign.php id=93 exclude-singular=product:12       (product_cat 12)
 *      wp eval-file template-assign.php id=175 all=1                            (single-portfolio: todos)
 *      wp eval-file template-assign.php id=128 tax=portfolio-types:15           (archive-portfolio: solo término 15)
 *      wp eval-file template-assign.php id=200 used=1                           (cart/checkout/thanks/search)
 *      wp eval-file template-assign.php id=93 cond=@conditions.json             (JSON literal de mfn_template_conditions)
 *      wp eval-file template-assign.php id=93 type=header ...                   (fija mfn_template_type si falta)
 *      wp eval-file template-assign.php id=93 ... publish=1                     (publica antes: el compilador ignora drafts)
 *      wp eval-file template-assign.php id=93 ... dry-run=1
 *      wp eval-file template-assign.php restore=@uploads/betheme/backups/template-header-…json
 *
 * Atajos header/footer/popup:  everywhere=1 | singular=all|<pt>[,<pt>:<term_id>] | archive=all|<pt>[:<term_id>] | search=1
 *                              exclude-singular=… | exclude-archive=…      (pt ∈ page,post,product,portfolio,offer)
 * Atajos single-* y archive-*:   all=1 | tax=<taxonomy>:all|<term_id>[,…] | exclude-tax=… | wishlist=1 | products-search=1
 * cart/checkout/thanks/search: used=1 | used=0
 *
 * Replica visual-builder/visual-builder.php:357-383 (guardado del modal "Display Conditions") sin nonce ni AJAX.
 */
require_once __DIR__ . '/_tmpl.php';
$a   = be_args( $args );
$dry = be_flag( $a, 'dry-run' );

// --- restore --------------------------------------------------------------
if ( ! empty( $a['restore'] ) ) {
	$snap = json_decode( $a['restore'], true );
	if ( ! is_array( $snap ) || empty( $snap['type'] ) ) {
		be_err( 'restore=@fichero debe ser un backup generado por template-assign/unassign (JSON con "type").' );
	}
	if ( $dry ) {
		be_out( [ 'dry_run' => true, 'would_restore' => [ 'type' => $snap['type'], 'options' => array_keys( (array) $snap['options'] ), 'postmeta' => count( (array) $snap['postmeta'] ), 'termmeta' => count( (array) $snap['termmeta'] ), 'conditions' => (array) $snap['conditions'] ] ] );
		return;
	}
	$r = be_tmpl_restore( $snap );
	be_msg( 'Backup restaurado; no se recompila (se reponen las options tal cual estaban).' );
	be_out( [ 'restored' => $r ] );
	return;
}

// --- template y tipo ------------------------------------------------------
$id = (int) ( $a['id'] ?? 0 );
if ( $id <= 0 ) {
	be_err( 'Indica id=<ID del template> (o restore=@fichero).' );
}
$tmpl = be_tmpl_get( $id );
$type = $tmpl['type'];

if ( ! empty( $a['type'] ) ) {
	$given = be_tmpl_normalize_type( trim( $a['type'] ) );
	if ( $type !== '' && $type !== $given ) {
		be_err( "El template $id ya es de tipo '$type'; no se cambia de tipo (borra mfn_template_type a mano si de verdad quieres)." );
	}
	$type = $given;
}
if ( $type === '' ) {
	be_err( "El template $id no tiene mfn_template_type; pásalo con type=header|footer|popup|single-post|…" );
}
$normalized = be_tmpl_normalize_type( $type );
$family     = be_tmpl_family( $normalized );

// --- condiciones a escribir -----------------------------------------------
$conditions = null;
if ( ! empty( $a['cond'] ) ) {
	$conditions = json_decode( $a['cond'], true );
	if ( ! is_array( $conditions ) ) {
		be_err( 'cond= debe ser un array JSON de condiciones: [{"rule":"include","var":"everywhere",...}]' );
	}
} elseif ( $family === 'used' ) {
	$conditions = [];
} elseif ( in_array( $family, [ 'global', 'addons' ], true ) ) {
	$conditions = be_tmpl_build_global( $a );
} else {
	$conditions = be_tmpl_build_posttypes( $a );
}
be_tmpl_validate( $conditions, $family );

$set_used = isset( $a['used'] ) ? be_flag( $a, 'used' ) : null;
if ( $family === 'used' && $set_used === null && ! $conditions ) {
	be_err( "Los templates de tipo $normalized se activan con used=1 (option mfn_{$normalized}_template_used) o used=0." );
}
if ( $family !== 'used' && ! $conditions ) {
	be_err( 'No hay condiciones que asignar: usa everywhere=1, singular=…, archive=…, all=1, tax=… o cond=@fichero. Para vaciar usa template-unassign.' );
}

$plan = [
	'id'          => $id,
	'title'       => $tmpl['title'],
	'status'      => $tmpl['status'],
	'type'        => $normalized,
	'family'      => $family,
	'lang'        => $tmpl['lang'],
	'before'      => $tmpl['conditions'],
	'after'       => $conditions,
	'set_used'    => $set_used,
	'will_publish'=> $tmpl['status'] !== 'publish' && be_flag( $a, 'publish' ),
];
if ( $tmpl['status'] !== 'publish' && ! be_flag( $a, 'publish' ) ) {
	be_msg( "aviso: el template está en estado '{$tmpl['status']}'; los compiladores solo leen post_status='publish' (class-mfn-builder-admin.php:3972). Añade publish=1." );
}
if ( $type !== $normalized ) {
	be_msg( "aviso: tipo deprecated '$type' → se reescribe como '$normalized' (visual-builder.php:308-320)." );
}

if ( $dry ) {
	$plan['dry_run'] = true;
	$plan['options_now'] = array_keys( be_tmpl_snapshot( $normalized )['options'] );
	be_out( $plan );
	return;
}

// --- backup ---------------------------------------------------------------
$backup = null;
if ( ! be_flag( $a, 'no-backup' ) ) {
	$backup = be_tmpl_snapshot_save( be_tmpl_snapshot( $normalized ) );
	be_msg( "Backup de options/metas mfn_{$normalized}_* y conditions: $backup" );
	be_msg( "Revertir: wp eval-file template-assign.php restore=@$backup" );
}

// --- escritura (visual-builder.php:308-320, 357-383) ------------------------
if ( $type !== $normalized || (string) get_post_meta( $id, 'mfn_template_type', true ) !== $normalized ) {
	update_post_meta( $id, 'mfn_template_type', $normalized );
}
if ( $plan['will_publish'] ) {
	wp_update_post( [ 'ID' => $id, 'post_status' => 'publish' ] );
}
if ( $conditions ) {
	update_post_meta( $id, 'mfn_template_conditions', wp_slash( json_encode( $conditions ) ) );
} else {
	delete_post_meta( $id, 'mfn_template_conditions' );
}
if ( $family === 'used' && $set_used !== null ) {
	if ( $set_used ) {
		update_option( 'mfn_' . $normalized . '_template_used', $id );
	} elseif ( (int) get_option( 'mfn_' . $normalized . '_template_used' ) === $id ) {
		delete_option( 'mfn_' . $normalized . '_template_used' );
	}
}
$method = be_tmpl_compile( $normalized );
wp_cache_flush();

$after = be_tmpl_snapshot( $normalized );
be_out( [
	'id'         => $id,
	'type'       => $normalized,
	'conditions' => $conditions,
	'compiled'   => $method,
	'backup'     => $backup,
	'options'    => $after['options'],
	'postmeta'   => count( $after['postmeta'] ),
	'termmeta'   => count( $after['termmeta'] ),
] );
