/**
 * Data hook for a single employee's leave (`erp/v2/employees/{id}/leave`).
 *
 * Read-only. Returns the current financial-year balance per policy + the leave
 * request history (status labels resolved server-side). Same direct-fetch
 * pattern as the other profile tabs.
 */

import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect, useState } from 'react';

import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

/** Flatten the pro `extra` values onto FormData as `extra[key]` (+ one nested level). */
function appendExtra( fd: FormData, extra: Record< string, unknown >, prefix = 'extra' ): void {
	Object.entries( extra ).forEach( ( [ key, value ] ) => {
		const field = `${ prefix }[${ key }]`;
		if ( value !== null && typeof value === 'object' ) {
			appendExtra( fd, value as Record< string, unknown >, field );
		} else if ( value !== undefined && value !== null ) {
			fd.append( field, String( value ) );
		}
	} );
}

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
	readonly id:             number;
	readonly name:           string;
	readonly available:      number;
	/**
	 * Advanced Leave (pro): whether this policy allows half-day requests
	 * (appended server-side via `erp_hr_v2_leave_assignable_item`). Absent when
	 * the pro module isn't active.
	 */
	readonly halfday_enable?: boolean;
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

export interface LeaveDateValidation {
	readonly total:    number;
	readonly sandwich: boolean;
	readonly days:     ReadonlyArray< { date: string; status: string | null } >;
}

/**
 * Pre-validate a leave date range (mirrors the legacy `leave_request_dates`):
 * resolves the working-day count, or throws an ApiError with the server's
 * message (FY bounds / overlap / insufficient balance).
 */
export async function validateLeaveDates(
	userId: number,
	payload: { leave_policy: number; leave_from: string; leave_to: string }
): Promise< LeaveDateValidation > {
	const path = restPath( 'v2', `/employees/${ userId }/leave/validate-dates` );
	return request< LeaveDateValidation >( path, { method: 'POST', data: payload } );
}

/**
 * Submit a leave request for the employee (mirrors the legacy leave_request). When
 * document files are attached, the request is sent as multipart so the files reach
 * `$_FILES['leave_document']` — the legacy `erp_hr_leave_new` hook
 * (`erp_hr_save_leave_attachment`) then stores them, exactly like the old form.
 */
export async function submitLeaveRequest(
	userId: number,
	payload: { leave_policy: number; leave_from: string; leave_to: string; leave_reason: string; extra?: Record< string, unknown > },
	files: readonly File[] = []
): Promise< void > {
	const path = restPath( 'v2', `/employees/${ userId }/leave/requests` );

	if ( files.length > 0 ) {
		const fd = new FormData();
		fd.append( 'leave_policy', String( payload.leave_policy ) );
		fd.append( 'leave_from', payload.leave_from );
		fd.append( 'leave_to', payload.leave_to );
		fd.append( 'leave_reason', payload.leave_reason );
		if ( payload.extra ) {
			appendExtra( fd, payload.extra );
		}
		files.forEach( ( file ) => fd.append( 'leave_document[]', file ) );
		await apiFetch( { path, method: 'POST', body: fd } );
		return;
	}

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
