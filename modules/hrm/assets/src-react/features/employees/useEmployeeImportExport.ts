/**
 * Employee CSV import/export helpers (free parity with the legacy
 * Import/Export buttons on the employee list).
 *
 * Import: parse a CSV client-side, map known columns to the create payload, and
 * POST the rows to `POST /erp/v2/employees/import` (bulk create, per-row error
 * report). Export: fetch the current employee list and stream it to a CSV
 * download — no backend endpoint, matching the legacy client-side export.
 */

import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';

/** Columns accepted on import — keys match the create payload exactly. */
export const IMPORT_COLUMNS: readonly string[] = [
	'first_name',
	'last_name',
	'middle_name',
	'email',
	'employee_id',
	'type',
	'status',
	'hiring_date',
	'end_date',
	'date_of_birth',
	'department',
	'designation',
	'location',
	'reporting_to',
	'pay_rate',
	'pay_type',
	'hiring_source',
	'phone',
	'mobile',
	'gender',
	'marital_status',
];

const IMPORT_COLUMN_SET = new Set( IMPORT_COLUMNS );

export interface ImportRow {
	readonly [ key: string ]: string;
}

export interface ImportResult {
	readonly total:   number;
	readonly created: number;
	readonly failed:  ReadonlyArray< { row: number; email: string; message: string } >;
}

/**
 * Minimal RFC-4180-ish CSV parser: handles quoted fields, escaped quotes
 * (`""`), embedded commas/newlines, and CRLF. Returns a matrix of strings.
 */
export function parseCsv( text: string ): string[][] {
	const rows: string[][] = [];
	let row: string[] = [];
	let field = '';
	let inQuotes = false;

	// Strip a leading BOM if present.
	const input = text.charCodeAt( 0 ) === 0xfeff ? text.slice( 1 ) : text;

	for ( let i = 0; i < input.length; i++ ) {
		const char = input[ i ];

		if ( inQuotes ) {
			if ( char === '"' ) {
				if ( input[ i + 1 ] === '"' ) {
					field += '"';
					i++;
				} else {
					inQuotes = false;
				}
			} else {
				field += char;
			}
			continue;
		}

		if ( char === '"' ) {
			inQuotes = true;
		} else if ( char === ',' ) {
			row.push( field );
			field = '';
		} else if ( char === '\n' || char === '\r' ) {
			if ( char === '\r' && input[ i + 1 ] === '\n' ) {
				i++;
			}
			row.push( field );
			rows.push( row );
			row = [];
			field = '';
		} else {
			field += char;
		}
	}

	// Flush the trailing field/row if the file didn't end with a newline.
	if ( field !== '' || row.length > 0 ) {
		row.push( field );
		rows.push( row );
	}

	return rows;
}

/**
 * Turn a parsed CSV matrix into create-payload rows. The first row is the
 * header; only columns whose header matches a known field are kept. Fully
 * blank rows are dropped.
 */
export function rowsToEmployees( matrix: string[][] ): {
	rows: ImportRow[];
	headers: string[];
	unknownHeaders: string[];
} {
	if ( matrix.length === 0 ) {
		return { rows: [], headers: [], unknownHeaders: [] };
	}

	const header = ( matrix[ 0 ] ?? [] ).map( ( h ) => h.trim() );
	const known = header.filter( ( h ) => IMPORT_COLUMN_SET.has( h ) );
	const unknownHeaders = header.filter( ( h ) => h !== '' && ! IMPORT_COLUMN_SET.has( h ) );

	const rows: ImportRow[] = [];
	for ( let r = 1; r < matrix.length; r++ ) {
		const cells = matrix[ r ] ?? [];
		if ( cells.every( ( c ) => c.trim() === '' ) ) {
			continue;
		}
		const obj: Record< string, string > = {};
		header.forEach( ( key, idx ) => {
			if ( IMPORT_COLUMN_SET.has( key ) ) {
				obj[ key ] = ( cells[ idx ] ?? '' ).trim();
			}
		} );
		rows.push( obj );
	}

	return { rows, headers: known, unknownHeaders };
}

/** POST parsed rows to the bulk-import endpoint. */
export async function importEmployees( rows: readonly ImportRow[] ): Promise< ImportResult > {
	return request< ImportResult >( restPath( 'v2', '/employees/import' ), {
		method: 'POST',
		data:   { employees: rows },
	} );
}

/** A ready-to-fill template: the header row plus one example record. */
export function importTemplateCsv(): string {
	const example: Record< string, string > = {
		first_name:    'Jane',
		last_name:     'Doe',
		email:         'jane.doe@example.com',
		type:          'permanent',
		status:        'active',
		hiring_date:   '2026-01-15',
		date_of_birth: '1990-05-20',
	};
	const header = IMPORT_COLUMNS.join( ',' );
	const sample = IMPORT_COLUMNS.map( ( c ) => csvCell( example[ c ] ?? '' ) ).join( ',' );
	return `${ header }\n${ sample }\n`;
}

