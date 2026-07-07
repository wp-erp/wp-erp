/**
 * Pure constants + formatters for the Announcements list: the status tab set,
 * the search debounce interval, and the list date formatter. No component state.
 */

import { __ } from '@/shared/i18n';
import { formatDisplayDate } from '@/shared/utils/date';

export const STATUS_TABS: ReadonlyArray< { value: string; label: string } > = [
	{ value: 'publish', label: __( 'Published', 'erp' ) },
	{ value: 'draft', label: __( 'Draft', 'erp' ) },
	{ value: 'trash', label: __( 'Trash', 'erp' ) },
];

export const SEARCH_DEBOUNCE_MS = 350;

export function fmt( value: string | null ): string {
	return formatDisplayDate( value, ( value ?? '' ).slice( 0, 10 ) || '—' );
}
