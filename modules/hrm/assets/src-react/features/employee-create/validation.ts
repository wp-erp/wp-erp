/**
 * Pure form logic for the shared employee form — field rules, client-side
 * validation and payload assembly. Kept free of React state so it is unit-
 * testable and so `EmployeeForm.tsx` stays focused on UI + wiring.
 *
 * The server stays the source of truth; these client mirrors of
 * `Employee::create_employee()`'s validators only give inline feedback.
 */

import { __ } from '@/shared/i18n';
import type { EmployeeCreateInput } from '@/stores/employees';

import type { ExtraField } from './ExtraFields';
import type { UserCheckResult } from './useUserCheck';

export type FormState = Record< string, string >;
export type FormMode = 'create' | 'edit';

const NUMERIC_FIELDS = new Set( [
	'department',
	'designation',
	'location',
	'reporting_to',
	'photo_id',
] );

// Create and edit now render the same fields in the same order, so nothing is
// stripped from the edit payload any more. `PUT /erp/v2/employees/{id}` still
// drops the manager-only keys server-side when a self-editor submits.

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
// Mirrors the server's erp_is_valid_employee_id(): start alphanumeric, then
// only letters, digits and hyphens (no spaces or symbols).
const EMPLOYEE_ID_RE = /^[A-Za-z0-9][-A-Za-z0-9]*$/;
// Client mirrors of the server-side validators in `Employee::create_employee()`
// — character-for-character copies of the PHP patterns so the form never accepts
// a value the server rejects, nor rejects one it accepts. The server stays the
// source of truth; these only give inline feedback.
// erp_is_valid_name(): invalid when ANY of these appear (digits included).
const NAME_DISALLOWED_RE = /[_@!%#&:;"=<>/*+?$^{}[\]0-9]/;
// erp_contains_disallowed_chars(): the narrower set applied to City.
const DISALLOWED_CHARS_RE = /[%;"=<>/*+?$^{}[\]]/;
// erp_is_valid_contact_no(): optional +, then 4 digit groups (1-3 then 3x1-5)
// separated by an optional space, dot or hyphen. Parentheses are NOT allowed.
const CONTACT_RE = /^\+?[0-9]{1,3}([\s.-]?[0-9]{1,5}){3}$/;
// erp_is_valid_zip_code(): uppercase letters/digits only (the PHP pattern has no
// /i flag), 4-13 chars, spaces and hyphens allowed after the first character.
const ZIP_RE = /^[A-Z0-9][ \-A-Z0-9]{3,12}$/;
// erp_is_valid_currency_amount(): non-negative, up to 4 decimals.
const CURRENCY_RE = /^[0-9]+(\.[0-9]{1,4})?$/;
// erp_is_valid_url(): scheme optional, must carry a dotted host.
const URL_RE = /^(?:(?:https?|ftp):\/\/)?(?:[a-z0-9-]+\.)*(?:[a-z0-9-]+\.)[a-z]+/i;

// Name fields validated when non-empty (first/last also required below).
const NAME_FIELDS = [
	'first_name',
	'middle_name',
	'last_name',
	'father_name',
	'mother_name',
	'spouse_name',
] as const;
const CONTACT_FIELDS = [ 'mobile', 'phone', 'work_phone' ] as const;
const DATE_FIELDS = [ 'hiring_date', 'end_date', 'date_of_birth' ] as const;

/** Valid email pattern (exported so the form can debounce the create-flow check). */
export { EMAIL_RE };

function isValidDate( value: string ): boolean {
	// HTML date inputs emit YYYY-MM-DD; accept that and verify it's a real date.
	if ( ! /^\d{4}-\d{2}-\d{2}$/.test( value ) ) {
		return false;
	}
	const ts = Date.parse( value );
	return ! Number.isNaN( ts );
}

/**
 * Fields that must be non-empty to submit — the same set in both modes, since
 * create and edit render the same fields.
 *
 * @param _mode Kept for call-site symmetry (both modes share one rule set).
 */
export function requiredFields( _mode: FormMode ): readonly string[] {
	return [
		'first_name',
		'last_name',
		'email',
		'type',
		'status',
		'hiring_date',
		'department',
		'designation',
	];
}

/**
 * Field → human label, so the validation summary at the top of the form can name
 * the offending field instead of listing bare messages.
 */
export const FIELD_LABELS: Record< string, string > = {
	first_name:    __( 'First Name', 'erp' ),
	middle_name:   __( 'Middle Name', 'erp' ),
	last_name:     __( 'Last Name', 'erp' ),
	employee_id:   __( 'Employee ID', 'erp' ),
	email:         __( 'Email', 'erp' ),
	type:          __( 'Employee Type', 'erp' ),
	status:        __( 'Employee Status', 'erp' ),
	end_date:      __( 'Employee End Date', 'erp' ),
	hiring_date:   __( 'Date of Hire', 'erp' ),
	department:    __( 'Department', 'erp' ),
	designation:   __( 'Job Title', 'erp' ),
	location:      __( 'Location', 'erp' ),
	reporting_to:  __( 'Reporting To', 'erp' ),
	hiring_source: __( 'Source of Hire', 'erp' ),
	pay_rate:      __( 'Pay Rate', 'erp' ),
	pay_type:      __( 'Pay Type', 'erp' ),
	work_phone:    __( 'Work Phone', 'erp' ),
	spouse_name:   __( "Spouse's name", 'erp' ),
	father_name:   __( "Father's name", 'erp' ),
	mother_name:   __( "Mother's name", 'erp' ),
	mobile:        __( 'Mobile', 'erp' ),
	phone:         __( 'Phone', 'erp' ),
	other_email:   __( 'Other Email', 'erp' ),
	date_of_birth: __( 'Date of Birth', 'erp' ),
	user_url:      __( 'Website', 'erp' ),
	city:          __( 'City', 'erp' ),
	postal_code:   __( 'Post Code / Zip Code', 'erp' ),
};

/** Extra context the validator needs beyond the raw form values. */
interface ValidateContext {
	readonly userCheck: UserCheckResult | null;
}

/**
 * Client-side validation — returns a field→message map (empty when valid).
 * Mirrors the server validators; the component owns the resulting error state.
 *
 * @param form The current form values.
 * @param mode 'create' | 'edit'.
 * @param ctx  Extra context (the duplicate-email user check).
 */
export function validateEmployeeForm(
	form: FormState,
	mode: FormMode,
	ctx: ValidateContext
): Record< string, string > {
	const isEdit = mode === 'edit';
	const next: Record< string, string > = {};

	for ( const key of requiredFields( mode ) ) {
		if ( ! ( form[ key ] ?? '' ).trim() ) {
			next[ key ] = __( 'This field is required.', 'erp' );
		}
	}
	if ( form.email && ! EMAIL_RE.test( form.email ) ) {
		next.email = __( 'Enter a valid email address.', 'erp' );
	}
	// Mirror the legacy guard: block creating a duplicate employee.
	if ( ! isEdit && ctx.userCheck && ctx.userCheck.type === 'employee' ) {
		next.email = __(
			'An employee already exists with this email address.',
			'erp'
		);
	}
	if (
		form.employee_id &&
		form.employee_id.trim() &&
		! EMPLOYEE_ID_RE.test( form.employee_id.trim() )
	) {
		next.employee_id = __(
			'Employee ID can contain only letters, numbers and hyphens.',
			'erp'
		);
	}

	// Name fields — mirrors erp_is_valid_name(): no digits, no _@!%#&:;"=<>/*+?$^{}[].
	for ( const key of NAME_FIELDS ) {
		const v = ( form[ key ] ?? '' ).trim();
		if ( v && NAME_DISALLOWED_RE.test( v ) ) {
			next[ key ] = __(
				'Use letters only (no digits or symbols).',
				'erp'
			);
		}
	}

	// Dates must be real calendar dates.
	for ( const key of DATE_FIELDS ) {
		const v = ( form[ key ] ?? '' ).trim();
		if ( v && ! isValidDate( v ) ) {
			next[ key ] = __( 'Enter a valid date.', 'erp' );
		}
	}

	// Phone numbers.
	for ( const key of CONTACT_FIELDS ) {
		const v = ( form[ key ] ?? '' ).trim();
		if ( v && ! CONTACT_RE.test( v ) ) {
			next[ key ] = __(
				'Enter a valid phone number, e.g. +880 1711 123 456.',
				'erp'
			);
		}
	}

	const otherEmail = ( form.other_email ?? '' ).trim();
	if ( otherEmail && ! EMAIL_RE.test( otherEmail ) ) {
		next.other_email = __( 'Enter a valid email address.', 'erp' );
	}
	const payRate = ( form.pay_rate ?? '' ).trim();
	if ( payRate && ! CURRENCY_RE.test( payRate ) ) {
		next.pay_rate = __( 'Enter a valid amount.', 'erp' );
	}
	const postalCode = ( form.postal_code ?? '' ).trim();
	if ( postalCode && ! ZIP_RE.test( postalCode ) ) {
		next.postal_code = __(
			'Use 4-13 uppercase letters, numbers, spaces or hyphens.',
			'erp'
		);
	}
	// Website — mirrors erp_is_valid_url() (the server also trims the URL down to
	// scheme + host before saving).
	const userUrl = ( form.user_url ?? '' ).trim();
	if ( userUrl && ! URL_RE.test( userUrl ) ) {
		next.user_url = __( 'Enter a valid website URL.', 'erp' );
	}
	// City — mirrors erp_contains_disallowed_chars().
	const city = ( form.city ?? '' ).trim();
	if ( city && DISALLOWED_CHARS_RE.test( city ) ) {
		next.city = __( 'Remove the special characters from the city name.', 'erp' );
	}

	return next;
}

/** Extra context the payload builder needs beyond the raw form values. */
interface PayloadContext {
	readonly extraFields: readonly ExtraField[];
	readonly notify: boolean;
	readonly sendLogin: boolean;
}

/**
 * Assemble the REST payload from the form values: trims, drops empties, coerces
 * numeric fields, buckets pro custom fields under `additional`, and (on create)
 * attaches the notification flags. Both modes submit the same field set.
 *
 * @param form The current form values.
 * @param mode 'create' | 'edit'.
 * @param ctx  Extra context (custom fields + notification flags).
 */
export function buildEmployeePayload(
	form: FormState,
	mode: FormMode,
	ctx: PayloadContext
): EmployeeCreateInput {
	const isEdit = mode === 'edit';
	const payload: EmployeeCreateInput = {};
	const extraKeys = new Set( ctx.extraFields.map( ( f ) => f.key ) );
	const additional: Record< string, string > = {};

	for ( const [ key, raw ] of Object.entries( form ) ) {
		// Custom (pro) fields go in the `additional` bucket — always sent (even
		// when blank) so clearing a value persists on edit.
		if ( extraKeys.has( key ) ) {
			additional[ key ] = raw;
			continue;
		}
		const value = raw.trim();
		if ( value === '' ) {
			continue;
		}
		payload[ key ] = NUMERIC_FIELDS.has( key )
			? parseInt( value, 10 )
			: value;
	}
	if ( Object.keys( additional ).length > 0 ) {
		payload.additional = additional;
	}
	if ( ! isEdit && ctx.notify ) {
		payload.user_notification = true;
		if ( ctx.sendLogin ) {
			payload.login_info = true;
		}
	}
	return payload;
}
