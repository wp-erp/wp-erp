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
 *
 * Chrome pieces live alongside: `ProfileHeader` (hero), `PillTabs` (tab bar),
 * `OverviewTab` (personal body), `InfoCard`/`Field` (cards), `profile-format`
 * (pure helpers).
 */

import { Skeleton } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
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
import { OverviewTab } from './OverviewTab';
import { PillTabs, type TabDef } from './PillTabs';
import { ProfileHeader } from './ProfileHeader';
import { EmployeeJobTab } from './job/EmployeeJobTab';
import { EmployeeLeaveTab } from './leave/EmployeeLeaveTab';
import { EmployeeNotesTab } from './notes/EmployeeNotesTab';
import { EmployeePerformanceTab } from './performance/EmployeePerformanceTab';
import { EmployeePermissionTab } from './permission/EmployeePermissionTab';
import type { Record_ } from './profile-format';

interface SingleDispatch {
	fetchEmployeeForEdit: ( userId: number ) => Promise< Record< string, unknown > >;
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
			<ProfileHeader record={ record } userId={ userId } canEdit={ canEdit } onEdit={ goEdit } />

			<PillTabs tabs={ tabs } current={ tab } onSelect={ setTab } />

			<div>
				{ tab === 'personal' ? (
					<OverviewTab userId={ userId } record={ record } canEdit={ canEdit } onEdit={ goEdit } />
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
