# QA TASK-001 — уменьшение h1 в 1,5 раза (прототип V4)

- Роль: qa-tester. Ветка `role/implementation/TASK-001-v4-h1-font-size`, коммит `f3dbb6d`, база `origin/main` (`60f090b`).
- **Статус: PASS**

## 1. Диф
`git diff origin/main...HEAD -- prototype`: изменены ровно 4 строки в 4 файлах — `styles.css` (`h1`), `css/about.css` (`.fk-ab-hero__title`), `css/projects.css` (`.fk-prj-cover__text .fk-page-title`), `css/product-page.css` (`.fk-pdp__title`). Меняется только `font-size`; line-height, вес, letter-spacing и токены (`--fk-fs-h2`) не тронуты. Вне `prototype` дифф касается только `docs/tasks` и `docs/workflow` (разрешено). Посторонних изменений нет.

## 2. Измерения getComputedStyle(h1).fontSize, px
Допуск ±0,5 px от main/1,5. На каждой странице найден ровно один h1 (число h1 на main и ветке совпадает). Варианты URL: `collection.html?collection=F70`, `project.html?id=1` (валидный) и `?id=99` (не найден), `product.html?id=1` (валидный) и `?id=999999` (не найден).

| Страница | Ширина | main | ветка | main/ветка |
|---|---|---|---|---|
| about.html | 360 | 30 | 20 | 1.500 |
| about.html | 768 | 39.168 | 26.1094 | 1.500 |
| about.html | 1280 | 52.48 | 34.9824 | 1.500 |
| about.html | 1920 | 56 | 37.328 | 1.500 |
| account.html | 360 | 30 | 20 | 1.500 |
| account.html | 768 | 39.168 | 26.1094 | 1.500 |
| account.html | 1280 | 52.48 | 34.9824 | 1.500 |
| account.html | 1920 | 56 | 37.328 | 1.500 |
| cart.html | 360 | 30 | 20 | 1.500 |
| cart.html | 768 | 39.168 | 26.1094 | 1.500 |
| cart.html | 1280 | 52.48 | 34.9824 | 1.500 |
| cart.html | 1920 | 56 | 37.328 | 1.500 |
| catalog.html | 360 | 30 | 20 | 1.500 |
| catalog.html | 768 | 39.168 | 26.1094 | 1.500 |
| catalog.html | 1280 | 52.48 | 34.9824 | 1.500 |
| catalog.html | 1920 | 56 | 37.328 | 1.500 |
| checkout.html | 360 | 30 | 20 | 1.500 |
| checkout.html | 768 | 39.168 | 26.1094 | 1.500 |
| checkout.html | 1280 | 52.48 | 34.9824 | 1.500 |
| checkout.html | 1920 | 56 | 37.328 | 1.500 |
| collection.html | 360 | 64 | 42.672 | 1.500 |
| collection.html | 768 | 64 | 42.672 | 1.500 |
| collection.html | 1280 | 65.28 | 43.52 | 1.500 |
| collection.html | 1920 | 88.32 | 58.88 | 1.500 |
| collection.html?collection=F70 | 360 | 64 | 42.672 | 1.500 |
| collection.html?collection=F70 | 768 | 64 | 42.672 | 1.500 |
| collection.html?collection=F70 | 1280 | 65.28 | 43.52 | 1.500 |
| collection.html?collection=F70 | 1920 | 88.32 | 58.88 | 1.500 |
| configurator.html | 360 | 30 | 20 | 1.500 |
| configurator.html | 768 | 39.168 | 26.1094 | 1.500 |
| configurator.html | 1280 | 52.48 | 34.9824 | 1.500 |
| configurator.html | 1920 | 56 | 37.328 | 1.500 |
| contacts.html | 360 | 30 | 20 | 1.500 |
| contacts.html | 768 | 39.168 | 26.1094 | 1.500 |
| contacts.html | 1280 | 52.48 | 34.9824 | 1.500 |
| contacts.html | 1920 | 56 | 37.328 | 1.500 |
| cooperation.html | 360 | 30 | 20 | 1.500 |
| cooperation.html | 768 | 39.168 | 26.1094 | 1.500 |
| cooperation.html | 1280 | 52.48 | 34.9824 | 1.500 |
| cooperation.html | 1920 | 56 | 37.328 | 1.500 |
| favorites.html | 360 | 30 | 20 | 1.500 |
| favorites.html | 768 | 39.168 | 26.1094 | 1.500 |
| favorites.html | 1280 | 52.48 | 34.9824 | 1.500 |
| favorites.html | 1920 | 56 | 37.328 | 1.500 |
| index.html | 360 | 64 | 42.672 | 1.500 |
| index.html | 768 | 64 | 42.672 | 1.500 |
| index.html | 1280 | 65.28 | 43.52 | 1.500 |
| index.html | 1920 | 88.32 | 58.88 | 1.500 |
| new.html | 360 | 30 | 20 | 1.500 |
| new.html | 768 | 39.168 | 26.1094 | 1.500 |
| new.html | 1280 | 52.48 | 34.9824 | 1.500 |
| new.html | 1920 | 56 | 37.328 | 1.500 |
| product.html?id=1 | 360 | 25.6 | 17.072 | 1.500 |
| product.html?id=1 | 768 | 31.424 | 20.944 | 1.500 |
| product.html?id=1 | 1280 | 40.64 | 27.088 | 1.500 |
| product.html?id=1 | 1920 | 41.6 | 27.728 | 1.500 |
| product.html?id=999999 | 360 | 25.6 | 17.072 | 1.500 |
| product.html?id=999999 | 768 | 31.424 | 20.944 | 1.500 |
| product.html?id=999999 | 1280 | 40.64 | 27.088 | 1.500 |
| product.html?id=999999 | 1920 | 41.6 | 27.728 | 1.500 |
| project.html?id=1 | 360 | 28 | 18.672 | 1.500 |
| project.html?id=1 | 768 | 31.36 | 20.9094 | 1.500 |
| project.html?id=1 | 1280 | 41.6 | 27.7344 | 1.500 |
| project.html?id=1 | 1920 | 48 | 32 | 1.500 |
| project.html?id=99 | 360 | 64 | 42.672 | 1.500 |
| project.html?id=99 | 768 | 64 | 42.672 | 1.500 |
| project.html?id=99 | 1280 | 65.28 | 43.52 | 1.500 |
| project.html?id=99 | 1920 | 88.32 | 58.88 | 1.500 |
| projects.html | 360 | 30 | 20 | 1.500 |
| projects.html | 768 | 39.168 | 26.1094 | 1.500 |
| projects.html | 1280 | 52.48 | 34.9824 | 1.500 |
| projects.html | 1920 | 56 | 37.328 | 1.500 |
| search.html | 360 | 30 | 20 | 1.500 |
| search.html | 768 | 39.168 | 26.1094 | 1.500 |
| search.html | 1280 | 52.48 | 34.9824 | 1.500 |
| search.html | 1920 | 56 | 37.328 | 1.500 |

