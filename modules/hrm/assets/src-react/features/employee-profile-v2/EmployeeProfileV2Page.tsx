/**
 * Employee profile — layout variant v2 (`#/employees/{id}/profile-v2`).
 *
 * A two-pane layout inspired by the "profile card + tabbed detail" pattern:
 *   - a sticky LEFT card: soft cover band, large overlapping avatar, name +
 *     employee id (copyable), designation chip, and a "Basic Information" list
 *     of icon-led rows (email, phone, nationality, gender, age, status, hire).
 *   - a RIGHT pane: a horizontal dark-pill tab bar over stacked detail cards.
 *     Each card has an icon chip, a title, an Edit affordance, and label→value
 *     rows split left/right with hairline separators.
 *
 * Fully self-contained: it keeps a private copy of every tab body (Job, Leave,
 * Notes, Performance, Permission, General sections) under this folder and
 * imports nothing from `employee-create`. Delete the folder + the blocks tagged
 * `[NEW-PROFILE-V2]` to remove it cleanly.
 */

import {
	Avatar,
	AvatarFallback,
	AvatarImage,
	Badge,
	Skeleton,
	toast,
} from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	Briefcase,
	CalendarDays,
	Check,
	Copy,
	Droplet,
	Globe,
	GraduationCap,
	Home,
	Mail,
	Pencil,
	Phone,
	User,
	UserCircle,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { ComponentType, JSX, ReactNode, SVGProps } from 'react';
import { useNavigate, useParams } from 'react-router-dom';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

import { EmployeeExtraFieldsView } from '../employee-create/EmployeeExtraFieldsView';
import { useProfileExtraTabs } from '../employee-create/profile-tabs';
import { AvatarUpload } from './AvatarUpload';
import { EmployeeGeneralSections } from './general/EmployeeGeneralSections';
import { EmployeeJobTab } from './job/EmployeeJobTab';
import { EmployeeLeaveTab } from './leave/EmployeeLeaveTab';
import { EmployeeNotesTab } from './notes/EmployeeNotesTab';
import type { Option } from './options';
import {
	BLOOD_GROUP_OPTIONS,
	GENDER_OPTIONS,
	MARITAL_OPTIONS,
	PAY_TYPE_OPTIONS,
	SOURCE_OPTIONS,
	STATUS_OPTIONS,
	TYPE_OPTIONS,
} from './options';
import { EmployeePerformanceTab } from './performance/EmployeePerformanceTab';
import { EmployeePermissionTab } from './permission/EmployeePermissionTab';

interface SingleDispatch {
	fetchEmployeeForEdit: ( userId: number ) => Promise< Record< string, unknown > >;
}

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;
type Record_ = Record< string, unknown >;

function str( record: Record_, key: string ): string {
	const value = record[ key ];
	return value === null || value === undefined ? '' : String( value );
}

function labelOf( options: readonly Option[], value: string ): string {
	return options.find( ( o ) => o.value === value )?.label ?? value;
}

function initials( name: string ): string {
	const parts = name.trim().split( /\s+/ ).filter( Boolean );
	if ( parts.length === 0 ) {
		return '?';
	}
	const first = parts[ 0 ]?.[ 0 ] ?? '';
	const last  = parts.length > 1 ? parts[ parts.length - 1 ]?.[ 0 ] ?? '' : '';
	return ( first + last ).toUpperCase();
}

function statusVariant( status: string ): 'success' | 'secondary' | 'destructive' {
	switch ( status ) {
		case 'active':
			return 'success';
		case 'terminated':
		case 'deceased':
			return 'destructive';
		default:
			return 'secondary';
	}
}

/** Whole-year age from a YYYY-MM-DD birth date, or '' when unknown. */
function ageFrom( dob: string ): string {
	const v = ( dob ?? '' ).trim();
	if ( ! v || v.startsWith( '0000' ) ) {
		return '';
	}
	const d = new Date( v );
	if ( Number.isNaN( d.getTime() ) ) {
		return '';
	}
	const now = new Date();
	let years = now.getFullYear() - d.getFullYear();
	const m = now.getMonth() - d.getMonth();
	if ( m < 0 || ( m === 0 && now.getDate() < d.getDate() ) ) {
		years -= 1;
	}
	return years >= 0 ? String( years ) : '';
}

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
			<span className="mt-0.5 inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
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

interface TabDef {
	readonly value: string;
	readonly label: string;
}

