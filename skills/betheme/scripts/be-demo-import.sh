#!/usr/bin/env bash
# SAFETY: destructive (reset=1 TRUNCA posts/postmeta/terms/comments…; siempre sobrescribe Theme Options, menús, widgets y portada)
# be-demo-import — importa una pre-built website de Betheme (Setup Wizard) por consola. Doble confirmación: --yes y really=1.
#
# Uso:
#   be-demo-import.sh --list                                   (catálogo local functions/importer/demos.php: RO)
#   be-demo-import.sh --list filter=shop
#   be-demo-import.sh demo=doctor2 --dry-run                   (plan: pasos, plugins requeridos, purchase code)
#   be-demo-import.sh demo=doctor2 reset=1 media=1 --yes really=1 --user=<admin>
#   be-demo-import.sh demo=doctor2 reset=0 media=0 complete=0 --yes really=1     (solo contenido + Theme Options)
#   be-demo-import.sh demo=doctor2 skip=download,slider --yes really=1           (reanudar con el paquete ya descargado)
#   ... builder=elementor   blogname="Mi web"   no-backup=1 (no recomendado)
#
# Antes hace `wp db export` a uploads/betheme/backups/pre-demo-<demo>-<ts>.sql (restaurar: wp db import <fichero>).
# NO ejecutar en producción. Requiere purchase code registrado y los plugins de la demo instalados y activos.
source "$(dirname "$0")/_env.sh"
[[ "${1:-}" == "-h" || "${1:-}" == "--help" ]] && { be_usage "$0"; exit 0; }
be_run demo-import "$@"
