/**
 * `/org-chart` route — company organogram.
 *
 * Full parity with the legacy pro Org Chart: a department filter (rendered as a
 * left sidebar of team pills), the department-lead-rooted hierarchy (multi-tree
 * for "All Teams"), employee node cards (avatar · name · designation · mail
 * action) and zoom controls. The hierarchy comes from `GET /erp/v2/org-chart`
 * (which ports the legacy `Helpers::get_employee_hierarchy`); the tree is a
 * top-down layout drawn with pure-CSS connectors — no charting dependency.
 *
 * Leaf children of a lead are grouped into a dashed, department-labelled box
 * (e.g. all "Senior Engineer" reports under a "Lead Engineer") to mirror the
 * team-cluster look of the redesign.
 *
 * The card / tree / pill pieces and pure tree helpers live alongside:
 * `OrgCard`, `OrgTree` (`OrgSubtree`), `DeptPill`, `org-chart-format`.
 */

import { Minus, Plus } from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

import { DeptPill } from './DeptPill';
import { OrgSubtree } from './OrgTree';
import { topTrees, type ChartResponse, type DeptName } from './org-chart-format';

const ZOOM_MIN  = 0.5;
const ZOOM_MAX  = 1.5;
const ZOOM_STEP = 0.1;

function OrgChartInner(): JSX.Element {
	const [ deptId, setDeptId ]   = useState( '' );
	const [ data, setData ]       = useState< ChartResponse | null >( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );
	const [ zoom, setZoom ]       = useState( 1 );

	useEffect( () => {
		const controller = new AbortController();
		setLoading( true );
		setError( null );

		void request< ChartResponse >(
			restPath( 'v2', '/org-chart', deptId ? { dept_id: deptId } : {} ),
			{ signal: controller.signal }
		)
			.then( ( res ) => setData( res ) )
			.catch( ( raw ) => {
				if ( ! controller.signal.aborted ) {
					setError( ( raw as ApiError )?.message ?? __( 'Could not load the org chart.', 'erp' ) );
				}
			} )
			.finally( () => {
				if ( ! controller.signal.aborted ) {
					setLoading( false );
				}
			} );

		return () => controller.abort();
	}, [ deptId ] );

	const roots       = useMemo( () => topTrees( data?.tree ?? null ), [ data ] );
	const departments = useMemo(
		() => ( data?.departments ?? [] ).filter( ( d ) => d.value !== '' ),
		[ data ]
	);
	const deptName = useCallback< DeptName >(
		( id ) => departments.find( ( d ) => d.value === String( id ) )?.label ?? '',
		[ departments ]
	);

	const zoomBy = useCallback( ( delta: number ) => {
		setZoom( ( z ) => Math.min( ZOOM_MAX, Math.max( ZOOM_MIN, Math.round( ( z + delta ) * 100 ) / 100 ) ) );
	}, [] );

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex flex-wrap items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">{ __( 'Org Chart', 'erp' ) }</h1>
				<div className="inline-flex items-center gap-1 rounded-md border border-border bg-card p-1">
					<button
						type="button"
						aria-label={ __( 'Zoom out', 'erp' ) }
						disabled={ zoom <= ZOOM_MIN }
						onClick={ () => zoomBy( -ZOOM_STEP ) }
						className="inline-flex size-7 items-center justify-center rounded text-muted-foreground hover:bg-muted hover:text-foreground disabled:opacity-40"
					>
						<Minus size={ 16 } aria-hidden="true" />
					</button>
					<span className="w-10 text-center text-xs text-muted-foreground">{ Math.round( zoom * 100 ) }%</span>
					<button
						type="button"
						aria-label={ __( 'Zoom in', 'erp' ) }
						disabled={ zoom >= ZOOM_MAX }
						onClick={ () => zoomBy( ZOOM_STEP ) }
						className="inline-flex size-7 items-center justify-center rounded text-muted-foreground hover:bg-muted hover:text-foreground disabled:opacity-40"
					>
						<Plus size={ 16 } aria-hidden="true" />
					</button>
				</div>
			</header>

			{ departments.length > 0 ? (
				<div role="group" aria-label={ __( 'Filter by team', 'erp' ) } className="mb-4 flex flex-wrap items-center gap-1.5">
					<DeptPill label={ __( 'All Teams', 'erp' ) } active={ deptId === '' } onClick={ () => setDeptId( '' ) } />
					{ departments.map( ( d ) => (
						<DeptPill
							key={ d.value }
							label={ d.label }
							active={ deptId === d.value }
							onClick={ () => setDeptId( d.value ) }
						/>
					) ) }
				</div>
			) : null }

			<div
				className="overflow-auto rounded-lg border border-border bg-card p-6 shadow-sm"
				style={ {
					backgroundImage: 'radial-gradient(color-mix(in srgb, var(--border) 70%, transparent) 1px, transparent 1px)',
					backgroundSize:  '22px 22px',
				} }
			>
				{ error ? (
					<p className="p-6 text-center text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : roots.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ __( 'No reporting structure for this team.', 'erp' ) }
					</p>
				) : (
					<div
						className="origin-top transition-transform"
						style={ { transform: `scale(${ zoom })`, width: `${ 100 / zoom }%` } }
					>
						<ul className="flex flex-wrap items-start justify-center gap-12 p-2">
							{ roots.map( ( root ) => (
								<OrgSubtree key={ `${ root.dept_id }-${ root.id }` } node={ root } deptName={ deptName } />
							) ) }
						</ul>
					</div>
				) }
			</div>
		</section>
	);
}

export function OrgChartPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_list_employee' ] }>
			<ErrorBoundary>
				<OrgChartInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
