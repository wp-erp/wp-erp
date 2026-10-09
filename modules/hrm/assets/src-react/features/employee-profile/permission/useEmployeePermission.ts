/**
 * Data hook for a single employee's permission state
 * (`erp/v2/employees/{id}/permission`).
 *
 * Mirrors the legacy permission tab: the endpoint returns one entry per role the
 * *acting* user may grant — HR Manager (free) plus any active module's manager
 * roles (CRM Manager/Agent, Accounting Manager, Recruiter). GET reads the list,
 * `save` PUTs the full desired map (so module setters keep unchanged roles), and
 * the server fires `erp_hr_after_employee_permission_set` so every module applies
 * its own role.
 */

import { useCallback, useEffect, useState } from 'react';

import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

export interface PermissionRole {
	readonly key:         string;
	readonly label:       string;
	readonly description: string;
	readonly enabled:     boolean;
}

interface PermissionPayload {
	readonly roles: readonly PermissionRole[];
}

export interface UseEmployeePermission {
	readonly roles:   readonly PermissionRole[];
	readonly loading: boolean;
	readonly error:   string | null;
	readonly save:    ( key: string, enabled: boolean ) => Promise< void >;
}

export function useEmployeePermission( userId: number ): UseEmployeePermission {
	const [ roles, setRoles ]     = useState< readonly PermissionRole[] >( [] );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	useEffect( () => {
		let cancelled = false;
		setLoading( true );
		setError( null );

		const path = restPath( 'v2', `/employees/${ userId }/permission` );
		void request< PermissionPayload >( path )
			.then( ( body ) => {
				if ( ! cancelled ) {
					setRoles( body.roles ?? [] );
				}
			} )
			.catch( ( raw ) => {
				if ( ! cancelled ) {
					setError( ( raw as ApiError )?.message ?? __( 'Could not load permissions.', 'erp' ) );
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
	}, [ userId ] );

	// Toggle one role: send the full current map with the single change applied so
	// the module setters preserve every other (unchanged) role.
	const save = useCallback(
		async ( key: string, enabled: boolean ): Promise< void > => {
			const desired: Record< string, boolean > = {};
			roles.forEach( ( role ) => {
				desired[ role.key ] = role.key === key ? enabled : role.enabled;
			} );

			const path = restPath( 'v2', `/employees/${ userId }/permission` );
			const body = await request< PermissionPayload >( path, {
				method: 'PUT',
				data: { roles: desired },
			} );
			setRoles( body.roles ?? [] );
		},
		[ userId, roles ]
	);

	return { roles, loading, error, save };
}
