# 10 — Plantillas: Header/Footer Builder, popups y condiciones

Cómo Betheme guarda las plantillas del BeBuilder (CPT `template`: header, footer, popup, sidemenu, megamenu, single/archive,
cart/checkout…), cómo el modal "Display Conditions" se compila a options `mfn_<tipo>_*` y cómo asignar, listar y crear
plantillas por SSH. El **contenido** de una plantilla (JSON de secciones) está en muffinAI: `~/Documents/Mis proyectos/muffinAI/docs/bebuilder/`.

## 1. Qué hace el panel

Betheme → Templates (`edit.php?post_type=template`) lista las plantillas por pestañas (Header, Mega menu, Sidebar menu, Footer,
Popup, Blog, Portfolio, WooCommerce, Global, Search, Page y un bloque por cada CPT público: `list_tabs_wrapper()`,
`betheme/functions/post-types/class-mfn-post-type-template.php:2358-2470`). Cada plantilla se edita con el BeBuilder; el botón
"Display Conditions" abre `betheme/visual-builder/partials/modal-conditions.php` y al guardar el builder escribe las condiciones y
recompila las options que el front consulta (`betheme/visual-builder/visual-builder.php:357-383`).

## 2. Dónde vive

| Qué | Dónde | Cita |
|---|---|---|
| CPT `template` | `register_post_type('template')`: `public=false`, `publicly_queryable=true`, slug `template-item`, `supports title,author` | `class-mfn-post-type-template.php:2181-2212` |
| Gate del CPT | `post-type-disable[template]` (no se carga la clase) | `betheme/functions.php:122-124` |
| Gate del constructor | sin WPML (`class_exists('Sitepress')`), el CPT solo se registra si `bebuilder_access` es true **y** el usuario actual es editor/administrador | `class-mfn-post-type-template.php:21-34` |
| Tipo | postmeta `mfn_template_type` (select oculto, `std` = pestaña desde la que se creó) | `class-mfn-post-type-template.php:522-528` |
| Condiciones | postmeta `mfn_template_conditions` (JSON string) | `visual-builder.php:357-362` |
| Opciones de publicación | postmeta `mfn_publication_options` (JSON) | `visual-builder.php:364-369` |
| Contenido | `mfn-page-items` (array PHP, o base64(serialize) si `builder-storage=encode`), `mfn-page-object` (JSON string), `mfn-page-local-style` (JSON generado por `Mfn_Helper::preparePostUpdate()`), `mfn-page-fonts` (JSON, solo si usa fuentes) | `visual-builder.php:394-415`; `betheme/functions/admin/class-mfn-helper.php:419-423` |
| CSS local | fichero `uploads/betheme/css/post-<ID>.css` (`Mfn_Helper::generate_css()`) | `class-mfn-helper.php:568` |
| Metas propias del tipo | header: `header_position`, `header_sticky`, `header_mobile`… (`set_header_fields()` l.786); popup: `popup_position`, `popup_display`, `popup_hide`, `css_*` (l.957); sidemenu: `mfn_sidemenu_visibility` (l.1561), `sidemenu_position`, `css_width`… (l.1547); megamenu: `megamenu_width` (l.1996); footer: `footer_type` (l.2131) | `class-mfn-post-type-template.php` |

### 2.1 Valores de `mfn_template_type`

`set_fields()` (`class-mfn-post-type-template.php:475-508`): `default` (Page template), `section`, `wrap` (Global), `popup`, `header`,
`megamenu`, `sidemenu`, `footer`, `search`, `single-post`, `archive-post`, `single-portfolio`, `archive-portfolio`; con WooCommerce
`archive-product`, `single-product`, `cart`, `checkout`, `thanks`; y por cada CPT público no built-in `single-<cpt>` / `archive-<cpt>`
(l.503-512). Deprecated y reescritos al guardar: `shop-archive→archive-product`, `portfolio→archive-portfolio`, `blog→archive-post`
(`visual-builder.php:308-320`). La columna "Conditions" del listado los traduce a texto (`mfn_template_column()`, l.314-460) y para
cart/checkout/thanks/search muestra "Currently in use" si `mfn_<tipo>_template_used` apunta a ese ID (l.456-457).

### 2.2 Familias de compilación (`visual-builder.php:370-383`)

