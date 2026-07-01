/**
 * Approve / reject confirmation dialog with an optional reason.
 *
 * Mirrors the legacy approve/reject flow (`leave_approve()`/`leave_reject()`):
 * both accept an optional comment shown to the employee. Purely presentational
 * + controlled — the parent owns busy state and the actual mutation.
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

import { TextareaField } from '../employee-create/fields';
import type { LeaveRequest } from './types';

interface LeaveRequestModerateDialogProps {
	readonly open:      boolean;
	readonly action:    'approve' | 'reject';
	readonly request:   LeaveRequest | null;
	readonly busy:      boolean;
	readonly error:     string | null;
	readonly onConfirm: ( reason: string ) => void;
	readonly onCancel:  () => void;
}

export function LeaveRequestModerateDialog( {
	open,
	action,
	request,
	busy,
	error,
	onConfirm,
	onCancel,
}: LeaveRequestModerateDialogProps ): JSX.Element {
	const [ reason, setReason ] = useState( '' );

	useEffect( () => {
		if ( open ) {
			setReason( '' );
		}
	}, [ open ] );

	const isReject     = action === 'reject';
	const who          = request?.name ?? '';
	// Legacy hard-rejects an empty reason on reject (functions-leave.php:1695).
	const reasonMissing = isReject && reason.trim() === '';

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onCancel() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ isReject ? __( 'Reject leave request', 'erp' ) : __( 'Approve leave request', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ who
							? sprintf(
									isReject
										? __( 'Reject %1$s’s %2$s request? You can add a note explaining why.', 'erp' )
										: __( 'Approve %1$s’s %2$s request? You can add an optional note.', 'erp' ),
									who,
									request?.policy_name ?? ''
							  )
							: '' }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<div>
					<TextareaField
						id="leave_request_reason"
						label={ isReject ? __( 'Reason', 'erp' ) : __( 'Note (optional)', 'erp' ) }
						value={ reason }
						onChange={ setReason }
						rows={ 3 }
					/>
					{ reasonMissing ? (
						<p className="mt-1.5 text-sm text-destructive">
							{ __( 'A reason is required to reject this request.', 'erp' ) }
						</p>
					) : null }
				</div>

				{ error ? (
					<Alert variant="destructive">
						<AlertDescription>{ error }</AlertDescription>
					</Alert>
				) : null }

				<DialogFooter className="gap-5 sm:gap-5">
					<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onCancel }>
						{ __( 'Cancel', 'erp' ) }
					</Button>
					<Button
						type="button"
						variant={ isReject ? 'destructive' : 'default' }
						className="h-10 px-6"
						disabled={ busy || reasonMissing }
						onClick={ () => onConfirm( reason.trim() ) }
					>
						{ busy
							? __( 'Saving…', 'erp' )
							: isReject
							? __( 'Reject', 'erp' )
							: __( 'Approve', 'erp' ) }
					</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}
