#!/usr/bin/env bash
# SAFETY: read-only (--status) | reversible (code= / --remove: imprime el code anterior oculto; --show-previous lo muestra entero)
# be-purchase-code-set — registro del tema (site option envato_purchase_code_7758048) y estado de updates
#
# Uso:
#   be-purchase-code-set.sh --status [--no-remote]
#   be-purchase-code-set.sh code=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx --dry-run
#   be-purchase-code-set.sh code=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx            (solo local: NO avisa a api.muffingroup.com)
#   be-purchase-code-set.sh code=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx --remote   (registra también en la API de Muffin)
#   be-purchase-code-set.sh --remove --yes [--remote] [--show-previous]
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run purchase-code-set "$@"
