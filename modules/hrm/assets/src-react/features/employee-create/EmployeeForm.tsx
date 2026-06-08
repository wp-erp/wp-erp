/**
 * Shared employee form body — used by both the create and edit pages.
 *
 * Field parity with the legacy Vue template (`views/js-templates/new-employee.php`).
 * In `edit` mode the create-only fields are hidden exactly as the old form did
 * (it wrapped them in `<# if ( ! data.id ) { #>`): Employee Type, Employee
 * Status, Location, Reporting To, Pay Rate, Pay Type — those are edited from the
 * single-employee Job/Compensation tabs instead. The Notification section is
 * also create-only.
 *
 * Validation mirrors the server: required First/Last/Email (+ Type/Status/Hire
 * date/Department/Designation on create), email format, and the
 * erp_is_valid_employee_id() pattern for Employee ID.
 */

import { Alert, AlertDescription, AlertTitle, Button, Checkbox, toast } from '@wedevs/plugin-ui';
import { applyFilters } from '@wordpress/hooks';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';
import { useNavigate } from 'react-router-dom';

import { DependencyHint } from '@/shared/components/DependencyHint';
import { HOOKS } from '@/shared/filters';
import { __ } from '@/shared/i18n';
import type { EmployeeCreateInput } from '@/stores/employees';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

import { loadLookup } from '../employees/filters/lookups';
import type { LookupOption } from '../employees/filters/lookups';
import { ExtraFields } from './ExtraFields';
import type { ExtraField } from './ExtraFields';
import { FormSection, SelectField, SmartSelectField, TextField, TextareaField } from './fields';
import { checkUser, convertUser } from './useUserCheck';
import type { UserCheckResult } from './useUserCheck';
import type { Option } from './options';
import {
	BLOOD_GROUP_OPTIONS,
	GENDER_OPTIONS,
	MARITAL_OPTIONS,
	PAY_TYPE_OPTIONS,
	SOURCE_OPTIONS,
	STATUS_OPTIONS,
	TYPE_OPTIONS,
} from './options';

export type FormState = Record< string, string >;
export type FormMode = 'create' | 'edit';

const NUMERIC_FIELDS = new Set( [ 'department', 'designation', 'location', 'reporting_to' ] );

