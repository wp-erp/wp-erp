/**
 * Header card for the employee profile: avatar, then the name (with an inline
 * edit icon), designation, email, status and the Leave / Notes quick actions
 * stacked down one column, the page actions on the right, and a facts strip
 * under a divider (employee ID, department, date of hire, type). Avatar is
 * editable in place (for self / managers) via `AvatarUpload`.
 */

import { Avatar, AvatarFallback, AvatarImage, Button, toast } from '@wedevs/plugin-ui';
import { Briefcase, Building2, Calendar, CalendarPlus, IdCard, Pencil, Phone, Printer, Smartphone, StickyNote, UserCheck, UserX } from 'lucide-react';
import type { JSX, ReactNode } from 'react';
import { Link } from 'react-router-dom';

import { PlainButton } from '@/shared/components/PlainButton';
import { StatusPill } from '@/shared/components/StatusPill';
import { employeeStatusTone } from '@/shared/components/status-tones';
import { __ } from '@/shared/i18n';
import { formatCalendarDate } from '@/shared/utils/date';

import { AvatarUpload } from './AvatarUpload';
import { STATUS_OPTIONS, TYPE_OPTIONS } from './options';
import { initials, labelOf, str, type LucideIcon, type Record_ } from './profile-format';

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
	/** Reverse a termination. Rendered only when both this and `canReactivate` are set. */
	readonly onReactivate?:  () => void;
	/** Whether the current user may reactivate this (terminated) employee. */
	readonly canReactivate?: boolean;
	/** Open a profile tab. Enables the Leave / Notes quick actions. */
	readonly onOpenTab?:     ( tab: string ) => void;
	/** Whether the Notes tab is available to the current user. */
	readonly canViewNotes?:  boolean;
}

