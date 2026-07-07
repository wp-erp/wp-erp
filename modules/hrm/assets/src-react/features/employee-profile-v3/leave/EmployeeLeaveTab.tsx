/**
 * Leave tab for the single-employee profile (read-only).
 *
 * Top: per-policy balance for the current financial year (entitlement / spent /
 * available). Bottom: the leave request history with resolved status labels.
 * Requesting/approving leave is a follow-up; this surfaces what the v1 model
 * already tracks.
 */

import { Badge, Button, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Spinner, toast } from '@wedevs/plugin-ui';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';

import { LeaveRequestDialog } from './LeaveRequestDialog';
import { useEmployeeLeave } from './useEmployeeLeave';
import type { LeaveFilters } from './useEmployeeLeave';

// v1 leave-request status codes: 1 approved, 2 pending, 3 rejected.
function statusVariant( code: number | null ): 'success' | 'secondary' | 'destructive' {
	switch ( code ) {
		case 1:
			return 'success';
		case 3:
			return 'destructive';
		default:
			return 'secondary';
	}
}

function formatDate( iso: string | null ): string {
	if ( ! iso ) {
		return '—';
	}
	const date = new Date( iso );
	if ( Number.isNaN( date.getTime() ) ) {
		return '—';
	}
	return date.toLocaleDateString( undefined, { month: 'short', day: 'numeric' } );
}

function dateRange( start: string | null, end: string | null ): string {
	if ( ! start ) {
		return '—';
	}
	if ( ! end || start === end ) {
		return formatDate( start );
	}
	return `${ formatDate( start ) } – ${ formatDate( end ) }`;
}

function num( value: number ): string {
	return Number.isInteger( value ) ? String( value ) : value.toFixed( 2 );
}

const SELECT_CLASS =
	'h-8 cursor-pointer rounded-md border border-border bg-card pl-2 pr-6 text-xs font-medium text-foreground focus:border-primary focus:outline-none';

// Accent palette for the leave-balance cards (cycled by index).
const BALANCE_COLORS = [ '#3b82f6', '#ef4444', '#22c55e', '#ec4899', '#a78bfa', '#f59e0b' ];

