# 08 — Export, import, reset y revisiones de Theme Options

Cubre la pestaña **Backup & Reset** del panel (Export / Import / Reset) y la pestaña **History**
(revisiones `update` / `revision` / `backup`), y cómo reproducir cada operación por SSH. Dónde vive
la fila `betheme` y qué pasa al guardarla: [01-almacenamiento-y-guardado.md](01-almacenamiento-y-guardado.md).

## 1. Qué hace el panel

Theme Options → menú lateral "Backup & Reset" (`betheme/muffin-options/options.php:2041-2044`), con dos pestañas:

| Pestaña | Bloque | Qué hace | Código |
|---|---|---|---|
| History | Update / Revision / Backup | Lista las tres colas de revisiones con botón *Restore*; botón *Save revision* | `options.php:2118-2165` |
| Backup & Reset | Export | *Copy* (textarea), *Copy link* (URL del feed), *Download* (fichero `.txt` con JSON) | `options.php:2176-2205` |
| Backup & Reset | Import | *Import from file* (textarea `betheme[import_code]`) / *Import from link* (`betheme[import_link]`) + submit `betheme[import]` | `options.php:2231-2243` |
| Backup & Reset | Reset | *Reset to default* → pide el código `r3s3t` → submit `betheme[defaults]` | `options.php:2262-2274` |

Todo el bloque se procesa en `MFN_Options::_validate_options()` (`options.php:1113`), el callback del
Settings API, en este orden: **restaurar revisión** (l.1119-1135) → **import** (l.1141-1171) → **reset**
(l.1175-1178) → validación normal + `set_revision('update')` (l.1182-1186).

## 2. Export

### 2.1 Feed (lo que usan *Copy link* y *Download*)

`MFN_Options::_download_options()` (`options.php:868-897`), enganchado a `do_feed_mfn-opts-betheme` (l.85):

- URL: `<site_url>/?feed=mfn-opts-betheme&secret=<md5(AUTH_KEY.SECURE_AUTH_KEY)>` (l.2205; con `&action=download_options` fuerza descarga como `betheme_options_<d-m-Y>.txt`, l.883-895).
- El secreto se comprueba en l.870; no hace falta sesión ni nonce. **El feed sirve cualquier opción**: `get_option(str_replace('mfn-opts-','', $_GET['feed']))` (l.879) → quien tenga el secreto puede leer `?feed=mfn-opts-siteurl`, etc.
- Cuerpo: `json_encode( get_option('betheme') + ['mfn-opts-backup' => '1'] )` (l.879-881). Es un JSON plano `{id: valor}`.

### 2.2 Textarea *Copy*

`'###' . serialize( $this->options + ['mfn-opts-backup' => '1'] ) . '###'` (`options.php:2198-2202`). **No es JSON ni base64**: es `serialize()` PHP entre `###`. Ver trampa 6.1.

### 2.3 Forma del fichero exportado

| Clave | Valor | Origen |
|---|---|---|
| `<id de campo>` | string / array según tipo ([02](02-tipos-de-campo-y-valores.md)) | fila `betheme` |
| `last_tab` | string (pestaña abierta en el panel) o `false` | UI, no es opción |
| `imported` | `1` si alguna vez se importó | `options.php:1166` |
| `mfn-opts-backup` | `"1"` | marca que añade el export (l.880, 2199) |

## 3. Import

`_validate_options()` l.1141-1171, solo si llega `betheme[import]`:

1. Origen: `import_code` (textarea, l.1143-1146) o, si está vacío, `import_link` descargado con `wp_safe_remote_get()` (l.1148-1151).
2. `json_decode($import, true)` (l.1155).
3. Fallback "1-click Demo Data": `if ($imported_options === false)` → `base64_decode` + `unserialize` (l.1159-1163). **Código muerto**: `json_decode` devuelve `NULL` con JSON inválido, nunca `false`, así que el fallback no entra jamás.
4. Si es array: `imported = 1`, `last_tab = false` y **devuelve el array tal cual** (l.1165-1168): reemplaza TODA la opción, sin validar campos, sin pasar por `_validate_values()` y **sin revisión** (`set_revision('update')` está después, l.1186, y ya no se alcanza).

Por tanto, por el panel solo importa de verdad el **JSON del feed**. Ni el contenido del textarea *Copy* (`###a:…###`) ni el `options.txt` de las demos (base64) se importan desde esta pantalla: la petición cae al guardado normal y no cambia nada (aparece "saved" pero con los valores anteriores).

## 4. Reset

Botón *Reset to default* (`options.php:2262-2274`): el JS exige el código `r3s3t` y, al confirmar, cambia el valor del submit a `Resetting...` (`muffin-options/js/options.js:580-586`). En el servidor: `if ($plugin_options['defaults'] == 'Resetting...') return $this->_default_values()` (l.1175-1178).

