#!/usr/bin/env bash
# SAFETY: reversible (revisión previa en betheme_revision_backup; con --delete-media los adjuntos van a la papelera)
# be-font-custom-remove — baja de una fuente propia (vacía su slot y compacta los dinámicos); avisa de los roles que la usan
#
# Uso:
#   be-font-custom-remove.sh name="Avenir Heavy"
#   be-font-custom-remove.sh slot=3                 ('' o 1 = font-custom, 2 = font-custom2, N≥3 dinámicos)
#   be-font-custom-remove.sh name=... --delete-media (además manda a la papelera los adjuntos woff/ttf)
#   be-font-custom-remove.sh ... --dry-run
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run font-custom-remove "$@"
