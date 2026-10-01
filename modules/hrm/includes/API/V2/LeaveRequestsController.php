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

class LeaveRequestsController extends RestController {

	/**
	 * @var string
	 */
	protected $rest_base = 'leave-requests';

	/**
	 * Latest approval-status row per request id, filled by `prime_rows()`.
	 *
	 * @var array<int,object|null>
	 */
	private $latest_approvals = [];

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

		// Column sort (F18): whitelist against the same keys the model maps. Any
		// other value falls through to `created_at` in the model layer.
		$allowed_orderby = [ 'created_at', 'start_date', 'end_date', 'name', 'days', 'policy', 'last_status', 'available' ];
		$orderby         = sanitize_key( (string) ( $request['orderby'] ?? 'created_at' ) );
		$orderby         = \in_array( $orderby, $allowed_orderby, true ) ? $orderby : 'created_at';

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
			// Date-range filter (F7): the model only applies the range when BOTH
			// bounds are present, so pass them through as-is (empty string = off).
			'start_date'    => sanitize_text_field( (string) ( $request['start_date'] ?? '' ) ),
			'end_date'      => sanitize_text_field( (string) ( $request['end_date'] ?? '' ) ),
			'orderby'       => $orderby,
			'order'         => strtoupper( (string) ( $request['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC',
		];

		// A department lead without the manage cap only sees the departments they
		// lead, as the legacy list table scopes it.
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$args['lead'] = get_current_user_id();
		}

		$result = erp_hr_get_leave_requests( $args );
		$rows   = isset( $result['data'] ) ? (array) $result['data'] : [];
		$total  = isset( $result['total'] ) ? (int) $result['total'] : \count( $rows );

		$this->prime_rows( $rows );

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

		// Only HR may moderate their own request; a department lead cannot.
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$leave_request = \WeDevs\ERP\HRM\Models\LeaveRequest::find( $request_id );

			if ( $leave_request && (int) $leave_request->user_id === get_current_user_id() ) {
				return new \WP_Error(
					'rest_leave_request_own',
					1 === $status
						? __( 'You cannot approve your own leave request.', 'erp' )
						: __( 'You cannot reject your own leave request.', 'erp' ),
					[ 'status' => 403 ]
				);
			}
		}

		$update = erp_hr_leave_request_update_status( $request_id, $status, sanitize_text_field( $reason ) );

		if ( is_wp_error( $update ) ) {
			return new \WP_Error(
				$update->get_error_code() ?: 'rest_leave_request_error',
				$update->get_error_message(),
				[ 'status' => $this->error_status( $update->get_error_code() ) ]
			);
		}

		// An extension handled the request itself (Advanced Leave multi-level
		// approval records a lead's response and leaves the request open).
		if ( is_array( $update ) ) {
			return rest_ensure_response(
				[
					'id'      => $request_id,
					'status'  => isset( $update['status'] ) ? (int) $update['status'] : $status,
					'message' => isset( $update['message'] ) ? (string) $update['message'] : '',
				]
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
				[ 'status' => $this->error_status( $result->get_error_code() ) ]
			);
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	/**
	 * HTTP status for an error code from the leave model functions.
	 *
	 * @param string|int $code Error code.
	 *
	 * @return int
	 */
	private function error_status( $code ): int {
		switch ( (string) $code ) {
			case 'no-permission':
				return 403;
			case 'no-request-found':
			case 'invalid_leave_id':
				return 404;
			case 'no-leave-status':
			case 'leave-status-changed':
				return 409;
			default:
				return 400;
		}
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

		// Same scope as the list: a lead without the manage cap counts only the
		// departments they lead.
		$user_ids = null;
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$user_ids = array_map( 'absint', (array) erp_hr_get_dept_lead_subordinate_employees( get_current_user_id() ) );
		}

		// Calendar-year scope: count rows exactly as the list filters them.
		if ( $year ) {
			$counts = $this->scoped_counts( $user_ids, $year, 0 );

			return rest_ensure_response(
				[
					'all'       => $counts['all'],
					'approved'  => $counts['1'],
					'pending'   => $counts['2'],
					'rejected'  => $counts['3'],
					'forwarded' => $counts['4'],
					'year'      => $year,
				]
			);
		}

		// No year filter ("All Years") → legacy financial-year view counts.
		$f_year = (int) ( $request['f_year'] ?? 0 );

		if ( ! $f_year && function_exists( 'erp_hr_get_financial_year_from_date' ) ) {
			$fy     = erp_hr_get_financial_year_from_date();
			$f_year = $fy ? (int) $fy->id : 0;
		}

		if ( null !== $user_ids ) {
			$counts = $this->scoped_counts( $user_ids, 0, $f_year );

			return rest_ensure_response(
				[
					'all'       => $counts['all'],
					'approved'  => $counts['1'],
					'pending'   => $counts['2'],
					'rejected'  => $counts['3'],
					'forwarded' => $counts['4'],
					'f_year'    => $f_year,
				]
			);
		}

		$counts = (array) erp_hr_leave_get_requests_count( $f_year );

		$pick = static function ( $key ) use ( $counts ): int {
			return isset( $counts[ $key ]['count'] ) ? (int) $counts[ $key ]['count'] : 0;
		};

		return rest_ensure_response(
			[
				'all'       => $pick( 'all' ),
				'approved'  => $pick( '1' ),
				'pending'   => $pick( '2' ),
				'rejected'  => $pick( '3' ),
				'forwarded' => $pick( '4' ),
				'f_year'    => $f_year,
			]
		);
	}

