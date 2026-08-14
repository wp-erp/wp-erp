/**
 * Designation lookup filter — sources options from /erp/v1/hrm/designations.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { LookupFilter } from './LookupFilter';

export function DesignationFilter(): JSX.Element | null {
	return (
		<LookupFilter
			label={ __( 'Designation:', 'erp' ) }
			placeholder={ __( 'All designations', 'erp' ) }
			lookupKey="designations"
			field="designation_id"
		/>
	);
}
