#!/usr/bin/env bash
# SAFETY: reversible (backup JSON en uploads/betheme/backups/; revertir con be-template-assign.sh restore=@fichero)
# be-template-unassign — vacía las condiciones de un template y recompila las options mfn_<type>_* (deja de aplicarse)
#
# Uso:
#   be-template-unassign.sh id=93
#   be-template-unassign.sh id=93 --dry-run
#   be-template-unassign.sh id=93 --clear-post-overrides   (borra además los metas mfn_<type>_template=93 de los posts)
#
# Globales: --path=/ruta/wp  --url=...
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run template-unassign "$@"
