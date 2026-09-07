# 11 — CPTs de Betheme y importador de demos

Los post types que registra Betheme (y sus metas por tipo), cómo crearlos/editarlos por SSH, y el Setup Wizard / Pre-built websites
(importador de demos, **DESTRUCTIVO**). Las plantillas (`template`) están en [10](10-plantillas-header-footer-popups.md) y los
icon packs (`icons`) en [06](06-iconos.md).

## 1. CPTs y gates

Todos se cargan desde `betheme/functions.php:115-150` según `post-type-disable` (checkbox, Theme Options → Theme functions;
`{'client':'client',...}` = **desactivado**; el dev desactiva client, layout, offer, slide, testimonial) y `theme-disable`.

| CPT | Registro | Taxonomía | Gate (`functions.php`) | Notas |
|---|---|---|---|---|
| `template` | `class-mfn-post-type-template.php:2212` | — | `post-type-disable[template]` l.122-124 | `public=false`, ver 10 |
| `portfolio` | `class-mfn-post-type-portfolio.php:585` (`register()` l.533) | `portfolio-types` l.587 | `[portfolio]` l.131-133 | `public=true`; slugs desde options `portfolio-slug` (def. `portfolio-item`) y `portfolio-tax` (def. `portfolio-types`), l.535-536 |
| `client` | `class-mfn-post-type-client.php:114` | `client-types` l.116 | `[client]` l.125-127 | `public=false`, supports title/thumbnail/page-attributes |
| `offer` | `class-mfn-post-type-offer.php:128` | `offer-types` l.130 | `[offer]` l.128-130 | `public=false`, supports editor/thumbnail/title/page-attributes |
| `slide` | `class-mfn-post-type-slide.php:129` | `slide-types` l.131 | `[slide]` l.134-136 | `public=false` |
| `testimonial` | `class-mfn-post-type-testimonial.php:115` | `testimonial-types` l.117 | `[testimonial]` l.137-139 | `public=false` |
| `layout` (deprecated) | `class-mfn-post-type-layout.php:279` | — | `[layout]` l.141-143 | solo se registra si ya existen layouts publicados (`SELECT COUNT(*)`, l.271-279): no se pueden crear nuevos desde admin |
| `icons` | `class-mfn-post-type-icons.php:138` | — | `theme-disable[custom-icons]` l.119-121 | ver 06 |
| `product` (Woo) | solo metas: `class-mfn-post-type-product.php` | — | `function_exists('is_woocommerce')` l.145-147 | |
| `page`, `post` | solo metas: `class-mfn-post-type-page.php`, `class-mfn-post-type-post.php` | — | siempre l.149-150 | |

Cambiar el gate: `be-opt-set.sh key=post-type-disable --json value='{"client":"client","layout":"layout"}'` (ver [02](02-tipos-de-campo-y-valores.md)).
Un CPT desactivado sigue en la BD; solo deja de registrarse (y de aparecer en admin y en el front).

## 2. Metas por tipo

Todas las metas las define `set_fields()` de cada clase y las guarda `Mfn_Post_Type::save_box()`
(`betheme/functions/post-types/class-mfn-post-type.php:253-320`): nonce `mfn-builder-nonce` (l.257-263) y, por cada campo con `id`,
`update_post_meta` con el valor posteado tal cual o `delete_post_meta` si llega vacío (l.300-318). Por SSH se escriben con
`wp post meta update`; la forma del valor por tipo de campo es la de [02](02-tipos-de-campo-y-valores.md) (`switch` = `1`/`0`,
`upload` = URL o `URL#id`, `checkbox` = array `{k:k}`).

### 2.1 Comunes a page / post / portfolio (`class-mfn-post-type-page.php:65-333`, `class-mfn-post-type-post.php:113-447`, `class-mfn-post-type-portfolio.php:125-506`)

