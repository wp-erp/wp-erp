/**
 * Create / edit designation dialog.
 *
 * Mirrors the legacy form: title (required) + description. The model only
 * enforces a non-empty title; the server re-validates.
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
import type { Designation, DesignationInput } from './types';

interface DesignationFormDialogProps {
	readonly open:     boolean;
	readonly editing:  Designation | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: DesignationInput ) => void;
}

interface FormState {
	title:       string;
	description: string;
}

const EMPTY: FormState = { title: '', description: '' };

export function DesignationFormDialog( {
	open,
	editing,
	busy,
	error,
	onClose,
	onSubmit,
}: DesignationFormDialogProps ): JSX.Element {
	const [ form, setForm ]         = useState< FormState >( EMPTY );
	const [ titleErr, setTitleErr ] = useState< string >( '' );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setTitleErr( '' );
		setForm(
			editing
				? { title: editing.title, description: editing.description }
				: EMPTY
		);
	}, [ open, editing ] );

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();
		const title = form.title.trim();
		if ( ! title ) {
			setTitleErr( __( 'Designation name is required.', 'erp' ) );
			return;
		}
		onSubmit( { title, description: form.description.trim() } );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ editing ? __( 'Edit Designation', 'erp' ) : __( 'Add Designation', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Designations are the job titles employees can hold.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<TextField
						id="desig_title"
						label={ __( 'Designation Name', 'erp' ) }
						required
						value={ form.title }
						onChange={ ( v ) => {
							setForm( ( p ) => ( { ...p, title: v } ) );
							setTitleErr( '' );
						} }
						error={ titleErr }
						maxLength={ 200 }
					/>
					<TextareaField
						id="desig_description"
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
								? __( 'Update Designation', 'erp' )
								: __( 'Create Designation', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
