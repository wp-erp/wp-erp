/**
 * Per-row action menu (kebab DropdownMenu).
 */

import { ArchiveRestore, Eye, LogIn, Pencil, Trash2, UserCheck, UserX } from 'lucide-react';
import type { JSX } from 'react';

import { RowActionsMenu } from '@/shared/components/RowActionsMenu';
import { __ } from '@/shared/i18n';
import type { EmployeeListItem } from '@/stores/employees';

import { useEmployeeRowActions } from './useEmployeeRowActions';

interface EmployeesRowActionsProps {
	readonly employee: EmployeeListItem;
}

const ICON_MAP: Record< string, typeof Eye > = {
	ArchiveRestore,
	Eye,
	LogIn,
	Pencil,
	Trash2,
	UserCheck,
	UserX,
};

export function EmployeesRowActions( { employee }: EmployeesRowActionsProps ): JSX.Element | null {
	const actions = useEmployeeRowActions( employee );

	return (
		<RowActionsMenu
			label={ __( 'Row actions', 'erp' ) }
			actions={ actions.map( ( action ) => {
				const Icon = action.icon ? ICON_MAP[ action.icon ] : undefined;
				return {
					id:       action.id,
					label:    action.label,
					...( Icon ? { icon: Icon } : {} ),
					onSelect: () => {
						void action.onSelect( employee );
					},
					variant:  action.variant === 'destructive' ? 'destructive' : 'default',
				};
			} ) }
		/>
	);
}
