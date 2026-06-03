/**
 * Full-page read-only employee detail view (`#/employees/{id}`).
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
import { ArrowLeft, Pencil } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX, ReactNode } from 'react';
import { useNavigate, useParams } from 'react-router-dom';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

import { AvatarUpload } from './AvatarUpload';
import { EmployeeGeneralSections } from './general/EmployeeGeneralSections';
import { EmployeeJobTab } from './job/EmployeeJobTab';
import { EmployeeLeaveTab } from './leave/EmployeeLeaveTab';
import { EmployeeNotesTab } from './notes/EmployeeNotesTab';
import { EmployeePerformanceTab } from './performance/EmployeePerformanceTab';
import { EmployeePermissionTab } from './permission/EmployeePermissionTab';
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

interface SingleDispatch {
	fetchEmployeeForEdit: ( userId: number ) => Promise< Record< string, unknown > >;
}

// Underline tab style — matches the blue active tab used on the People table
// (StatusFilter). Plugin-ui's `line` border styles conflict/load after our CSS,
// so the active underline is drawn with an absolute span toggled by the
// trigger's `data-active` attribute (reliable, no border-color cascade fights).
const TAB_TRIGGER_CLASS = 'group relative shrink-0 flex-none rounded-none px-3 pb-2.5';

function ProfileTab( { value, children }: { readonly value: string; readonly children: ReactNode } ): JSX.Element {
	return (
		<TabsTrigger value={ value } className={ TAB_TRIGGER_CLASS }>
			{ children }
			<span
				aria-hidden="true"
				className="pointer-events-none absolute inset-x-0 bottom-0 h-0.5 bg-primary opacity-0 transition-opacity group-data-[active]:opacity-100"
			/>
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

function Item( { label, value }: { readonly label: string; readonly value: string } ): JSX.Element {
	return (
		<div className="flex flex-col gap-0.5">
			<dt className="text-xs font-medium text-muted-foreground">{ label }</dt>
			<dd className="text-sm text-foreground">{ value.trim() === '' ? '—' : value }</dd>
		</div>
	);
}

function EmployeeSingleInner( { userId }: { userId: number } ): JSX.Element {
	const navigate = useNavigate();
	const canEdit      = useCan( 'erp_edit_employee' );
	const canViewNotes = useCan( 'erp_manage_review' );
	const canViewPerf  = useCan( 'erp_create_review' );

	const currentUserId = useSelect(
		( select ) => ( select( meStoreName ) as unknown as { getUser: () => MeUser | null } ).getUser()?.id ?? 0,
		[]
	);
	// Permission tab: same gate as legacy — needs review cap and is hidden when
	// viewing your own profile (can't change your own role here).
	const canViewPermission = canViewPerf && currentUserId !== userId;
	const { fetchEmployeeForEdit } = useDispatch( employeesStoreName ) as unknown as SingleDispatch;

	const [ record, setRecord ] = useState< Record_ | null >( null );
	const [ loadError, setLoadError ] = useState< string | null >( null );
	const [ tab, setTab ] = useState( 'overview' );

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
			navigate( '/people-pro' );
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
					{ canEdit ? (
						<Button
							variant="default"
							size="sm"
							className="h-9 gap-1.5 px-4"
							onClick={ () => { window.location.hash = `#/employees/${ userId }/edit`; } }
						>
							<Pencil size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
							{ __( 'Edit', 'erp' ) }
						</Button>
					) : null }
				</section>

				<Tabs value={ tab } onValueChange={ ( value ) => setTab( String( value ) ) }>
					<TabsList variant="line" className="h-auto w-full justify-start gap-1 overflow-x-auto border-b border-border pb-0.5 scrollbar-none">
						<ProfileTab value="overview">{ __( 'Overview', 'erp' ) }</ProfileTab>
						{ canEdit ? (
							<ProfileTab value="job">{ __( 'Job', 'erp' ) }</ProfileTab>
						) : null }
						{ canEdit ? (
							<ProfileTab value="leave">{ __( 'Leave', 'erp' ) }</ProfileTab>
						) : null }
						{ canViewNotes ? (
							<ProfileTab value="notes">{ __( 'Notes', 'erp' ) }</ProfileTab>
						) : null }
						{ canViewPerf ? (
							<ProfileTab value="performance">{ __( 'Performance', 'erp' ) }</ProfileTab>
						) : null }
						{ canViewPermission ? (
							<ProfileTab value="permission">{ __( 'Permission', 'erp' ) }</ProfileTab>
						) : null }
					</TabsList>

					<TabsContent value="overview" className="mt-6 space-y-6">
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
					<section className="rounded-[10px] bg-card p-6 shadow-sm">
						<h2 className="mt-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Biography', 'erp' ) }</h2>
						<div className="mb-4 mt-4 h-px w-full bg-border" />
						<p className="whitespace-pre-line text-sm text-foreground">
							{ str( record, 'description' ) }
						</p>
					</section>
				) : null }

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
				</Tabs>
		</div>
	);
}

export function EmployeeSinglePage(): JSX.Element {
	const { id } = useParams< { id: string } >();
	const userId = Number( id );

	// Route-level access is enforced by the router's CapabilityGate
	// (`erp_list_employee`); sensitive sections self-gate per tab below.
	return (
		<ErrorBoundary>
			{ Number.isFinite( userId ) && userId > 0 ? (
				<EmployeeSingleInner userId={ userId } />
			) : (
				<div className="mx-auto my-12 max-w-md text-center text-sm text-muted-foreground">
					{ __( 'Invalid employee.', 'erp' ) }
				</div>
			) }
		</ErrorBoundary>
	);
}
