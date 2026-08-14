/**
 * List + CRUD + bulk-delete hook for Leave Types.
 *
 * Reads + writes `erp/v2/leave-types`, which delegates (server side) to the
 * unchanged v1 model layer (`erp_hr_insert_leave_policy_name()`,
 * `erp_hr_remove_leave_policy_name()`) so the unique-name guard, the
 * "associated with a policy" delete guard and every cache purge keep firing.
 *
 * Leave types are low-cardinality, so the full list is fetched once and
 * searched / paged in the browser (same pattern as the taxonomy entities).
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

import type { LeaveType, LeaveTypeInput } from './types';

export interface UseLeaveTypesResult {
	readonly rows:       readonly LeaveType[];
	readonly total:      number;
	readonly loading:    boolean;
	readonly error:      string | null;
	readonly reload:     () => Promise< void >;
	readonly save:       ( id: number | null, payload: LeaveTypeInput ) => Promise< void >;
	readonly remove:     ( id: number ) => Promise< void >;
	readonly bulkRemove: ( ids: readonly number[] ) => Promise< void >;
}

export function useLeaveTypes(): UseLeaveTypesResult {
	const [ rows, setRows ]       = useState< readonly LeaveType[] >( [] );
	const [ total, setTotal ]     = useState( 0 );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const { body, headers } = await requestWithHeaders< LeaveType[] >(
				restPath( 'v2', '/leave-types', { per_page: 100 } )
			);
			const list = Array.isArray( body ) ? body : [];
			setRows( list );
			setTotal( toInt( headers.get( 'X-WP-Total' ), list.length ) );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load leave types.' );
		} finally {
			setLoading( false );
		}
	}, [] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	const save = useCallback(
		async ( id: number | null, payload: LeaveTypeInput ): Promise< void > => {
			if ( id ) {
				await request( restPath( 'v2', `/leave-types/${ id }` ), { method: 'PUT', data: payload } );
			} else {
				await request( restPath( 'v2', '/leave-types' ), { method: 'POST', data: payload } );
			}
			await reload();
		},
		[ reload ]
	);

	const remove = useCallback(
		async ( id: number ): Promise< void > => {
			await request( restPath( 'v2', `/leave-types/${ id }` ), { method: 'DELETE' } );
			await reload();
		},
		[ reload ]
	);

	const bulkRemove = useCallback(
		async ( ids: readonly number[] ): Promise< void > => {
			await request( restPath( 'v2', '/leave-types/bulk-delete' ), { method: 'POST', data: { ids } } );
			await reload();
		},
		[ reload ]
	);

	return { rows, total, loading, error, reload, save, remove, bulkRemove };
}
