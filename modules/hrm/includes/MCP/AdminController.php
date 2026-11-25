<?php
/**
 * WP-ERP HR — REST API behind the MCP settings screen.
 *
 * Endpoints (all under `erp/v2`):
 *   GET    /hrm/mcp/overview       — what the current user can connect to.
 *   GET    /hrm/mcp/settings       — the server's settings (administrators).
 *   POST   /hrm/mcp/settings       — change them (administrators).
 *   GET    /hrm/mcp/users          — find users for an individual access rule.
 *   GET    /hrm/mcp/tokens         — the current user's tokens; `all=1` for everyone's.
 *   POST   /hrm/mcp/tokens         — create a token.
 *   DELETE /hrm/mcp/tokens/{id}    — revoke a token.
 *   GET    /hrm/mcp/log            — the audit log.
 */

namespace WeDevs\ERP\HRM\MCP;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class AdminController {

	/**
	 * The tool catalogue.
	 *
	 * @var ToolRegistry
	 */
	private $registry;

	/**
	 * The token store.
	 *
	 * @var TokenManager
	 */
	private $tokens;

	/**
	 * The audit log.
	 *
	 * @var AuditLog
	 */
	private $audit;

	/**
	 * Constructor.
	 *
	 * @param ToolRegistry $registry The tool catalogue.
	 * @param TokenManager $tokens   The token store.
	 * @param AuditLog     $audit    The audit log.
	 */
	public function __construct( ToolRegistry $registry, TokenManager $tokens, AuditLog $audit ) {
		$this->registry = $registry;
		$this->tokens   = $tokens;
		$this->audit    = $audit;
	}

	/**
	 * Register the routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$namespace = Settings::REST_NAMESPACE;
		$base      = Settings::REST_ROUTE;

		register_rest_route(
			$namespace,
			$base . '/overview',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_overview' ],
				'permission_callback' => 'is_user_logged_in',
			]
		);

		register_rest_route(
			$namespace,
			$base . '/settings',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ $this, 'permission_admin' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'update_settings' ],
					'permission_callback' => [ $this, 'permission_admin' ],
					'args'                => [
						'enabled'            => [ 'type' => 'boolean' ],
						'disabled_tools'     => [
							'type'  => 'array',
							'items' => [ 'type' => 'string' ],
						],
						'allowed_origins'    => [
							'type'  => 'array',
							'items' => [ 'type' => 'string' ],
						],
						'log_retention_days' => [
							'type'    => 'integer',
							'minimum' => 0,
						],
						'access'             => [ 'type' => 'object' ],
					],
				],
			]
		);

		register_rest_route(
			$namespace,
			$base . '/users',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'search_users' ],
				'permission_callback' => [ $this, 'permission_admin' ],
				'args'                => [
					'search' => [
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			$namespace,
			$base . '/tokens',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_tokens' ],
					'permission_callback' => 'is_user_logged_in',
					'args'                => [
						'all' => [
							'type'    => 'boolean',
							'default' => false,
						],
					],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_token' ],
					'permission_callback' => 'is_user_logged_in',
					'args'                => [
						'label'   => [
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						],
						'scope'   => [
							'type'    => 'string',
							'enum'    => TokenManager::SCOPES,
							'default' => 'full',
						],
						'user_id' => [
							'type'    => 'integer',
							'default' => 0,
						],
					],
				],
			]
		);

		register_rest_route(
			$namespace,
			$base . '/tokens/(?P<id>[\d]+)',
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'revoke_token' ],
				'permission_callback' => 'is_user_logged_in',
			]
		);

		register_rest_route(
			$namespace,
			$base . '/log',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_log' ],
				'permission_callback' => 'is_user_logged_in',
				'args'                => [
					'page'     => [
						'type'    => 'integer',
						'default' => 1,
						'minimum' => 1,
					],
					'per_page' => [
						'type'    => 'integer',
						'default' => 20,
						'minimum' => 1,
						'maximum' => 100,
					],
				],
			]
		);
	}

	/**
	 * Only administrators configure the server.
	 *
	 * @return bool
	 */
	public function permission_admin(): bool {
		return Access::is_admin();
	}

	/**
	 * What the current user can connect to.
	 *
	 * @return WP_REST_Response
	 */
	public function get_overview(): WP_REST_Response {
		$user_id = get_current_user_id();
		$enabled = Settings::get()['enabled'];
		$tools   = [];

		if ( $enabled ) {
			foreach ( $this->registry->available_for( $user_id ) as $tool ) {
				$tools[] = $this->tool_summary( $tool );
			}
		}

		return new WP_REST_Response(
			[
				'enabled'      => $enabled,
				'is_admin'     => Access::is_admin(),
				'can_connect'  => $enabled && Access::can_connect( $user_id ),
				'endpoint_url' => Settings::endpoint_url(),
				'groups'       => $this->groups(),
				'levels'       => Access::levels_for( $user_id ),
				'tools'        => $tools,
			]
		);
	}