| Tipo | Método de `Mfn_Builder_Admin` (`betheme/functions/builder/class-mfn-builder-admin.php`) | Escribe |
|---|---|---|
| `header`, `footer` | `set_global_templates_conditions()` l.3919 (antes `reset_global_templates_conditions()` l.3883) | options `mfn_<tipo>[_<lang>]_*`, postmeta `mfn_<tipo>[_<lang>]_post`, termmeta `mfn_<tipo>[_<lang>]_term` |
| `popup` | `set_addons_templates_conditions()` l.3443 | options `mfn_popup_addons_archives[_<lang>]`, `mfn_popup_addons_singular[_<lang>]` |
| `cart`, `checkout`, `thanks`, `search` | option `mfn_<tipo>_template_used` (toggle con `tmpl_confirmation`, l.371-376) y, sin confirmación, `set_post_types_templates_conditions()` | `mfn_<tipo>_template_used`, `mfn_<tipo>_template` |
| resto (`single-*`, `archive-*`, `sidemenu`, `megamenu`, `section`, `wrap`, `default`) | `set_post_types_templates_conditions()` l.3746 | option `mfn_<tipo>_template[_<lang>]` (array) |

Los tres compiladores solo leen plantillas **publicadas** (SQL `post_status='publish'`, l.3781 y l.3972) y como máximo
`mfn_hints_limit` = 500 (l.3970).

## 3. Forma exacta de `mfn_template_conditions`

Array JSON de objetos; el compilador solo mira `rule`, `var` y la clave homónima de `var`. El modal envía todas las claves de sus selects.

**Header / footer / popup** (`modal-conditions.php:167-216`): `{rule, var, archives, singular, other}` con
`rule ∈ include|exclude`, `var ∈ everywhere|archives|singular|other`; `archives`/`singular` = `''` (todos) | `<post_type>` |
`<post_type>:<term_id>` (`page`, `post`, `product`, `portfolio`, `offer`; términos de `category`, `product_cat`, `portfolio-types`,
`offer-types`: `mfn_get_posttypes('tax')`, `betheme/functions/theme-functions.php:3602-3679`); `other = search-page`.

```json
[{"rule":"include","var":"everywhere","archives":"","singular":"","other":"search-page"}]        // todo el sitio (real del dev, ID 93)
[{"rule":"include","var":"singular","archives":"","singular":"page","other":"search-page"}]      // todas las páginas
[{"rule":"include","var":"archives","archives":"product:15","singular":"","other":"search-page"}] // archivo de la product_cat 15
[{"rule":"include","var":"singular","archives":"","singular":"post:7","other":"search-page"}]    // posts con la categoría 7
[{"rule":"exclude","var":"archives","archives":"post","singular":"","other":"search-page"}]      // excluir archivos del blog
[{"rule":"include","var":"other","archives":"","singular":"","other":"search-page"}]             // página de búsqueda
```

**single-\* / archive-\*** (`modal-conditions.php:74-95`): `{rule, var, <taxonomía>}` con `var ∈ all|<taxonomía pública del post type>|wishlist|products-search`
y `<taxonomía> = all|<term_id>`:

```json
[{"rule":"include","var":"all","portfolio-types":"all"}]      // real del dev (IDs 128 y 175)
[{"rule":"include","var":"category","category":"12"}]         // single-post: solo posts de la categoría 12
[{"rule":"exclude","var":"post_tag","post_tag":"33"}]
```

No existe condición "este post concreto": eso se hace con el override por post (§5).

## 4. Options y metas que genera cada compilador

### 4.1 `set_global_templates_conditions($type)` — header y footer (l.3919-4153)

Primero `reset_global_templates_conditions()` borra **todas** las claves del tipo (l.3886-3916: 21 options + `DELETE` de postmeta
`mfn_<tipo>_post(_excluded)` y termmeta `mfn_<tipo>_term(_excluded)`), también para cada idioma no principal (`_<lang>`, l.3929-3948).
Luego, por cada plantilla publicada del tipo, `$t_lang` = `_<código>` solo si su idioma no es el principal (WPML l.3982-3986,
Polylang l.3988) y se escribe (valor = ID de la plantilla; la última gana):

