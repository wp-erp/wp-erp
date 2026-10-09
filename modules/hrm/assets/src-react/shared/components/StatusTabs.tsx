/**
 * Status tablist with counts: the underlined tab strip at the top of a list
 * card (All | Active | Trash ...). The Employees `StatusFilter` styling.
 *
 * One copy for every list. Same name and props as the pro `@erp/hr-shared`
 * `StatusTabs`; the free one also accepts numeric tab values and a missing
 * count (nothing is rendered while it loads).
 */

import type { JSX } from 'react';

export interface StatusTab< V extends string | number = string > {
	readonly value:  V;
	readonly label:  string;
	/** Omit or pass null to render the tab without a "(n)" suffix. */
	readonly count?: number | null;
}

interface StatusTabsProps< V extends string | number > {
	readonly tabs:      readonly StatusTab< V >[];
	/** `value` of the selected tab. */
	readonly value:     V;
	readonly onChange:  ( value: V ) => void;
	/** Accessible name of the tablist, e.g. "Employee status". */
	readonly ariaLabel: string;
}

export function StatusTabs< V extends string | number = string >( { tabs, value, onChange, ariaLabel }: StatusTabsProps< V > ): JSX.Element {
	return (
		<div role="tablist" aria-label={ ariaLabel } className="-mb-2 flex min-w-0 max-w-full items-stretch overflow-x-auto pb-2 scrollbar-none">
			{ tabs.map( ( tab ) => {
				const active = tab.value === value;
				return (
					<button
						key={ String( tab.value ) }
						type="button"
						role="tab"
						aria-selected={ active }
						onClick={ () => onChange( tab.value ) }
						className={ [ 'relative inline-flex h-11 shrink-0 items-center gap-1.5 whitespace-nowrap px-4 text-sm font-medium transition-colors', active ? 'text-primary' : 'text-foreground hover:text-primary' ].join( ' ' ) }
					>
						<span>{ tab.label }</span>
						{ tab.count !== undefined && tab.count !== null ? (
							<span className="font-normal text-[#a5a5aa]">({ tab.count })</span>
						) : null }
						<span aria-hidden="true" className={ [ 'absolute inset-x-0 -bottom-2 h-0.5', active ? 'bg-primary' : 'bg-transparent' ].join( ' ' ) } />
					</button>
				);
			} ) }
		</div>
	);
}
