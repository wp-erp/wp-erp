/**
 * Status action fields for the Job update dialog: the Employee Status select
 * plus — when "Terminated" is chosen — the three termination fields, otherwise
 * a free-text comment. Presentational; the dialog owns the form state.
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
	/**
	 * Edit-in-place mode: editing the current status row only changes
	 * category / comment / date — it never re-runs the termination flow (which
	 * writes the extra termination meta), so the termination sub-fields stay
	 * hidden even when the status is "Terminated".
	 */
	readonly editing?: boolean;
}

export function JobStatusFields( { form, set, editing }: JobStatusFieldsProps ): JSX.Element {
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
			{ ! editing && form.category === 'terminated' ? (
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
