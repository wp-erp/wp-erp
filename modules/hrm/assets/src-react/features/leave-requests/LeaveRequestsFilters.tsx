/**
 * Collapsible secondary filter row for `/leave/requests` — leave type, year,
 * department, designation and employment type. Controlled: every value + its
 * option list is supplied by the page, which owns the query state.
 */

import { SmartSelect } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { DateField } from '@/shared/DateField';
import { __ } from '@/shared/i18n';

interface Option {
	readonly value: string;
	readonly label: string;
}

/** Relative date-range presets — mirror the legacy `filter_leave_year` select. */
export const DATE_PRESET_OPTIONS: Option[] = [
	{ value: '', label: __( 'Filter by date', 'erp' ) },
	{ value: '1', label: __( 'Last week', 'erp' ) },
	{ value: '2', label: __( 'Last month', 'erp' ) },
	{ value: '3', label: __( 'Last 3 months', 'erp' ) },
	{ value: 'custom', label: __( 'Custom', 'erp' ) },
];

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
	readonly datePreset: string;
	readonly onDatePreset: ( v: string ) => void;
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
	datePreset,
	onDatePreset,
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
				<SmartSelect
					options={ DATE_PRESET_OPTIONS }
					value={ datePreset }
					onValueChange={ ( v ) => onDatePreset( v || '' ) }
					placeholder={ __( 'Filter by date', 'erp' ) }
					showClear
					className="h-9 w-44 bg-background"
					contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
				/>
			</label>
			{ datePreset === 'custom' ? (
				<>
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'From', 'erp' ) }
						<DateField
							value={ startDate }
							onChange={ onStartDate }
							max={ endDate || undefined }
							className="h-9 w-40 bg-background"
						/>
					</label>
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'To', 'erp' ) }
						<DateField
							value={ endDate }
							onChange={ onEndDate }
							min={ startDate || undefined }
							className="h-9 w-40 bg-background"
						/>
					</label>
				</>
			) : null }
		</div>
	);
}
