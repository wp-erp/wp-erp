/**
 * Create / edit department dialog.
 *
 * Mirrors the legacy form: title (required), description, department head
 * (lead) and parent department. The model only enforces a non-empty title, so
 * that is the sole client-side requirement; the server re-validates.
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
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

import { SmartSelectField, TextField, TextareaField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import type { Department, DepartmentInput } from './types';

interface DepartmentFormDialogProps {
	readonly open:       boolean;
	readonly editing:    Department | null;
	readonly departments: readonly Department[];
	readonly busy:       boolean;
	readonly error:      string | null;
	readonly onClose:    () => void;
	readonly onSubmit:   ( payload: DepartmentInput ) => void;
}

interface FormState {
	title:       string;
	description: string;
	lead:        string;
	parent:      string;
}

const EMPTY: FormState = { title: '', description: '', lead: '', parent: '' };

export function DepartmentFormDialog( {
	open,
	editing,
	departments,
	busy,
	error,
	onClose,
	onSubmit,
}: DepartmentFormDialogProps ): JSX.Element {
	const [ form, setForm ]       = useState< FormState >( EMPTY );
	const [ titleErr, setTitleErr ] = useState< string >( '' );
	// Server-side employee search for the Department Head picker; seed the
	// already-selected lead so its name still renders when editing.
	const lead = useEmployeeSearch(
		open,
		editing && editing.lead ? { value: String( editing.lead ), label: editing.lead_name } : null,
		form.lead,
	);

	// Prime the form whenever the dialog opens for a fresh target.
	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setTitleErr( '' );
		setForm(
			editing
				? {
						title:       editing.title,
						description: editing.description,
						lead:        editing.lead ? String( editing.lead ) : '',
						parent:      editing.parent ? String( editing.parent ) : '',
				  }
				: EMPTY
		);
	}, [ open, editing ] );


	// Parent options exclude the department being edited (can't parent itself).
	const parentOptions = useMemo< Option[] >(
		() =>
			departments
				.filter( ( d ) => ! editing || d.id !== editing.id )
				.map( ( d ) => ( { value: String( d.id ), label: d.title } ) ),
		[ departments, editing ]
	);

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();
		const title = form.title.trim();
		if ( ! title ) {
			setTitleErr( __( 'Department name is required.', 'erp' ) );
			return;
		}
		const payload: DepartmentInput = {
			title,
			description: form.description.trim(),
			lead:        form.lead ? Number( form.lead ) : 0,
			parent:      form.parent ? Number( form.parent ) : 0,
		};
		onSubmit( payload );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ editing ? __( 'Edit Department', 'erp' ) : __( 'Add Department', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Departments group employees and can have a head and a parent.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<TextField
						id="dept_title"
						label={ __( 'Department Name', 'erp' ) }
						required
						value={ form.title }
						onChange={ ( v ) => {
							setForm( ( p ) => ( { ...p, title: v } ) );
							setTitleErr( '' );
						} }
						error={ titleErr }
						maxLength={ 200 }
					/>
					<SmartSelectField
						id="dept_lead"
						label={ __( 'Department Head', 'erp' ) }
						options={ lead.options }
						value={ form.lead }
						onChange={ ( v ) => setForm( ( p ) => ( { ...p, lead: v } ) ) }
						onSearch={ lead.onSearch }
						loading={ lead.loading }
						placeholder={ __( '- Select -', 'erp' ) }
						searchPlaceholder={ __( 'Search employees…', 'erp' ) }
						emptyMessage={ __( 'No employees found.', 'erp' ) }
					/>
					<SmartSelectField
						id="dept_parent"
						label={ __( 'Parent Department', 'erp' ) }
						options={ parentOptions }
						value={ form.parent }
						onChange={ ( v ) => setForm( ( p ) => ( { ...p, parent: v } ) ) }
						placeholder={ __( '- None -', 'erp' ) }
						searchPlaceholder={ __( 'Search departments…', 'erp' ) }
						emptyMessage={ __( 'No departments found.', 'erp' ) }
					/>
					<TextareaField
						id="dept_description"
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
								? __( 'Update Department', 'erp' )
								: __( 'Create Department', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
