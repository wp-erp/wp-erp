/**
 * Modern filter strip — segmented status pills + search input + filter pop-down.
 *
 * Style departure from the legacy underline-tab pattern:
 *   - Status uses a unified segmented control (rounded-full container, pill
 *     buttons with inline counts).
 *   - Search uses a soft "input bar" with a keyboard shortcut hint.
 *   - Secondary filters reveal as a popover row below.
 */

import { Input } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { Filter, Search, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName, toCountsQuery } from '@/stores/employees';
import type {
	EmployeeCountsQuery,
	EmployeeListQuery,
	EmployeeStatusCounts,
	EmployeesState,
} from '@/stores/employees';

import { LookupFilter } from '../employees/filters/LookupFilter';

type StatusValue = NonNullable< EmployeeListQuery[ 'status' ] >;

interface StatusTab {
	readonly value:        StatusValue;
	readonly label:        string;
	/** Pill tone applied only when this tab is the selected status. */
	readonly pillSelected: string;
}

const PILL_IDLE = 'bg-white/60 text-muted-foreground';

const STATUS_TABS: ReadonlyArray< StatusTab > = [
	{ value: 'all',        label: __( 'All',        'erp' ), pillSelected: 'bg-slate-500/20 text-slate-800' },
	{ value: 'active',     label: __( 'Active',     'erp' ), pillSelected: 'bg-emerald-500/20 text-emerald-800' },
	{ value: 'inactive',   label: __( 'Inactive',   'erp' ), pillSelected: 'bg-rose-500/20 text-rose-800' },
	{ value: 'terminated', label: __( 'Terminated', 'erp' ), pillSelected: 'bg-rose-600/25 text-rose-900' },
	{ value: 'trash',      label: __( 'Trash',      'erp' ), pillSelected: 'bg-slate-400/20 text-slate-700' },
];

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
	getCounts:  ( query: EmployeeCountsQuery ) => EmployeeStatusCounts | null;
}

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

const SEARCH_DEBOUNCE_MS = 250;

export function PeopleReviewFilters(): JSX.Element {
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

	const [ searchInput,  setSearchInput  ] = useState( filters.search ?? '' );
	const [ filtersOpen,  setFiltersOpen  ] = useState( false );

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

	const hasAnyFilter =
		Boolean( filters.search ) || activeSecondary > 0 || activeStatus !== 'all';

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
		<div className="space-y-3">
			<div className="flex flex-wrap items-center justify-between gap-3">
				<div
					role="tablist"
					aria-label={ __( 'Status', 'erp' ) }
					className="inline-flex items-center gap-1 rounded-full border border-white/40 bg-white/40 p-1 ring-1 ring-white/50 backdrop-blur-xl backdrop-saturate-150"
				>
					{ STATUS_TABS.map( ( tab ) => {
						const active = tab.value === activeStatus;
						const count  = countFor( tab.value );
						return (
							<button
								key={ tab.value }
								type="button"
								role="tab"
								aria-selected={ active }
								onClick={ () => {
									setFilters( { ...filters, status: tab.value } );
									setPagination( { page: 1, perPage: 20 } );
								} }
								className={ [
									'relative inline-flex h-8 items-center gap-1.5 rounded-full px-3 text-xs font-medium transition-all',
									active
										? 'bg-white/80 text-foreground shadow-sm ring-1 ring-white/60 backdrop-blur'
										: 'text-muted-foreground hover:bg-white/30 hover:text-foreground',
								].join( ' ' ) }
							>
								<span>{ tab.label }</span>
								{ count !== null ? (
									<span
										className={ [
											'inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-semibold tabular-nums',
											active ? tab.pillSelected : PILL_IDLE,
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
							size={ 16 }
							aria-hidden="true"
							className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
						/>
						<Input
							type="search"
							placeholder={ __( 'Search people…', 'erp' ) }
							value={ searchInput }
							onChange={ ( e ) => setSearchInput( e.target.value ) }
							className="h-10 w-72 rounded-full border-white/40 bg-white/60 pl-9 pr-16 text-sm shadow-sm ring-1 ring-white/40 backdrop-blur-xl focus-visible:ring-primary/40"
						/>
						<kbd
							aria-hidden="true"
							className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded border border-white/50 bg-white/70 px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground backdrop-blur"
						>
							⌘K
						</kbd>
					</div>

					<button
						type="button"
						aria-pressed={ filtersOpen || activeSecondary > 0 }
						onClick={ () => setFiltersOpen( ( prev ) => ! prev ) }
						className={ [
							'inline-flex h-10 items-center gap-2 rounded-full border bg-white/60 px-4 text-sm font-medium shadow-sm ring-1 backdrop-blur-xl transition-all',
							filtersOpen || activeSecondary > 0
								? 'border-primary/50 text-primary ring-primary/30'
								: 'border-white/40 text-muted-foreground ring-white/40 hover:text-foreground',
						].join( ' ' ) }
					>
						<Filter size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
						<span>{ __( 'Filters', 'erp' ) }</span>
						{ activeSecondary > 0 ? (
							<span className="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-[10px] font-semibold text-primary-foreground">
								{ activeSecondary }
							</span>
						) : null }
					</button>

					{ hasAnyFilter ? (
						<button
							type="button"
							onClick={ () => {
								setFilters( { status: 'all' } );
								setSearchInput( '' );
								setPagination( { page: 1, perPage: 20 } );
							} }
							className="inline-flex h-10 items-center gap-1.5 rounded-full px-3 text-xs font-medium text-muted-foreground hover:text-foreground"
						>
							<X size={ 14 } aria-hidden="true" />
							{ __( 'Reset', 'erp' ) }
						</button>
					) : null }
				</div>
			</div>

			{ filtersOpen || activeSecondary > 0 ? (
				<div className="flex flex-wrap items-center gap-3 rounded-2xl border border-white/40 bg-white/40 px-4 py-3 ring-1 ring-white/40 backdrop-blur-xl backdrop-saturate-150">
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
			) : null }
		</div>
	);
}
