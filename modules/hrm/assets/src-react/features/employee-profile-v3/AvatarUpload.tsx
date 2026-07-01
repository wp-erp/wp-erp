/**
 * Hover-to-edit employee profile photo.
 *
 * Shows the 90px avatar; on hover (or keyboard focus) overlays Upload + Remove
 * actions. Uploads a multipart `photo` to the v2 avatar route (which mirrors the
 * legacy `personal[photo_id]` media write), removes via DELETE. Optimistically
 * lifts the new `avatar_url` to the parent so the header updates instantly.
 *
 * Gated by `erp_edit_employee` (the parent only renders this when the user can
 * edit); the buttons themselves stay keyboard-reachable.
 */

import apiFetch from '@wordpress/api-fetch';
import { Avatar, AvatarFallback, AvatarImage, Button, Spinner, toast } from '@wedevs/plugin-ui';
import { Camera, Trash2 } from 'lucide-react';
import { useRef, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

import { OrgDeleteDialog } from '../org/OrgDeleteDialog';

interface AvatarResponse {
	readonly photo_id:   number | null;
	readonly avatar_url: string | null;
}

interface AvatarUploadProps {
	readonly userId:    number;
	readonly avatarUrl: string;
	readonly fullName:  string;
	readonly initials:  string;
	readonly onChange:  ( avatarUrl: string ) => void;
}

export function AvatarUpload( { userId, avatarUrl, fullName, initials, onChange }: AvatarUploadProps ): JSX.Element {
	const inputRef = useRef< HTMLInputElement >( null );
	const [ busy, setBusy ] = useState( false );
	const [ confirmOpen, setConfirmOpen ] = useState( false );

	async function handleFile( file: File ): Promise< void > {
		setBusy( true );
		try {
			const form = new FormData();
			form.append( 'photo', file );
			const body = await apiFetch< AvatarResponse >( {
				path:   restPath( 'v2', `/employees/${ userId }/avatar` ),
				method: 'POST',
				body:   form,
			} );
			onChange( body.avatar_url ?? '' );
			toast.success( __( 'Photo updated.', 'erp' ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not upload the photo.', 'erp' ) );
		} finally {
			setBusy( false );
			if ( inputRef.current ) {
				inputRef.current.value = '';
			}
		}
	}

	async function handleRemove(): Promise< void > {
		setBusy( true );
		try {
			const body = await apiFetch< AvatarResponse >( {
				path:   restPath( 'v2', `/employees/${ userId }/avatar` ),
				method: 'DELETE',
			} );
			onChange( body.avatar_url ?? '' );
			toast.success( __( 'Photo removed.', 'erp' ) );
			setConfirmOpen( false );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not remove the photo.', 'erp' ) );
			setConfirmOpen( false );
		} finally {
			setBusy( false );
		}
	}

	return (
		<div className="group relative size-[90px] shrink-0">
			<Avatar className="size-[90px]">
				{ avatarUrl ? <AvatarImage src={ avatarUrl } alt={ fullName } /> : null }
				<AvatarFallback className="text-xl">{ initials }</AvatarFallback>
			</Avatar>

			<input
				ref={ inputRef }
				type="file"
				accept="image/*"
				className="hidden"
				onChange={ ( e ) => {
					const file = e.currentTarget.files?.[ 0 ];
					if ( file ) {
						void handleFile( file );
					}
				} }
			/>

			{ /* Hover/focus overlay with Upload + Remove. */ }
			<div className="pointer-events-none absolute inset-0 flex items-center justify-center gap-1 rounded-full bg-black/50 opacity-0 transition-opacity group-hover:opacity-100 focus-within:opacity-100">
				{ busy ? (
					<Spinner className="size-5 text-white" />
				) : (
					<>
						<Button
							type="button"
							variant="ghost"
							size="icon"
							className="pointer-events-auto inline-flex size-8 items-center justify-center rounded-full bg-white/90 text-foreground hover:bg-white"
							onClick={ () => inputRef.current?.click() }
							aria-label={ __( 'Upload photo', 'erp' ) }
							title={ __( 'Upload photo', 'erp' ) }
						>
							<Camera size={ 16 } aria-hidden="true" />
						</Button>
						{ avatarUrl ? (
							<Button
								type="button"
								variant="ghost"
								size="icon"
								className="pointer-events-auto inline-flex size-8 items-center justify-center rounded-full bg-white/90 text-destructive hover:bg-white"
								onClick={ () => setConfirmOpen( true ) }
								aria-label={ __( 'Remove photo', 'erp' ) }
								title={ __( 'Remove photo', 'erp' ) }
							>
								<Trash2 size={ 16 } aria-hidden="true" />
							</Button>
						) : null }
					</>
				) }
			</div>

			<OrgDeleteDialog
				open={ confirmOpen }
				title={ __( 'Remove profile photo?', 'erp' ) }
				description={ __( 'The current profile photo will be removed. This cannot be undone.', 'erp' ) }
				busy={ busy }
				onConfirm={ () => void handleRemove() }
				onCancel={ () => setConfirmOpen( false ) }
			/>
		</div>
	);
}
