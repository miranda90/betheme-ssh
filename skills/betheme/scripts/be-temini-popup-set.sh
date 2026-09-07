#!/usr/bin/env bash
# SAFETY: reversible (la salida muestra el valor anterior; vuelve a pasarlo en pages= para restaurar)
# be-temini-popup-set — asigna páginas a un popup (campo ACF `paginas_mostradas` de Témini) y vacía el transient del índice
#
# Uso:
#   be-temini-popup-set.sh --list                      lista popups, sus páginas y los transients presentes (solo lectura)
#   be-temini-popup-set.sh template=210 pages=28,95    asigna las páginas 28 y 95 al popup 210
#   be-temini-popup-set.sh template=210 pages=         quita la asignación (borra la meta)
#   be-temini-popup-set.sh ... --dry-run | --force
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run temini-popup-set "$@"
