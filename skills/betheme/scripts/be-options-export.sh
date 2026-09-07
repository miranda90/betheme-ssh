#!/usr/bin/env bash
# SAFETY: read-only
# be-options-export — exporta Theme Options en JSON idéntico al feed ?feed=mfn-opts-betheme (con mfn-opts-backup=1)
#
# Uso:
#   be-options-export.sh > backup.json
#   be-options-export.sh out=/ruta/backup.json
#   be-options-export.sh --strip                 (quita last_tab)
#   be-options-export.sh --backup-flag=0         (sin la marca mfn-opts-backup, como `wp option get betheme --format=json`)
#   be-options-export.sh --compact
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run options-export "$@"
