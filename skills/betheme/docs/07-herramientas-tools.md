# 07 — Herramientas (Betheme > Tools), CSS generado y cachés

Cada botón de Betheme > Tools, qué hace por dentro y su equivalente por SSH; además los ficheros que Betheme
genera (`static.css`, `post-<ID>.css`, el JS del BeBuilder) y las cachés que toca (`.htaccess`, transients, W3TC).
Los valores de las opciones citadas están en [02-tipos-de-campo-y-valores.md](02-tipos-de-campo-y-valores.md) y
en `reference/catalogo-opciones.md`; fuentes locales en [05-fuentes.md](05-fuentes.md).

## 1. La página Tools

`Mfn_Tools::init()` (`betheme/functions/admin/class-mfn-tools.php:23-42`): submenú `be-tools` bajo `betheme`, capacidad
`edit_theme_options`; **no existe con `WHITE_LABEL`** (l.25-27). Plantilla `betheme/functions/admin/templates/tools.php` (l.47-50).
Cada botón hace un POST a `admin-ajax.php` con `data-action` + nonce `mfn-builder-nonce` (`tools.php:7`); los handlers
viven en `Mfn_Builder_Ajax` (`betheme/functions/builder/class-mfn-builder-ajax.php`, registro l.39-54) y todos empiezan
por `check_ajax_referer` + `current_user_can('edit_theme_options')`. Por SSH **no se llaman**: se replica su cuerpo.
`Mfn_Helper` (`betheme/functions/admin/class-mfn-helper.php`) solo se carga en admin (`functions.php:217-243`); los scripts usan `be_load_admin_classes()`.

| Botón (`tools.php`) | `data-action` | Handler (`class-mfn-builder-ajax.php`) | Qué hace | SSH |
|---|---|---|---|---|
| Local CSS · Regenerate files (l.29-39) | `mfn_regenerate_css` | `_tool_regenerate_css` l.436-465 | posts `publish` no attachment (l.446); si `mfn-page-local-style` no está vacía → `json_decode` → `Mfn_Helper::generate_css($styles, ID)` (l.451-455) → `uploads/betheme/css/post-<ID>.css` | `be-tools-regenerate-css.sh` |
| Analyze Builder content (l.41-53) | `mfn_analyze_builder` | `_tool_analyze_builder` l.594-706 | page/post/portfolio/product/template con `mfn-page-items` (l.637-640): concatena los valores string de `attr` y `tabs` saltando la lista `$skip` (l.605-635) → meta `mfn-page-items-seo` (l.699) | sin script: se hace solo al guardar cada post (texto del botón l.48) |
| CSS update · Update (l.56-67) | `mfn_new_css_rewrite` | `_tool_new_css_rewrite` l.493-500 → `Mfn_Helper::bebuilder_data_updater()` l.707-730 | ver §5 | `be-tools-bebuilder-data.sh op=rewrite --yes` |
| Regenerate Thumbnails (l.75-86) | `mfn_regenerate_thumbnails` | `_regenerate_thumbnails` l.92-164 (+ progreso `_ajax_progress` l.61-86) | todos los `attachment` (l.100-103); reanuda desde la option `be_regenerate_thumbnails` (l.107-109); por cada fichero existente `wp_generate_attachment_metadata` (l.124); si no hay width/height y es SVG los lee del XML (l.128-139); `wp_update_attachment_metadata` (l.151); guarda progreso (l.156) y borra la option al acabar (l.161) | `wp media regenerate --yes` (nativo, sin SVG) o `be-tools-regenerate-thumbnails.sh --yes` (réplica con SVG) |
| Laptop Breakpoint (l.88-99) | `mfn_assign_laptop_breakpoint` | `_tool_assign_laptop_breakpoint` l.522-588 | reescribe `mfn-page-items` de todos los posts publicados: donde `visibility` contiene `hide-desktop` y no `hide-laptop`, añade `hide-laptop` (l.551-566); reserializa según `builder-storage` (l.571-577). **Modifica contenido, sin backup** | sin script (migración puntual de versiones < laptop breakpoint) |
| Local Fonts · Regenerate fonts (l.107-118) | `mfn_regenerate_fonts` | `_tool_regenerate_fonts` l.274-430 | borra `uploads/betheme/fonts/` y descarga de Google las fuentes usadas → `mfn-local-fonts.css` | `be-fonts-local-regenerate.sh --yes` ([05-fuentes.md](05-fuentes.md)) |
| Re-render Builder data (l.120-130) | `mfn_rerender_bebuilder` | `_tool_rerender_bebuilder` l.257-268 → `MfnVisualBuilder::removeBeDataFile()` | ver §4 | `be-tools-bebuilder-data.sh op=rerender` |
| Delete History (l.132-142) | `mfn_history_delete` | `_tool_history_delete` l.712-737 | borra las metas `mfn-builder-revision-{revision,update,autosave,backup}` (l.720, l.730-731) de page/post/portfolio/product/template | sin script: bucle `wp post meta delete` de §7 |

