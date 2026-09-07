<?php
/**
 * _lib.php — helpers compartidos por los scripts be-* (ejecutados con `wp eval-file`).
 *
 * Todos los scripts de scripts/php/ hacen `require_once __DIR__ . '/_lib.php';`.
 * Requiere WordPress cargado con Betheme activo (nunca `wp --skip-themes`).
 *
 * Fuente de verdad de lo que replica (Betheme 28.4.3):
 *  - opción `betheme`:            muffin-options/options.php:38,77
 *  - revisiones:                  MFN_Options::set_revision()   options.php:132-166
 *  - CSS estático:                MFN_Options::_static_CSS()    options.php:903-933
 *  - efectos tras guardar:        options.php:88-90 (hook mfn-opts-page-before-form), 939-957
 *  - fichero JS del BeBuilder:    MfnVisualBuilder::removeBeDataFile()  visual-builder/classes/visual-builder-class.php:1721
 *  - valores por defecto:         MFN_Options::_default_values()  options.php:640-663
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Este fichero se ejecuta con: wp eval-file <script>.php clave=valor ...\n" );
	exit( 2 );
}

define( 'BE_LIB_VERSION', '1.0.0' );
define( 'BE_OPT', 'betheme' );

// ---------------------------------------------------------------------------
// Argumentos y salida
// ---------------------------------------------------------------------------

/**
 * Convierte los argumentos posicionales de `wp eval-file` (`clave=valor`) en array.
 * `valor=@/ruta/fichero` lee el fichero; `valor=@-` lee stdin. `clave` sin `=` equivale a `clave=1`.
 */
function be_args( array $argv ): array {
	$out = [];
	foreach ( $argv as $a ) {
		if ( strpos( $a, '=' ) === false ) {
			$out[ ltrim( $a, '-' ) ] = '1';
			continue;
		}
		list( $k, $v ) = explode( '=', $a, 2 );
		$k = ltrim( $k, '-' );
		if ( $v === '@-' ) {
			$v = stream_get_contents( STDIN );
		} elseif ( strlen( $v ) > 1 && $v[0] === '@' && is_readable( substr( $v, 1 ) ) ) {
			$v = file_get_contents( substr( $v, 1 ) );
		}
		$out[ $k ] = $v;
	}
	return $out;
}

/** true si el flag está presente y no es 0/false/no/vacío */
function be_flag( array $args, string $key ): bool {
	if ( ! isset( $args[ $key ] ) ) {
		return false;
	}
	return ! in_array( strtolower( trim( (string) $args[ $key ] ) ), [ '0', 'false', 'no', '' ], true );
}

function be_json( $data, bool $pretty = true ): string {
	$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
	if ( $pretty ) {
		$flags |= JSON_PRETTY_PRINT;
	}
	return json_encode( $data, $flags );
}

/** Imprime JSON en stdout (lo que consume el usuario o el siguiente script) */
function be_out( $data, bool $pretty = true ): void {
	echo be_json( $data, $pretty ), "\n";
}

/** Mensaje informativo en stderr (no ensucia el stdout JSON) */
function be_msg( string $msg ): void {
	fwrite( STDERR, $msg . "\n" );
}

function be_err( string $msg, int $code = 1 ): void {
	fwrite( STDERR, 'ERROR: ' . $msg . "\n" );
	exit( $code );
}

/** Aborta si no se ha confirmado con yes=1 (--yes en el wrapper) */
function be_require_yes( array $args, string $what ): void {
	if ( ! be_flag( $args, 'yes' ) ) {
		be_err( "$what es una operación destructiva. Añade yes=1 (o --yes en el wrapper) para confirmar.", 3 );
	}
}

// ---------------------------------------------------------------------------
// Carga de clases que Betheme solo incluye en admin (functions.php:216-243)
// ---------------------------------------------------------------------------

