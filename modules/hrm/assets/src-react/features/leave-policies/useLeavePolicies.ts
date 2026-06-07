/**
 * List + CRUD hook for Leave Policies, plus a one-shot form-options loader.
 *
 * Reads + writes `erp/v2/leave-policies`, which delegates (server side) to the
 * unchanged v1 model layer (`erp_hr_leave_insert_policy()`,
 * `erp_hr_leave_get_policies()`, `erp_hr_leave_policy_delete()`). Creating a
 * policy fires `erp_hr_leave_insert_policy` → `erp_hr_apply_policy_existing_employee()`
 * which auto-creates entitlements for matching employees — preserved verbatim.
 *
 * Policies are date/scope-driven, so the list is server-paginated with scope
 * filters rather than loaded whole.
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

import type {
	LeavePolicy,
	LeavePolicyInput,
	LeavePolicyListRow,
	PolicyFormOptions,
	PolicyLookup,
	PolicyOption,
} from './types';

interface RawLookup { id: number; name?: string; title?: string; fy_name?: string }

interface RawFormOptions {
	leave_types?:      RawLookup[];
	financial_years?:  RawLookup[];
	current_f_year?:   number;
	departments?:      RawLookup[];
	designations?:     RawLookup[];
	locations?:        RawLookup[];
	employee_types?:   PolicyOption[];
	genders?:          PolicyOption[];
	marital_statuses?: PolicyOption[];
}

interface UseLeavePoliciesArgs {
	readonly fYear:        number;
	readonly departmentId: number;
	readonly employeeType: string;
	readonly page:         number;
	readonly perPage:      number;
}

export interface UseLeavePoliciesResult {
	readonly rows:        readonly LeavePolicyListRow[];
	readonly total:       number;
	readonly loading:     boolean;
	readonly error:       string | null;
	readonly reload:      () => Promise< void >;
	readonly getOne:      ( id: number ) => Promise< LeavePolicy >;
	readonly save:        ( id: number | null, payload: LeavePolicyInput ) => Promise< void >;
	readonly remove:      ( id: number ) => Promise< void >;
	readonly loadOptions: () => Promise< PolicyFormOptions >;
}

function toLookups( rows: RawLookup[] | undefined, labelKey: 'name' | 'title' | 'fy_name' ): PolicyLookup[] {
	return ( rows ?? [] ).map( ( r ) => ( { id: Number( r.id ), label: String( r[ labelKey ] ?? '' ) } ) );
}

export function useLeavePolicies( {
	fYear,
	departmentId,
	employeeType,
	page,
	perPage,
}: UseLeavePoliciesArgs ): UseLeavePoliciesResult {
	const [ rows, setRows ]       = useState< readonly LeavePolicyListRow[] >( [] );
	const [ total, setTotal ]     = useState( 0 );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const { body, headers } = await requestWithHeaders< LeavePolicyListRow[] >(
				restPath( 'v2', '/leave-policies', {
					f_year:        fYear,
					department_id: departmentId,
					employee_type: employeeType,
					page,
					per_page:      perPage,
				} )
			);
			const list = Array.isArray( body ) ? body : [];
			setRows( list );
			setTotal( toInt( headers.get( 'X-WP-Total' ), list.length ) );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load leave policies.' );
		} finally {
			setLoading( false );
		}
	}, [ fYear, departmentId, employeeType, page, perPage ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	const getOne = useCallback( async ( id: number ): Promise< LeavePolicy > => {
		return request< LeavePolicy >( restPath( 'v2', `/leave-policies/${ id }` ) );
	}, [] );

	const save = useCallback(
		async ( id: number | null, payload: LeavePolicyInput ): Promise< void > => {
			if ( id ) {
				await request( restPath( 'v2', `/leave-policies/${ id }` ), { method: 'PUT', data: payload } );
			} else {
				await request( restPath( 'v2', '/leave-policies' ), { method: 'POST', data: payload } );
			}
			await reload();
		},
		[ reload ]
	);

	const remove = useCallback(
		async ( id: number ): Promise< void > => {
			await request( restPath( 'v2', `/leave-policies/${ id }` ), { method: 'DELETE' } );
			await reload();
		},
		[ reload ]
	);

	const loadOptions = useCallback( async (): Promise< PolicyFormOptions > => {
		const raw = await request< RawFormOptions >( restPath( 'v2', '/leave-policies/form-options' ) );
		return {
			leaveTypes:      toLookups( raw.leave_types, 'name' ),
			financialYears:  toLookups( raw.financial_years, 'fy_name' ),
			currentFYear:    Number( raw.current_f_year ?? 0 ),
			departments:     toLookups( raw.departments, 'title' ),
			designations:    toLookups( raw.designations, 'title' ),
			locations:       toLookups( raw.locations, 'title' ),
			employeeTypes:   raw.employee_types ?? [],
			genders:         raw.genders ?? [],
			maritalStatuses: raw.marital_statuses ?? [],
		};
	}, [] );

	return { rows, total, loading, error, reload, getOne, save, remove, loadOptions };
}
