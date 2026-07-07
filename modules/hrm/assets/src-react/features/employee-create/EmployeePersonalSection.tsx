/**
 * "Personal Details" section of the employee create/edit form: blood group,
 * family/contact fields, DOB, nationality, gender/marital status, address, and
 * biography. Nationality / Country / State render as searchable selects when the
 * boot payload supplies country/state options, falling back to plain text inputs
 * otherwise. Presentational — the form owns state.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import {
	FormSection,
	SelectField,
	SmartSelectField,
	TextField,
	TextareaField,
} from './fields';
import type { Option } from './options';
import { BLOOD_GROUP_OPTIONS, GENDER_OPTIONS, MARITAL_OPTIONS } from './options';
import type { FormState } from './validation';

interface EmployeePersonalSectionProps {
	readonly form:            FormState;
	readonly set:             ( key: string ) => ( value: string ) => void;
	readonly countryOptions:  Option[];
	readonly stateOptions:    Option[];
	readonly onCountryChange: ( value: string ) => void;
}

export function EmployeePersonalSection( {
	form,
	set,
	countryOptions,
	stateOptions,
	onCountryChange,
}: EmployeePersonalSectionProps ): JSX.Element {
	return (
		<FormSection title={ __( 'Personal Details', 'erp' ) }>
			<SelectField
				id="blood_group"
				label={ __( 'Blood Group', 'erp' ) }
				options={ BLOOD_GROUP_OPTIONS }
				value={ form.blood_group ?? '' }
				onChange={ set( 'blood_group' ) }
				placeholder={ __( '- Select -', 'erp' ) }
			/>
			<TextField
				id="spouse_name"
				label={ __( "Spouse's name", 'erp' ) }
				value={ form.spouse_name ?? '' }
				onChange={ set( 'spouse_name' ) }
			/>
			<TextField
				id="father_name"
				label={ __( "Father's name", 'erp' ) }
				value={ form.father_name ?? '' }
				onChange={ set( 'father_name' ) }
			/>
			<TextField
				id="mother_name"
				label={ __( "Mother's name", 'erp' ) }
				value={ form.mother_name ?? '' }
				onChange={ set( 'mother_name' ) }
			/>
			<TextField
				id="mobile"
				label={ __( 'Mobile', 'erp' ) }
				type="tel"
				value={ form.mobile ?? '' }
				onChange={ set( 'mobile' ) }
			/>
			<TextField
				id="phone"
				label={ __( 'Phone', 'erp' ) }
				type="tel"
				value={ form.phone ?? '' }
				onChange={ set( 'phone' ) }
			/>
			<TextField
				id="other_email"
				label={ __( 'Other Email', 'erp' ) }
				type="email"
				value={ form.other_email ?? '' }
				onChange={ set( 'other_email' ) }
			/>
			<TextField
				id="date_of_birth"
				label={ __( 'Date of Birth', 'erp' ) }
				type="date"
				value={ form.date_of_birth ?? '' }
				onChange={ set( 'date_of_birth' ) }
			/>
			{ countryOptions.length > 0 ? (
				<SmartSelectField
					id="nationality"
					label={ __( 'Nationality', 'erp' ) }
					options={ countryOptions }
					value={ form.nationality ?? '' }
					onChange={ set( 'nationality' ) }
					placeholder={ __( '- Select -', 'erp' ) }
					searchPlaceholder={ __(
						'Search countries…',
						'erp'
					) }
				/>
			) : (
				<TextField
					id="nationality"
					label={ __( 'Nationality', 'erp' ) }
					value={ form.nationality ?? '' }
					onChange={ set( 'nationality' ) }
				/>
			) }
			<SelectField
				id="gender"
				label={ __( 'Gender', 'erp' ) }
				options={ GENDER_OPTIONS }
				value={ form.gender ?? '' }
				onChange={ set( 'gender' ) }
				placeholder={ __( '- Select -', 'erp' ) }
			/>
			<SelectField
				id="marital_status"
				label={ __( 'Marital Status', 'erp' ) }
				options={ MARITAL_OPTIONS }
				value={ form.marital_status ?? '' }
				onChange={ set( 'marital_status' ) }
				placeholder={ __( '- Select -', 'erp' ) }
			/>
			<TextField
				id="driving_license"
				label={ __( 'Driving License', 'erp' ) }
				value={ form.driving_license ?? '' }
				onChange={ set( 'driving_license' ) }
			/>
			<TextField
				id="hobbies"
				label={ __( 'Hobbies', 'erp' ) }
				value={ form.hobbies ?? '' }
				onChange={ set( 'hobbies' ) }
			/>
			<TextField
				id="user_url"
				label={ __( 'Website', 'erp' ) }
				type="url"
				value={ form.user_url ?? '' }
				onChange={ set( 'user_url' ) }
			/>
			<TextField
				id="street_1"
				label={ __( 'Address 1', 'erp' ) }
				value={ form.street_1 ?? '' }
				onChange={ set( 'street_1' ) }
			/>
			<TextField
				id="street_2"
				label={ __( 'Address 2', 'erp' ) }
				value={ form.street_2 ?? '' }
				onChange={ set( 'street_2' ) }
			/>
			<TextField
				id="city"
				label={ __( 'City', 'erp' ) }
				value={ form.city ?? '' }
				onChange={ set( 'city' ) }
			/>
			{ countryOptions.length > 0 ? (
				<SmartSelectField
					id="country"
					label={ __( 'Country', 'erp' ) }
					options={ countryOptions }
					value={ form.country ?? '' }
					onChange={ onCountryChange }
					placeholder={ __( '- Select -', 'erp' ) }
					searchPlaceholder={ __(
						'Search countries…',
						'erp'
					) }
				/>
			) : (
				<TextField
					id="country"
					label={ __( 'Country', 'erp' ) }
					value={ form.country ?? '' }
					onChange={ set( 'country' ) }
				/>
			) }
			{ countryOptions.length > 0 &&
			stateOptions.length > 0 ? (
				<SmartSelectField
					id="state"
					label={ __( 'Province / State', 'erp' ) }
					options={ stateOptions }
					value={ form.state ?? '' }
					onChange={ set( 'state' ) }
					placeholder={ __( '- Select -', 'erp' ) }
					searchPlaceholder={ __(
						'Search states…',
						'erp'
					) }
				/>
			) : (
				<TextField
					id="state"
					label={ __( 'Province / State', 'erp' ) }
					value={ form.state ?? '' }
					onChange={ set( 'state' ) }
				/>
			) }
			<TextField
				id="postal_code"
				label={ __( 'Post Code / Zip Code', 'erp' ) }
				value={ form.postal_code ?? '' }
				onChange={ set( 'postal_code' ) }
			/>
			<TextareaField
				id="description"
				label={ __( 'Biography', 'erp' ) }
				value={ form.description ?? '' }
				onChange={ set( 'description' ) }
				className="sm:col-span-2 lg:col-span-3"
			/>
		</FormSection>
	);
}
