/**
 * Filters row — status tabs on the left + search input + filter funnel button
 * on the right. Department / Designation / Location filters live in a
 * collapsible secondary row revealed by the funnel button (Dokan-parity
 * pattern from `AdminDataViewTable.tsx`).
 *
 * Pro filters from `erp_hr.employees.filters` render at the end of the
 * secondary row when revealed.
 */

import { Input } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { Filter, Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeListQuery,
	EmployeesState,
} from '@/stores/employees';

import { SEARCH_DEBOUNCE_MS } from './constants';
import { DepartmentFilter } from './filters/DepartmentFilter';
import { DesignationFilter } from './filters/DesignationFilter';
import { EmployeeTypeFilter } from './filters/EmployeeTypeFilter';
import { LocationFilter } from './filters/LocationFilter';
import { StatusFilter } from './filters/StatusFilter';
import { useEmployeeFilters } from './useEmployeeFilters';

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
}

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

export function EmployeesFilters(): JSX.Element {
	const filters = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getFilters(),
		[]
	);
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const [ searchInput, setSearchInput ] = useState( filters.search ?? '' );
	const [ showFilters, setShowFilters ] = useState( false );
	const proFilters = useEmployeeFilters();

	const activeFilterCount =
		( filters.department_id  ? 1 : 0 ) +
		( filters.designation_id ? 1 : 0 ) +
		( filters.location_id    ? 1 : 0 ) +
		( filters.employee_type  ? 1 : 0 );
	const hasActiveSecondaryFilters = activeFilterCount > 0;

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

	const filterButtonActive = showFilters || hasActiveSecondaryFilters;

	return (
		<div>
			<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
				<StatusFilter />
				<div className="flex items-center gap-3">
					<div className="relative">
						<Search
							size={ 16 }
							aria-hidden="true"
							className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
						/>
						<Input
							type="search"
							placeholder={ __( 'Search', 'erp' ) }
							value={ searchInput }
							onChange={ ( e ) => setSearchInput( e.target.value ) }
							className="h-9 w-60 rounded-md border-border pl-9 text-sm"
						/>
					</div>
					<button
						type="button"
						aria-label={ __( 'Toggle filters', 'erp' ) }
						aria-pressed={ filterButtonActive }
						onClick={ () => setShowFilters( ( prev ) => ! prev ) }
						className={ [
							'relative inline-flex size-5 items-center justify-center transition-colors',
							filterButtonActive
								? 'text-primary'
								: 'text-muted-foreground hover:text-foreground',
						].join( ' ' ) }
					>
						<Filter size={ 20 } strokeWidth={ 1.75 } aria-hidden="true" />
						{ activeFilterCount > 0 ? (
							<span className="absolute -right-1.5 -top-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground">
								{ activeFilterCount }
							</span>
						) : null }
					</button>
				</div>
			</div>

			{ showFilters || hasActiveSecondaryFilters ? (
				<div className="flex flex-wrap items-center gap-2 border-b border-border bg-muted/20 px-4 py-3">
					<DepartmentFilter />
					<DesignationFilter />
					<LocationFilter />
					<EmployeeTypeFilter />
					{ proFilters.map( ( f ) => (
						<f.Component
							key={ f.id }
							value={ f.fromQuery( filters ) }
							onChange={ ( next ) => {
								setFilters( { ...filters, ...f.toQuery( next ) } );
								setPagination( { page: 1, perPage: 20 } );
							} }
						/>
					) ) }
				</div>
			) : null }
		</div>
	);
}