	/**
	 * Per-status leave-request counts, scoped exactly like
	 * `erp_hr_get_leave_requests()` (same join + date bucketing) so the tab
	 * counts agree with the list rows.
	 *
	 * @param int[]|null $user_ids Restrict to these employees (a lead's
	 *                             departments); null for no restriction.
	 * @param int        $year     Calendar year (e.g. 2025), 0 for none.
	 * @param int        $f_year   Financial year id, used when no calendar year.
	 *
	 * @return array{all:int,1:int,2:int,3:int,4:int}
	 */
	private function scoped_counts( ?array $user_ids, int $year, int $f_year ): array {
		global $wpdb;

		$where = " WHERE entl.trn_type = 'leave_policies'";

		if ( $year ) {
			$from   = ( new \DateTime( $year . '-01-01 00:00:00', wp_timezone() ) )->getTimestamp();
			$to     = ( new \DateTime( $year . '-12-31 23:59:59', wp_timezone() ) )->getTimestamp();
			$where .= $wpdb->prepare( ' AND request.start_date >= %d AND request.end_date <= %d', $from, $to );
		} else {
			$where .= $wpdb->prepare( ' AND entl.f_year = %d', $f_year );
		}

		if ( null !== $user_ids ) {
			$where .= erp_hr_leave_request_user_in_clause( $user_ids );
		}

		$rows = $wpdb->get_results(
			"SELECT request.last_status AS status, COUNT( request.id ) AS total
			 FROM {$wpdb->prefix}erp_hr_leave_requests AS request
			 LEFT JOIN {$wpdb->prefix}erp_hr_leave_entitlements AS entl ON request.leave_entitlement_id = entl.id
			 {$where}
			 GROUP BY request.last_status", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where is built from prepared fragments.
			ARRAY_A
		);

		// `4` is Advanced Leave's Forwarded state. It has to be a bucket of its own:
		// leaving it out made `all` disagree with the sum of the tabs, and the row
		// belonged to no tab at all.
		$counts = [ 'all' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0 ];

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
	 * Human label for a day-status id, or '' for an ordinary full day.
	 *
	 * Full Day is the overwhelming majority, and labelling every row "Full Day"
	 * would bury the halves — legacy printed the label only when the status was
	 * not 1, and this returns '' in that case so the client can do the same.
	 *
	 * @param int $status_id Day status id.
	 *
	 * @return string
	 */
	private function day_status_label( int $status_id ): string {
		if ( $status_id <= 1 || ! function_exists( 'erp_hr_leave_request_get_day_statuses' ) ) {
			return '';
		}

		return (string) erp_hr_leave_request_get_day_statuses( (string) $status_id );
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
		$id      = (int) ( $row->id ?? 0 );

		// Approver / rejecter meta (F8) — latest row from the approval-status log,
		// exactly as the legacy list-table `approved_by` column resolves it.
		$approval = $this->approval_meta( $id );

		return [
			'id'           => $id,
			'user_id'      => $user_id,
			'name'         => (string) ( $row->name ?? $row->display_name ?? '' ),
			'avatar'       => $avatar,
			'leave_id'     => $this->cast_int_or_null( $row->leave_id ?? null ),
			'policy_name'  => (string) ( $row->policy_name ?? '' ),
			'start_date'   => $this->ts_to_iso( $row->start_date ?? null ),
			'end_date'     => $this->ts_to_iso( $row->end_date ?? null ),
			'days'         => $this->cast_float_or_null( $row->days ?? null ) ?? 0,
			// Half-day period. `erp_hr_get_leave_requests()` already carries
			// `day_status_id` (1 Full Day / 2 Morning / 3 Afternoon) — it was simply
			// never forwarded, so the list could not tell a morning half-day from a
			// full day. The label comes from the same filtered map the legacy list
			// printed, so a pro override of `erp_hr_leave_day_statuses` still wins.
			'day_status_id'    => $this->cast_int_or_null( $row->day_status_id ?? null ) ?? 1,
			'day_status_label' => $this->day_status_label( (int) ( $row->day_status_id ?? 1 ) ),
			'available'    => $this->cast_float_or_null( $row->available ?? null ) ?? 0,
			// Extra (over-drawn) leave days (F18) — drives the red "Extra Leave"
			// indicator; mirrors the legacy `available` column's `extra_leaves`.
			'extra_leaves' => $this->cast_float_or_null( $row->extra_leaves ?? null ) ?? 0,
			'spent'        => $this->cast_float_or_null( $row->spent ?? null ) ?? 0,
			'status'       => (int) ( $row->status ?? 0 ),
			'status_label' => $this->status_label( (int) ( $row->status ?? 0 ) ),
			'reason'       => (string) ( $row->reason ?? '' ),
			'message'      => (string) ( $row->message ?? '' ),
			'color'        => (string) ( $row->color ?? '' ),
			'f_year'       => $this->cast_int_or_null( $row->f_year ?? null ),
			// `erp_hr_leave_requests.created_at` is a unix timestamp, not a date
			// string — `cast_date_iso()` bails on it (strtotime fails) and the
			// "Requested On" column rendered an em dash. `ts_to_iso()` handles both.
			'created_at'   => $this->ts_to_iso( $row->created_at ?? null ),
			// F8 — moderator name + when + note (empty when still pending).
			'approved_by'  => $approval['name'],
			'approved_at'  => $approval['date'],
			'approver_note'=> $approval['message'],
			// F3 — uploaded supporting documents as viewable/downloadable links.
			'attachments'  => $this->request_attachments( (int) ( $user_id ?? 0 ), $id ),
		];
	}

	/**
	 * Uploaded leave attachments for a request (F3).
	 *
	 * Legacy list-table `reason` column stores each uploaded file's attachment ID
	 * under user-meta `leave_document_{request_id}` (one meta row per file) and
	 * renders `wp_get_attachment_url()` links. We surface the same, resolved to
	 * `{id,url,filename}` so React can render view/download links.
	 *
	 * @param int $user_id    Requesting employee's WP user ID.
	 * @param int $request_id Leave request ID.
	 *
	 * @return array<int,array{id:int,url:string,filename:string}>
	 */
	private function request_attachments( int $user_id, int $request_id ): array {
		if ( ! $user_id || ! $request_id ) {
			return [];
		}

		$attachment_ids = get_user_meta( $user_id, 'leave_document_' . $request_id );
		$files          = [];

		foreach ( (array) $attachment_ids as $attachment_id ) {
			$attachment_id = (int) $attachment_id;
			$url           = $attachment_id ? wp_get_attachment_url( $attachment_id ) : false;

			if ( ! $url ) {
				continue;
			}

			$files[] = [
				'id'       => $attachment_id,
				'url'      => $url,
				'filename' => wp_basename( $url ),
			];
		}

		return $files;
	}

	/**
	 * Load what the list rows need per row (latest approval entry, users, user
	 * meta and employee photos) in a few batched queries instead of per row.
	 *
	 * @param array $rows Rows from `erp_hr_get_leave_requests()`.
	 *
	 * @return void
	 */
	private function prime_rows( array $rows ): void {
		global $wpdb;

		$request_ids = [];
		$user_ids    = [];

		foreach ( $rows as $row ) {
			$request_ids[] = (int) ( $row->id ?? 0 );
			$user_ids[]    = (int) ( $row->user_id ?? 0 );
		}

		$request_ids = array_values( array_unique( array_filter( $request_ids ) ) );
		$user_ids    = array_values( array_unique( array_filter( $user_ids ) ) );

		if ( $request_ids ) {
			$placeholders = implode( ', ', array_fill( 0, \count( $request_ids ), '%d' ) );

			$latest = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT las.leave_request_id, las.message, las.approved_by, las.created_at
					 FROM {$wpdb->prefix}erp_hr_leave_approval_status AS las
					 INNER JOIN (
						SELECT MAX( id ) AS id FROM {$wpdb->prefix}erp_hr_leave_approval_status
						WHERE leave_request_id IN ( {$placeholders} )
						GROUP BY leave_request_id
					 ) AS latest ON latest.id = las.id", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$request_ids
				)
			);

			foreach ( $request_ids as $request_id ) {
				$this->latest_approvals[ $request_id ] = null;
			}

			foreach ( (array) $latest as $row ) {
				$this->latest_approvals[ (int) $row->leave_request_id ] = $row;

				if ( null !== $row->approved_by ) {
					$user_ids[] = (int) $row->approved_by;
				}
			}
		}

