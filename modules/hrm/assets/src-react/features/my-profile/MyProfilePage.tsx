/**
 * `/my-profile` — the current user's own employee profile (self-service).
 *
 * Mirrors the legacy "My Profile" HR menu entry: it resolves the logged-in
 * user's id from the `erp-hr/me` store and renders the same profile view used
 * for `#/employees/{id}`. Edit access is governed by the existing capability
 * map (`erp_edit_employee` maps to self for the employee role), so no extra
 * gating is needed here.
 */

import { useSelect } from '@wordpress/data';
import type { JSX } from 'react';

import { EmployeeSingleInner } from '@/features/employee-create/EmployeeSinglePage';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { __ } from '@/shared/i18n';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

export function MyProfilePage(): JSX.Element {
	const userId = useSelect(
		( select ) => ( select( meStoreName ) as unknown as { getUser: () => MeUser | null } ).getUser()?.id ?? 0,
		[]
	);

	return (
		<ErrorBoundary>
			{ userId > 0 ? (
				<EmployeeSingleInner userId={ userId } />
			) : (
				<div className="mx-auto my-12 max-w-md text-center text-sm text-muted-foreground">
					{ __( 'Your employee profile could not be found.', 'erp' ) }
				</div>
			) }
		</ErrorBoundary>
	);
}
