# 13 — Verificación y pruebas

Cómo comprobar que los scripts y la documentación siguen siendo correctos (tras actualizar Betheme o tocar los scripts),
y el protocolo con el que se probaron.

## 1. Comprobaciones sin servidor (local)

```bash
cd temini/docs/betheme-ssh
for f in scripts/_env.sh scripts/*.sh scripts/dev/*.sh; do bash -n "$f" || echo "ERROR $f"; done   # sintaxis bash
for f in scripts/php/*.php scripts/dev/*.php; do php -l "$f"; done
scripts/be-catalog-check.sh                                   # ids de opciones citados en los .md existen en el catálogo
scripts/dev/check-citations.sh --show                         # cada `betheme/x.php:N` existe (imprime la línea para revisarla)
```

`check-citations.sh` busca `betheme/` y `temini/` en la raíz del repo (`../../..` desde `scripts/dev/`); con `--src=/ruta` se puede
apuntar a otra copia del tema (por ejemplo una descargada del servidor).

## 2. Comprobaciones en un sitio (solo lectura)

```bash
S=/ruta/scripts; cd /ruta/wp
$S/be-doctor.sh                                   # versión, static-css, hold-cache, dirs, registro, revisiones, packs, plantillas
$S/be-opt-get.sh all=1 | head -50
$S/be-static-css-regenerate.sh --check            # ¿static.css al día?
$S/be-icons-pack-list.sh
$S/be-template-list.sh
$S/be-options-revisions.sh
$S/be-catalog-generate.sh out=/tmp/ref && diff /tmp/ref/catalogo-opciones.json reference/catalogo-opciones.json | head
```

Si el `diff` del catálogo no es vacío, la versión de Betheme del sitio no coincide con la documentada: regenera `reference/` desde el
sitio de referencia y revisa los docs afectados.

## 3. Protocolo de pruebas de escritura (reversibles)

Siempre en un entorno de desarrollo. Antes de nada:

```bash
cd /ruta/wp
wp db export ~/pre-pruebas.sql
$S/be-options-export.sh out=~/pre-pruebas.json
```

Cada prueba: `--dry-run` primero, luego ejecutar, comprobar el efecto (`be-opt-get`, `curl -s` del front, md5 de `static.css`) y revertir.

| Script | Prueba | Comprobación | Reversión |
|---|---|---|---|
| `be-opt-set` | `key=logo-height value=61` | `be-opt-get key=logo-height`; `#logo` en el HTML lleva `data-height="61"`; md5 de `static.css` cambia | `be-options-revisions type=backup ts=<último> --restore` |
| `be-opt-set --json` | `key=font-size-h1 value='{…}'` | `static.css` contiene el nuevo tamaño | ídem |
| `be-logo-set` | `main=/tmp/logo-test.png` | `logo-img` termina en `#<id>`; `wp post get <id>` | restaurar revisión + `wp post delete <id> --force` |
| `be-favicon-set` | `favicon=/tmp/fav.png` | `<link rel="shortcut icon">` en el HTML | ídem |
| `be-font-custom-add` / `-remove` | fuente de prueba | `@font-face` inline en el HTML; slot libre tras remove | remove + revisión |
| `be-icons-pack-add` / `-remove` | pack de prueba propio | carpeta + 6 metas + `<link id="mfn-custom-icons-…">` en el footer; tras remove no queda carpeta | remove --yes |
| `be-template-assign` / `-unassign` | footer de prueba `everywhere=1` | `wp option get mfn_footer_entire_site` | unassign (restaura el JSON de backup) |
| `be-tools-regenerate-css` | sin args | ficheros `uploads/betheme/css/post-<ID>.css` con mtime nuevo | ninguna necesaria (derivado) |
| `be-tools-bebuilder-data` | `op=rerender` | `betheme_form_uid` cambia; el JS se regenera al abrir el builder | ninguna |
| `be-cache-htaccess` | `mode=status` (on/off solo si `hold-cache` se usa) | bloque `# BEGIN BETHEME` presente/ausente; `.htaccess.bak-*` creado | `mode=on`/`off` inverso o restaurar el `.bak` |
| `be-purchase-code-set` | `--status` | muestra registrado/oculto/historial | — |
| `be-temini-popup-set` | `--list`, luego `template=… pages=…` sobre una página de prueba | transient borrado, popup visible en esa página | `pages=` vacío |
| `be-temini-legal-set` | `--show`, luego un campo | `wp post meta get` | volver a escribir el valor anterior |

