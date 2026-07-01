/**
 * Pure form state + helpers for the create/edit leave-policy dialog: the local
 * form shape, the validation-error shape, the empty default, the `-1` "All"
 * scope sentinel, and the option helper that prepends "All" to a scope select.
 */

import { __ } from '@/shared/i18n';

import type { Option } from '../employee-create/options';

export const ALL = '-1';

export interface FormState {
	leave_id:            string;
	days:                string;
	color:               string;
	description:         string;
	f_year:              string;
	applicable_from:     string;
	employee_type:       string;
	department_id:       string;
	designation_id:      string;
	location_id:         string;
	gender:              string;
	marital:             string;
	apply_for_new_users: boolean;
	apply_for_existing:  boolean;
}

export interface PolicyErrors {
	leave_id?: string | undefined;
	days?:     string | undefined;
	f_year?:   string | undefined;
}

export const EMPTY: FormState = {
	leave_id:            '',
	days:                '',
	color:               '#3b82f6',
	description:         '',
	f_year:              '',
	applicable_from:     '0',
	employee_type:       ALL,
	department_id:       ALL,
	designation_id:      ALL,
	location_id:         ALL,
	gender:              ALL,
	marital:             ALL,
	apply_for_new_users: false,
	apply_for_existing:  false,
};

/** Prepend the "All" sentinel option to a scope select. */
export function withAll( opts: readonly Option[] ): Option[] {
	return [ { value: ALL, label: __( 'All', 'erp' ) }, ...opts ];
}
