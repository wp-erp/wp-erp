/**
 * Date parsing/formatting helpers shared across HR React.
 *
 * The server sends leave/job/goal dates as **date-only** strings (`YYYY-MM-DD`).
 * `new Date( "2026-07-07" )` parses that as UTC midnight, so
 * `toLocaleDateString()` in any browser at a negative UTC offset renders the
 * *previous* calendar day (the "leave saved one day less" bug). To avoid it we
 * parse a bare `YYYY-MM-DD` as a **local** date instead of letting the Date
 * constructor assume UTC. Full ISO datetimes (with time/offset) are left alone.
 */

import { dateI18n } from '@/shared/i18n';

const DATE_ONLY = /^(\d{4})-(\d{2})-(\d{2})$/;

/**
 * Parse a server date string to a Date without the UTC off-by-one.
 *
 * Bare `YYYY-MM-DD` → local midnight of that calendar day. Anything else is
 * handed to the native `Date` constructor unchanged. Returns `null` when the
 * value is empty or unparseable.
 * @param value
 */
export function parseServerDate( value: string | null | undefined ): Date | null {
	if ( ! value ) {
		return null;
	}
	const match = DATE_ONLY.exec( value.trim() );
	if ( match ) {
		const [ , y, m, d ] = match;
		return new Date( Number( y ), Number( m ) - 1, Number( d ) );
	}
	const date = new Date( value );
	return Number.isNaN( date.getTime() ) ? null : date;
}

/**
 * Human "Year Mon D" label for a server date; falls back to the given
 * placeholder (default em dash) when empty or unparseable.
 * @param value
 * @param fallback
 */
export function formatDisplayDate( value: string | null | undefined, fallback = '—' ): string {
	const date = parseServerDate( value );
	if ( ! date ) {
		return fallback;
	}
	return date.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } );
}

/**
 * Human "Mon D, YYYY" label for a **calendar date** (`YYYY-MM-DD`), in the
 * WordPress locale.
 *
 * `dateI18n( 'M j, Y', '2026-01-01' )` is wrong for this: it treats the string
 * as an instant at browser-local midnight, then re-renders it in the *site*
 * timezone, so the label lands a day off whenever the two zones straddle
 * midnight (site UTC + browser UTC+6 showed Jan 1 as "Dec 31, 2025"). A
 * calendar date has no timezone, so the conversion is meaningless either way —
 * the same class of bug as the leave-year one fixed in #1540.
 *
 * Pinning to UTC noon and forcing UTC rendering removes the conversion: no real
 * offset (+14 to -12) can push noon across a day boundary. Use this for stored
 * dates like hire dates and leave-year bounds; keep `dateI18n` for genuine
 * instants such as `created_at`, where the timezone shift is the correct
 * behaviour.
 * @param value
 * @param fallback
 */
export function formatCalendarDate( value: string | null | undefined, fallback = '—' ): string {
	if ( ! value ) {
		return fallback;
	}
	const match = DATE_ONLY.exec( value.trim() );
	if ( ! match ) {
		// Not a bare calendar date — an instant, so let dateI18n localise it.
		return dateI18n( 'M j, Y', value );
	}
	return dateI18n( 'M j, Y', `${ match[ 0 ] }T12:00:00Z`, true );
}

/**
 * Format a Date as `YYYY-MM-DD` using its **local** calendar fields.
 *
 * The mirror of `parseServerDate`, and the reason it exists: `toISOString()`
 * converts to UTC first, so at 20:00 in Dhaka it reports tomorrow's date and at
 * 19:00 in Los Angeles it reports yesterday's. Both directions are wrong, just
 * at different hours. Reading the local fields is always the day the user sees.
 * @param date
 */
export function toLocalYmd( date: Date ): string {
	if ( Number.isNaN( date.getTime() ) ) {
		return '';
	}
	const pad = ( n: number ): string => String( n ).padStart( 2, '0' );
	const month = pad( date.getMonth() + 1 );
	const day = pad( date.getDate() );
	return `${ date.getFullYear() }-${ month }-${ day }`;
}

/**
 * Today as `YYYY-MM-DD` in the browser's own timezone.
 */
export function todayLocalYmd(): string {
	return toLocalYmd( new Date() );
}
