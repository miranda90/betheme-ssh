# 09 — Registro (purchase code), updates, System Status y plugins

Cubre Betheme → Dashboard (registro del tema), las actualizaciones del tema desde `api.muffingroup.com`,
la pantalla System Status y Betheme → Plugins (TGMPA + premium), y cómo hacerlo todo por SSH.
Trampa de partida: **nada de esto se carga en wp-cli**. `functions.php:217-243` solo incluye
`functions/admin/*` (`Mfn_Update`, `Mfn_Dashboard`, `Mfn_Setup`, `Mfn_TGMPA`, `Mfn_Plugins`, `Mfn_Status`) con
`is_admin()`, que en CLI es `false` (verificado en el dev). Los helpers de `functions/theme-functions.php` sí están.

## 1. Purchase code

### 1.1 Dónde vive

| Dato | Almacén | Código |
|---|---|---|
| Purchase code | **site option** `envato_purchase_code_7758048` (string 36 chars `8-4-4-4-12`) | `betheme/functions/theme-functions.php:4074-4089` |
| Legacy (< 21.0.8) | `betheme_purchase_code` + `betheme_registered`: `mfn_get_purchase_code()` los migra y borra al leer | `theme-functions.php:4080-4084` |
| Registrado sí/no | `mfn_is_registered()` devuelve `strlen(code)` o `false` | `theme-functions.php:4061-4068` |
| Code oculto | `mfn_get_purchase_code_hidden()` → `xxxxxxxx-xxxx-****-****-************` (13 chars + máscara) | `theme-functions.php:4095-4104` |
| Caducidad soporte | site transient `betheme_expires` (1 semana; `-1` si la API falla) | `class-mfn-dashboard.php:176-186` |

El Dashboard muestra aviso "corrupted" si `mfn_is_registered() !== 36` (`betheme/functions/admin/class-mfn-dashboard.php:598`) y el de registro si no hay code y no es localhost (l.602).

### 1.2 Qué hace el panel al registrar

AJAX `mfn_setup_register` (`betheme/functions/admin/setup/class-mfn-setup.php:88`, handler `_register()` l.425-448, nonce `mfn-setup-register` l.432):

1. `validate($code)` (l.494-506): `trim` + regex `^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$` (l.502).
2. `register($code)` (l.514-560, `protected`): **POST a `https://api.muffingroup.com/register.php`** (`class-mfn-api.php:14`) con `body = {code, register: 1}` y `user-agent = WordPress/<ver>; <network_site_url>` (l.520-530). Si la respuesta no trae `success` → "Invalid purchase code" y **no guarda nada** (l.537-539).
3. Si `success`: `update_site_option('envato_purchase_code_7758048', $code)` (l.547), `set_site_transient('betheme_expires', $response['success'], WEEK_IN_SECONDS)` (l.548), borra `betheme_update_plugins`, `betheme_plugins`, `update_themes` y lanza `wp_update_themes` (l.552-556).

`deregister()` (l.565-604): POST con `deregister: 1` (l.586, respuesta ignorada), `update_site_option(..., '')` (l.590) y la misma purga de transients.

**Local vs remoto**: lo único local es la site option + transients. El POST a Muffin es lo que asocia el code a este dominio (`network_site_url()` va en el user-agent). Si escribes la option por SSH sin llamar a la API:
- Betheme se considera registrado (`mfn_is_registered()` solo mira la option) y los updates funcionan si el code es válido en el servidor de descargas.
- Pero `Mfn_Dashboard::__construct()` llama a `get_expiration()` (l.21, 158-168) en **cada carga de admin**; si `betheme_expires` no existe o caducó, `remote_get_expiration()` (l.192-232) vuelve a hacer el POST `register` y, si Muffin no devuelve `success`, **borra el code** (l.209-213) y purga transients. Es decir: un code inválido puesto por SSH dura hasta la siguiente visita al admin; uno válido queda registrado en Muffin en esa misma visita.

### 1.3 Receta: registrar / desregistrar por SSH

```bash
be-purchase-code-set.sh --status                      # RO: registrado, code oculto, versiones, historial, transients
be-purchase-code-set.sh code=XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX --dry-run
be-purchase-code-set.sh code=XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX --remote   # valida en api.muffingroup.com y guarda solo si success (como el panel)
be-purchase-code-set.sh code=XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX            # solo local: option + purga; Muffin lo sabrá al abrir el admin
be-purchase-code-set.sh --remove --yes [--remote] [--show-previous]          # desregistrar (imprime el anterior oculto; --show-previous entero)
```

