<?php
/**
 * WP-ERP HR — employee create-flow helpers REST controller.
 *
 *   GET  /erp/v2/employees/check-user?email=…  — does a WP user / employee
 *        already exist for this email?
 *   POST /erp/v2/employees/from-wp-user        — convert an existing WP user
 *        into an employee.
 *
 * Mirrors the legacy AJAX handlers `AjaxHandler::check_user()` and
 * `AjaxHandler::employee_create_from_wp_user()` (the admin reference) — same
 * lookups, same `Models\Employee` create with `designation/department = 0,
 * status = active`, same `employee` role grant. Only the envelope is the v2
 * contract. `erp/v1` stays untouched.
 *
 * Route note: the `/employees/{user_id}` routes use a numeric `[\d]+` pattern,
 * so the literal `check-user` / `from-wp-user` paths never collide with them.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Employee as EmployeeModel;
use WP_REST_Request;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class EmployeeUserControllerV2 extends RestControllerV2 {

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
			'/' . $this->rest_base . '/check-user',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'check_user' ],
					'permission_callback' => [ $this, 'permission_create' ],
					'args'                => [
						'email' => [
							'description'       => __( 'Email address to look up.', 'erp' ),
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						],
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/from-wp-user',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'convert_user' ],
					'permission_callback' => [ $this, 'permission_convert' ],
					'args'                => [
						'user_id' => [
							'description'       => __( 'WP user ID to convert.', 'erp' ),
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
						],
					],
				],
			]
		);
	}

	/**
	 * Looking up / creating an employee requires the create-employee cap.
	 *
	 * @return bool
	 */
	public function permission_create(): bool {
		return $this->permission_cap( 'erp_create_employee' );
	}

	/**
	 * Converting requires the edit-employee cap on the target user — same gate as
	 * `AjaxHandler::employee_create_from_wp_user()`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_convert( $request ): bool {
		return $this->permission_cap( 'erp_edit_employee', (int) $request['user_id'] );
	}

	/**
	 * GET /erp/v2/employees/check-user
	 *
	 * `type`: 'none' (no WP user), 'wp_user' (exists, not an employee — can be
	 * converted), or 'employee' (already an employee).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function check_user( $request ) {
		$email = sanitize_text_field( (string) $request['email'] );

		if ( '' === $email ) {
			return new \WP_Error( 'rest_no_email', __( 'No email address provided', 'erp' ), [ 'status' => 400 ] );
		}

		$user = get_user_by( 'email', $email );

		if ( false === $user ) {
			return rest_ensure_response( [ 'available' => true, 'type' => 'none', 'user' => null ] );
		}

		$is_employee = null !== EmployeeModel::withTrashed()->whereUserId( $user->ID )->first();

		return rest_ensure_response(
			[
				'available' => false,
				'type'      => $is_employee ? 'employee' : 'wp_user',
				'user'      => [
					'id'           => (int) $user->ID,
					'display_name' => (string) $user->display_name,
					'email'        => (string) $user->user_email,
				],
			]
		);
	}

	/**
	 * POST /erp/v2/employees/from-wp-user
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function convert_user( $request ) {
		$id   = (int) $request['user_id'];
		$user = $id ? get_user_by( 'id', $id ) : false;

		if ( ! $user ) {
			return new \WP_Error( 'rest_user_not_found', __( 'User not found', 'erp' ), [ 'status' => 404 ] );
		}

		$user->add_role( 'employee' );

		$exists = EmployeeModel::where( 'user_id', '=', $user->ID )->first();

		if ( null !== $exists ) {
			return new \WP_Error( 'rest_employee_exists', __( 'Employee already exist.', 'erp' ), [ 'status' => 409 ] );
		}

		EmployeeModel::create(
			[
				'user_id'     => $user->ID,
				'designation' => 0,
				'department'  => 0,
				'status'      => 'active',
			]
		);

		$employee = new Employee( (int) $user->ID );

		$response = rest_ensure_response(
			[
				'converted' => true,
				'user_id'   => (int) $user->ID,
				'employee'  => $employee->to_array(),
			]
		);
		$response->set_status( 201 );

		return $response;
	}
}
