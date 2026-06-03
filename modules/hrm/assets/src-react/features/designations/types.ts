/**
 * Designation row shape returned by `GET /erp/v2/designations`.
 */
export interface Designation {
	readonly id:              number;
	readonly title:           string;
	readonly description:     string;
	readonly total_employees: number;
}

/** Flat create/update payload for `POST|PUT /erp/v2/designations`. */
export interface DesignationInput {
	readonly title:        string;
	readonly description?: string;
}
