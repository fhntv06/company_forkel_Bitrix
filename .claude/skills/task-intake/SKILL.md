---
name: task-intake
description: Use whenever the user gives a new FORKEL task in free text (or via /task). Formalizes it into docs/tasks/TASK-NNN.md, logs it, executes it automatically through the specialist agents with independent QA, and stops at a Pull Request for the owner's review.
---

# FORKEL task intake and automatic execution

The owner writes a task in chat. Everything from formalization to a reviewed-ready Pull Request runs without asking for approval between steps. The only human gate is the owner's review of the PR. You (the main session) act as the Orchestrator and delegate to specialist agents by name with the Agent tool.

## 0. Preconditions
- Read `CLAUDE.md`, `docs/workflow/current-state.md`, `docs/workflow/agents-mapping.md`, `docs/workflow/github-workflow.md`.
- `git fetch`; check `git status`. Do not touch another session's unfinished branch or uncommitted work. Base every task branch on up-to-date `main`.
- Check `docs/workflow/task-queue.md` and `docs/tasks/` for an existing task covering the same thing; if found, continue it instead of creating a duplicate.

## 1. Intake (formalize)
1. ID: `TASK-NNN` = highest number found in `docs/tasks/TASK-*.md` and `task-queue.md`, plus 1 (zero-padded to 3 digits).
2. Classify with `bitrix-task-routing` and pick the responsible agent/role. Split a mixed task into ordered sub-tasks inside one task file.
3. Create `docs/tasks/TASK-NNN.md` from `docs/workflow/task-template.md`, in Russian, fully filled: goal, context (relevant requirements/decisions/files, verified by reading them), allowed/read-only/forbidden paths, expected result, specific testable acceptance criteria, constraints, dependencies, priority. Keep the user's original wording in a "Исходная формулировка" quote at the top.
4. Fill gaps yourself from requirements, decisions and the code; record every inference under a "Допущения" heading in the task file. Do not invent products, prices, contacts, or commercial terms.
5. Ask the owner (AskUserQuestion) ONLY when: two readings of the task lead to materially different work; the task is irreversible or touches production, data, dependencies or architecture; it conflicts with `requirements.md`/`decisions.md`; or essential input only the owner has is missing. Otherwise proceed and flag assumptions in the PR.
6. If the task cannot be done in this checkout (for example Bitrix code is absent in `backend/www/`, see `current-state.md`), set status `BLOCKED` with the reason and the needed action, log it, and stop.

## 2. Log the intake (before any implementation)
- Branch: `role/<role>/TASK-NNN-<short-english-slug>` from `main`.
- Add an entry to `docs/workflow/task-queue.md` in section 5 (before section 6), in the existing format (`### TASK-NNN — Название` with Роль, Приоритет, Статус, Зависимости, Результат, Критерии приёмки, Документы). Status `IN_PROGRESS`.
- Add a work-log entry to `docs/workflow/work-log.md` (append after the last dated entry, before the closing `---` / "Важное ограничение" section; never rewrite old entries): date, `TASK-NNN — intake`, role, goal, assumptions.
- Commit as `task: TASK-NNN intake — <title>` and push the branch, so the task and its log exist even if execution fails.

## 3. Execute
- Delegate to the responsible agent(s) with a handoff packet: task ID, goal, allowed/forbidden files, acceptance criteria, branch, assumptions. Independent changes may run in parallel only if they touch different files.
- Implementers commit to the task branch. No force push, no merge into `main`.
- Then delegate verification to `qa-tester` (a different agent from the implementer) with the exact commit ref and acceptance criteria. QA writes its report to `docs/qa/`.
- QA loop: `FAIL` -> send confirmed defects (by ID) to the implementer -> QA retests those defects plus a short regression. Maximum 3 rounds, then stop and escalate to the owner with the open defects.
- `BLOCKED` or `PARTIAL` is not completion: report exactly what was not verified.
- Never claim a check that was not actually run.

## 4. Finish at the review gate
1. Fill section 8 of the task file (result, changed files, checks with real commands/results, untested scenarios, deviations, known issues, branch, PR) and section 9 (next role/open questions).
2. Set the task status in `task-queue.md` to `REVIEW`; append a work-log entry with the outcome and commit SHAs. Update `current-state.md` / `handoff.md` only if the change really affects them.
3. Commit, push, and open a PR into `main` using `.github/pull_request_template.md` (task ID, role, what changed, boundaries, real check results, risks/assumptions). Leave the "Проверка ревьюером" and "Решение" boxes unchecked.
4. Tell the owner in a few lines: task ID, QA status (PASS/FAIL/BLOCKED/PARTIAL), PR link, the assumptions you made, and what is worth checking in review.
5. STOP. Do not merge. Do not set `DONE` — only the owner's acceptance does that.

## 5. Review feedback
When the owner comments or asks for changes, continue on the same branch and task ID: fix, rerun QA on the changed parts, add a work-log entry, update the PR. After the owner accepts and merges, set the task to `DONE` in the queue (in a follow-up change on a new branch).
