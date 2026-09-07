#!/usr/bin/env bash
# SAFETY: reversible (la salida muestra old/new por campo; vuelve a pasar el valor anterior para restaurar)
# be-temini-legal-set — escribe las metas del aviso legal (aviso-legal.php) y de la declaración de accesibilidad (accesibilidad.php)
#
# Uso:
#   be-temini-legal-set.sh post=79 --show                                        valores actuales y plantilla (solo lectura)
#   be-temini-legal-set.sh post=79 razon_social='X, S.L.' nombre_comercial=X direccion='Rúa Y 1' \
#                          identificacion_tipo=CIF identificacion_numero=B00000000 correo_electronico=info@x.com juzgado='A Coruña'
#   be-temini-legal-set.sh post=81 fecha_de_revision=2026-09-01
#   be-temini-legal-set.sh post=79 juzgado=                                      valor vacío ⇒ borra la meta
#   be-temini-legal-set.sh ... --translations | --dry-run | --force
#
# Globales: --path=/ruta/wp  --url=...  (WP_BIN y WP_PATH como variables de entorno)
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run temini-legal-set "$@"
