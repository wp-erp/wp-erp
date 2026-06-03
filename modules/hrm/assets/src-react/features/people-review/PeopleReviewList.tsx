/**
 * Card-row list with sort header + modern pagination footer.
 *
 * Uses the same `erp-hr/employees` store dispatch the legacy table uses, so
 * sort + selection state stays in sync between the two views.
 */

import { useDispatch, useSelect } from '@wordpress/data';
import {
	ArrowDown,
	ArrowUp,
	ArrowUpDown,
	ChevronLeft,
	ChevronRight,
} from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeesState } from '@/stores/employees';

import { useEmployeesQuery } from '../employees/useEmployeesQuery';
import { PeopleReviewRow } from './PeopleReviewRow';

interface EmployeesStoreDispatch {
	setSort:        ( sort: EmployeesState[ 'sort' ] ) => void;
	setPagination:  ( pagination: EmployeesState[ 'pagination' ] ) => void;
	setSelectedIds: ( ids: readonly number[] ) => void;
}

interface EmployeesStoreSelectors {
	getSelectedIds: () => readonly number[];
}

type Orderby = EmployeesState[ 'sort' ][ 'orderby' ];

const SORT_OPTIONS: ReadonlyArray< { readonly value: Orderby; readonly label: string } > = [
	{ value: 'full_name', label: __( 'Name',      'erp' ) },
	{ value: 'email',     label: __( 'Email',     'erp' ) },
	{ value: 'hire_date', label: __( 'Hire date', 'erp' ) },
	{ value: 'status',    label: __( 'Status',    'erp' ) },
];

