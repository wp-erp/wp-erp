/**
 * Left-card vertical nav for the v4 single-employee profile view. Active row is
 * filled with the brand blue. `NavItem` is the shape the page builds its menu
 * list from.
 */

import type { JSX } from 'react';

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
					<button
						key={ item.value }
						type="button"
						aria-current={ isActive ? 'page' : undefined }
						onClick={ () => onSelect( item.value ) }
						className={ [
							'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium transition-colors',
							isActive
								? 'bg-primary text-primary-foreground'
								: 'text-foreground hover:bg-muted',
						].join( ' ' ) }
					>
						<Icon size={ 18 } strokeWidth={ 2 } aria-hidden="true" />
						{ item.label }
					</button>
				);
			} ) }
		</nav>
	);
}
