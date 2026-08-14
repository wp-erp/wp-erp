/**
 * Canonical query-key builder.
 *
 * Sorted JSON of the EmployeeListQuery so identical filters produce identical
 * cache keys regardless of object-property order.
 */

import type { EmployeeCountsQuery, EmployeeListQuery } from './types';

export function toQueryKey( query: EmployeeListQuery ): string {
	const filtered: Record< string, unknown > = {};
	const keys = Object.keys( query ).sort();
	for ( const key of keys ) {
		const value = ( query as Record< string, unknown > )[ key ];
		if ( value === undefined || value === null || value === '' ) {
			continue;
		}
		filtered[ key ] = value;
	}
	return JSON.stringify( filtered );
}

/**
 * Reduce an EmployeeListQuery to the filter dims relevant for counts
 * (search + department + designation + location). Status / sort / pagination
 * do NOT affect the count, so they're stripped to maximize cache reuse.
 */
export function toCountsQuery( query: EmployeeListQuery ): EmployeeCountsQuery {
	const out: Record< string, unknown > = {};
	if ( query.search )         { out.search         = query.search; }
	if ( query.department_id )  { out.department_id  = query.department_id; }
	if ( query.designation_id ) { out.designation_id = query.designation_id; }
	if ( query.location_id )    { out.location_id    = query.location_id; }
	return out as EmployeeCountsQuery;
}

export function toCountsKey( query: EmployeeCountsQuery ): string {
	const filtered: Record< string, unknown > = {};
	const keys = Object.keys( query ).sort();
	for ( const key of keys ) {
		const value = ( query as Record< string, unknown > )[ key ];
		if ( value === undefined || value === null || value === '' ) {
			continue;
		}
		filtered[ key ] = value;
	}
	return JSON.stringify( filtered );
}
