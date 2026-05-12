---
name: mcp-ability
description: Register, modify, or audit a WordPress Abilities API entry exposed by WP ERP for the MCP adapter. Trigger when the user asks to "add an MCP ability", "register a new ability", "expose this to MCP", "make this callable by Claude / AI", or when modifying any `modules/*/includes/functions-abilities.php` file or `docs/mcp-abilities.md`.
---

# MCP ability workflow

Goal: keep the WP ERP ↔ MCP surface coherent — code registration matches docs, every ability has a real cap check, every ability wraps an existing helper rather than re-implementing.

## Procedure

1. **Locate the right file**:
   - HRM → `modules/hrm/includes/functions-abilities.php`
   - CRM → `modules/crm/includes/functions-abilities.php`
   - Accounting → `modules/accounting/includes/functions-abilities.php`
   - The doc → `docs/mcp-abilities.md`

2. **Pick the slug**: `wp-erp/<module-prefix>-<verb>-<noun>` where prefix is `hrm`, `crm`, or `ac`. Check the doc table for collisions.

3. **Pick the capability**: from the table in `docs/mcp-abilities.md`. Match operation type:
   - read one / list → `erp_view_*`, `erp_list_*`, `erp_ac_view_*`
   - create → `erp_create_*`, `erp_*_add_*`
   - update → `erp_edit_*`
   - delete → `erp_delete_*`
   - Inventing a new cap is a bigger change — surface that, don't silently do it.

4. **Find the existing helper** to call from `execute_callback`. Grep `modules/<m>/includes/functions-*.php`. If none exists, write one in the matching `functions-*.php` first, then call it from the ability — never duplicate logic inline.

5. **Register**:
   - Inside `erp_<module>_register_abilities()`, in the right `// ── Section ──` block.
   - Wrap in `function_exists( 'wp_register_ability' )` (the enclosing function already does — verify).
   - Use `__( …, 'erp' )` for labels and descriptions.
   - `meta` always: `[ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ]`.
   - `input_schema` and `output_schema` strict — list `required`, type each property.

6. **Update `docs/mcp-abilities.md`**:
   - Add a row to the module's table (Ability ID | Label | Required capability).
   - Add a JSON example request + response under the module's examples.

7. **Verify**:
   ```bash
   php -l modules/<m>/includes/functions-abilities.php
   ./vendor/bin/phpcs modules/<m>/includes/functions-abilities.php
   ```
   If a WP 6.9+ install with the MCP adapter is available:
   ```bash
   curl -u user:app-pw https://site.local/wp-json/mcp/v1/tools | jq '.[] | select(.name=="wp-erp/<slug>")'
   ```

## Gotchas

- Schema changes after release break cached client schemas. Treat as semver-major for the ability.
- Permission callback runs in WP user context — the user is whoever owns the Application Password the MCP client sent.
- Don't return `WP_Error` from `execute_callback` unless the failure is user-actionable — generic infrastructure errors should be logged + a clean error returned.
- Removing a built-in ability requires `wp_unregister_ability` at priority 20+, not deleting the registration.
