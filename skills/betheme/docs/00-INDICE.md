# Betheme por SSH — índice

Documentación de **todo lo que permite el panel de administración de Betheme 28.4.3** (y lo que añade el tema hijo
Témini 1.1.0) y de cómo reproducir cada operación **desde consola** con wp-cli, `wp eval-file` y el sistema de ficheros,
sin entrar en wp-admin. Objetivo: poder configurar y actualizar un sitio Témini entero por SSH.

Toda afirmación cita el fichero y la línea del código de Betheme 28.4.3 (`betheme/…:N`) o de Témini (`temini/…:N`).
Si actualizas Betheme, regenera el catálogo (`be-catalog-generate.sh`) y pasa `scripts/dev/check-citations.sh`.

## Documentos

| Doc | Qué cubre |
|---|---|
| [01-almacenamiento-y-guardado.md](01-almacenamiento-y-guardado.md) | Dónde vive Theme Options (`wp_options.betheme`), `mfn_opts_get()`, ciclo de guardado del panel vs escritura por SSH, efectos secundarios (static.css, caché, BeBuilder), otras options/transients, WPML |
| [02-tipos-de-campo-y-valores.md](02-tipos-de-campo-y-valores.md) | Forma exacta del valor guardado por cada tipo de campo del panel, con ejemplos JSON; cómo pasar arrays |
| [03-theme-options-por-pestaña.md](03-theme-options-por-pestaña.md) | Recorrido de las 21 pestañas del panel con las opciones de uso real y su receta |
| [04-logos-y-favicon.md](04-logos-y-favicon.md) | Logo, retina, sticky, móvil, texto, favicon, apple-touch-icon; formato `URL#id`; Header Builder |
| [05-fuentes.md](05-fuentes.md) | Familia por rol, tamaños, fuentes propias (`font-custom*`), Google Fonts (CDN/local/off), `@font-face`, trampa static-css |
| [06-iconos.md](06-iconos.md) | Packs de iconos personalizados (CPT `icons`): metas, disco, alta/baja, packs built-in |
| [07-herramientas-tools.md](07-herramientas-tools.md) | Betheme > Tools: regenerar CSS local, static.css, fuentes locales, miniaturas, JS del BeBuilder, caché .htaccess |
| [08-export-import-reset-revisiones.md](08-export-import-reset-revisiones.md) | Exportar/importar/resetear Theme Options, revisiones `betheme_revision_*`, migración entre entornos |
| [09-registro-updates-plugins.md](09-registro-updates-plugins.md) | Purchase code, actualizaciones del tema, Status, plugins requeridos (TGMPA) |
| [10-plantillas-header-footer-popups.md](10-plantillas-header-footer-popups.md) | CPT `template`: Header/Footer Builder, popups, condiciones y las options `mfn_<tipo>_*` que generan |
| [11-cpts-y-demo-import.md](11-cpts-y-demo-import.md) | CPTs de Betheme y sus metas; importador de demos (DESTRUCTIVO) |
| [12-temini-ajustes-propios.md](12-temini-ajustes-propios.md) | Lo que añade Témini: ACF, proyectos, popup por página, metas legales, white-label, enqueue |
| [13-verificacion-y-pruebas.md](13-verificacion-y-pruebas.md) | Protocolo de pruebas de los scripts, matriz de seguridad, cómo revertir |
| [reference/catalogo-opciones.md](reference/catalogo-opciones.md) | **GENERADO.** Los 829 campos de Theme Options por pestaña/sección: id, tipo, forma del valor, default. (JSON: `reference/catalogo-opciones.json`) |

## Cómo se usan los scripts

```bash
# en el servidor, dentro de la instalación de WordPress (o con --path=/ruta/wp o export WP_PATH=/ruta/wp)
cd ~/public_html
S=/ruta/a/temini/docs/betheme-ssh/scripts     # o cópialos donde quieras: son autocontenidos

$S/be-doctor.sh                                # empieza siempre por aquí
$S/be-opt-get.sh key=logo-img
$S/be-opt-set.sh key=logo-height value=80 --dry-run
$S/be-opt-set.sh key=logo-height value=80
```

