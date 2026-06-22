/**
 * "Work" section of the employee create/edit form. Create mode shows the full
 * set (Location, Reporting To, Source of Hire, Pay Rate, Pay Type, Work Phone);
 * edit mode shows only Source of Hire + Work Phone — the rest move to the
 * single-employee Job/Compensation tabs, matching the legacy form.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

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
	readonly isEdit:    boolean;
	readonly locations: Option[];
	readonly reporting: ReturnType< typeof useEmployeeSearch >;
}

export function EmployeeWorkSection( {
	form,
	set,
	isEdit,
	locations,
	reporting,
}: EmployeeWorkSectionProps ): JSX.Element {
	if ( ! isEdit ) {
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
				/>
			</FormSection>
		);
	}

	return (
		<FormSection title={ __( 'Work', 'erp' ) }>
			<SelectField
				id="hiring_source"
				label={ __( 'Source of Hire', 'erp' ) }
				options={ SOURCE_OPTIONS }
				value={ form.hiring_source ?? '' }
				onChange={ set( 'hiring_source' ) }
				placeholder={ __( '- Select -', 'erp' ) }
			/>
			<TextField
				id="work_phone"
				label={ __( 'Work Phone', 'erp' ) }
				type="tel"
				value={ form.work_phone ?? '' }
				onChange={ set( 'work_phone' ) }
			/>
		</FormSection>
	);
}
