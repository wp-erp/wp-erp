/**
 * Update dialog for the Job tab — one component, four actions:
 *   status       → module 'employee'   (Update Status)
 *   type         → module 'employment' (Update Type)
 *   compensation → module 'compensation'
 *   job          → module 'job'         (Update Job Information)
 *
 * Mirrors the legacy job-tab modals. Submits a flat payload to the v2
 * `/job-histories` POST, which delegates to the unchanged v1 model methods.
 *
 * The per-action field groups live alongside (JobStatusFields, JobTypeFields,
 * JobCompensationFields, JobInfoFields); shared types/defaults are in
 * job-update-helpers. This file owns state, lookups, payload and submit.
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
	toast,
} from '@wedevs/plugin-ui';
import { useEffect, useMemo, useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import { DepartmentFormDialog } from '../../departments/DepartmentFormDialog';
import type { Department, DepartmentInput } from '../../departments/types';
import { DesignationFormDialog } from '../../designations/DesignationFormDialog';
import type { Designation, DesignationInput } from '../../designations/types';
import { loadLookup } from '../../employees/filters/lookups';
import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';
import { TextField } from '../fields';
import type { Option } from '../options';
import { JobCompensationFields } from './JobCompensationFields';
import { JobInfoFields } from './JobInfoFields';
import { JobStatusFields } from './JobStatusFields';
import { JobTypeFields } from './JobTypeFields';
import {
	emptyForm,
	EDIT_TITLES,
	resolveLabelToValue,
	TITLES,
	toOptions,
} from './job-update-helpers';
import type { FormState, JobAction } from './job-update-helpers';

// Re-exported so existing consumers keep importing this from JobUpdateDialog.
export type { JobAction };

interface JobUpdateDialogProps {
	readonly action:   JobAction | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: Record< string, unknown > ) => void;
	/** Edit-in-place: the id of the history row being edited (PUT). Omit for create (POST). */
	readonly editId?:  number | undefined;
	/** Edit-in-place: form fields to seed when the dialog opens in edit mode. */
	readonly initial?: Partial< FormState > | undefined;
}

