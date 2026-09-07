# 06 — Iconos: packs personalizados (CPT `icons`)

Cómo Betheme 28.4.3 registra, almacena, carga y borra los packs de iconos IcoMoon, y cómo darlos de alta
y de baja por SSH sin el wp-admin. Adaptado de la referencia muffinAI 07 con todas las líneas revalidadas.
El JSON de los elementos del BeBuilder que consumen iconos (`icon`, `icon_box`…) no se documenta aquí:
ver `~/Documents/Mis proyectos/muffinAI/docs/bebuilder/`.

## 1. Las tres fuentes de iconos

| Fuente | Clases | Dónde se define | Cómo se carga en el front |
|---|---|---|---|
| Muffin icons | `icon-*` | `Mfn_Icons::icons['mfn']` (`betheme/muffin-options/icons.php:12-13`), listado `get_icons()` l.2097 | dentro de `css/be.css` (`functions/theme-head.php:395`); `fonts/mfn/icons.css` solo con BeBlocks (l.391-393) |
| Font Awesome | `fas fa-*`, `far fa-*`, `fab fa-*` | `Mfn_Icons::icons['fa']` (`icons.php:489`) | `fonts/fontawesome/fontawesome.css` salvo `performance-assets-disable[font-awesome]` (`theme-head.php:403-405`); el selector deshabilita la opción FA (`visual-builder/partials/modal-icons.php:17-19`) |
| **Packs personalizados** | `{prefijo}-{nombre}` | CPT `icons` (`betheme/functions/post-types/class-mfn-post-type-icons.php:138`) | `<link>` impreso por `load_icons()` en `wp_footer`/`admin_footer` (l.48-49, l.180-202) |

Gate del CPT: `theme-disable[custom-icons]` (Theme Options > Advanced) — con la casilla marcada el fichero de la clase
ni se incluye (`betheme/functions.php:117-119`) y desaparecen el menú, el `<link>` y el pack en el selector.

## 2. El CPT `icons`

`register()` (`class-mfn-post-type-icons.php:102-139`): `public=false`, `show_ui=true`, `supports=['title']`,
capacidades `create/edit/publish_posts => edit_theme_options` (l.129-135), menú `betheme` solo si el usuario
tiene `edit_theme_options` (l.116-120). Sin front, sin permalink. Solo cuenta con `post_status=publish`
(l.186, l.214). El constructor (l.25-50) engancha: `init` → `register` y `make_dir` (l.145-155: crea
`uploads/betheme/` y `uploads/betheme/icons/`), `trashed_post` → `single_post_remove` (l.40), `wp_footer` y
`admin_footer` prio 1 → `load_icons` (l.48-49). En wp-cli la clase está cargada (se incluye fuera del gate `is_admin()`).

Campos del post (`set_fields()` l.56-96): `mfn-icon-name` (std `My icon <uid>`), `mfn-icon-prefix` (std `ci-<uid>`),
`mfn-icon-upload` (tipo `upload_icon`). Todo lo demás son metas que escribe el campo al subir.

## 3. Contrato de metas

| Meta | Contenido | Quién la consume |
|---|---|---|
| `mfn-icon-name` | Nombre visible sin parsear (`Mi Empresa`) | `set_icons_name()` → elemento `[0]` del titles-array; `<option>` del selector (`modal-icons.php:23-25`) |
| `mfn-icon-name-parsed` | Nombre saneado = **nombre de la carpeta** (`MiEmpresa`) | `load_icons()` (id del `<link>`, l.197); `single_post_remove()` (**qué carpeta borra**, l.164-165) |
| `mfn-icon-prefix` | Prefijo de clase **sin guion** (`miempresa`) | elemento `[1]` del titles-array; clase final `{prefijo}-{icono}` |
| `mfn-icon-upload` | Ruta **absoluta** del directorio del pack | condición "hay pack" de `load_icons()` (l.194-196); `is_icon_uploaded()` en el admin |
| `mfn-icon-url` | URL pública del directorio | `href` del `<link>` (`{url}/style.css`, l.197) |
| `mfn-icon-titles-array` | Array serializado `[nombre, prefijo, icono1, icono2…]` | `get_list_of_icons()` (l.208-230) → selector del builder (`modal-icons.php:11`, `icons.php:2135`) |

`mfn-icon-titles-array` es la **única** fuente del selector: sin ella el CSS carga pero el pack no aparece.
Las metas se leen con `get_post_field()` (l.164, 194, 197, 222), que para claves desconocidas cae en `get_post_meta` (WP_Post::__get).

## 4. Qué hace el admin al subir (`upload_icons()`, `betheme/muffin-options/fields/upload_icon/field_upload_icon.php:46-137`)

Se ejecuta desde `render()` cuando la URL trae `?message=` tras guardar el post (l.238-245), es decir, **cada vez que se guarda el post**.

1. Lee nombre, prefijo y URL subida; si falta cualquiera, `return` silencioso (l.69-71).
2. `parse_str_on_upload()` (l.34-40): `preg_replace('[\W]','',…)` elimina todo lo que no sea `[A-Za-z0-9_]`
   (acentos y guiones incluidos: `Témini` → `Tmini`, `ci-1a2b` → `ci1a2b`) y después pasa espacios a `_` (ya no queda ninguno).
   Se aplica al nombre **y al prefijo** (l.60, l.63). Luego `sanitize_file_name()` + `validate_file()` (l.75-78).
