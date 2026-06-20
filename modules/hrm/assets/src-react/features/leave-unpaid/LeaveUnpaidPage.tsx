/**
 * Unpaid ("Extra Leaves") page — React parity of the legacy Advanced Leave
 * `LeaveUnpaidListTable`.
 *
 * Lists unpaid leaves for a financial year, lets a manager bulk-calculate the
 * monetary value (per-day pay rate) or edit a single row's amount, and exports
 * the list to CSV. Consumes the pro `erp/v2/hrm/advance-leave/unpaid` endpoints
 * (provided by the Advanced Leave pro module); the page only appears in the nav
 * when that module is active.
 *
 * Layout mirrors the free leave-policy list (`LeavePoliciesPage`): top header +
 * a single bordered card with an "All (n)" tablist / filter row, the shared
 * table tokens, and `OrgPagination`.
 */

import { Button, SmartSelect, toast } from '@wedevs/plugin-ui';
import { Calculator, Download, Filter } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

import { OrgPagination } from '../org/OrgPagination';

interface UnpaidRow {
	readonly id:            number;
	readonly user_id:       number;
	readonly employee_name: string;
	readonly policy_name:   string;
	readonly days:          number;
	readonly f_year:        string;
	readonly start_date:    string;
	readonly end_date:      string;
	readonly amount:        number;
	readonly total:         number;
}

interface FinancialYear {
	readonly id:      number;
	readonly fy_name: string;
}

