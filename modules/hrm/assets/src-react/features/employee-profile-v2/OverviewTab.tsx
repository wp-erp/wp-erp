/**
 * "Personal Information" tab body for the Employee Profile v2 layout: pro extra
 * fields plus the Employment / Contact / Personal / Home Address / Biography
 * detail cards and the general sections. Each card's Edit affordance is shown
 * only for self / managers (`canEdit`).
 */

import {
	Briefcase,
	Building2,
	Calendar,
	Compass,
	DollarSign,
	Droplet,
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
import { InfoCard, SplitRow } from './DetailCard';
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

			<InfoCard
				icon={ Briefcase }
				tone="bg-sky-100 text-sky-700"
				title={ __( 'Employment', 'erp' ) }
				onEdit={ canEdit ? onEdit : undefined }
			>
				<SplitRow icon={ IdCard } label={ __( 'Employee ID', 'erp' ) } value={ str( record, 'employee_id' ) } />
				<SplitRow icon={ Briefcase } label={ __( 'Employee Type', 'erp' ) } value={ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) } />
				<SplitRow icon={ Calendar } label={ __( 'Date of Hire', 'erp' ) } value={ str( record, 'hiring_date' ) } />
				<SplitRow icon={ Building2 } label={ __( 'Department', 'erp' ) } value={ str( record, 'department_name' ) } />
				<SplitRow icon={ Tag } label={ __( 'Job Title', 'erp' ) } value={ str( record, 'designation_name' ) } />
				<SplitRow icon={ UserCog } label={ __( 'Reporting To', 'erp' ) } value={ str( record, 'reporting_to_name' ) } />
				<SplitRow icon={ Compass } label={ __( 'Source of Hire', 'erp' ) } value={ labelOf( SOURCE_OPTIONS, str( record, 'hiring_source' ) ) } />
				<SplitRow icon={ DollarSign } label={ __( 'Pay Rate', 'erp' ) } value={ str( record, 'pay_rate' ) } />
				<SplitRow icon={ Wallet } label={ __( 'Pay Type', 'erp' ) } value={ labelOf( PAY_TYPE_OPTIONS, str( record, 'pay_type' ) ) } />
			</InfoCard>

			<InfoCard
				icon={ Mail }
				tone="bg-violet-100 text-violet-700"
				title={ __( 'Contact', 'erp' ) }
				onEdit={ canEdit ? onEdit : undefined }
			>
				<SplitRow icon={ Mail } label={ __( 'Email', 'erp' ) } value={ str( record, 'email' ) } />
				<SplitRow icon={ Mail } label={ __( 'Other Email', 'erp' ) } value={ str( record, 'other_email' ) } />
				<SplitRow icon={ Smartphone } label={ __( 'Mobile', 'erp' ) } value={ str( record, 'mobile' ) } />
				<SplitRow icon={ Phone } label={ __( 'Phone', 'erp' ) } value={ str( record, 'phone' ) } />
				<SplitRow icon={ Phone } label={ __( 'Work Phone', 'erp' ) } value={ str( record, 'work_phone' ) } />
				<SplitRow icon={ Globe } label={ __( 'Website', 'erp' ) } value={ str( record, 'user_url' ) } />
			</InfoCard>

			<InfoCard
				icon={ GraduationCap }
				tone="bg-amber-100 text-amber-700"
				title={ __( 'Personal Details', 'erp' ) }
				onEdit={ canEdit ? onEdit : undefined }
			>
				<SplitRow icon={ Calendar } label={ __( 'Date of Birth', 'erp' ) } value={ str( record, 'date_of_birth' ) } />
				<SplitRow icon={ User } label={ __( 'Gender', 'erp' ) } value={ labelOf( GENDER_OPTIONS, str( record, 'gender' ) ) } />
				<SplitRow icon={ Heart } label={ __( 'Marital Status', 'erp' ) } value={ labelOf( MARITAL_OPTIONS, str( record, 'marital_status' ) ) } />
				<SplitRow icon={ Droplets } label={ __( 'Blood Group', 'erp' ) } value={ labelOf( BLOOD_GROUP_OPTIONS, str( record, 'blood_group' ) ) } />
				<SplitRow icon={ Flag } label={ __( 'Nationality', 'erp' ) } value={ str( record, 'nationality' ) } />
				<SplitRow icon={ User } label={ __( "Father's name", 'erp' ) } value={ str( record, 'father_name' ) } />
				<SplitRow icon={ User } label={ __( "Mother's name", 'erp' ) } value={ str( record, 'mother_name' ) } />
			</InfoCard>

			<InfoCard
				icon={ Home }
				tone="bg-rose-100 text-rose-700"
				title={ __( 'Home Address', 'erp' ) }
				onEdit={ canEdit ? onEdit : undefined }
			>
				<SplitRow icon={ MapPin } label={ __( 'Address', 'erp' ) } value={ str( record, 'street_1' ) } />
				<SplitRow icon={ MapPin } label={ __( 'Address (cont.)', 'erp' ) } value={ str( record, 'street_2' ) } />
				<SplitRow icon={ Building2 } label={ __( 'City', 'erp' ) } value={ str( record, 'city' ) } />
				<SplitRow icon={ Map } label={ __( 'Province / State', 'erp' ) } value={ str( record, 'state' ) } />
				<SplitRow icon={ Globe } label={ __( 'Country', 'erp' ) } value={ str( record, 'country' ) } />
				<SplitRow icon={ Hash } label={ __( 'Postal code', 'erp' ) } value={ str( record, 'postal_code' ) } />
			</InfoCard>

			{ str( record, 'description' ).trim() !== '' ? (
				<InfoCard icon={ Droplet } tone="bg-emerald-100 text-emerald-700" title={ __( 'Biography', 'erp' ) }>
					<p className="whitespace-pre-line py-3.5 text-sm text-foreground">
						{ str( record, 'description' ) }
					</p>
				</InfoCard>
			) : null }

			<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'basic', 'work', 'personal', 'bottom' ] } />

			{ canEdit ? <EmployeeGeneralSections userId={ userId } /> : null }
		</div>
	);
}
