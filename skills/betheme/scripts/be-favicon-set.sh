#!/usr/bin/env bash
# SAFETY: reversible (revisión previa en betheme_revision_backup)
# be-favicon-set — favicon-img y apple-touch-icon (Theme Options → Global → General)
#
# Uso:
#   be-favicon-set.sh favicon=/tmp/favicon-32.png apple=/tmp/apple-touch-180.png
#   be-favicon-set.sh favicon=https://sitio.com/wp-content/uploads/favicon.ico        (URL ya subida)
#   be-favicon-set.sh clear=1        (vacía ambos → manda el Site Icon de WP si existe, si no images/favicon.ico)
#   be-favicon-set.sh ... --dry-run | --no-static
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run favicon-set "$@"
