<?php
/**
 * WP-ERP HR — `erp/v2/leave-requests` REST controller.
 *
 * Endpoints:
 *   GET    /erp/v2/leave-requests              — paginated leave-request list (status/year/policy filters).
 *   PUT    /erp/v2/leave-requests/{id}/approve — approve a request (optional reason).
 *   PUT    /erp/v2/leave-requests/{id}/reject  — reject a request (optional reason).
 *   DELETE /erp/v2/leave-requests/{id}         — delete a request (cascade).
 *
 * Mirrors the legacy AJAX handlers `AjaxHandler::leave_approve()` (status 1),
 * `leave_reject()` (status 3) and `remove_leave_request()` — same caps
 * (`erp_leave_manage` OR dept-lead for approve/reject; `erp_leave_manage` for
 * delete), the same `erp_hr_leave_request_update_status()` /
 * `erp_hr_delete_leave_request()` model calls (which carry the balance
 * adjustments, status-history rows, e-mail notifications and cascade). List is
 * `erp_hr_get_leave_requests()`. Only the envelope is the v2 contract. `erp/v1`
 * + the AJAX layer stay untouched.
 *
 * Status codes (legacy `last_status`): 1 = approved, 2 = pending, 3 = rejected.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class LeaveRequestsControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'leave-requests';

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
					'permission_callback' => [ $this, 'permission_view' ],
					'args'                => $this->get_collection_params(),
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/counts',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_counts' ],
					'permission_callback' => [ $this, 'permission_view' ],
					'args'                => [
						'year'   => [
							'description'       => __( 'Calendar year; scopes the counts the same way the list does.', 'erp' ),
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
						],
						'f_year' => [
							'description'       => __( 'Financial year ID; used only when no calendar year is given (defaults to the current financial year).', 'erp' ),
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
						],
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/approve',
			[
				'args' => [ 'id' => $this->id_arg() ],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'approve_item' ],
					'permission_callback' => [ $this, 'permission_moderate' ],
					'args'                => [ 'reason' => $this->reason_arg() ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/reject',
			[
				'args' => [ 'id' => $this->id_arg() ],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'reject_item' ],
					'permission_callback' => [ $this, 'permission_moderate' ],
					'args'                => [ 'reason' => $this->reason_arg() ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'args' => [ 'id' => $this->id_arg() ],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'bulk_action' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);
	}

	/**
	 * Viewing + approving/rejecting: managers OR a department lead (the legacy
	 * `leave_approve()`/`leave_reject()` gate).
	 *
	 * @return bool
	 */
	public function permission_moderate(): bool {
		return current_user_can( 'erp_leave_manage' ) || ( function_exists( 'erp_hr_is_current_user_dept_lead' ) && erp_hr_is_current_user_dept_lead() );
	}

	/**
	 * Listing uses the same gate as moderation (managers + dept leads see the
	 * requests queue).
	 *
	 * @return bool
	 */
	public function permission_view(): bool {
		return $this->permission_moderate();
	}

	/**
	 * Deleting requires the manage cap only — same as `remove_leave_request()`.
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return $this->permission_cap( 'erp_leave_manage' );
	}

	/**
	 * GET /erp/v2/leave-requests
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
			'status'        => '' === (string) ( $request['status'] ?? '' ) ? '' : (int) $request['status'],
			'year'          => (int) ( $request['year'] ?? 0 ),
			'f_year'        => (int) ( $request['f_year'] ?? 0 ),
			'policy_id'     => (int) ( $request['policy_id'] ?? 0 ),
			'department_id' => (int) ( $request['department_id'] ?? 0 ),
			'designation_id'=> (int) ( $request['designation_id'] ?? 0 ),
			'type'          => sanitize_text_field( (string) ( $request['type'] ?? '' ) ),
			's'             => sanitize_text_field( (string) ( $request['search'] ?? '' ) ),
			'orderby'       => 'created_at',
			'order'         => strtoupper( (string) ( $request['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC',
		];

		$result = erp_hr_get_leave_requests( $args );
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
	 * PUT /erp/v2/leave-requests/{id}/approve
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function approve_item( $request ) {
		return $this->update_status( (int) $request['id'], 1, (string) ( $request['reason'] ?? '' ) );
	}

	/**
	 * PUT /erp/v2/leave-requests/{id}/reject
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function reject_item( $request ) {
		return $this->update_status( (int) $request['id'], 3, (string) ( $request['reason'] ?? '' ) );
	}

	/**
	 * Shared approve/reject path — mirrors `leave_approve()`/`leave_reject()`.
	 *
	 * @param int    $request_id Leave request ID.
	 * @param int    $status     New status (1 approve, 3 reject).
	 * @param string $reason     Optional moderator comment.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	private function update_status( int $request_id, int $status, string $reason ) {
		if ( ! $request_id ) {
			return new \WP_Error( 'rest_leave_request_bad_request', __( 'Invalid leave request.', 'erp' ), [ 'status' => 400 ] );
		}

		$update = erp_hr_leave_request_update_status( $request_id, $status, sanitize_text_field( $reason ) );

		if ( is_wp_error( $update ) ) {
			return new \WP_Error(
				$update->get_error_code() ?: 'rest_leave_request_error',
				$update->get_error_message(),
				[ 'status' => 400 ]
			);
		}

		return rest_ensure_response(
			[
				'id'     => $request_id,
				'status' => $status,
			]
		);
	}

	/**
	 * DELETE /erp/v2/leave-requests/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = (int) $request['id'];

		$result = erp_hr_delete_leave_request( $id );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code() ?: 'rest_leave_request_error',
				$result->get_error_message(),
				[ 'status' => 400 ]
			);
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	/**
	 * POST /erp/v2/leave-requests/bulk
	 *
	 * Bulk approve / reject / delete — restores the legacy list-table bulk actions
	 * (`FormHandler::leave_request_bulk_action()`). Reuses the same per-item helpers
	 * (`erp_hr_leave_request_update_status()` / `erp_hr_delete_leave_request()`) so
	 * every hook + cache purge fires exactly as the single-item path.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function bulk_action( $request ) {
		$action = sanitize_text_field( (string) ( $request['action'] ?? '' ) );
		$ids    = array_values( array_filter( array_map( 'absint', (array) ( $request['ids'] ?? [] ) ) ) );

		if ( empty( $ids ) || ! \in_array( $action, [ 'approve', 'reject', 'delete' ], true ) ) {
			return new \WP_Error( 'rest_leave_request_bad_request', __( 'Provide a valid action and at least one request.', 'erp' ), [ 'status' => 400 ] );
		}

		$done   = [];
		$failed = [];

		foreach ( $ids as $id ) {
			if ( 'delete' === $action ) {
				$res = erp_hr_delete_leave_request( $id );
			} else {
				$status  = 'approve' === $action ? 1 : 3;
				$comment = 'approve' === $action
					? __( 'Approved from bulk action', 'erp' )
					: __( 'Rejected from bulk action', 'erp' );
				$res = erp_hr_leave_request_update_status( $id, $status, $comment );
			}

			if ( is_wp_error( $res ) ) {
				$failed[] = $id;
			} else {
				$done[] = $id;
			}
		}

		return rest_ensure_response(
			[
				'action' => $action,
				'done'   => $done,
				'failed' => $failed,
			]
		);
	}

	/**
	 * GET /erp/v2/leave-requests/counts
	 *
	 * Per-status request counts for the status tabs. These MUST agree with the
	 * list rows. The list (`erp_hr_get_leave_requests()`) buckets by CALENDAR
	 * year (`year` → `request.start_date >= Jan 1 AND end_date <= Dec 31`), so
	 * when a `year` is supplied the counts are computed with the same
	 * calendar-year scope. With no `year` we fall back to the legacy
	 * financial-year view counts (`erp_hr_leave_get_requests_count()`, current FY
	 * by default) — matching the list's "All Years" default, which is FY-agnostic
	 * but uses the same model layer.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_counts( $request ): WP_REST_Response {
		$year = (int) ( $request['year'] ?? 0 );

		// Calendar-year scope: count rows exactly as the list filters them.
		if ( $year ) {
			$counts = $this->calendar_year_counts( $year );

			return rest_ensure_response(
				[
					'all'      => $counts['all'],
					'approved' => $counts['1'],
					'pending'  => $counts['2'],
					'rejected' => $counts['3'],
					'year'     => $year,
				]
			);
		}

		// No year filter ("All Years") → legacy financial-year view counts.
		$f_year = (int) ( $request['f_year'] ?? 0 );

		if ( ! $f_year && function_exists( 'erp_hr_get_financial_year_from_date' ) ) {
			$fy     = erp_hr_get_financial_year_from_date();
			$f_year = $fy ? (int) $fy->id : 0;
		}

		$counts = (array) erp_hr_leave_get_requests_count( $f_year );

		$pick = static function ( $key ) use ( $counts ): int {
			return isset( $counts[ $key ]['count'] ) ? (int) $counts[ $key ]['count'] : 0;
		};

		return rest_ensure_response(
			[
				'all'      => $pick( 'all' ),
				'approved' => $pick( '1' ),
				'pending'  => $pick( '2' ),
				'rejected' => $pick( '3' ),
				'f_year'   => $f_year,
			]
		);
	}

	/**
	 * Per-status leave-request counts for a single calendar year, scoped exactly
	 * like `erp_hr_get_leave_requests()` (same join + date bucketing) so the tab
	 * counts agree with the list rows.
	 *
	 * @param int $year Calendar year (e.g. 2025).
	 *
	 * @return array{all:int,1:int,2:int,3:int}
	 */
	private function calendar_year_counts( int $year ): array {
		global $wpdb;

		$from = ( new \DateTime( $year . '-01-01 00:00:00', wp_timezone() ) )->getTimestamp();
		$to   = ( new \DateTime( $year . '-12-31 23:59:59', wp_timezone() ) )->getTimestamp();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT request.last_status AS status, COUNT( request.id ) AS total
				 FROM {$wpdb->prefix}erp_hr_leave_requests AS request
				 LEFT JOIN {$wpdb->prefix}erp_hr_leave_entitlements AS entl ON request.leave_entitlement_id = entl.id
				 WHERE entl.trn_type = 'leave_policies'
				   AND request.start_date >= %d
				   AND request.end_date <= %d
				 GROUP BY request.last_status",
				$from,
				$to
			),
			ARRAY_A
		);

		$counts = [ 'all' => 0, '1' => 0, '2' => 0, '3' => 0 ];

		foreach ( (array) $rows as $row ) {
			$status = (string) ( $row['status'] ?? '' );
			$total  = (int) ( $row['total'] ?? 0 );

			if ( isset( $counts[ $status ] ) ) {
				$counts[ $status ] = $total;
			}

			$counts['all'] += $total;
		}

		return $counts;
	}

	/**
	 * Reshape a formatted leave-request row from `erp_hr_get_leave_requests()`.
	 *
	 * @param mixed           $row     stdClass row.
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $row, $request ) {
		unset( $request );

		$row = (object) $row;

		$user_id = $this->cast_int_or_null( $row->user_id ?? null );
		$avatar  = $user_id ? ( ( new \WeDevs\ERP\HRM\Employee( $user_id ) )->get_avatar_url( 40 ) ?: null ) : null;

		return [
			'id'           => (int) ( $row->id ?? 0 ),
			'user_id'      => $user_id,
			'name'         => (string) ( $row->name ?? $row->display_name ?? '' ),
			'avatar'       => $avatar,
			'leave_id'     => $this->cast_int_or_null( $row->leave_id ?? null ),
			'policy_name'  => (string) ( $row->policy_name ?? '' ),
			'start_date'   => $this->ts_to_iso( $row->start_date ?? null ),
			'end_date'     => $this->ts_to_iso( $row->end_date ?? null ),
			'days'         => $this->cast_float_or_null( $row->days ?? null ) ?? 0,
			'available'    => $this->cast_float_or_null( $row->available ?? null ) ?? 0,
			'spent'        => $this->cast_float_or_null( $row->spent ?? null ) ?? 0,
			'status'       => (int) ( $row->status ?? 0 ),
			'status_label' => $this->status_label( (int) ( $row->status ?? 0 ) ),
			'reason'       => (string) ( $row->reason ?? '' ),
			'message'      => (string) ( $row->message ?? '' ),
			'color'        => (string) ( $row->color ?? '' ),
			'f_year'       => $this->cast_int_or_null( $row->f_year ?? null ),
			'created_at'   => $this->cast_date_iso( $row->created_at ?? null ),
		];
	}

	/**
	 * Convert a unix-timestamp (or date string) leave date to an ISO date.
	 *
	 * `erp_hr_leave_requests.start_date`/`end_date` are stored as unix timestamps.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return string|null
	 */
	private function ts_to_iso( $value ): ?string {
		if ( null === $value || '' === $value ) {
			return null;
		}
		if ( is_numeric( $value ) ) {
			return gmdate( 'Y-m-d', (int) $value );
		}
		return $this->cast_date_iso( $value );
	}

	/**
	 * Map a numeric status to its label.
	 *
	 * @param int $status Status code.
	 *
	 * @return string
	 */
	private function status_label( int $status ): string {
		switch ( $status ) {
			case 1:
				return __( 'Approved', 'erp' );
			case 3:
				return __( 'Rejected', 'erp' );
			case 2:
			default:
				return __( 'Pending', 'erp' );
		}
	}

	/**
	 * Shared `id` route arg.
	 *
	 * @return array
	 */
	private function id_arg(): array {
		return [
			'description'       => __( 'Unique leave request ID.', 'erp' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];
	}

	/**
	 * Shared `reason` body arg.
	 *
	 * @return array
	 */
	private function reason_arg(): array {
		return [
			'description'       => __( 'Optional moderator comment shown to the employee.', 'erp' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		];
	}

	/**
	 * Collection params: pagination + filters.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		$params = parent::get_collection_params();

		$params['status']        = [ 'type' => 'integer', 'enum' => [ 1, 2, 3 ], 'sanitize_callback' => 'absint' ];
		$params['year']          = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['f_year']        = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['policy_id']      = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['department_id']  = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['designation_id'] = [ 'type' => 'integer', 'sanitize_callback' => 'absint' ];
		$params['type']           = [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ];
		$params['order']          = [ 'type' => 'string', 'default' => 'desc', 'enum' => [ 'asc', 'desc' ], 'sanitize_callback' => 'sanitize_key' ];

		return $params;
	}
}
