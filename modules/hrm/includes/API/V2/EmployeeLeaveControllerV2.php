<?php
/**
 * WP-ERP HR — `erp/v2/employees/{user_id}/leave` REST controller.
 *
 * Endpoint:
 *   GET /erp/v2/employees/{user_id}/leave — current financial-year leave
 *   balance (per policy) + the employee's leave request history.
 *
 * Read-only: delegates to the unchanged v1 `Employee::get_leave_summary()` and
 * `Employee::get_leave_requests()`. Status / day-status codes are resolved to
 * labels server-side (the `tab-leave.php` lookups) so the React tab stays
 * presentational. `erp/v1` stays untouched.
 *
 * Permission mirrors the v1 controller: `erp_edit_employee` on the target.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\FinancialYear;
use WP_REST_Request;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class EmployeeLeaveControllerV2 extends RestControllerV2 {

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
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/leave',
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
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'permission_view' ],
					'args'                => [
						'year'      => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
						'status'    => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
						'policy_id' => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
					],
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);

		// Policies the employee is entitled to in a financial year, with the
		// available-day balance — feeds the "Request Leave" dialog. Mirrors the
		// legacy `leave_assign_employee_policy` + `leave_available_days` AJAX.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/leave/assignable',
			[
				'args' => [
					'user_id' => $this->user_id_arg(),
					'f_year'  => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_assignable' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);

		// Submit a leave request for the employee. Mirrors the `leave_request`
		// AJAX → `erp_hr_leave_insert_request()` + the NewLeaveRequest email.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/leave/requests',
			[
				'args' => [
					'user_id' => $this->user_id_arg(),
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_request' ],
					'permission_callback' => [ $this, 'permission_create_request' ],
				],
			]
		);
	}

	/**
	 * Shared `user_id` route arg.
	 *
	 * @return array
	 */
	private function user_id_arg(): array {
		return [
			'description'       => __( 'Unique employee user ID.', 'erp' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];
	}

	/**
	 * Creating a request requires the leave-create-request meta cap on the target
	 * employee — the same gate `AjaxHandler::leave_request()` enforced.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_create_request( $request ): bool {
		return $this->permission_cap( 'erp_leave_create_request', (int) $request['user_id'] );
	}

	/**
	 * GET /erp/v2/employees/{user_id}/leave/assignable?f_year=X
	 *
	 * Returns the entitlements the employee can apply against in the given
	 * financial year, each with its available-day balance.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_assignable( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$f_year = (int) ( $request['f_year'] ?? 0 );
		if ( ! $f_year ) {
			$current = erp_hr_get_financial_year_from_date();
			$f_year  = ! empty( $current ) ? (int) $current->id : 0;
		}

		$financial_year = $f_year ? FinancialYear::find( $f_year ) : null;
		if ( ! $financial_year ) {
			return new \WP_Error( 'rest_invalid_year', __( 'Invalid financial year.', 'erp' ), [ 'status' => 400 ] );
		}

		// Map of entitlement_id => policy label (same helper the AJAX used).
		$policies = (array) erp_hr_get_assign_policy_from_entitlement( $user_id, $financial_year->start_date );

		$items = [];
		foreach ( $policies as $entitlement_id => $label ) {
			$balance   = (array) erp_hr_leave_get_balance_for_single_entitlement( (int) $entitlement_id );
			$items[]   = [
				'id'        => (int) $entitlement_id,
				'name'      => (string) $label,
				'available' => isset( $balance['available'] ) ? (float) $balance['available'] : 0,
			];
		}

		return rest_ensure_response( [ 'policies' => $items ] );
	}

	/**
	 * POST /erp/v2/employees/{user_id}/leave/requests
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_request( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$leave_policy = (int) ( $request['leave_policy'] ?? 0 );
		$reason       = wp_strip_all_tags( sanitize_text_field( (string) ( $request['leave_reason'] ?? '' ) ) );

		if ( '' === trim( $reason ) ) {
			return new \WP_Error( 'rest_leave_reason_required', __( 'Leave reason field can not be blank', 'erp' ), [ 'status' => 400 ] );
		}

		// Same date envelope the AJAX handler built (whole-day window).
		$start_date = sanitize_text_field( (string) ( $request['leave_from'] ?? '' ) );
		$end_date   = sanitize_text_field( (string) ( $request['leave_to'] ?? '' ) );
		$start_date = $start_date ? $start_date . ' 00:00:00' : date_i18n( 'Y-m-d 00:00:00' );
		$end_date   = $end_date ? $end_date . ' 23:59:59' : date_i18n( 'Y-m-d 23:59:59' );

		$request_id = erp_hr_leave_insert_request(
			[
				'user_id'      => $user_id,
				'leave_policy' => $leave_policy,
				'start_date'   => $start_date,
				'end_date'     => $end_date,
				'reason'       => $reason,
			]
		);

		if ( is_wp_error( $request_id ) ) {
			return new \WP_Error(
				$request_id->get_error_code() ?: 'rest_leave_request_failed',
				$request_id->get_error_message() ?: __( 'The leave request could not be saved.', 'erp' ),
				[ 'status' => 400 ]
			);
		}

		// Fire the same notification email the legacy flow sent.
		$emailer = wperp()->emailer->get_email( 'NewLeaveRequest' );
		if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
			$emailer->trigger( $request_id );
		}

		$response = rest_ensure_response( [ 'created' => true, 'id' => (int) $request_id ] );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Reading leave requires the edit-employee meta cap on the target.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_view( $request ): bool {
		return $this->permission_cap( 'erp_edit_employee', (int) $request['user_id'] );
	}

	/**
	 * GET /erp/v2/employees/{user_id}/leave
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_items( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		// Current financial year (for the default + the balance, which is always
		// shown for the current year — matching the legacy tab).
		$current_fy = erp_hr_get_financial_year_from_date();
		$current_id = ! empty( $current_fy ) ? (int) $current_fy->id : 0;

		$year      = (int) ( $request['year'] ?? 0 ) ?: $current_id;
		$status    = $this->cast_string_or_null( $request['status'] ?? '' ) ?? 'all';
		$policy_id = (int) ( $request['policy_id'] ?? 0 );

		$request_args = [
			'f_year'    => $year,
			'status'    => $status,
			'policy_id' => $policy_id,
			'orderby'   => 'start_date',
		];

		return rest_ensure_response(
			[
				'summary'  => $this->map_summary( $employee->get_leave_summary() ),
				'requests' => $this->map_requests( (array) $employee->get_leave_requests( $request_args ) ),
				'meta'     => $this->build_meta( $employee, $current_id ),
			]
		);
	}

	/**
	 * Dropdown data for the history filter: financial years, status map, and the
	 * policies assigned in the current financial year.
	 *
	 * @param Employee $employee   Employee.
	 * @param int      $current_id Current financial-year id.
	 *
	 * @return array
	 */
	private function build_meta( Employee $employee, int $current_id ): array {
		$years = [];
		foreach ( FinancialYear::all() as $fy ) {
			$years[] = [
				'id'   => (int) $fy->id,
				'name' => (string) $fy->fy_name,
			];
		}

		$statuses = [];
		foreach ( (array) erp_hr_leave_request_get_statuses() as $code => $label ) {
			$statuses[] = [
				'value' => (string) $code,
				'label' => (string) $label,
			];
		}

		// Policies assigned in the current financial year.
		$policies = [];
		foreach ( (array) $employee->get_leave_policies() as $policy ) {
			$policy = (array) $policy;
			if ( (int) ( $policy['f_year'] ?? 0 ) === $current_id ) {
				$policies[] = [
					'id'   => (int) ( $policy['leave_id'] ?? 0 ),
					'name' => (string) ( $policy['name'] ?? '' ),
				];
			}
		}

		return [
			'current_year'    => $current_id,
			'financial_years' => $years,
			'statuses'        => $statuses,
			'policies'        => $policies,
		];
	}

	/**
	 * Per-policy balance for the current financial year.
	 *
	 * `get_leave_summary()` returns an object keyed by leave id; cast to an array
	 * and flatten into a list.
	 *
	 * @param mixed $summary Balance object/array keyed by leave id.
	 *
	 * @return array
	 */
	private function map_summary( $summary ): array {
		$out = [];

		foreach ( (array) $summary as $row ) {
			$row = (array) $row;

			$out[] = [
				'policy'      => $this->cast_string_or_null( $row['policy'] ?? '' ) ?? '',
				'entitlement' => $this->cast_float_or_null( $row['entitlement'] ?? null ) ?? 0,
				'total'       => $this->cast_float_or_null( $row['total'] ?? null ) ?? 0,
				'available'   => $this->cast_float_or_null( $row['available'] ?? null ) ?? 0,
				'spent'       => $this->cast_float_or_null( $row['spent'] ?? null ) ?? 0,
				'from_date'   => $this->cast_date_iso( $row['from_date'] ?? null ),
				'to_date'     => $this->cast_date_iso( $row['to_date'] ?? null ),
			];
		}

		return $out;
	}

	/**
	 * Leave request history with resolved status labels.
	 *
	 * @param array $requests Raw request rows.
	 *
	 * @return array
	 */
	private function map_requests( array $requests ): array {
		$out = [];

		foreach ( $requests as $request ) {
			$request       = (object) $request;
			$status_code   = $request->status ?? '';
			$day_status_id = (string) ( $request->day_status_id ?? '1' );

			// Whole-day vs partial-day: legacy shows the day-status label for
			// anything other than full days (day_status_id 1).
			$duration = '1' !== $day_status_id
				? (string) erp_hr_leave_request_get_day_statuses( $day_status_id )
				: '';

			$out[] = [
				'id'          => (int) ( $request->id ?? 0 ),
				'start_date'  => $this->iso_from_mixed( $request->start_date ?? null ),
				'end_date'    => $this->iso_from_mixed( $request->end_date ?? null ),
				'policy'      => $this->cast_string_or_null( $request->policy_name ?? '' ) ?? '',
				'reason'      => $this->cast_string_or_null( isset( $request->reason ) ? stripslashes( (string) $request->reason ) : '' ) ?? '',
				'days'        => $this->cast_float_or_null( $request->days ?? null ) ?? 0,
				'duration'    => $duration,
				'status'      => '' !== $status_code ? (string) erp_hr_leave_request_get_statuses( $status_code ) : '',
				'status_code' => $this->cast_int_or_null( $status_code ),
			];
		}

		return $out;
	}

	/**
	 * Normalize a date that may arrive as a Unix timestamp (leave requests store
	 * `start_date`/`end_date` as integer timestamps) or a date string.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return string|null
	 */
	private function iso_from_mixed( $value ): ?string {
		if ( $value === null || $value === '' ) {
			return null;
		}
		// Pure-integer string/number → treat as a Unix timestamp.
		if ( is_numeric( $value ) && (string) (int) $value === (string) $value ) {
			return gmdate( 'c', (int) $value );
		}
		return $this->cast_date_iso( $value );
	}

	/**
	 * JSON Schema for the leave payload.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'employee_leave',
			'type'       => 'object',
			'properties' => [
				'summary'  => [ 'type' => 'array' ],
				'requests' => [ 'type' => 'array' ],
				'meta'     => [ 'type' => 'object' ],
			],
		];
	}
}
