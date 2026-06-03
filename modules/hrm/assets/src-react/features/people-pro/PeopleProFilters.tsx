/**
 * Toolbar inside the table card.
 *
 * Layout:
 *   [Segmented status tabs]                [Search] [Filters] [Density]
 *
 * Tokens-only styling. Segmented tabs use the design system's underline
 * pattern; counts render inline in muted text.
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

const STATUS_TABS: ReadonlyArray< { readonly value: StatusValue; readonly label: string } > = [
	{ value: 'all',        label: __( 'All',        'erp' ) },
	{ value: 'active',     label: __( 'Active',     'erp' ) },
	{ value: 'inactive',   label: __( 'Inactive',   'erp' ) },
	{ value: 'terminated', label: __( 'Terminated', 'erp' ) },
	{ value: 'trash',      label: __( 'Trash',      'erp' ) },
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

export function PeopleProFilters(): JSX.Element {
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
		<div className="border-b border-border">
			<div className="flex flex-wrap items-center justify-between gap-3 px-4 pt-3">
				<div
					role="tablist"
					aria-label={ __( 'Status', 'erp' ) }
					className="flex items-stretch"
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
									'relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium transition-colors',
									active
										? 'text-foreground'
										: 'text-muted-foreground hover:text-foreground',
								].join( ' ' ) }
							>
								<span>{ tab.label }</span>
								{ count !== null ? (
									<span className="text-xs font-normal text-muted-foreground">
										({ count })
									</span>
								) : null }
								<span
									aria-hidden="true"
									className={ [
										'absolute inset-x-0 -bottom-px h-0.5',
										active ? 'bg-primary' : 'bg-transparent',
									].join( ' ' ) }
								/>
							</button>
						);
					} ) }
				</div>

				<div className="flex items-center gap-2 pb-2">
					<div className="relative">
						<Search
							size={ 16 }
							aria-hidden="true"
							className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
						/>
						<Input
							type="search"
							placeholder={ __( 'Search members…', 'erp' ) }
							value={ searchInput }
							onChange={ ( e ) => setSearchInput( e.target.value ) }
							className="h-9 w-64 rounded-md border-border bg-background pl-9 pr-3 text-sm"
						/>
					</div>
					<button
						type="button"
						aria-pressed={ filtersOpen || activeSecondary > 0 }
						onClick={ () => setFiltersOpen( ( prev ) => ! prev ) }
						className={ [
							'inline-flex h-9 items-center gap-2 rounded-md border bg-card px-3 text-sm font-medium transition-colors',
							filtersOpen || activeSecondary > 0
								? 'border-primary text-primary'
								: 'border-border text-muted-foreground hover:text-foreground',
						].join( ' ' ) }
					>
						<Filter size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
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
							className="inline-flex h-9 items-center gap-1.5 rounded-md px-2 text-xs font-medium text-muted-foreground hover:text-foreground"
						>
							<X size={ 14 } aria-hidden="true" />
							{ __( 'Reset', 'erp' ) }
						</button>
					) : null }
				</div>
			</div>

			{ filtersOpen || activeSecondary > 0 ? (
				<div className="flex flex-wrap items-center gap-3 border-t border-border bg-muted/30 px-4 py-3">
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