function LeaveUnpaidInner(): JSX.Element {
	const canManage = useCan( 'erp_leave_manage' );

	const [ years, setYears ]   = useState< readonly FinancialYear[] >( [] );
	const [ fYear, setFYear ]   = useState( 0 );
	const [ showFilters, setShowFilters ] = useState( false );
	const [ rows, setRows ]     = useState< readonly UnpaidRow[] >( [] );
	const [ total, setTotal ]   = useState( 0 );
	const [ page, setPage ]     = useState( 1 );
	const [ perPage, setPerPage ] = useState( 20 );
	const [ loading, setLoading ] = useState( true );
	const [ busy, setBusy ]     = useState( false );
	const [ error, setError ]   = useState< string | null >( null );

	// Load financial years once (reuse the leave-policies form-options endpoint).
	useEffect( () => {
		let cancelled = false;
		void request< { financial_years?: FinancialYear[]; current_f_year?: number } >(
			restPath( 'v2', '/leave-policies/form-options' )
		)
			.then( ( opts ) => {
				if ( cancelled ) {
					return;
				}
				const fys = Array.isArray( opts.financial_years ) ? opts.financial_years : [];
				setYears( fys );
				const current = Number( opts.current_f_year ?? 0 );
				if ( current && fys.some( ( fy ) => fy.id === current ) ) {
					setFYear( current );
				}
			} )
			.catch( () => undefined );
		return () => {
			cancelled = true;
		};
	}, [] );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const { body, headers } = await requestWithHeaders< UnpaidRow[] >(
				restPath( 'v2', '/hrm/advance-leave/unpaid', {
					...( fYear ? { f_year: fYear } : {} ),
					page,
					per_page: perPage,
				} )
			);
			const list = Array.isArray( body ) ? body : [];
			setRows( list );
			setTotal( toInt( headers.get( 'X-WP-Total' ), list.length ) );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not load unpaid leaves.', 'erp' ) );
		} finally {
			setLoading( false );
		}
	}, [ fYear, page, perPage ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	async function handleCalculate(): Promise< void > {
		setBusy( true );
		try {
			await request( restPath( 'v2', '/hrm/advance-leave/unpaid/calculate' ), {
				method: 'POST',
				data:   { f_year: fYear, salary_type: 'pay_rate' },
			} );
			await reload();
			toast.success( __( 'Amounts recalculated.', 'erp' ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not calculate amounts.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	async function handleAmountChange( id: number, value: string ): Promise< void > {
		const amount = Number( value );
		if ( Number.isNaN( amount ) || amount < 0 ) {
			return;
		}
		try {
			const res = await request< { total: number } >( restPath( 'v2', `/hrm/advance-leave/unpaid/${ id }` ), {
				method: 'PUT',
				data:   { amount },
			} );
			setRows( ( prev ) => prev.map( ( r ) => ( r.id === id ? { ...r, amount, total: res.total } : r ) ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not update the amount.', 'erp' ) );
		}
	}

	function handleExport(): void {
		const header = [ 'Policy Name', 'Employee Name', 'Days', 'Year', 'Start Date', 'End Date', 'Amount', 'Total' ];
		const lines  = rows.map( ( r ) =>
			[ r.policy_name, r.employee_name, r.days, r.f_year, r.start_date, r.end_date, r.amount, r.total ]
				.map( csvCell )
				.join( ',' )
		);
		downloadCsv( [ header.join( ',' ), ...lines ].join( '\n' ), 'unpaid-leaves.csv' );
	}

	const yearFilterOpts = years.map( ( fy ) => ( { value: String( fy.id ), label: fy.fy_name } ) );
	const totalPages     = Math.max( 1, Math.ceil( total / perPage ) );

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">{ __( 'Unpaid Leaves', 'erp' ) }</h1>
				{ canManage ? (
					<div className="flex items-center gap-3">
						<Button
							variant="outline"
							className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5"
							disabled={ busy || rows.length === 0 }
							onClick={ handleExport }
						>
							<Download size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
							{ __( 'Export CSV', 'erp' ) }
						</Button>
						<Button
							className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
							disabled={ busy }
							onClick={ () => void handleCalculate() }
						>
							<Calculator size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
							{ busy ? __( 'Calculating…', 'erp' ) : __( 'Calculate', 'erp' ) }
						</Button>
					</div>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Unpaid Leaves', 'erp' ) } className="flex items-stretch">
						<span role="tab" aria-selected="true" className="relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium text-primary">
							<span>{ __( 'All', 'erp' ) }</span>
							<span className="font-normal text-muted-foreground">({ total })</span>
							<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
						</span>
					</div>
					{ yearFilterOpts.length > 0 ? (
						<button
							type="button"
							aria-label={ __( 'Toggle filters', 'erp' ) }
							aria-pressed={ showFilters }
							onClick={ () => setShowFilters( ( prev ) => ! prev ) }
							className={ [
								'inline-flex h-9 items-center gap-2 rounded-md border bg-card px-3 text-sm font-medium transition-colors',
								showFilters || fYear ? 'border-primary text-primary' : 'border-border text-muted-foreground hover:text-foreground',
							].join( ' ' ) }
						>
							<Filter size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
							<span>{ __( 'Filter', 'erp' ) }</span>
						</button>
					) : null }
				</div>

				{ showFilters && yearFilterOpts.length > 0 ? (
					<div className="flex flex-wrap items-center gap-2 border-b border-border bg-muted/20 px-4 py-3">
						<label className="flex items-center gap-2 text-sm text-muted-foreground">
							{ __( 'Year', 'erp' ) }
							<SmartSelect
								options={ yearFilterOpts }
								value={ String( fYear || '' ) }
								onValueChange={ ( v ) => { setFYear( Number( v || 0 ) ); setPage( 1 ); } }
								placeholder={ __( 'All Years', 'erp' ) }
								showClear
								className="h-9 w-40 bg-background"
								contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
							/>
						</label>
					</div>
				) : null }

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">{ __( 'No unpaid leaves found.', 'erp' ) }</p>
				) : (
					<div className="overflow-x-auto">
						<table className="w-full min-w-3xl text-left">
							<thead className="border-b border-border bg-muted/40">
								<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
									<th scope="col" className="px-4">{ __( 'Employee', 'erp' ) }</th>
									<th scope="col" className="px-2">{ __( 'Policy', 'erp' ) }</th>
									<th scope="col" className="px-2">{ __( 'Days', 'erp' ) }</th>
									<th scope="col" className="px-2">{ __( 'Year', 'erp' ) }</th>
									<th scope="col" className="px-2">{ __( 'Start', 'erp' ) }</th>
									<th scope="col" className="px-2">{ __( 'End', 'erp' ) }</th>
									<th scope="col" className="px-2">{ __( 'Amount/day', 'erp' ) }</th>
									<th scope="col" className="px-2">{ __( 'Total', 'erp' ) }</th>
								</tr>
							</thead>
							<tbody>
								{ rows.map( ( r ) => (
									<tr key={ r.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
										<td className="px-4 align-middle text-sm font-medium text-foreground">{ r.employee_name }</td>
										<td className="px-2 align-middle text-sm text-muted-foreground">{ r.policy_name }</td>
										<td className="px-2 align-middle text-sm text-foreground">{ r.days }</td>
										<td className="px-2 align-middle text-sm text-muted-foreground">{ r.f_year }</td>
										<td className="px-2 align-middle text-sm text-muted-foreground">{ r.start_date }</td>
										<td className="px-2 align-middle text-sm text-muted-foreground">{ r.end_date }</td>
										<td className="px-2 align-middle">
											<input
												type="number"
												min="0"
												step="0.01"
												defaultValue={ String( r.amount ) }
												disabled={ ! canManage }
												onBlur={ ( e ) => void handleAmountChange( r.id, e.target.value ) }
												aria-label={ __( 'Amount per day', 'erp' ) }
												className="h-10 w-28 rounded-md border border-border bg-background px-4 text-sm focus:border-primary focus:outline-none"
											/>
										</td>
										<td className="px-2 align-middle text-sm font-medium text-foreground">{ r.total }</td>
									</tr>
								) ) }
							</tbody>
						</table>
					</div>
				) }

				{ ! error && ! loading && total > 0 ? (
					<OrgPagination
						page={ page }
						totalPages={ totalPages }
						total={ total }
						perPage={ perPage }
						onPage={ setPage }
						onPerPage={ ( n ) => { setPerPage( n ); setPage( 1 ); } }
					/>
				) : null }
			</div>
		</section>
	);
}

export function LeaveUnpaidPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_leave_manage' ] }>
			<ErrorBoundary>
				<LeaveUnpaidInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}

/** Quote a CSV cell. */
function csvCell( value: unknown ): string {
	return `"${ String( value ).replace( /"/g, '""' ) }"`;
}

/** Trigger a client-side CSV download. */
function downloadCsv( content: string, filename: string ): void {
	const blob = new Blob( [ content ], { type: 'text/csv;charset=utf-8;' } );
	const url  = URL.createObjectURL( blob );
	const a    = document.createElement( 'a' );
	a.href     = url;
	a.download = filename;
	document.body.appendChild( a );
	a.click();
	document.body.removeChild( a );
	URL.revokeObjectURL( url );
}
