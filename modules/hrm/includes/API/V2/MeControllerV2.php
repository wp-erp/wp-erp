<?php
/**
 * WP-ERP HR — `erp/v2/me` REST controller.
 *
 * Endpoints:
 *   GET /erp/v2/me/capabilities — current user identity + HR capability map.
 *
 * Consumed once on React boot to hydrate the `erp-hr/me` `@wordpress/data`
 * store. Response shape locked in
 * openspec/changes/redesign-hr-free/playbooks/_first-deliverable.md (§REST
 * contract).
 */

namespace WeDevs\ERP\HRM\API\V2;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class MeControllerV2 extends RestControllerV2 {

	/**
	 * Rest base.
	 *
	 * @var string
	 */
	protected $rest_base = 'me';

	/**
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/capabilities',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_capabilities' ],
					'permission_callback' => [ $this, 'permission_logged_in' ],
				],
				'schema' => [ $this, 'get_capabilities_schema' ],
			]
		);
	}

	/**
	 * GET /erp/v2/me/capabilities
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_capabilities( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );

		$user_id = get_current_user_id();
		$user    = wp_get_current_user();

		$caps_keys    = $this->hr_capability_keys();
		$capabilities = [];

		foreach ( $caps_keys as $cap ) {
			$capabilities[ $cap ] = current_user_can( $cap );
		}

		$payload = [
			'user_id'       => (int) $user_id,
			'display_name'  => $this->cast_string_or_null( $user->display_name ) ?? '',
			'email'         => $this->cast_string_or_null( $user->user_email ) ?? '',
			'avatar_url'    => get_avatar_url( $user_id, [ 'size' => 80 ] ) ?: '',
			'is_pro'        => $this->cast_bool( defined( 'WPERP_PRO_VERSION' ) || class_exists( 'WeDevs_ERP_PRO' ) ),
			'is_hr_manager' => current_user_can( erp_hr_get_manager_role() ) || in_array( erp_hr_get_manager_role(), (array) $user->roles, true ),
			'roles'         => array_values( array_map( 'strval', (array) $user->roles ) ),
			'capabilities'  => $capabilities,
			'preferences'   => [
				'erp_hr_color_scheme' => $this->resolve_color_scheme_preference( $user_id ),
			],
		];

		/**
		 * Filter the `/erp/v2/me/capabilities` response payload.
		 *
		 * Pro plugins can append fields. Pro must never replace a free field.
		 *
		 * @since 1.13.5
		 *
		 * @param array $payload Response payload.
		 * @param int   $user_id Current user ID.
		 */
		$payload = (array) apply_filters( 'erp_hr_v2_me_capabilities', $payload, $user_id );

		return rest_ensure_response( $payload );
	}

	/**
	 * Union of every HR capability key exposed to the React shell.
	 *
	 * Source: erp_hr_get_caps_for_role() for the two HR roles. The
	 * intentional typo `erp_crate_announcement` is preserved verbatim
	 * (see openspec/ground-truth.md §8).
	 *
	 * @return string[]
	 */
	private function hr_capability_keys(): array {
		$manager_caps  = (array) erp_hr_get_caps_for_role( erp_hr_get_manager_role() );
		$employee_caps = (array) erp_hr_get_caps_for_role( erp_hr_get_employee_role() );

		$all = array_keys( array_merge( $manager_caps, $employee_caps ) );

		/**
		 * Filter the capability keys returned to the React shell.
		 *
		 * @since 1.13.5
		 *
		 * @param string[] $all Capability keys.
		 */
		$all = (array) apply_filters( 'erp_hr_v2_me_capability_keys', $all );

		return array_values( array_unique( array_map( 'strval', $all ) ) );
	}

	/**
	 * Read the user's preferred theme mode.
	 *
	 * Returns one of: 'light' | 'dark' | 'auto'. Defaults to 'auto'.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return string
	 */
	private function resolve_color_scheme_preference( int $user_id ): string {
		if ( ! $user_id ) {
			return 'auto';
		}

		$stored = (string) get_user_meta( $user_id, 'erp_hr_color_scheme', true );
		$stored = $this->cast_enum( $stored, [ 'light', 'dark', 'auto' ] );

		return $stored ?? 'auto';
	}

	/**
	 * JSON Schema for `/me/capabilities`.
	 *
	 * @return array
	 */
	public function get_capabilities_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'me-capabilities',
			'type'       => 'object',
			'properties' => [
				'user_id'       => [ 'type' => 'integer' ],
				'display_name'  => [ 'type' => 'string' ],
				'email'         => [ 'type' => 'string' ],
				'avatar_url'    => [ 'type' => 'string' ],
				'is_pro'        => [ 'type' => 'boolean' ],
				'is_hr_manager' => [ 'type' => 'boolean' ],
				'roles'         => [
					'type'  => 'array',
					'items' => [ 'type' => 'string' ],
				],
				'capabilities'  => [
					'type'                 => 'object',
					'additionalProperties' => [ 'type' => 'boolean' ],
				],
				'preferences'   => [
					'type'       => 'object',
					'properties' => [
						'erp_hr_color_scheme' => [
							'type' => 'string',
							'enum' => [ 'light', 'dark', 'auto' ],
						],
					],
				],
			],
		];
	}
}