3. Mueve el zip a `uploads/betheme/icons/{parsed}.zip` (l.85) y escribe 4 metas (l.89-92).
4. `unzip_file()` a `uploads/betheme/icons/{parsed}/` (l.101); borra todo **fichero** cuya extensión no esté en
   `svg, ttf, woff, woff2, eot, json, css` (l.113-122; las carpetas quedan, p. ej. `demo-files/` con su `demo.css`); borra el zip (l.126).
5. `add_prefix_on_upload()` (l.13-28): `preg_replace('/icon-/', '{prefijo}-', style.css)` → **el zip debe venir de IcoMoon con el prefijo por defecto `icon-`**.
6. `mfn-icon-url` = `get_home_url() . '/wp-content/uploads/betheme/icons/{parsed}'` (l.83, **ruta hardcodeada**, no `wp_upload_dir()`), l.134.
7. `set_icons_name()` (l.143-166): lee `selection.json`, `icons[].properties.name` → `mfn-icon-titles-array` (l.154-164).

La previsualización del admin (`draw_icons_svg()` l.172-200) dibuja `<svg>` con `icons[].icon.paths` de `selection.json`, no usa la fuente.

## 5. Estructura en disco

```
wp-content/uploads/betheme/icons/{NombreParseado}/
├── style.css          @font-face + .{prefijo}-{icono}:before { content: "\eXXX" }
├── selection.json     formato IcoMoon (el theme solo lee icons[].properties.name e icons[].icon.paths)
└── fonts/{prefijo}.{eot,ttf,woff,svg}
```

## 6. Carga y borrado

**CSS**: `load_icons()` recorre los `icons` publicados (l.184-191) y, si `mfn-icon-upload` no está vacío, imprime
`<link rel="stylesheet" id="mfn-custom-icons-{parsed}" href="{mfn-icon-url}/style.css" media="all">` (l.197).
Sin `wp_enqueue_style`, **sin versión** y en el footer.

**Selector**: `get_list_of_icons()` devuelve los titles-array (l.221-227); el modal genera `data-rel="{prefijo}-{icono}"`, que es lo que se guarda en el JSON del builder.

**Borrado**: `single_post_remove()` (l.161-174) en `trashed_post`: `delete(path_icons/{mfn-icon-name-parsed}, true)` y `wp_delete_post($id, true)`.

## 7. Recetas SSH

Listar (RO):

```bash
scripts/be-icons-pack-list.sh              # metas, carpeta, dir_exists, in_front, in_picker, salida de load_icons()
scripts/be-icons-pack-list.sh --icons      # + nombres de icono
# wp puro:
wp post list --post_type=icons --fields=ID,post_title,post_status
wp eval 'foreach(Mfn_Post_Type_Icons::get_list_of_icons() as $p) echo "$p[0] | $p[1] | ".(count($p)-2)." iconos\n"; Mfn_Post_Type_Icons::load_icons();'
```

Alta (replica `upload_icons()` punto por punto; aborta si ya hay un pack con ese nombre parseado o existe la carpeta):

```bash
scp icomoon.zip usuario@host:/tmp/
scripts/be-icons-pack-add.sh zip=/tmp/icomoon.zip name="Mi Pack" prefix=mipack --dry-run   # plan: parsed, carpeta, ficheros
scripts/be-icons-pack-add.sh zip=/tmp/icomoon.zip name="Mi Pack" prefix=mipack
scripts/be-icons-pack-add.sh dir=/tmp/pack-descomprimido name="Mi Pack" prefix=mipack       # carpeta ya lista
curl -s https://sitio/wp-content/uploads/betheme/icons/MiPack/style.css | grep -c 'mipack-'  # comprobar
```

wp puro (sin scripts; `PARSED` y `PREFIX` ya parseados a mano según el paso 2 de §4):

```bash
cd ~/public_html; NAME="Mi Pack"; PARSED="MiPack"; PREFIX="mipack"; DIR="wp-content/uploads/betheme/icons/$PARSED"
mkdir -p "$DIR" && unzip -o /tmp/icomoon.zip -d "$DIR" && find "$DIR" -type f ! -regex '.*\.\(svg\|ttf\|woff\|woff2\|eot\|json\|css\)$' -delete
sed -i "s/icon-/${PREFIX}-/g" "$DIR/style.css"
ID=$(wp post create --post_type=icons --post_status=publish --post_title="$NAME" --porcelain)
for m in "mfn-icon-name|$NAME" "mfn-icon-name-parsed|$PARSED" "mfn-icon-prefix|$PREFIX" "mfn-icon-upload|$PWD/$DIR" "mfn-icon-url|$(wp option get home)/$DIR"; do wp post meta update $ID "${m%%|*}" "${m#*|}"; done
wp post meta update $ID mfn-icon-titles-array "$(wp eval "\$j=json_decode(file_get_contents('$DIR/selection.json'),true);\$a=['$NAME','$PREFIX'];foreach(\$j['icons'] as \$i)\$a[]=\$i['properties']['name'];echo json_encode(\$a);")" --format=json
```

