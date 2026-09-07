# 03 — Theme Options por pestaña

Recorrido "humano" del panel **Betheme > Theme Options** (Betheme 28.4.3): qué controla cada pestaña y cada
sección, qué opciones toca de verdad un integrador y cómo escribirlas por SSH. La lista exhaustiva de los 829
campos está en [reference/catalogo-opciones.md](reference/catalogo-opciones.md); aquí solo aparecen los de uso real.
Cómo se guardan: [01-almacenamiento-y-guardado.md](01-almacenamiento-y-guardado.md). Forma de cada tipo de valor:
[02-tipos-de-campo-y-valores.md](02-tipos-de-campo-y-valores.md).

## 1. Cómo está organizado el panel

Todo el panel se define en `mfn_opts_setup()` (`betheme/muffin-options/theme-options.php:771`) en tres niveles:

| Nivel | Dónde | Qué es |
|---|---|---|
| **menu** (pestaña) | `$menu = array(` (`theme-options.php:786-936`) | 21 pestañas con `title` y lista `sections`; la clave es `global`, `header-subheader`, `mab`, `bps`… (la última abierta se recuerda en `last_tab`). |
| **section** (subpestaña) | `$sections['<id>'] = array(` (una por sección, líneas en cada apartado de abajo) | `title`, `icon` y `fields`. |
| **field** (campo) | cada `array( 'id' => …, 'type' => …, 'std' => …, 'options' => … )` | Solo los campos con `id` persisten, en `wp_options.betheme[<id>]`. `condition` es solo UI. |

Dos filtros quitan pestañas enteras de `global`: `betheme_disable_advanced` y `betheme_disable_hooks`
(`theme-options.php:777-781`, `$global_sections` en la l.775). **Solo ocultan la UI**: las opciones siguen
guardadas y `mfn_opts_get()` las sigue leyendo, así que por SSH se escriben igual.

White-label: Betheme no tiene opción para ello; son filtros que consume el builder y el dashboard:
`betheme_slug` (slug del action `mfn-live-builder`, `betheme/visual-builder/visual-builder.php:104`),
`betheme_label` (`betheme/visual-builder/visual-builder-header.php:20`, `functions/admin/class-mfn-dashboard.php:335`)
y `betheme_logo` / `betheme_logo_nohtml` (`visual-builder.php:10`). Témini los implementa en `temini-customize.php`
→ [12-temini-ajustes-propios.md](12-temini-ajustes-propios.md). La constante `WHITE_LABEL` además oculta el importador
de demos (`functions/importer/class-mfn-importer.php:27`).

Muchos campos de Header/Menu/Responsive llevan `'class' => 'hide-if-tpl-header'` (p. ej. `sticky-header`,
`theme-options.php:2584`): el panel los esconde cuando hay una plantilla de Header Builder activa, porque entonces
manda la plantilla → [10-plantillas-header-footer-popups.md](10-plantillas-header-footer-popups.md).

### Cómo leer las tablas y las recetas

Columnas: `id` | tipo de campo | valores posibles (`std` en negrita cuando existe) | qué hace. Los valores
vienen del catálogo generado; `''` es la cadena vacía (casi siempre "Predeterminado").

Receta genérica (los scripts hacen revisión + `static.css` + reset del JS del BeBuilder; ver
[01](01-almacenamiento-y-guardado.md)):

```bash
cd ~/public_html                      # raíz de WordPress (o exporta WP_PATH)
S=temini/docs/betheme-ssh/scripts     # o donde estén los scripts

$S/be-opt-get.sh key=layout                                   # leer
$S/be-opt-set.sh key=layout value=boxed --dry-run             # ver el diff
$S/be-opt-set.sh key=layout value=boxed                       # escalar
$S/be-opt-set.sh key=transparent --json value='{"header":"header"}'   # array (checkbox)
$S/be-opt-set.sh set=@cambios.json                            # varios ids a la vez {id: valor}
```

Equivalente en `wp` puro (sin scripts; **no** regenera `static.css` ni la revisión, hazlo a mano con
[07](07-herramientas-tools.md)):

```bash
wp option pluck betheme layout
wp option patch update betheme layout boxed
wp option patch update betheme transparent '{"header":"header"}' --format=json
```

## 2. Global (`global`)

### 2.1 General — `$sections['general']` (`theme-options.php:942`)

Anchura y tipo de contenedor del sitio, fondo de página y favicon. Es lo primero que se ajusta en un sitio nuevo.

| id | tipo | valores | qué hace |
|---|---|---|---|
| `layout` | radio_img | **`full-width`**, `boxed` | Contenedor a todo el ancho o caja centrada |
| `grid-width` | sliderbar | px, **`1240`** | Anchura del grid (`.container`) |
| `section-padding` | text | px, **`0`** | Padding lateral de las secciones del builder |
| `style` | radio_img | **`''`**, `simple` | Estilo base (simple quita sombras/bordes) |
| `transparent` | checkbox | `header`, `menu`, `content`, `footer` | Transparencias de cada zona (solo marcados) |
| `img-page-bg`, `position-page-bg`, `size-page-bg` | upload / select / select | URL#id; `no-repeat;center top;;`…; `auto`,`contain`,`cover`,`cover-ultrawide` | Fondo del `body` |
| `favicon-img`, `apple-touch-icon` | upload | `URL#attachment_id` | → [04-logos-y-favicon.md](04-logos-y-favicon.md) |

```bash
$S/be-opt-set.sh key=grid-width value=1320
$S/be-opt-set.sh key=transparent --json value='{"header":"header","menu":"menu"}'
```

### 2.2 Logo — `$sections['logo']` (`theme-options.php:1070`)

Logo principal, retina, sticky, texto alternativo, altura y padding. Cubierto entero en
[04-logos-y-favicon.md](04-logos-y-favicon.md) (`logo-img`, `retina-logo-img`, `sticky-logo-img`,
`sticky-retina-logo-img`, `logo-text`, `logo-link`, `logo-width*`, `logo-height`, `logo-vertical-padding`,
`logo-vertical-align`, `logo-advanced`).

### 2.3 Buttons — `$sections['buttons']` (`theme-options.php:1252`)

Estilo global de los botones (`.button`): tipografía, padding, radio, color por variante
(default / highlighted / shop / action). Casi todo son `color_multi` `{normal,hover}` y `dimensions`.

| id | tipo | valores | qué hace |
|---|---|---|---|
| `button-font-family` | font_select | nombre de fuente, **`''`** | Familia |
| `button-font` (+`-tablet`, `-mobile`) | typography | `{size,line_height,weight_style,letter_spacing}` | Tamaño/peso |
| `button-padding` (+responsive) | dimensions | `{top,right,bottom,left}` | Padding |
| `button-border-radius` | dimensions | **`"3"`** o `{top,…}` | Radio |
| `button-animation` | select | **`''`**, `fade`, `slide slide-right/left/top/bottom` | Animación hover |
| `button-color`, `button-background`, `button-border-color` | color_multi | `{"normal":"#…","hover":"#…"}` | Colores de la variante por defecto |
| `button-highlighted-*`, `button-shop-*`, `button-action-*` | color_multi / switch / gradient | igual | Variantes |

