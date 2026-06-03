/**
 * `/reports/headcount` — headcount by month + active employee list.
 *
 * Mirrors views/reporting/headcount.php: a year + department filter, the active
 * total, a 12-month headcount series (rendered as a simple bar list instead of
 * the legacy flot chart), and the filtered active employee table. Data from
 * `GET /reports/headcount?year=&department=`.
 */

import { SmartSelect } from '@wedevs/plugin-ui';
import { Filter, Users } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { ReportShell, ReportState } from './ReportShell';
import { useHeadcount } from './useReports';

function fmtDate( value: string | null ): string {
	if ( ! value ) {
		return '—';
	}
	const d = new Date( value );
	if ( Number.isNaN( d.getTime() ) ) {
		return value.slice( 0, 10 );
	}
	return d.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } );
}

function monthLabel( ym: string ): string {
	const [ y, m ] = ym.split( '-' ).map( Number );
	if ( ! y || ! m ) {
		return ym;
	}
	return new Date( y, m - 1, 1 ).toLocaleDateString( undefined, { month: 'short', year: '2-digit' } );
}

export function HeadcountPage(): JSX.Element {
	const now = new Date().getFullYear();
	const [ year, setYear ]             = useState( String( now ) );
	const [ department, setDepartment ] = useState( 0 );
	const [ showFilters, setShowFilters ] = useState( false );

	const { data, loading, error } = useHeadcount( year, department );

	const yearOptions = useMemo( () => {
		const ys = data?.years ?? [];
		return ys.map( ( y ) => ( { value: String( y ), label: String( y ) } ) );
	}, [ data ] );

	const deptOptions = useMemo(
		() => [
			{ value: '', label: __( 'All Departments', 'erp' ) },
			...( data?.departments ?? [] ).map( ( d ) => ( { value: String( d.id ), label: d.label } ) ),
		],
		[ data ]
	);

	const maxCount = useMemo( () => Math.max( 1, ...( data?.chart ?? [] ).map( ( p ) => p.count ) ), [ data ] );

	const activeFilterCount = ( department ? 1 : 0 );
	const filterButtonActive = showFilters || activeFilterCount > 0;

	const toolbar = (
		<div className="space-y-3">
			<div className="flex flex-wrap items-center justify-between gap-3">
				<div className="flex items-center gap-3">
					<span className="inline-flex items-center gap-2 rounded-md bg-primary/10 px-3 py-1.5 text-sm font-medium text-primary">
						<Users size={ 16 } aria-hidden="true" />
						{ __( 'Total Employees', 'erp' ) }: { data?.total ?? 0 }
					</span>
				</div>
				<div className="flex items-center gap-3">
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Year', 'erp' ) }
						<SmartSelect
							options={ yearOptions }
							value={ year }
							onValueChange={ ( v ) => setYear( v || String( now ) ) }
							placeholder={ String( now ) }
							className="h-9 w-32"
							contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
						/>
					</label>
					<button
						type="button"
						aria-label={ __( 'Toggle filters', 'erp' ) }
						aria-pressed={ filterButtonActive }
						onClick={ () => setShowFilters( ( prev ) => ! prev ) }
						className={ [
							'inline-flex h-9 items-center gap-2 rounded-md border bg-card px-3 text-sm font-medium transition-colors',
							filterButtonActive ? 'border-primary text-primary' : 'border-border text-muted-foreground hover:text-foreground',
						].join( ' ' ) }
					>
						<Filter size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
						<span>{ __( 'Filter', 'erp' ) }</span>
						{ activeFilterCount > 0 ? (
							<span className="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-medium text-primary-foreground">
								{ activeFilterCount }
							</span>
						) : null }
					</button>
				</div>
			</div>
			{ filterButtonActive ? (
				<div className="flex flex-wrap items-center gap-2 rounded-md border border-border bg-muted/20 p-3">
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Department', 'erp' ) }
						<SmartSelect
							options={ deptOptions }
							value={ String( department || '' ) }
							onValueChange={ ( v ) => setDepartment( Number( v || 0 ) ) }
							placeholder={ __( 'All Departments', 'erp' ) }
							showClear
							className="h-9 w-56"
							contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
						/>
					</label>
				</div>
			) : null }
		</div>
	);

	return (
		<ReportShell title={ __( 'Head Count', 'erp' ) } toolbar={ toolbar }>
			<ReportState loading={ loading } error={ error } empty={ false }>
				{ /* Headcount by month — simple horizontal bar list. */ }
				<div className="mb-5 border-b border-border pb-5">
					<h3 className="mb-3 text-sm font-semibold text-foreground">{ __( 'Headcount by Month', 'erp' ) }</h3>
					<ul className="space-y-1.5">
						{ ( data?.chart ?? [] ).map( ( point ) => (
							<li key={ point.month } className="flex items-center gap-3 text-sm">
								<span className="w-16 shrink-0 text-muted-foreground">{ monthLabel( point.month ) }</span>
								<span className="relative h-4 flex-1 overflow-hidden rounded bg-muted">
									<span
										className="absolute inset-y-0 left-0 rounded bg-primary"
										style={ { width: `${ ( point.count / maxCount ) * 100 }%` } }
									/>
								</span>
								<span className="w-8 shrink-0 text-right tabular-nums text-foreground">{ point.count }</span>
							</li>
						) ) }
					</ul>
				</div>

				{ ( data?.employees ?? [] ).length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ __( 'No employees match these filters.', 'erp' ) }
					</p>
				) : (
					<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								<th scope="col" className="px-4">{ __( 'Name', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Hire Date', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Job Title', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Department', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Location', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Status', 'erp' ) }</th>
							</tr>
						</thead>
						<tbody>
							{ ( data?.employees ?? [] ).map( ( emp ) => (
								<tr key={ emp.user_id } className="h-12 border-b border-border last:border-b-0 hover:bg-muted/40">
									<td className="px-4 align-middle font-medium text-foreground">{ emp.name }</td>
									<td className="whitespace-nowrap px-4 align-middle text-sm text-muted-foreground">{ fmtDate( emp.hire_date ) }</td>
									<td className="px-4 align-middle text-sm text-foreground">{ emp.designation ?? '—' }</td>
									<td className="px-4 align-middle text-sm text-foreground">{ emp.department ?? '—' }</td>
									<td className="px-4 align-middle text-sm text-foreground">{ emp.location ?? '—' }</td>
									<td className="px-4 align-middle text-sm capitalize text-muted-foreground">{ emp.status ?? '—' }</td>
								</tr>
							) ) }
						</tbody>
					</table>
					</div>
				) }
			</ReportState>
		</ReportShell>
	);
}
