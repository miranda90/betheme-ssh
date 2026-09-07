# 12 — Témini: ajustes propios del tema hijo

Qué añade Témini 1.1.0 sobre Betheme 28.4.3 y dónde vive cada ajuste (fichero, meta, transient), para
configurarlo por SSH. Témini **no guarda nada en `betheme` ni en theme_mods**: todo es código + postmeta.
Rutas relativas a `temini/` salvo que se indique `betheme/`.

## 1. Carga del tema (`functions.php`)

| Constante | Valor | Uso |
|---|---|---|
| `TEMINI_DIR` / `TEMINI_URI` | `get_stylesheet_directory[_uri]()` | rutas del hijo (`functions.php:17-18`) |
| `BETHEME_DIR` / `BETHEME_URI` | `get_template_directory[_uri]()` | rutas del padre (`:19-20`) |
| `TEMINI_INC_DIR`, `TEMINI_LANG_DIR` | `TEMINI_DIR/inc`, `TEMINI_DIR/languages` | `:21-22` |
| `TEMINI_WEBMASTER_ID` | `2` | único usuario que ve updates y avisos (`:24`); se puede fijar antes en `wp-config.php` (`defined() ||`) |

Autoload: `temini_require_inc_dir()` (`functions.php:31-47`) hace `require_once` de **todos los `*.php`** del primer
nivel de `inc/core`, `inc/utils`, `inc/shortcodes` (orden natural) y `inc/woocommerce` solo si `class_exists('WooCommerce')`
(`:52-58`). Consecuencia: cualquier fichero `.php` que dejes en esas carpetas se ejecuta; un `.php.bak-…` no (no termina en `.php`).

## 2. CSS/JS del front y del admin (`inc/core/temini-setup.php`)

`temini_enqueue_scripts_styles()` en `wp_enqueue_scripts` **prioridad 101** (`temini-setup.php:55-77`), es decir, después de todo lo de Betheme:

| Paso | Handle | Fichero | Deps | Cita |
|---|---|---|---|---|
| RTL | `mfn-rtl` | `betheme/rtl.css` (solo `is_rtl()`) | — | `:56-58` |
| dequeue | `style` | se quita el handle `style` (hoja del hijo que encola el padre) | — | `:60` |
| 1 | `temini-style` | `style.css` (solo cabecera) | — | `:62` |
| 2 | `temini-tokens` | `assets/css/tokens.css` (variables `:root`) | `temini-style` | `:63` |
| 3 | `temini-theme` | `assets/css/theme.css` (componentes) | `temini-tokens` | `:64` |
| JS | `temini-main` | `assets/js/main.js` (footer, dep `jquery`) | — | `:68-75` |
| admin | `temini-admin` | `assets/css/admin.css` en `admin_enqueue_scripts` | — | `:82-95` |

Cada fichero se encola **solo si existe y pesa > 0 bytes** (`temini_enqueue_theme_stylesheet()`, `:37-50`); la versión es `filemtime`,
así que al editar por SSH el cache-busting es automático. Los shortcodes añaden `na-proyectos-destacados` y `na-slider-servicios` con dep `temini-theme`
(`inc/shortcodes/proyectos-destacados.php:167-172`, `slider-servicios.php:216-221`). Convención de dónde va cada CSS: `assets/css/README.md`.

## 3. Textdomains y textos legales

- `mfn-opts`, `betheme`, `temini` → `load_child_theme_textdomain()` desde `languages/` en `init` prio 10 (`temini-setup.php:7-12`);
  el padre carga los suyos en `after_setup_theme` (`betheme/functions.php:33-34`), el hijo los complementa (`languages/temini/es_ES.l10n.php`).
- `legal-texts` (textos de aviso legal y accesibilidad) → `load_textdomain()` con ruta `.mo`; WP ≥ 6.5 usa el `.l10n.php` emparejado
  (`languages/legal-texts/<locale>.l10n.php`: ca, es_ES, fr_FR, gl_ES, it_IT, pt_PT) en `init` prio 20 (`:17-24`).
  Con WPML se recarga en `wpml_language_has_switched` (`unload_textdomain` + carga, `:25-28`).
