/**
 * Dense table row — single line, tight type, monochrome.
 *
 * Avatar 28px + name (link) + small role line.
 * Status uses a flat chip (no glass, no glow).
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import type { EmployeeListItem, EmployeeStatus } from '@/stores/employees';

import { EmployeesRowActions } from '../employees/EmployeesRowActions';

interface PeopleSaasRowProps {
	readonly row:      EmployeeListItem;
	readonly selected: boolean;
	readonly onToggle: ( id: number, next: boolean ) => void;
}

interface StatusVisual {
	readonly label: string;
	readonly dot:   string;
	readonly chip:  string;
}

function statusVisual( status: EmployeeStatus | null ): StatusVisual {
	switch ( status ) {
		case 'active':
			return { label: __( 'Active',     'erp' ), dot: 'bg-emerald-500', chip: 'border-emerald-200 bg-emerald-50 text-emerald-700' };
		case 'inactive':
			return { label: __( 'Inactive',   'erp' ), dot: 'bg-amber-500',   chip: 'border-amber-200 bg-amber-50 text-amber-700' };
		case 'terminated':
			return { label: __( 'Terminated', 'erp' ), dot: 'bg-rose-500',    chip: 'border-rose-200 bg-rose-50 text-rose-700' };
		case 'resigned':
			return { label: __( 'Resigned',   'erp' ), dot: 'bg-rose-400',    chip: 'border-rose-200 bg-rose-50 text-rose-700' };
		case 'deceased':
			return { label: __( 'Deceased',   'erp' ), dot: 'bg-slate-400',   chip: 'border-slate-200 bg-slate-50 text-slate-700' };
		default:
			return { label: '—',                       dot: 'bg-slate-300',   chip: 'border-slate-200 bg-slate-50 text-slate-500' };
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

export function PeopleSaasRow( { row, selected, onToggle }: PeopleSaasRowProps ): JSX.Element {
	const visual = statusVisual( row.status );

	return (
		<tr
			data-selected={ selected ? 'true' : 'false' }
			className="group border-b border-slate-100 last:border-b-0 hover:bg-slate-50/70 data-[selected=true]:bg-slate-50"
		>
			<td className="w-10 px-4 py-2.5 align-middle">
				<input
					type="checkbox"
					checked={ selected }
					onChange={ ( e ) => onToggle( row.id, e.target.checked ) }
					aria-label={ __( 'Select person', 'erp' ) }
					className="size-3.5 rounded border-slate-300 text-slate-900 focus:ring-slate-400"
				/>
			</td>

			<td className="min-w-0 py-2.5 pr-4 align-middle">
				<div className="flex min-w-0 items-center gap-2.5">
					<Avatar className="size-7 shrink-0">
						{ row.avatar_url ? <AvatarImage src={ row.avatar_url } alt="" /> : null }
						<AvatarFallback className="text-[10px]">{ makeInitials( row.full_name || row.email ) }</AvatarFallback>
					</Avatar>
					<div className="min-w-0">
						<a
							href={ `#/employees/${ row.id }` }
							className="block truncate text-xs font-medium text-slate-900 hover:underline"
						>
							{ row.full_name || row.email }
						</a>
						<p className="truncate text-[11px] text-slate-500">
							{ row.designation?.name ?? row.employee_id ?? '—' }
						</p>
					</div>
				</div>
			</td>

			<td className="min-w-0 py-2.5 pr-4 align-middle">
				<a
					href={ `mailto:${ row.email }` }
					className="block truncate text-xs text-slate-700 hover:text-slate-900 hover:underline"
				>
					{ row.email }
				</a>
			</td>

			<td className="min-w-0 py-2.5 pr-4 align-middle text-xs text-slate-700">
				<span className="block truncate">{ row.department?.name ?? '—' }</span>
			</td>

			<td className="min-w-0 py-2.5 pr-4 align-middle text-xs text-slate-700">
				<span className="block truncate">{ row.location?.name ?? '—' }</span>
			</td>

			<td className="py-2.5 pr-4 align-middle text-xs tabular-nums text-slate-600">
				{ formatHireDate( row.hire_date ) }
			</td>

			<td className="py-2.5 pr-4 align-middle">
				<span className={ `inline-flex items-center gap-1.5 rounded border px-1.5 py-0.5 text-[11px] font-medium ${ visual.chip }` }>
					<span aria-hidden="true" className={ `size-1.5 rounded-full ${ visual.dot }` } />
					{ visual.label }
				</span>
			</td>

			<td className="w-10 px-2 py-2.5 align-middle">
				<div className="opacity-0 transition-opacity group-hover:opacity-100 group-focus-within:opacity-100">
					<EmployeesRowActions employee={ row } />
				</div>
			</td>
		</tr>
	);
}
