/**
 * "Basic Information" section of the employee create/edit form: name/email/ID,
 * the create-only Type/Status selects, hire/end dates, and the required
 * Department + Job Title selects (each with an inline "+ Add new" quick-add).
 * Presentational — the form owns state and the quick-add handlers.
 */

import type { JSX } from 'react';

import { QuickAddButton } from '@/shared/components/QuickAddButton';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';

import {
	FormSection,
	SelectField,
	SmartSelectField,
	TextField,
} from './fields';
import type { Option } from './options';
import { STATUS_OPTIONS, TYPE_OPTIONS } from './options';
import type { FormState } from './validation';

interface EmployeeBasicSectionProps {
	readonly form:         FormState;
	readonly errors:       Record< string, string >;
	readonly set:          ( key: string ) => ( value: string ) => void;
	readonly isEdit:       boolean;
	readonly departments:  Option[];
	readonly designations: Option[];
	readonly submitting:   boolean;
	readonly onAddDept:    () => void;
	readonly onAddDesig:   () => void;
}

export function EmployeeBasicSection( {
	form,
	errors,
	set,
	isEdit,
	departments,
	designations,
	submitting,
	onAddDept,
	onAddDesig,
}: EmployeeBasicSectionProps ): JSX.Element {
	// The inline "+ Add new" quick-creates department/designation rows, so only
	// surface them to users who hold the matching manage capability (same cap the
	// v2 create endpoints enforce). Others just pick from the existing options.
	const canAddDept  = useCan( 'erp_manage_department' );
	const canAddDesig = useCan( 'erp_manage_designation' );

	// Field-level access (legacy parity): Employee ID, hire/end dates, Department
	// and Job Title are manager-only fields. When an employee edits their OWN
	// profile (no bare `erp_edit_employee` manager cap), these stay read-only —
	// the legacy form hid them behind `current_user_can( 'erp_edit_employee' )`.
	// Create mode is manager-only already, so this only bites self-edit.
	const isManager   = useCan( 'erp_edit_employee' );
	const lockManager = isEdit && ! isManager;

	return (
		<FormSection
			title={ __( 'Basic Information', 'erp' ) }
			description={ __(
				'Fields marked with * are required.',
				'erp'
			) }
		>
			<TextField
				id="first_name"
				label={ __( 'First Name', 'erp' ) }
				required
				value={ form.first_name ?? '' }
				onChange={ set( 'first_name' ) }
				error={ errors.first_name }
				maxLength={ 30 }
			/>
			<TextField
				id="middle_name"
				label={ __( 'Middle Name', 'erp' ) }
				value={ form.middle_name ?? '' }
				onChange={ set( 'middle_name' ) }
				maxLength={ 30 }
			/>
			<TextField
				id="last_name"
				label={ __( 'Last Name', 'erp' ) }
				required
				value={ form.last_name ?? '' }
				onChange={ set( 'last_name' ) }
				error={ errors.last_name }
				maxLength={ 30 }
			/>
			<TextField
				id="employee_id"
				label={ __( 'Employee ID', 'erp' ) }
				value={ form.employee_id ?? '' }
				onChange={ set( 'employee_id' ) }
				error={ errors.employee_id }
				disabled={ lockManager }
			/>
			<TextField
				id="email"
				label={ __( 'Email', 'erp' ) }
				type="email"
				required
				value={ form.email ?? '' }
				onChange={ set( 'email' ) }
				error={ errors.email }
			/>
			{ ! isEdit ? (
				<>
					<SelectField
						id="type"
						label={ __( 'Employee Type', 'erp' ) }
						required
						options={ TYPE_OPTIONS }
						value={ form.type ?? '' }
						onChange={ set( 'type' ) }
						error={ errors.type }
						placeholder={ __( '- Select -', 'erp' ) }
					/>
					<SelectField
						id="status"
						label={ __( 'Employee Status', 'erp' ) }
						required
						options={ STATUS_OPTIONS }
						value={ form.status ?? '' }
						onChange={ set( 'status' ) }
						error={ errors.status }
						placeholder={ __( '- Select -', 'erp' ) }
					/>
				</>
			) : null }
			<TextField
				id="hiring_date"
				label={ __( 'Date of Hire', 'erp' ) }
				type="date"
				required
				value={ form.hiring_date ?? '' }
				onChange={ set( 'hiring_date' ) }
				error={ errors.hiring_date }
				disabled={ lockManager }
			/>
			<TextField
				id="end_date"
				label={ __( 'Employee End Date', 'erp' ) }
				type="date"
				value={ form.end_date ?? '' }
				onChange={ set( 'end_date' ) }
				disabled={ lockManager }
			/>
			<SmartSelectField
				id="department"
				label={ __( 'Department', 'erp' ) }
				required
				options={ departments }
				value={ form.department ?? '' }
				onChange={ set( 'department' ) }
				error={ errors.department }
				placeholder={ __( '- Select -', 'erp' ) }
				searchPlaceholder={ __(
					'Search departments…',
					'erp'
				) }
				disabled={ lockManager }
				labelAction={
					canAddDept ? (
						<QuickAddButton
							label={ __( 'Add new', 'erp' ) }
							onClick={ onAddDept }
							disabled={ submitting }
						/>
					) : undefined
				}
			/>
			<SmartSelectField
				id="designation"
				label={ __( 'Job Title', 'erp' ) }
				required
				options={ designations }
				value={ form.designation ?? '' }
				onChange={ set( 'designation' ) }
				error={ errors.designation }
				placeholder={ __( '- Select -', 'erp' ) }
				searchPlaceholder={ __(
					'Search job titles…',
					'erp'
				) }
				disabled={ lockManager }
				labelAction={
					canAddDesig ? (
						<QuickAddButton
							label={ __( 'Add new', 'erp' ) }
							onClick={ onAddDesig }
							disabled={ submitting }
						/>
					) : undefined
				}
			/>
		</FormSection>
	);
}