- Añadir un idioma: compilar `legal-texts.pot` a `<locale>.po` y generar `.l10n.php` con `wp i18n make-php languages/legal-texts/`.

## 4. Updates y avisos ocultos salvo al webmaster

- `pre_site_transient_update_core` devuelve un objeto vacío para todo usuario ≠ `TEMINI_WEBMASTER_ID` (`temini-setup.php:100-113`): el core "no tiene updates".
- CSS inline `.update-nag, .updated, .is-dismissible, .notice-info, #duplicate-post-notice {display:none}` en admin y login (`:115-122`).
- **Trampa en wp-cli:** sin `--user`, `get_current_user_id()` es 0 ≠ 2, el filtro también se aplica y `wp core check-update` / `wp core update`
  responden que no hay actualizaciones. Ejecuta esos comandos con `--user=<TEMINI_WEBMASTER_ID>` (en el dev, `--user=2`, login `webmaster`).

## 5. White-label del admin (`inc/core/temini-customize.php`)

| Filtro / hook | Qué devuelve | Dónde lo usa Betheme | Cita hijo |
|---|---|---|---|
| `betheme_label` | `Name` de `style.css` + espacio (`Témini `) | menú y breadcrumbs (`betheme/functions/admin/class-mfn-dashboard.php:335`), título del builder (`visual-builder/visual-builder-header.php:20`) | `temini-customize.php:7-14` |
| `betheme_logo` | `<img class="betheme-custom-logo" src="images/isotipo-invbit.svg">` | cabecera del panel (`functions/admin/templates/parts/header.php:25`) | `:19-24` |
| `betheme_logo_nohtml` | URL del SVG | icono del menú (`class-mfn-dashboard.php:338`), botón "Edit with …Builder" | `:29-34` |
| `admin_footer_text` | «Developed by Invbit» | pie del admin | `:39-47` |
| `update_footer` (prio 11) | `WordPress X - Betheme Y - Témini Z` | pie derecho | `:52-76` |
| `wp_before_admin_bar_render` | quita el nodo `wp-logo` | barra superior | `:81-86` |

No toca `betheme_slug` (slug de la URL `?action=mfn-live-builder`, `betheme/visual-builder/visual-builder.php:104`) ni `WHITE_LABEL`
(`betheme/functions.php:25`, oculta el logo del todo, `header.php:24`). Para eso, ver [03-theme-options-por-pestaña.md](03-theme-options-por-pestaña.md).
El nombre que se muestra sale de `style.css:2` (`Theme Name`); la versión, de `style.css:8`.

## 6. CPT `portfolio` reetiquetado como «Proyectos» (`inc/core/temini-proyectos.php`)

La clave interna sigue siendo `portfolio` / `portfolio-types` (BeBuilder depende de ella). Cambia lo público:

| Qué | Cómo | Cita |
|---|---|---|
| URL single `/proyectos/<slug>/` | `register_post_type_args` prio 20 fuerza `rewrite.slug = TEMINI_PROYECTOS_SLUG` | `:20`, `:34-46` |
| URL taxonomía `/tipos-de-proyecto/<slug>/` | `register_taxonomy_args` prio 20 | `:25`, `:55-67` |
| Etiquetas CPT / taxonomía en castellano | `post_type_labels_portfolio`, `taxonomy_labels_portfolio-types` | `:75-117`, `:125-156` |
| Columnas del listado | `manage_edit-portfolio_columns` (renombra `portfolio_*`, añade `temini_localizacion` tras `title`) | `:164-179`, `:343-372` |
| Cadenas «Portfolio…» del panel Betheme | filtro `gettext` prio 20, **solo dominio `mfn-opts` y solo en admin** (`:236`) con el mapa de `:196-222` | `:189-244` |
| Meta `_temini_proyecto_localizacion` | `register_post_meta` (string, single, `show_in_rest`) | `:249`, `:256-269` |
| Espejo ACF → meta | en `acf/save_post` prio 20 copia `ubicacion` a `_temini_proyecto_localizacion` (o la borra si vacío) | `:295-319` |

