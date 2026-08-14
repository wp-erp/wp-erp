import type { JSX } from 'react';

import type { EmployeeListItem } from '@/stores/employees';

interface DepartmentCellProps {
	readonly row: EmployeeListItem;
}

export function DepartmentCell( { row }: DepartmentCellProps ): JSX.Element {
	if ( ! row.department ) {
		return <span className="text-muted-foreground">—</span>;
	}
	return <span className="text-sm text-foreground">{ row.department.name }</span>;
}
