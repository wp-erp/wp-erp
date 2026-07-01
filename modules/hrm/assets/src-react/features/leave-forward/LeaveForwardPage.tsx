/**
 * Forward ("Forward Leaves") page — React parity of the legacy Advanced Leave
 * `LeaveForwardListTable`.
 *
 * Two modes, exactly like the legacy page:
 *  - `pending`: previews the carry-forward / encashment split per eligible
 *    employee for the previous financial year, with an "Apply" action that
 *    persists the encashment + entitlement records.
 *  - `applied`: once applied, lists the encashment requests and offers a CSV
 *    export.
 *
 * Consumes the pro `erp/v2/hrm/advance-leave/forward` endpoints; the page only
 * appears in the nav when the Advanced Leave module is active. Layout follows
 * the free leave-policy list design system (header + bordered card + shared
 * table tokens).
 */

import {
	Button,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	toast,
} from '@wedevs/plugin-ui';
import { Download } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import type { JSX, ReactNode } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

interface ForwardRow {
	readonly user_id:          number;
	readonly employee_name:    string;
	readonly policy_name:      string;
	readonly available?:       number;
	readonly max_encash_days?: number;
	readonly max_carry_days?:  number;
	readonly encash_days:      number;
	readonly forward_days:     number;
	readonly amount:           number;
	readonly total:            number;
}

interface ForwardResponse {
	readonly mode:   'pending' | 'applied';
	readonly f_year: string;
	readonly rows:   readonly ForwardRow[];
}

