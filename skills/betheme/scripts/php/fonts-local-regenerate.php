<?php
/**
 * fonts-local-regenerate.php — descarga las Google Fonts usadas y genera uploads/betheme/fonts/mfn-local-fonts.css.
 * Réplica del cuerpo de Mfn_Builder_Ajax::_tool_regenerate_fonts() (functions/builder/class-mfn-builder-ajax.php:274-430)
 * sin el nonce ni la capability (l.276-280), que impiden llamarlo desde wp-cli. Es lo que hace el botón
 * "Cache fonts" (Theme Options → Performance, campo ajax google-font-mode-regenerate, theme-options.php:11594)
 * y "Regenerate fonts" de Tools.
 * SAFETY: destructive (BORRA por completo uploads/betheme/fonts/ antes de descargar; exige yes=1)
 *
 * Uso: wp eval-file fonts-local-regenerate.php yes=1
 *      wp eval-file fonts-local-regenerate.php dry-run=1     (lista fuentes/pesos/subsets y qué se borraría; no toca nada)
 *
 * Correspondencia con el original (línea → paso):
 *   l.282       Mfn_Helper::filesystem()                        → be_filesystem()
 *   l.284-285   rutas uploads/betheme y /fonts (mfn_uploads_dir, theme-functions.php:3950)
 *   l.291-294   user-agent Firefox + timeout 30 para fonts.googleapis.com
 *   l.297-303   wp_mkdir_p de ambas carpetas
 *   l.307       $fonts = mfn_fonts_selected('builder_fonts')    (roles + mfn-page-fonts de todo el builder)
 *   l.308       $google_fonts = mfn_fonts('all')
 *   l.311-313   añade "Poppins" siempre (fuente por defecto del tema)
 *   l.316-319   añade button-font-family
 *   l.322-331   $subset (font-subset, def latin,latin-ext; se fuerzan ambos) y $weight (font-weight, def 400)
 *   l.334       $wp_filesystem->delete($path_fonts, true)       ← DESTRUCTIVO
 *   l.337       array_unique
 *   l.339-419   por fuente Google: mkdir; pesos (Poppins/Roboto/Open Sans añaden 400,500,600); por peso:
 *               GET https://fonts.googleapis.com/css?family=<slug>:<peso>&display=swap; parsea los bloques
 *               (comentario con el nombre del subset + @font-face{...}), se queda con los subsets elegidos, descarga cada fichero a
 *               <fonts>/<slug>/<slug>-<peso>-<subset>.<ext> y reescribe el src como './<slug>/<fichero>'
 *   l.422       mfn_styles_minify() (theme-head.php:1251)
 *   l.425       escribe <fonts>/mfn-local-fonts.css
 * El front lo encola en theme-head.php:501-502 solo si google-font-mode=local.
 */
require_once __DIR__ . '/_lib.php';
$a   = be_args( $args );
$dry = be_flag( $a, 'dry-run' );

$mode = mfn_opts_get( 'google-font-mode' );
if ( $mode !== 'local' ) {
	be_msg( "AVISO: google-font-mode='" . $mode . "' (no 'local'): el fichero se generará pero el front no lo encolará (theme-head.php:501)." );
}

// l.284-285
$path_be    = mfn_uploads_dir( 'basedir' );
$path_fonts = wp_normalize_path( $path_be . '/fonts' );
$fonts_dir  = mfn_uploads_dir( 'basedir', 'fonts' );

// l.291-294
$user_agent = [
	'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:104.0) Gecko/20100101 Firefox/104.0',
	'timeout'    => 30,
];

// l.307-319 (Mfn_Builder_Helper la carga el constructor de Mfn_Builder también en front, class-mfn-builder.php:29)
if ( ! class_exists( 'Mfn_Builder_Helper' ) ) {
	require_once get_template_directory() . '/functions/builder/class-mfn-builder-helper.php';
}
$fonts        = mfn_fonts_selected( 'builder_fonts' );
$google_fonts = mfn_fonts( 'all' );
if ( ! in_array( 'Poppins', $fonts, true ) ) {
	$fonts[] = 'Poppins';
}
$custom_button_font = mfn_opts_get( 'button-font-family' );
if ( ! in_array( $custom_button_font, $fonts, true ) ) {
	$fonts[] = $custom_button_font;
}

// l.322-331
$subset = mfn_opts_get( 'font-subset', [ 'latin', 'latin-ext' ] );
$weight = mfn_opts_get( 'font-weight', [ '400' ] );
$subset = array_values( array_filter( (array) $subset, fn( $v, $k ) => $k !== 'post-meta', ARRAY_FILTER_USE_BOTH ) );
$weight = array_values( array_filter( (array) $weight, fn( $v, $k ) => $k !== 'post-meta', ARRAY_FILTER_USE_BOTH ) );
if ( ! in_array( 'latin', $subset, true ) ) {
	$subset[] = 'latin';
}
if ( ! in_array( 'latin-ext', $subset, true ) ) {
	$subset[] = 'latin-ext';
}

