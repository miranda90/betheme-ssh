# 01 — Almacenamiento y guardado de Theme Options

Dónde vive todo lo que se toca en *Betheme → Theme Options*, cómo lo lee el tema, qué hace exactamente el botón
**Save** del panel y qué parte de eso **no ocurre** cuando se escribe la opción por SSH (y cómo lo replican los scripts).
La forma de cada valor por tipo de campo está en [02-tipos-de-campo-y-valores.md](02-tipos-de-campo-y-valores.md).

## 1. Una sola fila: `wp_options.betheme`

Todo el panel (21 pestañas, 829 campos con id) se guarda en **una única opción** llamada `betheme`
(`$defaults['opt_name'] = 'betheme'`, `betheme/muffin-options/options.php:38`), que el constructor carga en memoria
con `$this->options = get_option( $this->args['opt_name'] )` (`options.php:77`). Es un array PHP serializado, **plano**:
la clave es el `id` del campo y el valor es lo que describe el doc 02 (string o array de un nivel, salvo excepciones).

| Dónde | Qué | Cita |
|---|---|---|
| `wp_options.betheme` | array `[id => valor, ...]`, autoload `yes` (creada con `add_option` sin más argumentos) | `options.php:672-673` |
| `$MFN_Options->options` | copia en memoria de esa fila, la que consultan todas las lecturas | `options.php:77`, `:675` |
| `$MFN_Options->menu` / `->sections` | definición de pestañas y campos (no se persiste) | `theme-options.php:771` (`mfn_opts_setup()`), `:11986` (`new MFN_Options`) |

La instancia global `$MFN_Options` se crea al cargar `theme-options.php` (`theme-options.php:11986-11989`), es decir,
también con wp-cli y con `require 'wp-load.php'`; solo desaparece con `--skip-themes`.

En un entorno de desarrollo real la fila tiene 742 claves (631 strings, 111 arrays) frente a los 829 ids del catálogo:
las claves que el formulario no postea (campos `ajax`, `custom`, `group`) o que nunca se guardaron no existen en la fila.

## 2. Lectura: `mfn_opts_get()` → `MFN_Options::get()`

`mfn_opts_get( $opt_name, $default = null, $attr = [] )` (`betheme/muffin-options/theme-options.php:11997`) delega en
`$MFN_Options->get( $opt_name, $default )` (`options.php:620-634`) y después post-procesa. Semántica exacta de `get()`:

| Situación | Devuelve | Línea |
|---|---|---|
| `$this->options` no es array (fila ausente/corrupta) | `$default` | `options.php:621-623` |
| la clave **no existe** en la fila | `$default` | `:625-627` |
| la clave existe pero es "vacía" (`''`, `0`, `[]`, `null`, `false`) **y no es la cadena `'0'`** | `$default` | `:629-631` |
| cualquier otro valor, incluida la cadena `'0'` | el valor guardado | `:633` |

Consecuencias que hay que tener claras al escribir por SSH:

- **El `std` del campo NO interviene en la lectura.** El segundo argumento de `mfn_opts_get()` es el único default en
  tiempo de ejecución; cada llamada del tema pone el suyo (o ninguno → `null`). El `std` solo se materializa en la fila la
  primera vez (sección 3). Borrar una clave o dejarla en `''` es lo mismo para el front: cae al `$default` de esa llamada.
- **`'0'` como string se respeta** (es la única forma de guardar un "cero" que no se confunda con "sin valor"). El panel lo
  aprovecha, por ejemplo, en `header-height = '0'` (`options.php:298`, comentario "use string not integer").
- Post-proceso de `mfn_opts_get()` (`theme-options.php:12008-12048`): si el valor es array elimina la subclave `isLinked`
  (`:12012`, campo `dimensions`), aplica `unit`, `implode` y `key` de `$attr`; con `not_empty => true` devuelve `$default`
  si el resultado sigue siendo falsy (`:12043-12045`).