export function EmployeeLeaveTab( { userId }: { readonly userId: number } ): JSX.Element {
	const [ filters, setFilters ] = useState< LeaveFilters >( {} );
	const { data, loading, error, refetch } = useEmployeeLeave( userId, filters );
	const meta = data?.meta;
	const canCreate = useCan( 'erp_leave_create_request' );
	const [ showRequest, setShowRequest ] = useState( false );

	if ( error ) {
		return <p className="rounded-lg border border-border bg-card p-6 text-sm text-destructive">{ error }</p>;
	}

	if ( loading || ! data ) {
		return (
			<div className="flex items-center justify-center gap-2 rounded-lg border border-border bg-card p-10 text-sm text-muted-foreground">
				<Spinner className="size-4" />
				{ __( 'Loading leave…', 'erp' ) }
			</div>
		);
	}

	return (
		<div className="space-y-6">
			{ /* Balance per policy */ }
			<section className="overflow-hidden rounded-[10px] bg-card shadow-sm">
				<header className="flex items-center justify-between gap-4 border-b border-border px-6 py-4">
					<h2 className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Leave Balance', 'erp' ) }</h2>
					{ canCreate ? (
						<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ () => setShowRequest( true ) }>
							<Plus size={ 14 } aria-hidden="true" />
							{ __( 'Request Leave', 'erp' ) }
						</Button>
					) : null }
				</header>
				{ data.summary.length === 0 ? (
					<p className="p-6 text-sm text-muted-foreground">{ __( 'No leave policies assigned.', 'erp' ) }</p>
				) : (
					<div className="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
						{ data.summary.map( ( row, i ) => {
							const color = BALANCE_COLORS[ i % BALANCE_COLORS.length ];
							const total = Number( row.total ) || 0;
							const spent = Number( row.spent ) || 0;
							const pctUsed = total > 0 ? Math.min( 100, Math.round( ( spent / total ) * 100 ) ) : 0;
							return (
								<div
									key={ row.policy }
									className="group rounded-[10px] border border-border bg-card p-4 transition-all hover:-translate-y-0.5 hover:shadow-md"
								>
									<div className="flex items-center justify-between gap-2">
										<span className="flex items-center gap-2 text-sm font-semibold text-foreground">
											<span className="size-2.5 shrink-0 rounded-full" style={ { backgroundColor: color } } aria-hidden="true" />
											{ row.policy || __( 'Policy', 'erp' ) }
										</span>
										<span className="shrink-0 text-xs font-medium text-muted-foreground">
											{ sprintf( __( '%s%% used', 'erp' ), String( pctUsed ) ) }
										</span>
									</div>
									<div className="mt-3 flex items-end gap-1.5">
										<span className="text-3xl font-bold leading-none text-foreground">{ num( row.available ) }</span>
										<span className="pb-0.5 text-xs text-muted-foreground">
											{ sprintf(
												/* translators: %s: total entitled days */
												__( 'of %s days left', 'erp' ),
												num( row.total )
											) }
										</span>
									</div>
									<div className="mt-3 h-2 w-full overflow-hidden rounded-full bg-muted">
										<div
											className="h-full rounded-full transition-all"
											style={ { width: `${ pctUsed }%`, backgroundColor: color } }
										/>
									</div>
									<div className="mt-2 flex items-center justify-between text-xs text-muted-foreground">
										<span>{ sprintf( __( 'Entitled %s', 'erp' ), num( row.entitlement ) ) }</span>
										<span>{ sprintf( __( 'Spent %s', 'erp' ), num( row.spent ) ) }</span>
									</div>
								</div>
							);
						} ) }
					</div>
				) }
			</section>

			{ /* Request history */ }
			<section className="rounded-[10px] bg-card p-6 shadow-sm">
				<div className="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-border pb-4">
					<h2 className="m-0 text-lg font-bold leading-tight tracking-tight text-foreground">{ __( 'Leave History', 'erp' ) }</h2>
					{ meta ? (
						<div className="flex flex-wrap items-center gap-2">
							<Select
								items={ meta.financial_years.map( ( y ) => ( { value: String( y.id ), label: y.name } ) ) }
								value={ String( filters.year ?? meta.current_year ) }
								onValueChange={ ( v ) => setFilters( ( f ) => ( { ...f, year: parseInt( String( v ), 10 ) } ) ) }
							>
								<SelectTrigger className={ SELECT_CLASS } aria-label={ __( 'Financial year', 'erp' ) }>
									<SelectValue placeholder={ __( 'Financial year', 'erp' ) } />
								</SelectTrigger>
								<SelectContent align="start" alignItemWithTrigger={ false }>
									{ meta.financial_years.map( ( y ) => (
										<SelectItem key={ y.id } value={ String( y.id ) }>{ y.name }</SelectItem>
									) ) }
								</SelectContent>
							</Select>
							<Select
								items={ [ { value: 'all', label: __( 'All Status', 'erp' ) }, ...meta.statuses.filter( ( s ) => s.value !== 'all' ).map( ( s ) => ( { value: s.value, label: s.label } ) ) ] }
								value={ filters.status ?? 'all' }
								onValueChange={ ( v ) => setFilters( ( f ) => ( { ...f, status: v == null ? '' : String( v ) } ) ) }
							>
								<SelectTrigger className={ SELECT_CLASS } aria-label={ __( 'Status', 'erp' ) }>
									<SelectValue placeholder={ __( 'Status', 'erp' ) } />
								</SelectTrigger>
								<SelectContent align="start" alignItemWithTrigger={ false }>
									<SelectItem value="all">{ __( 'All Status', 'erp' ) }</SelectItem>
									{ meta.statuses
										.filter( ( s ) => s.value !== 'all' )
										.map( ( s ) => (
											<SelectItem key={ s.value } value={ s.value }>{ s.label }</SelectItem>
										) ) }
								</SelectContent>
							</Select>
							<Select
								items={ [ { value: '0', label: __( 'All Policies', 'erp' ) }, ...meta.policies.map( ( p ) => ( { value: String( p.id ), label: p.name } ) ) ] }
								value={ String( filters.policy_id ?? 0 ) }
								onValueChange={ ( v ) => setFilters( ( f ) => ( { ...f, policy_id: parseInt( String( v ), 10 ) } ) ) }
							>
								<SelectTrigger className={ SELECT_CLASS } aria-label={ __( 'Policy', 'erp' ) }>
									<SelectValue placeholder={ __( 'Policy', 'erp' ) } />
								</SelectTrigger>
								<SelectContent align="start" alignItemWithTrigger={ false }>
									<SelectItem value="0">{ __( 'All Policies', 'erp' ) }</SelectItem>
									{ meta.policies.map( ( p ) => (
										<SelectItem key={ p.id } value={ String( p.id ) }>{ p.name }</SelectItem>
									) ) }
								</SelectContent>
							</Select>
						</div>
					) : null }
				</div>
				{ data.requests.length === 0 ? (
					<p className="py-6 text-sm text-muted-foreground">{ __( 'No leave requests found.', 'erp' ) }</p>
				) : (
					<div className="overflow-hidden rounded-lg border border-border bg-card shadow-sm">
						<div className="overflow-x-auto">
						<table className="w-full text-left">
							<thead className="border-b border-border bg-card">
								<tr className="h-10">
									<th scope="col" className="whitespace-nowrap px-4 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Date', 'erp' ) }</th>
									<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Policy', 'erp' ) }</th>
									<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Reason', 'erp' ) }</th>
									<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Request', 'erp' ) }</th>
									<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Status', 'erp' ) }</th>
								</tr>
							</thead>
							<tbody>
								{ data.requests.map( ( row ) => (
									<tr key={ row.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
										<td className="px-4 align-middle text-sm text-foreground">{ dateRange( row.start_date, row.end_date ) }</td>
										<td className="px-2 align-middle text-sm text-foreground">{ row.policy || '—' }</td>
										<td className="px-2 align-middle text-sm text-muted-foreground">{ row.reason || '—' }</td>
										<td className="px-2 align-middle text-sm text-foreground">
											{ row.duration || sprintf(
												/* translators: %s: number of days */
												__( '%s days', 'erp' ),
												num( row.days )
											) }
										</td>
										<td className="px-2 align-middle">
											{ row.status ? (
												<Badge variant={ statusVariant( row.status_code ) }>{ row.status }</Badge>
											) : '—' }
										</td>
									</tr>
								) ) }
							</tbody>
						</table>
					</div>
				</div>
				) }
			</section>

			<LeaveRequestDialog
				open={ showRequest }
				userId={ userId }
				financialYears={ meta?.financial_years ?? [] }
				currentYear={ meta?.current_year ?? 0 }
				onClose={ () => setShowRequest( false ) }
				onSubmitted={ () => {
					toast.success( __( 'Leave request submitted.', 'erp' ) );
					setShowRequest( false );
					refetch();
				} }
			/>
		</div>
	);
}