function be_load_admin_classes(): void {
	$dir = get_template_directory();
	if ( ! class_exists( 'Mfn_Helper' ) && file_exists( $dir . '/functions/admin/class-mfn-helper.php' ) ) {
		require_once $dir . '/functions/admin/class-mfn-helper.php';
	}
	if ( ! class_exists( 'Mfn_Importer_Helper' ) && file_exists( $dir . '/functions/importer/class-mfn-importer-helper.php' ) ) {
		require_once $dir . '/functions/importer/class-mfn-importer-helper.php';
	}
}

/** Instancia global de MFN_Options creada en mfn_opts_setup() (muffin-options/theme-options.php:771) */
function be_options_obj(): MFN_Options {
	global $MFN_Options;
	if ( ! ( $MFN_Options instanceof MFN_Options ) ) {
		be_err( 'Global $MFN_Options no disponible. ¿Betheme/Témini activo? No usar --skip-themes.' );
	}
	return $MFN_Options;
}

function be_theme_version(): string {
	return defined( 'MFN_THEME_VERSION' ) ? MFN_THEME_VERSION : 'desconocida';
}

// ---------------------------------------------------------------------------
// Definición de campos (para validar ids y formas de valor)
// ---------------------------------------------------------------------------

/**
 * Tabla tipo de campo → forma del valor guardado.
 * Verificado en muffin-options/fields/<tipo>/field_<tipo>.php (28.4.3).
 */
function be_shape( string $type ): string {
	static $map = [
		// escalares (string)
		'text' => 'string', 'textarea' => 'string', 'select' => 'string', 'select_ajax' => 'string',
		'switch' => 'string', 'color' => 'string', 'icon' => 'string', 'sliderbar' => 'string',
		'pills' => 'string', 'radio_img' => 'string', 'font_select' => 'string', 'order' => 'string (csv)',
		'checkbox_pseudo' => 'string', 'category' => 'string', 'multiselect' => 'array',
		'box_shadow' => 'string', 'boxshadow' => 'string', 'text_shadow' => 'string',
		'backdrop_filter' => 'string', 'css_filters' => 'string', 'transform' => 'array',
		'upload' => 'string (URL o URL#attachment_id)', 'upload_multi' => 'string',
		// arrays
		'checkbox' => 'array {clave: clave} solo marcados', 'color_multi' => 'array {subclave: color}',
		'gradient' => 'array {angle,color,color2,...}', 'dimensions' => 'array {top,right,bottom,left,isLinked}',
		'typography' => 'array {size,line_height,weight_style,letter_spacing}', 'typography_vb' => 'array',
		'social' => 'array {red: url, ..., order: csv}', 'multi_text' => 'array indexado',
		'dynamic_items' => 'array de {url,id,uid,type}', 'tabs' => 'array de filas', 'logic' => 'array anidado',
		'hotspot' => 'array anidado', 'group' => 'array',
		// no persisten
		'ajax' => 'no persiste (botón)', 'header' => 'no persiste', 'subheader' => 'no persiste',
		'info' => 'no persiste', 'helper' => 'no persiste', 'preview' => 'no persiste',
		'visual' => 'no persiste', 'custom' => 'no persiste',
	];
	return $map[ $type ] ?? 'desconocida (' . $type . ')';
}

/** true si la forma es array */
function be_shape_is_array( string $shape ): bool {
	return strpos( $shape, 'array' ) === 0;
}

/**
 * Índice id → definición de campo (+ 'section', 'section_title', 'tab', 'tab_title').
 * Solo campos con id; los de maquetación (header/info/...) no cuentan.
 */
function be_fields(): array {
	static $index = null;
	if ( $index !== null ) {
		return $index;
	}
	$mfn   = be_options_obj();
	$index = [];
	$section_tab = [];
	foreach ( (array) $mfn->menu as $tab_id => $tab ) {
		foreach ( (array) ( $tab['sections'] ?? [] ) as $s ) {
			$section_tab[ $s ] = [ $tab_id, $tab['title'] ?? $tab_id ];
		}
	}
	foreach ( (array) $mfn->sections as $section_id => $section ) {
		foreach ( (array) ( $section['fields'] ?? [] ) as $field ) {
			if ( empty( $field['id'] ) ) {
				continue;
			}
			$field['section']       = $section_id;
			$field['section_title'] = $section['title'] ?? $section_id;
			$field['tab']           = $section_tab[ $section_id ][0] ?? '';
			$field['tab_title']     = $section_tab[ $section_id ][1] ?? '';
			$index[ $field['id'] ]  = $field;
		}
	}
	return $index;
}

