/**
 * Load + full-set save hook for HR Financial Years.
 *
 * Reads + writes `erp/v2/financial-years`, which delegates to the unchanged
 * helpers `erp_get_hr_financial_years()` / `erp_settings_save_leave_years()`.
 * Save is a full replace (truncate + reinsert) — exactly the legacy Settings
 * "Leave Years" behaviour, so deleting a year = omitting it from the payload.
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import type { FinancialYear } from './types';

export interface UseFinancialYearsResult {
	readonly rows:    readonly FinancialYear[];
	readonly loading: boolean;
	readonly error:   string | null;
	readonly reload:  () => Promise< void >;
	readonly save:    ( years: readonly FinancialYear[] ) => Promise< void >;
}

export function useFinancialYears(): UseFinancialYearsResult {
	const [ rows, setRows ]       = useState< readonly FinancialYear[] >( [] );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const res = await request< FinancialYear[] >( restPath( 'v2', '/financial-years' ) );
			setRows( Array.isArray( res ) ? res : [] );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load financial years.' );
		} finally {
			setLoading( false );
		}
	}, [] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	const save = useCallback( async ( years: readonly FinancialYear[] ): Promise< void > => {
		const res = await request< FinancialYear[] >( restPath( 'v2', '/financial-years' ), {
			method: 'POST',
			data:   { years },
		} );
		setRows( Array.isArray( res ) ? res : [] );
	}, [] );

	return { rows, loading, error, reload, save };
}
