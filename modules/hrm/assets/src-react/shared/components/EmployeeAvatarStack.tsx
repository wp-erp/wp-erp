/**
 * Overlapping avatar group for an org column's employees (department /
 * designation lists). Renders up to a few employee avatars with a trailing
 * "+N" overflow bubble, using plugin-ui's `AvatarGroup` / `AvatarGroupCount`.
 *
 * The backend returns a small `{ name, avatar }[]` preview plus the full
 * `total`; this component shows the preview and derives the overflow from
 * `total`. Falls back to a muted dash when the column has no employees.
 */

import { Avatar, AvatarFallback, AvatarGroup, AvatarGroupCount, AvatarImage } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { makeInitials } from './PersonCell';

export interface AvatarPerson {
	readonly name:   string;
	readonly avatar: string | null;
}

interface EmployeeAvatarStackProps {
	readonly people: readonly AvatarPerson[];
	readonly total:  number;
	/** Max avatars to render before collapsing into the "+N" bubble. */
	readonly max?:   number;
}

export function EmployeeAvatarStack( { people, total, max = 3 }: EmployeeAvatarStackProps ): JSX.Element {
	if ( total <= 0 || people.length === 0 ) {
		return <span className="text-muted-foreground">—</span>;
	}

	const shown     = people.slice( 0, max );
	const remaining = total - shown.length;

	return (
		<AvatarGroup>
			{ shown.map( ( person, i ) => (
				<Avatar key={ `${ person.name }-${ i }` } size="sm" className="ring-2 ring-card">
					{ person.avatar ? <AvatarImage src={ person.avatar } alt={ person.name } /> : null }
					<AvatarFallback>{ makeInitials( person.name ) }</AvatarFallback>
				</Avatar>
			) ) }
			{ remaining > 0 ? <AvatarGroupCount size="sm">+{ remaining }</AvatarGroupCount> : null }
		</AvatarGroup>
	);
}
