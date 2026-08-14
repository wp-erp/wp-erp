/**
 * Segmented-pill tab trigger for the Employee Profile tab bar, matching the
 * Reports tabs. The active chip (white card + primary text + ring) is driven off
 * our own `current` state rather than a plugin-ui/base-ui data attribute, so the
 * blue active style is reliable.
 */

import { TabsTrigger } from '@wedevs/plugin-ui';
import type { JSX, ReactNode } from 'react';

import type { LucideIcon } from './profile-format';

export function ProfileTab( {
	value,
	current,
	icon: Icon,
	children,
}: {
	readonly value:    string;
	readonly current:  string;
	readonly icon:     LucideIcon;
	readonly children: ReactNode;
} ): JSX.Element {
	const isActive = current === value;
	return (
		<TabsTrigger
			value={ value }
			className={ [
				'!flex-none shrink-0 grow-0 rounded-md px-3 py-1.5 text-sm font-medium ring-1 ring-transparent transition-all',
				isActive ? '!bg-card !shadow-sm !ring-primary/40' : '!bg-transparent !shadow-none',
			].join( ' ' ) }
		>
			{ /* Colour lives on this inner span so it beats plugin-ui's trigger
			    colour rule; the icon inherits via currentColor. */ }
			<span
				className={ [
					'inline-flex items-center gap-2',
					isActive ? '!text-primary' : '!text-muted-foreground',
				].join( ' ' ) }
			>
				<Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
				{ children }
			</span>
		</TabsTrigger>
	);
}