	/**
	 * The server's settings, with everything the screen needs to edit them.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings(): WP_REST_Response {
		return new WP_REST_Response( $this->settings_payload() );
	}

	/**
	 * Change the settings.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function update_settings( WP_REST_Request $request ): WP_REST_Response {
		$changes = [];

		foreach ( [ 'enabled', 'disabled_tools', 'allowed_origins', 'log_retention_days', 'access' ] as $key ) {
			if ( $request->has_param( $key ) ) {
				$changes[ $key ] = $request->get_param( $key );
			}
		}

		Settings::update( $changes );

		// The tables are normally created on an admin page load, but the
		// switch can be thrown from anywhere that can reach this route.
		if ( ! empty( $changes['enabled'] ) ) {
			MCP::install();
		}

		return new WP_REST_Response( $this->settings_payload() );
	}

	/**
	 * Find users to write an individual access rule for.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function search_users( WP_REST_Request $request ): WP_REST_Response {
		$search = (string) $request->get_param( 'search' );
		$users  = get_users(
			[
				'search'         => '' === $search ? '' : '*' . $search . '*',
				'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
				'role__in'       => array_keys( Access::roles() ),
				'number'         => 20,
				'orderby'        => 'display_name',
			]
		);

		return new WP_REST_Response( array_map( [ $this, 'user_summary' ], wp_list_pluck( $users, 'ID' ) ) );
	}

	/**
	 * List tokens.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_tokens( WP_REST_Request $request ): WP_REST_Response {
		$everyone = $request->get_param( 'all' ) && Access::is_admin();
		$tokens   = $this->tokens->all( $everyone ? null : get_current_user_id() );

		return new WP_REST_Response( array_map( [ $this, 'token_summary' ], $tokens ) );
	}

	/**
	 * Create a token.
	 *
	 * Anyone the administrator has granted access may create one for
	 * themselves. Only an administrator may create one for somebody else.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_token( WP_REST_Request $request ) {
		if ( ! Settings::get()['enabled'] ) {
			return new WP_Error( 'erp_hr_mcp_disabled', __( 'The MCP server is not enabled on this site.', 'erp' ), [ 'status' => 400 ] );
		}

		$user_id = (int) $request->get_param( 'user_id' );

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( get_current_user_id() !== $user_id && ! Access::is_admin() ) {
			return new WP_Error( 'rest_forbidden', __( 'You can only create access tokens for yourself.', 'erp' ), [ 'status' => 403 ] );
		}

		$label = trim( (string) $request->get_param( 'label' ) );

		if ( '' === $label ) {
			return new WP_Error( 'erp_hr_mcp_label_required', __( 'Give the token a name to recognise it by.', 'erp' ), [ 'status' => 400 ] );
		}

		$created = $this->tokens->create( $user_id, $label, (string) $request->get_param( 'scope' ) );

		if ( is_wp_error( $created ) ) {
			return $created;
		}

		$summary          = $this->token_summary( $this->tokens->find( $created['id'] ) );
		$summary['token'] = $created['token'];

		return new WP_REST_Response( $summary, 201 );
	}

	/**
	 * Revoke a token.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function revoke_token( WP_REST_Request $request ) {
		$token = $this->tokens->find( (int) $request['id'] );

		// Somebody else's token is reported as missing rather than
		// forbidden, so ids cannot be probed for.
		if ( ! $token || ( get_current_user_id() !== (int) $token->user_id && ! Access::is_admin() ) ) {
			return new WP_Error( 'erp_hr_mcp_token_not_found', __( 'That token was not found.', 'erp' ), [ 'status' => 404 ] );
		}

		if ( ! $token->revoked_at ) {
			$this->tokens->revoke( (int) $token->id );
		}

		return new WP_REST_Response( $this->token_summary( $this->tokens->find( (int) $token->id ) ) );
	}

	/**
	 * Read the audit log: everything for an administrator, otherwise the
	 * current user's own calls.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_log( WP_REST_Request $request ): WP_REST_Response {
		$per_page = (int) $request->get_param( 'per_page' );
		$page     = (int) $request->get_param( 'page' );
		$user_id  = Access::is_admin() ? null : get_current_user_id();
		$total    = $this->audit->count( $user_id );
		$items    = [];

		foreach ( $this->audit->recent( $per_page, ( $page - 1 ) * $per_page, $user_id ) as $row ) {
			$tool    = $this->registry->get( (string) $row->tool );
			$items[] = [
				'id'         => (int) $row->id,
				'token_id'   => (int) $row->token_id,
				'user'       => $this->user_summary( (int) $row->user_id ),
				'tool'       => (string) $row->tool,
				'tool_title' => $tool ? $tool->get_title() : (string) $row->tool,
				'arguments'  => json_decode( (string) $row->arguments, true ),
				'status'     => (string) $row->status,
				'message'    => $row->message,
				'created_at' => $this->iso_date( $row->created_at ),
			];
		}

		$response = new WP_REST_Response( $items );

		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) max( 1, (int) ceil( $total / $per_page ) ) );

		return $response;
	}

	/**
	 * Everything the settings screen renders.
	 *
	 * @return array<string, mixed>
	 */
	private function settings_payload(): array {
		$settings = Settings::get();
		$tools    = array_map( [ $this, 'tool_summary' ], array_values( $this->registry->all() ) );
		$roles    = [];
		$users    = [];

		$group_slugs = array_keys( Access::groups() );

		foreach ( Access::roles() as $slug => $label ) {
			$levels = [];

			// Sent resolved, defaults included, so the screen shows what is
			// actually in force rather than only what was saved.
			foreach ( $group_slugs as $group ) {
				$levels[ $group ] = $settings['access']['roles'][ $slug ][ $group ] ?? Access::default_level( $slug );
			}

			$roles[] = [
				'slug'   => $slug,
				'label'  => $label,
				'levels' => $levels,
			];
		}

		foreach ( $settings['access']['users'] as $user_id => $rule ) {
			$levels = [];

			foreach ( $group_slugs as $group ) {
				$levels[ $group ] = $rule['groups'][ $group ] ?? Access::LEVEL_NONE;
			}

			$users[] = [
				'user'   => $this->user_summary( (int) $user_id ),
				'mode'   => $rule['mode'],
				'levels' => $levels,
			];
		}

		return [
			'enabled'            => $settings['enabled'],
			'endpoint_url'       => Settings::endpoint_url(),
			'disabled_tools'     => $settings['disabled_tools'],
			'allowed_origins'    => $settings['allowed_origins'],
			'log_retention_days' => $settings['log_retention_days'],
			'levels'             => Access::LEVELS,
			'groups'             => $this->groups(),
			'roles'              => $roles,
			'users'              => $users,
			'tools'              => $tools,
			'is_pro_active'      => class_exists( 'WP_ERP_Pro' ),
		];
	}

