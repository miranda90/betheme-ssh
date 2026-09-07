#!/usr/bin/env bash
# _env.sh — entorno común de los wrappers be-*.sh (se hace `source`, no se ejecuta).
#
# Resuelve dónde está WordPress y wp-cli y traduce los argumentos del wrapper al
# formato posicional `clave=valor` que aceptan los scripts PHP vía `wp eval-file`.
#
# Variables de entorno reconocidas:
#   WP_BIN   binario de wp-cli (por defecto `wp`; alternativa `php /ruta/wp-cli.phar`)
#   WP_PATH  raíz de WordPress (si no se pasa --path=... y no estamos dentro de la instalación)
#
# Argumentos que entienden todos los wrappers:
#   --path=/ruta/wp   --url=...   --user=...     se pasan tal cual a wp-cli (globales)
#   --yes             confirma operaciones destructivas       → yes=1
#   --dry-run         muestra qué cambiaría sin escribir       → dry-run=1
#   --json            interpreta `value` como JSON             → json=1
#   --clave=valor     cualquier otro flag                      → clave=valor
#   clave=valor       formato nativo, se pasa sin tocar
#
# Compatible con bash 3.2 (macOS) y bash 4/5 (Linux).

set -euo pipefail

BE_SCRIPTS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BE_PHP_DIR="$BE_SCRIPTS_DIR/php"
WP_BIN="${WP_BIN:-wp}"

be_die() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }

# be_run <nombre-php-sin-extension> [args...]
be_run() {
	local name="$1"; shift
	local php_file="$BE_PHP_DIR/$name.php"
	[[ -f "$php_file" ]] || be_die "no existe $php_file"

	local wpargs=() args=() has_path=0 a
	for a in "$@"; do
		case "$a" in
			--path=*)            wpargs+=("$a"); has_path=1 ;;
			--url=*|--user=*|--debug|--quiet|--skip-plugins|--skip-plugins=*)
			                     wpargs+=("$a") ;;
			--skip-themes*)      be_die "--skip-themes desactiva Betheme; los scripts no pueden funcionar sin el tema cargado" ;;
			--yes)               args+=("yes=1") ;;
			--dry-run)           args+=("dry-run=1") ;;
			--json)              args+=("json=1") ;;
			--*=*)               args+=("${a#--}") ;;
			--*)                 args+=("${a#--}=1") ;;
			*)                   args+=("$a") ;;
		esac
	done

	if [[ $has_path -eq 0 && -n "${WP_PATH:-}" ]]; then
		wpargs+=("--path=$WP_PATH")
	fi

	# ${arr[@]+"${arr[@]}"} evita el error "unbound variable" de bash 3.2 con arrays vacíos
	exec $WP_BIN ${wpargs[@]+"${wpargs[@]}"} eval-file "$php_file" ${args[@]+"${args[@]}"}
}

# be_usage: imprime la cabecera de comentarios del wrapper que lo llama (líneas que empiezan por "# ")
be_usage() {
	sed -n '2,/^[^#]/p' "$1" | sed -n 's/^# \{0,1\}//p'
}
