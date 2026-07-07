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

import { Button, toast } from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';
import { useNavigate } from 'react-router-dom';

import { HOOKS } from '@/shared/filters';
import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeeCreateInput } from '@/stores/employees';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';
import { useBoot } from '@/shared/hooks/useBoot';
import { useCan } from '@/shared/hooks/useCan';

import { DepartmentFormDialog } from '../departments/DepartmentFormDialog';
import type { Department, DepartmentInput } from '../departments/types';
import { DesignationFormDialog } from '../designations/DesignationFormDialog';
import type { Designation, DesignationInput } from '../designations/types';
import { loadLookup } from '../employees/filters/lookups';
import type { LookupOption } from '../employees/filters/lookups';
import { EmployeeBasicSection } from './EmployeeBasicSection';
import { EmployeeFormAlerts } from './EmployeeFormAlerts';
import { PhotoUpload } from './PhotoUpload';
import { EmployeeNotificationSection } from './EmployeeNotificationSection';
import { EmployeePersonalSection } from './EmployeePersonalSection';
import { EmployeeWorkSection } from './EmployeeWorkSection';
import { ExtraFields } from './ExtraFields';
import type { ExtraField } from './ExtraFields';
import { checkUser, convertUser } from './useUserCheck';
import type { UserCheckResult } from './useUserCheck';
import type { Option } from './options';
import {
	buildEmployeePayload,
	EMAIL_RE,
	validateEmployeeForm,
} from './validation';

import type { FormMode, FormState } from './validation';

// Re-exported so existing consumers keep importing these from EmployeeForm.
export type { FormMode, FormState };

function toOptions( list: LookupOption[] ): Option[] {
	return list.map( ( o ) => ( { value: String( o.id ), label: o.title } ) );
}

interface EmployeeFormProps {
	readonly mode: FormMode;
	readonly initialValues: FormState;
	readonly submitLabel: string;
	readonly busyLabel: string;
	readonly submitting: boolean;
	readonly submitError: string | null;
	readonly onSubmit: ( payload: EmployeeCreateInput ) => void;
	readonly onCancel: () => void;
	/** Employee id in edit mode — passed to the extra-fields filter for prefill. */
	readonly employeeId?: number;
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

	// Manager (bare `erp_edit_employee`) vs an employee editing their OWN profile.
	// The Work section (Source of Hire, Work Phone) is manager-only in the legacy
	// form, so hide it from a self-editor — they keep Basic + Personal.
	const isManager = useCan( 'erp_edit_employee' );
	const navigate = useNavigate();
	const { invalidate } = useDispatch( employeesStoreName ) as unknown as {
		invalidate: () => void;
	};

	const [ form, setForm ] = useState< FormState >( initialValues );
	// Preview URL for the create-mode photo picker (the attachment id lives in
	// `form.photo_id`, which flows into the create payload).
	const [ photoUrl, setPhotoUrl ] = useState( '' );
	const [ notify, setNotify ] = useState( false );
	const [ sendLogin, setSendLogin ] = useState( false );
	const [ errors, setErrors ] = useState< Record< string, string > >( {} );
	const [ userCheck, setUserCheck ] = useState< UserCheckResult | null >(
		null
	);
	const [ converting, setConverting ] = useState( false );

	const [ departments, setDepartments ] = useState< Option[] >( [] );
	const [ designations, setDesignations ] = useState< Option[] >( [] );
	const [ locations, setLocations ] = useState< Option[] >( [] );
	const reporting = useEmployeeSearch(
		! isEdit,
		undefined,
		form.reporting_to ?? ''
	);
	const [ lookupsLoaded, setLookupsLoaded ] = useState( false );

	// Country / state options for the address selects — sourced from the boot
	// payload (parity with the legacy new-employee.php Countries dropdowns).
	const boot = useBoot();
	const countryOptions = useMemo< Option[] >(
		() =>
			( boot.countries ?? [] ).map( ( c ) => ( {
				value: c.value,
				label: c.label,
			} ) ),
		[ boot.countries ]
	);
	// State options depend on the selected country; empty when the country has
	// no states defined (the field then stays an empty/disabled select).
	const stateOptions = useMemo< Option[] >(
		() =>
			( ( boot.states ?? {} )[ form.country ?? '' ] ?? [] ).map(
				( s ) => ( { value: s.value, label: s.label } )
			),
		[ boot.states, form.country ]
	);