**Trampa verificada:** las opciones `portfolio-slug` / `portfolio-tax` de Theme Options (`betheme/functions/post-types/class-mfn-post-type-portfolio.php:535-536`,
que llegan a `register_post_type` en `:580,585` y a la taxonomía en `:587,593`) quedan **ignoradas**: el filtro del hijo las pisa. En el dev
`portfolio-slug` vale `proyectos` y la URL sale igual aunque valga otra cosa. Tras tocar cualquier slug: `wp rewrite flush`.

**Trampa:** el espejo solo corre en `acf/save_post`. Si escribes `ubicacion` por SSH con `wp post meta update`, hay que actualizar también
`_temini_proyecto_localizacion` (es lo que lee la plantilla BeBuilder del single vía `{postmeta:…}` y la columna del listado, `:284-290`).

```bash
# Localización de un proyecto por SSH (ACF + espejo), y comprobación REST
wp post meta update 176 ubicacion 'Cadarache, Provenza, Francia'
wp post meta update 176 _ubicacion field_temini_proyecto_ubicacion
wp post meta update 176 _temini_proyecto_localizacion 'Cadarache, Provenza, Francia'
wp eval 'echo temini_proyectos_get_localizacion(176), PHP_EOL;'
```

## 7. Grupos ACF locales (`inc/core/temini-campos-acf.php`)

Registrados con `acf_add_local_field_group()` en `acf/init` (`:20-21`, `:299`): viajan con el tema, no existen en la BD y no se editan
desde la UI de ACF. Sin ACF activo, no se registra nada y los shortcodes leen vacío (`function_exists('get_field')`).

| Grupo (`key`) | Campo `name` | `field_key` | Tipo ACF | Forma guardada | Ubicación | Cita |
|---|---|---|---|---|---|---|
| `group_temini_home_destacados` | `proyectos_destacados` | `field_temini_proyectos_destacados` | relationship (`portfolio`, máx. 6, `return_format=id`) | array serializado de IDs (strings) | `page_type == front_page` | `:26-55` |
| `group_temini_proyecto_datos` | `imagen_hero` | `field_temini_proyecto_imagen_hero` | image (id) | attachment id | `post_type == portfolio` | `:75-82` |
| | `titulo_hero` | `field_temini_proyecto_titulo_hero` | textarea | string | | `:84-91` |
| | `cliente_final` | `field_temini_proyecto_cliente_final` | text | string | | `:93-98` |
| | `ubicacion` | `field_temini_proyecto_ubicacion` | text | string (+ espejo, §6) | | `:100-105` |
| | `periodo_ejecucion` | `field_temini_proyecto_periodo` | text | string | | `:107-112` |
| | `logotipo` | `field_temini_proyecto_logotipo` | image (id) | attachment id | | `:114-121` |
| | `reto_titulo` | `field_temini_proyecto_reto_titulo` | text | string | | `:132-137` |
| | `reto_bloque_izq` | `field_temini_proyecto_reto_izq` | wysiwyg | HTML | | `:139-146` |
| | `reto_bloque_der` | `field_temini_proyecto_reto_der` | wysiwyg | HTML | | `:148-155` |
| | `reto_video` | `field_temini_proyecto_reto_video` | file (mp4/webm, `return_format=url`) | attachment id | | `:157-165` |
| | `reto_video_url` | `field_temini_proyecto_reto_video_url` | text | URL | | `:167-172` |
| | `imagen_grande` | `field_temini_proyecto_imagen_grande` | image (id) | attachment id | | `:183-190` |
| `group_temini_home_slider_servicios` | `slider_titulo` | `field_temini_slider_titulo` | textarea | string | `page_type == front_page` | `:212-218` |
| | `slider_descripcion` | `field_temini_slider_descripcion` | textarea | string | | `:220-226` |
| | `slider_servicios` | `field_temini_slider_servicios` | post_object (`page`, múltiple, id) | array serializado de IDs (strings) | | `:228-237` |
| `group_temini_servicio_slider` | `slider_descripcion_servicio` | `field_temini_slider_descripcion_servicio` | textarea | string | `page_parent == 95` | `:259-265` |
| | `slider_video` | `field_temini_slider_video` | file (mp4/webm, url) | attachment id | | `:267-275` |
| | `slider_video_url` | `field_temini_slider_video_url` | text | URL o ruta `/wp-content/…` | | `:277-282` |
| `group_popup_tmpl_by_page` (§8) | `paginas_mostradas` | `field_paginas_mostradas` | post_object (`page`, múltiple, id) | array serializado de IDs (strings) | `post_type == template` | `popup-tmpl-by-page.php:21-75` |

