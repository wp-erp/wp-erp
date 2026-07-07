<?php
/**
 * WP-ERP HR — `erp/v2/employees/{user_id}/permission` REST controller.
 *
 * Endpoints:
 *   GET /erp/v2/employees/{user_id}/permission — whether the employee holds the
 *     HR-manager role.
 *   PUT /erp/v2/employees/{user_id}/permission — toggle the HR-manager role.
 *
 * The Free permission tab is a single toggle (grant/revoke the HR-manager
 * role). The mutation reproduces the legacy `FormHandler::employee_permission()`
 * logic verbatim — `add_role()` / `remove_role()` plus the
 * `erp_hr_after_employee_permission_set` action so pro permission extensions
 * keep firing. `erp/v1` stays untouched.
 *
 * Write requires the HR-manager role itself (same gate the legacy form used).
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WP_REST_Request;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class EmployeePermissionControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'employees';

	/**
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/permission',
			[
				'args' => [
					'user_id' => [
						'description'       => __( 'Unique employee user ID.', 'erp' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => [
						'roles' => [
							'description' => __( 'Map of permission-role key → enabled boolean.', 'erp' ),
							'type'        => 'object',
							'required'    => true,
						],
					],
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);
	}

	/**
	 * Reading requires the create-review meta cap on the target (matches the
	 * legacy tab gate).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_view( $request ): bool {
		return $this->permission_cap( 'erp_create_review', (int) $request['user_id'] );
	}

	/**
	 * Mutating the HR-manager role requires the HR-manager role itself — the same
	 * gate `FormHandler::employee_permission()` enforced.
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return current_user_can( erp_hr_get_manager_role() );
	}

	/**
	 * Build the list of permission roles available for this employee.
	 *
	 * Mirrors the legacy `erp_hr_permission_management` action exactly: each ERP
	 * module rendered its own checkbox there (HR Manager — free, CRM Manager/Agent,
	 * Accounting Manager, Recruiter — pro), gated by whether the *acting* user is a
	 * manager of that module. We reproduce the same set as data so the React tab can
	 * render one toggle per role. The `module` setters that legacy hooked to
	 * `erp_hr_after_employee_permission_set` still do the actual add/remove on save,
	 * so this stays in lock-step with whatever modules are active.
	 *
	 * The list is filterable so other modules can register data-driven entries
	 * instead of (or alongside) the HTML hook.
	 *
	 * @param int $user_id Target employee user ID.
	 *
	 * @return array<int, array<string, mixed>> Available role entries.
	 */
	protected function permission_catalog( int $user_id ): array {
		$roles = [];

		// HR Manager — always present (free). The tab itself is HR-gated.
		$roles[] = [
			'key'         => 'enable_manager',
			'label'       => __( 'HR Manager', 'erp' ),
			'description' => __( 'Grant full HR management capabilities to this employee.', 'erp' ),
			'enabled'     => user_can( $user_id, erp_hr_get_manager_role() ),
		];

		// CRM Manager + Agent — only when the CRM module is active and the acting
		// user is a CRM manager (same gate as `erp_crm_permission_management_field`).
		if ( function_exists( 'erp_crm_is_current_user_manager' ) && erp_crm_is_current_user_manager() ) {
			$roles[] = [
				'key'         => 'crm_manager',
				'label'       => __( 'CRM Manager', 'erp' ),
				'description' => __( 'This Employee is CRM Manager', 'erp' ),
				'enabled'     => user_can( $user_id, erp_crm_get_manager_role() ),
			];
			$roles[] = [
				'key'         => 'crm_agent',
				'label'       => __( 'CRM Agent', 'erp' ),
				'description' => __( 'This Employee is CRM agent', 'erp' ),
				'enabled'     => user_can( $user_id, erp_crm_get_agent_role() ),
			];
		}

		// Accounting Manager — same gate as `Admin::permission_management_field`.
		if ( function_exists( 'erp_ac_is_current_user_manager' ) && erp_ac_is_current_user_manager() ) {
			$roles[] = [
				'key'         => 'acct_manager',
				'label'       => __( 'Accounting Manager', 'erp' ),
				'description' => __( 'This Employee is Accounting Manager', 'erp' ),
				'enabled'     => user_can( $user_id, erp_ac_get_manager_role() ),
			];
		}

		// Recruiter (pro recruitment) — gated by HR-manager, same as
		// `erp_rec_recruiter_role_in_permission`.
		if ( function_exists( 'erp_rec_recruiter_role_in_permission' ) && current_user_can( erp_hr_get_manager_role() ) ) {
			$roles[] = [
				'key'         => 'erp_recruiter',
				'label'       => __( 'Recruiter', 'erp' ),
				'description' => __( 'This Employee is Recruiter', 'erp' ),
				'enabled'     => user_can( $user_id, 'erp_recruiter' ),
			];
		}

		/**
		 * Filters the permission-role catalog for an employee.
		 *
		 * @param array $roles   List of `[ key, label, description, enabled ]` entries.
		 * @param int   $user_id Target employee user ID.
		 */
		return apply_filters( 'erp_hr_employee_permission_roles', $roles, $user_id );
	}

	/**
	 * GET /erp/v2/employees/{user_id}/permission
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		return rest_ensure_response( [ 'roles' => $this->permission_catalog( $user_id ) ] );
	}

	/**
	 * PUT /erp/v2/employees/{user_id}/permission
	 *
	 * Accepts a `roles` map (`{ key: bool }`). The HR-manager role is applied
	 * inline (the free path), then `erp_hr_after_employee_permission_set` fires with
	 * the flattened map so every module's legacy setter (CRM/Accounting/Recruiter)
	 * applies its own role — each gated by the acting user's own manager cap.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$roles = (array) $request['roles'];

		// Normalize the incoming map to plain booleans the legacy setters expect.
		$params = [ 'employee_id' => $user_id ];
		foreach ( $roles as $key => $value ) {
			$params[ (string) $key ] = $this->cast_bool( $value ) ? 'on' : 'off';
		}

		// HR Manager is applied inline (same add/remove logic as the legacy form).
		$hr_manager_role = erp_hr_get_manager_role();
		$enable_manager  = isset( $roles['enable_manager'] ) ? $this->cast_bool( $roles['enable_manager'] ) : user_can( $user, $hr_manager_role );

		if ( $enable_manager && ! user_can( $user, $hr_manager_role ) ) {
			$user->add_role( $hr_manager_role );
		} elseif ( ! $enable_manager && user_can( $user, $hr_manager_role ) ) {
			$user->remove_role( $hr_manager_role );
		}

		/**
		 * Fires after an employee's permission set is updated.
		 *
		 * Mirrors the legacy `FormHandler::employee_permission()` hook so the
		 * CRM / Accounting / Recruiter setters keep firing. The first arg is the
		 * flattened role map (legacy passed `$_POST`); each module reads its own
		 * keys off it and is gated by the acting user's manager cap.
		 */
		do_action( 'erp_hr_after_employee_permission_set', $params, $user );

		return rest_ensure_response( [ 'roles' => $this->permission_catalog( $user_id ) ] );
	}

	/**
	 * JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'employee_permission',
			'type'       => 'object',
			'properties' => [
				'roles' => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'key'         => [ 'type' => 'string' ],
							'label'       => [ 'type' => 'string' ],
							'description' => [ 'type' => 'string' ],
							'enabled'     => [ 'type' => 'boolean' ],
						],
					],
				],
			],
		];
	}
}