wp puro (solo local; los site transients en single-site son options `_site_transient_*`):

```bash
wp option update envato_purchase_code_7758048 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'   # en multisite: wp site option update
wp eval 'foreach (["betheme_update","betheme_expires","betheme_plugins","betheme_update_plugins","update_themes"] as $t) delete_site_transient($t);'
wp eval 'var_dump(mfn_is_registered(), mfn_get_purchase_code_hidden());'
```

## 2. Updates del tema

### 2.1 Cómo funciona en el admin

`Mfn_Update` (`betheme/functions/admin/class-mfn-update.php`, instanciado al incluir el fichero, l.135):

| Paso | Código |
|---|---|
| Filtro `pre_set_site_transient_update_themes` (l.19): si `mfn_is_registered()` (l.38), consulta `remote_get_version()` y, si la instalada es menor (l.45), añade `response['betheme'] = {new_version, url: changelog, package: theme/download.php?code=<code>}` (l.51-55) | l.36-61 |
| Versión disponible: `Mfn_API::get_update_version()` → site transient `betheme_update` (1 h, l.153; `-1` si la API no responde, l.149), filtro `betheme_disable_theme_update` (l.130); remoto `GET https://api.muffingroup.com/theme/version.php` **sin code** (l.165-178) | `class-mfn-api.php:125-178` |
| `?forcecheck` en cualquier página de admin refresca el transient y redirige (l.184-194); con `&be-debug` imprime la respuesta cruda (l.97) | `class-mfn-api.php` |
| Historial: `upgrader_process_complete` (l.25, 70-98) añade `{time, version}` a la **site option** `betheme_updates_history` cuando el upgrader actualiza `betheme` | `class-mfn-update.php:80-91` |
| `autoupdate()` (l.104-131) leería `mfn_opts_get('automatic-updates')` (l.112), pero el filtro que lo engancha está comentado (l.22) y **`automatic-updates` no existe** en `theme-options.php` (no está en el catálogo): código muerto. Los auto-updates del tema se gestionan con el mecanismo estándar de WP (`auto_update_themes`) | |

`Mfn_Dashboard::refresh_transients()` (`class-mfn-dashboard.php:633-639`) es el "Refresh" del Dashboard: borra `betheme_update_plugins`, `betheme_plugins`, `update_themes` y lanza `wp_update_themes`.

### 2.2 Por qué `wp theme update betheme` no ve nada

En CLI `class-mfn-update.php` no se carga, el filtro no existe y `update_themes` solo trae lo de wordpress.org (verificado en el dev: `has_filter('pre_set_site_transient_update_themes') === false`, `response` sin `betheme`, aunque el transient `betheme_update` diga `28.5.9.1` y la instalada sea 28.4.3). Hay que cargar la clase en el mismo proceso que ejecuta el update:

```bash
# 1. Ver si hay update (RO). version_available viene de betheme_update o de la API si el transient caducó.
be-purchase-code-set.sh --status | jq '{version_installed, version_available, update_available, wp_update_transient}'

# 2. Actualizar: carga Mfn_Update, rellena update_themes con el package (con el code) y lanza el upgrader en el mismo proceso
wp eval '
require_once get_template_directory() . "/functions/admin/class-mfn-api.php";
require_once get_template_directory() . "/functions/admin/class-mfn-update.php";   // instancia $mfn_update → filtro activo
delete_site_transient("update_themes"); wp_update_themes();
$t = get_site_transient("update_themes");
if ( empty($t->response["betheme"]) ) { WP_CLI::error("Sin update disponible o tema no registrado."); }
WP_CLI::log("Nueva versión: " . $t->response["betheme"]["new_version"]);
WP_CLI::runcommand("theme update betheme", ["launch" => false]);   // mismo proceso: el filtro sigue enganchado
'
```

Con `launch => false` el `theme update` corre en el mismo PHP, así que `upgrader_process_complete` también anota `betheme_updates_history`. **[sin verificar]** en vivo (no se ejecutan escrituras en el dev): un `wp theme update betheme` en un proceso aparte justo después del `wp_update_themes()` de arriba probablemente también funcione (WP no vuelve a consultar si `last_checked` < 12 h), pero no lo garantizamos.

