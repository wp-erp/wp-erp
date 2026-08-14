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
 *
 * Chrome pieces live alongside: `ProfileHeader` (header card), `ProfileTab`
 * (segmented-pill tab), `OverviewTab` (overview body), `DetailCard`/`Item`
 * (cards), `profile-format` (pure helpers).
 */

import {
	Skeleton,
	Tabs,
	TabsContent,
	TabsList,
} from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { ArrowLeft, Briefcase, CalendarClock, Shield, StickyNote, TrendingUp, User } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';
import { useNavigate, useParams } from 'react-router-dom';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

import { useProfileExtraTabs } from '../employee-create/profile-tabs';
import { EmployeeJobTab } from '../employee-create/job/EmployeeJobTab';
import { EmployeeLeaveTab } from '../employee-create/leave/EmployeeLeaveTab';
import { EmployeeNotesTab } from '../employee-create/notes/EmployeeNotesTab';
import { EmployeePerformanceTab } from '../employee-create/performance/EmployeePerformanceTab';
import { EmployeePermissionTab } from '../employee-create/permission/EmployeePermissionTab';
import { OverviewTab } from './OverviewTab';
import { ProfileHeader } from './ProfileHeader';
import { ProfileTab } from './ProfileTab';
import type { Record_ } from './profile-format';

interface SingleDispatch {
	fetchEmployeeForEdit: ( userId: number ) => Promise< Record< string, unknown > >;
	invalidate:           () => void;
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
	const { fetchEmployeeForEdit, invalidate } = useDispatch( employeesStoreName ) as unknown as SingleDispatch;

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
			<div className="mx-auto w-full max-w-full text-center text-sm text-destructive">
				{ loadError }
			</div>
		);
	}

	if ( ! record ) {
		return (
			<div className="mx-auto w-full max-w-full space-y-6">
				<Skeleton className="h-28 w-full rounded-lg" />
				{ [ 0, 1, 2 ].map( ( i ) => (
					<Skeleton key={ i } className="h-44 w-full rounded-lg" />
				) ) }
			</div>
		);
	}

	return (
		<div className="mx-auto w-full max-w-full space-y-6">
				<button
					type="button"
					onClick={ back }
					className="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
				>
					<ArrowLeft size={ 16 } aria-hidden="true" />
					{ __( 'Back to People', 'erp' ) }
				</button>

				<ProfileHeader
					record={ record }
					userId={ userId }
					canEdit={ canEdit }
					onEdit={ () => navigate( `/employees/${ userId }/edit`, { viewTransition: true } ) }
					onAvatarChange={ ( url ) => {
						setRecord( ( prev ) => ( prev ? { ...prev, avatar_url: url } : prev ) );
						invalidate();
					} }
				/>

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
						<OverviewTab userId={ userId } record={ record } canEdit={ canEdit } />
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
