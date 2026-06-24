/**
 * Build the column list for the Employees table.
 *
 * Free defaults + `wp.hooks.applyFilters('erp_hr.employees.columns', cols, ctx)`
 * so pro modules can append columns sourced from `row.extra[*]`.
 *
 * Memoized on `wp.hooks.didFilter(hook)` so pro filters registered after the
 * first render trigger exactly one rebuild.
 */

import { applyFilters, didFilter } from '@wordpress/hooks';
import { useMemo } from 'react';

import { CopyButton } from '@/shared/components/CopyButton';
import { __ } from '@/shared/i18n';
import type {
	ColumnContext,
	ColumnsFilter,
	EmployeeColumn,
} from '@/stores/employees';

import { COLUMN_IDS, EMPLOYEES_HOOKS } from './constants';
import { DepartmentCell } from './columns/DepartmentCell';
import { DesignationCell } from './columns/DesignationCell';
import { EmailCell } from './columns/EmailCell';
import { EmploymentTypeCell } from './columns/EmploymentTypeCell';
import { HireDateCell } from './columns/HireDateCell';
import { NameCell } from './columns/NameCell';
import { StatusCell } from './columns/StatusCell';
import { useColumnContext } from './useColumnContext';

export function useEmployeeColumns(): readonly EmployeeColumn[] {
	const { ctx, can } = useColumnContext();

	const baseColumns = useMemo< EmployeeColumn[] >(
		() => [
			{
				id:             COLUMN_IDS.NAME,
				label:          __( 'Employee', 'erp' ),
				priority:       10,
				defaultVisible: true,
				sortable:       true,
				filterable:     false,
				getValue:       ( row ) => row.full_name,
				render:         ( row ) => <NameCell row={ row } />,
			},
			{
				id:             COLUMN_IDS.EMPLOYEE_ID,
				label:          __( 'Employee ID', 'erp' ),
				priority:       15,
				defaultVisible: true,
				sortable:       false,
				filterable:     false,
				getValue:       ( row ) => row.employee_id ?? null,
				render:         ( row ) => (
					row.employee_id ? (
						<span className="group/cell inline-flex items-center gap-1 whitespace-nowrap">
							<span className="font-medium text-foreground">{ row.employee_id }</span>
							<CopyButton value={ row.employee_id } label={ __( 'Copy employee ID', 'erp' ) } />
						</span>
					) : (
						<span className="text-muted-foreground">—</span>
					)
				),
			},
			{
				id:             COLUMN_IDS.EMAIL,
				label:          __( 'Email', 'erp' ),
				priority:       20,
				defaultVisible: true,
				sortable:       true,
				filterable:     false,
				getValue:       ( row ) => row.email,
				render:         ( row ) => <EmailCell row={ row } />,
			},
			{
				id:             COLUMN_IDS.DEPARTMENT,
				label:          __( 'Department', 'erp' ),
				priority:       30,
				defaultVisible: true,
				sortable:       false,
				filterable:     true,
				getValue:       ( row ) => row.department?.name ?? null,
				render:         ( row ) => <DepartmentCell row={ row } />,
			},
			{
				id:             COLUMN_IDS.DESIGNATION,
				// Hidden by default — the designation already shows as the subtitle
				// under the employee name (NameCell), so a dedicated column duplicates
				// it. Still available via the column toggle + as a filter.
				label:          __( 'Designation', 'erp' ),
				priority:       40,
				defaultVisible: false,
				sortable:       false,
				filterable:     true,
				getValue:       ( row ) => row.designation?.name ?? null,
				render:         ( row ) => <DesignationCell row={ row } />,
			},
			{
				id:             COLUMN_IDS.LOCATION,
				label:          __( 'Location', 'erp' ),
				priority:       45,
				defaultVisible: true,
				sortable:       false,
				filterable:     false,
				getValue:       ( row ) => row.location?.name ?? null,
				render:         ( row ) => (
					<span className="whitespace-nowrap text-sm text-foreground">{ row.location?.name || '—' }</span>
				),
			},
			{
				id:             COLUMN_IDS.PHONE,
				label:          __( 'Phone', 'erp' ),
				priority:       55,
				defaultVisible: true,
				sortable:       false,
				filterable:     false,
				getValue:       ( row ) => row.phone ?? null,
				render:         ( row ) => (
					row.phone ? (
						<span className="group/cell inline-flex items-center gap-1 whitespace-nowrap">
							<a href={ `tel:${ row.phone }` } className="text-sm text-foreground hover:text-primary hover:underline">{ row.phone }</a>
							<CopyButton value={ row.phone } label={ __( 'Copy phone number', 'erp' ) } />
						</span>
					) : (
						<span className="text-muted-foreground">—</span>
					)
				),
			},
			{
				id:             COLUMN_IDS.EMPLOYMENT_TYPE,
				label:          __( 'Employment Type', 'erp' ),
				priority:       48,
				defaultVisible: true,
				sortable:       false,
				filterable:     false,
				getValue:       ( row ) => row.employee_type ?? null,
				render:         ( row ) => <EmploymentTypeCell row={ row } />,
			},
			{
				id:             COLUMN_IDS.STATUS,
				label:          __( 'Status', 'erp' ),
				priority:       50,
				defaultVisible: true,
				sortable:       true,
				filterable:     true,
				getValue:       ( row ) => row.status ?? null,
				render:         ( row ) => <StatusCell row={ row } />,
			},
			{
				id:             COLUMN_IDS.HIRE_DATE,
				label:          __( 'Hire date', 'erp' ),
				priority:       60,
				defaultVisible: true,
				sortable:       true,
				filterable:     false,
				getValue:       ( row ) => row.hire_date ?? null,
				render:         ( row ) => <HireDateCell row={ row } />,
			},
		],
		[]
	);

	const filterVersion = didFilter( EMPLOYEES_HOOKS.COLUMNS );

	return useMemo( () => {
		const filter = applyFilters as unknown as (
			name: string,
			value: EmployeeColumn[],
			context: ColumnContext
		) => ReturnType< ColumnsFilter >;
		const merged = filter( EMPLOYEES_HOOKS.COLUMNS, [ ...baseColumns ], ctx );
		return [ ...merged ]
			.filter( ( col ) => col.defaultVisible !== false )
			.filter( ( col ) => ! col.capability || can( col.capability ) )
			.sort( ( a, b ) => a.priority - b.priority );
	}, [ baseColumns, ctx, filterVersion, can ] );
}