Handlers sin botón en Tools pero de la misma clase: `_refresh_cache` (l.238-251, botón *Refresh cache* de Performance, `theme-options.php:11853-11859`) que solo hace `@clearstatcache()` e imprime "Done"; `_set_transient`/`_delete_transient` (l.210-232) que el JS de los switch con `old_value` usa para marcar `betheme_<id>` = `changed` durante 30 min (l.218; `fields/switch/field_switch.js:28,38`; `field_switch.php:107-109`). Solo `hold-cache` tiene `old_value` (`theme-options.php:11848`).

## 2. Static CSS (`static-css`)

| Pieza | Dónde | Qué hace |
|---|---|---|
| Opción `static-css` | `betheme/muffin-options/theme-options.php:11827-11836`, switch `0/1`, std `0` | activa el fichero |
| `MFN_Options::_static_CSS($force)` | `betheme/muffin-options/options.php:903-933` | guard l.905: sin `$force` solo con `?settings-updated` **y** `static-css=1`; escribe `uploads/betheme/css/static.css` = `/* theme options */` + `mfn_styles_dynamic()` + `/* custom CSS */` + `mfn_styles_custom()` (l.924-932). Hook `mfn-opts-page-before-form` prio 10 (l.88) |
| `mfn_styles_dynamic()` | `betheme/functions/theme-head.php:1280-1309` | `style.php` + `style-responsive.php` (si `responsive`) + `style-colors.php` (`skin=custom`) o `style-one.php` (`skin=one`), pasado por `mfn_styles_minify()` (l.1251) |
| `mfn_styles_custom()` | `theme-head.php:1319` | opción `custom-css` |
| `mfn_styles_static()` | `theme-head.php:625-634` | si `static-css`: `wp_enqueue_style('mfn-static', uploads/betheme/css/static.css, MFN_THEME_VERSION)`; hook `wp_enqueue_scripts` 11 o `mfn_wp_footer_before` 11 si `css-location=footer` (l.636-640) |
| `mfn_styles_inline()` | `theme-head.php:723-762` | inline `mfn-dynamic` (fuentes custom, backgrounds, html, local, header, sidemenu, templates) y `mfn-custom` (page custom); **`mfn_styles_dynamic()` y `mfn_styles_custom()` solo si `static-css` está off** (l.736-738, l.756-758). Hook según `css-location` (l.764-768) |
| skins predefinidos | `theme-head.php:423-435` | con `static-css` off encola `css/skins/<skin>/style.css` si el skin no es `custom`/`one` |
| `css-location` | `theme-options.php:11762-11771`, `''`/`footer` | mueve `mfn_styles_static` y `mfn_styles_inline` a `mfn_wp_footer_before` (`footer.php`) |

Por SSH: `be-opt-set` regenera `static.css` siempre (`_lib.php` → `_static_CSS(true)`), esté o no activa la opción, para que
no quede un fichero viejo si alguien la activa (en el dev hay un `static.css` de mayo con `static-css` off).

