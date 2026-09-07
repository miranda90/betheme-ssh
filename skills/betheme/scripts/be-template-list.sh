#!/usr/bin/env bash
# SAFETY: read-only
# be-template-list — lista los templates de Betheme (CPT template): tipo, estado, condiciones y qué options/metas apuntan a cada uno
#
# Uso:
#   be-template-list.sh                       (todos)
#   be-template-list.sh type=header           (header|footer|popup|sidemenu|megamenu|single-post|archive-portfolio|cart|…)
#   be-template-list.sh --used                (solo los publicados y referenciados por alguna option/meta)
#   be-template-list.sh --options             (añade el volcado de todas las options mfn_*)
#   be-template-list.sh --refs=0              (sin buscar referencias; más rápido)
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run template-list "$@"
