/**
 * Collapsible secondary filter row for `/leave/requests` — leave type, year,
 * department, designation and employment type. Controlled: every value + its
 * option list is supplied by the page, which owns the query state.
 */

import { Button, SmartSelect } from '@wedevs/plugin-ui';
import { X } from 'lucide-react';
import type { JSX } from 'react';

import { DateRangeField } from '@/shared/DateRangeField';
import { __ } from '@/shared/i18n';

interface Option {
	readonly value: string;
	readonly label: string;
}

interface LeaveRequestsFiltersProps {
	readonly leaveId: number;
	readonly onLeaveId: ( v: number ) => void;
	readonly year: number;
	readonly onYear: ( v: number ) => void;
	readonly departmentId: number;
	readonly onDepartmentId: ( v: number ) => void;
	readonly designationId: number;
	readonly onDesignationId: ( v: number ) => void;
	readonly employmentType: string;
	readonly onEmploymentType: ( v: string ) => void;
	readonly startDate: string;
	readonly onStartDate: ( v: string ) => void;
	readonly endDate: string;
	readonly onEndDate: ( v: string ) => void;
	readonly leaveTypeOptions: Option[];
	readonly yearOptions: Option[];
	readonly departmentOptions: Option[];
	readonly designationOptions: Option[];
	readonly employmentTypeOptions: Option[];
}

export function LeaveRequestsFilters( {
	leaveId,
	onLeaveId,
	year,
	onYear,
	departmentId,
	onDepartmentId,
	designationId,
	onDesignationId,
	employmentType,
	onEmploymentType,
	startDate,
	onStartDate,
	endDate,
	onEndDate,
	leaveTypeOptions,
	yearOptions,
	departmentOptions,
	designationOptions,
	employmentTypeOptions,
}: LeaveRequestsFiltersProps ): JSX.Element {
	const hasActiveFilters = Boolean(
		leaveId || year || departmentId || designationId || employmentType || startDate || endDate
	);
	const clearFilters = (): void => {
		onLeaveId( 0 );
		onYear( 0 );
		onDepartmentId( 0 );
		onDesignationId( 0 );
		onEmploymentType( '' );
		onStartDate( '' );
		onEndDate( '' );
	};

	return (
		<div className="flex flex-wrap items-center gap-2 border-b border-border bg-muted/20 px-4 py-3">
			<label className="flex items-center gap-2 text-sm text-muted-foreground">
				{ __( 'Leave Type', 'erp' ) }
				<SmartSelect
					options={ leaveTypeOptions }
					value={ String( leaveId || '' ) }
					onValueChange={ ( v ) => onLeaveId( Number( v || 0 ) ) }
					placeholder={ __( 'All Types', 'erp' ) }
					showClear
					className="h-9 w-48 bg-background"
					contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
				/>
			</label>
			<label className="flex items-center gap-2 text-sm text-muted-foreground">
				{ __( 'Year', 'erp' ) }
				<SmartSelect
					options={ yearOptions }
					value={ String( year || '' ) }
					onValueChange={ ( v ) => onYear( Number( v || 0 ) ) }
					placeholder={ __( 'All Years', 'erp' ) }
					showClear
					className="h-9 w-36 bg-background"
					contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
				/>
			</label>
			<label className="flex items-center gap-2 text-sm text-muted-foreground">
				{ __( 'Department', 'erp' ) }
				<SmartSelect
					options={ departmentOptions }
					value={ String( departmentId || '' ) }
					onValueChange={ ( v ) =>
						onDepartmentId( Number( v || 0 ) )
					}
					placeholder={ __( 'All Departments', 'erp' ) }
					searchPlaceholder={ __( 'Search…', 'erp' ) }
					showClear
					className="h-9 w-48 bg-background"
					contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
				/>
			</label>
			<label className="flex items-center gap-2 text-sm text-muted-foreground">
				{ __( 'Designation', 'erp' ) }
				<SmartSelect
					options={ designationOptions }
					value={ String( designationId || '' ) }
					onValueChange={ ( v ) =>
						onDesignationId( Number( v || 0 ) )
					}
					placeholder={ __( 'All Designations', 'erp' ) }
					searchPlaceholder={ __( 'Search…', 'erp' ) }
					showClear
					className="h-9 w-48 bg-background"
					contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
				/>
			</label>
			<label className="flex items-center gap-2 text-sm text-muted-foreground">
				{ __( 'Type', 'erp' ) }
				<SmartSelect
					options={ employmentTypeOptions }
					value={ employmentType }
					onValueChange={ ( v ) => onEmploymentType( v || '' ) }
					placeholder={ __( 'All Employment Types', 'erp' ) }
					showClear
					className="h-9 w-48 bg-background"
					contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
				/>
			</label>
			<label className="flex items-center gap-2 text-sm text-muted-foreground">
				{ __( 'Date range', 'erp' ) }
				<DateRangeField
					value={ { from: startDate, to: endDate } }
					onChange={ ( r ) => {
						onStartDate( r.from );
						onEndDate( r.to );
					} }
					className="w-64 bg-background"
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
