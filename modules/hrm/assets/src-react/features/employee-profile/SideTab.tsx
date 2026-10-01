/**
 * Left-sidebar nav button for the employee profile. The active row is
 * a solid primary (blue) pill. `TabDef` is the shared shape the page builds its
 * nav list from.
 */

import type { JSX } from 'react';

import { NavItemButton } from '@/shared/components/NavItemButton';

import type { LucideIcon } from './profile-format';

export interface TabDef {
	readonly value: string;
	readonly label: string;
	readonly icon:  LucideIcon;
}

export function SideTab( {
	tab,
	current,
	onSelect,
}: {
	readonly tab:      TabDef;
	readonly current:  string;
	readonly onSelect: ( value: string ) => void;
} ): JSX.Element {
	const isActive = current === tab.value;
	const Icon = tab.icon;
	return (
		<NavItemButton active={ isActive } onClick={ () => onSelect( tab.value ) }>
			<Icon size={ 16 } aria-hidden="true" />
			{ tab.label }
		</NavItemButton>
	);
}
