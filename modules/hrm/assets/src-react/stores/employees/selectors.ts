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

// The two list selectors build a new array/object on every call, and `useSelect`
// re-renders a consumer whenever a returned value is not `===` the last one. So
// each keeps its last result and returns it while its inputs are the same
// references (the reducer replaces a slice only when it changes). Hand-rolled:
// `createSelector` comes from core's wp.data, which WordPress 5.6 lacks.
const EMPTY: readonly EmployeeListItem[] = [];
let lastRows: { ids: readonly number[]; byId: EmployeesState[ 'byId' ]; rows: readonly EmployeeListItem[] } | null = null;
let lastQuery: {
	filters:    EmployeesState[ 'filters' ];
	sort:       EmployeesState[ 'sort' ];
	pagination: EmployeesState[ 'pagination' ];
	query:      EmployeeListQuery;
} | null = null;

export function getEmployees(
	state: EmployeesState,
	query: EmployeeListQuery
): readonly EmployeeListItem[] {
	const key = toQueryKey( query );
	const ids = state.byQuery[ key ];
	if ( ! ids ) {
		return EMPTY;
	}
	if ( lastRows && lastRows.ids === ids && lastRows.byId === state.byId ) {
		return lastRows.rows;
	}
	const out: EmployeeListItem[] = [];
	for ( const id of ids ) {
		const item = state.byId[ id ];
		if ( item ) {
			out.push( item );
		}
	}
	lastRows = { ids, byId: state.byId, rows: out };
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
	const { filters, sort, pagination } = state;
	if ( lastQuery && lastQuery.filters === filters && lastQuery.sort === sort && lastQuery.pagination === pagination ) {
		return lastQuery.query;
	}
	const query = {
		...filters,
		orderby:  sort.orderby,
		order:    sort.order,
		page:     pagination.page,
		per_page: pagination.perPage,
	};
	lastQuery = { filters, sort, pagination, query };
	return query;
}
