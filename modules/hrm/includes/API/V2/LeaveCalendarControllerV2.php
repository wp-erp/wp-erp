<?php
/**
 * WP-ERP HR — `erp/v2/leave-calendar` REST controller.
 *
 * Endpoint:
 *   GET /erp/v2/leave-calendar?start=&end=&user_id=  — month-view events:
 *        leave requests (status ≠ rejected) + overlapping holidays + weekend
 *        background blocks, merged into one event list.
 *
 * Mirrors the legacy AJAX handler `AjaxHandler::get_leave_holiday_by_date()` —
 * same `erp_hr_get_leave_requests()` query (current user, all statuses), the
 * same holiday-overlap window, the same `erp_hr_get_work_days()` weekend
 * derivation + `filter_holidays` filter, the same pending / half-day labels and
 * colours. Only the envelope is the v2 contract; `erp/v1` + the AJAX layer stay
 * untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Models\LeaveHoliday;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class LeaveCalendarControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'leave-calendar';

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
					'callback'            => [ $this, 'get_events' ],
					'permission_callback' => [ $this, 'permission_view' ],
					'args'                => [
						'start'   => [
							'description'       => __( 'Range start (Y-m-d). Defaults to the start of this month.', 'erp' ),
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						],
						'end'     => [
							'description'       => __( 'Range end (Y-m-d). Defaults to the end of this month.', 'erp' ),
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						],
						'user_id' => [
							'description'       => __( 'Employee to show leave for. Defaults to the current user.', 'erp' ),
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
						],
					],
				],
			]
		);
	}

	/**
	 * The leave calendar (admin Calendar submenu) gates on `erp_leave_manage`.
	 *
	 * @return bool
	 */
	public function permission_view(): bool {
		return $this->permission_cap( 'erp_leave_manage' );
	}

	/**
	 * GET /erp/v2/leave-calendar
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_events( $request ): WP_REST_Response {
		$start_in = (string) ( $request['start'] ?? '' );
		$end_in   = (string) ( $request['end'] ?? '' );

		$start = '' !== $start_in
			? erp_current_datetime()->modify( $start_in )->setTime( 0, 0, 0 )
			: erp_current_datetime()->modify( 'start of this month' )->setTime( 0, 0, 0 );
		$end   = '' !== $end_in
			? erp_current_datetime()->modify( $end_in )->setTime( 23, 59, 59 )
			: erp_current_datetime()->modify( 'end of this month' )->setTime( 23, 59, 59 );

		$user_id = absint( $request['user_id'] ?? 0 ) ?: get_current_user_id();

		// --- Leave requests (current/selected user, all statuses) ---
		$leave_requests = erp_hr_get_leave_requests(
			[
				'user_id'    => $user_id,
				'status'     => 'all',
				'number'     => '-1',
				'start_date' => $start->getTimestamp(),
				'end_date'   => $end->getTimestamp(),
			],
			false
		);
		$leave_requests = isset( $leave_requests['data'] ) ? $leave_requests['data'] : [];

		// --- Holidays overlapping the window ---
		$start_date = $start->format( 'Y-m-d H:i:s' );
		$end_date   = $end->format( 'Y-m-d H:i:s' );

		$holiday = new LeaveHoliday();
		$holiday = $holiday->where(
			function ( $c ) use ( $start_date ) {
				$c->where( 'start', '<=', $start_date );
				$c->where( 'end', '>=', $start_date );
			}
		);
		$holiday = $holiday->orWhere(
			function ( $c ) use ( $end_date ) {
				$c->where( 'start', '<=', $end_date );
				$c->where( 'end', '>=', $end_date );
			}
		);
		$holiday = $holiday->orWhere(
			function ( $c ) use ( $start_date, $end_date ) {
				$c->where( 'start', '>=', $start_date );
				$c->where( 'start', '<=', $end_date );
			}
		);
		$holiday = $holiday->orWhere(
			function ( $c ) use ( $start_date, $end_date ) {
				$c->where( 'end', '>=', $start_date );
				$c->where( 'end', '<=', $end_date );
			}
		);

		$holidays        = $holiday->get()->toArray();
		$match_holidays  = [];
		$filter_holidays = apply_filters( 'filter_holidays', [], $start_date, $end_date );

		// --- Weekend background blocks (when no custom holiday filter is active) ---
		if ( empty( $filter_holidays ) ) {
			$weekends  = [];
			$work_days = erp_hr_get_work_days();

			array_walk(
				$work_days,
				function ( $value, $key ) use ( &$weekends ) {
					if ( 0 === (int) $value ) {
						$weekends[] = $key;
					}
				}
			);

			$dates = new \DatePeriod(
				new \DateTime( $start_date ),
				new \DateInterval( 'P1D' ),
				new \DateTime( $end_date )
			);

			foreach ( $dates as $index => $date ) {
				$weekday = strtolower( $date->format( 'D' ) );
				if ( \in_array( $weekday, $weekends, true ) ) {
					$match_holidays[] = [
						'title'      => __( 'Weekly Holiday', 'erp' ),
						'start'      => $date->format( 'Y-m-d' ),
						'end'        => $date->format( 'Y-m-d' ),
						'id'         => $index,
						'background' => true,
					];
				}
			}
		}

		$holidays = array_merge( $holidays, (array) $filter_holidays, $match_holidays );

		// --- Build the merged event list ---
		$events = [];

		foreach ( $leave_requests as $leave_request ) {
			if ( 3 == $leave_request->status ) {
				continue;
			}

			$label = (string) $leave_request->policy_name;

			if ( 2 == $leave_request->status ) {
				$label .= sprintf( ' ( %s ) ', __( 'Pending', 'erp' ) );
			}

			if ( isset( $leave_request->day_status_id ) && 1 != $leave_request->day_status_id ) {
				$label .= '(' . erp_hr_leave_request_get_day_statuses( $leave_request->day_status_id ) . ')';
			}

			$events[] = [
				'id'      => (int) $leave_request->id,
				'type'    => 'leave',
				'title'   => $label,
				'start'   => gmdate( 'Y-m-d', (int) $leave_request->start_date ),
				'end'     => gmdate( 'Y-m-d', (int) $leave_request->end_date ),
				'color'   => $this->cast_string_or_null( $leave_request->color ?? '' ) ?? '',
				'reason'  => (string) ( $leave_request->reason ?? '' ),
				'user_id' => $this->cast_int_or_null( $leave_request->user_id ?? null ),
				'status'  => (int) $leave_request->status,
			];
		}

		foreach ( erp_array_to_object( $holidays ) as $h ) {
			$is_weekend = isset( $h->background ) && $h->background;

			$events[] = [
				'id'         => (int) ( $h->id ?? 0 ),
				'type'       => $is_weekend ? 'weekend' : 'holiday',
				'title'      => (string) ( $h->title ?? '' ),
				'start'      => $this->cast_date_iso( $h->start ?? null ),
				'end'        => $this->cast_date_iso( $h->end ?? null ),
				'holiday'    => true,
				'background' => (bool) $is_weekend,
				'color'      => $is_weekend ? '#c5bfbf' : '#FF5354',
			];
		}

		return rest_ensure_response( $events );
	}
}
