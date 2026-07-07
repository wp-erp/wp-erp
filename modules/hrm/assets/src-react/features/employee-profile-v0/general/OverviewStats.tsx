/**
 * Stat row for the employee Overview tab — quick, relevant per-employee numbers:
 * tenure (since hire date), age (from DOB), and current-year leave balance
 * (available + taken, summed across policies from the leave summary endpoint).
 */

import { CalendarCheck2, CalendarClock, CalendarMinus2, Clock } from 'lucide-react';
import type { ComponentType, JSX, SVGProps } from 'react';

import { __ } from '@/shared/i18n';

import { useEmployeeLeave } from '../leave/useEmployeeLeave';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

interface OverviewStatsProps {
	readonly userId:     number;
	readonly hiringDate: string;
	readonly dateOfBirth: string;
}

/** Parse a YYYY-MM-DD (or ISO) value to a Date, or null when blank/invalid. */
function parseDate( value: string ): Date | null {
	const v = ( value ?? '' ).trim();
	if ( ! v || v === '—' || v.startsWith( '0000' ) ) {
		return null;
	}
	const d = new Date( v );
	return Number.isNaN( d.getTime() ) ? null : d;
}

/** Whole years + months elapsed from `from` to now, formatted e.g. "3y 2m". */
function elapsed( from: Date | null ): string {
	if ( ! from ) {
		return '—';
	}
	const now = new Date();
	let months = ( now.getFullYear() - from.getFullYear() ) * 12 + ( now.getMonth() - from.getMonth() );
	if ( now.getDate() < from.getDate() ) {
		months -= 1;
	}
	if ( months < 0 ) {
		return '—';
	}
	const y = Math.floor( months / 12 );
	const m = months % 12;
	if ( y === 0 ) {
		// translators: %d = months
		return sprintfMonths( m );
	}
	// translators: 1: years, 2: months
	return m === 0
		? `${ y }${ __( 'y', 'erp' ) }`
		: `${ y }${ __( 'y', 'erp' ) } ${ m }${ __( 'm', 'erp' ) }`;
}

function sprintfMonths( m: number ): string {
	return `${ m }${ __( 'm', 'erp' ) }`;
}

/** Whole years from a birth date to now. */
function ageYears( dob: Date | null ): string {
	if ( ! dob ) {
		return '—';
	}
	const now = new Date();
	let age = now.getFullYear() - dob.getFullYear();
	const beforeBirthday =
		now.getMonth() < dob.getMonth() || ( now.getMonth() === dob.getMonth() && now.getDate() < dob.getDate() );
	if ( beforeBirthday ) {
		age -= 1;
	}
	return age >= 0 && age < 130 ? String( age ) : '—';
}

interface StatProps {
	readonly icon:  LucideIcon;
	readonly label: string;
	readonly value: string;
	readonly sub?:  string;
	readonly tint:  string;
}

function Stat( { icon: Icon, label, value, sub, tint }: StatProps ): JSX.Element {
	return (
		<div className="flex items-center gap-3 rounded-[10px] bg-card p-4 shadow-sm">
			<span className={ `inline-flex size-11 shrink-0 items-center justify-center rounded-xl ${ tint }` }>
				<Icon size={ 20 } strokeWidth={ 1.9 } aria-hidden="true" />
			</span>
			<div className="min-w-0">
				<p className="truncate text-2xl font-bold leading-7 text-foreground">{ value }</p>
				<p className="truncate text-xs text-muted-foreground">{ sub ? `${ label } · ${ sub }` : label }</p>
			</div>
		</div>
	);
}

export function OverviewStats( { userId, hiringDate, dateOfBirth }: OverviewStatsProps ): JSX.Element {
	const { data } = useEmployeeLeave( userId );

	const summary   = data?.summary ?? [];
	const available = summary.reduce( ( acc, s ) => acc + ( Number( s.available ) || 0 ), 0 );
	const spent     = summary.reduce( ( acc, s ) => acc + ( Number( s.spent ) || 0 ), 0 );
	const fmtDays   = ( n: number ): string => ( Number.isInteger( n ) ? String( n ) : n.toFixed( 1 ) );

	return (
		<div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
			<Stat
				icon={ Clock }
				label={ __( 'Tenure', 'erp' ) }
				value={ elapsed( parseDate( hiringDate ) ) }
				tint="bg-primary/10 text-primary"
			/>
			<Stat
				icon={ CalendarClock }
				label={ __( 'Age', 'erp' ) }
				value={ ageYears( parseDate( dateOfBirth ) ) }
				sub={ __( 'years', 'erp' ) }
				tint="bg-violet-500/10 text-violet-600 dark:text-violet-400"
			/>
			<Stat
				icon={ CalendarCheck2 }
				label={ __( 'Leave Available', 'erp' ) }
				value={ summary.length ? fmtDays( available ) : '—' }
				sub={ __( 'days', 'erp' ) }
				tint="bg-success-light text-success-on-light"
			/>
			<Stat
				icon={ CalendarMinus2 }
				label={ __( 'Leave Taken', 'erp' ) }
				value={ summary.length ? fmtDays( spent ) : '—' }
				sub={ __( 'days', 'erp' ) }
				tint="bg-warning-light text-warning-on-light"
			/>
		</div>
	);
}
