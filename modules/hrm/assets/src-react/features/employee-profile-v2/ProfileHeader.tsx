/**
 * Sticky LEFT profile card for the Employee Profile v2 layout: soft cover band,
 * large overlapping avatar (editable in place for self / managers), name +
 * copyable employee id, designation/status chips, and a "Basic Information"
 * list of icon-led rows. `CopyId` and `BasicRow` are private pieces of this card.
 */

import { Avatar, AvatarFallback, AvatarImage, Badge, toast } from '@wedevs/plugin-ui';
import { Briefcase, CalendarDays, Check, Copy, Globe, Mail, Pencil, Phone, User, UserCircle } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { AvatarUpload } from './AvatarUpload';
import { GENDER_OPTIONS, STATUS_OPTIONS, TYPE_OPTIONS } from './options';
import { ageFrom, initials, labelOf, statusVariant, str, type LucideIcon, type Record_ } from './profile-format';

/** Copy-to-clipboard button for the employee id. */
function CopyId( { value }: { readonly value: string } ): JSX.Element | null {
	const [ copied, setCopied ] = useState( false );
	if ( ! value.trim() ) {
		return null;
	}
	return (
		<button
			type="button"
			onClick={ () => {
				void navigator.clipboard
					?.writeText( value )
					.then( () => {
						setCopied( true );
						toast.success( __( 'Employee ID copied.', 'erp' ) );
						window.setTimeout( () => setCopied( false ), 1500 );
					} )
					.catch( () => toast.error( __( 'Could not copy.', 'erp' ) ) );
			} }
			className="inline-flex items-center gap-1 text-xs font-medium text-muted-foreground transition-colors hover:text-foreground"
			aria-label={ __( 'Copy employee ID', 'erp' ) }
			title={ __( 'Copy employee ID', 'erp' ) }
		>
			<span>{ value }</span>
			{ copied ? <Check size={ 13 } aria-hidden="true" /> : <Copy size={ 13 } aria-hidden="true" /> }
		</button>
	);
}

/** A single icon-led row in the left card's Basic Information list. */
function BasicRow( {
	icon: Icon,
	label,
	value,
}: {
	readonly icon:  LucideIcon;
	readonly label: string;
	readonly value: string;
} ): JSX.Element {
	return (
		<div className="flex items-start gap-3">
			<span className="mt-0.5 inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
				<Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
			</span>
			<div className="flex min-w-0 flex-col">
				<span className="text-xs text-muted-foreground">{ label }</span>
				<span className="truncate text-sm font-medium text-foreground">
					{ value.trim() === '' ? '—' : value }
				</span>
			</div>
		</div>
	);
}

interface ProfileHeaderProps {
	readonly record:         Record_;
	readonly userId:         number;
	readonly canEdit:        boolean;
	readonly onEdit:         () => void;
	readonly onAvatarChange: ( url: string ) => void;
}

export function ProfileHeader( { record, userId, canEdit, onEdit, onAvatarChange }: ProfileHeaderProps ): JSX.Element {
	const fullName    = str( record, 'full_name' );
	const avatarUrl   = str( record, 'avatar_url' );
	const status      = str( record, 'status' );
	const designation = str( record, 'designation_name' );
	const age         = ageFrom( str( record, 'date_of_birth' ) );

	return (
		<aside className="shrink-0 lg:sticky lg:top-6 lg:w-80">
			<section className="overflow-hidden rounded-2xl bg-card shadow-sm ring-1 ring-border/60">
				{ /* Soft cover band — flat tint, no gradient. */ }
				<div className="relative h-28 bg-muted">
					{ canEdit ? (
						<button
							type="button"
							onClick={ onEdit }
							className="absolute right-3 top-3 inline-flex size-8 items-center justify-center rounded-full bg-card text-muted-foreground shadow-sm ring-1 ring-border transition-colors hover:text-foreground"
							aria-label={ __( 'Edit employee', 'erp' ) }
							title={ __( 'Edit employee', 'erp' ) }
						>
							<Pencil size={ 14 } aria-hidden="true" />
						</button>
					) : null }
				</div>

				<div className="px-6 pb-6">
					{ /* Avatar overlaps the cover. */ }
					<div className="-mt-12 mb-3">
						{ canEdit ? (
							<div className="w-fit rounded-full ring-4 ring-card">
								<AvatarUpload
									userId={ userId }
									avatarUrl={ avatarUrl }
									fullName={ fullName }
									initials={ initials( fullName ) }
									sizeClass="size-24"
									fallbackClass="text-2xl"
									onChange={ onAvatarChange }
								/>
							</div>
						) : (
							<Avatar className="size-24 ring-4 ring-card">
								{ avatarUrl ? <AvatarImage src={ avatarUrl } alt={ fullName } /> : null }
								<AvatarFallback className="text-2xl">{ initials( fullName ) }</AvatarFallback>
							</Avatar>
						) }
					</div>

					<div className="flex items-center gap-2">
						<h1 className="m-0 text-xl font-bold tracking-tight text-foreground">
							{ fullName || __( 'Employee', 'erp' ) }
						</h1>
						<CopyId value={ str( record, 'employee_id' ) } />
					</div>

					<div className="mt-2 flex flex-wrap items-center gap-2">
						{ designation ? (
							<Badge variant="secondary" className="font-medium">{ designation }</Badge>
						) : null }
						{ status ? (
							<Badge variant={ statusVariant( status ) }>{ labelOf( STATUS_OPTIONS, status ) }</Badge>
						) : null }
					</div>

					<h3 className="mb-4 mt-6 text-sm font-semibold text-foreground">
						{ __( 'Basic Information', 'erp' ) }
					</h3>
					<div className="flex flex-col gap-4">
						<BasicRow icon={ Mail } label={ __( 'Email', 'erp' ) } value={ str( record, 'email' ) } />
						<BasicRow icon={ Phone } label={ __( 'Mobile Phone', 'erp' ) } value={ str( record, 'mobile' ) } />
						<BasicRow icon={ Globe } label={ __( 'Nationality', 'erp' ) } value={ str( record, 'nationality' ) } />
						<BasicRow icon={ User } label={ __( 'Gender', 'erp' ) } value={ labelOf( GENDER_OPTIONS, str( record, 'gender' ) ) } />
						<BasicRow icon={ CalendarDays } label={ __( 'Age', 'erp' ) } value={ age } />
						<BasicRow icon={ UserCircle } label={ __( 'Status', 'erp' ) } value={ labelOf( STATUS_OPTIONS, status ) } />
						<BasicRow icon={ Briefcase } label={ __( 'Type of Hire', 'erp' ) } value={ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) } />
					</div>
				</div>
			</section>
		</aside>
	);
}
