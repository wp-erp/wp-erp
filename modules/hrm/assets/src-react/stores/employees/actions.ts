/**
 * Actions for the `erp-hr/employees` store.
 *
 * Sync setters + the `fetchEmployees` thunk + the mutating thunks
 * (create / update / delete / restore / terminate / reactivate). Every mutation
 * hits a v2 endpoint that delegates to the unchanged v1 model layer, then
 * `invalidate()`s the store so the list + counts refetch.
 */

import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt, toObject, toStr } from '@/shared/utils/coerce';

import { normalizeEmployee } from './normalize';
import { toCountsKey, toQueryKey } from './query-key';
import type { EmployeesAction } from './reducer';
import type {
	EmployeeCountsQuery,
	EmployeeListError,
	EmployeeListItem,
	EmployeeListMeta,
	EmployeeListQuery,
	EmployeeStatusCounts,
	EmployeesState,
	RawEmployeeListItem,
} from './types';

export function setEmployees(
	queryKey: string,
	items: readonly EmployeeListItem[],
	meta: EmployeeListMeta
): EmployeesAction {
	return { type: 'SET_EMPLOYEES', payload: { queryKey, items, meta } };
}

export function setCounts(
	countsKey: string,
	counts: EmployeeStatusCounts
): EmployeesAction {
	return { type: 'SET_COUNTS', payload: { countsKey, counts } };
}

export function setFilters( filters: EmployeeListQuery ): EmployeesAction {
	return { type: 'SET_FILTERS', payload: filters };
}

export function setSort( sort: EmployeesState[ 'sort' ] ): EmployeesAction {
	return { type: 'SET_SORT', payload: sort };
}

export function setPagination( pagination: EmployeesState[ 'pagination' ] ): EmployeesAction {
	return { type: 'SET_PAGINATION', payload: pagination };
}

export function setSelectedIds( ids: readonly number[] ): EmployeesAction {
	return { type: 'SET_SELECTED_IDS', payload: ids };
}

export function setLoading( isLoading: boolean ): EmployeesAction {
	return { type: 'SET_LOADING', payload: isLoading };
}

export function setError( error: EmployeeListError | null ): EmployeesAction {
	return { type: 'SET_ERROR', payload: error };
}

/**
 * Force the list + counts to refetch after a mutation (or an error retry).
 *
 * `getEmployees` / `getCounts` are resolver-backed selectors, and @wordpress/data
 * caches resolution per selector-args — so merely clearing reducer data would
 * NOT trigger a refetch (the resolver is already marked resolved). We instead
 * invalidate the resolver cache, which re-runs the resolver for the currently
 * observed query. Filters / sort / pagination are intentionally left untouched
 * so the user stays on the same tab + page after the mutation; the previously
 * loaded rows stay visible until the fresh data arrives (no empty-state flash).
 */
export const invalidate =
	() =>
	( { dispatch }: ThunkContext ): void => {
		dispatch.invalidateResolutionForStoreSelector( 'getEmployees' );
		dispatch.invalidateResolutionForStoreSelector( 'getCounts' );
	};

/**
 * Thunk context shape provided by @wordpress/data when a thunk action is
 * dispatched. The store-bound `dispatch` exposes every registered action
 * creator with curried args.
 */
interface ThunkContext {
	readonly dispatch: {
		setLoading:    ( isLoading: boolean ) => void;
		setError:      ( error: EmployeeListError | null ) => void;
		setEmployees:  (
			queryKey: string,
			items: readonly EmployeeListItem[],
			meta: EmployeeListMeta
		) => void;
		setCounts:     ( countsKey: string, counts: EmployeeStatusCounts ) => void;
		invalidate:    () => void;
		/** Built-in @wordpress/data resolution meta-action (re-runs a resolver). */
		invalidateResolutionForStoreSelector: ( selectorName: string ) => void;
	};
}

/**
 * Flat create payload accepted by `POST /erp/v2/employees`. Mirrors the v2
 * controller's `get_create_params()`. Only `first_name`, `last_name` and
 * `email` are required; everything else is optional and omitted when blank.
 */
export type EmployeeCreateInput = Record< string, string | number | boolean | Record< string, string > >;

