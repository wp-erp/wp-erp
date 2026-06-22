/**
 * Header card for the v4 single-employee profile view: round avatar, name +
 * inline edit, designation, email, status chip, a row of quick-action buttons
 * (jump to Leave / Notes), a primary Edit action, and a meta strip underneath
 * (employee id, department, hire date, type). Presentational — the page owns
 * data and the tab/edit handlers.
 */

import { Avatar, AvatarFallback, AvatarImage, Badge, Button } from '@wedevs/plugin-ui';
import { CalendarPlus, Pencil, StickyNote } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { STATUS_OPTIONS, TYPE_OPTIONS } from '../employee-profile-v4/options';
import { initials, labelOf, statusVariant, str, type Record_ } from './single-format';

interface SingleHeaderProps {
	readonly record:       Record_;
	readonly canEdit:      boolean;
	readonly canViewNotes: boolean;
	readonly onEdit:       () => void;
	readonly onSetTab:     ( v: string ) => void;
}

export function SingleHeader( { record, canEdit, canViewNotes, onEdit, onSetTab }: SingleHeaderProps ): JSX.Element {
	const fullName    = str( record, 'full_name' );
	const avatarUrl   = str( record, 'avatar_url' );
	const status      = str( record, 'status' );
	const designation = str( record, 'designation_name' );
	const department  = str( record, 'department_name' );
	const email       = str( record, 'email' );

	return (
		<section className="rounded-2xl bg-card p-6 shadow-sm ring-1 ring-border/60">
			<div className="flex flex-wrap items-start gap-5">
				<Avatar className="size-20 shrink-0">
					{ avatarUrl ? <AvatarImage src={ avatarUrl } alt={ fullName } /> : null }
					<AvatarFallback className="text-lg">{ initials( fullName ) }</AvatarFallback>
				</Avatar>

				<div className="flex min-w-0 flex-1 flex-col gap-2">
					<div className="flex items-center gap-2">
						<h1 className="m-0 text-2xl font-bold tracking-tight text-foreground">
							{ fullName || __( 'Employee', 'erp' ) }
						</h1>
						{ canEdit ? (
							<button
								type="button"
								onClick={ onEdit }
								className="inline-flex size-7 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
								aria-label={ __( 'Edit employee', 'erp' ) }
								title={ __( 'Edit employee', 'erp' ) }
							>
								<Pencil size={ 15 } aria-hidden="true" />
							</button>
						) : null }
					</div>
					{ designation ? <p className="m-0 text-sm font-semibold text-foreground">{ designation }</p> : null }
					{ email ? <p className="m-0 text-sm text-muted-foreground">{ email }</p> : null }
					{ status ? (
						<div className="mt-1">
							<Badge variant={ statusVariant( status ) }>{ labelOf( STATUS_OPTIONS, status ) }</Badge>
						</div>
					) : null }

					{ /* Quick actions — jump to the relevant section. */ }
					<div className="mt-3 flex flex-wrap gap-2">
						{ canEdit ? (
							<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ () => onSetTab( 'leave' ) }>
								<CalendarPlus size={ 15 } aria-hidden="true" />
								{ __( 'Leave', 'erp' ) }
							</Button>
						) : null }
						{ canViewNotes ? (
							<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ () => onSetTab( 'notes' ) }>
								<StickyNote size={ 15 } aria-hidden="true" />
								{ __( 'Notes', 'erp' ) }
							</Button>
						) : null }
					</div>
				</div>

				{ canEdit ? (
					<Button variant="default" size="sm" className="h-9 gap-1.5 px-4" onClick={ onEdit }>
						<Pencil size={ 14 } aria-hidden="true" />
						{ __( 'Edit', 'erp' ) }
					</Button>
				) : null }
			</div>

			{ /* Meta strip — complements the header; no repeat of designation/status. */ }
			<div className="mt-5 flex flex-wrap items-center gap-x-8 gap-y-3 border-t border-border pt-5 text-sm">
				<span className="inline-flex items-center gap-2">
					<span className="text-muted-foreground">{ __( 'Employee ID:', 'erp' ) }</span>
					<span className="font-medium text-foreground">{ str( record, 'employee_id' ) || '—' }</span>
				</span>
				<span className="inline-flex items-center gap-2">
					<span className="text-muted-foreground">{ __( 'Department:', 'erp' ) }</span>
					<span className="font-medium text-foreground">{ department || '—' }</span>
				</span>
				<span className="inline-flex items-center gap-2">
					<span className="text-muted-foreground">{ __( 'Date of Hire:', 'erp' ) }</span>
					<span className="font-medium text-foreground">{ str( record, 'hiring_date' ) || '—' }</span>
				</span>
				<span className="inline-flex items-center gap-2">
					<span className="text-muted-foreground">{ __( 'Type:', 'erp' ) }</span>
					<span className="font-medium text-foreground">{ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) || '—' }</span>
				</span>
			</div>
		</section>
	);
}