| Condición | Clave escrita | Línea |
|---|---|---|
| include everywhere | option `mfn_<tipo><lang>_entire_site` | 4011 |
| include archives `pt:term` | termmeta `mfn_<tipo><lang>_term` en ese término | 4039 |
| include archives `pt` | option `mfn_<tipo><lang>_<pt>_arch` | 4043 |
| include archives `''` | options `_post_arch`, `_product_arch`, `_portfolio_arch`, `_offer_arch` | 4046-4049 |
| exclude archives `pt:term` | termmeta `mfn_<tipo><lang>_term_excluded` = **term_id** (no el ID de plantilla; el front solo mira que no esté vacío) | 4060 |
| exclude archives `pt` / `''` | options `_<pt>_arch_excluded` / los cuatro | 4064, 4067-4070 |
| include singular `pt:term` | postmeta `mfn_<tipo><lang>_post` en **cada post** del término (`get_posts` sin límite) | 4086-4091 |
| include singular `pt` | option `mfn_<tipo><lang>_<pt>_single` | 4096 |
| include singular `''` | options `_post_single`, `_product_single`, `_portfolio_single`, `_offer_single`, `_page_single` | 4099-4103 |
| exclude singular `pt:term` | postmeta `mfn_<tipo><lang>_post_excluded` en cada post | 4113-4118 |
| exclude singular `pt` | option `mfn_<tipo><lang>_<pt>_single` (**sin** `_excluded`: el código lo escribe como include) | 4123 |
| exclude singular `''` | los cinco `_<pt>_single_excluded` | 4126-4130 |
| include other search-page | option `mfn_<tipo><lang>_search_page` | 4139 |

### 4.2 `set_post_types_templates_conditions($type)` — single-\*, archive-\*, sidemenu… (l.3746-3877)

Borra `mfn_<tipo>_template` (y `_<lang>`), y construye un array: `all` → `['all'][] = ID`; `wishlist` → `['wishlist'][]`;
taxonomía → `[<tax>][<'all'|term_id>][] = ID`; exclude → `[<tax>][<term>]['exclude'][] = ID` (l.3801-3838). Con WPML el array se
agrupa por idioma de la plantilla (aquí `$t_lang` es siempre el código, l.3793) y se guarda `mfn_<tipo>_template` para el idioma
principal y `mfn_<tipo>_template_<lang>` para los demás (l.3847-3873). Ejemplo real del dev:
`mfn_archive-portfolio_template = {"all":["128"]}`, `mfn_archive-portfolio_template_en = []`, `mfn_sidemenu_template = []`.
Lo consume `Mfn_Template_View::get_archive_template()` (`betheme/visual-builder/classes/front-template-view.php:135-200`:
`$opt['all']`, `$opt[<tax>]['all']`, `$opt[<tax>][<term_id>]`) y `get_singular_template()` (l.37).

### 4.3 `set_addons_templates_conditions('popup')` (l.3443-3740)

Dos options con la misma gramática que 4.1 pero acumulando IDs: `mfn_popup_addons_archives` y `mfn_popup_addons_singular`
(`[<pt>]['all'][]`, `[<term_id>][]`, `[<term_id>]['exclude'][]`; con WPML una copia por idioma `_<lang>`, l.3721-3738).
Las lee `mfn_addons_ID('popup')` (`theme-functions.php:1047`), que además suma el postmeta `mfn_popup_included` de la página (l.1069-1071).

## 5. Resolución en el front

`mfn_global()` (`betheme/functions.php:59-92`, hook `wp`) rellena `$mfn_global['header'|'footer']` con
`mfn_template_part_ID('header'|'footer')`, `cart/checkout/thank_you` con `mfn_endpoint_tmpl()` (`theme-functions.php:833-855`:
`mfn_<tipo>_template` si es numérico, si no `mfn_<tipo>_template_used`), `sidemenu` con `mfn_global_sidemenu_id()` (l.1205-1220:
primer template publicado con `mfn_sidemenu_visibility=always-visible`) y `search` con `get_option('mfn_search_template_used')`.

Orden de `mfn_template_part_ID($type)` (`theme-functions.php:863-1039`); el primero que exista, sea `template` y esté publicado gana:

1. Querystring `?mfn-header-template=<ID>` / `?mfn-footer-template=<ID>` (l.873-875; drafts solo con `&visual=iframe`).
2. Checkout Gutenberg: postmeta `mfn_dedicated_header_template` de la página de checkout (l.879-883; lo escribe Theme Options
   `gutenberg-checkout-header`, `betheme/muffin-options/options.php:1231,1269,1274`).
3. En WooCommerce, el meta `mfn_<tipo>_template` de la plantilla single-product/archive-product activa (l.888-894).
4. Sufijo de idioma `_<lang>` si el idioma actual no es el principal (l.897-903).
5. Búsqueda: `mfn_<tipo><lang>_search_page` (l.908-913).
6. Singular: override del post `mfn_<tipo>_template` (l.926-928) → mismo meta en la plantilla singular activa (l.939-944) →
   postmeta `mfn_<tipo><lang>_post` si no hay `_post_excluded` (l.947-950) → option `_<pt>_single` si no hay `_single_excluded`
   (l.953-956; tipos fuera de page/post/offer/portfolio/product/template cuentan como `page`, l.921-923) → página de portfolio
   (l.959-972) → `_entire_site` (l.974-979).
