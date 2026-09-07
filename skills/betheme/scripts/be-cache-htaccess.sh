#!/usr/bin/env bash
# SAFETY: reversible (copia previa .htaccess.bak-<fecha>; mode=off deshace mode=on)
# be-cache-htaccess — bloque "# BEGIN BETHEME … # END BETHEME" (Expires) que escribe Performance > Cache assets (hold-cache)
#
# Uso:
#   be-cache-htaccess.sh mode=status
#   be-cache-htaccess.sh mode=on --with-option      (añade el bloque y pone hold-cache=1)
#   be-cache-htaccess.sh mode=off --with-option     (quita el bloque y pone hold-cache=0)
#   be-cache-htaccess.sh mode=on|off --dry-run
#   be-cache-htaccess.sh mode=on --force            (aunque hold-cache no sea 1, o para duplicar el bloque)
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run cache-htaccess "$@"
