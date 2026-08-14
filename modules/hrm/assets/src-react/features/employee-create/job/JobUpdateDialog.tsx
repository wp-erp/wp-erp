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
 * The per-action field groups live alongside (JobStatus/Type/Compensation/Info
 * Fields); shared types/defaults are in job-update-helpers.
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

import { loadLookup } from '../../employees/filters/lookups';
import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';
import { TextField } from '../fields';
import type { Option } from '../options';
import { JobCompensationFields } from './JobCompensationFields';
import { JobInfoFields } from './JobInfoFields';
import { JobStatusFields } from './JobStatusFields';
import { JobTypeFields } from './JobTypeFields';
import { emptyForm, TITLES, toOptions } from './job-update-helpers';
import type { FormState, JobAction } from './job-update-helpers';

// Re-exported so existing consumers keep importing it from JobUpdateDialog.
export type { JobAction };

interface JobUpdateDialogProps {
	readonly action:   JobAction | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: Record< string, unknown > ) => void;
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

		if ( ! form.date ) {
			toast.error( __( 'Please fill all required fields.', 'erp' ) );
			return;
		}
		if ( action === 'status' ) {
			if ( ! form.category ) {
				toast.error( __( 'Please fill all required fields.', 'erp' ) );
				return;
			}
			if ( form.category === 'terminated' && ( ! form.termination_type || ! form.termination_reason || ! form.eligible_for_rehire ) ) {
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
		if ( action === 'job' && ( ! form.department || ! form.designation || ! form.reporting_to ) ) {
			toast.error( __( 'Please fill all required fields.', 'erp' ) );
			return;
		}

		onSubmit( payload );
	}

	return (
		<Dialog open={ action !== null } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">{ action ? TITLES[ action ] : '' }</DialogTitle>
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

					{ action === 'status' ? <JobStatusFields form={ form } set={ set } /> : null }

					{ action === 'type' ? <JobTypeFields form={ form } set={ set } /> : null }

					{ action === 'compensation' ? <JobCompensationFields form={ form } set={ set } /> : null }

					{ action === 'job' ? (
						<JobInfoFields
							form={ form }
							set={ set }
							departments={ departments }
							designations={ designations }
							locations={ locations }
							reporting={ reporting }
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
	);
}
