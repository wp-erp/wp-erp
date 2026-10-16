/**
 * Hire-date cell — formatted as a calendar date (`M j, Y`, WordPress locale).
 *
 * Via `formatCalendarDate`, not `dateI18n` directly: a hire date is a calendar
 * date, and `dateI18n` would timezone-shift it a day when the browser and site
 * zones straddle midnight.
 */

import type { JSX } from 'react';

import { formatCalendarDate } from '@/shared/utils/date';
import type { EmployeeListItem } from '@/stores/employees';

interface HireDateCellProps {
	readonly row: EmployeeListItem;
}

export function HireDateCell( { row }: HireDateCellProps ): JSX.Element {
	if ( ! row.hire_date ) {
		return <span className="text-muted-foreground">—</span>;
	}
	const formatted = formatCalendarDate( row.hire_date );
	return <span className="text-sm text-foreground">{ formatted }</span>;
}
