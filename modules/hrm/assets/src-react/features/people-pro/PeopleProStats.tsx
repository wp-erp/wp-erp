/**
 * Inline stats strip — 4 token-driven metric tiles.
 *
 * No raw hex; uses design-system "light pill" tokens: success-light,
 * warning-light, destructive-light, info-light. Each tile is clickable and
 * sets the matching status filter (same UX pattern as the segmented control).
 */

import { useDispatch, useSelect } from '@wordpress/data';
import { CircleCheck, CirclePause, CircleX, Users } from 'lucide-react';
import type { ComponentType, JSX, SVGProps } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName, toCountsQuery } from '@/stores/employees';
import type {
	EmployeeCountsQuery,
	EmployeeListQuery,
	EmployeeStatusCounts,
	EmployeesState,
} from '@/stores/employees';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
	getCounts:  ( query: EmployeeCountsQuery ) => EmployeeStatusCounts | null;
}

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

type StatusKey = 'all' | 'active' | 'inactive' | 'terminated';

interface Tile {
	readonly id:        StatusKey;
	readonly label:     string;
	readonly icon:      LucideIcon;
	readonly tone:      string;
	readonly value:     ( c: EmployeeStatusCounts ) => number;
}

const TILES: ReadonlyArray< Tile > = [
	{
		id:    'all',
		label: __( 'Total members', 'erp' ),
		icon:  Users,
		tone:  'bg-info-light text-info-on-light',
		value: ( c ) => c.all,
	},
	{
		id:    'active',
		label: __( 'Active', 'erp' ),
		icon:  CircleCheck,
		tone:  'bg-success-light text-success-on-light',
		value: ( c ) => c.by_status.active ?? 0,
	},
	{
		id:    'inactive',
		label: __( 'Inactive', 'erp' ),
		icon:  CirclePause,
		tone:  'bg-warning-light text-warning-on-light',
		value: ( c ) => c.by_status.inactive ?? 0,
	},
	{
		id:    'terminated',
		label: __( 'Terminated', 'erp' ),
		icon:  CircleX,
		tone:  'bg-destructive-light text-destructive-on-light',
		value: ( c ) => c.by_status.terminated ?? 0,
	},
];

export function PeopleProStats(): JSX.Element {
	const filters = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getFilters(),
		[]
	);
	const counts = useSelect(
		( select ) =>
			( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getCounts(
				toCountsQuery( filters )
			),
		[ filters.search, filters.department_id, filters.designation_id, filters.location_id ]
	);
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const activeStatus = filters.status ?? 'all';

	return (
		<div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
			{ TILES.map( ( tile ) => {
				const Icon   = tile.icon;
				const value  = counts ? tile.value( counts ) : null;
				const active = activeStatus === tile.id;
				return (
					<button
						key={ tile.id }
						type="button"
						aria-pressed={ active }
						onClick={ () => {
							setFilters( { ...filters, status: tile.id } );
							setPagination( { page: 1, perPage: 20 } );
						} }
						className={ [
							'group flex items-start gap-3 rounded-lg border bg-card p-4 text-left shadow-sm transition-all',
							active
								? 'border-primary ring-1 ring-primary/30'
								: 'border-border hover:border-primary/40 hover:shadow-md',
						].join( ' ' ) }
					>
						<span className={ `inline-flex size-10 shrink-0 items-center justify-center rounded-md ${ tile.tone }` }>
							<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
						</span>
						<div className="min-w-0 flex-1">
							<p className="truncate text-xs font-medium uppercase tracking-wide text-muted-foreground">
								{ tile.label }
							</p>
							<p className="mt-1 text-2xl font-semibold leading-7 tabular-nums text-foreground">
								{ value === null ? '—' : value.toLocaleString() }
							</p>
						</div>
					</button>
				);
			} ) }
		</div>
	);
}
