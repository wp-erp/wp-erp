/**
 * Left-sidebar nav button for the Employee Profile v4 layout. The active row is
 * a solid primary (blue) pill. `TabDef` is the shared shape the page builds its
 * nav list from.
 */

import type { JSX } from 'react';

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
		<button
			type="button"
			onClick={ () => onSelect( tab.value ) }
			aria-current={ isActive ? 'page' : undefined }
			className={ [
				'flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm font-medium transition-colors',
				isActive ? 'bg-primary text-primary-foreground' : 'text-foreground hover:bg-muted',
			].join( ' ' ) }
		>
			<Icon size={ 16 } aria-hidden="true" />
			{ tab.label }
		</button>
	);
}