	// Inline "+ Add new" for the two required org dependencies — open the same
	// dialogs the Departments / Designations screens use, then merge the new
	// record into the select and select it (no navigating away mid-form).
	const [ quickDeptOpen, setQuickDeptOpen ] = useState( false );
	const [ quickDeptBusy, setQuickDeptBusy ] = useState( false );
	const [ quickDeptErr, setQuickDeptErr ] = useState< string | null >( null );
	const [ quickDesigOpen, setQuickDesigOpen ] = useState( false );
	const [ quickDesigBusy, setQuickDesigBusy ] = useState( false );
	const [ quickDesigErr, setQuickDesigErr ] = useState< string | null >(
		null
	);

	// Pro-injected custom fields (Custom Field Builder). Empty when pro is absent.
	const [ extraFields, setExtraFields ] = useState< ExtraField[] >( [] );

	useEffect( () => {
		let cancelled = false;
		void Promise.all( [
			loadLookup( 'departments' ).then(
				( l ) => ! cancelled && setDepartments( toOptions( l ) )
			),
			loadLookup( 'designations' ).then(
				( l ) => ! cancelled && setDesignations( toOptions( l ) )
			),
		] ).finally( () => ! cancelled && setLookupsLoaded( true ) );
		if ( ! isEdit ) {
			void loadLookup( 'locations' ).then(
				( l ) => ! cancelled && setLocations( toOptions( l ) )
			);
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
		const result = applyFilters( HOOKS.EMPLOYEE_EXTRA_FIELDS, [], {
			mode,
			employeeId,
		} ) as ExtraField[] | Promise< ExtraField[] >;
		void Promise.resolve( result ).then( ( fields ) => {
			if (
				cancelled ||
				! Array.isArray( fields ) ||
				fields.length === 0
			) {
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
			missingOrgSteps.push( {
				label: __( 'Add a department', 'erp' ),
				path: '/departments',
			} );
		}
		if ( designations.length === 0 ) {
			missingOrgSteps.push( {
				label: __( 'Add a designation', 'erp' ),
				path: '/designations',
			} );
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
			// Raw POST bypasses the store — drop the list/counts cache so the new
			// employee shows on the People page.
			invalidate();
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

	// Country select — also resets the dependent state field so a stale state
	// code can't survive a country change.
	const setCountry = ( value: string ): void => {
		setForm( ( prev ) => ( { ...prev, country: value, state: '' } ) );
	};

	function handleQuickDept( payload: DepartmentInput ): void {
		setQuickDeptBusy( true );
		setQuickDeptErr( null );
		request< Department >( restPath( 'v2', '/departments' ), {
			method: 'POST',
			data: payload,
		} )
			.then( ( created ) => {
				const opt: Option = {
					value: String( created.id ),
					label: created.title,
				};
				setDepartments( ( prev ) => [ ...prev, opt ] );
				set( 'department' )( opt.value );
				setQuickDeptOpen( false );
			} )
			.catch( ( raw ) =>
				setQuickDeptErr(
					( raw as ApiError )?.message ??
						__( 'Could not create the department.', 'erp' )
				)
			)
			.finally( () => setQuickDeptBusy( false ) );
	}

	function handleQuickDesig( payload: DesignationInput ): void {
		setQuickDesigBusy( true );
		setQuickDesigErr( null );
		request< Designation >( restPath( 'v2', '/designations' ), {
			method: 'POST',
			data: payload,
		} )
			.then( ( created ) => {
				const opt: Option = {
					value: String( created.id ),
					label: created.title,
				};
				setDesignations( ( prev ) => [ ...prev, opt ] );
				set( 'designation' )( opt.value );
				setQuickDesigOpen( false );
			} )
			.catch( ( raw ) =>
				setQuickDesigErr(
					( raw as ApiError )?.message ??
						__( 'Could not create the job title.', 'erp' )
				)
			)
			.finally( () => setQuickDesigBusy( false ) );
	}

	function validate(): boolean {
		const next = validateEmployeeForm( form, mode, { userCheck } );
		setErrors( next );
		return Object.keys( next ).length === 0;
	}

	function buildPayload(): EmployeeCreateInput {
		return buildEmployeePayload( form, mode, {
			extraFields,
			notify,
			sendLogin,
		} );
	}

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();
		if ( ! validate() ) {
			return;
		}
		onSubmit( buildPayload() );
	}

	return (
		<>
			<form onSubmit={ handleSubmit } className="w-full">
				<EmployeeFormAlerts
					submitError={ submitError }
					missingOrgSteps={ missingOrgSteps }
					isEdit={ isEdit }
					userCheck={ userCheck }
					converting={ converting }
					onConvert={ () => void handleConvert() }
					errors={ errors }
				/>

				<div className="space-y-6">
					{ ! isEdit && (
						<section className="rounded-[10px] bg-card p-6 shadow-sm">
							<PhotoUpload
								avatarUrl={ photoUrl }
								fullName={ `${ form.first_name ?? '' } ${ form.last_name ?? '' }`.trim() }
								initials={
									(
										( form.first_name ?? '' ).charAt( 0 ) +
										( form.last_name ?? '' ).charAt( 0 )
									).toUpperCase() || 'E'
								}
								onChange={ ( photoId, url ) => {
									setPhotoUrl( url );
									setForm( ( prev ) => ( {
										...prev,
										photo_id: String( photoId ),
									} ) );
								} }
							/>
						</section>
					) }

					<ExtraFields
						fields={ extraBySection( 'top' ) }
						values={ form }
						onChange={ set }
					/>

					<EmployeeBasicSection
						form={ form }
						errors={ errors }
						set={ set }
						isEdit={ isEdit }
						departments={ departments }
						designations={ designations }
						submitting={ submitting }
						onAddDept={ () => {
							setQuickDeptErr( null );
							setQuickDeptOpen( true );
						} }
						onAddDesig={ () => {
							setQuickDesigErr( null );
							setQuickDesigOpen( true );
						} }
					/>

					<ExtraFields
						fields={ extraBySection( 'basic' ) }
						values={ form }
						onChange={ set }
					/>

					{ ( ! isEdit || isManager ) && (
						<EmployeeWorkSection
							form={ form }
							set={ set }
							isEdit={ isEdit }
							locations={ locations }
							reporting={ reporting }
						/>
					) }

					<ExtraFields
						fields={ extraBySection( 'work' ) }
						values={ form }
						onChange={ set }
					/>

					<EmployeePersonalSection
						form={ form }
						set={ set }
						countryOptions={ countryOptions }
						stateOptions={ stateOptions }
						onCountryChange={ setCountry }
					/>

					<ExtraFields
						fields={ extraBySection( 'personal' ) }
						values={ form }
						onChange={ set }
					/>

					<ExtraFields
						fields={ extraBySection( 'bottom' ) }
						values={ form }
						onChange={ set }
					/>

					{ ! isEdit ? (
						<EmployeeNotificationSection
							notify={ notify }
							setNotify={ setNotify }
							sendLogin={ sendLogin }
							setSendLogin={ setSendLogin }
						/>
					) : null }
				</div>

				<div className="sticky bottom-0 z-10 mt-6 flex w-full items-center justify-end gap-3 rounded-[10px] border border-border bg-card/95 px-6 py-4 shadow-[0_-1px_3px_rgba(0,0,0,0.06)] backdrop-blur supports-backdrop-filter:bg-card/80">
					<Button
						type="button"
						variant="outline"
						className="h-10 px-5"
						onClick={ onCancel }
						disabled={ submitting }
					>
						{ __( 'Cancel', 'erp' ) }
					</Button>
					<Button
						type="submit"
						variant="default"
						className="h-10 px-5"
						disabled={ submitting }
					>
						{ submitting ? busyLabel : submitLabel }
					</Button>
				</div>
			</form>

			<DepartmentFormDialog
				open={ quickDeptOpen }
				editing={ null }
				departments={ [] }
				busy={ quickDeptBusy }
				error={ quickDeptErr }
				onClose={ () => setQuickDeptOpen( false ) }
				onSubmit={ handleQuickDept }
			/>
			<DesignationFormDialog
				open={ quickDesigOpen }
				editing={ null }
				busy={ quickDesigBusy }
				error={ quickDesigErr }
				onClose={ () => setQuickDesigOpen( false ) }
				onSubmit={ handleQuickDesig }
			/>
		</>
	);
}
