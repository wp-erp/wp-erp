/**
 * Leave-request shapes for the `erp/v2/leave-requests` endpoints.
 *
 * Status codes (legacy `last_status`): 1 = approved, 2 = pending, 3 = rejected.
 */
export type LeaveRequestStatus = 1 | 2 | 3;

/** An uploaded supporting document on a leave request. */
export interface LeaveRequestAttachment {
	readonly id:       number;
	readonly url:      string;
	readonly filename: string;
}

/**
 * A pro-appended dropdown action for a leave-request row (Advanced Leave
 * multilevel: "Forward"). Supplied via the `erp_hr.leave.request_row_actions`
 * filter; the free table renders each as a dropdown item.
 */
export interface LeaveRequestRowAction {
	readonly id:        string;
	readonly label:     string;
	readonly onSelect:  ( request: LeaveRequest ) => void;
	readonly variant?:  'default' | 'destructive';
}

/** A formatted leave-request list row (`GET /leave-requests`). */
export interface LeaveRequest {
	readonly id:            number;
	readonly user_id:       number | null;
	readonly name:          string;
	readonly avatar:        string | null;
	readonly leave_id:      number | null;
	readonly policy_name:   string;
	readonly start_date:    string | null;
	readonly end_date:      string | null;
	readonly days:          number;
	readonly available:     number;
	readonly extra_leaves:  number;
	readonly spent:         number;
	readonly status:        number;
	readonly status_label:  string;
	readonly reason:        string;
	readonly message:       string;
	readonly color:         string;
	readonly f_year:        number | null;
	readonly created_at:    string | null;
	readonly approved_by:   string;
	readonly approved_at:   string | null;
	readonly approver_note: string;
	readonly attachments:   readonly LeaveRequestAttachment[];
}
