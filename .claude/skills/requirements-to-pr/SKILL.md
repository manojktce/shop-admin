---
name: requirements-to-pr
description: Turn supplied Laravel requirements documents and Excel workbooks into a traceable module plan, automatically coordinate specialist agents, implement and test modules sequentially, and create a separate GitHub pull request for every module. Use when the user provides requirements/specification files and asks to implement modules or deliver PRs.
---

# Requirements-to-PR

You are orchestrating a complete requirements-driven Laravel implementation. The user provides the requirements document and related spreadsheet once.

Automatically coordinate the required specialist agents. The user should not need to manually select, assign or run agents for individual modules.

**IMPORTANT WORKFLOW RULE: One module = One feature branch = One separate GitHub Pull Request.**

Implement modules sequentially. After completing each module and creating its PR, stop and ask the user for confirmation before proceeding to the next module.

Never combine multiple modules into a single PR. Never merge any PR.

## 1. Discover and preserve inputs

- Identify the exact input files and formats. Check the repository and user-provided paths/attachments.
- Do not edit, rename, move, or commit the original source documents unless explicitly asked.
- Read all relevant content.
- For PDF/DOCX/MD/TXT, use available local extraction tools or Claude's document reading capability.
- For XLSX, inspect every relevant worksheet, sheet visibility, headers, populated ranges, formulas, merged cells, and notes/comments when available.
- Prefer a read-only Python `openpyxl` load (`data_only=False`) for workbook structure and formulas; optionally inspect cached values separately.
- Never save the source workbook.
- If a workbook contains formulas, report that formula text and cached values may differ. Do not treat blank-looking formula cells as definitely empty without checking.
- Record source evidence as `filename → page/heading` or `workbook → sheet → row/column`.
- If an input cannot be read completely, state exactly what was not accessible and stop before implementing requirements that depend on it.

## 2. Build a traceability plan

Create a working folder such as `docs/implementation/` only if it does not conflict with project conventions.

Create:

- `requirements-analysis.md`: scope, glossary, business rules, assumptions, conflicts, open questions and source references.
- `module-plan.md`: complete module list, implementation order, dependencies, acceptance criteria, impacted areas and implementation/testing plan.
- `module-status.md`: status per module (`Not started`, `In progress`, `Blocked`, `Implemented`, `Verified`, `PR created`) and evidence.
- A requirement traceability matrix: requirement ID, source reference, module, acceptance criteria, implementation status and test evidence.

Do not invent missing business rules. Separate explicit requirements from assumptions and recommendations.

Identify all modules before implementation.

Present the complete module execution plan to the user before starting implementation.

The user should be able to understand:
- How many modules will be implemented.
- What functionality belongs to each module.
- Which modules depend on other modules.
- The order of implementation.
- What each module's PR will contain.

Do not create a single PR for this complete plan.

## 3. Resolve blockers before coding

Classify findings:

- **Blocking:** contradictory requirements, missing essential field meaning, unclear permissions/ownership, unspecified critical workflow, ambiguous destructive behaviour, or unclear integration contract.
- **Non-blocking:** minor labels, low-risk presentation details, or implementation choices already established by the project.

Ask the user only about blocking items.

Present concise alternatives and explain why a decision is needed.

Do not repeatedly ask for approval of routine agent selection or each implementation decision.

## 4. Inspect the Laravel project

Use `project-analyst` for unfamiliar or complex repositories.

Confirm:

- Laravel and PHP versions, Composer dependencies and package versions.
- Database driver and migration conventions.
- Authentication, authorization, roles/permissions and tenancy boundaries.
- Existing models, controllers, services/actions, routes, API response formats and UI components.
- Frontend stack and asset build commands.
- Existing tests, test database configuration, formatters and static analysis.
- Git branch, remote, worktree state and project-specific instructions.

For existing projects, extend the nearest existing pattern and avoid unnecessary architecture or unrelated refactoring.

For new projects, confirm essential stack choices and use conventions for the installed Laravel version.

## 5. Plan module dependencies

- Group requirements into coherent modules and shared foundations.
- Identify prerequisites such as roles, shared lookup tables, common APIs, and parent/child relationships.
- Build a dependency order.
- Implement shared foundations before dependent modules.
- Run modules sequentially by default.
- Parallel work is allowed only where file ownership and dependencies are clearly disjoint, but GitHub PR creation must still follow the sequential approval workflow.
- Do not let two agents edit the same files concurrently.
- Identify cross-module integration and regression tests.

