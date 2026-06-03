/**
 * Token-driven table — clear hierarchy, generous row height, sortable headers.
 */

import { Checkbox } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeesState } from '@/stores/employees';

import { useEmployeesQuery } from '../employees/useEmployeesQuery';
import { PeopleProRow } from './PeopleProRow';

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
	{ id: 'person',     label: __( 'Member',     'erp' ), width: 'min-w-[240px]', orderby: 'full_name' },
	{ id: 'email',      label: __( 'Email',      'erp' ), width: 'min-w-[220px]', orderby: 'email' },
	{ id: 'department', label: __( 'Department', 'erp' ), width: 'min-w-[160px]' },
	{ id: 'location',   label: __( 'Location',   'erp' ), width: 'min-w-[140px]' },
	{ id: 'joined',     label: __( 'Joined',     'erp' ), width: 'min-w-[120px]', orderby: 'hire_date' },
	{ id: 'status',     label: __( 'Status',     'erp' ), width: 'min-w-[120px]', orderby: 'status' },
];

export function PeopleProTable(): JSX.Element {
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
			<table className="w-full text-left" role="grid" aria-label={ __( 'People', 'erp' ) }>
				<thead className="border-b border-border bg-muted/40">
					<tr className="h-10">
						<th scope="col" className="w-12 px-4">
							<Checkbox
								checked={ allSelected }
								onCheckedChange={ ( next: boolean ) => toggleAll( next ) }
								aria-label={ __( 'Select all', 'erp' ) }
							/>
						</th>
						{ COLUMNS.map( ( col ) => (
							<th
								key={ col.id }
								scope="col"
								className={ `${ col.width } pr-4 text-xs font-medium uppercase tracking-wider text-muted-foreground` }
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
									<span className="uppercase tracking-wider">{ col.label }</span>
								) }
							</th>
						) ) }
						<th scope="col" className="w-10 pr-4">
							<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{ rows.map( ( row ) => (
						<PeopleProRow
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
				'inline-flex items-center gap-1 uppercase tracking-wider transition-colors',
				isActive ? 'text-foreground' : 'text-muted-foreground hover:text-foreground',
			].join( ' ' ) }
		>
			{ label }
			<Icon size={ 12 } aria-hidden="true" />
		</button>
	);
}
