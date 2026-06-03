/**
 * Full-page "Edit Employee" screen.
 *
 * Loads the employee in the flat edit shape from `GET /erp/v2/employees/{id}`,
 * prefills the shared <EmployeeForm/> (edit mode hides the create-only fields),
 * and saves via `PUT /erp/v2/employees/{id}` → unchanged
 * `Employee::update_employee()` model.
 */

import { Skeleton, toast } from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { X } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';
import { useNavigate, useParams } from 'react-router-dom';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeeCreateInput } from '@/stores/employees';

import { EmployeeForm } from './EmployeeForm';
import type { FormState } from './EmployeeForm';

interface EditDispatch {
	fetchEmployeeForEdit: ( userId: number ) => Promise< Record< string, unknown > >;
	updateEmployee:       ( userId: number, payload: EmployeeCreateInput ) => Promise< void >;
}

// Keys the form binds to. Everything else on the record (full_name, avatar_url,
// user_id) is ignored — the form only reads these.
const FORM_KEYS: readonly string[] = [
	'employee_id', 'first_name', 'middle_name', 'last_name', 'email', 'type', 'status',
	'hiring_date', 'end_date', 'date_of_birth', 'department', 'designation', 'location',
	'reporting_to', 'hiring_source', 'pay_rate', 'pay_type', 'work_phone', 'other_email',
	'phone', 'mobile', 'blood_group', 'gender', 'marital_status', 'nationality',
	'driving_license', 'hobbies', 'user_url', 'description', 'street_1', 'street_2',
	'city', 'country', 'state', 'postal_code', 'father_name', 'mother_name', 'spouse_name',
];

function toFormState( record: Record< string, unknown > ): FormState {
	const out: FormState = {};
	for ( const key of FORM_KEYS ) {
		const value = record[ key ];
		out[ key ] = value === null || value === undefined ? '' : String( value );
	}
	return out;
}

function EmployeeEditInner( { userId }: { userId: number } ): JSX.Element {
	const navigate = useNavigate();
	const { fetchEmployeeForEdit, updateEmployee } = useDispatch(
		employeesStoreName
	) as unknown as EditDispatch;

	const [ initial, setInitial ] = useState< FormState | null >( null );
	const [ fullName, setFullName ] = useState( '' );
	const [ loadError, setLoadError ] = useState< string | null >( null );
	const [ submitError, setSubmitError ] = useState< string | null >( null );
	const [ submitting, setSubmitting ] = useState( false );

	useEffect( () => {
		let cancelled = false;
		setLoadError( null );
		void fetchEmployeeForEdit( userId )
			.then( ( record ) => {
				if ( cancelled ) {
					return;
				}
				setFullName( String( record.full_name ?? '' ) );
				setInitial( toFormState( record ) );
			} )
			.catch( ( raw ) => {
				if ( cancelled ) {
					return;
				}
				const err = raw as { message?: string };
				setLoadError( err?.message || __( 'Could not load this employee.', 'erp' ) );
			} );
		return () => {
			cancelled = true;
		};
	}, [ userId, fetchEmployeeForEdit ] );

	function close(): void {
		if ( window.history.length > 1 ) {
			navigate( -1 );
		} else {
			navigate( '/people-pro' );
		}
	}

	async function handleSubmit( payload: EmployeeCreateInput ): Promise< void > {
		setSubmitError( null );
		setSubmitting( true );
		try {
			await updateEmployee( userId, payload );
			toast.success( __( 'Employee updated.', 'erp' ) );
			close();
		} catch ( raw ) {
			const err = raw as { message?: string };
			setSubmitError( err?.message || __( 'Could not save the changes. Please try again.', 'erp' ) );
		} finally {
			setSubmitting( false );
		}
	}

	return (
		<div className="mx-auto w-full max-w-7xl space-y-6">
			<section className="flex flex-wrap items-start justify-between gap-4 rounded-[10px] bg-card p-6 shadow-sm">
				<div className="min-w-0">
					<h1 className="mt-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'Edit Employee', 'erp' ) }
					</h1>
					<p className="mt-1 truncate text-sm text-muted-foreground">
						{ fullName || __( 'Update employee details.', 'erp' ) }
					</p>
				</div>
				<button
					type="button"
					onClick={ close }
					aria-label={ __( 'Close', 'erp' ) }
					className="inline-flex size-9 shrink-0 items-center justify-center rounded-md border border-border bg-card text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
				>
					<X size={ 18 } aria-hidden="true" />
				</button>
			</section>

			{ loadError ? (
				<div className="text-center text-sm text-destructive">
					{ loadError }
				</div>
			) : ! initial ? (
				<div className="space-y-6">
					{ [ 0, 1, 2 ].map( ( i ) => (
						<Skeleton key={ i } className="h-48 w-full rounded-lg" />
					) ) }
				</div>
			) : (
				<EmployeeForm
					mode="edit"
					initialValues={ initial }
					submitLabel={ __( 'Save Changes', 'erp' ) }
					busyLabel={ __( 'Saving…', 'erp' ) }
					submitting={ submitting }
					submitError={ submitError }
					onSubmit={ handleSubmit }
					onCancel={ close }
				/>
			) }
		</div>
	);
}

export function EmployeeEditPage(): JSX.Element {
	const { id } = useParams< { id: string } >();
	const userId = Number( id );

	return (
		<CapabilityGate caps={ [ 'erp_edit_employee' ] }>
			<ErrorBoundary>
				{ Number.isFinite( userId ) && userId > 0 ? (
					<EmployeeEditInner userId={ userId } />
				) : (
					<div className="mx-auto my-12 max-w-md text-center text-sm text-muted-foreground">
						{ __( 'Invalid employee.', 'erp' ) }
					</div>
				) }
			</ErrorBoundary>
		</CapabilityGate>
	);
}
