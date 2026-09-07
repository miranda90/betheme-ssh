#!/usr/bin/env bash
# SAFETY: read-only (PHP CLI local, sin WordPress)
# be-catalog-check — comprueba que los ids de Theme Options citados en los .md existen en reference/catalogo-opciones.json
#
# Uso:
#   be-catalog-check.sh                (docs = carpeta padre de scripts/)
#   be-catalog-check.sh /ruta/docs/betheme-ssh
set -euo pipefail
HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { sed -n '2,/^set /p' "$0" | sed -n 's/^# \{0,1\}//p'; exit 0; }
exec php "$HERE/dev/catalog-check.php" "${1:-$(cd "$HERE/.." && pwd)}"
