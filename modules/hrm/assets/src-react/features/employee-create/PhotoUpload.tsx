/**
 * Create-mode employee photo picker.
 *
 * The single-employee AvatarUpload posts a file to `/employees/{id}/avatar`,
 * which needs an existing employee. On the create form there is no employee yet,
 * so — mirroring the legacy Vue `personal[photo_id]` flow — this uploads the file
 * to the core media library (`POST /wp/v2/media`) and lifts the resulting
 * attachment id up as `photo_id`. `Employee::create_employee()` stores it as the
 * `photo_id` user meta (same meta the old form wrote).
 *
 * Visual: compact left-aligned header row — a hover-to-upload avatar (mirroring
 * the single-employee AvatarUpload) beside a label + Upload / Remove actions.
 */

import apiFetch from '@wordpress/api-fetch';
import { Avatar, AvatarFallback, AvatarImage, Button, Spinner, toast } from '@wedevs/plugin-ui';
import { Camera, Trash2, Upload } from 'lucide-react';
import { useRef, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

interface MediaResponse {
	readonly id:         number;
	readonly source_url: string;
}

interface PhotoUploadProps {
	readonly avatarUrl: string;
	readonly fullName:  string;
	readonly initials:  string;
	/** Lifts the uploaded attachment id (0 when removed) + its preview URL. */
	readonly onChange:  ( photoId: number, avatarUrl: string ) => void;
	/** Circle-only variant for the profile-header card (no label/buttons). */
	readonly compact?:  boolean;
}

export function PhotoUpload( { avatarUrl, fullName, initials, onChange, compact }: PhotoUploadProps ): JSX.Element {
	const inputRef = useRef< HTMLInputElement >( null );
	const [ busy, setBusy ] = useState( false );

	async function handleFile( file: File ): Promise< void > {
		setBusy( true );
		try {
			const form = new FormData();
			form.append( 'file', file );
			const body = await apiFetch< MediaResponse >( {
				path:   '/wp/v2/media',
				method: 'POST',
				body:   form,
			} );
			onChange( body.id, body.source_url ?? '' );
			toast.success( __( 'Photo uploaded.', 'erp' ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not upload the photo.', 'erp' ) );
		} finally {
			setBusy( false );
			if ( inputRef.current ) {
				inputRef.current.value = '';
			}
		}
	}

	const fileInput = (
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
	);

	const avatarButton = (
		<button
			type="button"
			onClick={ () => inputRef.current?.click() }
			disabled={ busy }
			aria-label={ __( 'Upload photo', 'erp' ) }
			className="group relative size-20 shrink-0 rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
		>
			<Avatar className="size-20">
				{ avatarUrl ? <AvatarImage src={ avatarUrl } alt={ fullName } /> : null }
				<AvatarFallback className="text-lg">{ initials }</AvatarFallback>
			</Avatar>
			<span className="absolute inset-0 flex items-center justify-center rounded-full bg-black/50 opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">
				{ busy ? (
					<Spinner className="size-5 text-white" />
				) : (
					<Camera size={ 18 } className="text-white" aria-hidden="true" />
				) }
			</span>
		</button>
	);

	if ( compact ) {
		return (
			<div className="shrink-0">
				{ avatarButton }
				{ fileInput }
			</div>
		);
	}

	return (
		<div className="flex items-center gap-5">
			{ /* Hover-to-upload avatar — mirrors the single-employee AvatarUpload. */ }
			{ avatarButton }

			{ fileInput }

			<div className="min-w-0">
				<h3 className="mt-0 mb-0.5 text-sm font-semibold text-foreground">
					{ __( 'Profile Photo', 'erp' ) }
				</h3>
				<p className="mt-0 mb-2 text-xs text-muted-foreground">
					{ __( 'JPG or PNG, square image recommended.', 'erp' ) }
				</p>
				<div className="flex items-center gap-2">
					<Button
						type="button"
						variant="outline"
						size="sm"
						className="h-6 gap-1 px-2 text-[11px] leading-none [&_svg]:size-3"
						disabled={ busy }
						onClick={ () => inputRef.current?.click() }
					>
						<Upload size={ 12 } aria-hidden="true" />
						{ avatarUrl ? __( 'Change', 'erp' ) : __( 'Upload Photo', 'erp' ) }
					</Button>
					{ avatarUrl && ! busy ? (
						<Button
							type="button"
							variant="outline"
							size="sm"
							className="h-6 gap-1 border-destructive px-2 text-[11px] leading-none text-destructive [&_svg]:size-3 hover:bg-destructive/10 hover:text-destructive"
							onClick={ () => onChange( 0, '' ) }
						>
							<Trash2 size={ 12 } aria-hidden="true" />
							{ __( 'Remove', 'erp' ) }
						</Button>
					) : null }
				</div>
			</div>
		</div>
	);
}
