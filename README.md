Для FORKEL предлагаю организовать `docs` как единый источник проектного контекста для всех ролей Claude. Цель — чтобы каждый аккаунт понимал свою задачу, видел актуальное состояние проекта и не перечитывал всю историю переписки.

Основной принцип: короткий `CLAUDE.md` → профильные документы → журнал изменений → конкретная задача на текущую сессию.

## 1. Структура проекта

```
forkel/
├── CLAUDE.md
├── docs/
│   ├── project/
│   │   ├── overview.md
│   │   ├── requirements.md
│   │   └── decisions.md
│   │
│   ├── design/
│   │   ├── reference-analysis.md
│   │   ├── comparison.md
│   │   ├── design-system.md
│   │   └── implementation-guide.md
│   │
│   ├── workflow/
│   │   ├── roles.md
│   │   ├── current-state.md
│   │   ├── work-log.md
│   │   ├── task-queue.md
│   │   └── handoff.md
│   │
│   └── qa/
│       ├── ui-review.md
│       └── acceptance-checklist.md
│
└── frontend/
```

Необязательно сразу заполнять все файлы. Создадим структуру, а содержимое будем добавлять по мере работы.

## 2. Назначение документов

| Файл                             | Что хранит                                                         |
| -------------------------------- | ------------------------------------------------------------------ |
| `CLAUDE.md`                      | Общие правила для любого Claude, навигация по документации         |
| `project/overview.md`            | Что за проект, аудитория, цели, стек                               |
| `project/requirements.md`        | Согласованные требования из ТЗ                                     |
| `project/decisions.md`           | Принятые решения и причины, по которым их нельзя самовольно менять |
| `design/reference-analysis.md`   | Детальный разбор каждого сайта-референса                           |
| `design/comparison.md`           | Сравнение референсов: что подходит и что не подходит FORKEL        |
| `design/design-system.md`        | Цвета, типографика, сетки, отступы, компоненты, состояния          |
| `design/implementation-guide.md` | Как применять дизайн-систему в текущем frontend                    |
| `workflow/roles.md`              | Полномочия и ограничения каждой роли                               |
| `workflow/current-state.md`      | Текущее проверенное состояние проекта                              |
| `workflow/work-log.md`           | Хронология завершённых работ и изменений                           |
| `workflow/task-queue.md`         | Очередь задач с приоритетами и зависимостями                       |
| `workflow/handoff.md`            | Передача работы между сессиями и аккаунтами                        |
| `qa/ui-review.md`                | Найденные визуальные проблемы и результаты проверок                |
| `qa/acceptance-checklist.md`     | Критерии готовности страниц и функций                              |

Важно: не дублируй один факт в нескольких документах. Например, актуальное решение хранится в `decisions.md`, а `work-log.md` лишь ссылается на него.

## 3. Что написать в `CLAUDE.md`

Это главный файл контекста. Он должен быть коротким: постоянные правила и ссылки на документы, а не копия всего ТЗ.

# FORKEL — Project Instructions

## Project

Интернет-магазин электротехнической продукции FORKEL. Цель — разработать удобный, современный интернет-магазин с каталогом, покупкой товаров и визуальным конфигуратором.

## Before Starting

1. Read `docs/workflow/current-state.md`.
2. Read `docs/workflow/roles.md` and follow the assigned role.
3. Read only the documents relevant to the current task.
4. Inspect the existing code before proposing or making changes.
5. Follow `docs/project/requirements.md` and `docs/project/decisions.md`.

## Core Rules

- Do not invent project architecture, APIs, data structures or implemented features.
- Do not rewrite existing functionality without a specific reason.
- Do not introduce new dependencies without justification.
- Preserve existing conventions and component structure.
- Do not duplicate existing components or design tokens.
- Keep changes limited to the current task.
- Do not overwrite another role's work.
- Do not mark a task complete without verification.
- Never claim that a build, test or visual check passed unless it was actually performed.

## Design

- Follow `docs/design/design-system.md` and `docs/design/implementation-guide.md`.
- Use the installed `frontend-design` skill for relevant UI tasks.
- Treat project requirements and verified technical constraints as higher priority than stylistic experimentation.
- Verify responsive states and visual consistency.

