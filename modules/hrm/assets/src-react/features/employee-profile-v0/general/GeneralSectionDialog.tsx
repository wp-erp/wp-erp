/**
 * Create / edit dialog for the employee General sub-entities (Work Experience,
 * Education, Dependents). One component, three field sets — chosen by `section`.
 * Field names match the v2 routes, which mirror the legacy AJAX handlers.
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
import { useEffect, useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { __ } from '@/shared/i18n';

import { SelectField, TextField, TextareaField } from '../fields';
import type { GeneralSection } from './useEmployeeGeneral';

interface GeneralSectionDialogProps {
	readonly section: GeneralSection | null;
	readonly initial: Record< string, unknown > | null;
	readonly busy:    boolean;
	readonly error:   string | null;
	readonly onClose: () => void;
	readonly onSubmit: ( data: Record< string, unknown > ) => void;
}

const RESULT_TYPE_OPTIONS = [
	{ value: 'gpa', label: __( 'GPA', 'erp' ) },
	{ value: 'grade', label: __( 'Grade', 'erp' ) },
];

function titleFor( section: GeneralSection, editing: boolean ): string {
	const map: Record< GeneralSection, [ string, string ] > = {
		experiences: [ __( 'Add Work Experience', 'erp' ), __( 'Edit Work Experience', 'erp' ) ],
		educations:  [ __( 'Add Education', 'erp' ), __( 'Edit Education', 'erp' ) ],
		dependents:  [ __( 'Add Dependent', 'erp' ), __( 'Edit Dependent', 'erp' ) ],
	};
	return editing ? map[ section ][ 1 ] : map[ section ][ 0 ];
}

function str( record: Record< string, unknown > | null, key: string ): string {
	const value = record?.[ key ];
	return value === null || value === undefined ? '' : String( value );
}

export function GeneralSectionDialog( {
	section,
	initial,
	busy,
	error,
	onClose,
	onSubmit,
}: GeneralSectionDialogProps ): JSX.Element {
	const [ form, setForm ] = useState< Record< string, string > >( {} );

	// Reset the form whenever the dialog opens for a section / row.
	useEffect( () => {
		if ( ! section ) {
			return;
		}
		setForm( {
			id:              str( initial, 'id' ),
			company_name:    str( initial, 'company_name' ),
			job_title:       str( initial, 'job_title' ),
			from:            str( initial, 'from' ),
			to:              str( initial, 'to' ),
			description:     str( initial, 'description' ),
			school:          str( initial, 'school' ),
			degree:          str( initial, 'degree' ),
			field:           str( initial, 'field' ),
			result_type:     str( initial, 'result_type' ) || 'gpa',
			gpa:             str( initial, 'gpa' ),
			scale:           str( initial, 'scale' ),
			finished:        str( initial, 'finished' ),
			notes:           str( initial, 'notes' ),
			interest:        str( initial, 'interest' ),
			expiration_date: str( initial, 'expiration_date' ),
			name:            str( initial, 'name' ),
			relation:        str( initial, 'relation' ),
			dob:             str( initial, 'dob' ),
		} );
	}, [ section, initial ] );

	const set = ( key: string ) => ( value: string ): void =>
		setForm( ( prev ) => ( { ...prev, [ key ]: value } ) );

	function handleSubmit( e: FormEvent ): void {
		e.preventDefault();
		if ( ! section ) {
			return;
		}

		if ( section === 'experiences' ) {
			if ( ! ( form.company_name ?? '' ).trim() || ! ( form.job_title ?? '' ).trim() || ! ( form.from ?? '' ).trim() || ! ( form.to ?? '' ).trim() ) {
				toast.error( __( 'Please fill all required fields.', 'erp' ) );
				return;
			}
		} else if ( section === 'educations' ) {
			if ( ! ( form.school ?? '' ).trim() || ! ( form.degree ?? '' ).trim() || ! ( form.field ?? '' ).trim() || ! ( form.gpa ?? '' ).trim() || ! ( form.finished ?? '' ).trim() ) {
				toast.error( __( 'Please fill all required fields.', 'erp' ) );
				return;
			}
			if ( form.result_type === 'grade' && ! ( form.scale ?? '' ).trim() ) {
				toast.error( __( 'Please fill all required fields.', 'erp' ) );
				return;
			}
			const year = Number( form.finished );
			if ( ! ( year >= 1970 && year <= 2099 ) ) {
				toast.error( __( 'Please enter a completion year between 1970 and 2099.', 'erp' ) );
				return;
			}
		} else if ( ! ( form.name ?? '' ).trim() || ! ( form.relation ?? '' ).trim() ) {
			toast.error( __( 'Please fill all required fields.', 'erp' ) );
			return;
		}

		const id = form.id ? Number( form.id ) : 0;
		let payload: Record< string, unknown >;

		if ( section === 'experiences' ) {
			payload = {
				id,
				company_name: form.company_name,
				job_title:    form.job_title,
				from:         form.from,
				to:           form.to,
				description:  form.description,
			};
		} else if ( section === 'educations' ) {
			payload = {
				id,
				school:          form.school,
				degree:          form.degree,
				field:           form.field,
				result_type:     form.result_type,
				gpa:             form.gpa,
				scale:           form.scale,
				finished:        form.finished,
				notes:           form.notes,
				interest:        form.interest,
				expiration_date: form.expiration_date,
			};
		} else {
			payload = {
				id,
				name:     form.name,
				relation: form.relation,
				dob:      form.dob,
			};
		}

		onSubmit( payload );
	}

	const editing = Boolean( form.id && form.id !== '0' );

	return (
		<Dialog open={ section !== null } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ section ? titleFor( section, editing ) : '' }
					</DialogTitle>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					{ section === 'experiences' ? (
						<>
							<TextField id="exp_company" label={ __( 'Company Name', 'erp' ) } required value={ form.company_name ?? '' } onChange={ set( 'company_name' ) } />
							<TextField id="exp_title" label={ __( 'Job Title', 'erp' ) } required value={ form.job_title ?? '' } onChange={ set( 'job_title' ) } />
							<TextField id="exp_from" label={ __( 'From', 'erp' ) } type="date" required value={ form.from ?? '' } onChange={ set( 'from' ) } />
							<TextField id="exp_to" label={ __( 'To', 'erp' ) } type="date" required value={ form.to ?? '' } onChange={ set( 'to' ) } />
							<TextareaField id="exp_desc" label={ __( 'Description', 'erp' ) } value={ form.description ?? '' } onChange={ set( 'description' ) } />
						</>
					) : null }

					{ section === 'educations' ? (
						<>
							<TextField id="edu_school" label={ __( 'School / Institution', 'erp' ) } required value={ form.school ?? '' } onChange={ set( 'school' ) } />
							<TextField id="edu_degree" label={ __( 'Degree', 'erp' ) } required value={ form.degree ?? '' } onChange={ set( 'degree' ) } />
							<TextField id="edu_field" label={ __( 'Field of Study', 'erp' ) } required value={ form.field ?? '' } onChange={ set( 'field' ) } />
							<SelectField id="edu_result_type" label={ __( 'Result Type', 'erp' ) } options={ RESULT_TYPE_OPTIONS } value={ form.result_type ?? 'gpa' } onChange={ set( 'result_type' ) } required />
							<TextField id="edu_gpa" label={ form.result_type === 'grade' ? __( 'Grade', 'erp' ) : __( 'GPA', 'erp' ) } required value={ form.gpa ?? '' } onChange={ set( 'gpa' ) } />
							{ form.result_type === 'grade' ? (
								<TextField id="edu_scale" label={ __( 'Scale', 'erp' ) } required value={ form.scale ?? '' } onChange={ set( 'scale' ) } />
							) : null }
							<TextField id="edu_finished" label={ __( 'Completion Year', 'erp' ) } type="number" required value={ form.finished ?? '' } onChange={ set( 'finished' ) } />
							<TextField id="edu_interest" label={ __( 'Interests', 'erp' ) } value={ form.interest ?? '' } onChange={ set( 'interest' ) } />
							<TextField id="edu_expiration" label={ __( 'Expiration Date', 'erp' ) } type="date" value={ form.expiration_date ?? '' } onChange={ set( 'expiration_date' ) } />
							<TextareaField id="edu_notes" label={ __( 'Notes', 'erp' ) } value={ form.notes ?? '' } onChange={ set( 'notes' ) } />
						</>
					) : null }

					{ section === 'dependents' ? (
						<>
							<TextField id="dep_name" label={ __( 'Name', 'erp' ) } required value={ form.name ?? '' } onChange={ set( 'name' ) } />
							<TextField id="dep_relation" label={ __( 'Relation', 'erp' ) } required value={ form.relation ?? '' } onChange={ set( 'relation' ) } />
							<TextField id="dep_dob" label={ __( 'Date of Birth', 'erp' ) } type="date" value={ form.dob ?? '' } onChange={ set( 'dob' ) } />
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
	);
}
