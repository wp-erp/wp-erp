<?php
/**
 * WP-ERP HR — `erp/v2/leave-entitlements` REST controller.
 *
 * Endpoints:
 *   GET    /erp/v2/leave-entitlements            — paginated entitlement list (year/policy/type filters).
 *   POST   /erp/v2/leave-entitlements            — assign a policy to one or many employees.
 *   DELETE /erp/v2/leave-entitlements/{id}       — delete an entitlement (cascade) — needs `user_id`.
 *   GET    /erp/v2/leave-entitlements/policies   — filtered policy dropdown for the assign form.
 *   GET    /erp/v2/leave-entitlements/employees  — employees matching a policy's scope.
 *
 * The legacy assign flow is a **form POST** (`FormHandler::leave_entitlement()`
 * on `erp_action_hr-leave-assign-policy`), not an AJAX action; the helper
 * dropdowns ARE AJAX (`get_policies_for_entitlement()`, `get_employees()`); and
 * delete is AJAX (`remove_entitlement()`). This controller mirrors all of them,
 * reusing the same model fns — `erp_hr_leave_insert_entitlement()` (with its
 * required-field + already-assigned + employee-active guards), `erp_hr_get_employees()`,
 * `erp_hr_leave_get_policies_dropdown_raw()`, `erp_hr_leave_get_entitlements()`,
 * `erp_hr_delete_entitlement()` (cascades dependent leave requests). Only the
 * envelope is the v2 contract. `erp/v1` + the AJAX/form layers stay untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Models\LeavePolicy;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class LeaveEntitlementsControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'leave-entitlements';

	/**
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => $this->get_collection_params(),
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'assign_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => $this->get_assign_params(),
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/policies',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_policy_options' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/employees',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_policy_employees' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => [
						'policy_id' => [
							'description'       => __( 'Policy ID to resolve matching employees.', 'erp' ),
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
						],
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'args' => [
					'id' => [
						'description'       => __( 'Unique entitlement ID.', 'erp' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => [
						'user_id' => [
							'description'       => __( 'Owning employee user ID.', 'erp' ),
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
	 * Every entitlement operation requires the leave-management capability — same
	 * gate as the legacy form/AJAX handlers.
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return $this->permission_cap( 'erp_leave_manage' );
	}

	/**
	 * GET /erp/v2/leave-entitlements
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		$page     = max( 1, (int) ( $request['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $request['per_page'] ?? 20 ) ) );

		$args = [
			'number'        => $per_page,
			'offset'        => ( $page - 1 ) * $per_page,
			'year'          => (int) ( $request['year'] ?? 0 ),
			'policy_id'     => (int) ( $request['policy_id'] ?? 0 ),
			'leave_id'      => (int) ( $request['leave_id'] ?? 0 ),
			'employee_type' => sanitize_text_field( (string) ( $request['employee_type'] ?? '' ) ),
			'search'        => sanitize_text_field( (string) ( $request['search'] ?? '' ) ),
		];

		$result = erp_hr_leave_get_entitlements( $args );
		$rows   = isset( $result['data'] ) ? (array) $result['data'] : [];
		$total  = isset( $result['total'] ) ? (int) $result['total'] : \count( $rows );

		$items = [];
		foreach ( $rows as $row ) {
			$items[] = $this->prepare_item_for_response( $row, $request );
		}

		$response = rest_ensure_response( $items );
		return $this->paginate( $response, $request, $total );
	}

	/**
	 * POST /erp/v2/leave-entitlements
	 *
	 * Assign a policy to a single employee or to every employee matching the
	 * policy's scope — mirrors `FormHandler::leave_entitlement()`.
	 *
	 * Body: `{ policy_id, assignment_to: 'single'|'all', single_employee?, comment? }`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function assign_item( $request ) {
		$policy_id       = absint( $request['policy_id'] ?? 0 );
		$assignment_to   = sanitize_text_field( (string) ( $request['assignment_to'] ?? 'single' ) );
		$single_employee = absint( $request['single_employee'] ?? 0 );
		$comment         = isset( $request['comment'] ) ? sanitize_text_field( $request['comment'] ) : '';
		$is_single       = 'all' !== $assignment_to;

		if ( ! $policy_id ) {
			return new \WP_Error( 'rest_no_policy', __( 'Error: Please select a leave policy.', 'erp' ), [ 'status' => 400 ] );
		}

		$policy = LeavePolicy::find( $policy_id );
		if ( ! $policy ) {
			return new \WP_Error( 'rest_invalid_policy', __( 'Error: Invalid policy selected. Please check your input.', 'erp' ), [ 'status' => 404 ] );
		}

		if ( $is_single && ! $single_employee ) {
			return new \WP_Error( 'rest_no_employee', __( 'Error: Please select an employee.', 'erp' ), [ 'status' => 400 ] );
		}

		// Resolve the target employee set.
		$employees = [];

		if ( ! $is_single ) {
			$employees = erp_hr_get_employees(
				[
					'department'     => $policy->department_id,
					'location'       => $policy->location_id,
					'designation'    => $policy->designation_id,
					'gender'         => $policy->gender,
					'marital_status' => $policy->marital,
					'number'         => '-1',
					'no_object'      => true,
				]
			);
		} else {
			$user = get_user_by( 'id', $single_employee );

			if ( ! $user ) {
				return new \WP_Error( 'rest_no_employee', __( 'Error: No Employees Found. Please check your input.', 'erp' ), [ 'status' => 404 ] );
			}

			$emp               = new \stdClass();
			$emp->user_id      = $user->ID;
			$emp->display_name = $user->display_name;
			$employees[]       = $emp;
		}

		if ( 0 === \count( $employees ) ) {
			return new \WP_Error( 'rest_no_employee', __( 'Error: No Employees Found. Please check your input.', 'erp' ), [ 'status' => 404 ] );
		}

		$affected = 0;
		$errors   = [];

		foreach ( $employees as $employee ) {
			$inserted = erp_hr_leave_insert_entitlement(
				[
					'user_id'     => $employee->user_id,
					'leave_id'    => $policy->leave_id,
					'created_by'  => get_current_user_id(),
					'trn_id'      => $policy->id,
					'trn_type'    => 'leave_policies',
					'day_in'      => $policy->days,
					'day_out'     => 0,
					'description' => $comment,
					'f_year'      => $policy->f_year,
				]
			);

			if ( is_wp_error( $inserted ) ) {
				$errors[] = $inserted->get_error_message();
			} else {
				++$affected;
			}
		}

		// Mirror the form handler: any successful assignment is a success, with
		// per-employee errors surfaced alongside the affected count.
		if ( 0 === $affected && ! empty( $errors ) ) {
			return new \WP_Error(
				'rest_entitlement_assign_failed',
				implode( ' ', array_unique( $errors ) ),
				[ 'status' => 409, 'errors' => array_values( array_unique( $errors ) ) ]
			);
		}

		$response = rest_ensure_response(
			[
				'affected' => $affected,
				'errors'   => array_values( array_unique( $errors ) ),
			]
		);
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * DELETE /erp/v2/leave-entitlements/{id}
	 *
	 * Mirrors `AjaxHandler::remove_entitlement()` (which passes the entitlement id
	 * as both `$id` and `$entitlement_id` — they are the same row id since 1.6).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id      = (int) $request['id'];
		$user_id = absint( $request['user_id'] ?? 0 );

		if ( ! $id || ! $user_id ) {
			return new \WP_Error( 'rest_entitlement_bad_request', __( 'Something went wrong! Please try again later.', 'erp' ), [ 'status' => 400 ] );
		}

		erp_hr_delete_entitlement( $id, $user_id, $id );

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	/**
	 * GET /erp/v2/leave-entitlements/policies
	 *
	 * Filtered policy dropdown — mirrors `AjaxHandler::get_policies_for_entitlement()`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_policy_options( $request ): WP_REST_Response {
		$data = [
			'employee_type'  => sanitize_text_field( (string) ( $request['employee_type'] ?? '-1' ) ),
			'department_id'  => sanitize_text_field( (string) ( $request['department_id'] ?? '-1' ) ),
			'location_id'    => sanitize_text_field( (string) ( $request['location_id'] ?? '-1' ) ),
			'designation_id' => sanitize_text_field( (string) ( $request['designation_id'] ?? '-1' ) ),
			'gender'         => sanitize_text_field( (string) ( $request['gender'] ?? '-1' ) ),
			'marital'        => sanitize_text_field( (string) ( $request['marital'] ?? '-1' ) ),
			'f_year'         => sanitize_text_field( (string) ( $request['f_year'] ?? '' ) ),
		];

		$raw = (array) erp_hr_leave_get_policies_dropdown_raw( $data );

		$options = [];
		foreach ( $raw as $id => $label ) {
			$options[] = [ 'value' => (int) $id, 'label' => (string) $label ];
		}

		return rest_ensure_response( $options );
	}

	/**
	 * GET /erp/v2/leave-entitlements/employees
	 *
	 * Employees matching a policy's scope — mirrors `AjaxHandler::get_employees()`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function get_policy_employees( $request ) {
		$policy_id = absint( $request['policy_id'] ?? 0 );

		if ( ! $policy_id ) {
			return new \WP_Error( 'rest_invalid_policy', __( 'Invalid Policy id.', 'erp' ), [ 'status' => 400 ] );
		}

		$policy = LeavePolicy::find( $policy_id );
		if ( ! $policy ) {
			return new \WP_Error( 'rest_no_policy', __( 'No policy found with given policy id.', 'erp' ), [ 'status' => 404 ] );
		}

		$employees = erp_hr_get_employees(
			[
				'number'         => '-1',
				'no_object'      => true,
				'department'     => $policy->department_id,
				'location'       => $policy->location_id,
				'designation'    => $policy->designation_id,
				'gender'         => $policy->gender,
				'marital_status' => $policy->marital,
				'type'           => $policy->employee_type,
			]
		);

		$options = [];
		foreach ( (array) $employees as $employee ) {
			$options[] = [
				'value' => (int) $employee->user_id,
				'label' => (string) $employee->display_name,
			];
		}

		return rest_ensure_response( $options );
	}

	/**
	 * Reshape a joined entitlement row from `erp_hr_leave_get_entitlements()`.
	 *
	 * @param mixed           $row     stdClass row.
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $row, $request ) {
		unset( $request );

		$row = (object) $row;

		return [
			'id'            => (int) ( $row->id ?? 0 ),
			'user_id'       => $this->cast_int_or_null( $row->user_id ?? null ),
			'employee_name' => (string) ( $row->employee_name ?? '' ),
			'leave_id'      => $this->cast_int_or_null( $row->leave_id ?? null ),
			'policy_id'     => $this->cast_int_or_null( $row->trn_id ?? null ),
			'policy_name'   => (string) ( $row->policy_name ?? '' ),
			'days'          => $this->cast_float_or_null( $row->day_in ?? null ) ?? 0,
			'spent'         => $this->cast_float_or_null( $row->day_out ?? null ) ?? 0,
			'f_year'        => $this->cast_int_or_null( $row->f_year ?? null ),
			'from_date'     => $this->cast_date_iso( $row->from_date ?? null ),
			'to_date'       => $this->cast_date_iso( $row->to_date ?? null ),
			'description'   => (string) ( $row->description ?? '' ),
			'emp_status'    => (string) ( $row->emp_status ?? '' ),
		];
	}

	/**
	 * Assign params for POST.
	 *
	 * @return array
	 */
	public function get_assign_params(): array {
		return [
			'policy_id'       => [
				'description'       => __( 'Leave policy ID to assign.', 'erp' ),
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
			'assignment_to'   => [
				'description' => __( "'single' for one employee, 'all' for every employee matching the policy scope.", 'erp' ),
				'type'        => 'string',
				'enum'        => [ 'single', 'all' ],
				'default'     => 'single',
			],
			'single_employee' => [
				'description'       => __( 'Employee user ID (when assignment_to=single).', 'erp' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			],
			'comment'         => [
				'description'       => __( 'Optional note stored on each entitlement.', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Collection params: pagination + filters.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		$params = parent::get_collection_params();

		$params['year']          = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['policy_id']     = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['leave_id']      = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['employee_type'] = [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ];

		return $params;
	}
}