/** Horizontal tab bar — active chip is a dark (foreground) pill. */
function PillTabs( {
	tabs,
	current,
	onSelect,
}: {
	readonly tabs:     readonly TabDef[];
	readonly current:  string;
	readonly onSelect: ( v: string ) => void;
} ): JSX.Element {
	return (
		<div
			role="tablist"
			aria-label={ __( 'Profile sections', 'erp' ) }
			className="flex flex-wrap items-center gap-1 rounded-full bg-muted/60 p-1"
		>
			{ tabs.map( ( t ) => {
				const isActive = current === t.value;
				return (
					<button
						key={ t.value }
						type="button"
						role="tab"
						aria-selected={ isActive }
						onClick={ () => onSelect( t.value ) }
						className={ [
							'rounded-full px-4 py-2 text-sm font-medium transition-colors',
							isActive
								? 'bg-primary text-primary-foreground shadow-sm'
								: 'text-muted-foreground hover:text-foreground',
						].join( ' ' ) }
					>
						{ t.label }
					</button>
				);
			} ) }
		</div>
	);
}

/** Detail card with an icon chip, title, optional edit action, and split rows. */
function InfoCard( {
	icon: Icon,
	tone,
	title,
	onEdit,
	children,
}: {
	readonly icon:     LucideIcon;
	readonly tone:     string;
	readonly title:    string;
	readonly onEdit?:  ( () => void ) | undefined;
	readonly children: ReactNode;
} ): JSX.Element {
	return (
		<section className="rounded-2xl bg-card p-6 shadow-sm ring-1 ring-border/60">
			<div className="flex items-center gap-3">
				<span className={ `inline-flex size-9 items-center justify-center rounded-lg ${ tone }` }>
					<Icon size={ 18 } strokeWidth={ 2 } aria-hidden="true" />
				</span>
				<h2 className="m-0 flex-1 text-base font-semibold tracking-tight text-foreground">{ title }</h2>
				{ onEdit ? (
					<button
						type="button"
						onClick={ onEdit }
						className="inline-flex size-8 items-center justify-center rounded-full text-muted-foreground ring-1 ring-border transition-colors hover:bg-muted hover:text-foreground"
						aria-label={ __( 'Edit', 'erp' ) }
						title={ __( 'Edit', 'erp' ) }
					>
						<Pencil size={ 14 } aria-hidden="true" />
					</button>
				) : null }
			</div>
			<dl className="mt-2 divide-y divide-border">{ children }</dl>
		</section>
	);
}

/** Label (left) → value (right) split row. */
function SplitRow( { label, value }: { readonly label: string; readonly value: string } ): JSX.Element {
	return (
		<div className="flex items-start justify-between gap-6 py-3.5">
			<dt className="text-sm text-muted-foreground">{ label }</dt>
			<dd className="max-w-[60%] text-right text-sm font-medium text-foreground">
				{ value.trim() === '' ? '—' : value }
			</dd>
		</div>
	);
}

