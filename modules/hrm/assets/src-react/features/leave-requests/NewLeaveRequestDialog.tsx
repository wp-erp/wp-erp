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
	Alert,
	AlertDescription,
	Button,
	Dialog,
	DialogContent,
	DialogDescription,
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
import { request, restPath } from '@/shared/utils/apiFetch';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

import { SelectField, SmartSelectField, TextField, TextareaField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import {
	fetchAssignablePolicies,
	submitLeaveRequest,
	validateLeaveDates,
} from '../employee-create/leave/useEmployeeLeave';
import type { AssignablePolicy, LeaveDateValidation } from '../employee-create/leave/useEmployeeLeave';

interface NewLeaveRequestDialogProps {
	readonly open:        boolean;
	readonly onClose:     () => void;
	readonly onSubmitted: () => void;
}

interface RawFinancialYear {
	readonly id:      number;
	readonly fy_name: string;
}

const DATE_VALIDATE_DEBOUNCE_MS = 350;

export function NewLeaveRequestDialog( { open, onClose, onSubmitted }: NewLeaveRequestDialogProps ): JSX.Element {
	const [ years, setYears ]         = useState< readonly RawFinancialYear[] >( [] );

	const [ employeeId, setEmployeeId ] = useState( '' );
	const employee                      = useEmployeeSearch( open, undefined, employeeId );
	const [ year, setYear ]             = useState( '' );
	const [ policies, setPolicies ]     = useState< readonly AssignablePolicy[] >( [] );
	const [ policiesLoading, setPoliciesLoading ] = useState( false );
	const [ entitlementError, setEntitlementError ] = useState< string | null >( null );
	const [ policy, setPolicy ]         = useState( '' );
	const [ from, setFrom ]             = useState( '' );
	const [ to, setTo ]                 = useState( '' );
	const [ reason, setReason ]         = useState( '' );

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
		setEmployeeId( '' );
		setYear( '' );
		setPolicies( [] );
		setPolicy( '' );
		setEntitlementError( null );
		setFrom( '' );
		setTo( '' );
		setReason( '' );
		setError( null );
		setValidation( null );
		setDateError( null );

		const fields = applyFilters( HOOKS.LEAVE_REQUEST_FIELDS, [], { userId: 0, leavePolicyId: 0 } ) as LeaveExtraField[];
		setExtraFields( fields );
		setExtra( initLeaveFieldValues( fields ) );

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

	const yearOptions: Option[]     = years.map( ( fy ) => ( { value: String( fy.id ), label: fy.fy_name } ) );
	const policyOptions: Option[]   = policies.map( ( p ) => ( {
		value: String( p.id ),
		// translators: %1$s policy name, %2$s available day count.
		label: sprintf( __( '%1$s (%2$s available)', 'erp' ), p.name, String( p.available ) ),
	} ) );

	async function handleSubmit( e: FormEvent ): Promise< void > {
		e.preventDefault();
		if ( ! userId || ! policy || ! from || ! to ) {
			setError( __( 'Please select an employee, policy and date range.', 'erp' ) );
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
			} );
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

	const policyPlaceholder = ! employeeId
		? __( 'Select an employee first', 'erp' )
		: ! year
		? __( 'Select a financial year first', 'erp' )
		: policiesLoading
		? __( 'Loading…', 'erp' )
		: __( '- Select -', 'erp' );

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'New Leave Request', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Apply for leave on behalf of an employee.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ ( e ) => void handleSubmit( e ) } className="flex min-w-0 flex-col gap-4">
					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					<SmartSelectField
						id="leave_employee"
						label={ __( 'Employee', 'erp' ) }
						required
						options={ employee.options }
						value={ employeeId }
						onChange={ ( v ) => { setEmployeeId( v ); setPolicy( '' ); } }
						onSearch={ employee.onSearch }
						loading={ employee.loading }
						placeholder={ __( '- Select -', 'erp' ) }
						searchPlaceholder={ __( 'Search employees…', 'erp' ) }
						emptyMessage={ __( 'No employees found.', 'erp' ) }
					/>
					<SelectField
						id="leave_year"
						label={ __( 'Financial Year', 'erp' ) }
						required
						options={ yearOptions }
						value={ year }
						onChange={ ( v ) => { setYear( v ); setPolicy( '' ); } }
						placeholder={ __( '- Select -', 'erp' ) }
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
						placeholder={ policyPlaceholder }
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

					<DialogFooter className="items-center gap-5 sm:gap-5">
						{ employeeId && ! entitled ? (
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
