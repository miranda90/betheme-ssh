#!/usr/bin/env bash
# SAFETY: reversible (solo sobrescribe uploads/betheme/css/post-<ID>.css, ficheros derivados de la meta)
# be-tools-regenerate-css — Tools > Local CSS > "Regenerate files": post-<ID>.css desde mfn-page-local-style
#
# Uso:
#   be-tools-regenerate-css.sh                    (todos los publicados con mfn-page-local-style, como el botón)
#   be-tools-regenerate-css.sh ids=28,122,175     (solo esos posts)
#   be-tools-regenerate-css.sh --preview          (también post-<ID>-preview.css)
#   be-tools-regenerate-css.sh --dry-run
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run tools-regenerate-css "$@"
