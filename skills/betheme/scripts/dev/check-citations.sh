#!/usr/bin/env bash
# check-citations.sh — verifica que cada cita `betheme/ruta.php:N[-M]`, `temini/ruta.php:N` o `fichero.php:N`
# de los .md apunta a un fichero existente y a una línea que existe. Con --show imprime la línea citada para revisarla a ojo.
# Las citas con solo el nombre del fichero se resuelven buscándolo en betheme/ y temini/ (si hay varios candidatos, aviso).
# SAFETY: read-only. No necesita WordPress.
#
# Uso: scripts/dev/check-citations.sh [--show] [--src=/ruta/con/betheme/y/temini] [docs_dir]
#   por defecto docs_dir = carpeta docs/betheme-ssh (padre de scripts/), y src = ../../.. (raíz del repo temini)
set -euo pipefail
shopt -s nullglob

SHOW=0; SRC=""; DOCS=""
for a in "$@"; do
	case "$a" in
		--show) SHOW=1 ;;
		--src=*) SRC="${a#--src=}" ;;
		*) DOCS="$a" ;;
	esac
done
HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DOCS="${DOCS:-$(cd "$HERE/../.." && pwd)}"
SRC="${SRC:-$(cd "$DOCS/../../.." && pwd)}"

[[ -d "$SRC/betheme" ]] || { echo "ERROR: no encuentro $SRC/betheme (usa --src=)"; exit 2; }

# índice nombre → rutas (relativas a SRC) de todos los ficheros citables
INDEX="$(mktemp)"
( cd "$SRC" && find betheme temini -type f \( -name '*.php' -o -name '*.js' -o -name '*.css' -o -name '*.xml' \) 2>/dev/null ) \
	| awk -F/ '{print $NF "\t" $0}' | sort > "$INDEX"

resolve() { # $1 = cita de fichero (con o sin ruta) → imprime ruta relativa única, o "AMBIGUO:n", o vacío
	local f="$1"
	if [[ "$f" == betheme/* || "$f" == temini/* ]]; then
		[[ -f "$SRC/$f" ]] && echo "$f"; return
	fi
	local base="${f##*/}" matches
	matches="$(awk -F'\t' -v b="$base" '$1==b {print $2}' "$INDEX")"
	[[ -z "$matches" ]] && return
	if [[ "$f" == */* ]]; then   # ruta parcial: filtrar por sufijo
		matches="$(printf '%s\n' "$matches" | grep -E "(^|/)${f//\//\\/}$" || true)"
		[[ -z "$matches" ]] && return
	fi
	local n; n="$(printf '%s\n' "$matches" | wc -l | tr -d ' ')"
	if (( n == 1 )); then echo "$matches"; else echo "AMBIGUO:$n"; fi
}

errors=0; checked=0; ambiguous=0
for md in "$DOCS"/*.md; do
	rel="$(basename "$md")"
	while IFS= read -r cite; do
		[[ -z "$cite" ]] && continue
		file="${cite%%:*}"
		range="${cite##*:}"
		start="${range%%-*}"
		checked=$((checked+1))
		path="$(resolve "$file")"
		if [[ -z "$path" ]]; then
			echo "ERROR $rel: no existe $file"; errors=$((errors+1)); continue
		fi
		if [[ "$path" == AMBIGUO:* ]]; then
			ambiguous=$((ambiguous+1))
			(( SHOW )) && echo "AMBIGUO $rel: $file (${path#AMBIGUO:} candidatos; cita con ruta)"
			continue
		fi
		total=$(wc -l < "$SRC/$path")
		if (( start > total )); then
			echo "ERROR $rel: $path tiene $total líneas, cita :$start"; errors=$((errors+1)); continue
		fi
		if (( SHOW )); then
			printf '%s  %s:%s  |  %s\n' "$rel" "$path" "$start" "$(sed -n "${start}p" "$SRC/$path" | sed 's/^[[:space:]]*//' | cut -c1-110)"
		fi
	done < <(grep -oE '`[A-Za-z0-9_./-]+\.(php|js|css|xml):[0-9]+(-[0-9]+)?' "$md" | tr -d '`' | sort -u)
done
rm -f "$INDEX"
echo "Citas comprobadas: $checked  Errores: $errors  Ambiguas (solo nombre, varios candidatos): $ambiguous"
(( errors == 0 ))
