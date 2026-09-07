#!/usr/bin/env bash
# SAFETY: read-only
# be-catalog-generate — genera reference/catalogo-opciones.{json,md} desde la definición real de Theme Options
#
# Uso:
#   be-catalog-generate.sh out=/ruta/a/docs/betheme-ssh/reference
#   be-catalog-generate.sh                  (JSON por stdout)
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run catalog-generate "$@"