Los campos de tipo `tab` (`:68-73`, `:125-130`, `:176-181`) no persisten.

**Pareja valor / `_campo`.** ACF guarda cada campo como **dos** postmeta: `<name>` = valor y `_<name>` = `field_key`. Sin la segunda,
`get_field()` no sabe qué tipo es el campo y devuelve el valor crudo (un relationship saldría como string serializado, una imagen sin formatear)
y el editor no lo muestra. Por SSH escribe siempre las dos:

```bash
# Escalar
wp post meta update 176 cliente_final 'ITER Organization / Fusion for Energy'
wp post meta update 176 _cliente_final field_temini_proyecto_cliente_final
# Array (relationship / post_object múltiple): ACF guarda IDs como strings
wp post meta update 28 proyectos_destacados '["132","140","138","146"]' --format=json
wp post meta update 28 _proyectos_destacados field_temini_proyectos_destacados
# Equivalente con la API de ACF (resuelve la pareja sola; acepta name o field_key)
wp eval 'update_field("field_temini_proyectos_destacados", [132,140,138,146], 28);'
# Leer como lo hace el tema
wp eval 'var_export( get_field("proyectos_destacados", 28) );'
```

**IDs fijos en código:** la ubicación del grupo de servicios es `page_parent == 95` (`:289-291`, «Servicios», con nota WPML en `:284-285`), el fallback
de `[slider_servicios]` son las hijas de la 95 (`slider-servicios.php:71-81`) y el botón de `[proyectos_destacados]` enlaza a la página 100 (`proyectos-destacados.php:77`).
En otro sitio hay que cambiar esos números (o pasarlos por `wpml_object_id`, que ya se aplica).

## 8. Popup por página (`inc/utils/popup-tmpl-by-page.php`)

Alternativa a las condiciones del builder para los templates de tipo popup (`mfn_template_type = popup`, ver [10-plantillas-header-footer-popups.md](10-plantillas-header-footer-popups.md)):

1. Campo ACF `paginas_mostradas` (metabox lateral «Dónde mostrar», `:21-75`; `wpml_cf_preferences => 1`, en ACFML 1 = copiar a traducciones **[sin verificar]**, `:40`) en **todos** los templates (`:58-60`), aunque solo se lee en popups.
2. Índice `[page_id => [popup_id, …]]` construido leyendo `paginas_mostradas` de cada popup **publicado** (`:147-167`, `:174-210`; primero `get_post_meta`, `:183`, y `get_field` de respaldo, `:185-187`).
3. Cacheado en el transient `temini_popup_by_page_v1` + sufijo `_<ICL_LANGUAGE_CODE>` si WPML (`:9`, `:81-89`), 1 día (`:224`).
4. En `wp_footer` prio 5 (`:268`), en singulares, instancia `new MfnPopup($id)->render()` (`betheme/functions/modules/class-mfn-popup.php:7,97`) para cada popup de la página que Betheme no haya pintado ya por condiciones (`mfn_addons_ID('popup')`, `betheme/functions/theme-functions.php:1047`; `:296-305`).

Invalidación: `temini_flush_popup_page_map_cache()` (`:94-106`) borra la clave actual y una por idioma activo de WPML; se dispara en `save_post_template` y `acf/save_post`
**solo si el template es popup** (`:108-140`). Escribir la meta por SSH **no** dispara nada: hay que borrar el transient a mano (lo hace el script).

