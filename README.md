# BeTheme SSH Toolkit

Plugin de Claude Code con documentación y scripts para administrar **BeTheme** (tema de WordPress) por SSH
con `wp-cli`, sin entrar en wp-admin: Theme Options completo, logos y favicon, fuentes, packs de iconos,
Tools (static.css, caché, regeneración de CSS/fuentes/miniaturas), export/import/reset de opciones,
registro y actualizaciones, Header/Footer Builder, CPTs, importador de demos, y los ajustes propios del
tema hijo **Témini**.

Todo verificado línea a línea contra BeTheme 28.4.3 y probado de forma reversible en un entorno de
desarrollo real (ver `skills/betheme/docs/13-verificacion-y-pruebas.md`).

## Instalación

Dentro de una sesión de Claude Code:

```
/plugin marketplace add miranda90/betheme-ssh
/plugin install betheme-ssh@invbit-tools
```

Reinicia Claude Code o ejecuta `/reload-plugins` si hace falta. El skill se auto-invoca cuando el trabajo
va de configurar/migrar BeTheme por SSH; también puede invocarse a mano con `/betheme-ssh:betheme`.

## Contenido

```
skills/betheme/
├── SKILL.md              punto de entrada: convenciones, índice, clases de seguridad
├── docs/                 14 documentos + catálogo generado de ~829 opciones
└── scripts/              32 wrappers be-*.sh (wp eval-file) + PHP + verificadores
```

## Seguridad

- **read-only**: no escribe nada.
- **reversible**: cada escritura de opciones guarda el estado anterior (`betheme_revision_backup`); admite `--dry-run`.
- **destructivo**: exige `--yes` (reset, import en modo reemplazo, importador de demos, borrar un pack de iconos).

Nunca se ejecuta nada destructivo sin confirmación explícita del usuario en la conversación.

## Alcance

Cubre el **panel de administración** de BeTheme. No cubre el contenido del BeBuilder (JSON de páginas) —
eso es un dominio de generación de contenido aparte.

## Mantenimiento

Si trabajas contra una versión de BeTheme distinta de 28.4.3, regenera el catálogo de opciones antes de
fiarte de las citas de código:

```bash
scripts/be-catalog-generate.sh out=skills/betheme/docs/reference
scripts/dev/check-citations.sh --show --src=/ruta/al/tema
```

## Autor

Martín Miranda — Invbit (desarrollo@invbit.com)
