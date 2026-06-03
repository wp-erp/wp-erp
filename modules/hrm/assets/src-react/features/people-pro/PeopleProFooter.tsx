/**
 * Card footer — selection summary + page-size + pagination controls.
 */

import { Button } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeesState } from '@/stores/employees';

import { useEmployeesQuery } from '../employees/useEmployeesQuery';

interface EmployeesStoreSelectors {
	getSelectedIds: () => readonly number[];
}

interface EmployeesStoreDispatch {
	setPagination:  ( pagination: EmployeesState[ 'pagination' ] ) => void;
	setSelectedIds: ( ids: readonly number[] ) => void;
}

export function PeopleProFooter(): JSX.Element {
	const { page, perPage, total, totalPages } = useEmployeesQuery();
	const selectedIds = useSelect(
		( select ) =>
			( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getSelectedIds(),
		[]
	);
	const { setPagination, setSelectedIds } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const start = total === 0 ? 0 : ( page - 1 ) * perPage + 1;
	const end   = Math.min( page * perPage, total );

	return (
		<footer className="flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3 text-sm text-muted-foreground">
			<div className="flex items-center gap-3">
				{ selectedIds.length > 0 ? (
					<>
						<span className="font-medium text-foreground">
							{ sprintf(
								/* translators: %d: selected count */
								__( '%d selected', 'erp' ),
								selectedIds.length
							) }
						</span>
						<Button
							variant="ghost"
							size="sm"
							className="h-7 px-2 text-xs"
							onClick={ () => setSelectedIds( [] ) }
						>
							{ __( 'Clear', 'erp' ) }
						</Button>
					</>
				) : (
					<span className="tabular-nums">
						{ sprintf(
							/* translators: 1: start row, 2: end row, 3: total */
							__( 'Showing %1$d–%2$d of %3$d', 'erp' ),
							start,
							end,
							total
						) }
					</span>
				) }
			</div>

			<div className="flex items-center gap-3">
				<label className="flex items-center gap-2">
					<span className="text-xs">{ __( 'Rows per page', 'erp' ) }</span>
					<select
						value={ perPage }
						onChange={ ( e ) =>
							setPagination( { page: 1, perPage: parseInt( e.target.value, 10 ) } )
						}
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
	);
}
