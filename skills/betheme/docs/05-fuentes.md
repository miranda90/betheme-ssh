# 05 — Fuentes: familia por rol, tamaños, fuentes propias y Google Fonts local

Cubre Theme Options → **Fonts** (Family, Size & Style, Custom) y el modo de carga de Google Fonts de **Performance**.
Adaptado de muffinAI `08-tipografia-fuentes.md` y revalidado línea a línea contra Betheme 28.4.3. La forma de los
campos `typography`/`font_select`/`checkbox` está en [02-tipos-de-campo-y-valores.md](02-tipos-de-campo-y-valores.md);
static.css en [07-herramientas-tools.md](07-herramientas-tools.md).

## 1. Dos sistemas independientes

| Sistema | Qué hace | Campos |
|---|---|---|
| Familia por rol | asigna una fuente (sistema, Google o propia) a 8 selectores CSS fijos + botones | `font_select` |
| Fuente propia autoalojada | `.woff`/`.ttf` propios → `@font-face` inline | `text` + `upload` por slot |

Ambos viven en `wp_options.betheme` y comparten el desplegable: `mfn_fonts()` (`betheme/muffin-options/fonts.php:14-1980`)
devuelve `system` (l.22-30: `''`, Arial, Georgia, Tahoma, Times, Trebuchet, Verdana), `custom` (l.34-56, cada nombre
**con `#`** solo dentro de esta lista) y `all` (Google, l.58-1969; **vacío** si `google-font-mode = disabled`, l.1971-1973).

## 2. Familia por rol (`$sections['font-family']`, `theme-options.php:9328`)

| id | línea | std | selector CSS (`betheme/style.php`) |
|---|---|---|---|
| `font-content` | 9349 | Poppins | `body, input, textarea, select, …` (l.126-128) |
| `font-lead` | 9358 | Poppins (cae a `font-content`, `theme-head.php:309`) | `.lead, .big` (l.130-132) |
| `font-menu` | 9368 | Poppins | `#menu > ul > li > a, #overlay-menu ul li a` (l.134-136) |
| `font-title` | 9377 | Poppins | `#Subheader .title` (l.138-140) |
| `font-headings` | 9386 | Poppins | `h1, h2, h3, h4, .text-logo #logo` (l.142-144) |
| `font-headings-small` | 9396 | Poppins | `h5, h6` (l.146-148) |
| `font-blockquote` | 9406 | Poppins | `blockquote` (l.150-152) |
| `font-decorative` | 9415 | Poppins | contadores, precios, chart… (l.155-157) |
| `button-font-family` | 1265 (sección buttons) | `''` (hereda) | `--mfn-button-font-family` (`style.php:1060-1065, 1116`) |
| `font-weight` | 9433 | checkbox `{300,400,400italic,500,600,700,700italic}` | pesos pedidos a Google (`theme-head.php:486-488`) |
| `font-subset` | 9470 | checkbox `{latin,latin-ext}` | subsets, solo modo local (`class-mfn-builder-ajax.php:322-331`) |

**Valor guardado = nombre literal, sin `#` también para las propias.** Verificado por tres vías: el `<option>` de una
custom se pinta sin `#` (`betheme/muffin-options/fields/font_select/field_font_select.php:61-62`), `style.php:112` hace
`str_replace('#','',…)` antes de escribir `font-family:"Nombre"`, y en el dev `wp option pluck betheme font-headings`
→ `"Avenir Medium"`. La referencia muffinAI decía `#Avenir`: era incorrecto. Los scripts aceptan `#` y lo quitan.

`style.php:115-120`: si `google-font-mode !== 'local'` se añade la pila del sistema (`-apple-system, … sans-serif`) tras
la familia; en modo local **no** hay fallback.

### Tamaños (`$sections['font-size']`, l.9495)

`font-size-{content,big,menu,title,single-intro,h1..h6}` + `-tablet`/`-mobile`, tipo `typography`
`{size, line_height, weight_style, letter_spacing}` (std en el catálogo; ids l.9535-9921). `font-size-responsive` (switch, l.9515, std 1):
si activo y no hay valores tablet/móvil, `style.php:336-345, 549, 762` escala los desktop ×0.85/×0.7 con mínimos.

## 3. Google Fonts: qué se encola y cómo

`mfn_styles()` (`betheme/functions/theme-head.php:441-525`):

1. `google-font-mode` (`theme-options.php:11581`, switch `''` | `local` | `disabled`): `disabled` → `return` sin fuentes (l.442-446).
2. Lista = `mfn_fonts_selected()` (l.305-327: los 8 roles) **+** `mfn-page-fonts` de todos los `template` publicados
   (SQL l.457-463) **+** `mfn-page-fonts` de la página actual (l.468-475) **+** option `be_classes_fonts` (JSON, l.455, 480-482).
3. Se filtran las que están en `mfn_fonts('all')`, con sufijo `:peso,peso` de `font-weight` (l.486-494).
4. `local` → `wp_enqueue_style('mfn-local-fonts', uploads/betheme/fonts/mfn-local-fonts.css)` (l.501-502);
   si no → `https://fonts.googleapis.com/css?family=A:400,700|B:…&display=swap` (l.503-505).
