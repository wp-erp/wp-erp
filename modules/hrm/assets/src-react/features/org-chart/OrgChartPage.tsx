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
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import { Mail, Minus, Plus } from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
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

type DeptName = ( id: number ) => string;

const ZOOM_MIN  = 0.5;
const ZOOM_MAX  = 1.5;
const ZOOM_STEP = 0.1;

/** Real (employee) children of a node. */
function realChildren( node: ServerNode ): ServerNode[] {
	return ( node.children ?? [] ).filter( ( c ) => c.id > 0 );
}

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

/** Single employee card: avatar · name · designation, with a mail action. */
function OrgCard( { node }: { node: ServerNode } ): JSX.Element {
	return (
		<div className="group relative inline-flex w-60 items-center gap-3 rounded-xl border border-border bg-card py-2.5 pl-3 pr-8 text-left shadow-sm transition-shadow hover:shadow-md">
			<Avatar className="size-10 shrink-0">
				{ node.avatar ? <AvatarImage src={ node.avatar } alt="" /> : null }
				<AvatarFallback>{ makeInitials( node.name ) }</AvatarFallback>
			</Avatar>
			<div className="min-w-0 flex-1">
				<div className="truncate text-sm font-semibold text-foreground">{ node.name }</div>
				{ node.title ? <div className="truncate text-xs text-muted-foreground">{ node.title }</div> : null }
			</div>
			{ node.email ? (
				<a
					href={ `mailto:${ node.email }` }
					aria-label={ __( 'Email', 'erp' ) }
					title={ node.email }
					className="absolute right-1.5 top-1.5 inline-flex size-6 items-center justify-center rounded-md text-muted-foreground opacity-0 transition-opacity hover:bg-muted hover:text-foreground focus:opacity-100 group-hover:opacity-100"
				>
					<Mail size={ 13 } aria-hidden="true" />
				</a>
			) : null }
		</div>
	);
}

/** Dashed, department-labelled cluster of leaf reports. */
function DeptGroup( { label, members }: { readonly label: string; readonly members: readonly ServerNode[] } ): JSX.Element {
	return (
		<div className="relative rounded-xl border-2 border-dashed border-primary/40 px-5 pb-5 pt-7">
			<span className="absolute -top-3 left-5 rounded bg-card px-2 text-sm font-semibold text-foreground">{ label }</span>
			<ul className="flex flex-wrap justify-center gap-3">
				{ members.map( ( m ) => (
					<li key={ m.id }>
						<OrgCard node={ m } />
					</li>
				) ) }
			</ul>
		</div>
	);
}

/**
 * Recursive subtree (top-down). Connectors are pure-CSS bordered spans tinted
 * with the primary colour, plus a junction dot above each child:
 *   - a vertical stub drops from each parent to the row of children,
 *   - each child has a top stub + dot, and a horizontal rule spans the siblings
 *     (trimmed at the first/last child so the ends don't overhang).
 * When every child is a leaf, they collapse into a single dashed DeptGroup.
 */
function OrgSubtree( { node, deptName }: { node: ServerNode; deptName: DeptName } ): JSX.Element {
	const children    = realChildren( node );
	const hasChildren = children.length > 0;
	const firstChild  = children[ 0 ];
	const allLeaves   = !! firstChild && children.every( ( c ) => realChildren( c ).length === 0 );

	return (
		<li className="flex flex-col items-center">
			<OrgCard node={ node } />

			{ hasChildren ? (
				<>
					<span aria-hidden="true" className="h-6 w-px bg-primary/40" />
					{ allLeaves && firstChild ? (
						<DeptGroup label={ deptName( firstChild.dept_id ) || node.title } members={ children } />
					) : (
						<ul className="flex items-start justify-center">
							{ children.map( ( child, index ) => {
								const isFirst = index === 0;
								const isLast  = index === children.length - 1;
								const isOnly  = children.length === 1;
								return (
									<li key={ child.id } className="relative flex flex-col items-center px-5 pt-6">
										{ ! isOnly ? (
											<span
												aria-hidden="true"
												className={ [
													'absolute top-0 h-px bg-primary/40',
													isFirst ? 'left-1/2 right-0' : isLast ? 'left-0 right-1/2' : 'inset-x-0',
												].join( ' ' ) }
											/>
										) : null }
										<span aria-hidden="true" className="absolute top-0 left-1/2 h-6 w-px -translate-x-1/2 bg-primary/40" />
										<span aria-hidden="true" className="absolute top-0 left-1/2 size-1.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary" />
										<OrgSubtree node={ child } deptName={ deptName } />
									</li>
								);
							} ) }
						</ul>
					) }
				</>
			) : null }
		</li>
	);
}

/** A team pill in the top filter bar. */
function DeptPill( { label, active, onClick }: { readonly label: string; readonly active: boolean; readonly onClick: () => void } ): JSX.Element {
	return (
		<button
			type="button"
			onClick={ onClick }
			aria-pressed={ active }
			className={ [
				'max-w-[14rem] shrink-0 truncate rounded-md border px-3 py-1.5 text-left text-xs font-medium transition-colors',
				active
					? 'border-primary/30 bg-primary/10 text-primary'
					: 'border-border bg-card text-foreground hover:bg-muted',
			].join( ' ' ) }
		>
			{ label }
		</button>
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
