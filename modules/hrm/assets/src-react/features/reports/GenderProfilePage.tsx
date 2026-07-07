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
import { Bar, BarChart, CartesianGrid, Cell, Pie, PieChart, XAxis, YAxis } from 'recharts';

import { __ } from '@/shared/i18n';

import { ReportShell, ReportState } from './ReportShell';
import type { GenderProfileResponse } from './types';
import { useReport } from './useReports';

/** Distinct colour per gender slice (legacy gender-ratio pie parity). */
const GENDER_COLORS = [ '#3b82f6', '#ec4899', '#a78bfa', '#f59e0b', '#22c55e' ];

/** Stacked-bar series colours (mirrors the legacy Flot by-department stack). */
const DEPT_CONFIG = {
	male:   { label: __( 'Male', 'erp' ),        color: '#648d9e' },
	female: { label: __( 'Female', 'erp' ),      color: '#D797AF' },
	other:  { label: __( 'Unspecified', 'erp' ), color: '#AAC6D4' },
};

/** Stable colour for slice `i` — never undefined (satisfies ChartConfig). */
function genderColor( i: number ): string {
	return GENDER_COLORS[ i % GENDER_COLORS.length ] ?? '#3b82f6';
}

export function GenderProfilePage(): JSX.Element {
	const { data, loading, error } = useReport< GenderProfileResponse >( '/reports/gender-profile' );
	const rows = data?.rows ?? [];
	const byDepartment = data?.by_department ?? [];

	// Only chart departments that have at least one counted employee.
	const deptChart = useMemo(
		() => byDepartment.filter( ( d ) => d.male + d.female + d.other > 0 ),
		[ byDepartment ]
	);

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
				<div className="overflow-hidden rounded-lg border border-border bg-card shadow-sm">
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

			{ byDepartment.length > 0 ? (
				<div className="mt-6 border-t border-border pt-6">
					<h3 className="mb-3 text-sm font-semibold text-foreground">{ __( 'Employee Gender Ratio By Department', 'erp' ) }</h3>

					{ deptChart.length > 0 ? (
						<ChartContainer config={ DEPT_CONFIG } className="mb-5 h-[320px] w-full">
							<BarChart data={ deptChart as unknown as Record< string, unknown >[] } layout="vertical" margin={ { left: 12, right: 12, top: 8 } }>
								<CartesianGrid horizontal={ false } strokeDasharray="3 3" className="stroke-border" />
								<XAxis type="number" tickLine={ false } axisLine={ false } allowDecimals={ false } className="text-xs" />
								<YAxis type="category" dataKey="department" tickLine={ false } axisLine={ false } width={ 120 } className="text-xs" />
								<ChartTooltip content={ <ChartTooltipContent /> } />
								<Bar dataKey="male" stackId="g" fill="var(--color-male)" />
								<Bar dataKey="female" stackId="g" fill="var(--color-female)" />
								<Bar dataKey="other" stackId="g" fill="var(--color-other)" />
							</BarChart>
						</ChartContainer>
					) : null }

					<div className="overflow-hidden rounded-lg border border-border bg-card shadow-sm">
						<div className="overflow-x-auto">
							<table className="w-full min-w-[40rem] text-left">
								<thead className="border-b border-border bg-card">
									<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
										<th scope="col" className="px-4">{ __( 'Department', 'erp' ) }</th>
										<th scope="col" className="px-2 text-right">{ __( 'Male', 'erp' ) }</th>
										<th scope="col" className="px-2 text-right">{ __( 'Female', 'erp' ) }</th>
										<th scope="col" className="px-2 text-right">{ __( 'Unspecified', 'erp' ) }</th>
									</tr>
								</thead>
								<tbody>
									{ byDepartment.map( ( row, idx ) => (
										<tr key={ `${ row.department }-${ idx }` } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
											<td className="px-4 align-middle font-medium text-foreground">{ row.department || '—' }</td>
											<td className="px-2 text-right align-middle text-sm text-foreground">{ row.male }</td>
											<td className="px-2 text-right align-middle text-sm text-foreground">{ row.female }</td>
											<td className="px-2 text-right align-middle text-sm text-foreground">{ row.other }</td>
										</tr>
									) ) }
								</tbody>
							</table>
						</div>
					</div>
				</div>
			) : null }
			</ReportState>
		</ReportShell>
	);
}
