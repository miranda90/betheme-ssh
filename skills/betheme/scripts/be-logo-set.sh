#!/usr/bin/env bash
# SAFETY: reversible (revisión previa en betheme_revision_backup)
# be-logo-set — logos del header clásico (logo-img, retina, sticky, mobile...) + altura/padding/texto/link
#
# Uso:
#   be-logo-set.sh main=/tmp/logo.svg retina=/tmp/logo@2x.png
#   be-logo-set.sh main=https://sitio.com/wp-content/uploads/2026/01/logo.svg     (URL ya subida)
#   be-logo-set.sh sticky=... sticky-retina=... mobile=... mobile-retina=... mobile-sticky=... mobile-sticky-retina=...
#   be-logo-set.sh height=70 padding=10 text="Mi marca" link=link,h1-home
#   be-logo-set.sh width=180 width-tablet=140 width-mobile=110 valign=top      (SVG width / vertical align)
#   be-logo-set.sh clear=1                                                       (vacía los 8 logo-*-img)
#   be-logo-set.sh ... --dry-run | --no-static
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run logo-set "$@"
