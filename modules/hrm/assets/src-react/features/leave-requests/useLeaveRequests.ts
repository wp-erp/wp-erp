/**
 * List + approve/reject/delete hook for central Leave Requests, plus a
 * leave-type option loader for the filter.
 *
 * Reads + writes `erp/v2/leave-requests`, which delegates (server side) to the
 * unchanged v1 model layer: `erp_hr_get_leave_requests()`,
 * `erp_hr_leave_request_update_status()` (balance adjustments + status-history
 * rows + e-mail notifications), `erp_hr_delete_leave_request()` (cascade).
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

import type { LeaveRequest } from './types';

interface IdName { id: number; name: string }

export interface LeaveTypeOption {
	readonly value: number;
	readonly label: string;
}

/** Per-status request counts for the status tabs. */
export interface LeaveRequestCounts {
	readonly all:      number;
	readonly approved: number;
	readonly pending:  number;
	readonly rejected: number;
}

const EMPTY_COUNTS: LeaveRequestCounts = { all: 0, approved: 0, pending: 0, rejected: 0 };

interface UseLeaveRequestsArgs {
	readonly status:        number;
	readonly leaveId:       number;
	readonly year:          number;
	readonly departmentId:  number;
	readonly designationId: number;
	readonly type:          string;
	readonly search:        string;
	readonly page:          number;
	readonly perPage:       number;
}

export interface UseLeaveRequestsResult {
	readonly rows:           readonly LeaveRequest[];
	readonly total:          number;
	readonly counts:         LeaveRequestCounts;
	readonly loading:        boolean;
	readonly error:          string | null;
	readonly reload:         () => Promise< void >;
	readonly approve:        ( id: number, reason: string ) => Promise< void >;
	readonly reject:         ( id: number, reason: string ) => Promise< void >;
	readonly remove:         ( id: number ) => Promise< void >;
	readonly bulk:           ( action: 'approve' | 'reject' | 'delete', ids: readonly number[] ) => Promise< void >;
	readonly loadLeaveTypes: () => Promise< readonly LeaveTypeOption[] >;
}

export function useLeaveRequests( {
	status,
	leaveId,
	year,
	departmentId,
	designationId,
	type,
	search,
	page,
	perPage,
}: UseLeaveRequestsArgs ): UseLeaveRequestsResult {
	const [ rows, setRows ]       = useState< readonly LeaveRequest[] >( [] );
	const [ total, setTotal ]     = useState( 0 );
	const [ counts, setCounts ]   = useState< LeaveRequestCounts >( EMPTY_COUNTS );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const { body, headers } = await requestWithHeaders< LeaveRequest[] >(
				restPath( 'v2', '/leave-requests', {
					status:         status || '',
					policy_id:      leaveId,
					year,
					department_id:  departmentId || '',
					designation_id: designationId || '',
					type:           type || '',
					search,
					page,
					per_page:       perPage,
				} )
			);
			const list = Array.isArray( body ) ? body : [];
			setRows( list );
			setTotal( toInt( headers.get( 'X-WP-Total' ), list.length ) );

			// Per-status tab counts, scoped to the SAME calendar year as the list
			// (the controller buckets counts identically) so the tab numbers always
			// agree with the visible rows.
			try {
				const c = await request< LeaveRequestCounts >( restPath( 'v2', '/leave-requests/counts', { year } ) );
				setCounts( {
					all:      Number( c?.all ?? 0 ),
					approved: Number( c?.approved ?? 0 ),
					pending:  Number( c?.pending ?? 0 ),
					rejected: Number( c?.rejected ?? 0 ),
				} );
			} catch {
				setCounts( EMPTY_COUNTS );
			}
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load leave requests.' );
		} finally {
			setLoading( false );
		}
	}, [ status, leaveId, year, departmentId, designationId, type, search, page, perPage ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	const approve = useCallback(
		async ( id: number, reason: string ): Promise< void > => {
			await request( restPath( 'v2', `/leave-requests/${ id }/approve` ), { method: 'PUT', data: { reason } } );
			await reload();
		},
		[ reload ]
	);

	const reject = useCallback(
		async ( id: number, reason: string ): Promise< void > => {
			await request( restPath( 'v2', `/leave-requests/${ id }/reject` ), { method: 'PUT', data: { reason } } );
			await reload();
		},
		[ reload ]
	);

	const remove = useCallback(
		async ( id: number ): Promise< void > => {
			await request( restPath( 'v2', `/leave-requests/${ id }` ), { method: 'DELETE' } );
			await reload();
		},
		[ reload ]
	);

	const bulk = useCallback(
		async ( action: 'approve' | 'reject' | 'delete', ids: readonly number[] ): Promise< void > => {
			await request( restPath( 'v2', '/leave-requests/bulk' ), { method: 'POST', data: { action, ids } } );
			await reload();
		},
		[ reload ]
	);

	const loadLeaveTypes = useCallback( async (): Promise< readonly LeaveTypeOption[] > => {
		const res = await request< IdName[] >( restPath( 'v2', '/leave-types', { per_page: 100 } ) );
		return Array.isArray( res ) ? res.map( ( t ) => ( { value: t.id, label: t.name } ) ) : [];
	}, [] );

	return { rows, total, counts, loading, error, reload, approve, reject, remove, bulk, loadLeaveTypes };
}
