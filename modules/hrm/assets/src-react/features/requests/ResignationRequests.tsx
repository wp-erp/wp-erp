/**
 * Resignation requests — the React admin list for the People → Requests
 * "Resignation" tab. Replaces the placeholder once the pro Resignation feature is
 * active (the `erp/v2/hrm/resignations` controller). Reads/writes the same
 * `erp_hr_employee_resign_requests` table as the legacy Vue requests screen, so
 * both UIs stay in sync. A manager can review (approve / reject / delete) and file
 * a request on an employee's behalf — mirroring the legacy flow.
 */

import {
	Badge,
	Button,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	SmartSelect,
	Skeleton,
	toast,
} from '@wedevs/plugin-ui';
import { Check, Plus, Trash2, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';
import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

interface ResignRow {
	readonly id:        number;
	readonly employee:  { readonly id: number; readonly name: string };
	readonly reason:    string;
	readonly date:      string;
	readonly status:    string;
	readonly updatedBy: string;
}
interface ReasonOption { readonly value: string; readonly label: string }

const BASE = '/hrm/resignations';

function statusTone( s: string ): string {
	if ( 'approved' === s ) return 'bg-success/15 text-success';
	if ( 'rejected' === s ) return 'bg-destructive/15 text-destructive';
	return 'bg-muted text-muted-foreground';
}

export function ResignationRequests(): JSX.Element {
	const [ rows, setRows ]       = useState< ResignRow[] >( [] );
	const [ loading, setLoading ] = useState( true );
	const [ busy, setBusy ]       = useState( false );
	const [ creating, setCreating ] = useState( false );

	function load(): void {
		setLoading( true );
		request< { items: ResignRow[] } >( restPath( 'v2', BASE ) )
			.then( ( r ) => setRows( [ ...r.items ] ) )
			.catch( ( e ) => toast.error( ( e as ApiError )?.message || __( 'Could not load resignation requests.', 'erp' ) ) )
			.finally( () => setLoading( false ) );
	}
	useEffect( load, [] );

	function act( id: number, kind: 'approve' | 'reject' | 'delete' ): void {
		setBusy( true );
		const p = 'delete' === kind
			? request( restPath( 'v2', `${ BASE }/${ id }` ), { method: 'DELETE' } )
			: request( restPath( 'v2', `${ BASE }/${ id }/${ kind }` ), { method: 'POST', data: {} } );
		p
			.then( () => { toast.success( __( 'Done.', 'erp' ) ); load(); } )
			.catch( ( e: ApiError ) => toast.error( e.message || __( 'Action failed.', 'erp' ) ) )
			.finally( () => setBusy( false ) );
	}

	return (
		<div className="rounded-lg border border-border bg-card shadow-sm">
			<div className="flex items-center justify-between border-b border-border px-4 py-3">
				<h2 className="m-0 text-sm font-semibold text-foreground">{ __( 'Resignation Requests', 'erp' ) }</h2>
				<Button size="sm" className="h-8 gap-1.5" onClick={ () => setCreating( true ) }>
					<Plus size={ 14 } aria-hidden="true" />{ __( 'New Request', 'erp' ) }
				</Button>
			</div>

			{ loading ? (
				<div className="space-y-2 p-4"><Skeleton className="h-6 w-full" /><Skeleton className="h-6 w-full" /></div>
			) : rows.length === 0 ? (
				<p className="px-4 py-12 text-center text-sm text-muted-foreground">{ __( 'No resignation requests.', 'erp' ) }</p>
			) : (
				<div className="overflow-x-auto">
					<table className="w-full min-w-[40rem] text-left text-sm">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10">
								<th className="px-4 text-xs font-medium uppercase tracking-normal text-muted-foreground">{ __( 'Employee', 'erp' ) }</th>
								<th className="px-2 text-xs font-medium uppercase tracking-normal text-muted-foreground">{ __( 'Reason', 'erp' ) }</th>
								<th className="px-2 text-xs font-medium uppercase tracking-normal text-muted-foreground">{ __( 'Date', 'erp' ) }</th>
								<th className="px-2 text-xs font-medium uppercase tracking-normal text-muted-foreground">{ __( 'Status', 'erp' ) }</th>
								<th className="px-2 text-right text-xs font-medium uppercase tracking-normal text-muted-foreground">{ __( 'Actions', 'erp' ) }</th>
							</tr>
						</thead>
						<tbody>
							{ rows.map( ( r ) => (
								<tr key={ r.id } className="border-b border-border/60 last:border-b-0">
									<td className="px-4 py-2 font-medium text-foreground">{ r.employee.name || '—' }</td>
									<td className="px-2 py-2 text-muted-foreground">{ r.reason || '—' }</td>
									<td className="px-2 py-2 text-muted-foreground">{ r.date || '—' }</td>
									<td className="px-2 py-2"><Badge variant="secondary" className={ `capitalize ${ statusTone( r.status ) }` }>{ r.status || '—' }</Badge></td>
									<td className="px-2 py-2 text-right">
										<div className="inline-flex items-center gap-1">
											{ 'pending' === r.status ? (
												<>
													<Button variant="ghost" size="icon" className="h-8 w-8 text-success hover:text-success" disabled={ busy } onClick={ () => act( r.id, 'approve' ) } aria-label={ __( 'Approve', 'erp' ) }><Check size={ 14 } /></Button>
													<Button variant="ghost" size="icon" className="h-8 w-8 text-warning hover:text-warning" disabled={ busy } onClick={ () => act( r.id, 'reject' ) } aria-label={ __( 'Reject', 'erp' ) }><X size={ 14 } /></Button>
												</>
											) : null }
											<Button variant="ghost" size="icon" className="h-8 w-8 text-destructive hover:text-destructive" disabled={ busy } onClick={ () => act( r.id, 'delete' ) } aria-label={ __( 'Delete', 'erp' ) }><Trash2 size={ 14 } /></Button>
										</div>
									</td>
								</tr>
							) ) }
						</tbody>
					</table>
				</div>
			) }

			{ creating ? <NewResignationDialog onClose={ () => setCreating( false ) } onSaved={ () => { setCreating( false ); load(); } } /> : null }
		</div>
	);
}

function NewResignationDialog( { onClose, onSaved }: { readonly onClose: () => void; readonly onSaved: () => void } ): JSX.Element {
	const [ employeeId, setEmployeeId ] = useState( '' );
	const employee = useEmployeeSearch( true, undefined, employeeId );
	const [ reasons, setReasons ] = useState< ReasonOption[] >( [] );
	const [ reason, setReason ]   = useState( '' );
	const [ date, setDate ]       = useState( new Date().toISOString().slice( 0, 10 ) );
	const [ busy, setBusy ]       = useState( false );

	useEffect( () => {
		request< { items: ReasonOption[] } >( restPath( 'v2', `${ BASE }/reasons` ) )
			.then( ( r ) => setReasons( [ ...r.items ] ) )
			.catch( () => undefined );
	}, [] );

	function submit(): void {
		if ( ! employeeId ) { toast.error( __( 'Select an employee.', 'erp' ) ); return; }
		setBusy( true );
		request( restPath( 'v2', BASE ), { method: 'POST', data: { user_id: Number( employeeId ), reason, date } } )
			.then( () => { toast.success( __( 'Resignation request submitted.', 'erp' ) ); onSaved(); } )
			.catch( ( e: ApiError ) => toast.error( e.message || __( 'Could not submit.', 'erp' ) ) )
			.finally( () => setBusy( false ) );
	}

	return (
		<Dialog open onOpenChange={ ( o ) => ( o || busy ? undefined : onClose() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-md">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'New Resignation Request', 'erp' ) }</DialogTitle>
					<DialogDescription>{ __( 'File a resignation request on an employee\'s behalf.', 'erp' ) }</DialogDescription>
				</DialogHeader>

				<div className="flex flex-col gap-2.5">
					<label className="text-sm font-medium text-foreground">{ __( 'Employee', 'erp' ) }</label>
					<SmartSelect options={ employee.options } value={ employeeId } onValueChange={ ( v ) => setEmployeeId( v ?? '' ) } onSearch={ employee.onSearch } placeholder={ __( 'Select employee', 'erp' ) } searchPlaceholder={ __( 'Search…', 'erp' ) } emptyMessage={ __( 'No employees found.', 'erp' ) } className="h-10 w-full" />
				</div>
				<div className="flex flex-col gap-2.5">
					<label className="text-sm font-medium text-foreground">{ __( 'Reason', 'erp' ) }</label>
					<SmartSelect options={ reasons } value={ reason } onValueChange={ ( v ) => setReason( v ?? '' ) } placeholder={ __( 'Select reason', 'erp' ) } searchPlaceholder={ __( 'Search…', 'erp' ) } emptyMessage={ __( 'No reasons.', 'erp' ) } className="h-10 w-full" />
				</div>
				<div className="flex flex-col gap-2.5">
					<label htmlFor="resign_date" className="text-sm font-medium text-foreground">{ __( 'Resignation Date', 'erp' ) }</label>
					<input id="resign_date" type="date" value={ date } onChange={ ( e ) => setDate( e.target.value ) } className="h-10 rounded-md border border-border bg-background px-3 text-sm" />
				</div>

				<DialogFooter className="gap-3">
					<Button variant="outline" disabled={ busy } onClick={ onClose }>{ __( 'Cancel', 'erp' ) }</Button>
					<Button disabled={ busy } onClick={ submit }>{ busy ? __( 'Submitting…', 'erp' ) : __( 'Submit', 'erp' ) }</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}
