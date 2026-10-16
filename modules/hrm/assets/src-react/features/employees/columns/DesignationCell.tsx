import type { JSX } from 'react';

import type { EmployeeListItem } from '@/stores/employees';

interface DesignationCellProps {
	readonly row: EmployeeListItem;
}

export function DesignationCell( { row }: DesignationCellProps ): JSX.Element {
	if ( ! row.designation ) {
		return <span className="text-muted-foreground">—</span>;
	}
	return <span className="text-sm text-foreground">{ row.designation.name }</span>;
}
