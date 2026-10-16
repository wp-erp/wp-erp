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
 * - Pending counts go through the `erp_hr_employee_pending_request_count` filter
 *   (the one `erp_hr_get_employee_pending_requests_count()` applies, which the
 *   pro Asset + Reimbursement modules populate); their sum is the nav badge,
 *   mirroring the legacy badge.
 * - Non-managers get Leave only, scoped to the departments they lead or to
 *   their own requests.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class RequestsController extends RestController {

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

		// Org-wide numbers (and the pro modules' totals, which are org-wide) are
		// for managers. A department lead counts the departments they lead, and
		// anyone else only their own leave.
		$is_manager = current_user_can( 'erp_leave_manage' ) || current_user_can( 'erp_hr_manager' );
		$user_ids   = null;

		if ( ! $is_manager ) {
			$user_ids = erp_hr_is_current_user_dept_lead()
				? (array) erp_hr_get_dept_lead_subordinate_employees( get_current_user_id() )
				: [ get_current_user_id() ];
		}

		$leave = $this->leave_counts( $user_ids );

		// Per-type pending counts (free Leave + pro Asset/Reimbursement via filter).
		// Same filter `erp_hr_get_employee_pending_requests_count()` applies, fed
		// with a counted Leave figure instead of a fully hydrated request list.
		$pending = [ 'leave' => $leave['pending'] ];
		if ( $is_manager ) {
			$pending = (array) apply_filters( 'erp_hr_employee_pending_request_count', $pending );
		}
		$pending = array_map( 'intval', $pending );

		// Nav badge = total pending across every type (mirrors the legacy badge).
		$pending_total = array_sum( $pending );

		// The reimbursement module is inconsistent with itself: its pending count
		// is keyed `reimburse`, its total is keyed `reimbursement`, and the React
		// tab it registers has the id `reimburse`. The tab strip keys by tab id
		// against `totals`, so it read an undefined key and rendered "(0)" over
		// two requests that were waiting to be paid.
		//
		// Mirror the two spellings in both branches rather than aliasing one of
		// them, so the badge is right whichever id the module registers and no
		// deep link (`?tab=reimburse`) has to change.
		$pending = $this->mirror_reimbursement_key( $pending );

		// Same for resignation: pro keys its pending count `resigned` (the legacy
		// Vue screen's id) and its total `resignation` (the React tab id), so the
		// tab read no pending count. The total above is summed before this.
		if ( isset( $pending['resigned'] ) && ! isset( $pending['resignation'] ) ) {
			$pending['resignation'] = $pending['resigned'];
		}

		// Per-type TOTALS (all statuses) for the tab badges. Free seeds Leave; pro
		// modules add their own via `erp_hr_request_total_count`.
		$totals = [ 'leave' => $leave['total'] ];

		if ( $is_manager ) {
			/**
			 * Filter per-type request totals for the Requests tab badges.
			 *
			 * Keyed by request-tab id (e.g. `leave`, `asset`, `reimbursement`).
			 *
			 * @param array $totals Map of tab id => total count.
			 */
			$totals = (array) apply_filters( 'erp_hr_request_total_count', $totals );
		}

		$totals = array_map( 'intval', $totals );
		$totals = $this->mirror_reimbursement_key( $totals );

		return rest_ensure_response(
			[
				'totals'        => $totals,
				'pending'       => $pending,
				'pending_total' => (int) $pending_total,
			]
		);
	}

	/**
	 * Leave request total + pending, counted with the same join and
	 * `leave_policies` scope `erp_hr_get_leave_requests()` lists by.
	 *
	 * @param int[]|null $user_ids Restrict to these employees; null for everyone.
	 *
	 * @return array{total:int,pending:int}
	 */
	private function leave_counts( ?array $user_ids ): array {
		global $wpdb;

		$where = " WHERE entl.trn_type = 'leave_policies'";

		if ( null !== $user_ids ) {
			$where .= erp_hr_leave_request_user_in_clause( $user_ids );
		}

		$row = $wpdb->get_row(
			"SELECT COUNT( request.id ) AS total, SUM( request.last_status = 2 ) AS pending
			 FROM {$wpdb->prefix}erp_hr_leave_requests AS request
			 LEFT JOIN {$wpdb->prefix}erp_hr_leave_entitlements AS entl ON request.leave_entitlement_id = entl.id
			 {$where}" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where is built from prepared fragments.
		);

		return [
			'total'   => (int) ( $row->total ?? 0 ),
			'pending' => (int) ( $row->pending ?? 0 ),
		];
	}

	/**
	 * Make `reimburse` and `reimbursement` resolve to the same number, whichever
	 * one a caller happens to hold.
	 *
	 * @param array $counts Count map keyed by request type / tab id.
	 *
	 * @return array
	 */
	private function mirror_reimbursement_key( array $counts ): array {
		if ( isset( $counts['reimburse'] ) && ! isset( $counts['reimbursement'] ) ) {
			$counts['reimbursement'] = (int) $counts['reimburse'];
		}

		if ( isset( $counts['reimbursement'] ) && ! isset( $counts['reimburse'] ) ) {
			$counts['reimburse'] = (int) $counts['reimbursement'];
		}

		return $counts;
	}
}
