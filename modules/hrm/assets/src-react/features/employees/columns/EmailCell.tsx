/**
 * Email cell — clickable mailto link in brand-blue.
 *
 * Figma node `502:22929` (283 px column).
 */

import type { JSX } from 'react';

import type { EmployeeListItem } from '@/stores/employees';

interface EmailCellProps {
	readonly row: EmployeeListItem;
}

export function EmailCell( { row }: EmailCellProps ): JSX.Element {
	if ( ! row.email ) {
		return <span className="text-muted-foreground">—</span>;
	}
	return (
		<a
			href={ `mailto:${ row.email }` }
			className="text-sm text-primary hover:underline"
		>
			{ row.email }
		</a>
	);
}
