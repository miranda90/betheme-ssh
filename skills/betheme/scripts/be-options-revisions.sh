#!/usr/bin/env bash
# SAFETY: read-only (listar/diff/export) | reversible (--restore guarda antes la actual en `backup`; --save añade una revisión)
# be-options-revisions — revisiones de Theme Options (betheme_revision_update|revision|backup)
#
# Uso:
#   be-options-revisions.sh                                   (lista las 3 colas: type=update|revision|backup)
#   be-options-revisions.sh type=update ts=1787238636         (diff revisión → estado actual)
#   be-options-revisions.sh type=update ts=1787238636 --full
#   be-options-revisions.sh type=update ts=1787238636 export=/ruta/rev.json
#   be-options-revisions.sh type=update ts=1787238636 --restore --dry-run
#   be-options-revisions.sh type=update ts=1787238636 --restore
#   be-options-revisions.sh --save [type=revision]            (como el botón "Save revision")
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run options-revisions "$@"