```bash
# Ver popups y sus páginas (RO)
scripts/be-temini-popup-set.sh --list
# Asignar / quitar (reversible: la salida muestra el valor anterior)
scripts/be-temini-popup-set.sh template=210 pages=28,95 --dry-run
scripts/be-temini-popup-set.sh template=210 pages=28,95
scripts/be-temini-popup-set.sh template=210 pages=
# wp puro
wp eval 'update_field("field_paginas_mostradas", [28,95], 210);'
wp transient delete --all   # o solo los del mapa:
wp db query "DELETE FROM $(wp db prefix)options WHERE option_name LIKE '\_transient%temini\_popup\_by\_page\_v1%'"
```

**Trampas verificadas:** (a) el popup debe estar `publish` (`:151`); (b) en wp-cli `ICL_LANGUAGE_CODE` es el idioma por defecto, por eso el script borra por `LIKE` en `wp_options`
y además `wp_cache_flush()` (con object cache persistente el transient no está en la tabla); (c) el guard `Mfn_Builder_Front::$is_bebuilder` (`:278`) no existe en Betheme 28.4.3
(ninguna aparición en `betheme/`), así que no impide nada; (d) si ACF está inactivo, el render se salta (`:282`) aunque la meta exista.

## 9. Metas de las páginas legales (`inc/utils/aviso-legal-meta.php`, `accesibilidad-meta.php`)

| Plantilla (`_wp_page_template`) | `Template Name` | Metas (todas string, `sanitize_text_field`) | Metabox | Dónde se imprimen |
|---|---|---|---|---|
| `aviso-legal.php` | «Legal Warning» (`aviso-legal.php:3`) | `razon_social`, `nombre_comercial`, `direccion`, `identificacion_tipo` (∈ `CIF,NIF,NIE,DNI`, `aviso-legal-meta.php:37-44`), `identificacion_numero`, `correo_electronico`, `juzgado` (`:25-35`) | `add_meta_boxes_page` solo con esa plantilla (`:11-12`) | `aviso-legal.php:39-47` |
| `accesibilidad.php` | «Accesibility Statement» (`accesibilidad.php:3`) | `fecha_de_revision` (input `type=date` → `YYYY-MM-DD`, `accesibilidad-meta.php:31`) | `:11-12` | `accesibilidad.php:38,92-94` (se imprime tal cual, sin formatear) |

Comportamiento del guardado en admin que conviene imitar: valor vacío o tipo no permitido ⇒ `delete_post_meta` (`aviso-legal-meta.php:113-121`); si la página deja de usar la plantilla,
**se borran todas las metas** al guardar (`:96-101`, `accesibilidad-meta.php:41-45`). Ambas plantillas pintan primero el BeBuilder de la página y luego el texto legal con los metas;
los comentarios dependen de `page-comments` de Theme Options (`aviso-legal.php:195`). WPML: `wpml-config.xml:3-12` marca las 8 metas como `action="copy"`
(WPML las copia del original a la traducción al guardar ésta **[sin verificar]**; por SSH usa `translations=1` para escribir en todos los idiomas).

```bash
scripts/be-temini-legal-set.sh post=79 --show                                  # RO: plantilla + valores
scripts/be-temini-legal-set.sh post=79 razon_social='Mi Empresa, S.L.' identificacion_tipo=CIF \
  identificacion_numero=B00000000 correo_electronico=info@example.com juzgado='A Coruña' --dry-run
scripts/be-temini-legal-set.sh post=79 razon_social='Mi Empresa, S.L.' nombre_comercial='Mi Empresa' \
  direccion='Rúa X 1, 15001 A Coruña' identificacion_tipo=CIF identificacion_numero=B00000000 \
  correo_electronico=info@example.com juzgado='A Coruña' translations=1
scripts/be-temini-legal-set.sh post=81 fecha_de_revision=2026-09-01
# wp puro
wp post meta update 79 razon_social 'Mi Empresa, S.L.'
wp post meta delete 79 juzgado
wp post update 79 --page_template=aviso-legal.php   # asignar la plantilla si aún no la tiene
```

## 10. Redirección de la categoría por defecto (`inc/utils/default-category-redirect.php`)

En `template_redirect`: si la vista es la categoría `default_category`, no hay `page_for_posts` y no existe ninguna entrada en ningún estado
(`:9-25`), redirige 301 a la home (`:27`). Se apaga sola al crear la primera entrada o asignar página de entradas: `wp option update page_for_posts <id>`.