// l.337
$fonts = array_values( array_unique( array_filter( (array) $fonts ) ) );
$to_download = array_values( array_filter( $fonts, fn( $f ) => in_array( $f, $google_fonts, true ) ) );
$skipped     = array_values( array_diff( $fonts, $to_download ) );

$plan = [
	'google_font_mode' => $mode,
	'fonts_dir'        => $fonts_dir,
	'delete_dir'       => file_exists( $path_fonts ) ? $path_fonts : '(no existe aún)',
	'fonts_google'     => $to_download,
	'fonts_skipped'    => $skipped,
	'weights'          => $weight,
	'subsets'          => $subset,
];

if ( $dry ) {
	be_out( [ 'dry_run' => true, 'plan' => $plan ] );
	exit( 0 );
}

be_require_yes( $a, "Regenerar fuentes locales (borra $path_fonts entero)" );

$wp_filesystem = be_filesystem();

// l.297-303
if ( ! file_exists( $path_be ) ) {
	wp_mkdir_p( $path_be );
}
if ( ! file_exists( $path_fonts ) ) {
	wp_mkdir_p( $path_fonts );
}

// l.334  ← DESTRUCTIVO
$wp_filesystem->delete( $path_fonts . '/', true, 'd' );
wp_mkdir_p( $path_fonts );

$content_of_css = '';
$files          = [];
$errors         = [];

// l.339-419
foreach ( $to_download as $font ) {

	$font_slug     = str_replace( ' ', '+', $font );
	$font_location = $fonts_dir . '/' . $font_slug;
	wp_mkdir_p( $font_location );

	// l.348-352: pesos extra para las fuentes "de sistema" del tema
	if ( in_array( $font_slug, [ 'Poppins', 'Roboto', 'Open Sans' ], true ) ) {
		$weight_set = array_unique( array_merge( $weight, [ 400, 500, 600 ] ) );
	} else {
		$weight_set = $weight;
	}

	foreach ( $weight_set as $item ) {

		// l.359-363
		$url_created = 'https://fonts.googleapis.com/css?family=' . $font_slug . ':' . $item . '&display=swap';
		$response    = wp_remote_get( $url_created, $user_agent );
		if ( is_wp_error( $response ) ) {
			$errors[] = "$font:$item → " . $response->get_error_message();
			continue;
		}
		$css = wp_remote_retrieve_body( $response );
		if ( empty( $css ) ) {
			// l.366: respuesta vacía = ese peso no existe para la fuente; se ignora
			continue;
		}

		// l.369-392: bloques "/* subset */ @font-face{...}" → nos quedamos con los subsets elegidos (sin duplicar)
		preg_match_all( '#/\*\s*([a-zA-Z0-9\-]+)\s*\*/\s*(@font-face\s*\{[^}]*\})#', $css, $blocks, PREG_SET_ORDER );
		$seen = [];
		$n    = 0;
		foreach ( $blocks as $b ) {
			$subset_name = $b[1];
			$face        = $b[2];
			if ( ! in_array( $subset_name, $subset, true ) || in_array( $subset_name, $seen, true ) ) {
				continue;
			}
			$seen[] = $subset_name;
			if ( ! preg_match( '#url\((https://[^)]+?\.(woff2|woff))\)#', $face, $m ) ) {
				continue;
			}
			$src_url   = $m[1];
			$extension = $m[2];

			// l.395-404: descarga a <fonts>/<slug>/<slug>-<peso>-<subset>.<ext>
			$file_resp = wp_remote_get( $src_url, $user_agent );
			$bin       = is_wp_error( $file_resp ) ? '' : wp_remote_retrieve_body( $file_resp );
			if ( $bin === '' ) {
				$errors[] = "$font:$item:$subset_name → no se pudo descargar $src_url";
				continue;
			}
			$file_name = $font_slug . '-' . $item . '-' . $subset_name . '.' . $extension;
			$location  = wp_normalize_path( $font_location . '/' . $file_name );
			$wp_filesystem->put_contents( $location, $bin, FS_CHMOD_FILE );
			$files[] = $location;

			// l.407-408: mismo @font-face con el src apuntando al fichero local
			$content_of_css .= str_replace( $src_url, "'./" . $font_slug . '/' . $file_name . "'", $face );
			$n++;
		}
		if ( $n === 0 ) {
			$errors[] = "$font:$item → CSS recibido pero sin bloques para subsets " . implode( ',', $subset );
		}
	}
}

// l.422-425
$content_of_css = mfn_styles_minify( $content_of_css );
$css_file       = wp_normalize_path( $fonts_dir . '/mfn-local-fonts.css' );
$wp_filesystem->put_contents( $css_file, $content_of_css, FS_CHMOD_FILE );

// static.css incluye style.php, que con mode=local omite el fallback de sistema (style.php:115-120): regenerar por coherencia
$static = be_static_css_regenerate();

be_out( [
	'dry_run' => false,
	'plan'    => $plan,
	'css'     => [ 'path' => $css_file, 'bytes' => file_exists( $css_file ) ? filesize( $css_file ) : 0, 'faces' => substr_count( $content_of_css, '@font-face' ) ],
	'files'   => count( $files ),
	'errors'  => $errors,
	'static_css' => $static,
] );
