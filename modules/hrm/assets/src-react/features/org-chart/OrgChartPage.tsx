/**
 * `/org-chart` route — read-only reporting hierarchy.
 *
 * Reuses the existing `GET /erp/v2/employees` endpoint (no new REST surface):
 * fetches every active employee (paging in blocks of 100), then builds the
 * manager → reports tree client-side from each row's `reporting_to.id` chain.
 *
 * Roots = employees with no `reporting_to`, or whose manager is not in the
 * fetched set (defensive: a dangling manager id never hides a subtree). Children
 * are grouped under their manager's `user_id`. The tree renders top-down with
 * pure-CSS connector lines — no charting dependency.
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { makeInitials } from '@/shared/components/PersonCell';
import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

const PER_PAGE = 100;

/** Minimal employee shape consumed by the chart (subset of the v2 response). */
interface OrgNode {
	readonly id:          number;
	readonly userId:      number;
	readonly fullName:    string;
	readonly avatarUrl:   string | null;
	readonly designation: string | null;
	readonly department:  string | null;
	readonly managerId:   number | null;
}

/** Raw row fields we read off the loose v2 employees payload. */
interface RawRow {
	readonly id?:           unknown;
	readonly user_id?:      unknown;
	readonly full_name?:    unknown;
	readonly avatar_url?:   unknown;
	readonly designation?:  { name?: unknown } | null;
	readonly department?:   { name?: unknown } | null;
	readonly reporting_to?: { id?: unknown } | null;
}

function toNum( value: unknown ): number {
	const n = typeof value === 'number' ? value : parseInt( String( value ?? '' ), 10 );
	return Number.isFinite( n ) ? n : 0;
}

function toStrOrNull( value: unknown ): string | null {
	if ( typeof value === 'string' && value.trim() !== '' ) {
		return value;
	}
	return null;
}

function normalizeRow( row: RawRow ): OrgNode {
	const managerId = toNum( row.reporting_to?.id );
	return {
		id:          toNum( row.id ),
		userId:      toNum( row.user_id ),
		fullName:    toStrOrNull( row.full_name ) ?? __( 'Unnamed', 'erp' ),
		avatarUrl:   toStrOrNull( row.avatar_url ),
		designation: toStrOrNull( row.designation?.name ),
		department:  toStrOrNull( row.department?.name ),
		managerId:   managerId > 0 ? managerId : null,
	};
}

/** Fetch every active employee, paging until a short page is returned. */
async function fetchAllEmployees( signal: AbortSignal ): Promise< OrgNode[] > {
	const all: OrgNode[] = [];
	for ( let page = 1; ; page += 1 ) {
		const body = await request< RawRow[] >(
			restPath( 'v2', '/employees', { per_page: PER_PAGE, status: 'active', page } ),
			{ signal }
		);
		const rows = Array.isArray( body ) ? body : [];
		rows.forEach( ( row ) => all.push( normalizeRow( row ) ) );
		if ( rows.length < PER_PAGE ) {
			break;
		}
	}
	return all;
}

interface TreeNode extends OrgNode {
	readonly children: TreeNode[];
}

/**
 * Build the forest of reporting trees. Roots are employees with no manager, or
 * whose manager id is absent from the fetched set. Children sort by name for a
 * stable layout. Already-visited ids are skipped to defend against cycles.
 */
function buildForest( nodes: readonly OrgNode[] ): TreeNode[] {
	const byUserId = new Map< number, OrgNode >();
	nodes.forEach( ( n ) => {
		if ( n.userId > 0 ) {
			byUserId.set( n.userId, n );
		}
	} );

	const childrenOf = new Map< number, OrgNode[] >();
	const roots: OrgNode[] = [];

	nodes.forEach( ( n ) => {
		const hasManager = n.managerId !== null && byUserId.has( n.managerId );
		if ( hasManager ) {
			const bucket = childrenOf.get( n.managerId as number ) ?? [];
			bucket.push( n );
			childrenOf.set( n.managerId as number, bucket );
		} else {
			roots.push( n );
		}
	} );

	const visited = new Set< number >();
	const byName  = ( a: OrgNode, b: OrgNode ): number => a.fullName.localeCompare( b.fullName );

	function attach( node: OrgNode ): TreeNode {
		visited.add( node.userId );
		const kids = ( childrenOf.get( node.userId ) ?? [] )
			.filter( ( c ) => ! visited.has( c.userId ) )
			.sort( byName )
			.map( attach );
		return { ...node, children: kids };
	}

	return roots.sort( byName ).map( attach );
}

