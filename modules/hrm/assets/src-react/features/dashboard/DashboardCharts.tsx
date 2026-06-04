/**
 * Dashboard analytics charts (recharts via plugin-ui's `ChartContainer`).
 *
 * - Headcount trend  → 12-month area chart
 * - Gender split     → donut
 * - By department    → horizontal bar
 * - Leave status     → donut (HR managers only)
 *
 * Colours are injected by `ChartContainer` as `--color-<key>` from the per-chart
 * config, so the recharts series reference `var(--color-…)`.
 */

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from '@wedevs/plugin-ui';
import { TrendingUp, Users, Building2, CalendarClock } from 'lucide-react';
import type { ComponentType, JSX, SVGProps } from 'react';
import {
	Area,
	AreaChart,
	Bar,
	BarChart,
	CartesianGrid,
	Cell,
	LabelList,
	Pie,
	PieChart,
	XAxis,
	YAxis,
} from 'recharts';

import { __ } from '@/shared/i18n';

import type { DashboardCharts } from './types';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

function monthTick( ym: string ): string {
	const [ y, m ] = ym.split( '-' ).map( Number );
	if ( ! y || ! m ) {
		return ym;
	}
	return new Date( y, m - 1, 1 ).toLocaleDateString( undefined, { month: 'short' } );
}

interface ChartCardProps {
	readonly icon:      LucideIcon;
	readonly title:     string;
	readonly subtitle?: string;
	readonly className?: string;
	readonly children:  React.ReactNode;
}

function ChartCard( { icon: Icon, title, subtitle, className, children }: ChartCardProps ): JSX.Element {
	return (
		<section className={ `flex flex-col rounded-[10px] bg-card p-6 shadow-sm ${ className ?? '' }` }>
			<header className="mb-4 flex items-center gap-2.5">
				<span className="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
					<Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
				</span>
				<div>
					<h2 className="text-base font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
					{ subtitle ? <p className="text-xs text-muted-foreground">{ subtitle }</p> : null }
				</div>
			</header>
			<div className="flex-1">{ children }</div>
		</section>
	);
}

function EmptyChart( { text }: { text: string } ): JSX.Element {
	return <p className="flex h-[220px] items-center justify-center text-sm text-muted-foreground">{ text }</p>;
}

const HEADCOUNT_CONFIG = {
	count: { label: __( 'Headcount', 'erp' ), color: '#6366f1' },
};

const GENDER_CONFIG = {
	male:   { label: __( 'Male', 'erp' ), color: '#3b82f6' },
	female: { label: __( 'Female', 'erp' ), color: '#ec4899' },
	other:  { label: __( 'Unspecified', 'erp' ), color: '#a78bfa' },
};

const DEPT_CONFIG = {
	count: { label: __( 'Employees', 'erp' ), color: '#0ea5e9' },
};

const LEAVE_CONFIG = {
	approved: { label: __( 'Approved', 'erp' ), color: '#22c55e' },
	pending:  { label: __( 'Pending', 'erp' ), color: '#f59e0b' },
	rejected: { label: __( 'Rejected', 'erp' ), color: '#ef4444' },
};

interface ChartsSectionProps {
	readonly charts:    DashboardCharts;
	readonly isManager: boolean;
}