Al terminar:

```bash
$S/be-options-export.sh out=~/post-pruebas.json
diff <(jq -S 'del(.last_tab)' ~/pre-pruebas.json) <(jq -S 'del(.last_tab)' ~/post-pruebas.json)   # debe estar vacío
# si no lo está:
wp db import ~/pre-pruebas.sql
```

## 4. Destructivos: no se prueban en dev compartido

`be-options-reset`, `be-options-import` (modo reemplazo) y `be-demo-import` solo se han validado sintácticamente y leyendo el código
que replican. Probarlos únicamente en una instalación desechable (por ejemplo `wp core download` + MySQL en Docker con `betheme/` y
`temini/` copiados) y siempre con `wp db export` previo.

## 5. Matriz de seguridad

| Clase | Qué garantiza | Flags |
|---|---|---|
| read-only | no escribe en BD ni en disco (salvo ficheros de salida que indiques con `out=`) | — |
| reversible | el estado anterior queda recuperable: revisión en `betheme_revision_backup` (opciones), `.bak-<fecha>` (ficheros), JSON de backup (options de plantillas) | `--dry-run` obligatorio de soportar |
| destructivo | sin vuelta atrás automática | `--yes` obligatorio; `be-demo-import` además `really=1` y `wp db export` previo |

## 6. Estado de las pruebas

Pruebas ejecutadas el 2026-09-07 en un entorno de desarrollo real (Betheme 28.4.3, Témini 1.1.0, wp-cli 2.12.0, PHP 8.3, WPML 4.9.7, FlyingPress activo). Backup previo con `wp db export` y `be-options-export`; al final `be-options-import in=pre.json --yes` dejó la opción `betheme` idéntica (diff vacío) y el front en HTTP 200.

| Script | Resultado |
|---|---|
| `be-doctor`, `be-opt-get`, `be-opt-set`, `be-opt-unset`, `be-static-css-regenerate --check`, `be-catalog-generate` | OK |
| `be-logo-set`, `be-favicon-set` | OK (`URL#id`, checkbox con `post-meta`) |
| `be-font-custom-add/remove`, `be-font-role-set` | OK; `be-fonts-local-regenerate` solo `--dry-run` |
| `be-icons-pack-list/add/remove` | OK tras corregir `remove` (cargar `Mfn_Helper` antes de `wp_trash_post`) |
| `be-tools-regenerate-css`, `be-tools-bebuilder-data op=rerender\|items`, `be-cache-htaccess mode=status` | OK; `op=rewrite`, `mode=on/off` y `be-tools-regenerate-thumbnails` solo `--dry-run` |
| `be-options-export/revisions/import`, `be-options-reset --dry-run` | OK; `reset --yes` no ejecutado |
| `be-purchase-code-set --status`, `be-plugins-required` | OK (solo lectura) |
| `be-template-list/assign/unassign` (+ `restore=@backup`) | OK con `--user=webmaster` |
| `be-demo-import --list`, `--dry-run` | OK; importación real no ejecutada |
| `be-temini-popup-set`, `be-temini-legal-set` | OK |

Incidencias encontradas y corregidas durante las pruebas:

1. `icons-pack-remove.php`: el hook `trashed_post` de Betheme llama a `Mfn_Helper::filesystem()`, clase que solo se carga en admin → fatal en CLI. Solución: `be_load_admin_classes()` antes de `wp_trash_post()`.
2. Comprobaciones del front con `curl`: el dev sirve páginas cacheadas (FlyingPress); hay que añadir una query aleatoria a la URL o vaciar la caché para ver los cambios.
3. Los scripts que instancian clases del builder (`be-template-*`, `be-tools-bebuilder-data op=items`) requieren `--user=<admin>` porque `bebuilder_access` depende del usuario actual.
