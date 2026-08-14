/**
 * Full-page "Add New Employee" screen.
 *
 * Thin wrapper around the shared <EmployeeForm/>. Submits to the modern
 * `POST /erp/v2/employees` endpoint (which delegates to the unchanged
 * `Employee::create_employee()` model, so every server hook fires).
 *
 * Visual language: token-only styling, a sticky header with a close (✕)
 * action, and a sticky action bar.
 */

import { useDispatch } from '@wordpress/data';
import { useState } from 'react';
import type { JSX } from 'react';
import { useNavigate } from 'react-router-dom';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeeCreateInput, EmployeeListItem } from '@/stores/employees';

import { EmployeeForm } from './EmployeeForm';
import { erpToast } from '@/shared/toast';

interface CreateDispatch {
	createEmployee: ( payload: EmployeeCreateInput ) => Promise< EmployeeListItem >;
}

function EmployeeCreateInner(): JSX.Element {
	const navigate = useNavigate();
	const { createEmployee } = useDispatch( employeesStoreName ) as unknown as CreateDispatch;

	const [ submitError, setSubmitError ] = useState< string | null >( null );
	const [ submitting, setSubmitting ] = useState( false );

	function close(): void {
		if ( window.history.length > 1 ) {
			navigate( -1 );
		} else {
			navigate( '/employees' );
		}
	}

	async function handleSubmit( payload: EmployeeCreateInput ): Promise< void > {
		setSubmitError( null );
		setSubmitting( true );
		try {
			await createEmployee( payload );
			// Name them: the toast is about a person, and an initials avatar with
			// no name beside it reads as decoration. No photo yet — the form
			// stages a `photo_id`, and the URL never reaches this payload.
			erpToast.success( sprintf( __( '%s was added.', 'erp' ), employeeName( payload ) ), {
				user: { name: employeeName( payload ) },
			} );
			close();
		} catch ( raw ) {
			const err = raw as { message?: string };
			setSubmitError( err?.message || __( 'Could not create the employee. Please try again.', 'erp' ) );
		} finally {
			setSubmitting( false );
		}
	}

	return (
		<div className="mx-auto w-full max-w-full space-y-6">
				<EmployeeForm
					mode="create"
					heading={ __( 'Add New Employee', 'erp' ) }
					subheading={ __( 'Create a new member of your team.', 'erp' ) }
					initialValues={ { status: 'active' } }
					submitLabel={ __( 'Create Employee', 'erp' ) }
					busyLabel={ __( 'Creating…', 'erp' ) }
					submitting={ submitting }
					submitError={ submitError }
					onSubmit={ handleSubmit }
					onCancel={ close }
				/>
		</div>
	);
}

/** Display name from a submitted payload, for the toast's avatar + title. */
function employeeName( payload: EmployeeCreateInput ): string {
	const first = String( payload.first_name ?? '' ).trim();
	const last  = String( payload.last_name ?? '' ).trim();
	return `${ first } ${ last }`.trim();
}

export function EmployeeCreatePage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_create_employee' ] }>
			<ErrorBoundary>
				<EmployeeCreateInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
