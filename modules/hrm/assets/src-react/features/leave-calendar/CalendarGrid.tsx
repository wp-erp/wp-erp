/**
 * The day-grid: weekday header + a grid of day cells (6 weeks for the month
 * view, 1 week for the week view), each with its holiday/leave chips and
 * weekend / out-of-month / today styling. Loading shows a translucent overlay.
 * Pure presentation — data is pre-bucketed by the page.
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import { NavLink } from 'react-router-dom';
import type { JSX } from 'react';

import { makeInitials } from '@/shared/components/PersonCell';
import { __ } from '@/shared/i18n';

import type { CalendarEvent } from './types';
import { WEEKDAYS, ymd, type DayEvents } from './leave-calendar-format';

/**
 * A single leave chip — shows an initials avatar + the employee name and, when
 * the event carries a `user_id`, links through to that employee's profile.
 * (F23: event avatar + event→profile link.)
 */
export function LeaveChip( { ev }: { ev: CalendarEvent } ): JSX.Element {
	const label = ev.employee_name || ev.title;
	const title = [ ev.employee_name, ev.title, ev.reason ].filter( Boolean ).join( ' — ' );
	const style = { backgroundColor: ev.color ? `${ ev.color }22` : 'var(--muted)' };

	const inner = (
		<>
			<Avatar className="size-4 shrink-0">
				{ ev.avatar_url ? <AvatarImage src={ ev.avatar_url } alt={ ev.employee_name || ev.title } /> : null }
				<AvatarFallback className="bg-primary/15 text-[8px] font-semibold uppercase text-primary">
					{ makeInitials( ev.employee_name || ev.title ) }
				</AvatarFallback>
			</Avatar>
			<span className="truncate">{ label }</span>
		</>
	);

	if ( ev.user_id ) {
		return (
			<NavLink
				to={ `/employees/${ ev.user_id }` }
				viewTransition
				className="flex items-center gap-1 truncate rounded px-1.5 py-0.5 text-[11px] text-foreground hover:underline"
				style={ style }
				title={ title }
			>
				{ inner }
			</NavLink>
		);
	}

	return (
		<span
			className="flex items-center gap-1 truncate rounded px-1.5 py-0.5 text-[11px] text-foreground"
			style={ style }
			title={ title }
		>
			{ inner }
		</span>
	);
}

interface CalendarGridProps {
	readonly weeks:     Date[][];
	readonly byDay:     Map< string, DayEvents >;
	readonly thisMonth: number;
	readonly todayKey:  string;
	readonly loading:   boolean;
	/** Grey out days outside `thisMonth` (month view). Off for the week view. */
	readonly dimOutOfMonth?: boolean;
}

export function CalendarGrid( { weeks, byDay, thisMonth, todayKey, loading, dimOutOfMonth = true }: CalendarGridProps ): JSX.Element {
	return (
		<div className="relative">
			{ loading ? (
				<div className="absolute inset-0 z-10 flex items-center justify-center bg-card/60 text-sm text-muted-foreground">
					{ __( 'Loading…', 'erp' ) }
				</div>
			) : null }

			{ /* Weekday header */ }
			<div className="grid grid-cols-7 border-b border-border">
				{ WEEKDAYS.map( ( wd ) => (
					<div key={ wd } className="px-2 py-2 text-center text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
						{ wd }
					</div>
				) ) }
			</div>

			{ /* Day grid */ }
			<div>
				{ weeks.map( ( week, wi ) => (
					<div key={ wi } className="grid grid-cols-7">
						{ week.map( ( day ) => {
							const key       = ymd( day );
							const bucket    = byDay.get( key );
							const inMonth   = ! dimOutOfMonth || day.getMonth() === thisMonth;
							const isToday   = key === todayKey;
							const isWeekend = bucket?.weekend ?? false;
								const holidays  = bucket?.holidays ?? [];
								const isHoliday = holidays.length > 0;
								// Holiday cells get a diagonal striped fill in the holiday
								// colour (matches the frontend calendar), so the whole day
								// reads as a holiday — not just the chip.
								const holidayColor = holidays[ 0 ]?.color || '#FF5354';
								const holidayStyle = isHoliday
									? { backgroundImage: `repeating-linear-gradient(-45deg, ${ holidayColor }20, ${ holidayColor }20 5px, transparent 5px, transparent 11px)` }
									: undefined;

							return (
								<div
									key={ key }
										style={ holidayStyle }
									className={ [
										'min-h-24 border-b border-r border-border p-1.5 last:border-r-0',
										inMonth ? '' : 'bg-muted/20',
										isWeekend && ! isHoliday ? 'bg-muted/30' : '',
									].join( ' ' ) }
								>
									<div className="mb-1 flex items-center justify-between">
										<span
											className={ [
												'inline-flex size-6 items-center justify-center rounded-full text-xs',
												isToday ? 'bg-primary font-semibold text-primary-foreground' : inMonth ? 'text-foreground' : 'text-muted-foreground',
											].join( ' ' ) }
										>
											{ day.getDate() }
										</span>
									</div>

									<div className="flex flex-col gap-1">
										{ ( bucket?.holidays ?? [] ).map( ( ev, i ) => (
											<span
												key={ `h-${ ev.id }-${ i }` }
												className="truncate rounded px-1.5 py-0.5 text-[11px] font-medium text-white"
												style={ { backgroundColor: ev.color || '#FF5354' } }
												title={ ev.title }
											>
												{ ev.title }
											</span>
										) ) }
										{ ( bucket?.leaves ?? [] ).map( ( ev, i ) => (
											<LeaveChip key={ `l-${ ev.id }-${ i }` } ev={ ev } />
										) ) }
									</div>
								</div>
							);
						} ) }
					</div>
				) ) }
			</div>
		</div>
	);
}
