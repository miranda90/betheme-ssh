<?php
/**
 * catalog-check.php — comprueba que los ids de Theme Options citados en los .md existen en el catálogo.
 * SAFETY: read-only. Se ejecuta con PHP CLI, sin WordPress.
 *
 * Uso: php scripts/dev/catalog-check.php [ruta/docs/betheme-ssh]
 *
 * Errores (exit 1): ids usados en contextos inequívocos que no existen en reference/catalogo-opciones.json:
 *   be-opt-set.sh key=ID | be-opt-get.sh key=ID | mfn_opts_get('ID') | wp option pluck betheme ID |
 *   wp option patch update betheme ID | `betheme[ID]`
 * Avisos: tokens entre comillas invertidas con prefijo típico de opción que no están en el catálogo.
 */
$root = rtrim( $argv[1] ?? dirname( __DIR__, 2 ), '/' );
$json = $root . '/reference/catalogo-opciones.json';
if ( ! is_readable( $json ) ) {
	fwrite( STDERR, "No encuentro $json\n" );
	exit( 2 );
}
$catalog = json_decode( file_get_contents( $json ), true );
$ids     = [];
$sections = [];
foreach ( $catalog['tabs'] as $t ) {
	$sections[ $t['id'] ] = true;
	foreach ( $t['sections'] as $s ) {
		$sections[ $s['id'] ] = true;
		foreach ( $s['fields'] as $f ) {
			$ids[ $f['id'] ] = $s['id'];
		}
	}
}
// claves válidas que no son campos, o patrones dinámicos
// claves válidas que no son campos (internas), claves del formulario del panel (no persisten) y placeholders de los docs
$extra = [ 'last_tab', 'imported', 'import', 'import_code', 'import_link', 'defaults', 'id', 'ID',
	'automatic-updates', // leída por Mfn_Update::autoupdate() pero nunca definida en el panel (código muerto, doc 09)
];
$dynamic_patterns = [
	'/^font-custom\d+(-woff|-ttf)?$/',            // slots dinámicos de fuentes (font-custom3, 4...)
	'/^social-custom-(icon|link|title)-\d+$/',    // sociales dinámicos
];

$strict_ctx = [
	'/be-opt-(?:set|get|unset)\.sh[^\n`]*?\bkey=([a-z0-9][a-z0-9_,-]*)/',
	'/mfn_opts_get\(\s*[\'"]([a-z0-9][a-z0-9_-]*)[\'"]/',
	'/wp option (?:pluck|patch update|patch delete|patch insert) betheme ([a-z0-9][a-z0-9_-]*)/',
	'/betheme\[([a-z0-9][a-z0-9_-]*)\]/',
];
$prefixes = 'logo|retina|sticky|responsive|mobile|header|subheader|menu|footer|blog|portfolio|shop|sidebar|font|color|background|translate|gdpr|google|static|hold|builder|performance|lazy|minify|custom|social|seo|search|page|grid|layout|section|preloader|construction|error404|favicon|apple|img|button|image|slider|prev|share|excerpt|love|featured|free|fake|sidecart|product|skin|border|form|alert|palette|hook|post-type|theme-disable|jquery|css|srcset|images|woocommerce|local|skip|keyboard|underline|repetitive|warning|recaptcha|cf7|elementor|parallax|prettyphoto|sc-gallery|plugin|top-bar|sliding|action|header-action|mab|breadcrumbs|pagination|single|related|read-more|exclude|meta|title|table|no-hover|math|display|content|layout-boxed|automatic|hide_editor|table_prefix';

$errors = $warnings = [];
foreach ( glob( $root . '/*.md' ) as $file ) {
	$md  = file_get_contents( $file );
	$rel = basename( $file );
	$seen_strict = [];
	foreach ( $strict_ctx as $re ) {
		if ( preg_match_all( $re, $md, $m ) ) {
			foreach ( $m[1] as $list ) {
				foreach ( explode( ',', $list ) as $id ) {
					$id = trim( $id );
					if ( $id === '' || $id[0] === '<' ) {
						continue;
					}
					$seen_strict[ $id ] = true;
				}
			}
		}
	}
	foreach ( array_keys( $seen_strict ) as $id ) {
		if ( isset( $ids[ $id ] ) || in_array( $id, $extra, true ) || strpos( $id, '<' ) !== false ) {
			continue;
		}
		$ok = false;
		foreach ( $dynamic_patterns as $p ) {
			if ( preg_match( $p, $id ) ) {
				$ok = true;
			}
		}
		if ( ! $ok ) {
			$errors[] = "$rel: id '$id' usado como opción de Theme Options pero no existe en el catálogo";
		}
	}
	if ( preg_match_all( '/`((?:' . $prefixes . ')[a-z0-9_-]*)`/', $md, $m ) ) {
		foreach ( array_unique( $m[1] ) as $tok ) {
			if ( isset( $ids[ $tok ] ) || isset( $sections[ $tok ] ) || in_array( $tok, $extra, true ) || isset( $seen_strict[ $tok ] ) ) {
				continue;
			}
			$ok = false;
			foreach ( $dynamic_patterns as $p ) {
				if ( preg_match( $p, $tok ) ) {
					$ok = true;
				}
			}
			if ( ! $ok && preg_match( '/-/', $tok ) && ! preg_match( '/^(mfn|be|betheme|temini|wp|post|page|template|layout|icon|mcb|header_|footer_|single-product-|archive-|shop-archive-|single-post-|mobile-menu-|css-|font-awesome|\.)/', $tok ) ) {
				$warnings[] = "$rel: `$tok` parece un id de opción pero no está en el catálogo (¿meta, clase CSS, o id inventado?)";
			}
		}
	}
}

echo "Catálogo: " . count( $ids ) . " ids (Betheme {$catalog['betheme_version']})\n";
echo "Errores: " . count( $errors ) . "\n";
foreach ( $errors as $e ) {
	echo "  ERROR  $e\n";
}
echo "Avisos: " . count( $warnings ) . "\n";
foreach ( $warnings as $w ) {
	echo "  aviso  $w\n";
}
exit( $errors ? 1 : 0 );
