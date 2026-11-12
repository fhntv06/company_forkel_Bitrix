# FORKEL — Claude Code Instructions

## Project context
FORKEL is an e-commerce project for electrical installation products. The repository currently contains project documentation, versioned homepage prototypes, and a Docker-based Bitrix development environment. Do not assume the production Bitrix site is fully present or installed in this repository.

## First steps for every task
1. Read `docs/workflow/current-state.md`.
2. Read `docs/workflow/roles.md` (Roles A/B/C) and `docs/workflow/agents-mapping.md` (how the Claude agents map to them) and identify the assigned role.
3. Read the relevant requirements in `docs/project/requirements.md` and decisions in `docs/project/decisions.md`.
4. Inspect the actual files and Git status before planning or editing.
5. Consult only the relevant design, QA, or development documents.

## Source of truth
- Current implementation: tracked source/configuration and actual runtime evidence.
- Product requirements: `docs/project/requirements.md`.
- Approved decisions: `docs/project/decisions.md`.
- Verified project status: `docs/workflow/current-state.md`.
- Work routing and role boundaries: `docs/workflow/roles.md`, `docs/workflow/role-permissions.md`, `docs/workflow/agents-mapping.md`.
- Git and PR rules: `docs/workflow/github-workflow.md`.
- Design: `docs/design/` documents, respecting their approval status.
- Task status: `docs/workflow/task-queue.md`.

If documents and code disagree, report the conflict. Do not silently choose an interpretation that materially affects scope, architecture, data, or user behavior.

## Role routing
- Content, standard Bitrix admin configuration, and CMS-managed entities: Content Manager.
- PHP, Bitrix components/templates, backend behavior, API, integration, or architecture: Bitrix Developer.
- Prototype or frontend HTML/CSS/JS implementation: UI Implementation.
- Visual specification, design system, or prototype research: Research & Design.
- Independent validation of a completed change: QA Tester.
- New task described by the owner in chat (or `/task`): follow the `task-intake` skill (formalize in `docs/tasks/`, log, execute, QA, PR for review; the owner reviews and merges).
- Multi-step, mixed, ambiguous, or cross-role tasks: Orchestrator coordinates and delegates (run it as the main session: `claude --agent orchestrator`; subagents cannot start other subagents).

For mixed tasks, split CMS/admin work from code work and define an explicit handoff. Do not let one role silently exceed its scope.

## Engineering safeguards
- Inspect before editing; keep changes scoped to the task.
- Do not assume Bitrix APIs, module availability, component contracts, database structure, or installed version.
- Do not edit the Bitrix core or vendor files unless explicitly approved and technically justified.
- Prefer supported Bitrix extension points and project-local code.
- Do not introduce dependencies without a reason and approval when material.
- Do not overwrite uncommitted or concurrent work. Check `git status` and diffs first.
- Never perform destructive production operations, migrations, bulk edits, or data deletion without explicit authorization and a backup/rollback plan.
- Do not expose `.env`, credentials, tokens, customer data, or database dumps.
- Never claim a build, test, browser check, or QA pass unless it was actually performed.

## Git rules
- Work in a separate branch per task: `role/<role>/<task-id>-<short-name>`. Never commit directly to `main`.
- No force push, no deleting or switching to another session's unfinished branch, no merging into `main` without explicit authorization.
- Changes go through a Pull Request. Do not claim a change is committed, pushed or merged unless verified.

## Prototype boundary
`prototype/` contains versioned design prototypes. Treat prior versions as frozen unless the task explicitly names them. Do not treat prototype HTML/CSS as a production Bitrix template. Before transferring prototype work into Bitrix, map it to the real template/component structure and approved requirements.

## QA and completion
Implementation is not complete merely because files were edited or committed. QA must check the agreed acceptance criteria independently. QA reports defects and evidence; it does not fix code unless separately assigned.

Use statuses: `PASS`, `FAIL`, `BLOCKED`, or `PARTIAL`.
QA loop: `FAIL` -> implementer fixes the confirmed defects -> QA retests those defects plus a short regression. At most 3 rounds, then escalate to the user. `BLOCKED` and `PARTIAL` do not count as completion.
At task completion report:
- task and status;
- files/settings changed;
- decisions or assumptions;
- checks actually performed and their outcomes;
- unresolved issues and risks;
- next role/action.

Update `docs/workflow/current-state.md`, `docs/workflow/work-log.md`, `docs/workflow/task-queue.md`, and `docs/workflow/handoff.md` only when relevant and within the assigned role's authority.

## Official references
Use official Bitrix documentation for API and platform behavior: https://dev.1c-bitrix.ru/api_help/ and https://dev.1c-bitrix.ru/api_d7/.
