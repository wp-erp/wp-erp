/**
 * Glue hook — derives the EmployeeListQuery from store state, fires the
 * matching resolver (via `useSelect.getEmployees`), and surfaces rows + meta
 * + status flags for the page.
 */

import { useSelect } from '@wordpress/data';

import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeListError,
	EmployeeListItem,
	EmployeeListQuery,
	EmployeesState,
} from '@/stores/employees';

interface EmployeesStoreSelectors {
	getCurrentQuery: () => EmployeeListQuery;
	getEmployees:    ( query: EmployeeListQuery ) => readonly EmployeeListItem[];
	getTotal:        ( query: EmployeeListQuery ) => number;
	getTotalPages:   ( query: EmployeeListQuery ) => number;
	isLoading:       () => boolean;
	getError:        () => EmployeeListError | null;
	getPagination:   () => EmployeesState[ 'pagination' ];
	getSort:         () => EmployeesState[ 'sort' ];
	getFilters:      () => EmployeeListQuery;
}

export interface EmployeesQueryResult {
	readonly query:      EmployeeListQuery;
	readonly rows:       readonly EmployeeListItem[];
	readonly total:      number;
	readonly totalPages: number;
	readonly isLoading:  boolean;
	readonly error:      EmployeeListError | null;
	readonly page:       number;
	readonly perPage:    number;
}

export function useEmployeesQuery(): EmployeesQueryResult {
	return useSelect( ( select ) => {
		const store = select( employeesStoreName ) as unknown as EmployeesStoreSelectors;
		const query = store.getCurrentQuery();
		const rows  = store.getEmployees( query );
		const pagination = store.getPagination();

		return {
			query,
			rows,
			total:      store.getTotal( query ),
			totalPages: store.getTotalPages( query ),
			isLoading:  store.isLoading(),
			error:      store.getError(),
			page:       pagination.page,
			perPage:    pagination.perPage,
		};
	}, [] );
}
