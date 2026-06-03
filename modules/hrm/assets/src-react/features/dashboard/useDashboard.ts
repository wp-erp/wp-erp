/**
 * Loads the aggregate `GET /erp/v2/dashboard` payload once on mount.
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import type { DashboardData } from './types';

export interface UseDashboardResult {
	readonly data:    DashboardData | null;
	readonly loading: boolean;
	readonly error:   string | null;
	readonly reload:  () => Promise< void >;
}

export function useDashboard(): UseDashboardResult {
	const [ data, setData ]       = useState< DashboardData | null >( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const body = await request< DashboardData >( restPath( 'v2', '/dashboard' ) );
			setData( body );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load the dashboard.' );
		} finally {
			setLoading( false );
		}
	}, [] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	return { data, loading, error, reload };
}