### Lo que lees con `mfn_opts_get()` no siempre es lo que hay en la fila

`_backward_compatibility()` (`options.php:286`) corre en `init` (`options.php:63`) y **modifica `$this->options` en
memoria** sin guardarlo: reconstruye `social-link[<red>]` a partir de las claves antiguas `social-<red>` si existen
(`options.php:335-344`), fija `variable-swatches = '1'` si falta (`:327-329`), recalcula `header-height` desde
`minimalist-header` (`:294-303`) y rellena `button-*` a partir de `button-style` (`:348-350`). Solo escribe en BD en un caso:
si detecta ids de Google Analytics/Ads de muffingroup en `google-gtag-*`/`google-analytics` los vacía (`:551-580`), guarda
una revisión `revision` y hace `update_option('betheme')` (`options.php:582-590`). Por tanto: `wp option pluck betheme <id>` muestra la
fila cruda; `be-opt-get` también (lee `get_option`); el front ve la versión "compatibilizada".

## 3. `std`: solo la primera vez

`_set_default_options()` (`options.php:670-681`, hook `init`, `options.php:62`) hace `add_option('betheme', $this->_default_values())`
**solo si la opción no existe** (`:672-674`). `_default_values()` (`options.php:640-663`) recorre todas las secciones y pone
`$defaults[id] = std` (o `''` si el campo no declara `std`, `:651-655`) para **todo campo con id**, incluidos los de tipo
`ajax`/`custom` que luego el formulario no postea; y añade `last_tab = false` (`:662`).

A partir de ahí el `std` **nunca se vuelve a aplicar automáticamente**: ni al actualizar el tema, ni al añadirse campos nuevos
en una versión (esas claves simplemente no existen en la fila hasta que alguien pulsa Save). Las únicas vías que vuelven a
usar `_default_values()` son el botón *Reset* (`options.php:1175-1178`) y el preview del setup wizard (`:678-680`, en memoria).

### Claves extra que no son campos

| Clave | Quién la escribe | Para qué |
|---|---|---|
| `last_tab` | input oculto `betheme[last_tab]` del formulario (`options.php:2008-2009`); `_default_values` la crea a `false` (`:662`); import la resetea (`:1167`) | recordar la pestaña abierta |
| `imported` | import de opciones: `$imported_options['imported'] = 1` (`options.php:1166`) | marca "estas opciones vienen de un import" |

`be_extra_keys()` de `_lib.php` las reconoce para que `be-opt-set` no las rechace como ids desconocidos.

## 4. El ciclo de guardado del panel (Settings API)

`_register_setting()` (`options.php:1367-1410`) registra en `admin_init` (`options.php:74`):
`register_setting( 'betheme_group', 'betheme', [$this, '_validate_options'] )` (`options.php:1369`). El formulario postea a
`wp-admin/options.php` (`options.php:2002`) con `settings_fields('betheme_group')` (`:2006`); WordPress llama a
`_validate_options($plugin_options)` con **el array completo posteado** y guarda lo que devuelva con `update_option`.
Esto implica la trampa más importante del panel: **cada Save reescribe la fila entera con lo que hay en el formulario**.
Cualquier clave que no renderice un input (campos `ajax`, `custom`, `group`, la clave `imported`, o claves ajenas añadidas
por SSH) desaparece en el siguiente Save del panel.

Pasos de `_validate_options()` (`options.php:1113-1286`), en orden:

| # | Paso | Líneas | Detalle |
|---|---|---|---|
| 1 | `set_transient('mfn-opts-saved','1',1000)` | `:1115` | para el aviso "saved" del panel |
| 2 | Restaurar revisión | `:1119-1136` | si llega `$_POST['revision-time']`+`revision-type`, devuelve `unserialize(base64_decode(betheme_revision_<type>[time]))` y **termina** |
| 3 | Import | `:1140-1171` | si `import` no vacío: `import_code` (texto) o `import_link` (URL, `wp_safe_remote_get`); `json_decode`; si falla, `base64_decode`+`unserialize`; añade `imported=1`, `last_tab=false` y **termina** |
| 4 | Reset | `:1175-1178` | si `defaults == 'Resetting...'` devuelve `_default_values()` y **termina** |
| 5 | `_validate_values()` | `:1182` | ver abajo |
| 6 | `set_revision('update', $plugin_options)` | `:1186` | guarda lo **nuevo** (no lo anterior) en `betheme_revision_update` |
| 7 | Transients de errores/avisos | `:1190-1196` | `mfn-opts-errors`, `mfn-opts-warnings` (1000 s) |
| 8 | Hooks | `:1200-1201` | `do_action('mfn-opts-options-validate', $new, $old)` y `'mfn-opts-options-validate-betheme'`. Ni Betheme ni Témini enganchan nada aquí (grep vacío en `src/`) |
| 9 | Limpieza | `:1205-1210` | `unset` de `defaults`, `import`, `import_code`, `import_link` (solo si no es AJAX) |
| 10 | Cabecera de checkout Woo | `:1226-1282` | si `gutenberg-checkout-header == 'create_checkout_header'` crea un CPT `template` de tipo header con `mfn-is-checkout-header=1` y guarda su id en la opción; si es numérico, lo asigna como `mfn_dedicated_header_template` de la página de checkout |

`_validate_values($plugin_options, $options)` (`options.php:1293-1360`): recorre todos los campos y **solo actúa** en los
que tienen `validate` (carga `validation/<x>/validation_<x>.php`, clase `MFN_Validation_<x>`, `:1317-1340`) o
`validate_callback` (`:1343-1355`), y solo si el valor posteado no está vacío (`:1301-1303`). El bloque que forzaba
validación de colores está comentado (`:1305-1313`). En la práctica no hay saneado de tipos: lo que postea el
formulario se guarda tal cual, y por eso los scripts validan la forma con `be_shape()` antes de escribir.

## 5. Efectos post-guardado que NO ocurren al escribir por SSH

Tras el redirect de `options.php` el panel se recarga con `?settings-updated=true`, y al pintar la página dispara
`do_action('mfn-opts-page-before-form')` (`options.php:2000`), al que el constructor engancha tres métodos
(`options.php:88-90`). Los tres comprueban `$_GET['settings-updated']` y **no hacen nada** fuera de ese contexto:

| Prio | Método | Guarda | Qué hace | Equivalente en `_lib.php` |
|---|---|---|---|---|
| 10 | `_static_CSS( $force = false )` (`options.php:903-933`) | `:905` (`settings-updated` **y** `static-css` activo, salvo `$force`) | escribe `uploads/betheme/css/static.css` = `mfn_styles_dynamic()` (`functions/theme-head.php:1280`) + `mfn_styles_custom()` (`:1319`), vía `Mfn_Helper::filesystem()` | `be_static_css_regenerate()` → `$mfn->_static_CSS( true )`, **siempre**, esté o no activo `static-css` |
| 11 | `_cache_manager()` (`options.php:961-984`) | `:963-967` (transient `betheme_hold-cache == 'changed'`) | según `hold-cache`: `_setup_cache()` (`:986-999`, añade `Mfn_Helper::get_cache_text()` al `.htaccess`) o `_remove_cache()` (`:1001-1015`, borra el bloque `# BEGIN BETHEME … # END BETHEME`) | no lo replica `be_opts_save`; ver `be-cache-htaccess` en [07-herramientas-tools.md](07-herramientas-tools.md) |
| 12 | `_flush_cache()` (`options.php:939-955`) | `:941` | `w3tc_flush_all()` si existe (`:945-947`); `Mfn_Helper::generate_bebuilder_items()` salvo `builder-visibility == 'hide'` (`:951-953`) | `be_post_save()`: `w3tc_flush_all()` + `wp_cache_flush()` + `be_bebuilder_reset()` |