		$user_ids = array_values( array_unique( array_filter( $user_ids ) ) );

		if ( ! $user_ids ) {
			return;
		}

		cache_users( $user_ids );

		$photo_ids = [];
		foreach ( $user_ids as $user_id ) {
			$photo_id = (int) get_user_meta( $user_id, 'photo_id', true );

			if ( $photo_id ) {
				$photo_ids[] = $photo_id;
			}
		}

		if ( $photo_ids ) {
			_prime_post_caches( $photo_ids, false, true );
		}
	}

	/**
	 * Latest approve/reject entry for a request (F8).
	 *
	 * Mirrors the legacy `approved_by` column: newest row from
	 * `erp_hr_leave_approval_status`, with the moderator's display name and the
	 * (unix-timestamp) `created_at` resolved to an ISO date.
	 *
	 * @param int $request_id Leave request ID.
	 *
	 * @return array{name:string,date:string|null,message:string}
	 */
	private function approval_meta( int $request_id ): array {
		global $wpdb;

		$empty = [ 'name' => '', 'date' => null, 'message' => '' ];

		if ( ! $request_id ) {
			return $empty;
		}

		if ( array_key_exists( $request_id, $this->latest_approvals ) ) {
			$row = $this->latest_approvals[ $request_id ];
		} else {
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT message, approved_by, created_at
					 FROM {$wpdb->prefix}erp_hr_leave_approval_status
					 WHERE leave_request_id = %d
					 ORDER BY id DESC
					 LIMIT 1",
					$request_id
				)
			);
		}

		if ( empty( $row ) || null === $row->approved_by ) {
			return $empty;
		}

		$user = get_user_by( 'id', (int) $row->approved_by );

		if ( ! $user instanceof \WP_User ) {
			return $empty;
		}

		return [
			'name'    => $user->display_name,
			'date'    => $this->ts_to_iso( $row->created_at ),
			'message' => (string) ( $row->message ?? '' ),
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
			// Leave start/end are calendar dates stored as the instant of
			// *site-local* midnight. Format the calendar day back in the site
			// timezone (`wp_date`, not `gmdate`) — UTC here renders the prior
			// day for positive-offset sites (the "leave one day less" bug).
			return wp_date( 'Y-m-d', (int) $value );
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
				return __( 'Pending', 'erp' );
			default:
				// A pro module may register further statuses on
				// `erp_hr_leave_approval_statuses` — Advanced Leave's multilevel
				// approval adds 4 = Forwarded. Ask the filtered map rather than
				// hardcoding them here, and only fall back to Pending when the
				// code is genuinely unknown (the map returns the whole array for
				// a miss, so guard on the type).
				$label = erp_hr_leave_request_get_statuses( $status );

				return is_string( $label ) && '' !== $label ? $label : __( 'Pending', 'erp' );
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

		// Column sort (F18) — whitelist re-checked in get_items() / the model layer.
		$params['orderby']        = [ 'type' => 'string', 'default' => 'created_at', 'sanitize_callback' => 'sanitize_key' ];

		// Date-range filter (F7) — applied only when BOTH bounds are supplied.
		$params['start_date']     = [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ];
		$params['end_date']       = [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ];

		return $params;
	}
}
