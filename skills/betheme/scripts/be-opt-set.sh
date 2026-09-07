#!/usr/bin/env bash
# SAFETY: reversible (revisión previa en betheme_revision_backup)
# be-opt-set — escribe opciones replicando el Save del panel: revisión + static.css + reset JS BeBuilder
#
# Uso:
#   be-opt-set.sh key=logo-height value=80
#   be-opt-set.sh key=font-size-h1 --json value='{"size":"48px","line_height":"56px","weight_style":"400","letter_spacing":"0"}'
#   be-opt-set.sh set=@cambios.json              (objeto JSON {id: valor})
#   be-opt-set.sh ... --dry-run | --force | --no-static | --no-revision
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run opt-set "$@"
