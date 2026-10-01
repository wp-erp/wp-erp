/**
 * Self-service request actions on `/my-profile` — lets the logged-in employee
 * apply for Resignation or Remote Work themselves (mirrors the legacy
 * `erp_hr_employee_extra_actions` / `erp_hr_leave_calendar_actions` buttons).
 *
 * Posts to the pro `erp/v2/hrm/{resignations,remote-work}` create routes WITHOUT a
 * `user_id` — the controller defaults it to the current user — so the row lands in
 * the same legacy table the manager Requests tab + legacy Vue screen read. The card
 * hides itself when neither pro feature is active (the reason lookup 404s).
 */

import {
	Button,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	SmartSelect,
	Textarea,
	toast,
} from '@wedevs/plugin-ui';
import { Laptop, LogOut } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { FormLabel } from '@/shared/components/FieldLabels';
import { StatusPill } from '@/shared/components/StatusPill';
import { requestStatusTone } from '@/shared/components/status-tones';
import { DateField } from '@/shared/DateField';
import { useBoot } from '@/shared/hooks/useBoot';
import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';
import { todaySiteYmd } from '@/shared/utils/date';

interface ReasonOption { readonly value: string; readonly label: string }
type Kind = 'resignation' | 'remote_work' | null;

const today = (): string => todaySiteYmd();

/**
 * The two self-service request buttons, sized to sit beside the profile "Edit"
 * button in the header. Rendered only on `/my-profile` (self context).
 */
export function MyRequestActions(): JSX.Element | null {
	const [ open, setOpen ] = useState< Kind >( null );
	const { isPro } = useBoot();

	// Both flows are pro-only: without ERP Pro the create/reason routes do not
	// exist, so offering the buttons only leads to a 404 toast.
	if ( ! isPro ) {
		return null;
	}

	return (
		<>
			<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ () => setOpen( 'remote_work' ) }>
				<Laptop size={ 16 } aria-hidden="true" />{ __( 'Remote Work', 'erp' ) }
			</Button>
			<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4 border-destructive text-destructive hover:border-destructive hover:text-destructive" onClick={ () => setOpen( 'resignation' ) }>
				<LogOut size={ 16 } aria-hidden="true" />{ __( 'Resign', 'erp' ) }
			</Button>

			{ 'resignation' === open ? <ResignDialog onClose={ () => setOpen( null ) } /> : null }
			{ 'remote_work' === open ? <RemoteDialog onClose={ () => setOpen( null ) } /> : null }
		</>
	);
}

function useReasons( base: string ): ReasonOption[] {
	const [ reasons, setReasons ] = useState< ReasonOption[] >( [] );
	useEffect( () => {
		request< { items: ReasonOption[] } >( restPath( 'v2', `${ base }/reasons` ) )
			.then( ( r ) => setReasons( [ ...r.items ] ) )
			.catch( () => undefined );
	}, [ base ] );
	return reasons;
}

interface HistRow { readonly id: number; readonly status: string; readonly date?: string; readonly startDate?: string; readonly endDate?: string }

/** The employee's own past/pending requests + their status (self-scoped GET). */
function MyHistory( { base, primary }: { readonly base: string; readonly primary: ( r: HistRow ) => string } ): JSX.Element | null {
	const [ rows, setRows ] = useState< HistRow[] >( [] );
	useEffect( () => {
		request< { items: HistRow[] } >( restPath( 'v2', base ) )
			.then( ( r ) => setRows( [ ...r.items ] ) )
			.catch( () => undefined );
	}, [ base ] );

	if ( rows.length === 0 ) {
		return null;
	}
	return (
		<div className="rounded-md border border-border">
			<div className="border-b border-border bg-muted/40 px-3 py-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Your Requests', 'erp' ) }</div>
			<ul className="max-h-40 divide-y divide-border/60 overflow-y-auto">
				{ rows.map( ( r ) => (
					<li key={ r.id } className="flex items-center justify-between gap-2 px-3 py-2 text-sm">
						<span className="text-foreground">{ primary( r ) }</span>
						<StatusPill tone={ requestStatusTone( r.status ) } className="capitalize">{ r.status }</StatusPill>
					</li>
				) ) }
			</ul>
		</div>
	);
}

