<?php
/**
 * catalog-generate.php — genera el catálogo exhaustivo de opciones de Theme Options
 * leyendo la definición real ($MFN_Options->menu / ->sections, muffin-options/theme-options.php).
 * SAFETY: read-only (solo escribe los ficheros de salida indicados)
 *
 * Uso: wp eval-file catalog-generate.php out=/ruta/reference          (escribe catalogo-opciones.json y .md)
 *      wp eval-file catalog-generate.php                                (JSON por stdout)
 *
 * Nota: theme-options.php no se puede cargar fuera de WordPress (usa __(), apply_filters,
 * helpers mfna_* que consultan la BD). Por eso el catálogo se genera en un sitio real.
 * Los campos cuyas `options` dependen del sitio (sidebars, páginas, menús, plantillas)
 * se marcan `dynamic: true` y sus valores no se listan.
 */
require_once __DIR__ . '/_lib.php';
$a   = be_args( $args );
$mfn = be_options_obj();

// Campos con opciones dependientes del sitio (helpers mfna_* de theme-options.php:88-370)
$dynamic_types = [ 'select_ajax', 'category', 'ajax' ];
$dynamic_ids   = '/(-page|-sidebar\d?|-menu|-template|-header|-footer|sidebars|construction-contact|shop-wishlist-page|blog-page|portfolio-page|error404-page|construction-page)$/';

$catalog = [
	'generated_at'    => gmdate( 'c' ),
	'betheme_version' => be_theme_version(),
	'site'            => home_url(),
	'tabs'            => [],
];

$field_count = 0;
foreach ( (array) $mfn->menu as $tab_id => $tab ) {
	$t = [ 'id' => $tab_id, 'title' => wp_strip_all_tags( $tab['title'] ?? $tab_id ), 'sections' => [] ];
	foreach ( (array) ( $tab['sections'] ?? [] ) as $section_id ) {
		$section = $mfn->sections[ $section_id ] ?? null;
		if ( ! $section ) {
			$t['sections'][] = [ 'id' => $section_id, 'title' => $section_id, 'missing' => true, 'fields' => [] ];
			continue;
		}
		$s = [ 'id' => $section_id, 'title' => wp_strip_all_tags( $section['title'] ?? $section_id ), 'fields' => [] ];
		$group = '';
		foreach ( (array) ( $section['fields'] ?? [] ) as $field ) {
			$type = $field['type'] ?? '';
			if ( in_array( $type, [ 'header', 'subheader' ], true ) ) {
				$group = wp_strip_all_tags( $field['title'] ?? '' );
			}
			if ( empty( $field['id'] ) ) {
				continue;
			}
			$f = [
				'id'    => $field['id'],
				'type'  => $type,
				'shape' => be_shape( $type ),
				'title' => wp_strip_all_tags( $field['title'] ?? '' ),
				'group' => $group,
			];
			if ( array_key_exists( 'std', $field ) ) {
				$f['std'] = $field['std'];
			}
			$is_dynamic = in_array( $type, $dynamic_types, true ) || preg_match( $dynamic_ids, $field['id'] );
			if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
				$opts = $field['options'];
				$flat = true;
				foreach ( $opts as $k => $v ) {
					if ( ! is_scalar( $k ) || ! ( is_scalar( $v ) || $v === null ) ) {
						$flat = false;
						break;
					}
				}
				if ( $is_dynamic || ! $flat || count( $opts ) > 60 ) {
					$f['options_count'] = count( $opts );
				} else {
					$f['options'] = array_map( 'wp_strip_all_tags', array_map( 'strval', $opts ) );
				}
			}
			if ( $is_dynamic ) {
				$f['dynamic'] = true;
			}
			foreach ( [ 'param', 'after', 'responsive', 'data', 'validate', 'role_restricted', 'sub_desc' ] as $k ) {
				if ( isset( $field[ $k ] ) && ( is_scalar( $field[ $k ] ) || is_array( $field[ $k ] ) ) ) {
					$f[ $k ] = is_string( $field[ $k ] ) ? wp_strip_all_tags( $field[ $k ] ) : $field[ $k ];
				}
			}
			if ( isset( $field['condition'] ) ) {
				$f['condition'] = $field['condition'];
			}
			if ( isset( $field['desc'] ) && is_string( $field['desc'] ) ) {
				$f['desc'] = trim( wp_strip_all_tags( $field['desc'] ) );
			}
			$s['fields'][] = $f;
			$field_count++;
		}
		$t['sections'][] = $s;
	}
	$catalog['tabs'][] = $t;
}
$catalog['field_count'] = $field_count;

// Secciones definidas pero no enlazadas desde el menú (p.ej. advanced/hooks ocultas por filtro)
$linked = [];
foreach ( $catalog['tabs'] as $t ) {
	foreach ( $t['sections'] as $s ) {
		$linked[] = $s['id'];
	}
}
$catalog['unlinked_sections'] = array_values( array_diff( array_keys( (array) $mfn->sections ), $linked ) );

