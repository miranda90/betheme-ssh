#!/usr/bin/env bash
# SAFETY: read-only
# be-icons-pack-list — lista los packs de iconos (CPT icons): metas, carpeta en disco, <link> que imprime load_icons()
#
# Uso:
#   be-icons-pack-list.sh
#   be-icons-pack-list.sh --icons            (incluye los nombres de icono de cada pack)
#   be-icons-pack-list.sh --status=any       (también borradores/papelera)
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run icons-pack-list "$@"
