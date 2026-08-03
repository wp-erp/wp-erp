/**
 * Read-only announcement view.
 *
 * The list offered Edit and Trash only, so the body of an announcement could be
 * read only by opening the editor — which a viewer without the manage cap cannot
 * do, and which invites an accidental save. This renders the same content as a
 * plain page, and offers Edit only to someone who could already edit.
 *
 * The body is the ONLY HTML this app injects. It renders `html_content`, which
 * the server produces with `wp_kses_post( wpautop() )` — never the raw `content`
 * the editor binds to.
 */

import {
	Badge,
	Button,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { EmployeeAvatarStack } from '@/shared/components/EmployeeAvatarStack';
import { __ } from '@/shared/i18n';

import { fmt } from './announcements-format';
import type { AnnouncementDetail } from './types';

interface AnnouncementViewDialogProps {
	readonly open:      boolean;
	readonly loading:   boolean;
	readonly item:      AnnouncementDetail | null;
	readonly canManage: boolean;
	readonly onEdit:    () => void;
	readonly onClose:   () => void;
}

/** Published / Draft / Trash, in the reader's language. */
function statusLabel( status: string ): string {
	if ( status === 'draft' ) {
		return __( 'Draft', 'erp' );
	}
	if ( status === 'trash' ) {
		return __( 'Trash', 'erp' );
	}
	return __( 'Published', 'erp' );
}

export function AnnouncementViewDialog( {
	open,
	loading,
	item,
	canManage,
	onEdit,
	onClose,
}: AnnouncementViewDialogProps ): JSX.Element {
	return (
		<Dialog open={ open } onOpenChange={ ( next: boolean ) => { if ( ! next ) { onClose(); } } }>
			<DialogContent className="sm:max-w-2xl">
				<DialogHeader>
					<DialogTitle>{ item?.title || __( '(no title)', 'erp' ) }</DialogTitle>
					<DialogDescription>
						{ item
							? [ item.type_label, item.author, fmt( item.date ) ].filter( Boolean ).join( ' · ' )
							: __( 'Loading…', 'erp' ) }
					</DialogDescription>
				</DialogHeader>

				{ loading || ! item ? (
					<p className="py-6 text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</p>
				) : (
					<div className="flex flex-col gap-4">
						<div className="flex flex-wrap items-center gap-2">
							<Badge variant="secondary">{ statusLabel( item.status ) }</Badge>
							<EmployeeAvatarStack
								people={ item.recipients_preview }
								total={ item.recipient_count }
							/>
						</div>

						{ item.html_content ? (
							<div
								className="max-h-[50vh] overflow-y-auto rounded-md border border-border bg-muted/20 px-4 py-3 text-sm leading-relaxed text-foreground [&_a]:text-primary [&_a]:underline [&_li]:ml-4 [&_li]:list-disc [&_p]:mb-2 last:[&_p]:mb-0"
								// Server-sanitised with wp_kses_post(); see the file header.
								dangerouslySetInnerHTML={ { __html: item.html_content } }
							/>
						) : (
							<p className="text-sm text-muted-foreground">{ __( 'This announcement has no content.', 'erp' ) }</p>
						) }
					</div>
				) }

				<DialogFooter>
					{ canManage && item && item.status !== 'trash' ? (
						<Button variant="outline" onClick={ onEdit }>{ __( 'Edit', 'erp' ) }</Button>
					) : null }
					<Button onClick={ onClose }>{ __( 'Close', 'erp' ) }</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}
