#!/usr/bin/env bash
# SAFETY: reversible (rerender/items: fichero JS derivado; rewrite: guarda mfn-page-items-backup y exige --yes)
# be-tools-bebuilder-data — Tools > "Re-render Builder data" / "CSS update" / regenerar el JS de campos del BeBuilder
#
# Uso:
#   be-tools-bebuilder-data.sh op=rerender                  (borra bebuilder-<ver>.js + nuevo betheme_form_uid)
#   be-tools-bebuilder-data.sh op=items --user=admin        (regenera el JS ya; --user=<administrador> OBLIGATORIO)
#   be-tools-bebuilder-data.sh op=rewrite --dry-run
#   be-tools-bebuilder-data.sh op=rewrite --yes [ids=28,122] [--resume]
#
# Globales: --path=/ruta/wp  --url=...  --user=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run tools-bebuilder-data "$@"
