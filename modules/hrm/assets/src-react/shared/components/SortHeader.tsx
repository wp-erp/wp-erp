/**
 * List-table header cells: `Th` (plain) and `SortHeader` (sortable), plus the
 * two parts a table composes by hand when its `<th>` needs extra classes
 * (sticky columns): `SortButton` and `SortArrow`.
 *
 * The sort affordance is the Employees table one: `ArrowUpDown` (inactive),
 * `ArrowUp` / `ArrowDown` (active); a click toggles the order.
 *
 * Same name and props as the pro `@erp/hr-shared` `SortHeader`. Horizontal
 * padding follows the same two schemes, selected by the optional `first` /
 * `last` props:
 *
 *  - omitted (default): `px-2`, and CSS gives the row's first and last cell the
 *    16px outer edge (`first:pl-4 last:pr-4`). Nothing to pass.
 *  - `true`: this cell is an edge column and gets `px-4` on both sides.
 *  - `false`: plain `px-2`, even when the cell is the row's first or last child.
 *
 * Passing either prop switches that cell to the explicit scheme, so a table
 * should use one scheme for the whole header row.
 */

import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import type { ComponentProps, JSX } from 'react';

const THCLS_TEXT = 'text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]';

/** Default scheme: the padding is resolved in CSS from the cell's position. */
const THCLS_AUTO = `whitespace-nowrap px-2 first:pl-4 last:pr-4 ${ THCLS_TEXT }`;

/** Explicit scheme: the caller says which cells are the edge columns. */
const THCLS_BARE = `whitespace-nowrap ${ THCLS_TEXT }`;

function cellClass( align: 'left' | 'right' | undefined, first: boolean | undefined, last: boolean | undefined ): string {
	const alignCls = align === 'right' ? 'text-right' : '';

	if ( first === undefined && last === undefined ) {
		return `${ THCLS_AUTO } ${ alignCls }`;
	}

	return `${ THCLS_BARE } ${ first || last ? 'px-4' : 'px-2' } ${ alignCls }`;
}

interface ThProps {
	readonly label:  string;
	readonly align?: 'left' | 'right';
	/** Leading edge column (`px-4`). Omit for the default CSS-resolved padding. */
	readonly first?: boolean;
	/** Trailing edge column, e.g. Actions (`px-4`). Omit for the default. */
	readonly last?:  boolean;
}

/** A plain (non-sortable) header cell. */
export function Th( { label, align, first, last }: ThProps ): JSX.Element {
	return <th scope="col" className={ cellClass( align, first, last ) }>{ label }</th>;
}

interface SortArrowProps {
	/** True when this column is the sorted one. */
	readonly active: boolean;
	readonly order:  'asc' | 'desc';
}

/** The sort glyph: a double arrow when idle, a single arrow for the direction. */
export function SortArrow( { active, order }: SortArrowProps ): JSX.Element {
	if ( ! active ) {
		return <ArrowUpDown size={ 12 } aria-hidden="true" />;
	}
	return order === 'asc'
		? <ArrowUp size={ 12 } aria-hidden="true" />
		: <ArrowDown size={ 12 } aria-hidden="true" />;
}

type SortButtonProps = Omit< ComponentProps< 'button' >, 'type' >;

/** The clickable label inside a sortable `<th>`. Children are label + glyph. */
export function SortButton( { className, children, ...rest }: SortButtonProps ): JSX.Element {
	return (
		<button
			type="button"
			className={ `inline-flex items-center gap-1 uppercase tracking-normal hover:text-foreground${ className ? ` ${ className }` : '' }` }
			{ ...rest }
		>
			{ children }
		</button>
	);
}

interface SortHeaderProps< K extends string > {
	readonly label:    string;
	readonly sortKey:  K;
	readonly orderBy:  string;
	readonly order:    'asc' | 'desc';
	readonly onSort:   ( key: K ) => void;
	readonly align?:   'left' | 'right';
	/** Leading edge column (`px-4`). Omit for the default CSS-resolved padding. */
	readonly first?:   boolean;
	/** Trailing edge column (`px-4`). Omit for the default. */
	readonly last?:    boolean;
	/** Accessible name of the sort button, e.g. "Sort by Title". Defaults to the label. */
	readonly ariaLabel?: string;
}

export function SortHeader< K extends string = string >( { label, sortKey, orderBy, order, onSort, align, first, last, ariaLabel }: SortHeaderProps< K > ): JSX.Element {
	const activeCol = orderBy === sortKey;
	const aria: 'ascending' | 'descending' | 'none' = activeCol ? ( order === 'asc' ? 'ascending' : 'descending' ) : 'none';

	return (
		<th scope="col" aria-sort={ aria } className={ cellClass( align, first, last ) }>
			<SortButton onClick={ () => onSort( sortKey ) } aria-label={ ariaLabel }>
				{ label }
				<SortArrow active={ activeCol } order={ order } />
			</SortButton>
		</th>
	);
}
