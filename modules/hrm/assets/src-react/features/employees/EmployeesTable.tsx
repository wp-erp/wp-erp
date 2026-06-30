/**
 * Employees data table.
 *
 * First deliverable ships a plain HTML table styled with Tailwind. The
 * follow-up swap to `@wordpress/dataviews` ` <DataViews/>` (or plugin-ui's
 * re-export) is mechanical — pass the same columns + rows + sort + page state.
 */

import { Checkbox } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { ArrowDown, ArrowUp, ArrowUpDown, ChevronLeft, ChevronRight } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeColumn,
	EmployeesState,
} from '@/stores/employees';

import { COLUMN_IDS } from './constants';
import { EmployeesRowActions } from './EmployeesRowActions';
import { useColumnContext } from './useColumnContext';
import { useEmployeeColumns } from './useEmployeeColumns';
import { useEmployeesQuery } from './useEmployeesQuery';

// Sticky-column helpers — Name pinned left (after the checkbox), Actions pinned
// right, so they stay visible while the middle columns scroll horizontally
// (same pattern as the Leave report). Header cells use a solid muted bg; body
// cells inherit the row bg (card / hover / selected) to stay opaque over scroll.
const STICKY_HEAD       = 'sticky z-20 bg-muted';
const STICKY_BODY       = 'sticky z-10 bg-card group-hover:bg-muted/40 group-data-[selected=true]:bg-primary/5';
const STICKY_LEFT_CHECK = 'left-0';
const STICKY_LEFT_NAME  = 'left-10';
const STICKY_RIGHT      = 'right-0';

interface EmployeesStoreDispatch {
	setSort:        ( sort: EmployeesState[ 'sort' ] ) => void;
	setPagination:  ( pagination: EmployeesState[ 'pagination' ] ) => void;
	setSelectedIds: ( ids: readonly number[] ) => void;
}

interface EmployeesStoreSelectors {
	getSelectedIds: () => readonly number[];
}

const SORTABLE_COLUMN_TO_QUERY: Record< string, EmployeesState[ 'sort' ][ 'orderby' ] > = {
	name:      'full_name',
	email:     'email',
	hire_date: 'hire_date',
	status:    'status',
};

