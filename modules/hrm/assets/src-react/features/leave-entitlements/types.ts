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
	/** Leave taken beyond the entitled balance (0 when none). */
	readonly extra_leave:   number;
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

/** `{ value, label }` option for the employee-type filter (value is a string key). */
export interface StringOption {
	readonly value: string;
	readonly label: string;
}

/** A financial year for the year filter (carries dates for current-FY detection). */
export interface FinancialYearOption {
	readonly id:         number;
	readonly label:      string;
	readonly start_date: string;
	readonly end_date:   string;
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
