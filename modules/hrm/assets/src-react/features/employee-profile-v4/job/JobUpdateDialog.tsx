/**
 * Update dialog for the Job tab — one component, four actions:
 *   status       → module 'employee'   (Update Status)
 *   type         → module 'employment' (Update Type)
 *   compensation → module 'compensation'
 *   job          → module 'job'         (Update Job Information)
 *
 * Mirrors the legacy job-tab modals. Submits a flat payload to the v2
 * `/job-histories` POST, which delegates to the unchanged v1 model methods.
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
import { useEffect, useMemo, useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { __ } from '@/shared/i18n';
import { QuickAddButton } from '@/shared/components/QuickAddButton';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import { DepartmentFormDialog } from '../../departments/DepartmentFormDialog';
import type { Department, DepartmentInput } from '../../departments/types';
import { DesignationFormDialog } from '../../designations/DesignationFormDialog';
import type { Designation, DesignationInput } from '../../designations/types';
import { loadLookup } from '../../employees/filters/lookups';
import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';
import type { LookupOption } from '../../employees/filters/lookups';
import {
	REHIRE_OPTIONS,
	TERMINATION_REASON_OPTIONS,
	TERMINATION_TYPE_OPTIONS,
} from '../../employees/terminate-options';
import { SelectField, SmartSelectField, TextField, TextareaField } from '../fields';
import type { Option } from '../options';
import { PAY_CHANGE_REASON_OPTIONS, PAY_TYPE_OPTIONS, STATUS_OPTIONS, TYPE_OPTIONS } from '../options';

export type JobAction = 'status' | 'type' | 'compensation' | 'job';

interface JobUpdateDialogProps {
	readonly action:   JobAction | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: Record< string, unknown > ) => void;
}

interface FormState {
	date:         string;
	category:     string; // status code
	type:         string; // type code
	comments:     string;
	pay_rate:     string;
	pay_type:     string;
	reason:       string;
	comment:      string;
	department:   string;
	designation:  string;
	location:     string;
	reporting_to: string;
	// Termination fields — shown only when status === 'terminated'.
	termination_type:    string;
	termination_reason:  string;
	eligible_for_rehire: string;
}

function todayISO(): string {
	const d = new Date();
	if ( Number.isNaN( d.getTime() ) ) {
		return '';
	}
	return d.toISOString().slice( 0, 10 );
}

function emptyForm(): FormState {
	return {
		date:         todayISO(),
		category:     '',
		type:         '',
		comments:     '',
		pay_rate:     '',
		pay_type:     '',
		reason:       '',
		comment:      '',
		department:   '',
		designation:  '',
		location:     '',
		reporting_to: '',
		termination_type:    '',
		termination_reason:  '',
		eligible_for_rehire: '',
	};
}

const TITLES: Record< JobAction, string > = {
	status:       __( 'Update Status', 'erp' ),
	type:         __( 'Update Type', 'erp' ),
	compensation: __( 'Update Compensation', 'erp' ),
	job:          __( 'Update Job Information', 'erp' ),
};

function toOptions( list: readonly LookupOption[] ): Option[] {
	return list.map( ( l ) => ( { value: String( l.id ), label: l.title } ) );
}

export function JobUpdateDialog( {
	action,
	busy,
	error,
	onClose,
	onSubmit,
}: JobUpdateDialogProps ): JSX.Element {
	const [ form, setForm ] = useState< FormState >( emptyForm );

	const [ departments, setDepartments ]   = useState< Option[] >( [] );
	const [ designations, setDesignations ] = useState< Option[] >( [] );
	const [ locations, setLocations ]       = useState< Option[] >( [] );
	const reporting = useEmployeeSearch( action === 'job', undefined, form.reporting_to );

	// Reset the form each time a dialog opens.
	useEffect( () => {
		if ( action ) {
			setForm( emptyForm() );
		}
	}, [ action ] );

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
				// with the four termination fields.
				if ( form.category === 'terminated' ) {
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
	}, [ action, form ] );

	function handleSubmit( e: FormEvent ): void {
		e.preventDefault();
		onSubmit( payload );
	}

	return (
		<>
		<Dialog open={ action !== null } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ action ? TITLES[ action ] : '' }</DialogTitle>
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
						<>
							<SelectField
								id="job_status"
								label={ __( 'Employee Status', 'erp' ) }
								required
								options={ STATUS_OPTIONS }
								value={ form.category }
								onChange={ set( 'category' ) }
								placeholder={ __( '- Select -', 'erp' ) }
							/>
							{ form.category === 'terminated' ? (
								<>
									<SelectField id="job_term_type" label={ __( 'Termination Type', 'erp' ) } required options={ TERMINATION_TYPE_OPTIONS } value={ form.termination_type } onChange={ set( 'termination_type' ) } placeholder={ __( '- Select -', 'erp' ) } />
									<SelectField id="job_term_reason" label={ __( 'Termination Reason', 'erp' ) } required options={ TERMINATION_REASON_OPTIONS } value={ form.termination_reason } onChange={ set( 'termination_reason' ) } placeholder={ __( '- Select -', 'erp' ) } />
									<SelectField id="job_term_rehire" label={ __( 'Eligible for Rehire', 'erp' ) } required options={ REHIRE_OPTIONS } value={ form.eligible_for_rehire } onChange={ set( 'eligible_for_rehire' ) } placeholder={ __( '- Select -', 'erp' ) } />
								</>
							) : (
								<TextareaField id="job_status_comment" label={ __( 'Comment', 'erp' ) } value={ form.comments } onChange={ set( 'comments' ) } />
							) }
						</>
					) : null }

					{ action === 'type' ? (
						<>
							<SelectField
								id="job_type"
								label={ __( 'Employment Type', 'erp' ) }
								required
								options={ TYPE_OPTIONS }
								value={ form.type }
								onChange={ set( 'type' ) }
								placeholder={ __( '- Select -', 'erp' ) }
							/>
							<TextareaField id="job_type_comment" label={ __( 'Comment', 'erp' ) } value={ form.comments } onChange={ set( 'comments' ) } />
						</>
					) : null }

					{ action === 'compensation' ? (
						<>
							<TextField id="job_pay_rate" label={ __( 'Pay Rate', 'erp' ) } type="number" required value={ form.pay_rate } onChange={ set( 'pay_rate' ) } />
							<SelectField id="job_pay_type" label={ __( 'Pay Type', 'erp' ) } required options={ PAY_TYPE_OPTIONS } value={ form.pay_type } onChange={ set( 'pay_type' ) } placeholder={ __( '- Select -', 'erp' ) } />
							<SelectField id="job_reason" label={ __( 'Change Reason', 'erp' ) } options={ PAY_CHANGE_REASON_OPTIONS } value={ form.reason } onChange={ set( 'reason' ) } placeholder={ __( '- Select -', 'erp' ) } />
							<TextareaField id="job_comp_comment" label={ __( 'Comment', 'erp' ) } value={ form.comment } onChange={ set( 'comment' ) } />
						</>
					) : null }

					{ action === 'job' ? (
						<>
							<SmartSelectField id="job_department" label={ __( 'Department', 'erp' ) } required options={ departments } value={ form.department } onChange={ set( 'department' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search departments…', 'erp' ) } labelAction={ <QuickAddButton label={ __( 'Add new', 'erp' ) } onClick={ () => { setQuickDeptErr( null ); setQuickDeptOpen( true ); } } disabled={ busy } /> } />
							<SmartSelectField id="job_designation" label={ __( 'Job Title', 'erp' ) } required options={ designations } value={ form.designation } onChange={ set( 'designation' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search job titles…', 'erp' ) } labelAction={ <QuickAddButton label={ __( 'Add new', 'erp' ) } onClick={ () => { setQuickDesigErr( null ); setQuickDesigOpen( true ); } } disabled={ busy } /> } />
							<SmartSelectField id="job_location" label={ __( 'Location', 'erp' ) } options={ locations } value={ form.location } onChange={ set( 'location' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search locations…', 'erp' ) } />
							<SmartSelectField id="job_reporting" label={ __( 'Reporting To', 'erp' ) } required options={ reporting.options } value={ form.reporting_to } onChange={ set( 'reporting_to' ) } onSearch={ reporting.onSearch } loading={ reporting.loading } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search employees…', 'erp' ) } />
						</>
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
