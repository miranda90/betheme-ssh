#!/usr/bin/env bash
# SAFETY: destructive (sustituye TODAS las Theme Options; revisión previa en betheme_revision_backup)
# be-options-import — importa un backup (JSON del feed, ###serialize###, serialize o base64) como el Import del panel
#
# Uso:
#   be-options-import.sh in=/ruta/backup.json --dry-run          (solo diff)
#   be-options-import.sh in=/ruta/backup.json --yes
#   be-options-import.sh in=@- --yes < backup.json
#   be-options-import.sh in=backup.json --merge --yes             (fusiona sobre lo actual)
#   be-options-import.sh in=backup.json --replace-url=https://viejo.com,https://nuevo.com --yes
#   be-options-import.sh ... --min-known=50 | --force | --no-static
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run options-import "$@"