- Cada `be-<op>.sh` es un wrapper que llama a `wp eval-file scripts/php/<op>.php clave=valor …` con WordPress y Betheme cargados.
- Flags comunes: `--dry-run` (muestra el cambio sin escribir), `--yes` (confirma destructivos), `--json` (el `value` es JSON),
  `--path=/ruta/wp`, `--help`. Cualquier `--clave=valor` se pasa como `clave=valor`. Valores largos: `clave=@fichero` o `clave=@-` (stdin).
- Salida: JSON por stdout; avisos y errores por stderr. Código de salida ≠ 0 si falla.
- **Nunca `wp --skip-themes`**: sin Betheme cargado no existe `$MFN_Options` ni `mfn_opts_get()`.
- Requisitos: wp-cli ≥ 2.5, PHP ≥ 7.4 en CLI, permisos de escritura en `wp-content/uploads/betheme/`.
- Sin wp-cli: ver la alternativa con `wp-load.php` en [01](01-almacenamiento-y-guardado.md).

### Lo que hace cada escritura de opciones (y el panel también hace)

Escribir `wp_options.betheme` directamente con `wp option patch` **no** regenera `static.css`, ni el JS del BeBuilder, ni vacía cachés
(el panel solo lo hace al recargar con `?settings-updated`; `betheme/muffin-options/options.php:88-90`). Todos los scripts que escriben opciones
pasan por `be_opts_save()` (`scripts/php/_lib.php`), que replica el guardado del panel: revisión previa en `betheme_revision_backup`
(máx. 5), `update_option`, regeneración de `uploads/betheme/css/static.css`, borrado del JS cacheado del BeBuilder y flush de caché.
Si usas `wp option patch` a pelo, ejecuta después `be-static-css-regenerate.sh`.

## Recetas rápidas

| Tarea | Doc | Script | Seguridad |
|---|---|---|---|
| Diagnóstico del sitio | 13 | `be-doctor.sh` | RO |
| Leer una opción / por prefijo / todas | 01, 02 | `be-opt-get.sh key=… \| prefix=… \| all=1 [--meta] [--format=raw]` | RO |
| Cambiar una opción escalar | 02, 03 | `be-opt-set.sh key=… value=…` | reversible |
| Cambiar una opción array (typography, checkbox, social…) | 02 | `be-opt-set.sh key=… --json value='{…}'` | reversible |
| Cambiar muchas opciones a la vez | 02 | `be-opt-set.sh set=@cambios.json` | reversible |
| Eliminar una clave | 01 | `be-opt-unset.sh key=…` | reversible |
| Regenerar static.css | 07 | `be-static-css-regenerate.sh [--check \| --print]` | RO-ish |
| Caché .htaccess (hold-cache) | 07 | `be-cache-htaccess.sh mode=status\|on\|off [--with-option]` | reversible |
| Cambiar logo / retina / sticky / móvil | 04 | `be-logo-set.sh main=… retina=… height=… link=…` / `clear=1` | reversible |
| Cambiar favicon / apple-touch-icon | 04 | `be-favicon-set.sh favicon=… apple=…` | reversible |
| Asignar fuente a un rol (body, h1-h4, menú…) | 05 | `be-font-role-set.sh role=… font=…` / `mode=local\|disabled` | reversible |
| Añadir / quitar fuente propia (woff/ttf) | 05 | `be-font-custom-add.sh name=… woff=… ttf=…` / `be-font-custom-remove.sh name=… [--delete-media]` | reversible |
| Google Fonts en local (regenerar) | 05 | `be-fonts-local-regenerate.sh --yes` | destructivo (borra `uploads/betheme/fonts`) |
| Listar / añadir / quitar pack de iconos | 06 | `be-icons-pack-list.sh` / `be-icons-pack-add.sh zip=… name=… prefix=…` / `be-icons-pack-remove.sh id=… --yes` | RO / reversible / destructivo |
| Regenerar CSS local de páginas del builder | 07 | `be-tools-regenerate-css.sh [ids=…]` | reversible |
| Regenerar miniaturas | 07 | `be-tools-regenerate-thumbnails.sh --yes` (o `wp media regenerate`) | destructivo |
| Re-render / rewrite de datos del BeBuilder | 07 | `be-tools-bebuilder-data.sh op=rerender` / `op=items --user=<admin>` / `op=rewrite --yes` | reversible |
| Exportar Theme Options | 08 | `be-options-export.sh out=backup.json` | RO |
| Importar Theme Options | 08 | `be-options-import.sh in=backup.json [--merge] [--replace-url=a,b] --yes` | destructivo (revisión previa) |
| Reset a valores de fábrica | 08 | `be-options-reset.sh --yes [--keep=…]` | destructivo (revisión previa) |
| Listar / ver / restaurar revisiones | 08 | `be-options-revisions.sh` / `type=backup ts=… [--restore]` | RO / reversible |
| Registrar purchase code / ver estado | 09 | `be-purchase-code-set.sh --status` / `code=… [--remote]` | RO / reversible |
| Plugins requeridos | 09 | `be-plugins-required.sh [--install]` | RO / reversible |
| Listar plantillas (header/footer/popup…) | 10 | `be-template-list.sh [type=…] [--used]` | RO |
| Asignar / desasignar header, footer, popup… | 10 | `be-template-assign.sh id=… everywhere=1 --user=<admin>` / `be-template-unassign.sh id=… --user=<admin>` / `restore=@backup.json` | reversible |
| Importar una demo | 11 | `be-demo-import.sh --list` / `demo=… --dry-run` / `demo=… --yes really=1 --user=<admin>` | **destructivo** |
| Popup por página (Témini) | 12 | `be-temini-popup-set.sh --list` / `template=… pages=…` | reversible |
| Metas del aviso legal / accesibilidad (Témini) | 12 | `be-temini-legal-set.sh post=… --show` / `campo=valor` | reversible |
| Regenerar el catálogo de opciones | reference | `be-catalog-generate.sh out=…/reference` | RO |
| Comprobar ids citados en los docs | 13 | `be-catalog-check.sh` | RO |
| Comprobar citas fichero:línea | 13 | `scripts/dev/check-citations.sh --show` | RO |

