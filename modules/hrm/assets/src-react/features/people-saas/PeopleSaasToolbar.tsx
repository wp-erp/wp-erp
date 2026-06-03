/**
 * SaaS-style toolbar — breadcrumb header + dense filter bar.
 *
 * Layout:
 *   ┌─ People · Directory ──────────────────────  [Export]  [+ Add person]
 *   ├─ chip · chip · chip   ┃ Search   [Filters] [Sort]
 *   └────────────────────────────────────────────────────────────────────
 *
 * Visual cues: hairline borders, neutral palette, accent reserved for the
 * primary CTA and the active chip.
 */

import { Input } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	ArrowDownUp,
	ChevronRight,
	Download,
	ListFilter,
	Plus,
	Search,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName, toCountsQuery } from '@/stores/employees';
import type {
	EmployeeCountsQuery,
	EmployeeListQuery,
	EmployeeStatusCounts,
	EmployeesState,
} from '@/stores/employees';

import { LookupFilter } from '../employees/filters/LookupFilter';

type StatusValue = NonNullable< EmployeeListQuery[ 'status' ] >;

const STATUS_CHIPS: ReadonlyArray< { readonly value: StatusValue; readonly label: string } > = [
	{ value: 'all',        label: __( 'All',        'erp' ) },
	{ value: 'active',     label: __( 'Active',     'erp' ) },
	{ value: 'inactive',   label: __( 'Inactive',   'erp' ) },
	{ value: 'terminated', label: __( 'Terminated', 'erp' ) },
	{ value: 'trash',      label: __( 'Trash',      'erp' ) },
];

type Orderby = EmployeesState[ 'sort' ][ 'orderby' ];

