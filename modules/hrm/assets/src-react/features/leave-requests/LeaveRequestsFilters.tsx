/**
 * Collapsible secondary filter row for `/leave/requests` — leave type, year,
 * department, designation and employment type. Controlled: every value + its
 * option list is supplied by the page, which owns the query state.
 */

import { SmartSelect } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

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
		</div>
	);
}
