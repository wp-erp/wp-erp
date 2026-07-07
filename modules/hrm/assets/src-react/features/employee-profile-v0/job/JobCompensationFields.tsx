/**
 * Compensation action fields for the Job update dialog: pay rate, pay type,
 * change reason and a comment. Presentational; the dialog owns the form state.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { SelectField, TextField, TextareaField } from '../fields';
import { PAY_CHANGE_REASON_OPTIONS, PAY_TYPE_OPTIONS } from '../options';
import type { FormState } from './job-update-helpers';

interface JobCompensationFieldsProps {
	readonly form: FormState;
	readonly set:  ( key: keyof FormState ) => ( value: string ) => void;
}

export function JobCompensationFields( { form, set }: JobCompensationFieldsProps ): JSX.Element {
	return (
		<>
			<TextField id="job_pay_rate" label={ __( 'Pay Rate', 'erp' ) } type="number" required value={ form.pay_rate } onChange={ set( 'pay_rate' ) } />
			<SelectField id="job_pay_type" label={ __( 'Pay Type', 'erp' ) } required options={ PAY_TYPE_OPTIONS } value={ form.pay_type } onChange={ set( 'pay_type' ) } placeholder={ __( '- Select -', 'erp' ) } />
			<SelectField id="job_reason" label={ __( 'Change Reason', 'erp' ) } options={ PAY_CHANGE_REASON_OPTIONS } value={ form.reason } onChange={ set( 'reason' ) } placeholder={ __( '- Select -', 'erp' ) } />
			<TextareaField id="job_comp_comment" label={ __( 'Comment', 'erp' ) } value={ form.comment } onChange={ set( 'comment' ) } />
		</>
	);
}