```bash
scripts/be-static-css-regenerate.sh --check     # up_to_date, md5 fichero vs calculado, sin escribir
scripts/be-static-css-regenerate.sh             # escribe
# wp puro:
wp eval 'global $MFN_Options; require_once get_template_directory()."/functions/admin/class-mfn-helper.php"; $MFN_Options->_static_CSS(true);'
```

Trampa: el `<link>` de `static.css` va versionado con `MFN_THEME_VERSION` (l.631), no con el mtime → tras regenerar, los
navegadores/CDN siguen sirviendo el viejo hasta purgar. Con `static-css=1` el CSS de Theme Options **ya no sale inline**: si el fichero no existe la web pierde todos los estilos de opciones.

## 3. CSS local por post (`post-<ID>.css`)

- Se genera al guardar en el BeBuilder: `Mfn_Helper::preparePostUpdate($object, $post_id, 'mfn-page-local-style')`
  (`betheme/visual-builder/visual-builder.php:396-397`, también la variante `mfn-builder-preview-local-style`) → escribe la meta como JSON (`class-mfn-helper.php:473-475`) y llama a `Mfn_Helper::generate_css()` (l.479).
- `generate_css($styles, $post_id, $preview)` (`class-mfn-helper.php:568-685`): fichero `uploads/betheme/css/post-<ID>.css` (l.576-582; sufijo `-preview` l.578; ids no numéricos usan el nombre tal cual l.580); bloques `desktop` + `@media(max-width:1440px)` laptop (l.609) + `959px` tablet (l.625) + `767px` mobile (l.641) + `custom` show/hide-under (l.656-680); `put_contents` l.683.
- Encolado: `Mfn_Builder_Front::enqueue_local_style()` (`betheme/functions/builder/class-mfn-builder-front.php:191-262`): solo si existen la meta **y** el fichero (l.224); handle `mfn-post-local-styles-<ID><time()>` con versión `time()` (l.229, l.259) → nunca se cachea; fallback WPML a la página en idioma nativo (l.231-243); además `be_classes.css` (l.202-210). Se llama desde el render del builder salvo `local-styles-location=inline` (l.322-328: los templates megamenu/footer/popup/sidemenu siempre van a fichero).
- `local-styles-location` (`theme-options.php:11774-11783`, `''` fichero externo / `inline`): con `inline`, `mfn_styles_local()` (`theme-head.php:836`) lee el mismo fichero y lo vuelca en `mfn-dynamic` (previene CLS). El fichero sigue haciendo falta.

```bash
scripts/be-tools-regenerate-css.sh --dry-run          # lista posts con mfn-page-local-style y el fichero destino
scripts/be-tools-regenerate-css.sh                    # = botón "Regenerate files"
scripts/be-tools-regenerate-css.sh ids=28,122 --preview
# wp puro (un post):
wp eval 'require_once get_template_directory()."/functions/admin/class-mfn-helper.php"; Mfn_Helper::generate_css(json_decode(get_post_meta(28,"mfn-page-local-style",true),true),28);'
```

Trampa: `_tool_regenerate_css` solo toca posts `publish`; un borrador con builder no recupera su CSS hasta publicarse (o `ids=`).
Ficheros de 0 bytes (`post-1.css`, `post-18.css` en el dev) son posts cuyo builder no genera reglas: normales.

## 4. Fichero JS de campos del BeBuilder

