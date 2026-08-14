/**
 * Type action fields for the Job-tab update dialog: employment type + comment.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { SelectField, TextareaField } from '../fields';
import { TYPE_OPTIONS } from '../options';
import type { FormState } from './job-update-helpers';

interface JobTypeFieldsProps {
	readonly form: FormState;
	readonly set:  ( key: keyof FormState ) => ( value: string ) => void;
}

export function JobTypeFields( { form, set }: JobTypeFieldsProps ): JSX.Element {
	return (
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
	);
}
