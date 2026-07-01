/**
 * Header card for the Employee Profile v4 layout: compact avatar-left, name +
 * status badge, an Edit action, and a summary info row of key facts. Avatar is
 * editable in place (for self / managers) via `AvatarUpload`.
 */

import { Avatar, AvatarFallback, AvatarImage, Badge, Button } from '@wedevs/plugin-ui';
import { Activity, Building2, IdCard, Pencil, Printer, Tag, UserX } from 'lucide-react';
import type { JSX, ReactNode } from 'react';

import { __ } from '@/shared/i18n';

import { AvatarUpload } from '../employee-profile/AvatarUpload';
import { STATUS_OPTIONS } from '../employee-profile/options';
import { initials, labelOf, str, statusVariant, type Record_ } from './profile-format';

interface ProfileHeaderProps {
	readonly record:         Record_;
	readonly userId:         number;
	readonly canEdit:        boolean;
	readonly onEdit:         () => void;
	readonly onAvatarChange: ( url: string ) => void;
	/** Extra header actions rendered beside Edit (e.g. self-service request buttons). */
	readonly extraActions?:  ReactNode;
	/** Print the profile (browser print). Hidden when omitted. */
	readonly onPrint?:       () => void;
	/** Open the terminate dialog. Rendered only when both this and `canTerminate` are set. */
	readonly onTerminate?:   () => void;
	/** Whether the current user may terminate this employee (manager, active, not self). */
	readonly canTerminate?:  boolean;
}

export function ProfileHeader( { record, userId, canEdit, onEdit, onAvatarChange, extraActions, onPrint, onTerminate, canTerminate }: ProfileHeaderProps ): JSX.Element {
	const fullName  = str( record, 'full_name' );
	const avatarUrl = str( record, 'avatar_url' );
	const status    = str( record, 'status' );
	const role      = [ str( record, 'designation_name' ), str( record, 'department_name' ) ]
		.filter( ( s ) => s.trim() !== '' )
		.join( ' · ' );
	const email = str( record, 'email' );

	return (
		<section className="rounded-[10px] border border-border bg-card p-6 shadow-sm">
			<div className="flex flex-wrap items-start gap-5">
				{ canEdit ? (
					<AvatarUpload
						userId={ userId }
						avatarUrl={ avatarUrl }
						fullName={ fullName }
						initials={ initials( fullName ) }
						sizeClass="size-[90px]"
						fallbackClass="text-xl"
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
						<h1 className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
							{ fullName || __( 'Employee', 'erp' ) }
						</h1>
						{ status ? (
							<Badge variant={ statusVariant( status ) }>{ labelOf( STATUS_OPTIONS, status ) }</Badge>
						) : null }
					</div>
					<div className="flex flex-col gap-1">
						{ role ? <p className="m-0 mb-4 truncate text-sm font-semibold text-foreground">{ role }</p> : null }
						{ email ? <p className="m-0 mb-4 truncate text-sm text-muted-foreground">{ email }</p> : null }
					</div>
				</div>

				{ canEdit || extraActions || onPrint || ( onTerminate && canTerminate ) ? (
					<div className="flex flex-wrap items-center gap-2">
						{ extraActions }
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
						{ onTerminate && canTerminate ? (
							<Button
								variant="outline"
								size="sm"
								className="h-9 gap-1.5 px-4"
								onClick={ onTerminate }
							>
								<UserX size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
								{ __( 'Terminate', 'erp' ) }
							</Button>
						) : null }
						{ onPrint ? (
							<Button
								variant="outline"
								size="sm"
								className="h-9 gap-1.5 px-4"
								onClick={ onPrint }
							>
								<Printer size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
								{ __( 'Print', 'erp' ) }
							</Button>
						) : null }
					</div>
				) : null }
			</div>

			{ /* Summary info row — key facts at a glance. */ }
			<div className="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 border-t border-border pt-4 text-sm">
				{ str( record, 'designation_name' ) ? (
					<span className="inline-flex items-center gap-1.5 text-muted-foreground"><Tag size={ 14 } aria-hidden="true" />{ __( 'Job:', 'erp' ) } <span className="font-medium text-foreground">{ str( record, 'designation_name' ) }</span></span>
				) : null }
				{ str( record, 'department_name' ) ? (
					<span className="inline-flex items-center gap-1.5 text-muted-foreground"><Building2 size={ 14 } aria-hidden="true" />{ __( 'Department:', 'erp' ) } <span className="font-medium text-foreground">{ str( record, 'department_name' ) }</span></span>
				) : null }
				{ status ? (
					<span className="inline-flex items-center gap-1.5 text-muted-foreground"><Activity size={ 14 } aria-hidden="true" />{ __( 'Status:', 'erp' ) } <span className="font-medium text-foreground">{ labelOf( STATUS_OPTIONS, status ) }</span></span>
				) : null }
				{ str( record, 'employee_id' ) ? (
					<span className="inline-flex items-center gap-1.5 text-muted-foreground"><IdCard size={ 14 } aria-hidden="true" />{ __( 'Employee ID:', 'erp' ) } <span className="font-medium text-foreground">{ str( record, 'employee_id' ) }</span></span>
				) : null }
			</div>
		</section>
	);
}
