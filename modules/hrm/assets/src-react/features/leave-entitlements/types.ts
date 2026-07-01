/**
 * Leave-entitlement shapes for the `erp/v2/leave-entitlements` endpoints.
 */

/** A joined entitlement list row (`GET /leave-entitlements`). */
export interface Entitlement {
	readonly id:            number;
	readonly user_id:       number | null;
	readonly employee_name: string;
	readonly leave_id:      number | null;
	readonly policy_id:     number | null;
	readonly policy_name:   string;
	readonly days:          number;
	readonly available:     number;
	readonly spent:         number;
	readonly f_year:        number | null;
	readonly from_date:     string | null;
	readonly to_date:       string | null;
	readonly description:   string;
	readonly emp_status:    string;
}

/** `{ value, label }` option for policy / employee selects. */
export interface IdOption {
	readonly value: number;
	readonly label: string;
}

/** Assign payload for `POST /erp/v2/leave-entitlements`. */
export interface EntitlementAssignInput {
	readonly policy_id:        number;
	readonly assignment_to:    'single' | 'all';
	readonly single_employee?: number | undefined;
	readonly comment?:         string | undefined;
}

/** Result of an assign call. */
export interface EntitlementAssignResult {
	readonly affected: number;
	readonly errors:   readonly string[];
}
