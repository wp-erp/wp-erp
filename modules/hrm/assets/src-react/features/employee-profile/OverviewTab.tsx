/**
 * Overview tab body for the Employee Profile layout: stats strip, pro extra
 * fields, and the Employment / Contact / Personal / Address / Biography detail
 * cards, mirroring the legacy single-page "General" tab.
 */

import {
	Activity,
	Briefcase,
	Building2,
	Calendar,
	CalendarOff,
	Compass,
	DollarSign,
	Droplets,
	Flag,
	Globe,
	Hash,
	Heart,
	IdCard,
	Mail,
	Map,
	MapPin,
	Phone,
	Smartphone,
	Sparkles,
	Tag,
	User,
	UserCog,
	Users,
	Wallet,
} from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { EmployeeExtraFieldsView } from '../employee-create/EmployeeExtraFieldsView';
import { EmployeeGeneralSections } from '../employee-create/general/EmployeeGeneralSections';
import { OverviewStats } from '../employee-create/general/OverviewStats';
import {
	BLOOD_GROUP_OPTIONS,
	GENDER_OPTIONS,
	MARITAL_OPTIONS,
	PAY_TYPE_OPTIONS,
	SOURCE_OPTIONS,
	STATUS_OPTIONS,
	TYPE_OPTIONS,
} from '../employee-create/options';
import { DetailCard, Item } from './DetailCard';
import { labelOf, str, type Record_ } from './profile-format';

interface OverviewTabProps {
	readonly userId:  number;
	readonly record:  Record_;
	readonly canEdit: boolean;
}

export function OverviewTab( { userId, record, canEdit }: OverviewTabProps ): JSX.Element {
	const status = str( record, 'status' );

	return (
		<>
			<OverviewStats
				userId={ userId }
				hiringDate={ str( record, 'hiring_date' ) }
				dateOfBirth={ str( record, 'date_of_birth' ) }
			/>
			<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'top' ] } />

				<DetailCard title={ __( 'Employment', 'erp' ) }>
				<Item icon={ IdCard } label={ __( 'Employee ID', 'erp' ) } value={ str( record, 'employee_id' ) } />
				<Item icon={ Briefcase } label={ __( 'Employee Type', 'erp' ) } value={ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) } />
				<Item icon={ Activity } label={ __( 'Employee Status', 'erp' ) } value={ labelOf( STATUS_OPTIONS, status ) } />
				<Item icon={ Calendar } label={ __( 'Date of Hire', 'erp' ) } value={ str( record, 'hiring_date' ) } />
				<Item icon={ CalendarOff } label={ __( 'Employee End Date', 'erp' ) } value={ str( record, 'end_date' ) } />
				<Item icon={ Building2 } label={ __( 'Department', 'erp' ) } value={ str( record, 'department_name' ) } />
				<Item icon={ Tag } label={ __( 'Job Title', 'erp' ) } value={ str( record, 'designation_name' ) } />
				<Item icon={ MapPin } label={ __( 'Location', 'erp' ) } value={ str( record, 'location_name' ) } />
				<Item icon={ UserCog } label={ __( 'Reporting To', 'erp' ) } value={ str( record, 'reporting_to_name' ) } />
				<Item icon={ Compass } label={ __( 'Source of Hire', 'erp' ) } value={ labelOf( SOURCE_OPTIONS, str( record, 'hiring_source' ) ) } />
				<Item icon={ DollarSign } label={ __( 'Pay Rate', 'erp' ) } value={ str( record, 'pay_rate' ) } />
				<Item icon={ Wallet } label={ __( 'Pay Type', 'erp' ) } value={ labelOf( PAY_TYPE_OPTIONS, str( record, 'pay_type' ) ) } />
			</DetailCard>

			<DetailCard title={ __( 'Contact', 'erp' ) }>
				<Item icon={ Mail } label={ __( 'Email', 'erp' ) } value={ str( record, 'email' ) } />
				<Item icon={ Mail } label={ __( 'Other Email', 'erp' ) } value={ str( record, 'other_email' ) } />
				<Item icon={ Smartphone } label={ __( 'Mobile', 'erp' ) } value={ str( record, 'mobile' ) } />
				<Item icon={ Phone } label={ __( 'Phone', 'erp' ) } value={ str( record, 'phone' ) } />
				<Item icon={ Phone } label={ __( 'Work Phone', 'erp' ) } value={ str( record, 'work_phone' ) } />
				<Item icon={ Globe } label={ __( 'Website', 'erp' ) } value={ str( record, 'user_url' ) } />
			</DetailCard>

			<DetailCard title={ __( 'Personal Details', 'erp' ) }>
				<Item icon={ Calendar } label={ __( 'Date of Birth', 'erp' ) } value={ str( record, 'date_of_birth' ) } />
				<Item icon={ User } label={ __( 'Gender', 'erp' ) } value={ labelOf( GENDER_OPTIONS, str( record, 'gender' ) ) } />
				<Item icon={ Heart } label={ __( 'Marital Status', 'erp' ) } value={ labelOf( MARITAL_OPTIONS, str( record, 'marital_status' ) ) } />
				<Item icon={ Droplets } label={ __( 'Blood Group', 'erp' ) } value={ labelOf( BLOOD_GROUP_OPTIONS, str( record, 'blood_group' ) ) } />
				<Item icon={ Flag } label={ __( 'Nationality', 'erp' ) } value={ str( record, 'nationality' ) } />
				<Item icon={ IdCard } label={ __( 'Driving License', 'erp' ) } value={ str( record, 'driving_license' ) } />
				<Item icon={ Sparkles } label={ __( 'Hobbies', 'erp' ) } value={ str( record, 'hobbies' ) } />
				<Item icon={ User } label={ __( "Father's name", 'erp' ) } value={ str( record, 'father_name' ) } />
				<Item icon={ User } label={ __( "Mother's name", 'erp' ) } value={ str( record, 'mother_name' ) } />
				<Item icon={ Users } label={ __( "Spouse's name", 'erp' ) } value={ str( record, 'spouse_name' ) } />
			</DetailCard>

			<DetailCard title={ __( 'Address', 'erp' ) }>
				<Item icon={ MapPin } label={ __( 'Address 1', 'erp' ) } value={ str( record, 'street_1' ) } />
				<Item icon={ MapPin } label={ __( 'Address 2', 'erp' ) } value={ str( record, 'street_2' ) } />
				<Item icon={ Building2 } label={ __( 'City', 'erp' ) } value={ str( record, 'city' ) } />
				<Item icon={ Map } label={ __( 'Province / State', 'erp' ) } value={ str( record, 'state' ) } />
				<Item icon={ Globe } label={ __( 'Country', 'erp' ) } value={ str( record, 'country' ) } />
				<Item icon={ Hash } label={ __( 'Post Code / Zip Code', 'erp' ) } value={ str( record, 'postal_code' ) } />
			</DetailCard>

			{ str( record, 'description' ).trim() !== '' ? (
				<section className="rounded-[10px] bg-card p-6 shadow-sm">
					<h2 className="mt-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Biography', 'erp' ) }</h2>
					<div className="mb-4 mt-4 h-px w-full bg-border" />
					<p className="whitespace-pre-line text-sm text-foreground">
						{ str( record, 'description' ) }
					</p>
				</section>
			) : null }

			<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'basic', 'work', 'personal', 'bottom' ] } />

			{ canEdit ? <EmployeeGeneralSections userId={ userId } /> : null }
		</>
	);
}
