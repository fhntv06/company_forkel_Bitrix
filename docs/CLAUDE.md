# FORKEL — Project Instructions

## Project
Интернет-магазин электротехнической продукции FORKEL. Цель — разработать удобный, современный интернет-магазин с каталогом, покупкой товаров и визуальным конфигуратором.

# FORKEL — Claude Instructions

## Project Context
FORKEL is an e-commerce project for electrical installation products. The project requirements, current implementation state, design system, and workflow are documented in `docs/`.

## Before Every Task
1. Read `docs/workflow/current-state.md`.
2. Read `docs/workflow/roles.md` and follow the assigned role.
3. Read only the documents relevant to the current task.
4. Inspect the existing code before proposing or implementing changes.
5. Check `docs/project/requirements.md` and `docs/project/decisions.md` when relevant.

## Core Rules
- Treat verified source code as the authority on current implementation.
- Treat approved project requirements as the authority on expected behavior.
- Never invent implemented features, APIs, data structures, or technical decisions.
- Do not rewrite existing architecture without explicit justification.
- Keep changes limited to the assigned task.
- Avoid unnecessary dependencies, duplicate components, and duplicate documentation.
- Do not overwrite changes made by another role.
- Ask for clarification when a conflict materially affects implementation.
- Never claim that a test or verification passed unless it was actually performed.

## Design Rules
- Follow `docs/design/design-system.md` and `docs/design/implementation-guide.md` when available.
- Use the `frontend-design` skill for relevant UI/UX tasks.
- Respect existing architecture and project requirements over stylistic experimentation.
- Check responsive layouts, interactive states, accessibility, and visual consistency.

## Documentation and Handoffs
At the end of each task:
1. Update `docs/workflow/current-state.md` if the verified project state changed.
2. Append a concise entry to `docs/workflow/work-log.md`.
3. Update `docs/workflow/task-queue.md` when task status or dependencies change.
4. Update `docs/workflow/handoff.md` when another session needs to continue the work.
5. Report changed files, verification results, unresolved issues, and next steps.

## Context Efficiency
- Do not read the entire repository or every document unless required.
- Load only the files and source code relevant to the current task.
- Use concise summaries and references instead of copying large sections.
- Do not repeat information already recorded in project documentation.
- Do not expand the task scope without approval.

## Source of Truth
- Requirements: `docs/project/requirements.md`
- Technical and product decisions: `docs/project/decisions.md`
- Verified project state: `docs/workflow/current-state.md`
- Role boundaries: `docs/workflow/roles.md`
- Design system: `docs/design/design-system.md`
- Task status: `docs/workflow/task-queue.md`

## Before Starting
1. Read `workflow/current-state.md`.
2. Read `workflow/roles.md` and follow the assigned role.
3. Read only the documents relevant to the current task.
4. Inspect the existing code before proposing or making changes.
5. Follow `project/requirements.md` and `docs/project/decisions.md`.

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
- Follow `design/design-system.md` and `docs/design/implementation-guide.md`.
- Use the installed `frontend-design` skill for relevant UI tasks.
- Treat project requirements and verified technical constraints as higher priority than stylistic experimentation.
- Verify responsive states and visual consistency.

## Handoffs and Tracking
At the end of each session:
1. Update `workflow/current-state.md` if the verified project state changed.
2. Append completed work and verification results to `workflow/work-log.md`.
3. Update `workflow/task-queue.md` when task status or dependencies change.
4. Prepare `workflow/handoff.md` for work that another session must continue.
5. List changed files, unresolved issues and exact next steps.

## Context Efficiency
- Do not read every document by default.
- Load only the files required for the assigned task.
- Prefer concise summaries and references to full explanations.
- Do not repeat existing documentation in responses.
- Ask before expanding the task beyond its stated scope.
