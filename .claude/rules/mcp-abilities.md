# MCP Abilities rules — WP ERP

WP ERP registers operations as **WordPress Abilities API** entries (WordPress 6.9+), which the [WordPress MCP Adapter](https://wordpress.org/plugins/wp-mcp/) exposes to AI clients. The authoritative user-facing doc is [docs/mcp-abilities.md](../../docs/mcp-abilities.md) — keep it in sync with the code.

## Where abilities live

- HRM: [modules/hrm/includes/functions-abilities.php](../../modules/hrm/includes/functions-abilities.php)
- CRM: [modules/crm/includes/functions-abilities.php](../../modules/crm/includes/functions-abilities.php)
- Accounting: same path under `modules/accounting/`

Each file:
1. Registers a category on `wp_abilities_api_categories_init` (slugs: `wp-erp-hrm`, `wp-erp-crm`, `wp-erp-ac`).
2. Registers individual abilities on `wp_abilities_api_init`.

## Naming

- **Ability ID** is namespaced: `wp-erp/<module>-<verb>-<noun>` e.g. `wp-erp/hrm-list-employees`, `wp-erp/ac-create-customer`.
- **Category** matches the module: `wp-erp-hrm`, `wp-erp-crm`, `wp-erp-ac`.

## Required shape

```php
wp_register_ability( 'wp-erp/<slug>', [
    'label'        => __( 'Human label', 'erp' ),
    'description'  => __( 'One-sentence description.', 'erp' ),
    'category'     => 'wp-erp-<module>',
    'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],

    'input_schema' => [
        'type'       => 'object',
        'required'   => [ /* required field names */ ],
        'properties' => [ /* JSON Schema for each input */ ],
    ],

    'output_schema' => [
        'type'       => 'object',
        'properties' => [ /* JSON Schema for the response */ ],
    ],

    'permission_callback' => function () {
        return current_user_can( 'erp_<existing-cap>' );
    },

    'execute_callback' => function ( $input ) {
        // Call the existing erp_*() helper. Do NOT duplicate business logic.
        $result = erp_hr_get_employees( $input );
        return [ 'employees' => $result, 'total' => count( $result ) ];
    },
] );
```

## Guards

Always wrap registration calls in `function_exists()`:

```php
if ( ! function_exists( 'wp_register_ability' ) ) {
    return;
}
```

This is what keeps activating the plugin on WP < 6.9 safe.

## Permission model

- Reuse an existing `erp_*` capability — see the table in [docs/mcp-abilities.md](../../docs/mcp-abilities.md).
- Inventing a new capability touches role definitions in `includes/Framework/` and is a larger change.
- Auth in MCP comes from a WordPress Application Password — the user behind it is the user `current_user_can` checks.

## Execute callback rules

- Thin wrapper around an existing `erp_*()` function or model method.
- Sanitize inputs even though the Abilities API validates schema (defense in depth).
- Return arrays / scalars matching `output_schema`. Use `WP_Error` for failures so MCP surfaces them.
- Don't echo or `wp_die()` — abilities return data.

## Documentation

Every new / changed ability must update [docs/mcp-abilities.md](../../docs/mcp-abilities.md):

1. Add to (or modify) the per-module table (Ability ID | Label | Required capability).
2. Add a JSON example request + response under the module's examples section.

## Don't

- Don't add abilities without updating the docs file.
- Don't put business logic inside `execute_callback` — call existing helpers.
- Don't pick a capability based on what *you* have — pick what the operation requires (least privilege).
- Don't change an ability's input/output schema after release without a migration plan — MCP clients cache schemas.