/**
 * Fetch a page of employees from `/erp/v2/employees`. Thunk action — modern
 * @wordpress/data pattern. Auto-fired by the matching resolver; can also be
 * dispatched directly for a forced refresh.
 *
 * Written as a thunk (not a generator) because yielding raw Promises through
 * generator resolvers proved unreliable across @wordpress/data v10 — the
 * resolved value reached `gen.next()` missing properties. `async`/`await`
 * keeps the control flow explicit and the Headers object intact.
 */
/**
 * Fetch per-status counts from `/erp/v2/employees/counts`. Thunk action —
 * dispatched by the matching resolver. The status filter is intentionally
 * stripped so the same counts are reused across tab switches.
 */
export const fetchCounts =
	( query: EmployeeCountsQuery ) =>
	async ( { dispatch }: ThunkContext ): Promise< void > => {
		const countsKey = toCountsKey( query );

		try {
			const raw = await request< unknown >(
				restPath( 'v2', '/employees/counts', query as Record< string, unknown > )
			);
			const obj       = toObject( raw );
			const allCount  = toInt( obj.all, 0 );
			const byStatus  = toObject( obj.by_status );
			const buckets: Record< string, number > = {};
			for ( const key of Object.keys( byStatus ) ) {
				buckets[ key ] = toInt( byStatus[ key ], 0 );
			}
			dispatch.setCounts( countsKey, { all: allCount, by_status: buckets } );
		} catch ( raw ) {
			// Soft-fail — counts are decorative; the list still renders.
			void raw;
		}
	};

export const fetchEmployees =
	( query: EmployeeListQuery ) =>
	async ( { dispatch }: ThunkContext ): Promise< void > => {
		const queryKey = toQueryKey( query );

		dispatch.setLoading( true );
		dispatch.setError( null );

		try {
			const { body, headers } = await requestWithHeaders< RawEmployeeListItem[] >(
				restPath( 'v2', '/employees', query as Record< string, unknown > )
			);

			const items = Array.isArray( body ) ? body.map( normalizeEmployee ) : [];
			const meta: EmployeeListMeta = {
				total:      toInt( headers.get( 'X-WP-Total' ), items.length ),
				totalPages: toInt( headers.get( 'X-WP-TotalPages' ), 1 ),
			};

			dispatch.setEmployees( queryKey, items, meta );
		} catch ( raw ) {
			const error = raw as { code?: string; message?: string };
			dispatch.setError( {
				code:    toStr( error.code, 'erp_hr_employees_failed' ),
				message: toStr( error.message, 'Could not load employees' ),
			} );
		} finally {
			dispatch.setLoading( false );
		}
	};

/**
 * Create an employee via `POST /erp/v2/employees`. The v2 endpoint delegates to
 * the same `Employee::create_employee()` model the legacy admin used, so every
 * server-side hook keeps firing — only the envelope is modern.
 *
 * Resolves with the normalized created employee; rejects with the normalized
 * `{ code, message, status }` ApiError so the form can surface field errors.
 * On success the list cache is invalidated so the table refetches.
 */
export const createEmployee =
	( payload: EmployeeCreateInput ) =>
	async ( { dispatch }: ThunkContext ): Promise< EmployeeListItem > => {
		const raw = await request< RawEmployeeListItem >(
			restPath( 'v2', '/employees' ),
			{ method: 'POST', data: payload }
		);
		const created = normalizeEmployee( raw );
		dispatch.invalidate();
		return created;
	};

/**
 * Fetch a single employee in the flat edit shape from
 * `GET /erp/v2/employees/{userId}`. Used to prefill the Edit form. Returns the
 * raw record as-is (keys already match the form fields) — rejects with the
 * normalized ApiError on failure.
 */
export const fetchEmployeeForEdit =
	( userId: number ) =>
	async (): Promise< Record< string, unknown > > => {
		const raw = await request< Record< string, unknown > >(
			restPath( 'v2', `/employees/${ userId }` )
		);
		return raw ?? {};
	};

/**
 * Update an employee via `PUT /erp/v2/employees/{userId}`. Delegates to the
 * unchanged `Employee::update_employee()` model server-side. Invalidates the
 * list cache on success; rejects with the normalized ApiError on failure.
 */
