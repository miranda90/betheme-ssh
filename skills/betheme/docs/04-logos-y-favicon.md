# 04 — Logos y favicon

Cubre los 8 logos del header clásico (`logo-*-img`), sus ajustes (altura, padding, texto, link, anchos SVG, alineación,
avanzado), el logo del Header Builder, el override por Layout, `favicon-img` / `apple-touch-icon` y el fondo de página
`img-page-bg`. La forma general de los campos `upload` está en [02-tipos-de-campo-y-valores.md](02-tipos-de-campo-y-valores.md).

## 1. Qué hace el panel

Theme Options → **Global → Logo** (`$sections['logo']`, `betheme/muffin-options/theme-options.php:1070-1248`) y
**Responsive → Header** (`responsive-*-logo-img`, l.6839-6873). Favicon y Apple Touch Icon están en **Global → General**
(l.1052, 1059), junto con el fondo de página (`img-page-bg` l.1010, `position-page-bg` l.1016, `size-page-bg` l.1025).
Todos los campos del logo llevan la clase `hide-if-tpl-header`: el panel los oculta cuando hay un template de Header Builder
activo, porque en ese caso no se usan (ver §4).

## 2. Dónde vive: ids en `wp_options.betheme`

| id | tipo | forma | qué hace (`betheme/includes/include-logo.php`) |
|---|---|---|---|
| `logo-img` | upload | `URL#id` | logo principal; si vacío, `images/logo/logo.png` del tema (l.107) |
| `retina-logo-img` | upload | `URL#id` | `data-retina` del principal (l.112) |
| `sticky-logo-img` | upload | `URL#id` | header sticky; cae al principal si vacío (l.108) |
| `sticky-retina-logo-img` | upload | `URL#id` | retina del sticky; cae a `retina-logo-img` (l.113) |
| `responsive-logo-img` | upload | `URL#id` | móvil; cae al principal (l.109) |
| `responsive-retina-logo-img` | upload | `URL#id` | retina móvil; cae a `retina-logo-img` (l.114) |
| `responsive-sticky-logo-img` | upload | `URL#id` | móvil + sticky; cae al principal (l.110) |
| `responsive-sticky-retina-logo-img` | upload | `URL#id` | retina móvil + sticky; cae a `retina-logo-img` (l.115) |
| `logo-height` | text | número (px), placeholder 60 | `data-height` del `#logo` (l.40, 51, 54); `#Top_bar #logo{height}` |
| `logo-vertical-padding` | text | número (px), placeholder 15 | `data-padding` del `#logo` (l.41) |
| `logo-text` | text | string | texto **en vez de** imagen; añade clase `text-logo` (l.30-34, 124-126) |
| `logo-link` | checkbox | `{"link":"link","h1-home":"h1-home","h1-all":"h1-all"}` (solo marcados); std `{"link":"link"}` | `<a id="logo">` vs `<span id="logo">` (l.50-56); envoltura `<h1>` en home / interiores (l.60-70) |
| `logo-width`, `-tablet`, `-mobile` | text | número (px) | ancho de `#Top_bar #logo img.svg` por breakpoint (solo SVG; `data_attr` l.1164, 1176, 1188) |
| `logo-vertical-align` | select | `top` \| `''` (middle) \| `bottom` | clase `logo-valign-*` en `<body>` (`betheme/functions/theme-head.php:2411-2413`) |
| `logo-advanced` | checkbox | claves `no-margin`, `overflow`, `no-sticky-padding`, `sticky-width-auto` | clases `logo-no-margin`, `logo-overflow`, `logo-no-sticky-padding`, `logo-sticky-width-auto` en `<body>` (`theme-head.php:2415-2426`) |
| `favicon-img` | upload | `URL#id` | `<link rel="shortcut icon">` (`theme-head.php:239-244`) |
| `apple-touch-icon` | upload | `URL#id` | `<link rel="apple-touch-icon">` (`theme-head.php:248-253`) |
| `img-page-bg` | upload | `URL#id` | `background-image` de `html`/`body` (`theme-head.php:1020, 1026`) |
| `position-page-bg` | select | `repeat;position;attachment[;size]` separados por `;`, std `center top no-repeat` (valor legado sin `;`, que no está entre las opciones del select: escribe siempre el formato `repeat;position;attachment;`)… | `background-repeat/position/attachment` (l.1028-1039) |
| `size-page-bg` | select | `''` \| `auto` \| `contain` \| `cover` \| `cover-ultrawide` | `background-size` (l.1041-1046) |

Los `checkbox` guardados desde el panel llevan además una clave `post-meta => "1"` (input oculto,
`betheme/muffin-options/fields/checkbox/field_checkbox.php:50`); el tema solo hace `isset($opts['link'])`, así que es inocua
y los scripts la conservan si ya existía.

### Forma del valor `upload`

