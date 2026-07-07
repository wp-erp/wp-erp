/**
 * Employee profile — main view (`#/employees/{id}`), layout variant v4.
 *
 * "Candidate card" pattern from the reference design:
 *   - a full-width HEADER card: round avatar, name + edit, designation, email,
 *     a status chip, a row of quick-action buttons, and a primary action on the
 *     right; a meta strip underneath (job, department, status).
 *   - a LEFT nav card with an icon menu (active row highlighted in the brand
 *     blue) and a RIGHT content card that renders the selected section.
 *
 * Chrome pieces live alongside: `SingleHeader` (header card), `NavMenu`
 * (left nav), `SingleOverview` (personal-tab body), `FieldGrid`/`Field`
 * (cards), `single-format` (pure helpers). Tab bodies are reused from
 * `employee-profile-v0`.
 */

import { Skeleton } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	Briefcase,
	CalendarClock,
	Shield,
	StickyNote,
	TrendingUp,
	User,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';
import { useNavigate, useParams } from 'react-router-dom';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

import { EmployeeJobTab } from '../employee-profile-v0/job/EmployeeJobTab';
import { EmployeeLeaveTab } from '../employee-profile-v0/leave/EmployeeLeaveTab';
import { EmployeeNotesTab } from '../employee-profile-v0/notes/EmployeeNotesTab';
import { EmployeePerformanceTab } from '../employee-profile-v0/performance/EmployeePerformanceTab';
import { EmployeePermissionTab } from '../employee-profile-v0/permission/EmployeePermissionTab';
import { SingleHeader } from './SingleHeader';
import { SingleOverview } from './SingleOverview';
import { NavMenu, type NavItem } from './SingleNavMenu';
import { useProfileExtraTabs } from './profile-tabs';
import type { Record_ } from './single-format';

interface SingleDispatch {
	fetchEmployeeForEdit: ( userId: number ) => Promise< Record< string, unknown > >;
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
	// (Personal, Job, Leave, …) even without the manager `erp_edit_employee` cap.
	const canEdit = canEditCap || currentUserId === userId;
	const canViewPermission = canViewPerf && currentUserId !== userId;
	const { fetchEmployeeForEdit } = useDispatch( employeesStoreName ) as unknown as SingleDispatch;

	const [ record, setRecord ] = useState< Record_ | null >( null );
	const [ loadError, setLoadError ] = useState< string | null >( null );
	const [ tab, setTab ] = useState( 'personal' );

	// Pro-injectable profile tabs (e.g. Document Manager's Documents tab). MUST be
	// called before any early return below — Rules of Hooks.
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
		return <div className="mx-auto w-full max-w-full text-center text-sm text-destructive">{ loadError }</div>;
	}

	if ( ! record ) {
		return (
			<div className="mx-auto w-full max-w-full space-y-6">
				<Skeleton className="h-52 w-full rounded-2xl" />
				<div className="flex flex-col gap-6 lg:flex-row">
					<Skeleton className="h-96 w-full rounded-2xl lg:w-64" />
					<Skeleton className="h-96 flex-1 rounded-2xl" />
				</div>
			</div>
		);
	}

	const navItems: NavItem[] = [
		{ value: 'personal', label: __( 'Personal Information', 'erp' ), icon: User },
		...( canEdit ? [ { value: 'job', label: __( 'Job Information', 'erp' ), icon: Briefcase } ] : [] ),
		...( canEdit ? [ { value: 'leave', label: __( 'Leave', 'erp' ), icon: CalendarClock } ] : [] ),
		...( canViewNotes ? [ { value: 'notes', label: __( 'Notes', 'erp' ), icon: StickyNote } ] : [] ),
		...( canViewPerf ? [ { value: 'performance', label: __( 'Performance', 'erp' ), icon: TrendingUp } ] : [] ),
		...( canViewPermission ? [ { value: 'permission', label: __( 'Permission', 'erp' ), icon: Shield } ] : [] ),
		...extraTabs.map( ( t ) => ( { value: t.id, label: t.label, icon: t.icon } ) ),
	];

	const activeLabel = navItems.find( ( i ) => i.value === tab )?.label ?? '';

	return (
		<div className="mx-auto w-full max-w-full space-y-6">
			{ /* HEADER card. */ }
			<SingleHeader
				record={ record }
				canEdit={ canEdit }
				canViewNotes={ canViewNotes }
				onEdit={ goEdit }
				onSetTab={ setTab }
			/>

			{ /* BODY — left nav card + right content card. */ }
			<div className="flex flex-col gap-6 lg:flex-row lg:items-start">
				<aside className="shrink-0 lg:sticky lg:top-[88px] lg:w-64">
					<div className="rounded-2xl bg-card p-3 shadow-sm ring-1 ring-border/60">
						<NavMenu items={ navItems } current={ tab } onSelect={ setTab } />
					</div>
				</aside>

				<div className="min-w-0 flex-1">
					{ tab === 'personal' ? (
						<SingleOverview
							userId={ userId }
							record={ record }
							canEdit={ canEdit }
							activeLabel={ activeLabel }
						/>
					) : (
						<>
							{ canEdit && tab === 'job' ? <EmployeeJobTab userId={ userId } /> : null }
							{ canEdit && tab === 'leave' ? <EmployeeLeaveTab userId={ userId } /> : null }
							{ canViewNotes && tab === 'notes' ? <EmployeeNotesTab userId={ userId } /> : null }
							{ canViewPerf && tab === 'performance' ? <EmployeePerformanceTab userId={ userId } /> : null }
							{ canViewPermission && tab === 'permission' ? <EmployeePermissionTab userId={ userId } /> : null }
							{ extraTabs.find( ( t ) => t.id === tab )?.render( { userId, canEdit } ) }
						</>
					) }
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
