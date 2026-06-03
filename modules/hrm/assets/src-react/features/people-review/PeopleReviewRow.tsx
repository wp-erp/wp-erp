/**
 * Modern row — soft-card row with avatar, name+role stack, meta chips,
 * status pill, and a kebab menu that reveals on hover/focus.
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import { Building2, Mail, MapPin } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import type { EmployeeListItem, EmployeeStatus } from '@/stores/employees';

import { EmployeesRowActions } from '../employees/EmployeesRowActions';

interface PeopleReviewRowProps {
	readonly row:        EmployeeListItem;
	readonly selected:   boolean;
	readonly onToggle:   ( id: number, next: boolean ) => void;
}

interface StatusVisual {
	readonly label: string;
	readonly dot:   string;
	readonly text:  string;
}

function statusVisual( status: EmployeeStatus | null ): StatusVisual {
	switch ( status ) {
		case 'active':
			return { label: __( 'Active',     'erp' ), dot: 'bg-emerald-500',  text: 'text-emerald-700' };
		case 'inactive':
			return { label: __( 'Inactive',   'erp' ), dot: 'bg-amber-500',    text: 'text-amber-700' };
		case 'terminated':
			return { label: __( 'Terminated', 'erp' ), dot: 'bg-rose-500',     text: 'text-rose-700' };
		case 'resigned':
			return { label: __( 'Resigned',   'erp' ), dot: 'bg-rose-400',     text: 'text-rose-600' };
		case 'deceased':
			return { label: __( 'Deceased',   'erp' ), dot: 'bg-slate-400',    text: 'text-slate-600' };
		default:
			return { label: '—',                       dot: 'bg-slate-300',    text: 'text-slate-500' };
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

function formatHireDate( iso: string | null ): string | null {
	if ( ! iso ) {
		return null;
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

export function PeopleReviewRow( { row, selected, onToggle }: PeopleReviewRowProps ): JSX.Element {
	const visual    = statusVisual( row.status );
	const hireDate  = formatHireDate( row.hire_date );
	const subtitle  = row.designation?.name ?? row.employee_id ?? row.email;

	return (
		<div
			data-selected={ selected ? 'true' : 'false' }
			className="group relative grid grid-cols-[auto_minmax(0,2fr)_minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)_auto] items-center gap-4 overflow-hidden rounded-2xl border border-white/40 bg-white/55 px-4 py-3 ring-1 ring-white/40 backdrop-blur-xl backdrop-saturate-150 transition-all hover:-translate-y-0.5 hover:border-white/60 hover:bg-white/70 hover:shadow-[0_8px_24px_-12px_rgba(15,23,42,0.25)] data-[selected=true]:border-primary/50 data-[selected=true]:bg-primary/10 data-[selected=true]:ring-primary/30"
		>
			<span
				aria-hidden="true"
				className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/80 to-transparent"
			/>
			<input
				type="checkbox"
				checked={ selected }
				onChange={ ( e ) => onToggle( row.id, e.target.checked ) }
				aria-label={ __( 'Select person', 'erp' ) }
				className="size-4 rounded border-border text-primary focus:ring-primary/40"
			/>

			<div className="flex min-w-0 items-center gap-3">
				<Avatar className="size-10 shrink-0 ring-2 ring-card group-hover:ring-primary/20">
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
						{ subtitle }
					</p>
				</div>
			</div>

			<div className="flex min-w-0 items-center gap-1.5 text-xs text-muted-foreground">
				<Mail size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
				<a
					href={ `mailto:${ row.email }` }
					className="truncate hover:text-primary"
				>
					{ row.email }
				</a>
			</div>

			<div className="flex min-w-0 items-center gap-1.5 text-xs text-muted-foreground">
				<Building2 size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
				<span className="truncate">
					{ row.department?.name ?? '—' }
				</span>
			</div>

			<div className="flex min-w-0 items-center gap-1.5 text-xs text-muted-foreground">
				<MapPin size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
				<span className="truncate">
					{ row.location?.name ?? hireDate ?? '—' }
				</span>
			</div>

			<div className="flex items-center justify-end gap-2">
				<span className={ `inline-flex items-center gap-1.5 rounded-full border border-white/40 bg-white/70 px-2.5 py-1 text-xs font-medium ring-1 ring-white/40 backdrop-blur ${ visual.text }` }>
					<span aria-hidden="true" className={ `size-1.5 rounded-full ${ visual.dot }` } />
					{ visual.label }
				</span>
				<div className="opacity-0 transition-opacity group-hover:opacity-100 group-focus-within:opacity-100">
					<EmployeesRowActions employee={ row } />
				</div>
			</div>
		</div>
	);
}