export function EmployeesTable(): JSX.Element {
	const columns = useEmployeeColumns();
	const { ctx } = useColumnContext();
	const { rows, page, perPage, total, totalPages, query } = useEmployeesQuery();
	const selectedIds = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getSelectedIds(),
		[]
	);
	const { setSort, setPagination, setSelectedIds } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const currentOrderBy = query.orderby;
	const currentOrder   = query.order;

	const start = total === 0 ? 0 : ( page - 1 ) * perPage + 1;
	const end   = Math.min( page * perPage, total );

	const rowIds        = rows.map( ( r ) => r.id );
	const selectedSet   = new Set( selectedIds );
	const visibleSelected = rowIds.filter( ( id ) => selectedSet.has( id ) );
	const allSelected   = rowIds.length > 0 && visibleSelected.length === rowIds.length;
	const someSelected  = visibleSelected.length > 0 && ! allSelected;

	const toggleAll = ( next: boolean ): void => {
		if ( next ) {
			const merged = Array.from( new Set( [ ...selectedIds, ...rowIds ] ) );
			setSelectedIds( merged );
		} else {
			const remaining = selectedIds.filter( ( id ) => ! rowIds.includes( id ) );
			setSelectedIds( remaining );
		}
	};

	const toggleRow = ( id: number, next: boolean ): void => {
		if ( next ) {
			setSelectedIds( Array.from( new Set( [ ...selectedIds, id ] ) ) );
		} else {
			setSelectedIds( selectedIds.filter( ( x ) => x !== id ) );
		}
	};

	return (
		<div className="bg-card">
			<div className="overflow-x-auto">
			<table className="w-full min-w-[72rem] text-left" role="grid" aria-label={ __( 'Employees', 'erp' ) }>
				<caption className="sr-only">{ __( 'Employee list', 'erp' ) }</caption>
				<thead className="border-b border-border bg-card">
					<tr className="h-10">
						<th scope="col" className={ `w-10 px-4 ${ STICKY_HEAD } ${ STICKY_LEFT_CHECK }` }>
							<span className="sr-only">{ __( 'Select all', 'erp' ) }</span>
							<Checkbox
								checked={ allSelected }
								onCheckedChange={ ( next: boolean ) => toggleAll( next ) }
								aria-label={
									someSelected
										? __( 'Some employees selected', 'erp' )
										: __( 'Select all employees on this page', 'erp' )
								}
							/>
						</th>
						{ columns.map( ( col ) => (
							<th
								key={ col.id }
								scope="col"
								aria-sort={ ariaSortFor( col, currentOrderBy, currentOrder ) }
								className={ `whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282] ${ col.id === COLUMN_IDS.NAME ? `${ STICKY_HEAD } ${ STICKY_LEFT_NAME }` : '' }` }
							>
								{ col.sortable ? (
									<button
										type="button"
										onClick={ () => {
											const nextOrderBy = SORTABLE_COLUMN_TO_QUERY[ col.id ];
											if ( ! nextOrderBy ) {
												return;
											}
											const nextOrder = currentOrderBy === nextOrderBy && currentOrder === 'asc' ? 'desc' : 'asc';
											setSort( { orderby: nextOrderBy, order: nextOrder } );
										} }
										className="inline-flex items-center gap-1 uppercase tracking-normal hover:text-foreground"
									>
										{ col.label }
										{ sortIcon( col, currentOrderBy, currentOrder ) }
									</button>
								) : (
									<span className="uppercase">{ col.label }</span>
								) }
							</th>
						) ) }
						<th scope="col" className={ `w-8 pr-4 ${ STICKY_HEAD } ${ STICKY_RIGHT }` }>
							<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{ rows.map( ( row ) => {
						const isChecked = selectedSet.has( row.id );
						return (
							<tr
								key={ row.id }
								data-selected={ isChecked ? 'true' : 'false' }
								className="group h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40 data-[selected=true]:bg-primary/5"
							>
								<td className={ `w-10 px-4 align-middle ${ STICKY_BODY } ${ STICKY_LEFT_CHECK }` }>
									<Checkbox
										checked={ isChecked }
										onCheckedChange={ ( next: boolean ) => toggleRow( row.id, next ) }
										aria-label={ __( 'Select employee', 'erp' ) }
									/>
								</td>
								{ columns.map( ( col ) => (
									<td
										key={ col.id }
										className={ `px-2 align-middle text-sm text-foreground ${ col.id === COLUMN_IDS.NAME ? `${ STICKY_BODY } ${ STICKY_LEFT_NAME }` : '' }` }
									>
										{ col.render( row, ctx ) }
									</td>
								) ) }
								<td className={ `pr-4 pl-2 text-right align-middle ${ STICKY_BODY } ${ STICKY_RIGHT }` }>
									<EmployeesRowActions employee={ row } />
								</td>
							</tr>
						);
					} ) }
				</tbody>
			</table>
			</div>

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
						<span className="min-w-20 px-2 text-center text-xs font-medium text-foreground">
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

type AriaSort = 'ascending' | 'descending' | 'none';

function ariaSortFor(
	col: EmployeeColumn,
	currentOrderBy: string | undefined,
	currentOrder:   string | undefined
): AriaSort | undefined {
	if ( ! col.sortable ) {
		return undefined;
	}
	const mapped = SORTABLE_COLUMN_TO_QUERY[ col.id ];
	if ( ! mapped || mapped !== currentOrderBy ) {
		return 'none';
	}
	return currentOrder === 'asc' ? 'ascending' : 'descending';
}

function sortIcon(
	col: EmployeeColumn,
	currentOrderBy: string | undefined,
	currentOrder:   string | undefined
): JSX.Element {
	const mapped = SORTABLE_COLUMN_TO_QUERY[ col.id ];
	if ( ! mapped || mapped !== currentOrderBy ) {
		return <ArrowUpDown size={ 12 } aria-hidden="true" />;
	}
	return currentOrder === 'asc' ? (
		<ArrowUp size={ 12 } aria-hidden="true" />
	) : (
		<ArrowDown size={ 12 } aria-hidden="true" />
	);
}
