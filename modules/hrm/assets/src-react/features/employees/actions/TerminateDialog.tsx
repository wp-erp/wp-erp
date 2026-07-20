/**
 * Terminate-employee form dialog.
 *
 * Collects the four fields the legacy `Employee::terminate()` model requires —
 * terminate date, type, reason, eligibility for rehire — with the exact same
 * enum values. Client-side required validation mirrors the model's checks so the
 * user gets inline feedback before the round-trip; the server re-validates.
 */

import {
	Alert,
	AlertDescription,
	Button,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '@wedevs/plugin-ui';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import type { EmployeeTerminateInput } from '@/stores/employees';

import { SelectField, TextField } from '../../employee-create/fields';
import { todayLocalYmd } from '@/shared/utils/date';
import {
	REHIRE_OPTIONS,
	TERMINATION_REASON_OPTIONS,
	TERMINATION_TYPE_OPTIONS,
} from '../terminate-options';

interface TerminateDialogProps {
	readonly open:         boolean;
	readonly employeeName: string;
	readonly busy:         boolean;
	readonly error:        string | null;
	readonly onClose:      () => void;
	readonly onSubmit:     ( payload: EmployeeTerminateInput ) => void;
}

/** Today as `YYYY-MM-DD`, the default termination date. */
function today(): string {
	return todayLocalYmd();
}

const EMPTY_FORM: EmployeeTerminateInput = {
	terminate_date:      '',
	termination_type:    '',
	termination_reason:  '',
	eligible_for_rehire: '',
};

export function TerminateDialog( {
	open,
	employeeName,
	busy,
	error,
	onClose,
	onSubmit,
}: TerminateDialogProps ): JSX.Element {
	const [ form, setForm ]     = useState< EmployeeTerminateInput >( EMPTY_FORM );
	const [ errors, setErrors ] = useState< Record< string, string > >( {} );

	// Reset the form each time the dialog opens for a fresh employee.
	useEffect( () => {
		if ( open ) {
			setForm( { ...EMPTY_FORM, terminate_date: today() } );
			setErrors( {} );
		}
	}, [ open ] );

	const set =
		( key: keyof EmployeeTerminateInput ) =>
		( value: string ): void => {
			setForm( ( prev ) => ( { ...prev, [ key ]: value } ) );
			setErrors( ( prev ) => ( { ...prev, [ key ]: '' } ) );
		};

	function validate(): boolean {
		const next: Record< string, string > = {};
		if ( ! form.terminate_date ) {
			next.terminate_date = __( 'Termination date is required.', 'erp' );
		}
		if ( ! form.termination_type ) {
			next.termination_type = __( 'Termination type is required.', 'erp' );
		}
		if ( ! form.termination_reason ) {
			next.termination_reason = __( 'Termination reason is required.', 'erp' );
		}
		if ( ! form.eligible_for_rehire ) {
			next.eligible_for_rehire = __( 'Eligibility for rehire is required.', 'erp' );
		}
		setErrors( next );
		return Object.keys( next ).length === 0;
	}

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();
		if ( ! validate() ) {
			return;
		}
		onSubmit( form );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Terminate employee', 'erp' ) }</DialogTitle>
					<DialogDescription>
						{ employeeName
							? sprintf( __( 'Record the termination details for %s.', 'erp' ), employeeName )
							: __( 'Record the termination details.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<TextField
						id="terminate_date"
						label={ __( 'Termination Date', 'erp' ) }
						type="date"
						required
						value={ form.terminate_date }
						onChange={ set( 'terminate_date' ) }
						error={ errors.terminate_date }
					/>
					<SelectField
						id="termination_type"
						label={ __( 'Termination Type', 'erp' ) }
						required
						options={ TERMINATION_TYPE_OPTIONS }
						value={ form.termination_type }
						onChange={ set( 'termination_type' ) }
						error={ errors.termination_type }
						placeholder={ __( '- Select -', 'erp' ) }
					/>
					<SelectField
						id="termination_reason"
						label={ __( 'Termination Reason', 'erp' ) }
						required
						options={ TERMINATION_REASON_OPTIONS }
						value={ form.termination_reason }
						onChange={ set( 'termination_reason' ) }
						error={ errors.termination_reason }
						placeholder={ __( '- Select -', 'erp' ) }
					/>
					<SelectField
						id="eligible_for_rehire"
						label={ __( 'Eligible for Rehire', 'erp' ) }
						required
						options={ REHIRE_OPTIONS }
						value={ form.eligible_for_rehire }
						onChange={ set( 'eligible_for_rehire' ) }
						error={ errors.eligible_for_rehire }
						placeholder={ __( '- Select -', 'erp' ) }
					/>

					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					<DialogFooter className="gap-5 sm:gap-5">
						<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onClose }>
							{ __( 'Cancel', 'erp' ) }
						</Button>
						<Button type="submit" variant="destructive" className="h-10 px-6" disabled={ busy }>
							{ busy ? __( 'Terminating…', 'erp' ) : __( 'Terminate', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
