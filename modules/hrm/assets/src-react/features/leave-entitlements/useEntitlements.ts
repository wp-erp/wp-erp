/**
 * List + assign + delete hook for Leave Entitlements, plus policy / employee
 * option loaders for the assign form.
 *
 * Reads + writes `erp/v2/leave-entitlements`, which delegates (server side) to
 * the unchanged v1 model layer: `erp_hr_leave_get_entitlements()`,
 * `erp_hr_leave_insert_entitlement()` (required-field + already-assigned +
 * employee-active guards), `erp_hr_delete_entitlement()` (cascades dependent
 * leave requests), `erp_hr_leave_get_policies_dropdown_raw()`, `erp_hr_get_employees()`.
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

import type {
	Entitlement,
	EntitlementAssignInput,
	EntitlementAssignResult,
	FinancialYearOption,
	IdOption,
	StringOption,
} from './types';

interface UseEntitlementsArgs {
	readonly year:         number;
	readonly policyId:     number;
	readonly employeeType: string;
	readonly search:       string;
	readonly page:         number;
	readonly perPage:      number;
}

export interface UseEntitlementsResult {
	readonly rows:          readonly Entitlement[];
	readonly total:         number;
	readonly loading:       boolean;
	readonly error:         string | null;
	readonly reload:        () => Promise< void >;
	readonly assign:        ( payload: EntitlementAssignInput ) => Promise< EntitlementAssignResult >;
	readonly remove:        ( id: number, userId: number ) => Promise< void >;
	readonly bulkRemove:    ( ids: readonly number[] ) => Promise< void >;
	readonly loadPolicies:  () => Promise< readonly IdOption[] >;
	readonly loadEmployees: ( policyId: number ) => Promise< readonly IdOption[] >;
	readonly loadFinancialYears: () => Promise< readonly FinancialYearOption[] >;
	readonly loadEmployeeTypes:  () => Promise< readonly StringOption[] >;
}

export function useEntitlements( {
	year,
	policyId,
	employeeType,
	search,
	page,
	perPage,
}: UseEntitlementsArgs ): UseEntitlementsResult {
	const [ rows, setRows ]       = useState< readonly Entitlement[] >( [] );
	const [ total, setTotal ]     = useState( 0 );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const { body, headers } = await requestWithHeaders< Entitlement[] >(
				restPath( 'v2', '/leave-entitlements', {
					year,
					policy_id:     policyId,
					employee_type: employeeType,
					search,
					page,
					per_page:      perPage,
				} )
			);
			const list = Array.isArray( body ) ? body : [];
			setRows( list );
			setTotal( toInt( headers.get( 'X-WP-Total' ), list.length ) );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load entitlements.' );
		} finally {
			setLoading( false );
		}
	}, [ year, policyId, employeeType, search, page, perPage ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	const assign = useCallback(
		async ( payload: EntitlementAssignInput ): Promise< EntitlementAssignResult > => {
			const res = await request< EntitlementAssignResult >(
				restPath( 'v2', '/leave-entitlements' ),
				{ method: 'POST', data: payload }
			);
			await reload();
			return res;
		},
		[ reload ]
	);

	const remove = useCallback(
		async ( id: number, userId: number ): Promise< void > => {
			await request( restPath( 'v2', `/leave-entitlements/${ id }`, { user_id: userId } ), { method: 'DELETE' } );
			await reload();
		},
		[ reload ]
	);

	const bulkRemove = useCallback(
		async ( ids: readonly number[] ): Promise< void > => {
			await request( restPath( 'v2', '/leave-entitlements/bulk-delete' ), { method: 'POST', data: { ids } } );
			await reload();
		},
		[ reload ]
	);

	const loadPolicies = useCallback( async (): Promise< readonly IdOption[] > => {
		const res = await request< IdOption[] >( restPath( 'v2', '/leave-entitlements/policies' ) );
		return Array.isArray( res ) ? res : [];
	}, [] );

	const loadEmployees = useCallback( async ( pid: number ): Promise< readonly IdOption[] > => {
		if ( ! pid ) {
			return [];
		}
		const res = await request< IdOption[] >( restPath( 'v2', '/leave-entitlements/employees', { policy_id: pid } ) );
		return Array.isArray( res ) ? res : [];
	}, [] );

	// Financial years for the year filter — sourced from `erp/v2/financial-years`
	// (the same list the Settings editor drives). The entitlement `year` param
	// filters by `f_year` id.
	const loadFinancialYears = useCallback( async (): Promise< readonly FinancialYearOption[] > => {
		const res = await request< Array< { id: number; fy_name: string; start_date: string; end_date: string } > >(
			restPath( 'v2', '/financial-years' )
		);
		return Array.isArray( res )
			? res.map( ( r ) => ( { id: Number( r.id ), label: String( r.fy_name ), start_date: String( r.start_date ), end_date: String( r.end_date ) } ) )
			: [];
	}, [] );

	// Employee-type options for the list filter (narrows by policy.employee_type).
	const loadEmployeeTypes = useCallback( async (): Promise< readonly StringOption[] > => {
		const res = await request< StringOption[] >( restPath( 'v2', '/leave-entitlements/employee-types' ) );
		return Array.isArray( res ) ? res : [];
	}, [] );

	return { rows, total, loading, error, reload, assign, remove, bulkRemove, loadPolicies, loadEmployees, loadFinancialYears, loadEmployeeTypes };
}
