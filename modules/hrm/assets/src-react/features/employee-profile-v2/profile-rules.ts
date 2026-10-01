/**
 * Value rules for the profile dialogs (General sections, Job updates and
 * Performance). Pure: each validator returns the first problem as a sentence
 * that names the field, or null when the values are fine. The v2 controllers
 * enforce the same rules with the same wording, so the server never trusts
 * this check; it only saves a round trip.
 *
 * Kept identical in every profile copy (employee-profile, -v0, -v2, -v3 and
 * employee-create). Change them together.
 */

import { __ } from '@/shared/i18n';

const ISO_DATE_RE = /^\d{4}-\d{2}-\d{2}$/;
// erp_is_valid_currency_amount(): non-negative, up to 4 decimals.
const AMOUNT_RE = /^[0-9]+(\.[0-9]{1,4})?$/;
const NUMBER_RE = /^[0-9]+(\.[0-9]+)?$/;

/**
 * Whether a value is empty once trimmed.
 *
 * @param value The raw field value.
 */
function blank( value: string | undefined ): boolean {
	return ! ( value ?? '' ).trim();
}

/**
 * A real calendar date in the `yyyy-mm-dd` shape a date input emits.
 *
 * @param value The raw date string.
 */
function isDate( value: string ): boolean {
	if ( ! ISO_DATE_RE.test( value ) ) {
		return false;
	}
	const [ y, m, d ] = value.split( '-' ).map( Number );
	const date = new Date( Date.UTC( y ?? 0, ( m ?? 1 ) - 1, d ?? 1 ) );
	return date.getUTCFullYear() === y && date.getUTCMonth() === ( m ?? 1 ) - 1 && date.getUTCDate() === d;
}

/** Today as `yyyy-mm-dd` in the browser's calendar. */
function todayYmd(): string {
	const now = new Date();
	const pad = ( n: number ): string => String( n ).padStart( 2, '0' );
	return `${ now.getFullYear() }-${ pad( now.getMonth() + 1 ) }-${ pad( now.getDate() ) }`;
}

export type GeneralRuleSection = 'experiences' | 'educations' | 'dependents';

/**
 * Work experience, education and dependent rules. Mirrors
 * `EmployeeProfileController::validate_fields()`.
 *
 * @param section Which General section the dialog is editing.
 * @param form    The dialog's flat string values.
 */
export function validateGeneralSection(
	section: GeneralRuleSection,
	form: Readonly< Record< string, string > >
): string | null {
	if ( section === 'experiences' ) {
		const from = ( form.from ?? '' ).trim();
		const to   = ( form.to ?? '' ).trim();
		if ( blank( form.company_name ) ) {
			return __( 'Company name is required.', 'erp' );
		}
		if ( blank( form.job_title ) ) {
			return __( 'Job title is required.', 'erp' );
		}
		if ( ! from ) {
			return __( 'From date is required.', 'erp' );
		}
		if ( ! isDate( from ) ) {
			return __( 'Enter a valid From date.', 'erp' );
		}
		if ( ! to ) {
			return __( 'To date is required.', 'erp' );
		}
		if ( ! isDate( to ) ) {
			return __( 'Enter a valid To date.', 'erp' );
		}
		if ( to < from ) {
			return __( 'The To date cannot be earlier than the From date.', 'erp' );
		}
		return null;
	}

	if ( section === 'educations' ) {
		const type   = ( form.result_type ?? '' ).trim();
		const result = ( form.gpa ?? '' ).trim();
		const scale  = ( form.scale ?? '' ).trim();
		const year   = ( form.finished ?? '' ).trim();
		const expiry = ( form.expiration_date ?? '' ).trim();
		if ( blank( form.school ) ) {
			return __( 'School name is required.', 'erp' );
		}
		if ( blank( form.degree ) ) {
			return __( 'Degree is required.', 'erp' );
		}
		if ( blank( form.field ) ) {
			return __( 'Field of study is required.', 'erp' );
		}
		if ( type !== 'grade' && type !== 'percentage' ) {
			return __( 'Select a valid result type.', 'erp' );
		}
		if ( ! result ) {
			return __( 'Result is required.', 'erp' );
		}
		if ( ! NUMBER_RE.test( result ) ) {
			return __( 'Result must be a number of 0 or more.', 'erp' );
		}
		if ( type === 'percentage' && Number( result ) > 100 ) {
			return __( 'Percentage cannot be more than 100.', 'erp' );
		}
		if ( type === 'grade' ) {
			if ( ! scale ) {
				return __( 'Scale is required.', 'erp' );
			}
			if ( ! NUMBER_RE.test( scale ) || Number( scale ) <= 0 ) {
				return __( 'Scale must be a number greater than 0.', 'erp' );
			}
			if ( Number( result ) > Number( scale ) ) {
				return __( 'Grade cannot be higher than the scale.', 'erp' );
			}
		}
		if ( ! year ) {
			return __( 'Completion year is required.', 'erp' );
		}
		if ( ! /^\d{4}$/.test( year ) || Number( year ) < 1970 || Number( year ) > 2099 ) {
			return __( 'Completion year must be between 1970 and 2099.', 'erp' );
		}
		if ( expiry && ! isDate( expiry ) ) {
			return __( 'Enter a valid expiration date.', 'erp' );
		}
		return null;
	}

	if ( blank( form.name ) ) {
		return __( 'Name is required.', 'erp' );
	}
	if ( blank( form.relation ) ) {
		return __( 'Relation is required.', 'erp' );
	}
	const dob = ( form.dob ?? '' ).trim();
	if ( dob && ! isDate( dob ) ) {
		return __( 'Enter a valid date of birth.', 'erp' );
	}
	if ( dob && dob > todayYmd() ) {
		return __( 'Date of birth cannot be in the future.', 'erp' );
	}
	return null;
}