`_default_values()` (`options.php:640-663`) recorre `$this->sections` y devuelve `[id => std]` para **todos** los campos con id (`std` vacío → `''`), más `last_tab => false` (l.662). Es la misma función que crea la fila la primera vez (`_set_default_options`, l.670). Tampoco crea revisión (se devuelve antes de l.1186).

## 5. Revisiones

### 5.1 Dónde viven y forma

Tres options, una por cola (`get_revisions()`, `options.php:194-221`):

| Option | Cuándo se crea | Código |
|---|---|---|
| `betheme_revision_update` | en cada *Save Changes* del panel, con las opciones **que se van a guardar** | `options.php:1186` |
| `betheme_revision_revision` | botón *Save revision* (AJAX `mfn_options_revision_save`, l.93, 100-126) y el arreglo de conflicto SEO de `_backward_compatibility` (l.586) | |
| `betheme_revision_backup` | automáticamente **antes de restaurar** una revisión de otra cola (`options.js:519-521`), y por `be_opts_save()` en todos los scripts `be-*` | |

Formato de cada option: `array( <timestamp> => base64( serialize( $options ) ) )` (`set_revision()`, `options.php:132-166`):

- `$options` es el array completo (la AJAX quita `last_tab`, `import_code`, `import_link`, l.117-119; el `update` conserva `last_tab`).
- Timestamp = `current_time('timestamp')` (l.159): **hora local de WP**, no UTC. El panel lo formatea con `date()` a secas (l.213), así que sale bien; si lo conviertes tú, no le sumes la zona horaria otra vez.
- Máximo **5** por cola: al llegar a 5 se elimina la primera (más antigua) antes de añadir (l.140-150).

### 5.2 Restauración

`_validate_options()` l.1119-1135: si el POST trae `revision-time` y `revision-type` (inputs ocultos que añade el JS, `options.js:525-526`), lee `betheme_revision_<type>[<time>]`, hace `unserialize(base64_decode())` con `allowed_classes => false` (l.1131) y **devuelve ese array tal cual** como nuevas opciones (sin validar, sin `set_revision('update')`). El JS crea antes una revisión `backup` con el formulario actual (l.519-521), salvo que lo restaurado sea ya un `backup`.

### 5.3 Cómo lo usan los scripts

`be_opts_save()` (`scripts/php/_lib.php`) llama a `$MFN_Options->set_revision('backup', <estado anterior en BD>)` antes de cada `update_option('betheme')`, así que **cualquier escritura con `be-*`** deja el estado previo en `betheme_revision_backup` (cola de 5). Para deshacer: `be-options-revisions.sh type=backup ts=<ts> --restore`.

## 6. Recetas SSH

Scripts: `be-options-export.sh` (RO), `be-options-import.sh` (destructivo, `--yes`), `be-options-reset.sh` (destructivo, `--yes`), `be-options-revisions.sh` (RO; `--restore`/`--save` reversibles). Todos aceptan `--path=/ruta/wp`.

### 6.1 Exportar a JSON

```bash
# Igual que "Download" del panel (incluye mfn-opts-backup=1; --strip quita last_tab)
be-options-export.sh out=/ruta/backups/betheme-$(date +%F).json --strip

# wp puro (sin la marca mfn-opts-backup; importable igualmente con be-options-import)
wp option get betheme --format=json > betheme.json

# El feed, desde fuera, sin sesión
curl -s "$(wp option get siteurl)/?feed=mfn-opts-betheme&secret=$(wp eval 'echo md5(AUTH_KEY.SECURE_AUTH_KEY);')" > betheme.json
```

### 6.2 Importar (reemplaza todo) con validación

```bash
be-options-import.sh in=betheme.json --dry-run      # diff: changed/added/removed, no escribe
be-options-import.sh in=betheme.json --yes           # revisión backup + update_option + static.css + reset JS BeBuilder
be-options-import.sh in=betheme.json --merge --yes   # array_merge sobre lo actual (solo pisa las claves del fichero)
```

Acepta JSON, `###serialize###` (textarea *Copy*), `serialize` y `base64(serialize)` (`options.txt` de demos, revisiones). Rechaza el fichero si menos de `min-known` (100) claves son ids reales de `be_fields()`; `--force` lo salta. Añade `imported=1` y `last_tab=false` como el panel (l.1166-1167).

wp puro (sin validación ni revisión ni static.css; después regenera con `be-static-css-regenerate.sh`):

```bash
wp option update betheme --format=json < betheme.json
```

### 6.3 Reset a valores por defecto

