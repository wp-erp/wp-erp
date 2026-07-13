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
					'permission_callback' => [ $this, 'permission_wish' ],
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
	/**
	 * Sending a birthday wish is an admin/HR-manager action (the Wish button is
	 * only shown to managers on the dashboard).
	 *
	 * @return bool
	 */
	public function permission_wish(): bool {
		return current_user_can( erp_hr_get_manager_role() );
	}

	public function birthday_wish( WP_REST_Request $request ) {
		$employee_user_id = (int) $request['employee_user_id'];

		if ( ! $employee_user_id ) {
			return new \WP_Error( 'rest_invalid_employee', __( 'Invalid employee.', 'erp' ), [ 'status' => 400 ] );
		}

		$emailer = wperp()->emailer->get_email( 'BirthdayWish' );

		if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
			$emailer->trigger( $employee_user_id );
		}

		// Persist the "sent" state so the Wish button stays disabled after a
		// refresh (this calendar year). Stored on the wisher, per recipient.
		$key  = $this->birthday_wish_meta_key();
		$sent = array_map( 'intval', (array) get_user_meta( get_current_user_id(), $key, true ) );

		if ( ! in_array( $employee_user_id, $sent, true ) ) {
			$sent[] = $employee_user_id;
			update_user_meta( get_current_user_id(), $key, $sent );
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

		// --- Who is out (approved leave: this month + next month) -------
		// Mirrors the legacy "Who is out" widget, which lists both This Month
		// and Next Month approved leave, each row optionally flagged half-day
		// (day_status_id 2 = Morning, 3 = Afternoon).
		$on_leave = [];
		foreach ( (array) erp_hr_get_current_month_leave_list() as $leave ) {
			$on_leave[] = $this->prepare_on_leave_row( $leave, 'this_month' );
		}
		foreach ( (array) erp_hr_get_next_month_leave_list() as $leave ) {
			$on_leave[] = $this->prepare_on_leave_row( $leave, 'next_month' );
		}

		// --- About to end (manager-only): contractual + trainee employees
		// whose job period ends within 21 days (legacy "About to end" widget,
		// gated to managers). -------------------------------------------
		$about_to_end = [ 'contract' => [], 'trainee' => [] ];
		if ( $is_manager ) {
			$about_to_end = $this->about_to_end_employees();
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
		// Mirrors the legacy "Latest Announcement" widget: managers see the 5
		// most recent published announcements (no per-user read state), everyone
		// else sees their assigned announcements with a per-user read/unread flag.
		$announcements = [];
		if ( $is_manager ) {
			$posts = erp_hr_get_announcements( [ 'numberposts' => 5, 'post_status' => 'publish' ] );
			foreach ( (array) $posts as $post ) {
				$announcements[] = [
					'id'      => (int) $post->ID,
					'title'   => $this->cast_string_or_null( $post->post_title ) ?? __( '(no title)', 'erp' ),
					'excerpt' => $this->announcement_excerpt( (int) $post->ID ),
					'date'    => $this->cast_date_iso( $post->post_date ),
					'read'    => true,
				];
			}
		} else {
			$assigned = erp_hr_employee_dashboard_announcement( get_current_user_id() );
			foreach ( (array) $assigned as $row ) {
				$announcements[] = [
					'id'      => (int) ( $row->post_id ?? $row->ID ?? 0 ),
					'title'   => $this->cast_string_or_null( $row->post_title ?? '' ) ?? __( '(no title)', 'erp' ),
					'excerpt' => $this->announcement_excerpt( (int) ( $row->post_id ?? $row->ID ?? 0 ) ),
					'date'    => $this->cast_date_iso( $row->post_date ?? null ),
					'read'    => ( $row->status ?? 'unread' ) === 'read',
				];
			}
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
				'pending_requests'     => $pending_requests,
			],
			'on_leave'           => $on_leave,
			'about_to_end'       => $about_to_end,
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

		// Recipients the current user has already wished this year (persisted
		// "sent" state so the button stays disabled after a refresh).
		$wished = array_map( 'intval', (array) get_user_meta( get_current_user_id(), $this->birthday_wish_meta_key(), true ) );

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
				'designation'   => $this->designation_title( $user_id ),
				'date_of_birth' => $this->cast_date_iso( $row->date_of_birth ?? null ),
				'wished'        => in_array( $user_id, $wished, true ),
			];
		}

		return $people;
	}

	/**
	 * Per-user, per-year meta key holding the recipients this user has already
	 * sent a birthday wish to (so the "Sent" state survives a page refresh).
	 *
	 * @return string
	 */
	private function birthday_wish_meta_key(): string {
		return '_erp_hr_birthday_wishes_' . wp_date( 'Y' );
	}

	/**
	 * Shape a "who is out" leave row.
	 *
	 * @param mixed  $leave  Leave row (from `erp_hr_get_*_month_leave_list()`).
	 * @param string $period 'this_month' | 'next_month'.
	 *
	 * @return array
	 */
	private function prepare_on_leave_row( $leave, string $period ): array {
		$day_status_id = (int) ( $leave->day_status_id ?? 1 );

		return [
			'user_id'       => (int) ( $leave->user_id ?? 0 ),
			'name'          => $this->cast_string_or_null( $leave->display_name ?? '' ) ?? '',
			'avatar_url'    => get_avatar_url( (int) ( $leave->user_id ?? 0 ), [ 'size' => 40 ] ) ?: '',
			'designation'   => $this->designation_title( (int) ( $leave->user_id ?? 0 ) ),
			'start_date'    => $this->ts_to_iso( $leave->start_date ?? null ),
			'end_date'      => $this->ts_to_iso( $leave->end_date ?? null ),
			'period'        => $period,
			// 1 = full day (no badge), 2 = Morning half-day, 3 = Afternoon half-day.
			'day_status_id' => $day_status_id,
			'day_status'    => $day_status_id > 1 && function_exists( 'erp_hr_leave_request_get_day_statuses' )
				? ( $this->cast_string_or_null( erp_hr_leave_request_get_day_statuses( $day_status_id ) ) ?? '' )
				: '',
		];
	}

	/**
	 * Resolve an employee's designation title for the who-is-out / birthday rows.
	 *
	 * @param int $user_id Employee WP user id.
	 *
	 * @return string Designation title, or '' when none.
	 */
	private function designation_title( int $user_id ): string {
		if ( ! $user_id ) {
			return '';
		}

		$employee = new \WeDevs\ERP\HRM\Employee( $user_id );

		return $this->cast_string_or_null( $employee->get_designation( 'view' ) ) ?? '';
	}

	/**
	 * One-line plain-text excerpt for an announcement row (Figma: cropped
	 * description under the title).
	 *
	 * @param int $post_id Announcement post id.
	 *
	 * @return string Trimmed excerpt, or '' when empty.
	 */
	private function announcement_excerpt( int $post_id ): string {
		if ( ! $post_id ) {
			return '';
		}

		$content = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );

		return $this->cast_string_or_null( wp_trim_words( $content, 14, '…' ) ) ?? '';
	}

	/**
	 * Contractual + trainee employees whose job period ends within 21 days.
	 *
	 * Mirrors `erp_hr_dashboard_widget_about_to_end()`: reads each employee's
	 * `end_date` user-meta, keeps those 1–20 days out, splits by type and sorts
	 * ascending by end date.
	 *
	 * @return array{contract:array,trainee:array}
	 */
	private function about_to_end_employees(): array {
		$contract = [];
		$trainee  = [];

		$today = date_create( current_time( 'Y-m-d' ) );

		foreach ( (array) erp_hr_get_contractual_employee() as $user ) {
			$user_id  = (int) ( $user->user_id ?? 0 );
			$end_date = $user_id ? (string) get_user_meta( $user_id, 'end_date', true ) : '';

			if ( ! $user_id || '' === $end_date ) {
				continue;
			}

			$end = date_create( $end_date );
			if ( ! $end ) {
				continue;
			}

			$diff = date_diff( $today, $end );

			// Legacy keeps strictly future end dates within 21 days.
			if ( $diff->invert !== 1 || $diff->days <= 0 || $diff->days >= 21 ) {
				continue;
			}

			$employee = new \WeDevs\ERP\HRM\Employee( $user_id );
			$row      = [
				'user_id'  => $user_id,
				'name'     => $this->cast_string_or_null( $employee->get_full_name() ) ?? '',
				'end_date' => $this->cast_date_iso( $end_date ),
			];

			if ( 'contract' === ( $user->type ?? '' ) ) {
				$contract[] = $row;
			} elseif ( 'trainee' === ( $user->type ?? '' ) ) {
				$trainee[] = $row;
			}
		}

		$sort_by_end = static function ( $a, $b ) {
			return strcmp( (string) ( $a['end_date'] ?? '' ), (string) ( $b['end_date'] ?? '' ) );
		};
		usort( $contract, $sort_by_end );
		usort( $trainee, $sort_by_end );

		return [ 'contract' => $contract, 'trainee' => $trainee ];
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
		// Leave/holiday dates are calendar days stored as the instant of
		// site-local midnight — format in the site timezone (`wp_date`), not
		// UTC, or the day shifts back one for positive-offset sites.
		return wp_date( 'Y-m-d', (int) $value );
	}
}
