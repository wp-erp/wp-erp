/**
 * Status cell: the shared `StatusPill`, toned by `employeeStatusTone`.
 */

import type { JSX } from 'react';

import { StatusPill } from '@/shared/components/StatusPill';
import { employeeStatusTone } from '@/shared/components/status-tones';
import { __ } from '@/shared/i18n';
import type { EmployeeListItem, EmployeeStatus } from '@/stores/employees';

interface StatusCellProps {
	readonly row: EmployeeListItem;
}

function labelFor( status: EmployeeStatus | null ): string {
	switch ( status ) {
		case 'active':
			return __( 'Active', 'erp' );
		case 'inactive':
			return __( 'Inactive', 'erp' );
		case 'terminated':
			return __( 'Terminated', 'erp' );
		case 'resigned':
			return __( 'Resigned', 'erp' );
		case 'deceased':
			return __( 'Deceased', 'erp' );
		default:
			return '—';
	}
}

export function StatusCell( { row }: StatusCellProps ): JSX.Element {
	return <StatusPill tone={ employeeStatusTone( row.status ) }>{ labelFor( row.status ) }</StatusPill>;
}
