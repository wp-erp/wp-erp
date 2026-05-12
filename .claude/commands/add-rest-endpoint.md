---
description: Scaffold a new REST endpoint under namespace erp/v1
---

# Add a REST endpoint

Goal: register a new route on the `erp/v1` namespace following the project's `REST_Controller` pattern.

## Inputs
- `$ARGUMENTS` = `<module> <resource>` e.g. `hrm departments-archive`. Module = `hrm | crm | accounting | core`.

## Procedure

1. **Pick the controller location**:
   - Core / cross-cutting → `includes/API/<Name>Controller.php` (namespace `WeDevs\ERP\API`)
   - Module-specific → `modules/<module>/includes/Admin/API/<Name>Controller.php` (namespace `WeDevs\ERP\<Module>\API`)

2. **Scaffold the class** extending `WeDevs\ERP\API\REST_Controller`:
   - `protected $namespace = 'erp/v1';`
   - `protected $rest_base = '<resource-slug>';`
   - Implement `register_routes()` calling `register_rest_route( $this->namespace, '/' . $this->rest_base, ... )`
   - Every route MUST have a `permission_callback` that checks `current_user_can( 'erp_…' )` — reuse an existing capability (see [docs/mcp-abilities.md](../../docs/mcp-abilities.md)). Never `__return_true`.

3. **Register the controller** in [includes/API/ApiRegistrar.php](../../includes/API/ApiRegistrar.php) (or the module's equivalent registrar).

4. **Define `get_item_schema()`** — JSON Schema for the resource. `get_collection_params()` is inherited; extend if you need extra filter params.

5. **Sanitize inputs, escape outputs**. Validate with `validate_callback` and `sanitize_callback` on every route arg.

6. **Cache reads** via `erp_cache_get_last_changed( $group, $key )`. Writers MUST call `erp_cache_set_last_changed`.

7. **Mirror as an MCP ability** if the endpoint is something AI assistants should call — add to `modules/<module>/includes/functions-abilities.php`. See [.claude/rules/mcp-abilities.md](../rules/mcp-abilities.md).

8. **Test**:
   ```bash
   ./vendor/bin/phpcs <new-files>
   # smoke test against a running site:
   curl -u user:app-password https://your-site.local/wp-json/erp/v1/<rest_base>
   ```

9. **Document** the endpoint inline (PHPDoc on the controller class).

## Reference
- Base class: [includes/API/REST_Controller.php](../../includes/API/REST_Controller.php)
- Existing controller examples: [includes/API/ContactsController.php](../../includes/API/ContactsController.php), [includes/API/CompanyController.php](../../includes/API/CompanyController.php)
