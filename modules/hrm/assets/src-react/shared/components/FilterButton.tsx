/**
 * Filter funnel toggle: a bare 20x20 icon (no box, no "Filter" label) that
 * reveals the secondary filter row. The icon is text-primary when active (panel
 * open or filters applied) and text-muted-foreground otherwise; a small count
 * badge shows how many filters are applied.
 *
 * One copy for every list toolbar. Same name, props and markup as the pro
 * `@erp/hr-shared` `FilterButton`, so free and pro toolbars look the same.
 */

import { Filter } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

interface FilterButtonProps {
	/** True while the filter row is open. */
	readonly active:   boolean;
	/** Number of applied filters; shown as a badge when above zero. */
	readonly count?:   number;
	readonly onToggle: () => void;
	/**
	 * Overrides `aria-pressed`. By default the button reports pressed whenever
	 * it is highlighted; pass this where the two differ (a page that highlights
	 * the icon for an applied filter but only reports the open panel).
	 */
	readonly pressed?: boolean;
}

export function FilterButton( { active, count = 0, onToggle, pressed }: FilterButtonProps ): JSX.Element {
	const on = active || count > 0;
	return (
		<button
			type="button"
			aria-label={ __( 'Toggle filters', 'erp' ) }
			aria-pressed={ pressed ?? on }
			onClick={ onToggle }
			className={ `relative inline-flex size-5 items-center justify-center transition-colors ${ on ? 'text-primary' : 'text-muted-foreground hover:text-foreground' }` }
		>
			<Filter size={ 20 } strokeWidth={ 1.75 } aria-hidden="true" />
			{ count > 0 ? (
				<span className="absolute -right-1.5 -top-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground">{ count }</span>
			) : null }
		</button>
	);
}
