/**
 * Create / edit leave-policy dialog.
 *
 * Mirrors the legacy policy form (`FormHandler::leave_policy_create()`): leave
 * type + days + color + financial year (required on create), plus the optional
 * scope selects (employee type, department, designation, gender, marital — each
 * defaulting to the `-1` "All" sentinel) and the "apply to new employees"
 * toggle. On edit the legacy model ignores `days`, so that field is locked.
 *
 * The server re-validates and dedupes (a duplicate scope returns "Policy
 * already exists.").
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
	Label,
	Switch,
} from '@wedevs/plugin-ui';
import { applyFilters } from '@wordpress/hooks';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { DependencyHint } from '@/shared/components/DependencyHint';
import {
	initLeaveFieldValues,
	LeaveExtraFields,
	setLeaveFieldValue,
} from '@/shared/components/LeaveExtraFields';
import type { LeaveExtraField, LeaveExtraValues } from '@/shared/components/LeaveExtraFields';
import { HOOKS } from '@/shared/filters';
import { __ } from '@/shared/i18n';

import { SelectField, SmartSelectField, TextField, TextareaField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import type { LeavePolicy, LeavePolicyInput, PolicyFormOptions } from './types';

interface LeavePolicyFormDialogProps {
	readonly open:     boolean;
	readonly editing:  LeavePolicy | null;
	readonly options:  PolicyFormOptions | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: LeavePolicyInput ) => void;
}

interface FormState {
	leave_id:            string;
	days:                string;
	color:               string;
	description:         string;
	f_year:              string;
	employee_type:       string;
	department_id:       string;
	designation_id:      string;
	gender:              string;
	marital:             string;
	apply_for_new_users: boolean;
	apply_for_existing:  boolean;
}

const ALL = '-1';

const EMPTY: FormState = {
	leave_id:            '',
	days:                '',
	color:               '#3b82f6',
	description:         '',
	f_year:              '',
	employee_type:       ALL,
	department_id:       ALL,
	designation_id:      ALL,
	gender:              ALL,
	marital:             ALL,
	apply_for_new_users: false,
	apply_for_existing:  false,
};

/** Prepend the "All" sentinel option to a scope select. */
function withAll( opts: readonly Option[] ): Option[] {
	return [ { value: ALL, label: __( 'All', 'erp' ) }, ...opts ];
}