5. `button-font-family` se encola aparte, solo en modo `''` (l.512-523).

`mfn-page-fonts` es JSON `["Montserrat","Spectral"]` por post (`Mfn_Builder_Helper::get_bebuilder_fonts()`,
`betheme/functions/builder/class-mfn-builder-helper.php:165-183` lo agrega para el modo local).

### Modo local: `_tool_regenerate_fonts()`

Botón "Cache fonts" (`google-font-mode-regenerate`, `theme-options.php:11594`, `action => mfn_regenerate_fonts`) y
Tools → Regenerate fonts. Handler `Mfn_Builder_Ajax::_tool_regenerate_fonts()`
(`betheme/functions/builder/class-mfn-builder-ajax.php:274-430`), nonce l.276, capability l.278-280 → **no llamable
desde CLI**; `be-fonts-local-regenerate` replica el cuerpo paso a paso (la correspondencia línea→paso está en la cabecera
de `scripts/php/fonts-local-regenerate.php`):

1. `Mfn_Helper::filesystem()` (l.282) y rutas `uploads/betheme[/fonts]` con `mfn_uploads_dir()` (l.284-285; `theme-functions.php:3950-3966`).
2. Fuentes = `mfn_fonts_selected('builder_fonts')` (roles + todo `mfn-page-fonts`, l.307) + `Poppins` siempre (l.311-313) + `button-font-family` (l.316-319).
3. `font-subset` (se fuerzan `latin` y `latin-ext`) y `font-weight` (l.322-331).
4. **`$wp_filesystem->delete(uploads/betheme/fonts/, true)`** (l.334): borra la carpeta entera, siempre.
5. Por cada fuente de Google: `mkdir`; Poppins/Roboto/Open Sans añaden pesos 400,500,600 (l.348-352); por peso, GET a
   `fonts.googleapis.com/css?family=<slug>:<peso>&display=swap` con user-agent Firefox (l.291-294, 359-363); parsea
   bloques `/* subset */ @font-face` (l.369-392), descarga cada fichero a `<slug>/<slug>-<peso>-<subset>.<ext>` y reescribe
   el `src` como `./<slug>/<fichero>` (l.395-410).
6. `mfn_styles_minify()` (l.422; `theme-head.php:1251`) y escribe `mfn-local-fonts.css` (l.425).

No hay descarga incremental: cualquier cambio de familia, peso o subset exige regenerar todo.

## 4. Fuentes propias (`$sections['font-custom']`, l.9925)

| id | línea | tipo | contenido |
|---|---|---|---|
| `font-custom` / `-woff` / `-ttf` | 9945 / 9952 / 9960 | text / upload / upload | slot 1 |
| `font-custom2` / `-woff` / `-ttf` | 9976 / … | text / upload / upload | slot 2 |
| `font-custom{N≥3}` / `-woff` / `-ttf` | (dinámicos, JS `mfn_new_font`) | idem | slots 3… |
| `font-custom-fields` | 10009 | text (oculto) | **cuántos** dinámicos hay: N máx = 2 + `font-custom-fields` |

Los `upload` de fuente llevan `data => font` (el tema lee solo la URL; con o sin `#id`). Mimes `woff`/`ttf`/`svg`
los añade `mfn_upload_mimes()` (`theme-options.php:12058-12067`), por lo que `wp media import` funciona sin más.

**Compactación**: `MFN_Options::_register_custom_fonts()` (`betheme/muffin-options/options.php:1072-1107`), hook `init`
prio 12 (l.67), recorre `3..N`, descarta los de nombre vacío, renumera desde `font-custom3` y recalcula
`font-custom-fields` — **solo en memoria** (`$this->options`); se persiste cuando alguien pulsa Save en el panel.
Por SSH, `be-font-custom-add/remove` compactan el array antes de guardar para dejar la BD como la dejaría el panel.

### `@font-face`: siempre inline

`mfn_styles_custom_font()` (`theme-head.php:947-1000`) genera por cada slot con nombre:

```css
@font-face{font-family:"Nombre";src:url("woff") format("woff"),url("ttf") format("truetype");font-weight:normal;font-style:normal;font-display:swap}
```

y se inyecta con `wp_add_inline_style('mfn-dynamic', …)` en `mfn_styles_inline()` (`theme-head.php:730`) **siempre**,
esté o no activo `static-css` (solo `mfn_styles_dynamic()` y `mfn_styles_custom()` dependen de él, l.736-738, 758-760).
Consecuencia: dar de alta una fuente propia por SSH se ve al instante; **asignarla a un rol** (`font-headings` → `style.php`)
no, si `static-css` está activo, hasta regenerar static.css.

## 5. Recetas SSH

