#!/usr/bin/env bash
# SAFETY: read-only
# be-doctor — diagnóstico del estado de Betheme/Témini: versiones, opciones que disparan procesos,
# directorios uploads/betheme, frescura de static.css, .htaccess, registro, revisiones, packs, plantillas.
#
# Uso:
#   be-doctor.sh
#   be-doctor.sh --path=/ruta/wp
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run doctor "$@"
