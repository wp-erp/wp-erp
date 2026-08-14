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
	Checkbox,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	RichTextEditor,
	SmartMultiSelect,
} from '@wedevs/plugin-ui';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { FieldSourceAction } from '@/shared/components/FieldSourceLink';
import { __ } from '@/shared/i18n';

import { SelectField, TextField } from '../employee-create/fields';
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
	publishDate:  string;
	sendPush:     boolean;
	sendSms:      boolean;
	smsContent:   string;
}

const EMPTY: FormState = {
	title:        '',
	content:      '',
	status:       'publish',
	assignType:   'all_employee',
	employees:    [],
	departments:  [],
	designations: [],
	publishDate:  '',
	sendPush:     false,
	sendSms:      false,
	smsContent:   '',
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
						// `Y-m-d H:i:s` → the `datetime-local` the input wants.
						publishDate:  ( editing.publish_date ?? '' ).slice( 0, 16 ).replace( ' ', 'T' ),
						sendPush:     editing.send_push === true,
						sendSms:      editing.send_sms === true,
						smsContent:   editing.sms_content ?? '',
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
			publish_date: form.publishDate ? `${ form.publishDate.replace( 'T', ' ' ) }:00` : '',
			send_push:    form.sendPush,
			send_sms:     form.sendSms,
			sms_content:  form.smsContent,
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

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4" noValidate>
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
					{ /* Legacy used the WP editor here; React shipped a plain textarea,
					     so an announcement could not carry any formatting. The design
					     system's own editor is already used by recruitment and
					     workflow, and the controller was built for it — it returns
					     `content` raw for the editor to bind to and a separate
					     KSES'd `html_content` for display. */ }
					<div className="flex min-w-0 flex-col gap-2.5">
						<label className="text-sm font-medium text-foreground" htmlFor="announcement_content">
							{ __( 'Content', 'erp' ) }
						</label>
						<RichTextEditor
							variant="full"
							value={ form.content }
							onChange={ ( v ) => setForm( ( p ) => ( { ...p, content: v } ) ) }
							placeholder={ __( 'Write your announcement…', 'erp' ) }
						/>
					</div>

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
							<div className="flex min-h-[1.25rem] items-center justify-between gap-2">
							<span className="text-sm font-medium text-foreground">{ __( 'Departments', 'erp' ) }</span>
							<FieldSourceAction source="departments" />
						</div>
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
							<div className="flex min-h-[1.25rem] items-center justify-between gap-2">
							<span className="text-sm font-medium text-foreground">{ __( 'Designations', 'erp' ) }</span>
							<FieldSourceAction source="designations" />
						</div>
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

					{ /* Scheduling. WordPress parks a future-dated post under `future` and
					     publishes it on cron, which fires the same assignment hook the
					     e-mail and push run off — so this needs no queue of its own.
					     Hidden for a draft, which ignores the date exactly as legacy did. */ }
					{ form.status === 'publish' ? (
						<div className="flex flex-col gap-2.5">
							<label htmlFor="announcement_publish_date" className="text-sm font-medium text-foreground">
								{ __( 'Publish date', 'erp' ) }
							</label>
							<input
								id="announcement_publish_date"
								type="datetime-local"
								value={ form.publishDate }
								onChange={ ( e ) => setForm( ( p ) => ( { ...p, publishDate: e.target.value } ) ) }
								className="h-10 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
							/>
							<p className="text-xs text-muted-foreground">
								{ __( 'Leave empty to publish now. A future date schedules the announcement.', 'erp' ) }
							</p>
						</div>
					) : null }

					{ /* Delivery channels. Both were legacy-metabox-only until now, so a
					     React save could not turn either on — the announcement went out
					     by e-mail alone. The senders run off the same meta the server
					     writes from these fields. SMS only renders where the pro module
					     says the channel exists. */ }
					{ options?.channels?.push || options?.channels?.sms ? (
						<div className="flex flex-col gap-2.5 rounded-md border border-border bg-muted/20 px-4 py-3">
							<span className="text-sm font-medium text-foreground">{ __( 'Also deliver as', 'erp' ) }</span>

							{ options?.channels?.push ? (
								<label className="flex items-center gap-2 text-sm text-foreground">
									<Checkbox
										checked={ form.sendPush }
										onCheckedChange={ ( v: boolean ) => setForm( ( p ) => ( { ...p, sendPush: v === true } ) ) }
									/>
									{ __( 'Push notification', 'erp' ) }
								</label>
							) : null }

							{ options?.channels?.sms ? (
								<label className="flex items-center gap-2 text-sm text-foreground">
									<Checkbox
										checked={ form.sendSms }
										onCheckedChange={ ( v: boolean ) => setForm( ( p ) => ( { ...p, sendSms: v === true } ) ) }
									/>
									{ __( 'SMS', 'erp' ) }
								</label>
							) : null }

							{ options?.channels?.sms && form.sendSms ? (
								<div className="flex flex-col gap-2.5">
									<label htmlFor="announcement_sms_content" className="text-sm font-medium text-foreground">
										{ __( 'SMS body', 'erp' ) }
									</label>
									<textarea
										id="announcement_sms_content"
										rows={ 3 }
										value={ form.smsContent }
										onChange={ ( e ) => setForm( ( p ) => ( { ...p, smsContent: e.target.value } ) ) }
										className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
									/>
									<p className="text-xs text-muted-foreground">
										{ __( 'Sent as plain text — the announcement body is not used.', 'erp' ) }
									</p>
								</div>
							) : null }
						</div>
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
