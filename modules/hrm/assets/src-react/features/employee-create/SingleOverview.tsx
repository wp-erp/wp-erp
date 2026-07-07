/**
 * Personal-tab body for the v4 single-employee profile view: the pro extra-field
 * sections plus the Employment / Contact / Personal Details / Home Address field
 * grids, and the general sections (self / managers). Presentational — the page
 * owns data.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { EmployeeGeneralSections } from '../employee-profile-v0/general/EmployeeGeneralSections';
import {
	BLOOD_GROUP_OPTIONS,
	GENDER_OPTIONS,
	MARITAL_OPTIONS,
	PAY_TYPE_OPTIONS,
	SOURCE_OPTIONS,
	TYPE_OPTIONS,
} from '../employee-profile-v0/options';
import { EmployeeExtraFieldsView } from './EmployeeExtraFieldsView';
import { Field, FieldGrid } from './SingleDetailCard';
import { labelOf, str, type Record_ } from './single-format';

interface SingleOverviewProps {
	readonly userId:      number;
	readonly record:      Record_;
	readonly canEdit:     boolean;
	readonly activeLabel: string;
}

export function SingleOverview( { userId, record, canEdit, activeLabel }: SingleOverviewProps ): JSX.Element {
	return (
		<div className="space-y-6">
			<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'top' ] } />

			<section className="rounded-2xl bg-card p-6 shadow-sm ring-1 ring-border/60">
			<h2 className="mt-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
				{ activeLabel }
			</h2>
			<div className="mb-4 mt-4 h-px w-full bg-border" />

			<div className="space-y-8">
				<FieldGrid title={ __( 'Employment', 'erp' ) }>
					<Field label={ __( 'Employee ID', 'erp' ) } value={ str( record, 'employee_id' ) } />
					<Field label={ __( 'Employee Type', 'erp' ) } value={ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) } />
					<Field label={ __( 'Date of Hire', 'erp' ) } value={ str( record, 'hiring_date' ) } />
					<Field label={ __( 'Department', 'erp' ) } value={ str( record, 'department_name' ) } />
					<Field label={ __( 'Job Title', 'erp' ) } value={ str( record, 'designation_name' ) } />
					<Field label={ __( 'Reporting To', 'erp' ) } value={ str( record, 'reporting_to_name' ) } />
					<Field label={ __( 'Source of Hire', 'erp' ) } value={ labelOf( SOURCE_OPTIONS, str( record, 'hiring_source' ) ) } />
					<Field label={ __( 'Pay Rate', 'erp' ) } value={ str( record, 'pay_rate' ) } />
					<Field label={ __( 'Pay Type', 'erp' ) } value={ labelOf( PAY_TYPE_OPTIONS, str( record, 'pay_type' ) ) } />
				</FieldGrid>

				<FieldGrid title={ __( 'Contact', 'erp' ) }>
					<Field label={ __( 'Email', 'erp' ) } value={ str( record, 'email' ) } />
					<Field label={ __( 'Other Email', 'erp' ) } value={ str( record, 'other_email' ) } />
					<Field label={ __( 'Mobile', 'erp' ) } value={ str( record, 'mobile' ) } />
					<Field label={ __( 'Phone', 'erp' ) } value={ str( record, 'phone' ) } />
					<Field label={ __( 'Work Phone', 'erp' ) } value={ str( record, 'work_phone' ) } />
					<Field label={ __( 'Website', 'erp' ) } value={ str( record, 'user_url' ) } />
				</FieldGrid>

				<FieldGrid title={ __( 'Personal Details', 'erp' ) }>
					<Field label={ __( 'Date of Birth', 'erp' ) } value={ str( record, 'date_of_birth' ) } />
					<Field label={ __( 'Gender', 'erp' ) } value={ labelOf( GENDER_OPTIONS, str( record, 'gender' ) ) } />
					<Field label={ __( 'Marital Status', 'erp' ) } value={ labelOf( MARITAL_OPTIONS, str( record, 'marital_status' ) ) } />
					<Field label={ __( 'Blood Group', 'erp' ) } value={ labelOf( BLOOD_GROUP_OPTIONS, str( record, 'blood_group' ) ) } />
					<Field label={ __( 'Nationality', 'erp' ) } value={ str( record, 'nationality' ) } />
					<Field label={ __( "Father's name", 'erp' ) } value={ str( record, 'father_name' ) } />
					<Field label={ __( "Mother's name", 'erp' ) } value={ str( record, 'mother_name' ) } />
					<Field label={ __( "Spouse's name", 'erp' ) } value={ str( record, 'spouse_name' ) } />
					<Field label={ __( 'Driver License', 'erp' ) } value={ str( record, 'driving_license' ) } />
					<Field label={ __( 'Hobbies', 'erp' ) } value={ str( record, 'hobbies' ) } />
				</FieldGrid>

				<FieldGrid title={ __( 'Home Address', 'erp' ) }>
					<Field label={ __( 'Address', 'erp' ) } value={ str( record, 'street_1' ) } />
					<Field label={ __( 'Address (cont.)', 'erp' ) } value={ str( record, 'street_2' ) } />
					<Field label={ __( 'City', 'erp' ) } value={ str( record, 'city' ) } />
					<Field label={ __( 'Province / State', 'erp' ) } value={ str( record, 'state' ) } />
					<Field label={ __( 'Country', 'erp' ) } value={ str( record, 'country' ) } />
					<Field label={ __( 'Postal code', 'erp' ) } value={ str( record, 'postal_code' ) } />
				</FieldGrid>

			</div>
			</section>

			<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'basic', 'work', 'personal', 'bottom' ] } />

			{ canEdit ? <EmployeeGeneralSections userId={ userId } /> : null }
		</div>
	);
}