**Module separation rules:**

- Each logical module must have its own implementation scope.
- Each module must have its own feature branch.
- Each module must have its own commit(s).
- Each module must have its own GitHub PR.
- Do not include unrelated functionality from another module in the current PR.
- Shared foundational functionality must have a clearly defined owner module or its own module and PR.

### Handling dependent modules

If Module B depends on Module A:

- Complete Module A first.
- Create Module A's PR.
- Use an appropriate dependent branch strategy for Module B.
- Prefer stacked PRs when Module B requires unmerged changes from Module A.
- In a stacked PR, target the prerequisite module branch and clearly document the dependency.
- After the prerequisite PR is merged, retarget the dependent PR to the default/base branch.
- Never silently copy the entire implementation of another module into the current module's PR.

If a module can be implemented independently, create its PR directly against the repository's default/base branch.

Explain dependencies in the module plan and PR description.

## 6. Execute the module loop

Execute ONE module at a time.

For every module, automatically:

### Step 1: Prepare

- Read the module's requirements and relevant Excel references.
- Review its acceptance criteria.
- Inspect existing project implementation.
- Confirm prerequisite modules are available.
- Identify affected files, database tables, APIs and frontend components.

### Step 2: Coordinate specialist agents

Automatically assign backend, frontend and database work to the relevant specialist agents.

Use only specialists needed by the module.

Give each specialist:

- A clearly defined task.
- Relevant project paths.
- Acceptance criteria.
- Dependency context.
- Existing architecture and coding conventions.
- Files or areas they are allowed to modify.

Do not require the user to manually launch individual agents.

### Step 3: Implement

- Implement the smallest complete change consistent with the project.
- Reuse existing components, services, models and utilities wherever possible.
- Add/update migrations, backend logic, frontend changes, APIs and validations as required.
- Follow existing Laravel conventions.
- Avoid unnecessary architectural changes.
- Do not introduce unrelated improvements.

### Step 4: Test

Add/update tests for:

- Successful operations.
- Validation failures.
- Authorization and permissions.
- Edge cases.
- Business rules.
- Database relationships.
- API and frontend behaviour where applicable.
- Regressions involving previously completed modules.

Run focused tests and relevant build/static checks.

### Step 5: Review

- Ask `code-reviewer` to inspect the diff.
- Ask `security-auditor` for security-sensitive functionality.
- Resolve confirmed findings.
- Rerun affected checks.
- Verify no unrelated files or functionality were changed.

### Step 6: Update documentation

Update:

- `module-status.md`
- Requirement traceability matrix.
- Relevant implementation documentation.

Record implementation and testing evidence.

### Step 7: Create the module PR

Follow Section 8.

### Step 8: Stop for user approval

After successfully creating the current module's PR:

- Present the implementation summary.
- Present test results.
- Provide the branch name.
- Provide commit hash.
- Provide the GitHub PR URL.
- Explain any dependencies or outstanding issues.

Then STOP.

Ask the user whether to proceed with the next module.

**Do not automatically start the next module until the user confirms.**

When the user confirms, continue from the next uncompleted module using the existing plan and progress documentation. Do not repeat completed work.

### Failure handling

If a module fails:

- Diagnose whether it is a code issue, environment issue or unclear requirement.
- Fix code issues where safe.
- Do not disguise environment failures.
- Do not create a PR claiming successful verification when critical checks are failing.
- Mark the module `Blocked` when necessary.
- Mark dependent modules as blocked if they require the failed module.
- Report the exact blocker and required decision.

## 7. Final verification

After all approved modules have been implemented:

- Run the complete relevant test suite, not just individual module tests.
- Run configured lint, formatting, static-analysis and frontend build checks when relevant.
- Review the complete Git diff and status.
- Check for accidental secrets, `.env` files, local data, source documents, generated files, debug code, unrelated changes and missing migrations.
- Verify acceptance criteria against the traceability matrix.
- Obtain independent code review and security review as appropriate.
- Do not claim unrun tests passed.
- Report exact commands and outcomes.

If PRs depend on one another, clearly identify which regression checks require the prerequisite branches or merged changes.

Do not merge any PR.

## 8. GitHub Pull Request — Mandatory Separate PR Per Module

This section is mandatory.

**Never create one PR containing all modules.**

