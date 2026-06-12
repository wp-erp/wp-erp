/**
 * Full-page read-only employee detail view (`#/employees/{id}/profile-v4`).
 *
 * Loads the record from `GET /erp/v2/employees/{id}` (the same flat payload the
 * edit form uses, plus resolved *_name display fields) and renders it grouped
 * into cards — Employment, Contact, Personal, Address, Biography — mirroring the
 * legacy single-page "General" tab. Header card carries the avatar, name,
 * designation, status badge and an Edit action.
 *
 * Deeper tabs (Leave, Notes, Performance, History) are separate deliverables.
 */

import {
	Avatar,
	AvatarFallback,
	AvatarImage,
	Badge,
	Button,
	Skeleton,
	Tabs,
	TabsContent,
	TabsList,
	TabsTrigger,
} from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { Activity, ArrowLeft, Briefcase, Building2, Calendar, CalendarClock, CalendarOff, Compass, DollarSign, Droplets, Flag, Globe, Hash, Heart, IdCard, Mail, Map, MapPin, Pencil, Phone, Shield, Smartphone, Sparkles, StickyNote, Tag, TrendingUp, User, UserCog, Users, Wallet } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { ComponentType, JSX, ReactNode, SVGProps } from 'react';
import { useNavigate, useParams } from 'react-router-dom';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

import { AvatarUpload } from '../employee-create/AvatarUpload';
import { EmployeeExtraFieldsView } from '../employee-create/EmployeeExtraFieldsView';
import { useProfileExtraTabs } from '../employee-create/profile-tabs';
import { EmployeeGeneralSections } from '../employee-create/general/EmployeeGeneralSections';
import { OverviewStats } from '../employee-create/general/OverviewStats';
import { EmployeeJobTab } from '../employee-create/job/EmployeeJobTab';
import { EmployeeLeaveTab } from '../employee-create/leave/EmployeeLeaveTab';
import { EmployeeNotesTab } from '../employee-create/notes/EmployeeNotesTab';
import { EmployeePerformanceTab } from '../employee-create/performance/EmployeePerformanceTab';
import { EmployeePermissionTab } from '../employee-create/permission/EmployeePermissionTab';
import type { Option } from '../employee-create/options';
import {
	BLOOD_GROUP_OPTIONS,
	GENDER_OPTIONS,
	MARITAL_OPTIONS,
	PAY_TYPE_OPTIONS,
	SOURCE_OPTIONS,
	STATUS_OPTIONS,
	TYPE_OPTIONS,
} from '../employee-create/options';

interface SingleDispatch {
	fetchEmployeeForEdit: ( userId: number ) => Promise< Record< string, unknown > >;
}

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

// Segmented-pill tab matching the Reports tabs. The active chip (white card +
// primary text + ring) is driven off our own `current` state rather than a
// plugin-ui/base-ui data attribute, so the blue active style is reliable.
function ProfileTab( {
	value,
	current,
	icon: Icon,
	children,
}: {
	readonly value:    string;
	readonly current:  string;
	readonly icon:     LucideIcon;
	readonly children: ReactNode;
} ): JSX.Element {
	const isActive = current === value;
	return (
		<TabsTrigger
			value={ value }
			className={ [
				'!flex-none shrink-0 grow-0 rounded-md px-3 py-1.5 text-sm font-medium ring-1 ring-transparent transition-all',
				isActive ? '!bg-card !shadow-sm !ring-primary/40' : '!bg-transparent !shadow-none',
			].join( ' ' ) }
		>
			{ /* Colour lives on this inner span so it beats plugin-ui's trigger
			    colour rule; the icon inherits via currentColor. */ }
			<span
				className={ [
					'inline-flex items-center gap-2',
					isActive ? '!text-primary' : '!text-muted-foreground',
				].join( ' ' ) }
			>
				<Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
				{ children }
			</span>
		</TabsTrigger>
	);
}

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

interface DetailCardProps {
	readonly title:    string;
	readonly children: ReactNode;
}

