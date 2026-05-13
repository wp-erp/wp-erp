---
description: Register a new MCP ability for an ERP operation
---

# Add an MCP ability

Goal: expose an ERP operation as a WP Abilities API entry so MCP clients can call it.

## Inputs
- `$ARGUMENTS` = `<module> <ability-slug>` e.g. `hrm hrm-archive-employee`. Module = `hrm | crm | accounting`.

## Procedure

1. **Open the right file**:
   - HRM → [modules/hrm/includes/functions-abilities.php](../../modules/hrm/includes/functions-abilities.php)
   - CRM → [modules/crm/includes/functions-abilities.php](../../modules/crm/includes/functions-abilities.php)
   - Accounting → `modules/accounting/includes/functions-abilities.php` (create if missing, follow CRM file structure)

2. **Pick capability**: reuse an existing `erp_*` cap from [docs/mcp-abilities.md](../../docs/mcp-abilities.md). Inventing a new capability requires updating the role map in `includes/Framework/` and is a bigger change — call this out before doing it.

3. **Register the ability** inside the existing `erp_<module>_register_abilities()` function:
   ```php
   wp_register_ability(
       'wp-erp/<slug>',
       [
           'label'        => __( 'Human-readable name', 'erp' ),
           'description'  => __( 'What it does, in one sentence.', 'erp' ),
           'category'     => 'wp-erp-<module>',
           'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
           'input_schema' => [
               'type'       => 'object',
               'required'   => [ /* required fields */ ],
               'properties' => [
                   // JSON Schema for every input
               ],
           ],
           'output_schema' => [
               'type'       => 'object',
               'properties' => [
                   // JSON Schema for the return value
               ],
           ],
           'permission_callback' => function () {
               return current_user_can( 'erp_<cap>' );
           },
           'execute_callback'    => function ( $input ) {
               // Call the existing erp_*() function; return arrays, not WP_Error objects for happy path.
               // For errors, return [ 'error' => '...' ] or use WP_Error per Abilities API conventions.
           },
       ]
   );
   ```

4. **Always wrap registration in `function_exists( 'wp_register_ability' )`** — keeps WP < 6.9 safe.

5. **Use an existing `erp_*()` function in `execute_callback`** — do NOT duplicate business logic. If no function exists, write one in the matching `functions-*.php` first, then call it from the ability.

6. **Update [docs/mcp-abilities.md](../../docs/mcp-abilities.md)**:
   - Add the ability ID + label + required capability to the module's table
   - Add an example request + response

7. **Test**:
   ```bash
   ./vendor/bin/phpcs modules/<module>/includes/functions-abilities.php
   # In a WP 6.9+ install with wp-mcp plugin active:
   curl -u user:app-pw https://site.local/wp-json/mcp/v1/tools | jq '.[] | select(.name=="wp-erp/<slug>")'
   ```

8. Commit with a message that names the ability slug. PR description should link the docs update.

## Reference
- Pattern: [modules/hrm/includes/functions-abilities.php](../../modules/hrm/includes/functions-abilities.php)
- Doc: [docs/mcp-abilities.md](../../docs/mcp-abilities.md)
