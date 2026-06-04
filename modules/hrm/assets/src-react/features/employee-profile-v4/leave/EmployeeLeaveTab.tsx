/**
 * Leave tab for the single-employee profile (read-only).
 *
 * Top: per-policy balance for the current financial year (entitlement / spent /
 * available). Bottom: the leave request history with resolved status labels.
 * Requesting/approving leave is a follow-up; this surfaces what the v1 model
 * already tracks.
 */

import { Badge, Button, Spinner, toast } from '@wedevs/plugin-ui';
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
				<header className="flex items-center justify-between gap-4 px-6 py-4">
					<h2 className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Leave Balance', 'erp' ) }</h2>
					{ canCreate ? (
						<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ () => setShowRequest( true ) }>
							<Plus size={ 14 } aria-hidden="true" />
							{ __( 'Request Leave', 'erp' ) }
						</Button>
					) : null }
				</header>
				<div className="mx-6 h-px bg-border" />
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
										<span className="shrink-0 text-xs font-medium text-muted-foreground tabular-nums">
											{ sprintf( __( '%s%% used', 'erp' ), String( pctUsed ) ) }
										</span>
									</div>
									<div className="mt-3 flex items-end gap-1.5">
										<span className="text-3xl font-bold leading-none tabular-nums text-foreground">{ num( row.available ) }</span>
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
			<section className="overflow-hidden rounded-[10px] bg-card shadow-sm">
				<header className="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
					<h2 className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Leave History', 'erp' ) }</h2>
					{ meta ? (
						<div className="flex flex-wrap items-center gap-2">
							<select
								className={ SELECT_CLASS }
								value={ filters.year ?? meta.current_year }
								onChange={ ( e ) => setFilters( ( f ) => ( { ...f, year: parseInt( e.target.value, 10 ) } ) ) }
								aria-label={ __( 'Financial year', 'erp' ) }
							>
								{ meta.financial_years.map( ( y ) => (
									<option key={ y.id } value={ y.id }>{ y.name }</option>
								) ) }
							</select>
							<select
								className={ SELECT_CLASS }
								value={ filters.status ?? 'all' }
								onChange={ ( e ) => setFilters( ( f ) => ( { ...f, status: e.target.value } ) ) }
								aria-label={ __( 'Status', 'erp' ) }
							>
								<option value="all">{ __( 'All Status', 'erp' ) }</option>
								{ meta.statuses
									.filter( ( s ) => s.value !== 'all' )
									.map( ( s ) => (
										<option key={ s.value } value={ s.value }>{ s.label }</option>
									) ) }
							</select>
							<select
								className={ SELECT_CLASS }
								value={ filters.policy_id ?? 0 }
								onChange={ ( e ) => setFilters( ( f ) => ( { ...f, policy_id: parseInt( e.target.value, 10 ) } ) ) }
								aria-label={ __( 'Policy', 'erp' ) }
							>
								<option value={ 0 }>{ __( 'All Policies', 'erp' ) }</option>
								{ meta.policies.map( ( p ) => (
									<option key={ p.id } value={ p.id }>{ p.name }</option>
								) ) }
							</select>
						</div>
					) : null }
				</header>
				<div className="mx-6 h-px bg-border" />
				{ data.requests.length === 0 ? (
					<p className="p-6 text-sm text-muted-foreground">{ __( 'No leave requests found.', 'erp' ) }</p>
				) : (
					<div className="overflow-x-auto">
						<table className="w-full text-left">
							<thead className="border-b border-border bg-muted/40">
								<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
									<th scope="col" className="px-6">{ __( 'Date', 'erp' ) }</th>
									<th scope="col" className="px-6">{ __( 'Policy', 'erp' ) }</th>
									<th scope="col" className="px-6">{ __( 'Reason', 'erp' ) }</th>
									<th scope="col" className="px-6">{ __( 'Request', 'erp' ) }</th>
									<th scope="col" className="px-6">{ __( 'Status', 'erp' ) }</th>
								</tr>
							</thead>
							<tbody>
								{ data.requests.map( ( row ) => (
									<tr key={ row.id } className="h-12 border-b border-border last:border-b-0">
										<td className="px-6 align-middle text-sm text-foreground">{ dateRange( row.start_date, row.end_date ) }</td>
										<td className="px-6 align-middle text-sm text-foreground">{ row.policy || '—' }</td>
										<td className="px-6 align-middle text-sm text-muted-foreground">{ row.reason || '—' }</td>
										<td className="px-6 align-middle text-sm text-foreground">
											{ row.duration || sprintf(
												/* translators: %s: number of days */
												__( '%s days', 'erp' ),
												num( row.days )
											) }
										</td>
										<td className="px-6 align-middle">
											{ row.status ? (
												<Badge variant={ statusVariant( row.status_code ) }>{ row.status }</Badge>
											) : '—' }
										</td>
									</tr>
								) ) }
							</tbody>
						</table>
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