const SORT_OPTIONS: ReadonlyArray< { readonly value: Orderby; readonly label: string } > = [
	{ value: 'full_name', label: __( 'Name',      'erp' ) },
	{ value: 'email',     label: __( 'Email',     'erp' ) },
	{ value: 'hire_date', label: __( 'Hire date', 'erp' ) },
	{ value: 'status',    label: __( 'Status',    'erp' ) },
];

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
	getSort:    () => EmployeesState[ 'sort' ];
	getCounts:  ( query: EmployeeCountsQuery ) => EmployeeStatusCounts | null;
}

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setSort:       ( sort: EmployeesState[ 'sort' ] ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

const SEARCH_DEBOUNCE_MS = 250;

export function PeopleSaasToolbar(): JSX.Element {
	const filters = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getFilters(),
		[]
	);
	const sort = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getSort(),
		[]
	);
	const counts = useSelect(
		( select ) =>
			( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getCounts(
				toCountsQuery( filters )
			),
		[ filters.search, filters.department_id, filters.designation_id, filters.location_id ]
	);
	const { setFilters, setSort, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;
	const canCreate = useCan( 'erp_create_employee' );

	const [ searchInput, setSearchInput ] = useState( filters.search ?? '' );
	const [ filtersOpen, setFiltersOpen ] = useState( false );

	useEffect( () => {
		const id = window.setTimeout( () => {
			if ( ( searchInput || '' ) === ( filters.search || '' ) ) {
				return;
			}
			const { search: _drop, ...rest } = filters;
			void _drop;
			const next: EmployeeListQuery = searchInput
				? { ...rest, search: searchInput }
				: rest;
			setFilters( next );
			setPagination( { page: 1, perPage: 20 } );
		}, SEARCH_DEBOUNCE_MS );
		return () => window.clearTimeout( id );
	}, [ searchInput, filters, setFilters, setPagination ] );

	const activeStatus = filters.status ?? 'all';
	const activeSecondary =
		( filters.department_id  ? 1 : 0 ) +
		( filters.designation_id ? 1 : 0 ) +
		( filters.location_id    ? 1 : 0 );

	function countFor( value: StatusValue ): number | null {
		if ( ! counts ) {
			return null;
		}
		if ( value === 'all' ) {
			return counts.all;
		}
		return counts.by_status[ value ] ?? 0;
	}

	return (
		<div className="border-b border-slate-200 bg-white">
			<div className="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-6 pt-6">
				<div className="min-w-0">
					<nav aria-label={ __( 'Breadcrumb', 'erp' ) } className="flex items-center gap-1 text-xs text-slate-500">
						<span>{ __( 'HR', 'erp' ) }</span>
						<ChevronRight size={ 12 } aria-hidden="true" />
						<span className="text-slate-700">{ __( 'People', 'erp' ) }</span>
						<ChevronRight size={ 12 } aria-hidden="true" />
						<span className="text-slate-400">{ __( 'Directory', 'erp' ) }</span>
					</nav>
					<h1 className="mt-1 text-xl font-semibold tracking-tight text-slate-900">
						{ __( 'Directory', 'erp' ) }
					</h1>
					{ counts ? (
						<p className="mt-0.5 text-xs text-slate-500">
							{ sprintf( __( '%d people · updated just now', 'erp' ), counts.all ) }
						</p>
					) : null }
				</div>
				<div className="flex items-center gap-2">
					<button
						type="button"
						className="inline-flex h-9 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 shadow-sm hover:border-slate-300 hover:bg-slate-50"
					>
						<Download size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
						{ __( 'Export', 'erp' ) }
					</button>
					{ canCreate ? (
						<button
							type="button"
							onClick={ () => { window.location.hash = '#/employees/new'; } }
							className="inline-flex h-9 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-xs font-medium text-white shadow-sm hover:bg-slate-800"
						>
							<Plus size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
							{ __( 'Add person', 'erp' ) }
						</button>
					) : null }
				</div>
			</div>

			<div className="mx-auto mt-4 flex w-full max-w-7xl flex-wrap items-center justify-between gap-3 px-6 pb-3">
				<div role="tablist" aria-label={ __( 'Status', 'erp' ) } className="flex flex-wrap items-center gap-1">
					{ STATUS_CHIPS.map( ( chip ) => {
						const active = chip.value === activeStatus;
						const count  = countFor( chip.value );
						return (
							<button
								key={ chip.value }
								type="button"
								role="tab"
								aria-selected={ active }
								onClick={ () => {
									setFilters( { ...filters, status: chip.value } );
									setPagination( { page: 1, perPage: 20 } );
								} }
								className={ [
									'inline-flex h-7 items-center gap-1.5 rounded-md border px-2.5 text-xs font-medium transition-colors',
									active
										? 'border-slate-900 bg-slate-900 text-white'
										: 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900',
								].join( ' ' ) }
							>
								<span>{ chip.label }</span>
								{ count !== null ? (
									<span
										className={ [
											'rounded px-1 text-[10px] font-semibold tabular-nums',
											active ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600',
										].join( ' ' ) }
									>
										{ count }
									</span>
								) : null }
							</button>
						);
					} ) }
				</div>

				<div className="flex items-center gap-2">
					<div className="relative">
						<Search
							size={ 14 }
							aria-hidden="true"
							className="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400"
						/>
						<Input
							type="search"
							placeholder={ __( 'Search people…', 'erp' ) }
							value={ searchInput }
							onChange={ ( e ) => setSearchInput( e.target.value ) }
							className="h-9 w-64 rounded-md border-slate-200 bg-white pl-8 pr-10 text-xs text-slate-700 shadow-sm placeholder:text-slate-400 focus-visible:ring-slate-300"
						/>
						<kbd
							aria-hidden="true"
							className="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 rounded border border-slate-200 bg-slate-50 px-1 py-px text-[10px] font-medium text-slate-500"
						>
							⌘K
						</kbd>
					</div>

					<button
						type="button"
						aria-pressed={ filtersOpen || activeSecondary > 0 }
						onClick={ () => setFiltersOpen( ( prev ) => ! prev ) }
						className={ [
							'inline-flex h-9 items-center gap-1.5 rounded-md border px-2.5 text-xs font-medium shadow-sm transition-colors',
							filtersOpen || activeSecondary > 0
								? 'border-slate-900 bg-slate-900 text-white'
								: 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900',
						].join( ' ' ) }
					>
						<ListFilter size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
						<span>{ __( 'Filters', 'erp' ) }</span>
						{ activeSecondary > 0 ? (
							<span className="rounded bg-white/20 px-1 text-[10px] font-semibold">
								{ activeSecondary }
							</span>
						) : null }
					</button>

					<div className="inline-flex h-9 items-center gap-1 rounded-md border border-slate-200 bg-white px-2 text-xs font-medium text-slate-600 shadow-sm">
						<ArrowDownUp size={ 12 } strokeWidth={ 1.75 } aria-hidden="true" />
						<select
							value={ sort.orderby }
							onChange={ ( e ) =>
								setSort( {
									orderby: e.target.value as Orderby,
									order:   sort.order,
								} )
							}
							aria-label={ __( 'Sort by', 'erp' ) }
							className="h-8 cursor-pointer border-0 bg-transparent pr-1 text-xs font-medium text-slate-700 focus:outline-none focus:ring-0"
						>
							{ SORT_OPTIONS.map( ( opt ) => (
								<option key={ opt.value } value={ opt.value }>
									{ opt.label }
								</option>
							) ) }
						</select>
						<button
							type="button"
							onClick={ () =>
								setSort( { orderby: sort.orderby, order: sort.order === 'asc' ? 'desc' : 'asc' } )
							}
							aria-label={ __( 'Toggle sort direction', 'erp' ) }
							className="inline-flex size-6 items-center justify-center rounded text-slate-500 hover:bg-slate-100 hover:text-slate-700"
						>
							{ sort.order === 'asc' ? '↑' : '↓' }
						</button>
					</div>
				</div>
			</div>

			{ filtersOpen || activeSecondary > 0 ? (
				<div className="border-t border-slate-200 bg-slate-50">
					<div className="mx-auto flex w-full max-w-7xl flex-wrap items-center gap-3 px-6 py-3">
						<LookupFilter
							label={ __( 'Department', 'erp' ) }
							placeholder={ __( 'All departments', 'erp' ) }
							lookupKey="departments"
							field="department_id"
						/>
						<LookupFilter
							label={ __( 'Designation', 'erp' ) }
							placeholder={ __( 'All designations', 'erp' ) }
							lookupKey="designations"
							field="designation_id"
						/>
						<LookupFilter
							label={ __( 'Location', 'erp' ) }
							placeholder={ __( 'All locations', 'erp' ) }
							lookupKey="locations"
							field="location_id"
						/>
					</div>
				</div>
			) : null }
		</div>
	);
}
