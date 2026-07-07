/**
 * Status cell — plugin-ui Badge with light-pill colors.
 *
 * Color map from figma-reference.md §"Status badge color contract".
 */

import { Badge } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import type { EmployeeListItem, EmployeeStatus } from '@/stores/employees';

interface StatusCellProps {
	readonly row: EmployeeListItem;
}

interface StatusVisual {
	readonly label:     string;
	readonly className: string;
}

function visualFor( status: EmployeeStatus | null ): StatusVisual {
	switch ( status ) {
		case 'active':
			return {
				label:     __( 'Active', 'erp' ),
				className: 'bg-success-light text-success-on-light',
			};
		case 'inactive':
			return {
				label:     __( 'Inactive', 'erp' ),
				className: 'bg-neutral-light text-neutral-on-light',
			};
		case 'terminated':
			return {
				label:     __( 'Terminated', 'erp' ),
				className: 'bg-destructive-light text-destructive-on-light',
			};
		case 'resigned':
			return {
				label:     __( 'Resigned', 'erp' ),
				className: 'bg-destructive-light text-destructive-on-light',
			};
		case 'deceased':
			return {
				label:     __( 'Deceased', 'erp' ),
				className: 'bg-neutral-light text-neutral-on-light',
			};
		default:
			return {
				label:     '—',
				className: 'bg-neutral-light text-neutral-on-light',
			};
	}
}

export function StatusCell( { row }: StatusCellProps ): JSX.Element {
	const { label, className } = visualFor( row.status );
	return <Badge className={ `${ className } rounded-md` }>{ label }</Badge>;
}