// Hidden in edit mode (the legacy form wrapped these in `<# if ( ! data.id ) #>`).
const CREATE_ONLY_FIELDS = new Set( [ 'type', 'status', 'location', 'reporting_to', 'pay_rate', 'pay_type' ] );

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
// Mirrors the server's erp_is_valid_employee_id(): start alphanumeric, then
// only letters, digits and hyphens (no spaces or symbols).
const EMPLOYEE_ID_RE = /^[A-Za-z0-9][-A-Za-z0-9]*$/;
// Client mirrors of the server-side validators in `Employee::create_employee()`
// (the server stays the source of truth; these just give inline feedback).
const NAME_RE    = /^[\p{L}\s.'-]+$/u;            // erp_is_valid_name: letters/space/.'-
const CONTACT_RE = /^[0-9+\-()\s]{6,20}$/;        // erp_is_valid_contact_no
const ZIP_RE     = /^[A-Za-z0-9\s-]{2,12}$/;      // erp_is_valid_zip_code
const CURRENCY_RE = /^\d+(\.\d{1,2})?$/;          // erp_is_valid_currency_amount (non-negative)

// Name fields validated when non-empty (first/last also required above).
const NAME_FIELDS = [ 'first_name', 'middle_name', 'last_name', 'father_name', 'mother_name', 'spouse_name' ] as const;
const CONTACT_FIELDS = [ 'mobile', 'phone', 'work_phone' ] as const;
const DATE_FIELDS = [ 'hiring_date', 'end_date', 'date_of_birth' ] as const;

function isValidDate( value: string ): boolean {
	// HTML date inputs emit YYYY-MM-DD; accept that and verify it's a real date.
	if ( ! /^\d{4}-\d{2}-\d{2}$/.test( value ) ) {
		return false;
	}
	const ts = Date.parse( value );
	return ! Number.isNaN( ts );
}

function requiredFields( mode: FormMode ): readonly string[] {
	const base = [ 'first_name', 'last_name', 'email', 'hiring_date', 'department', 'designation' ];
	// Employee Type + Status are only shown (and required) when creating.
	return mode === 'create' ? [ ...base, 'type', 'status' ] : base;
}

function toOptions( list: LookupOption[] ): Option[] {
	return list.map( ( o ) => ( { value: String( o.id ), label: o.title } ) );
}

interface EmployeeFormProps {
	readonly mode:          FormMode;
	readonly initialValues: FormState;
	readonly submitLabel:   string;
	readonly busyLabel:     string;
	readonly submitting:    boolean;
	readonly submitError:   string | null;
	readonly onSubmit:      ( payload: EmployeeCreateInput ) => void;
	readonly onCancel:      () => void;
	/** Employee id in edit mode — passed to the extra-fields filter for prefill. */
	readonly employeeId?:   number;
}

export function EmployeeForm( {
	mode,
	initialValues,
	submitLabel,
	busyLabel,
	submitting,
	submitError,
	onSubmit,
	onCancel,
	employeeId,
}: EmployeeFormProps ): JSX.Element {
	const isEdit = mode === 'edit';
	const navigate = useNavigate();

	const [ form, setForm ] = useState< FormState >( initialValues );
	const [ notify, setNotify ] = useState( false );
	const [ sendLogin, setSendLogin ] = useState( false );
	const [ errors, setErrors ] = useState< Record< string, string > >( {} );
	const [ userCheck, setUserCheck ] = useState< UserCheckResult | null >( null );
	const [ converting, setConverting ] = useState( false );

	const [ departments, setDepartments ] = useState< Option[] >( [] );
	const [ designations, setDesignations ] = useState< Option[] >( [] );
	const [ locations, setLocations ] = useState< Option[] >( [] );
	const reporting = useEmployeeSearch( ! isEdit, undefined, form.reporting_to ?? '' );
	const [ lookupsLoaded, setLookupsLoaded ] = useState( false );

	// Pro-injected custom fields (Custom Field Builder). Empty when pro is absent.
	const [ extraFields, setExtraFields ] = useState< ExtraField[] >( [] );

	useEffect( () => {
		let cancelled = false;
		void Promise.all( [
			loadLookup( 'departments' ).then( ( l ) => ! cancelled && setDepartments( toOptions( l ) ) ),
			loadLookup( 'designations' ).then( ( l ) => ! cancelled && setDesignations( toOptions( l ) ) ),
		] ).finally( () => ! cancelled && setLookupsLoaded( true ) );
		if ( ! isEdit ) {
			void loadLookup( 'locations' ).then( ( l ) => ! cancelled && setLocations( toOptions( l ) ) );
		}
		return () => {
			cancelled = true;
		};
	}, [ isEdit ] );

	// Load pro-injected custom field definitions (and saved values in edit mode)
	// via the wp.hooks filter. Pro returns an array (or a Promise of one); when
	// no pro consumer is registered the default `[]` keeps this a no-op.
	useEffect( () => {
		let cancelled = false;
		const result = applyFilters( HOOKS.EMPLOYEE_EXTRA_FIELDS, [], { mode, employeeId } ) as
			| ExtraField[]
			| Promise< ExtraField[] >;
		void Promise.resolve( result ).then( ( fields ) => {
			if ( cancelled || ! Array.isArray( fields ) || fields.length === 0 ) {
				return;
			}
			setExtraFields( fields );
			// Seed saved values into the form state (edit-mode prefill) without
			// clobbering anything the user may have already typed.
			setForm( ( prev ) => {
				const next = { ...prev };
				for ( const f of fields ) {
					if ( next[ f.key ] === undefined ) {
						next[ f.key ] = f.value ?? '';
					}
				}
				return next;
			} );
		} );
		return () => {
			cancelled = true;
		};
	}, [ mode, employeeId ] );

	// Department + Designation are required to create an employee. If the org has
	// none yet, point the user to set them up first instead of leaving the
	// required selects empty with no way forward.
	const missingOrgSteps: { label: string; path: string }[] = [];
	if ( lookupsLoaded && ! isEdit ) {
		if ( departments.length === 0 ) {
			missingOrgSteps.push( { label: __( 'Add a department', 'erp' ), path: '/departments' } );
		}
		if ( designations.length === 0 ) {
			missingOrgSteps.push( { label: __( 'Add a designation', 'erp' ), path: '/designations' } );
		}
	}

	// Create flow only: debounce-check the email. Mirrors the legacy `check_user`
	// — warns when the email already belongs to an employee, or offers to convert
	// an existing WP user into an employee.
	const email = form.email ?? '';
	useEffect( () => {
		if ( isEdit || ! email || ! EMAIL_RE.test( email ) ) {
			setUserCheck( null );
			return;
		}
		let cancelled = false;
		const handle = setTimeout( () => {
			void checkUser( email )
				.then( ( result ) => ! cancelled && setUserCheck( result ) )
				.catch( () => ! cancelled && setUserCheck( null ) );
		}, 500 );
		return () => {
			cancelled = true;
			clearTimeout( handle );
		};
	}, [ isEdit, email ] );

	async function handleConvert(): Promise< void > {
		if ( ! userCheck?.user ) {
			return;
		}
		setConverting( true );
		try {
			const newId = await convertUser( userCheck.user.id );
			toast.success( __( 'WP user converted to employee.', 'erp' ) );
			navigate( `/employees/${ newId }/edit` );
		} catch {
			toast.error( __( 'Could not convert the user.', 'erp' ) );
		} finally {
			setConverting( false );
		}
	}

	// Custom fields for one legacy section key (top/basic/work/personal/bottom),
	// so each group renders at its legacy position in the form.
	const extraBySection = ( sectionKey: string ): ExtraField[] =>
		extraFields.filter( ( f ) => ( f.sectionKey ?? '' ) === sectionKey );

	const set = ( key: string ) => ( value: string ) => {
		setForm( ( prev ) => ( { ...prev, [ key ]: value } ) );
		setErrors( ( prev ) => {
			if ( ! prev[ key ] ) {
				return prev;
			}
			const next = { ...prev };
			delete next[ key ];
			return next;
		} );
	};

	function validate(): boolean {
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
		if ( ! isEdit && userCheck && userCheck.type === 'employee' ) {
			next.email = __( 'An employee already exists with this email address.', 'erp' );
		}
		if ( form.employee_id && form.employee_id.trim() && ! EMPLOYEE_ID_RE.test( form.employee_id.trim() ) ) {
			next.employee_id = __( 'Employee ID can contain only letters, numbers and hyphens.', 'erp' );
		}

		// Name fields — letters, spaces and . ' - only (mirrors erp_is_valid_name).
		for ( const key of NAME_FIELDS ) {
			const v = ( form[ key ] ?? '' ).trim();
			if ( v && ! NAME_RE.test( v ) ) {
				next[ key ] = __( 'Use letters only (no digits or symbols).', 'erp' );
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

		setErrors( next );
		return Object.keys( next ).length === 0;
	}

	function buildPayload(): EmployeeCreateInput {
		const payload: EmployeeCreateInput = {};
		const extraKeys = new Set( extraFields.map( ( f ) => f.key ) );
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
			payload[ key ] = NUMERIC_FIELDS.has( key ) ? parseInt( value, 10 ) : value;
		}
		if ( Object.keys( additional ).length > 0 ) {
			payload.additional = additional;
		}
		if ( ! isEdit && notify ) {
			payload.user_notification = true;
			if ( sendLogin ) {
				payload.login_info = true;
			}
		}
		return payload;
	}

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();
		if ( ! validate() ) {
			return;
		}
		onSubmit( buildPayload() );
	}

	return (
		<form onSubmit={ handleSubmit } className="w-full">
			{ submitError ? (
				<Alert variant="destructive" className="mb-6">
					<AlertTitle>{ __( 'Something went wrong', 'erp' ) }</AlertTitle>
					<AlertDescription>{ submitError }</AlertDescription>
				</Alert>
			) : null }

			{ missingOrgSteps.length > 0 ? (
				<div className="mb-6">
					<DependencyHint
						message={ __( 'Set up your organisation before adding employees — Department and Designation are required.', 'erp' ) }
						steps={ missingOrgSteps }
					/>
				</div>
			) : null }

			{ ! isEdit && userCheck && userCheck.type === 'employee' ? (
				<Alert variant="destructive" className="mb-6">
					<AlertTitle>{ __( 'Email already in use', 'erp' ) }</AlertTitle>
					<AlertDescription>
						{ __( 'An employee already exists with this email address.', 'erp' ) }
					</AlertDescription>
				</Alert>
			) : null }

			{ ! isEdit && userCheck && userCheck.type === 'wp_user' && userCheck.user ? (
				<Alert className="mb-6">
					<AlertTitle>{ __( 'This email belongs to an existing user', 'erp' ) }</AlertTitle>
					<AlertDescription>
						<div className="flex flex-col gap-3">
							<span>
								{ __( 'A WordPress user with this email already exists. Convert them into an employee instead of creating a new account.', 'erp' ) }
							</span>
							<div>
								<Button type="button" variant="outline" className="h-9 px-4" disabled={ converting } onClick={ () => void handleConvert() }>
									{ converting ? __( 'Converting…', 'erp' ) : __( 'Convert to employee', 'erp' ) }
								</Button>
							</div>
						</div>
					</AlertDescription>
				</Alert>
			) : null }

			{ Object.keys( errors ).length > 0 ? (
				<Alert variant="destructive" className="mb-6">
					<AlertTitle>{ __( 'Please correct the following', 'erp' ) }</AlertTitle>
					<AlertDescription>
						<ul className="list-disc pl-5">
							{ Array.from( new Set( Object.values( errors ) ) ).map( ( msg ) => (
								<li key={ msg }>{ msg }</li>
							) ) }
						</ul>
					</AlertDescription>
				</Alert>
			) : null }

			<div className="space-y-6">
				<ExtraFields fields={ extraBySection( 'top' ) } values={ form } onChange={ set } />

				<FormSection
					title={ __( 'Basic Information', 'erp' ) }
					description={ __( 'Fields marked with * are required.', 'erp' ) }
				>
					<TextField id="first_name" label={ __( 'First Name', 'erp' ) } required value={ form.first_name ?? '' } onChange={ set( 'first_name' ) } error={ errors.first_name } maxLength={ 30 } />
					<TextField id="middle_name" label={ __( 'Middle Name', 'erp' ) } value={ form.middle_name ?? '' } onChange={ set( 'middle_name' ) } maxLength={ 30 } />
					<TextField id="last_name" label={ __( 'Last Name', 'erp' ) } required value={ form.last_name ?? '' } onChange={ set( 'last_name' ) } error={ errors.last_name } maxLength={ 30 } />
					<TextField id="employee_id" label={ __( 'Employee ID', 'erp' ) } value={ form.employee_id ?? '' } onChange={ set( 'employee_id' ) } error={ errors.employee_id } />
					<TextField id="email" label={ __( 'Email', 'erp' ) } type="email" required value={ form.email ?? '' } onChange={ set( 'email' ) } error={ errors.email } />
					{ ! isEdit ? (
						<>
							<SelectField id="type" label={ __( 'Employee Type', 'erp' ) } required options={ TYPE_OPTIONS } value={ form.type ?? '' } onChange={ set( 'type' ) } error={ errors.type } placeholder={ __( '- Select -', 'erp' ) } />
							<SelectField id="status" label={ __( 'Employee Status', 'erp' ) } required options={ STATUS_OPTIONS } value={ form.status ?? '' } onChange={ set( 'status' ) } error={ errors.status } placeholder={ __( '- Select -', 'erp' ) } />
						</>
					) : null }
					<TextField id="hiring_date" label={ __( 'Date of Hire', 'erp' ) } type="date" required value={ form.hiring_date ?? '' } onChange={ set( 'hiring_date' ) } error={ errors.hiring_date } />
					<TextField id="end_date" label={ __( 'Employee End Date', 'erp' ) } type="date" value={ form.end_date ?? '' } onChange={ set( 'end_date' ) } />
					<SmartSelectField id="department" label={ __( 'Department', 'erp' ) } required options={ departments } value={ form.department ?? '' } onChange={ set( 'department' ) } error={ errors.department } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search departments…', 'erp' ) } />
					<SmartSelectField id="designation" label={ __( 'Job Title', 'erp' ) } required options={ designations } value={ form.designation ?? '' } onChange={ set( 'designation' ) } error={ errors.designation } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search job titles…', 'erp' ) } />
				</FormSection>

				<ExtraFields fields={ extraBySection( 'basic' ) } values={ form } onChange={ set } />

				{ ! isEdit ? (
					<FormSection title={ __( 'Work', 'erp' ) }>
						<SmartSelectField id="location" label={ __( 'Location', 'erp' ) } options={ locations } value={ form.location ?? '' } onChange={ set( 'location' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search locations…', 'erp' ) } />
						<SmartSelectField id="reporting_to" label={ __( 'Reporting To', 'erp' ) } options={ reporting.options } value={ form.reporting_to ?? '' } onChange={ set( 'reporting_to' ) } onSearch={ reporting.onSearch } loading={ reporting.loading } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search employees…', 'erp' ) } />
						<SelectField id="hiring_source" label={ __( 'Source of Hire', 'erp' ) } options={ SOURCE_OPTIONS } value={ form.hiring_source ?? '' } onChange={ set( 'hiring_source' ) } placeholder={ __( '- Select -', 'erp' ) } />
						<TextField id="pay_rate" label={ __( 'Pay Rate', 'erp' ) } value={ form.pay_rate ?? '' } onChange={ set( 'pay_rate' ) } />
						<SelectField id="pay_type" label={ __( 'Pay Type', 'erp' ) } options={ PAY_TYPE_OPTIONS } value={ form.pay_type ?? '' } onChange={ set( 'pay_type' ) } placeholder={ __( '- Select -', 'erp' ) } />
						<TextField id="work_phone" label={ __( 'Work Phone', 'erp' ) } type="tel" value={ form.work_phone ?? '' } onChange={ set( 'work_phone' ) } />
					</FormSection>
				) : (
					<FormSection title={ __( 'Work', 'erp' ) }>
						<SelectField id="hiring_source" label={ __( 'Source of Hire', 'erp' ) } options={ SOURCE_OPTIONS } value={ form.hiring_source ?? '' } onChange={ set( 'hiring_source' ) } placeholder={ __( '- Select -', 'erp' ) } />
						<TextField id="work_phone" label={ __( 'Work Phone', 'erp' ) } type="tel" value={ form.work_phone ?? '' } onChange={ set( 'work_phone' ) } />
					</FormSection>
				) }

				<ExtraFields fields={ extraBySection( 'work' ) } values={ form } onChange={ set } />

				<FormSection title={ __( 'Personal Details', 'erp' ) }>
					<SelectField id="blood_group" label={ __( 'Blood Group', 'erp' ) } options={ BLOOD_GROUP_OPTIONS } value={ form.blood_group ?? '' } onChange={ set( 'blood_group' ) } placeholder={ __( '- Select -', 'erp' ) } />
					<TextField id="spouse_name" label={ __( "Spouse's name", 'erp' ) } value={ form.spouse_name ?? '' } onChange={ set( 'spouse_name' ) } />
					<TextField id="father_name" label={ __( "Father's name", 'erp' ) } value={ form.father_name ?? '' } onChange={ set( 'father_name' ) } />
					<TextField id="mother_name" label={ __( "Mother's name", 'erp' ) } value={ form.mother_name ?? '' } onChange={ set( 'mother_name' ) } />
					<TextField id="mobile" label={ __( 'Mobile', 'erp' ) } type="tel" value={ form.mobile ?? '' } onChange={ set( 'mobile' ) } />
					<TextField id="phone" label={ __( 'Phone', 'erp' ) } type="tel" value={ form.phone ?? '' } onChange={ set( 'phone' ) } />
					<TextField id="other_email" label={ __( 'Other Email', 'erp' ) } type="email" value={ form.other_email ?? '' } onChange={ set( 'other_email' ) } />
					<TextField id="date_of_birth" label={ __( 'Date of Birth', 'erp' ) } type="date" value={ form.date_of_birth ?? '' } onChange={ set( 'date_of_birth' ) } />
					<TextField id="nationality" label={ __( 'Nationality', 'erp' ) } value={ form.nationality ?? '' } onChange={ set( 'nationality' ) } />
					<SelectField id="gender" label={ __( 'Gender', 'erp' ) } options={ GENDER_OPTIONS } value={ form.gender ?? '' } onChange={ set( 'gender' ) } placeholder={ __( '- Select -', 'erp' ) } />
					<SelectField id="marital_status" label={ __( 'Marital Status', 'erp' ) } options={ MARITAL_OPTIONS } value={ form.marital_status ?? '' } onChange={ set( 'marital_status' ) } placeholder={ __( '- Select -', 'erp' ) } />
					<TextField id="driving_license" label={ __( 'Driving License', 'erp' ) } value={ form.driving_license ?? '' } onChange={ set( 'driving_license' ) } />
					<TextField id="hobbies" label={ __( 'Hobbies', 'erp' ) } value={ form.hobbies ?? '' } onChange={ set( 'hobbies' ) } />
					<TextField id="user_url" label={ __( 'Website', 'erp' ) } type="url" value={ form.user_url ?? '' } onChange={ set( 'user_url' ) } />
					<TextField id="street_1" label={ __( 'Address 1', 'erp' ) } value={ form.street_1 ?? '' } onChange={ set( 'street_1' ) } />
					<TextField id="street_2" label={ __( 'Address 2', 'erp' ) } value={ form.street_2 ?? '' } onChange={ set( 'street_2' ) } />
					<TextField id="city" label={ __( 'City', 'erp' ) } value={ form.city ?? '' } onChange={ set( 'city' ) } />
					<TextField id="country" label={ __( 'Country', 'erp' ) } value={ form.country ?? '' } onChange={ set( 'country' ) } />
					<TextField id="state" label={ __( 'Province / State', 'erp' ) } value={ form.state ?? '' } onChange={ set( 'state' ) } />
					<TextField id="postal_code" label={ __( 'Post Code / Zip Code', 'erp' ) } value={ form.postal_code ?? '' } onChange={ set( 'postal_code' ) } />
					<TextareaField id="description" label={ __( 'Biography', 'erp' ) } value={ form.description ?? '' } onChange={ set( 'description' ) } className="sm:col-span-2 lg:col-span-3" />
				</FormSection>

				<ExtraFields fields={ extraBySection( 'personal' ) } values={ form } onChange={ set } />

				<ExtraFields fields={ extraBySection( 'bottom' ) } values={ form } onChange={ set } />

				{ ! isEdit ? (
					<FormSection title={ __( 'Notification', 'erp' ) }>
						<label className="flex items-start gap-2.5 sm:col-span-2 lg:col-span-3">
							<Checkbox checked={ notify } onCheckedChange={ ( v ) => setNotify( v === true ) } className="mt-0.5" />
							<span className="text-sm text-foreground">
								{ __( 'Send the employee a welcome email.', 'erp' ) }
							</span>
						</label>
						{ notify ? (
							<label className="flex items-start gap-2.5 sm:col-span-2 lg:col-span-3">
								<Checkbox checked={ sendLogin } onCheckedChange={ ( v ) => setSendLogin( v === true ) } className="mt-0.5" />
								<span className="text-sm text-foreground">
									{ __( 'Include login details in the welcome email.', 'erp' ) }
								</span>
							</label>
						) : null }
					</FormSection>
				) : null }
			</div>

			<div className="sticky bottom-0 z-10 mt-6 flex w-full items-center justify-end gap-3 rounded-[10px] border border-border bg-card/95 px-6 py-4 shadow-[0_-1px_3px_rgba(0,0,0,0.06)] backdrop-blur supports-backdrop-filter:bg-card/80">
				<Button type="button" variant="outline" className="h-10 px-5" onClick={ onCancel } disabled={ submitting }>
					{ __( 'Cancel', 'erp' ) }
				</Button>
				<Button type="submit" variant="default" className="h-10 px-5" disabled={ submitting }>
					{ submitting ? busyLabel : submitLabel }
				</Button>
			</div>
		</form>
	);
}