Sobre el JS del BeBuilder: `generate_bebuilder_items()` (`functions/admin/class-mfn-helper.php:687-705`) necesita el
filtro `bebuilder_access` (`:689-690`), que solo se concede en admin; por eso `be_bebuilder_reset()` replica en su lugar
`MfnVisualBuilder::removeBeDataFile()` (`visual-builder/classes/visual-builder-class.php:1721-1727`): borra
`visual-builder/assets/js/forms/bebuilder-<versión>.js` y cambia `betheme_form_uid`. El fichero se vuelve a generar
solo la próxima vez que alguien abre el builder (`visual-builder-class.php:210`).

`static.css` solo se encola si `static-css` está activo (`mfn_styles_static()`, `functions/theme-head.php:625-634`;
hook según `css-location`, `:636-640`); si no, el CSS va inline en `mfn_styles_inline()` (`:721`). Regenerarlo siempre
evita servir un fichero desactualizado si alguien reactiva la opción más tarde.

### Qué hace `be_opts_save( $new, $flags )` (`scripts/php/_lib.php`)

1. `be_diff( old, new )`; con `dry => true` devuelve el diff y no escribe.
2. `$mfn->set_revision( 'backup', $old )` (`options.php:132-166`): guarda el estado **anterior** en `betheme_revision_backup`
   como `[timestamp => base64(serialize($old))]`, máximo 5 (`:140-151`). El panel guarda en `_update` el estado nuevo; los
   scripts guardan en `_backup` el anterior, que es lo útil para revertir. Ver [08-export-import-reset-revisiones.md](08-export-import-reset-revisiones.md).
3. `update_option('betheme', $new)` y refresco de `$mfn->options`.
4. `be_post_save()`: `_static_CSS(true)`, `be_bebuilder_reset()`, `w3tc_flush_all()` si existe, `wp_cache_flush()`.

No replica: `_validate_values` (irrelevante: ningún campo del catálogo declara `validate`), los hooks
`mfn-opts-options-validate` (nadie los usa), la cabecera de checkout (paso 10) ni `_cache_manager`.

## 6. Otros procesos en `init` que tocan la opción en memoria

- `_register_custom_social()` (`options.php:1017-1065`, `init` prio 11, `:66`): si `custom-icon-count` > 0 compacta los
  slots `social-custom-icon-N`/`-link-N`/`-title-N` (N ≥ 2) eliminando huecos, limpia el nombre `custom-N` de
  `social-link[order]` (`:1045-1054`) y recalcula `custom-icon-count` (`:1063`). **En memoria**: se persiste en el siguiente Save.
- `_register_custom_fonts()` (`options.php:1072-1107`, `init` prio 12, `:67`): lo mismo para `font-custom{N}`,
  `-woff`, `-ttf` (N ≥ 3) y `font-custom-fields`. Detalle en [05-fuentes.md](05-fuentes.md).
- `_backward_compatibility_admin()` (`options.php:601-614`, `admin_init`, `:63`): una sola vez por instalación regenera
  `static.css` y marca la site option `betheme_static = '28'` (`:609-612`).

Si escribes esos slots por SSH, escribe ya la forma compactada (sin huecos, contador correcto): así el front y el panel ven lo mismo.

## 7. Otras options, site options y transients que gestiona el panel

Verificadas con grep en 28.4.3 (fichero:línea de la escritura o lectura principal):

