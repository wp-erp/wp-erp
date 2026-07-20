/**
 * Regression tests for the calendar-date/timezone off-by-one.
 *
 * This bug has now landed three times: the leave-year screen (#1540, fixed by
 * moving formatting to PHP `wp_date`), and again in the React port via both the
 * `DatePicker` trigger label and direct `dateI18n` calls on stored calendar
 * dates. The failure only shows when the BROWSER timezone and the SITE timezone
 * disagree, which never happens on a developer machine running a local site —
 * hence these tests, which vary both independently.
 */

import { setSettings } from '@wordpress/date';
import { afterAll, describe, expect, it } from 'vitest';

import { formatCalendarDate, parseServerDate } from './date';

/** Local-field `YYYY-MM-DD`, the inverse of `parseServerDate`. */
function localYmd( date: Date ): string {
	const pad = ( n: number ): string => String( n ).padStart( 2, '0' );
	return `${ date.getFullYear() }-${ pad( date.getMonth() + 1 ) }-${ pad( date.getDate() ) }`;
}

/** Point `@wordpress/date` at a given site timezone. */
function setSiteTimezone( name: string, offset: number ): void {
	setSettings( {
		l10n: {
			locale: 'en_US',
			months: [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ],
			monthsShort: [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ],
			weekdays: [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ],
			weekdaysShort: [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ],
			weekdaysInitial: [ 'S', 'M', 'T', 'W', 'T', 'F', 'S' ],
			meridiem: { am: 'am', pm: 'pm', AM: 'AM', PM: 'PM' },
			relative: { future: '%s from now', past: '%s ago' },
			startOfWeek: 0,
		},
		formats: { date: 'Y-m-d', time: 'H:i', datetime: 'Y-m-d H:i', datetimeAbbreviated: 'Y-m-d H:i' },
		timezone: { offset, string: name, abbr: name, offsetFormatted: String( offset ) },
	} as Parameters< typeof setSettings >[ 0 ] );
}

// The extremes matter: +14 and -12 are the widest real offsets, and a fix that
// only shifts the anchor a few hours would still fail there.
const SITE_ZONES: ReadonlyArray< readonly [ string, number ] > = [
	[ 'UTC', 0 ],
	[ 'Asia/Dhaka', 6 ],
	[ 'America/New_York', -5 ],
	[ 'Pacific/Kiritimati', 14 ],
	[ 'Etc/GMT+12', -12 ],
];

const originalTz = process.env.TZ;

afterAll( () => {
	process.env.TZ = originalTz;
} );

describe( 'formatCalendarDate', () => {
	it( 'renders the stored calendar day whatever the site timezone', () => {
		for ( const [ name, offset ] of SITE_ZONES ) {
			setSiteTimezone( name, offset );
			expect( formatCalendarDate( '2026-01-01' ), `site ${ name }` ).toBe( 'Jan 1, 2026' );
			expect( formatCalendarDate( '2026-12-31' ), `site ${ name }` ).toBe( 'Dec 31, 2026' );
		}
	} );

	it( 'does not shift a year boundary — the case that produced "Dec 31, 2025"', () => {
		// Site UTC + browser UTC+6 is exactly the pair that rendered a leave year
		// stored as 2026-01-01..2026-12-31 as 2025-12-31..2026-12-30.
		setSiteTimezone( 'UTC', 0 );
		expect( formatCalendarDate( '2026-01-01' ) ).toBe( 'Jan 1, 2026' );
		expect( formatCalendarDate( '2026-12-31' ) ).toBe( 'Dec 31, 2026' );
	} );

	it( 'falls back for empty input rather than printing an epoch date', () => {
		setSiteTimezone( 'UTC', 0 );
		expect( formatCalendarDate( '' ) ).toBe( '—' );
		expect( formatCalendarDate( null ) ).toBe( '—' );
		expect( formatCalendarDate( undefined, 'n/a' ) ).toBe( 'n/a' );
	} );
} );

describe( 'parseServerDate', () => {
	it( 'round-trips a calendar date without drifting', () => {
		for ( const ymd of [ '2026-01-01', '2026-07-07', '2026-12-31' ] ) {
			const parsed = parseServerDate( ymd );
			expect( parsed ).not.toBeNull();
			expect( localYmd( parsed as Date ) ).toBe( ymd );
		}
	} );

	it( 'reads local calendar fields, not UTC ones', () => {
		// `new Date( '2026-01-01' )` would be UTC midnight; we want local midnight,
		// so the local getters must report the same day that was passed in.
		const parsed = parseServerDate( '2026-01-01' ) as Date;
		expect( parsed.getFullYear() ).toBe( 2026 );
		expect( parsed.getMonth() ).toBe( 0 );
		expect( parsed.getDate() ).toBe( 1 );
	} );

	it( 'returns null for junk instead of an Invalid Date', () => {
		expect( parseServerDate( '' ) ).toBeNull();
		expect( parseServerDate( 'not-a-date' ) ).toBeNull();
	} );
} );