```bash
be-options-reset.sh --dry-run
be-options-reset.sh --yes                              # _default_values() vía be_opts_save (revisión backup)
be-options-reset.sh --keep=custom-css,custom-js --yes  # conserva esos ids (el panel no lo permite)
```

wp puro no tiene equivalente corto: `_default_values()` necesita `$MFN_Options` cargado (`wp eval 'global $MFN_Options; update_option("betheme", $MFN_Options->_default_values());'`, sin revisión).

### 6.4 Listar, comparar y restaurar revisiones

```bash
be-options-revisions.sh                                   # las 3 colas: ts, fecha, nº claves, bytes
be-options-revisions.sh type=update ts=1787238636         # diff revisión → estado actual (valores recortados; --full)
be-options-revisions.sh type=update ts=1787238636 export=/ruta/rev.json
be-options-revisions.sh type=update ts=1787238636 --restore --dry-run
be-options-revisions.sh type=update ts=1787238636 --restore   # guarda antes la actual en `backup`
be-options-revisions.sh --save                             # como "Save revision" (cola revision)
```

wp puro:

```bash
wp option get betheme_revision_update --format=json | jq 'keys'                       # timestamps
wp eval 'foreach (get_option("betheme_revision_update") as $t => $b) echo $t, " ", date("Y-m-d H:i", $t), " ", count(unserialize(base64_decode($b))), "\n";'
```

### 6.5 Migración entre entornos (URLs de logos, favicon, fuentes, imágenes de fondo)

Los `upload` guardan `URL#attachment_id` ([04-logos-y-favicon.md](04-logos-y-favicon.md)); las URLs absolutas del origen hay que reescribirlas y el `#id` **no existe en el destino** (`mfn_get_attachment_data()` cae a la URL si el adjunto no está, ver 04).

```bash
# Opción A: reemplazo en el propio import (solo valores string de betheme; replica class-mfn-importer-helper.php:609-616)
be-options-import.sh in=betheme-pro.json --replace-url=https://web.com,https://dev.web.com --yes

# Opción B: wp search-replace sobre la fila serializada (WP-CLI reserializa bien); --dry-run primero
wp search-replace 'https://web.com' 'https://dev.web.com' "$(wp db prefix)options" --include-columns=option_value --dry-run
```

Tras cualquier import/restore: comprobar `uploads/betheme/css/static.css` (lo regenera `be_opts_save`), y si el destino usa `google-font-mode=local`, regenerar fuentes ([05-fuentes.md](05-fuentes.md)).

## 7. Trampas verificadas

1. **El textarea *Copy* no se puede importar por el panel** (sección 3): el formato `###serialize###` no es JSON y el fallback base64 (l.1159) es inalcanzable. Usa *Download*/feed (JSON) o `be-options-import`, que acepta los cuatro formatos.
2. **Import y reset no crean revisión** en el panel (retornan antes de l.1186). Los scripts sí (`backup`).
3. **Import por panel no dispara `_static_CSS()` hasta la recarga** con `?settings-updated` (`options.php:88-90`); los scripts lo hacen en el acto.
4. **Revisiones = 5 por cola** (l.140): cada `be-opt-set` consume un hueco de `backup`. Antes de una tanda larga, `be-options-export.sh out=…`.
5. `betheme_revision_update` guarda lo que **se va a guardar**, no lo anterior: la revisión más reciente de `update` es el estado actual (si nadie ha escrito por SSH después).
6. **Timestamps en hora local** de WP (`current_time('timestamp')`, l.159). `be-options-revisions` los formatea igual que el panel.
7. `import_link` usa `wp_safe_remote_get()`: rechaza hosts locales/privados salvo filtro `http_request_host_is_external`. Por SSH, descarga con `curl` y usa `in=`.
8. Un JSON de otra versión de Betheme importa sin quejas (no se valida): las claves desconocidas quedan en la fila (inocuas, `mfn_opts_get` no las lee) y las nuevas del tema toman `std`. `be-options-import` avisa de las desconocidas.
9. El feed expone **cualquier** option con el secreto (l.879). No compartas la URL de *Copy link*.

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-options-export out=pre.json` → 742 claves, formato compatible con el feed (`mfn-opts-backup = 1`).
- `be-options-revisions` lista las colas (`update` 4, `backup` creadas por los scripts); `type=backup ts=<ts>` muestra el diff contra el estado actual.
- `be-options-import in=pre.json --dry-run` mostró exactamente los cambios de prueba pendientes; con `--yes` restauró la fila (diff final vacío salvo `last_tab`/`imported`).
- `be-options-reset --dry-run`: 201 cambios, 89 añadidos, 6 eliminados respecto a los `std`. No se ejecutó con `--yes` (destructivo).