| Clave | Tipo | Contenido | Quién | Cita |
|---|---|---|---|---|
| `betheme_revision_update` / `_revision` / `_backup` | option | `[ts => base64(serialize(opts))]`, máx. 5 | `set_revision()`; restauración en `_validate_options`; AJAX `_revision_save` | `options.php:142`, `:207`, `:1126`, `:97-126` |
| `be_css_variables` | option | JSON de variables CSS del builder | `visual-builder.php:651`; lo lee `style.php:12` | |
| `be_classes` / `be_classes_fonts` | option | JSON de clases globales del builder y sus fuentes | `visual-builder.php:630`, `:621-623`; fuentes encoladas en `theme-head.php:455` | |
| `mfn-presets` | option | JSON de presets del builder | `visual-builder.php:703,732,1014` | |
| `betheme_form_uid` | option | uid que versiona `bebuilder-<ver>.js` | `visual-builder-class.php:210,1724`; `class-mfn-helper.php:703` | |
| `be_regenerate_thumbnails` | option | offset del proceso de regenerar miniaturas | `class-mfn-builder-ajax.php:74,81,107-109,156,161` | |
| `mfn-css-db-update` / `-status` | option | estado de la migración de CSS local | `visual-builder.php:1859-1864`; `class-mfn-helper.php:711-712` | |
| `mfn_fake_sale` | option | JSON caché de la notificación de ventas | `functions/theme-woocommerce.php:1840,1892` | |
| `mfn_cart_template` / `_used` | option | id de plantilla de carrito | `theme-woocommerce.php:1695-1697` | |
| `attr_loop_<id>` | option | `'1'` si el atributo Woo se muestra en listado | `theme-woocommerce.php:553,590-600` | |
| `envato_purchase_code_7758048` | site option | purchase code | `functions/theme-functions.php:4076-4082`; `class-mfn-dashboard.php:213,552` | ver [09](09-registro-updates-plugins.md) |
| `betheme_updates_history` | site option | historial de versiones | `class-mfn-update.php:80-91`; `class-mfn-status.php:94` | |
| `betheme_static` | site option | `'28'` tras la migración de static.css | `options.php:609-611` | |
| `betheme_promo` / `betheme_promo_closed` | site option | banner promo y versión en la que se cerró | `class-mfn-dashboard.php:289`, `:148` | |
| `betheme_builder_<user_id>` | site option | preferencias del builder por usuario | `theme-functions.php:4018`; `class-mfn-builder-helper.php:25` | |
| `mfn-opts-saved` / `-errors` / `-warnings` | transient (1000 s) | feedback del último Save | `options.php:1115,1191,1195` | |
| `betheme_hold-cache` | transient | `'changed'` → `_cache_manager` actúa | `options.php:963-977` | |
| `betheme_update` | site transient | versión disponible (1 h) | `class-mfn-api.php:129,153` | |
| `betheme_expires` | site transient | caducidad del soporte (1 semana) | `class-mfn-dashboard.php:162-183`; `setup/class-mfn-setup.php:548` | |
| `betheme_plugins` / `betheme_update_plugins` | site transient | lista/actualizaciones de plugins del tema | `class-mfn-dashboard.php:217-218,635-636`; `class-mfn-api.php:156` | |
| `betheme_promo_version` | site transient | versión del banner (1 día) | `class-mfn-dashboard.php:242,265` | |

Las options `mfn_<type>[_<lang>]_*` de plantillas header/footer se compilan desde el CPT `template`, no desde el panel:
[10-plantillas-header-footer-popups.md](10-plantillas-header-footer-popups.md).

## 8. WPML

WPML **no duplica** la fila `betheme` por idioma. Traduce por *String Translation → admin-texts*: `betheme/wpml-config.xml`
declara `<admin-texts><key name="betheme">` (`wpml-config.xml:565-566`) con 145 subclaves (`:568-736`, cierre `:737-738`):
`logo-img`…`logo-text`, `meta-description`, textos de GDPR (`gdpr2-about-title`, `gdpr2-button-*`), etc. Por SSH la
traducción vive en las tablas `icl_strings`/`icl_string_translations` (contexto `admin_texts_betheme`), no en `betheme`.