/** The Job-tab dialog fields the rules read. */
export interface JobRuleInput {
	readonly date:                string;
	readonly category:            string;
	readonly type:                string;
	readonly pay_rate:            string;
	readonly pay_type:            string;
	readonly department:          string;
	readonly designation:         string;
	readonly reporting_to:        string;
	readonly termination_type:    string;
	readonly termination_reason:  string;
	readonly eligible_for_rehire: string;
}

/**
 * Job-tab update rules. Mirrors the legacy model checks the v2
 * job-histories route delegates to (`update_compensation()`,
 * `update_job_info()`, `update_employment_status()`) and the terminate route.
 *
 * @param action    Which Job-tab update the dialog is making.
 * @param form      The dialog's values.
 * @param terminate Whether this status update is a termination.
 */
export function validateJobUpdate(
	action: 'status' | 'type' | 'compensation' | 'job',
	form: JobRuleInput,
	terminate: boolean
): string | null {
	const date = ( form.date ?? '' ).trim();
	if ( ! date ) {
		return __( 'Date is required.', 'erp' );
	}
	if ( ! isDate( date ) ) {
		return __( 'Enter a valid date.', 'erp' );
	}

	if ( action === 'status' ) {
		if ( blank( form.category ) ) {
			return __( 'Employee status is required.', 'erp' );
		}
		if ( terminate ) {
			if ( blank( form.termination_type ) ) {
				return __( 'Termination type is required.', 'erp' );
			}
			if ( blank( form.termination_reason ) ) {
				return __( 'Termination reason is required.', 'erp' );
			}
			if ( blank( form.eligible_for_rehire ) ) {
				return __( 'Eligible for rehire is required.', 'erp' );
			}
		}
		return null;
	}

	if ( action === 'type' ) {
		return blank( form.type ) ? __( 'Employee type is required.', 'erp' ) : null;
	}

	if ( action === 'compensation' ) {
		const rate = ( form.pay_rate ?? '' ).trim();
		if ( ! rate ) {
			return __( 'Pay rate is required.', 'erp' );
		}
		if ( ! AMOUNT_RE.test( rate ) || Number( rate ) <= 0 ) {
			return __( 'Pay rate must be a number greater than 0, with up to 4 decimal places.', 'erp' );
		}
		return blank( form.pay_type ) ? __( 'Pay type is required.', 'erp' ) : null;
	}

	// Job information: the model rejects an empty department, job title or
	// reporting-to, so ask for them here instead of after the round trip.
	if ( blank( form.department ) || form.department === '0' ) {
		return __( 'Department is required.', 'erp' );
	}
	if ( blank( form.designation ) || form.designation === '0' ) {
		return __( 'Job title is required.', 'erp' );
	}
	if ( blank( form.reporting_to ) || form.reporting_to === '0' ) {
		return __( 'Reporting to is required.', 'erp' );
	}
	return null;
}

/** The Performance dialog fields the rules read. */
export interface PerformanceRuleInput {
	readonly performance_date: string;
	readonly reporting_to:     string;
	readonly reviewer:         string;
	readonly completion_date:  string;
	readonly supervisor:       string;
}

/**
 * Performance review / comment / goal rules. Mirrors
 * `EmployeePerformanceController::validate_fields()`.
 *
 * @param type Which performance record the dialog is adding.
 * @param form The dialog's values.
 */
export function validatePerformance(
	type: 'reviews' | 'comments' | 'goals',
	form: PerformanceRuleInput
): string | null {
	const date = ( form.performance_date ?? '' ).trim();
	if ( ! date ) {
		return __( 'Date is required.', 'erp' );
	}
	if ( ! isDate( date ) ) {
		return __( 'Enter a valid date.', 'erp' );
	}
	if ( type === 'reviews' ) {
		return blank( form.reporting_to ) ? __( 'Reporting to is required.', 'erp' ) : null;
	}
	if ( type === 'comments' ) {
		return blank( form.reviewer ) ? __( 'Reviewer is required.', 'erp' ) : null;
	}
	const completion = ( form.completion_date ?? '' ).trim();
	if ( ! completion ) {
		return __( 'Completion date is required.', 'erp' );
	}
	if ( ! isDate( completion ) ) {
		return __( 'Enter a valid completion date.', 'erp' );
	}
	if ( completion < date ) {
		return __( 'Completion date cannot be earlier than the set date.', 'erp' );
	}
	return blank( form.supervisor ) ? __( 'Supervisor is required.', 'erp' ) : null;
}
