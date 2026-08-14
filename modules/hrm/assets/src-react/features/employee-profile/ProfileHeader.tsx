/**
 * Header card for the Employee Profile layout: avatar, name + status badge,
 * designation/department + email, and an Edit action. Avatar is editable in
 * place (for self / managers) via `AvatarUpload`.
 */

import { Avatar, AvatarFallback, AvatarImage, Badge, Button } from '@wedevs/plugin-ui';
import { Pencil } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { AvatarUpload } from '../employee-create/AvatarUpload';
import { STATUS_OPTIONS } from '../employee-create/options';
import { initials, labelOf, str, statusVariant, type Record_ } from './profile-format';

interface ProfileHeaderProps {
	readonly record:         Record_;
	readonly userId:         number;
	readonly canEdit:        boolean;
	readonly onEdit:         () => void;
	readonly onAvatarChange: ( url: string ) => void;
}

export function ProfileHeader( { record, userId, canEdit, onEdit, onAvatarChange }: ProfileHeaderProps ): JSX.Element {
	const fullName  = str( record, 'full_name' );
	const avatarUrl = str( record, 'avatar_url' );
	const status    = str( record, 'status' );

	return (
		/* Header card — spacing per Figma 1533:26766 (avatar 90, padding 24, name 24px). */
		<section className="flex flex-wrap items-start gap-5 rounded-[10px] bg-card p-6 shadow-sm">
			{ canEdit ? (
				<AvatarUpload
					userId={ userId }
					avatarUrl={ avatarUrl }
					fullName={ fullName }
					initials={ initials( fullName ) }
					onChange={ onAvatarChange }
				/>
			) : (
				<Avatar className="size-[90px] shrink-0">
					{ avatarUrl ? <AvatarImage src={ avatarUrl } alt={ fullName } /> : null }
					<AvatarFallback className="text-xl">{ initials( fullName ) }</AvatarFallback>
				</Avatar>
			) }
			<div className="flex min-w-0 flex-1 flex-col gap-2">
				<div className="flex flex-wrap items-center gap-3">
					<h1 className="text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ fullName || __( 'Employee', 'erp' ) }
					</h1>
					{ status ? (
						<Badge variant={ statusVariant( status ) }>
							{ labelOf( STATUS_OPTIONS, status ) }
						</Badge>
					) : null }
				</div>
				<div className="flex flex-col gap-1">
					{ ( () => {
						const role = [ str( record, 'designation_name' ), str( record, 'department_name' ) ]
							.filter( ( s ) => s.trim() !== '' )
							.join( ' · ' );
						return role ? (
							<p className="truncate text-sm font-semibold text-foreground">{ role }</p>
						) : null;
					} )() }
					<p className="truncate text-sm text-muted-foreground">{ str( record, 'email' ) }</p>
				</div>
			</div>
			<div className="flex items-center gap-2">
				{ canEdit ? (
					<Button
						variant="default"
						size="sm"
						className="h-9 gap-1.5 px-4"
						onClick={ onEdit }
					>
						<Pencil size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
						{ __( 'Edit', 'erp' ) }
					</Button>
				) : null }
			</div>
		</section>
	);
}