export function LeavePolicyFormDialog( {
	open,
	editing,
	options,
	busy,
	error,
	onClose,
	onSubmit,
}: LeavePolicyFormDialogProps ): JSX.Element {
	const [ form, setForm ]     = useState< FormState >( EMPTY );
	const [ errors, setErrors ] = useState< {
		leave_id?: string | undefined;
		days?:     string | undefined;
		f_year?:   string | undefined;
	} >( {} );

	// Pro-injected fields (Advanced Leave). Definitions arrive via wp.hooks; the
	// pro filter prefills each `default` from the saved policy (`saved`) on edit.
	const [ extraFields, setExtraFields ] = useState< LeaveExtraField[] >( [] );
	const [ extra, setExtra ]             = useState< LeaveExtraValues >( {} );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		const fields = applyFilters(
			HOOKS.LEAVE_POLICY_FIELDS,
			[],
			{ mode: editing ? 'edit' : 'create', saved: editing ?? {} }
		) as LeaveExtraField[];
		setExtraFields( fields );
		setExtra( initLeaveFieldValues( fields ) );
	}, [ open, editing ] );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setErrors( {} );
		setForm(
			editing
				? {
						leave_id:            editing.leave_id ? String( editing.leave_id ) : '',
						days:                String( editing.days ),
						color:               editing.color || '#3b82f6',
						description:         editing.description,
						f_year:              editing.f_year ? String( editing.f_year ) : '',
						employee_type:       editing.employee_type || ALL,
						department_id:       editing.department_id || ALL,
						designation_id:      editing.designation_id || ALL,
						gender:              editing.gender || ALL,
						marital:             editing.marital || ALL,
						apply_for_new_users: editing.apply_for_new_users,
						apply_for_existing:  false,
				  }
				: EMPTY
		);
	}, [ open, editing ] );

	const leaveTypeOpts = useMemo< Option[] >(
		() => ( options?.leaveTypes ?? [] ).map( ( t ) => ( { value: String( t.id ), label: t.label } ) ),
		[ options ]
	);
	const fYearOpts = useMemo< Option[] >(
		() => ( options?.financialYears ?? [] ).map( ( y ) => ( { value: String( y.id ), label: y.label } ) ),
		[ options ]
	);
	const deptOpts = useMemo< Option[] >(
		() => withAll( ( options?.departments ?? [] ).map( ( d ) => ( { value: String( d.id ), label: d.label } ) ) ),
		[ options ]
	);
	const desigOpts = useMemo< Option[] >(
		() => withAll( ( options?.designations ?? [] ).map( ( d ) => ( { value: String( d.id ), label: d.label } ) ) ),
		[ options ]
	);
	const empTypeOpts = useMemo< Option[] >(
		() => withAll( ( options?.employeeTypes ?? [] ).map( ( o ) => ( { value: o.value, label: o.label } ) ) ),
		[ options ]
	);
	const genderOpts = useMemo< Option[] >(
		() => withAll( ( options?.genders ?? [] ).map( ( o ) => ( { value: o.value, label: o.label } ) ) ),
		[ options ]
	);
	const maritalOpts = useMemo< Option[] >(
		() => withAll( ( options?.maritalStatuses ?? [] ).map( ( o ) => ( { value: o.value, label: o.label } ) ) ),
		[ options ]
	);

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();

		const next: { leave_id?: string; days?: string; f_year?: string } = {};
		if ( ! editing && ! form.leave_id ) {
			next.leave_id = __( 'Leave type is required.', 'erp' );
		}
		if ( ! editing && ! form.f_year ) {
			next.f_year = __( 'Financial year is required.', 'erp' );
		}
		if ( ! editing && ( form.days === '' || Number( form.days ) < 0 ) ) {
			next.days = __( 'Days is required.', 'erp' );
		}

		if ( Object.keys( next ).length > 0 ) {
			setErrors( next );
			return;
		}

		onSubmit( {
			leave_id:            Number( form.leave_id || ( editing?.leave_id ?? 0 ) ),
			days:                Number( form.days || 0 ),
			color:               form.color,
			description:         form.description.trim(),
			f_year:              Number( form.f_year || ( editing?.f_year ?? 0 ) ),
			employee_type:       form.employee_type,
			department_id:       form.department_id,
			designation_id:      form.designation_id,
			gender:              form.gender,
			marital:             form.marital,
			apply_for_new_users: form.apply_for_new_users,
			...( editing ? {} : { apply_for_existing: form.apply_for_existing } ),
			...( extraFields.length > 0 ? { extra } : {} ),
		} );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-2xl">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ editing ? __( 'Edit Leave Policy', 'erp' ) : __( 'Add Leave Policy', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'A policy grants a number of leave days for a financial year, optionally scoped to a department, designation, type, gender or marital status.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				{ ! editing && leaveTypeOpts.length === 0 ? (
					<DependencyHint
						message={ __( 'No leave type exists yet. Create one before adding a leave policy.', 'erp' ) }
						steps={ [ { label: __( 'Create a leave type', 'erp' ), path: '/leave/types' } ] }
						onBeforeNavigate={ onClose }
					/>
				) : null }

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<div className="grid grid-cols-2 gap-4">
						<SmartSelectField
							id="policy_leave_id"
							label={ __( 'Leave Type', 'erp' ) }
							required
							options={ leaveTypeOpts }
							value={ form.leave_id }
							onChange={ ( v ) => {
								setForm( ( p ) => ( { ...p, leave_id: v } ) );
								setErrors( ( p ) => ( { ...p, leave_id: undefined } ) );
							} }
							error={ errors.leave_id }
							placeholder={ __( '- Select -', 'erp' ) }
							searchPlaceholder={ __( 'Search leave types…', 'erp' ) }
							emptyMessage={ __( 'No leave types found.', 'erp' ) }
						/>
						<SmartSelectField
							id="policy_f_year"
							label={ __( 'Financial Year', 'erp' ) }
							required
							options={ fYearOpts }
							value={ form.f_year }
							onChange={ ( v ) => {
								setForm( ( p ) => ( { ...p, f_year: v } ) );
								setErrors( ( p ) => ( { ...p, f_year: undefined } ) );
							} }
							error={ errors.f_year }
							placeholder={ __( '- Select -', 'erp' ) }
							searchPlaceholder={ __( 'Search…', 'erp' ) }
							emptyMessage={ __( 'No financial years found.', 'erp' ) }
						/>
					</div>

					<div className="grid grid-cols-2 gap-4">
						<TextField
							id="policy_days"
							label={ __( 'Days', 'erp' ) }
							type="number"
							required={ ! editing }
							value={ form.days }
							onChange={ ( v ) => {
								setForm( ( p ) => ( { ...p, days: v } ) );
								setErrors( ( p ) => ( { ...p, days: undefined } ) );
							} }
							error={ errors.days }
							className={ editing ? 'opacity-60' : undefined }
						/>
						<div className="flex min-w-0 flex-col gap-2.5">
							<Label htmlFor="policy_color" className="text-sm font-medium text-foreground">
								{ __( 'Color', 'erp' ) }
								<span className="ml-0.5 text-destructive">*</span>
							</Label>
							<div className="flex items-center gap-3">
								<input
									id="policy_color"
									type="color"
									value={ form.color }
									onChange={ ( e ) => setForm( ( p ) => ( { ...p, color: e.target.value } ) ) }
									className="h-10 w-16 cursor-pointer rounded-md border border-border bg-background p-1"
								/>
								<span className="text-sm text-muted-foreground">{ form.color }</span>
							</div>
						</div>
					</div>

					{ editing ? (
						<p className="-mt-2 text-xs text-muted-foreground">
							{ __( 'The number of days cannot be changed after a policy is created.', 'erp' ) }
						</p>
					) : null }

					<div className="grid grid-cols-2 gap-4">
						<SelectField
							id="policy_employee_type"
							label={ __( 'Employee Type', 'erp' ) }
							options={ empTypeOpts }
							value={ form.employee_type }
							onChange={ ( v ) => setForm( ( p ) => ( { ...p, employee_type: v } ) ) }
						/>
						<SmartSelectField
							id="policy_department"
							label={ __( 'Department', 'erp' ) }
							options={ deptOpts }
							value={ form.department_id }
							onChange={ ( v ) => setForm( ( p ) => ( { ...p, department_id: v || ALL } ) ) }
							placeholder={ __( 'All', 'erp' ) }
							searchPlaceholder={ __( 'Search departments…', 'erp' ) }
							emptyMessage={ __( 'No departments found.', 'erp' ) }
						/>
					</div>

					<div className="grid grid-cols-2 gap-4">
						<SmartSelectField
							id="policy_designation"
							label={ __( 'Designation', 'erp' ) }
							options={ desigOpts }
							value={ form.designation_id }
							onChange={ ( v ) => setForm( ( p ) => ( { ...p, designation_id: v || ALL } ) ) }
							placeholder={ __( 'All', 'erp' ) }
							searchPlaceholder={ __( 'Search designations…', 'erp' ) }
							emptyMessage={ __( 'No designations found.', 'erp' ) }
						/>
						<SelectField
							id="policy_gender"
							label={ __( 'Gender', 'erp' ) }
							options={ genderOpts }
							value={ form.gender }
							onChange={ ( v ) => setForm( ( p ) => ( { ...p, gender: v } ) ) }
						/>
					</div>

					<div className="grid grid-cols-2 gap-4">
						<SelectField
							id="policy_marital"
							label={ __( 'Marital Status', 'erp' ) }
							options={ maritalOpts }
							value={ form.marital }
							onChange={ ( v ) => setForm( ( p ) => ( { ...p, marital: v } ) ) }
						/>
					</div>

					<TextareaField
						id="policy_description"
						label={ __( 'Description', 'erp' ) }
						value={ form.description }
						onChange={ ( v ) => setForm( ( p ) => ( { ...p, description: v } ) ) }
						rows={ 2 }
					/>

					<div className="flex items-center justify-between rounded-md border border-border bg-muted/20 px-4 py-3">
						<Label htmlFor="policy_apply_new" className="text-sm font-medium text-foreground">
							{ __( 'Apply to new employees automatically', 'erp' ) }
						</Label>
						<Switch
							id="policy_apply_new"
							checked={ form.apply_for_new_users }
							onCheckedChange={ ( checked ) => setForm( ( p ) => ( { ...p, apply_for_new_users: checked } ) ) }
						/>
					</div>

					{ ! editing ? (
						<div className="flex items-center justify-between rounded-md border border-border bg-muted/20 px-4 py-3">
							<Label htmlFor="policy_apply_existing" className="text-sm font-medium text-foreground">
								{ __( 'Apply for existing employees', 'erp' ) }
								<span className="mt-0.5 block text-xs font-normal text-muted-foreground">
									{ __( 'Entitle existing matching employees to this policy now.', 'erp' ) }
								</span>
							</Label>
							<Switch
								id="policy_apply_existing"
								checked={ form.apply_for_existing }
								onCheckedChange={ ( checked ) => setForm( ( p ) => ( { ...p, apply_for_existing: checked } ) ) }
							/>
						</div>
					) : null }

					<LeaveExtraFields
						fields={ extraFields }
						values={ extra }
						onChange={ ( field, value ) => setExtra( ( p ) => setLeaveFieldValue( p, field, value ) ) }
					/>

					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					<DialogFooter className="gap-5 sm:gap-5">
						<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onClose }>
							{ __( 'Cancel', 'erp' ) }
						</Button>
						<Button type="submit" className="h-10 px-6" disabled={ busy }>
							{ busy
								? __( 'Saving…', 'erp' )
								: editing
								? __( 'Update Policy', 'erp' )
								: __( 'Create Policy', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