```bash
$S/be-opt-set.sh key=button-border-radius --json value='{"top":"8","right":"8","bottom":"8","left":"8"}'
$S/be-opt-set.sh key=button-highlighted-background --json value='{"normal":"#0b5cff","hover":"#0847c4"}'
```

### 2.4 Image frame — `$sections['frame']` (`theme-options.php:1807`)

Efecto hover de las imágenes con enlace (lightbox/portfolio).

| id | tipo | valores | qué hace |
|---|---|---|---|
| `image-frame-style` | radio_img | **`modern-overlay`**, `overlay`, `''`, `zoom`, `disable` | Efecto |
| `image-frame-border-width` | text | px, **`0`** | Borde |
| `image-frame-caption` | switch | `''`, `on` | Mostrar caption |
| `color-imageframe-mask-new` | color | **`rgba(0,0,0,.15)`** | Máscara hover |

### 2.5 Sliders — `$sections['sliders']` (`theme-options.php:1928`)

Autoplay (ms) de cada slider del tema: `slider-blog-timeout`, `slider-clients-timeout`, `slider-offer-timeout`,
`slider-portfolio-timeout`, `slider-shop-timeout`, `slider-slider-timeout`, `slider-testimonials-timeout`
(text, **`0`** = sin autoplay).

```bash
$S/be-opt-set.sh key=slider-testimonials-timeout value=6000
```

### 2.6 Navigation & share — `$sections['navigation']` (`theme-options.php:2009`)

Flechas prev/next en single de blog/portfolio/producto, paginación y caja "Share".

| id | tipo | valores | qué hace |
|---|---|---|---|
| `prev-next-nav` | checkbox **invert** | `hide-header`, `hide-sticky`, `in-same-term` | Marcado = oculto/limitado (ver §23) |
| `prev-next-style` | switch | `''`, **`minimal`** | Flechas en header |
| `prev-next-sticky-style` | switch | `''`, `images`, `arrows` | Flechas sticky |
| `pagination-show-all` | switch | `0`, **`1`** | Paginación con todos los números |
| `share` | switch | `0`, `hide-mobile`, **`1`** | Caja Share |
| `share-style` | switch | `''`, **`simple`** | Estilo |

### 2.7 Advanced — `$sections['advanced']` (`theme-options.php:2144`)

Preloader, visibilidad del BeBuilder, API de Google Maps y los dos interruptores que desactivan CPTs y funciones
del tema. Se oculta con `betheme_disable_advanced`.

| id | tipo | valores | qué hace |
|---|---|---|---|
| `preloader`, `preloader-image` | switch, upload | `''`, `load`; URL#id | Preloader de página |
| `display-order` | select | `0`, `1` | Orden contenido/builder |
| `content-remove-padding` | switch | `1`, **`0`** | Quitar padding superior de `#Content` |
| `builder-visibility` | select | **`edit_theme_options`**, `edit_pages`, `''`, `hide` | Quién ve el BeBuilder; `hide` lo desactiva y además evita `generate_bebuilder_items()` al guardar (`options.php:951`) |
| `builder-blocks` | switch | `0`, `1` | BeBuilder Blocks clásico |
| `google-maps-api-key` | text | clave | Mapas |
| `no-hover` | select | `''`, `tablet`, `all` | Desactiva efectos hover |
| `layout-options` | checkbox **invert** | `no-shadows` (**std**), `boxed-no-margin` | Marcado = sin sombras / sin margen boxed |
| `post-type-disable` | checkbox **invert** | `client`, `layout`, `offer`, `portfolio`, `slide`, `template`, `testimonial` | Marcado = CPT **desactivado** (§23) |
| `theme-disable` | checkbox **invert** | `categories-sidebars`, `custom-icons`, `mega-menu`, `builder-preview`, `demo-data`, `svg-allow` (**std**), `json-allow` | Marcado = función **desactivada** (§23) |
| `builder-autosave` | switch | `1`, **`''`** | Autosave del builder |
| `builder-storage` | select | **`''`**, `non-utf-8`, `encode` | Formato de `mfn-page-items` (§23) |
| `slider-shortcode` | text | shortcode | Slider global forzado en todas las páginas |
| `hide_editor` | switch | `1`, **`0`** | Oculta el editor de WP (`options.php:722`) |

```bash
# permitir SVG (quitar svg-allow de la lista de desactivados) y desactivar Clients + Testimonials
$S/be-opt-set.sh key=theme-disable --json value='{}'
$S/be-opt-set.sh key=post-type-disable --json value='{"client":"client","testimonial":"testimonial"}'
$S/be-opt-set.sh key=builder-visibility value=edit_pages
```

### 2.8 Hooks — `$sections['hooks']` (`theme-options.php:2412`)

Cuatro textareas cuyo contenido se imprime con `do_shortcode()` en `functions/theme-hooks.php`
(`hook-top` l.19 tras `<body>`, `hook-content-before` l.34 antes de `#Content`, `hook-content-after` l.49 después,
`hook-bottom` l.64 antes de `</body>`). Se oculta con `betheme_disable_hooks`. Admiten HTML y shortcodes; para
scripts de tracking es mejor la pestaña SEO (§12).

```bash
$S/be-opt-set.sh key=hook-bottom value=@/ruta/snippet.html       # valor largo desde fichero
wp option patch update betheme hook-bottom "$(cat /ruta/snippet.html)"
```

## 3. Header & Subheader (`header-subheader`)

### 3.1 Header — `$sections['header']` (`theme-options.php:2461`)

Estilo de la cabecera "clásica" (la que no viene de una plantilla de Header Builder), altura, fondo y sticky.

| id | tipo | valores | qué hace |
|---|---|---|---|
| `header-style` | radio_img | **`classic`**, `modern`, `plain`, `stack,left`, `stack,center`, `stack,right`, `stack,magazine`, `creative`, `creative,rtl`, `creative,open`, `creative,open,rtl`, `fixed`, `transparent`, `simple`, `simple,empty`, `below`, `split`, `split,semi`, `below,split`, `overlay,transparent`, `shop`, `shop-split` | Estilo de header |
| `header-fw` | checkbox | `full-width`, `header-boxed` | Anchura |
| `header-height` | text | px, **`250`** | Alto (solo estilos con slider/fondo) |
| `img-subheader-bg`, `img-subheader-attachment`, `size-subheader-bg` | upload/select/select | URL#id; posición; tamaño | Fondo del header |
| `top-bar-bg-img`, `top-bar-bg-position` | upload/select | | Fondo del top bar |
| `sticky-header` | switch | `0`, **`1`** (`theme-options.php:2576-2583`) | Header fijo al hacer scroll |
| `sticky-header-style` | select | `tb-color`, `white`, `dark` | Color del sticky |

```bash
$S/be-opt-set.sh key=header-style value=creative,open
$S/be-opt-set.sh key=sticky-header value=0
```

### 3.2 Subheader — `$sections['subheader']` (`theme-options.php:2605`)

Franja de título + breadcrumbs bajo el header.