export const updateEmployee =
	( userId: number, payload: EmployeeCreateInput ) =>
	async ( { dispatch }: ThunkContext ): Promise< void > => {
		await request( restPath( 'v2', `/employees/${ userId }` ), {
			method: 'PUT',
			data: payload,
		} );
		dispatch.invalidate();
	};

/**
 * Required payload for `POST /erp/v2/employees/{userId}/terminate`. Mirrors the
 * v2 controller's `get_terminate_params()` — all four fields are required and
 * the keys/enum values match the legacy `Employee::terminate()` contract.
 */
export interface EmployeeTerminateInput {
	readonly terminate_date:      string;
	readonly termination_type:    string;
	readonly termination_reason:  string;
	readonly eligible_for_rehire: string;
}

/**
 * Soft-delete (trash) or permanently delete an employee via
 * `DELETE /erp/v2/employees/{userId}`. Delegates to the unchanged
 * `erp_employee_delete()` model. Invalidates the list + counts cache on success;
 * rejects with the normalized ApiError on failure.
 */
export const deleteEmployee =
	( userId: number, force = false ) =>
	async ( { dispatch }: ThunkContext ): Promise< void > => {
		await request( restPath( 'v2', `/employees/${ userId }`, { force } ), {
			method: 'DELETE',
		} );
		dispatch.invalidate();
	};

/**
 * Restore a trashed employee via `POST /erp/v2/employees/{userId}/restore`.
 * Invalidates the cache on success; rejects with the normalized ApiError.
 */
export const restoreEmployee =
	( userId: number ) =>
	async ( { dispatch }: ThunkContext ): Promise< void > => {
		await request( restPath( 'v2', `/employees/${ userId }/restore` ), {
			method: 'POST',
		} );
		dispatch.invalidate();
	};

/**
 * Terminate an employee via `POST /erp/v2/employees/{userId}/terminate`.
 * Delegates to the unchanged `Employee::terminate()` model (same validation,
 * status change, meta write and hooks as the legacy admin). Invalidates the
 * cache on success; rejects with the normalized ApiError (the model's
 * required-field / enum messages surface verbatim).
 */
export const terminateEmployee =
	( userId: number, payload: EmployeeTerminateInput ) =>
	async ( { dispatch }: ThunkContext ): Promise< void > => {
		await request( restPath( 'v2', `/employees/${ userId }/terminate` ), {
			method: 'POST',
			data: payload,
		} );
		dispatch.invalidate();
	};

/**
 * Bulk soft-delete (trash) or permanently delete the given employees. Fires the
 * per-employee v2 DELETE in parallel, then invalidates the store ONCE so the
 * list + counts refetch a single time (vs. once per row). Resolves with the
 * count of failures so the caller can surface a partial-failure notice.
 */
export const bulkDeleteEmployees =
	( userIds: readonly number[], force = false ) =>
	async ( { dispatch }: ThunkContext ): Promise< number > => {
		const results = await Promise.allSettled(
			userIds.map( ( id ) =>
				request( restPath( 'v2', `/employees/${ id }`, { force } ), { method: 'DELETE' } )
			)
		);
		dispatch.invalidate();
		return results.filter( ( r ) => r.status === 'rejected' ).length;
	};

/**
 * Bulk restore the given trashed employees. Single invalidate after all settle.
 */
export const bulkRestoreEmployees =
	( userIds: readonly number[] ) =>
	async ( { dispatch }: ThunkContext ): Promise< number > => {
		const results = await Promise.allSettled(
			userIds.map( ( id ) =>
				request( restPath( 'v2', `/employees/${ id }/restore` ), { method: 'POST' } )
			)
		);
		dispatch.invalidate();
		return results.filter( ( r ) => r.status === 'rejected' ).length;
	};

/**
 * Reverse a termination via `POST /erp/v2/employees/{userId}/reactivate` —
 * sets the status back to `active` and clears the termination meta. Invalidates
 * the cache on success; rejects with the normalized ApiError.
 */
export const reactivateEmployee =
	( userId: number ) =>
	async ( { dispatch }: ThunkContext ): Promise< void > => {
		await request( restPath( 'v2', `/employees/${ userId }/reactivate` ), {
			method: 'POST',
		} );
		dispatch.invalidate();
	};
