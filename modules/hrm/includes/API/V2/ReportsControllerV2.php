<?php
/**
 * WP-ERP HR — `erp/v2/reports` REST controller (read-only, FREE reports).
 *
 * Endpoints:
 *   GET /erp/v2/reports                        — report catalogue (key/title/description).
 *   GET /erp/v2/reports/age-profile            — age breakdown by department.
 *   GET /erp/v2/reports/gender-profile         — gender ratio.
 *   GET /erp/v2/reports/headcount              — headcount-by-month chart + active list.
 *   GET /erp/v2/reports/salary-history         — compensation history per active employee.
 *   GET /erp/v2/reports/years-of-service       — hire anniversaries grouped by month/day.
 *   GET /erp/v2/reports/leaves                 — employee-based leave matrix (spent/days per policy).
 *   GET /erp/v2/reports/leaves/form-options    — filter pickers for the leaves report.
 *
 * Mirrors the legacy reporting views in modules/hrm/views/reporting/* and the
 * `LeaveReportEmployeeBased` WP_List_Table. The legacy admin renders these
 * directly in PHP (no AJAX); this controller exposes the exact same data via the
 * same data functions: `erp_hr_get_age_breakdown_data()`,
 * `erp_hr_get_gender_ratio_data()`, `erp_hr_get_headcount()`,
 * `erp_get_leave_report()`, `erp_hr_get_reports()`. The legacy menu gates the
 * Reports page on the HR-manager role (`erp_hr_manager`), so every route here
 * uses that same gate. No write paths exist; `erp/v1` stays untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Employee as EmployeeModel;
use WeDevs\ERP\HRM\Models\FinancialYear;
use WeDevs\ERP\HRM\Models\LeavePolicy;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class ReportsControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'reports';

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
					'callback'            => [ $this, 'get_catalogue' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/age-profile',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_age_profile' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/gender-profile',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_gender_profile' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/headcount',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_headcount' ],
					'permission_callback' => [ $this, 'permission_view' ],
					'args'                => [
						'year'       => [
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						],
						'department' => [
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
						],
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/salary-history',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_salary_history' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/years-of-service',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_years_of_service' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/leaves/form-options',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_leaves_form_options' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/leaves',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_leaves' ],
					'permission_callback' => [ $this, 'permission_view' ],
					'args'                => $this->leaves_collection_params(),
				],
			]
		);
	}

	/**
	 * Permission — the Reports menu is gated on the HR-manager role (`erp_hr_manager`)
	 * in modules/hrm/includes/Admin/AdminMenu.php. Match that exactly.
	 *
	 * @return bool
	 */
	public function permission_view(): bool {
		return $this->permission_cap( erp_hr_get_manager_role() );
	}

	// -----------------------------------------------------------------
	// Catalogue
	// -----------------------------------------------------------------

	/**
	 * GET /reports — the free report catalogue (mirrors erp_hr_get_reports()).
	 *
	 * @return WP_REST_Response
	 */
	public function get_catalogue(): WP_REST_Response {
		$reports = erp_hr_get_reports();
		$items   = [];

		foreach ( $reports as $key => $report ) {
			$items[] = [
				'key'         => (string) $key,
				'title'       => $this->cast_string_or_null( $report['title'] ?? '' ) ?? '',
				'description' => $this->cast_string_or_null( $report['description'] ?? '' ) ?? '',
			];
		}

		return rest_ensure_response( $items );
	}

	// -----------------------------------------------------------------
	// Age profile
	// -----------------------------------------------------------------

	/**
	 * GET /reports/age-profile.
	 *
	 * @return WP_REST_Response
	 */
	public function get_age_profile(): WP_REST_Response {
		$data = erp_hr_get_age_breakdown_data();
		$rows = [];

		foreach ( (array) $data as $row ) {
			$rows[] = [
				'department' => $this->cast_string_or_null( $row->department ?? '' ) ?? '',
				'under_18'   => (int) ( $row->_under18 ?? 0 ),
				'age_18_25'  => (int) ( $row->_18_to_25 ?? 0 ),
				'age_26_35'  => (int) ( $row->_26_to_35 ?? 0 ),
				'age_36_45'  => (int) ( $row->_36_to_45 ?? 0 ),
				'age_46_55'  => (int) ( $row->_46_to_55 ?? 0 ),
				'age_56_65'  => (int) ( $row->_56_to_65 ?? 0 ),
				'age_65_plus' => (int) ( $row->_65_plus ?? 0 ),
			];
		}

		return rest_ensure_response( [ 'rows' => $rows ] );
	}

	// -----------------------------------------------------------------
	// Gender profile
	// -----------------------------------------------------------------

	/**
	 * GET /reports/gender-profile.
	 *
	 * @return WP_REST_Response
	 */
	public function get_gender_profile(): WP_REST_Response {
		$data = erp_hr_get_gender_ratio_data();
		$rows = [];

		// erp_hr_get_gender_ratio_data() returns null when there are no employees.
		foreach ( (array) $data as $row ) {
			$rows[] = [
				'gender'     => $this->cast_string_or_null( $row->gender ?? '' ) ?? '',
				'count'      => (int) ( $row->count ?? 0 ),
				'percentage' => $this->cast_string_or_null( $row->percentage ?? '' ) ?? '0%',
			];
		}

		// By-department breakdown (mirrors views/reporting/gender-profile.php's
		// "Employee Gender Ratio By Department" table + stacked bar). For each
		// department, `erp_hr_get_gender_count( $dept_id )` returns the active
		// male / female / other counts. The `other` bucket is labelled
		// "Unspecified" in the UI. Legacy caps the view at the department query's
		// default (20); we return all departments for a complete breakdown.
		$by_department = [];

		if ( function_exists( 'erp_hr_get_departments' ) && function_exists( 'erp_hr_get_gender_count' ) ) {
			foreach ( (array) erp_hr_get_departments( [ 'number' => -1 ] ) as $department ) {
				$counts = (array) erp_hr_get_gender_count( (int) ( $department->id ?? 0 ) );

				$by_department[] = [
					'department' => $this->cast_string_or_null( $department->title ?? '' ) ?? '',
					'male'       => (int) ( $counts['male'] ?? 0 ),
					'female'     => (int) ( $counts['female'] ?? 0 ),
					'other'      => (int) ( $counts['other'] ?? 0 ),
				];
			}
		}

		return rest_ensure_response(
			[
				'rows'          => $rows,
				'by_department' => $by_department,
			]
		);
	}

	// -----------------------------------------------------------------
	// Headcount
	// -----------------------------------------------------------------

	/**
	 * GET /reports/headcount?year=&department= .
	 *
	 * Mirrors views/reporting/headcount.php: a 12-month headcount series ending
	 * on December of the selected year (current month when no year), the active
	 * total, the year dropdown range, and the filtered active employee list.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_headcount( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;

		$company_starts = erp_get_option( 'gen_com_start', 'erp_settings_general' );
		$start_year     = $company_starts ? (int) gmdate( 'Y', strtotime( $company_starts ) ) : (int) current_time( 'Y' );
		$current_year   = (int) current_time( 'Y' );

		$raw_year   = $request['year'];
		$query_year = ( $raw_year !== null && '' !== $raw_year && '-1' !== (string) $raw_year )
			? (string) $raw_year
			: gmdate( 'Y' );

		$raw_dept   = $request['department'];
		$query_dept = ( $raw_dept !== null && '' !== $raw_dept && '-1' !== (string) $raw_dept )
			? (int) $raw_dept
			: '';

		$this_month = $query_year ? gmdate( $query_year . '-12-01' ) : current_time( 'Y-m-01' );

		$chart = [];
		for ( $i = 0; $i <= 11; $i++ ) {
			$month   = gmdate( 'Y-m', strtotime( $this_month . " -$i months" ) );
			$chart[] = [
				'month' => $month,
				'count' => (int) erp_hr_get_headcount( $month, $query_dept, 'month' ),
			];
		}
		// Oldest month first (the view builds newest-first then plots by timestamp).
		$chart = array_reverse( $chart );

		$total_emp_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active'" );

		$user_all      = $wpdb->get_results( "SELECT user_id, department, hiring_date, termination_date FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active'" );
		$user_filtered = [];

		foreach ( $user_all as $user ) {
			if ( $query_dept && $user->department != $query_dept ) {
				continue;
			}

			if ( '0000-00-00' == $user->hiring_date ) {
				continue;
			}

			$hiring_year      = (int) substr( $user->hiring_date, 0, 4 );
			$termination_year = '0000-00-00' == $user->termination_date ? (int) current_time( 'Y' ) : (int) substr( $user->termination_date, 0, 4 );

			if ( $query_year ) {
				if ( $query_year < $hiring_year || $query_year > $termination_year ) {
					continue;
				}
			}

			$user_filtered[] = (int) $user->user_id;
		}

		$employees = [];
		foreach ( $user_filtered as $user_id ) {
			$employee    = new Employee( $user_id );
			$employees[] = [
				'user_id'          => (int) $employee->get_user_id(),
				'employee_id'      => $this->cast_string_or_null( $employee->employee_id ),
				'name'             => $this->cast_string_or_null( $employee->display_name ) ?? '',
				'avatar'           => $employee->get_avatar_url( 60 ) ?: null,
				'hire_date'        => $this->cast_date_iso( $employee->hiring_date ),
				'designation'      => $this->cast_string_or_null( $employee->designation_title ),
				'department'       => $this->cast_string_or_null( $employee->department_title ),
				'location'         => $this->cast_string_or_null( $employee->location_name ),
				'status'           => $this->cast_string_or_null( $employee->status ),
			];
		}

		// Year dropdown range (descending, like the legacy <select>).
		$years = [];
		for ( $y = $current_year; $y >= $start_year; $y-- ) {
			$years[] = $y;
		}

		// Department dropdown (legacy used erp_hr_get_departments_dropdown_raw()).
		$departments = [];
		foreach ( erp_hr_get_departments( [ 'number' => '-1' ] ) as $department ) {
			$departments[] = [
				'id'    => (int) $department->id,
				'label' => $this->cast_string_or_null( $department->title ) ?? '',
			];
		}

		return rest_ensure_response( [
			'chart'       => $chart,
			'total'       => $total_emp_count,
			'employees'   => $employees,
			'years'       => $years,
			'departments' => $departments,
			'filters'     => [
				'year'       => (string) $query_year,
				'department' => $query_dept === '' ? null : (int) $query_dept,
			],
		] );
	}

	// -----------------------------------------------------------------
	// Salary history
	// -----------------------------------------------------------------

	/**
	 * GET /reports/salary-history.
	 *
	 * @return WP_REST_Response
	 */
	public function get_salary_history(): WP_REST_Response {
		global $wpdb;

		$all_user_id = $wpdb->get_col( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active' ORDER BY hiring_date DESC" );
		$rows        = [];

		foreach ( (array) $all_user_id as $user_id ) {
			$employee      = new Employee( (int) $user_id );
			$compensations = $employee->get_job_histories( 'compensation' );

			if ( empty( $compensations['compensation'] ) ) {
				continue;
			}

			$line = 0;
			foreach ( $compensations['compensation'] as $compensation ) {
				$rows[] = [
					'user_id'     => (int) $employee->get_user_id(),
					'employee_id' => $this->cast_string_or_null( $employee->employee_id ),
					// Name shown only on the first row per employee (legacy rowspan behaviour).
					'name'        => 0 === $line ? ( $this->cast_string_or_null( $employee->display_name ) ?? '' ) : '',
					'avatar'      => 0 === $line ? ( $employee->get_avatar_url( 60 ) ?: null ) : null,
					'date'        => $this->cast_date_iso( $compensation['date'] ?? '' ),
					'pay_rate'    => $this->cast_string_or_null( $compensation['pay_rate'] ?? '' ),
					'pay_type'    => $this->cast_string_or_null( $compensation['pay_type'] ?? '' ),
				];
				$line++;
			}
		}

		return rest_ensure_response( [ 'rows' => $rows ] );
	}

	// -----------------------------------------------------------------
	// Years of service
	// -----------------------------------------------------------------

	/**
	 * GET /reports/years-of-service.
	 *
	 * Mirrors views/reporting/years-of-service.php: active employees grouped by
	 * hire month then day, each carrying the completed years of service.
	 *
	 * @return WP_REST_Response
	 */
	public function get_years_of_service(): WP_REST_Response {
		global $wpdb;

		$all_user_id = $wpdb->get_col( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active'" );
		$hire_data   = [];

		foreach ( (array) $all_user_id as $user_id ) {
			$employee = new Employee( (int) $user_id );
			$date     = date_parse_from_format( 'Y-m-d', $employee->hiring_date ?? '' );
			$month    = (int) $date['month'];
			$day      = (int) $date['day'];

			if ( $month <= 0 ) {
				continue;
			}

			$years = (int) gmdate( 'Y', time() ) - (int) gmdate( 'Y', strtotime( $employee->hiring_date ) );

			if ( $years <= 0 ) {
				continue;
			}

			$hire_data[ $month ][ $day ][] = [
				'user_id'     => (int) $employee->get_user_id(),
				'name'        => $this->cast_string_or_null( $employee->display_name ) ?? '',
				'avatar'      => $employee->get_avatar_url( 60 ) ?: null,
				'hiring_date' => $this->cast_date_iso( $employee->hiring_date ),
				'years'       => $years,
			];
		}

		ksort( $hire_data );

		$months = [];
		foreach ( $hire_data as $month => $days ) {
			ksort( $days );

			$day_rows = [];
			foreach ( $days as $day => $people ) {
				$day_rows[] = [
					'day'    => (int) $day,
					'people' => $people,
				];
			}

			$months[] = [
				'month'      => (int) $month,
				'month_name' => gmdate( 'F', mktime( 0, 0, 0, (int) $month, 1 ) ),
				'days'       => $day_rows,
			];
		}

		return rest_ensure_response( [ 'months' => $months ] );
	}

	// -----------------------------------------------------------------
	// Leaves
	// -----------------------------------------------------------------

	/**
	 * Collection params for the leaves report (mirror LeaveReportEmployeeBased filters).
	 *
	 * @return array
	 */
	private function leaves_collection_params(): array {
		return [
			'page'                   => [
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			],
			'per_page'               => [
				'type'              => 'integer',
				'default'           => 20,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
			],
			'filter_year'            => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'filter_designation'     => [
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			],
			'filter_department'      => [
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			],
			'filter_employment_type' => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'start'                  => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'end'                    => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * GET /reports/leaves/form-options — filter pickers for the leaves report.
	 *
	 * @return WP_REST_Response
	 */
	public function get_leaves_form_options(): WP_REST_Response {
		$financial_years = [];
		foreach ( FinancialYear::all() as $fy ) {
			$financial_years[] = [
				'id'    => (int) $fy->id,
				'label' => $this->cast_string_or_null( $fy->fy_name ) ?? '',
			];
		}

		$designations = [];
		foreach ( erp_hr_get_designations( [ 'number' => '-1' ] ) as $designation ) {
			$designations[] = [
				'id'    => (int) $designation->id,
				'label' => $this->cast_string_or_null( $designation->title ) ?? '',
			];
		}

		$departments = [];
		foreach ( erp_hr_get_departments( [ 'number' => '-1' ] ) as $department ) {
			$departments[] = [
				'id'    => (int) $department->id,
				'label' => $this->cast_string_or_null( $department->title ) ?? '',
			];
		}

		$employment_types = [];
		foreach ( erp_hr_get_employee_types() as $key => $title ) {
			$employment_types[] = [
				'value' => (string) $key,
				'label' => $this->cast_string_or_null( $title ) ?? '',
			];
		}

		$current_f_year = erp_hr_get_financial_year_from_date();

		return rest_ensure_response( [
			'financial_years'  => $financial_years,
			'designations'     => $designations,
			'departments'      => $departments,
			'employment_types' => $employment_types,
			'current_f_year'   => ! empty( $current_f_year ) ? (int) $current_f_year->id : 0,
		] );
	}

	/**
	 * GET /reports/leaves — employee-based leave matrix.
	 *
	 * Mirrors LeaveReportEmployeeBased::prepare_items(): paginate active
	 * employees (optionally filtered by designation/department/type), build the
	 * per-policy columns, and fill each cell from erp_get_leave_report().
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_leaves( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;

		$policy_tbl     = "{$wpdb->prefix}erp_hr_leave_policies";
		$leave_name_tbl = "{$wpdb->prefix}erp_hr_leaves";

		$policies = LeavePolicy::select( "$leave_name_tbl.name", "$policy_tbl.id", "$policy_tbl.leave_id" )
			->leftJoin( $leave_name_tbl, "$policy_tbl.leave_id", '=', "$leave_name_tbl.id" )
			->get();

		// Columns keyed by leave_id (legacy get_columns()).
		$columns = [];
		$seen    = [];
		foreach ( $policies as $policy ) {
			$leave_id = (int) $policy->leave_id;
			if ( isset( $seen[ $leave_id ] ) ) {
				continue;
			}
			$seen[ $leave_id ] = true;
			$columns[]         = [
				'leave_id' => $leave_id,
				'name'     => $this->cast_string_or_null( $policy->name ) ?? '',
			];
		}

		$f_year         = erp_hr_get_financial_year_from_date();
		$current_f_year = ! empty( $f_year ) ? (int) $f_year->id : 0;

		$page     = max( 1, (int) ( $request['page'] ?? 1 ) );
		$per_page = (int) ( $request['per_page'] ?? 20 );
		if ( $per_page < 1 ) {
			$per_page = 20;
		}
		$offset = ( $page - 1 ) * $per_page;

		$selected_designation = $request['filter_designation'] !== null ? (int) $request['filter_designation'] : 0;
		$selected_department  = $request['filter_department'] !== null ? (int) $request['filter_department'] : 0;
		$selected_type        = $request['filter_employment_type'] !== null ? (string) $request['filter_employment_type'] : '';
		$selected_f_year      = ( $request['filter_year'] !== null && '' !== $request['filter_year'] )
			? (string) $request['filter_year']
			: (string) $current_f_year;
		$start_date           = $request['start'] !== null && '' !== $request['start'] ? (string) $request['start'] : null;
		$end_date             = $request['end'] !== null && '' !== $request['end'] ? (string) $request['end'] : null;

		$query = EmployeeModel::where( 'status', 'active' )->select( 'user_id' )->orderBy( 'hiring_date', 'desc' );

		if ( $selected_department && -1 != $selected_department ) {
			$query->where( 'department', $selected_department );
		}

		if ( $selected_designation && -1 != $selected_designation ) {
			$query->where( 'designation', $selected_designation );
		}

		if ( $selected_type && '-1' != $selected_type ) {
			$query->where( 'type', $selected_type );
		}

		$total_count = $query->count();

		$employees_obj = $query->skip( $offset )->take( $per_page )->get()->toArray();
		$employees     = wp_list_pluck( $employees_obj, 'user_id' );

		$reports = erp_get_leave_report( $employees, $selected_f_year, $start_date, $end_date );

		$rows = [];
		foreach ( $employees as $user_id ) {
			$user_id = (int) $user_id;
			$user    = get_user_by( 'ID', $user_id );

			$cells = [];
			$report = isset( $reports[ $user_id ] ) ? $reports[ $user_id ] : [];

			foreach ( $columns as $column ) {
				$leave_id = $column['leave_id'];

				if ( isset( $report[ $leave_id ] ) ) {
					$summary  = $report[ $leave_id ];
					$cells[ (string) $leave_id ] = [
						'spent' => (float) ( $summary['spent'] ?? 0 ),
						'days'  => (float) ( $summary['days'] ?? 0 ),
					];
				} else {
					$cells[ (string) $leave_id ] = null;
				}
			}

			$employee = new Employee( $user_id );
			$rows[] = [
				'user_id' => $user_id,
				'name'    => $this->full_name( $user ),
				'avatar'  => $employee->get_avatar_url( 60 ) ?: null,
				'cells'   => $cells,
			];
		}

		$response = rest_ensure_response( [
			'columns'        => $columns,
			'rows'           => $rows,
			'current_f_year' => $current_f_year,
		] );

		return $this->paginate( $response, $request, (int) $total_count );
	}

	/**
	 * Build a user's full name (first middle last), like LeaveReportEmployeeBased.
	 *
	 * @param \WP_User|false $user User.
	 *
	 * @return string
	 */
	private function full_name( $user ): string {
		if ( ! $user instanceof \WP_User ) {
			return '';
		}

		$name = [];
		if ( $user->first_name ) {
			$name[] = $user->first_name;
		}
		if ( $user->middle_name ) {
			$name[] = $user->middle_name;
		}
		if ( $user->last_name ) {
			$name[] = $user->last_name;
		}

		$full = trim( implode( ' ', $name ) );

		return '' !== $full ? $full : ( $this->cast_string_or_null( $user->display_name ) ?? '' );
	}
}