| id | tipo | valores | qué hace |
|---|---|---|---|
| `subheader-style` | select | **`both-center`**, `both-left`, `both-right`, `''`, `title-right` | Disposición |
| `subheader` | checkbox | `hide-breadcrumbs`, `hide-title`, `hide-subheader` | Ocultar partes |
| `subheader-padding` | text | px | Padding |
| `subheader-title-tag` | switch | **`h1`**…`h6`, `span` | Etiqueta del título |
| `subheader-image`, `subheader-position`, `subheader-size`, `subheader-transparent` | upload/select/select/sliderbar | ; ; ; **`100`** | Fondo |
| `subheader-advanced` | checkbox | `breadcrumbs-link`, `slider-show` | Extras |

```bash
$S/be-opt-set.sh key=subheader --json value='{"hide-subheader":"hide-subheader"}'
```

### 3.3 Extras — `$sections['extras']` (`theme-options.php:2736`)

Botón de acción del header, selector WPML y "sliding top".

| id | tipo | valores | qué hace |
|---|---|---|---|
| `top-bar-right-hide` | switch | `1`, **`0`** | Oculta la zona derecha del top bar |
| `header-action-title`, `header-action-link`, `header-action-target` | text, text, checkbox(`target`,`scroll`) | | Botón de acción |
| `header-wpml`, `header-wpml-options` | select, checkbox | `''`, `dropdown-name`, `horizontal`, `horizontal-code`, `hide`; `link-to-home` | Selector de idioma |
| `header-banner` | textarea | HTML | Banner en header |
| `sliding-top`, `sliding-top-icon` | select, icon | `1`, `center`, `left`, **`0`**; **`icon-down-open-mini`** | Panel desplegable superior |

## 4. Menu & Action Bar (`mab`)

### 4.1 Menu — `$sections['menu']` (`theme-options.php:2883`)

| id | tipo | valores | qué hace |
|---|---|---|---|
| `menu-style` | select | **`link-color`**, `''`, `line-below`, `line-below-80`, `line-below-80-1`, `arrow-top`, `arrow-bottom`, `highlight`, `hide` | Estilo del ítem activo |
| `menu-options` | checkbox | `align-right` (**std**), `menu-arrows`, `hide-borders`, `submenu-active`, `last` | Opciones |
| `menu-creative-options` | checkbox | `scroll`, `dropdown` | Solo header creative |
| `menu-mega-style` | select | `''`, `vertical` | Mega menú |

```bash
$S/be-opt-set.sh key=menu-options --json value='{"align-right":"align-right","menu-arrows":"menu-arrows"}'
```

### 4.2 Action Bar — `$sections['action-bar']` (`theme-options.php:2977`)

| id | tipo | valores | qué hace |
|---|---|---|---|
| `action-bar` | checkbox | `show`, `creative`, `side-slide` | Dónde se muestra la barra |
| `header-slogan`, `header-phone`, `header-phone-2`, `header-email` | text | texto | Datos de contacto de la barra |

## 5. Sidebars (`sidebars`) — `$sections['sidebars']` (`theme-options.php:3036`)

Registro de sidebars y layout por defecto de páginas, posts, portfolio y búsqueda. `sidebars` es un `multi_text`
(array indexado de nombres) y los `*-sidebar` son `text` con el nombre de la sidebar (dinámicos).

