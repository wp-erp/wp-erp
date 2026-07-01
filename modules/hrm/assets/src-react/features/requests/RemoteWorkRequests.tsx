/**
 * Remote-work requests — the React admin list for the People → Requests
 * "Remote Work" tab. Replaces the placeholder once the pro Remote Work feature is
 * active (the `erp/v2/hrm/remote-work` controller). Reads/writes the same
 * `erp_hr_employee_remote_work_requests` table as the legacy Vue requests screen,
 * so both UIs stay in sync. A manager can review (approve / reject / delete) and
 * file a request on an employee's behalf.
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

interface RemoteRow {
	readonly id:        number;
	readonly employee:  { readonly id: number; readonly name: string };
	readonly reason:    string;
	readonly startDate: string;
	readonly endDate:   string;
	readonly days:      number;
	readonly status:    string;
}
interface ReasonOption { readonly value: string; readonly label: string }

const BASE = '/hrm/remote-work';

function statusTone( s: string ): string {
	if ( 'approved' === s ) return 'bg-success/15 text-success';
	if ( 'rejected' === s ) return 'bg-destructive/15 text-destructive';
	return 'bg-muted text-muted-foreground';
}

export function RemoteWorkRequests(): JSX.Element {
	const [ rows, setRows ]       = useState< RemoteRow[] >( [] );
	const [ loading, setLoading ] = useState( true );
	const [ busy, setBusy ]       = useState( false );
	const [ creating, setCreating ] = useState( false );

	function load(): void {
		setLoading( true );
		request< { items: RemoteRow[] } >( restPath( 'v2', BASE ) )
			.then( ( r ) => setRows( [ ...r.items ] ) )
			.catch( ( e ) => toast.error( ( e as ApiError )?.message || __( 'Could not load remote work requests.', 'erp' ) ) )
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
		<div>
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="m-0 text-2xl font-bold leading-8 text-foreground">{ __( 'Remote Work Requests', 'erp' ) }</h1>
				<Button className="h-10 gap-1.5" onClick={ () => setCreating( true ) }>
					<Plus size={ 15 } aria-hidden="true" />{ __( 'New Request', 'erp' ) }
				</Button>
			</header>
			<div className="rounded-lg border border-border bg-card shadow-sm">

			{ loading ? (
				<div className="space-y-2 p-4"><Skeleton className="h-6 w-full" /><Skeleton className="h-6 w-full" /></div>
			) : rows.length === 0 ? (
				<p className="px-4 py-12 text-center text-sm text-muted-foreground">{ __( 'No remote work requests.', 'erp' ) }</p>
			) : (
				<div className="overflow-x-auto">
					<table className="w-full min-w-[44rem] text-left text-sm">
						<thead className="border-b border-border bg-card">
							<tr className="h-10">
								<th className="px-4 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Employee', 'erp' ) }</th>
								<th className="px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Reason', 'erp' ) }</th>
								<th className="px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'From', 'erp' ) }</th>
								<th className="px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'To', 'erp' ) }</th>
								<th className="px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Days', 'erp' ) }</th>
								<th className="px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Status', 'erp' ) }</th>
								<th className="pl-2 pr-4 text-right text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Actions', 'erp' ) }</th>
							</tr>
						</thead>
						<tbody>
							{ rows.map( ( r ) => (
								<tr key={ r.id } className="group h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
									<td className="px-4 align-middle text-sm font-medium text-foreground">{ r.employee.name || '—' }</td>
									<td className="px-2 align-middle text-sm text-muted-foreground">{ r.reason || '—' }</td>
									<td className="px-2 align-middle text-sm text-muted-foreground">{ r.startDate || '—' }</td>
									<td className="px-2 align-middle text-sm text-muted-foreground">{ r.endDate || '—' }</td>
									<td className="px-2 align-middle text-sm text-muted-foreground">{ r.days || '—' }</td>
									<td className="px-2 align-middle"><Badge variant="secondary" className={ `capitalize ${ statusTone( r.status ) }` }>{ r.status || '—' }</Badge></td>
									<td className="pl-2 pr-4 text-right align-middle">
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

			</div>

			{ creating ? <NewRemoteWorkDialog onClose={ () => setCreating( false ) } onSaved={ () => { setCreating( false ); load(); } } /> : null }
		</div>
	);
}

function NewRemoteWorkDialog( { onClose, onSaved }: { readonly onClose: () => void; readonly onSaved: () => void } ): JSX.Element {
	const [ employeeId, setEmployeeId ] = useState( '' );
	const employee = useEmployeeSearch( true, undefined, employeeId );
	const [ reasons, setReasons ] = useState< ReasonOption[] >( [] );
	const [ reason, setReason ]   = useState( '' );
	const [ other, setOther ]     = useState( '' );
	const [ from, setFrom ]       = useState( new Date().toISOString().slice( 0, 10 ) );
	const [ to, setTo ]           = useState( new Date().toISOString().slice( 0, 10 ) );
	const [ busy, setBusy ]       = useState( false );

	useEffect( () => {
		request< { items: ReasonOption[] } >( restPath( 'v2', `${ BASE }/reasons` ) )
			.then( ( r ) => setReasons( [ ...r.items ] ) )
			.catch( () => undefined );
	}, [] );

	function submit(): void {
		if ( ! employeeId ) { toast.error( __( 'Select an employee.', 'erp' ) ); return; }
		if ( to < from ) { toast.error( __( 'The end date must be on or after the start date.', 'erp' ) ); return; }
		setBusy( true );
		request( restPath( 'v2', BASE ), { method: 'POST', data: { user_id: Number( employeeId ), reason, other_reason: other, start_date: from, end_date: to } } )
			.then( () => { toast.success( __( 'Remote work request submitted.', 'erp' ) ); onSaved(); } )
			.catch( ( e: ApiError ) => toast.error( e.message || __( 'Could not submit.', 'erp' ) ) )
			.finally( () => setBusy( false ) );
	}

	return (
		<Dialog open onOpenChange={ ( o ) => ( o || busy ? undefined : onClose() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-md">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'New Remote Work Request', 'erp' ) }</DialogTitle>
					<DialogDescription>{ __( 'File a remote work request on an employee\'s behalf.', 'erp' ) }</DialogDescription>
				</DialogHeader>

				<div className="flex flex-col gap-2.5">
					<label className="text-sm font-medium text-foreground">{ __( 'Employee', 'erp' ) }</label>
					<SmartSelect options={ employee.options } value={ employeeId } onValueChange={ ( v ) => setEmployeeId( v ?? '' ) } onSearch={ employee.onSearch } placeholder={ __( 'Select employee', 'erp' ) } searchPlaceholder={ __( 'Search…', 'erp' ) } emptyMessage={ __( 'No employees found.', 'erp' ) } className="h-10 w-full" />
				</div>
				<div className="flex flex-col gap-2.5">
					<label className="text-sm font-medium text-foreground">{ __( 'Reason', 'erp' ) }</label>
					<SmartSelect options={ reasons } value={ reason } onValueChange={ ( v ) => setReason( v ?? '' ) } placeholder={ __( 'Select reason', 'erp' ) } searchPlaceholder={ __( 'Search…', 'erp' ) } emptyMessage={ __( 'No reasons.', 'erp' ) } className="h-10 w-full" />
				</div>
				{ 'other' === reason ? (
					<div className="flex flex-col gap-2.5">
						<label htmlFor="rw_other" className="text-sm font-medium text-foreground">{ __( 'Other Reason', 'erp' ) }</label>
						<textarea id="rw_other" rows={ 2 } value={ other } onChange={ ( e ) => setOther( e.target.value ) } className="rounded-md border border-border bg-background px-3 py-2 text-sm" />
					</div>
				) : null }
				<div className="grid grid-cols-2 gap-3">
					<div className="flex flex-col gap-2.5">
						<label htmlFor="rw_from" className="text-sm font-medium text-foreground">{ __( 'From', 'erp' ) }</label>
						<input id="rw_from" type="date" value={ from } onChange={ ( e ) => setFrom( e.target.value ) } className="h-10 rounded-md border border-border bg-background px-3 text-sm" />
					</div>
					<div className="flex flex-col gap-2.5">
						<label htmlFor="rw_to" className="text-sm font-medium text-foreground">{ __( 'To', 'erp' ) }</label>
						<input id="rw_to" type="date" value={ to } onChange={ ( e ) => setTo( e.target.value ) } className="h-10 rounded-md border border-border bg-background px-3 text-sm" />
					</div>
				</div>

				<DialogFooter className="gap-3">
					<Button variant="outline" disabled={ busy } onClick={ onClose }>{ __( 'Cancel', 'erp' ) }</Button>
					<Button disabled={ busy } onClick={ submit }>{ busy ? __( 'Submitting…', 'erp' ) : __( 'Submit', 'erp' ) }</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}