Clases de seguridad: **RO** = no escribe nada; **reversible** = deja el estado anterior recuperable (revisión de opciones, backup de fichero)
y admite `--dry-run`; **destructivo** = exige `--yes` (y en `be-demo-import` además `really=1`); haz `wp db export` antes.
Cada wrapper imprime su ayuda con `--help`.

### Notas de uso comprobadas en el dev

- **`--user=<admin>`** es necesario (úsalo siempre) en `be-template-*`, `be-tools-bebuilder-data op=items` y `be-demo-import`: el acceso al builder
  (`bebuilder_access`) depende del usuario actual y sin él el CPT `template` ni `Mfn_Builder_Admin` están disponibles en CLI.
- **Caché de página**: si el sitio usa FlyingPress, LiteSpeed, WP Rocket… un `curl` del front devuelve la copia cacheada. Para comprobar un
  cambio añade una query (`curl -s "https://sitio/?x=$RANDOM"`) o vacía la caché del plugin.
- Las clases de `functions/admin/` de Betheme (`Mfn_Helper`, importador, TGMPA…) **no se cargan en CLI**; los scripts las cargan con
  `be_load_admin_classes()`. Si escribes tu propio `wp eval`, haz lo mismo o los hooks del tema (p. ej. borrar un pack de iconos) fallarán.
- Los `checkbox` del panel guardan siempre una clave extra `post-meta => "1"` además de las opciones marcadas; los scripts la conservan.

## Fuera de alcance

- El **contenido** de las páginas y plantillas del BeBuilder (JSON de secciones/wraps/items, opciones `css_*` de cada elemento):
  está documentado en `~/Documents/Mis proyectos/muffinAI/docs/bebuilder/` (`00-INDICE.md`). Aquí solo se cubre cómo se almacena y se asigna una plantilla.
- Elementor, WPBakery, Revolution Slider: solo lo que Betheme configura sobre ellos.