Antes de actualizar: `wp db export` y copia de `betheme/` (el upgrader borra el directorio). Témini es tema hijo: no se toca.

## 3. System Status

`Mfn_Status::set_status()` (`betheme/functions/admin/class-mfn-status.php:69-143`) solo lee. Comprueba (`$status`, l.97-115):

| Check | Umbral | Línea |
|---|---|---|
| `version` | la API devolvió versión (`$this->version > 0`) | 98 |
| `uploads` | `wp_is_writable(uploads basedir)` | 99 |
| `fs` | `Mfn_Helper::filesystem()` o `WP_Filesystem()` | 100 |
| `zip`, `curl`, `dom` | `ZipArchive`, ext `curl`, `DOMDocument` | 101, 107, 108 |
| `php` | ≥ 7.0 | 102 |
| `memory_limit` | ≥ 512 MB | 104 |
| `time_limit` | ≥ 180 s o 0 | 105 |
| `max_input_vars` | ≥ 5000 | 106 |
| `htaccess` | `.htaccess` legible y escribible | 109 |
| `siteurl` / `https_home` / `https_site` | mismo host en `home` y `siteurl`; ambos https | 117-139 |
| `version_history` | site option `betheme_updates_history` | 94 |

Por CLI, sin cargar la clase (sus propiedades son `private`), los mismos valores:

```bash
wp eval 'echo json_encode([
 "php"=>PHP_VERSION, "mysql"=>$GLOBALS["wpdb"]->db_version(),
 "memory_limit_ok"=>wp_convert_hr_to_bytes(ini_get("memory_limit"))>=536870912, "memory_limit"=>ini_get("memory_limit"),
 "time_limit_ok"=>(ini_get("max_execution_time")>=180||ini_get("max_execution_time")==0), "max_input_vars_ok"=>ini_get("max_input_vars")>=5000,
 "curl"=>extension_loaded("curl"), "dom"=>class_exists("DOMDocument"), "zip"=>class_exists("ZipArchive"),
 "uploads_writable"=>wp_is_writable(wp_get_upload_dir()["basedir"]), "htaccess_writable"=>is_writable(get_home_path().".htaccess"),
 "home"=>home_url(), "siteurl"=>get_option("siteurl"), "max_upload"=>size_format(wp_max_upload_size()),
 "history"=>get_site_option("betheme_updates_history")], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);'
```

(`max_execution_time` en CLI suele ser 0: mira el de PHP-FPM con `php-fpm -i` o `wp eval` desde una petición web si importa.)

## 4. Plugins (Betheme → Plugins / Install Plugins)

### 4.1 De dónde sale la lista

`Mfn_TGMPA` (`betheme/functions/admin/tgm/class-mfn-tgmpa.php`, solo se define si ningún plugin cargó ya `TGM_Plugin_Activation`, l.6):

- Lista estática (l.15-58): `contact-form-7`, `duplicate-post`, `elementor`, `leadin` (HubSpot), `woocommerce`; todos `required => false` (ninguno es obligatorio para el tema).
- Premium desde la API (`get_plugins_list()` l.127-142 → `update_plugins_list()` l.145-176): `GET plugins/version.php` (l.156), a cada uno le añade `source = plugins/download.php?code=<code>&plugin=<slug>` (l.169), cachea en site transient `betheme_plugins` (1 h, l.174) y marca `betheme_update_plugins` para no repetir en 1 h (l.148-153). En el dev: `LayerSlider 8.4.0`, `revslider 6.7.58`, `js_composer 9.0.1`, `becustom 1.2.4`.
- Registro en TGMPA con `is_automatic => true` (l.94): instalar = instalar + activar. `file_path` se calcula buscando `<slug>/` entre `get_plugins()` (`class-tgm-plugin-activation.php:1394, 1551-1561`).
- Estado por plugin en la pantalla (`betheme/functions/admin/class-mfn-plugins.php:119-146`): activo → `update` si la versión de la API es mayor; instalado inactivo → `activate`; si no → `install`. Los premium sin registro no muestran botón (`templates/plugins.php:82`).

### 4.2 Receta