```bash
cd ~/public_html
S=~/be-ssh-test/scripts

# --- leer
wp option pluck betheme font-headings
wp option pluck betheme google-font-mode          # '' (Google) | local | disabled; error = no existe = ''
wp option pluck betheme font-custom-fields

# --- familia por rol (Google, sistema o propia; propia SIN #)
$S/be-font-role-set.sh role=headings font="Inter" --dry-run
$S/be-font-role-set.sh role=headings,title font="Inter"
$S/be-font-role-set.sh role=content,lead font="Verdana"
$S/be-font-role-set.sh role=button font="Poppins"

# --- fuente propia: alta (primer slot libre) → asignar → baja
$S/be-font-custom-add.sh name="Avenir Heavy" woff=/tmp/Avenir-Heavy.woff ttf=/tmp/Avenir-Heavy.ttf
$S/be-font-role-set.sh role=decorative font="Avenir Heavy"
$S/be-font-custom-remove.sh name="Avenir Heavy"            # avisa de los roles que aún la usan
$S/be-font-custom-remove.sh slot=3 --delete-media           # y manda los adjuntos a la papelera

# --- Google Fonts en local
$S/be-font-role-set.sh mode=local
$S/be-fonts-local-regenerate.sh --dry-run                   # fuentes/pesos/subsets y carpeta que se borraría
$S/be-fonts-local-regenerate.sh --yes                       # BORRA uploads/betheme/fonts/ y descarga
curl -s https://sitio.com/ | grep -o 'mfn-local-fonts[^"]*\|fonts.googleapis.com/css[^"]*'

# --- tamaños (typography = array → --json)
$S/be-opt-set.sh key=font-size-h1 --json value='{"size":"48","line_height":"56","weight_style":"500","letter_spacing":"0"}'
```

Equivalente en `wp` puro (sin revisión ni static.css; solo si `static-css` está desactivado):

```bash
wp option patch update betheme font-headings "Inter"
wp option patch update betheme font-custom "Avenir Heavy"
wp option patch update betheme font-custom-woff "$(wp option get home)/wp-content/uploads/2026/08/fonts/Avenir-Heavy.woff"
```

Slot dinámico nuevo a mano: `N = 3 + font-custom-fields`, escribir `font-customN`, `-woff`, `-ttf` y
`font-custom-fields = N - 2`. Regenerar static.css después: `be-static-css-regenerate.sh`.

## 6. Trampas verificadas

- **El valor de un rol es el nombre literal**: renombrar una fuente propia (`font-custom`) no actualiza `font-*`;
  `be-font-custom-remove` lista los roles afectados, `be-font-role-set` los corrige.
- **`#` nunca se guarda** en `font-*` (§2). Si lo escribes a mano funciona igual (`style.php:112` lo quita) pero difiere del panel.
- **`static-css` activo + cambio de rol por `wp option patch` = invisible** hasta `_static_CSS(true)`; el `@font-face`
  propio no sufre esto (siempre inline). Los scripts regeneran static.css siempre.
- **`google-font-mode = local` sin regenerar** = fuentes Google sin cargar (`mfn-local-fonts.css` viejo o inexistente);
  y sin pila de fallback en `style.php:115` → texto en la fuente por defecto del navegador.
- **`disabled` no borra el `@font-face` propio** ni bloquea `button-font-family` (que solo se encola en modo `''`).
- **`font-weight` y `font-subset` traen `post-meta: "1"`** (`field_checkbox.php:50`): el original lo pide a Google como
  peso `post-meta` (respuesta vacía, ignorado); el script lo filtra.
- **`_tool_regenerate_fonts()` borra `uploads/betheme/fonts/` entero** antes de descargar; un fallo de red deja el sitio sin
  fuentes locales. `be-fonts-local-regenerate` devuelve `errors[]` y regenera static.css al final.
- **Una fuente propia declara siempre `font-weight:normal`**: con `weight_style` ≠ 400 el navegador sintetiza negrita.
  Para pesos reales, un slot por peso (`Avenir`, `Avenir Medium`, `Avenir Heavy`, como en el dev) y `weight_style` 400.
- **`mfn_fonts('all')` vacío con `disabled`**: `be-font-role-set` no puede validar una Google Font en ese modo (`force=1`).
- **Compactación solo en memoria** (§4): `get_option('betheme')` crudo puede tener huecos que `mfn_opts_get()` ya no ve.

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-font-custom-add name="Prueba SSH" woff=… ttf=…` ocupó el slot 4 (`font-custom4*`) y puso `font-custom-fields = 2`; el `@font-face` inline se comprueba con `curl` cache-busted. `be-font-custom-remove name="Prueba SSH" --delete-media` vació el slot, devolvió `font-custom-fields` a `1` y borró los adjuntos; `font-custom3` (Avenir Heavy) intacto.
- `be-font-role-set role=blockquote font="#Avenir Heavy"` guarda `Avenir Heavy` **sin `#`** (confirmado con `wp option pluck`); vuelta a `Avenir`.
- `be-fonts-local-regenerate --dry-run`: plan correcto (Poppins como Google; Verdana y las tres Avenir omitidas; aviso de que `google-font-mode` no es `local`). No se ejecutó con `--yes` porque borraría `uploads/betheme/fonts/` (restos de una regeneración anterior).
