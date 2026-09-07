#!/usr/bin/env bash
# SAFETY: reversible (revisión previa en betheme_revision_backup)
# be-font-role-set — familia por rol (font-content, font-headings...) y google-font-mode
#
# Uso:
#   be-font-role-set.sh role=headings font="Inter"
#   be-font-role-set.sh role=content,lead,menu font="Avenir"          (fuente propia: SIN #)
#   be-font-role-set.sh role=font-decorative font="Georgia"           (también vale el id completo)
#   be-font-role-set.sh role=button font="Poppins"                    (button-font-family)
#   be-font-role-set.sh mode=local | mode=disabled | mode=            (google-font-mode)
#   be-font-role-set.sh ... --force | --dry-run
#
# Roles: content lead menu title headings headings-small blockquote decorative button
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run font-role-set "$@"
