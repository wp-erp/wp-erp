# REST API rules — WP ERP

## Namespace

All ERP REST routes use namespace **`erp/v1`** (`/wp-json/erp/v1/...`).

## Controller pattern

Every controller extends `WeDevs\ERP\API\REST_Controller` ([includes/API/REST_Controller.php](../../includes/API/REST_Controller.php)):

```php
namespace WeDevs\ERP\API;

class WidgetsController extends REST_Controller {
    protected $namespace = 'erp/v1';
    protected $rest_base = 'widgets';

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_widget' );
                },
                'args'                => $this->get_collection_params(),
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_item' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_widget' );
                },
                'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
            ],
        ] );
    }

    public function get_item_schema() {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'widget',
            'type'       => 'object',
            'properties' => [ /* ... */ ],
        ];
    }
}
```

Registration lives in [includes/API/ApiRegistrar.php](../../includes/API/ApiRegistrar.php) (core) or `modules/<m>/includes/Admin/API/` (module).

## Required for every route

| Element | Why |
|---|---|
| `permission_callback` calling `current_user_can('erp_…')` | Auth |
| `args` with `sanitize_callback` + `validate_callback` per param | Input safety |
| `get_item_schema()` | Auto-generates `OPTIONS` response + arg coercion |
| Cache invalidation via `erp_cache_set_last_changed` on writes | Stale-data prevention |
| `WP_REST_Response` with proper status code | Consistent client behavior |

## Don't

- Don't use `permission_callback => '__return_true'` outside genuinely public routes (there are almost none).
- Don't return raw `$wpdb` rows — shape them into the schema first.
- Don't accept and trust IDs without an existence check (`get_post`, `get_user_by`, Eloquent `find`).
- Don't roll a new namespace — `erp/v1` is the contract.

## Cross-reference

If the new endpoint is something AI assistants should call, also expose it as an MCP ability — see [.claude/rules/mcp-abilities.md](mcp-abilities.md).
