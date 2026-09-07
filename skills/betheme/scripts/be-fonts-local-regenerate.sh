#!/usr/bin/env bash
# SAFETY: destructive (borra uploads/betheme/fonts/ entero y lo vuelve a descargar de Google; exige --yes)
# be-fonts-local-regenerate — equivalente a "Cache fonts" / "Regenerate fonts": genera mfn-local-fonts.css
#
# Uso:
#   be-fonts-local-regenerate.sh --dry-run     (lista fuentes, pesos, subsets y carpeta a borrar; no toca nada)
#   be-fonts-local-regenerate.sh --yes
#
# Solo tiene efecto en el front con google-font-mode=local (be-font-role-set.sh mode=local).
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run fonts-local-regenerate "$@"
