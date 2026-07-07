/**
 * Status-adaptive date cell — renders the latest status-change date
 * (`status_date`) for the non-active status tabs, mirroring the legacy list's
 * "Terminated At" / "Inactive From" / "Resigned At" / "Deceased From" column.
 */

import type { JSX } from 'react';

import type { EmployeeListItem } from '@/stores/employees';

function formatDate( iso: string | null ): string {
	if ( ! iso ) {
		return '—';
	}
	const date = new Date( iso );
	if ( Number.isNaN( date.getTime() ) ) {
		return iso;
	}
	return date.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } );
}

export function StatusDateCell( { row }: { readonly row: EmployeeListItem } ): JSX.Element {
	return <span className="whitespace-nowrap text-sm text-foreground">{ formatDate( row.status_date ) }</span>;
}