- Ruta: `MfnVisualBuilder::bebuilderFilePath()` (`betheme/visual-builder/classes/visual-builder-class.php:1729-1739`) = `betheme/visual-builder/assets/js/forms/bebuilder-<MFN_THEME_VERSION>.js` (**dentro del tema**, se pierde al actualizar; en el dev no existe ahora mismo).
- Generación: `Mfn_Helper::generate_bebuilder_items()` (`class-mfn-helper.php:687-705`): aborta si el filtro `bebuilder_access` es false (l.689-690; `mfn_bebuilder_access()` `theme-head.php:2635-2670` mira roles del usuario actual y `builder-visibility=hide`); `removeBeDataFile()`; `fieldsToJS()` (l.629-640: section/wrap/items/advanced + **formulario de Theme Options solo si `current_user_can('edit_theme_options') && mfn_is_registered()`**, l.636); escribe el fichero y `update_option('betheme_form_uid', unique_ID())` (l.703).
- Regeneración perezosa: `mfn_append_vb_footer()` l.210-215: en admin, si el fichero no existe (o `MFN_DEBUG`), lo genera al abrir el BeBuilder; se encola con versión `betheme_form_uid` (l.210, l.218).
- Borrado: `removeBeDataFile()` l.1721-1727 (`wp_delete_file` + nuevo `betheme_form_uid`). Lo llaman Tools (`_tool_rerender_bebuilder` l.265), `Mfn_Builder_Ajax::settings()` l.1318 (cambios de UI del builder) y `_flush_cache()` tras guardar Theme Options vía `generate_bebuilder_items()` salvo `builder-visibility=hide` (`options.php:949-953`). `_lib.php` lo hace en cada `be_opts_save` (`be_bebuilder_reset()`).

```bash
scripts/be-tools-bebuilder-data.sh op=rerender               # = botón "Re-render Builder data"; se regenera al abrir el builder
scripts/be-tools-bebuilder-data.sh op=items --user=admin     # lo regenera ya (carga Mfn_Builder_Fields, MfnVisualBuilder, icons.php)
# wp puro:
wp eval 'wp_delete_file(get_template_directory()."/visual-builder/assets/js/forms/bebuilder-".MFN_THEME_VERSION.".js"); update_option("betheme_form_uid", Mfn_Builder_Helper::unique_ID());'
```

`op=items` exige `--user=<administrador>`: sin usuario, `fieldsToJS()` omite el formulario de Theme Options (l.636) y el
BeBuilder abriría sin él hasta que alguien pulse Re-render. En CLI las clases del builder no están cargadas
(`class-mfn-builder.php:37-45` solo en admin; `MfnVisualBuilder` se incluye desde `visual-builder.php`, cargado solo con
`is_admin() && bebuilder_access`, `functions.php:239-241`); el script incluye los cuatro ficheros necesarios y añade
`add_filter('bebuilder_access','__return_true')`. **[sin verificar]** que `Mfn_Builder_Fields(true)` construya sin
errores fuera del admin: probar en el dev antes de usarlo en producción; si falla, `op=rerender` es suficiente.

## 5. "CSS update" (`bebuilder_data_updater`)

`Mfn_Helper::bebuilder_data_updater()` (`class-mfn-helper.php:707-730`): `mfn-css-db-update = pending` (l.711); lee el
progreso `mfn-css-db-update-status` (l.712); por cada `post_id` con meta `mfn-page-items` (l.716) ejecuta
`MfnLocalCssCompability->render($id)` y guarda el índice (l.720-724); al final `mfn-css-db-update = 1` y borra el status (l.727-728).
`render()` (`betheme/visual-builder/classes/helpers/local-css-compability.php:22-42`): crea `mfn-page-items-backup` si no existía (l.28),
convierte el formato antiguo de estilos y **solo escribe** si detecta builder antiguo (l.151 → `update()` l.910-921: `mfn-page-items`
según `builder-storage`, `mfn-css-db-update=1`, `preparePostUpdate` → `mfn-page-local-style` + `post-<ID>.css`).
Mientras `mfn-css-db-update` ≠ `1` el admin muestra el aviso "BeBuilder Data Updater" (`visual-builder.php:1855-1883`; se autoajusta a `1` si no hay posts con builder, l.1861-1864) y el BeBuilder redirige a `mfn_update_db_required` (`visual-builder-class.php:456`).