| Meta | Tipo | Qué es |
|---|---|---|
| `mfn_header_template`, `mfn_footer_template`, `mfn_popup_included` | select (ID de template, `0` = default) | overrides de plantilla (ver 10 §5) |
| `mfn_single-post_template` (post, portfolio) | select | plantilla single-post / single-portfolio para ese post |
| `mfn-post-hide-content` | switch | ocultar el contenido |
| `mfn-post-layout` | radio_img | layout (`no-sidebar`, `left-sidebar`, `right-sidebar`, `both-sidebars`) |
| `mfn-post-sidebar`, `mfn-post-sidebar2` | select (id de sidebar de `sidebars`) | sidebar 1 y 2 |
| `mfn-post-template` (post, portfolio) | radio_img | estilo del single |
| `_thumbnail_id` | upload | imagen destacada (WP nativo) |
| `mfn-post-slider`, `mfn-post-slider-layer`, `mfn-post-slider-shortcode` (page) | select / text | Revolution / Layer Slider / shortcode |
| `mfn-post-header-bg` (post, portfolio), `mfn-post-subheader-image` | upload | imagen de cabecera / subheader |
| `mfn-post-video`, `mfn-post-video-mp4` (post, portfolio) | text / upload | vídeo destacado |
| `mfn-post-one-page` (page) | switch | modo One Page |
| `mfn-post-full-width`, `mfn-post-hide-title`, `mfn-post-remove-padding` | switch | ancho completo / ocultar subheader / sin padding superior |
| `mfn-post-custom-layout` (page, portfolio), `mfn-post-menu` (page) | select | Layout (CPT `layout`) / menú propio |
| `mfn-post-hide-image`, `mfn-post-link`, `mfn-post-bg`, `mfn-post-intro` (post) | switch / text / color / checkbox | ocultar destacada, link externo, color de fondo, opciones intro |
| `mfn-post-client`, `mfn-post-link`, `mfn-post-task`, `mfn-post-slider-header`, `mfn-post-bg`, `mfn-post-bg-hover`, `mfn-post-size`, `mfn-post-intro` (portfolio) | text / switch / upload / color / select / checkbox | cliente, web, tarea, slider en cabecera, fondo del item, color hover, tamaño del item, intro |
| `mfn-meta-seo-title`, `-description`, `-keywords`, `-og-image` | text / upload | SEO propio de Betheme |
| `mfn-post-css`, `mfn-post-js` | textarea | CSS/JS de la página |
| `mfn-page-items`, `mfn-page-object`, `mfn-page-local-style`, `mfn-page-fonts` | builder | contenido BeBuilder (ver 10 §2 y muffinAI) |

### 2.2 Resto de tipos

| CPT | Metas (`id` | tipo | significado) |
|---|---|
| `product` (`class-mfn-post-type-product.php:60-141`) | `_thumbnail_id`; `mfn_header_template`, `mfn_footer_template`; `mfn_single_product_template` (select); `mfn_product_labels` (text, etiqueta extra), `mfn_product_labels_color`, `_bg_color`, `_border_color` (color); `mfn-post-js` |
| `client` (`class-mfn-post-type-client.php:58-73`) | `mfn-post-link` (text), `mfn-post-target` (switch, abrir en nueva pestaña); imagen = `_thumbnail_id` |
| `offer` (`class-mfn-post-type-offer.php:58-91`) | `mfn-post-link_title` (texto del botón), `mfn-post-link`, `mfn-post-target` (switch), `mfn-post-thumbnail` (upload) |
| `slide` (`class-mfn-post-type-slide.php:58-91`) | `mfn-post-mp4` (upload vídeo), `mfn-post-link`, `mfn-post-target` (switch), `mfn-post-desc` (textarea) |
| `testimonial` (`class-mfn-post-type-testimonial.php:58-78`) | `mfn-post-author`, `mfn-post-company`, `mfn-post-link` (text); el texto va en `post_content` |
| `layout` (`class-mfn-post-type-layout.php:59-232`) | `mfn-post-layout` (radio_img), `mfn-post-bg` (upload), `mfn-post-bg-pos` (select); logos `mfn-post-logo-img`, `-retina-logo-img`, `-sticky-logo-img`, `-sticky-retina-logo-img`, `-responsive-logo-img`, `-responsive-retina-logo-img`, `-responsive-sticky-logo-img`, `-responsive-sticky-retina-logo-img` (upload, ver 04); `mfn-post-header-style` (radio_img), `mfn-post-header-height` (text), `mfn-post-sticky-header` (switch), `mfn-post-sticky-header-style`, `mfn-post-skin` (select), `mfn-post-background-subheader`, `mfn-post-color-subheader` (color) |

## 3. Receta: crear y editar por SSH

