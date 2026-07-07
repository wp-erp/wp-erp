/**
 * Single person identity cell — 32×32 avatar + name, matching the Employees
 * list `NameCell`. Shared across report tables, the leave-request queue, and any
 * other list that shows one employee per row.
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

interface PersonCellProps {
	readonly name:    string;
	readonly avatar:  string | null;
}

/** Two-letter initials fallback (matches the Employees `NameCell`). */
export function makeInitials( name: string ): string {
	const parts = name.trim().split( /\s+/ ).filter( Boolean );
	if ( parts.length === 0 ) {
		return 'U';
	}
	if ( parts.length === 1 ) {
		const first = parts[ 0 ];
		return first ? first.slice( 0, 2 ).toUpperCase() : 'U';
	}
	const [ first, last ] = parts;
	return `${ first?.charAt( 0 ) ?? '' }${ last?.charAt( 0 ) ?? '' }`.toUpperCase() || 'U';
}

export function PersonCell( { name, avatar }: PersonCellProps ): JSX.Element {
	return (
		<div className="flex items-center gap-3">
			<Avatar className="size-8 shrink-0">
				{ avatar ? <AvatarImage src={ avatar } alt="" /> : null }
				<AvatarFallback>{ makeInitials( name ) }</AvatarFallback>
			</Avatar>
			<span>{ name }</span>
		</div>
	);
}
