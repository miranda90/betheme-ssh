# 02 — Tipos de campo y forma exacta del valor

Qué guarda cada tipo de campo de Theme Options en la fila `betheme` (ver [01](01-almacenamiento-y-guardado.md)), verificado
leyendo el renderer `betheme/muffin-options/fields/<tipo>/field_<tipo>.php` de 28.4.3 y contrastado con valores reales
de un entorno de desarrollo real (`wp option pluck betheme <id> --format=json`). Los ids de ejemplo salen del catálogo
`reference/catalogo-opciones.md` (829 campos, 25 tipos en uso).

## 1. Cómo se construye el nombre del input (y por tanto la clave guardada)

`Mfn_Options_field::get_name( $meta = false, $key = false )` (`betheme/muffin-options/fields/class-mfn-options-field.php:25-61`):

| Caso | `name=` resultante | Valor guardado | Líneas |
|---|---|---|---|
| campo con clave `ajax` | `''` (sin name) | **no se guarda** | `:27-30` |
| Theme Options (`$meta = false`) | `betheme[<id>]` | `betheme[<id>]` = string | `:40-42` |
| con `$key` (dimensions, color_multi, gradient, social…) | `betheme[<id>][<key>]` | `betheme[<id>]` = array `{key: string}` | `:46-48` |
| metabox/builder (`$meta = true`) | `<id>` | no aplica a Theme Options | `:38` |

`_field_input()` (`options.php:1415-1445`) instancia `MFN_Options_<type>` con el valor guardado o, si la clave no existe,
con el `std` (`:1429-1433`), y llama a `render()`. Todo lo que postea el formulario llega como **string** (PHP `$_POST`):
los números son `"10"`, no `10`. El `std` puede ser entero (`'size' => 50`), pero tras el primer Save la fila solo tiene strings.
Por SSH conviene escribir siempre strings para que `be_diff` no marque cambios espurios (`"10" !== 10`).

## 2. Resumen: `be_shape()` de `_lib.php`

| Tipo | Forma (`be_shape`) | Campos en catálogo | Ejemplo de id |
|---|---|---|---|
| text, textarea, select, select_ajax, switch, color, icon, sliderbar, radio_img, font_select | `string` | 177, 23, 71, 6, 149, 172, 11, 16, 15, 9 | `logo-height`, `hook-top`, `layout`, `blog-page` |
| upload | `string (URL o URL#attachment_id)` | 23 | `logo-img`, `favicon-img` |
| box_shadow, text_shadow | `string` | 4, 0 | `button-box-shadow` |
| order, pills, checkbox_pseudo, category, upload_multi | `string` (csv / separado por espacios) | 0 | solo builder/metaboxes |
| checkbox | `array {clave: clave} solo marcados` | 34 | `logo-link`, `transparent` |
| color_multi | `array {subclave: color}` | 40 | `button-color` |
| gradient | `array {angle,color,color2,...}` | 8 | `button-gradient` |
| dimensions | `array {top,right,bottom,left,isLinked}` | 5 | `button-padding` |
| typography | `array {size,line_height,weight_style,letter_spacing}` | 36 | `font-size-h1` |
| boxshadow | `array {x,y,blur,spread,color,inset}` (**no está en `be_shape`**, ver §4) | 2 | `gdpr2-reopen-boxshadow` |
| social | `array {red: url, ..., order: csv}` | 1 | `social-link` |
| multi_text | `array indexado` | 1 | `sidebars` |
| multiselect, transform, group | `array` | 0, 0, 3 | — |
| dynamic_items, tabs, logic, hotspot | `array` anidado | 0 | solo builder/metaboxes |
| ajax, header, subheader, info, helper, preview, visual, custom | `no persiste` | 4, —, —, 13, —, 1, 0, 5 | `hold-cache-regenerate` |

`be-opt-set` rechaza (salvo `force=1`) un valor cuya forma no coincide: array para tipo string o viceversa (`opt-set.php:46-53`).

## 3. Tipos escalares (string)