function ResignDialog( { onClose }: { readonly onClose: () => void } ): JSX.Element {
	const reasons = useReasons( '/hrm/resignations' );
	const [ reason, setReason ] = useState( '' );
	const [ date, setDate ]     = useState( today() );
	const [ busy, setBusy ]     = useState( false );

	function submit(): void {
		if ( ! reason ) { toast.error( __( 'Select a reason.', 'erp' ) ); return; }
		if ( ! date ) { toast.error( __( 'Select a resignation date.', 'erp' ) ); return; }
		setBusy( true );
		request( restPath( 'v2', '/hrm/resignations' ), { method: 'POST', data: { reason, date } } )
			.then( () => { toast.success( __( 'Your resignation request has been submitted.', 'erp' ) ); onClose(); } )
			.catch( ( e: ApiError ) => toast.error( e.message || __( 'Could not submit.', 'erp' ) ) )
			.finally( () => setBusy( false ) );
	}

	return (
		<Dialog open onOpenChange={ ( o ) => ( o || busy ? undefined : onClose() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-md">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Apply for Resignation', 'erp' ) }</DialogTitle>
					<DialogDescription>{ __( 'Submit a resignation request to HR.', 'erp' ) }</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />
				<MyHistory base="/hrm/resignations" primary={ ( r ) => r.date || '—' } />
				<div className="flex flex-col gap-2.5">
					<FormLabel>{ __( 'Reason', 'erp' ) }</FormLabel>
					<SmartSelect options={ reasons } value={ reason } onValueChange={ ( v ) => setReason( v ?? '' ) } placeholder={ __( 'Select reason', 'erp' ) } searchPlaceholder={ __( 'Search…', 'erp' ) } emptyMessage={ __( 'No reasons.', 'erp' ) } className="h-10 w-full" />
				</div>
				<div className="flex flex-col gap-2.5">
					<FormLabel>{ __( 'Resignation Date', 'erp' ) }</FormLabel>
					<DateField value={ date } onChange={ setDate } className="h-10 rounded-md border border-border bg-background px-3 text-sm" />
				</div>
				<DialogFooter className="gap-5 sm:gap-5">
					<Button variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onClose }>{ __( 'Cancel', 'erp' ) }</Button>
					<Button className="h-10 px-6" disabled={ busy } onClick={ submit }>{ busy ? __( 'Submitting…', 'erp' ) : __( 'Submit', 'erp' ) }</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}

function RemoteDialog( { onClose }: { readonly onClose: () => void } ): JSX.Element {
	const reasons = useReasons( '/hrm/remote-work' );
	const [ reason, setReason ] = useState( '' );
	const [ other, setOther ]   = useState( '' );
	const [ from, setFrom ]     = useState( today() );
	const [ to, setTo ]         = useState( today() );
	const [ busy, setBusy ]     = useState( false );

	function submit(): void {
		if ( ! reason ) { toast.error( __( 'Select a reason.', 'erp' ) ); return; }
		if ( ! from ) { toast.error( __( 'Select a start date.', 'erp' ) ); return; }
		if ( ! to ) { toast.error( __( 'Select an end date.', 'erp' ) ); return; }
		if ( to < from ) { toast.error( __( 'The end date must be on or after the start date.', 'erp' ) ); return; }
		setBusy( true );
		request( restPath( 'v2', '/hrm/remote-work' ), { method: 'POST', data: { reason, other_reason: other, start_date: from, end_date: to } } )
			.then( () => { toast.success( __( 'Your remote work request has been submitted.', 'erp' ) ); onClose(); } )
			.catch( ( e: ApiError ) => toast.error( e.message || __( 'Could not submit.', 'erp' ) ) )
			.finally( () => setBusy( false ) );
	}

	return (
		<Dialog open onOpenChange={ ( o ) => ( o || busy ? undefined : onClose() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-md">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Apply for Remote Work', 'erp' ) }</DialogTitle>
					<DialogDescription>{ __( 'Submit a remote work request to HR.', 'erp' ) }</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />
				<MyHistory base="/hrm/remote-work" primary={ ( r ) => `${ r.startDate ?? '' } → ${ r.endDate ?? '' }` } />
				<div className="flex flex-col gap-2.5">
					<FormLabel>{ __( 'Reason', 'erp' ) }</FormLabel>
					<SmartSelect options={ reasons } value={ reason } onValueChange={ ( v ) => setReason( v ?? '' ) } placeholder={ __( 'Select reason', 'erp' ) } searchPlaceholder={ __( 'Search…', 'erp' ) } emptyMessage={ __( 'No reasons.', 'erp' ) } className="h-10 w-full" />
				</div>
				{ 'other' === reason ? (
					<div className="flex flex-col gap-2.5">
						<FormLabel htmlFor="my_rw_other">{ __( 'Other Reason', 'erp' ) }</FormLabel>
						<Textarea id="my_rw_other" rows={ 2 } value={ other } onChange={ ( e ) => setOther( e.target.value ) } className="rounded-md border border-border bg-background px-3 py-2 text-sm" />
					</div>
				) : null }
				<div className="grid grid-cols-2 gap-3">
					<div className="flex flex-col gap-2.5">
						<FormLabel>{ __( 'From', 'erp' ) }</FormLabel>
						<DateField value={ from } onChange={ setFrom } max={ to || undefined } className="h-10 rounded-md border border-border bg-background px-3 text-sm" />
					</div>
					<div className="flex flex-col gap-2.5">
						<FormLabel>{ __( 'To', 'erp' ) }</FormLabel>
						<DateField value={ to } onChange={ setTo } min={ from || undefined } className="h-10 rounded-md border border-border bg-background px-3 text-sm" />
					</div>
				</div>
				<DialogFooter className="gap-5 sm:gap-5">
					<Button variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onClose }>{ __( 'Cancel', 'erp' ) }</Button>
					<Button className="h-10 px-6" disabled={ busy } onClick={ submit }>{ busy ? __( 'Submitting…', 'erp' ) : __( 'Submit', 'erp' ) }</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}
