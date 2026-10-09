/**
 * Employment-type cell — renders the human label for the employee's
 * employment type slug.
 *
 * Slug → label map mirrors `erp_hr_get_employee_types()` (functions-employee.php)
 * verbatim so the list matches the legacy EmployeeListTable "Employment Type"
 * column.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import type { EmployeeListItem } from '@/stores/employees';

interface EmploymentTypeCellProps {
	readonly row: EmployeeListItem;
}

function labelFor( type: string | null ): string {
	switch ( type ) {
		case 'permanent':
			return __( 'Full Time', 'erp' );
		case 'parttime':
			return __( 'Part Time', 'erp' );
		case 'contract':
			return __( 'On Contract', 'erp' );
		case 'temporary':
			return __( 'Temporary', 'erp' );
		case 'trainee':
			return __( 'Trainee', 'erp' );
		default:
			return '';
	}
}

export function EmploymentTypeCell( { row }: EmploymentTypeCellProps ): JSX.Element {
	const label = labelFor( row.employee_type );
	return label ? (
		<span className="whitespace-nowrap text-sm text-foreground">{ label }</span>
	) : (
		<span className="text-muted-foreground">—</span>
	);
}