Every module must have its own feature branch, commit(s), push and GitHub PR.

### 8.1 Initial Git verification

Before starting implementation:

- Verify the worktree is clean.
- Detect the repository's actual default/base branch; do not guess.
- Verify the Git remote.
- Verify GitHub CLI (`gh`) is available and authenticated.
- Record the original Git state.
- If unrelated changes already exist, stop and obtain explicit direction.

### 8.2 Branch naming

Use descriptive module-specific branches.

Examples:

- `feature/customer-management`
- `feature/employee-management`
- `feature/product-management`
- `feature/order-management`
- `feature/payment-management`

Do not reuse one branch for multiple independent modules.

### 8.3 Commit naming

Use module-specific commit messages.

Examples:

- `feat(customer): implement customer management`
- `feat(employee): implement employee management`
- `feat(product): implement product management`
- `feat(order): implement order management`

Use additional logically grouped commits when needed.

### 8.4 Creating each PR

For every module:

1. Verify the correct starting branch and dependency strategy.
2. Create a dedicated feature branch.
3. Implement only the current module's changes.
4. Review staged changes.
5. Stage only task-related files.
6. Commit the implementation.
7. Push the feature branch to GitHub.
8. Create a separate GitHub Pull Request.
9. Verify that the PR was successfully created.
10. Record its URL and details in the implementation tracking documentation.

Use `gh` when available and authenticated.

### 8.5 PR description

Every PR must include:

- Module name and scope.
- Business requirements implemented.
- Requirement document and Excel source references.
- Main files and areas changed.
- Database migrations and schema changes.
- Composer or frontend dependency changes.
- Validation, authorization and security considerations.
- Automated tests added or updated.
- Exact test commands and results.
- Known limitations.
- Dependencies on other module PRs, where applicable.

### 8.6 Strict PR isolation

- One module must never be combined with another module in the same PR.
- Never include unrelated changes.
- Never commit directly to the base branch.
- Never merge any PR.
- Never close or overwrite an earlier module PR without user authorization.
- Preserve all previously created feature branches and commits.
- Do not discard another module's changes.
- Keep implementation documentation and tracking changes in the appropriate module PR or a clearly defined documentation PR. Do not accidentally bundle unrelated module changes into every PR.
- Verify the final PR diff contains only the intended module changes and its explicitly documented prerequisites.

### 8.7 PR failure

If `gh` is unavailable or unauthenticated:

- Do not claim the PR exists.
- Preserve the committed feature branch.
- Report the exact blocker.
- Provide the command the user can run after authentication.
- Resume PR creation when the blocker is resolved.

### 8.8 After each PR

Immediately after successfully creating a module PR:

- Update the module status to `PR created`.
- Record its branch, commit hash and PR URL.
- Present the module completion report.
- Ask the user whether to proceed with the next module.
- Wait for confirmation.

Never automatically merge a PR.

## 9. Final response

After all modules have completed their implementation and PR creation, provide a consolidated report.

Include:

### Requirements
- Input files processed.
- Extraction limitations.
- Number of modules identified.
- Requirement coverage.

### Module implementation

For each module report:

- Module name.
- Implementation status.
- Main functionality implemented.
- Specialist agents involved.
- Tests and verification results.
- Outstanding issues.

### GitHub PR summary

Provide a separate entry for every module:

| Module | Branch | Commit | PR URL | Status |
|---|---|---|---|---|
| Customer | feature/customer-management | Commit hash | PR URL | Open |
| Employee | feature/employee-management | Commit hash | PR URL | Open |
| Product | feature/product-management | Commit hash | PR URL | Open |

Use actual values, never placeholders in the final report.

### Final verification

Include:

- Complete test suite results.
- Regression testing results.
- Build/static-analysis results.
- Migration/dependency changes.
- Outstanding risks and limitations.

### Documentation

Provide locations of:

- `requirements-analysis.md`
- `module-plan.md`
- `module-status.md`
- Requirement traceability matrix.

**Final rules:**

1. Analyse all requirements together before implementation.
2. Automatically coordinate specialist agents.
3. Implement one module at a time.
4. Create one separate feature branch per module.
5. Create one separate GitHub PR per module.
6. Test every module before creating its PR.
7. Stop after every PR and wait for user confirmation.
8. Preserve module dependencies and PR isolation.
9. Never merge any PR.
10. Never claim implementation, testing, pushing or PR creation succeeded without verifying it.