#!/usr/bin/env bash
# SAFETY: reversible (be-icons-pack-remove.sh id=<ID> --yes deshace el alta)
# be-icons-pack-add — da de alta un pack IcoMoon como el campo upload_icon del admin (unzip + whitelist + prefijo + post + 6 metas)
#
# Uso:
#   be-icons-pack-add.sh zip=/ruta/icomoon.zip name="Mi Pack" prefix=mipack
#   be-icons-pack-add.sh dir=/ruta/carpeta-descomprimida name="Mi Pack" prefix=mipack
#   be-icons-pack-add.sh ... --dry-run                 (plan: nombre parseado, carpeta, ficheros que quedarían)
#   be-icons-pack-add.sh ... --no-prefix-rewrite       (el zip ya trae el prefijo definitivo en style.css)
#   be-icons-pack-add.sh ... --status=draft
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run icons-pack-add "$@"
