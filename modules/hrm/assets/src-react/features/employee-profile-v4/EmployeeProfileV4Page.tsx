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
 *
 * Chrome pieces live alongside: `ProfileHeader` (header card), `SideTab`
 * (sidebar nav), `OverviewTab` (overview body), `DetailCard`/`Item` (cards),
 * `profile-format` (pure helpers).
 */

import { Skeleton } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	ArrowLeft,
	Briefcase,
	CalendarClock,
	Shield,
	StickyNote,
	TrendingUp,
	User,
} from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import type { JSX, ReactNode } from 'react';
import { useNavigate, useParams, useSearchParams } from 'react-router-dom';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { request, restPath } from '@/shared/utils/apiFetch';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

import { useProfileExtraTabs } from '../employee-create/profile-tabs';
import { OverviewTab } from './OverviewTab';
import { ProfileHeader } from './ProfileHeader';
import { SideTab, type TabDef } from './SideTab';
import { EmployeeJobTab } from './job/EmployeeJobTab';
import { EmployeeLeaveTab } from './leave/EmployeeLeaveTab';
import { EmployeeNotesTab } from './notes/EmployeeNotesTab';
import { EmployeePerformanceTab } from './performance/EmployeePerformanceTab';
import { EmployeePermissionTab } from './permission/EmployeePermissionTab';
import type { Record_ } from './profile-format';

interface SingleDispatch {
	fetchEmployeeForEdit: ( userId: number ) => Promise< Record< string, unknown > >;
}

export function EmployeeProfileV4Inner( { userId, headerActions }: { userId: number; headerActions?: ReactNode } ): JSX.Element {
	const navigate     = useNavigate();
	const canEditCap   = useCan( 'erp_edit_employee' );
	const canViewNotesCap = useCan( 'erp_manage_review' );
	const canViewPerfCap  = useCan( 'erp_create_review' );

	// Per-target caps (meta-mapped against THIS employee). The global `useCan`
	// map resolves review/edit caps to manager-only; a department lead is granted
	// review caps only for their direct reports, so the Notes/Performance tabs
	// need this target-aware resolution to match legacy `single.php`.
	const [ targetCaps, setTargetCaps ] = useState< Record< string, boolean > >( {} );
	useEffect( () => {
		if ( ! userId ) {
			return;
		}
		const ctrl = new AbortController();
		request< { capabilities: Record< string, boolean > } >(
			restPath( 'v2', `/me/employee-capabilities/${ userId }` ),
			{ signal: ctrl.signal }
		)
			.then( ( res ) => setTargetCaps( res.capabilities ?? {} ) )
			.catch( () => undefined );
		return () => ctrl.abort();
	}, [ userId ] );

	const currentUserId = useSelect(
		( select ) => ( select( meStoreName ) as unknown as { getUser: () => MeUser | null } ).getUser()?.id ?? 0,
		[]
	);
	// Own profile is self-service: an employee can view/edit their OWN profile
	// (Personal, Job, Leave, …) even without the manager `erp_edit_employee` cap —
	// mirrors the legacy "My Profile". `erp_edit_employee` is a meta-cap that the
	// generic `useCan` check resolves to false for employees.
	const canEdit = canEditCap || currentUserId === userId || Boolean( targetCaps.erp_edit_employee );
	// Notes / Performance also surface for a department lead viewing a report
	// (per-target caps), not just full HR managers.
	const canViewNotes = canViewNotesCap || Boolean( targetCaps.erp_manage_review );
	const canViewPerf  = canViewPerfCap || Boolean( targetCaps.erp_create_review );
	// Permission tab stays manager-only (global cap) and never for self — legacy
	// single.php removes it for self and shows it only to managers.
	const canViewPermission = canViewPerfCap && currentUserId !== userId;
	const { fetchEmployeeForEdit } = useDispatch( employeesStoreName ) as unknown as SingleDispatch;

	const [ record, setRecord ] = useState< Record_ | null >( null );
	const [ loadError, setLoadError ] = useState< string | null >( null );

	// Active tab lives in the URL (`?tab=job`) so a refresh / deep-link keeps the
	// open tab instead of snapping back to Overview. `replace` so tab switches
	// don't pile up browser-history entries.
	const [ searchParams, setSearchParams ] = useSearchParams();
	const rawTab = searchParams.get( 'tab' ) ?? 'overview';
	const setTab = useCallback( ( value: string ): void => {
		setSearchParams(
			( prev ) => {
				const out = new URLSearchParams( prev );
				out.set( 'tab', value );
				return out;
			},
			{ replace: true }
		);
	}, [ setSearchParams ] );

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
				<Skeleton className="h-44 w-full rounded-xl" />
				<div className="flex gap-6">
					<Skeleton className="h-72 w-60 shrink-0 rounded-xl" />
					<Skeleton className="h-72 flex-1 rounded-xl" />
				</div>
			</div>
		);
	}

	const tabs: TabDef[] = [
		{ value: 'overview', label: __( 'Overview', 'erp' ), icon: User },
		...( canEdit ? [ { value: 'job', label: __( 'Job', 'erp' ), icon: Briefcase } ] : [] ),
		...( canEdit ? [ { value: 'leave', label: __( 'Leave', 'erp' ), icon: CalendarClock } ] : [] ),
		...( canViewNotes ? [ { value: 'notes', label: __( 'Notes', 'erp' ), icon: StickyNote } ] : [] ),
		...( canViewPerf ? [ { value: 'performance', label: __( 'Performance', 'erp' ), icon: TrendingUp } ] : [] ),
		...( canViewPermission ? [ { value: 'permission', label: __( 'Permission', 'erp' ), icon: Shield } ] : [] ),
		...extraTabs.map( ( t ) => ( { value: t.id, label: t.label, icon: t.icon } ) ),
	];

	// Fall back to Overview when the URL names a tab the current user can't see
	// (e.g. a deep-link to ?tab=permission without the cap) so the body never blanks.
	const tab = tabs.some( ( t ) => t.value === rawTab ) ? rawTab : 'overview';

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
				onAvatarChange={ ( url ) => setRecord( ( prev ) => ( prev ? { ...prev, avatar_url: url } : prev ) ) }
				extraActions={ headerActions }
			/>

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
						<OverviewTab userId={ userId } record={ record } canEdit={ canEdit } />
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