El sufijo `_<lang>` solo aparece en las options compiladas de plantillas
(`'mfn_'.$type.$t_lang.'_entire_site'`, `functions/builder/class-mfn-builder-admin.php:4011`), nunca en `betheme`.

## 9. Rutas de escritura que se saltan `_validate_options`

| Ruta | Cita | Qué escribe |
|---|---|---|
| Live Builder → Theme Options (AJAX `mfn_vb_themeoptions`) | `betheme/visual-builder/visual-builder.php:1714-1727` | `update_option('betheme', wp_unslash($_POST['betheme']))` (`:1724`) con nonce + `edit_theme_options`; sin revisión, sin static.css |
| Importador de demos | `functions/importer/class-mfn-importer-helper.php:570-620` | `options.txt` (base64+serialize, `:572-575`), quita `google-*`/`facebook-pixel` (`:600-605`), reemplaza la URL de origen (`:609-617`), `update_option('betheme')` (`:620`) |
| Setup wizard (colores) | `functions/admin/setup/class-mfn-setup.php:1207` | `update_option('betheme', $options)` tras aplicar la paleta |
| `_backward_compatibility` (conflicto SEO) | `options.php:589` | ver sección 2 |

Ninguna regenera `static.css` ni el JS del builder: tras usarlas conviene un `be-static-css-regenerate`.

## 10. Recetas

Prerrequisitos y variables (`WP_PATH`, `WP_BIN`) en [00-INDICE.md](00-INDICE.md). Todos los wrappers aceptan `--path=`.

```bash
S=~/be-ssh-test/scripts        # o donde estén los scripts

# Leer
$S/be-opt-get.sh key=logo-height                      # {"logo-height":"60"}
$S/be-opt-get.sh key=logo-height --format=raw           # 60
$S/be-opt-get.sh key=font-size-h1,font-size-h2 --meta # + tipo, forma, std, sección
$S/be-opt-get.sh prefix=font-                         # todas las que empiezan por font-
$S/be-opt-get.sh all=1 > betheme-$(date +%F).json     # volcado completo (JSON)

# Escribir (revisión previa + static.css + reset JS del builder)
$S/be-opt-set.sh key=logo-height value=80 --dry-run   # muestra el diff, no escribe
$S/be-opt-set.sh key=logo-height value=80
$S/be-opt-set.sh key=font-size-h1 --json value='{"size":"48","line_height":"56","weight_style":"400","letter_spacing":"0"}'
$S/be-opt-set.sh set=@cambios.json                    # {"logo-height":"80","layout":"boxed"}

# Eliminar claves (cae al default del 2º argumento de mfn_opts_get, NO al std)
$S/be-opt-unset.sh key=logo-height --dry-run
$S/be-opt-unset.sh key=logo-height,logo-width

# Regenerar static.css a mano (tras cualquier escritura "a pelo")
$S/be-static-css-regenerate.sh
$S/be-static-css-regenerate.sh --check                # compara sin escribir
```

Equivalente con `wp` puro (sin scripts). `pluck` devuelve exit 1 si la clave no existe; `patch` no valida la forma:

```bash
wp option pluck betheme logo-height                         # lee
wp option pluck betheme font-size-h1 --format=json          # {"size":"64",...}
wp option patch update betheme logo-height 80               # escribe (crea la clave si falta con `insert`)
wp option patch update betheme font-size-h1 size 48         # subclave de un array
wp option patch delete betheme logo-height                  # elimina la clave
wp option get betheme --format=json > betheme-backup.json   # backup completo
```

Después de un `wp option patch` a pelo faltan los efectos de la sección 5. Mínimo imprescindible:

```bash
$S/be-static-css-regenerate.sh
# sin scripts:
wp eval 'global $MFN_Options; $MFN_Options->options = get_option("betheme"); require_once get_template_directory()."/functions/admin/class-mfn-helper.php"; $MFN_Options->_static_CSS(true);'
wp cache flush
```

