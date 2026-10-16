/**
 * Per-row action menu: the three-dot trigger every table and list row uses.
 *
 * Same markup as `EmployeesRowActions`, so row actions look and behave the same
 * on every screen. Pass the actions a row offers; hidden ones are skipped and a
 * row with none renders nothing.
 */

import {
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { MoreVertical, type LucideIcon } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

export interface RowAction {
	readonly id:       string;
	readonly label:    string;
	readonly icon?:    LucideIcon;
	readonly onSelect: () => void;
	readonly variant?: 'default' | 'destructive';
	readonly disabled?: boolean;
	/** True to leave the action out, e.g. when the viewer lacks the capability. */
	readonly hidden?:  boolean;
}

interface RowActionsMenuProps {
	readonly actions: readonly RowAction[];
	/** Accessible name of the trigger, e.g. "Actions for Jane". */
	readonly label?:  string;
}

export function RowActionsMenu( { actions, label }: RowActionsMenuProps ): JSX.Element | null {
	const visible = actions.filter( ( action ) => ! action.hidden );

	if ( visible.length === 0 ) {
		return null;
	}

	return (
		<DropdownMenu>
			<DropdownMenuTrigger
				render={
					<Button variant="ghost" size="icon" aria-label={ label ?? __( 'Row actions', 'erp' ) }>
						<MoreVertical size={ 16 } aria-hidden="true" />
					</Button>
				}
			/>
			<DropdownMenuContent align="end" className="min-w-44">
				{ visible.map( ( action ) => {
					const Icon = action.icon;
					return (
						<DropdownMenuItem
							key={ action.id }
							onClick={ action.onSelect }
							variant={ action.variant === 'destructive' ? 'destructive' : 'default' }
							disabled={ action.disabled }
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
