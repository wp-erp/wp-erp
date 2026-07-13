/**
 * "New Request" dialog for the central Leave → Requests page.
 *
 * Legacy parity: `views/leave/requests.php` renders a "New Request" button so an
 * HR manager can apply for leave on behalf of any employee (legacy
 * `views/leave/new-request.php`, employee dropdown + policy/dates). The
 * single-employee Leave tab already creates against a fixed user; this dialog
 * adds the employee picker up front, then reuses the exact same
 * year → policy → dates → validate → submit flow (and the same v2 endpoints).
 */

import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogHeader,
	DialogTitle,
} from '@wedevs/plugin-ui';
import { applyFilters } from '@wordpress/hooks';
import { useEffect, useState } from 'react';
import type { FormEvent, JSX } from 'react';

import {
	initLeaveFieldValues,
} from '@/shared/components/LeaveExtraFields';
import type { LeaveExtraField, LeaveExtraValues } from '@/shared/components/LeaveExtraFields';
import { HOOKS } from '@/shared/filters';
import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

import {
	fetchAssignablePolicies,
	submitLeaveRequest,
	validateLeaveDates,
} from '../employee-create/leave/useEmployeeLeave';
import type { AssignablePolicy, LeaveDateValidation } from '../employee-create/leave/useEmployeeLeave';
import { NewLeaveRequestForm } from './NewLeaveRequestForm';
import {
	DATE_VALIDATE_DEBOUNCE_MS,
	policyPlaceholder as buildPolicyPlaceholder,
	toPolicyOptions,
	toYearOptions,
} from './new-leave-request-helpers';
import type { RawFinancialYear } from './new-leave-request-helpers';

interface NewLeaveRequestDialogProps {
	readonly open:        boolean;
	readonly onClose:     () => void;
	readonly onSubmitted: () => void;
	/** Lock the request to this employee (self-service "Take a Leave"): the
	 * employee picker is hidden and pre-set to this user. */
	readonly lockEmployeeId?: number;
}