/** Single employee card. */
function OrgCard( { node }: { node: TreeNode } ): JSX.Element {
	return (
		<div className="inline-flex w-56 flex-col items-center gap-2 rounded-lg border border-border bg-card p-4 text-center shadow-sm">
			<Avatar className="size-12 shrink-0">
				{ node.avatarUrl ? <AvatarImage src={ node.avatarUrl } alt="" /> : null }
				<AvatarFallback>{ makeInitials( node.fullName ) }</AvatarFallback>
			</Avatar>
			<div className="min-w-0">
				<div className="truncate font-semibold text-foreground">{ node.fullName }</div>
				{ node.designation ? (
					<div className="truncate text-sm text-muted-foreground">{ node.designation }</div>
				) : null }
				{ node.department ? (
					<div className="truncate text-xs text-muted-foreground">{ node.department }</div>
				) : null }
			</div>
		</div>
	);
}

/**
 * Recursive subtree. Connector lines are drawn with bordered wrappers:
 *   - a vertical stub drops from each parent to the row of children,
 *   - each child has a top stub, and a horizontal rule spans the siblings
 *     (trimmed at the first/last child so the ends don't overhang).
 */
function OrgSubtree( { node }: { node: TreeNode } ): JSX.Element {
	const hasChildren = node.children.length > 0;
	return (
		<li className="flex flex-col items-center">
			<OrgCard node={ node } />

			{ hasChildren ? (
				<>
					{ /* vertical connector from the parent card down to the rule */ }
					<span aria-hidden="true" className="h-6 w-px bg-border" />

					<ul className="flex items-start justify-center">
						{ node.children.map( ( child, index ) => {
							const isFirst = index === 0;
							const isLast  = index === node.children.length - 1;
							const isOnly  = node.children.length === 1;
							return (
								<li
									key={ child.userId || child.id }
									className="relative flex flex-col items-center px-4 pt-6"
								>
									{ /* horizontal rule across siblings (trimmed at the ends) */ }
									{ ! isOnly ? (
										<span
											aria-hidden="true"
											className={ [
												'absolute top-0 h-px bg-border',
												isFirst ? 'left-1/2 right-0' : isLast ? 'left-0 right-1/2' : 'inset-x-0',
											].join( ' ' ) }
										/>
									) : null }
									{ /* vertical stub up to the rule */ }
									<span
										aria-hidden="true"
										className="absolute top-0 left-1/2 h-6 w-px -translate-x-1/2 bg-border"
									/>
									<OrgSubtree node={ child } />
								</li>
							);
						} ) }
					</ul>
				</>
			) : null }
		</li>
	);
}

function OrgChartInner(): JSX.Element {
	const [ nodes, setNodes ]     = useState< OrgNode[] | null >( null );
	const [ error, setError ]     = useState< string | null >( null );

	useEffect( () => {
		const controller = new AbortController();
		setNodes( null );
		setError( null );

		fetchAllEmployees( controller.signal )
			.then( ( rows ) => setNodes( rows ) )
			.catch( ( raw ) => {
				if ( controller.signal.aborted ) {
					return;
				}
				setError( ( raw as ApiError )?.message ?? __( 'Could not load the org chart.', 'erp' ) );
			} );

		return () => controller.abort();
	}, [] );

	const forest = useMemo(
		() => ( nodes ? buildForest( nodes ) : [] ),
		[ nodes ]
	);

	return (
		<section className="mx-auto w-full max-w-7xl">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Org Chart', 'erp' ) }
				</h1>
			</header>

			<div className="rounded-lg border border-border bg-card p-6 shadow-sm">
				{ error ? (
					<p className="p-6 text-center text-sm text-destructive">{ error }</p>
				) : nodes === null ? (
					<p className="p-10 text-center text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</p>
				) : nodes.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">{ __( 'No employees.', 'erp' ) }</p>
				) : forest.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ __( 'No reporting structure defined.', 'erp' ) }
					</p>
				) : (
					<div className="overflow-x-auto">
						<ul className="flex items-start justify-center gap-10 p-2">
							{ forest.map( ( root ) => (
								<OrgSubtree key={ root.userId || root.id } node={ root } />
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
