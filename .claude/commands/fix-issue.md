---
description: Pick up a GitHub issue and implement a fix end-to-end
---

# Fix a GitHub issue

Goal: take an issue from "open" to "PR-ready" against WP ERP standards.

## Inputs
- `$ARGUMENTS` = issue number (e.g. `1234`) or a free-text description.

## Procedure

1. **Read the issue**:
   - If numeric → `gh issue view $ARGUMENTS --comments`
   - Note: reproduction steps, expected vs actual, affected module (HRM / CRM / Accounting), severity.

2. **Locate the code**:
   - Module dir: `modules/{hrm|crm|accounting}/`
   - Cross-cutting: `includes/`
   - REST: `includes/API/*Controller.php` or module-level `Admin/API/`
   - MCP ability: `modules/*/includes/functions-abilities.php`
   - Use Grep on key identifiers from the bug report; do not guess paths.

3. **Branch**:
   ```bash
   git checkout develop && git pull
   git checkout -b fix/<issue-number>-<short-slug>
   ```

4. **Write the fix** following [.claude/rules/code-style.md](../rules/code-style.md) and [.claude/rules/wordpress-security.md](../rules/wordpress-security.md). Prefer the smallest change that solves the issue — no drive-by refactors.

5. **Add / update tests** in `tests/acceptance/{HR|CRM|Accounting}/` (or `tests/unit/` if pure logic). Pattern: Codeception Cest. See [.claude/rules/testing.md](../rules/testing.md).

6. **Verify**:
   ```bash
   ./vendor/bin/phpcs <changed-files>
   ./vendor/bin/codecept run acceptance <changed-suite>   # if env configured
   php -l <changed-file>                                   # syntax sanity
   ```

7. **Commit** (small, focused; subject ≤72 chars; body explains *why*):
   ```
   fix: <short summary> (#<issue-number>)

   <why this change, what was broken, what the user-visible effect is>
   ```

8. **Push and open PR** against `develop`:
   ```bash
   git push -u origin HEAD
   gh pr create --base develop --title "..." --body "..."
   ```
   PR body must include: `Closes #<issue>`, a short repro, the root cause in one sentence, and a manual test plan.

9. Run `/review` on the staged diff before pushing.