export function ProfileHeader( { record, userId, canEdit, onEdit, onAvatarChange, extraActions, onPrint, onTerminate, canTerminate, onReactivate, canReactivate, onOpenTab, canViewNotes = false }: ProfileHeaderProps ): JSX.Element {
	const fullName    = str( record, 'full_name' );
	const avatarUrl   = str( record, 'avatar_url' );
	const status      = str( record, 'status' );
	const designation = str( record, 'designation_name' );
	const email       = str( record, 'email' );

	const employeeId    = str( record, 'employee_id' );
	const designationId = Number( str( record, 'designation' ) ) || 0;
	const departmentId  = Number( str( record, 'department' ) ) || 0;
	const mobile        = str( record, 'mobile' );
	const phone         = str( record, 'phone' );

	// Click targets kept from the pill row: department links to the People list
	// filtered by it, the employee ID copies, mobile / phone dial.
	const facts: ReadonlyArray< FactProps > = [
		{
			icon:    IdCard,
			label:   __( 'Employee ID:', 'erp' ),
			value:   employeeId,
			title:   employeeId ? __( 'Copy employee ID', 'erp' ) : undefined,
			onClick: employeeId
				? () => {
					void navigator.clipboard?.writeText( employeeId );
					toast.success( __( 'Employee ID copied.', 'erp' ) );
				}
				: undefined,
		},
		{
			icon:  Building2,
			label: __( 'Department:', 'erp' ),
			value: str( record, 'department_name' ),
			to:    departmentId ? `/employees?department_id=${ departmentId }` : undefined,
		},
		{ icon: Calendar, label: __( 'Date of Hire:', 'erp' ), value: formatCalendarDate( str( record, 'hiring_date' ), '' ) },
		{ icon: Briefcase, label: __( 'Type:', 'erp' ), value: labelOf( TYPE_OPTIONS, str( record, 'type' ) ) },
		...( mobile ? [ { icon: Smartphone, label: __( 'Mobile:', 'erp' ), value: mobile, href: `tel:${ mobile }` } ] : [] ),
		...( phone ? [ { icon: Phone, label: __( 'Phone:', 'erp' ), value: phone, href: `tel:${ phone }` } ] : [] ),
	];

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
					<div className="flex items-center gap-2">
						<h1 className="m-0 text-2xl font-bold leading-8 text-foreground">
							{ fullName || __( 'Employee', 'erp' ) }
						</h1>
						{ canEdit ? (
							<Button
								type="button"
								variant="ghost"
								size="icon"
								onClick={ onEdit }
								className="size-7 rounded-full"
								aria-label={ __( 'Edit employee', 'erp' ) }
								title={ __( 'Edit employee', 'erp' ) }
							>
								<Pencil size={ 16 } aria-hidden="true" />
							</Button>
						) : null }
					</div>
					{ designation ? (
						<p className="m-0 mb-4 text-sm font-semibold text-foreground">
							{ designationId ? (
								<Link to={ `/employees?designation_id=${ designationId }` } className="text-foreground hover:text-primary hover:underline">
									{ designation }
								</Link>
							) : designation }
						</p>
					) : null }
					{ email ? <p className="m-0 mb-4 truncate text-sm text-muted-foreground">{ email }</p> : null }
					{ status ? (
						<div className="mt-1">
							<Link to={ `/employees?status=${ status }` } className="inline-flex rounded-md hover:opacity-80" title={ __( 'Show employees with this status', 'erp' ) }>
								<StatusPill tone={ employeeStatusTone( status ) }>{ labelOf( STATUS_OPTIONS, status ) }</StatusPill>
							</Link>
						</div>
					) : null }

					{ /* Quick actions: jump straight to the Leave / Notes tabs. */ }
					{ onOpenTab && ( canEdit || canViewNotes ) ? (
						<div className="mt-3 flex flex-wrap gap-2">
							{ canEdit ? (
								<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ () => onOpenTab( 'leave' ) }>
									<CalendarPlus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
									{ __( 'Leave', 'erp' ) }
								</Button>
							) : null }
							{ canViewNotes ? (
								<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ () => onOpenTab( 'notes' ) }>
									<StickyNote size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
									{ __( 'Notes', 'erp' ) }
								</Button>
							) : null }
						</div>
					) : null }
				</div>

				{ canEdit || extraActions || onPrint || ( onTerminate && canTerminate ) || ( onReactivate && canReactivate ) ? (
					<div className="flex flex-wrap items-center gap-2">
						{ extraActions }
						{ canEdit ? (
							<Button variant="default" size="sm" className="h-9 gap-1.5 px-4" onClick={ onEdit }>
								<Pencil size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
								{ __( 'Edit', 'erp' ) }
							</Button>
						) : null }
						{ onReactivate && canReactivate ? (
							<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ onReactivate }>
								<UserCheck size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
								{ __( 'Reactivate', 'erp' ) }
							</Button>
						) : null }
						{ onTerminate && canTerminate ? (
							<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ onTerminate }>
								<UserX size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
								{ __( 'Terminate', 'erp' ) }
							</Button>
						) : null }
						{ onPrint ? (
							<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ onPrint }>
								<Printer size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
								{ __( 'Print', 'erp' ) }
							</Button>
						) : null }
					</div>
				) : null }
			</div>

			{ /* Facts strip under a divider; no repeat of designation / status. */ }
			<div className="mt-5 flex flex-wrap items-center gap-x-8 gap-y-3 border-t border-border pt-5 text-sm">
				{ facts.map( ( fact ) => <Fact key={ fact.label } { ...fact } /> ) }
			</div>
		</section>
	);
}

interface FactProps {
	readonly icon:     LucideIcon;
	readonly label:    string;
	readonly value:    string;
	/** Internal route (react-router): the value renders as a Link. */
	readonly to?:      string | undefined;
	/** External / protocol href (e.g. tel:): the value renders as an anchor. */
	readonly href?:    string | undefined;
	/** Click handler (e.g. copy): the value renders as a button. */
	readonly onClick?: ( () => void ) | undefined;
	readonly title?:   string | undefined;
}

/**
 * One label / value fact in the header strip. A value with a target (to /
 * href / onClick) gets the primary hover colour; an empty value shows a dash.
 */
function Fact( { icon: Icon, label, value, to, href, onClick, title }: FactProps ): JSX.Element {
	const valueClass = 'font-medium text-foreground';
	const linkClass  = `${ valueClass } hover:text-primary hover:underline`;
	let shown: ReactNode = value || '—';

	if ( value && to ) {
		shown = <Link to={ to } className={ linkClass } title={ title }>{ value }</Link>;
	} else if ( value && href ) {
		shown = <a href={ href } className={ linkClass } title={ title }>{ value }</a>;
	} else if ( value && onClick ) {
		shown = <PlainButton onClick={ onClick } className={ `${ linkClass } cursor-pointer` } title={ title }>{ value }</PlainButton>;
	} else {
		shown = <span className={ valueClass }>{ shown }</span>;
	}

	return (
		<span className="inline-flex items-center gap-2">
			<Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" className="shrink-0 text-muted-foreground" />
			<span className="text-muted-foreground">{ label }</span>
			{ shown }
		</span>
	);
}