export function JobUpdateDialog( {
	action,
	busy,
	error,
	onClose,
	onSubmit,
	editId,
	initial,
}: JobUpdateDialogProps ): JSX.Element {
	const [ form, setForm ] = useState< FormState >( emptyForm );
	const isEditing = editId != null;

	const [ departments, setDepartments ]   = useState< Option[] >( [] );
	const [ designations, setDesignations ] = useState< Option[] >( [] );
	const [ locations, setLocations ]       = useState< Option[] >( [] );
	const reporting = useEmployeeSearch( action === 'job', undefined, form.reporting_to );

	// Reset the form each time a dialog opens — seeded with `initial` in edit mode.
	useEffect( () => {
		if ( action ) {
			setForm( { ...emptyForm(), ...( initial ?? {} ) } );
		}
		// Re-run only on open (action) / target-row (editId) change; `initial` is a
		// fresh object each render, so keying on it would reset the form mid-edit.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ action, editId ] );

	// Job-info needs the org lookups; load them lazily when that dialog opens.
	useEffect( () => {
		if ( action !== 'job' ) {
			return;
		}
		let cancelled = false;
		void loadLookup( 'departments' ).then( ( l ) => ! cancelled && setDepartments( toOptions( l ) ) );
		void loadLookup( 'designations' ).then( ( l ) => ! cancelled && setDesignations( toOptions( l ) ) );
		void loadLookup( 'locations' ).then( ( l ) => ! cancelled && setLocations( toOptions( l ) ) );
		return () => {
			cancelled = true;
		};
	}, [ action ] );

	// Edit mode seeds job-info dept/designation/location as labels (that's all the
	// read model returns); once the lookups load, resolve those labels to the ids
	// the selects expect. Idempotent — a value already an id is left untouched.
	useEffect( () => {
		if ( action !== 'job' || ! isEditing ) {
			return;
		}
		setForm( ( prev ) => ( {
			...prev,
			department:  resolveLabelToValue( departments, prev.department ),
			designation: resolveLabelToValue( designations, prev.designation ),
			location:    resolveLabelToValue( locations, prev.location ),
		} ) );
	}, [ action, isEditing, departments, designations, locations ] );

	const set = ( key: keyof FormState ) => ( value: string ): void =>
		setForm( ( prev ) => ( { ...prev, [ key ]: value } ) );

	// Inline "+ Add new" for the two required org dependencies — open the same
	// dialogs the Departments / Designations screens use, merge + select.
	const [ quickDeptOpen, setQuickDeptOpen ]   = useState( false );
	const [ quickDeptBusy, setQuickDeptBusy ]   = useState( false );
	const [ quickDeptErr, setQuickDeptErr ]     = useState< string | null >( null );
	const [ quickDesigOpen, setQuickDesigOpen ] = useState( false );
	const [ quickDesigBusy, setQuickDesigBusy ] = useState( false );
	const [ quickDesigErr, setQuickDesigErr ]   = useState< string | null >( null );

	function handleQuickDept( payload: DepartmentInput ): void {
		setQuickDeptBusy( true );
		setQuickDeptErr( null );
		request< Department >( restPath( 'v2', '/departments' ), { method: 'POST', data: payload } )
			.then( ( created ) => {
				const opt: Option = { value: String( created.id ), label: created.title };
				setDepartments( ( prev ) => [ ...prev, opt ] );
				set( 'department' )( opt.value );
				setQuickDeptOpen( false );
			} )
			.catch( ( raw ) => setQuickDeptErr( ( raw as ApiError )?.message ?? __( 'Could not create the department.', 'erp' ) ) )
			.finally( () => setQuickDeptBusy( false ) );
	}

	function handleQuickDesig( payload: DesignationInput ): void {
		setQuickDesigBusy( true );
		setQuickDesigErr( null );
		request< Designation >( restPath( 'v2', '/designations' ), { method: 'POST', data: payload } )
			.then( ( created ) => {
				const opt: Option = { value: String( created.id ), label: created.title };
				setDesignations( ( prev ) => [ ...prev, opt ] );
				set( 'designation' )( opt.value );
				setQuickDesigOpen( false );
			} )
			.catch( ( raw ) => setQuickDesigErr( ( raw as ApiError )?.message ?? __( 'Could not create the job title.', 'erp' ) ) )
			.finally( () => setQuickDesigBusy( false ) );
	}

	const payload = useMemo< Record< string, unknown > >( () => {
		switch ( action ) {
			case 'status':
				// Selecting "Terminated" mirrors the legacy flow: it is a termination,
				// not a plain status-history row, so route it to the terminate endpoint
				// with the four termination fields. Edit-in-place never re-runs the
				// termination flow — it just edits the current row's category/comment/date.
				if ( ! isEditing && form.category === 'terminated' ) {
					return {
						module:              'employee',
						terminate:           true,
						terminate_date:      form.date,
						termination_type:    form.termination_type,
						termination_reason:  form.termination_reason,
						eligible_for_rehire: form.eligible_for_rehire,
					};
				}
				return { module: 'employee', category: form.category, comments: form.comments, date: form.date };
			case 'type':
				return { module: 'employment', type: form.type, comments: form.comments, date: form.date };
			case 'compensation':
				return {
					module:   'compensation',
					pay_rate: form.pay_rate,
					pay_type: form.pay_type,
					reason:   form.reason,
					comment:  form.comment,
					date:     form.date,
				};
			case 'job':
				return {
					module:       'job',
					department:   form.department ? Number( form.department ) : 0,
					designation:  form.designation ? Number( form.designation ) : 0,
					location:     form.location ? Number( form.location ) : 0,
					reporting_to: form.reporting_to ? Number( form.reporting_to ) : 0,
					date:         form.date,
				};
			default:
				return {};
		}
	}, [ action, form, isEditing ] );

	function handleSubmit( e: FormEvent ): void {
		e.preventDefault();

		if ( ! form.date ) {
			toast.error( __( 'Please fill all required fields.', 'erp' ) );
			return;
		}
		if ( action === 'status' ) {
			if ( ! form.category ) {
				toast.error( __( 'Please fill all required fields.', 'erp' ) );
				return;
			}
			if ( ! isEditing && form.category === 'terminated' && ( ! form.termination_type || ! form.termination_reason || ! form.eligible_for_rehire ) ) {
				toast.error( __( 'Please fill all required fields.', 'erp' ) );
				return;
			}
		}
		if ( action === 'type' && ! form.type ) {
			toast.error( __( 'Please fill all required fields.', 'erp' ) );
			return;
		}
		if ( action === 'compensation' && ( ! form.pay_rate.trim() || ! form.pay_type ) ) {
			toast.error( __( 'Please fill all required fields.', 'erp' ) );
			return;
		}
		// Legacy job-info required only the effective date (enforced above); department,
		// designation and reporting-to were all optional. Don't over-validate here.

		onSubmit( payload );
	}

	return (
		<>
		<Dialog open={ action !== null } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">{ action ? ( isEditing ? EDIT_TITLES[ action ] : TITLES[ action ] ) : '' }</DialogTitle>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					<TextField
						id="job_date"
						label={ __( 'Date', 'erp' ) }
						type="date"
						required
						value={ form.date }
						onChange={ set( 'date' ) }
					/>

					{ action === 'status' ? (
						<JobStatusFields form={ form } set={ set } editing={ isEditing } />
					) : null }

					{ action === 'type' ? (
						<JobTypeFields form={ form } set={ set } />
					) : null }

					{ action === 'compensation' ? (
						<JobCompensationFields form={ form } set={ set } />
					) : null }

					{ action === 'job' ? (
						<JobInfoFields
							form={ form }
							set={ set }
							busy={ busy }
							departments={ departments }
							designations={ designations }
							locations={ locations }
							reporting={ reporting }
							onAddDept={ () => { setQuickDeptErr( null ); setQuickDeptOpen( true ); } }
							onAddDesig={ () => { setQuickDesigErr( null ); setQuickDesigOpen( true ); } }
						/>
					) : null }

					<DialogFooter className="gap-5 sm:gap-5">
						<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onClose }>
							{ __( 'Cancel', 'erp' ) }
						</Button>
						<Button type="submit" className="h-10 px-6" disabled={ busy }>
							{ busy ? __( 'Saving…', 'erp' ) : __( 'Save', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>

		<DepartmentFormDialog
			open={ quickDeptOpen }
			editing={ null }
			departments={ [] }
			busy={ quickDeptBusy }
			error={ quickDeptErr }
			onClose={ () => setQuickDeptOpen( false ) }
			onSubmit={ handleQuickDept }
		/>
		<DesignationFormDialog
			open={ quickDesigOpen }
			editing={ null }
			busy={ quickDesigBusy }
			error={ quickDesigErr }
			onClose={ () => setQuickDesigOpen( false ) }
			onSubmit={ handleQuickDesig }
		/>
		</>
	);
}
