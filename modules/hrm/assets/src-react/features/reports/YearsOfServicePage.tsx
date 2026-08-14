/**
 * `/reports/years-of-service` — hire anniversaries grouped by month/day.
 *
 * Mirrors views/reporting/years-of-service.php: each month is a section, each
 * day lists the employees hired that day with their completed years of service.
 * Data from `GET /reports/years-of-service`.
 */

import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

import { ReportNameCell } from './ReportNameCell';
import { ReportShell, ReportState } from './ReportShell';
import type { YearsOfServiceResponse } from './types';
import { useReport } from './useReports';

function ordinal( n: number ): string {
	const s = [ 'th', 'st', 'nd', 'rd' ];
	const v = n % 100;
	return n + ( s[ ( v - 20 ) % 10 ] ?? s[ v ] ?? s[ 0 ] ?? 'th' );
}

export function YearsOfServicePage(): JSX.Element {
	const { data, loading, error } = useReport< YearsOfServiceResponse >( '/reports/years-of-service' );
	const months = data?.months ?? [];

	// Paginate by PEOPLE (not months): anniversaries often cluster into a few
	// months, so a month-based cap would render hundreds of rows with no button.
	// Trim months/days down to the first `visible` people and load more in chunks.
	const PAGE = 25;
	const [ visible, setVisible ] = useState( PAGE );
	useEffect( () => { setVisible( PAGE ); }, [ data ] );

	const totalPeople = months.reduce( ( sum, m ) => sum + m.days.reduce( ( s, d ) => s + d.people.length, 0 ), 0 );

	let budget = visible;
	const shownMonths = [];
	for ( const month of months ) {
		if ( budget <= 0 ) {
			break;
		}
		const days = [];
		for ( const day of month.days ) {
			if ( budget <= 0 ) {
				break;
			}
			const people = day.people.slice( 0, budget );
			budget -= people.length;
			days.push( { ...day, people } );
		}
		if ( days.length > 0 ) {
			shownMonths.push( { ...month, days } );
		}
	}

	return (
		<ReportShell title={ __( 'Years of Service', 'erp' ) }>
			<ReportState loading={ loading } error={ error } empty={ months.length === 0 }>
				<div className="divide-y divide-border">
					{ shownMonths.map( ( month ) => (
						<div key={ month.month } className="px-4 py-4">
							<h3 className="mb-2 text-sm font-semibold text-foreground">{ month.month_name }</h3>
							<div className="overflow-x-auto">
						<table className="w-full min-w-160 text-left">
								<tbody>
									{ month.days.map( ( day ) => (
										<tr key={ day.day } className="border-b border-border/60 last:border-b-0">
											<th scope="row" className="w-16 py-2 pr-4 align-top text-sm font-medium text-muted-foreground">
												{ ordinal( day.day ) }
											</th>
											<td className="py-2 text-sm text-foreground">
												<div className="flex flex-col gap-2">
													{ day.people.map( ( p ) => (
														<div key={ p.user_id } className="flex items-center gap-2">
															<ReportNameCell name={ p.name } avatar={ p.avatar } />
															<span className="text-muted-foreground">
																({ sprintf(
																	/* translators: %d: number of years */
																	p.years === 1 ? __( '%d Year', 'erp' ) : __( '%d Years', 'erp' ),
																	p.years
																) })
															</span>
														</div>
													) ) }
												</div>
											</td>
										</tr>
									) ) }
								</tbody>
							</table>
					</div>
						</div>
					) ) }
					{ totalPeople > visible ? (
						<div className="flex justify-center p-3">
							<button type="button" onClick={ () => setVisible( ( v ) => v + PAGE ) } className="inline-flex h-9 items-center rounded-md border border-border bg-card px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted">
								{ __( 'Load more', 'erp' ) } ({ totalPeople - visible })
							</button>
						</div>
					) : null }
				</div>
			</ReportState>
		</ReportShell>
	);
}
