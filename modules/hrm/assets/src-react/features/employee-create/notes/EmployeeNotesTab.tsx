/**
 * Notes tab for the single-employee profile.
 *
 * Add a note (textarea + submit), list existing notes newest-first with the
 * author avatar + timestamp, and delete a note behind a confirm dialog. All
 * writes go through `useEmployeeNotes`, which hits the v2 endpoint that
 * delegates to the unchanged v1 `Employee` model. Capability gating mirrors the
 * server: `erp_manage_review` to add, `erp_edit_employee` to delete.
 */

import { Avatar, AvatarFallback, AvatarImage, Button, Spinner, Textarea, toast } from '@wedevs/plugin-ui';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { OrgDeleteDialog } from '@/features/org/OrgDeleteDialog';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { useEmployeeNotes } from './useEmployeeNotes';
import type { EmployeeNote } from './useEmployeeNotes';

function initials( name: string ): string {
	const parts = name.trim().split( /\s+/ ).filter( Boolean );
	if ( parts.length === 0 ) {
		return '?';
	}
	const first = parts[ 0 ]?.[ 0 ] ?? '';
	const last  = parts.length > 1 ? parts[ parts.length - 1 ]?.[ 0 ] ?? '' : '';
	return ( first + last ).toUpperCase();
}

function formatDate( iso: string ): string {
	const date = new Date( iso );
	if ( Number.isNaN( date.getTime() ) ) {
		return '';
	}
	return (
		date.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } ) +
		' · ' +
		date.toLocaleTimeString( undefined, { hour: '2-digit', minute: '2-digit' } )
	);
}

export function EmployeeNotesTab( { userId }: { readonly userId: number } ): JSX.Element {
	const { notes, loading, error, addNote, removeNote } = useEmployeeNotes( userId );
	const canManage = useCan( 'erp_manage_review' );
	const canDelete = useCan( 'erp_edit_employee' );

	const [ draft, setDraft ]           = useState( '' );
	const [ submitting, setSubmitting ] = useState( false );
	const [ deleting, setDeleting ]     = useState< EmployeeNote | null >( null );
	const [ busy, setBusy ]             = useState( false );

	async function handleAdd( event: FormEvent< HTMLFormElement > ): Promise< void > {
		event.preventDefault();
		const comment = draft.trim();
		if ( comment === '' ) {
			return;
		}
		setSubmitting( true );
		try {
			await addNote( comment );
			setDraft( '' );
			toast.success( __( 'Note added.', 'erp' ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not add the note.', 'erp' ) );
		} finally {
			setSubmitting( false );
		}
	}

	async function handleDelete(): Promise< void > {
		if ( ! deleting ) {
			return;
		}
		setBusy( true );
		try {
			await removeNote( deleting.id );
			toast.success( __( 'Note deleted.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the note.', 'erp' ) );
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	return (
		<div className="space-y-5">
			<section className="overflow-hidden rounded-[10px] bg-card shadow-sm">
				<header className="border-b border-border px-6 py-4">
					<h2 className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Notes', 'erp' ) }</h2>
				</header>
				<div className="space-y-5 p-5">
			{ canManage ? (
				<form onSubmit={ ( e ) => void handleAdd( e ) } className="rounded-lg border border-border bg-card p-4 shadow-sm">
					<Textarea
						value={ draft }
						onChange={ ( e ) => setDraft( e.target.value ) }
						placeholder={ __( 'Write a note about this employee…', 'erp' ) }
						rows={ 3 }
						className="resize-none"
						aria-label={ __( 'New note', 'erp' ) }
					/>
					<div className="mt-3 flex justify-end">
						<Button type="submit" size="sm" className="h-9 px-4" disabled={ submitting || draft.trim() === '' }>
							{ submitting ? __( 'Adding…', 'erp' ) : __( 'Add Note', 'erp' ) }
						</Button>
					</div>
				</form>
			) : null }

			{ error ? (
				<p className="rounded-lg border border-border bg-card p-6 text-sm text-destructive">{ error }</p>
			) : loading ? (
				<div className="flex items-center justify-center gap-2 rounded-lg border border-border bg-card p-10 text-sm text-muted-foreground">
					<Spinner className="size-4" />
					{ __( 'Loading notes…', 'erp' ) }
				</div>
			) : notes.length === 0 ? (
				<p className="rounded-lg border border-border bg-card p-10 text-center text-sm text-muted-foreground">
					{ __( 'No notes yet.', 'erp' ) }
				</p>
			) : (
				<ul className="space-y-3">
					{ notes.map( ( note ) => (
						<li
							key={ note.id }
							className="flex gap-3 rounded-lg border border-border bg-card p-4 shadow-sm"
						>
							<Avatar className="size-9 shrink-0">
								{ note.author_avatar_url ? (
									<AvatarImage src={ note.author_avatar_url } alt={ note.author_name } />
								) : null }
								<AvatarFallback>{ initials( note.author_name ) }</AvatarFallback>
							</Avatar>
							<div className="min-w-0 flex-1">
								<div className="flex items-start justify-between gap-2">
									<span className="text-sm font-medium text-foreground">
										{ note.author_name || __( 'Unknown', 'erp' ) }
									</span>
									<div className="flex shrink-0 items-center gap-2">
										{ note.created_at ? (
											<time className="text-xs text-muted-foreground">
												{ formatDate( note.created_at ) }
											</time>
										) : null }
										{ canDelete ? (
											<Button
												variant="ghost"
												size="icon-sm"
												className="text-destructive hover:text-destructive focus:text-destructive"
												aria-label={ __( 'Delete note', 'erp' ) }
												onClick={ () => setDeleting( note ) }
											>
												<Trash2 size={ 14 } aria-hidden="true" />
											</Button>
										) : null }
									</div>
								</div>
								<p className="mt-1 whitespace-pre-line text-sm text-foreground">{ note.comment }</p>
							</div>
						</li>
					) ) }
				</ul>
			) }
				</div>
			</section>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ __( 'Delete note?', 'erp' ) }
				description={ __( 'This note will be permanently deleted.', 'erp' ) }
				busy={ busy }
				onConfirm={ () => void handleDelete() }
				onCancel={ () => setDeleting( null ) }
			/>
		</div>
	);
}
