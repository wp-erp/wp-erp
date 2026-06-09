<?php
/**
 * WP-ERP HR — `erp/v2/dashboard` REST controller (read-only Overview widgets).
 *
 * Endpoint:
 *   GET /erp/v2/dashboard — aggregate payload for the HR Overview landing page.
 *
 * Mirrors the legacy HR dashboard (`views/dashboard.php` + the
 * `erp_hr_dashboard_widget_*` widgets): the active-employee / department /
 * designation badges, "who is out" (current-month approved leave), today's +
 * upcoming birthdays, upcoming holidays and the latest announcements — all from
 * the same free data functions. Manager-only figures (pending approvals) are
 * computed only when the current user can moderate leave. No write paths;
 * `erp/v1` stays untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class DashboardControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'dashboard';

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
					'callback'            => [ $this, 'get_dashboard' ],
					'permission_callback' => [ $this, 'permission_logged_in' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/birthday-wish',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'birthday_wish' ],
					'permission_callback' => [ $this, 'permission_logged_in' ],
					'args'                => [
						'employee_user_id' => [
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
	 * POST /erp/v2/dashboard/birthday-wish
	 *
	 * Sends a birthday wish e-mail to a coworker — mirrors
	 * `AjaxHandler::birthday_wish()` (triggers the `BirthdayWish` emailer).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function birthday_wish( WP_REST_Request $request ) {
		$employee_user_id = (int) $request['employee_user_id'];

		if ( ! $employee_user_id ) {
			return new \WP_Error( 'rest_invalid_employee', __( 'Invalid employee.', 'erp' ), [ 'status' => 400 ] );
		}

		$emailer = wperp()->emailer->get_email( 'BirthdayWish' );

		if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
			$emailer->trigger( $employee_user_id );
		}

		return rest_ensure_response( [ 'sent' => true, 'employee_user_id' => $employee_user_id ] );
	}

	/**
	 * GET /dashboard.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_dashboard( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );

		$is_manager = current_user_can( erp_hr_get_manager_role() );

		// --- Summary badges ---------------------------------------------
		$total_employees = (int) erp_hr_get_employees( [ 'count' => true, 'status' => 'active' ] );
		$departments     = erp_hr_get_departments( [ 'number' => '-1' ] );
		$designations    = erp_hr_get_designations( [ 'number' => '-1' ] );

		$headcount_this_month = (int) erp_hr_get_headcount( current_time( 'Y-m' ), '', 'month' );

		// --- Pending leave approvals + status split (manager-only) ------
		$pending_requests = 0;
		$leave_status     = [
			'approved' => 0,
			'pending'  => 0,
			'rejected' => 0,
		];
		if ( $is_manager ) {
			$f_year = erp_hr_get_financial_year_from_date();
			$fy_id  = ! empty( $f_year ) ? (int) $f_year->id : 0;
			$counts = erp_hr_leave_get_requests_count( $fy_id );
			$pending_requests     = (int) ( $counts['2']['count'] ?? 0 );
			$leave_status['approved'] = (int) ( $counts['1']['count'] ?? 0 );
			$leave_status['pending']  = (int) ( $counts['2']['count'] ?? 0 );
			$leave_status['rejected'] = (int) ( $counts['3']['count'] ?? 0 );
		}

		// --- Who is out (current-month approved leave) ------------------
		$on_leave = [];
		foreach ( (array) erp_hr_get_current_month_leave_list() as $leave ) {
			$on_leave[] = [
				'user_id'    => (int) ( $leave->user_id ?? 0 ),
				'name'       => $this->cast_string_or_null( $leave->display_name ?? '' ) ?? '',
				'avatar_url' => get_avatar_url( (int) ( $leave->user_id ?? 0 ), [ 'size' => 40 ] ) ?: '',
				'start_date' => $this->ts_to_iso( $leave->start_date ?? null ),
				'end_date'   => $this->ts_to_iso( $leave->end_date ?? null ),
			];
		}

		// --- Birthdays ---------------------------------------------------
		$birthdays_today    = $this->birthday_people( erp_hr_get_todays_birthday() );
		$birthdays_upcoming = $this->birthday_people( erp_hr_get_next_seven_days_birthday() );

		// --- Upcoming holidays (today → +30 days) -----------------------
		$holidays = [];
		$holiday_rows = erp_hr_get_holidays( [
			'number' => '-1',
			'from'   => current_time( 'Y-m-d' ),
			'to'     => gmdate( 'Y-m-d', strtotime( '+30 days', current_time( 'timestamp' ) ) ),
		] );
		foreach ( (array) $holiday_rows as $holiday ) {
			$holidays[] = [
				'id'          => (int) ( $holiday->id ?? 0 ),
				'title'       => $this->cast_string_or_null( $holiday->title ?? '' ) ?? '',
				'start'       => $this->cast_date_iso( $holiday->start ?? null ),
				'end'         => $this->cast_date_iso( $holiday->end ?? null ),
				'description' => $this->cast_string_or_null( $holiday->description ?? '' ),
			];
		}

		// --- Latest announcements ---------------------------------------
		$announcements = [];
		$posts = erp_hr_get_announcements( [ 'numberposts' => 5, 'post_status' => 'publish' ] );
		foreach ( (array) $posts as $post ) {
			$announcements[] = [
				'id'    => (int) $post->ID,
				'title' => $this->cast_string_or_null( $post->post_title ) ?? __( '(no title)', 'erp' ),
				'date'  => $this->cast_date_iso( $post->post_date ),
			];
		}

		// --- Charts ------------------------------------------------------
		$charts = [
			'headcount_trend' => $this->headcount_trend(),
			'gender'          => $this->gender_split(),
			'departments'     => $this->department_distribution( (array) $departments ),
			'leave_status'    => $leave_status,
		];

		$payload = [
			'is_hr_manager' => $this->cast_bool( $is_manager ),
			'summary'       => [
				'total_employees'      => $total_employees,
				'total_departments'    => count( (array) $departments ),
				'total_designations'   => count( (array) $designations ),
				'headcount_this_month' => $headcount_this_month,
				'pending_requests'     => $pending_requests,
			],
			'on_leave'           => $on_leave,
			'birthdays_today'    => $birthdays_today,
			'birthdays_upcoming' => $birthdays_upcoming,
			'holidays_upcoming'  => $holidays,
			'announcements'      => $announcements,
			'charts'             => $charts,
			// Pro modules push meaningful summary widgets here via the filter below;
			// the React dashboard renders each entry under "Upcoming Holidays".
			'pro_widgets'        => [],
		];

		/**
		 * Filter the HR dashboard payload.
		 *
		 * Active pro modules (recruitment, assets, reimbursement, attendance,
		 * payroll) append a widget to `pro_widgets` — each: `id`, `title`,
		 * optional `icon`/`to`, a `stats` list (`label` + `value`) and/or an
		 * `items` list (`label` + optional `meta`/`to`). Manager-gated figures
		 * should respect `$is_manager`.
		 *
		 * @since 1.13.5
		 *
		 * @param array $payload    Dashboard payload.
		 * @param bool  $is_manager Whether the current user is an HR manager.
		 */
		$payload = (array) apply_filters( 'erp_hr_v2_dashboard', $payload, $this->cast_bool( $is_manager ) );

		return rest_ensure_response( $payload );
	}

	/**
	 * 12-month active-headcount series ending this month (oldest → newest).
	 *
	 * Same derivation as the Headcount report / `views/reporting/headcount.php`.
	 *
	 * @return array
	 */
	private function headcount_trend(): array {
		$this_month = current_time( 'Y-m-01' );
		$trend      = [];

		for ( $i = 0; $i <= 11; $i++ ) {
			$month   = gmdate( 'Y-m', strtotime( $this_month . " -$i months" ) );
			$trend[] = [
				'month' => $month,
				'count' => (int) erp_hr_get_headcount( $month, '', 'month' ),
			];
		}

		return array_reverse( $trend );
	}

	/**
	 * Active-employee gender split (mirrors `erp_hr_get_gender_count()`).
	 *
	 * @return array
	 */
	private function gender_split(): array {
		$counts = (array) erp_hr_get_gender_count();

		return [
			'male'   => (int) ( $counts['male'] ?? 0 ),
			'female' => (int) ( $counts['female'] ?? 0 ),
			'other'  => (int) ( $counts['other'] ?? 0 ),
		];
	}

	/**
	 * Active-employee count per department (top 8, descending).
	 *
	 * @param array $departments Department objects (id + title).
	 *
	 * @return array
	 */
	private function department_distribution( array $departments ): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			"SELECT department, COUNT(*) AS total
			 FROM {$wpdb->prefix}erp_hr_employees
			 WHERE status = 'active' AND department > 0
			 GROUP BY department",
			ARRAY_A
		);

		$by_id = [];
		foreach ( (array) $rows as $row ) {
			$by_id[ (int) $row['department'] ] = (int) $row['total'];
		}

		$out = [];
		foreach ( $departments as $department ) {
			$id = (int) ( $department->id ?? 0 );
			if ( ! $id || empty( $by_id[ $id ] ) ) {
				continue;
			}
			$out[] = [
				'name'  => $this->cast_string_or_null( $department->title ?? '' ) ?? '',
				'count' => $by_id[ $id ],
			];
		}

		usort( $out, static function ( $a, $b ) {
			return $b['count'] <=> $a['count'];
		} );

		return array_slice( $out, 0, 8 );
	}

	/**
	 * Map a birthday employee collection to display rows.
	 *
	 * The free birthday functions return only `user_id` (+ `date_of_birth` for
	 * the upcoming list), so the name + avatar are resolved per user.
	 *
	 * @param mixed $collection Birthday employee collection.
	 *
	 * @return array
	 */
	private function birthday_people( $collection ): array {
		$people = [];

		foreach ( (array) $collection as $row ) {
			$user_id = (int) ( $row->user_id ?? 0 );
			if ( ! $user_id ) {
				continue;
			}

			$user = get_user_by( 'ID', $user_id );

			$people[] = [
				'user_id'       => $user_id,
				'name'          => $user instanceof \WP_User ? ( $this->cast_string_or_null( $user->display_name ) ?? '' ) : '',
				'avatar_url'    => get_avatar_url( $user_id, [ 'size' => 40 ] ) ?: '',
				'date_of_birth' => $this->cast_date_iso( $row->date_of_birth ?? null ),
			];
		}

		return $people;
	}

	/**
	 * Convert a stored unix timestamp (leave dates are stored as timestamps) to
	 * an ISO-8601 date. Returns null for empty/invalid input.
	 *
	 * @param mixed $value Unix timestamp.
	 *
	 * @return string|null
	 */
	private function ts_to_iso( $value ): ?string {
		if ( $value === null || $value === '' ) {
			return null;
		}
		if ( ! is_numeric( $value ) ) {
			// Already a date string — defer to the shared caster.
			return $this->cast_date_iso( $value );
		}
		return gmdate( 'Y-m-d', (int) $value );
	}
}