export function PeopleReviewList(): JSX.Element {
	const { rows, page, perPage, total, totalPages, query } = useEmployeesQuery();
	const selectedIds = useSelect(
		( select ) =>
			( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getSelectedIds(),
		[]
	);
	const { setSort, setPagination, setSelectedIds } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const currentOrderBy = query.orderby ?? 'full_name';
	const currentOrder   = query.order   ?? 'asc';

	const rowIds      = rows.map( ( r ) => r.id );
	const selectedSet = new Set( selectedIds );
	const visibleSel  = rowIds.filter( ( id ) => selectedSet.has( id ) );
	const allSelected = rowIds.length > 0 && visibleSel.length === rowIds.length;
	const anySelected = visibleSel.length > 0;

	const toggleAll = ( next: boolean ): void => {
		if ( next ) {
			setSelectedIds( Array.from( new Set( [ ...selectedIds, ...rowIds ] ) ) );
		} else {
			setSelectedIds( selectedIds.filter( ( id ) => ! rowIds.includes( id ) ) );
		}
	};

	const toggleRow = ( id: number, next: boolean ): void => {
		if ( next ) {
			setSelectedIds( Array.from( new Set( [ ...selectedIds, id ] ) ) );
		} else {
			setSelectedIds( selectedIds.filter( ( x ) => x !== id ) );
		}
	};

	const sortClick = ( orderby: Orderby ): void => {
		const order: 'asc' | 'desc' =
			currentOrderBy === orderby && currentOrder === 'asc' ? 'desc' : 'asc';
		setSort( { orderby, order } );
	};

	const totalLabel = total
		? sprintf( __( '%d people', 'erp' ), total )
		: __( 'No people', 'erp' );

	return (
		<section aria-label={ __( 'People list', 'erp' ) } className="space-y-2">
			{ anySelected ? (
				<div className="flex items-center justify-between rounded-2xl border border-primary/40 bg-primary/10 px-4 py-2 text-xs ring-1 ring-primary/20 backdrop-blur-xl">
					<span className="font-medium text-primary">
						{ sprintf(
							/* translators: %d: selected people count */
							__( '%d selected', 'erp' ),
							visibleSel.length
						) }
					</span>
					<button
						type="button"
						className="font-medium text-primary hover:underline"
						onClick={ () => setSelectedIds( [] ) }
					>
						{ __( 'Clear selection', 'erp' ) }
					</button>
				</div>
			) : null }

			<div className="grid grid-cols-[auto_minmax(0,2fr)_minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)_auto] items-center gap-4 px-4 pb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
				<input
					type="checkbox"
					checked={ allSelected }
					onChange={ ( e ) => toggleAll( e.target.checked ) }
					aria-label={ __( 'Select all on this page', 'erp' ) }
					className="size-4 rounded border-border text-primary focus:ring-primary/40"
				/>
				<SortHeader
					label={ __( 'Person', 'erp' ) }
					orderby="full_name"
					currentOrderBy={ currentOrderBy }
					currentOrder={ currentOrder }
					onClick={ sortClick }
				/>
				<SortHeader
					label={ __( 'Email', 'erp' ) }
					orderby="email"
					currentOrderBy={ currentOrderBy }
					currentOrder={ currentOrder }
					onClick={ sortClick }
				/>
				<span>{ __( 'Department', 'erp' ) }</span>
				<SortHeader
					label={ __( 'Joined', 'erp' ) }
					orderby="hire_date"
					currentOrderBy={ currentOrderBy }
					currentOrder={ currentOrder }
					onClick={ sortClick }
				/>
				<SortHeader
					label={ __( 'Status', 'erp' ) }
					orderby="status"
					currentOrderBy={ currentOrderBy }
					currentOrder={ currentOrder }
					onClick={ sortClick }
				/>
			</div>

			<div className="space-y-2">
				{ rows.map( ( row ) => (
					<PeopleReviewRow
						key={ row.id }
						row={ row }
						selected={ selectedSet.has( row.id ) }
						onToggle={ toggleRow }
					/>
				) ) }
			</div>

			<footer className="flex flex-wrap items-center justify-between gap-3 pt-4 text-xs text-muted-foreground">
				<span className="font-medium">
					{ totalLabel }
					{ totalPages > 1
						? ' · ' + sprintf( __( 'page %1$d of %2$d', 'erp' ), page, totalPages )
						: null }
				</span>
				<div className="flex items-center gap-2">
					<select
						value={ perPage }
						onChange={ ( e ) =>
							setPagination( { page: 1, perPage: parseInt( e.target.value, 10 ) } )
						}
						className="h-9 rounded-full border border-white/40 bg-white/60 px-3 text-xs font-medium text-foreground ring-1 ring-white/40 backdrop-blur focus:border-primary focus:outline-none"
						aria-label={ __( 'Rows per page', 'erp' ) }
					>
						{ [ 10, 20, 50, 100 ].map( ( n ) => (
							<option key={ n } value={ n }>
								{ sprintf( __( '%d / page', 'erp' ), n ) }
							</option>
						) ) }
					</select>
					<div className="inline-flex h-9 items-center gap-1 rounded-full border border-white/40 bg-white/60 p-1 shadow-sm ring-1 ring-white/40 backdrop-blur-xl">
						<button
							type="button"
							onClick={ () => setPagination( { page: Math.max( 1, page - 1 ), perPage } ) }
							disabled={ page <= 1 }
							className="inline-flex size-7 items-center justify-center rounded-full text-foreground transition-colors hover:bg-white/70 disabled:opacity-40"
							aria-label={ __( 'Previous page', 'erp' ) }
						>
							<ChevronLeft size={ 14 } aria-hidden="true" />
						</button>
						<span className="min-w-8 px-1 text-center text-xs font-semibold tabular-nums text-foreground">
							{ page }
						</span>
						<button
							type="button"
							onClick={ () => setPagination( { page: page + 1, perPage } ) }
							disabled={ page >= totalPages }
							className="inline-flex size-7 items-center justify-center rounded-full text-foreground transition-colors hover:bg-white/70 disabled:opacity-40"
							aria-label={ __( 'Next page', 'erp' ) }
						>
							<ChevronRight size={ 14 } aria-hidden="true" />
						</button>
					</div>
				</div>
			</footer>
		</section>
	);
}

interface SortHeaderProps {
	readonly label:          string;
	readonly orderby:        Orderby;
	readonly currentOrderBy: Orderby;
	readonly currentOrder:   'asc' | 'desc';
	readonly onClick:        ( orderby: Orderby ) => void;
}

function SortHeader( {
	label,
	orderby,
	currentOrderBy,
	currentOrder,
	onClick,
}: SortHeaderProps ): JSX.Element {
	const isActive = currentOrderBy === orderby;
	const Icon     = ! isActive ? ArrowUpDown : currentOrder === 'asc' ? ArrowUp : ArrowDown;
	return (
		<button
			type="button"
			onClick={ () => onClick( orderby ) }
			className={ [
				'inline-flex items-center gap-1 text-left uppercase tracking-wider transition-colors',
				isActive ? 'text-foreground' : 'hover:text-foreground',
			].join( ' ' ) }
		>
			{ label }
			<Icon size={ 11 } aria-hidden="true" />
		</button>
	);
}

// Keep import "SORT_OPTIONS" accessible for future grid-view; reference it
// here so the const is not flagged unused while it lives in the same module.
export { SORT_OPTIONS };
