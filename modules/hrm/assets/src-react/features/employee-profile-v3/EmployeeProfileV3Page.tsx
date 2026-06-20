/**
 * Employee profile — layout variant v3 (`#/employees/{id}/profile-v3`).
 *
 * Dashboard-card aesthetic with a large SQUARE portrait as the anchor:
 *   - a big rounded-square image card with the name / designation / status
 *     resting on a solid legibility band at the bottom (no decorative gradient).
 *   - a quick stat strip (tenure, age, leave) beside it.
 *   - black-pill tabs over rounded detail cards laid out in a 2-column grid.
 *
 * Fully self-contained: keeps a private copy of every tab body under this folder
 * and imports nothing from `employee-create`. Delete the folder + the blocks
 * tagged `[NEW-PROFILE-V3]` to remove it cleanly.
 */

import { Badge, Skeleton } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { Briefcase, Building2, Calendar, Compass, DollarSign, Droplets, Flag, Globe, GraduationCap, Hash, Heart, Home, IdCard, Mail, Map, MapPin, Pencil, Phone, Smartphone, Tag, User, UserCog, Wallet } from 'lucide-react';
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

interface TabDef {
	readonly value: string;
	readonly label: string;
}

/** Horizontal tab bar — active chip is a black (foreground) pill. */
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

/** Detail card — icon chip + title + optional edit, then a 2-col label/value grid. */
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
		<section className="rounded-[10px] bg-card p-6 shadow-sm">
			<div className="flex items-center gap-3">
				<span className={ `inline-flex size-9 items-center justify-center rounded-xl ${ tone }` }>
					<Icon size={ 18 } strokeWidth={ 2 } aria-hidden="true" />
				</span>
				<h2 className="m-0 flex-1 text-2xl font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
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
			<div className="mb-4 mt-4 h-px w-full bg-border" />
			<dl className="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">{ children }</dl>
		</section>
	);
}

function Field( { label, value, icon: Icon }: { readonly label: string; readonly value: string; readonly icon?: LucideIcon } ): JSX.Element {
	return (
		<div className="flex items-start gap-2.5">
			{ Icon ? (
				<span className="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
					<Icon size={ 14 } aria-hidden="true" />
				</span>
			) : null }
			<div className="flex min-w-0 flex-col gap-0.5">
				<dt className="text-xs text-muted-foreground">{ label }</dt>
				<dd className="text-sm font-medium text-foreground">{ value.trim() === '' ? '—' : value }</dd>
			</div>
		</div>
	);
}

