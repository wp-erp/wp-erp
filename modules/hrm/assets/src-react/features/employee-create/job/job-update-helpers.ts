/**
 * Shared types, defaults and constants for the Job-tab update dialog
 * (employee-create copy). Pure — no component state.
 */

import { __ } from '@/shared/i18n';

import type { LookupOption } from '../../employees/filters/lookups';
import type { Option } from '../options';

export type JobAction = 'status' | 'type' | 'compensation' | 'job';

export interface FormState {
	date:         string;
	category:     string; // status code
	type:         string; // type code
	comments:     string;
	pay_rate:     string;
	pay_type:     string;
	reason:       string;
	comment:      string;
	department:   string;
	designation:  string;
	location:     string;
	reporting_to: string;
	// Termination fields — shown only when status === 'terminated'.
	termination_type:    string;
	termination_reason:  string;
	eligible_for_rehire: string;
}

export function todayISO(): string {
	const d = new Date();
	if ( Number.isNaN( d.getTime() ) ) {
		return '';
	}
	return d.toISOString().slice( 0, 10 );
}

export function emptyForm(): FormState {
	return {
		date:         todayISO(),
		category:     '',
		type:         '',
		comments:     '',
		pay_rate:     '',
		pay_type:     '',
		reason:       '',
		comment:      '',
		department:   '',
		designation:  '',
		location:     '',
		reporting_to: '',
		termination_type:    '',
		termination_reason:  '',
		eligible_for_rehire: '',
	};
}

export const TITLES: Record< JobAction, string > = {
	status:       __( 'Update Status', 'erp' ),
	type:         __( 'Update Type', 'erp' ),
	compensation: __( 'Update Compensation', 'erp' ),
	job:          __( 'Update Job Information', 'erp' ),
};

export function toOptions( list: readonly LookupOption[] ): Option[] {
	return list.map( ( l ) => ( { value: String( l.id ), label: l.title } ) );
}
