/**
 * Designations list toolbar: the "All (n)" tab, search box, filter toggle and
 * the collapsible Employees filter panel. Pure presentation — state and
 * handlers come from `DesignationsPage`.
 */

import { Input, SmartSelect } from '@wedevs/plugin-ui';
import { Filter, Search } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

interface DesignationsToolbarProps {
	readonly total:              number;
	readonly search:             string;
	readonly onSearch:           ( value: string ) => void;
	readonly filterButtonActive: boolean;
	readonly activeFilterCount:  number;
	readonly onToggleFilters:    () => void;
	readonly employeesFilter:    '' | 'with' | 'without';
	readonly onEmployeesFilter:  ( value: '' | 'with' | 'without' ) => void;
}

export function DesignationsToolbar( {
	total,
	search,
	onSearch,
	filterButtonActive,
	activeFilterCount,
	onToggleFilters,
	employeesFilter,
	onEmployeesFilter,
}: DesignationsToolbarProps ): JSX.Element {
	return (
		<>
			<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
				<div role="tablist" aria-label={ __( 'Designations', 'erp' ) } className="flex items-stretch">
					<span role="tab" aria-selected="true" className="relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium text-primary">
						<span>{ __( 'All', 'erp' ) }</span>
						<span className="font-normal text-[#a5a5aa]">({ total })</span>
						<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
					</span>
				</div>
				<div className="flex items-center gap-3">
					<div className="relative">
						<Search
							size={ 16 }
							aria-hidden="true"
							className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
						/>
						<Input
							type="search"
							value={ search }
							onChange={ ( e ) => onSearch( e.target.value ) }
							placeholder={ __( 'Search designations…', 'erp' ) }
							className="h-9 w-60 rounded-md border-border pl-9 text-sm"
							aria-label={ __( 'Search designations', 'erp' ) }
						/>
					</div>
					<button
						type="button"
						aria-label={ __( 'Toggle filters', 'erp' ) }
						aria-pressed={ filterButtonActive }
						onClick={ onToggleFilters }
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

			{ filterButtonActive ? (
				<div className="flex flex-wrap items-center gap-2 border-b border-border bg-muted/20 px-4 py-3">
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Employees', 'erp' ) }
						<SmartSelect
							options={ [
								{ value: 'with', label: __( 'With employees', 'erp' ) },
								{ value: 'without', label: __( 'Without employees', 'erp' ) },
							] }
							value={ employeesFilter }
							onValueChange={ ( v ) => onEmployeesFilter( ( v ?? '' ) as '' | 'with' | 'without' ) }
							placeholder={ __( 'All', 'erp' ) }
							searchPlaceholder={ __( 'Search…', 'erp' ) }
							emptyMessage={ __( 'No matches found.', 'erp' ) }
							showClear
							className="h-9 w-52 bg-background"
							contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
						/>
					</label>
				</div>
			) : null }
		</>
	);
}
