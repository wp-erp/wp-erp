/**
 * Data hook for a single employee's job history
 * (`erp/v2/employees/{id}/job-histories`).
 *
 * Read-only. Same direct-fetch pattern as the Notes tab. The v2 endpoint
 * resolves IDs/codes to display labels server-side, so this hook just holds the
 * four grouped buckets the legacy "Job" tab shows.
 */

import { useCallback, useEffect, useState } from 'react';

import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

export interface StatusHistory {
	readonly id:      number;
	readonly date:    string | null;
	readonly status:  string;
	readonly comment: string;
}

export interface EmploymentHistory {
	readonly id:      number;
	readonly date:    string | null;
	readonly type:    string;
	readonly comment: string;
}

export interface CompensationHistory {
	readonly id:       number;
	readonly date:     string | null;
	readonly pay_rate: string;
	readonly pay_type: string;
	readonly reason:   string;
	readonly comment:  string;
}

export interface JobInfoHistory {
	readonly id:              number;
	readonly date:            string | null;
	readonly department:      string;
	readonly designation:     string;
	readonly location:        string;
	readonly reporting_to:    string;
	// Raw ids for edit-prefill (labels above are display-only).
	readonly department_id?:   number;
	readonly designation_id?:  number;
	readonly location_id?:     number;
	readonly reporting_to_id?: number;
}

export interface JobHistories {
	readonly status:       readonly StatusHistory[];
	readonly employment:   readonly EmploymentHistory[];
	readonly compensation: readonly CompensationHistory[];
	readonly job:          readonly JobInfoHistory[];
}

export interface UseEmployeeJobHistories {
	readonly data:          JobHistories | null;
	readonly loading:       boolean;
	readonly error:         string | null;
	readonly createHistory: ( payload: Record< string, unknown > ) => Promise< void >;
	readonly updateHistory: ( historyId: number, payload: Record< string, unknown > ) => Promise< void >;
	readonly deleteHistory: ( id: number ) => Promise< void >;
	readonly refetch:       () => void;
}

export function useEmployeeJobHistories( userId: number ): UseEmployeeJobHistories {
	const [ data, setData ]       = useState< JobHistories | null >( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );
	const [ nonce, setNonce ]     = useState( 0 );

	useEffect( () => {
		let cancelled = false;
		setLoading( true );
		setError( null );

		const path = restPath( 'v2', `/employees/${ userId }/job-histories` );
		void request< JobHistories >( path )
			.then( ( body ) => {
				if ( ! cancelled ) {
					setData( body );
				}
			} )
			.catch( ( raw ) => {
				if ( ! cancelled ) {
					setError( ( raw as ApiError )?.message ?? __( 'Could not load job history.', 'erp' ) );
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

	const createHistory = useCallback(
		async ( payload: Record< string, unknown > ): Promise< void > => {
			const path = restPath( 'v2', `/employees/${ userId }/job-histories` );
			await request( path, { method: 'POST', data: payload } );
			setNonce( ( n ) => n + 1 );
		},
		[ userId ]
	);

	const updateHistory = useCallback(
		async ( historyId: number, payload: Record< string, unknown > ): Promise< void > => {
			const path = restPath( 'v2', `/employees/${ userId }/job-histories/${ historyId }` );
			await request( path, { method: 'PUT', data: payload } );
			setNonce( ( n ) => n + 1 );
		},
		[ userId ]
	);

	const deleteHistory = useCallback(
		async ( id: number ): Promise< void > => {
			const path = restPath( 'v2', `/employees/${ userId }/job-histories/${ id }` );
			await request( path, { method: 'DELETE' } );
			setNonce( ( n ) => n + 1 );
		},
		[ userId ]
	);

	const refetch = useCallback( () => setNonce( ( n ) => n + 1 ), [] );

	return { data, loading, error, createHistory, updateHistory, deleteHistory, refetch };
}