// ---------------------------------------------------------------------------

function be_catalog_md( array $c ): string {
	$md  = "# Catálogo de opciones de Theme Options — Betheme {$c['betheme_version']}\n\n";
	$md .= "> GENERADO por `scripts/be-catalog-generate.sh` el {$c['generated_at']} (sitio: {$c['site']}). No editar a mano.\n";
	$md .= "> {$c['field_count']} campos con id. Los campos `dynamic` tienen opciones que dependen del sitio (páginas, sidebars, menús, plantillas).\n";
	$md .= "> `std` es el valor por defecto que Betheme escribe la PRIMERA vez que crea la opción (options.php:670); después manda lo guardado.\n\n";
	$md .= "Todos los ids se leen con `mfn_opts_get('<id>')` y viven en `wp_options.betheme[<id>]`.\n\n";
	$md .= "## Índice\n\n";
	foreach ( $c['tabs'] as $t ) {
		$md .= "- **{$t['title']}** (`{$t['id']}`): " . implode( ', ', array_map( fn( $s ) => '`' . $s['id'] . '`', $t['sections'] ) ) . "\n";
	}
	$md .= "\n";
	foreach ( $c['tabs'] as $t ) {
		$md .= "## {$t['title']} (`{$t['id']}`)\n\n";
		foreach ( $t['sections'] as $s ) {
			$md .= "### {$s['title']} (`{$s['id']}`)\n\n";
			if ( ! $s['fields'] ) {
				$md .= "_Sin campos con id._\n\n";
				continue;
			}
			$md .= "| id | tipo | forma del valor | std | opciones / notas |\n|---|---|---|---|---|\n";
			foreach ( $s['fields'] as $f ) {
				$std = array_key_exists( 'std', $f ) ? be_json( $f['std'], false ) : '';
				if ( strlen( $std ) > 60 ) {
					$std = substr( $std, 0, 57 ) . '...';
				}
				$notes = [];
				if ( ! empty( $f['group'] ) ) {
					$notes[] = 'grupo: ' . $f['group'];
				}
				if ( isset( $f['options'] ) ) {
					$keys = array_keys( $f['options'] );
					$notes[] = 'opciones: ' . implode( ', ', array_map( fn( $k ) => '`' . $k . '`', array_slice( $keys, 0, 12 ) ) ) . ( count( $keys ) > 12 ? ', …' : '' );
				} elseif ( isset( $f['options_count'] ) ) {
					$notes[] = ( ! empty( $f['dynamic'] ) ? 'dynamic, ' : '' ) . $f['options_count'] . ' opciones';
				} elseif ( ! empty( $f['dynamic'] ) ) {
					$notes[] = 'dynamic';
				}
				if ( ! empty( $f['responsive'] ) ) {
					$notes[] = 'responsive';
				}
				if ( ! empty( $f['data'] ) && is_string( $f['data'] ) ) {
					$notes[] = 'data=' . $f['data'];
				}
				if ( ! empty( $f['role_restricted'] ) ) {
					$notes[] = 'role_restricted';
				}
				if ( isset( $f['condition']['id'] ) ) {
					$notes[] = 'condición UI: `' . $f['condition']['id'] . '`';
				}
				$title = str_replace( '|', '\\|', $f['title'] );
				$std   = str_replace( '|', '\\|', $std );
				$md   .= "| `{$f['id']}` | {$f['type']} | {$f['shape']} | " . ( $std !== '' ? '`' . $std . '`' : '' ) . " | " . str_replace( '|', '\\|', implode( '; ', $notes ) ) . ( $title ? " — *{$title}*" : '' ) . " |\n";
			}
			$md .= "\n";
		}
	}
	if ( $c['unlinked_sections'] ) {
		$md .= "## Secciones definidas pero no visibles en el menú\n\n" . implode( ', ', array_map( fn( $s ) => '`' . $s . '`', $c['unlinked_sections'] ) ) . "\n";
	}
	return $md;
}

if ( ! empty( $a['out'] ) ) {
	$dir = rtrim( $a['out'], '/' );
	if ( ! is_dir( $dir ) && ! mkdir( $dir, 0755, true ) ) {
		be_err( "No se puede crear $dir" );
	}
	file_put_contents( $dir . '/catalogo-opciones.json', be_json( $catalog ) . "\n" );
	file_put_contents( $dir . '/catalogo-opciones.md', be_catalog_md( $catalog ) );
	be_out( [
		'json'   => $dir . '/catalogo-opciones.json',
		'md'     => $dir . '/catalogo-opciones.md',
		'fields' => $field_count,
		'tabs'   => count( $catalog['tabs'] ),
		'unlinked_sections' => $catalog['unlinked_sections'],
	] );
	return;
}
be_out( $catalog );
