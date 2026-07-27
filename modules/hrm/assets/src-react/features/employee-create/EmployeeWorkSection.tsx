/**
 * "Work" section of the employee create/edit form: Location, Reporting To,
 * Source of Hire, Pay Rate, Pay Type, Work Phone.
 *
 * Create and edit render the same fields in the same order — the job/compensation
 * values stay editable from the single-employee Job tab too, but the edit form no
 * longer drops them. Only managers reach this section (the form gates it), and
 * `PUT /erp/v2/employees/{id}` strips these keys server-side for a self-editor.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { FieldSourceAction } from '@/shared/components/FieldSourceLink';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

import {
	FormSection,
	SelectField,
	SmartSelectField,
	TextField,
} from './fields';
import type { Option } from './options';
import { PAY_TYPE_OPTIONS, SOURCE_OPTIONS } from './options';
import type { FormState } from './validation';

interface EmployeeWorkSectionProps {
	readonly form:      FormState;
	readonly set:       ( key: string ) => ( value: string ) => void;
	readonly errors:    Record< string, string >;
	readonly locations: Option[];
	readonly reporting: ReturnType< typeof useEmployeeSearch >;
	/** Pro custom fields for this section — rendered inside this card, legacy-style. */
	readonly extra?:    JSX.Element | null;
}

export function EmployeeWorkSection( {
	form,
	set,
	errors,
	locations,
	reporting,
	extra,
}: EmployeeWorkSectionProps ): JSX.Element {
	return (
		<FormSection title={ __( 'Work', 'erp' ) }>
			<SmartSelectField
				id="location"
				label={ __( 'Location', 'erp' ) }
				options={ locations }
				value={ form.location ?? '' }
				onChange={ set( 'location' ) }
				placeholder={ __( '- Select -', 'erp' ) }
				searchPlaceholder={ __(
					'Search locations…',
					'erp'
				) }
				labelAction={ <FieldSourceAction source="locations" /> }
			/>
			<SmartSelectField
				id="reporting_to"
				label={ __( 'Reporting To', 'erp' ) }
				options={ reporting.options }
				value={ form.reporting_to ?? '' }
				onChange={ set( 'reporting_to' ) }
				onSearch={ reporting.onSearch }
				loading={ reporting.loading }
				placeholder={ __( '- Select -', 'erp' ) }
				searchPlaceholder={ __(
					'Search employees…',
					'erp'
				) }
			/>
			<SelectField
				id="hiring_source"
				label={ __( 'Source of Hire', 'erp' ) }
				options={ SOURCE_OPTIONS }
				value={ form.hiring_source ?? '' }
				onChange={ set( 'hiring_source' ) }
				placeholder={ __( '- Select -', 'erp' ) }
			/>
			<TextField
				id="pay_rate"
				label={ __( 'Pay Rate', 'erp' ) }
				value={ form.pay_rate ?? '' }
				onChange={ set( 'pay_rate' ) }
				error={ errors.pay_rate }
			/>
			<SelectField
				id="pay_type"
				label={ __( 'Pay Type', 'erp' ) }
				options={ PAY_TYPE_OPTIONS }
				value={ form.pay_type ?? '' }
				onChange={ set( 'pay_type' ) }
				placeholder={ __( '- Select -', 'erp' ) }
			/>
			<TextField
				id="work_phone"
				label={ __( 'Work Phone', 'erp' ) }
				type="tel"
				value={ form.work_phone ?? '' }
				onChange={ set( 'work_phone' ) }
				error={ errors.work_phone }
			/>
			{ extra }
		</FormSection>
	);
}
