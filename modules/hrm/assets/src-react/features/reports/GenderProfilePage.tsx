/**
 * `/reports/gender-profile` — workforce gender ratio.
 *
 * Mirrors views/reporting/gender-profile.php: male / female / unspecified rows
 * plus a Total row, each with a count and percentage. Data from
 * `GET /reports/gender-profile`.
 */

import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@wedevs/plugin-ui';
import { useMemo } from 'react';
import type { JSX } from 'react';
import { Cell, Pie, PieChart } from 'recharts';

import { __ } from '@/shared/i18n';

import { ReportShell, ReportState } from './ReportShell';
import type { GenderProfileResponse } from './types';
import { useReport } from './useReports';

/** Distinct colour per gender slice (legacy gender-ratio pie parity). */
const GENDER_COLORS = [ '#3b82f6', '#ec4899', '#a78bfa', '#f59e0b', '#22c55e' ];

/** Stable colour for slice `i` — never undefined (satisfies ChartConfig). */
function genderColor( i: number ): string {
	return GENDER_COLORS[ i % GENDER_COLORS.length ] ?? '#3b82f6';
}

export function GenderProfilePage(): JSX.Element {
	const { data, loading, error } = useReport< GenderProfileResponse >( '/reports/gender-profile' );
	const rows = data?.rows ?? [];

	// Exclude the trailing Total row from the pie (legacy charts the per-gender counts).
	const pieData = useMemo(
		() =>
			( rows.length > 1 ? rows.slice( 0, -1 ) : rows )
				.map( ( row, i ) => ( {
					key:   row.gender,
					value: row.count,
					fill:  genderColor( i ),
				} ) )
				.filter( ( d ) => d.value > 0 ),
		[ rows ]
	);

	const pieConfig = useMemo(
		() =>
			Object.fromEntries(
				pieData.map( ( d, i ) => [ d.key, { label: d.key, color: genderColor( i ) } ] )
			),
		[ pieData ]
	);

	return (
		<ReportShell title={ __( 'Gender Profile', 'erp' ) }>
			<ReportState
				loading={ loading }
				error={ error }
				empty={ rows.length === 0 }
				emptyText={ __( 'No employee data available.', 'erp' ) }
			>
				{ pieData.length > 0 ? (
					<div className="mb-5 border-b border-border pb-5">
						<h3 className="mb-3 text-sm font-semibold text-foreground">{ __( 'Gender Ratio', 'erp' ) }</h3>
						<ChartContainer config={ pieConfig } className="mx-auto aspect-square h-[260px]">
							<PieChart>
								<ChartTooltip content={ <ChartTooltipContent nameKey="key" hideLabel /> } />
								<Pie data={ pieData } dataKey="value" nameKey="key" innerRadius={ 60 } strokeWidth={ 4 } label>
									{ pieData.map( ( d ) => <Cell key={ d.key } fill={ d.fill } /> ) }
								</Pie>
							</PieChart>
						</ChartContainer>
					</div>
				) : null }
				<div className="rounded-lg border border-border bg-card shadow-sm">
					<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
					<thead className="border-b border-border bg-card">
						<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
							<th scope="col" className="px-4">{ __( 'Gender', 'erp' ) }</th>
							<th scope="col" className="px-2 text-right">{ __( 'Count', 'erp' ) }</th>
							<th scope="col" className="px-2 text-right">{ __( 'Percentage', 'erp' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ rows.map( ( row, idx ) => {
							const isTotal = idx === rows.length - 1;
							return (
								<tr
									key={ `${ row.gender }-${ idx }` }
									className={ [
										'h-18 border-b border-border last:border-b-0',
										isTotal ? 'bg-muted/30 font-semibold text-foreground' : 'bg-card hover:bg-muted/40',
									].join( ' ' ) }
								>
									<td className="px-4 align-middle font-medium text-foreground">{ row.gender }</td>
									<td className="px-2 text-right align-middle text-sm text-foreground">{ row.count }</td>
									<td className="px-2 text-right align-middle text-sm text-foreground">{ row.percentage }</td>
								</tr>
							);
						} ) }
					</tbody>
				</table>
					</div>
				</div>
			</ReportState>
		</ReportShell>
	);
}
