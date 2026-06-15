/**
 * Employment-type filter — string-keyed (unlike the numeric Department /
 * Designation / Location lookups), so it can't reuse `LookupFilter`. Options
 * are the static `erp_hr_get_employee_types()` slugs shared with the employee
 * create/edit form (`employee-create/options.ts`). Sets `filters.employee_type`
 * which the v2 controller maps onto the legacy `type` arg.
 */

import { SmartSelect } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import type { JSX } from 'react';

import { TYPE_OPTIONS } from '@/features/employee-create/options';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeListQuery,
	EmployeesState,
} from '@/stores/employees';

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
}

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

export function EmployeeTypeFilter(): JSX.Element {
	const filters = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getFilters(),
		[]
	);
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const value = filters.employee_type ?? '';

	return (
		<div className="flex items-center gap-2">
			<label className="text-xs font-medium text-muted-foreground">
				{ __( 'Type:', 'erp' ) }
			</label>
			<SmartSelect
				options={ [ ...TYPE_OPTIONS ] }
				value={ value }
				onValueChange={ ( raw ) => {
					const next: EmployeeListQuery = { ...filters };
					if ( ! raw ) {
						delete ( next as Record< string, unknown > ).employee_type;
					} else {
						( next as Record< string, unknown > ).employee_type = raw;
					}
					setFilters( next );
					setPagination( { page: 1, perPage: 20 } );
				} }
				placeholder={ __( 'All types', 'erp' ) }
				searchPlaceholder={ __( 'Search…', 'erp' ) }
				emptyMessage={ __( 'No matches found.', 'erp' ) }
				showClear
				className="h-9 w-52 bg-background"
				contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
			/>
		</div>
	);
}
