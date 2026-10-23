/**
 * Build the filter list for the Employees table.
 *
 * Free ships only the canonical Status / Department / Designation / Location
 * filter components — they live under `./filters/*` and the resolver in
 * EmployeesFilters wires them directly. This hook surfaces them as the
 * extension contract for pro consumers via
 * `wp.hooks.applyFilters('erp_hr.employees.filters', filters, ctx)`.
 *
 * Free does NOT register default filters here (the Filters component renders
 * them directly). Pro returns the merged array — typically just an append.
 */

import { applyFilters, didFilter } from '@wordpress/hooks';
import { useMemo } from 'react';

import type {
	ColumnContext,
	EmployeeFilter,
	FiltersFilter,
} from '@/stores/employees';

import { EMPLOYEES_HOOKS } from './constants';
import { useColumnContext } from './useColumnContext';

export function useEmployeeFilters(): readonly EmployeeFilter[] {
	const { ctx, can } = useColumnContext();
	const filterVersion = didFilter( EMPLOYEES_HOOKS.FILTERS );

	return useMemo( () => {
		const filter = applyFilters as unknown as (
			name: string,
			value: EmployeeFilter[],
			context: ColumnContext
		) => ReturnType< FiltersFilter >;
		const merged = filter( EMPLOYEES_HOOKS.FILTERS, [], ctx );
		return [ ...merged ]
			.filter( ( f ) => ! f.capability || can( f.capability ) )
			.sort( ( a, b ) => a.priority - b.priority );
	}, [ ctx, filterVersion, can ] );
}
