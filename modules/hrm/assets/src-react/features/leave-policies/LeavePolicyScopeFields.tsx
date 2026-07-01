/**
 * Optional scope selects of the leave-policy form: employee type, department,
 * designation, gender, marital status, location, and "applicable after" days.
 * Each scope defaults to the `-1` "All" sentinel. Presentational — the dialog
 * owns state.
 */

import type { Dispatch, JSX, SetStateAction } from 'react';

import { __ } from '@/shared/i18n';

import { SelectField, SmartSelectField, TextField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import { ALL, type FormState } from './leave-policy-form-helpers';

interface LeavePolicyScopeFieldsProps {
	readonly form:         FormState;
	readonly setForm:      Dispatch< SetStateAction< FormState > >;
	readonly empTypeOpts:  Option[];
	readonly deptOpts:     Option[];
	readonly desigOpts:    Option[];
	readonly genderOpts:   Option[];
	readonly maritalOpts:  Option[];
	readonly locationOpts: Option[];
}

export function LeavePolicyScopeFields( {
	form,
	setForm,
	empTypeOpts,
	deptOpts,
	desigOpts,
	genderOpts,
	maritalOpts,
	locationOpts,
}: LeavePolicyScopeFieldsProps ): JSX.Element {
	return (
		<>
			<div className="grid grid-cols-2 gap-4">
				<SelectField
					id="policy_employee_type"
					label={ __( 'Employee Type', 'erp' ) }
					options={ empTypeOpts }
					value={ form.employee_type }
					onChange={ ( v ) => setForm( ( p ) => ( { ...p, employee_type: v } ) ) }
				/>
				<SmartSelectField
					id="policy_department"
					label={ __( 'Department', 'erp' ) }
					options={ deptOpts }
					value={ form.department_id }
					onChange={ ( v ) => setForm( ( p ) => ( { ...p, department_id: v || ALL } ) ) }
					placeholder={ __( 'All', 'erp' ) }
					searchPlaceholder={ __( 'Search departments…', 'erp' ) }
					emptyMessage={ __( 'No departments found.', 'erp' ) }
				/>
			</div>

			<div className="grid grid-cols-2 gap-4">
				<SmartSelectField
					id="policy_designation"
					label={ __( 'Designation', 'erp' ) }
					options={ desigOpts }
					value={ form.designation_id }
					onChange={ ( v ) => setForm( ( p ) => ( { ...p, designation_id: v || ALL } ) ) }
					placeholder={ __( 'All', 'erp' ) }
					searchPlaceholder={ __( 'Search designations…', 'erp' ) }
					emptyMessage={ __( 'No designations found.', 'erp' ) }
				/>
				<SelectField
					id="policy_gender"
					label={ __( 'Gender', 'erp' ) }
					options={ genderOpts }
					value={ form.gender }
					onChange={ ( v ) => setForm( ( p ) => ( { ...p, gender: v } ) ) }
				/>
			</div>

			<div className="grid grid-cols-2 gap-4">
				<SelectField
					id="policy_marital"
					label={ __( 'Marital Status', 'erp' ) }
					options={ maritalOpts }
					value={ form.marital }
					onChange={ ( v ) => setForm( ( p ) => ( { ...p, marital: v } ) ) }
				/>
			</div>

			<div className="grid grid-cols-2 gap-4">
				<SmartSelectField
					id="policy_location"
					label={ __( 'Location', 'erp' ) }
					options={ locationOpts }
					value={ form.location_id }
					onChange={ ( v ) => setForm( ( p ) => ( { ...p, location_id: v || ALL } ) ) }
					placeholder={ __( 'All', 'erp' ) }
					searchPlaceholder={ __( 'Search locations…', 'erp' ) }
					emptyMessage={ __( 'No locations found.', 'erp' ) }
				/>
				<TextField
					id="policy_applicable_from"
					label={ __( 'Applicable After (days)', 'erp' ) }
					type="number"
					value={ form.applicable_from }
					onChange={ ( v ) => setForm( ( p ) => ( { ...p, applicable_from: v } ) ) }
				/>
			</div>
		</>
	);
}