```bash
# testimonio
ID=$(wp post create --post_type=testimonial --post_status=publish --post_title='Ana G.' --post_content='Muy contentos.' --porcelain)
wp post meta update $ID mfn-post-author 'Ana G.'; wp post meta update $ID mfn-post-company 'Acme'
wp post term add $ID testimonial-types portada
# proyecto de portfolio con destacada y categoría
ID=$(wp post create --post_type=portfolio --post_status=publish --post_title='Casa X' --porcelain)
AT=$(wp media import /tmp/casa.jpg --porcelain); wp post meta update $ID _thumbnail_id $AT
wp post meta update $ID mfn-post-client 'Cliente'; wp post term add $ID portfolio-types vivienda
# layout de página / sidebar
wp post meta update 128 mfn-post-layout right-sidebar; wp post meta update 128 mfn-post-sidebar 'Sidebar'
wp post meta list 128 --keys=mfn-post-layout,mfn-post-sidebar,mfn_header_template
```
En Témini el portfolio está reetiquetado como "Proyectos" y tiene ACF propio: ver [12](12-temini-ajustes-propios.md).
Layouts: al ser deprecated y no registrarse sin layouts previos, un `wp post create --post_type=layout` funciona (WP no valida el tipo)
pero el CPT solo aparecerá en admin tras publicar el primero.

## 4. Importador de demos (Setup Wizard / Pre-built websites) — DESTRUCTIVO

### 4.1 Qué hace el panel

Betheme → Setup Wizard (`betheme/functions/admin/setup/class-mfn-setup.php`) y Pre-built websites (`functions/importer/class-mfn-importer.php`,
oculto si `theme-disable[demo-data]` o white-label, l.25-29). El JS (`functions/admin/setup/assets/setup.js`) encadena endpoints AJAX
con nonce `mfn-setup` (registro l.88-103): `mfn_setup_database_reset` → `_plugin_install/_activate` → `_download` → `_content` →
`_options` → `_slider` → `_settings`. Handlers y lo que llaman en `Mfn_Importer_Helper` (`functions/importer/class-mfn-importer-helper.php`):

| Paso | Handler (`class-mfn-setup.php`) | Método helper | Efecto |
|---|---|---|---|
| Reset | `_database_reset()` l.607 | `database_reset($remove_media)` l.64-146 | ver 4.3 |
| Descarga | `_download_package()` l.682 | `download_package()` l.153 → `Mfn_Importer_API::remote_get_demo()` (`class-mfn-importer-api.php:82-170`) | GET `https://api.muffingroup.com/websites/download.php?code=<purchase>&demo=<id>` (`class-mfn-api.php:23`, l.89, timeout 30 s l.93), zip a `uploads/betheme/websites/<demo>/<demo>.zip`, `unzip_file` l.140 (fallback `ZipArchive` l.151) → `uploads/betheme/websites/<demo>/<demo>/` (`content.xml.gz`, `options.txt`, `menu.txt`, `widget_data.json`, sliders) |
| Contenido | `_content()` l.710 | con `complete_import`: `remove_menus()` l.1319 (DELETE de `nav_menu_item`); `content($attachments)` l.202 → `custom_import()` l.238 (parser XML propio, adjuntos solo si `attachments=1`) + `replace_builder()` l.1488 (reemplaza URLs en `mfn-page-items`/`mfn-page-local-style`, regenera `uid`) | posts, terms, menús, adjuntos |
| Opciones | `_options()` l.748 | `options()` l.570: `options.txt` = base64(serialize) → quita `google-*`/`facebook-pixel` (l.600), reemplaza URLs, **`update_option('betheme')` sin validar** (l.620), atributos Woo, conditions de header/footer buscando la plantilla **por título** (l.653 y ss.), `be_classes`/`be_classes_fonts`/`be_css_variables` (l.702), popups (l.714); con `complete_import`: `menu()` l.979 (`nav_menu_locations`), `widgets()` l.1015 | Theme Options, menús, widgets |
| Slider | `_slider()` l.803 | `slider()` l.1038 (solo demos con plugin `rev`) | RevSlider import |
| Ajustes | `_settings()` l.831 | blogname/blogdescription por `$wpdb->update` (l.860-869); con `complete_import`: `set_pages()` l.1114 (`show_on_front`, `page_on_front=Home`, `page_for_posts=Blog`, páginas Woo, tamaños de imagen l.1163) y `regenerate_CSS()` l.1193 (`generate_css` por post si la demo trae `theme_version`, si no `bebuilder_data_updater()`); `delete_temp_dir()` l.183 + `flush_rewrite_rules(false)` (l.922-929) | portada, CSS, limpieza |

