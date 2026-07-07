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
