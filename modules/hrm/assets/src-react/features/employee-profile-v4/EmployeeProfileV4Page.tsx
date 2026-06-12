/**
 * Modern employee profile view (`#/employees/{id}/profile`).
 *
 * Alternative, redesigned layout for the single-employee detail screen:
 *   - a large, prominent avatar header (no gradient — flat card, clean rings)
 *   - a left vertical sidebar for tab navigation instead of top segmented pills
 *
 * Pulls the same record from `GET /erp/v2/employees/{id}` and reuses every tab
 * body component from the Employee Create feature (Job, Leave, Notes,
 * Performance, Permission, General sections, Overview stats) so behaviour stays
 * 1:1 with the original `EmployeeSinglePage`. Only the chrome differs.
 *
 * The original `EmployeeSinglePage` is left untouched; this is a parallel page
 * wired at its own route.
 */

import {
	Avatar,
	AvatarFallback,
	AvatarImage,
	Badge,
	Button,
	Skeleton,
} from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	Activity,
	ArrowLeft,
	Briefcase,
	Building2,
	Calendar,
	CalendarClock,
	CalendarOff,
	Compass,
	DollarSign,
	Droplets,
	Flag,
	Globe,
	Hash,
	Heart,
	IdCard,
	Mail,
	Map,
	MapPin,
	Pencil,
	Phone,
	Shield,
	Smartphone,
	Sparkles,
	StickyNote,
	Tag,
	TrendingUp,
	User,
	UserCog,
	Users,
	Wallet,
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
import { AvatarUpload } from '../employee-profile/AvatarUpload';
import { EmployeeGeneralSections } from './general/EmployeeGeneralSections';
import { OverviewStats } from './general/OverviewStats';
import { EmployeeJobTab } from './job/EmployeeJobTab';
import { EmployeeLeaveTab } from './leave/EmployeeLeaveTab';
import { EmployeeNotesTab } from './notes/EmployeeNotesTab';
import type { Option } from '../employee-profile/options';
import {
	BLOOD_GROUP_OPTIONS,
	GENDER_OPTIONS,
	MARITAL_OPTIONS,
	PAY_TYPE_OPTIONS,
	SOURCE_OPTIONS,
	STATUS_OPTIONS,
	TYPE_OPTIONS,
} from '../employee-profile/options';
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

/** Status → Badge tone. */
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

interface TabDef {
	readonly value: string;
	readonly label: string;
	readonly icon:  LucideIcon;
}

/** Left-sidebar nav button — active row is a solid primary (blue) pill. */
function SideTab( {
	tab,
	current,
	onSelect,
}: {
	readonly tab:      TabDef;
	readonly current:  string;
	readonly onSelect: ( value: string ) => void;
} ): JSX.Element {
	const isActive = current === tab.value;
	const Icon = tab.icon;
	return (
		<button
			type="button"
			onClick={ () => onSelect( tab.value ) }
			aria-current={ isActive ? 'page' : undefined }
			className={ [
				'flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm font-medium transition-colors',
				isActive ? 'bg-primary text-primary-foreground' : 'text-foreground hover:bg-muted',
			].join( ' ' ) }
		>
			<Icon size={ 16 } aria-hidden="true" />
			{ tab.label }
		</button>
	);
}

interface DetailCardProps {
	readonly title:    string;
	readonly children: ReactNode;
}

function DetailCard( { title, children }: DetailCardProps ): JSX.Element {
	return (
		<section className="rounded-xl bg-card p-6 shadow-sm ring-1 ring-border/60">
			<h2 className="mt-0 text-lg font-semibold leading-tight tracking-tight text-foreground">{ title }</h2>
			<div className="mb-5 mt-4 h-px w-full bg-border" />
			<dl className="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
				{ children }
			</dl>
		</section>
	);
}

