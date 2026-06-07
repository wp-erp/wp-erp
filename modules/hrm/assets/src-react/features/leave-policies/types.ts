/**
 * Leave-policy shapes for the `erp/v2/leave-policies` endpoints.
 */

/** A single option in a form dropdown. */
export interface PolicyOption {
	readonly value: string;
	readonly label: string;
}

/** A named lookup entity (leave type / financial year / department / designation). */
export interface PolicyLookup {
	readonly id:    number;
	readonly label: string;
}

/** Dropdown data for the create/edit form (`GET /leave-policies/form-options`). */
export interface PolicyFormOptions {
	readonly leaveTypes:      readonly PolicyLookup[];
	readonly financialYears:  readonly PolicyLookup[];
	readonly currentFYear:    number;
	readonly departments:     readonly PolicyLookup[];
	readonly designations:    readonly PolicyLookup[];
	readonly employeeTypes:   readonly PolicyOption[];
	readonly genders:         readonly PolicyOption[];
	readonly maritalStatuses: readonly PolicyOption[];
}

/** A formatted list row (`GET /leave-policies`) — labels already resolved. */
export interface LeavePolicyListRow {
	readonly id:             number;
	readonly leave_id:       number | null;
	readonly name:           string;
	readonly description:    string;
	readonly days:           number;
	readonly color:          string;
	readonly department_id:  number | null;
	readonly department:     string;
	readonly designation_id: number | null;
	readonly designation:    string;
	readonly location:       string;
	readonly f_year:         string;
	readonly gender:         string;
	readonly marital:        string;
	readonly employee_type:  string;
}

/** A single policy (`GET /leave-policies/{id}`) — raw IDs for editing. */
export interface LeavePolicy {
	readonly id:                  number;
	readonly leave_id:            number | null;
	readonly name:                string;
	readonly description:         string;
	readonly days:                number;
	readonly color:               string;
	readonly employee_type:       string;
	readonly department_id:       string;
	readonly designation_id:      string;
	readonly location_id:         string;
	readonly gender:              string;
	readonly marital:             string;
	readonly f_year:              number | null;
	readonly applicable_from:     number;
	readonly apply_for_new_users: boolean;
}

/** Flat create/update payload for `POST|PUT /erp/v2/leave-policies`. */
export interface LeavePolicyInput {
	readonly leave_id:             number;
	readonly days:                 number;
	readonly color:                string;
	readonly description?:         string;
	readonly f_year:               number;
	readonly employee_type?:       string;
	readonly department_id?:       string;
	readonly designation_id?:      string;
	readonly gender?:              string;
	readonly marital?:             string;
	readonly applicable_from?:     number;
	readonly apply_for_new_users?: boolean;
	/**
	 * Pro-injected extra fields (Advanced Leave: half-day, accrual, carry-forward,
	 * segregation). The v2 controller bridges these onto `$_POST` so the legacy
	 * `erp_hr_leave_insert_policy_extra` filter persists them. Nested values
	 * (e.g. `segre`) keep their sub-object shape.
	 */
	readonly extra?: Record< string, unknown >;
}