export function ChartsSection( { charts, isManager }: ChartsSectionProps ): JSX.Element {
	const genderData = [
		{ key: 'male', label: __( 'Male', 'erp' ), value: charts.gender.male, fill: 'var(--color-male)' },
		{ key: 'female', label: __( 'Female', 'erp' ), value: charts.gender.female, fill: 'var(--color-female)' },
		{ key: 'other', label: __( 'Unspecified', 'erp' ), value: charts.gender.other, fill: 'var(--color-other)' },
	].filter( ( d ) => d.value > 0 );

	const leaveData = [
		{ key: 'approved', label: __( 'Approved', 'erp' ), value: charts.leave_status.approved, fill: 'var(--color-approved)' },
		{ key: 'pending', label: __( 'Pending', 'erp' ), value: charts.leave_status.pending, fill: 'var(--color-pending)' },
		{ key: 'rejected', label: __( 'Rejected', 'erp' ), value: charts.leave_status.rejected, fill: 'var(--color-rejected)' },
	].filter( ( d ) => d.value > 0 );

	const deptData = charts.departments.map( ( d ) => ( { name: d.name, count: d.count } ) );

	return (
		<div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
			{ /* Headcount trend — spans 2 cols */ }
			<ChartCard
				icon={ TrendingUp }
				title={ __( 'Headcount Trend', 'erp' ) }
				subtitle={ __( 'Active employees, last 12 months', 'erp' ) }
				className="lg:col-span-2"
			>
				<ChartContainer config={ HEADCOUNT_CONFIG } className="h-[240px] w-full">
					<AreaChart data={ [ ...charts.headcount_trend ] } margin={ { left: 4, right: 12, top: 8 } }>
						<defs>
							<linearGradient id="fillHeadcount" x1="0" y1="0" x2="0" y2="1">
								<stop offset="5%" stopColor="var(--color-count)" stopOpacity={ 0.35 } />
								<stop offset="95%" stopColor="var(--color-count)" stopOpacity={ 0.03 } />
							</linearGradient>
						</defs>
						<CartesianGrid vertical={ false } strokeDasharray="3 3" className="stroke-border" />
						<XAxis
							dataKey="month"
							tickLine={ false }
							axisLine={ false }
							tickMargin={ 8 }
							tickFormatter={ monthTick }
							className="text-xs"
						/>
						<YAxis tickLine={ false } axisLine={ false } width={ 28 } allowDecimals={ false } className="text-xs" />
						<ChartTooltip content={ <ChartTooltipContent labelFormatter={ ( v ) => monthTick( String( v ) ) } /> } />
						<Area
							type="monotone"
							dataKey="count"
							stroke="var(--color-count)"
							strokeWidth={ 2 }
							fill="url(#fillHeadcount)"
						/>
					</AreaChart>
				</ChartContainer>
			</ChartCard>

			{ /* Gender donut */ }
			<ChartCard icon={ Users } title={ __( 'Gender Distribution', 'erp' ) }>
				{ genderData.length === 0 ? (
					<EmptyChart text={ __( 'No employee data.', 'erp' ) } />
				) : (
					<ChartContainer config={ GENDER_CONFIG } className="mx-auto aspect-square h-[240px]">
						<PieChart>
							<ChartTooltip content={ <ChartTooltipContent nameKey="key" hideLabel /> } />
							<Pie data={ genderData } dataKey="value" nameKey="key" innerRadius={ 55 } strokeWidth={ 4 }>
								{ genderData.map( ( d ) => <Cell key={ d.key } fill={ d.fill } /> ) }
							</Pie>
							<ChartLegend content={ <ChartLegendContent nameKey="key" /> } className="mt-2 flex-wrap" />
						</PieChart>
					</ChartContainer>
				) }
			</ChartCard>

			{ /* Department bar — spans 2 cols */ }
			<ChartCard
				icon={ Building2 }
				title={ __( 'Employees by Department', 'erp' ) }
				className={ isManager ? 'lg:col-span-2' : 'lg:col-span-3' }
			>
				{ deptData.length === 0 ? (
					<EmptyChart text={ __( 'No department data.', 'erp' ) } />
				) : (
					<ChartContainer config={ DEPT_CONFIG } className="h-[260px] w-full">
						<BarChart data={ deptData } layout="vertical" margin={ { left: 8, right: 24 } }>
							<CartesianGrid horizontal={ false } strokeDasharray="3 3" className="stroke-border" />
							<XAxis type="number" tickLine={ false } axisLine={ false } allowDecimals={ false } className="text-xs" />
							<YAxis
								type="category"
								dataKey="name"
								tickLine={ false }
								axisLine={ false }
								width={ 120 }
								className="text-xs"
							/>
							<ChartTooltip content={ <ChartTooltipContent hideLabel /> } />
							<Bar dataKey="count" fill="var(--color-count)" radius={ [ 0, 4, 4, 0 ] } barSize={ 18 }>
								<LabelList dataKey="count" position="right" className="fill-foreground text-xs" />
							</Bar>
						</BarChart>
					</ChartContainer>
				) }
			</ChartCard>

			{ /* Leave status donut — managers only */ }
			{ isManager ? (
				<ChartCard icon={ CalendarClock } title={ __( 'Leave Requests', 'erp' ) } subtitle={ __( 'This financial year', 'erp' ) }>
					{ leaveData.length === 0 ? (
						<EmptyChart text={ __( 'No leave requests yet.', 'erp' ) } />
					) : (
						<ChartContainer config={ LEAVE_CONFIG } className="mx-auto aspect-square h-[240px]">
							<PieChart>
								<ChartTooltip content={ <ChartTooltipContent nameKey="key" hideLabel /> } />
								<Pie data={ leaveData } dataKey="value" nameKey="key" innerRadius={ 55 } strokeWidth={ 4 }>
									{ leaveData.map( ( d ) => <Cell key={ d.key } fill={ d.fill } /> ) }
								</Pie>
								<ChartLegend content={ <ChartLegendContent nameKey="key" /> } className="mt-2 flex-wrap" />
							</PieChart>
						</ChartContainer>
					) }
				</ChartCard>
			) : null }
		</div>
	);
}
