/**
 * Employees grid (card) view — an additive alternative to `EmployeesTable`.
 *
 * Renders the exact same `useEmployeesQuery` rows, selection store and
 * pagination as the table; only the per-row presentation differs. Each card
 * reuses the canonical cells (`NameCell`, `StatusCell`) and `EmployeesRowActions`
 * so behaviour stays in sync with the list view.
 */

import { Avatar, AvatarFallback, AvatarImage, Checkbox } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { ChevronLeft, ChevronRight, Hash, Mail, Phone } from 'lucide-react';
import type { JSX } from 'react';
import { NavLink } from 'react-router-dom';

import { makeInitials } from '@/shared/components/PersonCell';
import { useCan } from '@/shared/hooks/useCan';
import { __, dateI18n, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeeListItem, EmployeesState } from '@/stores/employees';

import { StatusCell } from './columns/StatusCell';
import { EmployeesRowActions } from './EmployeesRowActions';
import { useEmployeesQuery } from './useEmployeesQuery';

interface EmployeesStoreDispatch {
	setPagination:  ( pagination: EmployeesState[ 'pagination' ] ) => void;
	setSelectedIds: ( ids: readonly number[] ) => void;
}

interface EmployeesStoreSelectors {
	getSelectedIds: () => readonly number[];
}

/** "{employee_type} · {pay_type}" with only the present parts. */
function typeLine( row: EmployeeListItem ): string {
	return [ row.employee_type, row.pay_type ].filter( Boolean ).join( ' · ' );
}

