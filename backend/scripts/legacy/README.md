# Устаревшие скрипты миграций (TASK-018) — НЕ ЗАПУСКАТЬ

Эти скрипты заменены миграциями **sprint.migration** (2026-11-27). Лежат здесь только для истории.

| Старый скрипт | Куда перенесён |
|---|---|
| `migrate_task018_block1_catalog.php`, шаги 1–5, 7, 8 | `backend/www/local/php_interface/migrations/Version20261127100000.php` |
| `migrate_task018_block1_catalog.php`, шаг 6 (бета-товары) | `backend/www/local/php_interface/migrations.testdata/Version20261127100200.php` |
| `migrate_task018_block3_shop.php` | `backend/www/local/php_interface/migrations/Version20261127100100.php` |

Почему не запускать:
- `migrate_task018_block3_shop.php` при каждом запуске **создаёт дубль доставки СДЭК** (поиск по `CODE='cdek'` не работает: Битрикс перезаписывает CODE на ID). В миграции исправлено.
- Там же пароль пользователя `1c_exchange` вычислялся из пути к файлу (предсказуемый). В миграции исправлено.
- Скрипты смешивают структуру и тестовые данные и не ведут журнал применения.

Регламент: [docs/development/deployment.md](../../../docs/development/deployment.md).
