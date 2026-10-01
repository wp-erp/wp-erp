<?php
/**
 * WP-ERP HR — `erp/v2/employees/{user_id}/job-histories` REST controller.
 *
 * Endpoint:
 *   GET /erp/v2/employees/{user_id}/job-histories — the employee's full job
 *   history, grouped into the four legacy buckets (status / employment-type /
 *   compensation / job-info changes).
 *
 * Read-only: delegates to the unchanged v1 `Employee::get_job_histories()`
 * model. The raw history rows store IDs / enum codes (department id, pay-type
 * code, …); we resolve them to display labels server-side — exactly the lookups
 * the legacy `tab-job.php` view does — so the React tab stays presentational.
 * `erp/v1` stays untouched.
 *
 * Permission mirrors the v1 controller: `erp_view_jobinfo` on the target
 * employee.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WP_REST_Request;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class EmployeeJobHistoriesController extends RestController {

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
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/job-histories',
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
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => $this->get_create_params(),
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/job-histories/(?P<history_id>[\d]+)',
			[
				'args' => [
					'user_id'    => [
						'description'       => __( 'Unique employee user ID.', 'erp' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
					'history_id' => [
						'description'       => __( 'Job-history row ID.', 'erp' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => $this->get_create_params(),
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'permission_delete' ],
				],
			]
		);
	}

	/**
	 * Deleting a history row requires the manage-jobinfo cap, same as adding or
	 * editing one. The legacy `AjaxHandler::employee_remove_history()` gate
	 * (`erp_edit_employee` on the target) is held by every employee for their own
	 * record, which let them erase their own status and pay history.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_delete( $request ): bool {
		return $this->permission_cap( 'erp_manage_jobinfo', (int) $request['user_id'] );
	}

	/**
	 * Normalise the optional `date` param before it reaches the model, which
	 * feeds it to `DateTimeImmutable::modify()` and fatals on a bad string.
	 *
	 * @param array $params Request params (by reference).
	 *
	 * @return \WP_Error|null WP_Error on an unparseable date, null otherwise.
	 */
	private function validate_date( array &$params ) {
		if ( ! array_key_exists( 'date', $params ) ) {
			return null;
		}

		$date = trim( (string) $params['date'] );

		if ( '' === $date ) {
			unset( $params['date'] );

			return null;
		}

		if ( false === strtotime( $date ) ) {
			return new \WP_Error( 'rest_invalid_date', __( 'Invalid date format', 'erp' ), [ 'status' => 400 ] );
		}

		$params['date'] = $date;

		return null;
	}

	/**
	 * Per-module value rules, checked before the model writes. The model holds
	 * the same rules (legacy parity) but words them as codes ("Invalid
	 * Designation Type"); this names the field the way the React dialog does
	 * (profile-rules.ts).
	 *
	 * @param string $module Module slug.
	 * @param array  $params Request params.
	 *
	 * @return \WP_Error|null WP_Error (400) to reject, null when valid.
	 */
	private function validate_values( string $module, array $params ) {
		$text = static function ( $key ) use ( $params ): string {
			$value = $params[ $key ] ?? '';
			return \is_scalar( $value ) ? trim( (string) $value ) : '';
		};
		$fail = static function ( string $message ) {
			return new \WP_Error( 'rest_invalid_param', $message, [ 'status' => 400 ] );
		};

		if ( 'employee' === $module && '' === $text( 'category' ) ) {
			return $fail( __( 'Employee status is required.', 'erp' ) );
		}

		if ( 'employment' === $module && '' === $text( 'type' ) ) {
			return $fail( __( 'Employee type is required.', 'erp' ) );
		}

		if ( 'compensation' === $module ) {
			$rate = $text( 'pay_rate' );

			if ( '' === $rate ) {
				return $fail( __( 'Pay rate is required.', 'erp' ) );
			}
			if ( ! erp_is_valid_currency_amount( $rate ) || (float) $rate <= 0 ) {
				return $fail( __( 'Pay rate must be a number greater than 0, with up to 4 decimal places.', 'erp' ) );
			}
			if ( '' === $text( 'pay_type' ) ) {
				return $fail( __( 'Pay type is required.', 'erp' ) );
			}
		}

		if ( 'job' === $module ) {
			if ( empty( $params['department'] ) ) {
				return $fail( __( 'Department is required.', 'erp' ) );
			}
			if ( empty( $params['designation'] ) ) {
				return $fail( __( 'Job title is required.', 'erp' ) );
			}
			if ( empty( $params['reporting_to'] ) ) {
				return $fail( __( 'Reporting to is required.', 'erp' ) );
			}
		}

		return null;
	}

	/**
	 * DELETE /erp/v2/employees/{user_id}/job-histories/{history_id}
	 *
	 * Mirrors `AjaxHandler::employee_remove_history()` — delegates to the
	 * unchanged `Employee::delete_job_history()`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$history_id = (int) $request['history_id'];
		$result     = $employee->delete_job_history( $history_id );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code() ?: 'rest_job_history_delete_failed',
				$result->get_error_message() ?: __( 'The history could not be deleted.', 'erp' ),
				[ 'status' => 400 ]
			);
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $history_id ] );
	}

	/**
	 * Reading job info requires the view-jobinfo meta cap on the target employee.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_view( $request ): bool {
		return $this->permission_cap( 'erp_view_jobinfo', (int) $request['user_id'] );
	}

	/**
	 * Adding a job-history entry requires the manage-jobinfo cap on the TARGET
	 * employee (matches the legacy `update_job_history`, which scoped the cap to
	 * the `$user_id`).
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_manage( $request ): bool {
		return $this->permission_cap( 'erp_manage_jobinfo', (int) $request['user_id'] );
	}

	/**
	 * POST /erp/v2/employees/{user_id}/job-histories
	 *
	 * Routes by `module` to the unchanged v1 model methods — identical to the v1
	 * `create_history` controller — so every legacy hook + the
	 * future-dated-history guard keeps firing.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$module = sanitize_key( (string) ( $request['module'] ?? '' ) );

		if ( ! \in_array( $module, [ 'employee', 'employment', 'compensation', 'job' ], true ) ) {
			return new \WP_Error( 'rest_invalid_module', __( 'Invalid history module.', 'erp' ), [ 'status' => 400 ] );
		}

		$params = $request->get_params();

		// This route only creates: an `id` would make the model update a row instead.
		unset( $params['id'] );

		$invalid = $this->validate_date( $params );
		if ( is_wp_error( $invalid ) ) {
			return $invalid;
		}

		$invalid = $this->validate_values( $module, $params );
		if ( is_wp_error( $invalid ) ) {
			return $invalid;
		}

		// Snapshot before the write so the `erp_hr_employee_update` action can carry
		// the old data — the model methods below fire only their `*_create` hooks, so
		// the legacy AjaxHandler fired this update action separately after each write.
		$old_data = $employee->get_data();

		if ( 'employee' === $module || 'employment' === $module ) {
			$result = $employee->update_employment_status( $params );
		} elseif ( 'compensation' === $module ) {
			$result = $employee->update_compensation( $params );
		} else {
			$result = $employee->update_job_info( $params );
		}

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code() ?: 'rest_job_history_error',
				$result->get_error_message() ?: __( 'The history could not be saved.', 'erp' ),
				[ 'status' => 400 ]
			);
		}

		do_action( 'erp_hr_employee_update', $user_id, $old_data );

		$response = rest_ensure_response( [ 'created' => true ] );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * PUT /erp/v2/employees/{user_id}/job-histories/{history_id}
	 *
	 * Edit-in-place for the active history row — mirrors the legacy
	 * `AjaxHandler::update_job_history()`. Same routing as create, but the
	 * `history_id` is injected so the model methods UPDATE the existing row
	 * (they branch on a non-empty `id`) instead of inserting a new one.
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

		$module = sanitize_key( (string) ( $request['module'] ?? '' ) );

		if ( ! \in_array( $module, [ 'employee', 'employment', 'compensation', 'job' ], true ) ) {
			return new \WP_Error( 'rest_invalid_module', __( 'Invalid history module.', 'erp' ), [ 'status' => 400 ] );
		}

		$params       = $request->get_params();
		$params['id'] = (int) $request['history_id'];

		if ( empty( $params['id'] ) ) {
			return new \WP_Error( 'rest_invalid_history', __( 'No valid history found!', 'erp' ), [ 'status' => 400 ] );
		}

		// The model's updateOrCreate() silently INSERTS when the id is not one of
		// this employee's rows, so the row has to be resolved here first.
		$history = $employee->get_erp_user()->histories()->find( $params['id'] );

		if ( ! $history ) {
			return new \WP_Error( 'rest_invalid_history', __( 'No valid history found!', 'erp' ), [ 'status' => 404 ] );
		}

		if ( (string) $history->module !== $module ) {
			return new \WP_Error( 'rest_invalid_module', __( 'Invalid history module.', 'erp' ), [ 'status' => 400 ] );
		}

		$invalid = $this->validate_date( $params );
		if ( is_wp_error( $invalid ) ) {
			return $invalid;
		}

		$invalid = $this->validate_values( $module, $params );
		if ( is_wp_error( $invalid ) ) {
			return $invalid;
		}

		$old_data = $employee->get_data();

		if ( 'employee' === $module || 'employment' === $module ) {
			$result = $employee->update_employment_status( $params );
		} elseif ( 'compensation' === $module ) {
			$result = $employee->update_compensation( $params );
		} else {
			$result = $employee->update_job_info( $params );
		}

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code() ?: 'rest_job_history_error',
				$result->get_error_message() ?: __( 'The history could not be saved.', 'erp' ),
				[ 'status' => 400 ]
			);
		}

		do_action( 'erp_hr_employee_update', $user_id, $old_data );

		return rest_ensure_response( [ 'updated' => true, 'id' => (int) $params['id'] ] );
	}

	/**
	 * Write params for create — the union of fields across the four modules.
	 *
	 * @return array
	 */
	public function get_create_params(): array {
		return [
			'module'       => [
				'description' => __( 'History module: employee | employment | compensation | job.', 'erp' ),
				'type'        => 'string',
				'required'    => true,
				'enum'        => [ 'employee', 'employment', 'compensation', 'job' ],
			],
			'date'         => [
				'description' => __( 'Effective date (Y-m-d).', 'erp' ),
				'type'        => 'string',
			],
			'category'     => [
				'description' => __( 'Employment status code (module=employee).', 'erp' ),
				'type'        => 'string',
			],
			'type'         => [
				'description' => __( 'Employment type code (module=employment).', 'erp' ),
				'type'        => 'string',
			],
			'comments'     => [
				'description' => __( 'Comment for status/type history.', 'erp' ),
				'type'        => 'string',
			],
			'pay_rate'     => [
				'description' => __( 'Pay rate (module=compensation).', 'erp' ),
				'type'        => 'string',
			],
			'pay_type'     => [
				'description' => __( 'Pay type code (module=compensation).', 'erp' ),
				'type'        => 'string',
			],
			'reason'       => [
				'description' => __( 'Pay-change reason code (module=compensation).', 'erp' ),
				'type'        => 'string',
			],
			'comment'      => [
				'description' => __( 'Comment for compensation history.', 'erp' ),
				'type'        => 'string',
			],
			'designation'  => [
				'description' => __( 'Designation ID (module=job).', 'erp' ),
				'type'        => 'integer',
			],
			'department'   => [
				'description' => __( 'Department ID (module=job).', 'erp' ),
				'type'        => 'integer',
			],
			'reporting_to' => [
				'description' => __( 'Reporting-to employee user ID (module=job).', 'erp' ),
				'type'        => 'integer',
			],
			'location'     => [
				'description' => __( 'Location ID (module=job).', 'erp' ),
				'type'        => 'integer',
			],
		];
	}

	/**
	 * GET /erp/v2/employees/{user_id}/job-histories
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

		// All buckets, newest-first. `get_job_histories()` already groups rows by
		// module and orders by date desc.
		$histories = (array) $employee->get_job_histories( 'all', 100, 0 );

		// Resolver maps mirror the legacy tab-job.php lookups.
		$statuses     = (array) erp_hr_get_employee_statuses();
		$types        = (array) erp_hr_get_employee_types();
		$pay_types    = (array) erp_hr_get_pay_type();
		$reasons      = (array) erp_hr_get_pay_change_reasons();
		$departments  = (array) erp_hr_get_departments_dropdown_raw();
		$designations = (array) erp_hr_get_designation_dropdown_raw();
		$locations    = (array) erp_company_get_location_dropdown_raw();

		$data = [
			'status'       => $this->map_status( $histories['employee'] ?? [], $statuses ),
			'employment'   => $this->map_employment( $histories['employment'] ?? [], $types ),
			'compensation' => $this->map_compensation( $histories['compensation'] ?? [], $pay_types, $reasons ),
			'job'          => $this->map_job( $histories['job'] ?? [], $departments, $designations, $locations ),
		];

		return rest_ensure_response( $data );
	}

	/**
	 * Employment-status history (module 'employee').
	 *
	 * @param array $rows     Raw rows.
	 * @param array $statuses Status code → label map.
	 *
	 * @return array
	 */
	private function map_status( array $rows, array $statuses ): array {
		$out = [];
		foreach ( $rows as $row ) {
			$code = (string) ( $row['status'] ?? '' );
			$out[] = [
				'id'      => (int) ( $row['id'] ?? 0 ),
				'date'    => $this->cast_date_iso( $row['date'] ?? null ),
				'status'  => $code !== '' && isset( $statuses[ $code ] ) ? (string) $statuses[ $code ] : '',
				'comment' => $this->cast_string_or_null( $row['comments'] ?? '' ) ?? '',
			];
		}
		return $out;
	}

	/**
	 * Employment-type history (module 'employment').
	 *
	 * @param array $rows  Raw rows.
	 * @param array $types Type code → label map.
	 *
	 * @return array
	 */
	private function map_employment( array $rows, array $types ): array {
		$out = [];
		foreach ( $rows as $row ) {
			$code = (string) ( $row['type'] ?? '' );
			$out[] = [
				'id'      => (int) ( $row['id'] ?? 0 ),
				'date'    => $this->cast_date_iso( $row['date'] ?? null ),
				'type'    => $code !== '' && isset( $types[ $code ] ) ? (string) $types[ $code ] : '',
				'comment' => $this->cast_string_or_null( $row['comments'] ?? '' ) ?? '',
			];
		}
		return $out;
	}

	/**
	 * Compensation history (module 'compensation').
	 *
	 * @param array $rows      Raw rows.
	 * @param array $pay_types Pay-type code → label map.
	 * @param array $reasons   Pay-change-reason code → label map.
	 *
	 * @return array
	 */
	private function map_compensation( array $rows, array $pay_types, array $reasons ): array {
		$out = [];
		foreach ( $rows as $row ) {
			$pay_type = (string) ( $row['pay_type'] ?? '' );
			$reason   = (string) ( $row['reason'] ?? '' );
			$out[] = [
				'id'       => (int) ( $row['id'] ?? 0 ),
				'date'     => $this->cast_date_iso( $row['date'] ?? null ),
				'pay_rate' => $this->cast_string_or_null( $row['pay_rate'] ?? '' ) ?? '',
				'pay_type' => $pay_type !== '' && isset( $pay_types[ $pay_type ] ) ? (string) $pay_types[ $pay_type ] : $pay_type,
				'reason'   => $reason !== '' && isset( $reasons[ $reason ] ) ? (string) $reasons[ $reason ] : $reason,
				'comment'  => $this->cast_string_or_null( $row['comment'] ?? '' ) ?? '',
			];
		}
		return $out;
	}

	/**
	 * Job-info history (module 'job').
	 *
	 * @param array $rows         Raw rows.
	 * @param array $departments  Department id → title map.
	 * @param array $designations Designation id → title map.
	 * @param array $locations    Location id → name map.
	 *
	 * @return array
	 */
	private function map_job( array $rows, array $departments, array $designations, array $locations ): array {
		$out = [];
		foreach ( $rows as $row ) {
			$dept_id     = (string) ( $row['department'] ?? '' );
			$desig_id    = (string) ( $row['designation'] ?? '' );
			$location_id = (string) ( $row['location'] ?? '' );
			$reporting   = $this->cast_int_or_null( $row['reporting_to'] ?? null );

			$reporting_name = '';
			if ( $reporting ) {
				$lead = new Employee( $reporting );
				if ( $lead->is_employee() ) {
					$reporting_name = (string) $lead->get_full_name();
				}
			}

			$out[] = [
				'id'              => (int) ( $row['id'] ?? 0 ),
				'date'            => $this->cast_date_iso( $row['date'] ?? null ),
				'department'      => $dept_id !== '' && isset( $departments[ $dept_id ] ) ? (string) $departments[ $dept_id ] : $dept_id,
				'designation'     => $desig_id !== '' && isset( $designations[ $desig_id ] ) ? (string) $designations[ $desig_id ] : $desig_id,
				'location'        => $location_id !== '' && isset( $locations[ $location_id ] ) ? (string) $locations[ $location_id ] : $location_id,
				'reporting_to'    => $reporting_name,
				// Raw ids so the edit dialog can prefill the pickers directly
				// (the label→id reverse-map is unreliable for reporting-to names).
				'department_id'   => $dept_id !== '' ? (int) $dept_id : 0,
				'designation_id'  => $desig_id !== '' ? (int) $desig_id : 0,
				'location_id'     => $location_id !== '' ? (int) $location_id : 0,
				'reporting_to_id' => $reporting ? (int) $reporting : 0,
			];
		}
		return $out;
	}

	/**
	 * JSON Schema for the grouped job-history payload.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'employee_job_histories',
			'type'       => 'object',
			'properties' => [
				'status'       => [ 'type' => 'array' ],
				'employment'   => [ 'type' => 'array' ],
				'compensation' => [ 'type' => 'array' ],
				'job'          => [ 'type' => 'array' ],
			],
		];
	}
}
