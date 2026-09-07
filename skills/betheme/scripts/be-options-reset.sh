#!/usr/bin/env bash
# SAFETY: destructive (todas las opciones vuelven a su std; revisión previa en betheme_revision_backup)
# be-options-reset — equivale a Backup & Reset → "Reset to default" + código r3s3t
#
# Uso:
#   be-options-reset.sh --dry-run
#   be-options-reset.sh --yes
#   be-options-reset.sh --keep=custom-css,custom-js --yes    (conserva el valor actual de esos ids)
#   be-options-reset.sh ... --no-static
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run options-reset "$@"
