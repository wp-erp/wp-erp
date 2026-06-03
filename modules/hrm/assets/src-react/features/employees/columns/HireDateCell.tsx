/**
 * Hire-date cell — formatted via `@wordpress/date`'s `dateI18n` with `M j, Y`.
 */

import type { JSX } from 'react';

import { dateI18n } from '@/shared/i18n';
import type { EmployeeListItem } from '@/stores/employees';

interface HireDateCellProps {
	readonly row: EmployeeListItem;
}

export function HireDateCell( { row }: HireDateCellProps ): JSX.Element {
	if ( ! row.hire_date ) {
		return <span className="text-muted-foreground">—</span>;
	}
	const formatted = dateI18n( 'M j, Y', row.hire_date );
	return <span className="text-sm text-foreground">{ formatted }</span>;
}
