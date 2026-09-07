#!/usr/bin/env bash
# SAFETY: reversible (revisión previa en betheme_revision_backup)
# be-font-custom-add — alta de fuente propia (nombre + .woff + .ttf) en el primer slot libre font-custom{,2,N}
#
# Uso:
#   be-font-custom-add.sh name="Avenir Heavy" woff=/tmp/Avenir-Heavy.woff ttf=/tmp/Avenir-Heavy.ttf
#   be-font-custom-add.sh name="Mi Fuente" woff=https://sitio.com/wp-content/uploads/2026/01/mi.woff
#   be-font-custom-add.sh name=... woff=... slot=2 --force        (slot concreto; force sobreescribe uno ocupado)
#   be-font-custom-add.sh ... --dry-run
#
# Después asigna la fuente a un rol: be-font-role-set.sh role=headings font="Avenir Heavy"
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run font-custom-add "$@"
