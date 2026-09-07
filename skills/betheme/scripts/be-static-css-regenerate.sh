#!/usr/bin/env bash
# SAFETY: read-only en BD; sobrescribe uploads/betheme/css/static.css
# be-static-css-regenerate — regenera static.css como al guardar Theme Options
#
# Uso:
#   be-static-css-regenerate.sh
#   be-static-css-regenerate.sh --check     (compara sin escribir)
#   be-static-css-regenerate.sh --print     (vuelca el CSS calculado)
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run static-css-regenerate "$@"