interface ExportListItem {
	readonly employee_id?:   string;
	readonly first_name?:    string;
	readonly last_name?:     string;
	readonly email?:         string;
	readonly employee_type?: string | null;
	readonly status?:        string | null;
	readonly hire_date?:     string | null;
	readonly department?:    { name?: string; label?: string } | null;
	readonly designation?:   { name?: string; label?: string } | null;
	readonly location?:      { name?: string; label?: string } | null;
	readonly reporting_to?:  { name?: string; label?: string } | null;
}

function embedName( value: { name?: string; label?: string } | null | undefined ): string {
	return value?.name ?? value?.label ?? '';
}

/**
 * Exportable column. `key` is the CSV header (kept identical to the import
 * column keys so an exported file round-trips back through import), `label` is
 * the human-facing checkbox label, `value` pulls the cell from a list item.
 */
export interface ExportField {
	readonly key:   string;
	readonly label: string;
	readonly value: ( emp: ExportListItem ) => string;
}

export const EXPORT_FIELDS: readonly ExportField[] = [
	{ key: 'employee_id', label: __( 'Employee ID', 'erp' ),  value: ( e ) => e.employee_id ?? '' },
	{ key: 'first_name',  label: __( 'First Name', 'erp' ),   value: ( e ) => e.first_name ?? '' },
	{ key: 'last_name',   label: __( 'Last Name', 'erp' ),    value: ( e ) => e.last_name ?? '' },
	{ key: 'email',       label: __( 'User Email', 'erp' ),   value: ( e ) => e.email ?? '' },
	{ key: 'type',        label: __( 'Type', 'erp' ),         value: ( e ) => e.employee_type ?? '' },
	{ key: 'status',      label: __( 'Status', 'erp' ),       value: ( e ) => e.status ?? '' },
	{ key: 'hiring_date', label: __( 'Hiring Date', 'erp' ),  value: ( e ) => e.hire_date ?? '' },
	{ key: 'department',  label: __( 'Department', 'erp' ),   value: ( e ) => embedName( e.department ) },
	{ key: 'designation', label: __( 'Designation', 'erp' ),  value: ( e ) => embedName( e.designation ) },
	{ key: 'location',    label: __( 'Location', 'erp' ),     value: ( e ) => embedName( e.location ) },
	{ key: 'reporting_to', label: __( 'Reporting To', 'erp' ), value: ( e ) => embedName( e.reporting_to ) },
];

export const EXPORT_FIELD_KEYS: readonly string[] = EXPORT_FIELDS.map( ( f ) => f.key );

/**
 * Fetch the employee list for the given status and build a CSV string. Pulls a
 * single large page — adequate for the free list sizes; pro can override.
 *
 * `fieldKeys` restricts the output to the selected columns (in canonical order);
 * when omitted or empty, every field is exported.
 */
export async function buildEmployeesCsv( status: string, fieldKeys?: readonly string[] ): Promise< string > {
	const wanted = fieldKeys && fieldKeys.length > 0 ? new Set( fieldKeys ) : null;
	const fields = wanted ? EXPORT_FIELDS.filter( ( f ) => wanted.has( f.key ) ) : EXPORT_FIELDS;

	// The list endpoint caps `per_page` at 100 (anything larger fails REST
	// validation with a 400), so walk the pages until a short page signals the
	// end. Bounded at 100 pages (10k employees) as a runaway guard.
	const PER_PAGE = 100;
	const items: ExportListItem[] = [];
	for ( let page = 1; page <= 100; page++ ) {
		const batch = await request< ExportListItem[] >(
			restPath( 'v2', '/employees', { per_page: PER_PAGE, page, status } )
		);
		if ( ! Array.isArray( batch ) || batch.length === 0 ) {
			break;
		}
		items.push( ...batch );
		if ( batch.length < PER_PAGE ) {
			break;
		}
	}

	const lines = [ fields.map( ( f ) => f.key ).join( ',' ) ];
	for ( const emp of items ) {
		lines.push( fields.map( ( f ) => csvCell( f.value( emp ) ) ).join( ',' ) );
	}

	return lines.join( '\n' ) + '\n';
}

/** Quote a CSV cell when it contains a comma, quote, or newline. */
function csvCell( value: string ): string {
	if ( /[",\n\r]/.test( value ) ) {
		return `"${ value.replace( /"/g, '""' ) }"`;
	}
	return value;
}

/** Trigger a browser download of `content` as `filename`. */
export function downloadCsv( filename: string, content: string ): void {
	const blob = new Blob( [ content ], { type: 'text/csv;charset=utf-8;' } );
	const url  = URL.createObjectURL( blob );
	const link = document.createElement( 'a' );
	link.href = url;
	link.download = filename;
	document.body.appendChild( link );
	link.click();
	document.body.removeChild( link );
	URL.revokeObjectURL( url );
}
