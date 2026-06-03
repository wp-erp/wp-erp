/**
 * Data hook for a single employee's leave (`erp/v2/employees/{id}/leave`).
 *
 * Read-only. Returns the current financial-year balance per policy + the leave
 * request history (status labels resolved server-side). Same direct-fetch
 * pattern as the other profile tabs.
 */

import { useCallback, useEffect, useState } from 'react';

import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

export interface LeaveSummary {
	readonly policy:      string;
	readonly entitlement: number;
	readonly total:       number;
	readonly available:   number;
	readonly spent:       number;
	readonly from_date:   string | null;
	readonly to_date:     string | null;
}

export interface LeaveRequest {
	readonly id:          number;
	readonly start_date:  string | null;
	readonly end_date:    string | null;
	readonly policy:      string;
	readonly reason:      string;
	readonly days:        number;
	readonly duration:    string;
	readonly status:      string;
	readonly status_code: number | null;
}

export interface LeaveOption {
	readonly id:   number;
	readonly name: string;
}

export interface LeaveStatusOption {
	readonly value: string;
	readonly label: string;
}

export interface LeaveMeta {
	readonly current_year:    number;
	readonly financial_years: readonly LeaveOption[];
	readonly statuses:        readonly LeaveStatusOption[];
	readonly policies:        readonly LeaveOption[];
}

export interface EmployeeLeave {
	readonly summary:  readonly LeaveSummary[];
	readonly requests: readonly LeaveRequest[];
	readonly meta:     LeaveMeta;
}

export interface LeaveFilters {
	readonly year?:      number;
	readonly status?:    string;
	readonly policy_id?: number;
}

export interface AssignablePolicy {
	readonly id:        number;
	readonly name:      string;
	readonly available: number;
}

export interface UseEmployeeLeave {
	readonly data:    EmployeeLeave | null;
	readonly loading: boolean;
	readonly error:   string | null;
	readonly refetch: () => void;
}

/** Policies the employee can apply against in a financial year (+ balance). */
export async function fetchAssignablePolicies(
	userId: number,
	fYear: number
): Promise< readonly AssignablePolicy[] > {
	const path = restPath( 'v2', `/employees/${ userId }/leave/assignable`, { f_year: fYear } );
	const body = await request< { policies: readonly AssignablePolicy[] } >( path );
	return body.policies ?? [];
}

/** Submit a leave request for the employee (mirrors the legacy leave_request). */
export async function submitLeaveRequest(
	userId: number,
	payload: { leave_policy: number; leave_from: string; leave_to: string; leave_reason: string }
): Promise< void > {
	const path = restPath( 'v2', `/employees/${ userId }/leave/requests` );
	await request( path, { method: 'POST', data: payload } );
}

export function useEmployeeLeave( userId: number, filters: LeaveFilters = {} ): UseEmployeeLeave {
	const [ data, setData ]       = useState< EmployeeLeave | null >( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );
	const [ nonce, setNonce ]     = useState( 0 );

	const { year, status, policy_id: policyId } = filters;

	useEffect( () => {
		let cancelled = false;
		setLoading( true );
		setError( null );

		const path = restPath( 'v2', `/employees/${ userId }/leave`, {
			year,
			status,
			policy_id: policyId,
		} );
		void request< EmployeeLeave >( path )
			.then( ( body ) => {
				if ( ! cancelled ) {
					setData( body );
				}
			} )
			.catch( ( raw ) => {
				if ( ! cancelled ) {
					setError( ( raw as ApiError )?.message ?? __( 'Could not load leave.', 'erp' ) );
				}
			} )
			.finally( () => {
				if ( ! cancelled ) {
					setLoading( false );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ userId, year, status, policyId, nonce ] );

	const refetch = useCallback( () => setNonce( ( n ) => n + 1 ), [] );

	return { data, loading, error, refetch };
}