export function EmployeeProfileV3Inner( { userId }: { userId: number } ): JSX.Element {
	const navigate     = useNavigate();
	const canEditCap   = useCan( 'erp_edit_employee' );
	const canViewNotes = useCan( 'erp_manage_review' );
	const canViewPerf  = useCan( 'erp_create_review' );

	const currentUserId = useSelect(
		( select ) => ( select( meStoreName ) as unknown as { getUser: () => MeUser | null } ).getUser()?.id ?? 0,
		[]
	);
	// Own profile is self-service: viewable/editable even without the manager cap.
	const canEdit = canEditCap || currentUserId === userId;
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
			<div className="mx-auto w-full max-w-full text-center text-sm text-destructive">{ loadError }</div>
		);
	}

	if ( ! record ) {
		return (
			<div className="mx-auto w-full max-w-full">
				<div className="flex flex-col gap-6 lg:flex-row">
					<Skeleton className="aspect-square w-full rounded-3xl lg:w-72" />
					<div className="flex-1 space-y-4">
						<Skeleton className="h-28 w-full rounded-3xl" />
						<Skeleton className="h-12 w-96 rounded-full" />
						<Skeleton className="h-64 w-full rounded-3xl" />
					</div>
				</div>
			</div>
		);
	}

	const fullName    = str( record, 'full_name' );
	const avatarUrl   = str( record, 'avatar_url' );
	const status      = str( record, 'status' );
	const designation = str( record, 'designation_name' );
	const department  = str( record, 'department_name' );

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
		<div className="mx-auto w-full max-w-full space-y-6">
			<nav className="flex items-center gap-2 text-sm text-muted-foreground">
				<button type="button" onClick={ () => navigate( '/employees' ) } className="transition-colors hover:text-foreground">
					{ __( 'Employees', 'erp' ) }
				</button>
				<span aria-hidden="true">/</span>
				<span className="font-medium text-foreground">{ __( 'Employee Profile', 'erp' ) }</span>
			</nav>

			{ /* Hero — big square portrait + stat strip. */ }
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
						<button
							type="button"
							onClick={ goEdit }
							className="absolute right-3 top-3 inline-flex size-9 items-center justify-center rounded-full bg-card/90 text-foreground shadow-sm ring-1 ring-border transition-colors hover:bg-card"
							aria-label={ __( 'Edit employee', 'erp' ) }
							title={ __( 'Edit employee', 'erp' ) }
						>
							<Pencil size={ 15 } aria-hidden="true" />
						</button>
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
							<h1 className="m-0 truncate text-2xl font-bold tracking-tight text-foreground">
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

			<PillTabs tabs={ tabs } current={ tab } onSelect={ setTab } />

			<div>
				{ tab === 'personal' ? (
					<div className="space-y-6">
						<EmployeeExtraFieldsView employeeId={ userId } sections={ [ 'top' ] } />

						<InfoCard icon={ Briefcase } tone="bg-amber-100 text-amber-700" title={ __( 'Employment', 'erp' ) } onEdit={ canEdit ? goEdit : undefined }>
							<Field icon={ IdCard } label={ __( 'Employee ID', 'erp' ) } value={ str( record, 'employee_id' ) } />
							<Field icon={ Briefcase } label={ __( 'Employee Type', 'erp' ) } value={ labelOf( TYPE_OPTIONS, str( record, 'type' ) ) } />
							<Field icon={ Calendar } label={ __( 'Date of Hire', 'erp' ) } value={ str( record, 'hiring_date' ) } />
							<Field icon={ Building2 } label={ __( 'Department', 'erp' ) } value={ str( record, 'department_name' ) } />
							<Field icon={ Tag } label={ __( 'Job Title', 'erp' ) } value={ str( record, 'designation_name' ) } />
							<Field icon={ UserCog } label={ __( 'Reporting To', 'erp' ) } value={ str( record, 'reporting_to_name' ) } />
							<Field icon={ Compass } label={ __( 'Source of Hire', 'erp' ) } value={ labelOf( SOURCE_OPTIONS, str( record, 'hiring_source' ) ) } />
							<Field icon={ DollarSign } label={ __( 'Pay Rate', 'erp' ) } value={ str( record, 'pay_rate' ) } />
							<Field icon={ Wallet } label={ __( 'Pay Type', 'erp' ) } value={ labelOf( PAY_TYPE_OPTIONS, str( record, 'pay_type' ) ) } />
						</InfoCard>

						<InfoCard icon={ Mail } tone="bg-sky-100 text-sky-700" title={ __( 'Contact', 'erp' ) } onEdit={ canEdit ? goEdit : undefined }>
							<Field icon={ Mail } label={ __( 'Email', 'erp' ) } value={ str( record, 'email' ) } />
							<Field icon={ Mail } label={ __( 'Other Email', 'erp' ) } value={ str( record, 'other_email' ) } />
							<Field icon={ Smartphone } label={ __( 'Mobile', 'erp' ) } value={ str( record, 'mobile' ) } />
							<Field icon={ Phone } label={ __( 'Phone', 'erp' ) } value={ str( record, 'phone' ) } />
							<Field icon={ Phone } label={ __( 'Work Phone', 'erp' ) } value={ str( record, 'work_phone' ) } />
							<Field icon={ Globe } label={ __( 'Website', 'erp' ) } value={ str( record, 'user_url' ) } />
						</InfoCard>

						<InfoCard icon={ GraduationCap } tone="bg-violet-100 text-violet-700" title={ __( 'Personal Details', 'erp' ) } onEdit={ canEdit ? goEdit : undefined }>
							<Field icon={ Calendar } label={ __( 'Date of Birth', 'erp' ) } value={ str( record, 'date_of_birth' ) } />
							<Field icon={ User } label={ __( 'Gender', 'erp' ) } value={ labelOf( GENDER_OPTIONS, str( record, 'gender' ) ) } />
							<Field icon={ Heart } label={ __( 'Marital Status', 'erp' ) } value={ labelOf( MARITAL_OPTIONS, str( record, 'marital_status' ) ) } />
							<Field icon={ Droplets } label={ __( 'Blood Group', 'erp' ) } value={ labelOf( BLOOD_GROUP_OPTIONS, str( record, 'blood_group' ) ) } />
							<Field icon={ Flag } label={ __( 'Nationality', 'erp' ) } value={ str( record, 'nationality' ) } />
							<Field icon={ User } label={ __( "Father's name", 'erp' ) } value={ str( record, 'father_name' ) } />
							<Field icon={ User } label={ __( "Mother's name", 'erp' ) } value={ str( record, 'mother_name' ) } />
						</InfoCard>

						<InfoCard icon={ Home } tone="bg-rose-100 text-rose-700" title={ __( 'Home Address', 'erp' ) } onEdit={ canEdit ? goEdit : undefined }>
							<Field icon={ MapPin } label={ __( 'Address', 'erp' ) } value={ str( record, 'street_1' ) } />
							<Field icon={ MapPin } label={ __( 'Address (cont.)', 'erp' ) } value={ str( record, 'street_2' ) } />
							<Field icon={ Building2 } label={ __( 'City', 'erp' ) } value={ str( record, 'city' ) } />
							<Field icon={ Map } label={ __( 'Province / State', 'erp' ) } value={ str( record, 'state' ) } />
							<Field icon={ Globe } label={ __( 'Country', 'erp' ) } value={ str( record, 'country' ) } />
							<Field icon={ Hash } label={ __( 'Postal code', 'erp' ) } value={ str( record, 'postal_code' ) } />
						</InfoCard>

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
	);
}

export function EmployeeProfileV3Page(): JSX.Element {
	const { id } = useParams< { id: string } >();
	const userId = Number( id );

	return (
		<ErrorBoundary>
			{ Number.isFinite( userId ) && userId > 0 ? (
				<EmployeeProfileV3Inner userId={ userId } />
			) : (
				<div className="mx-auto my-12 max-w-md text-center text-sm text-muted-foreground">
					{ __( 'Invalid employee.', 'erp' ) }
				</div>
			) }
		</ErrorBoundary>
	);
}
