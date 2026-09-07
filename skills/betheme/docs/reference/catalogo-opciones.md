# Catálogo de opciones de Theme Options — Betheme 28.4.3

> GENERADO por `scripts/be-catalog-generate.sh` el 2026-09-07T17:42:55+00:00 (sitio de referencia, no publicado). No editar a mano.
> 829 campos con id. Los campos `dynamic` tienen opciones que dependen del sitio (páginas, sidebars, menús, plantillas).
> `std` es el valor por defecto que Betheme escribe la PRIMERA vez que crea la opción (options.php:670); después manda lo guardado.

Todos los ids se leen con `mfn_opts_get('<id>')` y viven en `wp_options.betheme[<id>]`.

## Índice

- **Global** (`global`): `general`, `logo`, `buttons`, `frame`, `sliders`, `navigation`, `advanced`, `hooks`
- **Header & Subheader** (`header-subheader`): `header`, `subheader`, `extras`
- **Menu & Action Bar** (`mab`): `menu`, `action-bar`
- **Sidebars** (`sidebars`): `sidebars`
- **Blog & Portfolio** (`bps`): `bps-general`, `blog`, `portfolio`, `featured-image`
- **Tienda** (`shop`): `shop`, `shop-list`, `shop-single`, `shop-addons`, `shop-addons-design`
- **Pages** (`pages`): `pages-general`, `pages-404`, `pages-under`
- **Pie de página** (`footer`): `footer`
- **Buscar** (`search`): `search-form`, `search-form-design`, `search-page`
- **Responsive** (`responsive`): `responsive`, `responsive-header`
- **SEO** (`seo`): `seo`
- **Social** (`social`): `social`
- **Addons & Plugins** (`addons-plugins`): `addons`, `plugins`
- **Colores** (`colors`): `colors-general`, `colors-action`, `colors-header`, `colors-menu`, `content`, `colors-alerts`, `colors-shortcodes`, `colors-forms`, `headings`, `colors-shop`, `colors-footer`, `colors-sliding-top`, `palette`
- **Fonts** (`font`): `font-family`, `font-size`, `font-custom`
- **Translate** (`translate`): `translate-general`, `translate-blog`, `translate-shop`, `translate-search`, `translate-404`, `translate-wpml`
- **GDPR 2.0** (`gdpr2`): `gdpr2-general`, `gdpr2-design`
- **GDPR & Cookies** (`gdpr`): `gdpr-general`, `gdpr-design`
- **Performance** (`performance`): `performance-general`
- **Accessibility** (`accessibility`): `accessibility-general`
- **Custom CSS & JS** (`custom`): `css`, `js`

## Global (`global`)

