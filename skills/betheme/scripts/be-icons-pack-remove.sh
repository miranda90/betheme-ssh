#!/usr/bin/env bash
# SAFETY: destructive (borra la carpeta uploads/betheme/icons/<name-parsed> y el post; exige --yes)
# be-icons-pack-remove — elimina un pack de iconos vía wp_trash_post() → single_post_remove() del theme
#
# Uso:
#   be-icons-pack-remove.sh id=123                 (muestra qué carpeta borraría; no toca nada)
#   be-icons-pack-remove.sh id=123 --yes           (borra carpeta + post)
#   be-icons-pack-remove.sh id=123 --yes --keep-files   (solo el post, wp_delete_post(force); la carpeta queda)
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run icons-pack-remove "$@"
