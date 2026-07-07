/**
 * Holiday row shape returned by `GET /erp/v2/holidays`.
 */
export interface Holiday {
	readonly id:          number;
	readonly title:       string;
	readonly start:       string | null;
	readonly end:         string | null;
	readonly description: string;
	readonly duration:    number;
	readonly range:       boolean;
}

/** Flat create/update payload for `POST|PUT /erp/v2/holidays`. */
export interface HolidayInput {
	readonly title:        string;
	readonly start:        string;
	readonly end?:         string;
	readonly range?:       boolean;
	readonly description?: string;
}

/** A preview row returned by `POST /erp/v2/holidays/parse`. */
export interface HolidayPreviewRow {
	readonly title:       string;
	readonly start:       string;
	readonly end:         string;
	readonly description: string;
	readonly duration?:   string;
}

/** Result of `POST /erp/v2/holidays/import`. */
export interface HolidayImportResult {
	readonly imported: number;
	readonly failed:   readonly number[];
	readonly total:    number;
}
