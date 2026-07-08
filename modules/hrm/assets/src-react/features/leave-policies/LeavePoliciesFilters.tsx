/**
 * Collapsible filter bar for the leave-policies list: financial-year,
 * department and employee-type selects. Option lists are derived from the
 * shared form-options payload; the page owns the values and toggle state.
 */

import { Button, SmartSelect } from '@wedevs/plugin-ui';
import { X } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import type { PolicyFormOptions } from './types';

interface LeavePoliciesFiltersProps {
	readonly options:          PolicyFormOptions | null;
	readonly fYear:            number;
	readonly departmentId:     number;
	readonly employeeType:     string;
	readonly onFYear:          ( value: number ) => void;
	readonly onDepartment:     ( value: number ) => void;
	readonly onEmployeeType:   ( value: string ) => void;
}

export function LeavePoliciesFilters( {
	options,
	fYear,
	departmentId,
	employeeType,
	onFYear,
	onDepartment,
	onEmployeeType,
}: LeavePoliciesFiltersProps ): JSX.Element {
	const fYearFilterOpts = [
		{ value: '', label: __( 'All Years', 'erp' ) },
		...( options?.financialYears ?? [] ).map( ( y ) => ( { value: String( y.id ), label: y.label } ) ),
	];
	const deptFilterOpts = [
		{ value: '', label: __( 'All Departments', 'erp' ) },
		...( options?.departments ?? [] ).map( ( d ) => ( { value: String( d.id ), label: d.label } ) ),
	];
	const empTypeFilterOpts = [
		{ value: '', label: __( 'All Types', 'erp' ) },
		...( options?.employeeTypes ?? [] ).map( ( o ) => ( { value: o.value, label: o.label } ) ),
	];

	const hasActiveFilters = Boolean( fYear || departmentId || employeeType );
	const clearFilters = (): void => {
		onFYear( 0 );
		onDepartment( 0 );
		onEmployeeType( '' );
	};

	return (
		<div className="flex flex-wrap items-center gap-2 border-b border-border bg-muted/20 px-4 py-3">
			<label className="flex items-center gap-2 text-sm text-muted-foreground">
				{ __( 'Year', 'erp' ) }
				<SmartSelect
					options={ fYearFilterOpts }
					value={ String( fYear || '' ) }
					onValueChange={ ( v ) => onFYear( Number( v || 0 ) ) }
					placeholder={ __( 'All Years', 'erp' ) }
					showClear
					className="h-9 w-40 bg-background"
					contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
				/>
			</label>
			<label className="flex items-center gap-2 text-sm text-muted-foreground">
				{ __( 'Department', 'erp' ) }
				<SmartSelect
					options={ deptFilterOpts }
					value={ String( departmentId || '' ) }
					onValueChange={ ( v ) => onDepartment( Number( v || 0 ) ) }
					placeholder={ __( 'All Departments', 'erp' ) }
					showClear
					className="h-9 w-48 bg-background"
					contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
				/>
			</label>
			<label className="flex items-center gap-2 text-sm text-muted-foreground">
				{ __( 'Employee Type', 'erp' ) }
				<SmartSelect
					options={ empTypeFilterOpts }
					value={ employeeType }
					onValueChange={ ( v ) => onEmployeeType( v ?? '' ) }
					placeholder={ __( 'All Types', 'erp' ) }
					showClear
					className="h-9 w-40 bg-background"
					contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
				/>
			</label>
			{ hasActiveFilters ? (
				<Button
					type="button"
					variant="outline"
					size="sm"
					onClick={ clearFilters }
					className="ml-auto h-9 gap-1.5 border-border bg-card text-sm text-muted-foreground hover:bg-muted hover:text-foreground"
				>
					<X size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
					{ __( 'Clear', 'erp' ) }
				</Button>
			) : null }
		</div>
	);
}
