#!/usr/bin/env bash
# SAFETY: read-only
# be-opt-get — lee opciones de Theme Options (wp_options.betheme)
#
# Uso:
#   be-opt-get.sh key=logo-img
#   be-opt-get.sh key=logo-img,retina-logo-img
#   be-opt-get.sh prefix=font-          --meta   (añade tipo, forma, std, sección)
#   be-opt-get.sh all=1
#   be-opt-get.sh key=logo-height --format=raw
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run opt-get "$@"
