/**
 * Table footer: "Showing %d-%d of %d", a rows-per-page select and a chevron
 * pager. The Employees table footer, reused by every list.
 *
 * Same name and props as the pro `@erp/hr-shared` `TablePager`. The optional
 * props cover what the free lists need on top of that: a server-reported page
 * count, and the card grid's own page sizes and wording.
 */

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@wedevs/plugin-ui';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

import { IconButton } from './IconButton';

const DEFAULT_PER_PAGE_OPTIONS: readonly number[] = [ 10, 20, 50, 100 ];

interface TablePagerProps {
	/** Current page, 1-based. */
	readonly page:        number;
	readonly perPage:     number;
	/** Total number of rows across all pages. */
	readonly total:       number;
	readonly onPage:      ( page: number ) => void;
	readonly onPerPage:   ( perPage: number ) => void;
	/** Page count when the caller already has it. Defaults to `total / perPage`. */
	readonly totalPages?: number;
	/** Page sizes offered by the select. Defaults to 10, 20, 50, 100. */
	readonly perPageOptions?:   readonly number[];
	/** Visible label of the select. Defaults to "Rows per page". */
	readonly perPageLabel?:     string;
	/** Accessible name and placeholder of the select. Defaults to the label. */
	readonly perPageAriaLabel?: string;
}

export function TablePager( {
	page,
	perPage,
	total,
	onPage,
	onPerPage,
	totalPages,
	perPageOptions = DEFAULT_PER_PAGE_OPTIONS,
	perPageLabel,
	perPageAriaLabel,
}: TablePagerProps ): JSX.Element {
	const pages = Math.max( 1, totalPages ?? Math.ceil( total / perPage ) );
	const from  = total === 0 ? 0 : ( page - 1 ) * perPage + 1;
	const to    = Math.min( total, page * perPage );
	const label = perPageLabel ?? __( 'Rows per page', 'erp' );
	const aria  = perPageAriaLabel ?? label;

	return (
		<footer className="flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3 text-sm text-muted-foreground">
			<span className="text-xs">
				{ sprintf(
					/* translators: 1: start row, 2: end row, 3: total */
					__( 'Showing %1$d–%2$d of %3$d', 'erp' ),
					from,
					to,
					total
				) }
			</span>
			<div className="flex items-center gap-3">
				<label className="flex items-center gap-2">
					<span className="text-xs">{ label }</span>
					<Select
						items={ perPageOptions.map( ( n ) => ( { value: String( n ), label: String( n ) } ) ) }
						value={ String( perPage ) }
						onValueChange={ ( v ) => onPerPage( parseInt( String( v ), 10 ) ) }
					>
						<SelectTrigger aria-label={ aria } className="h-8 cursor-pointer rounded-md border border-border bg-card pl-2 pr-6 text-xs font-medium text-foreground focus:border-primary focus:outline-none">
							<SelectValue placeholder={ aria } />
						</SelectTrigger>
						<SelectContent align="start" alignItemWithTrigger={ false }>
							{ perPageOptions.map( ( n ) => (
								<SelectItem key={ n } value={ String( n ) }>
									{ n }
								</SelectItem>
							) ) }
						</SelectContent>
					</Select>
				</label>

				<div className="inline-flex items-center gap-1">
					<IconButton
						onClick={ () => onPage( Math.max( 1, page - 1 ) ) }
						disabled={ page <= 1 }
						aria-label={ __( 'Previous page', 'erp' ) }
					>
						<ChevronLeft size={ 14 } aria-hidden="true" />
					</IconButton>
					<span className="min-w-20 px-2 text-center text-xs font-medium text-foreground">
						{ sprintf( __( '%1$d of %2$d', 'erp' ), page, pages ) }
					</span>
					<IconButton
						onClick={ () => onPage( Math.min( pages, page + 1 ) ) }
						disabled={ page >= pages }
						aria-label={ __( 'Next page', 'erp' ) }
					>
						<ChevronRight size={ 14 } aria-hidden="true" />
					</IconButton>
				</div>
			</div>
		</footer>
	);
}
