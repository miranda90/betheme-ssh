---
description: Administra el panel de administración de BeTheme (tema de WordPress) por SSH con wp-cli, sin entrar en wp-admin — Theme Options (todas las pestañas), logos, favicon, fuentes, packs de iconos, Tools (static.css, caché, regenerar CSS/fuentes/miniaturas, JS del BeBuilder), export/import/reset/revisiones de opciones, purchase code y actualizaciones, Header/Footer Builder y plantillas, CPTs, importador de demos, y los ajustes propios del tema hijo Témini. Úsalo cuando el usuario pida configurar, migrar, automatizar o depurar un sitio BeTheme/Témini por consola.
user-invocable: true
---

# BeTheme por SSH

Documentación exhaustiva (verificada línea a línea contra el código de BeTheme 28.4.3) y 32 scripts wp-cli
listos para usar, para hacer desde SSH todo lo que el panel de administración de BeTheme permite hacer
desde wp-admin: Theme Options, logos/favicon, fuentes, packs de iconos, Tools, export/import/reset,
registro y actualizaciones, Header/Footer Builder, CPTs, importador de demos, y los ajustes propios del
tema hijo Témini.

## 0. Antes de la primera vez en esta máquina

Al clonar/instalar desde git se pierde el bit de ejecución. Antes de llamar a cualquier script, una vez:

```bash
chmod +x "${CLAUDE_SKILL_DIR}"/scripts/*.sh "${CLAUDE_SKILL_DIR}"/scripts/dev/*.sh
```

A partir de ahí, todas las recetas de los docs (que asumen `S=.../scripts`) funcionan literalmente con:

```bash
S="${CLAUDE_SKILL_DIR}/scripts"
$S/be-doctor.sh --path=/ruta/a/wordpress
```

## 1. Cómo trabajar con este skill

1. **Lee primero** [`docs/00-INDICE.md`](docs/00-INDICE.md): tiene la tabla completa "tarea → doc → script → seguridad".
2. Localiza el doc de la sección concreta (`01`…`13`) para el detalle: dónde vive cada opción, forma exacta
   del valor, y la receta con el script correspondiente. Cada doc cita `fichero:línea` del código de BeTheme.
3. Ejecuta el script con `bash` (no `./`, ver §0) desde dentro de la instalación de WordPress, o con
   `--path=/ruta/wp`. Nunca `wp --skip-themes`: sin BeTheme cargado no existe `mfn_opts_get()`.
4. Antes de cualquier escritura, si el script admite `--dry-run`, úsalo primero y enseña el diff al usuario.
5. Si el script es **destructivo** (reset, import en modo reemplazo, importador de demos), exige `--yes`
   explícito del usuario en la conversación — no lo asumas — y recomienda `wp db export` antes.

## 2. Clases de seguridad de los scripts

| Clase | Qué garantiza |
|---|---|
| **read-only** | No escribe nada. |
| **reversible** | Cada escritura de opciones deja el estado anterior en `betheme_revision_backup` (recuperable con `be-options-revisions.sh --restore`); los ficheros derivados (`static.css`, `post-<ID>.css`) se regeneran sin pérdida; admite `--dry-run`. |
| **destructivo** | Sin vuelta atrás automática (`be-options-reset`, `be-options-import` en modo reemplazo, `be-demo-import`, `be-icons-pack-remove`, `be-fonts-local-regenerate`). Exige `--yes`. |

Detalle completo en [`docs/13-verificacion-y-pruebas.md`](docs/13-verificacion-y-pruebas.md).

## 3. Trampa a tener siempre presente

Escribir la opción `betheme` a pelo (`wp option patch`) **no** regenera `static.css`, ni el JS del BeBuilder,
ni vacía cachés — el panel solo lo hace al recargar con `?settings-updated`. Todos los scripts de este
paquete pasan por `be_opts_save()` (`scripts/php/_lib.php`), que sí lo replica. Si el usuario pide un
`wp option patch` manual, recuérdale ejecutar después `be-static-css-regenerate.sh`.

## 4. Índice de documentos

| Doc | Contenido |
|---|---|
| `01-almacenamiento-y-guardado.md` | Dónde vive Theme Options, ciclo de guardado del panel vs. SSH, efectos secundarios |
| `02-tipos-de-campo-y-valores.md` | Forma exacta del valor por tipo de campo, con ejemplos JSON |
| `03-theme-options-por-pestaña.md` | Recorrido de las 21 pestañas del panel con recetas |
| `04-logos-y-favicon.md` | Logo, retina, sticky, favicon, apple-touch-icon (`URL#id`) |
| `05-fuentes.md` | Familia por rol, fuentes propias, Google Fonts |
| `06-iconos.md` | Packs de iconos personalizados (CPT `icons`) |
| `07-herramientas-tools.md` | Betheme > Tools: static.css, caché, CSS local, fuentes, miniaturas, BeBuilder |
| `08-export-import-reset-revisiones.md` | Backup/restauración de Theme Options |
| `09-registro-updates-plugins.md` | Purchase code, actualizaciones, plugins requeridos |
| `10-plantillas-header-footer-popups.md` | Header/Footer Builder, condiciones, popups |
| `11-cpts-y-demo-import.md` | CPTs de BeTheme; importador de demos (DESTRUCTIVO) |
| `12-temini-ajustes-propios.md` | Solo si el tema hijo activo es Témini: ACF, popup por página, metas legales |
| `13-verificacion-y-pruebas.md` | Protocolo de pruebas, matriz de seguridad |
| `reference/catalogo-opciones.md` | **Generado.** Los ~829 campos de Theme Options por pestaña/sección |

Docs `01`–`11` y `13` son genéricos para cualquier sitio BeTheme 28.x. `12` solo aplica si el tema hijo
activo es Témini (compruébalo con `wp theme list` o `be-doctor.sh`); si es otro hijo, ignóralo.

## 5. Si la versión de BeTheme del sitio no es la documentada

Todo se verificó contra BeTheme 28.4.3. Si `be-doctor.sh` reporta otra versión, los conceptos generales
(almacenamiento en `wp_options.betheme`, `mfn_opts_get()`, tipos de campo) siguen valiendo, pero antes de
citar un `fichero:línea` concreto a ese código, regenera el catálogo real del sitio:

```bash
$S/be-catalog-generate.sh out="${CLAUDE_SKILL_DIR}/docs/reference"
```

y contrasta contra él en vez de fiarte ciegamente de las líneas citadas en los docs.

## 6. Lo que no cubre este skill

El contenido del BeBuilder (JSON de secciones/wraps/items de páginas) no está aquí: es un dominio propio
de generación de contenido, no de administración del panel. Este skill es solo para el **panel de
administración** de BeTheme y sus ajustes.
