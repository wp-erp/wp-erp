/**
 * The month day-grid: weekday header + 6-week grid of day cells, each with its
 * holiday/leave chips and weekend / out-of-month / today styling. Loading shows
 * a translucent overlay. Pure presentation — data is pre-bucketed by the page.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { WEEKDAYS, ymd, type DayEvents } from './leave-calendar-format';

interface CalendarGridProps {
	readonly weeks:     Date[][];
	readonly byDay:     Map< string, DayEvents >;
	readonly thisMonth: number;
	readonly todayKey:  string;
	readonly loading:   boolean;
}

export function CalendarGrid( { weeks, byDay, thisMonth, todayKey, loading }: CalendarGridProps ): JSX.Element {
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
					<div key={ wd } className="px-2 py-2 text-center text-xs font-medium uppercase tracking-normal text-muted-foreground">
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
							const inMonth   = day.getMonth() === thisMonth;
							const isToday   = key === todayKey;
							const isWeekend = bucket?.weekend ?? false;

							return (
								<div
									key={ key }
									className={ [
										'min-h-24 border-b border-r border-border p-1.5 last:border-r-0',
										inMonth ? '' : 'bg-muted/20',
										isWeekend ? 'bg-muted/30' : '',
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
											<span
												key={ `l-${ ev.id }-${ i }` }
												className="flex items-center gap-1 truncate rounded px-1.5 py-0.5 text-[11px] text-foreground"
												style={ { backgroundColor: ev.color ? `${ ev.color }22` : 'var(--muted)' } }
												title={ [ ev.employee_name, ev.title, ev.reason ].filter( Boolean ).join( ' — ' ) }
											>
												<span aria-hidden="true" className="inline-block size-2 shrink-0 rounded-full" style={ { backgroundColor: ev.color || 'var(--primary)' } } />
												<span className="truncate">{ ev.employee_name || ev.title }</span>
											</span>
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
