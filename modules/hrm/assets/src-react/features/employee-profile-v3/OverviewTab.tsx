/**
 * Personal-tab body for the Employee Profile v3 layout: pro extra fields plus
 * the Employment / Contact / Personal / Home Address detail cards and the
 * general sections. Each card carries an edit affordance for self / managers.
 */

import {
	Briefcase,
	Building2,
	Calendar,
	Compass,
	DollarSign,
	Droplets,
	Flag,
	Globe,
	GraduationCap,
	Hash,
	Heart,
	Home,
	IdCard,
	Mail,
	Map,
	MapPin,
	Phone,
	Smartphone,
	Tag,
	User,
	UserCog,
	Wallet,
} from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { EmployeeExtraFieldsView } from '../employee-create/EmployeeExtraFieldsView';
import { InfoCard, Field } from './DetailCard';
import { EmployeeGeneralSections } from './general/EmployeeGeneralSections';
import {
	BLOOD_GROUP_OPTIONS,
	GENDER_OPTIONS,
	MARITAL_OPTIONS,
	PAY_TYPE_OPTIONS,
	SOURCE_OPTIONS,
	TYPE_OPTIONS,
} from './options';
import { labelOf, str, type Record_ } from './profile-format';

interface OverviewTabProps {
	readonly userId:  number;
	readonly record:  Record_;
	readonly canEdit: boolean;
	readonly onEdit:  () => void;
}

export function OverviewTab( { userId, record, canEdit, onEdit }: OverviewTabProps ): JSX.Element {
	return (
		<div className="space-y-6">
			<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'top' ] } />

			<InfoCard icon={ Briefcase } tone="bg-amber-100 text-amber-700" title={ __( 'Employment', 'erp' ) } onEdit={ canEdit ? onEdit : undefined }>
				<Field icon={ IdCard } label={ __( 'Employee ID', 'erp' ) } value={ str( record, 'employee_id' ) } />
				<Field icon={ Briefcase } label={ __( 'Employee Type', 'erp' ) } value={ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) } />
				<Field icon={ Calendar } label={ __( 'Date of Hire', 'erp' ) } value={ str( record, 'hiring_date' ) } />
				<Field icon={ Building2 } label={ __( 'Department', 'erp' ) } value={ str( record, 'department_name' ) } />
				<Field icon={ Tag } label={ __( 'Job Title', 'erp' ) } value={ str( record, 'designation_name' ) } />
				<Field icon={ UserCog } label={ __( 'Reporting To', 'erp' ) } value={ str( record, 'reporting_to_name' ) } />
				<Field icon={ Compass } label={ __( 'Source of Hire', 'erp' ) } value={ labelOf( SOURCE_OPTIONS, str( record, 'hiring_source' ) ) } />
				<Field icon={ DollarSign } label={ __( 'Pay Rate', 'erp' ) } value={ str( record, 'pay_rate' ) } />
				<Field icon={ Wallet } label={ __( 'Pay Type', 'erp' ) } value={ labelOf( PAY_TYPE_OPTIONS, str( record, 'pay_type' ) ) } />
			</InfoCard>

			<InfoCard icon={ Mail } tone="bg-sky-100 text-sky-700" title={ __( 'Contact', 'erp' ) } onEdit={ canEdit ? onEdit : undefined }>
				<Field icon={ Mail } label={ __( 'Email', 'erp' ) } value={ str( record, 'email' ) } />
				<Field icon={ Mail } label={ __( 'Other Email', 'erp' ) } value={ str( record, 'other_email' ) } />
				<Field icon={ Smartphone } label={ __( 'Mobile', 'erp' ) } value={ str( record, 'mobile' ) } />
				<Field icon={ Phone } label={ __( 'Phone', 'erp' ) } value={ str( record, 'phone' ) } />
				<Field icon={ Phone } label={ __( 'Work Phone', 'erp' ) } value={ str( record, 'work_phone' ) } />
				<Field icon={ Globe } label={ __( 'Website', 'erp' ) } value={ str( record, 'user_url' ) } />
			</InfoCard>

			<InfoCard icon={ GraduationCap } tone="bg-violet-100 text-violet-700" title={ __( 'Personal Details', 'erp' ) } onEdit={ canEdit ? onEdit : undefined }>
				<Field icon={ Calendar } label={ __( 'Date of Birth', 'erp' ) } value={ str( record, 'date_of_birth' ) } />
				<Field icon={ User } label={ __( 'Gender', 'erp' ) } value={ labelOf( GENDER_OPTIONS, str( record, 'gender' ) ) } />
				<Field icon={ Heart } label={ __( 'Marital Status', 'erp' ) } value={ labelOf( MARITAL_OPTIONS, str( record, 'marital_status' ) ) } />
				<Field icon={ Droplets } label={ __( 'Blood Group', 'erp' ) } value={ labelOf( BLOOD_GROUP_OPTIONS, str( record, 'blood_group' ) ) } />
				<Field icon={ Flag } label={ __( 'Nationality', 'erp' ) } value={ str( record, 'nationality' ) } />
				<Field icon={ User } label={ __( "Father's name", 'erp' ) } value={ str( record, 'father_name' ) } />
				<Field icon={ User } label={ __( "Mother's name", 'erp' ) } value={ str( record, 'mother_name' ) } />
			</InfoCard>

			<InfoCard icon={ Home } tone="bg-rose-100 text-rose-700" title={ __( 'Home Address', 'erp' ) } onEdit={ canEdit ? onEdit : undefined }>
				<Field icon={ MapPin } label={ __( 'Address', 'erp' ) } value={ str( record, 'street_1' ) } />
				<Field icon={ MapPin } label={ __( 'Address (cont.)', 'erp' ) } value={ str( record, 'street_2' ) } />
				<Field icon={ Building2 } label={ __( 'City', 'erp' ) } value={ str( record, 'city' ) } />
				<Field icon={ Map } label={ __( 'Province / State', 'erp' ) } value={ str( record, 'state' ) } />
				<Field icon={ Globe } label={ __( 'Country', 'erp' ) } value={ str( record, 'country' ) } />
				<Field icon={ Hash } label={ __( 'Postal code', 'erp' ) } value={ str( record, 'postal_code' ) } />
			</InfoCard>

			<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'basic', 'work', 'personal', 'bottom' ] } />

			{ canEdit ? <EmployeeGeneralSections userId={ userId } /> : null }
		</div>
	);
}
