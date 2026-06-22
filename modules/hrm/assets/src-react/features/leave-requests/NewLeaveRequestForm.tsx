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

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

import { SelectField, SmartSelectField, TextField, TextareaField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import type { LeaveDateValidation } from '../employee-create/leave/useEmployeeLeave';

interface NewLeaveRequestFormProps {
	readonly error:            string | null;
	readonly employee:         ReturnType< typeof useEmployeeSearch >;
	readonly employeeId:       string;
	readonly setEmployeeId:    ( value: string ) => void;
	readonly year:             string;
	readonly setYear:          ( value: string ) => void;
	readonly yearOptions:      Option[];
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
	readonly validating:       boolean;
	readonly dateError:        string | null;
	readonly validation:       LeaveDateValidation | null;
	readonly busy:             boolean;
	readonly onClose:          () => void;
	readonly onSubmit:         ( e: FormEvent ) => void;
}

export function NewLeaveRequestForm( {
	error,
	employee,
	employeeId,
	setEmployeeId,
	year,
	setYear,
	yearOptions,
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
	validating,
	dateError,
	validation,
	busy,
	onClose,
	onSubmit,
}: NewLeaveRequestFormProps ): JSX.Element {
	return (
		<form onSubmit={ onSubmit } className="flex min-w-0 flex-col gap-4">
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
	);
}
