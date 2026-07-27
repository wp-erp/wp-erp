/**
 * DateField — string-based drop-in replacement for `<Input type="date" />`.
 *
 * Wraps plugin-ui's `DatePicker` (a real React calendar) instead of the native
 * browser date control, so it renders in the WordPress locale + RTL — an Arabic
 * site gets an Arabic, right-to-left calendar. Follows the plugin-ui DatePicker
 * Storybook format (`value`/`onChange` are `Date`-based on the picker); this
 * wrapper keeps the existing string (`YYYY-MM-DD`) contract so call sites swap 1:1.
 */

import { DatePicker, Input } from '@wedevs/plugin-ui';
import { CalendarDays } from 'lucide-react';
import type { JSX } from 'react';

import { siteToday } from '@/shared/utils/date';

interface DateFieldProps {
	readonly value:        string;
	readonly onChange:     ( value: string ) => void;
	readonly className?:   string | undefined;
	readonly placeholder?: string | undefined;
	readonly disabled?:    boolean | undefined;
	/** Optional ISO (`YYYY-MM-DD`) bounds — disables days before/after. */
	readonly min?:         string | undefined;
	readonly max?:         string | undefined;
}

const pad = ( n: number ): string => String( n ).padStart( 2, '0' );

const toDate = ( s?: string ): Date | undefined => {
	if ( ! s ) {
		return undefined;
	}
	const [ y, m, d ] = s.split( '-' ).map( Number );
	if ( ! y || ! m || ! d ) {
		return undefined;
	}
	return new Date( y, m - 1, d );
};

const toStr = ( d?: Date ): string =>
	d ? `${ d.getFullYear() }-${ pad( d.getMonth() + 1 ) }-${ pad( d.getDate() ) }` : '';

// WordPress prints `<html lang="ar">` on Arabic/RTL locales. Feed that to the
// picker so the calendar localises its month/weekday names without importing
// date-fns locale bundles. Falls back to the WP default when empty.
const wpLocale =
	typeof document !== 'undefined'
		? ( document.documentElement.lang || '' ).replace( '-', '_' )
		: undefined;

/** Years offered in the caption's year dropdown, relative to the current one. */
const YEARS_BACK  = 100;
const YEARS_AHEAD = 10;

/**
 * Month + year dropdowns in the calendar caption (react-day-picker's
 * `captionLayout="dropdown"`, the same shape shadcn/ui ships) instead of the
 * default static label. Reaching a birth date or a contract end is then one
 * pick rather than dozens of arrow clicks.
 *
 * The dropdown needs explicit bounds: left to itself react-day-picker spans
 * today − 100 years → end of the current year, so no future year is reachable.
 * Bounds honour `min`/`max` when the call site sets them, so the year list
 * never offers a year whose days are all disabled.
 */
export const captionDropdownProps = (
	min?: Date,
	max?: Date
): Record< string, unknown > => {
	// The org's today, not the laptop's — see `siteToday()`. It anchors both the
	// ringed day and the year range, so a company in Dhaka never sees a calendar
	// centred on the previous day because someone opened it from Los Angeles.
	const today = siteToday();
	const year  = today.getFullYear();

	return {
		today,
		captionLayout: 'dropdown',
		startMonth:    min ?? new Date( year - YEARS_BACK, 0, 1 ),
		endMonth:      max ?? new Date( year + YEARS_AHEAD, 11, 31 ),
	};
};

export function DateField( {
	value,
	onChange,
	className,
	placeholder = 'YYYY-MM-DD',
	disabled,
	min,
	max,
}: DateFieldProps ): JSX.Element {
	const dateVal = toDate( value );
	const minDate = toDate( min );
	const maxDate = toDate( max );
	const calendarProps = {
		...captionDropdownProps( minDate, maxDate ),
		...( min || max
			? {
				disabled: {
					...( minDate ? { before: minDate } : {} ),
					...( maxDate ? { after: maxDate } : {} ),
				},
			}
			: {} ),
	};

	// Conditional-spread the optional props: `exactOptionalPropertyTypes` forbids
	// passing an explicit `undefined` to an optional prop, so omit when empty.
	return (
		<DatePicker
			onChange={ ( d ) => onChange( toStr( d ) ) }
			displayFormat="Y-m-d"
			// These are calendar dates, not instants. `toDate()` builds them at
			// browser-local midnight and `toStr()` reads them back with local
			// getters, so the calendar must agree — left at its default the
			// picker resolves days in the *site* timezone and the grid lands a
			// day off whenever the two zones straddle midnight (site UTC +
			// browser UTC+6 selected the 26th when you clicked the 27th).
			wpTimezone={ false }
			{ ...( dateVal ? { value: dateVal } : {} ) }
			{ ...( wpLocale ? { wpLocale } : {} ) }
			calendarProps={ calendarProps }
			// Render our own `value`, not the picker's formatted string. The
			// picker formats via `dateI18n`, which converts the instant into the
			// *site* timezone — but `toDate()` built that Date at *browser*-local
			// midnight. When the two zones straddle midnight the label lands a day
			// off (site UTC + browser UTC+6 showed 2026-01-01 as "2025-12-31").
			// These are calendar dates, not instants, so no conversion is correct.
			// Same class of bug as the leave-year one fixed in #1540.
			trigger={ () => (
				<div className="relative">
					<Input
						type="text"
						readOnly
						disabled={ disabled }
						value={ value }
						placeholder={ placeholder }
						className={ `cursor-pointer pe-9 ${ className ?? '' }` }
					/>
					<CalendarDays
						className="pointer-events-none absolute end-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
						aria-hidden="true"
					/>
				</div>
			) }
		/>
	);
}
