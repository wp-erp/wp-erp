/**
 * Recursive org-chart renderer: the top-down `OrgSubtree` (with pure-CSS
 * connectors) and the dashed, department-labelled `DeptGroup` cluster that
 * leaf-only reports collapse into.
 */

import type { JSX } from 'react';

import { OrgCard } from './OrgCard';
import { realChildren, type DeptName, type ServerNode } from './org-chart-format';

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
export function OrgSubtree( { node, deptName }: { node: ServerNode; deptName: DeptName } ): JSX.Element {
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
