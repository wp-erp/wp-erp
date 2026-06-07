/**
 * Employees grid (card) view — an additive alternative to `EmployeesTable`.
 *
 * Renders the exact same `useEmployeesQuery` rows, selection store and
 * pagination as the table; only the per-row presentation differs. Each card
 * reuses the canonical cells (`NameCell`, `StatusCell`) and `EmployeesRowActions`
 * so behaviour stays in sync with the list view.
 */

import { Checkbox } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeesState } from '@/stores/employees';

import { NameCell } from './columns/NameCell';
import { StatusCell } from './columns/StatusCell';
import { EmployeesRowActions } from './EmployeesRowActions';
import { useEmployeesQuery } from './useEmployeesQuery';

interface EmployeesStoreDispatch {
	setPagination:  ( pagination: EmployeesState[ 'pagination' ] ) => void;
	setSelectedIds: ( ids: readonly number[] ) => void;
}

interface EmployeesStoreSelectors {
	getSelectedIds: () => readonly number[];
}

export function EmployeesGrid(): JSX.Element {
	const { rows, page, perPage, total, totalPages } = useEmployeesQuery();
	const selectedIds = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getSelectedIds(),
		[]
	);
	const { setPagination, setSelectedIds } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const selectedSet = new Set( selectedIds );

	const toggleRow = ( id: number, next: boolean ): void => {
		if ( next ) {
			setSelectedIds( Array.from( new Set( [ ...selectedIds, id ] ) ) );
		} else {
			setSelectedIds( selectedIds.filter( ( x ) => x !== id ) );
		}
	};

	const start = total === 0 ? 0 : ( page - 1 ) * perPage + 1;
	const end   = Math.min( page * perPage, total );

	return (
		<div className="bg-card">
			<ul className="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
				{ rows.map( ( row ) => {
					const isChecked = selectedSet.has( row.id );
					return (
						<li
							key={ row.id }
							data-selected={ isChecked ? 'true' : 'false' }
							className="group relative flex flex-col gap-3 rounded-lg border border-border bg-card p-4 shadow-sm transition-colors hover:bg-muted/40 data-[selected=true]:border-primary/40 data-[selected=true]:bg-primary/5"
						>
							<div className="flex items-center justify-between">
								<Checkbox
									checked={ isChecked }
									onCheckedChange={ ( next: boolean ) => toggleRow( row.id, next ) }
									aria-label={ sprintf( __( 'Select %s', 'erp' ), row.full_name || row.email ) }
								/>
								<EmployeesRowActions employee={ row } />
							</div>

							<NameCell row={ row } />

							<div className="flex items-center justify-between gap-2">
								<span className="truncate text-xs text-muted-foreground">
									{ row.department?.name ?? '—' }
								</span>
								<StatusCell row={ row } />
							</div>
						</li>
					);
				} ) }
			</ul>

			<footer className="flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3 text-sm text-muted-foreground">
				<span className="text-xs tabular-nums">
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
						<span className="text-xs">{ __( 'Per page', 'erp' ) }</span>
						<select
							value={ perPage }
							onChange={ ( e ) => setPagination( { page: 1, perPage: parseInt( e.target.value, 10 ) } ) }
							aria-label={ __( 'Cards per page', 'erp' ) }
							className="h-8 cursor-pointer rounded-md border border-border bg-card pl-2 pr-6 text-xs font-medium text-foreground focus:border-primary focus:outline-none"
						>
							{ [ 12, 24, 48, 96 ].map( ( n ) => (
								<option key={ n } value={ n }>{ n }</option>
							) ) }
						</select>
					</label>

					<div className="inline-flex items-center gap-1">
						<button
							type="button"
							onClick={ () => setPagination( { page: Math.max( 1, page - 1 ), perPage } ) }
							disabled={ page <= 1 }
							className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted disabled:opacity-40"
							aria-label={ __( 'Previous page', 'erp' ) }
						>
							<ChevronLeft size={ 14 } aria-hidden="true" />
						</button>
						<span className="min-w-20 px-2 text-center text-xs font-medium tabular-nums text-foreground">
							{ sprintf( __( '%1$d of %2$d', 'erp' ), page, Math.max( totalPages, 1 ) ) }
						</span>
						<button
							type="button"
							onClick={ () => setPagination( { page: page + 1, perPage } ) }
							disabled={ page >= totalPages }
							className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted disabled:opacity-40"
							aria-label={ __( 'Next page', 'erp' ) }
						>
							<ChevronRight size={ 14 } aria-hidden="true" />
						</button>
					</div>
				</div>
			</footer>
		</div>
	);
}