El panel escribe `URL + '#' + attachment_id` (`betheme/muffin-options/fields/upload/field_upload.js:31`); en el dev hay
valores antiguos sin `#id` (`favicon-img = https://…/uploads/favicon-32.png`) y funcionan: `mfn_get_attachment_data()`
(`betheme/functions/theme-functions.php:2426-2439`) usa el id si hay `#`, y si no resuelve la URL con
`mfn_get_attachment_id_url()` (l.2397). Sin id resoluble, `alt`/`height` salen vacíos. Los scripts aceptan ruta local
(importa a la Media Library) o URL; a una URL sin `#` le añaden `#id` si `attachment_url_to_postid()` lo encuentra.

## 3. Cómo se pinta el logo clásico

`include-logo.php` se incluye desde `includes/header-top-area.php:60`, `header-creative.php:44`, `header-style-shop.php:41`
y `header-style-shop-split.php:65` (`get_template_part('includes/include', 'logo')`).

1. Origen: si `mfn_layout_ID()` (`theme-functions.php:1246`) devuelve un Layout, lee las metas `mfn-post-*logo-img` del
   Layout (l.89-101); si no, las options (l.103-116). Los fallbacks de la tabla de §2 se aplican en ambos casos.
2. Condición de salida (l.120): pinta el bloque propio si hay `logo-img` **o** `logo-text` **o** no hay `custom_logo`
   en el Customizer; si no, `the_custom_logo()` (l.149). Es decir: con `logo-img` vacío, sin `logo-text` y con logo del
   Customizer, manda el Customizer.
3. Cuatro `<img>` (`logo-main`, `logo-sticky`, `logo-mobile`, `logo-mobile-sticky`) con `data-retina`, `data-height`
   (alto real del adjunto), `alt` del adjunto, clase `svg` si la URL contiene `.svg` (l.130-141). No hay `srcset`: la
   retina la cambia el JS del tema leyendo `data-retina`.
4. `logo-height`/`logo-vertical-padding` viajan como `data-height`/`data-padding` en `#logo` (l.51, 54) y además
   como CSS en `style.php` (data_attr de `logo-height`, `theme-options.php:1209`).

## 4. Header Builder: el logo NO sale de `include-logo.php`

`header.php:80-85`: si `$mfn_global['header']` tiene id (resuelto por `mfn_template_part_ID('header')`,
`betheme/functions.php:64`, condiciones en options `mfn_header_*`, ver
[10-plantillas-header-footer-popups.md](10-plantillas-header-footer-popups.md)) se carga `includes/header-template.php`
con el template; si no, `includes/header-classic.php`. Con template, el logo es el elemento **`header_logo`** del
builder (`betheme/functions/builder/class-mfn-builder-items.php:206-207` → `sc_header_logo()`,
`betheme/functions/theme-shortcodes.php:1832-1862`):

| attr del item | qué hace |
|---|---|
| `image` | `URL#id` → `mfn_get_attachment()` (l.1843-1844); URL sin `#` → `mfn_vc_image()`; con `:` → dato dinámico `be_dynamic_data()` (l.1841-1842) |
| `image` vacío | **fallback a `logo-img`** de Theme Options (l.1847-1848); si tampoco, placeholder SVG |
| `link` | href; vacío o `/` → `siteurl` (l.1854) |

El campo del builder (`class-mfn-builder-fields.php:15293-15300`) tiene `std = logo-img` y `dynamic_data => featured_image`.
El dato dinámico `{featured_image:site}` devuelve **`mfn_opts_get('logo-img')`**
(`betheme/functions/modules/class-mfn-dynamic-data.php:209-210`), no el `custom_logo` del Customizer.

Por tanto, con Header Builder hay dos formas de cambiar el logo por SSH:

- **Centralizada**: dejar `image` vacío (o `{featured_image:site}`) en el item y gobernarlo con `be-logo-set.sh main=…`.
- **Por template**: editar `mfn-page-items` del template (`attr.image`, `attr.link`) — es JSON de BeBuilder, fuera del
  alcance de este doc: ver `~/Documents/Mis proyectos/muffinAI/docs/bebuilder/` y
  [10-plantillas-header-footer-popups.md](10-plantillas-header-footer-popups.md). En el dev el item del template 93
  tiene `image = https://…/uploads/logotipo-blanco.svg#126` y `link = /`, así que `logo-img` (vacío) no interviene.

Retina/sticky/móvil del header clásico no existen en `header_logo`: se resuelven con anchos por breakpoint del item
(`css_advanced_flex`) y, si hace falta otro logo sticky, con la sección sticky del template.

## 5. Override por Layout (CPT deprecado)

Un Layout (`betheme/functions/post-types/class-mfn-post-type-layout.php`) define `mfn-post-logo-img`,
`mfn-post-retina-logo-img`, `mfn-post-sticky-logo-img`, `mfn-post-sticky-retina-logo-img`, `mfn-post-responsive-logo-img`,
`mfn-post-responsive-retina-logo-img`, `mfn-post-responsive-sticky-logo-img`, `mfn-post-responsive-sticky-retina-logo-img`
(l.105-158) y `mfn-post-bg` / `mfn-post-bg-pos` (l.84, 90). Se aplica cuando la página tiene `mfn-post-custom-layout`
o hay `blog-single-layout` / `portfolio-single-layout` (`theme-functions.php:1246-1274`). Cambio por SSH:
`wp post meta update <layout_id> mfn-post-logo-img 'URL#id'` (no pasa por `betheme`, no requiere `be_opts_save`).