### General (`general`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `layout` | radio_img | string | `"full-width"` | grupo: Diseño; opciones: `full-width`, `boxed` — *Diseño* |
| `grid-width` | sliderbar | string | `1240` | grupo: Diseño — *Site width* |
| `section-padding` | text | string | `0` | grupo: Diseño — *Section side padding* |
| `style` | radio_img | string | `""` | grupo: Diseño; opciones: ``, `simple` — *Estilo* |
| `img-page-bg` | upload | string (URL o URL#attachment_id) |  | grupo: Diseño — *Imagen* |
| `position-page-bg` | select | string | `"center top no-repeat"` | grupo: Diseño; opciones: ``, `no-repeat;left top;;`, `repeat;left top;;`, `no-repeat;left center;;`, `repeat;left center;;`, `no-repeat;left bottom;;`, `repeat;left bottom;;`, `no-repeat;center top;;`, `repeat;center top;;`, `repeat-x;center top;;`, `repeat-y;center top;;`, `no-repeat;center;;`, … — *Posición* |
| `size-page-bg` | select | string |  | grupo: Diseño; opciones: ``, `auto`, `contain`, `cover`, `cover-ultrawide` — *Tamaño* |
| `transparent` | checkbox | array {clave: clave} solo marcados |  | grupo: Diseño; opciones: `header`, `menu`, `content`, `footer` — *Transparency* |
| `favicon-img` | upload | string (URL o URL#attachment_id) |  | grupo: Diseño — *Favicon* |
| `apple-touch-icon` | upload | string (URL o URL#attachment_id) |  | grupo: Diseño — *Apple Touch Icon* |

### Logotipo (`logo`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `logo-img` | upload | string (URL o URL#attachment_id) |  | grupo: Logotipo — *Logotipo* |
| `retina-logo-img` | upload | string (URL o URL#attachment_id) |  | grupo: Logotipo — *Retina Logo* |
| `sticky-logo-img` | upload | string (URL o URL#attachment_id) |  | grupo: Sticky header logo — *Logotipo* |
| `sticky-retina-logo-img` | upload | string (URL o URL#attachment_id) |  | grupo: Sticky header logo — *Retina Logo* |
| `logo-link` | checkbox | array {clave: clave} solo marcados | `{"link":"link"}` | grupo: Opciones; opciones: `link`, `h1-home`, `h1-all` — *Opciones* |
| `logo-text` | text | string |  | grupo: Opciones — *Text logo* |
| `logo-width` | text | string |  | grupo: Opciones; responsive — *SVG logo width* |
| `logo-width-tablet` | text | string |  | grupo: Opciones; responsive — *SVG logo width* |
| `logo-width-mobile` | text | string |  | grupo: Opciones; responsive — *SVG logo width* |
| `logo-height` | text | string |  | grupo: Avanzado — *Altura* |
| `logo-vertical-padding` | text | string |  | grupo: Avanzado — *Padding top & bottom* |
| `logo-vertical-align` | select | string |  | grupo: Avanzado; opciones: `top`, ``, `bottom` — *Vertical align* |
| `logo-advanced` | checkbox | array {clave: clave} solo marcados |  | grupo: Avanzado; opciones: `no-margin`, `overflow`, `no-sticky-padding`, `sticky-width-auto` — *Avanzado* |

### Buttons (`buttons`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `button-font-family` | font_select | string | `""` | grupo: Estilo — *Familia de fuentes* |
| `button-font` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":14,"weight_style":"400","letter_spacing":0}` | grupo: Estilo; responsive — *Font* |
| `button-font-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Estilo; responsive — *Font* |
| `button-font-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Estilo; responsive — *Font* |
| `button-padding` | dimensions | array {top,right,bottom,left,isLinked} | `{"top":10,"right":20,"bottom":10,"left":20,"isLinked":0}` | grupo: Estilo; responsive — *Relleno* |
| `button-padding-tablet` | dimensions | array {top,right,bottom,left,isLinked} |  | grupo: Estilo; responsive — *Relleno* |
| `button-padding-mobile` | dimensions | array {top,right,bottom,left,isLinked} |  | grupo: Estilo; responsive — *Relleno* |
| `button-border-width` | dimensions | array {top,right,bottom,left,isLinked} |  | grupo: Estilo — *Ancho del borde* |
| `button-border-radius` | dimensions | array {top,right,bottom,left,isLinked} | `"3"` | grupo: Estilo — *Radio del borde* |
| `button-gap` | sliderbar | string | `"10"` | grupo: Estilo — *Icon gap* |
| `button-animation` | select | string | `""` | grupo: Estilo; opciones: `fade`, `slide slide-right`, `slide slide-left`, `slide slide-top`, `slide slide-bottom` — *Hover animation* |
| `button-animation-time` | sliderbar | string | `"0.2"` | grupo: Estilo — *Hover animation time* |
| `button-preview` | preview | no persiste |  | grupo: Vista previa — *Vista previa* |
| `button-color` | color_multi | array {subclave: color} | `{"normal":"#626262","hover":"#626262"}` | grupo: Predeterminado — *Text color* |
| `button-icon-color` | color_multi | array {subclave: color} | `{"normal":"#626262","hover":"#626262"}` | grupo: Predeterminado — *Color del icono* |
| `button-background-type` | switch | string | `""` | grupo: Predeterminado; opciones: ``, `1` — *Background type* |
| `button-background` | color_multi | array {subclave: color} | `{"normal":"#dbdddf","hover":"#d3d3d3"}` | grupo: Predeterminado; condición UI: `button-background-type` — *Color de fondo* |
| `button-gradient` | gradient | array {angle,color,color2,...} |  | grupo: Predeterminado; condición UI: `button-background-type` — *Background gradient* |
| `button-gradient-hover` | gradient | array {angle,color,color2,...} |  | grupo: Predeterminado; condición UI: `button-background-type` — *Background gradient hover* |
| `button-border-color` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Predeterminado — *Color del borde* |
| `button-box-shadow` | box_shadow | string |  | grupo: Predeterminado — *Sombra de la caja* |
| `button-highlighted-color` | color_multi | array {subclave: color} | `{"normal":"#ffffff","hover":"#ffffff"}` | grupo: Highlighted — *Text color* |
| `button-highlighted-icon-color` | color_multi | array {subclave: color} | `{"normal":"#ffffff","hover":"#ffffff"}` | grupo: Highlighted — *Color del icono* |
| `button-highlighted-background-type` | switch | string | `""` | grupo: Highlighted; opciones: ``, `1` — *Background type* |
| `button-highlighted-background` | color_multi | array {subclave: color} | `{"normal":"#0095eb","hover":"#007cc3"}` | grupo: Highlighted; condición UI: `button-highlighted-background-type` — *Fondo* |
| `button-highlighted-gradient` | gradient | array {angle,color,color2,...} |  | grupo: Highlighted; condición UI: `button-highlighted-background-type` — *Background gradient* |
| `button-highlighted-gradient-hover` | gradient | array {angle,color,color2,...} |  | grupo: Highlighted; condición UI: `button-highlighted-background-type` — *Background gradient hover* |
| `button-highlighted-border-color` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Highlighted — *Color del borde* |
| `button-highlighted-box-shadow` | box_shadow | string |  | grupo: Highlighted — *Sombra de la caja* |
| `button-shop-color` | color_multi | array {subclave: color} | `{"normal":"#ffffff","hover":"#ffffff"}` | grupo: Tienda — *Text color* |
| `button-shop-background-type` | switch | string | `""` | grupo: Tienda; opciones: ``, `1` — *Background type* |
| `button-shop-background` | color_multi | array {subclave: color} | `{"normal":"#0095eb","hover":"#007cc3"}` | grupo: Tienda; condición UI: `button-shop-background-type` — *Fondo* |
| `button-shop-gradient` | gradient | array {angle,color,color2,...} |  | grupo: Tienda; condición UI: `button-shop-background-type` — *Background gradient* |
| `button-shop-gradient-hover` | gradient | array {angle,color,color2,...} |  | grupo: Tienda; condición UI: `button-shop-background-type` — *Background gradient hover* |
| `button-shop-border-color` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Tienda — *Color del borde* |
| `button-shop-box-shadow` | box_shadow | string |  | grupo: Tienda — *Sombra de la caja* |
| `button-action-color` | color_multi | array {subclave: color} | `{"normal":"#626262","hover":"#626262"}` | grupo: Action — *Text color* |
| `button-action-icon-color` | color_multi | array {subclave: color} | `{"normal":"#626262","hover":"#626262"}` | grupo: Action — *Color del icono* |
| `button-action-background-type` | switch | string | `""` | grupo: Action; opciones: ``, `1` — *Background type* |
| `button-action-background` | color_multi | array {subclave: color} | `{"normal":"#dbdddf","hover":"#d3d3d3"}` | grupo: Action; condición UI: `button-action-background-type` — *Fondo* |
| `button-action-gradient` | gradient | array {angle,color,color2,...} |  | grupo: Action; condición UI: `button-action-background-type` — *Background gradient* |
| `button-action-gradient-hover` | gradient | array {angle,color,color2,...} |  | grupo: Action; condición UI: `button-action-background-type` — *Background gradient hover* |
| `button-action-border-color` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Action — *Color del borde* |
| `button-action-box-shadow` | box_shadow | string |  | grupo: Action — *Sombra de la caja* |

### Image frame (`frame`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `image-frame-style` | radio_img | string | `"modern-overlay"` | grupo: Image frame; opciones: `modern-overlay`, `overlay`, ``, `zoom`, `disable` — *Estilo* |
| `image-frame-border-width` | text | string | `"0"` | grupo: Image frame — *Ancho del borde* |
| `image-frame-caption` | switch | string | `""` | grupo: Image frame; opciones: ``, `on` — *Caption* |
| `background-imageframe-link` | color_multi | array {subclave: color} | `{"normal":"#ffffff","hover":"#ffffff"}` | grupo: Diseño — *Icon backgound* |
| `color-imageframe-link` | color_multi | array {subclave: color} | `{"normal":"#161922","hover":"#0089f7"}` | grupo: Diseño — *Color del icono* |
| `border-imageframe-link` | color_multi | array {subclave: color} | `{"normal":"#ffffff","hover":"#ffffff"}` | grupo: Diseño — *Icon border* |
| `border-imageframe` | color | string | `"#f8f8f8"` | grupo: Diseño; condición UI: `image-frame-border-width` — *Image border color* |
| `color-imageframe-mask-new` | color | string | `"rgba(0,0,0,.15)"` | grupo: Diseño — *Image hover mask* |

### Sliders (`sliders`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `slider-blog-timeout` | text | string | `"0"` | grupo: Sliders — *Blog* |
| `slider-clients-timeout` | text | string | `"0"` | grupo: Sliders — *Clientes* |
| `slider-offer-timeout` | text | string | `"0"` | grupo: Sliders — *Oferta* |
| `slider-portfolio-timeout` | text | string | `"0"` | grupo: Sliders — *Portafolio* |
| `slider-shop-timeout` | text | string | `"0"` | grupo: Sliders — *Tienda* |
| `slider-slider-timeout` | text | string | `"0"` | grupo: Sliders — *Control deslizante* |
| `slider-testimonials-timeout` | text | string | `"0"` | grupo: Sliders — *Testimonios* |

### Navigation & share (`navigation`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-navigation` | info | no persiste |  |  — *Navigation and Share box show in Blog, Portfolio and Shop* |
| `prev-next-nav` | checkbox | array {clave: clave} solo marcados |  | grupo: Navegación; opciones: `hide-header`, `hide-sticky`, `in-same-term` — *Opciones* |
| `prev-next-style` | switch | string | `"minimal"` | grupo: Navegación; opciones: ``, `minimal` — *Header arrows* |
| `prev-next-sticky-style` | switch | string |  | grupo: Navegación; opciones: ``, `images`, `arrows` — *Sticky arrows* |
| `prev-next-date` | switch | string | `"1"` | grupo: Navegación; opciones: `0`, `1`; condición UI: `prev-next-sticky-style` — *Fecha* |
| `pagination-show-all` | switch | string | `"1"` | grupo: Paginación; opciones: `0`, `1` — *Pagination type* |
| `share` | switch | string | `"1"` | grupo: Share; opciones: `0`, `hide-mobile`, `1` — *Share Box* |
| `share-style` | switch | string | `"simple"` | grupo: Share; opciones: ``, `simple` — *Estilo* |

### Avanzado (`advanced`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `preloader` | switch | string |  | grupo: Diseño; opciones: ``, `load` — *Preloader* |
| `preloader-image` | upload | string (URL o URL#attachment_id) |  | grupo: Diseño; condición UI: `preloader` — *Preloader image* |
| `layout-boxed-padding` | text | string |  | grupo: Diseño — *Side padding for Boxed Layout* |
| `display-order` | select | string |  | grupo: Diseño; opciones: `0`, `1` — *Content display order* |
| `content-remove-padding` | switch | string | `"0"` | grupo: Diseño; opciones: `1`, `0` — *Relleno superior del contenido* |
| `builder-visibility` | select | string | `"edit_theme_options"` | grupo: Diseño; opciones: `edit_theme_options`, `edit_pages`, ``, `hide` — *BeBuilder visibility* |
| `builder-blocks` | switch | string |  | grupo: Diseño; opciones: `0`, `1` — *BeBuilder Blocks Classic* |
| `google-maps-api-key` | text | string |  | grupo: Opciones — *Google Maps API key* |
| `table-hover` | select | string |  | grupo: Opciones; opciones: ``, `hover`, `responsive` — *HTML table* |
| `no-hover` | select | string |  | grupo: Opciones; opciones: ``, `tablet`, `all` — *Hover Effects* |
| `math-animations-disable` | switch | string | `"0"` | grupo: Opciones; opciones: `1`, `0` — *Animate digits* |
| `layout-options` | checkbox | array {clave: clave} solo marcados | `{"no-shadows":"no-shadows"}` | grupo: Opciones; opciones: `no-shadows`, `boxed-no-margin` — *Otro* |
| `post-type-disable` | checkbox | array {clave: clave} solo marcados |  | grupo: Theme functions; opciones: `client`, `layout`, `offer`, `portfolio`, `slide`, `template`, `testimonial` — *Tipos de publicaciones personalizadas* |
| `theme-disable` | checkbox | array {clave: clave} solo marcados | `{"svg-allow":"svg-allow"}` | grupo: Theme functions; opciones: `categories-sidebars`, `custom-icons`, `mega-menu`, `builder-preview`, `demo-data`, `svg-allow`, `json-allow` — *Theme functions* |
| `builder-autosave` | switch | string | `""` | grupo: Avanzado; opciones: `1`, `` — *BeBuilder autosave* |
| `builder-storage` | select | string |  | grupo: Avanzado; opciones: ``, `non-utf-8`, `encode` — *BeBuilder data storage* |
| `slider-shortcode` | text | string |  | grupo: Avanzado — *Código corto del control deslizante* |
| `table_prefix` | select | string |  | grupo: Avanzado; opciones: `base_prefix`, `prefix` — *Table Prefix* |
| `hide_editor` | switch | string | `"0"` | grupo: Avanzado; opciones: `1`, `0` — *WordPress Editor* |

### Hooks (`hooks`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `hook-top` | textarea | string |  | grupo: Hooks — *Arriba* |
| `hook-content-before` | textarea | string |  | grupo: Hooks — *Content before* |
| `hook-content-after` | textarea | string |  | grupo: Hooks — *Content after* |
| `hook-bottom` | textarea | string |  | grupo: Hooks — *Abajo* |

## Header & Subheader (`header-subheader`)

### Encabezado (`header`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `header-style` | radio_img | string | `"classic"` | grupo: Diseño; opciones: `classic`, `modern`, `plain`, `stack,left`, `stack,center`, `stack,right`, `stack,magazine`, `creative`, `creative,rtl`, `creative,open`, `creative,open,rtl`, `fixed`, … — *Estilo* |
| `header-fw` | checkbox | array {clave: clave} solo marcados |  | grupo: Diseño; opciones: `full-width`, `header-boxed` — *Opciones* |
| `header-height` | text | string | `250` | grupo: Diseño — *Altura* |
| `img-subheader-bg` | upload | string (URL o URL#attachment_id) |  | grupo: Fondo — *Imagen* |
| `img-subheader-attachment` | select | string |  | grupo: Fondo; opciones: ``, `no-repeat;left top;;`, `repeat;left top;;`, `no-repeat;left center;;`, `repeat;left center;;`, `no-repeat;left bottom;;`, `repeat;left bottom;;`, `no-repeat;center top;;`, `repeat;center top;;`, `repeat-x;center top;;`, `repeat-y;center top;;`, `no-repeat;center;;`, … — *Posición* |
| `size-subheader-bg` | select | string |  | grupo: Fondo; opciones: ``, `auto`, `contain`, `cover`, `cover-ultrawide` — *Tamaño* |
| `top-bar-bg-img` | upload | string (URL o URL#attachment_id) |  | grupo: Top bar background — *Imagen* |
| `top-bar-bg-position` | select | string |  | grupo: Top bar background; opciones: ``, `no-repeat;left top;;`, `repeat;left top;;`, `no-repeat;left center;;`, `repeat;left center;;`, `no-repeat;left bottom;;`, `repeat;left bottom;;`, `no-repeat;center top;;`, `repeat;center top;;`, `repeat-x;center top;;`, `repeat-y;center top;;`, `no-repeat;center;;`, … — *Posición* |
| `sticky-header` | switch | string | `"1"` | grupo: Encabezado fijo; dynamic, 2 opciones — *Fijo* |
| `sticky-header-style` | select | string |  | grupo: Encabezado fijo; opciones: `tb-color`, `white`, `dark` — *Estilo* |

### Subtítulo (`subheader`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `subheader-style` | select | string | `"both-center"` | grupo: Diseño; opciones: `both-center`, `both-left`, `both-right`, ``, `title-right` — *Estilo* |
| `subheader` | checkbox | array {clave: clave} solo marcados |  | grupo: Diseño; opciones: `hide-breadcrumbs`, `hide-title`, `hide-subheader` — *Ocultar* |
| `subheader-padding` | text | string |  | grupo: Diseño — *Relleno* |
| `subheader-title-tag` | switch | string | `"h1"` | grupo: Diseño; opciones: `h1`, `h2`, `h3`, `h4`, `h5`, `h6`, `span` — *Etiqueta de título* |
| `subheader-image` | upload | string (URL o URL#attachment_id) |  | grupo: Fondo — *Imagen* |
| `subheader-position` | select | string | `"center top no-repeat"` | grupo: Fondo; opciones: ``, `no-repeat;left top;;`, `repeat;left top;;`, `no-repeat;left center;;`, `repeat;left center;;`, `no-repeat;left bottom;;`, `repeat;left bottom;;`, `no-repeat;center top;;`, `repeat;center top;;`, `repeat-x;center top;;`, `repeat-y;center top;;`, `no-repeat;center;;`, … — *Posición* |
| `subheader-size` | select | string |  | grupo: Fondo; opciones: ``, `auto`, `contain`, `cover`, `cover-ultrawide` — *Tamaño* |
| `subheader-transparent` | sliderbar | string | `"100"` | grupo: Fondo — *Transparency (alpha)* |
| `subheader-advanced` | checkbox | array {clave: clave} solo marcados |  | grupo: Avanzado; opciones: `breadcrumbs-link`, `slider-show` — *Opciones* |

### Extras (`extras`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `top-bar-right-hide` | switch | string | `"0"` | grupo: Top bar right; opciones: `1`, `0` — *Top bar right* |
| `header-action-title` | text | string |  | grupo: Action button — *Título* |
| `header-action-link` | text | string |  | grupo: Action button — *Enlace* |
| `header-action-target` | checkbox | array {clave: clave} solo marcados |  | grupo: Action button; opciones: `target`, `scroll` — *Opciones* |
| `header-wpml` | select | string |  | grupo: WPML; opciones: ``, `dropdown-name`, `horizontal`, `horizontal-code`, `hide` — *Custom switcher* |
| `header-wpml-options` | checkbox | array {clave: clave} solo marcados |  | grupo: WPML; opciones: `link-to-home` — *Custom switcher options* |
| `header-banner` | textarea | string |  | grupo: Otro — *Banner* |
| `sliding-top` | select | string | `"0"` | grupo: Parte superior deslizante; opciones: `1`, `center`, `left`, `0` — *Parte superior deslizante* |
| `sliding-top-icon` | icon | string | `"icon-down-open-mini"` | grupo: Parte superior deslizante — *Icono* |

## Menu & Action Bar (`mab`)

### Menu (`menu`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `menu-style` | select | string | `"link-color"` | grupo: Diseño; opciones: `link-color`, ``, `line-below`, `line-below-80`, `line-below-80-1`, `arrow-top`, `arrow-bottom`, `highlight`, `hide` — *Estilo* |
| `menu-options` | checkbox | array {clave: clave} solo marcados | `{"align-right":"align-right"}` | grupo: Diseño; opciones: `align-right`, `menu-arrows`, `hide-borders`, `submenu-active`, `last` — *Opciones* |
| `menu-creative-options` | checkbox | array {clave: clave} solo marcados |  | grupo: Header creative; opciones: `scroll`, `dropdown` — *Opciones* |
| `menu-mega-style` | select | string |  | grupo: Menú mega; opciones: ``, `vertical` — *Estilo* |

### Action Bar (`action-bar`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `action-bar` | checkbox | array {clave: clave} solo marcados |  | grupo: Diseño; opciones: `show`, `creative`, `side-slide` — *Action Bar* |
| `header-slogan` | text | string |  | grupo: Diseño — *Eslogan* |
| `header-phone` | text | string |  | grupo: Diseño — *Teléfono* |
| `header-phone-2` | text | string |  | grupo: Diseño — *2nd Phone* |
| `header-email` | text | string |  | grupo: Diseño — *Correo electrónico* |

## Sidebars (`sidebars`)

### General (`sidebars`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `sidebars` | multi_text | array indexado |  | grupo: Sidebars; dynamic — *Sidebars* |
| `sidebar-width` | sliderbar | string | `"23"` | grupo: Diseño — *Ancho* |
| `sidebar-style` | switch | string | `"simple"` | grupo: Diseño; opciones: `classic`, `simple` — *Estilo* |
| `sidebar-lines` | switch | string | `"lines-hidden"` | grupo: Diseño; opciones: `lines-hidden`, `lines-boxed`, `` — *Lines* |
| `sidebar-sticky` | switch | string | `"0"` | grupo: Diseño; opciones: `0`, `1` — *Fijo* |
| `ofcs-global-icon` | icon | string | `"fas fa-indent"` | grupo: Diseño — *Off-canvas sidebar icon* |
| `single-page-layout` | radio_img | string |  | grupo: Pages; opciones: ``, `no-sidebar`, `left-sidebar`, `right-sidebar`, `both-sidebars`, `offcanvas-sidebar` — *Diseño* |
| `single-page-sidebar` | text | string |  | grupo: Pages; dynamic — *Barra lateral* |
| `single-page-sidebar2` | text | string |  | grupo: Pages; dynamic — *Sidebar 2* |
| `single-layout` | radio_img | string |  | grupo: Single posts; opciones: ``, `no-sidebar`, `left-sidebar`, `right-sidebar`, `both-sidebars`, `offcanvas-sidebar` — *Diseño* |
| `single-sidebar` | text | string |  | grupo: Single posts; dynamic — *Barra lateral* |
| `single-sidebar2` | text | string |  | grupo: Single posts; dynamic — *Sidebar 2* |
| `single-portfolio-layout` | radio_img | string |  | grupo: Single portfolio projects; opciones: ``, `no-sidebar`, `left-sidebar`, `right-sidebar`, `both-sidebars`, `offcanvas-sidebar` — *Diseño* |
| `single-portfolio-sidebar` | text | string |  | grupo: Single portfolio projects; dynamic — *Barra lateral* |
| `single-portfolio-sidebar2` | text | string |  | grupo: Single portfolio projects; dynamic — *Sidebar 2* |
| `search-layout` | radio_img | string | `"no-sidebar"` | grupo: Página de búsqueda; opciones: `no-sidebar`, `left-sidebar`, `right-sidebar`, `offcanvas-sidebar` — *Diseño* |
| `search-sidebar-inherited-woo` | switch | string | `""` | grupo: Página de búsqueda; opciones: ``, `1` — *Inherited from shop* |

## Blog & Portfolio (`bps`)

### General (`bps-general`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `excerpt-length` | text | string | `"26"` | grupo: Blog & Portfolio — *Excerpt length* |
| `love` | switch | string | `"1"` | grupo: Blog & Portfolio; opciones: `0`, `1` — *Love Box* |
| `featured-image-caption` | switch | string | `""` | grupo: Single post & Single portfolio; opciones: `hide`, `hide-mobile`, `` — *Featured Image caption* |
| `related-style` | switch | string | `"simple"` | grupo: Single post & Single portfolio; opciones: ``, `simple` — *Related style* |
| `title-heading` | switch | string | `"1"` | grupo: Single post & Single portfolio; opciones: `1`, `2`, `3`, `4`, `5`, `6` — *Etiqueta de título* |

### Blog (`blog`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `blog-posts` | text | string | `9` | grupo: Diseño — *Posts per page* |
| `blog-layout` | radio_img | string | `"grid"` | grupo: Diseño; opciones: `grid`, `classic`, `masonry`, `masonry tiles`, `photo`, `photo2`, `timeline` — *Diseño* |
| `blog-columns` | sliderbar | string | `3` | grupo: Diseño — *Columnas* |
| `blog-title-tag` | select | string | `"4"` | grupo: Diseño; opciones: `2`, `3`, `4`, `5`, `6` — *Etiqueta de título* |
| `blog-images` | select | string |  | grupo: Diseño; opciones: ``, `images-only` — *Post image* |
| `blog-full-width` | switch | string | `"0"` | grupo: Diseño; opciones: `0`, `1` — *Ancho completo* |
| `blog-page` | select_ajax | string |  | grupo: Opciones; dynamic — *Blog page* |
| `blog-orderby` | select | string | `"date"` | grupo: Opciones; opciones: `date`, `title`, `rand` — *Ordenar por* |
| `blog-order` | select | string | `"DESC"` | grupo: Opciones; opciones: `ASC`, `DESC` — *Orden* |
| `exclude-category` | text | string |  | grupo: Opciones — *Exclude category* |
| `read-more-icon` | icon | string | `"icon-doc-text"` | grupo: Opciones — *Read more Icon* |
| `blog-meta` | checkbox | array {clave: clave} solo marcados | `{"author":"author","date":"date","categories":"categories"}` | grupo: Opciones; opciones: `author`, `date`, `categories` — *Meta* |
| `blog-load-more` | switch | string | `"0"` | grupo: Opciones; opciones: `0`, `1` — *Cargar más* |
| `blog-infinite-scroll` | switch | string | `"0"` | grupo: Opciones; opciones: `0`, `1` — *Infinite scroll* |
| `blog-filters` | select | string | `"1"` | grupo: Opciones; opciones: `1`, `only-categories`, `only-tags`, `only-authors`, `0` — *Filtros* |
| `blog-isotope` | switch | string | `"0"` | grupo: Opciones; opciones: `0`, `1` — *jQuery filtering* |
| `blog-title` | switch | string | `"0"` | grupo: Publicación individual; opciones: `0`, `1` — *Título* |
| `blog-author` | switch | string | `"1"` | grupo: Publicación individual; opciones: `0`, `1` — *Author box* |
| `blog-comments` | switch | string | `"1"` | grupo: Publicación individual; opciones: `0`, `1` — *Comments* |
| `blog-featured-image-hide` | switch | string | `""` | grupo: Publicación individual; opciones: `hide`, `` — *Imagen destacada* |
| `blog-single-zoom` | switch | string | `"1"` | grupo: Publicación individual; opciones: `0`, `1` — *Featured image click* |
| `blog-single-layout` | text | string |  | grupo: Publicación individual — *ID de diseño* |
| `blog-single-menu` | select | string |  | grupo: Publicación individual; dynamic, 4 opciones — *Menu* |
| `blog-related` | text | string | `3` | grupo: Related posts — *Contar* |
| `blog-related-columns` | sliderbar | string | `3` | grupo: Related posts — *Columnas* |
| `blog-related-images` | select | string |  | grupo: Related posts; opciones: ``, `images-only` — *Post image* |
| `single-intro-padding` | text | string |  | grupo: Encabezado de introducción — *Relleno* |
| `blog-love-rand` | ajax | no persiste (botón) |  | grupo: Avanzado; dynamic — *Love count* |

### Portafolio (`portfolio`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `portfolio-posts` | text | string | `9` | grupo: Diseño — *Posts per page* |
| `portfolio-layout` | radio_img | string | `"grid"` | grupo: Diseño; opciones: `grid`, `flat`, `masonry`, `masonry-hover`, `masonry-minimal`, `masonry-flat`, `list`, `exposure` — *Diseño* |
| `portfolio-columns` | sliderbar | string | `3` | grupo: Diseño — *Columnas* |
| `portfolio-full-width` | switch | string | `"0"` | grupo: Diseño; opciones: `0`, `1` — *Ancho completo* |
| `portfolio-page` | select_ajax | string |  | grupo: Opciones; dynamic — *Portfolio page* |
| `portfolio-orderby` | select | string | `"date"` | grupo: Opciones; opciones: `date`, `menu_order`, `title`, `rand` — *Ordenar por* |
| `portfolio-order` | select | string | `"DESC"` | grupo: Opciones; opciones: `ASC`, `DESC` — *Orden* |
| `portfolio-external` | select | string |  | grupo: Opciones; opciones: ``, `popup`, `disable`, `_self`, `_blank` — *Project link* |
| `portfolio-hover-title` | switch | string | `"0"` | grupo: Opciones; opciones: `0`, `1` — *Hover title* |
| `portfolio-meta` | checkbox | array {clave: clave} solo marcados | `{"author":"author","date":"date","categories":"categories"}` | grupo: Opciones; opciones: `author`, `date`, `categories` — *Meta* |
| `portfolio-load-more` | switch | string | `"0"` | grupo: Opciones; opciones: `0`, `1` — *Cargar más* |
| `portfolio-infinite-scroll` | switch | string | `"0"` | grupo: Opciones; opciones: `0`, `1` — *Infinite scroll* |
| `portfolio-filters` | select | string | `"1"` | grupo: Opciones; opciones: `1`, `only-categories`, `0` — *Filtros* |
| `portfolio-isotope` | switch | string | `"1"` | grupo: Opciones; opciones: `0`, `1` — *jQuery filtering* |
| `portfolio-single-title` | switch | string | `"0"` | grupo: Single portfolio project; opciones: `0`, `1` — *Título* |
| `portfolio-featured-image-hide` | switch | string | `""` | grupo: Single portfolio project; opciones: `hide`, `` — *Imagen destacada* |
| `portfolio-related` | text | string | `3` | grupo: Single portfolio project — *Related projects count* |
| `portfolio-related-columns` | sliderbar | string | `3` | grupo: Single portfolio project — *Related projects columns* |
| `portfolio-comments` | switch | string | `"0"` | grupo: Single portfolio project; opciones: `0`, `1` — *Comments* |
| `portfolio-single-layout` | text | string |  | grupo: Single portfolio project — *ID de diseño* |
| `portfolio-single-menu` | select | string |  | grupo: Single portfolio project; dynamic, 4 opciones — *Menu* |
| `portfolio-love-rand` | ajax | no persiste (botón) |  | grupo: Avanzado; dynamic — *Love count* |
| `portfolio-slug` | text | string | `"portfolio-item"` | grupo: Avanzado — *Single project slug* |
| `portfolio-tax` | text | string | `"portfolio-types"` | grupo: Avanzado — *Slug de la categoría* |

### Imagen destacada (`featured-image`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `srcset-featured-image` | switch | string | `"0"` | grupo: Responsive; opciones: `0`, `1` — *Srcset* |
| `info-force-regenerate` | info | no persiste |  | grupo: Responsive; condición UI: `srcset-featured-image` — *After making changes on this page please Regenerate Thumbnails.* |
| `featured-blog-portfolio-width` | text | string | `"960"` | grupo: Archivos — *Ancho* |
| `featured-blog-portfolio-height` | text | string | `"750"` | grupo: Archivos — *Altura* |
| `featured-blog-portfolio-crop` | select | string |  | grupo: Archivos; opciones: `crop`, `resize` — *Crop* |
| `featured-desc-list` | custom | no persiste |  | grupo: Archivos — *Description* |
| `featured-single-width` | text | string | `"1200"` | grupo: Single — *Ancho* |
| `featured-single-height` | text | string | `"480"` | grupo: Single — *Altura* |
| `featured-single-crop` | select | string |  | grupo: Single; opciones: `crop`, `resize` — *Crop* |
| `featured-desc-single` | custom | no persiste |  | grupo: Single — *Description* |

## Tienda (`shop`)

### General (`shop`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-shop` | info | no persiste |  |  — *Shop requires free WooCommerce plugin.* |
| `shop-sidebar` | select | string |  | grupo: General; dynamic, 2 opciones — *Barra lateral* |
| `shop-slider` | select | string |  | grupo: General; opciones: ``, `all` — *Control deslizante* |
| `shop-catalogue` | switch | string | `"0"` | grupo: General; opciones: `0`, `1`, `hide-price` — *Catalogue mode* |
| `variable-swatches` | switch | string | `"1"` | grupo: General; opciones: `0`, `1` — *Custom Variation Swatches* |
| `shop-image-width` | text | string | `800` | grupo: Image sizes — *Shop product image width* |
| `single-product-main-image-size` | text | string | `800` | grupo: Image sizes — *Single product image width* |
| `single-product-thumbnails-size` | text | string | `300` | grupo: Image sizes — *Single product thumbnail width* |
| `product-badge-new` | switch | string | `"0"` | grupo: Badges; opciones: `0`, `1` — *New badge* |
| `product-badge-new-days` | text | string | `"14"` | grupo: Badges; condición UI: `product-badge-new` — *New badge time limit* |
| `product-badge-new-text` | text | string | `"NEW"` | grupo: Badges; condición UI: `product-badge-new` — *New badge text* |
| `sale-badge-style` | switch | string | `"label"` | grupo: Badges; opciones: `label`, `percent` — *Sale badge style* |
| `sale-badge-label` | text | string |  | grupo: Badges; condición UI: `sale-badge-style` — *Sale badge label* |
| `sale-badge-before` | text | string |  | grupo: Badges; condición UI: `sale-badge-style` — *Text before "percent"* |
| `sale-badge-after` | text | string |  | grupo: Badges; condición UI: `sale-badge-style` — *Text after "percent"* |
| `shop-soldout` | text | string | `"Sold out"` | grupo: Badges — *Sold out text* |
| `shop-icons-hide` | checkbox | array {clave: clave} solo marcados |  | grupo: Header icons; opciones: `user`, `wishlist`, `cart` — *Show icons* |
| `shop-user` | icon | string |  | grupo: Header icons — *User icon* |
| `shop-icon-wishlist` | icon | string |  | grupo: Header icons — *Wishlist icon* |
| `shop-cart` | icon | string |  | grupo: Header icons — *Cart icon* |
| `shop-cart-total-hide` | checkbox | array {clave: clave} solo marcados |  | grupo: Header icons; opciones: `desktop`, `tablet`, `mobile` — *Cart total* |
| `shop-icon-count-if-zero` | switch | string | `"1"` | grupo: Header icons; opciones: `0`, `1` — *Icon count if zero* |
| `shop-wishlist` | switch | string | `"0"` | grupo: Wishlist; opciones: `0`, `1` — *Wishlist* |
| `shop-wishlist-page` | select_ajax | string |  | grupo: Wishlist; dynamic; condición UI: `shop-wishlist` — *Wishlist page* |
| `shop-wishlist-position` | checkbox | array {clave: clave} solo marcados | `{"2":"2"}` | grupo: Wishlist; opciones: `0`, `1`, `2`; condición UI: `shop-wishlist` — *Wishlist button* |
| `shop-wishlist-type` | switch | string | `""` | grupo: Wishlist; opciones: ``, `button`; condición UI: `shop-wishlist` — *Single product wishlist* |
| `shop-button-label-wishlist` | text | string | `"Add to wishlist"` | grupo: Wishlist; condición UI: `shop-wishlist-type` — *Wishlist button label* |
| `info-shopify` | info | no persiste |  | grupo: Finalizar compra — *Do you want checkout to look like Shopify’s? Apply recommended settings* |
| `gutenberg-checkout` | switch | string | `""` | grupo: Finalizar compra; opciones: ``, `1` — *Block-based checkout style* |
| `gutenberg-checkout-summary-background` | color | string |  | grupo: Finalizar compra; condición UI: `gutenberg-checkout` — *Checkout summary background* |
| `gutenberg-checkout-header` | select | string |  | grupo: Finalizar compra; dynamic, 3 opciones — *Encabezado* |
| `gutenberg-checkout-options` | checkbox | array {clave: clave} solo marcados |  | grupo: Finalizar compra; opciones: `hide-subheader`, `hide-steps`, `hide-footer` — *Visibility options* |
| `sticky-shop-menu` | switch | string | `"0"` | grupo: Móvil; dynamic, 2 opciones — *Sticky shop menu* |

### Products list (`shop-list`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `shop-products` | text | string | `"12"` | grupo: Diseño — *Products per page* |
| `shop-layout` | radio_img | string | `"grid"` | grupo: Diseño; opciones: `grid col-2`, `grid`, `grid col-4`, `masonry`, `list`, `custom_tmpl` — *Diseño* |
| `mobile-products-row` | switch | string | `"2"` | grupo: Diseño; opciones: `1`, `2` — *Mobile layout* |
| `shop-template` | select | string |  | grupo: Diseño; dynamic, 1 opciones — *Plantilla* |
| `shop-images` | select | string |  | grupo: Imagen; opciones: ``, `secondary`, `slider`, `plugin` — *Imágenes* |
| `shop-align` | switch | string |  | grupo: Contenido; opciones: `left`, ``, `right` — *Alignment* |
| `shop-title-tag` | switch | string | `""` | grupo: Contenido; opciones: `h1`, `h2`, `h3`, ``, `h5`, `h6`, `p`, `p.lead` — *Etiqueta de título* |
| `shop-excerpt` | switch | string | `"0"` | grupo: Contenido; opciones: `0`, `1`, `list` — *Descripción* |
| `shop-button` | switch | string | `"0"` | grupo: Contenido; opciones: `0`, `1`, `list` — *Add to cart button* |
| `shop-infinite-load` | switch | string | `0` | grupo: Opciones; opciones: `0`, `1` — *Infinite Load* |
| `shop-quick-view` | switch | string | `"0"` | grupo: Opciones; opciones: `0`, `1` — *Quick view* |
| `shop_equal_heights` | switch | string | `0` | grupo: Opciones; opciones: `0`, `1` — *Products List Standardization* |
| `shop_equal_heights_last_el_class` | select | string | `""` | grupo: Opciones; opciones: ``, `title`, `price`, `variations`, `description`, `button`; condición UI: `shop_equal_heights` — *Align to the bottom from* |
| `shop-list-active-filters` | switch | string | `"0"` | grupo: Filtros; opciones: `0`, `1` — *Active filters* |
| `shop-list-perpage` | switch | string | `"0"` | grupo: Filtros; opciones: `0`, `1` — *Products per page* |
| `shop-list-layout` | switch | string | `"0"` | grupo: Filtros; opciones: `0`, `1` — *Layout switch* |
| `shop-list-sorting` | switch | string | `"0"` | grupo: Filtros; opciones: `0`, `1` — *List sorting* |
| `shop-list-results-count` | switch | string | `"0"` | grupo: Filtros; opciones: `0`, `1` — *Results count* |

### Producto individual (`shop-single`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `shop-product-style` | radio_img | string | `"default"` | grupo: Diseño; opciones: `default`, `modern`, `wide`, `wide tabs`, ``, `tabs`, `custom_tmpl` — *Estilo* |
| `shop-product-tabs` | switch | string | `""` | grupo: Diseño; opciones: `inside`, ``; condición UI: `shop-product-style` — *Product tabs* |
| `shop-product-template` | select | string |  | grupo: Diseño; dynamic, 1 opciones — *Plantilla* |
| `shop-single-image` | switch | string | `""` | grupo: Imagen; opciones: ``, `disable-zoom` — *Main image* |
| `shop-product-gallery` | select | string |  | grupo: Imagen; opciones: ``, `mfn-thumbnails-bottom mfn-bottom-left`, `mfn-thumbnails-bottom mfn-bottom-center`, `mfn-thumbnails-bottom mfn-bottom-right`, `mfn-thumbnails-left mfn-left-top`, `mfn-thumbnails-left mfn-left-center`, `mfn-thumbnails-left mfn-left-bottom`, `mfn-thumbnails-right mfn-right-top`, `mfn-thumbnails-right mfn-right-center`, `mfn-thumbnails-right mfn-right-bottom`, `mfn-gallery-grid` — *Gallery* |
| `shop-product-gallery-overlay` | switch | string | `"mfn-thumbnails-outside"` | grupo: Imagen; opciones: `mfn-thumbnails-outside`, `mfn-thumbnails-overlay`; condición UI: `shop-product-gallery` — *Thumbnails position* |
| `shop-product-main-image-margin` | select | string | `"mfn-mim-0"` | grupo: Imagen; opciones: `mfn-mim-0`, `mfn-mim-2`, `mfn-mim-5`, `mfn-mim-10`, `mfn-mim-15`, `mfn-mim-20`, `mfn-mim-25`, `mfn-mim-30`; condición UI: `shop-product-gallery` — *Main image margin* |
| `shop-product-thumbnails-margin` | text | string |  | grupo: Imagen; condición UI: `shop-product-gallery` — *Thumbnails margin* |
| `shop-product-gallery-grid-cols` | text | string |  | grupo: Imagen; responsive — *Columnas* |
| `shop-product-gallery-grid-cols-tablet` | text | string |  | grupo: Imagen; responsive — *Columnas* |
| `shop-product-gallery-grid-cols-mobile` | text | string |  | grupo: Imagen; responsive — *Columnas* |
| `product-lightbox-bg` | color | string |  | grupo: Lightbox — *Fondo* |
| `product-lightbox-caption` | switch | string | `""` | grupo: Lightbox; opciones: `off`, `` — *Caption* |
| `shop-product-title` | switch | string | `""` | grupo: Contenido; opciones: ``, `content-sub`, `sub` — *Show title in* |
| `shop-product-tag` | switch | string | `"h1"` | grupo: Contenido; opciones: `h1`, `h2`, `h3`, `h4`, `h5`, `h6`, `span` — *Subheader title tag* |
| `shop-hide-content` | switch | string | `""` | grupo: Contenido; opciones: `1`, `` — *El contenido* |
| `shop-product-cart-button-extra` | switch | string | `"0"` | grupo: Contenido; opciones: `0`, `1` — *Cart button extra options* |
| `shop-related` | text | string | `3` | grupo: Contenido — *Related products count* |
| `shop-mobile-review-avatar-hide` | switch | string | `"0"` | grupo: Contenido; opciones: `1`, `0` — *Reviews avatar on mobile* |

### Addons (`shop-addons`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `free-delivery-addon` | switch | string | `"0"` | grupo: Free delivery progress bar; opciones: `0`, `1` — *Estado* |
| `free-delivery-sum` | text | string | `"200"` | grupo: Free delivery progress bar; condición UI: `free-delivery-addon` — *Sum* |
| `free-delivery-addon-tax` | switch | string | `""` | grupo: Free delivery progress bar; opciones: ``, `1`, `2`; condición UI: `free-delivery-addon` — *Calculate by* |
| `fake-sale-addon` | switch | string | `"0"` | grupo: Fake sale notification; opciones: `0`, `1` — *Estado* |
| `fake-sale-type` | select | string | `"0"` | grupo: Fake sale notification; opciones: `0`, `1`, `2`; condición UI: `fake-sale-addon` — *Tipo* |
| `fake-sale-clients-names` | select | string | `"0"` | grupo: Fake sale notification; opciones: `0`, `1`; condición UI: `fake-sale-addon` — *Clients names* |
| `fake-sale-clients-list` | textarea | string | `"John, Linda, Ann, Charles"` | grupo: Fake sale notification; condición UI: `fake-sale-addon` — *Clients fake names* |
| `fake-sale-clients-position` | select | string | `""` | grupo: Fake sale notification; opciones: ``, `bottom-right`; condición UI: `fake-sale-addon` — *Notification position* |
| `fake-sale-closeable` | select | string | `""` | grupo: Fake sale notification; opciones: ``, `1`; condición UI: `fake-sale-addon` — *Closeable* |
| `fake-sale-start-delay` | text | string | `"5"` | grupo: Fake sale notification; condición UI: `fake-sale-addon` — *Display after* |
| `fake-sale-products-limit` | text | string | `"10"` | grupo: Fake sale notification; condición UI: `fake-sale-addon` — *Random products limit* |
| `shop-sidecart` | switch | string | `""` | grupo: Side cart; opciones: ``, `1` — *Estado* |
| `shop-sidecart-continue-shopping` | switch | string | `""` | grupo: Side cart; opciones: ``, `1`; condición UI: `shop-sidecart` — *Side cart "Continue Shopping" button* |

### Addons design (`shop-addons-design`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `fake-sale-container-background` | color | string |  | grupo: Fake sale notification; condición UI: `fake-sale-addon` — *Fondo* |
| `fake-sale-container-color` | color | string |  | grupo: Fake sale notification; condición UI: `fake-sale-addon` — *Text color* |
| `fake-sale-container-link-color` | color | string |  | grupo: Fake sale notification; condición UI: `fake-sale-addon` — *Link color* |
| `fake-sale-container-exit-color` | color | string |  | grupo: Fake sale notification; condición UI: `fake-sale-addon` — *Close color* |
| `free-delivery-color-active` | color | string |  | grupo: Free delivery progress bar; condición UI: `free-delivery-addon` — *Active bar color* |
| `free-delivery-color-inactive` | color | string |  | grupo: Free delivery progress bar; condición UI: `free-delivery-addon` — *Inactive bar color* |
| `free-delivery-color-achieved` | color | string |  | grupo: Free delivery progress bar; condición UI: `free-delivery-addon` — *“Eligible for free delivery” bar color* |
| `product-list-gallery-slider-nav-offset` | text | string | `""` | grupo: Product gallery slider — *Nav & Pagination offset* |
| `product-list-gallery-slider-border-radius` | text | string | `""` | grupo: Product gallery slider — *Nav & Pagination border radius* |
| `product-list-gallery-slider-arrows-visibility` | switch | string | `""` | grupo: Product gallery slider; opciones: ``, `hidden` — *Flechas* |
| `product-list-gallery-slider-arrow-bg` | color_multi | array {subclave: color} | `{"normal":"#fff","hover":"#fff"}` | grupo: Product gallery slider — *Arrow background* |
| `product-list-gallery-slider-arrow-color` | color_multi | array {subclave: color} | `{"normal":"#000","hover":"#000"}` | grupo: Product gallery slider — *Arrow color* |
| `product-list-gallery-slider-pagination-style` | switch | string | `""` | grupo: Product gallery slider; opciones: ``, `lines` — *Pagination style* |
| `product-list-gallery-slider-pagination-dots-size` | text | string | `""` | grupo: Product gallery slider — *Pagination dot size* |
| `product-list-gallery-slider-pagination-dots-gap` | text | string | `""` | grupo: Product gallery slider — *Pagination dots gap* |
| `product-list-gallery-slider-dots-bg` | color_multi | array {subclave: color} | `{"normal":"rgba(0,0,0,0.3)","active":"#000"}` | grupo: Product gallery slider — *Pagination color* |
| `product-list-gallery-slider-pagination-bg` | color | string |  | grupo: Product gallery slider — *Pagination background* |
| `sidecart-background` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Color de fondo* |
| `sidecart-heading` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Heading color* |
| `sidecart-color` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Text color* |
| `sidecart-close-color` | color_multi | array {subclave: color} |  | grupo: Side cart; condición UI: `shop-sidecart` — *Close icon color* |
| `sidecart-border-color` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Color del borde* |
| `sidecart-product-group` | group | array |  | grupo: Side cart; condición UI: `shop-sidecart` |
| `sidecart-product-background` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Product container background* |
| `sidecart-product-title-color` | color_multi | array {subclave: color} |  | grupo: Side cart; condición UI: `shop-sidecart` — *Product title color* |
| `sidecart-product-color` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Product text color* |
| `sidecart-product-price-color` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Product price color* |
| `sidecart-product-quantity-background` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Product quantity background* |
| `sidecart-product-quantity-color` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Product quantity text color* |
| `sidecart-product-quantity-button-background` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Product quantity button background* |
| `sidecart-product-quantity-button-color` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Product quantity button color* |
| `sidecart-footer-group` | group | array |  | grupo: Side cart; condición UI: `shop-sidecart` |
| `sidecart-footer-background` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Footer background* |
| `sidecart-footer-color` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Footer text color* |
| `sidecart-footer-code-background` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Coupon code background* |
| `sidecart-footer-code-color` | color | string |  | grupo: Side cart; condición UI: `shop-sidecart` — *Coupon code text color* |
| `sidecart-buttons-group` | group | array |  | grupo: Side cart; condición UI: `shop-sidecart` |
| `sidecart-button-background` | color_multi | array {subclave: color} |  | grupo: Side cart; condición UI: `shop-sidecart` — *Checkout button background* |
| `sidecart-button-color` | color_multi | array {subclave: color} |  | grupo: Side cart; condición UI: `shop-sidecart` — *Checkout button text color* |
| `sidecart-cart-color` | color_multi | array {subclave: color} |  | grupo: Side cart; condición UI: `shop-sidecart` — *Cart link color* |

## Pages (`pages`)

### General (`pages-general`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `page-comments` | switch | string | `"0"` | grupo: General; opciones: `0`, `1` — *Page comments* |

### Error 404 (`pages-404`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `error404-icon` | icon | string | `"icon-traffic-cone"` | grupo: Error 404 — *Icono* |
| `error404-page` | select_ajax | string |  | grupo: Error 404; dynamic — *Custom page* |
| `error404-header` | switch | string | `"0"` | grupo: Error 404; dynamic, 2 opciones; condición UI: `error404-page` — *Encabezado* |
| `error404-footer` | switch | string | `"0"` | grupo: Error 404; dynamic, 2 opciones; condición UI: `error404-page` — *Pie de página* |

### En construcción (`pages-under`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `construction` | switch | string | `"0"` | grupo: En construcción; opciones: `0`, `1` — *En construcción* |
| `logo-under-construction` | upload | string (URL o URL#attachment_id) |  | grupo: En construcción — *Logotipo* |
| `construction-title` | text | string | `"Coming Soon"` | grupo: En construcción — *Título* |
| `construction-text` | textarea | string |  | grupo: En construcción — *Text* |
| `construction-date` | text | string | `"12/30/2018 12:00:00"` | grupo: En construcción — *Launch date* |
| `construction-offset` | select | string | `"0"` | grupo: En construcción; opciones: `-12`, `-11`, `-10`, `-9.5`, `-9`, `-8`, `-7`, `-6`, `-5`, `-4`, `-3.5`, `-3`, … — *UTC timezone* |
| `construction-contact` | text | string |  | grupo: En construcción; dynamic — *Contact Form shortcode* |
| `construction-page` | select_ajax | string |  | grupo: En construcción; dynamic — *Custom page* |
| `construction-header` | switch | string | `"0"` | grupo: En construcción; dynamic, 2 opciones; condición UI: `construction-page` — *Encabezado* |
| `construction-footer` | switch | string | `"0"` | grupo: En construcción; dynamic, 2 opciones; condición UI: `construction-page` — *Pie de página* |

## Pie de página (`footer`)

### General (`footer`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `footer-layout` | radio_img | string |  | grupo: Diseño; opciones: ``, `5;one-fifth;one-fifth;one-fifth;one-fifth;one-fifth;`, `4;one-fourth;one-fourth;one-fourth;one-fourth`, `3;one-fifth;two-fifth;two-fifth`, `3;two-fifth;one-fifth;two-fifth`, `3;two-fifth;two-fifth;one-fifth`, `3;one-fourth;one-fourth;one-second;`, `3;one-fourth;one-second;one-fourth;`, `3;one-second;one-fourth;one-fourth;`, `3;one-third;one-third;one-third;`, `2;one-third;two-third;;`, `2;two-third;one-third;;`, … — *Diseño* |
| `footer-style` | select | string |  | grupo: Diseño; opciones: ``, `fixed`, `sliding`, `stick`, `hide` — *Estilo* |
| `footer-padding` | text | string | `"70px 0"` | grupo: Diseño — *Relleno* |
| `footer-options` | checkbox | array {clave: clave} solo marcados |  | grupo: Diseño; opciones: `full-width` — *Opciones* |
| `footer-bg-img` | upload | string (URL o URL#attachment_id) |  | grupo: Fondo — *Imagen* |
| `footer-bg-img-position` | select | string | `"center top no-repeat"` | grupo: Fondo; opciones: ``, `no-repeat;left top;;`, `repeat;left top;;`, `no-repeat;left center;;`, `repeat;left center;;`, `no-repeat;left bottom;;`, `repeat;left bottom;;`, `no-repeat;center top;;`, `repeat;center top;;`, `repeat-x;center top;;`, `repeat-y;center top;;`, `no-repeat;center;;`, … — *Posición* |
| `footer-bg-img-size` | select | string |  | grupo: Fondo; opciones: ``, `auto`, `contain`, `cover`, `cover-ultrawide` — *Tamaño* |
| `footer-call-to-action` | textarea | string |  | grupo: Avanzado — *Call to action* |
| `footer-copy` | textarea | string |  | grupo: Avanzado — *Copyright* |
| `footer-hide` | select | string |  | grupo: Avanzado; opciones: ``, `center`, `1` — *Copyright & Social bar* |
| `back-top-top` | select | string |  | grupo: Extras; opciones: ``, `sticky`, `sticky scroll`, `hide` — *Back to top* |
| `popup-contact-form` | text | string |  | grupo: Extras — *Popup Contact Form shortcode* |
| `popup-contact-form-icon` | icon | string | `"icon-mail-line"` | grupo: Extras — *Popup Contact Form icon* |

## Buscar (`search`)

### Form (`search-form`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `header-search` | switch | string | `""` | grupo: General; opciones: ``, `shop` — *Search type* |
| `header-search-mode` | switch | string | `""` | grupo: General; opciones: ``, `exact`; condición UI: `header-search` — *Search Mode* |
| `header-search-form` | switch | string | `"0"` | grupo: General; opciones: `0`, `1`, `input` — *Search form* |
| `header-search-input-width` | text | string | `"200"` | grupo: General; condición UI: `header-search-form` — *Input field width* |
| `header-search-live` | switch | string | `"0"` | grupo: Live search; opciones: `0`, `1` — *Live Search* |
| `header-search-live-min-characters` | text | string | `"3"` | grupo: Live search; condición UI: `header-search-live` — *Caracteres mínimos* |
| `header-search-live-load-posts` | text | string | `"10"` | grupo: Live search; condición UI: `header-search-live` — *Número de publicaciones* |
| `header-search-live-container-height` | text | string | `"300"` | grupo: Live search; condición UI: `header-search-live` — *Altura del contenedor de resultados de búsqueda* |
| `header-search-live-featured_image` | switch | string | `"1"` | grupo: Live search; opciones: `0`, `1`; condición UI: `header-search-live` — *Imagen destacada* |
| `header-search-live-wpml_search_in_language` | switch | string | `"1"` | grupo: Live search; opciones: `0`, `1`; condición UI: `header-search-live` — *Search in language* |

### Form design (`search-form-design`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `search-scroll-disable` | switch | string | `"0"` | grupo: Popup search form; opciones: `1`, `0` — *Desplazamiento del navegador* |
| `search-overlay` | switch | string | `"0"` | grupo: Popup search form; opciones: `0`, `1` — *Superposición de contenido* |
| `search-overlay-color` | color | string | `"rgba(0,0,0,.6)"` | grupo: Popup search form; condición UI: `search-overlay` — *Superposición de contenido* |
| `search-overlay-blur` | sliderbar | string | `0` | grupo: Popup search form — *Content blur* |

### Página (`search-page`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `search-page-featured` | switch | string | `"1"` | grupo: Imagen; opciones: `0`, `1` — *Imagen* |
| `search-page-featured-position` | switch | string | `"left"` | grupo: Imagen; opciones: `left`, `right`; condición UI: `search-page-featured` — *Posición* |
| `search-page-author` | switch | string | `"1"` | grupo: Contenido; opciones: `0`, `1` — *Autor* |
| `search-page-date` | switch | string | `"1"` | grupo: Contenido; opciones: `0`, `1` — *Publish date* |
| `search-page-excerpt` | switch | string | `"1"` | grupo: Contenido; opciones: `0`, `1` — *Extracto* |
| `search-page-readmore` | switch | string | `"1"` | grupo: Leer más; opciones: `0`, `1` — *Leer más* |
| `search-page-readmore-aligment` | switch | string | `"right"` | grupo: Leer más; opciones: `left`, `center`, `right`; condición UI: `search-page-readmore` — *Aligment* |
| `search-page-readmore-style` | switch | string | `"link"` | grupo: Leer más; opciones: `button`, `link`; condición UI: `search-page-readmore` — *Estilo* |
| `search-page-readmore-icon` | icon | string |  | grupo: Leer más; condición UI: `search-page-readmore` — *Icono* |

## Responsive (`responsive`)

### General (`responsive`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `responsive` | switch | string | `"1"` | grupo: General; opciones: `0`, `1` — *Responsive* |
| `elementor-enable` | custom | no persiste |  | grupo: Diseño — *Elementor Flexbox Container* |
| `mobile-grid-width` | sliderbar | string | `480` | grupo: Diseño — *Mobile site width* |
| `mobile-site-padding` | sliderbar | string | `33` | grupo: Diseño — *Mobile site padding* |
| `mobile-images-max-srcset` | sliderbar | string | `""` | grupo: Diseño — *Maximum mobile images srcset width* |
| `responsive-zoom` | switch | string | `"0"` | grupo: Diseño; opciones: `0`, `1` — *Pinch to zoom* |
| `tap-highlight-disable` | switch | string | `"0"` | grupo: Diseño; opciones: `1`, `0` — *WebKit Tap Highlight* |
| `responsive-overflow-x` | select | string | `""` | grupo: Diseño; opciones: `disable`, `tablet`, `` — *Content overflow* |
| `mobile-order` | select | string | `""` | grupo: Diseño; opciones: ``, `sidebar-first` — *Mobile content order* |
| `responsive-boxed2fw` | switch | string | `"0"` | grupo: Opciones; opciones: `0`, `1` — *Diseño* |
| `no-section-bg` | select | string |  | grupo: Opciones; opciones: ``, `tablet` — *Section background image* |
| `responsive-parallax` | select | string |  | grupo: Opciones; opciones: `0`, `1` — *Section parallax* |
| `responsive-video` | select | string |  | grupo: Opciones; opciones: `0`, `1` — *Section video* |
| `builder-section-padding` | select | string |  | grupo: Opciones; opciones: ``, `no-tablet`, `no-mobile` — *Section horizontal padding* |
| `builder-wrap-moveup` | select | string |  | grupo: Opciones; opciones: ``, `no-tablet`, `no-move` — *Wrap move up* |
| `footer-align` | select | string |  | grupo: Opciones; opciones: ``, `center` — *Footer text alignment* |
| `mobile-sidebar` | switch | string | `"0"` | grupo: Opciones; dynamic, 2 opciones — *Mobile sidebar* |
| `responsive-logo-img` | upload | string (URL o URL#attachment_id) |  | grupo: Logotipo — *Logotipo* |
| `responsive-retina-logo-img` | upload | string (URL o URL#attachment_id) |  | grupo: Logotipo — *Retina Logo* |
| `responsive-sticky-logo-img` | upload | string (URL o URL#attachment_id) |  | grupo: Sticky header logo — *Logo* |
| `responsive-sticky-retina-logo-img` | upload | string (URL o URL#attachment_id) |  | grupo: Sticky header logo — *Retina Logo* |
| `safari-bar-light-scheme` | color | string | `"#ffffff"` | grupo: Safari bar — *Light scheme background* |
| `safari-bar-dark-scheme` | color | string | `"#ffffff"` | grupo: Safari bar — *Dark scheme background* |

### Encabezado (`responsive-header`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `mobile-header-height` | text | string |  | grupo: Diseño — *Header height* |
| `mobile-subheader` | checkbox | array {clave: clave} solo marcados |  | grupo: Diseño; opciones: `hide-breadcrumbs` — *Subtítulo* |
| `mobile-subheader-padding` | text | string |  | grupo: Diseño — *Subheader padding* |
| `mobile-menu-initial` | sliderbar | string | `1240` | grupo: Menu — *Menu breakpoint* |
| `responsive-mobile-menu` | select | string | `"side-slide"` | grupo: Menu; dynamic, 2 opciones — *Estilo* |
| `responsive-side-slide-width` | sliderbar | string | `250` | grupo: Menu; condición UI: `responsive-mobile-menu` — *Side slide width* |
| `responsive-side-slide` | checkbox | array {clave: clave} solo marcados |  | grupo: Menu; opciones: `social`; condición UI: `responsive-mobile-menu` — *Side slide options* |
| `header-menu-text` | text | string |  | grupo: Menu — *Menu button text* |
| `mobile-menu` | select | string |  | grupo: Menu; dynamic, 4 opciones — *Menú personalizado* |
| `responsive-header-minimal` | radio_img | string | `""` | grupo: Móvil; opciones: ``, `mr-ll`, `mr-lc`, `mr-lr`, `ml-ll`, `ml-lc`, `ml-lr` — *Diseño* |
| `responsive-top-bar` | switch | string | `"center"` | grupo: Móvil; opciones: `left`, `center`, `right`; condición UI: `responsive-header-minimal` — *Icons alignment* |
| `responsive-header-mobile` | checkbox | array {clave: clave} solo marcados |  | grupo: Móvil; opciones: `sticky`, `transparent` — *Opciones* |
| `header-menu-mobile-sticky` | switch | string | `"0"` | grupo: Móvil; opciones: `0`, `1` — *Sticky menu button* |
| `mobile-icon-user` | switch | string | `"ss"` | grupo: Mobile icons; opciones: `hide`, `tb`, `ss`, `` — *User* |
| `mobile-icon-wishlist` | switch | string | `"ss"` | grupo: Mobile icons; opciones: `hide`, `tb`, `ss`, `` — *Wishlist* |
| `mobile-icon-cart` | switch | string | `""` | grupo: Mobile icons; opciones: `hide`, `tb`, `ss`, `` — *Carrito* |
| `mobile-icon-search` | switch | string | `"ss"` | grupo: Mobile icons; opciones: `hide`, `tb`, `ss`, `` — *Buscar* |
| `mobile-icon-wpml` | switch | string | `"ss"` | grupo: Mobile icons; opciones: `hide`, `tb`, `ss`, `` — *WPML* |
| `mobile-icon-action` | switch | string | `"ss"` | grupo: Mobile icons; opciones: `hide`, `tb`, `ss`, `` — *Action button* |
| `responsive-header-tablet` | checkbox | array {clave: clave} solo marcados |  | grupo: Tableta; opciones: `sticky` — *Opciones* |

## SEO (`seo`)

### General (`seo`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `google-gtag-id` | text | string |  | grupo: Google — *Google Tag ID* |
| `google-gtag-js` | textarea | string |  | grupo: Google — *Google Tag Manager (gtag) - JS snippet* |
| `google-gtag-html` | textarea | string |  | grupo: Google — *Google Tag Manager (gtag) - HTML iframe* |
| `google-remarketing` | textarea | string |  | grupo: Google — *Google Remarketing Tag* |
| `facebook-pixel` | textarea | string |  | grupo: Google — *Facebook Pixel* |
| `google-analytics` | textarea | string |  | grupo: Google — *Google Analytics* |
| `mfn-seo` | switch | string | `"1"` | grupo: SEO fields; opciones: `0`, `1` — *Use built-in fields* |
| `meta-description` | text | string | `""` | grupo: SEO fields — *Meta description* |
| `meta-keywords` | text | string |  | grupo: SEO fields — *Meta keywords* |
| `mfn-seo-og-image` | upload | string (URL o URL#attachment_id) |  | grupo: SEO fields — *Imagen de Open Graph* |
| `seo-fb-app-id` | text | string |  | grupo: SEO fields — *Facebook App ID* |
| `mfn-seo-schema-type` | switch | string | `"1"` | grupo: Avanzado; opciones: `0`, `1` — *Schema Type* |

## Social (`social`)

### General (`social`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `social-attr` | checkbox | array {clave: clave} solo marcados |  | grupo: General; opciones: `blank`, `nofollow`, `noopener`, `noreferrer` — *Link attributes* |
| `social-link` | social | array {red: url, ..., order: csv} |  | grupo: General — *Social icons* |
| `social-custom-icon` | icon | string |  | grupo: Personalizado — *Icono* |
| `social-custom-link` | text | string |  | grupo: Personalizado — *Enlace* |
| `social-custom-title` | text | string |  | grupo: Personalizado — *Título* |
| `custom-icon-count` | text | string |  | grupo: New icon — *New fields amount* |
| `social-rss` | switch | string | `"0"` | grupo: RSS; opciones: `0`, `1` — *RSS* |

## Addons & Plugins (`addons-plugins`)

### Addons (`addons`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `cf7-error` | select | string |  | grupo: Contact Form 7; opciones: ``, `message` — *Contact Form 7 form error* |
| `elementor-container-content` | select | string |  | grupo: Elementor; opciones: ``, `theme` — *Container Content Width* |
| `parallax` | select | string |  | grupo: Parallax; opciones: `translate3d`, `translate3d no-safari`, `enllax`, `stellar` — *Parallax plugin* |
| `prettyphoto-options` | checkbox | array {clave: clave} solo marcados |  | grupo: Lightbox; opciones: `disable`, `disable-swiper`, `disable-mobile`, `title` — *Opciones* |
| `sc-gallery-disable` | switch | string | `"0"` | grupo: Addons; opciones: `1`, `0` — *Gallery shortcode* |
| `recaptcha-display` | checkbox | array {clave: clave} solo marcados |  | grupo: Recaptcha; opciones: `login`, `register` — *Where to display* |
| `recaptcha-key` | text | string |  | grupo: Recaptcha — *Recaptcha key* |
| `recaptcha-secret` | text | string |  | grupo: Recaptcha — *Recaptcha secret* |

### Premium plugins (`plugins`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-plugins` | info | no persiste |  |  — *If you purchased an extra license from plugin author, you can disable the bundled option for plugins you have purchased to get support from the plugin author and premium features.* |
| `plugin-rev` | select | string |  | grupo: Premium plugins; opciones: ``, `disable` — *Slider Revolution* |
| `plugin-visual` | select | string |  | grupo: Premium plugins; opciones: ``, `disable` — *WPBakery Page Builder* |
| `plugin-layer` | select | string |  | grupo: Premium plugins; opciones: ``, `disable` — *Slider de capas* |

## Colores (`colors`)

### General (`colors-general`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `skin` | select | string | `"custom"` | grupo: Aspecto; opciones: `custom`, `one`, `blue`, `brown`, `chocolate`, `gold`, `green`, `olive`, `orange`, `pink`, `red`, `sea`, … — *Theme skin* |
| `color-one` | color | string | `"#0095eb"` | grupo: Aspecto; condición UI: `skin` — *One Color* |
| `background-html` | color | string | `"#FCFCFC"` | grupo: Fondo — *Body background* |
| `background-body` | color | string | `"#FCFCFC"` | grupo: Fondo — *Content background* |
| `background-archives-post` | color | string | `""` | grupo: Archivos — *Post background* |
| `background-archives-portfolio` | color | string | `""` | grupo: Archivos — *Portfolio background* |
| `background-archives-product` | color | string | `""` | grupo: Archivos — *Product background* |

### Action Bar (`colors-action`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `background-action-bar` | color | string | `"#101015"` | grupo: Desktop & Tablet — *Fondo* |
| `color-action-bar` | color | string | `"#bbbbbb"` | grupo: Desktop & Tablet — *Text color* |
| `color-action-bar-a` | color | string | `"#006edf"` | grupo: Desktop & Tablet — *Link color* |
| `color-action-bar-a-hover` | color | string | `"#0089f7"` | grupo: Desktop & Tablet — *Link hover color* |
| `color-action-bar-social` | color | string | `"#bbbbbb"` | grupo: Desktop & Tablet — *Social Icon color* |
| `color-action-bar-social-hover` | color | string | `"#FFFFFF"` | grupo: Desktop & Tablet — *Social Icon hover color* |
| `mobile-background-action-bar` | color | string | `"#FFFFFF"` | grupo: Móvil — *Fondo* |
| `mobile-color-action-bar` | color | string | `"#222222"` | grupo: Móvil — *Text color* |
| `mobile-color-action-bar-a` | color | string | `"#006edf"` | grupo: Móvil — *Link color* |
| `mobile-color-action-bar-a-hover` | color | string | `"#0089f7"` | grupo: Móvil — *Link hover color* |
| `mobile-color-action-bar-social` | color | string | `"#bbbbbb"` | grupo: Móvil — *Social Icon color* |
| `mobile-color-action-bar-social-hover` | color | string | `"#777777"` | grupo: Móvil — *Social Icon hover color* |

### Encabezado (`colors-header`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `background-header` | color | string | `"#13162f"` | grupo: Encabezado; dynamic — *Header background* |
| `background-top-left` | color | string | `"#ffffff"` | grupo: Top bar — *Top Bar Left background* |
| `background-top-middle` | color | string | `"#e3e3e3"` | grupo: Top bar — *Top Bar Middle background* |
| `background-top-right` | color | string | `"#f5f5f5"` | grupo: Top bar — *Top Bar Right background* |
| `color-top-right-a` | color | string | `"#333333"` | grupo: Top bar — *Top Bar Right icon color* |
| `border-top-bar` | color | string | `""` | grupo: Top bar — *Top Bar border bottom* |
| `background-subheader` | color | string | `"#f7f7f7"` | grupo: Subtítulo — *Fondo* |
| `color-subheader` | color | string | `"#161922"` | grupo: Subtítulo — *Title color* |

### Menu (`colors-menu`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `color-menu-a` | color | string | `"#2a2b39"` | grupo: Menu — *Link color* |
| `color-menu-a-active` | color | string | `"#0089F7"` | grupo: Menu — *Active Link color* |
| `background-menu-a-active` | color | string | `"#F2F2F2"` | grupo: Menu — *Active Link background* |
| `background-submenu` | color | string | `"#F2F2F2"` | grupo: Submenu — *Fondo* |
| `color-submenu-a` | color | string | `"#5f5f5f"` | grupo: Submenu — *Link color* |
| `color-submenu-a-hover` | color | string | `"#2e2e2e"` | grupo: Submenu — *Hover Link color* |
| `color-menu-responsive-icon` | color | string | `"#0089F7"` | grupo: Menu icon — *Color del icono* |
| `background-menu-responsive-icon` | color | string | `""` | grupo: Menu icon — *Fondo del icono* |
| `background-overlay-menu` | color | string | `"#0089F7"` | grupo: Estilo; dynamic — *Overlay MenuMenu background* |
| `background-overlay-menu-a` | color | string | `"#FFFFFF"` | grupo: Estilo — *Overlay MenuLink color* |
| `background-overlay-menu-a-active` | color | string | `"#B1DCFB"` | grupo: Estilo — *Overlay MenuActive Link color* |
| `border-menu-plain` | color | string | `"#F2F2F2"` | grupo: Estilo — *PlainBorder color* |
| `background-side-menu` | color | string | `"#191919"` | grupo: Side slide; dynamic — *Fondo* |
| `color-side-menu-a` | color | string | `"#A6A6A6"` | grupo: Side slide — *Link color* |
| `color-side-menu-a-hover` | color | string | `"#FFFFFF"` | grupo: Side slide — *Active Link color* |

### Contenido (`content`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `color-theme` | color | string | `"#0089F7"` | grupo: Contenido — *Theme color* |
| `color-text` | color | string | `"#626262"` | grupo: Contenido — *Text color* |
| `color-lead` | color | string | `"#2e2e2e"` | grupo: Contenido — *Lead paragraph* |
| `color-selection` | color | string | `"#0089F7"` | grupo: Contenido — *Selection color* |
| `color-a` | color | string | `"#006edf"` | grupo: Enlace — *Link color* |
| `color-a-hover` | color | string | `"#0089f7"` | grupo: Enlace — *Link hover color* |
| `color-fancy-link` | color | string | `"#656B6F"` | grupo: Enlace — *Fancy Link color* |
| `background-fancy-link` | color | string | `"#006edf"` | grupo: Enlace — *Fancy Link background* |
| `color-fancy-link-hover` | color | string | `"#006edf"` | grupo: Enlace — *Fancy Link hover color* |
| `background-fancy-link-hover` | color | string | `"#0089f7"` | grupo: Enlace — *Fancy Link hover background* |
| `background-highlight` | color | string | `"#0089F7"` | grupo: Inline shortcodes — *Dropcap & Highlight background* |
| `color-hr` | color | string | `"#0089F7"` | grupo: Inline shortcodes — *Hr color* |
| `color-list` | color | string | `"#737E86"` | grupo: Inline shortcodes — *List color* |
| `color-note` | color | string | `"#a8a8a8"` | grupo: Inline shortcodes — *Note color* |
| `background-highlight-section` | color | string | `"#0089F7"` | grupo: Section — *Highlight Section background* |

### Alerts (`colors-alerts`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `background-alert-warning` | color | string | `"#fef8ea"` | grupo: Warning — *Fondo* |
| `color-alert-warning` | color | string | `"#8a5b20"` | grupo: Warning — *Text color* |
| `background-alert-error` | color | string | `"#fae9e8"` | grupo: Error — *Fondo* |
| `color-alert-error` | color | string | `"#962317"` | grupo: Error — *Text color* |
| `background-alert-info` | color | string | `"#efefef"` | grupo: Info — *Fondo* |
| `color-alert-info` | color | string | `"#57575b"` | grupo: Info — *Text color* |
| `background-alert-success` | color | string | `"#eaf8ef"` | grupo: Success — *Fondo* |
| `color-alert-success` | color | string | `"#3a8b5b"` | grupo: Success — *Text color* |
| `alert-border-radius` | text | string | `""` | grupo: Avanzado — *Radio del borde* |

### Elementos (`colors-shortcodes`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `color-blockquote` | color | string | `"#444444"` | grupo: Elementos — *Blockquote color* |
| `background-getintouch` | color | string | `"#0089F7"` | grupo: Elementos — *Contact Box background* |
| `color-contentlink` | color | string | `"#0089F7"` | grupo: Elementos — *Content Link Icon color* |
| `color-counter` | color | string | `"#0089F7"` | grupo: Elementos — *Counter Icon color* |
| `color-iconbar` | color | string | `"#0089F7"` | grupo: Elementos — *Icon Bar Hover Icon color* |
| `color-iconbox` | color | string | `"#0089F7"` | grupo: Elementos — *Icon Box Icon color* |
| `color-list-icon` | color | string | `"#0089F7"` | grupo: Elementos — *List & Feature List Icon color* |
| `color-pricing-price` | color | string | `"#0089F7"` | grupo: Elementos — *Pricing Box Price color* |
| `background-pricing-featured` | color | string | `"#0089F7"` | grupo: Elementos — *Pricing Box Featured background* |
| `background-progressbar` | color | string | `"#0089F7"` | grupo: Elementos — *Progress Bar background* |
| `color-quickfact-number` | color | string | `"#0089F7"` | grupo: Elementos — *Quick Fact Number color* |
| `background-slidingbox-title` | color | string | `"#0089F7"` | grupo: Elementos — *Sliding Box Title background* |
| `color-table-th` | color | string | `"#444444"` | grupo: Elementos — *Table TH color* |
| `color-tab` | color | string | `"#444444"` | grupo: Elementos — *Toggle Accordion Tabs Title color* |
| `color-tab-title` | color | string | `"#0089F7"` | grupo: Elementos — *Toggle Accordion Tabs Active color* |
| `background-trailer-subtitle` | color | string | `"#0089F7"` | grupo: Elementos — *Trailer Box Subtitle background* |

### Forms (`colors-forms`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `color-form` | color | string | `"#626262"` | grupo: Input, select & textarea — *Text color* |
| `background-form` | color | string | `"#FFFFFF"` | grupo: Input, select & textarea — *Fondo* |
| `border-form` | color | string | `"#EBEBEB"` | grupo: Input, select & textarea — *Color del borde* |
| `color-form-placeholder` | color | string | `"#929292"` | grupo: Input, select & textarea — *Placeholder color* |
| `color-form-focus` | color | string | `"#0089F7"` | grupo: Focus — *Text color* |
| `background-form-focus` | color | string | `"#e9f5fc"` | grupo: Focus — *Fondo* |
| `border-form-focus` | color | string | `"#d5e5ee"` | grupo: Focus — *Color del borde* |
| `color-form-placeholder-focus` | color | string | `"#929292"` | grupo: Focus — *Placeholder color* |
| `form-border-width` | text | string |  | grupo: Avanzado — *Ancho del borde* |
| `form-border-radius` | text | string |  | grupo: Avanzado — *Radio del borde* |
| `form-transparent` | sliderbar | string | `"100"` | grupo: Avanzado — *Background transparency (alpha)* |

### Headings (`headings`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `color-h1` | color | string | `"#161922"` | grupo: Título — *Heading H1 color* |
| `color-h2` | color | string | `"#161922"` | grupo: Título — *Heading H2 color* |
| `color-h3` | color | string | `"#161922"` | grupo: Título — *Heading H3 color* |
| `color-h4` | color | string | `"#161922"` | grupo: Título — *Heading H4 color* |
| `color-h5` | color | string | `"#5f6271"` | grupo: Título — *Heading H5 color* |
| `color-h6` | color | string | `"#161922"` | grupo: Título — *Heading H6 color* |

### Tienda (`colors-shop`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `background-shop-single-image-icon` | color_multi | array {subclave: color} | `{"normal":"#ffffff","hover":"#ffffff"}` | grupo: Producto individual — *Image icon background* |
| `color-shop-single-image-icon` | color_multi | array {subclave: color} | `{"normal":"#161922","hover":"#0089f7"}` | grupo: Producto individual — *Image icon color* |
| `color-shop-cat-desc-gradient` | color_multi | array {subclave: color} | `{"from":"rgba(255,255,255,0)","to":"rgba(255,255,255,1)"}` | grupo: Category desc — *Category read more gradient* |
| `color-wishlist` | color_multi | array {subclave: color} | `{"normal":"rgba(0,0,0,.15)","hover":"rgba(0,0,0,.3)"}` | grupo: Wishlist — *Add to wishlist icon color* |

### Pie de página (`colors-footer`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `color-footer-theme` | color | string | `"#0089F7"` | grupo: Pie de página — *Theme color* |
| `background-footer` | color | string | `"#101015"` | grupo: Pie de página; dynamic — *Fondo* |
| `color-footer` | color | string | `"#bababa"` | grupo: Pie de página; dynamic — *Text color* |
| `color-footer-heading` | color | string | `"#ffffff"` | grupo: Pie de página — *Heading color* |
| `color-footer-note` | color | string | `"#a8a8a8"` | grupo: Pie de página — *Note color* |
| `border-copyright` | color | string | `"rgba(255,255,255,0.1)"` | grupo: Pie de página — *Copyright border* |
| `color-footer-a` | color | string | `"#d1d1d1"` | grupo: Enlace — *Link color* |
| `color-footer-a-hover` | color | string | `"#0089f7"` | grupo: Enlace — *Link hover color* |
| `color-footer-social` | color | string | `"#65666C"` | grupo: Social — *Social Icon color* |
| `color-footer-social-hover` | color | string | `"#FFFFFF"` | grupo: Social — *Social Icon hover color* |
| `color-footer-backtotop` | color | string | `"#65666C"` | grupo: Back to top — *Color del icono* |
| `background-footer-backtotop` | color | string | `""` | grupo: Back to top — *Fondo del icono* |

### Parte superior deslizante (`colors-sliding-top`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `color-sliding-top-theme` | color | string | `"#0089F7"` | grupo: Sliding top — *Sliding Top Theme color* |
| `background-sliding-top` | color | string | `"#545454"` | grupo: Sliding top — *Sliding Top background* |
| `color-sliding-top` | color | string | `"#cccccc"` | grupo: Sliding top — *Sliding Top Text color* |
| `color-sliding-top-a` | color | string | `"#006edf"` | grupo: Sliding top — *Sliding Top Link color* |
| `color-sliding-top-a-hover` | color | string | `"#0089f7"` | grupo: Sliding top — *Sliding Top Hover Link color* |
| `color-sliding-top-heading` | color | string | `"#ffffff"` | grupo: Sliding top — *Sliding Top Heading color* |
| `color-sliding-top-note` | color | string | `"#a8a8a8"` | grupo: Sliding top — *Sliding Top Note color* |

### Palette (`palette`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `color-palette-1` | color | string | `"#f44336"` | grupo: Color picker palette — *Color 1* |
| `color-palette-2` | color | string | `"#e91e63"` | grupo: Color picker palette — *Color 2* |
| `color-palette-3` | color | string | `"#9c27b0"` | grupo: Color picker palette — *Color 3* |
| `color-palette-4` | color | string | `"#673ab7"` | grupo: Color picker palette — *Color 4* |
| `color-palette-5` | color | string | `"#3f51b5"` | grupo: Color picker palette — *Color 5* |
| `color-palette-6` | color | string | `"#2196f3"` | grupo: Color picker palette — *Color 6* |
| `color-palette-7` | color | string | `"#03a9f4"` | grupo: Color picker palette — *Color 7* |
| `color-palette-8` | color | string | `"#00bcd4"` | grupo: Color picker palette — *Color 8* |
| `color-palette-9` | color | string | `"#009688"` | grupo: Color picker palette — *Color 9* |
| `color-palette-10` | color | string | `"#4caf50"` | grupo: Color picker palette — *Color 10* |
| `color-palette-11` | color | string | `"#8bc34a"` | grupo: Color picker palette — *Color 11* |
| `color-palette-12` | color | string | `"#cddc39"` | grupo: Color picker palette — *Color 12* |
| `color-palette-13` | color | string | `"#ffeb3b"` | grupo: Color picker palette — *Color 13* |
| `color-palette-14` | color | string | `"#ffc107"` | grupo: Color picker palette — *Color 14* |

## Fonts (`font`)

### Family (`font-family`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-font-family-local` | info | no persiste |  | condición UI: `google-font-mode` — *You chose to Cache fonts local in Performance tab.Please Regenerate fonts every time you change anything in this tab.* |
| `font-content` | font_select | string | `"Poppins"` | grupo: Familia de fuentes — *Contenido* |
| `font-lead` | font_select | string | `"Poppins"` | grupo: Familia de fuentes — *Lead paragraph* |
| `font-menu` | font_select | string | `"Poppins"` | grupo: Familia de fuentes; dynamic — *Main Menu* |
| `font-title` | font_select | string | `"Poppins"` | grupo: Familia de fuentes — *Page Title* |
| `font-headings` | font_select | string | `"Poppins"` | grupo: Familia de fuentes — *Big Headings* |
| `font-headings-small` | font_select | string | `"Poppins"` | grupo: Familia de fuentes — *Small Headings* |
| `font-blockquote` | font_select | string | `"Poppins"` | grupo: Familia de fuentes — *Cita* |
| `font-decorative` | font_select | string | `"Poppins"` | grupo: Familia de fuentes — *Decorative* |
| `font-weight` | checkbox | array {clave: clave} solo marcados | `{"300":"300","400":"400","400italic":"400italic","500":"5...` | grupo: Google fonts; opciones: `100`, `100italic`, `200`, `200italic`, `300`, `300italic`, `400`, `400italic`, `500`, `500italic`, `600`, `600italic`, … — *Weight & Style* |
| `font-subset` | checkbox | array {clave: clave} solo marcados | `{"latin":"latin","latin-ext":"latin-ext"}` | grupo: Google fonts; opciones: `cyrylic`, `cyrylic-ext`, `greek`, `greek-ext`, `latin`, `latin-ext`, `vietnamese`, `vietnamese-ext` — *Subset* |

### Size & Style (`font-size`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-force-regenerate` | info | no persiste |  |  — *Some Google Fonts support multiple weights & styles. Include them in Fonts > Family > Google Fonts Weight & Style* |
| `font-size-responsive` | switch | string | `"1"` | grupo: General; opciones: `0`, `1` — *Auto font size* |
| `font-size-content` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":15,"line_height":28,"weight_style":"400","letter_...` | grupo: Contenido; responsive — *Contenido* |
| `font-size-content-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Contenido; responsive — *Contenido* |
| `font-size-content-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Contenido; responsive — *Contenido* |
| `font-size-big` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":17,"line_height":30,"weight_style":"400","letter_...` | grupo: Contenido; responsive — *Lead paragraph* |
| `font-size-big-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Contenido; responsive — *p.big* |
| `font-size-big-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Contenido; responsive — *p.big* |
| `font-size-menu` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":15,"line_height":0,"weight_style":"500","letter_s...` | grupo: Contenido; dynamic; responsive — *Main menu* |
| `font-size-menu-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Contenido; responsive — *Main menu* |
| `font-size-menu-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Contenido; responsive — *Main menu* |
| `font-size-title` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":50,"line_height":60,"weight_style":"400","letter_...` | grupo: Page title; responsive — *Page title* |
| `font-size-title-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Page title; responsive — *Page title* |
| `font-size-title-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Page title; responsive — *Page title* |
| `font-size-single-intro` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":70,"line_height":70,"weight_style":"400","letter_...` | grupo: Page title; responsive — *Encabezado de introducción* |
| `font-size-single-intro-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Page title; responsive — *Encabezado de introducción* |
| `font-size-single-intro-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Page title; responsive — *Encabezado de introducción* |
| `font-size-h1` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":50,"line_height":60,"weight_style":"500","letter_...` | grupo: Título; responsive — *H1* |
| `font-size-h1-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H1* |
| `font-size-h1-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H1* |
| `font-size-h2` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":40,"line_height":50,"weight_style":"500","letter_...` | grupo: Título; responsive — *H2* |
| `font-size-h2-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H2* |
| `font-size-h2-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H2* |
| `font-size-h3` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":30,"line_height":40,"weight_style":"400","letter_...` | grupo: Título; responsive — *H3* |
| `font-size-h3-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H3* |
| `font-size-h3-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H3* |
| `font-size-h4` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":20,"line_height":30,"weight_style":"600","letter_...` | grupo: Título; responsive — *H4* |
| `font-size-h4-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H4* |
| `font-size-h4-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H4* |
| `font-size-h5` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":18,"line_height":30,"weight_style":"400","letter_...` | grupo: Título; responsive — *H5* |
| `font-size-h5-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H5* |
| `font-size-h5-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H5* |
| `font-size-h6` | typography | array {size,line_height,weight_style,letter_spacing} | `{"size":15,"line_height":26,"weight_style":"700","letter_...` | grupo: Título; responsive — *H6* |
| `font-size-h6-tablet` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H6* |
| `font-size-h6-mobile` | typography | array {size,line_height,weight_style,letter_spacing} |  | grupo: Título; responsive — *H6* |

### Personalizado (`font-custom`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-fonts` | info | no persiste |  |  — *Use below fields if you want to use webfonts directly from your server.* |
| `font-custom` | text | string |  | grupo: Font 1 — *Nombre* |
| `font-custom-woff` | upload | string (URL o URL#attachment_id) |  | grupo: Font 1; data=font — *.woff* |
| `font-custom-ttf` | upload | string (URL o URL#attachment_id) |  | grupo: Font 1; data=font — *.ttf* |
| `font-custom2` | text | string |  | grupo: Font 2 — *Nombre* |
| `font-custom2-woff` | upload | string (URL o URL#attachment_id) |  | grupo: Font 2; data=font — *.woff* |
| `font-custom2-ttf` | upload | string (URL o URL#attachment_id) |  | grupo: Font 2; data=font — *.ttf* |
| `font-custom-fields` | text | string |  | grupo: New font — *New fields amount* |

## Translate (`translate`)

### General (`translate-general`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-translate` | info | no persiste |  |  — *The fields below, must be filled out if you are using WPML String Translation.If you already use English language, you can use this tab to change some texts.* |
| `translate` | switch | string | `"1"` | grupo: General; opciones: `0`, `1` — *Translate* |
| `translate-home` | text | string | `"Home"` | grupo: General — *Home* |
| `translate-prev` | text | string | `"Prev page"` | grupo: General — *Prev page* |
| `translate-next` | text | string | `"Next page"` | grupo: General — *Next page* |
| `translate-load-more` | text | string | `"Load more"` | grupo: General — *Cargar más* |
| `translate-see-more` | text | string | `"See more"` | grupo: General — *See more* |
| `translate-see-less` | text | string | `"See less"` | grupo: General — *See less* |
| `translate-wpml-no` | text | string | `"No translations available for this page"` | grupo: General — *No translations available for this page* |
| `translate-share` | text | string | `"Share"` | grupo: General — *Share* |
| `translate-success-message` | text | string | `"Link copied to the clipboard."` | grupo: General — *Success message* |
| `translate-error-message` | text | string | `"Something went wrong. Please try again later!"` | grupo: General — *Error message* |
| `translate-recaptcha-error-1` | text | string | `"Could not verify reCAPTCHA."` | grupo: Recaptcha — *Could not verify reCAPTCHA* |
| `translate-recaptcha-error-2` | text | string | `"Please complete the reCAPTCHA."` | grupo: Recaptcha — *Please complete the reCAPTCHA* |
| `translate-before` | text | string | `"Before"` | grupo: Elementos — *Antes* |
| `translate-after` | text | string | `"After"` | grupo: Elementos — *Después* |
| `translate-days` | text | string | `"days"` | grupo: Elementos — *Días* |
| `translate-hours` | text | string | `"hours"` | grupo: Elementos — *Horas* |
| `translate-minutes` | text | string | `"minutes"` | grupo: Elementos — *Minutes* |
| `translate-seconds` | text | string | `"seconds"` | grupo: Elementos — *Seconds* |

### Blog & Portfolio (`translate-blog`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `translate-filter` | text | string | `"Filter by"` | grupo: Blog & Portfolio — *Filtrar por* |
| `translate-authors` | text | string | `"Authors"` | grupo: Blog & Portfolio — *Authors* |
| `translate-all` | text | string | `"Show all"` | grupo: Blog & Portfolio — *Show all* |
| `translate-item-all` | text | string | `"All"` | grupo: Blog & Portfolio — *Todo* |
| `translate-published` | text | string | `"Published by"` | grupo: Blog & Portfolio — *Published by* |
| `translate-at` | text | string | `"on"` | grupo: Blog & Portfolio — *on* |
| `translate-categories` | text | string | `"Categories"` | grupo: Blog & Portfolio — *Categorías* |
| `translate-tags` | text | string | `"Tags"` | grupo: Blog & Portfolio — *Tags* |
| `translate-readmore` | text | string | `"Read more"` | grupo: Blog & Portfolio — *Leer más* |
| `translate-like` | text | string | `"Do you like it?"` | grupo: Blog & Portfolio — *Do you like it?* |
| `translate-related` | text | string | `"Related posts"` | grupo: Blog & Portfolio — *Related posts* |
| `translate-client` | text | string | `"Client"` | grupo: Blog & Portfolio — *Cliente* |
| `translate-date` | text | string | `"Date"` | grupo: Blog & Portfolio — *Fecha* |
| `translate-website` | text | string | `"Website"` | grupo: Blog & Portfolio — *Sitio web* |
| `translate-view` | text | string | `"View website"` | grupo: Blog & Portfolio — *View website* |
| `translate-task` | text | string | `"Task"` | grupo: Blog & Portfolio — *Tarea* |
| `translate-commented-on` | text | string | `"commented on"` | grupo: Blog & Portfolio — *Commented on* |

### Tienda (`translate-shop`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `translate-shop-filters` | text | string | `"Filters"` | grupo: General — *Filtros* |
| `translate-empty-wishlist` | text | string | `"Your wishlist is empty"` | grupo: General — *Your wishlist is empty* |
| `translate-add-to-cart` | text | string | `"Add to cart"` | grupo: Image frame — *Add to cart* |
| `translate-view-product` | text | string | `"View product"` | grupo: Image frame — *View product* |
| `translate-add-to-wishlist` | text | string | `"Add to wishlist"` | grupo: Image frame — *Add to wishlist* |
| `translate-if-preview` | text | string | `"Preview"` | grupo: Image frame — *Vista previa* |
| `translate-side-cart-shipping-free` | text | string | `"Free!"` | grupo: Side cart — *Free shipping* |
| `translate-my-account-heading` | text | string | `"Hello %s"` | grupo: My account — *My account heading* |
| `translate-free-delivery-progress-bar` | text | string | `"You are %s short for free delivery."` | grupo: Free delivery progress bar — *Título* |
| `translate-free-delivery-progress-bar-achieved` | text | string | `"Your order qualifies for free shipping!"` | grupo: Free delivery progress bar — *“Eligible for free delivery” Heading* |
| `translate-fake-sale-notification-someone` | text | string | `"Someone"` | grupo: Fake sale notification — *Someone* |
| `translate-fake-sale-notification-single` | text | string | `"bought the product"` | grupo: Fake sale notification — *Bought the product* |
| `translate-fake-sale-notification-multi` | text | string | `"has been bought %s times recently."` | grupo: Fake sale notification — *Has been bought %s times recently.* |

### Buscar (`translate-search`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `translate-search-placeholder` | text | string | `"Enter your search"` | grupo: Form — *Input placeholder* |
| `translate-search-results` | text | string | `"results found for:"` | grupo: Página — *Page title with number of results* |
| `translate-search-title` | text | string | `"Ooops..."` | grupo: Página — *Not found \| Title* |
| `translate-search-subtitle` | text | string | `"No results found for:"` | grupo: Página — *Not found \| Text* |
| `translate-livesearch-categories` | text | string | `"Categories"` | grupo: Live search — *Categorías* |
| `translate-livesearch-pages` | text | string | `"Pages"` | grupo: Live search — *Pages* |
| `translate-livesearch-portfolio` | text | string | `"Portfolio"` | grupo: Live search — *Portafolio* |
| `translate-livesearch-posts` | text | string | `"Posts"` | grupo: Live search — *Posts* |
| `translate-livesearch-products` | text | string | `"Products"` | grupo: Live search — *Products* |
| `translate-livesearch-noresults` | text | string | `"No results"` | grupo: Live search — *Not found text* |
| `translate-livesearch-button` | text | string | `"See all results"` | grupo: Live search — *See all button* |

### Error 404 (`translate-404`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `translate-404-title` | text | string | `"Ooops... Error 404"` | grupo: Error 404 — *Título* |
| `translate-404-subtitle` | text | string | `"We are sorry, but the page you are looking for does not ...` | grupo: Error 404 — *Subtítulo* |
| `translate-404-text` | text | string | `"Please check entered address and try again or "` | grupo: Error 404 — *Text* |
| `translate-404-btn` | text | string | `"go to homepage"` | grupo: Error 404 — *Botón* |

### WPML Installer (`translate-wpml`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-wpml` | info | no persiste |  |  — *WPML is an optional premium plugin and it is not included into the theme* |
| `translate-wpml-installer` | custom | no persiste |  | grupo: WPML — *WPML Installer* |

## GDPR 2.0 (`gdpr2`)

### General (`gdpr2-general`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `gdpr2` | switch | string | `""` | grupo: General; opciones: ``, `1` — *Consent Mode V2* |
| `gdpr2-button` | switch | string | `""` | grupo: General; opciones: ``, `1`; condición UI: `gdpr2` — *Re-open modal button* |
| `gdpr2-settings-animation` | switch | string | `"fade"` | grupo: Opciones; opciones: ``, `fade`, `slide`; condición UI: `gdpr2` — *Close animation* |
| `gdpr2-settings-cookie_expire` | text | string | `"365"` | grupo: Opciones; condición UI: `gdpr2` — *Cookie expiration* |
| `gdpr2-scroll-disable` | switch | string | `"0"` | grupo: Opciones; opciones: `1`, `0` — *Desplazamiento del navegador* |
| `gdpr2-content-title` | text | string | `"Consent"` | grupo: Opciones; condición UI: `gdpr2` — *Título* |
| `gdpr2-consent-content` | textarea | string | `"<p><strong>This website uses cookies</strong></p>\n<p>We...` | grupo: Opciones; condición UI: `gdpr2` — *Contenido* |
| `gdpr2-details-title` | text | string | `"Details"` | grupo: Opciones; condición UI: `gdpr2` — *Título* |
| `gdpr2-necessary-title` | text | string | `"Necessary"` | grupo: Opciones; condición UI: `gdpr2` — *Necessary title* |
| `gdpr2-necessary-consent` | textarea | string | `"<p>Necessary cookies help make a website usable by enabl...` | grupo: Opciones; condición UI: `gdpr2` — *Necessary content* |
| `gdpr2-analytics-title` | text | string | `"Analytics & Performance"` | grupo: Opciones; condición UI: `gdpr2` — *Analytics title* |
| `gdpr2-analytics-consent` | textarea | string | `"<p>Statistic cookies help website owners to understand h...` | grupo: Opciones; condición UI: `gdpr2` — *Analytics content* |
| `gdpr2-marketing-title` | text | string | `"Marketing"` | grupo: Opciones; condición UI: `gdpr2` — *Marketing title* |
| `gdpr2-marketing-consent` | textarea | string | `"<p>Marketing cookies are used to track visitors across w...` | grupo: Opciones; condición UI: `gdpr2` — *Marketing content* |
| `gdpr2-about-title` | text | string | `"About <span class=\"hide-mobile\">Cookies</span>"` | grupo: Opciones; condición UI: `gdpr2` — *Título* |
| `gdpr2-about-content` | textarea | string | `"<p>Cookies are small text files that can be used by webs...` | grupo: Opciones; condición UI: `gdpr2` — *Contenido* |
| `gdpr2-button-deny` | text | string | `"Deny"` | grupo: Opciones; condición UI: `gdpr2` — *Deny* |
| `gdpr2-button-customize` | text | string | `"Customize"` | grupo: Opciones; condición UI: `gdpr2` — *Customize* |
| `gdpr2-button-allow-selected` | text | string | `"Allow selected"` | grupo: Opciones; condición UI: `gdpr2` — *Allow selected* |
| `gdpr2-button-allow-all` | text | string | `"Allow all"` | grupo: Opciones; condición UI: `gdpr2` — *Allow all* |

### Diseño (`gdpr2-design`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-shop2` | info | no persiste |  | condición UI: `gdpr2` — *Please enable Consent Mode V2 to get access to this tab.* |
| `gdpr2-color-container-font` | color | string | `"#626262"` | grupo: Container; condición UI: `gdpr2` — *Text color* |
| `gdpr2-color-container-font-strong` | color | string | `"#07070A"` | grupo: Container; condición UI: `gdpr2` — *Strong text color* |
| `gdpr2-color-container` | color | string | `"#ffffff"` | grupo: Container; condición UI: `gdpr2` — *Fondo* |
| `gdpr2-color-overlay` | color | string | `"rgba(25, 37, 48, 0.6)"` | grupo: Container; condición UI: `gdpr2` — *Body overlay* |
| `gdpr2-color-details-box-bg` | color | string | `"#FBFBFB"` | grupo: Details; condición UI: `gdpr2` — *Box background* |
| `gdpr2-color-details-switch` | color | string | `"#00032A"` | grupo: Details; condición UI: `gdpr2` — *Switch background* |
| `gdpr2-color-details-switch-active` | color | string | `"#5ACB65"` | grupo: Details; condición UI: `gdpr2` — *Active switch background* |
| `gdpr2-tabs-text-color` | color | string | `"#07070A"` | grupo: Pestañas; condición UI: `gdpr2` — *Text color* |
| `gdpr2-tabs-text-color-active` | color | string | `"#0089F7"` | grupo: Pestañas; condición UI: `gdpr2` — *Active text color* |
| `gdpr2-tabs-border` | color | string | `"rgba(8,8,14,.1)"` | grupo: Pestañas; condición UI: `gdpr2` — *Color del borde* |
| `gdpr2-color-buttons-bg` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Buttons; condición UI: `gdpr2` — *Fondo* |
| `gdpr2-color-buttons` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Buttons; condición UI: `gdpr2` — *Text color* |
| `gdpr2-color-buttons-border` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Buttons; condición UI: `gdpr2` — *Color del borde* |
| `gdpr2-color-buttons-bg-active` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Buttons; condición UI: `gdpr2` — *Highlighted background* |
| `gdpr2-color-buttons-active` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Buttons; condición UI: `gdpr2` — *Highlighted text color* |
| `gdpr2-color-buttons-border-active` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Buttons; condición UI: `gdpr2` — *Highlighted border color* |
| `gdpr2-color-buttons-box-bg` | color | string | `"#FBFBFB"` | grupo: Buttons; condición UI: `gdpr2` — *Box background* |
| `gdpr2-reopen-icon` | icon | string | `""` | grupo: Re-open modal button — *Icono* |
| `gdpr2-reopen-background` | color | string | `"#ffffff"` | grupo: Re-open modal button — *Fondo* |
| `gdpr2-reopen-color` | color | string | `"#222222"` | grupo: Re-open modal button — *Color del icono* |
| `gdpr2-reopen-boxshadow` | boxshadow | string | `{"x":"0","y":"15","blur":"30","spread":"0","color":"rgba(...` | grupo: Re-open modal button — *Sombra de la caja* |

## GDPR & Cookies (`gdpr`)

### General (`gdpr-general`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `gdpr` | switch | string | `""` | grupo: General; opciones: ``, `1` — *Privacy bar* |
| `gdpr-settings-position` | radio_img | string | `"left"` | grupo: Opciones; opciones: `top`, `bottom`, `left`, `right`; condición UI: `gdpr` — *Diseño* |
| `gdpr-settings-animation` | switch | string | `"slide"` | grupo: Opciones; opciones: ``, `fade`, `slide`; condición UI: `gdpr` — *Animation* |
| `gdpr-settings-cookie_expire` | text | string | `"365"` | grupo: Opciones; condición UI: `gdpr` — *Cookie expiration* |
| `gdpr-content` | textarea | string | `"This website uses cookies to improve your experience. By...` | grupo: Opciones; condición UI: `gdpr` — *Message* |
| `gdpr-content-image` | upload | string (URL o URL#attachment_id) | `"#"` | grupo: Opciones; condición UI: `gdpr` — *Imagen* |
| `gdpr-content-button_text` | text | string | `"Accept all"` | grupo: Opciones; condición UI: `gdpr` — *Texto del botón* |
| `gdpr-content-more_info_text` | text | string | `"Read more"` | grupo: Opciones; condición UI: `gdpr` — *Text* |
| `gdpr-content-more_info_link` | text | string | `"#"` | grupo: Opciones; condición UI: `gdpr` — *Enlace* |
| `gdpr-content-more_info_page` | select_ajax | string |  | grupo: Opciones; dynamic; condición UI: `gdpr` — *Página* |
| `gdpr-settings-link_target` | switch | string | `"_blank"` | grupo: Opciones; opciones: `_self`, `_blank`; condición UI: `gdpr` — *Destino del enlace* |

### Diseño (`gdpr-design`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `info-shop2` | info | no persiste |  | condición UI: `gdpr` — *Please show Privacy bar to get access to this tab.* |
| `gdpr-container-background` | color | string | `"#eef2f5"` | grupo: Container; condición UI: `gdpr` — *Fondo* |
| `gdpr-container-font_color` | color | string | `"#626262"` | grupo: Container; condición UI: `gdpr` — *Text color* |
| `gdpr-container-border-radius` | text | string | `"5"` | grupo: Container; condición UI: `gdpr` — *Radio del borde* |
| `gdpr-container-box_shadow` | boxshadow | string | `{"x":"0","y":"15","blur":"30","spread":"0","color":"rgba(...` | grupo: Container; condición UI: `gdpr` — *Sombra de la caja* |
| `gdpr-button-background` | color_multi | array {subclave: color} | `{"normal":"#006edf","hover":"#0089f7"}` | grupo: Botón; condición UI: `gdpr` — *Fondo* |
| `gdpr-button-font_color` | color_multi | array {subclave: color} | `{"normal":"#ffffff","hover":"#ffffff"}` | grupo: Botón; condición UI: `gdpr` — *Text color* |
| `gdpr-button-border_color` | color_multi | array {subclave: color} | `{"normal":"","hover":""}` | grupo: Botón; condición UI: `gdpr` — *Color del borde* |
| `gdpr-more-info-font_color` | color_multi | array {subclave: color} | `{"normal":"#161922","hover":"#0089f7"}` | grupo: More info; condición UI: `gdpr` — *Text color* |

## Performance (`performance`)

### General (`performance-general`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `performance-enable` | custom | no persiste |  | grupo: One click — *Performance* |
| `google-font-mode` | switch | string | `""` | grupo: Fuentes de Google; opciones: ``, `local`, `disabled` — *Fuentes de Google* |
| `google-font-mode-regenerate` | ajax | no persiste (botón) |  | grupo: Fuentes de Google; dynamic; condición UI: `google-font-mode` — *Cache fonts* |
| `lazy-load` | switch | string | `""` | grupo: Imágenes; opciones: ``, `lazy` — *Lazy load* |
| `srcset-limit` | switch | string | `""` | grupo: Imágenes; opciones: ``, `lazy` — *Limit srcset* |
| `images-optimization` | switch | string | `""` | grupo: Imágenes; opciones: ``, `1` — *Images optimization* |
| `images-optimization-remove` | switch | string | `""` | grupo: Imágenes; opciones: ``, `1`; condición UI: `images-optimization` — *Images optimization - Original image* |
| `performance-image-size-disable` | checkbox | array {clave: clave} solo marcados |  | grupo: Imágenes; opciones: `blog-portfolio`, `blog-single`, `be_clients`, `portfolio-list`, `portfolio-mf`, `portfolio-mf-w`, `portfolio-mf-t`, `slider-content`, `be_thumbnail` — *Image sizes* |
| `performance-preload` | textarea | string |  | grupo: Imágenes — *Preload images* |
| `performance-assets-disable` | checkbox | array {clave: clave} solo marcados |  | grupo: Assets; opciones: `entrance-animations`, `font-awesome` — *Theme assets* |
| `performance-wp-disable` | checkbox | array {clave: clave} solo marcados |  | grupo: Assets; opciones: `wp-block-library`, `dashicons`, `emoji` — *WordPress assets* |
| `woocommerce-assets` | switch | string | `""` | grupo: Assets; opciones: ``, `shop` — *WooCommerce assets* |
| `woocommerce-assets-id` | text | string | `""` | grupo: Assets; condición UI: `woocommerce-assets` — *WooCommerce page IDs* |
| `jquery-location` | switch | string | `""` | grupo: Files location; opciones: ``, `footer` — *jQuery location* |
| `css-location` | switch | string | `""` | grupo: Files location; opciones: ``, `footer` — *CSS location* |
| `local-styles-location` | switch | string | `""` | grupo: Files location; opciones: ``, `inline` — *Builder local styles* |
| `minify-css` | switch | string | `"0"` | grupo: Minify; opciones: `0`, `1` — *CSS* |
| `minify-js` | switch | string | `"0"` | grupo: Minify; opciones: `0`, `1` — *JS* |
| `static-css` | switch | string | `"0"` | grupo: Cache; opciones: `0`, `1` — *Static CSS* |
| `hold-cache` | switch | string | `"0"` | grupo: Cache; opciones: `0`, `1` — *Cache assets* |
| `hold-cache-regenerate` | ajax | no persiste (botón) |  | grupo: Cache; dynamic; condición UI: `hold-cache` — *Refresh cache* |

## Accessibility (`accessibility`)

### Accessibility (`accessibility-general`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `skip-links` | info | no persiste |  |  — *To show Skip Navigation Links add custom menu to Accessibility Skip Links Menu location* |
| `keyboard-support` | switch | string | `"0"` | grupo: General; opciones: `0`, `1` — *Keyboard support* |
| `underline-links` | switch | string | `"0"` | grupo: General; opciones: `0`, `1` — *Underline links in text block* |
| `repetitive-links` | switch | string | `"0"` | grupo: General; opciones: `0`, `1` — *Repetitive link text* |
| `warning-open-links` | switch | string | `"0"` | grupo: General; opciones: `0`, `1` — *Warning on links* |

## Custom CSS & JS (`custom`)

### CSS (`css`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `custom-css` | textarea | string |  | grupo: CSS personalizado; role_restricted — *CSS personalizado* |

### JS (`js`)

| id | tipo | forma del valor | std | opciones / notas |
|---|---|---|---|---|
| `custom-js` | textarea | string |  | grupo: JS personalizado; role_restricted — *JS personalizado* |