/** Claves válidas en `betheme` que no son campos del panel */
function be_extra_keys(): array {
	return [ 'last_tab', 'imported' ];
}

// ---------------------------------------------------------------------------
// Lectura / escritura de la opción `betheme`
// ---------------------------------------------------------------------------

function be_opts(): array {
	$o = get_option( BE_OPT );
	return is_array( $o ) ? $o : [];
}

/** Diferencia entre dos arrays de opciones (solo primer nivel) */
function be_diff( array $old, array $new ): array {
	$diff = [ 'changed' => [], 'added' => [], 'removed' => [] ];
	foreach ( $new as $k => $v ) {
		if ( ! array_key_exists( $k, $old ) ) {
			$diff['added'][ $k ] = $v;
		} elseif ( $old[ $k ] !== $v ) {
			$diff['changed'][ $k ] = [ 'old' => $old[ $k ], 'new' => $v ];
		}
	}
	foreach ( $old as $k => $v ) {
		if ( ! array_key_exists( $k, $new ) ) {
			$diff['removed'][ $k ] = $v;
		}
	}
	return $diff;
}

function be_diff_is_empty( array $diff ): bool {
	return ! $diff['changed'] && ! $diff['added'] && ! $diff['removed'];
}

/**
 * Guarda el array completo de opciones replicando lo que hace el panel al pulsar "Save":
 *   1. revisión de seguridad con el estado ANTERIOR en `betheme_revision_<tipo>` (máx. 5)
 *   2. update_option('betheme') + refresco de $MFN_Options->options
 *   3. efectos secundarios (be_post_save): static.css, fichero JS del BeBuilder, cachés
 *
 * $flags: revision (bool, def true), revision_type (def 'backup'), static (def true),
 *         bebuilder (def true), dry (def false)
 * Devuelve el diff aplicado (o el que se aplicaría con dry=true).
 */
function be_opts_save( array $new, array $flags = [] ): array {
	$flags = array_merge(
		[ 'revision' => true, 'revision_type' => 'backup', 'static' => true, 'bebuilder' => true, 'dry' => false ],
		$flags
	);
	$old  = be_opts();
	$diff = be_diff( $old, $new );

	if ( $flags['dry'] ) {
		return $diff;
	}
	if ( be_diff_is_empty( $diff ) ) {
		be_msg( 'Sin cambios: no se escribe nada.' );
		return $diff;
	}

	$mfn = be_options_obj();

	if ( $flags['revision'] && $old ) {
		// set_revision() guarda base64(serialize($opciones)) con timestamp; conserva 5 (options.php:132-166)
		$mfn->set_revision( $flags['revision_type'], $old );
	}

	update_option( BE_OPT, $new );
	$mfn->options = $new;

	be_post_save( (bool) $flags['static'], (bool) $flags['bebuilder'] );

	return $diff;
}

/**
 * Efectos secundarios que el panel ejecuta tras guardar y que una escritura directa
 * en wp_options NO dispara (options.php:88-90 solo corre con ?settings-updated).
 */
function be_post_save( bool $static = true, bool $bebuilder = true ): array {
	$result = [];
	if ( $static ) {
		$result['static_css'] = be_static_css_regenerate();
	}
	if ( $bebuilder ) {
		$result['bebuilder_js_reset'] = be_bebuilder_reset();
	}
	if ( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all();
		$result['w3tc_flush'] = true;
	}
	wp_cache_flush();
	return $result;
}

/**
 * Regenera uploads/betheme/css/static.css (MFN_Options::_static_CSS(true), options.php:903-933).
 * Se escribe siempre, esté o no activa la opción `static-css`: así nunca queda un fichero
 * desactualizado que se serviría al reactivar la opción.
 */
