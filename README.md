# Laravel Claude Code Agent Team

A reusable Claude Code agent setup for **existing and new Laravel projects**.

## Included

- `CLAUDE.md` — main coordinator instructions and project-wide guardrails.
- `.claude/agents/` — specialist subagents:
- `.claude/skills/requirements-to-pr/SKILL.md` — end-to-end requirements-to-PR workflow:
  - `project-analyst.md`
  - `backend-developer.md`
  - `frontend-developer.md`
  - `database-specialist.md`
  - `qa-engineer.md`
  - `security-auditor.md`
  - `code-reviewer.md`
  - `documentation-specialist.md`

## Install

1. Back up your current `CLAUDE.md` and `.claude/` folder if they already exist.
2. Copy the `CLAUDE.md` file and `.claude/` folder from this package into the root of your Laravel repository.
3. Keep your existing project-specific instructions. Merge them rather than blindly overwriting them.
4. Start or restart Claude Code in the repository.
5. Run `/agents` and confirm the specialist agents are listed.
6. Ask Claude to analyse the project before assigning implementation work.

These are project-level agents. To share them across all your repositories, copy the agent files into `~/.claude/agents/` instead. Keep project-specific rules in each repository's `CLAUDE.md`.

## Recommended first prompt

> Read CLAUDE.md and inspect this Laravel repository. Use the project-analyst agent to produce a concise architecture and conventions report. Do not modify any files. Identify the Laravel/PHP versions, application structure, test setup, frontend stack, database approach, and existing patterns. Clearly distinguish verified facts from assumptions.

## Recommended task workflow

1. Main Claude session acts as **Laravel Team Lead**.
2. Project Analyst inspects unfamiliar projects or high-impact changes.
3. Backend / Frontend / Database specialists implement only their assigned scope.
4. QA Engineer runs relevant tests and reports failures.
5. Security Auditor and Code Reviewer independently inspect the changes.
6. Documentation Specialist updates docs when requested or when a feature changes documented behaviour.
7. You approve risky commands, migrations, commits, pushes and pull requests.

## Important operating rules

- Agents do not replace your judgement. Review diffs and test results.
- Never assume a Laravel version, PHP version, database, frontend stack or package is installed. Inspect first.
- Never run production migrations, destructive database commands, deployment commands, or change secrets without explicit approval.
- Do not let two implementation agents edit the same files concurrently.
- For existing projects, preserve established architecture unless the task explicitly requires a change.
- For new projects, use the Laravel version's conventions and the project's chosen starter kit and stack.
- Tests must be reported honestly. Do not claim tests passed unless they were actually run and passed.

## Example prompts

### Existing project feature
> Implement the requested feature in this existing Laravel project. First inspect the relevant code and follow CLAUDE.md. Use the project analyst if the architecture or existing pattern is unclear. Delegate implementation to the appropriate specialist, then ask QA, security and code review agents to inspect the final diff. Do not commit or push.

### New Laravel project
> Help establish this new Laravel project. Inspect the installed Laravel and PHP versions and the selected starter kit first. Propose a minimal structure and implementation plan before creating files. Follow current Laravel conventions and ask before installing packages or making irreversible changes.

### Review only
> Review the current git diff for correctness, security, performance and maintainability. Use the code-reviewer and security-auditor agents. Do not edit files.

## Customisation

After the initial project analysis, add verified project-specific facts to `CLAUDE.md` or scoped rules. Avoid copying assumptions from one Laravel application into another.


## Requirements-to-PR workflow

Use `/requirements-to-pr` in Claude Code after placing/providing the requirement document and related workbook. The skill analyses the inputs, creates a module plan and traceability matrix, executes modules with the relevant agents, verifies the full change, and creates a GitHub PR when all checks pass and `gh` is authenticated. It does not merge the PR.

The workflow may pause for genuinely blocking requirement questions, a dirty pre-existing worktree, material dependency decisions, or safety-sensitive operations. It does not require you to manually run agents for each module.
