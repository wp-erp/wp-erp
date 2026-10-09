/**
 * Selectors for the `erp-hr/employees` store.
 */

import { toCountsKey, toQueryKey } from './query-key';
import type {
	EmployeeCountsQuery,
	EmployeeListError,
	EmployeeListItem,
	EmployeeListMeta,
	EmployeeListQuery,
	EmployeeStatusCounts,
	EmployeesState,
} from './types';

export function getEmployees(
	state: EmployeesState,
	query: EmployeeListQuery
): readonly EmployeeListItem[] {
	const key = toQueryKey( query );
	const ids = state.byQuery[ key ];
	if ( ! ids ) {
		return [];
	}
	const out: EmployeeListItem[] = [];
	for ( const id of ids ) {
		const item = state.byId[ id ];
		if ( item ) {
			out.push( item );
		}
	}
	return out;
}

export function getEmployeeById(
	state: EmployeesState,
	id: number
): EmployeeListItem | null {
	return state.byId[ id ] ?? null;
}

export function getMeta(
	state: EmployeesState,
	query: EmployeeListQuery
): EmployeeListMeta {
	const key = toQueryKey( query );
	return state.metaByQuery[ key ] ?? { total: 0, totalPages: 0 };
}

export function getTotal(
	state: EmployeesState,
	query: EmployeeListQuery
): number {
	return getMeta( state, query ).total;
}

export function getTotalPages(
	state: EmployeesState,
	query: EmployeeListQuery
): number {
	return getMeta( state, query ).totalPages;
}

export function getFilters( state: EmployeesState ): EmployeeListQuery {
	return state.filters;
}

export function getSort( state: EmployeesState ): EmployeesState[ 'sort' ] {
	return state.sort;
}

export function getPagination( state: EmployeesState ): EmployeesState[ 'pagination' ] {
	return state.pagination;
}

export function getSelectedIds( state: EmployeesState ): readonly number[] {
	return state.selectedIds;
}

export function isLoading( state: EmployeesState ): boolean {
	return state.isLoading;
}

export function getError( state: EmployeesState ): EmployeeListError | null {
	return state.lastError;
}

export function getCounts(
	state: EmployeesState,
	query: EmployeeCountsQuery
): EmployeeStatusCounts | null {
	const key = toCountsKey( query );
	return state.countsByKey[ key ] ?? null;
}

/**
 * Build the canonical query for the current store state (filters + sort +
 * pagination). Used by the resolver hook to fetch the right page.
 */
export function getCurrentQuery( state: EmployeesState ): EmployeeListQuery {
	return {
		...state.filters,
		orderby:  state.sort.orderby,
		order:    state.sort.order,
		page:     state.pagination.page,
		per_page: state.pagination.perPage,
	};
}
