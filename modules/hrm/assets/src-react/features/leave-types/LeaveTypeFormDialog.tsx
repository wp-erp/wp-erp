/**
 * Create / edit leave-type dialog.
 *
 * Mirrors the legacy form: name (required) + description. The model enforces a
 * non-empty, unique name; the client requires non-empty and the server returns
 * the "Name already exists" guard as a form error.
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

import { __ } from '@/shared/i18n';

import { TextField, TextareaField } from '../employee-create/fields';
import type { LeaveType, LeaveTypeInput } from './types';

interface LeaveTypeFormDialogProps {
	readonly open:     boolean;
	readonly editing:  LeaveType | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: LeaveTypeInput ) => void;
}

interface FormState {
	name:        string;
	description: string;
}

const EMPTY: FormState = { name: '', description: '' };

export function LeaveTypeFormDialog( {
	open,
	editing,
	busy,
	error,
	onClose,
	onSubmit,
}: LeaveTypeFormDialogProps ): JSX.Element {
	const [ form, setForm ]         = useState< FormState >( EMPTY );
	const [ nameErr, setNameErr ]   = useState< string >( '' );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setNameErr( '' );
		setForm(
			editing
				? { name: editing.name, description: editing.description }
				: EMPTY
		);
	}, [ open, editing ] );

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();
		const name = form.name.trim();
		if ( ! name ) {
			setNameErr( __( 'Name field should not be left empty', 'erp' ) );
			return;
		}
		onSubmit( { name, description: form.description.trim() } );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ editing ? __( 'Edit Leave Type', 'erp' ) : __( 'Add Leave Type', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Leave types are the categories employees can request leave against (e.g. Casual, Sick).', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<TextField
						id="leave_type_name"
						label={ __( 'Name', 'erp' ) }
						required
						value={ form.name }
						onChange={ ( v ) => {
							setForm( ( p ) => ( { ...p, name: v } ) );
							setNameErr( '' );
						} }
						error={ nameErr }
						maxLength={ 255 }
					/>
					<TextareaField
						id="leave_type_description"
						label={ __( 'Description', 'erp' ) }
						value={ form.description }
						onChange={ ( v ) => setForm( ( p ) => ( { ...p, description: v } ) ) }
						rows={ 3 }
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
						<Button type="submit" className="h-10 px-6" disabled={ busy }>
							{ busy
								? __( 'Saving…', 'erp' )
								: editing
								? __( 'Update Leave Type', 'erp' )
								: __( 'Create Leave Type', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
