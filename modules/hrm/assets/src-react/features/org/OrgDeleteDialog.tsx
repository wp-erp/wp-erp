/**
 * Reusable delete-confirmation AlertDialog for the HR taxonomy pages.
 *
 * Purely presentational + controlled: the parent owns the open state, the busy
 * flag and the actual delete call (so it can surface the server-side "contains
 * employees" guard as a toast).
 */

import {
	AlertDialog,
	AlertDialogAction,
	AlertDialogCancel,
	AlertDialogContent,
	AlertDialogDescription,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogTitle,
} from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

interface OrgDeleteDialogProps {
	readonly open:        boolean;
	readonly title:       string;
	readonly description: string;
	readonly busy:        boolean;
	readonly onConfirm:   () => void;
	readonly onCancel:    () => void;
}

export function OrgDeleteDialog( {
	open,
	title,
	description,
	busy,
	onConfirm,
	onCancel,
}: OrgDeleteDialogProps ): JSX.Element {
	return (
		<AlertDialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onCancel() ) }>
			<AlertDialogContent>
				<AlertDialogHeader>
					<AlertDialogTitle>{ title }</AlertDialogTitle>
					<AlertDialogDescription>{ description }</AlertDialogDescription>
				</AlertDialogHeader>
				<AlertDialogFooter>
					<AlertDialogCancel disabled={ busy } onClick={ onCancel }>
						{ __( 'Cancel', 'erp' ) }
					</AlertDialogCancel>
					<AlertDialogAction variant="destructive" disabled={ busy } onClick={ onConfirm }>
						{ busy ? __( 'Deleting…', 'erp' ) : __( 'Delete', 'erp' ) }
					</AlertDialogAction>
				</AlertDialogFooter>
			</AlertDialogContent>
		</AlertDialog>
	);
}