function be_static_css_regenerate(): array {
	be_load_admin_classes();
	$mfn          = be_options_obj();
	$mfn->options = be_opts();
	$mfn->_static_CSS( true );
	$path = be_static_css_path();
	return [
		'path'    => $path,
		'bytes'   => file_exists( $path ) ? filesize( $path ) : 0,
		'enabled' => (bool) mfn_opts_get( 'static-css' ),
	];
}

function be_static_css_path(): string {
	$upload_dir = wp_upload_dir();
	return wp_normalize_path( $upload_dir['basedir'] . '/betheme/css/static.css' );
}

/** Contenido que tendría static.css ahora mismo, sin escribirlo (misma receta que _static_CSS) */
function be_static_css_build(): string {
	$mfn          = be_options_obj();
	$mfn->options = be_opts();
	return "/* theme options */\n" . mfn_styles_dynamic() . "\n\n/* custom CSS */\n" . mfn_styles_custom();
}

/**
 * Borra el JS de campos del BeBuilder (bebuilder-<version>.js) y cambia `betheme_form_uid`.
 * El admin lo regenera solo al abrir el builder (visual-builder-class.php:212).
 * Equivale a Tools → "Re-render Builder data" (MfnVisualBuilder::removeBeDataFile()).
 */
function be_bebuilder_reset(): bool {
	$file = get_template_directory() . '/visual-builder/assets/js/forms/bebuilder-' . be_theme_version() . '.js';
	if ( file_exists( $file ) ) {
		wp_delete_file( $file );
	}
	$uid = class_exists( 'Mfn_Builder_Helper' ) && method_exists( 'Mfn_Builder_Helper', 'unique_ID' )
		? Mfn_Builder_Helper::unique_ID()
		: uniqid();
	update_option( 'betheme_form_uid', $uid );
	return true;
}

// ---------------------------------------------------------------------------
// Valores
// ---------------------------------------------------------------------------

/** Interpreta un valor de línea de comandos: JSON si json=1, si no string tal cual */
function be_value_parse( $raw, bool $as_json ) {
	if ( ! $as_json ) {
		return (string) $raw;
	}
	$decoded = json_decode( (string) $raw, true );
	if ( json_last_error() !== JSON_ERROR_NONE ) {
		be_err( 'JSON inválido: ' . json_last_error_msg() );
	}
	return $decoded;
}

/** Valor que guarda un campo `upload` del panel: URL + '#' + id de adjunto (fields/upload/field_upload.js:31) */
function be_upload_value( int $attachment_id ): string {
	$url = wp_get_attachment_url( $attachment_id );
	if ( ! $url ) {
		be_err( "El adjunto $attachment_id no existe." );
	}
	return $url . '#' . $attachment_id;
}

/**
 * Importa un fichero local a la Media Library (equivale a `wp media import`).
 * Devuelve ['id' => int, 'url' => string, 'value' => 'URL#id'].
 */
function be_media_import( string $path, string $title = '' ): array {
	if ( ! is_readable( $path ) ) {
		be_err( "No se puede leer $path" );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( basename( $path ) );
	copy( $path, $tmp );
	$file = [ 'name' => basename( $path ), 'tmp_name' => $tmp ];
	$id   = media_handle_sideload( $file, 0, $title ?: null );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		be_err( 'media_handle_sideload: ' . $id->get_error_message() );
	}
	return [ 'id' => (int) $id, 'url' => wp_get_attachment_url( $id ), 'value' => be_upload_value( (int) $id ) ];
}

/** Rutas de uploads/betheme[/<sub>]: ['path' => ..., 'url' => ...] */
function be_uploads_betheme( string $sub = '' ): array {
	$upload_dir = wp_upload_dir();
	$sub        = $sub ? '/' . trim( $sub, '/' ) : '';
	return [
		'path' => wp_normalize_path( $upload_dir['basedir'] . '/betheme' . $sub ),
		'url'  => $upload_dir['baseurl'] . '/betheme' . $sub,
	];
}

function be_filesystem() {
	be_load_admin_classes();
	return Mfn_Helper::filesystem();
}
