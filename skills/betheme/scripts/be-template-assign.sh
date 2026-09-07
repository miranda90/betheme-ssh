#!/usr/bin/env bash
# SAFETY: reversible (backup JSON de las options/metas mfn_<type>_* en uploads/betheme/backups/ + restore=@fichero)
# be-template-assign — asigna condiciones a un template (header/footer/popup/single-* y archive-*/cart…) y recompila las options mfn_<type>_*
#
# Uso:
#   be-template-assign.sh id=93 everywhere=1                       (header/footer/popup: todo el sitio)
#   be-template-assign.sh id=93 singular=page,post archive=product search=1
#   be-template-assign.sh id=93 exclude-singular=product:12         (excluye singulars de la product_cat 12)
#   be-template-assign.sh id=175 all=1                              (single-* y archive-*: todos)
#   be-template-assign.sh id=128 tax=portfolio-types:15             (archive-portfolio: solo el término 15)
#   be-template-assign.sh id=200 used=1                             (cart|checkout|thanks|search: activar)
#   be-template-assign.sh id=93 cond=@conditions.json               (JSON literal de mfn_template_conditions)
#   be-template-assign.sh id=93 type=header everywhere=1 --publish  (fija el tipo si falta; publica si es draft)
#   be-template-assign.sh id=93 everywhere=1 --dry-run
#   be-template-assign.sh restore=@/ruta/uploads/betheme/backups/template-header-YYYYmmdd-HHMMSS.json
#
# Globales: --path=/ruta/wp  --url=...  --user=<admin> (recomendado con WPML/Polylang)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run template-assign "$@"
