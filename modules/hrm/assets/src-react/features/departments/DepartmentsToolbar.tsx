/**
 * Department table toolbar: the "All (count)" tab, a search field, and a Filter
 * toggle with an expandable Parent-department filter panel. Presentational —
 * search/filter state lives in the page orchestrator.
 */

import { Input, SmartSelect } from '@wedevs/plugin-ui';
import { Filter, Search } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

interface DepartmentsToolbarProps {
	readonly count:             number;
	readonly search:            string;
	readonly onSearch:          ( value: string ) => void;
	readonly onToggleFilters:   () => void;
	readonly filterButtonActive: boolean;
	readonly activeFilterCount: number;
	readonly parentOptions:     readonly string[];
	readonly parentFilter:      string;
	readonly onParentFilter:    ( value: string ) => void;
}

export function DepartmentsToolbar( {
	count,
	search,
	onSearch,
	onToggleFilters,
	filterButtonActive,
	activeFilterCount,
	parentOptions,
	parentFilter,
	onParentFilter,
}: DepartmentsToolbarProps ): JSX.Element {
	return (
		<>
			<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
				<div role="tablist" aria-label={ __( 'Departments', 'erp' ) } className="flex items-stretch">
					<span role="tab" aria-selected="true" className="relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium text-primary">
						<span>{ __( 'All', 'erp' ) }</span>
						<span className="font-normal text-[#a5a5aa]">({ count })</span>
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
							placeholder={ __( 'Search departments…', 'erp' ) }
							className="h-9 w-60 rounded-md border-border pl-9 text-sm"
							aria-label={ __( 'Search departments', 'erp' ) }
						/>
					</div>
					<button
						type="button"
						aria-label={ __( 'Toggle filters', 'erp' ) }
						aria-pressed={ filterButtonActive }
						onClick={ onToggleFilters }
						className={ [
							'relative inline-flex items-center justify-center gap-1 transition-colors',
							filterButtonActive
								? 'border-primary text-primary'
								: 'border-border text-muted-foreground hover:text-foreground',
						].join( ' ' ) }
					>
						<Filter size={ 20 } strokeWidth={ 1.75 } aria-hidden="true" />
						{ activeFilterCount > 0 ? (
							<span className="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-medium text-primary-foreground">
								{ activeFilterCount }
							</span>
						) : null }
					</button>
				</div>
			</div>

			{ filterButtonActive ? (
				<div className="flex flex-wrap items-center gap-2 border-b border-border bg-muted/20 px-4 py-3">
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Parent', 'erp' ) }
						<SmartSelect
							options={ parentOptions.map( ( p ) => ( { value: p, label: p } ) ) }
							value={ parentFilter }
							onValueChange={ ( v ) => onParentFilter( v ?? '' ) }
							placeholder={ __( 'All departments', 'erp' ) }
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