export function NewLeaveRequestDialog( { open, onClose, onSubmitted, lockEmployeeId }: NewLeaveRequestDialogProps ): JSX.Element {
	const [ years, setYears ]         = useState< readonly RawFinancialYear[] >( [] );

	const [ employeeId, setEmployeeId ] = useState( lockEmployeeId ? String( lockEmployeeId ) : '' );
	const employee                      = useEmployeeSearch( open, undefined, employeeId );
	const [ year, setYear ]             = useState( '' );
	const [ policies, setPolicies ]     = useState< readonly AssignablePolicy[] >( [] );
	const [ policiesLoading, setPoliciesLoading ] = useState( false );
	const [ entitlementError, setEntitlementError ] = useState< string | null >( null );
	const [ policy, setPolicy ]         = useState( '' );
	const [ from, setFrom ]             = useState( '' );
	const [ to, setTo ]                 = useState( '' );
	const [ reason, setReason ]         = useState( '' );
	const [ files, setFiles ]           = useState< File[] >( [] );

	const [ busy, setBusy ]   = useState( false );
	const [ error, setError ] = useState< string | null >( null );

	const [ validating, setValidating ] = useState( false );
	const [ validation, setValidation ] = useState< LeaveDateValidation | null >( null );
	const [ dateError, setDateError ]   = useState< string | null >( null );

	// Pro-injected request fields (Advanced Leave half-day).
	const [ extraFields, setExtraFields ] = useState< LeaveExtraField[] >( [] );
	const [ extra, setExtra ]             = useState< LeaveExtraValues >( {} );

	const userId = Number( employeeId || 0 );

	// Reset + load the employee list and financial years when (re)opened.
	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setEmployeeId( lockEmployeeId ? String( lockEmployeeId ) : '' );
		setYear( '' );
		setPolicies( [] );
		setPolicy( '' );
		setEntitlementError( null );
		setFrom( '' );
		setTo( '' );
		setReason( '' );
		setFiles( [] );
		setError( null );
		setValidation( null );
		setDateError( null );

		let cancelled = false;
		void request< { financial_years?: RawFinancialYear[]; current_f_year?: number } >( restPath( 'v2', '/leave-policies/form-options' ) )
			.then( ( opts ) => {
				if ( cancelled ) {
					return;
				}
				const fys = Array.isArray( opts.financial_years ) ? opts.financial_years : [];
				setYears( fys );
				const current = Number( opts.current_f_year ?? 0 );
				if ( current && fys.some( ( fy ) => fy.id === current ) ) {
					setYear( String( current ) );
				}
			} )
			.catch( ( raw ) => {
				if ( ! cancelled ) {
					setError( ( raw as ApiError )?.message ?? __( 'Could not load the form.', 'erp' ) );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ open ] );

	// Load assignable policies (with balance) once an employee + year are chosen.
	// Mirrors the legacy `get_assign_policy` AJAX: an employee with no
	// entitlement in the year surfaces the "not entitled" error and blocks the
	// rest of the form.
	useEffect( () => {
		if ( ! open || ! userId || ! year ) {
			setPolicies( [] );
			setEntitlementError( null );
			return;
		}
		let cancelled = false;
		setPoliciesLoading( true );
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
			.catch( () => {
				if ( ! cancelled ) {
					setPolicies( [] );
				}
			} )
			.finally( () => ! cancelled && setPoliciesLoading( false ) );
		return () => {
			cancelled = true;
		};
	}, [ open, userId, year ] );

	// Re-derive the pro-injected fields whenever the selected policy changes, so
	// Advanced Leave's per-policy halfday gate (`ctx.halfdayEnabled`) can hide the
	// halfday control for policies that don't allow it.
	useEffect( () => {
		if ( ! open ) {
			return;
		}
		const selected = policies.find( ( p ) => String( p.id ) === policy );
		const fields = applyFilters( HOOKS.LEAVE_REQUEST_FIELDS, [], {
			userId,
			leavePolicyId:  Number( policy || 0 ),
			halfdayEnabled: selected?.halfday_enable ?? false,
		} ) as LeaveExtraField[];
		setExtraFields( fields );
		setExtra( initLeaveFieldValues( fields ) );
	}, [ open, userId, policy, policies ] );

	// Live pre-validation of the date range (mirrors legacy leave_request_dates).
	useEffect( () => {
		if ( ! open || ! userId || ! policy || ! from || ! to ) {
			setValidation( null );
			setDateError( null );
			return;
		}
		let cancelled = false;
		setValidating( true );
		setDateError( null );
		const timer = window.setTimeout( () => {
			void validateLeaveDates( userId, { leave_policy: Number( policy ), leave_from: from, leave_to: to } )
				.then( ( result ) => ! cancelled && setValidation( result ) )
				.catch( ( raw ) => {
					if ( ! cancelled ) {
						setValidation( null );
						setDateError( ( raw as ApiError )?.message ?? __( 'Invalid leave dates.', 'erp' ) );
					}
				} )
				.finally( () => ! cancelled && setValidating( false ) );
		}, DATE_VALIDATE_DEBOUNCE_MS );
		return () => {
			cancelled = true;
			window.clearTimeout( timer );
		};
	}, [ open, userId, policy, from, to ] );

	const yearOptions   = toYearOptions( years );
	const policyOptions = toPolicyOptions( policies );

	async function handleSubmit( e: FormEvent ): Promise< void > {
		e.preventDefault();
		if ( ! userId || ! policy || ! from || ! to ) {
			setError( __( 'Please select an employee, policy and date range.', 'erp' ) );
			return;
		}
		if ( to < from ) {
			setError( __( 'The end date must be on or after the start date.', 'erp' ) );
			return;
		}
		setBusy( true );
		setError( null );
		try {
			await submitLeaveRequest( userId, {
				leave_policy: Number( policy ),
				leave_from:   from,
				leave_to:     to,
				leave_reason: reason,
				...( extraFields.length > 0 ? { extra } : {} ),
			}, files );
			onSubmitted();
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not submit the leave request.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	// An employee is "entitled" once at least one assignable policy loads for the
	// chosen year. Until then, the policy/date/reason inputs stay disabled —
	// matching the legacy Vue form.
	const entitled = policies.length > 0;

	const policyPlaceholder = buildPolicyPlaceholder( employeeId, year, policiesLoading );

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'New Leave Request', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ lockEmployeeId ? __( 'Apply for your leave.', 'erp' ) : __( 'Apply for leave on behalf of an employee.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<NewLeaveRequestForm
					error={ error }
					hideEmployeePicker={ Boolean( lockEmployeeId ) }
					hideFinancialYear={ Boolean( lockEmployeeId ) }
					employee={ employee }
					employeeId={ employeeId }
					setEmployeeId={ setEmployeeId }
					year={ year }
					setYear={ setYear }
					yearOptions={ yearOptions }
					entitlementError={ entitlementError }
					entitled={ entitled }
					policy={ policy }
					setPolicy={ setPolicy }
					policyOptions={ policyOptions }
					policyPlaceholder={ policyPlaceholder }
					extraFields={ extraFields }
					extra={ extra }
					setExtra={ setExtra }
					from={ from }
					setFrom={ setFrom }
					to={ to }
					setTo={ setTo }
					reason={ reason }
					setReason={ setReason }
					files={ files }
					setFiles={ setFiles }
					validating={ validating }
					dateError={ dateError }
					validation={ validation }
					busy={ busy }
					onClose={ onClose }
					onSubmit={ ( e ) => void handleSubmit( e ) }
				/>
			</DialogContent>
		</Dialog>
	);
}
