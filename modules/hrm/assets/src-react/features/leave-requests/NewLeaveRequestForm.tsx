/**
 * Form body for the central "New Leave Request" dialog: error alert, employee /
 * financial-year / policy pickers, pro-injected extra fields, date range with
 * live validation feedback, reason, and the footer actions. Presentational —
 * the dialog owns all state, effects and the submit handler.
 */

import {
	Alert,
	AlertDescription,
	Button,
	DialogFooter,
} from '@wedevs/plugin-ui';
import type { Dispatch, FormEvent, JSX, SetStateAction } from 'react';

import { EntitlementEmptyHint } from '@/shared/components/EntitlementEmptyHint';
import { InfoTooltip } from '@/shared/components/InfoTooltip';
import {
	LeaveExtraFields,
	setLeaveFieldValue,
} from '@/shared/components/LeaveExtraFields';
import type { LeaveExtraField, LeaveExtraValues } from '@/shared/components/LeaveExtraFields';
import { __, sprintf } from '@/shared/i18n';
import { FieldSourceAction } from '@/shared/components/FieldSourceLink';
import { QuickAddButton } from '@/shared/components/QuickAddButton';
import { useCan } from '@/shared/hooks/useCan';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

import { SelectField, SmartSelectField, TextField, TextareaField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import type { LeaveDateValidation } from '../employee-create/leave/useEmployeeLeave';

interface NewLeaveRequestFormProps {
	readonly error:            string | null;
	/** Hide the employee picker (self-service "Take a Leave" — locked to self). */
	readonly hideEmployeePicker?: boolean;
	/** Hide the Financial Year picker (self-service — auto-uses the current FY). */
	readonly hideFinancialYear?: boolean;
	readonly employee:         ReturnType< typeof useEmployeeSearch >;
	readonly employeeId:       string;
	readonly setEmployeeId:    ( value: string ) => void;
	readonly year:             string;
	readonly setYear:          ( value: string ) => void;
	readonly yearOptions:      Option[];
	readonly yearPlaceholder:  string;
	/** Opens the inline "New Financial Year" dialog. */
	readonly onAddYear:        () => void;
	/** Opens the inline "New Leave Policy" dialog. */
	readonly onAddPolicy:      () => void;
	readonly entitlementError: string | null;
	readonly entitled:         boolean;
	readonly policy:           string;
	readonly setPolicy:        ( value: string ) => void;
	readonly policyOptions:    Option[];
	readonly policyPlaceholder: string;
	readonly extraFields:      LeaveExtraField[];
	readonly extra:            LeaveExtraValues;
	readonly setExtra:         Dispatch< SetStateAction< LeaveExtraValues > >;
	readonly from:             string;
	readonly setFrom:          ( value: string ) => void;
	readonly to:               string;
	readonly setTo:            ( value: string ) => void;
	readonly reason:           string;
	readonly setReason:        ( value: string ) => void;
	readonly files:            File[];
	readonly setFiles:         ( value: File[] ) => void;
	readonly validating:       boolean;
	readonly dateError:        string | null;
	readonly validation:       LeaveDateValidation | null;
	readonly busy:             boolean;
	readonly onClose:          () => void;
	readonly onSubmit:         ( e: FormEvent ) => void;
}

export function NewLeaveRequestForm( {
	error,
	hideEmployeePicker,
	hideFinancialYear,
	employee,
	employeeId,
	setEmployeeId,
	year,
	setYear,
	yearOptions,
	yearPlaceholder,
	onAddYear,
	onAddPolicy,
	entitlementError,
	entitled,
	policy,
	setPolicy,
	policyOptions,
	policyPlaceholder,
	extraFields,
	extra,
	setExtra,
	from,
	setFrom,
	to,
	setTo,
	reason,
	setReason,
	files,
	setFiles,
	validating,
	dateError,
	validation,
	busy,
	onClose,
	onSubmit,
}: NewLeaveRequestFormProps ): JSX.Element {
	// Advanced Leave injects `halfday` as a checkbox into `extra`. A half day is a
	// single day, so the To date follows From while it is on — the legacy form
	// removed the To control from the DOM for the same reason.
	const isHalfday = extra.halfday === true || extra.halfday === '1' || extra.halfday === 'on';

	// Inline create needs the same gate the endpoints enforce: financial years
	// are `erp_hr_manager`, leave policies `erp_leave_manage`. Anyone else keeps
	// the read-only link to the setup screen.
	const canManageYears    = useCan( 'erp_hr_manager' );
	const canManagePolicies = useCan( 'erp_leave_manage' );

	return (
		<form onSubmit={ onSubmit } className="flex min-w-0 flex-col gap-4" noValidate>
			{ error ? (
				<Alert variant="destructive">
					<AlertDescription>{ error }</AlertDescription>
				</Alert>
			) : null }

			{ hideEmployeePicker ? null : (
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
			) }
			{ hideFinancialYear ? null : (
				<SelectField
					id="leave_year"
					labelAction={
						canManageYears ? (
							<QuickAddButton
								label={ __( 'Add New', 'erp' ) }
								onClick={ onAddYear }
								disabled={ busy }
							/>
						) : (
							<FieldSourceAction source="financialYears" />
						)
					}
					label={ __( 'Financial Year', 'erp' ) }
					required
					options={ yearOptions }
					value={ year }
					onChange={ ( v ) => { setYear( v ); setPolicy( '' ); } }
					placeholder={ yearPlaceholder }
				/>
			) }
			{ entitlementError ? <EntitlementEmptyHint onClose={ onClose } /> : null }
			<SelectField
				id="leave_policy"
				labelAction={
					canManagePolicies ? (
						<QuickAddButton
							label={ __( 'Add New', 'erp' ) }
							onClick={ onAddPolicy }
							disabled={ busy }
						/>
					) : (
						<FieldSourceAction source="leavePolicies" />
					)
				}
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
			<TextField
				id="leave_from"
				label={ __( 'From', 'erp' ) }
				type="date"
				required
				disabled={ ! entitled }
				value={ from }
				onChange={ ( v ) => {
					setFrom( v );
					// A half day is one day. Legacy removed the To control entirely
					// while the switch was on; leaving it free let a three-day range
					// be booked and charged as 0.5 days, so the calendar and the
					// entitlement ledger disagreed.
					if ( isHalfday ) {
						setTo( v );
					}
				} }
			/>
			<TextField
				id="leave_to"
				label={ __( 'To', 'erp' ) }
				type="date"
				required
				disabled={ ! entitled || isHalfday }
				value={ isHalfday ? from : to }
				onChange={ setTo }
			/>
			{ isHalfday ? (
				<p className="-mt-2 text-xs text-muted-foreground">
					{ __( 'A half-day request covers the From date only.', 'erp' ) }
				</p>
			) : null }
			{ validating ? (
				<p className="text-sm text-muted-foreground">{ __( 'Checking dates…', 'erp' ) }</p>
			) : dateError ? (
				<Alert variant="destructive">
					<AlertDescription>{ dateError }</AlertDescription>
				</Alert>
			) : validation ? (
				<div className="rounded-md border border-border bg-muted/30 px-3 py-2 text-sm text-foreground">
					{ /* A half day is 0.5 by definition. The validator still answers for
					     the last From-To range it was handed, so its total announced
					     "4 working days" over a request that books one half day. The
					     stored request was always right; the preview was not. */ }
					{ isHalfday
						? __( '0.5 working day', 'erp' )
						: sprintf(
								validation.total === 1 ? __( '%d working day', 'erp' ) : __( '%d working days', 'erp' ),
								validation.total
						  ) }
					{ ! isHalfday && validation.sandwich ? ` ${ __( '(Sandwich rule applied)', 'erp' ) }` : '' }
				</div>
			) : null }

			<TextareaField id="leave_reason" label={ __( 'Reason', 'erp' ) } required disabled={ ! entitled } value={ reason } onChange={ setReason } />

			<div className="flex flex-col gap-2.5">
				<label htmlFor="leave_document" className="text-sm font-medium text-foreground">{ __( 'Supporting Documents', 'erp' ) }</label>
				<input
					id="leave_document"
					type="file"
					multiple
					disabled={ ! entitled }
					onChange={ ( e ) => setFiles( e.target.files ? Array.from( e.target.files ) : [] ) }
					className="text-sm text-muted-foreground file:mr-3 file:rounded-md file:border file:border-border file:bg-muted file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-foreground"
				/>
				{ files.length > 0 ? (
					<span className="text-xs text-muted-foreground">{ sprintf( __( '%d file(s) selected', 'erp' ), files.length ) }</span>
				) : null }
			</div>

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
	);
}
