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
	ArrowLeft,
	Briefcase,
	CalendarClock,
	Mail,
	MapPin,
	Pencil,
	Phone,
	Shield,
	StickyNote,
	TrendingUp,
	User,
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
import { OverviewStats } from './general/OverviewStats';
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

/** Left-sidebar nav button — active row gets a card surface + primary accent bar. */
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
				'group relative flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium transition-colors',
				isActive
					? 'bg-card text-foreground shadow-sm ring-1 ring-border'
					: 'text-muted-foreground hover:bg-card/60 hover:text-foreground',
			].join( ' ' ) }
		>
			<span
				className={ [
					'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-primary transition-opacity',
					isActive ? 'opacity-100' : 'opacity-0',
				].join( ' ' ) }
				aria-hidden="true"
			/>
			<Icon
				size={ 18 }
				strokeWidth={ 2 }
				className={ isActive ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground' }
				aria-hidden="true"
			/>
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

function Item( { label, value }: { readonly label: string; readonly value: string } ): JSX.Element {
	return (
		<div className="flex flex-col gap-0.5">
			<dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{ label }</dt>
			<dd className="text-sm text-foreground">{ value.trim() === '' ? '—' : value }</dd>
		</div>
	);
}

export function EmployeeProfileInner( { userId }: { userId: number } ): JSX.Element {
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
	const mobile   = str( record, 'mobile' );
	const location = str( record, 'location_name' );

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

			{ /* Hero header — large avatar, flat surface, no gradient. */ }
			<section className="rounded-2xl bg-card p-8 shadow-sm ring-1 ring-border/60">
				<div className="flex flex-col items-center gap-6 sm:flex-row sm:items-center sm:gap-8">
					<div className="shrink-0">
						{ canEdit ? (
							<AvatarUpload
								userId={ userId }
								avatarUrl={ avatarUrl }
								fullName={ fullName }
								initials={ initials( fullName ) }
								sizeClass="size-36"
								fallbackClass="text-4xl"
								onChange={ ( url ) => setRecord( ( prev ) => ( prev ? { ...prev, avatar_url: url } : prev ) ) }
							/>
						) : (
							<Avatar className="size-36 shrink-0 ring-4 ring-background shadow-md">
								{ avatarUrl ? <AvatarImage src={ avatarUrl } alt={ fullName } /> : null }
								<AvatarFallback className="text-4xl">{ initials( fullName ) }</AvatarFallback>
							</Avatar>
						) }
					</div>

					<div className="flex min-w-0 flex-1 flex-col items-center gap-3 sm:items-start">
						<div className="flex flex-wrap items-center justify-center gap-3 sm:justify-start">
							<h1 className="text-3xl font-bold leading-tight tracking-tight text-foreground">
								{ fullName || __( 'Employee', 'erp' ) }
							</h1>
							{ status ? (
								<Badge variant={ statusVariant( status ) }>{ labelOf( STATUS_OPTIONS, status ) }</Badge>
							) : null }
						</div>
						{ role ? (
							<p className="text-base font-semibold text-foreground">{ role }</p>
						) : null }
						<div className="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm text-muted-foreground sm:justify-start">
							{ email ? (
								<span className="inline-flex items-center gap-1.5">
									<Mail size={ 15 } aria-hidden="true" />
									{ email }
								</span>
							) : null }
							{ mobile ? (
								<span className="inline-flex items-center gap-1.5">
									<Phone size={ 15 } aria-hidden="true" />
									{ mobile }
								</span>
							) : null }
							{ location ? (
								<span className="inline-flex items-center gap-1.5">
									<MapPin size={ 15 } aria-hidden="true" />
									{ location }
								</span>
							) : null }
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
			</section>

			{ /* Body — left sidebar nav + content. */ }
			<div className="flex flex-col gap-6 lg:flex-row lg:items-start">
				<nav
					aria-label={ __( 'Profile sections', 'erp' ) }
					className="flex shrink-0 gap-1 overflow-x-auto rounded-xl bg-muted/40 p-1.5 lg:sticky lg:top-6 lg:w-56 lg:flex-col lg:overflow-visible"
				>
					{ tabs.map( ( t ) => (
						<SideTab key={ t.value } tab={ t } current={ tab } onSelect={ setTab } />
					) ) }
				</nav>

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
								<Item label={ __( 'Employee ID', 'erp' ) } value={ str( record, 'employee_id' ) } />
								<Item label={ __( 'Employee Type', 'erp' ) } value={ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) } />
								<Item label={ __( 'Employee Status', 'erp' ) } value={ labelOf( STATUS_OPTIONS, status ) } />
								<Item label={ __( 'Date of Hire', 'erp' ) } value={ str( record, 'hiring_date' ) } />
								<Item label={ __( 'Employee End Date', 'erp' ) } value={ str( record, 'end_date' ) } />
								<Item label={ __( 'Department', 'erp' ) } value={ str( record, 'department_name' ) } />
								<Item label={ __( 'Job Title', 'erp' ) } value={ str( record, 'designation_name' ) } />
								<Item label={ __( 'Location', 'erp' ) } value={ str( record, 'location_name' ) } />
								<Item label={ __( 'Reporting To', 'erp' ) } value={ str( record, 'reporting_to_name' ) } />
								<Item label={ __( 'Source of Hire', 'erp' ) } value={ labelOf( SOURCE_OPTIONS, str( record, 'hiring_source' ) ) } />
								<Item label={ __( 'Pay Rate', 'erp' ) } value={ str( record, 'pay_rate' ) } />
								<Item label={ __( 'Pay Type', 'erp' ) } value={ labelOf( PAY_TYPE_OPTIONS, str( record, 'pay_type' ) ) } />
							</DetailCard>

							<DetailCard title={ __( 'Contact', 'erp' ) }>
								<Item label={ __( 'Email', 'erp' ) } value={ str( record, 'email' ) } />
								<Item label={ __( 'Other Email', 'erp' ) } value={ str( record, 'other_email' ) } />
								<Item label={ __( 'Mobile', 'erp' ) } value={ str( record, 'mobile' ) } />
								<Item label={ __( 'Phone', 'erp' ) } value={ str( record, 'phone' ) } />
								<Item label={ __( 'Work Phone', 'erp' ) } value={ str( record, 'work_phone' ) } />
								<Item label={ __( 'Website', 'erp' ) } value={ str( record, 'user_url' ) } />
							</DetailCard>

							<DetailCard title={ __( 'Personal Details', 'erp' ) }>
								<Item label={ __( 'Date of Birth', 'erp' ) } value={ str( record, 'date_of_birth' ) } />
								<Item label={ __( 'Gender', 'erp' ) } value={ labelOf( GENDER_OPTIONS, str( record, 'gender' ) ) } />
								<Item label={ __( 'Marital Status', 'erp' ) } value={ labelOf( MARITAL_OPTIONS, str( record, 'marital_status' ) ) } />
								<Item label={ __( 'Blood Group', 'erp' ) } value={ labelOf( BLOOD_GROUP_OPTIONS, str( record, 'blood_group' ) ) } />
								<Item label={ __( 'Nationality', 'erp' ) } value={ str( record, 'nationality' ) } />
								<Item label={ __( 'Driving License', 'erp' ) } value={ str( record, 'driving_license' ) } />
								<Item label={ __( 'Hobbies', 'erp' ) } value={ str( record, 'hobbies' ) } />
								<Item label={ __( "Father's name", 'erp' ) } value={ str( record, 'father_name' ) } />
								<Item label={ __( "Mother's name", 'erp' ) } value={ str( record, 'mother_name' ) } />
								<Item label={ __( "Spouse's name", 'erp' ) } value={ str( record, 'spouse_name' ) } />
							</DetailCard>

							<DetailCard title={ __( 'Address', 'erp' ) }>
								<Item label={ __( 'Address 1', 'erp' ) } value={ str( record, 'street_1' ) } />
								<Item label={ __( 'Address 2', 'erp' ) } value={ str( record, 'street_2' ) } />
								<Item label={ __( 'City', 'erp' ) } value={ str( record, 'city' ) } />
								<Item label={ __( 'Province / State', 'erp' ) } value={ str( record, 'state' ) } />
								<Item label={ __( 'Country', 'erp' ) } value={ str( record, 'country' ) } />
								<Item label={ __( 'Post Code / Zip Code', 'erp' ) } value={ str( record, 'postal_code' ) } />
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

export function EmployeeProfilePage(): JSX.Element {
	const { id } = useParams< { id: string } >();
	const userId = Number( id );

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
