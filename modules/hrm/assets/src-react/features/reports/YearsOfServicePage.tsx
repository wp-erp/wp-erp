/**
 * `/reports/years-of-service` — hire anniversaries grouped by month/day.
 *
 * Mirrors views/reporting/years-of-service.php: each month is a section, each
 * day lists the employees hired that day with their completed years of service.
 * Data from `GET /reports/years-of-service`.
 */

import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

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

	return (
		<ReportShell title={ __( 'Years of Service', 'erp' ) }>
			<ReportState loading={ loading } error={ error } empty={ months.length === 0 }>
				<div className="divide-y divide-border">
					{ months.map( ( month ) => (
						<div key={ month.month } className="px-4 py-4">
							<h3 className="mb-2 text-sm font-semibold text-foreground">{ month.month_name }</h3>
							<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
								<tbody>
									{ month.days.map( ( day ) => (
										<tr key={ day.day } className="border-b border-border/60 last:border-b-0">
											<th scope="row" className="w-16 py-2 pr-4 align-top text-sm font-medium text-muted-foreground">
												{ ordinal( day.day ) }
											</th>
											<td className="py-2 text-sm text-foreground">
												{ day.people.map( ( p, i ) => (
													<span key={ p.user_id }>
														{ p.name }
														<span className="text-muted-foreground">
															{ ' ' }
															({ sprintf(
																/* translators: %d: number of years */
																p.years === 1 ? __( '%d Year', 'erp' ) : __( '%d Years', 'erp' ),
																p.years
															) })
														</span>
														{ i < day.people.length - 1 ? ', ' : '' }
													</span>
												) ) }
											</td>
										</tr>
									) ) }
								</tbody>
							</table>
					</div>
						</div>
					) ) }
				</div>
			</ReportState>
		</ReportShell>
	);
}