| id | tipo | valores | qué hace |
|---|---|---|---|
| `sidebars` | multi_text | `["Sidebar","Blog"]` | Sidebars registradas |
| `sidebar-width` | sliderbar | %, **`23`** | Anchura |
| `sidebar-style`, `sidebar-lines`, `sidebar-sticky` | switch | `classic`/**`simple`**; **`lines-hidden`**/`lines-boxed`/`''`; `0`/**`1`** | Estilo |
| `single-page-layout`, `single-layout`, `single-portfolio-layout` | radio_img | `''`, `no-sidebar`, `left-sidebar`, `right-sidebar`, `both-sidebars`, `offcanvas-sidebar` | Layout por defecto |
| `single-page-sidebar`, `single-sidebar`, `single-portfolio-sidebar` (+`2`) | text | nombre | Sidebar asignada |
| `search-layout` | radio_img | **`no-sidebar`**, `left-sidebar`, `right-sidebar`, `offcanvas-sidebar` | Página de búsqueda |

```bash
$S/be-opt-set.sh key=sidebars --json value='["Sidebar","Blog"]'
$S/be-opt-set.sh key=single-layout value=right-sidebar
$S/be-opt-set.sh key=single-sidebar value=Blog
```

## 6. Blog & Portfolio (`bps`)

### 6.1 General — `$sections['bps-general']` (`theme-options.php:3293`)

`excerpt-length` (text, **`26`** palabras), `love` (switch `0`/**`1`**), `featured-image-caption`
(`hide`, `hide-mobile`, `''`), `related-style` (`''`/**`simple`**), `title-heading` (**`1`**…`6`: etiqueta H del título en single).

### 6.2 Blog — `$sections['blog']` (`theme-options.php:3383`)

| id | tipo | valores | qué hace |
|---|---|---|---|
| `blog-posts` | text | **`9`** | Posts por página |
| `blog-layout` | radio_img | **`grid`**, `classic`, `masonry`, `masonry tiles`, `photo`, `photo2`, `timeline` | Listado |
| `blog-columns` | sliderbar | **`3`** | Columnas |
| `blog-title-tag` | select | `2`, `3`, **`4`**, `5`, `6` | H del título en listado |
| `blog-page` | select_ajax | ID de página | Página del blog (dinámico) |
| `blog-orderby`, `blog-order` | select | **`date`**/`title`/`rand`; `ASC`/**`DESC`** | Orden |
| `blog-meta` | checkbox | `author`, `date`, `categories` (**std los 3**) | Meta visible |
| `blog-load-more`, `blog-infinite-scroll` | switch | `0`/`1` | Carga |
| `blog-filters` | select | **`1`**, `only-categories`, `only-tags`, `only-authors`, `0` | Filtros |
| `blog-author`, `blog-comments`, `blog-single-zoom` | switch | `0`/**`1`** | Single |
| `blog-featured-image-hide` | switch | `hide`, **`''`** | Imagen destacada en single |
| `blog-related`, `blog-related-columns` | text, sliderbar | **`3`**, **`3`** | Relacionados |

```bash
$S/be-opt-set.sh key=blog-layout value=masonry
$S/be-opt-set.sh key=blog-meta --json value='{"date":"date"}'
```

### 6.3 Portfolio — `$sections['portfolio']` (`theme-options.php:3793`)

En Témini el CPT `portfolio` está reetiquetado como "proyectos" ([12](12-temini-ajustes-propios.md)); los ids no cambian.

| id | tipo | valores | qué hace |
|---|---|---|---|
| `portfolio-posts` | text | **`9`** | Por página |
| `portfolio-layout` | radio_img | **`grid`**, `flat`, `masonry`, `masonry-hover`, `masonry-minimal`, `masonry-flat`, `list`, `exposure` | Listado |
| `portfolio-columns` | sliderbar | **`3`** | Columnas |
| `portfolio-page` | select_ajax | ID | Página del portfolio |
| `portfolio-orderby`, `portfolio-order` | select | **`date`**/`menu_order`/`title`/`rand`; **`DESC`** | Orden |
| `portfolio-external` | select | `''`, `popup`, `disable`, `_self`, `_blank` | Enlace del proyecto |
| `portfolio-meta` | checkbox | `author`, `date`, `categories` | Meta |
| `portfolio-filters`, `portfolio-isotope` | select, switch | **`1`**/`only-categories`/`0`; `0`/**`1`** | Filtros |
| `portfolio-featured-image-hide`, `portfolio-comments`, `portfolio-related` | switch, switch, text | `hide`/`''`; `0`/`1`; **`3`** | Single |
| `portfolio-slug`, `portfolio-tax` | text | **`portfolio-item`**, **`portfolio-types`** | Slugs (tras cambiar: `wp rewrite flush`) |

```bash
$S/be-opt-set.sh key=portfolio-slug value=proyecto && wp rewrite flush
```

### 6.4 Featured image — `$sections['featured-image']` (`theme-options.php:4143`)

Tamaños de miniatura `blog-portfolio` (archivos) y `blog-single`: `featured-blog-portfolio-width/height/crop`
(**`960`**/**`750`**/`crop`|`resize`), `featured-single-width/height/crop` (**`1200`**/**`480`**), `srcset-featured-image`
(`0`/`1`). Tras cambiarlos hay que regenerar miniaturas → [07-herramientas-tools.md](07-herramientas-tools.md).

## 7. Shop (`shop`) — requiere WooCommerce

### 7.1 General — `$sections['shop']` (`theme-options.php:4274`)

| id | tipo | valores | qué hace |
|---|---|---|---|
| `shop-sidebar` | select | nombre de sidebar (dinámico) | Sidebar de tienda |
| `shop-catalogue` | switch | **`0`**, `1`, `hide-price` | Modo catálogo (quita "Añadir al carrito"; `hide-price` también el precio) |
| `variable-swatches` | switch | `0`/**`1`** | Swatches de variaciones |
| `shop-image-width`, `single-product-main-image-size`, `single-product-thumbnails-size` | text | **`800`**, **`800`**, **`300`** | Tamaños de imagen |
| `product-badge-new`, `product-badge-new-days`, `product-badge-new-text` | switch, text, text | `0`/`1`; **`14`**; **`NEW`** | Badge "nuevo" |
| `sale-badge-style`, `sale-badge-label` | switch, text | **`label`**/`percent` | Badge oferta |
| `shop-icons-hide` | checkbox **invert** | `user`, `wishlist`, `cart` | Marcado = icono **oculto** en header |
| `shop-cart-total-hide` | checkbox **invert** | `desktop`, `tablet`, `mobile` | Marcado = total del carrito oculto |
| `shop-wishlist`, `shop-wishlist-page` | switch, select_ajax | `0`/`1`; ID | Wishlist |
| `gutenberg-checkout`, `gutenberg-checkout-options` | switch, checkbox **invert** | `''`/`1`; `hide-subheader`,`hide-steps`,`hide-footer` | Checkout por bloques |

```bash
$S/be-opt-set.sh key=shop-catalogue value=hide-price
$S/be-opt-set.sh key=shop-icons-hide --json value='{"wishlist":"wishlist"}'
```

### 7.2 Products list — `$sections['shop-list']` (`theme-options.php:4696`)

`shop-products` (text, **`12`** por página), `shop-layout` (radio_img: `grid col-2`, **`grid`**, `grid col-4`,
`masonry`, `list`, `custom_tmpl` → usa `shop-template`), `mobile-products-row` (`1`/**`2`** columnas en móvil),
`shop-images` (`''`, `secondary`, `slider`, `plugin`: imagen al hover), `shop-title-tag` (`h1`…`p.lead`),
`shop-excerpt` / `shop-button` (`0`/`1`/`list`), `shop-quick-view`, `shop-infinite-load` (`0`/`1`) y la barra de
filtros `shop-list-active-filters`, `shop-list-perpage`, `shop-list-layout`, `shop-list-sorting`,
`shop-list-results-count` (switch `0`/`1`).

### 7.3 Single product — `$sections['shop-single']` (`theme-options.php:4984`)

`shop-product-style` (radio_img: **`default`**, `modern`, `wide`, `wide tabs`, `''`, `tabs`, `custom_tmpl`),
`shop-product-gallery` (select: `''`, `mfn-thumbnails-bottom mfn-bottom-left|center|right`,
`mfn-thumbnails-left mfn-left-top|center|bottom`, `mfn-thumbnails-right mfn-right-top|center|bottom`, `mfn-gallery-grid`),
`shop-single-image` (`''`/`disable-zoom`), `shop-product-title` (`''`/`content-sub`/`sub`), `shop-product-tag`
(**`h1`**…`span`), `shop-related` (text, **`3`**).

### 7.4 Addons — `$sections['shop-addons']` (`theme-options.php:5282`)

`free-delivery-addon` (`0`/`1`) + `free-delivery-sum` (**`200`**); `fake-sale-addon` (`0`/`1`) y sus `fake-sale-*`;
`shop-sidecart` (`''`/`1`) + `shop-sidecart-continue-shopping`.

### 7.5 Addons design — `$sections['shop-addons-design']` (`theme-options.php:5467`)

Solo colores de los addons anteriores (`fake-sale-container-*`, `free-delivery-color-*`,
`product-list-gallery-slider-*`, `sidecart-*`): unos 40 campos `color`/`color_multi`. Ejemplos:
`sidecart-background` (color), `sidecart-button-background` (color_multi `{normal,hover}`),
`free-delivery-color-active` (color). Lista completa:
[reference/catalogo-opciones.md#addons-design-shop-addons-design](reference/catalogo-opciones.md#addons-design-shop-addons-design).

## 8. Pages (`pages`)

- **General** — `$sections['pages-general']` (`theme-options.php:5884`): `page-comments` (switch **`0`**/`1`).
- **Error 404** — `$sections['pages-404']` (`theme-options.php:5912`): `error404-page` (select_ajax, ID de página
  propia), `error404-header` / `error404-footer` (switch **`0`**/`1`, solo si hay página), `error404-icon` (**`icon-traffic-cone`**).
- **Under construction** — `$sections['pages-under']` (`theme-options.php:5974`): `construction` (switch **`0`**/`1`,
  **interruptor maestro**: con `1` los no logueados ven la página "en construcción", `theme-functions.php:3688`;
  si hay `construction-page` se sirve esa página, `theme-head.php:850-852`), `construction-page` (ID),
  `construction-title` (**`Coming Soon`**), `construction-text`, `construction-date` (**`12/30/2018 12:00:00`**),
  `construction-offset` (UTC, `-12`…`+14`), `logo-under-construction` (upload).

```bash
$S/be-opt-set.sh key=error404-page value=123
$S/be-opt-set.sh key=construction value=1        # activar modo en construcción; value=0 para quitarlo
```

## 9. Footer (`footer`) — `$sections['footer']` (`theme-options.php:6084`)

Footer "clásico" de widgets (si hay plantilla de Footer Builder, manda la plantilla → [10](10-plantillas-header-footer-popups.md)).

| id | tipo | valores | qué hace |
|---|---|---|---|
| `footer-layout` | radio_img | `''`, `5;one-fifth;…`, `4;one-fourth;one-fourth;one-fourth;one-fourth`, `3;one-third;one-third;one-third;`, `2;one-second;one-second;;`, `1;one;;;`… (14) | Columnas de widgets: `N;col1;col2;col3;col4` |
| `footer-style` | select | `''`, `fixed`, `sliding`, `stick`, `hide` | Comportamiento |
| `footer-padding` | text | **`70px 0`** | Padding |
| `footer-options` | checkbox | `full-width` | Anchura |
| `footer-bg-img`, `footer-bg-img-position`, `footer-bg-img-size` | upload/select/select | | Fondo |
| `footer-call-to-action`, `footer-copy` | textarea | HTML/shortcodes | CTA y copyright |
| `footer-hide` | select | `''`, `center`, `1` | Barra copyright/social (`center` centrada, `1` oculta) |
| `back-top-top` | select | `''`, `sticky`, `sticky scroll`, `hide` | Botón subir |
| `popup-contact-form`, `popup-contact-form-icon` | text, icon | shortcode; **`icon-mail-line`** | Formulario flotante |

```bash
$S/be-opt-set.sh key=footer-layout value='4;one-fourth;one-fourth;one-fourth;one-fourth'
$S/be-opt-set.sh key=footer-copy value='© 2026 Mi Empresa · <a href="/aviso-legal/">Aviso legal</a>'
```

## 10. Search (`search`)

- **Form** — `$sections['search-form']` (`theme-options.php:6257`): `header-search` (`''` todo / `shop` solo
  productos), `header-search-mode` (`''`/`exact`), `header-search-form` (**`0`** oculto / `1` icono / `input` campo de texto),
  `header-search-input-width` (**`200`**), `header-search-live` (`0`/`1`) + `header-search-live-min-characters` (**`3`**),
  `header-search-live-load-posts` (**`10`**), `header-search-live-featured_image` (`0`/**`1`**).
- **Form design** — `$sections['search-form-design']` (`theme-options.php:6406`): `search-overlay` (`0`/`1`),
  `search-overlay-color` (**`rgba(0,0,0,.6)`**), `search-overlay-blur` (**`0`**), `search-scroll-disable`.
- **Page** — `$sections['search-page']` (`theme-options.php:6466`): `search-page-featured` (`0`/**`1`**),
  `search-page-author`, `search-page-date`, `search-page-excerpt`, `search-page-readmore` (`0`/**`1`**),
  `search-page-readmore-style` (`button`/**`link`**).

```bash
$S/be-opt-set.sh key=header-search-live value=1
```

## 11. Responsive (`responsive`)

### 11.1 General — `$sections['responsive']` (`theme-options.php:6596`)

| id | tipo | valores | qué hace |
|---|---|---|---|
| `responsive` | switch | `0`/**`1`** | Activa el CSS responsive (a `0` la web no se adapta) |
| `mobile-grid-width` | sliderbar | **`480`** | Anchura del grid en móvil |
| `mobile-site-padding` | sliderbar | **`33`** | Padding lateral móvil |
| `responsive-zoom` | switch | **`0`**/`1` | Pinch to zoom |
| `responsive-overflow-x` | select | `disable`, `tablet`, **`''`** | `overflow-x` |
| `mobile-order` | select | **`''`**, `sidebar-first` | Orden sidebar/contenido |
| `responsive-boxed2fw` | switch | **`0`**/`1` | Boxed → full width en móvil |
| `no-section-bg`, `responsive-parallax`, `responsive-video` | select | `''`/`tablet`; `0`/`1`; `0`/`1` | Fondos de sección en móvil |
| `builder-section-padding`, `builder-wrap-moveup` | select | `''`/`no-tablet`/`no-mobile`; `''`/`no-tablet`/`no-move` | Builder en móvil |
| `mobile-sidebar` | switch | **`0`**/`1` | Mostrar sidebar en móvil |
| `responsive-logo-img`, `responsive-retina-logo-img`, `responsive-sticky-logo-img`, `responsive-sticky-retina-logo-img` | upload | URL#id | → [04](04-logos-y-favicon.md) |
| `safari-bar-light-scheme`, `safari-bar-dark-scheme` | color | **`#ffffff`** | `theme-color` de Safari |

### 11.2 Header — `$sections['responsive-header']` (`theme-options.php:6907`)

| id | tipo | valores | qué hace |
|---|---|---|---|
| `mobile-menu-initial` | sliderbar | px, **`1240`** | Breakpoint a partir del cual aparece la hamburguesa |
| `responsive-mobile-menu` | select | **`side-slide`**, `''` (classic) | Estilo del menú móvil |
| `responsive-side-slide-width` | sliderbar | 150-500, **`250`** | Anchura del side slide |
| `responsive-side-slide` | checkbox **invert** | `social` | Marcado = sin iconos sociales |
| `header-menu-text` | text | texto | Texto junto a la hamburguesa |
| `mobile-menu` | select | slug/ID de menú (dinámico) | Menú alternativo en móvil |
| `mobile-header-height`, `mobile-subheader-padding` | text | px | Alturas |
| `mobile-subheader` | checkbox **invert** | `hide-breadcrumbs` | Marcado = sin breadcrumbs |
| `responsive-header-minimal` | radio_img | `''`, `mr-ll`, `mr-lc`, `mr-lr`, `ml-ll`, `ml-lc`, `ml-lr` | Disposición móvil (menú/logo) |
| `responsive-header-mobile`, `responsive-header-tablet` | checkbox | `sticky`, `transparent`; `sticky` | Header sticky/transparente |
| `mobile-icon-user/-wishlist/-cart/-search/-wpml/-action` | switch | `hide`, `tb`, `ss`, `''` | Dónde va cada icono en móvil |

```bash
$S/be-opt-set.sh key=mobile-menu-initial value=1024
$S/be-opt-set.sh key=responsive-header-mobile --json value='{"sticky":"sticky"}'
```

## 12. SEO (`seo`) — `$sections['seo']` (`theme-options.php:7224`)

Snippets de analítica que el tema imprime en `<head>` (`functions/theme-head.php:186-188` para `google-analytics`,
`l.199` y `l.276` para los de GTM cuando no hay `google-gtag-id`, `l.1534` para `google-gtag-id`) y las metas propias.

| id | tipo | valores | qué hace |
|---|---|---|---|
| `google-gtag-id` | text | `G-…` / `GTM-…` | Google Tag; el tema genera el script (y anula `google-gtag-js/html`) |
| `google-gtag-js`, `google-gtag-html` | textarea | snippet completo | GTM manual (solo si `google-gtag-id` vacío) |
| `google-analytics`, `google-remarketing`, `facebook-pixel` | textarea | snippet | Se imprimen tal cual |
| `mfn-seo` | switch | `0`/**`1`** | Metas SEO del tema (`theme-head.php:62,105`); a `0` si usas Yoast/RankMath |
| `meta-description`, `meta-keywords`, `mfn-seo-og-image`, `seo-fb-app-id` | text/text/upload/text | | Metas globales |
| `mfn-seo-schema-type` | switch | `0`/**`1`** | Schema.org del tema |

```bash
$S/be-opt-set.sh key=google-gtag-id value=G-XXXXXXX
$S/be-opt-set.sh key=mfn-seo value=0
$S/be-opt-set.sh key=facebook-pixel value=@/ruta/pixel.html
```

## 13. Social (`social`) — `$sections['social']` (`theme-options.php:7356`)

Iconos sociales del top bar/footer/side slide. Témini los consume con el shortcode `[redes]` ([12](12-temini-ajustes-propios.md)).

| id | tipo | valores | qué hace |
|---|---|---|---|
| `social-link` | social | `{"facebook":"https://…","instagram":"…","order":"facebook,instagram"}` | Redes y su orden |
| `social-attr` | checkbox | `blank`, `nofollow`, `noopener`, `noreferrer` | Atributos del `<a>` |
| `social-custom-icon`, `social-custom-link`, `social-custom-title` | icon/text/text | | Icono extra |
| `social-rss` | switch | **`0`**/`1` | RSS |

```bash
$S/be-opt-set.sh key=social-link --json value='{"facebook":"https://facebook.com/x","instagram":"https://instagram.com/x","order":"instagram,facebook"}'
$S/be-opt-set.sh key=social-attr --json value='{"blank":"blank","noopener":"noopener"}'
```

## 14. Addons & Plugins (`addons-plugins`)

- **Addons** — `$sections['addons']` (`theme-options.php:7460`): `cf7-error` (`''`/`message`), `parallax`
  (`translate3d`, `translate3d no-safari`, `enllax`, `stellar`), `prettyphoto-options` (checkbox `disable`,
  `disable-swiper`, `disable-mobile`, `title`), `sc-gallery-disable` (`1`/**`0`**), `recaptcha-display`
  (`login`, `register`), `recaptcha-key`, `recaptcha-secret`, `elementor-container-content` (`''`/`theme`).
- **Premium plugins** — `$sections['plugins']` (`theme-options.php:7615`): `plugin-rev`, `plugin-visual`,
  `plugin-layer` (`''` bundled / `disable` = licencia propia: el tema deja de marcar el plugin "as theme"
  (`functions/theme-functions.php:3975-3981`) y de listarlo en el dashboard (`functions/admin/class-mfn-dashboard.php:614-622`)
  → [09](09-registro-updates-plugins.md)).

```bash
$S/be-opt-set.sh key=prettyphoto-options --json value='{"disable":"disable"}'   # sin lightbox del tema
```

## 15. Colors (`colors`) — `$sections['colors-*']` (`theme-options.php:7668-9193`)

13 secciones, ~150 campos `color` (string) o `color_multi` (`{normal,hover}`), todos volcados a CSS por
`mfn_styles_dynamic()` y por tanto a `static.css`. Líneas: `colors-general` 7668, `colors-header` 7774,
`colors-menu` 7886, `colors-action` 8063, `content` 8205, `colors-shop` 8394, `colors-footer` 8477,
`colors-sliding-top` 8637, `headings` 8706, `palette` 8778, `colors-shortcodes` 8908, `colors-alerts` 9073,
`colors-forms` 9193.

Lo que se toca de verdad:

| id | tipo | valores | qué hace |
|---|---|---|---|
| `skin` | select | **`custom`**, `one`, `blue`, `brown`, `chocolate`, `gold`, `green`, `olive`, `orange`, `pink`, `red`, `sea`, `violet`, `yellow` | `custom` = usar todos los campos de abajo; `one` = solo `color-one` |
| `color-one` | color | **`#0095eb`** | Color único (skin `one`) |
| `color-theme` | color | **`#0089F7`** | Color de acento |
| `color-text`, `color-a`, `color-a-hover` | color | **`#626262`**, **`#006edf`**, **`#0089f7`** | Texto y enlaces |
| `background-html`, `background-body` | color | **`#FCFCFC`** | Fondos |
| `color-h1`…`color-h6` | color | **`#161922`** (h5 `#5f6271`) | Títulos |
| `background-header`, `background-subheader`, `color-subheader` | color | | Header/subheader |
| `color-menu-a`, `color-menu-a-active`, `background-submenu` | color | | Menú |
| `background-footer`, `color-footer`, `color-footer-a` | color | **`#101015`**, **`#bababa`**, **`#d1d1d1`** | Footer |
| `color-palette-1`…`14` | color | | Paleta del color picker del builder |

Lista completa: [reference/catalogo-opciones.md#colores-colors](reference/catalogo-opciones.md#colores-colors).

```bash
cat > /tmp/colores.json <<'EOF'
{"skin":"custom","color-theme":"#1d4ed8","color-a":"#1d4ed8","color-a-hover":"#1e3a8a","color-h1":"#0f172a","background-footer":"#0f172a"}
EOF
$S/be-opt-set.sh set=@/tmp/colores.json --dry-run && $S/be-opt-set.sh set=@/tmp/colores.json
```

## 16. Fonts (`font`) — `$sections['font-family']` 9328, `['font-size']` 9495, `['font-custom']` 9925

Cubierto entero en [05-fuentes.md](05-fuentes.md): roles `font-content`, `font-lead`, `font-menu`, `font-title`,
`font-headings`, `font-headings-small`, `font-blockquote`, `font-decorative`; `font-weight`, `font-subset`;
`font-size-{content,big,menu,title,single-intro,h1..h6}{,-tablet,-mobile}` (typography) y `font-size-responsive`;
slots `font-custom{,2,N}` + `-woff`/`-ttf`. El modo de carga (`google-font-mode`) vive en Performance (§20).

## 17. Translate (`translate`) — `$sections['translate-*']` (`theme-options.php:10020-10667`)

Cadenas fijas del front (`translate-general` 10020, `translate-blog` 10209, `translate-shop` 10360,
`translate-search` 10507, `translate-404` 10619, `translate-wpml` 10667). Un solo interruptor manda sobre todas:

| id | tipo | valores | qué hace |
|---|---|---|---|
| `translate` | switch | `0`/**`1`** | **Interruptor maestro**: con `1` el tema usa `translate-*`; con `0` usa el `.mo` del tema (§23) |
| `translate-home` | text | **`Home`** | Breadcrumb inicio |
| `translate-readmore` | text | **`Read more`** | Botón de los listados |
| `translate-search-placeholder` | text | **`Enter your search`** | Placeholder del buscador |
| `translate-404-title`, `translate-404-btn` | text | **`Ooops... Error 404`**, **`go to homepage`** | 404 |
| `translate-add-to-cart`, `translate-shop-filters` | text | **`Add to cart`**, **`Filters`** | Tienda |

Son ~85 campos `text`; lista completa en
[reference/catalogo-opciones.md#translate-translate](reference/catalogo-opciones.md#translate-translate).
Con WPML se traducen como admin-texts, no duplicando `betheme` ([01](01-almacenamiento-y-guardado.md)).

```bash
cat > /tmp/es.json <<'EOF'
{"translate":"1","translate-home":"Inicio","translate-readmore":"Leer más","translate-search-placeholder":"Buscar…","translate-404-title":"Error 404","translate-404-btn":"Volver al inicio","translate-filter":"Filtrar por","translate-all":"Ver todo"}
EOF
$S/be-opt-set.sh set=@/tmp/es.json
```

## 18. GDPR 2.0 (`gdpr2`) — `$sections['gdpr2-general']` 10699, `['gdpr2-design']` 10956

Modal de consentimiento con Google Consent Mode V2 (se imprime si `mfn_opts_get('gdpr2')`, `footer.php:209`,
`theme-head.php:1511`). No usar a la vez que la barra GDPR clásica (§19).

| id | tipo | valores | qué hace |
|---|---|---|---|
| `gdpr2` | switch | **`''`**/`1` | Activar |
| `gdpr2-button` | switch | `''`/`1` | Botón para reabrir |
| `gdpr2-settings-cookie_expire` | text | días, **`365`** | Caducidad |
| `gdpr2-content-title`, `gdpr2-consent-content` | text, textarea (HTML) | **`Consent`** | Texto principal |
| `gdpr2-necessary-title/-consent`, `gdpr2-analytics-title/-consent`, `gdpr2-marketing-title/-consent`, `gdpr2-about-title/-content` | text/textarea | | Pestañas del modal |
| `gdpr2-button-deny`, `gdpr2-button-customize`, `gdpr2-button-allow-selected`, `gdpr2-button-allow-all` | text | **`Deny`**, **`Customize`**, **`Allow selected`**, **`Allow all`** | Botones |

Diseño (`gdpr2-design`, ~23 campos color/color_multi): `gdpr2-color-container` (**`#ffffff`**),
`gdpr2-color-overlay` (**`rgba(25, 37, 48, 0.6)`**), `gdpr2-color-buttons-bg-active` (color_multi),
`gdpr2-reopen-icon`… → [reference/catalogo-opciones.md#gdpr-20-gdpr2](reference/catalogo-opciones.md#gdpr-20-gdpr2).

```bash
$S/be-opt-set.sh key=gdpr2 value=1
$S/be-opt-set.sh key=gdpr2-consent-content value=@/tmp/cookies-es.html
```

## 19. GDPR & Cookies (`gdpr`) — `$sections['gdpr-general']` 11235, `['gdpr-design']` 11401

Barra de cookies simple (`functions/modules/class-mfn-gdpr.php:11`, activa si `mfn_opts_get('gdpr')`).

| id | tipo | valores | qué hace |
|---|---|---|---|
| `gdpr` | switch | **`''`**/`1` | Activar barra |
| `gdpr-settings-position` | radio_img | `top`, `bottom`, **`left`**, `right` | Posición |
| `gdpr-settings-animation` | switch | `''`, `fade`, **`slide`** | Animación |
| `gdpr-settings-cookie_expire` | text | **`365`** | Días |
| `gdpr-content` | textarea | HTML | Texto |
| `gdpr-content-button_text`, `gdpr-content-more_info_text` | text | **`Accept all`**, **`Read more`** | Botones |
| `gdpr-content-more_info_page` / `gdpr-content-more_info_link` | select_ajax / text | ID de página / URL (**`#`**) | Enlace "más info" |

Diseño: `gdpr-container-background` (**`#eef2f5`**), `gdpr-button-background` (color_multi
`{"normal":"#006edf","hover":"#0089f7"}`), `gdpr-container-border-radius` (**`5`**)…
→ [reference/catalogo-opciones.md#gdpr--cookies-gdpr](reference/catalogo-opciones.md#gdpr--cookies-gdpr).

## 20. Performance (`performance`) — `$sections['performance-general']` (`theme-options.php:11553`)

| id | tipo | valores | qué hace |
|---|---|---|---|
| `google-font-mode` | switch | **`''`** (Google CDN), `local`, `disabled` | Cómo se cargan las Google Fonts → [05](05-fuentes.md); `local` exige regenerar (`be-fonts-local-regenerate`) |
| `lazy-load` | switch | **`''`**/`lazy` | Lazy load de imágenes del tema (`theme-functions.php:2316`) |
| `srcset-limit` | switch | `''`/`lazy` | Limita srcset |
| `images-optimization`, `images-optimization-remove` | switch | `''`/`1` | Optimización de imágenes al subir |
| `performance-image-size-disable` | checkbox **invert** | `blog-portfolio`, `blog-single`, `be_clients`, `portfolio-list`, `portfolio-mf`, `portfolio-mf-w`, `portfolio-mf-t`, `slider-content`, `be_thumbnail` | Marcado = ese tamaño de imagen **no se genera** |
| `performance-preload` | textarea | URLs, una por línea | `<link rel=preload>` |
| `performance-assets-disable` | checkbox **invert** | `entrance-animations`, `font-awesome` | Marcado = asset **no cargado** (`theme-head.php:376`) |
| `performance-wp-disable` | checkbox **invert** | `wp-block-library`, `dashicons`, `emoji` | Marcado = asset de WP **quitado** |
| `woocommerce-assets`, `woocommerce-assets-id` | switch, text | `''`/`shop`; IDs csv | CSS/JS de Woo solo en tienda |
| `jquery-location`, `css-location` | switch | `''`/`footer` | Mover al footer |
| `local-styles-location` | switch | `''`/`inline` | CSS local del builder inline en vez de fichero |
| `minify-css`, `minify-js` | switch | **`0`**/`1` | Usa los `.min` del tema (`theme-head.php:377`, `l.1382`) |
| `static-css` | switch | **`0`**/`1` | Sirve `uploads/betheme/css/static.css` en vez de CSS inline (§23) |
| `hold-cache` | switch | **`0`**/`1` | Cabeceras de caché en `.htaccess` (§23) |

```bash
$S/be-opt-set.sh key=lazy-load value=lazy
$S/be-opt-set.sh key=performance-wp-disable --json value='{"emoji":"emoji","dashicons":"dashicons"}'
$S/be-opt-set.sh key=minify-css value=1 && $S/be-opt-set.sh key=minify-js value=1
```

`performance-enable` es un botón (no persiste) que aplica varios de estos a la vez desde el admin; por SSH
escríbelos uno a uno. Caché y ficheros generados: [07-herramientas-tools.md](07-herramientas-tools.md).

## 21. Accessibility (`accessibility`) — `$sections['accessibility-general']` (`theme-options.php:11865`)

`keyboard-support`, `underline-links`, `repetitive-links`, `warning-open-links` (switch **`0`**/`1`). Los skip
links no son opción: se asignan como menú en la ubicación "Accessibility Skip Links Menu".

```bash
$S/be-opt-set.sh key=keyboard-support value=1
```

## 22. Custom CSS & JS (`custom`) — `$sections['css']` 11935, `$sections['js']` 11960

| id | tipo | dónde se imprime | qué hace |
|---|---|---|---|
| `custom-css` | textarea | `mfn_styles_custom()` (`theme-head.php:1325`) → inline o dentro de `static.css` (`options.php:929-930`) | CSS global |
| `custom-js` | textarea | `wp_add_inline_script('mfn-scripts', …)` (`theme-head.php:1564`) | JS global; jQuery envuelto en `jQuery(function($){ … })` |

Ambos llevan `'role_restricted' => true` (`theme-options.php:11948,11974`). En las metas de post ese flag oculta el
campo a quien no es administrador (`functions/post-types/class-mfn-post-type.php:164-169`); en Theme Options solo lo
hemos encontrado declarado, no consumido en PHP **[sin verificar]**. Por SSH no aplica.

```bash
$S/be-opt-set.sh key=custom-css value=@/ruta/custom.css        # regenera static.css automáticamente
$S/be-opt-set.sh key=custom-js value=@/ruta/custom.js
# wp puro (después: be-static-css-regenerate.sh si static-css=1)
wp option patch update betheme custom-css "$(cat /ruta/custom.css)"
```

En Témini el CSS del tema hijo va en ficheros encolados (`temini-setup.php`, [12](12-temini-ajustes-propios.md));
reserva `custom-css` para parches rápidos.

## 23. Valores especiales

### 23.1 Checkbox `invert` (`theme-disable`, `post-type-disable` y otros diez)

`'invert' => true` **solo cambia la UI**: `MFN_Options_checkbox::render()` añade la clase CSS `invert`
(`betheme/muffin-options/fields/checkbox/field_checkbox.php:19-21`) y `options.css:1223-1228` pinta como "activo" el
ítem **no** marcado. El valor guardado es idéntico al de cualquier checkbox: `['clave' => 'clave']` solo para los
marcados. Regla: **clave presente = desactivado**; ausente = activo.

Consumidores verificados: `$theme_disable = mfn_opts_get('theme-disable')` (`betheme/functions.php:48`) e
`isset($theme_disable['mega-menu'])` (l.105), `['custom-icons']` (l.119), `empty($theme_disable['svg-allow'])`
(l.197, `functions/theme-functions.php:294,318,330`), `['json-allow']` (`theme-functions.php:302,360`),
`['categories-sidebars']` (`functions/theme-sidebars.php:196`), `['demo-data']`
(`functions/importer/class-mfn-importer.php:27`); `$post_types_disable = mfn_opts_get('post-type-disable')`
(`functions.php:115`) con `isset($post_types_disable['template'|'client'|'offer'|'portfolio'|'slide'|'testimonial'|'layout'])`
(l.122-142).

Campos con `invert` en 28.4.3 (grep `'invert' => true` en `theme-options.php`): `prev-next-nav` (l.2037),
`layout-options` (2296), `post-type-disable` (2321), `theme-disable` (2341), `shop-icons-hide` (4485),
`shop-cart-total-hide` (4519), `gutenberg-checkout-options` (4659), `mobile-subheader` (6939),
`responsive-side-slide` (7009), `performance-image-size-disable` (11676), `performance-assets-disable` (11703),
`performance-wp-disable` (11715).

```bash
# ver qué hay desactivado
$S/be-opt-get.sh key=theme-disable,post-type-disable
# desactivar Layouts (deprecated) y Slides; el resto de CPTs sigue activo
$S/be-opt-set.sh key=post-type-disable --json value='{"layout":"layout","slide":"slide"}'
# reactivar todo (array vacío). OJO: theme-disable vacío también permite subir SVG/TTF/WOFF/ICO
$S/be-opt-set.sh key=post-type-disable --json value='{}'
```

### 23.2 `builder-storage` (`encode`)

Decide cómo se guarda `mfn-page-items` en cada post: `''` array serializado, `non-utf-8` serializado seguro,
`encode` = `base64_encode(serialize(...))` (`betheme/visual-builder/visual-builder.php:403-404`; misma rama en
`functions/builder/class-mfn-builder-admin.php:3309` y `class-mfn-builder-ajax.php:571`). Solo afecta a páginas
guardadas **después** del cambio (`theme-options.php:2368`); las anteriores se leen en el formato en que estén.
Cualquier script que lea `mfn-page-items` tiene que contemplar ambos formatos
([10](10-plantillas-header-footer-popups.md), y muffinAI `docs/bebuilder/`).

### 23.3 `translate` como interruptor maestro

Cada cadena se resuelve como `mfn_opts_get('translate') ? mfn_opts_get('translate-share', 'Share') : __('Share','betheme')`
(`betheme/functions/theme-functions.php:1462`; mismo patrón en l.1628-1630, 1830, 3410-3412). Con `translate=0`
**todos** los `translate-*` se ignoran y mandan los `.mo`; con `1` (std) se usan los textos del panel, y un campo
vacío cae al segundo argumento de `mfn_opts_get` (el texto inglés), no a `std`.

### 23.4 `hold-cache` y `static-css` disparan procesos

- `static-css=1` hace que el front encole `uploads/betheme/css/static.css` (`mfn_styles_static()`,
  `betheme/functions/theme-head.php:625-630`) y omita el CSS inline (`l.423`, `736`, `756`). El fichero lo escribe
  `_static_CSS()` (`betheme/muffin-options/options.php:903-933`), que en el admin solo corre con
  `?settings-updated` y la opción activa (l.905). Por SSH lo regenera `be_opts_save()` en cada `be-opt-set`, o a mano
  `be-static-css-regenerate.sh` → [07](07-herramientas-tools.md).
- `hold-cache=1` añade el bloque `# BEGIN BETHEME` al `.htaccess` mediante `_cache_manager()`
  (`options.php:961-984`), que solo actúa si existe el transient `betheme_hold-cache = 'changed'`; ese transient lo
  crea el JS del panel al mover el switch vía AJAX `mfn_set_transient`
  (`betheme/functions/builder/class-mfn-builder-ajax.php:210-220`). Escribir `hold-cache` por SSH **no toca el
  `.htaccess`**: usa `be-cache-htaccess.sh mode=on|off` → [07](07-herramientas-tools.md).

### 23.5 Claves que no son campos

`last_tab` (pestaña abierta, campo oculto del formulario `options.php:2008-2009`; se descarta al guardar una
revisión por AJAX, l.117) e `imported` (marca del importador, l.1167). `be-opt-set` las acepta sin `--force` (`be_extra_keys()` en `_lib.php`).

## 24. Trampas verificadas

- **Vacío ≠ std.** `MFN_Options::get()` (`betheme/muffin-options/options.php:620-634`) devuelve el `$default` que
  pasa cada llamada a `mfn_opts_get()` cuando la clave falta o está vacía (salvo la cadena `'0'`). Para apagar un
  switch cuyo std es `1` escribe `value=0`, no `value=`; y `std` solo se aplica al crear la opción por primera vez.
- **Valores con comas/puntos y coma** (`header-style=stack,left`, `footer-layout=4;one-fourth;…`,
  `position-page-bg=no-repeat;center top;;`): entrecomilla el `value=` en bash; son strings, no arrays.
- **Checkbox = solo marcados.** Para desmarcar todo escribe `--json value='{}'`; nunca `{"clave":"0"}`.
- **`sticky-header` y otros campos `hide-if-tpl-header`** no hacen nada si hay plantilla de Header Builder activa.
- **`portfolio-slug` / `portfolio-tax`** exigen `wp rewrite flush` después.
- **`featured-*` y `performance-image-size-disable`** cambian tamaños de imagen: hay que regenerar miniaturas.
- **`google-gtag-id` anula** `google-gtag-js`/`google-gtag-html` (`theme-head.php:199,276`).
- **`gdpr` y `gdpr2` a la vez** muestran dos avisos de cookies.
- Los filtros `betheme_disable_advanced`/`betheme_disable_hooks` solo ocultan pestañas; las opciones siguen vivas.

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `post-type-disable` (checkbox `invert`): quitar `slide` del array registra el CPT; volver a ponerlo lo desregistra. Verificado con `wp post-type list`.
- `custom-css`: `be-opt-set key=custom-css value='/* prueba-03 */'` → la cadena aparece en `uploads/betheme/css/static.css` inmediatamente; al vaciarla desaparece.
- `translate` en el dev = `0` (interruptor maestro apagado): los `translate-*` no se usan.
- El dev sirve el front con FlyingPress: para ver un cambio con `curl` hay que añadir una query (`?x=$RANDOM`) o vaciar su caché.