| Tipo | Renderer | Qué guarda | Ejemplo real (dev) |
|---|---|---|---|
| `text` | `field_text.php:69` (`<input type=text>`) | texto libre; los numéricos también son string | `"section-padding": "64"`, `"logo-width": ""` |
| `textarea` | `field_textarea.php:85-87` | texto multilínea, HTML/CSS/JS sin escapar | `"hook-top": ""`, `"gdpr2-about-title": "About <span class=\"hide-mobile\">Cookies</span>"` |
| `select` | `field_select.php:42-61` (`<option value=$k>`) | la **clave** de `options`, no la etiqueta | `"builder-visibility": "edit_pages"` |
| `select_ajax` | `field_select_ajax.php:22` (hidden con `$this->value`; el texto visible es `get_the_title`) | id de post como string | `"blog-page": "102"` |
| `switch` | `field_switch.php:91` (checkbox único con `get_name`; solo se postea el marcado) | clave de `options` (normalmente `'0'`/`'1'` o `''`/`'1'`, pero también `hide-price`, `lazy`, `local`, `h1`…; consulta el catálogo) | `"variable-swatches": "1"`, `"button-background-type": ""` |
| `radio_img` | `field_radio_img.php:43` | clave de `options` | `"layout": "full-width"`, `"header-style": "classic"` |
| `color` | `field_color.php:70` | color tal cual se tecleó: `#hex`, `rgba(...)`, o `''` | `"color-a": "#006edf"` |
| `icon` | `field_icon.php:57` | clase CSS del icono (`icon-*` del pack mfn, `fas fa-*` de FA, o prefijo de un pack custom) | `"sliding-top-icon": "icon-down-open-mini"` |
| `sliderbar` | `field_sliderbar.php:148` (`<input type=number>`; `''`→std, `'0'`→0 en `:140-146`) | número **sin unidad** | `"grid-width": "1920"`, `"button-gap": "10"` |
| `font_select` | `field_font_select.php:28-62` | nombre literal de la fuente. Las custom se listan con `#` en `mfn_fonts()['custom']` pero el `#` se quita al pintar y al guardar (`:19-21`, `:61`) | `"font-content": "Verdana"`, `"font-title": "Avenir"` (custom) |

Detalle de `switch`: cada `<li>` tiene un `<input type=checkbox name="betheme[id]" value="$k">` (`field_switch.php:91`); el JS
desmarca los demás, así que llega uno solo. Si el usuario deja el "Disable" (`'0'`) marcado se guarda `'0'` y
`mfn_opts_get` lo devuelve tal cual (regla del `'0'`, doc 01 §2). Si nada está marcado no se postea y la clave desaparece.

### `upload`: `URL` o `URL#id`

`field_upload.php:51` pinta un `<input type=text>` con `$this->value`. Al elegir de la Media Library el JS escribe
`url + '#' + id` (`fields/upload/field_upload.js:31`: `.val(url +'#'+ id)`). Si se teclea a mano queda solo la URL.
Lectura: `mfn_get_attachment_data( $image, $data )` (`functions/theme-functions.php:2426-2449`) parte por `#` y usa el id si
es numérico (`:2433-2441`); si no hay id lo busca por URL con `mfn_get_attachment_id_url()` (`:2443-2445`, consulta a BD),
y pasa por `wpml_object_id` (`:2449`). Por tanto **el sufijo `#id` es opcional pero ahorra una query por imagen**.

Ejemplo real: `"favicon-img": "https://tu-sitio.example.com/wp-content/uploads/favicon-32.png"` (sin `#`, escrito a mano).
Helper: `be_upload_value( $attachment_id )` en `_lib.php` devuelve la forma `URL#id`; `be_media_import( $path )` sube y
devuelve `['id','url','value']`. Con `wp` puro: `wp media import foto.png --porcelain` → id → `wp post get <id> --field=guid`.

## 4. Tipos array

### `checkbox` — solo los marcados, más `post-meta`

`field_checkbox.php:72`: un input por opción con `name="betheme[id][$k]" value="$k"`; solo los marcados llegan al POST,
así que el array guardado es `{clave: clave}`. Además hay un `<input type=hidden name="betheme[id][post-meta]" value="1">`
(`:48-50`, "FIX | post meta save | all values unchecked") que **siempre** viaja: por eso un checkbox sin nada marcado
se guarda como `{"post-meta":"1"}` y no desaparece.

```json
"logo-link":     {"post-meta": "1", "link": "link"}
"transparent":   {"post-meta": "1"}
"prev-next-nav": {"post-meta": "1", "hide-header": "hide-header", "hide-sticky": "hide-sticky"}
```

El tema consulta **presencia**, no valor: `isset($opts['hide-sticky'])` (`betheme/single.php:26`). Nadie lee `post-meta`
(grep sin consumidores fuera del propio campo). Por SSH: `{"link":"link"}` funciona igual; para "ninguno" escribe `{}` o
`{"post-meta":"1"}`, nunca `{"link":""}` (seguiría siendo `isset`).

### `color_multi` — `{subclave: color}`

`field_color_multi.php:55-79`: un input por cada clave del `std` (`get_name($meta, $s_key)`). Las subclaves las fija el `std`
del campo (normalmente `normal`/`hover`). Ejemplo: `"button-color": {"normal": "#626262", "hover": "#626262"}`.

