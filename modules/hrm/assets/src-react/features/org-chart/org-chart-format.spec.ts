/**
 * All Teams keeps the server's per-department groups apart: a team without a
 * lead and the people with no department used to be flattened into one run of
 * loose cards with no team name.
 */

import { describe, expect, it } from 'vitest';

import { teamGroups, topTrees, type ServerNode } from './org-chart-format';

const person = ( id: number, dept: number, children: ServerNode[] = [] ): ServerNode => ( {
	id, name: `P${ id }`, title: '', avatar: '', email: '', dept_id: dept, children,
} );
const group = ( dept: number, children: ServerNode[] ): ServerNode => ( {
	id: 0, name: '', title: '', avatar: '', email: '', dept_id: dept, children,
} );

const allTeams: ServerNode = {
	...group( 0, [
		group( 1, [ person( 10, 1, [ person( 11, 1 ) ] ) ] ), // led team
		group( 2, [ person( 20, 2 ), person( 21, 2 ) ] ),       // no lead
		group( 3, [] ),                                          // empty
		group( 0, [ person( 30, 0 ) ] ),                         // No Team
	] ),
	is_array: true,
};

describe( 'teamGroups', () => {
	it( 'keeps one group per team, No Team included, and drops empty teams', () => {
		const groups = teamGroups( allTeams ) ?? [];

		expect( groups.map( ( g ) => g.deptId ) ).toEqual( [ 1, 2, 0 ] );
		expect( groups.map( ( g ) => g.roots.map( ( r ) => r.id ) ) ).toEqual( [ [ 10 ], [ 20, 21 ], [ 30 ] ] );
	} );

	it( 'is null for a single team, which keeps the flat roots', () => {
		const single = group( 2, [ person( 20, 2 ), person( 21, 2 ) ] );

		expect( teamGroups( single ) ).toBeNull();
		expect( topTrees( single ).map( ( r ) => r.id ) ).toEqual( [ 20, 21 ] );
	} );
} );