```bash
scripts/be-tools-bebuilder-data.sh op=rewrite --dry-run      # posts con mfn-page-items y si ya tienen backup
scripts/be-tools-bebuilder-data.sh op=rewrite --yes          # = botón "CSS update"
wp option get mfn-css-db-update                              # debe ser 1 (en el dev lo es)
```

## 6. Caché `.htaccess` (`hold-cache`) y otras cachés

Flujo en el admin: el switch `hold-cache` (`theme-options.php:11839-11850`, `old_value` l.11848) imprime un `input.old-value`
(`field_switch.php:107-109`); al cambiarlo, el JS llama a `mfn_set_transient` (`field_switch.js:38`) → transient
`betheme_hold-cache = changed` 30 min (`class-mfn-builder-ajax.php:218`). Al guardar con `?settings-updated`,
`_cache_manager()` (`options.php:961-984`, hook l.89) solo actúa si el transient vale `changed`: `_setup_cache()`
(l.986-999: exige `hold-cache=1` l.988; **concatena** `Mfn_Helper::get_cache_text()` al final de `get_home_path().'.htaccess'`,
l.996, permisos 0644) o `_remove_cache()` (l.1001-1010: `preg_replace('/(# BEGIN BETHEME)(.|\n)*?(# END BETHEME)/', '')`), y borra el transient.

Bloque que escribe `get_cache_text()` (`class-mfn-helper.php:762-811`), íntegro:

```apache
# BEGIN BETHEME
<IfModule mod_expires.c>
ExpiresActive On
AddType font/woff2 .woff2
# Images
ExpiresByType image/jpeg "access plus 1 year"
ExpiresByType image/gif "access plus 1 year"
ExpiresByType image/png "access plus 1 year"
ExpiresByType image/webp "access plus 1 year"
ExpiresByType image/svg+xml "access plus 1 year"
ExpiresByType image/x-icon "access plus 1 year"
# Video
ExpiresByType video/webm "access plus 1 year"
ExpiresByType video/mp4 "access plus 1 year"
ExpiresByType video/mpeg "access plus 1 year"
# Fonts
ExpiresByType font/ttf "access plus 1 year"
ExpiresByType font/otf "access plus 1 year"
ExpiresByType font/woff "access plus 1 year"
ExpiresByType font/woff2 "access plus 1 year"
ExpiresByType application/x-font-ttf "access plus 1 year"
ExpiresByType application/font-woff "access plus 1 year"
# CSS, JavaScript
ExpiresByType text/css "access plus 6 months"
ExpiresByType text/javascript "access plus 6 months"
ExpiresByType application/javascript "access plus 6 months"
# Others
ExpiresByType application/pdf "access plus 6 months"
ExpiresByType image/vnd.microsoft.icon "access plus 1 year"
ExpiresDefault "access 1 month"
</IfModule>
# END BETHEME
```

Por SSH nada de esto ocurre (`be-opt-set hold-cache=1` cambia la opción y nada más), de ahí el script:

```bash
scripts/be-cache-htaccess.sh mode=status                  # block_present, block_count, hold_cache, consistent
scripts/be-cache-htaccess.sh mode=on  --with-option       # backup .htaccess.bak-<fecha> + bloque + hold-cache=1
scripts/be-cache-htaccess.sh mode=off --with-option
# wp puro:
wp eval 'require_once ABSPATH."wp-admin/includes/file.php"; $p=get_home_path().".htaccess"; file_put_contents($p, preg_replace("/(# BEGIN BETHEME)(.|\n)*?(# END BETHEME)/", "", file_get_contents($p)));'
```

Otras cachés: `_flush_cache()` (`options.php:939-955`) llama a `w3tc_flush_all()` si existe; `be_post_save()` de `_lib.php`
hace lo mismo y además `wp_cache_flush()`. Al guardar una página en el BeBuilder se limpia WP Rocket (`visual-builder.php:418-419`).
El botón *Refresh cache* de Performance (`hold-cache-regenerate`) solo hace `clearstatcache()` (l.246): no purga nada.