function Item( { label, value, icon: Icon }: { readonly label: string; readonly value: string; readonly icon?: LucideIcon } ): JSX.Element {
	return (
		<div className="flex items-start gap-2.5">
			{ Icon ? (
				<span className="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
					<Icon size={ 14 } aria-hidden="true" />
				</span>
			) : null }
			<div className="flex min-w-0 flex-col gap-0.5">
				<dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{ label }</dt>
				<dd className="text-sm text-foreground">{ value.trim() === '' ? '—' : value }</dd>
			</div>
		</div>
	);
}

export function EmployeeProfileV4Inner( { userId }: { userId: number } ): JSX.Element {
	const navigate     = useNavigate();
	const canEditCap   = useCan( 'erp_edit_employee' );
	const canViewNotes = useCan( 'erp_manage_review' );
	const canViewPerf  = useCan( 'erp_create_review' );

	const currentUserId = useSelect(
		( select ) => ( select( meStoreName ) as unknown as { getUser: () => MeUser | null } ).getUser()?.id ?? 0,
		[]
	);
	// Own profile is self-service: an employee can view/edit their OWN profile
	// (Personal, Job, Leave, …) even without the manager `erp_edit_employee` cap —
	// mirrors the legacy "My Profile". `erp_edit_employee` is a meta-cap that the
	// generic `useCan` check resolves to false for employees.
	const canEdit = canEditCap || currentUserId === userId;
	const canViewPermission = canViewPerf && currentUserId !== userId;
	const { fetchEmployeeForEdit } = useDispatch( employeesStoreName ) as unknown as SingleDispatch;

	const [ record, setRecord ] = useState< Record_ | null >( null );
	const [ loadError, setLoadError ] = useState< string | null >( null );
	const [ tab, setTab ] = useState( 'overview' );

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

	function back(): void {
		if ( window.history.length > 1 ) {
			navigate( -1 );
		} else {
			navigate( '/employees' );
		}
	}

	if ( loadError ) {
		return (
			<div className="mx-auto w-full max-w-7xl text-center text-sm text-destructive">
				{ loadError }
			</div>
		);
	}

	if ( ! record ) {
		return (
			<div className="mx-auto w-full max-w-7xl space-y-6">
				<Skeleton className="h-44 w-full rounded-xl" />
				<div className="flex gap-6">
					<Skeleton className="h-72 w-60 shrink-0 rounded-xl" />
					<Skeleton className="h-72 flex-1 rounded-xl" />
				</div>
			</div>
		);
	}

	const fullName  = str( record, 'full_name' );
	const avatarUrl = str( record, 'avatar_url' );
	const status    = str( record, 'status' );
	const role      = [ str( record, 'designation_name' ), str( record, 'department_name' ) ]
		.filter( ( s ) => s.trim() !== '' )
		.join( ' · ' );
	const email    = str( record, 'email' );

	const tabs: TabDef[] = [
		{ value: 'overview', label: __( 'Overview', 'erp' ), icon: User },
		...( canEdit ? [ { value: 'job', label: __( 'Job', 'erp' ), icon: Briefcase } ] : [] ),
		...( canEdit ? [ { value: 'leave', label: __( 'Leave', 'erp' ), icon: CalendarClock } ] : [] ),
		...( canViewNotes ? [ { value: 'notes', label: __( 'Notes', 'erp' ), icon: StickyNote } ] : [] ),
		...( canViewPerf ? [ { value: 'performance', label: __( 'Performance', 'erp' ), icon: TrendingUp } ] : [] ),
		...( canViewPermission ? [ { value: 'permission', label: __( 'Permission', 'erp' ), icon: Shield } ] : [] ),
		...extraTabs.map( ( t ) => ( { value: t.id, label: t.label, icon: t.icon } ) ),
	];

	return (
		<div className="mx-auto w-full max-w-7xl space-y-6">
			<button
				type="button"
				onClick={ back }
				className="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
			>
				<ArrowLeft size={ 16 } aria-hidden="true" />
				{ __( 'Back to People', 'erp' ) }
			</button>

			{ /* Header card — compact avatar-left, name + status, with a summary info row. */ }
			<section className="rounded-[10px] bg-card p-6 shadow-sm">
				<div className="flex flex-wrap items-start gap-5">
					{ canEdit ? (
						<AvatarUpload
							userId={ userId }
							avatarUrl={ avatarUrl }
							fullName={ fullName }
							initials={ initials( fullName ) }
							sizeClass="size-[90px]"
							fallbackClass="text-xl"
							onChange={ ( url ) => setRecord( ( prev ) => ( prev ? { ...prev, avatar_url: url } : prev ) ) }
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
							{ role ? <p className="m-0 truncate text-sm font-semibold text-foreground">{ role }</p> : null }
							{ email ? <p className="m-0 truncate text-sm text-muted-foreground">{ email }</p> : null }
						</div>
					</div>

					{ canEdit ? (
						<Button
							variant="default"
							size="sm"
							className="h-9 gap-1.5 px-4"
							onClick={ () => navigate( `/employees/${ userId }/edit`, { viewTransition: true } ) }
						>
							<Pencil size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
							{ __( 'Edit', 'erp' ) }
						</Button>
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

			{ /* Body — left sidebar nav (white card, blue active) + content. */ }
			<div className="flex flex-col gap-6 lg:flex-row lg:items-start">
				<aside className="shrink-0 lg:sticky lg:top-6 lg:w-60">
					<div className="rounded-[10px] bg-card p-3 shadow-sm">
						<nav aria-label={ __( 'Profile sections', 'erp' ) } className="space-y-1">
							{ tabs.map( ( t ) => (
								<SideTab key={ t.value } tab={ t } current={ tab } onSelect={ setTab } />
							) ) }
						</nav>
					</div>
				</aside>

				<div className="min-w-0 flex-1">
					{ tab === 'overview' ? (
						<div className="space-y-6">
							<OverviewStats
								userId={ userId }
								hiringDate={ str( record, 'hiring_date' ) }
								dateOfBirth={ str( record, 'date_of_birth' ) }
							/>
							<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'top' ] } />

							<DetailCard title={ __( 'Employment', 'erp' ) }>
								<Item icon={ IdCard } label={ __( 'Employee ID', 'erp' ) } value={ str( record, 'employee_id' ) } />
								<Item icon={ Briefcase } label={ __( 'Employee Type', 'erp' ) } value={ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) } />
								<Item icon={ Activity } label={ __( 'Employee Status', 'erp' ) } value={ labelOf( STATUS_OPTIONS, status ) } />
								<Item icon={ Calendar } label={ __( 'Date of Hire', 'erp' ) } value={ str( record, 'hiring_date' ) } />
								<Item icon={ CalendarOff } label={ __( 'Employee End Date', 'erp' ) } value={ str( record, 'end_date' ) } />
								<Item icon={ Building2 } label={ __( 'Department', 'erp' ) } value={ str( record, 'department_name' ) } />
								<Item icon={ Tag } label={ __( 'Job Title', 'erp' ) } value={ str( record, 'designation_name' ) } />
								<Item icon={ MapPin } label={ __( 'Location', 'erp' ) } value={ str( record, 'location_name' ) } />
								<Item icon={ UserCog } label={ __( 'Reporting To', 'erp' ) } value={ str( record, 'reporting_to_name' ) } />
								<Item icon={ Compass } label={ __( 'Source of Hire', 'erp' ) } value={ labelOf( SOURCE_OPTIONS, str( record, 'hiring_source' ) ) } />
								<Item icon={ DollarSign } label={ __( 'Pay Rate', 'erp' ) } value={ str( record, 'pay_rate' ) } />
								<Item icon={ Wallet } label={ __( 'Pay Type', 'erp' ) } value={ labelOf( PAY_TYPE_OPTIONS, str( record, 'pay_type' ) ) } />
							</DetailCard>

							<DetailCard title={ __( 'Contact', 'erp' ) }>
								<Item icon={ Mail } label={ __( 'Email', 'erp' ) } value={ str( record, 'email' ) } />
								<Item icon={ Mail } label={ __( 'Other Email', 'erp' ) } value={ str( record, 'other_email' ) } />
								<Item icon={ Smartphone } label={ __( 'Mobile', 'erp' ) } value={ str( record, 'mobile' ) } />
								<Item icon={ Phone } label={ __( 'Phone', 'erp' ) } value={ str( record, 'phone' ) } />
								<Item icon={ Phone } label={ __( 'Work Phone', 'erp' ) } value={ str( record, 'work_phone' ) } />
								<Item icon={ Globe } label={ __( 'Website', 'erp' ) } value={ str( record, 'user_url' ) } />
							</DetailCard>

							<DetailCard title={ __( 'Personal Details', 'erp' ) }>
								<Item icon={ Calendar } label={ __( 'Date of Birth', 'erp' ) } value={ str( record, 'date_of_birth' ) } />
								<Item icon={ User } label={ __( 'Gender', 'erp' ) } value={ labelOf( GENDER_OPTIONS, str( record, 'gender' ) ) } />
								<Item icon={ Heart } label={ __( 'Marital Status', 'erp' ) } value={ labelOf( MARITAL_OPTIONS, str( record, 'marital_status' ) ) } />
								<Item icon={ Droplets } label={ __( 'Blood Group', 'erp' ) } value={ labelOf( BLOOD_GROUP_OPTIONS, str( record, 'blood_group' ) ) } />
								<Item icon={ Flag } label={ __( 'Nationality', 'erp' ) } value={ str( record, 'nationality' ) } />
								<Item icon={ IdCard } label={ __( 'Driving License', 'erp' ) } value={ str( record, 'driving_license' ) } />
								<Item icon={ Sparkles } label={ __( 'Hobbies', 'erp' ) } value={ str( record, 'hobbies' ) } />
								<Item icon={ User } label={ __( "Father's name", 'erp' ) } value={ str( record, 'father_name' ) } />
								<Item icon={ User } label={ __( "Mother's name", 'erp' ) } value={ str( record, 'mother_name' ) } />
								<Item icon={ Users } label={ __( "Spouse's name", 'erp' ) } value={ str( record, 'spouse_name' ) } />
							</DetailCard>

							<DetailCard title={ __( 'Address', 'erp' ) }>
								<Item icon={ MapPin } label={ __( 'Address 1', 'erp' ) } value={ str( record, 'street_1' ) } />
								<Item icon={ MapPin } label={ __( 'Address 2', 'erp' ) } value={ str( record, 'street_2' ) } />
								<Item icon={ Building2 } label={ __( 'City', 'erp' ) } value={ str( record, 'city' ) } />
								<Item icon={ Map } label={ __( 'Province / State', 'erp' ) } value={ str( record, 'state' ) } />
								<Item icon={ Globe } label={ __( 'Country', 'erp' ) } value={ str( record, 'country' ) } />
								<Item icon={ Hash } label={ __( 'Post Code / Zip Code', 'erp' ) } value={ str( record, 'postal_code' ) } />
							</DetailCard>

							{ str( record, 'description' ).trim() !== '' ? (
								<section className="rounded-xl bg-card p-6 shadow-sm ring-1 ring-border/60">
									<h2 className="mt-0 text-lg font-semibold leading-tight tracking-tight text-foreground">{ __( 'Biography', 'erp' ) }</h2>
									<div className="mb-5 mt-4 h-px w-full bg-border" />
									<p className="whitespace-pre-line text-sm text-foreground">
										{ str( record, 'description' ) }
									</p>
								</section>
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
	);
}

export function EmployeeProfileV4Page(): JSX.Element {
	const { id } = useParams< { id: string } >();
	const userId = Number( id );

	return (
		<ErrorBoundary>
			{ Number.isFinite( userId ) && userId > 0 ? (
				<EmployeeProfileV4Inner userId={ userId } />
			) : (
				<div className="mx-auto my-12 max-w-md text-center text-sm text-muted-foreground">
					{ __( 'Invalid employee.', 'erp' ) }
				</div>
			) }
		</ErrorBoundary>
	);
}
