/**
 * Left-card vertical nav for the v4 single-employee profile view. Active row is
 * filled with the brand blue. `NavItem` is the shape the page builds its menu
 * list from.
 */

import type { JSX } from 'react';

import { NavItemButton } from '@/shared/components/NavItemButton';
import { __ } from '@/shared/i18n';

import type { LucideIcon } from './single-format';

export interface NavItem {
	readonly value: string;
	readonly label: string;
	readonly icon:  LucideIcon;
}

export function NavMenu( {
	items,
	current,
	onSelect,
}: {
	readonly items:    readonly NavItem[];
	readonly current:  string;
	readonly onSelect: ( v: string ) => void;
} ): JSX.Element {
	return (
		<nav aria-label={ __( 'Profile sections', 'erp' ) } className="flex flex-col gap-1">
			{ items.map( ( item ) => {
				const isActive = current === item.value;
				const Icon = item.icon;
				return (
					<NavItemButton
						key={ item.value }
						active={ isActive }
						onClick={ () => onSelect( item.value ) }
						className="rounded-lg"
					>
						<Icon size={ 18 } strokeWidth={ 2 } aria-hidden="true" />
						{ item.label }
					</NavItemButton>
				);
			} ) }
		</nav>
	);
}
