# Claude setup for FORKEL

This package is a proposed project-level Claude Code configuration based on the current public repository documentation. It is not written to GitHub.

## Included
- Root `CLAUDE.md`: shared rules and routing.
- `PROJECT_CONTEXT.md`: compact verified repository context.
- `.claude/agents/`: Orchestrator, Content Manager, Bitrix Developer, QA Tester, Research & Design.
- `.claude/skills/bitrix-task-routing/SKILL.md`: repeatable task classification and handoff procedure.

## Apply
1. Review the files against the current repository and your actual permissions/tooling.
2. Copy the root `CLAUDE.md` to the repository root (merge rather than blindly overwrite existing project instructions).
3. Copy `.claude/` into the repository root.
4. Keep `PROJECT_CONTEXT.md` if useful; it is supplemental and should be updated as verified facts change.
5. In Claude Code, start with a task and ask the Orchestrator to route it. If agents were added after the session began, reload/restart the session if they are not discovered.

## Important
Content Manager assumes a UI/admin workflow. If Claude has no authenticated browser/admin access, it must provide a procedure, not pretend to make changes. The QA agent is intentionally read-only. This package does not make any repository changes or perform Bitrix deployment.
