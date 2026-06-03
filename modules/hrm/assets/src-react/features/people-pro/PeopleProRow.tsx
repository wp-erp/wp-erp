/**
 * Single table row — token-driven, balanced typographic rhythm.
 *
 * Reuses plugin-ui Avatar, Badge, Checkbox and the shared
 * `EmployeesRowActions` kebab menu — single source of truth for behavior.
 */

import { Avatar, AvatarFallback, AvatarImage, Badge, Checkbox } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import type { EmployeeListItem, EmployeeStatus } from '@/stores/employees';

import { EmployeesRowActions } from '../employees/EmployeesRowActions';

interface PeopleProRowProps {
	readonly row:      EmployeeListItem;
	readonly selected: boolean;
	readonly onToggle: ( id: number, next: boolean ) => void;
}

interface StatusVisual {
	readonly label:     string;
	readonly className: string;
}

function statusVisual( status: EmployeeStatus | null ): StatusVisual {
	switch ( status ) {
		case 'active':
			return { label: __( 'Active',     'erp' ), className: 'bg-success-light text-success-on-light' };
		case 'inactive':
			return { label: __( 'Inactive',   'erp' ), className: 'bg-warning-light text-warning-on-light' };
		case 'terminated':
			return { label: __( 'Terminated', 'erp' ), className: 'bg-destructive-light text-destructive-on-light' };
		case 'resigned':
			return { label: __( 'Resigned',   'erp' ), className: 'bg-destructive-light text-destructive-on-light' };
		case 'deceased':
			return { label: __( 'Deceased',   'erp' ), className: 'bg-neutral-light text-neutral-on-light' };
		default:
			return { label: '—',                       className: 'bg-neutral-light text-neutral-on-light' };
	}
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
	const b = last  ? last.charAt( 0 )  : '';
	return `${ a }${ b }`.toUpperCase() || 'U';
}

function formatHireDate( iso: string | null ): string {
	if ( ! iso ) {
		return '—';
	}
	try {
		return new Date( iso ).toLocaleDateString( undefined, {
			year:  'numeric',
			month: 'short',
			day:   'numeric',
		} );
	} catch {
		return iso;
	}
}

export function PeopleProRow( { row, selected, onToggle }: PeopleProRowProps ): JSX.Element {
	const visual = statusVisual( row.status );

	return (
		<tr
			data-selected={ selected ? 'true' : 'false' }
			className="group h-16 border-b border-border last:border-b-0 transition-colors hover:bg-muted/40 data-[selected=true]:bg-primary/5"
		>
			<td className="w-12 px-4 align-middle">
				<Checkbox
					checked={ selected }
					onCheckedChange={ ( next: boolean ) => onToggle( row.id, next ) }
					aria-label={ __( 'Select member', 'erp' ) }
				/>
			</td>

			<td className="min-w-0 py-2 pr-4 align-middle">
				<div className="flex min-w-0 items-center gap-3">
					<Avatar className="size-9 shrink-0">
						{ row.avatar_url ? <AvatarImage src={ row.avatar_url } alt="" /> : null }
						<AvatarFallback>{ makeInitials( row.full_name || row.email ) }</AvatarFallback>
					</Avatar>
					<div className="min-w-0 leading-tight">
						<a
							href={ `#/employees/${ row.id }` }
							className="block truncate text-sm font-semibold text-foreground hover:text-primary"
						>
							{ row.full_name || row.email }
						</a>
						<p className="truncate text-xs text-muted-foreground">
							{ row.designation?.name ?? row.employee_id ?? '—' }
						</p>
					</div>
				</div>
			</td>

			<td className="min-w-0 py-2 pr-4 align-middle">
				<a
					href={ `mailto:${ row.email }` }
					className="block truncate text-sm text-foreground hover:text-primary"
				>
					{ row.email }
				</a>
			</td>

			<td className="min-w-0 py-2 pr-4 align-middle text-sm text-foreground">
				<span className="block truncate">{ row.department?.name ?? '—' }</span>
			</td>

			<td className="min-w-0 py-2 pr-4 align-middle text-sm text-muted-foreground">
				<span className="block truncate">{ row.location?.name ?? '—' }</span>
			</td>

			<td className="py-2 pr-4 align-middle text-sm tabular-nums text-muted-foreground">
				{ formatHireDate( row.hire_date ) }
			</td>

			<td className="py-2 pr-4 align-middle">
				<Badge className={ `${ visual.className } rounded-full` }>{ visual.label }</Badge>
			</td>

			<td className="w-10 pr-4 align-middle text-right">
				<EmployeesRowActions employee={ row } />
			</td>
		</tr>
	);
}
