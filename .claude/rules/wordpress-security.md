# Security rules — WP ERP

WP ERP holds HR (PII), CRM (contacts), and Accounting (financial) data. Every write path is a potential CSRF / privilege-escalation target. Apply WordPress's standard four lines of defense to **every** request handler.

## 1. Sanitize input

Apply at the boundary, *before* using a value.

| Input shape | Function |
|---|---|
| free text | `sanitize_text_field( wp_unslash( $value ) )` |
| email | `sanitize_email( wp_unslash( $value ) )` |
| URL | `esc_url_raw( wp_unslash( $value ) )` |
| integer ID | `absint( $value )` |
| array of IDs | `array_map( 'absint', (array) $value )` |
| HTML allowed | `wp_kses_post( wp_unslash( $value ) )` |
| key / slug | `sanitize_key( $value )` |
| textarea | `sanitize_textarea_field( wp_unslash( $value ) )` |

**Always `wp_unslash()` before sanitizing** `$_GET`/`$_POST`/`$_REQUEST` — WordPress magic-slashes them.

## 2. Verify nonces

Every state-changing AJAX / form handler MUST check a nonce:

```php
// Form
check_admin_referer( 'erp-employee-create', '_wpnonce' );

// AJAX
check_ajax_referer( 'wp-erp-hr-nonce', 'nonce' );
```

If the nonce check fails, `wp_die()` or `wp_send_json_error()` — never proceed.

## 3. Check capability

The capability check is the auth boundary, not the nonce. After the nonce passes:

```php
if ( ! current_user_can( 'erp_edit_employee' ) ) {
    wp_send_json_error( [ 'message' => __( 'Permission denied.', 'erp' ) ], 403 );
}
```

- Always use an `erp_*` capability — never `manage_options` for ERP-specific actions.
- Reuse existing caps; the canonical list is in [docs/mcp-abilities.md](../../docs/mcp-abilities.md).
- REST `permission_callback` must do the same — never `__return_true`.

## 4. Escape on output

Apply at the *output* (in templates / responses), not at storage.

| Context | Function |
|---|---|
| HTML body text | `esc_html()` |
| HTML attribute | `esc_attr()` |
| URL | `esc_url()` |
| JS data | `wp_json_encode()` |
| translated HTML | `esc_html__()`, `esc_attr__()` |
| rich text | `wp_kses_post()` |

Even for translated strings: `esc_html__( '…', 'erp' )`.

## SQL

- Use `$wpdb->prepare()` for **every** query with a variable:
  ```php
  $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_hr_employees WHERE id = %d", $id );
  ```
- For raw table names that change at runtime, use `esc_sql()` — see commit `699663a6`:
  ```php
  $table = esc_sql( "{$wpdb->prefix}erp_hr_employees" );
  ```
- Prefer Eloquent models (under `Models/`) for non-trivial reads/writes — they parameterize automatically.
- `phpcs` flags `WordPress.DB.PreparedSQL.NotPrepared` as a warning — treat as blocking.

## File operations

- Uploads must go through `wp_handle_upload()` with a MIME-type whitelist.
- Never `include` / `require` a path derived from user input.
- Never `unlink()` an arbitrary path — sanitize against `WP_CONTENT_DIR` first.

## Secrets

- Never log, echo, or commit `.env`, app passwords, OAuth tokens, SMTP creds.
- Don't include API keys in `appsero.json`, `package.json`, or any committed file.

## Common mistakes I should reject

- `current_user_can( 'manage_options' )` for an ERP action → use the matching `erp_*` cap.
- Missing `wp_unslash()` before `sanitize_*` on superglobals.
- Escaping at storage and again at output → double-encoded HTML in UI.
- Calling `$wpdb->query( "SELECT * FROM x WHERE id=$id" )` → SQLi.
- `__return_true` as a REST `permission_callback`.
- Skipping the ABSPATH guard on a new file.
