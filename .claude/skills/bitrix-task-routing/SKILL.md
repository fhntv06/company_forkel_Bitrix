---
name: bitrix-task-routing
description: Use when a FORKEL request needs role selection, a multi-role handoff, or a decision between Bitrix admin configuration and code implementation.
---

# FORKEL Bitrix task routing

1. Clarify the desired outcome and acceptance criteria if essential details are missing.
2. Inspect `docs/workflow/current-state.md`, `docs/workflow/roles.md`, task queue, and relevant requirements/decisions.
3. Classify:
   - Content or ordinary CMS setting → Content Manager.
   - PHP/component/template/API/integration → Bitrix Developer.
   - Design/specification/prototype → Research & Design.
   - Verification → QA Tester.
   - Mixed → split into sequenced subtasks.
4. For admin work, confirm that a connected browser/admin tool is actually available. If not, return an operational procedure instead of claiming execution.
5. Define an explicit handoff and acceptance criteria before assigning the next role.
6. Keep QA independent and read-only by default.
7. Update project status docs only when the evidence supports the update and the assigned role has authority.
