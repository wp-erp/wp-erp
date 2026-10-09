/**
 * List / grid (card) view switch: a bordered `bg-card` pill holding two icon
 * segments. Purely presentational; both views render the same data.
 *
 * Same name and props as the pro `@erp/hr-shared` `ViewToggle`. The group keeps
 * `overflow-hidden` so the active segment's square `bg-muted` does not paint
 * past the rounded border.
 */

import { LayoutGrid, List } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

interface ViewToggleOption< V extends string > {
	readonly value: V;
	readonly label: string;
	readonly icon:  JSX.Element;
}

interface ViewToggleProps< V extends string > {
	readonly value:    V;
	readonly onChange: ( view: V ) => void;
	/** Optional override; defaults to List + Grid. */
	readonly options?: readonly ViewToggleOption< V >[];
}

export function ViewToggle< V extends string >( { value, onChange, options }: ViewToggleProps< V > ): JSX.Element {
	const items = ( options ?? [
		{ value: 'list' as V, label: __( 'List view', 'erp' ), icon: <List size={ 16 } aria-hidden="true" /> },
		{ value: 'grid' as V, label: __( 'Grid view', 'erp' ), icon: <LayoutGrid size={ 16 } aria-hidden="true" /> },
	] ) as readonly ViewToggleOption< V >[];

	return (
		<div role="group" aria-label={ __( 'View', 'erp' ) } className="inline-flex h-10 items-center gap-0.5 overflow-hidden rounded-md border border-border bg-card p-0.5">
			{ items.map( ( o ) => {
				const active = value === o.value;
				return (
					<button
						key={ o.value }
						type="button"
						aria-label={ o.label }
						aria-pressed={ active }
						title={ o.label }
						onClick={ () => onChange( o.value ) }
						className={ [
							'inline-flex size-8 items-center justify-center rounded transition-colors',
							active ? 'bg-muted text-foreground' : 'text-muted-foreground hover:text-foreground',
						].join( ' ' ) }
					>
						{ o.icon }
					</button>
				);
			} ) }
		</div>
	);
}