7. Archivos: meta de la plantilla archive activa (l.993-998) → termmeta `mfn_<tipo><lang>_term` sin `_term_excluded` (l.1004-1008)
   → `_entire_site` (l.1011-1014), que sobreescribe `_<pt>_arch` si existe y no está excluido (l.1029-1034).

Overrides por post (select "Custom Header/Footer Template", options via `mfna_templates()`, `betheme/muffin-options/theme-options.php:150-181`):
`mfn_header_template`, `mfn_footer_template`, `mfn_popup_included` en page (`class-mfn-post-type-page.php:71,80,93`), post (l.132,141,154;
además `mfn_single-post_template` l.119), portfolio (l.144,152,164; `mfn_single-post_template` l.131) y product (l.80,89; `mfn_single_product_template` l.103).
Valor `0` = "Default" (sin override; el dev tiene `mfn_header_template=0` en todas las páginas salvo la 128, que fuerza 93/122).

## 6. Recetas SSH

### 6.1 Ver qué hay y qué está en uso

```bash
S=/ruta/scripts
$S/be-template-list.sh                       # todos, con conditions y refs (options/postmeta/termmeta que apuntan a cada uno)
$S/be-template-list.sh type=header --used
$S/be-template-list.sh --options             # + volcado de todas las options mfn_*
# wp puro
wp post list --post_type=template --fields=ID,post_title,post_status
wp post meta get 93 mfn_template_type; wp post meta get 93 mfn_template_conditions
wp option list --search='mfn_*' --fields=option_name,option_value
```

### 6.2 Asignar un header/footer a todo el sitio (reversible)

```bash
$S/be-template-assign.sh id=93 everywhere=1 --dry-run
$S/be-template-assign.sh id=93 everywhere=1            # backup JSON en uploads/betheme/backups/template-header-<ts>.json
$S/be-template-assign.sh id=93 singular=page,post archive=product exclude-singular=product:12 search=1
$S/be-template-assign.sh restore=@/ruta/uploads/betheme/backups/template-header-<ts>.json   # revertir
```
El script escribe `mfn_template_conditions`, normaliza tipos deprecated, publica con `publish=1` si hace falta y llama al
método real (`new Mfn_Builder_Admin(true)`; la clase solo se carga en admin, `betheme/functions/builder/class-mfn-builder.php:37-45`,
pero con `$ajax=true` el constructor no necesita nada más, `class-mfn-builder-admin.php:54-63`).
Equivalente `wp` puro (sin backup, sin recompilar el `_<lang>` de los demás idiomas si no se usa la clase):
```bash
wp post meta update 93 mfn_template_conditions '[{"rule":"include","var":"everywhere","archives":"","singular":"","other":"search-page"}]'
wp eval 'require get_template_directory()."/functions/builder/class-mfn-builder-admin.php"; (new Mfn_Builder_Admin(true))->set_global_templates_conditions("header");'
```

### 6.3 Plantillas single/archive, popups y WooCommerce

```bash
$S/be-template-assign.sh id=175 all=1                          # single-portfolio para todos los proyectos
$S/be-template-assign.sh id=128 tax=portfolio-types:15         # archive-portfolio solo para el término 15
$S/be-template-assign.sh id=210 everywhere=1                   # popup en todo el sitio (mfn_popup_addons_*)
$S/be-template-assign.sh id=300 used=1                         # cart|checkout|thanks|search → mfn_<tipo>_template_used
$S/be-template-assign.sh id=93 cond=@conditions.json           # JSON literal de §3
```

### 6.4 Quitar una plantilla / override por página

```bash
$S/be-template-unassign.sh id=93 --dry-run
$S/be-template-unassign.sh id=93 --clear-post-overrides        # también borra mfn_header_template=93 en los posts
wp post meta update 128 mfn_header_template 93                 # override por página (0 = default)
wp post meta update 128 mfn_popup_included 210
```

### 6.5 Crear un header por SSH desde un export de otro sitio

En el sitio origen: `wp post meta get <ID> mfn-page-items --format=json > items.json`, y lo mismo para `mfn-page-object` y
`mfn-page-local-style` (strings JSON) y los metas del tipo (`header_position`, `header_sticky`…). En destino:

