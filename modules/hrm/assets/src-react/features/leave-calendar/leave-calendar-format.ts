/**
 * Pure date helpers + per-day bucket type for the leave calendar. No component
 * state — `Y-m-d` parsing/formatting, day arithmetic and the weekday header.
 */

import type { CalendarEvent } from './types';

export const WEEKDAYS = [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ];

/** Local-midnight Date from a `Y-m-d` string. */
export function parseYmd( value: string ): Date {
	const [ y = 1970, m = 1, d = 1 ] = value.split( '-' ).map( ( n ) => parseInt( n, 10 ) );
	return new Date( y, m - 1, d );
}

export function ymd( date: Date ): string {
	const y = date.getFullYear();
	const m = String( date.getMonth() + 1 ).padStart( 2, '0' );
	const d = String( date.getDate() ).padStart( 2, '0' );
	return `${ y }-${ m }-${ d }`;
}

export function addDays( date: Date, days: number ): Date {
	const next = new Date( date );
	next.setDate( next.getDate() + days );
	return next;
}

export interface DayEvents {
	leaves:   CalendarEvent[];
	holidays: CalendarEvent[];
	weekend:  boolean;
}
