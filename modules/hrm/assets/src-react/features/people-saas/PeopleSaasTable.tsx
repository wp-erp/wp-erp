/**
 * Dense data table — sticky header, hairline borders, monochrome.
 *
 * Sort header reuses the shared employees store (setSort), selection state
 * comes from the same store so it survives view switches.
 */

import { useDispatch, useSelect } from '@wordpress/data';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeesState } from '@/stores/employees';

import { useEmployeesQuery } from '../employees/useEmployeesQuery';
import { PeopleSaasRow } from './PeopleSaasRow';

interface EmployeesStoreDispatch {
	setSort:        ( sort: EmployeesState[ 'sort' ] ) => void;
	setSelectedIds: ( ids: readonly number[] ) => void;
}

interface EmployeesStoreSelectors {
	getSelectedIds: () => readonly number[];
}

type Orderby = EmployeesState[ 'sort' ][ 'orderby' ];

const COLUMNS: ReadonlyArray< {
	readonly id:       string;
	readonly label:    string;
	readonly width:    string;
	readonly orderby?: Orderby;
} > = [
	{ id: 'person',     label: __( 'Person',     'erp' ), width: 'min-w-[220px]', orderby: 'full_name' },
	{ id: 'email',      label: __( 'Email',      'erp' ), width: 'min-w-[200px]', orderby: 'email' },
	{ id: 'department', label: __( 'Department', 'erp' ), width: 'min-w-[140px]' },
	{ id: 'location',   label: __( 'Location',   'erp' ), width: 'min-w-[140px]' },
	{ id: 'joined',     label: __( 'Joined',     'erp' ), width: 'min-w-[110px]', orderby: 'hire_date' },
	{ id: 'status',     label: __( 'Status',     'erp' ), width: 'min-w-[110px]', orderby: 'status' },
];

export function PeopleSaasTable(): JSX.Element {
	const { rows, query } = useEmployeesQuery();
	const selectedIds = useSelect(
		( select ) =>
			( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getSelectedIds(),
		[]
	);
	const { setSort, setSelectedIds } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const currentOrderBy = query.orderby ?? 'full_name';
	const currentOrder   = query.order   ?? 'asc';

	const rowIds      = rows.map( ( r ) => r.id );
	const selectedSet = new Set( selectedIds );
	const visibleSel  = rowIds.filter( ( id ) => selectedSet.has( id ) );
	const allSelected = rowIds.length > 0 && visibleSel.length === rowIds.length;

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

	return (
		<div className="overflow-x-auto">
			<table className="w-full border-collapse text-left" role="grid" aria-label={ __( 'People', 'erp' ) }>
				<thead className="sticky top-0 z-[1] bg-white">
					<tr className="border-b border-slate-200">
						<th scope="col" className="w-10 px-4 py-2">
							<input
								type="checkbox"
								checked={ allSelected }
								onChange={ ( e ) => toggleAll( e.target.checked ) }
								aria-label={ __( 'Select all', 'erp' ) }
								className="size-3.5 rounded border-slate-300 text-slate-900 focus:ring-slate-400"
							/>
						</th>
						{ COLUMNS.map( ( col ) => (
							<th
								key={ col.id }
								scope="col"
								className={ `${ col.width } py-2 pr-4 text-[10px] font-semibold uppercase tracking-wider text-slate-500` }
							>
								{ col.orderby ? (
									<SortButton
										label={ col.label }
										orderby={ col.orderby }
										currentOrderBy={ currentOrderBy }
										currentOrder={ currentOrder }
										onClick={ sortClick }
									/>
								) : (
									<span>{ col.label }</span>
								) }
							</th>
						) ) }
						<th scope="col" className="w-10 px-2 py-2">
							<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{ rows.map( ( row ) => (
						<PeopleSaasRow
							key={ row.id }
							row={ row }
							selected={ selectedSet.has( row.id ) }
							onToggle={ toggleRow }
						/>
					) ) }
				</tbody>
			</table>
		</div>
	);
}

interface SortButtonProps {
	readonly label:          string;
	readonly orderby:        Orderby;
	readonly currentOrderBy: Orderby;
	readonly currentOrder:   'asc' | 'desc';
	readonly onClick:        ( orderby: Orderby ) => void;
}

function SortButton( {
	label,
	orderby,
	currentOrderBy,
	currentOrder,
	onClick,
}: SortButtonProps ): JSX.Element {
	const isActive = currentOrderBy === orderby;
	const Icon     = ! isActive ? ArrowUpDown : currentOrder === 'asc' ? ArrowUp : ArrowDown;
	return (
		<button
			type="button"
			onClick={ () => onClick( orderby ) }
			className={ [
				'inline-flex items-center gap-1 uppercase tracking-wider',
				isActive ? 'text-slate-900' : 'text-slate-500 hover:text-slate-700',
			].join( ' ' ) }
		>
			{ label }
			<Icon size={ 10 } aria-hidden="true" />
		</button>
	);
}
