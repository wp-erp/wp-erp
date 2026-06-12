/**
 * `/org-chart` route — company organogram.
 *
 * Full parity with the legacy pro Org Chart: a department filter (All Teams /
 * each team / No Team), the department-lead-rooted hierarchy (multi-tree for
 * "All Teams"), employee node cards (avatar · name · designation · mail action)
 * and zoom / pan controls. The hierarchy comes from `GET /erp/v2/org-chart`
 * (which ports the legacy `Helpers::get_employee_hierarchy`); the tree itself is
 * rendered with pure-CSS connectors — no charting dependency.
 */

import { Avatar, AvatarFallback, AvatarImage, SmartSelect } from '@wedevs/plugin-ui';
import { Mail, Minus, Plus } from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { makeInitials } from '@/shared/components/PersonCell';
import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

/** A node in the server hierarchy (`erp/v2/org-chart`). */
interface ServerNode {
	readonly id:        number;
	readonly name:      string;
	readonly title:     string;
	readonly avatar:    string;
	readonly email:     string;
	readonly dept_id:   number;
	readonly is_array?: boolean;
	readonly children?: readonly ServerNode[];
}

interface ChartResponse {
	readonly tree:        ServerNode;
	readonly departments: readonly { value: string; label: string }[];
}

const ZOOM_MIN  = 0.5;
const ZOOM_MAX  = 1.5;
const ZOOM_STEP = 0.1;

/** Top-level trees to render: unwrap the synthetic "all teams" / "no lead" roots
 *  (empty nodes) so only real employee cards become roots. */
function topTrees( tree: ServerNode | null ): ServerNode[] {
	if ( ! tree ) {
		return [];
	}
	const unwrap = ( node: ServerNode ): ServerNode[] =>
		node.id > 0 ? [ node ] : [ ...( node.children ?? [] ) ].flatMap( unwrap );

	if ( tree.is_array ) {
		return [ ...( tree.children ?? [] ) ].flatMap( unwrap );
	}
	return unwrap( tree );
}

/** Single employee card with a mail action (legacy node parity). */
function OrgCard( { node }: { node: ServerNode } ): JSX.Element {
	return (
		<div className="group relative inline-flex w-56 flex-col items-center gap-2 rounded-lg border border-border bg-card p-4 text-center shadow-sm">
			{ node.email ? (
				<a
					href={ `mailto:${ node.email }` }
					aria-label={ __( 'Email', 'erp' ) }
					title={ node.email }
					className="absolute right-2 top-2 inline-flex size-7 items-center justify-center rounded-md text-muted-foreground opacity-0 transition-opacity hover:bg-muted hover:text-foreground focus:opacity-100 group-hover:opacity-100"
				>
					<Mail size={ 14 } aria-hidden="true" />
				</a>
			) : null }
			<Avatar className="size-12 shrink-0">
				{ node.avatar ? <AvatarImage src={ node.avatar } alt="" /> : null }
				<AvatarFallback>{ makeInitials( node.name ) }</AvatarFallback>
			</Avatar>
			<div className="min-w-0">
				<div className="truncate font-semibold text-foreground">{ node.name }</div>
				{ node.title ? <div className="truncate text-sm text-muted-foreground">{ node.title }</div> : null }
			</div>
		</div>
	);
}

/**
 * Recursive subtree, vertical (indented-tree) orientation. Children are stacked
 * in a column beneath the parent and connected with pure-CSS rules:
 *   - the children list carries a continuous vertical border running down its
 *     left edge,
 *   - each child draws a horizontal elbow from that border to its card,
 *   - the last child masks the border below its elbow so the line stops there.
 */
function OrgSubtree( { node }: { node: ServerNode } ): JSX.Element {
	const children = ( node.children ?? [] ).filter( ( c ) => c.id > 0 );
	const hasChildren = children.length > 0;

	return (
		<li className="flex flex-col items-start">
			<OrgCard node={ node } />

			{ hasChildren ? (
				<ul className="relative ml-7 mt-2 flex flex-col gap-3 border-l border-border pl-7">
					{ children.map( ( child, index ) => {
						const isLast = index === children.length - 1;
						return (
							<li key={ child.id } className="relative">
								<span aria-hidden="true" className="absolute -left-7 top-10 h-px w-7 bg-border" />
								{ isLast ? (
									<span aria-hidden="true" className="absolute -left-7 bottom-0 top-10 w-px bg-card" />
								) : null }
								<OrgSubtree node={ child } />
							</li>
						);
					} ) }
				</ul>
			) : null }
		</li>
	);
}

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
	const departments = data?.departments ?? [];

	const zoomBy = useCallback( ( delta: number ) => {
		setZoom( ( z ) => Math.min( ZOOM_MAX, Math.max( ZOOM_MIN, Math.round( ( z + delta ) * 100 ) / 100 ) ) );
	}, [] );

	return (
		<section className="mx-auto w-full max-w-7xl">
			<header className="mb-6 flex flex-wrap items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">{ __( 'Org Chart', 'erp' ) }</h1>
				<div className="flex items-center gap-3">
					{ departments.length > 0 ? (
						<SmartSelect
							options={ [ ...departments ] }
							value={ deptId }
							onValueChange={ ( v ) => setDeptId( v ?? '' ) }
							placeholder={ __( 'All Teams', 'erp' ) }
							className="h-9 w-48"
							contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
						/>
					) : null }
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
						<span className="w-10 text-center text-xs tabular-nums text-muted-foreground">{ Math.round( zoom * 100 ) }%</span>
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
				</div>
			</header>

			<div className="rounded-lg border border-border bg-card p-6 shadow-sm">
				{ error ? (
					<p className="p-6 text-center text-sm text-destructive">{ error }</p>
				) : loading ? (
					<p className="p-10 text-center text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</p>
				) : roots.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ __( 'No reporting structure for this team.', 'erp' ) }
					</p>
				) : (
					<div className="overflow-auto">
						<div
							className="origin-top transition-transform"
							style={ { transform: `scale(${ zoom })`, width: `${ 100 / zoom }%` } }
						>
							<ul className="flex flex-col items-start gap-10 p-2">
								{ roots.map( ( root ) => (
									<OrgSubtree key={ `${ root.dept_id }-${ root.id }` } node={ root } />
								) ) }
							</ul>
						</div>
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
