#!/usr/bin/env bash
# SAFETY: reversible (revisión previa en betheme_revision_backup)
# be-opt-unset — elimina claves de la opción betheme replicando el Save del panel: revisión + static.css + reset JS BeBuilder
#
# Al eliminar una clave, mfn_opts_get('<id>', $default) devuelve el $default de cada llamada del tema, NO el std del campo.
#
# Uso:
#   be-opt-unset.sh key=logo-height
#   be-opt-unset.sh key=logo-height,logo-width,logo-width-tablet
#   be-opt-unset.sh key=imported --force                 (claves internas o ids desconocidos)
#   be-opt-unset.sh ... --dry-run | --no-static | --no-revision
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run opt-unset "$@"
