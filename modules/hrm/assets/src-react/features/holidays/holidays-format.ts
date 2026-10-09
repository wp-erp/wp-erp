/**
 * Pure presentation helpers for the Holidays feature. No component state.
 */

import { formatDisplayDate } from '@/shared/utils/date';

/** Format a `YYYY-MM-DD` value for display (locale short date). */
export function fmt( value: string | null ): string {
	return formatDisplayDate( value, ( value ?? '' ).slice( 0, 10 ) || '—' );
}