	/**
	 * The feature groups, as a list, with a tool count each.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function groups(): array {
		$counts = [];

		foreach ( $this->registry->all() as $tool ) {
			$counts[ $tool->get_group() ] = ( $counts[ $tool->get_group() ] ?? 0 ) + 1;
		}

		$groups = [];

		foreach ( Access::groups() as $slug => $group ) {
			// A group with no tools — a Pro extension that is switched off —
			// would only be a row of switches that do nothing.
			if ( empty( $counts[ $slug ] ) ) {
				continue;
			}

			$groups[] = [
				'slug'        => $slug,
				'label'       => $group['label'],
				'description' => $group['description'],
				'pro'         => $group['pro'],
				'tool_count'  => $counts[ $slug ],
			];
		}

		return $groups;
	}

	/**
	 * A tool, as the screens list it.
	 *
	 * @param Tool $tool The tool.
	 *
	 * @return array<string, mixed>
	 */
	private function tool_summary( Tool $tool ): array {
		return [
			'name'        => $tool->get_name(),
			'title'       => $tool->get_title(),
			'description' => $tool->get_description(),
			'group'       => $tool->get_group(),
			'mode'        => $tool->get_mode(),
			'pro'         => $tool->is_pro(),
		];
	}

	/**
	 * A token, as the screens list it. Never includes the secret.
	 *
	 * @param object $token The token row.
	 *
	 * @return array<string, mixed>
	 */
	private function token_summary( $token ): array {
		return [
			'id'           => (int) $token->id,
			'label'        => (string) $token->label,
			'scope'        => (string) $token->scope,
			'user'         => $this->user_summary( (int) $token->user_id ),
			'created_at'   => $this->iso_date( $token->created_at ),
			'last_used_at' => $this->iso_date( $token->last_used_at ),
			'revoked_at'   => $this->iso_date( $token->revoked_at ),
		];
	}

	/**
	 * A user, as the screens show them.
	 *
	 * @param int $user_id The user id.
	 *
	 * @return array<string, mixed>
	 */
	private function user_summary( int $user_id ): array {
		$user  = get_userdata( $user_id );
		$known = Access::roles();
		$roles = [];

		foreach ( $user ? (array) $user->roles : [] as $role ) {
			if ( isset( $known[ $role ] ) ) {
				$roles[] = $known[ $role ];
			}
		}

		return [
			'id'     => $user_id,
			'name'   => $user ? $user->display_name : __( 'Deleted user', 'erp' ),
			'email'  => $user ? $user->user_email : '',
			'avatar' => $user ? get_avatar_url( $user_id, [ 'size' => 64 ] ) : '',
			'roles'  => $roles,
		];
	}

	/**
	 * A stored UTC datetime as ISO 8601, or null.
	 *
	 * @param string|null $datetime The MySQL datetime.
	 *
	 * @return string|null
	 */
	private function iso_date( $datetime ) {
		return $datetime ? gmdate( 'c', (int) strtotime( $datetime . ' UTC' ) ) : null;
	}
}
