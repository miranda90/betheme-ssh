#!/usr/bin/env bash
# SAFETY: destructive (sobrescribe miniaturas y _wp_attachment_metadata de todos los adjuntos; exige --yes)
# be-tools-regenerate-thumbnails — Tools > "Regenerate Thumbnails" (incluye width/height de SVG)
#   Alternativa nativa: `wp media regenerate --yes` (no trata los SVG).
#
# Uso:
#   be-tools-regenerate-thumbnails.sh --dry-run
#   be-tools-regenerate-thumbnails.sh --yes
#   be-tools-regenerate-thumbnails.sh --yes ids=12,34
#   be-tools-regenerate-thumbnails.sh --yes --resume     (continúa desde la option be_regenerate_thumbnails)
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run tools-regenerate-thumbnails "$@"