export function EmployeesGrid(): JSX.Element {
	const { rows, page, perPage, total, totalPages } = useEmployeesQuery();
	const canView = useCan( 'erp_list_employee' );
	const selectedIds = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getSelectedIds(),
		[]
	);
	const { setPagination, setSelectedIds } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const selectedSet = new Set( selectedIds );

	const toggleRow = ( id: number, next: boolean ): void => {
		if ( next ) {
			setSelectedIds( Array.from( new Set( [ ...selectedIds, id ] ) ) );
		} else {
			setSelectedIds( selectedIds.filter( ( x ) => x !== id ) );
		}
	};

	const start = total === 0 ? 0 : ( page - 1 ) * perPage + 1;
	const end   = Math.min( page * perPage, total );

	return (
		<div className="bg-card">
			<ul className="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
				{ rows.map( ( row ) => {
					const isChecked = selectedSet.has( row.id );
					return (
						<li
							key={ row.id }
							data-selected={ isChecked ? 'true' : 'false' }
							className="group relative flex flex-col gap-4 overflow-hidden rounded-lg border border-border bg-card p-4 shadow-sm transition-colors hover:bg-muted/40 data-[selected=true]:border-primary/40 data-[selected=true]:bg-primary/5"
						>
							{ /* Top row: selection + status (left), row actions (right). */ }
							<div className="flex items-center justify-between gap-2">
								<div className="flex items-center gap-2">
									<Checkbox
										checked={ isChecked }
										onCheckedChange={ ( next: boolean ) => toggleRow( row.id, next ) }
										aria-label={ sprintf( __( 'Select %s', 'erp' ), row.full_name || row.email ) }
									/>
									<StatusCell row={ row } />
								</div>
								<EmployeesRowActions employee={ row } />
							</div>

							{ /* Centered avatar + name/designation stacked beneath it. */ }
							<div className="flex flex-col items-center gap-3 text-center">
								<Avatar className="size-16 shrink-0">
									{ row.avatar_url ? <AvatarImage src={ row.avatar_url } alt="" /> : null }
									<AvatarFallback className="text-lg">{ makeInitials( row.full_name || row.email ) }</AvatarFallback>
								</Avatar>
								<div className="w-full min-w-0">
									<div className="truncate text-base font-semibold text-foreground">{ row.full_name || row.email }</div>
									<div className="truncate text-sm text-muted-foreground">{ row.designation?.name ?? row.department?.name ?? '—' }</div>
								</div>
							</div>

							{ /* Details box. */ }
							<dl className="flex flex-col gap-2 rounded-md border border-border bg-muted/30 p-3 text-sm">
								{ row.employee_id ? (
									<div className="flex items-center gap-2 text-muted-foreground">
										<Hash size={ 14 } aria-hidden="true" />
										<span className="truncate text-foreground">{ row.employee_id }</span>
									</div>
								) : null }
								{ typeLine( row ) ? (
									<div className="truncate text-muted-foreground">{ typeLine( row ) }</div>
								) : null }
								<div className="flex items-center gap-2 text-muted-foreground">
									<Mail size={ 14 } className="shrink-0" aria-hidden="true" />
									<a href={ `mailto:${ row.email }` } className="truncate text-primary hover:underline">{ row.email }</a>
								</div>
								{ row.phone ? (
									<div className="flex items-center gap-2 text-muted-foreground">
										<Phone size={ 14 } className="shrink-0" aria-hidden="true" />
										<span className="truncate text-foreground">{ row.phone }</span>
									</div>
								) : null }
							</dl>

							{ /* Footer: joined date + view details. */ }
							<div className="flex items-center justify-between gap-2 border-t border-border pt-3 text-sm">
								<span className="truncate text-xs text-muted-foreground">
									{ row.hire_date
										? sprintf( __( 'Joined %s', 'erp' ), dateI18n( 'M j, Y', row.hire_date ) )
										: ' ' }
								</span>
								{ canView ? (
									<NavLink
										to={ `/employees/${ row.user_id }` }
										viewTransition
										className="shrink-0 font-medium text-primary hover:underline"
									>
										{ __( 'View details', 'erp' ) }
									</NavLink>
								) : null }
							</div>
						</li>
					);
				} ) }
			</ul>

			<footer className="flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3 text-sm text-muted-foreground">
				<span className="text-xs tabular-nums">
					{ sprintf(
						/* translators: 1: start row, 2: end row, 3: total */
						__( 'Showing %1$d–%2$d of %3$d', 'erp' ),
						start,
						end,
						total
					) }
				</span>
				<div className="flex items-center gap-3">
					<label className="flex items-center gap-2">
						<span className="text-xs">{ __( 'Per page', 'erp' ) }</span>
						<select
							value={ perPage }
							onChange={ ( e ) => setPagination( { page: 1, perPage: parseInt( e.target.value, 10 ) } ) }
							aria-label={ __( 'Cards per page', 'erp' ) }
							className="h-8 cursor-pointer rounded-md border border-border bg-card pl-2 pr-6 text-xs font-medium text-foreground focus:border-primary focus:outline-none"
						>
							{ [ 12, 24, 48, 96 ].map( ( n ) => (
								<option key={ n } value={ n }>{ n }</option>
							) ) }
						</select>
					</label>

					<div className="inline-flex items-center gap-1">
						<button
							type="button"
							onClick={ () => setPagination( { page: Math.max( 1, page - 1 ), perPage } ) }
							disabled={ page <= 1 }
							className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted disabled:opacity-40"
							aria-label={ __( 'Previous page', 'erp' ) }
						>
							<ChevronLeft size={ 14 } aria-hidden="true" />
						</button>
						<span className="min-w-20 px-2 text-center text-xs font-medium tabular-nums text-foreground">
							{ sprintf( __( '%1$d of %2$d', 'erp' ), page, Math.max( totalPages, 1 ) ) }
						</span>
						<button
							type="button"
							onClick={ () => setPagination( { page: page + 1, perPage } ) }
							disabled={ page >= totalPages }
							className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted disabled:opacity-40"
							aria-label={ __( 'Next page', 'erp' ) }
						>
							<ChevronRight size={ 14 } aria-hidden="true" />
						</button>
					</div>
				</div>
			</footer>
		</div>
	);
}
