/**
 * Location lookup filter: sources options from
 * /erp/v2/company-locations.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { LookupFilter } from './LookupFilter';

export function LocationFilter(): JSX.Element | null {
	return (
		<LookupFilter
			label={ __( 'Location:', 'erp' ) }
			placeholder={ __( 'All locations', 'erp' ) }
			lookupKey="locations"
			field="location_id"
		/>
	);
}
