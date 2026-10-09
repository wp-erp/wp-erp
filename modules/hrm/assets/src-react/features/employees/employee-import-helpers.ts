/**
 * Pure shapes/constants for the employee CSV import dialog: the required-column
 * list and the parsed-file descriptor the dialog builds from a CSV and hands to
 * its upload/preview view.
 */

import type { ImportRow } from './useEmployeeImportExport';

export const REQUIRED = [ 'first_name', 'last_name', 'email' ];

export interface Parsed {
	readonly rows:           ImportRow[];
	readonly headers:        string[];
	readonly unknownHeaders: string[];
	readonly missing:        string[];
}
