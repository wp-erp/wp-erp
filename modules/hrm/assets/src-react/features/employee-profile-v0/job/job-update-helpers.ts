/**
 * Shared types, defaults and helpers for the Job-tab update dialog: the action
 * union, the flat form-state shape, today's ISO date, an empty form factory,
 * the per-action dialog titles, and the lookup→option mapper. Pure — no state.
 */

import { __ } from '@/shared/i18n';

import type { LookupOption } from '../../employees/filters/lookups';
import type { Option } from '../options';
import { PAY_CHANGE_REASON_OPTIONS, PAY_TYPE_OPTIONS, STATUS_OPTIONS, TYPE_OPTIONS } from '../options';
import type {
	CompensationHistory,
	EmploymentHistory,
	JobInfoHistory,
	StatusHistory,
} from './useEmployeeJobHistories';

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

// Titles for the edit-in-place (PUT) flow — mirrors the legacy history-edit
// modal wording ("… History").
export const EDIT_TITLES: Record< JobAction, string > = {
	status:       __( 'Employee Status History', 'erp' ),
	type:         __( 'Employment Type History', 'erp' ),
	compensation: __( 'Compensation History', 'erp' ),
	job:          __( 'Job Info History', 'erp' ),
};

export function toOptions( list: readonly LookupOption[] ): Option[] {
	return list.map( ( l ) => ( { value: String( l.id ), label: l.title } ) );
}

/** ISO / MySQL datetime → `yyyy-mm-dd` for the date input; '' when unparseable. */
export function toDateInput( iso: string | null ): string {
	if ( ! iso ) {
		return '';
	}
	const match = /^(\d{4}-\d{2}-\d{2})/.exec( iso );
	return match?.[ 1 ] ?? '';
}

/**
 * Reverse-map a display label back to its option code — the GET job-histories
 * endpoint resolves codes to labels server-side, so edit-mode prefill has to
 * translate the label back to the code the select expects. '' when not found.
 */
export function optionValue( options: readonly Option[], label: string ): string {
	if ( ! label ) {
		return '';
	}
	const found = options.find( ( o ) => o.label === label );
	return found ? found.value : '';
}

/**
 * For the async org lookups (department / designation / location): keep the
 * value if it's already a valid option code, otherwise resolve a label to its
 * code. Used once the lookups load so edit-mode job-info prefill can seed ids.
 */
export function resolveLabelToValue( options: readonly Option[], current: string ): string {
	if ( ! current ) {
		return current;
	}
	if ( options.some( ( o ) => o.value === current ) ) {
		return current;
	}
	const byLabel = options.find( ( o ) => o.label === current );
	return byLabel ? byLabel.value : current;
}

// ── Edit-mode prefill builders ──────────────────────────────────────────────
// Each takes a resolved history row (labels, not codes) and returns the form
// fields to seed. Static enums are reverse-mapped here; the job-info org
// lookups carry their labels through and are resolved in the dialog once loaded.

export function statusInitial( row: StatusHistory ): Partial< FormState > {
	return {
		date:     toDateInput( row.date ),
		category: optionValue( STATUS_OPTIONS, row.status ),
		comments: row.comment,
	};
}

export function typeInitial( row: EmploymentHistory ): Partial< FormState > {
	return {
		date:     toDateInput( row.date ),
		type:     optionValue( TYPE_OPTIONS, row.type ),
		comments: row.comment,
	};
}

export function compensationInitial( row: CompensationHistory ): Partial< FormState > {
	return {
		date:     toDateInput( row.date ),
		pay_rate: row.pay_rate,
		pay_type: optionValue( PAY_TYPE_OPTIONS, row.pay_type ),
		reason:   optionValue( PAY_CHANGE_REASON_OPTIONS, row.reason ),
		comment:  row.comment,
	};
}

export function jobInitial( row: JobInfoHistory ): Partial< FormState > {
	// department / designation / location carry their labels; the dialog resolves
	// them to ids once the org lookups load. reporting_to now prefills from the
	// raw id the read model exposes (`reporting_to_id`) so the manager picker is
	// preselected instead of forcing reselection.
	return {
		date:         toDateInput( row.date ),
		department:   row.department,
		designation:  row.designation,
		location:     row.location,
		reporting_to: row.reporting_to_id ? String( row.reporting_to_id ) : '',
	};
}
