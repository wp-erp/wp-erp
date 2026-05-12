---
name: security-review
description: Audit a WP ERP change (file, directory, diff, or PR) for WordPress-plugin security issues — missing nonces, capability checks, escaping, sanitization, SQL injection, broken auth on REST routes and MCP abilities. Trigger when the user asks for a "security review", "security audit", "check for vulns", "audit this change", or before merging changes that touch REST controllers, AJAX handlers, form handlers, abilities, file uploads, or `$wpdb` queries.
---

# WP ERP security review

A scoped check for the WordPress plugin attack surface. The plugin handles HR PII, CRM contacts, and financial data — assume every write path is a target.

## Procedure

1. **Resolve scope**:
   - File / dir → read directly
   - Diff → `git diff` (or `git diff --cached`, or `gh pr diff <n>`)
   - "Whole module" → enumerate `modules/<m>/includes/AjaxHandler.php`, `FormHandler.php`, `Admin/API/*.php`, `includes/API/*.php`, `functions-abilities.php`

2. **For every state-changing entry point, verify all four**:
   1. **Nonce** — `check_admin_referer`, `check_ajax_referer`, or schema-validated REST request
   2. **Capability** — `current_user_can( 'erp_*' )` (NOT `manage_options` for ERP actions)
   3. **Sanitization** — `wp_unslash()` + appropriate `sanitize_*` on every superglobal access
   4. **Output escaping** — `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`

3. **SQL audit**: grep for `$wpdb->` in scope. Every variable in SQL must go through `$wpdb->prepare()`. Every dynamic table name must go through `esc_sql()`.

4. **REST controller audit**: every `register_rest_route` call needs a real `permission_callback` (never `__return_true` for ERP data).

5. **Abilities audit**: every `wp_register_ability` needs `permission_callback => current_user_can( 'erp_*' )`. Confirm the cap matches the operation's risk level (read → list cap, write → create/edit cap, delete → delete cap).

6. **File operation audit**: any `include`, `require`, `unlink`, `wp_handle_upload` taking user-controlled paths needs whitelisting.

7. **Secret leakage**: grep for hardcoded `password`, `api_key`, `secret`, `token`, `SMTP` literals.

8. **Capability list integrity**: confirm intentional typos (`erp_crate_announcement`, `erp_crm_manage_activites`) are unchanged — see commit `a2e24c73`.

## Report format

```
## Security review — <scope>

### Critical (must fix before merge)
- file:line — <issue> — fix: <one-liner>

### High
- ...

### Medium / hardening
- ...

### Clean
- <area>: <what was checked>
```

## Gotchas specific to this repo

- AJAX handlers sometimes live in `<Module>/AjaxHandler.php` — easy to miss when scanning by filename.
- Capability check inside a method called by AJAX **and** by CLI: confirm both paths are intended (CLI usually bypasses caps).
- `erp_peoplemeta` shared between CRM and HRM — leaks across modules if filtered incorrectly.
- Abilities API: missing `function_exists` guard breaks the plugin on WP < 6.9 (availability, not security, but flag it).