### `gradient` — 8 subclaves, incluida `string` precalculada

`field_gradient.php:45` (`string`), `:62` (`color`), `:84` (`location`), `:106` (`color2`), `:128` (`location2`),
`:140` (`type`: `linear-gradient`|`radial-gradient`), `:167` (`angle` 0-360), `:185` (`position`: `center center`,
`top left`…). `string` la compone PHP al pintar (`:24-39`) y el JS al editar; **si escribes por SSH calcula `string` tú**,
porque es lo que consume el CSS. Ejemplo real (vacío):

```json
"button-gradient": {"string": "linear-gradient(0deg,  0%,  100%)", "color": "", "location": "0", "color2": "", "location2": "100", "type": "linear-gradient", "angle": "0", "position": "center center"}
```

Con colores: `"string": "linear-gradient(90deg, #ff0000 0%, #0000ff 100%)"`; radial: `"radial-gradient(at center center, #f00 0%, #00f 100%)"` (`:28-32`).

### `dimensions` — `{top,right,bottom,left}` (+ `isLinked` solo en el std)

`field_dimensions.php:116` (`get_name($meta, $i)` por lado, solo en `version => separated-fields`) o `:84` (un hidden
`betheme[id]` con el string `"10 20 10 20"` en la versión `pseudo`). En 28.4.3 los 5 campos del catálogo guardan array de
4 lados. El `std` de `button-padding` trae `isLinked => 0` (`theme-options.php:1315-1320`) y por eso la fila puede
contenerlo tras un Reset; `mfn_opts_get()` lo elimina siempre (`theme-options.php:12012`). Ejemplos reales:

```json
"button-padding":        {"top": "10", "right": "20", "bottom": "10", "left": "20"}
"button-padding-tablet": {"top": "", "right": "", "bottom": "", "left": ""}
```

Sin unidad: la pone el CSS (`data-unit="px"` en la definición, `theme-options.php:1313`).

### `typography` — `{size, line_height, weight_style, letter_spacing}`

`field_typography.php:127` (`size`), `:136` (`line_height`, omitido si `disable => 'line_height'`, `:133`), `:145-165`
(`weight_style`: `''`, `100`…`900`, `100italic`…`900italic`), `:169` (`letter_spacing`). Números en px sin unidad.
Valor antiguo no-array (`< 13.5`) se migra al pintar (`:42-50`). Ejemplos reales:

```json
"font-size-h1":       {"size": "64", "line_height": "77", "weight_style": "400", "letter_spacing": "0"}
"button-font":        {"size": "14", "weight_style": "400", "letter_spacing": "0"}
"font-size-h2-tablet":{"size": "", "line_height": "", "weight_style": "", "letter_spacing": ""}
```

### `social` — `{red: url, ..., order: csv}`

`field_social.php:186` (`get_name($meta, $key)` por red, solo las que tienen definición o custom con icono) y `:201`
(hidden `order` = `implode(',', $order)`). El orden guardado se fusiona con la lista completa al pintar (`:98-107`), así
que puede faltar alguna red en `order` sin romper nada. Ejemplo real (recortado):

```json
"social-link": {"skype": "", "facebook": "https://facebook.com/x", "...": "", "tiktok": "", "order": "skype,whatsapp,facebook,twitter,vimeo,youtube,flickr,linkedin,pinterest,dribbble,instagram,snapchat,behance,tumblr,tripadvisor,vkontakte,viadeo,xing,custom,rss,tiktok"}
```

Los custom van aparte: `social-custom-icon`, `-link`, `-title` y `-icon-N`/`-link-N`/`-title-N` (N ≥ 2, compactados
por `_register_custom_social`, doc 01 §6); en `order` aparecen como `custom` y `custom-N`.

### `multi_text` — array indexado

`field_multi_text.php:21` (`$name = 'betheme[id][]'`) y `:51`: un hidden por entrada. Guarda `["Sidebar A", "Sidebar B"]`
(índices 0..n). Único campo: `sidebars` (`theme-options.php:3049`). En el dev no existe la clave (nunca se creó ninguna).

### `boxshadow` (con `std`) vs `box_shadow` (string)

- `box_shadow` (`field_box_shadow.php:47`): **string** CSS `"inset 0px 4px 24px 0px rgba(66,68,90,1)"` o `''`; si llega un
  array lo aplana con `implode(' ')` (`:31`). Orden `inset x y blur spread color` (`:19-26`). Ejemplo real: `"button-box-shadow": ""`.