`update_option` en wp-cli no deja revisión: si quieres poder volver atrás, guarda antes el JSON completo (`wp option get`)
o usa `be-opt-set`, que crea la revisión `backup` restaurable desde el panel (*Tools → Revisions*) o con `be-options-revisions`.

### Alternativa sin wp-cli: PHP con `wp-load.php`

Los scripts de `scripts/php/` solo dependen de `$args` (posicionales) y de WordPress cargado. Esqueleto mínimo, ejecutado
con `php be-run.php opt-set.php key=logo-height value=80` desde la raíz de WordPress:

```php
<?php
// be-run.php — lanza un script de scripts/php/ sin wp-cli
$_SERVER['HTTP_HOST'] = 'midominio.com';         // evita avisos si WP_HOME/WP_SITEURL no están en wp-config.php
require __DIR__ . '/wp-load.php';                // carga WP + Betheme; $MFN_Options ya existe (theme-options.php:11986)
$args = array_slice( $argv, 2 );                 // mismo formato clave=valor que wp eval-file
require '/ruta/a/betheme-ssh/scripts/php/' . basename( $argv[1] );
```

`is_admin()` es `false` igual que en wp-cli, así que las clases de `functions/admin/*` no están cargadas
(`betheme/functions.php:217-243`); `_lib.php` carga `Mfn_Helper` con `be_load_admin_classes()` cuando hace falta.

## 11. Trampas verificadas

- **Save del panel = fila entera.** Cualquier clave que el formulario no postea se pierde en el siguiente Save (sección 4).
  No metas claves propias en `betheme`; si necesitas datos propios usa otra option.
- **`std` no es fallback en lectura.** Vaciar o borrar `logo-height` no devuelve `60`: devuelve lo que ponga la llamada
  concreta (`include-logo.php` pasa su propio default; otras llamadas pasan `null`). Usa `be-opt-get meta=1` para ver el
  `std` y escríbelo explícitamente si quieres "volver al valor de fábrica".
- **`'0'` sí, `0`/`''` no.** Para desactivar algo cuyo default es "activo" guarda la cadena `'0'`; con `''` cae al default.
- **`condition` no filtra al guardar.** Un campo oculto por condición sigue en el DOM (`muffin-options/js/options.js:1647-1651`
  solo hace `.hide()`) y se postea; por SSH puedes escribir cualquier campo aunque su condición "no se cumpla".
- **`wp option pluck` lee la fila cruda**, sin los arreglos de `_backward_compatibility` (sección 2): `social-link` puede
  no contener las redes antiguas que el front sí muestra.
- **Escribir por SSH no regenera `static.css` ni el JS del builder.** `be_opts_save` sí; `wp option patch` no.
- **Sitios con caché de objetos persistente**: `update_option` invalida la clave `alloptions`, pero un W3TC/Redis con
  página cacheada sigue sirviendo HTML viejo; `be_post_save` llama a `w3tc_flush_all()` si existe, para otros plugins
  purga a mano.
- **`unserialize` con `allowed_classes => false`** en revisiones e import (`options.php:1131`, `:1160`): cualquier objeto
  serializado dentro de la opción se convierte en `__PHP_Incomplete_Class`. Guarda solo strings y arrays.

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-opt-get` (key, lista, prefix, all, --meta, --format=raw) y `be-opt-set` (escalar, `--json`, `--dry-run`, id desconocido rechazado con exit 1) funcionan; cada escritura crea una entrada en `betheme_revision_backup` y regenera `static.css` (md5 cambia).
- `be-opt-unset key=logo-height`: la clave desaparece de la fila, `be-opt-get` devuelve `null` con aviso, `wp option pluck` sale con 1; `be-opt-set key=logo-height value=''` la recrea. `key=imported` (inexistente) → `missing`, exit 0.
- La fila del dev tiene 742 claves frente a 829 ids del catálogo (claves que el formulario no postea se pierden en cada Save del panel).
