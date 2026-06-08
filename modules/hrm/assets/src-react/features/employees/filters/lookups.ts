/**
 * Lightweight shared lookup cache for Department / Designation / Location
 * dropdowns in the filters row. Cached at module scope — same lookup is
 * reused across renders + tabs. Pull from v1 endpoints (existing) until the
 * v2 stores ship.
 */

import { request } from '@/shared/utils/apiFetch';
import { toInt, toStr } from '@/shared/utils/coerce';

export interface LookupOption {
	readonly id:    number;
	readonly title: string;
}

type LookupKey = 'departments' | 'designations' | 'locations';

interface RawListItem {
	id?:    unknown;
	title?: unknown;
	name?:  unknown;
}

const ENDPOINTS: Record< LookupKey, string > = {
	departments:  '/erp/v1/hrm/departments?per_page=100',
	designations: '/erp/v1/hrm/designations?per_page=100',
	locations:    '/erp/v1/hrm/company/company-locations?per_page=100',
};

const cache: Partial< Record< LookupKey, LookupOption[] > > = {};
const inflight: Partial< Record< LookupKey, Promise< LookupOption[] > > > = {};

function normalize( raw: unknown ): LookupOption[] {
	const list: unknown[] = Array.isArray( raw )
		? raw
		: Array.isArray( ( raw as { data?: unknown } )?.data )
		? ( raw as { data: unknown[] } ).data
		: [];
	return list
		.map( ( item ) => {
			const obj   = item as RawListItem;
			const id    = toInt( obj.id, 0 );
			const title = toStr( obj.title ?? obj.name ?? '', '' );
			return { id, title };
		} )
		.filter( ( opt ) => opt.id > 0 && opt.title !== '' );
}

export async function loadLookup( key: LookupKey ): Promise< LookupOption[] > {
	if ( cache[ key ] ) {
		return cache[ key ]!;
	}
	const pending = inflight[ key ];
	if ( pending ) {
		return pending;
	}
	const promise = ( async () => {
		try {
			const raw  = await request< unknown >( ENDPOINTS[ key ] );
			const list = normalize( raw );
			cache[ key ] = list;
			return list;
		} catch {
			cache[ key ] = [];
			return [];
		} finally {
			delete inflight[ key ];
		}
	} )();
	inflight[ key ] = promise;
	return promise;
}

export function readLookup( key: LookupKey ): LookupOption[] | null {
	return cache[ key ] ?? null;
}

interface RawEmployeeRow {
	user_id?:     unknown;
	id?:          unknown;
	full_name?:   unknown;
	employee_id?: unknown;
}

/**
 * Server-side employee search for the searchable employee/manager pickers.
 * The `/erp/v2/employees` list endpoint caps `per_page` at 100, so a dropdown
 * that loads once and filters client-side can never reach employees beyond the
 * first page. Instead, query the server with the typed term (`search` → matches
 * the display name) and cap each request at `perPage` rows (20 by default), so
 * any employee is reachable by typing — without ever loading the whole list.
 */
export async function searchEmployees( query = '', perPage = 20 ): Promise< LookupOption[] > {
	const q    = encodeURIComponent( query.trim() );
	const base = `/erp/v2/employees?per_page=${ perPage }&status=active&orderby=full_name&order=asc`;
	const url  = q ? `${ base }&search=${ q }` : base;
	try {
		const raw  = await request< unknown >( url );
		const list = Array.isArray( raw ) ? raw : [];
		return list
			.map( ( item ) => {
				const obj   = item as RawEmployeeRow;
				const id    = toInt( obj.user_id ?? obj.id, 0 );
				const name  = toStr( obj.full_name ?? '', '' );
				const empId = toStr( obj.employee_id ?? '', '' );
				// Label shows the HR Employee ID (not the DB user id) + name.
				const title = empId ? `${ empId } - ${ name }` : name;
				return { id, title };
			} )
			.filter( ( opt ) => opt.id > 0 && opt.title !== '' );
	} catch {
		return [];
	}
}
