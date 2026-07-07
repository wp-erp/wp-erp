/**
 * Department lookup filter — sources options from /erp/v1/hrm/departments.
 * Will swap to `erp-hr/departments` store when that ships.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { LookupFilter } from './LookupFilter';

export function DepartmentFilter(): JSX.Element | null {
	return (
		<LookupFilter
			label={ __( 'Department:', 'erp' ) }
			placeholder={ __( 'All departments', 'erp' ) }
			lookupKey="departments"
			field="department_id"
		/>
	);
}