Trampas: (1) `_setup_cache()` no comprueba si el bloque ya existe → duplicados si el transient queda a `changed` y se guarda dos veces; `mode=status` los cuenta. (2) Importar/restaurar opciones con `hold-cache=0` no toca `.htaccess`: en el dev el bloque está presente (líneas 257-297) con `hold-cache` vacío → `consistent:false`. (3) `get_home_path()` vive en `wp-admin/includes/file.php`; en CLI hay que incluirlo (el script lo hace).

## 7. Recetas wp puro para el resto de botones

```bash
# Delete History (mfn-builder-revision-*), todos los tipos que usa el handler (l.723)
for id in $(wp post list --post_type=page,post,portfolio,product,template --post_status=any --field=ID); do
  for t in revision update autosave backup; do wp post meta delete $id mfn-builder-revision-$t 2>/dev/null; done; done
# Regenerate thumbnails nativo (equivalente a l.100-152 sin el tratamiento SVG)
wp media regenerate --yes            # --only-missing para no rehacer lo que ya existe; --image_size=<size> para un tamaño
```

## 8. Qué regenerar después de cada tipo de cambio

| Cambio hecho por SSH | Acción necesaria | Lo hace ya |
|---|---|---|
| Cualquier opción de `betheme` (colores, fuentes, tamaños, custom-css) | `static.css` + reset JS BeBuilder + purga W3TC | `be_opts_save()` en todos los `be-opt-set`/`be-*-set` |
| `custom-css` con `static-css=1` | `static.css` | `be_opts_save()` |
| Activar `static-css` | asegurarse de que `static.css` existe y está al día | `be-static-css-regenerate.sh --check` |
| `hold-cache` | bloque en `.htaccess` | `be-cache-htaccess.sh mode=on|off --with-option` |
| `font-*` con `google-font-mode=local` | `uploads/betheme/fonts/` | `be-fonts-local-regenerate.sh --yes` ([05](05-fuentes.md)) |
| Editar `mfn-page-items` / `mfn-page-local-style` a mano, borrar `uploads/betheme/css/` | `post-<ID>.css` | `be-tools-regenerate-css.sh [ids=]` |
| Actualizar Betheme (cambia `MFN_THEME_VERSION`) | nada: el JS del builder lleva la versión en el nombre y se regenera al abrir; `static.css` cambia de versión en el `<link>` | — |
| Cambiar `builder-visibility`, `post-type-disable`, plugins con widgets del builder | reset JS BeBuilder | `be-tools-bebuilder-data.sh op=rerender` (o `be_opts_save`) |
| Alta/baja de pack de iconos | nada (el `<link>` se imprime al vuelo, sin caché) — purgar caché de página si la hay | — |
| Importar demo / migrar de servidor | `mfn-css-db-update` (aviso "Data Updater"), `mfn-icon-upload`/`-url`, thumbnails | `op=rewrite`, [06](06-iconos.md), `wp media regenerate` |
| Editar `mfn-page-items` en posts con formato antiguo | `op=rewrite` (crea `mfn-page-items-backup`) | `be-tools-bebuilder-data.sh op=rewrite --yes` |

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-tools-regenerate-css --dry-run` lista los 9 posts con `mfn-page-local-style`; `ids=28` regenera `post-28.css` con md5 idéntico al existente.
- `be-tools-bebuilder-data op=rerender` cambia `betheme_form_uid`; `op=items --user=webmaster` escribe `bebuilder-28.4.3.js` (2 MB, contiene el formulario de Theme Options). Sin `--user=<admin>` `op=items` aborta, como está previsto.
- `be-cache-htaccess mode=status`: bloque `# BEGIN BETHEME` presente con `hold-cache = 0` (`consistent: false`, estado heredado del dev). `mode=off --dry-run` muestra el `.htaccess` resultante; no se aplicó.
- `be-static-css-regenerate --check` detectó el `static.css` de mayo desactualizado (67 386 vs 68 126 bytes); cada `be-opt-set` lo regenera.