- `boxshadow` (`field_boxshadow.php:52-68`): **array** `{x,y,blur,spread,color,inset}` con `get_name($meta,$input)`; std
  `{"x":"0","y":"15","blur":"30","spread":"0","color":"rgba(1,7,39,.13)","inset":0}` (catálogo, `gdpr2-reopen-boxshadow`).
  `be_shape()` lo clasifica como string por el nombre: **`be-opt-set` lo rechazará sin `force=1`** (anotado para `_lib.php`).
- `text_shadow` (`field_text_shadow.php:38`): string `"h v blur color"`. No hay ninguno en Theme Options.

## 5. Tipos que no aparecen en Theme Options 28.4.3 (solo BeBuilder / metaboxes)

Se documentan porque `be_shape()` los contempla y porque comparten renderer con el builder; su JSON de elementos está en
`~/Documents/Mis proyectos/muffinAI/docs/bebuilder/`.

| Tipo | Forma | Renderer |
|---|---|---|
| `pills` | string separada por espacios (clases) | `field_pills.php:36` (hidden) |
| `order` | string csv | `field_order.php:41` |
| `checkbox_pseudo` | string separada por espacios | `field_checkbox_pseudo.php:27` |
| `upload_multi` | string csv de ids de adjunto | `field_upload_multi.php:40`, `:58` (`explode(',')`) |
| `category` | slug de término (string) | `field_category.php` |
| `multiselect` | array de `{key, value}` (JS `mfnDbLists`) | `field_multiselect.php:27-31` |
| `transform` | array `{string, rotate, skewX, skewY, scaleX, scaleY, translateX, translateY}` | `field_transform.php:35`, `:73` |
| `backdrop_filter` | array `{subclave: valor}` bajo `inner:backdrop-filter` | `field_backdrop_filter.php:12` |
| `css_filters` | array de sliderbars `{blur, brightness, contrast, saturate, ...}` | `field_css_filters.php:17-60` |
| `dynamic_items` | array de `{url, id, uid, type}`; el `name` es `<id>[i][url]` **sin prefijo `betheme`** (`:41-44`) | `field_dynamic_items.php` |
| `tabs` | array `{label: [fila0, fila1, ...]}` (`name="<id>[label][]"`, `:198`) | `field_tabs.php:215-217` |
| `logic` | array anidado `conditions[i][j]{key,var,value}` | `field_logic.php:15-17` |
| `hotspot` | array anidado hasta 4 niveles, `name="<id>[i][k1][k2][k3]"` | `field_hotspot.php:28-35` |
| `typography_vb` | array (variante del builder) | `field_typography_vb.php` |
| `visual` | string HTML (`<textarea>` TinyMCE) | `field_visual.php:51` |

`multi_text` y `dynamic_items` construyen el `name` a mano con `$this->field['id']` y `$this->prefix` respectivamente
(`field_multi_text.php:21` sí lleva prefijo; `field_dynamic_items.php:41-44` no): en Theme Options solo existe `sidebars`.

## 6. Tipos que no persisten

`get_name()` devuelve `''` si el campo tiene la clave `ajax` (`class-mfn-options-field.php:27-30`), y estos renderers no pintan
ningún input con `name`:

| Tipo | Renderer | Ids en el catálogo | Nota |
|---|---|---|---|
| `ajax` | `field_ajax.php:19-21` (botón con `data-action`) | `blog-love-rand`, `portfolio-love-rand`, `google-font-mode-regenerate`, `hold-cache-regenerate` | disparan un AJAX con nonce; por SSH se replica el cuerpo (docs 05, 07) |
| `custom` | `field_custom.php:9-68` (solo HTML según `action`) | `featured-desc-list`, `featured-desc-single`, `elementor-enable`, `translate-wpml-installer`, +1 | los botones "Apply recommended" escriben **otros** campos vía JS |
| `group` | `field_group.php:16` | `sidecart-product-group`, `-footer-group`, `-buttons-group` | separador visual |
| `header`, `subheader`, `info`, `helper`, `preview` | sin `name` | `info-*`, `button-preview` | maquetación |

Matiz: `_default_values()` (`options.php:652-656`) sí escribe `id => ''` para estos en la **primera** creación de la fila;
el primer Save del panel los elimina (doc 01 §4). En el dev no existen (`__MISSING__` en `blog-love-rand`, `elementor-enable`…).

## 7. `condition` es solo UI

La definición de un campo puede llevar `'condition' => ['id' => 'button-background-type', 'opt' => 'isnt', 'val' => '']`
(`theme-options.php:1490`). Se vuelca como `data-condition` en la fila del formulario (`options.php:1534-1549`) y
`muffin-options/js/options.js` únicamente hace `.hide()`/`.show()` (`:1647-1651`, `:1696-1704`). El input sigue en el DOM
y se postea; `_validate_options` no mira `condition`. Por SSH puedes escribir `button-gradient` aunque
`button-background-type` sea `''`: simplemente el CSS no lo usará hasta que la condición se cumpla en el front.

