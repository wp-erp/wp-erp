/**
 * Per-row action menu (kebab DropdownMenu).
 */

import {
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { ArchiveRestore, Eye, LogIn, MoreVertical, Pencil, Trash2, UserCheck, UserX } from 'lucide-react';
import type { JSX } from 'react';

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

	if ( actions.length === 0 ) {
		return null;
	}

	return (
		<DropdownMenu>
			<DropdownMenuTrigger
				render={
					<Button variant="ghost" size="icon" aria-label={ __( 'Row actions', 'erp' ) }>
						<MoreVertical size={ 16 } aria-hidden="true" />
					</Button>
				}
			/>
			<DropdownMenuContent align="end" className="min-w-44">
				{ actions.map( ( action ) => {
					const Icon = action.icon ? ICON_MAP[ action.icon ] : undefined;
					return (
						<DropdownMenuItem
							key={ action.id }
							onClick={ () => {
								void action.onSelect( employee );
							} }
							variant={ action.variant === 'destructive' ? 'destructive' : 'default' }
							className="gap-2"
						>
							{ Icon ? <Icon size={ 14 } aria-hidden="true" /> : null }
							{ action.label }
						</DropdownMenuItem>
					);
				} ) }
			</DropdownMenuContent>
		</DropdownMenu>
	);
}
