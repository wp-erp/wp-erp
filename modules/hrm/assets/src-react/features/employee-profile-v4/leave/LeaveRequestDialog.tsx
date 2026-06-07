/**
 * "Request Leave" dialog for the single-employee Leave tab.
 *
 * Mirrors the legacy new-leave-request form: pick a financial year → the
 * entitlements the employee can apply against load (with available balance) →
 * pick policy, dates, reason → submit. POSTs to the v2 route that mirrors the
 * `leave_request` AJAX handler.
 */

import {
	Alert,
	AlertDescription,
	Button,
	Dialog,
	DialogContent,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '@wedevs/plugin-ui';
import { applyFilters } from '@wordpress/hooks';
import { useEffect, useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { EntitlementEmptyHint } from '@/shared/components/EntitlementEmptyHint';
import { InfoTooltip } from '@/shared/components/InfoTooltip';
import {
	initLeaveFieldValues,
	LeaveExtraFields,
	setLeaveFieldValue,
} from '@/shared/components/LeaveExtraFields';
import type { LeaveExtraField, LeaveExtraValues } from '@/shared/components/LeaveExtraFields';
import { HOOKS } from '@/shared/filters';
import { __, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { SelectField, TextField, TextareaField } from '../fields';
import type { Option } from '../options';
import { fetchAssignablePolicies, submitLeaveRequest, validateLeaveDates } from './useEmployeeLeave';
import type { AssignablePolicy, LeaveDateValidation, LeaveOption } from './useEmployeeLeave';

interface LeaveRequestDialogProps {
	readonly open:           boolean;
	readonly userId:         number;
	readonly financialYears: readonly LeaveOption[];
	readonly currentYear:    number;
	readonly onClose:        () => void;
	readonly onSubmitted:    () => void;
}

export function LeaveRequestDialog( {
	open,
	userId,
	financialYears,
	currentYear,
	onClose,
	onSubmitted,
}: LeaveRequestDialogProps ): JSX.Element {
	const [ year, setYear ]         = useState( String( currentYear || '' ) );
	const [ policies, setPolicies ] = useState< readonly AssignablePolicy[] >( [] );
	const [ policy, setPolicy ]     = useState( '' );
	const [ from, setFrom ]         = useState( '' );
	const [ to, setTo ]             = useState( '' );
	const [ reason, setReason ]     = useState( '' );
	const [ documents, setDocuments ] = useState< File[] >( [] );
	const [ busy, setBusy ]         = useState( false );
	const [ error, setError ]       = useState< string | null >( null );
	const [ validating, setValidating ] = useState( false );
	const [ validation, setValidation ] = useState< LeaveDateValidation | null >( null );
	const [ dateError, setDateError ]   = useState< string | null >( null );
	const [ entitlementError, setEntitlementError ] = useState< string | null >( null );

	// Pro-injected request fields (Advanced Leave half-day).
	const [ extraFields, setExtraFields ] = useState< LeaveExtraField[] >( [] );
	const [ extra, setExtra ]             = useState< LeaveExtraValues >( {} );

	const entitled = policies.length > 0;

	// Reset when (re)opened.
	useEffect( () => {
		if ( open ) {
			setYear( String( currentYear || '' ) );
			setPolicy( '' );
			setFrom( '' );
			setTo( '' );
			setReason( '' );
			setDocuments( [] );
			setError( null );
			setValidation( null );
			setDateError( null );
			setEntitlementError( null );

			const fields = applyFilters( HOOKS.LEAVE_REQUEST_FIELDS, [], { userId, leavePolicyId: 0 } ) as LeaveExtraField[];
			setExtraFields( fields );
			setExtra( initLeaveFieldValues( fields ) );
		}
	}, [ open, currentYear, userId ] );

	// Live pre-validation of the date range (mirrors legacy leave_request_dates):
	// working-day count on success, server message (overlap / balance / FY) on error.
	useEffect( () => {
		if ( ! open || ! policy || ! from || ! to ) {
			setValidation( null );
			setDateError( null );
			return;
		}
		let cancelled = false;
		setValidating( true );
		setDateError( null );
		const timer = window.setTimeout( () => {
			void validateLeaveDates( userId, { leave_policy: Number( policy ), leave_from: from, leave_to: to } )
				.then( ( result ) => {
					if ( ! cancelled ) {
						setValidation( result );
					}
				} )
				.catch( ( raw ) => {
					if ( ! cancelled ) {
						setValidation( null );
						setDateError( ( raw as ApiError )?.message ?? __( 'Invalid leave dates.', 'erp' ) );
					}
				} )
				.finally( () => {
					if ( ! cancelled ) {
						setValidating( false );
					}
				} );
		}, 350 );
		return () => {
			cancelled = true;
			window.clearTimeout( timer );
		};
	}, [ open, userId, policy, from, to ] );

	// Load the assignable policies whenever the chosen year changes. No policy
	// for the year ⇒ the employee is not entitled (legacy `get_assign_policy`).
	useEffect( () => {
		if ( ! open || ! year ) {
			setPolicies( [] );
			setEntitlementError( null );
			return;
		}
		let cancelled = false;
		setEntitlementError( null );
		void fetchAssignablePolicies( userId, Number( year ) )
			.then( ( list ) => {
				if ( cancelled ) {
					return;
				}
				setPolicies( list );
				setEntitlementError(
					list.length === 0
						? __( 'Employee is not entitled to any leave policy. Set leave entitlement to apply for leave.', 'erp' )
						: null
				);
			} )
			.catch( () => ! cancelled && setPolicies( [] ) );
		return () => {
			cancelled = true;
		};
	}, [ open, userId, year ] );

	const yearOptions: Option[] = financialYears.map( ( fy ) => ( { value: String( fy.id ), label: fy.name } ) );

	const policyOptions: Option[] = policies.map( ( p ) => ( {
		value: String( p.id ),
		// translators: %1$s policy name, %2$s available day count.
		label: sprintf( __( '%1$s (%2$s available)', 'erp' ), p.name, String( p.available ) ),
	} ) );

	async function handleSubmit( e: FormEvent ): Promise< void > {
		e.preventDefault();
		setBusy( true );
		setError( null );
		try {
			await submitLeaveRequest( userId, {
				leave_policy: Number( policy ),
				leave_from:   from,
				leave_to:     to,
				leave_reason: reason,
				...( extraFields.length > 0 ? { extra } : {} ),
			}, documents );
			onSubmitted();
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not submit the leave request.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'Request Leave', 'erp' ) }
					</DialogTitle>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ ( e ) => void handleSubmit( e ) } className="flex min-w-0 flex-col gap-4">
					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					<SelectField
						id="leave_year"
						label={ __( 'Financial Year', 'erp' ) }
						required
						options={ yearOptions }
						value={ year }
						onChange={ ( v ) => { setYear( v ); setPolicy( '' ); } }
					/>
					{ entitlementError ? <EntitlementEmptyHint onClose={ onClose } /> : null }
					<SelectField
						id="leave_policy"
						label={ __( 'Leave Policy', 'erp' ) }
						required
						disabled={ ! entitled }
						options={ policyOptions }
						value={ policy }
						onChange={ setPolicy }
						placeholder={ __( '- Select -', 'erp' ) }
					/>
					{ entitled ? (
						<LeaveExtraFields
							fields={ extraFields }
							values={ extra }
							onChange={ ( field, value ) => setExtra( ( p ) => setLeaveFieldValue( p, field, value ) ) }
						/>
					) : null }
					<TextField id="leave_from" label={ __( 'From', 'erp' ) } type="date" required disabled={ ! entitled } value={ from } onChange={ setFrom } />
					<TextField id="leave_to" label={ __( 'To', 'erp' ) } type="date" required disabled={ ! entitled } value={ to } onChange={ setTo } />
					{ validating ? (
						<p className="text-sm text-muted-foreground">{ __( 'Checking dates…', 'erp' ) }</p>
					) : dateError ? (
						<Alert variant="destructive">
							<AlertDescription>{ dateError }</AlertDescription>
						</Alert>
					) : validation ? (
						<div className="rounded-md border border-border bg-muted/30 px-3 py-2 text-sm text-foreground">
							{ sprintf(
								validation.total === 1 ? __( '%d working day', 'erp' ) : __( '%d working days', 'erp' ),
								validation.total
							) }
							{ validation.sandwich ? ` ${ __( '(Sandwich rule applied)', 'erp' ) }` : '' }
						</div>
					) : null }

					<TextareaField id="leave_reason" label={ __( 'Reason', 'erp' ) } disabled={ ! entitled } value={ reason } onChange={ setReason } />

					<div className="flex flex-col gap-1.5">
						<label htmlFor="leave_document" className="text-sm font-medium text-foreground">{ __( 'Document', 'erp' ) }</label>
						<input
							id="leave_document"
							type="file"
							multiple
							disabled={ ! entitled }
							onChange={ ( e ) => setDocuments( e.target.files ? Array.from( e.target.files ) : [] ) }
							className="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-md file:border file:border-border file:bg-muted file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-foreground hover:file:bg-muted/70 disabled:opacity-50"
						/>
					</div>

					<DialogFooter className="items-center gap-5 sm:gap-5">
						{ year && ! entitled ? (
							<span className="mr-auto inline-flex items-center gap-1.5 text-xs text-muted-foreground">
								<InfoTooltip text={ __( 'This employee has no leave entitlement for the selected year. Use the links above to create a policy and assign it, then come back.', 'erp' ) } />
								{ __( 'Why can’t I submit?', 'erp' ) }
							</span>
						) : null }
						<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onClose }>
							{ __( 'Cancel', 'erp' ) }
						</Button>
						<Button type="submit" className="h-10 px-6" disabled={ busy || ! entitled || validating || dateError !== null }>
							{ busy ? __( 'Submitting…', 'erp' ) : __( 'Submit Request', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
