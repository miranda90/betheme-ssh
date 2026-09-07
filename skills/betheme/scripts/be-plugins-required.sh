#!/usr/bin/env bash
# SAFETY: read-only (listar) | reversible (--install: wp plugin deactivate/uninstall <slug> lo deshace)
# be-plugins-required — plugins de Betheme → Plugins (TGMPA + premium de Muffin) con estado, e instalación de los que falten
#
# Uso:
#   be-plugins-required.sh
#   be-plugins-required.sh --install --dry-run                    (imprime los comandos wp plugin ...)
#   be-plugins-required.sh --install --only=contact-form-7,duplicate-post
#   be-plugins-required.sh --install --activate=0                 (instala sin activar)
#   be-plugins-required.sh --install --update                     (también actualiza los desfasados)
#   be-plugins-required.sh --no-remote                            (sin consultar la API de Muffin)
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run plugins-required "$@"
