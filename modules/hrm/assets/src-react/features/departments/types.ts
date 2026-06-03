/**
 * Department row shape returned by `GET /erp/v2/departments`.
 */
export interface Department {
	readonly id:              number;
	readonly title:           string;
	readonly description:     string;
	readonly lead:            number | null;
	readonly lead_name:       string;
	readonly parent:          number | null;
	readonly parent_title:    string;
	readonly total_employees: number;
}

/** Flat create/update payload for `POST|PUT /erp/v2/departments`. */
export interface DepartmentInput {
	readonly title:        string;
	readonly description?: string;
	readonly lead?:        number;
	readonly parent?:      number;
}
