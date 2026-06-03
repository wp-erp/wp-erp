/**
 * Leave-request shapes for the `erp/v2/leave-requests` endpoints.
 *
 * Status codes (legacy `last_status`): 1 = approved, 2 = pending, 3 = rejected.
 */
export type LeaveRequestStatus = 1 | 2 | 3;

/** A formatted leave-request list row (`GET /leave-requests`). */
export interface LeaveRequest {
	readonly id:           number;
	readonly user_id:      number | null;
	readonly name:         string;
	readonly leave_id:     number | null;
	readonly policy_name:  string;
	readonly start_date:   string | null;
	readonly end_date:     string | null;
	readonly days:         number;
	readonly available:    number;
	readonly spent:        number;
	readonly status:       number;
	readonly status_label: string;
	readonly reason:       string;
	readonly message:      string;
	readonly color:        string;
	readonly f_year:       number | null;
	readonly created_at:   string | null;
}
