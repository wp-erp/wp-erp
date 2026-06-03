/**
 * Leave-type row shape returned by `GET /erp/v2/leave-types`.
 *
 * A "leave type" is a row in `erp_hr_leaves` (the `Models\Leave` model).
 */
export interface LeaveType {
	readonly id:          number;
	readonly name:        string;
	readonly description: string;
}

/** Flat create/update payload for `POST|PUT /erp/v2/leave-types`. */
export interface LeaveTypeInput {
	readonly name:         string;
	readonly description?: string;
}
