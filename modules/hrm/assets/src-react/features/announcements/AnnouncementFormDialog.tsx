/**
 * Create / edit announcement dialog.
 *
 * Mirrors the legacy announcement editor + recipient metabox: title, body,
 * publish/draft status, and a recipient strategy (all / by department / by
 * designation / selected employees) with the matching multi-select. The server
 * runs the same `erp_hr_assign_announcements_to_employees()` assignment.
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
	SmartMultiSelect,
} from '@wedevs/plugin-ui';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { SelectField, TextField, TextareaField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import type {
	AnnouncementAssignType,
	AnnouncementDetail,
	AnnouncementFormOptions,
	AnnouncementInput,
} from './types';

interface AnnouncementFormDialogProps {
	readonly open:     boolean;
	readonly editing:  AnnouncementDetail | null;
	readonly options:  AnnouncementFormOptions | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: AnnouncementInput ) => void;
}

interface FormState {
	title:        string;
	content:      string;
	status:       'publish' | 'draft';
	assignType:   AnnouncementAssignType;
	employees:    string[];
	departments:  string[];
	designations: string[];
}

const EMPTY: FormState = {
	title:        '',
	content:      '',
	status:       'publish',
	assignType:   'all_employee',
	employees:    [],
	departments:  [],
	designations: [],
};

const STATUS_OPTS: Option[] = [
	{ value: 'publish', label: __( 'Publish', 'erp' ) },
	{ value: 'draft', label: __( 'Draft', 'erp' ) },
];

export function AnnouncementFormDialog( {
	open,
	editing,
	options,
	busy,
	error,
	onClose,
	onSubmit,
}: AnnouncementFormDialogProps ): JSX.Element {
	const [ form, setForm ]       = useState< FormState >( EMPTY );
	const [ titleErr, setTitleErr ] = useState( '' );
	const [ recipientErr, setRecipientErr ] = useState( '' );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setTitleErr( '' );
		setForm(
			editing
				? {
						title:        editing.title,
						content:      editing.content,
						status:       editing.status === 'draft' ? 'draft' : 'publish',
						assignType:   ( editing.type || 'all_employee' ) as AnnouncementAssignType,
						employees:    ( editing.recipients?.employees ?? [] ).map( String ),
						departments:  ( editing.recipients?.departments ?? [] ).map( String ),
						designations: ( editing.recipients?.designations ?? [] ).map( String ),
				  }
				: EMPTY
		);
	}, [ open, editing ] );

	const assignTypeOpts = useMemo< Option[] >(
		() => ( options?.assignTypes ?? [] ).map( ( a ) => ( { value: a.value, label: a.label } ) ),
		[ options ]
	);
	const deptMulti = useMemo(
		() => ( options?.departments ?? [] ).map( ( d ) => ( { value: String( d.id ), label: String( d.title ?? '' ) } ) ),
		[ options ]
	);
	const desigMulti = useMemo(
		() => ( options?.designations ?? [] ).map( ( d ) => ( { value: String( d.id ), label: String( d.title ?? '' ) } ) ),
		[ options ]
	);
	const empMulti = useMemo(
		() => ( options?.employees ?? [] ).map( ( e ) => ( { value: String( e.id ), label: String( e.name ?? '' ) } ) ),
		[ options ]
	);

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();
		const title = form.title.trim();
		if ( ! title ) {
			setTitleErr( __( 'Title is required.', 'erp' ) );
			return;
		}

		// A targeted audience must actually select at least one recipient
		// (the legacy assign step silently no-ops on an empty target).
		if ( form.assignType === 'by_department' && form.departments.length === 0 ) {
			setRecipientErr( __( 'Select at least one department.', 'erp' ) );
			return;
		}
		if ( form.assignType === 'by_designation' && form.designations.length === 0 ) {
			setRecipientErr( __( 'Select at least one designation.', 'erp' ) );
			return;
		}
		if ( form.assignType === 'selected_employee' && form.employees.length === 0 ) {
			setRecipientErr( __( 'Select at least one employee.', 'erp' ) );
			return;
		}

		onSubmit( {
			title,
			content:      form.content,
			status:       form.status,
			assign_type:  form.assignType,
			employees:    form.employees.map( Number ),
			departments:  form.departments.map( Number ),
			designations: form.designations.map( Number ),
		} );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-2xl">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ editing ? __( 'Edit Announcement', 'erp' ) : __( 'New Announcement', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Publish a notice to all employees or a targeted group. Published announcements e-mail their recipients.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<TextField
						id="announcement_title"
						label={ __( 'Title', 'erp' ) }
						required
						value={ form.title }
						onChange={ ( v ) => {
							setForm( ( p ) => ( { ...p, title: v } ) );
							setTitleErr( '' );
						} }
						error={ titleErr }
					/>
					<TextareaField
						id="announcement_content"
						label={ __( 'Content', 'erp' ) }
						value={ form.content }
						onChange={ ( v ) => setForm( ( p ) => ( { ...p, content: v } ) ) }
						rows={ 6 }
					/>

					<div className="grid grid-cols-2 gap-4">
						<SelectField
							id="announcement_status"
							label={ __( 'Status', 'erp' ) }
							options={ STATUS_OPTS }
							value={ form.status }
							onChange={ ( v ) => setForm( ( p ) => ( { ...p, status: v === 'draft' ? 'draft' : 'publish' } ) ) }
						/>
						<SelectField
							id="announcement_assign_type"
							label={ __( 'Send To', 'erp' ) }
							options={ assignTypeOpts }
							value={ form.assignType }
							onChange={ ( v ) => { setForm( ( p ) => ( { ...p, assignType: v as AnnouncementAssignType } ) ); setRecipientErr( '' ); } }
						/>
					</div>

					{ form.assignType === 'by_department' ? (
						<div className="flex flex-col gap-2.5">
							<span className="text-sm font-medium text-foreground">{ __( 'Departments', 'erp' ) }</span>
							<SmartMultiSelect
								options={ deptMulti }
								value={ form.departments }
								onValueChange={ ( v ) => { setForm( ( p ) => ( { ...p, departments: v } ) ); setRecipientErr( '' ); } }
								placeholder={ __( 'Select departments…', 'erp' ) }
							/>
							{ recipientErr ? <p className="text-xs text-destructive">{ recipientErr }</p> : null }
						</div>
					) : null }

					{ form.assignType === 'by_designation' ? (
						<div className="flex flex-col gap-2.5">
							<span className="text-sm font-medium text-foreground">{ __( 'Designations', 'erp' ) }</span>
							<SmartMultiSelect
								options={ desigMulti }
								value={ form.designations }
								onValueChange={ ( v ) => { setForm( ( p ) => ( { ...p, designations: v } ) ); setRecipientErr( '' ); } }
								placeholder={ __( 'Select designations…', 'erp' ) }
							/>
							{ recipientErr ? <p className="text-xs text-destructive">{ recipientErr }</p> : null }
						</div>
					) : null }

					{ form.assignType === 'selected_employee' ? (
						<div className="flex flex-col gap-2.5">
							<span className="text-sm font-medium text-foreground">{ __( 'Employees', 'erp' ) }</span>
							<SmartMultiSelect
								options={ empMulti }
								value={ form.employees }
								onValueChange={ ( v ) => { setForm( ( p ) => ( { ...p, employees: v } ) ); setRecipientErr( '' ); } }
								placeholder={ __( 'Select employees…', 'erp' ) }
							/>
							{ recipientErr ? <p className="text-xs text-destructive">{ recipientErr }</p> : null }
						</div>
					) : null }

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
								? __( 'Update', 'erp' )
								: __( 'Publish', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