```bash
be-plugins-required.sh                                   # RO: name, slug, premium, path, installed, active, versiones, action
be-plugins-required.sh --install --dry-run               # imprime los `wp plugin install/activate` que ejecutaría
be-plugins-required.sh --install --only=contact-form-7,duplicate-post
be-plugins-required.sh --install --activate=0            # sin activar
be-plugins-required.sh --install --update                # además `wp plugin update` de los desfasados
```

wp puro: `wp plugin install contact-form-7 duplicate-post --activate`. Premium: `wp plugin install "https://api.muffingroup.com/plugins/download.php?code=<code>&plugin=revslider" --activate` (la URL exacta la da `--install --dry-run` con el code enmascarado; el script la usa completa). Deshacer: `wp plugin deactivate <slug> && wp plugin uninstall <slug>`.

## 5. Transients y options de esta área

| Clave | Ámbito | Contenido | Caducidad / quién la crea |
|---|---|---|---|
| `envato_purchase_code_7758048` | site option | purchase code | `Mfn_Setup::register()` |
| `betheme_updates_history` | site option | `[{time, version}, …]` | `Mfn_Update::upgrader_process_complete()` |
| `betheme_promo`, `betheme_promo_closed` | site option | banner promo de Muffin / versión cerrada | `class-mfn-dashboard.php:289-291, 148` |
| `betheme_update` | site transient | versión disponible o `-1` | 1 h, `Mfn_API::refresh_update_version()` |
| `betheme_expires` | site transient | fecha de soporte o `-1` (en el dev vale `"1"`) | 1 semana, `Mfn_Dashboard::refresh_expiration()` |
| `betheme_plugins` | site transient | lista premium con `source` (lleva el code) | 1 h, `Mfn_TGMPA::update_plugins_list()` |
| `betheme_update_plugins` | site transient | `1` = ya consultado | 1 h |
| `betheme_promo_version` | site transient | versión del promo | 1 día, `class-mfn-dashboard.php:265` |
| `betheme_survey_25` | site transient | encuesta cerrada | 1 año, l.80 |

Purgar todo lo de Betheme: `be-purchase-code-set.sh --status` muestra el estado; para forzar refresco `wp eval 'foreach (["betheme_update","betheme_expires","betheme_plugins","betheme_update_plugins","betheme_promo_version"] as $t) delete_site_transient($t);'` (la próxima carga de admin lo rellena).

## 6. Trampas verificadas

1. **CLI no carga `functions/admin/*`** (`functions.php:217`): `Mfn_API`, `Mfn_Update`, `Mfn_TGMPA`… hay que hacer `require_once` a mano (los scripts lo hacen); `Mfn_Setup` y `Mfn_Dashboard` tienen constructores con efectos (consultas, POST a Muffin): no instanciarlos.
2. **`wp theme update betheme` a secas no actualiza** (sección 2.2).
3. **Code escrito por SSH sin `--remote`**: válido → Muffin lo aprende en la siguiente carga de admin; inválido → el Dashboard lo borra (l.209-213). `--remote` reproduce exactamente el flujo del panel.
4. `remote_get_expiration()` también **borra el code si la API no responde** (`is_wp_error`, l.209): un corte de red o un firewall saliente puede desregistrar el tema. Guarda el code fuera de WP.
5. `Mfn_Setup::validate()` solo comprueba el formato; que el code sea de Betheme lo decide Muffin.
6. `betheme_expires` y `betheme_update` pueden valer `-1` (string en BD): no es "sin registrar", es "API sin respuesta".
7. `Mfn_TGMPA` no existe si otro plugin ya cargó TGMPA (l.6): `be-plugins-required` cae a lista estática + transient `betheme_plugins`.
8. En multisite todo es `get_site_option`/`site transient`: `wp option` no lo ve, usa `wp site option` / los scripts.

## Estado en el dev

Probado el 2026-09-07 en un entorno de desarrollo real (entorno, protocolo y tabla completa de resultados: [13 §6](13-verificacion-y-pruebas.md#6-estado-de-las-pruebas)).

- `be-purchase-code-set --status`: registrado, `betheme_update` = 28.5.9.1 disponible frente a 28.4.3 instalada, historial de 5 actualizaciones, y confirma que en CLI `update_themes` no contiene Betheme (`class-mfn-update.php` no se carga fuera de admin).
- `be-plugins-required`: lista TGMPA con estado real (Contact Form 7 no instalado, Duplicate Post 4.7 activo…). No se instaló nada.
- No se probó `code=` ni `--remove` para no tocar el registro del dev.
