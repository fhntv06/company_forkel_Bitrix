# FORKEL — Context for Claude

## Verified repository structure
- `docs/project/`: overview, requirements, decisions.
- `docs/design/`: design system, homepage specification/research and requirement map; some placeholder docs may be empty.
- `docs/workflow/`: current state, roles, task queue, work log and handoff.
- `docs/qa/`: UI review and acceptance checklist.
- `docs/development/`, `docs/documents/`, `docs/CLAUDE.md`: present in the repository; contents not reviewed for this file.
- `prototype/`: separately versioned homepage prototypes.
- `backend/`: Docker Compose setup for Nginx, PHP-FPM and MySQL; `backend/www/` is the site document root.

## Important limits
- The backend README describes a local setup, not a verified production deployment.
- PHP 8.2 and MySQL 8.0 are stated as preliminary environment versions; compatibility with the selected Bitrix edition must be verified.
- Do not infer that all Bitrix core files, modules, production data, APIs, or integrations are available.
- Verify current Git branch/status and inspect the relevant code before implementation.
- Existing project role documentation currently defines Research & Design System, UI Implementation, and UI Review & QA. The agents in `.claude/agents/` map to them in `docs/workflow/agents-mapping.md`: Research & Design = Role A, UI Implementation = Role B, QA Tester = Role C; Orchestrator, Content Manager and Bitrix Developer are additional specialists. Align their work with the existing role boundaries rather than replacing project documentation wholesale.

## Current task
The task must be supplied explicitly by the user or read from `docs/workflow/task-queue.md`. Do not invent a task or treat a prototype as approved production behavior.
