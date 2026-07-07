/**
 * Status action fields for the Job-tab update dialog: employee status, plus the
 * termination block (type / reason / rehire) when status is "terminated", or a
 * plain comment otherwise.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import {
	REHIRE_OPTIONS,
	TERMINATION_REASON_OPTIONS,
	TERMINATION_TYPE_OPTIONS,
} from '../../employees/terminate-options';
import { SelectField, TextareaField } from '../fields';
import { STATUS_OPTIONS } from '../options';
import type { FormState } from './job-update-helpers';

interface JobStatusFieldsProps {
	readonly form: FormState;
	readonly set:  ( key: keyof FormState ) => ( value: string ) => void;
}

export function JobStatusFields( { form, set }: JobStatusFieldsProps ): JSX.Element {
	return (
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
	);
}