## Handoffs and Tracking

At the end of each session:

1. Update `docs/workflow/current-state.md` if the verified project state changed.
2. Append completed work and verification results to `docs/workflow/work-log.md`.
3. Update `docs/workflow/task-queue.md` when task status or dependencies change.
4. Prepare `docs/workflow/handoff.md` for work that another session must continue.
5. List changed files, unresolved issues and exact next steps.

## Context Efficiency

- Do not read every document by default.
- Load only the files required for the assigned task.
- Prefer concise summaries and references to full explanations.
- Do not repeat existing documentation in responses.
- Ask before expanding the task beyond its stated scope.

## 4. Как разделить роли

В `docs/workflow/roles.md` закрепляем ответственность, чтобы роли не мешали друг другу.

Роль 1 — Research & Design System

Анализ референсов, изучение текущего frontend, формирование дизайн-системы и рекомендаций.

Изменяет: `docs/design/*`. Не изменяет код приложения.

Роль 2 — UI Implementation

Реализация страниц и компонентов в существующем frontend по требованиям и дизайн-системе.

Изменяет: код в пределах назначенной задачи. Не переписывает дизайн-систему самостоятельно.

Роль 3 — UI Review & QA

Независимая проверка скриншотов, адаптивности, состояний интерфейса и критериев приёмки.

По умолчанию не меняет код. Передаёт замечания исполнителю с приоритетом и способом проверки.

На старте хватит двух активных ролей — Research и Implementation. QA подключай после реализации ключевых страниц и конфигуратора.

## 5. Как отслеживать изменения

### `current-state.md`

Хранит только актуальную картину:

- что уже реализовано и проверено;
- что сейчас в работе;
- что заблокировано;
- какие проблемы остались;
- какая задача следующая.

### `work-log.md`

Каждая запись — по единому шаблону:

## 2026-10-16 — Краткое название задачи

**Роль:** UI Implementation\
**Задача:** ...\
**Изменённые файлы:** ...\
**Результат:** ...\
**Проверки:** выполненные команды, тесты, страницы и разрешения экрана\
**Проблемы:** ...\
**Следующий шаг:** ...

### `handoff.md`

Перед завершением сессии Claude должен указать:

- цель и статус текущей задачи;
- что именно изменено;
- какие проверки выполнены и с каким результатом;
- что не удалось сделать;
- точные следующие действия;
- какие файлы необходимо прочитать следующей роли.

Не нужно переносить весь диалог. Передавай проверенное состояние и конкретные инструкции.

## 6. Правила экономии контекста и лимитов

1. Один источник истины. Требования не копируются в каждый файл и каждую задачу.
2. Не загружать всё сразу. Для работы над каталогом достаточно `CLAUDE.md`, текущего состояния, требований, дизайн-системы и связанных исходников.
3. Одна задача — одна сессия. Например, сначала каталог, затем карточка товара. Не смешивать дизайн, архитектурный рефакторинг и бизнес-логику без необходимости.
4. Не использовать чат как журнал проекта. Завершённые решения фиксируются в репозитории.
5. Проверять Git перед началом и завершением работы. Не затирать чужие изменения; при параллельной работе использовать отдельные ветки.
6. Не поручать всем ролям одно и то же. Исследователь создаёт спецификацию, исполнитель реализует, проверяющий выявляет дефекты.
7. Суммировать, а не копировать. В `current-state.md` — короткое состояние, подробности реализации остаются в коде и профильных документах.

## 7. В каком порядке всё организовать

1. Создать `CLAUDE.md` и папки `docs`.
2. Поручить первой роли изучить существующий проект и референсы. Сначала — отчёт об обнаруженном, без изменений кода.
3. Сформировать и согласовать `design-system.md` и `implementation-guide.md` на основании референсов и реального frontend.
4. Передать второй роли конкретную страницу для реализации.
5. Проверить страницу отдельной сессией QA, исправить замечания и зафиксировать результат.

Следующий шаг: прежде чем создавать все файлы, нужно определить фактическую структуру репозитория FORKEL и проверить, какие документы и правила уже существуют. Иначе можно создать дубли или конфликтующие инструкции.