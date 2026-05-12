---
description: Review staged changes (or a specific PR) against WP ERP standards
---

# Review WP ERP changes

Goal: review the diff for correctness, security, WPCS compliance, and project conventions before it gets pushed.

## Inputs
- `$ARGUMENTS` may be: empty (review staged + unstaged), a PR number (e.g. `1234`), or a commit range (e.g. `develop..HEAD`).

## Procedure

1. **Resolve diff source**:
   - If `$ARGUMENTS` is a PR number → `gh pr diff $ARGUMENTS`
   - If it looks like a git range → `git diff $ARGUMENTS`
   - Otherwise → `git status` then `git diff` (unstaged) + `git diff --cached` (staged)

2. **Run phpcs on changed PHP files only** (matches CI):
   ```bash
   git diff --name-only --diff-filter=ACMR develop...HEAD | grep '\.php$' \
     | xargs -r ./vendor/bin/phpcs -q --report=full
   ```

3. **Check the diff for each item below; report violations with file:line and a fix**:
   - **ABSPATH guard** on every new PHP file
   - **Text domain** — every `__()`, `_e()`, `esc_*__()` uses `'erp'`
   - **Escape on output** — `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` at boundaries
   - **Sanitize on input** — `sanitize_text_field`, `absint`, `sanitize_email`, `wp_unslash`
   - **Nonces** on all state-changing AJAX / form handlers (`wp_verify_nonce`, `check_ajax_referer`)
   - **Capability checks** before reads/writes — must use an `erp_*` cap; reuse existing where possible (see [docs/mcp-abilities.md](../../docs/mcp-abilities.md))
   - **`$wpdb->prepare`** for any SQL with variables; `esc_sql()` on raw table names
   - **`wp_date()` not `date()`** for any human-facing date
   - **Cache invalidation** — writers call `erp_cache_set_last_changed( $group, $key )` matching reader's `erp_cache_get_last_changed`
   - **Function prefix** — new globals are `erp_`, `erp_hr_`, `erp_crm_`, or `erp_ac_`
   - **Namespace** PSR-4 matches `composer.json` autoload map
   - **No commits to "do not touch" files** — `composer.lock`, `package-lock.json`, `vendor/`, `build/`, `assets/`, `.env*`, `wp-erp.php` version, `includes/Lib/google/**`
   - **Intentional cap typos preserved** — `erp_crate_announcement`, `erp_crm_manage_activites` must NOT be "fixed"
   - **Abilities API** — any `wp_register_ability*` call wrapped in `function_exists()` guard

4. **Output format**:
   ```
   ## Review summary
   - Risk: low | medium | high
   - phpcs: pass | N errors / M warnings
   - Files reviewed: X

   ## Blocking issues
   - file:line — <issue> — fix: <one-liner>

   ## Suggestions
   - file:line — <improvement>

   ## Looks good
   - <one bullet per file or concern that passes>
   ```

5. If risk is `high` (security, data loss, broken capability check), stop and surface it first before continuing review.
