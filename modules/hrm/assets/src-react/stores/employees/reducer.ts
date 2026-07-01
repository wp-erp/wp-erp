/**
 * Reducer for the `erp-hr/employees` store.
 */

import type {
	EmployeeListError,
	EmployeeListItem,
	EmployeeListMeta,
	EmployeeListQuery,
	EmployeeStatusCounts,
	EmployeesState,
} from './types';

export type EmployeesAction =
	| {
			readonly type: 'SET_EMPLOYEES';
			readonly payload: {
				readonly queryKey: string;
				readonly items:    readonly EmployeeListItem[];
				readonly meta:     EmployeeListMeta;
			};
	  }
	| {
			readonly type: 'SET_COUNTS';
			readonly payload: {
				readonly countsKey: string;
				readonly counts:    EmployeeStatusCounts;
			};
	  }
	| { readonly type: 'SET_FILTERS'; readonly payload: EmployeeListQuery }
	| { readonly type: 'SET_SORT'; readonly payload: EmployeesState[ 'sort' ] }
	| { readonly type: 'SET_PAGINATION'; readonly payload: EmployeesState[ 'pagination' ] }
	| { readonly type: 'SET_SELECTED_IDS'; readonly payload: readonly number[] }
	| { readonly type: 'SET_LOADING'; readonly payload: boolean }
	| { readonly type: 'SET_ERROR'; readonly payload: EmployeeListError | null };

export const INITIAL_STATE: EmployeesState = {
	byId:        {},
	byQuery:     {},
	metaByQuery: {},
	filters:     { status: 'all' },
	sort:        { orderby: 'hire_date', order: 'desc' },
	pagination:  { page: 1, perPage: 20 },
	selectedIds: [],
	isLoading:   false,
	lastError:   null,
	countsByKey: {},
};

export default function reducer(
	state: EmployeesState = INITIAL_STATE,
	action: EmployeesAction
): EmployeesState {
	switch ( action.type ) {
		case 'SET_EMPLOYEES': {
			const { queryKey, items, meta } = action.payload;
			const byId = { ...state.byId };
			const order: number[] = [];
			for ( const item of items ) {
				byId[ item.id ] = item;
				order.push( item.id );
			}
			return {
				...state,
				byId,
				byQuery:     { ...state.byQuery, [ queryKey ]: order },
				metaByQuery: { ...state.metaByQuery, [ queryKey ]: meta },
			};
		}

		case 'SET_COUNTS': {
			const { countsKey, counts } = action.payload;
			return {
				...state,
				countsByKey: { ...state.countsByKey, [ countsKey ]: counts },
			};
		}

		case 'SET_FILTERS':
			return { ...state, filters: action.payload };

		case 'SET_SORT':
			return { ...state, sort: action.payload };

		case 'SET_PAGINATION':
			return { ...state, pagination: action.payload };

		case 'SET_SELECTED_IDS':
			return { ...state, selectedIds: action.payload };

		case 'SET_LOADING':
			return { ...state, isLoading: action.payload };

		case 'SET_ERROR':
			return { ...state, lastError: action.payload };

		default:
			return state;
	}
}
