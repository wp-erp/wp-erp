/**
 * DateRangeField — string-based date-range filter with quick presets.
 *
 * Replaces the old "From date" + "To date" pair of `<DateField>` inputs with the
 * combination the app already uses elsewhere (a preset `SmartSelect` +, for a
 * custom span, a range calendar): a preset dropdown (This Month, Last Month,
 * Last 3/6 Months, This Year, Last Year, Custom) beside the DS's own
 * `DateRangePicker`. Nothing here is a new widget — both controls are existing
 * `@wedevs/plugin-ui` components; the custom trigger mirrors `DateField`.
 *
 * Presets resolve to concrete `YYYY-MM-DD` bounds on the client and the whole
 * component keeps a string-pair contract (`{ from, to }`), so every call site
 * maps to its own REST keys (`start_date`/`end_date`, `from`/`to`, `start`/`end`)
 * with no backend change.
 */

import { DateRangePicker, Input, SmartSelect } from '@wedevs/plugin-ui';
import type { DateRange } from '@wedevs/plugin-ui';
import { CalendarDays } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

export interface DateRangeValue {
	readonly from: string;
	readonly to:   string;
}

interface DateRangeFieldProps {
	readonly value:        DateRangeValue;
	readonly onChange:     ( value: DateRangeValue ) => void;
	readonly className?:   string | undefined;
	readonly placeholder?: string | undefined;
	readonly disabled?:    boolean | undefined;
	/** Hide the preset dropdown and always show the range calendar. Default true. */
	readonly showPresets?: boolean | undefined;
}

const pad = ( n: number ): string => String( n ).padStart( 2, '0' );

const iso = ( d: Date ): string =>
	`${ d.getFullYear() }-${ pad( d.getMonth() + 1 ) }-${ pad( d.getDate() ) }`;

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

// WordPress prints `<html lang="ar">` on RTL locales; feed it to the calendar so
// month/weekday names localise (mirrors `DateField`).
const wpLocale =
	typeof document !== 'undefined'
		? ( document.documentElement.lang || '' ).replace( '-', '_' )
		: undefined;

/** Resolve a preset key to concrete `{ from, to }` ISO bounds. */
function resolvePreset( key: string ): DateRangeValue {
	const t = new Date();
	const y = t.getFullYear();
	const m = t.getMonth();
	switch ( key ) {
		case 'this_month':
			return { from: iso( new Date( y, m, 1 ) ), to: iso( new Date( y, m + 1, 0 ) ) };
		case 'last_month':
			return { from: iso( new Date( y, m - 1, 1 ) ), to: iso( new Date( y, m, 0 ) ) };
		case 'last_3_months': {
			const from = new Date( t );
			from.setMonth( from.getMonth() - 3 );
			return { from: iso( from ), to: iso( t ) };
		}
		case 'last_6_months': {
			const from = new Date( t );
			from.setMonth( from.getMonth() - 6 );
			return { from: iso( from ), to: iso( t ) };
		}
		case 'this_year':
			return { from: iso( new Date( y, 0, 1 ) ), to: iso( new Date( y, 11, 31 ) ) };
		case 'last_year':
			return { from: iso( new Date( y - 1, 0, 1 ) ), to: iso( new Date( y - 1, 11, 31 ) ) };
		default:
			return { from: '', to: '' };
	}
}

const PRESET_OPTIONS: ReadonlyArray< { value: string; label: string } > = [
	{ value: '', label: __( 'Filter by date', 'erp' ) },
	{ value: 'this_month', label: __( 'This Month', 'erp' ) },
	{ value: 'last_month', label: __( 'Last Month', 'erp' ) },
	{ value: 'last_3_months', label: __( 'Last 3 Months', 'erp' ) },
	{ value: 'last_6_months', label: __( 'Last 6 Months', 'erp' ) },
	{ value: 'this_year', label: __( 'This Year', 'erp' ) },
	{ value: 'last_year', label: __( 'Last Year', 'erp' ) },
	{ value: 'custom', label: __( 'Custom range', 'erp' ) },
];

export function DateRangeField( {
	value,
	onChange,
	className,
	placeholder = __( 'Select date range', 'erp' ),
	disabled,
	showPresets = true,
}: DateRangeFieldProps ): JSX.Element {
	// Start in custom mode when a range is already set (the picker can't tell
	// which preset produced it); otherwise no filter.
	const [ preset, setPreset ] = useState< string >(
		value.from || value.to ? 'custom' : ''
	);

	const handlePreset = ( v: string ): void => {
		const next = v || '';
		setPreset( next );
		if ( next === 'custom' ) {
			return; // reveal the calendar; keep the current value
		}
		onChange( resolvePreset( next ) );
	};

	const rangeValue: DateRange | undefined = ( () => {
		const from = toDate( value.from );
		const to = toDate( value.to );
		return from || to ? { from, to } : undefined;
	} )();

	const picker = (
		<DateRangePicker
			mode="range"
			displayFormat="Y-m-d"
			placeholder={ placeholder }
			onChange={ ( range ) =>
				onChange( {
					from: range?.from ? iso( range.from ) : '',
					to:   range?.to ? iso( range.to ) : '',
				} )
			}
			{ ...( rangeValue ? { value: rangeValue } : {} ) }
			{ ...( wpLocale ? { wpLocale } : {} ) }
			trigger={ ( { value: shown } ) => (
				<div className="relative">
					<Input
						type="text"
						readOnly
						disabled={ disabled }
						value={ shown ?? '' }
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

	if ( ! showPresets ) {
		return picker;
	}

	return (
		<div className="flex flex-wrap items-center gap-2">
			<SmartSelect
				options={ PRESET_OPTIONS as { value: string; label: string }[] }
				value={ preset }
				onValueChange={ handlePreset }
				placeholder={ __( 'Filter by date', 'erp' ) }
				{ ...( disabled !== undefined ? { disabled } : {} ) }
				className="h-9 w-44 bg-background"
				contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
			/>
			{ preset === 'custom' ? picker : null }
		</div>
	);
}
