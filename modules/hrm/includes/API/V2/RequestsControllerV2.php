<?php
/**
 * WP-ERP HR — `erp/v2/requests/counts` REST controller.
 *
 * Powers the People → Requests tab badges (per-type totals: Leave / Asset /
 * Reimbursement / …) and the Requests nav badge (total pending), restoring the
 * counts the legacy unified Requests screen showed.
 *
 * - Totals per type come from the `erp_hr_request_total_count` filter (free seeds
 *   the Leave total; pro modules add their own — asset/reimbursement — via the
 *   same filter, PHP-only, no React build).
 * - Pending counts reuse `erp_hr_get_employee_pending_requests_count()` (which the
 *   pro Asset + Reimbursement modules already populate); their sum is the nav
 *   badge, mirroring the legacy badge.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class RequestsControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'requests';

	/**
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/counts',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_counts' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);
	}

	/**
	 * Any HR-listed user can read the request counts (low-sensitivity totals).
	 *
	 * @return bool
	 */
	public function permission_view(): bool {
		return current_user_can( 'erp_list_employee' ) || current_user_can( 'erp_leave_manage' );
	}

	/**
	 * GET /erp/v2/requests/counts
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_counts( $request ) {
		unset( $request );

		// Per-type pending counts (free Leave + pro Asset/Reimbursement via filter).
		$pending = function_exists( 'erp_hr_get_employee_pending_requests_count' )
			? (array) erp_hr_get_employee_pending_requests_count()
			: [];
		$pending = array_map( 'intval', $pending );

		// Nav badge = total pending across every type (mirrors the legacy badge).
		$pending_total = array_sum( $pending );

		// The reimbursement module keys its pending count as `reimburse`; the React
		// tab id is `reimbursement`. Alias it so the frontend can key by tab id.
		if ( isset( $pending['reimburse'] ) ) {
			$pending['reimbursement'] = $pending['reimburse'];
		}

		// Per-type TOTALS (all statuses) for the tab badges. Free seeds Leave; pro
		// modules add their own via `erp_hr_request_total_count`.
		$leave_total = 0;
		if ( function_exists( 'erp_hr_get_leave_requests' ) ) {
			$res         = erp_hr_get_leave_requests( [ 'number' => -1 ] );
			$leave_total = ( is_array( $res ) && isset( $res['total'] ) ) ? (int) $res['total'] : 0;
		}

		/**
		 * Filter per-type request totals for the Requests tab badges.
		 *
		 * Keyed by request-tab id (e.g. `leave`, `asset`, `reimbursement`).
		 *
		 * @param array $totals Map of tab id => total count.
		 */
		$totals = (array) apply_filters( 'erp_hr_request_total_count', [ 'leave' => $leave_total ] );
		$totals = array_map( 'intval', $totals );

		return rest_ensure_response(
			[
				'totals'        => $totals,
				'pending'       => $pending,
				'pending_total' => (int) $pending_total,
			]
		);
	}
}
