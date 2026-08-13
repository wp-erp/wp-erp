---
name: erp-security-fix
description: Safely remediate a WP ERP security finding (missing capability check, missing/wrong nonce, IDOR/object-ownership gap, SQL injection, missing input sanitization, unescaped output). Use when fixing an item from SECURITY-AUDIT.md or any reported ERP vulnerability. Enforces evidence-first fixing — verify the real capability map and the real frontend nonce BEFORE editing, never patch on assumption, and always verify (static + live) after.
---

# ERP Security Fix

Remediate one WP ERP security finding at a time, with proof at every step. The
cardinal rule: **never change auth/nonce/query code based on an assumption.**
Read the actual capability map, the actual frontend that calls the handler, and
the actual sink — then fix, then verify.

## Hard rules (do not violate)

1. **No assumption fixes.** Before adding a `current_user_can()`, you MUST know
   (a) which capability the handler *should* require, derived from sibling
   handlers / the role→cap map, not invented; and (b) that the chosen cap is held
   by the legitimate caller role so the fix does not break the UI.
2. **Verify the nonce is actually passed from the frontend.** Before adding or
   changing a nonce check, grep the JS/PHP that calls the endpoint and confirm
   which nonce string it sends. Adding a check for a nonce the frontend never
   sends breaks the feature. If the frontend sends nonce `X`, the handler must
   verify `X`.
3. **Capability ≠ nonce.** A nonce is CSRF/intent only. Authorization needs
   `current_user_can()`. IDOR needs an *object-scoped* check (ownership or
   object-cap), not just a role cap.
4. **One finding = one commit.** Never bundle. Commit message references the
   finding number + file.
5. **Always verify after.** Static (lint + trace) is mandatory; live runtime
   verification is mandatory when the site is up. If the site is down, say so
   explicitly and mark the finding "static-verified, live-pending".

## Reference map (WP ERP infrastructure)

- **Nonce verification helpers:**
  - `includes/Framework/Traits/Ajax.php:17` → `verify_nonce( $action )` =
    `wp_verify_nonce( $_REQUEST['_wpnonce'], $action )`. Pure nonce, NO cap.
  - `modules/hrm/includes/AjaxHandler.php` → `verify_hrm_nonce()` wraps
    `verify_nonce( 'wp-erp-hr-nonce' )`.
  - CRM handlers use `wp_verify_nonce( ..., 'wp-erp-crm-nonce' )` inline.
- **Where nonces are localized to JS (what the frontend actually sends):**
  - `includes/Scripts.php:180` → `wpErp.nonce` = `wp_create_nonce('erp-nonce')`
    (every admin page).
  - HRM: `wp_create_nonce('wp-erp-hr-nonce')` in `modules/hrm/.../HRM.php`.
  - CRM: `wp_create_nonce('wp-erp-crm-nonce')` localized on CRM/settings pages.
  - Accounting: module-specific nonces (e.g. `erp_acct_var`, `export_import_nonce`).
- **Capability / role map:**
  - CRM: `modules/crm/includes/functions-capabilities.php` — role grants +
    `erp_crm_map_meta_caps()` (maps `erp_crm_edit_contact`/`_delete_contact` to a
    `contact_owner` ownership check; `erp_crm_list_contact` is FLAT — no owner map).
  - HRM: `modules/hrm/includes/functions-capabilities.php` —
    `erp_hr_map_meta_caps()` (maps review/employee caps to `reporting_to` /
    department-lead ownership).
  - Core: `manage_options` for admin-level ERP settings handlers.
- **REST controllers** are registered with a `permission_callback`; cookie auth
  carries the REST nonce automatically, so for REST the focus is the
  `permission_callback` capability + object-ownership inside the callback, not a
  manual nonce check.

## Per-finding procedure

For each finding, work through these phases. Do NOT skip a phase.

### Phase 1 — Confirm the defect (read, don't trust the report)
- Open the cited file + function. Read the WHOLE handler and its registration.
- Identify: the tainted source, the sink, every existing guard (nonce, cap,
  cast, `prepare`, ownership). Confirm the gap still exists in current code.
- If the finding is already mitigated, mark it "not-a-bug, verified" and skip —
  do not invent a change.

### Phase 2 — Derive the correct fix from evidence
- **For missing capability:** find 2-3 sibling handlers in the same file that DO
  guard, and copy their cap pattern. Confirm the cap is granted to the
  legitimate caller role in the cap map. Write down the cap + the file:line of
  the proof.
- **For IDOR / object access:** find how the listing/read path scopes the same
  object elsewhere (e.g. `contact_owner = current_user`, `reporting_to`). Mirror
  that ownership check against the object actually being acted on (not a
  separate request param — that decoupling is itself the bug, see finding #3).
- **For nonce:** grep the JS that triggers the endpoint
  (`grep -rn "action.*<ajax-action>" modules/*/assets` and views/`*.php`
  templates). Confirm which nonce it sends. If none is sent, the fix is to add
  nonce emission in the frontend too — note that, don't silently add a
  server-side check that breaks the call.
- **For SQLi:** replace string interpolation with `$wpdb->prepare()` using
  `%d`/`%s`/`%f`; for identifiers (ORDER BY/column/table) use a strict
  whitelist/`absint()`, never `prepare` (it quotes) or `esc_sql` (quotes only).
- **For sanitization/output:** `sanitize_text_field`/`wp_kses_post` on input at
  the boundary; `esc_html`/`esc_attr`/`esc_url` at output. Match the data's
  intent (plain vs rich).

### Phase 3 — Apply the minimal fix
- Smallest change that closes the gap. Match surrounding code style.
- Add a short comment stating the security intent.
- Do not refactor unrelated code.

### Phase 4 — Verify (mandatory)
- **Static:**
  - `php -l <file>` — no syntax errors.
  - Re-read the patched path: confirm tainted source can no longer reach the sink
    unsanitized / unauthorized.
  - Confirm the frontend still satisfies the new check (it sends the nonce / the
    legitimate role holds the cap). If not, fix the frontend too.
- **Live runtime (when the Local site is up):**
  - Bring up the site (`wp option get siteurl` from `app/public` should return a
    URL; if MySQL socket errors, the Local app is stopped — ask the user to
    start it).
  - Exercise the endpoint per role: prove the legitimate role STILL works
    (no regression) and the unauthorized role/IDOR is now BLOCKED.
  - Use throwaway test users per role; restore/clean any mutated state and any
    temporarily-changed credentials afterward (capture original hashes before,
    restore after, verify the restore).
  - Never leave the site or DB in a mutated state.

### Phase 5 — Commit
- `git commit` one finding, message: `Fix #<n>: <category> in <file> (<short>)`.
- Update SECURITY-AUDIT.md: flip the finding's checkbox to ☑ and append a
  one-line note (cap added / nonce verified / live-verified or live-pending).

## Anti-patterns (reject these)
- Adding `current_user_can('manage_options')` to a handler legitimately used by a
  non-admin role → breaks the feature. Derive the *correct* cap.
- Verifying a nonce string the frontend doesn't send → breaks the feature.
- "Fixing" a `prepare`d/int-cast query that is already safe (the verifier killed
  26 such false positives — re-confirm before touching).
- Bundling multiple findings in one commit.
- Claiming "verified" without running `php -l` and, when the site is up,
  exercising the endpoint.