Baja (destructivo; usa `wp_trash_post()` para que `single_post_remove()` borre la carpeta; si el hook no corre —
`EMPTY_TRASH_DAYS=0` o CPT desactivado— el script replica su cuerpo):

```bash
scripts/be-icons-pack-remove.sh id=123               # muestra la carpeta que borraría (mfn-icon-name-parsed)
scripts/be-icons-pack-remove.sh id=123 --yes
scripts/be-icons-pack-remove.sh id=123 --yes --keep-files   # solo el post (wp_delete_post force); la carpeta queda
# wp puro: comprobar ANTES la meta que decide qué carpeta se borra
wp post meta get 123 mfn-icon-name-parsed \
  && wp eval 'require_once get_template_directory()."/functions/admin/class-mfn-helper.php"; wp_trash_post(123);'
# el hook trashed_post usa Mfn_Helper (solo-admin): sin ese require_once, `wp post delete 123` da un fatal en CLI
```

Editar un pack existente:

| Cambio | Qué tocar |
|---|---|
| Añadir/quitar iconos | Sustituir ficheros en la carpeta y regenerar `mfn-icon-titles-array` (receta del `wp eval` de arriba) |
| Cambiar prefijo | `sed -i "s/viejo-/nuevo-/g" style.css`, meta `mfn-icon-prefix`, elemento `[1]` del array **y** las clases en el JSON de las páginas ya maquetadas |
| Renombrar carpeta | mover directorio + `mfn-icon-name-parsed` + `mfn-icon-upload` + `mfn-icon-url` |
| Mover de servidor | actualizar `mfn-icon-upload` (ruta absoluta) y `mfn-icon-url` (dominio) — `be-icons-pack-list` avisa (`upload_path_ok:false`) |

## 8. Trampas verificadas

- **`mfn-icon-name-parsed` vacío = catástrofe al enviar a la papelera**: `single_post_remove()` construye `path_icons.'/'.''` y borra **toda** `uploads/betheme/icons/` recursivamente (l.164-168). `be-icons-pack-remove` aborta en ese caso; nunca `wp post delete` sin `--force` a un post `icons` sin esa meta.
- **`mfn-icon-upload` con ruta de otro servidor** (tras migrar): front OK (usa `mfn-icon-url`) pero el admin muestra *"This is not the Icomoon zip file"* (`is_icon_uploaded()` l.206-222). En el dev, el pack `Témini` (post 72) apunta a `/home/otro-servidor/...`. Arreglo: actualizar la meta.
- **El prefijo se parsea**: `parse_str_on_upload()` quita guiones (`ci-abc` → `ciabc`) y acentos; el theme añade el guion al componer la clase. `be-icons-pack-add` avisa del valor final.
- **El zip debe traer `icon-`**; con otro prefijo `add_prefix_on_upload()` no reescribe nada y las clases colisionan con Muffin icons (`icon-*`). `be-icons-pack-add` avisa si `style.css` no contiene `icon-`.
- **Prefijos cortos son peligrosos**: IcoMoon genera `[class^="{prefijo}-"], [class*=" {prefijo}-"] { font-family: … !important }`; cualquier clase que empiece igual hereda la fuente.
- **Reabrir y guardar el post en el admin vuelve a ejecutar `upload_icons()`** (l.238-245): con la URL del zip original ya movida, falla en silencio (l.69-71 o l.85) y puede dejar metas a medias.
- **`mfn-icon-url` hardcodea `/wp-content/uploads/`** (l.83): con `UPLOADS` personalizado el `<link>` apunta mal. Los scripts usan `wp_upload_dir()['baseurl']`.
- **Sin versión en el `<link>`** (l.197): actualizar un pack en la misma carpeta no se ve para quien ya cacheó `style.css`. Diagnóstico: `curl -s …/style.css | grep clase-nueva`.
- **`post_status` ≠ `publish`** = pack inexistente para el theme (l.186, l.214).
- **La whitelist borra ficheros, no carpetas**: el zip de IcoMoon deja `demo-files/demo.css` (en el dev, `Tmini/demo-files/`). Inofensivo.

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-icons-pack-list` detecta los 2 packs reales y la trampa de migración del post 72 (`mfn-icon-upload` apunta a `/home/otro-servidor/...`).
- `be-icons-pack-add zip=pack-test.zip name="Prueba SSH" prefix=pssh`: carpeta `PruebaSSH/` con `style.css`, `selection.json`, `fonts/`; `README.txt` eliminado por la whitelist; 4 clases reescritas a `pssh-`; post 183 con las 6 metas; `<link id="mfn-custom-icons-PruebaSSH">` en el front (con cache-busting).
- `be-icons-pack-remove id=183 --yes`: la primera versión del script provocó un fatal en `single_post_remove()` (`Mfn_Helper` no está cargada en CLI); corregido cargando las clases admin antes de `wp_trash_post()`. Tras el arreglo: carpeta borrada, post eliminado definitivamente.