```bash
ID=$(wp post create --post_type=template --post_title='Header nuevo' --post_status=publish --porcelain)
wp post meta update $ID mfn_template_type header
wp eval "update_post_meta($ID,'mfn-page-items', wp_slash(json_decode(file_get_contents('items.json'), true)));"   # array, como visual-builder.php:406
wp post meta update $ID mfn-page-object "$(cat object.json)"
wp eval "require_once get_template_directory().'/functions/admin/class-mfn-helper.php'; \$o=json_decode(file_get_contents('object.json'),true); Mfn_Helper::preparePostUpdate(\$o,$ID,'mfn-page-local-style');"  # regenera local-style y mfn-page-fonts
$S/be-tools-regenerate-css.sh ids=$ID           # uploads/betheme/css/post-$ID.css (ver 07)
$S/be-template-assign.sh id=$ID everywhere=1
```
Si `builder-storage=encode`, guarda `mfn-page-items` como `base64_encode(serialize($array))` (`visual-builder.php:403-406`). Las URLs de
imágenes del origen hay que reemplazarlas (`wp search-replace` sobre `mfn-page-items`/`mfn-page-local-style`, como hace el importador de
demos, `class-mfn-importer-helper.php:1488`). Con WPML asigna idioma: `wp eval "do_action('wpml_set_element_language_details',['element_id'=>$ID,'element_type'=>'post_template','language_code'=>'es']);"`.
Los IDs `uid` de los elementos deben ser únicos: los genera `Mfn_Builder_Helper::unique_ID()`; ver muffinAI para el JSON.

## 7. Trampas verificadas

- **CPT no registrado en CLI**: sin WPML, `Mfn_Post_Type_Template` sale del constructor si `bebuilder_access` es false o el usuario no es
  editor/admin (l.21-34); en wp-cli no hay usuario → pasa `--user=<admin>` (`mfn_bebuilder_access()`, `betheme/functions/theme-head.php:2635-2670`).
  Los compiladores usan SQL directo y funcionan sin usuario, pero `be-template-list` y `wp post list --post_type=template` pueden no ver las
  plantillas si el CPT no está registrado: usa siempre `--user=<admin>` en los scripts `be-template-*`.
  En el dev (con WPML) el CPT sí se registra sin usuario. **[sin verificar]** el comportamiento de `wp post list --post_type=template` sin WPML ni `--user`.
- Solo cuentan plantillas **publicadas**; con drafts el compilador borra las options y el sitio se queda sin header. `be-template-assign` avisa y admite `publish=1`.
- Escribir solo `mfn_template_conditions` no cambia nada en el front: hay que recompilar (§4). Escribir solo las options funciona
  hasta que alguien guarde cualquier plantilla del tipo en el builder (se recompila todo desde las conditions).
- `exclude singular <pt>` escribe `_<pt>_single` sin `_excluded` (l.4123): excluir "todas las páginas" equivale a incluirlas. Usa términos o overrides.
- Varias plantillas del mismo tipo con `everywhere`: gana la última en el orden del SQL (sin ORDER BY); el panel avisa, el script no.
- Con WPML el sufijo se decide por el **idioma de la plantilla** al compilar y por el **idioma actual** al resolver: una plantilla en el
  idioma principal sirve a todos los idiomas que no tengan la suya (`mfn_header_entire_site` sin sufijo en el dev sirve a es/en/fr).
- `mfn_<tipo>_template_used` (cart/checkout/thanks/search) no lo compila nadie: solo el toggle del builder o `used=1`.
- El importador de demos restaura estas options buscando la plantilla por **título** (`class-mfn-importer-helper.php:653` y ss.): títulos únicos.
- `mfn-page-fonts` no se enqueue por página sino sumando el de todas las plantillas publicadas (`theme-head.php:458-465`).

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-template-list`: 5 plantillas (93 header, 94 sidemenu, 122 footer, 128 archive-portfolio, 175 single-portfolio) con condiciones, options que las referencian y overrides por post (`mfn_header_template` en el 128).
- `be-template-assign id=93 everywhere=1 search=1 --user=webmaster` añadió `mfn_header_search_page = 93` y dejó backup JSON en `uploads/betheme/backups/`; `restore=@<backup>` lo revirtió (la option desapareció).
- `be-template-unassign id=122 --user=webmaster` vació `mfn_footer_entire_site` y el footer dejó de renderizarse (`mfn-footer-tmpl` ausente); `restore=@<backup>` lo devolvió (122, footer de vuelta).
- Popup de prueba: `singular=page` escribió `mfn_popup_addons_singular = {"page":{"all":["185"]}}` (+ variantes `_en`/`_fr` vacías por WPML); `unassign` lo dejó en `[]`.
- Los scripts de plantillas necesitan `--user=<admin>`: sin usuario `bebuilder_access` es false y el CPT `template` no está disponible para `Mfn_Builder_Admin`.