Отклонений нет: все 76 измерений (19 URL × 4 ширины) дают отношение 1,500.

## 3. h2/h3 и горизонтальная прокрутка
- Первые три h2 и h3 каждой страницы на всех ширинах: размеры на ветке идентичны main (программное сравнение на всех 19 URL, включая index и product).
- 360 px: `scrollWidth <= innerWidth` на всех 19 URL и на main, и на ветке. Новых отличий нет.

## 4. Выполненные команды
- `git diff origin/main...HEAD -- prototype`, `git diff origin/main...HEAD --stat`
- `git worktree add /tmp/claude-0/qa_main origin/main` (после проверки удалён: `git worktree remove --force`, `git worktree prune`)
- `python3 -m http.server 8101` (ветка) и `8102` (main)
- Playwright (node, Chromium): скрипт m.js — 2 копии × 4 ширины × 19 URL; анализ скриптом a.py (оба в scratchpad сессии).

## 5. Дефекты
Подтверждённых дефектов: нет.

Предположения (не дефекты): на 360 px h1 товара ≈17 px и проекта ≈19 px, т.е. близко к размеру основного текста; это прямое следствие требования и допущения 3 в TASK-001. Визуальная иерархия h1/h2 не оценивалась.

## 6. Не проверено
- Скриншоты и визуальная оценка иерархии (на части страниц h2 может быть крупнее h1 — вне критериев).
- Промежуточные ширины, браузеры кроме Chromium, состояния после взаимодействия (результаты поиска, модальные окна).
- V1–V3 (вне области).
