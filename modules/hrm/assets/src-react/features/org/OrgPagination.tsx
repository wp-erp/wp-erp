/**
 * Pagination footer for the HR taxonomy tables (departments / designations).
 *
 * Visually identical to the People table footer
 * (`features/employees/EmployeesTable.tsx`) so pagination looks the same across
 * the HR admin: a left-aligned "Showing X–Y of Z", and a right cluster with a
 * rows-per-page select + prev / next icon buttons and a "page of total"
 * indicator.
 *
 * Client-side pagination over the already-fetched list — these are
 * low-cardinality entities, so the whole set is loaded once and paged in the
 * browser.
 */

import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

interface OrgPaginationProps {
	readonly page:       number;
	readonly totalPages: number;
	readonly total:      number;
	readonly perPage:    number;
	readonly onPage:     ( page: number ) => void;
	readonly onPerPage:  ( perPage: number ) => void;
}

export function OrgPagination( { page, totalPages, total, perPage, onPage, onPerPage }: OrgPaginationProps ): JSX.Element {
	const pages = Math.max( totalPages, 1 );
	const start = total === 0 ? 0 : ( page - 1 ) * perPage + 1;
	const end   = Math.min( page * perPage, total );

	return (
		<footer className="flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3 text-sm text-muted-foreground">
			<span className="text-xs">
				{ sprintf(
					/* translators: 1: start row, 2: end row, 3: total */
					__( 'Showing %1$d–%2$d of %3$d', 'erp' ),
					start,
					end,
					total
				) }
			</span>
			<div className="flex items-center gap-3">
				<label className="flex items-center gap-2">
					<span className="text-xs">{ __( 'Rows per page', 'erp' ) }</span>
					<select
						value={ perPage }
						onChange={ ( e ) => onPerPage( parseInt( e.target.value, 10 ) ) }
						aria-label={ __( 'Rows per page', 'erp' ) }
						className="h-8 cursor-pointer rounded-md border border-border bg-card pl-2 pr-6 text-xs font-medium text-foreground focus:border-primary focus:outline-none"
					>
						{ [ 10, 20, 50, 100 ].map( ( n ) => (
							<option key={ n } value={ n }>
								{ n }
							</option>
						) ) }
					</select>
				</label>

				<div className="inline-flex items-center gap-1">
					<button
						type="button"
						onClick={ () => onPage( Math.max( 1, page - 1 ) ) }
						disabled={ page <= 1 }
						className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted disabled:opacity-40"
						aria-label={ __( 'Previous page', 'erp' ) }
					>
						<ChevronLeft size={ 14 } aria-hidden="true" />
					</button>
					<span className="min-w-20 px-2 text-center text-xs font-medium text-foreground">
						{ sprintf( __( '%1$d of %2$d', 'erp' ), page, pages ) }
					</span>
					<button
						type="button"
						onClick={ () => onPage( Math.min( pages, page + 1 ) ) }
						disabled={ page >= pages }
						className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted disabled:opacity-40"
						aria-label={ __( 'Next page', 'erp' ) }
					>
						<ChevronRight size={ 14 } aria-hidden="true" />
					</button>
				</div>
			</div>
		</footer>
	);
}