function LeaveForwardInner(): JSX.Element {
	const canManage = useCan( 'erp_leave_manage' );

	const [ data, setData ]       = useState< ForwardResponse | null >( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );
	const [ confirm, setConfirm ] = useState( false );
	const [ busy, setBusy ]       = useState( false );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const res = await request< ForwardResponse >( restPath( 'v2', '/hrm/advance-leave/forward' ) );
			setData( { mode: res.mode, f_year: res.f_year, rows: Array.isArray( res.rows ) ? res.rows : [] } );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not load forward leaves.', 'erp' ) );
		} finally {
			setLoading( false );
		}
	}, [] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	async function handleApply(): Promise< void > {
		setBusy( true );
		try {
			await request( restPath( 'v2', '/hrm/advance-leave/forward/apply' ), { method: 'POST' } );
			setConfirm( false );
			await reload();
			toast.success( __( 'Forward leaves applied.', 'erp' ) );
		} catch ( raw ) {
			setConfirm( false );
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not apply forward leaves.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	function handleExport(): void {
		const rows   = data?.rows ?? [];
		const header = [ 'Employee', 'Leave', 'Encash days', 'Forward days', 'Amount', 'Total' ];
		const lines  = rows.map( ( r ) =>
			[ r.employee_name, r.policy_name, r.encash_days, r.forward_days, r.amount, r.total ]
				.map( ( c ) => `"${ String( c ).replace( /"/g, '""' ) }"` )
				.join( ',' )
		);
		downloadCsv( [ header.join( ',' ), ...lines ].join( '\n' ), 'encash-requests.csv' );
	}

	const mode    = data?.mode ?? 'pending';
	const rows    = data?.rows ?? [];
	const pending = mode === 'pending';

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">{ __( 'Forward Leaves', 'erp' ) }</h1>
				{ canManage ? (
					pending ? (
						<Button
							className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
							disabled={ busy || rows.length === 0 }
							onClick={ () => setConfirm( true ) }
						>
							{ data?.f_year ? sprintf( __( 'Apply for %s', 'erp' ), data.f_year ) : __( 'Apply', 'erp' ) }
						</Button>
					) : (
						<Button
							variant="outline"
							className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5"
							disabled={ rows.length === 0 }
							onClick={ handleExport }
						>
							<Download size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
							{ __( 'Export Encash Requests', 'erp' ) }
						</Button>
					)
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Forward Leaves', 'erp' ) } className="flex items-stretch">
						<span role="tab" aria-selected="true" className="relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium text-primary">
							<span>{ pending ? __( 'Pending', 'erp' ) : __( 'Applied', 'erp' ) }</span>
							<span className="font-normal text-[#a5a5aa]">({ rows.length })</span>
							<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
						</span>
					</div>
				</div>

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">{ __( 'No forward leaves to process.', 'erp' ) }</p>
				) : (
					<div className="overflow-x-auto">
						<table className="w-full min-w-3xl text-left">
							<thead className="border-b border-border bg-card">
								<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
									<Th className="whitespace-nowrap px-4">{ __( 'Employee', 'erp' ) }</Th>
									<Th>{ __( 'Policy', 'erp' ) }</Th>
									{ pending ? <Th>{ __( 'Available', 'erp' ) }</Th> : null }
									{ pending ? <Th>{ __( 'Max Encash', 'erp' ) }</Th> : null }
									{ pending ? <Th>{ __( 'Max Carry', 'erp' ) }</Th> : null }
									<Th>{ __( 'Encash', 'erp' ) }</Th>
									<Th>{ __( 'Forward', 'erp' ) }</Th>
									<Th>{ __( 'Amount', 'erp' ) }</Th>
									<Th>{ __( 'Total', 'erp' ) }</Th>
								</tr>
							</thead>
							<tbody>
								{ rows.map( ( r, i ) => (
									<tr key={ `${ r.user_id }-${ i }` } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
										<td className="px-4 align-middle text-sm font-medium text-foreground">{ r.employee_name }</td>
										<td className="px-2 align-middle text-sm text-muted-foreground">{ r.policy_name }</td>
										{ pending ? <td className="px-2 align-middle text-sm text-foreground">{ r.available ?? 0 }</td> : null }
										{ pending ? <td className="px-2 align-middle text-sm text-muted-foreground">{ r.max_encash_days ?? 0 }</td> : null }
										{ pending ? <td className="px-2 align-middle text-sm text-muted-foreground">{ r.max_carry_days ?? 0 }</td> : null }
										<td className="px-2 align-middle text-sm text-foreground">{ r.encash_days }</td>
										<td className="px-2 align-middle text-sm text-foreground">{ r.forward_days }</td>
										<td className="px-2 align-middle text-sm text-muted-foreground">{ r.amount }</td>
										<td className="px-2 align-middle text-sm font-medium text-foreground">{ r.total }</td>
									</tr>
								) ) }
							</tbody>
						</table>
					</div>
				) }
			</div>

			<Dialog open={ confirm } onOpenChange={ ( next ) => ( busy ? undefined : setConfirm( next ) ) }>
				<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-md">
					<DialogHeader>
						<DialogTitle className="m-0 mb-4 text-xl font-bold leading-tight tracking-tight text-foreground">
							{ __( 'Apply forward leaves?', 'erp' ) }
						</DialogTitle>
						<DialogDescription>
							{ __( 'This carries forward / encashes the listed leaves for the previous financial year and cannot be undone.', 'erp' ) }
						</DialogDescription>
					</DialogHeader>
					<DialogFooter className="gap-3 sm:gap-3">
						<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ () => setConfirm( false ) }>
							{ __( 'Cancel', 'erp' ) }
						</Button>
						<Button type="button" className="h-10 px-6" disabled={ busy } onClick={ () => void handleApply() }>
							{ busy ? __( 'Applying…', 'erp' ) : __( 'Apply', 'erp' ) }
						</Button>
					</DialogFooter>
				</DialogContent>
			</Dialog>
		</section>
	);
}

export function LeaveForwardPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_leave_manage' ] }>
			<ErrorBoundary>
				<LeaveForwardInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}

function Th( { children, className = 'whitespace-nowrap px-2' }: { children: ReactNode; className?: string } ): JSX.Element {
	return <th scope="col" className={ className }>{ children }</th>;
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
