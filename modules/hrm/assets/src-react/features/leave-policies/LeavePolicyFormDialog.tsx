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
 *
 * The field markup lives in flat siblings (PrimaryFields, ScopeFields, Toggles)
 * and the pure form shape/helpers in leave-policy-form-helpers; this file keeps
 * the state, effects, option memos and submit/validation handlers.
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
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import { TextareaField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import { LeaveTypeFormDialog } from '../leave-types/LeaveTypeFormDialog';
import type { LeaveType, LeaveTypeInput } from '../leave-types/types';
import { LeavePolicyPrimaryFields } from './LeavePolicyPrimaryFields';
import { LeavePolicyScopeFields } from './LeavePolicyScopeFields';
import { LeavePolicyToggles } from './LeavePolicyToggles';
import {
	ALL,
	EMPTY,
	withAll,
	type FormState,
	type PolicyErrors,
} from './leave-policy-form-helpers';
import type { LeavePolicy, LeavePolicyInput, PolicyFormOptions } from './types';

interface LeavePolicyFormDialogProps {
	readonly open:     boolean;
	readonly editing:  LeavePolicy | null;
	/**
	 * Create-mode prefill (Duplicate action): when `editing` is null but `seed`
	 * is set, the form fields are pre-populated from this policy so the user
	 * saves a copy. Ignored while editing.
	 */
	readonly seed?:    LeavePolicy | null;
	readonly options:  PolicyFormOptions | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: LeavePolicyInput ) => void;
	/** Refresh the host's cached form-options after an inline dependency is created. */
	readonly onOptionsStale?: () => void;
}

export function LeavePolicyFormDialog( {
	open,
	editing,
	seed,
	options,
	busy,
	error,
	onClose,
	onSubmit,
	onOptionsStale,
}: LeavePolicyFormDialogProps ): JSX.Element {
	const [ form, setForm ]     = useState< FormState >( EMPTY );
	const [ errors, setErrors ] = useState< PolicyErrors >( {} );

	// Inline "+ Add new" leave type — opens the same dialog the Leave Types
	// screen uses, then merges the new type into this select and selects it,
	// so the user never leaves the half-filled policy form.
	const [ quickTypeOpen, setQuickTypeOpen ] = useState( false );
	const [ quickTypeBusy, setQuickTypeBusy ] = useState( false );
	const [ quickTypeErr, setQuickTypeErr ]   = useState< string | null >( null );
	const [ createdTypes, setCreatedTypes ]   = useState< Option[] >( [] );

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
			{ mode: editing ? 'edit' : 'create', saved: editing ?? seed ?? {} }
		) as LeaveExtraField[];
		setExtraFields( fields );
		setExtra( initLeaveFieldValues( fields ) );
	}, [ open, editing, seed ] );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setErrors( {} );
		setQuickTypeOpen( false );
		setQuickTypeErr( null );
		setCreatedTypes( [] );
		// On edit, prefill from the policy. On a Duplicate (create + `seed`),
		// prefill the same scope/days/colour so the user saves a copy.
		const source = editing ?? seed ?? null;
		setForm(
			source
				? {
						leave_id:            source.leave_id ? String( source.leave_id ) : '',
						days:                String( source.days ),
						color:               source.color || '#3b82f6',
						description:         source.description,
						f_year:              source.f_year ? String( source.f_year ) : '',
						applicable_from:     String( source.applicable_from ?? 0 ),
						employee_type:       source.employee_type || ALL,
						department_id:       source.department_id || ALL,
						designation_id:      source.designation_id || ALL,
						location_id:         source.location_id || ALL,
						gender:              source.gender || ALL,
						marital:             source.marital || ALL,
						apply_for_new_users: source.apply_for_new_users,
						apply_for_existing:  false,
				  }
				: EMPTY
		);
	}, [ open, editing, seed ] );

	const leaveTypeOpts = useMemo< Option[] >(
		() => {
			const base = ( options?.leaveTypes ?? [] ).map( ( t ) => ( { value: String( t.id ), label: t.label } ) );
			// Merge inline-created types not yet reflected in the host's cached options.
			const seen = new Set( base.map( ( o ) => o.value ) );
			return [ ...base, ...createdTypes.filter( ( o ) => ! seen.has( o.value ) ) ];
		},
		[ options, createdTypes ]
	);

	function handleQuickTypeCreate( payload: LeaveTypeInput ): void {
		setQuickTypeBusy( true );
		setQuickTypeErr( null );
		request< LeaveType >( restPath( 'v2', '/leave-types' ), { method: 'POST', data: payload } )
			.then( ( created ) => {
				const opt: Option = { value: String( created.id ), label: created.name };
				setCreatedTypes( ( prev ) => [ ...prev, opt ] );
				setForm( ( p ) => ( { ...p, leave_id: opt.value } ) );
				setErrors( ( p ) => ( { ...p, leave_id: undefined } ) );
				setQuickTypeOpen( false );
				onOptionsStale?.();
			} )
			.catch( ( raw ) => setQuickTypeErr( ( raw as ApiError )?.message ?? __( 'Could not create the leave type.', 'erp' ) ) )
			.finally( () => setQuickTypeBusy( false ) );
	}
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
	const locationOpts = useMemo< Option[] >(
		() => withAll( ( options?.locations ?? [] ).map( ( d ) => ( { value: String( d.id ), label: d.label } ) ) ),
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
			applicable_from:     Number( form.applicable_from || 0 ),
			employee_type:       form.employee_type,
			department_id:       form.department_id,
			designation_id:      form.designation_id,
			location_id:         form.location_id,
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
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">
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
					<LeavePolicyPrimaryFields
						form={ form }
						errors={ errors }
						setForm={ setForm }
						setErrors={ setErrors }
						editing={ editing }
						leaveTypeOpts={ leaveTypeOpts }
						fYearOpts={ fYearOpts }
						busy={ busy }
						onAddType={ () => { setQuickTypeErr( null ); setQuickTypeOpen( true ); } }
					/>

					<LeavePolicyScopeFields
						form={ form }
						setForm={ setForm }
						empTypeOpts={ empTypeOpts }
						deptOpts={ deptOpts }
						desigOpts={ desigOpts }
						genderOpts={ genderOpts }
						maritalOpts={ maritalOpts }
						locationOpts={ locationOpts }
					/>

					<TextareaField
						id="policy_description"
						label={ __( 'Description', 'erp' ) }
						value={ form.description }
						onChange={ ( v ) => setForm( ( p ) => ( { ...p, description: v } ) ) }
						rows={ 2 }
					/>

					<LeavePolicyToggles form={ form } setForm={ setForm } editing={ editing } />

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

			<LeaveTypeFormDialog
				open={ quickTypeOpen }
				editing={ null }
				busy={ quickTypeBusy }
				error={ quickTypeErr }
				onClose={ () => setQuickTypeOpen( false ) }
				onSubmit={ handleQuickTypeCreate }
			/>
		</Dialog>
	);
}
