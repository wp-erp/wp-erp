/**
 * Horizontal tab bar for the Employee Profile v2 layout — the active chip is a
 * dark (primary) pill. `TabDef` is the shape the page builds its tab list from.
 */

import type { JSX } from 'react';

import { PillTab } from '@/shared/components/PillTab';
import { __ } from '@/shared/i18n';

export interface TabDef {
	readonly value: string;
	readonly label: string;
}

export function PillTabs( {
	tabs,
	current,
	onSelect,
}: {
	readonly tabs:     readonly TabDef[];
	readonly current:  string;
	readonly onSelect: ( v: string ) => void;
} ): JSX.Element {
	return (
		<div
			role="tablist"
			aria-label={ __( 'Profile sections', 'erp' ) }
			className="flex flex-wrap items-center gap-1 rounded-full bg-muted/60 p-1"
		>
			{ tabs.map( ( t ) => {
				const isActive = current === t.value;
				return (
					<PillTab
						key={ t.value }
						active={ isActive }
						onClick={ () => onSelect( t.value ) }
					>
						{ t.label }
					</PillTab>
				);
			} ) }
		</div>
	);
}
