/**
 * `/reports/age-profile` — employee age breakdown by department.
 *
 * Mirrors views/reporting/age-profile.php: one row per department plus a Total
 * row, columns for each age band. Data from `GET /reports/age-profile`.
 */

import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@wedevs/plugin-ui';
import { useMemo } from 'react';
import type { JSX } from 'react';
import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from 'recharts';

import { __ } from '@/shared/i18n';

import { ReportShell, ReportState } from './ReportShell';
import type { AgeProfileResponse } from './types';
import { useReport } from './useReports';

const AGE_CONFIG = {
	count: { label: __( 'Employees', 'erp' ), color: '#0ea5e9' },
};

const BANDS: ReadonlyArray< { key: keyof Omit< AgeProfileResponse['rows'][number], 'department' >; label: string } > = [
	{ key: 'under_18', label: __( 'Under 18', 'erp' ) },
	{ key: 'age_18_25', label: '18–25' },
	{ key: 'age_26_35', label: '26–35' },
	{ key: 'age_36_45', label: '36–45' },
	{ key: 'age_46_55', label: '46–55' },
	{ key: 'age_56_65', label: '56–65' },
	{ key: 'age_65_plus', label: __( '65+', 'erp' ) },
];

export function AgeProfilePage(): JSX.Element {
	const { data, loading, error } = useReport< AgeProfileResponse >( '/reports/age-profile' );
	const rows = data?.rows ?? [];

	// Overall age breakdown = the trailing Total row's per-band counts (legacy
	// age-breakdown bar chart). Falls back to summing all rows when no Total.
	const chartData = useMemo( () => {
		if ( rows.length === 0 ) {
			return [];
		}
		const total = rows.length > 1 ? rows[ rows.length - 1 ] : null;
		return BANDS.map( ( b ) => ( {
			band:  b.label,
			count: total ? total[ b.key ] : rows.reduce( ( sum, r ) => sum + r[ b.key ], 0 ),
		} ) );
	}, [ rows ] );

	const hasChart = chartData.some( ( d ) => d.count > 0 );

	return (
		<ReportShell title={ __( 'Age Profile', 'erp' ) }>
			<ReportState loading={ loading } error={ error } empty={ rows.length === 0 }>
				{ hasChart ? (
					<div className="mb-5 border-b border-border pb-5">
						<h3 className="mb-3 text-sm font-semibold text-foreground">{ __( 'Age Breakdown', 'erp' ) }</h3>
						<ChartContainer config={ AGE_CONFIG } className="h-[260px] w-full">
							<BarChart data={ chartData } margin={ { left: 4, right: 12, top: 8 } }>
								<CartesianGrid vertical={ false } strokeDasharray="3 3" className="stroke-border" />
								<XAxis dataKey="band" tickLine={ false } axisLine={ false } tickMargin={ 8 } className="text-xs" />
								<YAxis tickLine={ false } axisLine={ false } width={ 28 } allowDecimals={ false } className="text-xs" />
								<ChartTooltip content={ <ChartTooltipContent hideLabel /> } />
								<Bar dataKey="count" fill="var(--color-count)" radius={ [ 4, 4, 0, 0 ] } barSize={ 36 } />
							</BarChart>
						</ChartContainer>
					</div>
				) : null }
				<div className="rounded-lg border border-border bg-card shadow-sm">
					<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
					<thead className="border-b border-border bg-muted/40">
						<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
							<th scope="col" className="px-2">{ __( 'Department', 'erp' ) }</th>
							{ BANDS.map( ( b ) => (
								<th key={ b.key } scope="col" className="px-2 text-right">{ b.label }</th>
							) ) }
						</tr>
					</thead>
					<tbody>
						{ rows.map( ( row, idx ) => {
							const isTotal = idx === rows.length - 1;
							return (
								<tr
									key={ `${ row.department }-${ idx }` }
									className={ [
										'h-18 border-b border-border last:border-b-0',
										isTotal ? 'bg-muted/30 font-semibold text-foreground' : 'hover:bg-muted/40',
									].join( ' ' ) }
								>
									<td className="px-2 align-middle font-medium text-foreground">{ row.department }</td>
									{ BANDS.map( ( b ) => (
										<td key={ b.key } className="px-2 text-right align-middle text-sm text-foreground">
											{ row[ b.key ] }
										</td>
									) ) }
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
