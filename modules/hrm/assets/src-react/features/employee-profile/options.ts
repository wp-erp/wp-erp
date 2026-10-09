/**
 * Static select options for the employee create form.
 *
 * Values mirror the legacy PHP helpers verbatim so the v2 create payload maps
 * 1:1 onto `Employee::create_employee()`:
 *   - erp_hr_get_employee_types()    → TYPE_OPTIONS
 *   - erp_hr_get_employee_statuses() → STATUS_OPTIONS
 *   - erp_hr_get_employee_sources()  → SOURCE_OPTIONS
 *   - erp_hr_get_pay_type()          → PAY_TYPE_OPTIONS
 *   - erp_hr_get_genders()           → GENDER_OPTIONS
 *   - erp_hr_get_marital_statuses()  → MARITAL_OPTIONS
 *   - erp_hr_get_blood_groups()      → BLOOD_GROUP_OPTIONS
 */

import { __ } from '@/shared/i18n';

export interface Option {
	readonly value: string;
	readonly label: string;
}

export const TYPE_OPTIONS: readonly Option[] = [
	{ value: 'permanent', label: __( 'Full Time', 'erp' ) },
	{ value: 'parttime', label: __( 'Part Time', 'erp' ) },
	{ value: 'contract', label: __( 'On Contract', 'erp' ) },
	{ value: 'temporary', label: __( 'Temporary', 'erp' ) },
	{ value: 'trainee', label: __( 'Trainee', 'erp' ) },
];

export const STATUS_OPTIONS: readonly Option[] = [
	{ value: 'active', label: __( 'Active', 'erp' ) },
	{ value: 'inactive', label: __( 'Inactive', 'erp' ) },
	{ value: 'terminated', label: __( 'Terminated', 'erp' ) },
	{ value: 'deceased', label: __( 'Deceased', 'erp' ) },
	{ value: 'resigned', label: __( 'Resigned', 'erp' ) },
];

export const SOURCE_OPTIONS: readonly Option[] = [
	{ value: 'direct', label: __( 'Direct', 'erp' ) },
	{ value: 'referral', label: __( 'Referral', 'erp' ) },
	{ value: 'web', label: __( 'Web', 'erp' ) },
	{ value: 'newspaper', label: __( 'Newspaper', 'erp' ) },
	{ value: 'advertisement', label: __( 'Advertisement', 'erp' ) },
	{ value: 'social', label: __( 'Social Network', 'erp' ) },
	{ value: 'other', label: __( 'Other', 'erp' ) },
];

export const PAY_TYPE_OPTIONS: readonly Option[] = [
	{ value: 'hourly', label: __( 'Hourly', 'erp' ) },
	{ value: 'daily', label: __( 'Daily', 'erp' ) },
	{ value: 'weekly', label: __( 'Weekly', 'erp' ) },
	{ value: 'biweekly', label: __( 'Biweekly', 'erp' ) },
	{ value: 'monthly', label: __( 'Monthly', 'erp' ) },
	{ value: 'contract', label: __( 'Contract', 'erp' ) },
];

// Performance ratings — mirrors erp_performance_rating().
export const RATING_OPTIONS: readonly Option[] = [
	{ value: '1', label: __( 'Very Bad', 'erp' ) },
	{ value: '2', label: __( 'Poor', 'erp' ) },
	{ value: '3', label: __( 'Average', 'erp' ) },
	{ value: '4', label: __( 'Good', 'erp' ) },
	{ value: '5', label: __( 'Excellent', 'erp' ) },
];

// Pay-change reasons — mirrors erp_hr_get_pay_change_reasons().
export const PAY_CHANGE_REASON_OPTIONS: readonly Option[] = [
	{ value: 'promotion', label: __( 'Promotion', 'erp' ) },
	{ value: 'performance', label: __( 'Performance', 'erp' ) },
	{ value: 'increment', label: __( 'Increment', 'erp' ) },
];

export const GENDER_OPTIONS: readonly Option[] = [
	{ value: 'male', label: __( 'Male', 'erp' ) },
	{ value: 'female', label: __( 'Female', 'erp' ) },
	{ value: 'other', label: __( 'Other', 'erp' ) },
];

export const MARITAL_OPTIONS: readonly Option[] = [
	{ value: 'single', label: __( 'Single', 'erp' ) },
	{ value: 'married', label: __( 'Married', 'erp' ) },
	{ value: 'widowed', label: __( 'Widowed', 'erp' ) },
];

export const BLOOD_GROUP_OPTIONS: readonly Option[] = [
	{ value: 'ab+', label: __( 'AB+', 'erp' ) },
	{ value: 'ab-', label: __( 'AB-', 'erp' ) },
	{ value: 'a+', label: __( 'A+', 'erp' ) },
	{ value: 'a-', label: __( 'A-', 'erp' ) },
	{ value: 'b+', label: __( 'B+', 'erp' ) },
	{ value: 'b-', label: __( 'B-', 'erp' ) },
	{ value: 'o+', label: __( 'O+', 'erp' ) },
	{ value: 'o-', label: __( 'O-', 'erp' ) },
];