export function EmployeeProfileV2Inner( { userId }: { userId: number } ): JSX.Element {
	const navigate     = useNavigate();
	const canEdit      = useCan( 'erp_edit_employee' );
	const canViewNotes = useCan( 'erp_manage_review' );
	const canViewPerf  = useCan( 'erp_create_review' );

	const currentUserId = useSelect(
		( select ) => ( select( meStoreName ) as unknown as { getUser: () => MeUser | null } ).getUser()?.id ?? 0,
		[]
	);
	const canViewPermission = canViewPerf && currentUserId !== userId;
	const { fetchEmployeeForEdit } = useDispatch( employeesStoreName ) as unknown as SingleDispatch;

	const [ record, setRecord ] = useState< Record_ | null >( null );
	const [ loadError, setLoadError ] = useState< string | null >( null );
	const [ tab, setTab ] = useState( 'personal' );

	// Pro-injectable profile tabs (Documents). Before any early return — Rules of Hooks.
	const extraTabs = useProfileExtraTabs( { userId, canEdit } );

	useEffect( () => {
		let cancelled = false;
		setLoadError( null );
		void fetchEmployeeForEdit( userId )
			.then( ( data ) => ! cancelled && setRecord( data ) )
			.catch( ( raw ) => {
				if ( cancelled ) {
					return;
				}
				const err = raw as { message?: string };
				setLoadError( err?.message || __( 'Could not load this employee.', 'erp' ) );
			} );
		return () => {
			cancelled = true;
		};
	}, [ userId, fetchEmployeeForEdit ] );

	const goEdit = (): void => {
		navigate( `/employees/${ userId }/edit`, { viewTransition: true } );
	};

	if ( loadError ) {
		return (
			<div className="mx-auto w-full max-w-7xl text-center text-sm text-destructive">{ loadError }</div>
		);
	}

	if ( ! record ) {
		return (
			<div className="mx-auto flex w-full max-w-7xl flex-col gap-6 lg:flex-row">
				<Skeleton className="h-[34rem] w-full rounded-2xl lg:w-80" />
				<div className="flex-1 space-y-6">
					<Skeleton className="h-12 w-96 rounded-full" />
					<Skeleton className="h-64 w-full rounded-2xl" />
					<Skeleton className="h-64 w-full rounded-2xl" />
				</div>
			</div>
		);
	}

	const fullName    = str( record, 'full_name' );
	const avatarUrl   = str( record, 'avatar_url' );
	const status      = str( record, 'status' );
	const designation = str( record, 'designation_name' );
	const age         = ageFrom( str( record, 'date_of_birth' ) );

	const tabs: TabDef[] = [
		{ value: 'personal', label: __( 'Personal Information', 'erp' ) },
		...( canEdit ? [ { value: 'job', label: __( 'Job Information', 'erp' ) } ] : [] ),
		...( canEdit ? [ { value: 'leave', label: __( 'Leave', 'erp' ) } ] : [] ),
		...( canViewNotes ? [ { value: 'notes', label: __( 'Notes', 'erp' ) } ] : [] ),
		...( canViewPerf ? [ { value: 'performance', label: __( 'Performance', 'erp' ) } ] : [] ),
		...( canViewPermission ? [ { value: 'permission', label: __( 'Permission', 'erp' ) } ] : [] ),
		...extraTabs.map( ( t ) => ( { value: t.id, label: t.label } ) ),
	];

	return (
		<div className="mx-auto w-full max-w-7xl">
			<nav className="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
				<button
					type="button"
					onClick={ () => navigate( '/employees' ) }
					className="transition-colors hover:text-foreground"
				>
					{ __( 'Employees', 'erp' ) }
				</button>
				<span aria-hidden="true">/</span>
				<span className="font-medium text-foreground">{ __( 'Employee Profile', 'erp' ) }</span>
			</nav>

			<div className="flex flex-col gap-6 lg:flex-row lg:items-start">
				{ /* LEFT — sticky profile card. */ }
				<aside className="shrink-0 lg:sticky lg:top-6 lg:w-80">
					<section className="overflow-hidden rounded-2xl bg-card shadow-sm ring-1 ring-border/60">
						{ /* Soft cover band — flat tint, no gradient. */ }
						<div className="relative h-28 bg-muted">
							{ canEdit ? (
								<button
									type="button"
									onClick={ goEdit }
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
											onChange={ ( url ) =>
												setRecord( ( prev ) => ( prev ? { ...prev, avatar_url: url } : prev ) )
											}
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

				{ /* RIGHT — tabs + detail cards. */ }
				<div className="min-w-0 flex-1">
					<PillTabs tabs={ tabs } current={ tab } onSelect={ setTab } />

					<div className="mt-6">
						{ tab === 'personal' ? (
							<div className="space-y-6">
								<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'top' ] } />

								<InfoCard
									icon={ Briefcase }
									tone="bg-sky-100 text-sky-700"
									title={ __( 'Employment', 'erp' ) }
									onEdit={ canEdit ? goEdit : undefined }
								>
									<SplitRow label={ __( 'Employee ID', 'erp' ) } value={ str( record, 'employee_id' ) } />
									<SplitRow label={ __( 'Employee Type', 'erp' ) } value={ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) } />
									<SplitRow label={ __( 'Date of Hire', 'erp' ) } value={ str( record, 'hiring_date' ) } />
									<SplitRow label={ __( 'Department', 'erp' ) } value={ str( record, 'department_name' ) } />
									<SplitRow label={ __( 'Job Title', 'erp' ) } value={ str( record, 'designation_name' ) } />
									<SplitRow label={ __( 'Reporting To', 'erp' ) } value={ str( record, 'reporting_to_name' ) } />
									<SplitRow label={ __( 'Source of Hire', 'erp' ) } value={ labelOf( SOURCE_OPTIONS, str( record, 'hiring_source' ) ) } />
									<SplitRow label={ __( 'Pay Rate', 'erp' ) } value={ str( record, 'pay_rate' ) } />
									<SplitRow label={ __( 'Pay Type', 'erp' ) } value={ labelOf( PAY_TYPE_OPTIONS, str( record, 'pay_type' ) ) } />
								</InfoCard>

								<InfoCard
									icon={ Mail }
									tone="bg-violet-100 text-violet-700"
									title={ __( 'Contact', 'erp' ) }
									onEdit={ canEdit ? goEdit : undefined }
								>
									<SplitRow label={ __( 'Email', 'erp' ) } value={ str( record, 'email' ) } />
									<SplitRow label={ __( 'Other Email', 'erp' ) } value={ str( record, 'other_email' ) } />
									<SplitRow label={ __( 'Mobile', 'erp' ) } value={ str( record, 'mobile' ) } />
									<SplitRow label={ __( 'Phone', 'erp' ) } value={ str( record, 'phone' ) } />
									<SplitRow label={ __( 'Work Phone', 'erp' ) } value={ str( record, 'work_phone' ) } />
									<SplitRow label={ __( 'Website', 'erp' ) } value={ str( record, 'user_url' ) } />
								</InfoCard>

								<InfoCard
									icon={ GraduationCap }
									tone="bg-amber-100 text-amber-700"
									title={ __( 'Personal Details', 'erp' ) }
									onEdit={ canEdit ? goEdit : undefined }
								>
									<SplitRow label={ __( 'Date of Birth', 'erp' ) } value={ str( record, 'date_of_birth' ) } />
									<SplitRow label={ __( 'Gender', 'erp' ) } value={ labelOf( GENDER_OPTIONS, str( record, 'gender' ) ) } />
									<SplitRow label={ __( 'Marital Status', 'erp' ) } value={ labelOf( MARITAL_OPTIONS, str( record, 'marital_status' ) ) } />
									<SplitRow label={ __( 'Blood Group', 'erp' ) } value={ labelOf( BLOOD_GROUP_OPTIONS, str( record, 'blood_group' ) ) } />
									<SplitRow label={ __( 'Nationality', 'erp' ) } value={ str( record, 'nationality' ) } />
									<SplitRow label={ __( "Father's name", 'erp' ) } value={ str( record, 'father_name' ) } />
									<SplitRow label={ __( "Mother's name", 'erp' ) } value={ str( record, 'mother_name' ) } />
								</InfoCard>

								<InfoCard
									icon={ Home }
									tone="bg-rose-100 text-rose-700"
									title={ __( 'Home Address', 'erp' ) }
									onEdit={ canEdit ? goEdit : undefined }
								>
									<SplitRow label={ __( 'Address', 'erp' ) } value={ str( record, 'street_1' ) } />
									<SplitRow label={ __( 'Address (cont.)', 'erp' ) } value={ str( record, 'street_2' ) } />
									<SplitRow label={ __( 'City', 'erp' ) } value={ str( record, 'city' ) } />
									<SplitRow label={ __( 'Province / State', 'erp' ) } value={ str( record, 'state' ) } />
									<SplitRow label={ __( 'Country', 'erp' ) } value={ str( record, 'country' ) } />
									<SplitRow label={ __( 'Postal code', 'erp' ) } value={ str( record, 'postal_code' ) } />
								</InfoCard>

								{ str( record, 'description' ).trim() !== '' ? (
									<InfoCard icon={ Droplet } tone="bg-emerald-100 text-emerald-700" title={ __( 'Biography', 'erp' ) }>
										<p className="whitespace-pre-line py-3.5 text-sm text-foreground">
											{ str( record, 'description' ) }
										</p>
									</InfoCard>
								) : null }

								<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'basic', 'work', 'personal', 'bottom' ] } />

								{ canEdit ? <EmployeeGeneralSections userId={ userId } /> : null }
							</div>
						) : null }

						{ canEdit && tab === 'job' ? <EmployeeJobTab userId={ userId } /> : null }
						{ canEdit && tab === 'leave' ? <EmployeeLeaveTab userId={ userId } /> : null }
						{ canViewNotes && tab === 'notes' ? <EmployeeNotesTab userId={ userId } /> : null }
						{ canViewPerf && tab === 'performance' ? <EmployeePerformanceTab userId={ userId } /> : null }
						{ canViewPermission && tab === 'permission' ? <EmployeePermissionTab userId={ userId } /> : null }
						{ extraTabs.find( ( t ) => t.id === tab )?.render( { userId, canEdit } ) }
					</div>
				</div>
			</div>
		</div>
	);
}

export function EmployeeProfileV2Page(): JSX.Element {
	const { id } = useParams< { id: string } >();
	const userId = Number( id );

	return (
		<ErrorBoundary>
			{ Number.isFinite( userId ) && userId > 0 ? (
				<EmployeeProfileV2Inner userId={ userId } />
			) : (
				<div className="mx-auto my-12 max-w-md text-center text-sm text-muted-foreground">
					{ __( 'Invalid employee.', 'erp' ) }
				</div>
			) }
		</ErrorBoundary>
	);
}
