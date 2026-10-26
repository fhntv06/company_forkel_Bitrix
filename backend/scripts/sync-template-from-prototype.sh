#!/usr/bin/env bash
# Одноразовая начальная загрузка стилей и скрипта шаблона из prototype/homepage-v3.
# После этого источником истины становится шаблон (backend/www/local/templates/forkel),
# правки вносятся в него, а скрипт больше не запускается.
#
# Использование (из любого каталога):
#   backend/scripts/sync-template-from-prototype.sh          # создаёт файлы, не перезаписывает существующие
#   backend/scripts/sync-template-from-prototype.sh --force  # перезаписывает

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
SRC="$ROOT/prototype/homepage-v3"
DST="$ROOT/backend/www/local/templates/forkel"
FORCE=0
[ "${1:-}" = "--force" ] && FORCE=1

copy() {
  local from="$1" to="$2"
  if [ ! -f "$from" ]; then
    echo "Нет исходного файла: $from" >&2
    exit 1
  fi
  if [ -e "$to" ] && [ "$FORCE" -ne 1 ]; then
    echo "Пропуск (уже есть, используйте --force): $to"
    return
  fi
  mkdir -p "$(dirname "$to")"
  cp "$from" "$to"
  echo "Скопировано: $to"
}

copy "$SRC/styles.css" "$DST/template_styles.css"
copy "$SRC/app.js"     "$DST/js/app.js"