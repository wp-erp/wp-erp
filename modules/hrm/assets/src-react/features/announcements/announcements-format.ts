/**
 * Pure constants + formatters for the Announcements list: the status tab set,
 * the search debounce interval, and the list date formatter. No component state.
 */

import { __ } from '@/shared/i18n';

export const STATUS_TABS: ReadonlyArray< { value: string; label: string } > = [
	{ value: 'publish', label: __( 'Published', 'erp' ) },
	{ value: 'draft', label: __( 'Draft', 'erp' ) },
	{ value: 'trash', label: __( 'Trash', 'erp' ) },
];

export const SEARCH_DEBOUNCE_MS = 350;

export function fmt( value: string | null ): string {
	if ( ! value ) {
		return '—';
	}
	const d = new Date( value );
	return Number.isNaN( d.getTime() ) ? value.slice( 0, 10 ) : d.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } );
}
