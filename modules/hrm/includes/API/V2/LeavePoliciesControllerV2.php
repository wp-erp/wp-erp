<?php
/**
 * WP-ERP HR — `erp/v2/leave-policies` REST controller.
 *
 * Endpoints:
 *   GET    /erp/v2/leave-policies               — paginated policy list (scope filters).
 *   POST   /erp/v2/leave-policies               — create a policy.
 *   GET    /erp/v2/leave-policies/form-options   — dropdown data for the create/edit form.
 *   GET    /erp/v2/leave-policies/{id}          — single policy (raw IDs for editing).
 *   PUT    /erp/v2/leave-policies/{id}          — update a policy.
 *   DELETE /erp/v2/leave-policies/{id}          — delete a policy (cascade).
 *
 * The legacy admin creates/updates policies through a **form POST**
 * (`FormHandler::leave_policy_create()`), not an AJAX action — so this v2
 * controller mirrors that handler: same field set + `-1` "All" sentinels, the
 * same `erp_hr_leave_insert_policy()` model call (which itself fires
 * `erp_hr_leave_insert_policy` → `erp_hr_apply_policy_existing_employee()` to
 * auto-create entitlements for matching employees, plus the
 * `erp_hr_leave_update_policy` / `erp_hr_leave_before_policy_updated` hooks),
 * and the same `erp_hr_leave_get_policies()` / `erp_hr_leave_policy_delete()`
 * for list + cascade delete. Only the request/response envelope is the modern
 * v2 contract. `erp/v1` + the form handler stay untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Models\FinancialYear;
use WeDevs\ERP\HRM\Models\LeavePolicy;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class LeavePoliciesControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'leave-policies';

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
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => $this->get_write_params(),
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/form-options',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'form_options' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'args' => [
					'id' => [
						'description'       => __( 'Unique leave policy ID.', 'erp' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => $this->get_write_params(),
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);
	}

	/**
	 * Every policy operation requires the leave-management capability — same gate
	 * as `FormHandler::leave_policy_create()` (`erp_hr_get_manager_role()`) and
	 * the `erp_hr_leave_*` model fns (`erp_leave_manage`).
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return $this->permission_cap( 'erp_leave_manage' );
	}

	/**
	 * GET /erp/v2/leave-policies
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		$page     = max( 1, (int) ( $request['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $request['per_page'] ?? 20 ) ) );

		$args = [
			'number'         => $per_page,
			'offset'         => ( $page - 1 ) * $per_page,
			'orderby'        => sanitize_key( (string) ( $request['orderby'] ?? 'id' ) ),
			'order'          => strtoupper( (string) ( $request['order'] ?? 'ASC' ) ) === 'DESC' ? 'DESC' : 'ASC',
			'f_year'         => (int) ( $request['f_year'] ?? 0 ),
			'department_id'  => (int) ( $request['department_id'] ?? 0 ),
			'designation_id' => (int) ( $request['designation_id'] ?? 0 ),
			'employee_type'  => sanitize_text_field( (string) ( $request['employee_type'] ?? '' ) ),
		];

		$result = erp_hr_leave_get_policies( $args );
		$rows   = isset( $result['data'] ) ? (array) $result['data'] : [];
		$total  = isset( $result['total'] ) ? (int) $result['total'] : \count( $rows );

		$items = [];
		foreach ( $rows as $row ) {
			$items[] = $this->prepare_list_row( $row );
		}

		$response = rest_ensure_response( $items );
		return $this->paginate( $response, $request, $total );
	}

	/**
	 * GET /erp/v2/leave-policies/{id}
	 *
	 * Returns the raw column IDs (not the resolved "All" labels the list uses) so
	 * the edit form can repopulate its selects.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$policy = LeavePolicy::find( (int) $request['id'] );

		if ( ! $policy ) {
			return new \WP_Error( 'rest_leave_policy_invalid_id', __( 'No valid leave policy found', 'erp' ), [ 'status' => 404 ] );
		}

		return rest_ensure_response( $this->prepare_item_for_response( $policy, $request ) );
	}

	/**
	 * POST /erp/v2/leave-policies
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$error = $this->validate_required( $request, false );
		if ( is_wp_error( $error ) ) {
			return $error;
		}

		$id = $this->insert_policy( $this->prepare_item_for_database( $request ), $request );

		if ( is_wp_error( $id ) ) {
			return $this->to_rest_error( $id, 409 );
		}

		$response = rest_ensure_response( $this->prepare_item_for_response( LeavePolicy::find( (int) $id ), $request ) );
		$response->set_status( 201 );
		$response->header(
			'Location',
			rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, (int) $id ) )
		);

		return $response;
	}

	/**
	 * PUT /erp/v2/leave-policies/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id     = (int) $request['id'];
		$policy = LeavePolicy::find( $id );

		if ( ! $policy ) {
			return new \WP_Error( 'rest_leave_policy_invalid_id', __( 'No valid leave policy found', 'erp' ), [ 'status' => 404 ] );
		}

		$error = $this->validate_required( $request, true );
		if ( is_wp_error( $error ) ) {
			return $error;
		}

		$data       = $this->prepare_item_for_database( $request );
		$data['id'] = $id;

		$result = $this->insert_policy( $data, $request );

		if ( is_wp_error( $result ) ) {
			return $this->to_rest_error( $result, 409 );
		}

		return rest_ensure_response( $this->prepare_item_for_response( LeavePolicy::find( $id ), $request ) );
	}

	/**
	 * DELETE /erp/v2/leave-policies/{id}
	 *
	 * Cascade delete (entitlements + dependent leave requests) handled inside
	 * `erp_hr_leave_policy_delete()`. The cap is already enforced by
	 * `permission_manage`, so that fn's `wp_die()` guard never fires here.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id     = (int) $request['id'];
		$policy = LeavePolicy::find( $id );

		if ( ! $policy ) {
			return new \WP_Error( 'rest_leave_policy_invalid_id', __( 'No valid leave policy found', 'erp' ), [ 'status' => 404 ] );
		}

		erp_hr_leave_policy_delete( [ $id ] );

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	/**
	 * GET /erp/v2/leave-policies/form-options
	 *
	 * One round-trip of the dropdown data the create/edit form needs:
	 * leave types, financial years, departments, designations + the static
	 * enum maps. The `-1` "All" sentinel matches the legacy form.
	 *
	 * @return WP_REST_Response
	 */
	public function form_options(): WP_REST_Response {
		$leave_types = erp_hr_get_leave_policy_names();
		$leave_types = method_exists( $leave_types, 'toArray' ) ? $leave_types->toArray() : (array) $leave_types;

		$financial_years = [];
		foreach ( FinancialYear::all() as $fy ) {
			$financial_years[] = [
				'id'      => (int) $fy->id,
				'fy_name' => (string) $fy->fy_name,
			];
		}

		$departments = [];
		foreach ( (array) erp_hr_get_departments_dropdown_raw() as $dept_id => $title ) {
			if ( '' === (string) $dept_id ) {
				continue;
			}
			$departments[] = [ 'id' => (int) $dept_id, 'title' => (string) $title ];
		}

		$designations = [];
		foreach ( (array) erp_hr_get_designation_dropdown_raw() as $desig_id => $title ) {
			if ( '' === (string) $desig_id ) {
				continue;
			}
			$designations[] = [ 'id' => (int) $desig_id, 'title' => (string) $title ];
		}

		// Current financial year ID — the list filter + create form default to it.
		$current_f_year = 0;
		if ( function_exists( 'erp_hr_get_financial_year_from_date' ) ) {
			$fy             = erp_hr_get_financial_year_from_date();
			$current_f_year = $fy ? (int) $fy->id : 0;
		}

		return rest_ensure_response(
			[
				'leave_types'      => array_map(
					static function ( $t ) {
						$t = (array) $t;
						return [ 'id' => (int) ( $t['id'] ?? 0 ), 'name' => (string) ( $t['name'] ?? '' ) ];
					},
					$leave_types
				),
				'financial_years'  => $financial_years,
				'current_f_year'   => $current_f_year,
				'departments'      => $departments,
				'designations'     => $designations,
				'employee_types'   => $this->enum_to_options( erp_hr_get_employee_types() ),
				'genders'          => $this->enum_to_options( erp_hr_get_genders() ),
				'marital_statuses' => $this->enum_to_options( erp_hr_get_marital_statuses() ),
			]
		);
	}

	/**
	 * Required-field validation mirroring `FormHandler::leave_policy_create()`.
	 *
	 * On create: leave type, days (>= 0), color and financial year are required.
	 * On update: only color is enforced (the legacy handler skips name/days/year
	 * when editing). The model still re-validates and dedupes.
	 *
	 * @param WP_REST_Request $request    Request.
	 * @param bool            $is_editing Whether this is an update.
	 *
	 * @return true|\WP_Error
	 */
	private function validate_required( $request, bool $is_editing ) {
		$color = isset( $request['color'] ) ? sanitize_text_field( $request['color'] ) : '';

		if ( '' === $color ) {
			return new \WP_Error( 'rest_leave_policy_no_color', __( 'Color field should not be left empty', 'erp' ), [ 'status' => 400 ] );
		}

		if ( ! $is_editing ) {
			if ( empty( $request['leave_id'] ) ) {
				return new \WP_Error( 'rest_leave_policy_no_name', __( 'Name field should not be left empty', 'erp' ), [ 'status' => 400 ] );
			}
			if ( empty( $request['f_year'] ) ) {
				return new \WP_Error( 'rest_leave_policy_no_year', __( 'Year field should not be left empty', 'erp' ), [ 'status' => 400 ] );
			}
		}

		return true;
	}

	/**
	 * Call `erp_hr_leave_insert_policy()`, bridging the `segre` segregation array
	 * it reads off `$_POST` (the legacy form posts it). We populate `$_POST` from
	 * the request for the duration of the call, then restore it.
	 *
	 * @param array           $data    Policy args.
	 * @param WP_REST_Request $request Request.
	 *
	 * @return int|\WP_Error
	 */
	private function insert_policy( array $data, $request ) {
		$segre = $request['segre'] ?? null;

		$had_segre      = \array_key_exists( 'segre', $_POST );
		$previous_segre = $had_segre ? $_POST['segre'] : null;

		if ( null !== $segre && \is_array( $segre ) ) {
			$_POST['segre'] = array_map( 'absint', $segre );
		}

		// Bridge any pro-injected `extra` fields (Advanced Leave half-day,
		// accrual, carry-forward, segregation) onto `$_POST` so the legacy
		// `erp_hr_leave_insert_policy_extra` filter — which reads them off
		// `$_POST` — persists them. JSON REST bodies never populate `$_POST`.
		$extra_post = $this->bridge_extra_to_post( $request );

		$result = erp_hr_leave_insert_policy( $data );

		$this->restore_post( $extra_post );

		if ( $had_segre ) {
			$_POST['segre'] = $previous_segre;
		} else {
			unset( $_POST['segre'] );
		}

		return $result;
	}

	/**
	 * Copy the request's `extra` bucket onto `$_POST` (sanitized), returning the
	 * snapshot needed to restore `$_POST` afterwards. Generic by design: the free
	 * controller stays agnostic of which keys a pro module reads.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array Restore snapshot for `restore_post()`.
	 */
	private function bridge_extra_to_post( $request ): array {
		$extra = $request->get_param( 'extra' );

		if ( ! \is_array( $extra ) ) {
			return [ 'prev' => [], 'absent' => [] ];
		}

		$prev   = [];
		$absent = [];

		foreach ( $extra as $key => $value ) {
			$key = (string) $key;
			if ( \array_key_exists( $key, $_POST ) ) {
				$prev[ $key ] = $_POST[ $key ];
			} else {
				$absent[] = $key;
			}
			$_POST[ $key ] = $this->sanitize_extra_value( $value );
		}

		return [ 'prev' => $prev, 'absent' => $absent ];
	}

	/**
	 * Restore `$_POST` from a `bridge_extra_to_post()` snapshot.
	 *
	 * @param array $snapshot Snapshot.
	 *
	 * @return void
	 */
	private function restore_post( array $snapshot ): void {
		foreach ( (array) ( $snapshot['prev'] ?? [] ) as $key => $value ) {
			$_POST[ $key ] = $value;
		}
		foreach ( (array) ( $snapshot['absent'] ?? [] ) as $key ) {
			unset( $_POST[ $key ] );
		}
	}

	/**
	 * Recursively sanitize an `extra` value (scalars via `sanitize_text_field`,
	 * arrays element-wise) before it lands on `$_POST`.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return mixed
	 */
	private function sanitize_extra_value( $value ) {
		if ( \is_array( $value ) ) {
			return array_map( [ $this, 'sanitize_extra_value' ], $value );
		}
		if ( \is_bool( $value ) ) {
			return $value ? 'on' : '';
		}
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Map the flat v2 payload onto the args `erp_hr_leave_insert_policy()` expects,
	 * preserving the `-1` "All" sentinels from the legacy form.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	protected function prepare_item_for_database( $request ): array {
		return [
			'leave_id'        => absint( $request['leave_id'] ?? 0 ),
			'days'            => absint( $request['days'] ?? 0 ),
			'description'     => isset( $request['description'] ) ? sanitize_text_field( $request['description'] ) : '',
			'color'           => isset( $request['color'] ) ? sanitize_text_field( $request['color'] ) : '',
			'employee_type'   => $this->sentinel( $request['employee_type'] ?? '-1' ),
			'department_id'   => $this->sentinel( $request['department_id'] ?? '-1' ),
			'designation_id'  => $this->sentinel( $request['designation_id'] ?? '-1' ),
			'location_id'     => $this->sentinel( $request['location_id'] ?? '-1' ),
			'gender'          => $this->sentinel( $request['gender'] ?? '-1' ),
			'marital'         => $this->sentinel( $request['marital'] ?? '-1' ),
			'f_year'          => absint( $request['f_year'] ?? 0 ),
			'applicable_from' => absint( $request['applicable_from'] ?? 0 ),
			'apply_for_new_users' => ! empty( $request['apply_for_new_users'] ) ? 1 : 0,
		];
	}

	/**
	 * Normalise a scope value to the `-1` "All" sentinel.
	 *
	 * Empty/missing AND `0` both collapse to `-1`. This matters downstream:
	 * `erp_hr_leave_insert_entitlement()` only treats a scope as "any" when it
	 * equals exactly `-1` (`$policy->department_id != '-1'` …). A scope persisted
	 * as `0`/`''` is compared literally against the employee's real department,
	 * type, etc., so an "all employees" policy would reject every assignment with
	 * "Policy does not match with employee profile." Real scope IDs are positive,
	 * so mapping `0` → `-1` is safe.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return string
	 */
	private function sentinel( $value ): string {
		$value = sanitize_text_field( (string) $value );
		return ( '' === $value || '0' === $value ) ? '-1' : $value;
	}

	/**
	 * Reshape a `LeavePolicy` model into the v2 single-item row (raw IDs).
	 *
	 * @param mixed           $policy  A `Models\LeavePolicy` instance.
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $policy, $request ) {
		unset( $request );

		if ( ! $policy ) {
			return [];
		}

		$data = [
			'id'                  => (int) $policy->id,
			'leave_id'            => $this->cast_int_or_null( $policy->leave_id ),
			'name'                => $policy->leave ? (string) $policy->leave->name : '',
			'description'         => $this->cast_string_or_null( $policy->description ) ?? '',
			'days'                => (int) $policy->days,
			'color'               => $this->cast_string_or_null( $policy->color ) ?? '',
			'employee_type'       => (string) $policy->employee_type,
			'department_id'       => (string) $policy->department_id,
			'designation_id'      => (string) $policy->designation_id,
			'location_id'         => (string) $policy->location_id,
			'gender'              => (string) $policy->gender,
			'marital'             => (string) $policy->marital,
			'f_year'              => $this->cast_int_or_null( $policy->f_year ),
			'applicable_from'     => (int) $policy->applicable_from_days,
			'apply_for_new_users' => (int) $policy->apply_for_new_users === 1,
		];

		/**
		 * Let pro modules append their saved columns to the single-policy
		 * response so the React edit form can prefill injected fields (Advanced
		 * Leave: half-day, accrual, carry-forward, segregation).
		 *
		 * @param array $data   The base response row.
		 * @param mixed $policy The `Models\LeavePolicy` instance.
		 */
		return apply_filters( 'erp_hr_leave_policy_rest_item', $data, $policy );
	}

	/**
	 * Reshape a formatted list row (from `erp_hr_leave_get_policies()`, which
	 * already resolved the "All" labels) into the v2 list row.
	 *
	 * @param mixed $row stdClass/array list row.
	 *
	 * @return array
	 */
	private function prepare_list_row( $row ): array {
		$row = (object) $row;

		return [
			'id'             => (int) ( $row->id ?? 0 ),
			'leave_id'       => $this->cast_int_or_null( $row->leave_id ?? null ),
			'name'           => (string) ( $row->name ?? '' ),
			'description'    => (string) ( $row->description ?? '' ),
			'days'           => (int) ( $row->days ?? 0 ),
			'color'          => (string) ( $row->color ?? '' ),
			'department_id'  => $this->cast_int_or_null( $row->department_id ?? null ),
			'department'     => (string) ( $row->department ?? '' ),
			'designation_id' => $this->cast_int_or_null( $row->designation_id ?? null ),
			'designation'    => (string) ( $row->designation ?? '' ),
			'location'       => (string) ( $row->location ?? '' ),
			'f_year'         => (string) ( $row->f_year ?? '' ),
			'gender'         => (string) ( $row->gender ?? '' ),
			'marital'        => (string) ( $row->marital ?? '' ),
			'employee_type'  => (string) ( $row->employee_type ?? '' ),
		];
	}

	/**
	 * Convert a `[ key => label ]` enum map into `[ { value, label } ]` options.
	 *
	 * @param array $map Enum map.
	 *
	 * @return array
	 */
	private function enum_to_options( array $map ): array {
		$options = [];
		foreach ( $map as $value => $label ) {
			$options[] = [ 'value' => (string) $value, 'label' => (string) $label ];
		}
		return $options;
	}

	/**
	 * Write params for create / update.
	 *
	 * @return array
	 */
	public function get_write_params(): array {
		return [
			'leave_id'            => [ 'description' => __( 'Leave type ID.', 'erp' ), 'type' => 'integer', 'sanitize_callback' => 'absint' ],
			'days'                => [ 'description' => __( 'Number of days.', 'erp' ), 'type' => 'integer', 'sanitize_callback' => 'absint' ],
			'color'               => [ 'description' => __( 'Calendar color (hex).', 'erp' ), 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'description'         => [ 'description' => __( 'Policy description.', 'erp' ), 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'f_year'              => [ 'description' => __( 'Financial year ID.', 'erp' ), 'type' => 'integer', 'sanitize_callback' => 'absint' ],
			'employee_type'       => [ 'description' => __( 'Employee type, or -1 for all.', 'erp' ), 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'department_id'       => [ 'description' => __( 'Department ID, or -1 for all.', 'erp' ), 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'designation_id'      => [ 'description' => __( 'Designation ID, or -1 for all.', 'erp' ), 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'location_id'         => [ 'description' => __( 'Location ID, or -1 for all.', 'erp' ), 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'gender'              => [ 'description' => __( 'Gender, or -1 for all.', 'erp' ), 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'marital'             => [ 'description' => __( 'Marital status, or -1 for all.', 'erp' ), 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'applicable_from'     => [ 'description' => __( 'Applicable-from day offset.', 'erp' ), 'type' => 'integer', 'sanitize_callback' => 'absint' ],
			'apply_for_new_users' => [ 'description' => __( 'Auto-apply to new employees.', 'erp' ), 'type' => 'boolean' ],
			'segre'               => [ 'description' => __( 'Per-segment day segregation (optional).', 'erp' ), 'type' => 'array', 'items' => [ 'type' => 'integer' ] ],
			'extra'               => [
				'description'          => __( 'Pro-injected extra fields, e.g. Advanced Leave (optional).', 'erp' ),
				'type'                 => 'object',
				'properties'           => [],
				'additionalProperties' => true,
			],
		];
	}

	/**
	 * Collection params: pagination + scope filters.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		$params = parent::get_collection_params();

		$params['orderby']        = [ 'type' => 'string', 'default' => 'id', 'sanitize_callback' => 'sanitize_key' ];
		$params['order']          = [ 'type' => 'string', 'default' => 'asc', 'enum' => [ 'asc', 'desc' ], 'sanitize_callback' => 'sanitize_key' ];
		$params['f_year']         = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['department_id']  = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['designation_id'] = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['employee_type']  = [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ];

		return $params;
	}

	/**
	 * Convert a model WP_Error into a REST WP_Error with an HTTP status.
	 *
	 * @param \WP_Error $error  Error from the model layer.
	 * @param int       $status HTTP status (default 400).
	 *
	 * @return \WP_Error
	 */
	private function to_rest_error( \WP_Error $error, int $status = 400 ): \WP_Error {
		return new \WP_Error(
			$error->get_error_code() ?: 'rest_leave_policy_error',
			$error->get_error_message() ?: __( 'The leave policy could not be saved.', 'erp' ),
			[ 'status' => $status ]
		);
	}

	/**
	 * JSON Schema for a single leave policy.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'leave_policy',
			'type'       => 'object',
			'properties' => [
				'id'                  => [ 'type' => 'integer' ],
				'leave_id'            => [ 'type' => [ 'integer', 'null' ] ],
				'name'                => [ 'type' => 'string' ],
				'description'         => [ 'type' => 'string' ],
				'days'                => [ 'type' => 'integer' ],
				'color'               => [ 'type' => 'string' ],
				'employee_type'       => [ 'type' => 'string' ],
				'department_id'       => [ 'type' => 'string' ],
				'designation_id'      => [ 'type' => 'string' ],
				'location_id'         => [ 'type' => 'string' ],
				'gender'              => [ 'type' => 'string' ],
				'marital'             => [ 'type' => 'string' ],
				'f_year'              => [ 'type' => [ 'integer', 'null' ] ],
				'applicable_from'     => [ 'type' => 'integer' ],
				'apply_for_new_users' => [ 'type' => 'boolean' ],
			],
		];
	}
}
