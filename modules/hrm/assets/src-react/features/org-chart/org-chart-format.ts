/**
 * Org-chart data shapes and pure tree helpers shared by the page and the
 * recursive renderer. No component state — types plus list/tree transforms over
 * the `erp/v2/org-chart` response.
 */

/** A node in the server hierarchy (`erp/v2/org-chart`). */
export interface ServerNode {
	readonly id:        number;
	readonly name:      string;
	readonly title:     string;
	readonly avatar:    string;
	readonly email:     string;
	readonly dept_id:   number;
	readonly is_array?: boolean;
	readonly children?: readonly ServerNode[];
}

export interface ChartResponse {
	readonly tree:        ServerNode;
	readonly departments: readonly { value: string; label: string }[];
}

export type DeptName = ( id: number ) => string;

/** Real (employee) children of a node. */
export function realChildren( node: ServerNode ): ServerNode[] {
	return ( node.children ?? [] ).filter( ( c ) => c.id > 0 );
}

/** Top-level trees to render: unwrap the synthetic "all teams" / "no lead" roots
 *  (empty nodes) so only real employee cards become roots. */
export function topTrees( tree: ServerNode | null ): ServerNode[] {
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
