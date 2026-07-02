/**
 * Toolbar for the `/leave/entitlements` list: the "All (count)" tab, employee
 * search, and a collapsible Policy filter. Filter state is owned by the page;
 * this renders the controls and derives the active-filter badge.
 */

import { Input, SmartSelect } from '@wedevs/plugin-ui';
import { Filter, Search } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import type { FinancialYearOption, IdOption, StringOption } from './types';

interface LeaveEntitlementsFiltersProps {
	readonly total:           number;
	readonly search:          string;
	readonly onSearch:        ( value: string ) => void;
	readonly policyId:        number;
	readonly onPolicyId:      ( value: number ) => void;
	readonly year:            number;
	readonly onYear:          ( value: number ) => void;
	readonly financialYears:  readonly FinancialYearOption[];
	readonly employeeType:    string;
	readonly onEmployeeType:  ( value: string ) => void;
	readonly employeeTypes:   readonly StringOption[];
	readonly showFilters:     boolean;
	readonly onToggleFilters: () => void;
	readonly policies:        readonly IdOption[];
}

export function LeaveEntitlementsFilters( {
	total,
	search,
	onSearch,
	policyId,
	onPolicyId,
	year,
	onYear,
	financialYears,
	employeeType,
	onEmployeeType,
	employeeTypes,
	showFilters,
	onToggleFilters,
	policies,
}: LeaveEntitlementsFiltersProps ): JSX.Element {
	const policyFilterOpts = [
		{ value: '', label: __( 'All Policies', 'erp' ) },
		...policies.map( ( p ) => ( { value: String( p.value ), label: p.label } ) ),
	];

	const yearFilterOpts = [
		{ value: '', label: __( 'All Years', 'erp' ) },
		...financialYears.map( ( fy ) => ( { value: String( fy.id ), label: fy.label } ) ),
	];

	const employeeTypeOpts = [
		{ value: '', label: __( 'All Employee Types', 'erp' ) },
		...employeeTypes.map( ( t ) => ( { value: t.value, label: t.label } ) ),
	];

	const activeFilterCount  = ( policyId ? 1 : 0 ) + ( year ? 1 : 0 ) + ( employeeType ? 1 : 0 );
	const filterButtonActive = showFilters || activeFilterCount > 0;

	return (
		<>
			<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
				<div role="tablist" aria-label={ __( 'Leave Entitlements', 'erp' ) } className="flex items-stretch">
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
							placeholder={ __( 'Search employees…', 'erp' ) }
							className="h-9 w-56 rounded-md border-border pl-9 text-sm"
							aria-label={ __( 'Search entitlements by employee', 'erp' ) }
						/>
					</div>
					<button
						type="button"
						aria-label={ __( 'Toggle filters', 'erp' ) }
						aria-pressed={ filterButtonActive }
						onClick={ onToggleFilters }
						className={ [
							'relative inline-flex size-5 items-center justify-center transition-colors',
							filterButtonActive ? 'text-primary' : 'text-muted-foreground hover:text-foreground',
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
				<div className="flex flex-wrap items-center gap-4 border-b border-border bg-muted/20 px-4 py-3">
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Year', 'erp' ) }
						<SmartSelect
							options={ yearFilterOpts }
							value={ String( year || '' ) }
							onValueChange={ ( v ) => onYear( Number( v || 0 ) ) }
							placeholder={ __( 'All Years', 'erp' ) }
							showClear
							className="h-9 w-44 bg-background"
							contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
						/>
					</label>
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Employee Type', 'erp' ) }
						<SmartSelect
							options={ employeeTypeOpts }
							value={ employeeType || '' }
							onValueChange={ ( v ) => onEmployeeType( v || '' ) }
							placeholder={ __( 'All Employee Types', 'erp' ) }
							showClear
							className="h-9 w-48 bg-background"
							contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
						/>
					</label>
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Policy', 'erp' ) }
						<SmartSelect
							options={ policyFilterOpts }
							value={ String( policyId || '' ) }
							onValueChange={ ( v ) => onPolicyId( Number( v || 0 ) ) }
							placeholder={ __( 'All Policies', 'erp' ) }
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
