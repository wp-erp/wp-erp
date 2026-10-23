/**
 * Actionable "no leave entitlement" hint for the leave-request forms.
 *
 * Thin wrapper over the shared `DependencyHint`: surfaces the legacy message
 * plus the two steps to fix it, in dependency order — (1) a leave policy must
 * exist, (2) it must be assigned to the employee.
 *
 * Both steps open screens gated by `erp_leave_manage` (see `app/router.tsx`),
 * so a viewer without that capability gets a plain "contact your HR manager"
 * notice instead of links that would land on the No access panel.
 */

import { Alert, AlertDescription } from '@wedevs/plugin-ui';

import type { JSX } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';

import { DependencyHint } from './DependencyHint';

interface EntitlementEmptyHintProps {
	/** Close the host dialog before navigating away. */
	readonly onClose: () => void;
}

export function EntitlementEmptyHint( { onClose }: EntitlementEmptyHintProps ): JSX.Element {
	const canManageLeave = useCan( 'erp_leave_manage' );

	if ( ! canManageLeave ) {
		return (
			<Alert variant="destructive">
				<AlertDescription>
					{ __( 'You have no leave entitlement for this year yet. Please contact your HR manager.', 'erp' ) }
				</AlertDescription>
			</Alert>
		);
	}

	return (
		<DependencyHint
			message={ __( 'Employee is not entitled to any leave policy. Set leave entitlement to apply for leave.', 'erp' ) }
			steps={ [
				{ label: __( '1. Create or check a leave policy', 'erp' ), path: '/leave/policies' },
				{ label: __( '2. Assign the policy to this employee', 'erp' ), path: '/leave/entitlements' },
			] }
			onBeforeNavigate={ onClose }
		/>
	);
}
