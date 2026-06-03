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

/**
 * Active employees, shaped as `{ id: user_id, title: full_name }` for the
 * "Reporting To" manager dropdown on the create form. Pulls from the v2 list
 * endpoint (same source as the People table) and caches at module scope.
 */
let managersCache: LookupOption[] | null = null;
let managersInflight: Promise< LookupOption[] > | null = null;

interface RawEmployeeRow {
	user_id?:  unknown;
	id?:       unknown;
	full_name?: unknown;
}

export async function loadManagers(): Promise< LookupOption[] > {
	if ( managersCache ) {
		return managersCache;
	}
	if ( managersInflight ) {
		return managersInflight;
	}
	managersInflight = ( async () => {
		try {
			const raw  = await request< unknown >( '/erp/v2/employees?per_page=100&status=active&orderby=full_name&order=asc' );
			const list = Array.isArray( raw ) ? raw : [];
			const options = list
				.map( ( item ) => {
					const obj   = item as RawEmployeeRow;
					const id    = toInt( obj.user_id ?? obj.id, 0 );
					const title = toStr( obj.full_name ?? '', '' );
					return { id, title };
				} )
				.filter( ( opt ) => opt.id > 0 && opt.title !== '' );
			managersCache = options;
			return options;
		} catch {
			managersCache = [];
			return [];
		} finally {
			managersInflight = null;
		}
	} )();
	return managersInflight;
}
