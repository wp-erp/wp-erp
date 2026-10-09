/**
 * Employees data table.
 *
 * First deliverable ships a plain HTML table styled with Tailwind. The
 * follow-up swap to `@wordpress/dataviews` ` <DataViews/>` (or plugin-ui's
 * re-export) is mechanical — pass the same columns + rows + sort + page state.
 */

import { Checkbox } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import type { JSX } from 'react';

import { SortArrow, SortButton } from '@/shared/components/SortHeader';
import { TablePager } from '@/shared/components/TablePager';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeColumn,
	EmployeesState,
} from '@/stores/employees';

import { COLUMN_IDS } from './constants';
import { EmployeesRowActions } from './EmployeesRowActions';
import { useColumnContext } from './useColumnContext';
import { useEmployeeBulkActions } from './useEmployeeBulkActions';
import { useEmployeeColumns } from './useEmployeeColumns';
import { useEmployeesQuery } from './useEmployeesQuery';

// Sticky-column helpers — Name pinned left (after the checkbox), Actions pinned
// right, so they stay visible while the middle columns scroll horizontally
// (same pattern as the Leave report). Header cells use the same white card bg as
// the rest of the thead; body cells inherit the row bg (card / hover / selected)
// to stay opaque over scroll.
const STICKY_HEAD       = 'sticky z-20 bg-card';
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


	const rowIds        = rows.map( ( r ) => r.id );
	const selectedSet   = new Set( selectedIds );
	const visibleSelected = rowIds.filter( ( id ) => selectedSet.has( id ) );
	const allSelected   = rowIds.length > 0 && visibleSelected.length === rowIds.length;
	const someSelected  = visibleSelected.length > 0 && ! allSelected;
	// Selection only feeds the bulk bar; a viewer with no bulk action (an
	// employee) would tick rows that nothing can act on, so no checkboxes then.
	const canSelect     = useEmployeeBulkActions( selectedIds ).length > 0;
	const stickyName    = canSelect ? STICKY_LEFT_NAME : STICKY_LEFT_CHECK;

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
			<table className="w-full min-w-288 text-left" role="grid" aria-label={ __( 'Employees', 'erp' ) }>
				<caption className="sr-only">{ __( 'Employee list', 'erp' ) }</caption>
				<thead className="border-b border-border bg-card">
					<tr className="h-10">
						{ canSelect && (
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
						) }
						{ columns.map( ( col ) => (
							<th
								key={ col.id }
								scope="col"
								aria-sort={ ariaSortFor( col, currentOrderBy, currentOrder ) }
								className={ `whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282] ${ col.id === COLUMN_IDS.NAME ? `${ STICKY_HEAD } ${ stickyName }` : '' }` }
							>
								{ col.sortable ? (
									<SortButton
										onClick={ () => {
											const nextOrderBy = SORTABLE_COLUMN_TO_QUERY[ col.id ];
											if ( ! nextOrderBy ) {
												return;
											}
											const nextOrder = currentOrderBy === nextOrderBy && currentOrder === 'asc' ? 'desc' : 'asc';
											setSort( { orderby: nextOrderBy, order: nextOrder } );
										} }
									>
										{ col.label }
										{ sortIcon( col, currentOrderBy, currentOrder ) }
									</SortButton>
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
								{ canSelect && (
									<td className={ `w-10 px-4 align-middle ${ STICKY_BODY } ${ STICKY_LEFT_CHECK }` }>
										<Checkbox
											checked={ isChecked }
											onCheckedChange={ ( next: boolean ) => toggleRow( row.id, next ) }
											aria-label={ __( 'Select employee', 'erp' ) }
										/>
									</td>
								) }
								{ columns.map( ( col ) => (
									<td
										key={ col.id }
										className={ `px-2 align-middle text-sm text-foreground ${ col.id === COLUMN_IDS.NAME ? `${ STICKY_BODY } ${ stickyName }` : '' }` }
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

			<TablePager
				page={ page }
				perPage={ perPage }
				total={ total }
				totalPages={ totalPages }
				onPage={ ( next ) => setPagination( { page: next, perPage } ) }
				onPerPage={ ( next ) => setPagination( { page: 1, perPage: next } ) }
			/>
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
	return (
		<SortArrow
			active={ Boolean( mapped ) && mapped === currentOrderBy }
			order={ currentOrder === 'asc' ? 'asc' : 'desc' }
		/>
	);
}