Catálogo: `functions/importer/demos.php` (`$demos` l.441; por demo `name, url, layouts, categories, plugins, revslider, wrapper,
theme_version, pages, media`). `class-mfn-setup.php:306` referencia un `functions/admin/setup/demos.php` que **no existe** en 28.4.3.
Las demos Elementor usan la clave `<demo>_el` (`class-mfn-importer-helper.php:34-54`).

### 4.2 Receta SSH

```bash
S=/ruta/scripts
$S/be-demo-import.sh --list filter=doctor                       # RO: catálogo local
$S/be-demo-import.sh demo=doctor2 --dry-run                     # plan: pasos, plugins requeridos, purchase code, paquete ya descargado
wp plugin install contact-form-7 revslider --activate           # los slugs que lista el plan (el script NO instala plugins)
$S/be-demo-import.sh demo=doctor2 reset=1 media=1 --yes really=1 --user=<admin>
$S/be-demo-import.sh demo=doctor2 reset=0 complete=0 --yes really=1   # solo contenido + Theme Options, sin tocar menús/portada
$S/be-demo-import.sh demo=doctor2 skip=download --yes really=1        # reanudar con el paquete en uploads/betheme/websites/
```
El script hace `wp db export` a `uploads/betheme/backups/pre-demo-<demo>-<ts>.sql`, encadena los métodos en el orden del wizard,
captura la salida de cada paso y al final regenera `static.css` (el importador escribe `betheme` a pelo). Sin scripts, el mínimo
en `wp eval` es `new Mfn_Importer_Helper('doctor2')` + los métodos de la tabla, tras cargar `functions/admin/class-mfn-helper.php`,
`functions/importer/class-mfn-importer-helper.php` y `functions/admin/class-mfn-api.php` (en CLI no se cargan: `functions.php:97-98,221,227`).

### 4.3 Checklist antes de importar

- [ ] **Nunca en producción.** El reset TRUNCA `posts, postmeta, comments, commentmeta, terms, termmeta, term_taxonomy,
      term_relationships, links` (`class-mfn-importer-helper.php:89-97`), `revslider_*` si RevSlider (l.100-102),
      `wc_product_attributes_lookup, woocommerce_attribute_taxonomies, wc_product_meta_lookup` si Woo (l.106-108); borra options
      `sidebars_widgets|^widget_` (l.112-114) y las de la lista l.116-135 (`be_classes`, `mfn_*`, `mfn_header%`…); con `media=1` además
      `wp_delete_attachment` de todos los adjuntos (l.70-87). No toca `users`, `usermeta` ni `options` en general (el login sobrevive).
- [ ] Backup: `wp db export pre.sql` (el script lo hace) y copia de `wp-content/uploads` si `media=1`.
- [ ] Purchase code registrado (`envato_purchase_code_7758048`, ver [09](09-registro-updates-plugins.md)): sin él la API devuelve error.
- [ ] Plugins de la demo instalados y activos (`--list` los da); Elementor solo con `builder=elementor`.
- [ ] Permisos de escritura en `uploads/betheme/websites/` y `max_execution_time` amplio (el script hace `set_time_limit(0)`).
- [ ] Sin reset, el contenido se **añade** (IDs nuevos) y las options/menús/widgets/portada se **sobrescriben** igualmente.
- [ ] Después: revisar `wp option get page_on_front`, `be-template-list.sh --used`, `be-doctor.sh`, y borrar `uploads/betheme/websites/<demo>/` si quedó.

Revertir: `wp db import uploads/betheme/backups/pre-demo-<demo>-<ts>.sql` (+ restaurar uploads si se borraron adjuntos).

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-demo-import --list filter=doctor` devuelve `doctor2` desde `functions/importer/demos.php`; `demo=doctor2 --dry-run` muestra el plan (paquete no descargado, purchase code presente, avisos de plugins `contact-form-7` y `elementor` no activos) sin tocar nada.
- La importación real y `Mfn_Importer_Helper::database_reset()` **no se han ejecutado** (destructivo).
- CPTs y metas verificados solo por lectura (`wp post-type list`, `wp post meta list`).
