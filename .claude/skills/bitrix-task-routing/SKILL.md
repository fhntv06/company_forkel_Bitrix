---
name: bitrix-task-routing
description: Use when a FORKEL request needs role selection, a multi-role handoff, or a decision between Bitrix admin configuration and code implementation.
---

# FORKEL Bitrix task routing

1. Clarify the desired outcome and acceptance criteria if essential details are missing.
2. Inspect `docs/workflow/current-state.md`, `docs/workflow/roles.md`, `docs/workflow/agents-mapping.md`, task queue, and relevant requirements/decisions.
3. Classify:
   - Content or ordinary CMS setting -> `content-manager`.
   - PHP/component/template/API/integration -> `bitrix-developer`.
   - Prototype or frontend HTML/CSS/JS change -> `ui-implementation`.
   - Design/specification/prototype research -> `research-design`.
   - Verification -> `qa-tester`.
   - Mixed -> split into sequenced subtasks.
4. For admin work, confirm that a connected browser/admin tool is actually available. If not, return an operational procedure marked NOT EXECUTED instead of claiming execution.
5. Define an explicit handoff and acceptance criteria before assigning the next role: task ID, scope, allowed files, branch, criteria.
6. After implementation, route to `qa-tester` (independent of the implementer, read-only by default).
7. QA loop: `FAIL` -> confirmed defects to the implementer -> QA retests those defects + short regression. Max 3 rounds, then escalate to the user. `BLOCKED`/`PARTIAL` are not completion.
8. Update project status docs only when the evidence supports the update and the assigned role has authority.