## 6. Favicon y Apple Touch Icon

`theme-head.php:239-244`: imprime `favicon-img` (parte anterior al `#`) si está definido **o** si no hay Site Icon de WP
(`has_site_icon()`); sin ninguno, `images/favicon.ico` del tema. `apple-touch-icon` solo si tiene valor (l.248-253).
WordPress imprime además sus propios `<link rel="icon">` del Site Icon (`wp_site_icon`): con ambos definidos el navegador
suele quedarse con el último `<link>`; para un solo origen, vacía `favicon-img` (`clear=1`) o `wp option delete site_icon`.

## 7. Recetas SSH

```bash
cd ~/public_html
S=~/be-ssh-test/scripts          # o donde estén los scripts

# logo principal + retina desde ficheros locales (import a Media Library → URL#id)
$S/be-logo-set.sh main=/tmp/logo.svg retina=/tmp/logo@2x.png --dry-run
$S/be-logo-set.sh main=/tmp/logo.svg retina=/tmp/logo@2x.png

# sticky y móvil con URLs ya subidas; altura/padding; link + H1 en home; ancho SVG por breakpoint
$S/be-logo-set.sh sticky=https://sitio.com/wp-content/uploads/2026/01/logo-sticky.svg mobile=... \
  height=70 padding=10 link=link,h1-home width=180 width-tablet=140 width-mobile=110

# logo de texto (sustituye a la imagen) / vaciar los 8 logos
$S/be-logo-set.sh text="Mi marca"
$S/be-logo-set.sh clear=1

# favicon + apple touch icon
$S/be-favicon-set.sh favicon=/tmp/favicon-32.png apple=/tmp/apple-touch-180.png
$S/be-favicon-set.sh clear=1

# comprobar
wp option pluck betheme logo-img; wp option pluck betheme logo-link --format=json
curl -s https://sitio.com/ | grep -o '<img class="logo-main[^>]*>\|<link rel="shortcut icon"[^>]*>\|<link rel="apple-touch-icon"[^>]*>'
```

Equivalente en `wp` puro (sin static.css ni revisión; solo válido si `static-css` está desactivado):

```bash
ID=$(wp media import /tmp/logo.svg --porcelain)
wp option patch update betheme logo-img "$(wp post get $ID --field=guid)#$ID"
wp option patch update betheme logo-height 70
wp option patch update betheme favicon-img "$(wp post get $(wp media import /tmp/favicon.png --porcelain) --field=guid)"
```

`wp option patch update` sobre `logo-link` (array) no vale: usa `be-opt-set.sh key=logo-link --json value='{"link":"link","h1-home":"h1-home"}'`.

## 8. Trampas verificadas

- **Header Builder activo = `logo-*` ignorados** salvo como fallback de `header_logo` con `image` vacío (§4). `be-logo-set`
  avisa si existe `mfn_header_entire_site`.
- **`logo-img` vacío + `custom_logo` del Customizer + sin `logo-text`** → el header clásico pinta `the_custom_logo()`
  (`include-logo.php:120,149`), no el logo del tema.
- **La retina no usa `srcset`**: `data-retina` la aplica JS; un `retina-logo-img` sin el principal no se ve.
- **`logo-width*` solo afecta a SVG** (`img.svg`, `theme-options.php:1164`); para PNG manda `logo-height`.
- **`logo-height`/`padding` van sin unidad** (número). Vacío o borrado equivalen: `MFN_Options::get()` (`options.php:629-631`) devuelve el
  default de la llamada cuando el valor está vacío, así que `include-logo.php:40` y `style.php:1299` caen a 60/15 (no al `std`).
- **`favicon-img` compite con el Site Icon** (§6).
- **static.css**: `logo-height` y `logo-width*` entran en `style.php`; escribir por `wp option patch` con `static-css`
  activo deja el CSS viejo hasta `be-static-css-regenerate.sh` (los scripts lo hacen solos).
- **El `alt` del logo** sale del adjunto (`_wp_attachment_image_alt` o título): sin `#id` resoluble queda vacío.

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-logo-set main=/ruta/logo-test.png height=61 link=link,h1-home` → `logo-img = …/logo-test.png#179`, `logo-height = "61"`, `logo-link = {"post-meta":"1","link":"link","h1-home":"h1-home"}`. El dev usa Header Builder (`mfn_header_entire_site = 93` con `header_logo` propio), así que el logo de Theme Options no se ve en el front. `clear=1` vacía los ocho `*logo-img`.
- `be-favicon-set favicon=/ruta/fav-test.png` → `favicon-img = …/fav-test.png#180` y el HTML imprime `<link rel="shortcut icon" href="…/fav-test.png">` (sin el `#id`), junto a los dos `apple-touch-icon` que ya tenía el sitio.
- Restaurado todo con `be-options-import in=pre.json --yes` y `wp post delete <id> --force` de los adjuntos de prueba.
