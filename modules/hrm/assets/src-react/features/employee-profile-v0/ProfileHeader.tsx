/**
 * Header card for the Employee Profile v4 layout: compact avatar-left, name +
 * status badge, an Edit action, and a summary info row of key facts. Avatar is
 * editable in place (for self / managers) via `AvatarUpload`.
 */

import { Avatar, AvatarFallback, AvatarImage, Badge, Button, toast } from '@wedevs/plugin-ui';
import { Activity, Building2, IdCard, Pencil, Phone, Printer, Smartphone, Tag, UserCheck, UserX } from 'lucide-react';
import type { JSX, ReactNode } from 'react';
import { Link } from 'react-router-dom';

import { __ } from '@/shared/i18n';
import type { LucideIcon } from './profile-format';

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
	/** Reverse a termination. Rendered only when both this and `canReactivate` are set. */
	readonly onReactivate?:  () => void;
	/** Whether the current user may reactivate this (terminated) employee. */
	readonly canReactivate?: boolean;
}

export function ProfileHeader( { record, userId, canEdit, onEdit, onAvatarChange, extraActions, onPrint, onTerminate, canTerminate, onReactivate, canReactivate }: ProfileHeaderProps ): JSX.Element {
	const fullName  = str( record, 'full_name' );
	const avatarUrl = str( record, 'avatar_url' );
	const status    = str( record, 'status' );
	const role      = [ str( record, 'designation_name' ), str( record, 'department_name' ) ]
		.filter( ( s ) => s.trim() !== '' )
		.join( ' · ' );
	const email  = str( record, 'email' );
	const mobile = str( record, 'mobile' );
	const phone  = str( record, 'phone' );

	// Summary-pill facts. designation/department/status link to the employee
	// list pre-filtered by that value; employee-id copies to the clipboard.
	const designationName = str( record, 'designation_name' );
	const departmentName  = str( record, 'department_name' );
	const employeeId      = str( record, 'employee_id' );
	const designationId   = Number( str( record, 'designation' ) ) || 0;
	const departmentId    = Number( str( record, 'department' ) ) || 0;

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

				{ canEdit || extraActions || onPrint || ( onTerminate && canTerminate ) || ( onReactivate && canReactivate ) ? (
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
						{ onReactivate && canReactivate ? (
							<Button
								variant="outline"
								size="sm"
								className="h-9 gap-1.5 px-4"
								onClick={ onReactivate }
							>
								<UserCheck size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
								{ __( 'Reactivate', 'erp' ) }
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

			{ /* Summary info row — key facts as pills. Designation / Department /
			   Status link to the employee list filtered by that value; Employee
			   ID copies to the clipboard. */ }
			<div className="-mx-6 mt-5 flex flex-wrap items-center gap-2 border-t border-border px-6 pt-4">
				{ designationName ? (
					<MetaPill
						icon={ Tag }
						label={ __( 'Designation:', 'erp' ) }
						value={ designationName }
						to={ designationId ? `/employees?designation_id=${ designationId }` : undefined }
					/>
				) : null }
				{ departmentName ? (
					<MetaPill
						icon={ Building2 }
						label={ __( 'Department:', 'erp' ) }
						value={ departmentName }
						to={ departmentId ? `/employees?department_id=${ departmentId }` : undefined }
					/>
				) : null }
				{ status ? (
					<MetaPill
						icon={ Activity }
						label={ __( 'Status:', 'erp' ) }
						value={ labelOf( STATUS_OPTIONS, status ) }
						to={ `/employees?status=${ status }` }
					/>
				) : null }
				{ employeeId ? (
					<MetaPill
						icon={ IdCard }
						label={ __( 'Employee ID:', 'erp' ) }
						value={ employeeId }
						title={ __( 'Copy employee ID', 'erp' ) }
						onClick={ () => {
							void navigator.clipboard?.writeText( employeeId );
							toast.success( __( 'Employee ID copied.', 'erp' ) );
						} }
					/>
				) : null }
				{ mobile ? (
					<MetaPill icon={ Smartphone } label={ __( 'Mobile:', 'erp' ) } value={ mobile } href={ `tel:${ mobile }` } />
				) : null }
				{ phone ? (
					<MetaPill icon={ Phone } label={ __( 'Phone:', 'erp' ) } value={ phone } href={ `tel:${ phone }` } />
				) : null }
			</div>
		</section>
	);
}

interface MetaPillProps {
	readonly icon:     LucideIcon;
	readonly label:    string;
	readonly value:    string;
	/** Internal route (react-router) — renders a Link. */
	readonly to?:      string | undefined;
	/** External/protocol href (e.g. tel:) — renders an anchor. */
	readonly href?:    string | undefined;
	/** Click handler (e.g. copy) — renders a button. */
	readonly onClick?: ( () => void ) | undefined;
	readonly title?:   string | undefined;
}

/**
 * One key-fact pill in the profile header. Clickable variants (to / href /
 * onClick) get a hover affordance; a bare pill (no target) is a plain span.
 */
function MetaPill( { icon: Icon, label, value, to, href, onClick, title }: MetaPillProps ): JSX.Element {
	const base =
		'inline-flex items-center gap-1.5 rounded-full border border-border bg-muted/40 px-3 py-1 text-xs';
	const interactive = `${ base } transition-colors hover:border-primary/40 hover:bg-primary/10`;
	const inner = (
		<>
			<Icon size={ 14 } aria-hidden="true" className="text-muted-foreground" />
			<span className="text-muted-foreground">{ label }</span>
			<span className="font-medium text-foreground">{ value }</span>
		</>
	);

	if ( to ) {
		return <Link to={ to } className={ interactive } title={ title }>{ inner }</Link>;
	}
	if ( href ) {
		return <a href={ href } className={ interactive } title={ title }>{ inner }</a>;
	}
	if ( onClick ) {
		return (
			<button type="button" onClick={ onClick } className={ interactive } title={ title }>
				{ inner }
			</button>
		);
	}
	return <span className={ base } title={ title }>{ inner }</span>;
}
