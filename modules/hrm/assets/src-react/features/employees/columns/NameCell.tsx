/**
 * Employee name cell — avatar 32×32 + bold name + designation subtitle.
 *
 * Figma node `502:22903` (356 px column width, 72 px row height).
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import type { EmployeeListItem } from '@/stores/employees';

interface NameCellProps {
	readonly row: EmployeeListItem;
}

export function NameCell( { row }: NameCellProps ): JSX.Element {
	const initials = makeInitials( row.full_name || row.email );
	const subtitle = row.designation?.name ?? row.department?.name ?? '';

	return (
		<div className="flex items-center gap-3">
			<Avatar className="size-8 shrink-0">
				{ row.avatar_url ? <AvatarImage src={ row.avatar_url } alt="" /> : null }
				<AvatarFallback>{ initials }</AvatarFallback>
			</Avatar>
			<div className="flex min-w-0 flex-col leading-tight">
				<span className="whitespace-nowrap text-sm font-semibold text-foreground">
					{ row.full_name || row.email }
				</span>
				{ subtitle ? (
					<span className="whitespace-nowrap text-xs text-muted-foreground">{ subtitle }</span>
				) : null }
			</div>
		</div>
	);
}

function makeInitials( name: string ): string {
	const parts = name.trim().split( /\s+/ ).filter( Boolean );
	if ( parts.length === 0 ) {
		return 'U';
	}
	if ( parts.length === 1 ) {
		const first = parts[ 0 ];
		return first ? first.slice( 0, 2 ).toUpperCase() : 'U';
	}
	const [ first, last ] = parts;
	const a = first ? first.charAt( 0 ) : '';
	const b = last ? last.charAt( 0 ) : '';
	const joined = `${ a }${ b }`.toUpperCase();
	return joined || 'U';
}
