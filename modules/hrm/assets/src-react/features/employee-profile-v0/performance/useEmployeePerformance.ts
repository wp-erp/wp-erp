/**
 * Data hook for a single employee's performance
 * (`erp/v2/employees/{id}/performance`).
 *
 * Read-only. Returns the three legacy buckets — reviews / comments / goals —
 * with rating codes + reviewer/supervisor IDs already resolved server-side.
 * Same direct-fetch pattern as the other profile tabs.
 */

import { useCallback, useEffect, useState } from 'react';

import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

export interface PerformanceReview {
	readonly id:            number;
	readonly date:          string | null;
	readonly reporting_to:  string;
	readonly job_knowledge: string;
	readonly work_quality:  string;
	readonly attendance:    string;
	readonly communication: string;
	readonly dependability: string;
}

export interface PerformanceComment {
	readonly id:       number;
	readonly date:     string | null;
	readonly reviewer: string;
	readonly comment:  string;
}

export interface PerformanceGoal {
	readonly id:                     number;
	readonly set_date:               string | null;
	readonly completion_date:        string | null;
	readonly goal_description:       string;
	readonly employee_assessment:    string;
	readonly supervisor:             string;
	readonly supervisor_assessment:  string;
}

export interface EmployeePerformance {
	readonly reviews:  readonly PerformanceReview[];
	readonly comments: readonly PerformanceComment[];
	readonly goals:    readonly PerformanceGoal[];
}

export interface UseEmployeePerformance {
	readonly data:              EmployeePerformance | null;
	readonly loading:           boolean;
	readonly error:             string | null;
	readonly createPerformance: ( payload: Record< string, unknown > ) => Promise< void >;
	readonly deletePerformance: ( id: number ) => Promise< void >;
}

export function useEmployeePerformance( userId: number ): UseEmployeePerformance {
	const [ data, setData ]       = useState< EmployeePerformance | null >( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );
	const [ nonce, setNonce ]     = useState( 0 );

	useEffect( () => {
		let cancelled = false;
		setLoading( true );
		setError( null );

		const path = restPath( 'v2', `/employees/${ userId }/performance` );
		void request< EmployeePerformance >( path )
			.then( ( body ) => {
				if ( ! cancelled ) {
					setData( body );
				}
			} )
			.catch( ( raw ) => {
				if ( ! cancelled ) {
					setError( ( raw as ApiError )?.message ?? __( 'Could not load performance.', 'erp' ) );
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
	}, [ userId, nonce ] );

	const createPerformance = useCallback(
		async ( payload: Record< string, unknown > ): Promise< void > => {
			const path = restPath( 'v2', `/employees/${ userId }/performance` );
			await request( path, { method: 'POST', data: payload } );
			setNonce( ( n ) => n + 1 );
		},
		[ userId ]
	);

	const deletePerformance = useCallback(
		async ( id: number ): Promise< void > => {
			const path = restPath( 'v2', `/employees/${ userId }/performance/${ id }` );
			await request( path, { method: 'DELETE' } );
			setNonce( ( n ) => n + 1 );
		},
		[ userId ]
	);

	return { data, loading, error, createPerformance, deletePerformance };
}
