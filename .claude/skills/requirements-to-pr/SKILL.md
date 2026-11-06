---
name: requirements-to-pr
description: Turn a supplied Laravel requirements document and related Excel workbook into a traceable module plan, implement modules with specialist agents, verify the result, and create a GitHub pull request. Use when the user provides requirements/specification files and asks to build the modules or deliver a PR.
---

# Requirements-to-PR

You are orchestrating a complete requirements-driven Laravel implementation. The user provides the requirements document and related spreadsheet once. Do not make them manually select or run agents module by module.

## 1. Discover and preserve inputs

- Identify the exact input files and formats. Check the repository and user-provided paths/attachments.
- Do not edit, rename, move, or commit the original source documents unless explicitly asked.
- Read all relevant content. For PDF/DOCX/MD/TXT, use available local extraction tools or Claude's document reading capability. For XLSX, inspect every relevant worksheet, sheet visibility, headers, populated ranges, formulas, merged cells, and notes/comments when available. Prefer a read-only Python `openpyxl` load (`data_only=False`) for workbook structure and formulas; optionally inspect cached values separately. Never save the source workbook.
- If a workbook contains formulas, report that formula text and cached values may differ. Do not treat blank-looking formula cells as definitely empty without checking.
- Record source evidence as `filename → page/heading` or `workbook → sheet → row/column`.
- If an input cannot be read completely, state exactly what was not accessible and stop before implementing requirements that depend on it.

## 2. Build a traceability plan

Create a working folder such as `docs/implementation/` only if it does not conflict with project conventions. Create:
- `requirements-analysis.md`: scope, glossary, business rules, assumptions, conflicts, open questions, source references.
- `module-plan.md`: module order, dependencies, acceptance criteria, impacted areas, implementation/testing plan.
- `module-status.md`: status per module (`Not started`, `In progress`, `Blocked`, `Implemented`, `Verified`) and evidence.
- A requirement traceability matrix: requirement ID, source reference, module, acceptance criteria, implementation status, test evidence.

Do not invent missing business rules. Separate explicit requirements from assumptions and recommendations.

## 3. Resolve blockers before coding

Classify findings:
- **Blocking:** contradictory requirements, missing essential field meaning, unclear permissions/ownership, unspecified critical workflow, ambiguous destructive behaviour, or unclear integration contract.
- **Non-blocking:** minor labels, low-risk presentation details, or implementation choices already established by the project.

Ask the user only about blocking items. Present concise alternatives and explain why a decision is needed. Do not repeatedly ask for approval of routine agent selection or each module.

## 4. Inspect the Laravel project

Use `project-analyst` for unfamiliar or complex repositories. Confirm:
- Laravel and PHP versions, Composer dependencies and package versions.
- Database driver and migration conventions.
- Authentication, authorization, roles/permissions and tenancy boundaries.
- Existing models, controllers, services/actions, routes, API response formats and UI components.
- Frontend stack and asset build commands.
- Existing tests, test database configuration, formatters and static analysis.
- Git branch, remote, worktree state and project-specific instructions.

For existing projects, extend the nearest existing pattern and avoid unnecessary architecture or unrelated refactoring. For new projects, confirm essential stack choices and use conventions for the installed Laravel version.

## 5. Plan module dependencies

- Group requirements into coherent modules and shared foundations.
- Identify prerequisites such as roles, shared lookup tables, common APIs, and parent/child relationships.
- Build a dependency order. Implement shared foundations before dependent modules.
- Run modules sequentially by default. Parallel work is allowed only where file ownership and dependencies are clearly disjoint.
- Do not let two agents edit the same files concurrently.
- Identify cross-module integration and regression tests.

## 6. Execute the module loop

For each module, automatically:
1. Assign backend, frontend and database work to the relevant specialist(s). Use only specialists needed by the module.
2. Give each specialist a bounded scope, relevant paths, acceptance criteria and dependency context.
3. Implement the smallest complete change consistent with the project.
4. Add/update tests for success, validation, authorization, edge cases and regressions as relevant.
5. Run focused tests and relevant build/static checks.
6. Ask `code-reviewer` to inspect the diff. Ask `security-auditor` for security-sensitive functionality.
7. Resolve confirmed findings and rerun affected checks.
8. Update `module-status.md` and traceability evidence.
9. Move to the next unblocked module.

If a module fails, diagnose whether it is a code issue, environment issue, or unclear requirement. Fix code issues where safe. Do not disguise environment failures. Continue independent modules only if they do not depend on the failed module. Mark dependent modules blocked.

## 7. Final verification

After all modules:
- Run the complete relevant test suite, not just individual module tests.
- Run configured lint, formatting, static-analysis and frontend build checks when relevant.
- Review the complete Git diff and status.
- Check for accidental secrets, `.env` files, local data, source documents, generated files, debug code, unrelated changes and missing migrations.
- Verify acceptance criteria against the traceability matrix.
- Obtain independent code review and security review as appropriate.
- Do not claim unrun tests passed. Report exact commands and outcomes.

## 8. GitHub pull request

The user has requested a PR as the deliverable:
- Verify the worktree was clean before starting, or obtain explicit direction for existing changes.
- Detect the repository's actual default/base branch; do not guess.
- Create a descriptive feature branch, never work directly on the base branch.
- Commit only task-related files. Use coherent commit messages.
- Push the feature branch and create one PR against the correct base branch using `gh` when authenticated.
- Include a PR title and description with summary, modules, requirement coverage, database/migration notes, test commands/results, risks and limitations.
- Never merge the PR.
- If `gh` is unavailable or unauthenticated, do not claim the PR exists. Report the blocker and exact command the user can run after authenticating.

## 9. Final response

Report:
- Input files processed and any extraction limitations.
- Modules identified, implemented, verified, blocked or deferred.
- Requirement coverage and traceability document locations.
- Files/major areas changed.
- Exact tests/checks run with pass/fail/not-run status.
- Migration/dependency changes and operational risks.
- Branch, commit and PR URL if successfully created.
- Any remaining manual decisions.

Be transparent. A PR is not proof that every requirement is correct; show the evidence and remaining limitations.
