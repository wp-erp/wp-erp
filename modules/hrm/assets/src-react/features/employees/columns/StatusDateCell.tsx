/**
 * Status-adaptive date cell — renders the latest status-change date
 * (`status_date`) for the non-active status tabs, mirroring the legacy list's
 * "Terminated At" / "Inactive From" / "Resigned At" / "Deceased From" column.
 */

import type { JSX } from 'react';

import { formatDisplayDate } from '@/shared/utils/date';
import type { EmployeeListItem } from '@/stores/employees';

function formatDate( iso: string | null ): string {
	return formatDisplayDate( iso, iso ?? '—' );
}

export function StatusDateCell( { row }: { readonly row: EmployeeListItem } ): JSX.Element {
	return <span className="whitespace-nowrap text-sm text-foreground">{ formatDate( row.status_date ) }</span>;
}