## 11. Shortcodes (`inc/shortcodes/`)

| Shortcode | Lee | Cita |
|---|---|---|
| `[redes]` | Theme Options `social-attr` (`{blank,nofollow}` → `target/rel`, `redes.php:9-18`), `social-link` (array `{red: url, …, order: csv}`; recorre `order`, `:24-34`), `social-custom-icon/-link/-title[-N]` (`:45-47`), `social-rss` (`:52`); títulos/iconos de `mfna_social()` (`betheme/muffin-options/theme-options.php:667`). Ids en [03](03-theme-options-por-pestaña.md) / `reference/catalogo-opciones.md`, sección Social (`theme-options.php:7382-7383`). | `redes.php:7-81` |
| `[proyectos_destacados ids="" count="4"]` | `ids` del atributo, si no ACF `proyectos_destacados` de la portada (`wpml_object_id`), si no últimos `portfolio` con miniatura (`:34-53`); filtra publicados con thumbnail (`:66`); término de `portfolio-types` (`:117`), ACF `logotipo` (`:124`), extracto; botón → página 100 (`:77`). Encola el Swiper del padre (`:161-162`). | `proyectos-destacados.php:23-152` |
| `[slider_servicios]` | ACF de portada `slider_titulo`, `slider_descripcion`, `slider_servicios` (`:64-66`); fallback hijas de la 95 por `menu_order` (`:71-81`); por servicio `slider_descripcion_servicio` (`:111`) y vídeo `slider_video` > `slider_video_url` (rutas `/…` → `home_url`, `:27-53`). | `slider-servicios.php:60-204` |

```bash
# Cambiar las redes que pinta [redes] (mismas opciones que el footer de Betheme)
scripts/be-opt-set.sh key=social-link --json value='{"facebook":"https://facebook.com/x","linkedin":"https://linkedin.com/company/x","order":"linkedin,facebook"}'
scripts/be-opt-set.sh key=social-attr --json value='{"blank":"blank","nofollow":"nofollow"}'
```

## 12. Qué NO guarda Témini y ficheros a limpiar

- Ninguna `option` propia, ningún `theme_mod`, ningún CPT nuevo, ninguna tabla. Estado persistente = postmeta (§6-9) + el transient de §8. Un `wp option list --search='temini%'` debe salir vacío.
- Todo lo demás es código: cambiar colores/tipografía de marca = editar `assets/css/tokens.css`; IDs fijos (95, 100, `TEMINI_WEBMASTER_ID`) = editar PHP.
- Copias de seguridad encontradas en el dev (no están en el repo; bórralas cuando la versión actual esté validada; no se cargan porque no acaban en `.php`):
  `assets/css/tokens.css.bak-20260819`, `assets/css/tokens.css.bak-20260820`, `assets/css/theme.css.bak-20260820`,
  `inc/core/temini-proyectos.php.bak-20260825-134246`, `inc/core/temini-campos-acf.php.bak-20260825-134246`.

```bash
cd wp-content/themes/temini && find . -name '*.bak-*' -print          # revisar
cd wp-content/themes/temini && find . -name '*.bak-*' -delete         # limpiar
```

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-temini-popup-set list=1` lista popups y el transient `_transient_temini_popup_by_page_v1_es`. Con un popup de prueba (template 184): `template=184 pages=28,95` escribió `paginas_mostradas = ["28","95"]` y `_paginas_mostradas = field_paginas_mostradas` vía `update_field()` (método `acf:update_field`); `temini_get_popup_ids_for_page(28)` devolvió `[184]`; el transient se invalidó (lo borra el propio `acf/save_post` del tema, `rows_deleted = 0` en el script). `pages=` vacío y `wp post delete --force` lo dejaron limpio.
- `be-temini-legal-set post=79 --show` muestra los 7 campos de aviso legal vacíos (plantilla `aviso-legal.php`); `razon_social="Prueba, S.L." identificacion_tipo=CIF identificacion_numero=B00000000` se escribieron y aparecieron en el front (cache-busted); vaciarlos borró las metas. No hay página con `accesibilidad.php` en el dev, así que `fecha_de_revision` no se probó.
