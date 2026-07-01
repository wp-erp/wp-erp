/**
 * Static select options for the employee terminate dialog.
 *
 * Values mirror the legacy PHP helpers verbatim so the v2 terminate payload
 * maps 1:1 onto `Employee::terminate()`:
 *   - erp_hr_get_terminate_type()           → TERMINATION_TYPE_OPTIONS
 *   - erp_hr_get_terminate_reason()         → TERMINATION_REASON_OPTIONS
 *   - erp_hr_get_terminate_rehire_options() → REHIRE_OPTIONS
 */

import { __ } from '@/shared/i18n';

export interface Option {
	readonly value: string;
	readonly label: string;
}

export const TERMINATION_TYPE_OPTIONS: readonly Option[] = [
	{ value: 'voluntary', label: __( 'Voluntary', 'erp' ) },
	{ value: 'involuntary', label: __( 'Involuntary', 'erp' ) },
];

export const TERMINATION_REASON_OPTIONS: readonly Option[] = [
	{ value: 'attendance', label: __( 'Attendance', 'erp' ) },
	{ value: 'better_employment', label: __( 'Better Employment Conditions', 'erp' ) },
	{ value: 'career_prospect', label: __( 'Career Prospect', 'erp' ) },
	{ value: 'death', label: __( 'Death', 'erp' ) },
	{ value: 'desertion', label: __( 'Desertion', 'erp' ) },
	{ value: 'dismissed', label: __( 'Dismissed', 'erp' ) },
	{ value: 'dissatisfaction', label: __( 'Dissatisfaction with the job', 'erp' ) },
	{ value: 'higher_pay', label: __( 'Higher Pay', 'erp' ) },
	{ value: 'other_employement', label: __( 'Other Employment', 'erp' ) },
	{ value: 'personality_conflicts', label: __( 'Personality Conflicts', 'erp' ) },
	{ value: 'relocation', label: __( 'Relocation', 'erp' ) },
	{ value: 'retirement', label: __( 'Retirement', 'erp' ) },
];

export const REHIRE_OPTIONS: readonly Option[] = [
	{ value: 'yes', label: __( 'Yes', 'erp' ) },
	{ value: 'no', label: __( 'No', 'erp' ) },
	{ value: 'upon_review', label: __( 'Upon Review', 'erp' ) },
];
