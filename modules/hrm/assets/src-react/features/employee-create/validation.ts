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
] );

// Hidden in edit mode (the legacy form wrapped these in `<# if ( ! data.id ) #>`).
const CREATE_ONLY_FIELDS = new Set( [
	'type',
	'status',
	'location',
	'reporting_to',
	'pay_rate',
	'pay_type',
] );

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
// Mirrors the server's erp_is_valid_employee_id(): start alphanumeric, then
// only letters, digits and hyphens (no spaces or symbols).
const EMPLOYEE_ID_RE = /^[A-Za-z0-9][-A-Za-z0-9]*$/;
// Client mirrors of the server-side validators in `Employee::create_employee()`
// (the server stays the source of truth; these just give inline feedback).
const NAME_RE = /^[\p{L}\s.'-]+$/u; // erp_is_valid_name: letters/space/.'-
const CONTACT_RE = /^[0-9+\-()\s]{6,20}$/; // erp_is_valid_contact_no
const ZIP_RE = /^[A-Za-z0-9\s-]{2,12}$/; // erp_is_valid_zip_code
const CURRENCY_RE = /^\d+(\.\d{1,2})?$/; // erp_is_valid_currency_amount (non-negative)

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
 * Fields that must be non-empty to submit (Type/Status only required on create).
 * @param mode
 */
export function requiredFields( mode: FormMode ): readonly string[] {
	const base = [
		'first_name',
		'last_name',
		'email',
		'hiring_date',
		'department',
		'designation',
	];
	// Employee Type + Status are only shown (and required) when creating.
	return mode === 'create' ? [ ...base, 'type', 'status' ] : base;
}

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

	// Name fields — letters, spaces and . ' - only (mirrors erp_is_valid_name).
	for ( const key of NAME_FIELDS ) {
		const v = ( form[ key ] ?? '' ).trim();
		if ( v && ! NAME_RE.test( v ) ) {
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
			next[ key ] = __( 'Enter a valid phone number.', 'erp' );
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
		next.postal_code = __( 'Enter a valid postal code.', 'erp' );
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
 * attaches the notification flags. Create-only fields are skipped in edit mode.
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
		// Never submit the create-only fields from the edit form.
		if ( isEdit && CREATE_ONLY_FIELDS.has( key ) ) {
			continue;
		}
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