## 8. Responsive: `-tablet` / `-mobile` son ids distintos

No hay un valor `{desktop, tablet, mobile}` como en el builder. Cada breakpoint es **otro campo** con su propio id y su
propio valor: 32 ids del catálogo terminan en `-tablet`/`-mobile` (`logo-width-tablet`, `button-padding-mobile`,
`font-size-h1-tablet`…), definidos con `'responsive' => 'desktop'` + `class => mfn_field_tablet` (`theme-options.php:1168-1180`).
El selector de dispositivo del panel solo alterna qué fila se ve. Excepción: `responsive-header-mobile`/`-tablet` son
checkboxes de opciones (`theme-options.php:7074-7083`), no variantes responsive.

## 9. Cómo pasar arrays a `be-opt-set`

```bash
S=~/be-ssh-test/scripts
# un id con --json (el valor es JSON, no PHP)
$S/be-opt-set.sh key=font-size-h1 --json value='{"size":"48","line_height":"56","weight_style":"600","letter_spacing":"0"}'
$S/be-opt-set.sh key=logo-link --json value='{"link":"link","h1-home":"h1-home"}'
$S/be-opt-set.sh key=button-color --json value='{"normal":"#ffffff","hover":"#cccccc"}'

# varios ids a la vez: objeto {id: valor} en fichero (strings y arrays mezclados)
cat > cambios.json <<'EOF'
{
  "logo-height": "80",
  "button-padding": {"top": "12", "right": "24", "bottom": "12", "left": "24"},
  "transparent": {}
}
EOF
$S/be-opt-set.sh set=@cambios.json --dry-run
$S/be-opt-set.sh set=@cambios.json
$S/be-opt-set.sh set=@- < cambios.json         # o por stdin
```

`be_value_parse()` (`_lib.php`) hace `json_decode(..., true)`: los objetos JSON llegan como arrays asociativos, que es lo
que serializa `update_option`. Sin `--json` el valor se guarda como string literal (`'{"size":...}'`), que es un error
habitual: `be-opt-set` lo detecta porque `be_shape` espera array.

Con `wp` puro solo se puede tocar una subclave por llamada: `wp option patch update betheme font-size-h1 size 48`
(no valida la forma ni regenera `static.css`; después ejecutar `be-static-css-regenerate`).

## 10. Trampas verificadas

- **Enteros vs strings**: el `std` de muchos campos es `int` (`'size' => 50`, `'top' => 10`), la fila tras un Save tiene
  strings. `mfn_opts_get()` no convierte; el CSS concatena. Escribe strings por SSH para no dejar tipos mezclados.
- **`checkbox` con valor `''`**: `{"link":""}` cuenta como marcado (`isset`). Para desmarcar, elimina la subclave.
- **`gradient.string`** es la única subclave que consume el CSS; si cambias `color`/`angle` y no `string`, no pasa nada en el front.
- **`font_select` custom**: el valor es el nombre sin `#` aunque `mfn_fonts()['custom']` lo devuelva con él (`field_font_select.php:61`).
- **`select` guarda la clave**: `layout = "boxed"`, no "En caja"; consulta `options` en el catálogo (`be-opt-get meta=1` no lista
  las opciones, sí `reference/catalogo-opciones.md`).
- **`boxshadow` ≠ `box_shadow`**: dos tipos distintos con formas distintas (array vs string); `be_shape()` trata `boxshadow`
  como string → usar `force=1` con los dos `gdpr*-boxshadow`.
- **`sliderbar` vacío**: `''` cae al `std` en el panel (`field_sliderbar.php:141-142`) pero en el front cae al `$default` de
  la llamada a `mfn_opts_get` (doc 01 §2); no son necesariamente el mismo número.

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `checkbox`: al escribir `post-type-disable` con `--json` sin la clave `post-meta` el tema sigue funcionando, pero el panel la añade siempre; los scripts la conservan. Con `{"post-meta":"1","client":…,"testimonial":…}` (sin `slide`) el CPT `slide` aparece en `wp post-type list`; volviendo a incluir `slide` desaparece (checkbox `invert` verificado).
- `upload`: `be-logo-set` y `be-favicon-set` guardan `URL#id` (`…/logo-test.png#179`); el front imprime solo la URL.
- `typography`/`color_multi` leídos con `--meta` muestran tipo, forma y `std` correctos.