function DetailCard( { title, children }: DetailCardProps ): JSX.Element {
	return (
		<section className="rounded-[10px] bg-card p-6 shadow-sm">
			<h2 className="mt-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
			<div className="mb-4 mt-4 h-px w-full bg-border" />
			<dl className="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
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

export function EmployeeProfileInner( { userId }: { userId: number } ): JSX.Element {
	const navigate = useNavigate();
	const canEditCap   = useCan( 'erp_edit_employee' );
	const canViewNotes = useCan( 'erp_manage_review' );
	const canViewPerf  = useCan( 'erp_create_review' );

	const currentUserId = useSelect(
		( select ) => ( select( meStoreName ) as unknown as { getUser: () => MeUser | null } ).getUser()?.id ?? 0,
		[]
	);
	// Own profile is self-service: viewable/editable even without the manager cap.
	const canEdit = canEditCap || currentUserId === userId;
	// Permission tab: same gate as legacy — needs review cap and is hidden when
	// viewing your own profile (can't change your own role here).
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
				<Skeleton className="h-28 w-full rounded-lg" />
				{ [ 0, 1, 2 ].map( ( i ) => (
					<Skeleton key={ i } className="h-44 w-full rounded-lg" />
				) ) }
			</div>
		);
	}

	const fullName   = str( record, 'full_name' );
	const avatarUrl  = str( record, 'avatar_url' );
	const status     = str( record, 'status' );

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

				{ /* Header card — spacing per Figma 1533:26766 (avatar 90, padding 24, name 24px). */ }
				<section className="flex flex-wrap items-start gap-5 rounded-[10px] bg-card p-6 shadow-sm">
					{ canEdit ? (
						<AvatarUpload
							userId={ userId }
							avatarUrl={ avatarUrl }
							fullName={ fullName }
							initials={ initials( fullName ) }
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
								onClick={ () => navigate( `/employees/${ userId }/edit`, { viewTransition: true } ) }
							>
								<Pencil size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
								{ __( 'Edit', 'erp' ) }
							</Button>
						) : null }
					</div>
				</section>

				<Tabs value={ tab } onValueChange={ ( value ) => setTab( String( value ) ) }>
					<TabsList className="!inline-flex !w-fit max-w-full justify-start overflow-x-auto scrollbar-none">
						<ProfileTab value="overview" current={ tab } icon={ User }>{ __( 'Overview', 'erp' ) }</ProfileTab>
						{ canEdit ? (
							<ProfileTab value="job" current={ tab } icon={ Briefcase }>{ __( 'Job', 'erp' ) }</ProfileTab>
						) : null }
						{ canEdit ? (
							<ProfileTab value="leave" current={ tab } icon={ CalendarClock }>{ __( 'Leave', 'erp' ) }</ProfileTab>
						) : null }
						{ canViewNotes ? (
							<ProfileTab value="notes" current={ tab } icon={ StickyNote }>{ __( 'Notes', 'erp' ) }</ProfileTab>
						) : null }
						{ canViewPerf ? (
							<ProfileTab value="performance" current={ tab } icon={ TrendingUp }>{ __( 'Performance', 'erp' ) }</ProfileTab>
						) : null }
						{ canViewPermission ? (
							<ProfileTab value="permission" current={ tab } icon={ Shield }>{ __( 'Permission', 'erp' ) }</ProfileTab>
						) : null }
						{ extraTabs.map( ( t ) => (
							<ProfileTab key={ t.id } value={ t.id } current={ tab } icon={ t.icon }>{ t.label }</ProfileTab>
						) ) }
					</TabsList>

					<TabsContent value="overview" className="mt-6 space-y-6">
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
					<section className="rounded-[10px] bg-card p-6 shadow-sm">
						<h2 className="mt-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Biography', 'erp' ) }</h2>
						<div className="mb-4 mt-4 h-px w-full bg-border" />
						<p className="whitespace-pre-line text-sm text-foreground">
							{ str( record, 'description' ) }
						</p>
					</section>
				) : null }

				<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'basic', 'work', 'personal', 'bottom' ] } />

				{ canEdit ? <EmployeeGeneralSections userId={ userId } /> : null }
					</TabsContent>

					{ canEdit ? (
						<TabsContent value="job" className="mt-6">
							<EmployeeJobTab userId={ userId } />
						</TabsContent>
					) : null }

					{ canEdit ? (
						<TabsContent value="leave" className="mt-6">
							<EmployeeLeaveTab userId={ userId } />
						</TabsContent>
					) : null }

					{ canViewNotes ? (
						<TabsContent value="notes" className="mt-6">
							<EmployeeNotesTab userId={ userId } />
						</TabsContent>
					) : null }

					{ canViewPerf ? (
						<TabsContent value="performance" className="mt-6">
							<EmployeePerformanceTab userId={ userId } />
						</TabsContent>
					) : null }

					{ canViewPermission ? (
						<TabsContent value="permission" className="mt-6">
							<EmployeePermissionTab userId={ userId } />
						</TabsContent>
					) : null }
					{ extraTabs.map( ( t ) => (
						<TabsContent key={ t.id } value={ t.id } className="mt-6">
							{ t.render( { userId, canEdit } ) }
						</TabsContent>
					) ) }
				</Tabs>
		</div>
	);
}

export function EmployeeProfilePage(): JSX.Element {
	const { id } = useParams< { id: string } >();
	const userId = Number( id );

	// Route-level access is enforced by the router's CapabilityGate
	// (`erp_list_employee`); sensitive sections self-gate per tab below.
	return (
		<ErrorBoundary>
			{ Number.isFinite( userId ) && userId > 0 ? (
				<EmployeeProfileInner userId={ userId } />
			) : (
				<div className="mx-auto my-12 max-w-md text-center text-sm text-muted-foreground">
					{ __( 'Invalid employee.', 'erp' ) }
				</div>
			) }
		</ErrorBoundary>
	);
}
