/**
 * Entitlements list table (employee × policy × year) for the
 * `/leave/entitlements` route. Presentational — selection state and the delete
 * action are driven by the page; rows come from `useEntitlements`.
 */

import {
	Button,
	Checkbox,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { MoreVertical, Trash2 } from 'lucide-react';
import type { JSX } from 'react';

import { __, dateI18n, sprintf } from '@/shared/i18n';

import type { Entitlement, FinancialYearOption } from './types';

interface LeaveEntitlementsTableProps {
	readonly rows:              readonly Entitlement[];
	readonly canManage:         boolean;
	readonly selected:          ReadonlySet< number >;
	readonly allOnPageSelected: boolean;
	/** Financial years — resolves each row's `f_year` to its validity window. */
	readonly financialYears:    readonly FinancialYearOption[];
	readonly onToggleAll:       () => void;
	readonly onToggleOne:       ( id: number ) => void;
	readonly onDelete:          ( ent: Entitlement ) => void;
}

export function LeaveEntitlementsTable( {
	rows,
	canManage,
	selected,
	allOnPageSelected,
	financialYears,
	onToggleAll,
	onToggleOne,
	onDelete,
}: LeaveEntitlementsTableProps ): JSX.Element {
	// An entitlement's validity = its financial-year window (legacy list-table
	// "Validity" column, which reads FinancialYear::find( f_year )->start/end).
	function validityLabel( fYearId: number | null ): string {
		if ( ! fYearId ) {
			return '';
		}
		const fy = financialYears.find( ( y ) => y.id === fYearId );
		if ( ! fy || ( ! fy.start_date && ! fy.end_date ) ) {
			return '';
		}
		const a = fy.start_date ? dateI18n( 'M j, Y', fy.start_date ) : '…';
		const b = fy.end_date ? dateI18n( 'M j, Y', fy.end_date ) : '…';
		return `${ a } – ${ b }`;
	}

	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-[40rem] text-left">
			<thead className="border-b border-border bg-card">
				<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
					{ canManage ? (
						<th scope="col" className="w-10 px-4">
							<Checkbox checked={ allOnPageSelected } onCheckedChange={ onToggleAll } aria-label={ __( 'Select all', 'erp' ) } />
						</th>
					) : null }
					<th scope="col" className="px-4">{ __( 'Employee', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Policy', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Days', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Validity', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Available', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Spent', 'erp' ) }</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( ent ) => (
					<tr key={ ent.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
						{ canManage ? (
							<td className="w-10 px-4 align-middle">
								<Checkbox checked={ selected.has( ent.id ) } onCheckedChange={ () => onToggleOne( ent.id ) } aria-label={ sprintf( __( 'Select %s', 'erp' ), ent.employee_name ) } />
							</td>
						) : null }
						<td className="px-4 align-middle text-sm font-medium text-foreground">
							{ ent.employee_name || <span className="text-muted-foreground">—</span> }
						</td>
						<td className="px-2 align-middle text-sm text-foreground">{ ent.policy_name }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ ent.days }</td>
						<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">
							{ validityLabel( ent.f_year ) || <span className="text-muted-foreground">—</span> }
						</td>
						<td className="px-2 align-middle text-sm text-foreground">
							{ ent.available }
							{ ent.extra_leave > 0 ? (
								<span className="ml-1 text-xs font-medium text-destructive" title={ __( 'Extra Leave', 'erp' ) }>
									{ sprintf( __( '(+%s extra)', 'erp' ), String( ent.extra_leave ) ) }
								</span>
							) : null }
						</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ ent.spent }</td>
						<td className="px-4 align-middle">
							{ canManage ? (
								<div className="flex justify-end">
									<DropdownMenu>
										<DropdownMenuTrigger
											render={
												<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), ent.employee_name ) }>
													<MoreVertical size={ 16 } aria-hidden="true" />
												</Button>
											}
										/>
										<DropdownMenuContent align="end" className="min-w-44">
											<DropdownMenuItem
												variant="destructive"
												className="gap-2"
												onClick={ () => onDelete( ent ) }
											>
												<Trash2 size={ 14 } aria-hidden="true" />
												{ __( 'Delete', 'erp' ) }
											</DropdownMenuItem>
										</DropdownMenuContent>
									</DropdownMenu>
								</div>
							) : null }
						</td>
					</tr>
				) ) }
			</tbody>
		</table>
		</div>
	);
}
