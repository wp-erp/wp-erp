/**
 * Hero block for the Employee Profile v3 layout: a large rounded-square portrait
 * (with an in-place edit affordance and a solid legibility band) beside the name
 * card + quick stat strip.
 */

import { Badge, Button } from '@wedevs/plugin-ui';
import { Pencil } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { OverviewStats } from './general/OverviewStats';
import { STATUS_OPTIONS } from './options';
import { initials, labelOf, str, type Record_ } from './profile-format';

interface ProfileHeaderProps {
	readonly record:  Record_;
	readonly userId:  number;
	readonly canEdit: boolean;
	readonly onEdit:  () => void;
}

export function ProfileHeader( { record, userId, canEdit, onEdit }: ProfileHeaderProps ): JSX.Element {
	const fullName    = str( record, 'full_name' );
	const avatarUrl   = str( record, 'avatar_url' );
	const status      = str( record, 'status' );
	const designation = str( record, 'designation_name' );
	const department  = str( record, 'department_name' );

	return (
		<div className="flex flex-col gap-6 lg:flex-row lg:items-stretch">
			<div className="relative aspect-square w-full shrink-0 overflow-hidden rounded-3xl bg-amber-100 ring-1 ring-border/60 lg:w-72">
				{ avatarUrl ? (
					<img src={ avatarUrl } alt={ fullName } className="size-full object-cover" />
				) : (
					<div className="flex size-full items-center justify-center text-6xl font-bold text-amber-700">
						{ initials( fullName ) }
					</div>
				) }
				{ canEdit ? (
					<Button
						type="button"
						variant="ghost"
						size="icon"
						onClick={ onEdit }
						className="absolute right-3 top-3 inline-flex size-9 items-center justify-center rounded-full bg-card/90 text-foreground shadow-sm ring-1 ring-border transition-colors hover:bg-card"
						aria-label={ __( 'Edit employee', 'erp' ) }
						title={ __( 'Edit employee', 'erp' ) }
					>
						<Pencil size={ 15 } aria-hidden="true" />
					</Button>
				) : null }
				{ /* Solid legibility band (not a decorative gradient). */ }
				<div className="absolute inset-x-0 bottom-0 bg-black/55 px-4 py-3 text-white backdrop-blur-[1px]">
					<p className="truncate text-base font-semibold leading-tight">{ fullName || __( 'Employee', 'erp' ) }</p>
					<p className="truncate text-xs text-white/80">{ designation || __( 'Employee', 'erp' ) }</p>
				</div>
			</div>

			<div className="flex min-w-0 flex-1 flex-col gap-4">
				<section className="flex flex-wrap items-center gap-3 rounded-3xl bg-card p-6 shadow-sm ring-1 ring-border/60">
					<div className="min-w-0 flex-1">
						<h1 className="m-0 mb-4 truncate text-2xl font-bold tracking-tight text-foreground">
							{ fullName || __( 'Employee', 'erp' ) }
						</h1>
						<p className="mt-1 truncate text-sm text-muted-foreground">
							{ [ designation, department ].filter( Boolean ).join( ' · ' ) || __( 'Employee', 'erp' ) }
						</p>
					</div>
					{ status ? (
						<Badge variant={ status === 'active' ? 'success' : status === 'terminated' || status === 'deceased' ? 'destructive' : 'secondary' }>
							{ labelOf( STATUS_OPTIONS, status ) }
						</Badge>
					) : null }
				</section>

				<OverviewStats
					userId={ userId }
					hiringDate={ str( record, 'hiring_date' ) }
					dateOfBirth={ str( record, 'date_of_birth' ) }
				/>
			</div>
		</div>
	);
}
