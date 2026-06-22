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

import { __, sprintf } from '@/shared/i18n';

import type { Entitlement } from './types';

interface LeaveEntitlementsTableProps {
	readonly rows:              readonly Entitlement[];
	readonly canManage:         boolean;
	readonly selected:          ReadonlySet< number >;
	readonly allOnPageSelected: boolean;
	readonly onToggleAll:       () => void;
	readonly onToggleOne:       ( id: number ) => void;
	readonly onDelete:          ( ent: Entitlement ) => void;
}

export function LeaveEntitlementsTable( {
	rows,
	canManage,
	selected,
	allOnPageSelected,
	onToggleAll,
	onToggleOne,
	onDelete,
}: LeaveEntitlementsTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-[40rem] text-left">
			<thead className="border-b border-border bg-muted/40">
				<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
					{ canManage ? (
						<th scope="col" className="w-10 px-4">
							<Checkbox checked={ allOnPageSelected } onCheckedChange={ onToggleAll } aria-label={ __( 'Select all', 'erp' ) } />
						</th>
					) : null }
					<th scope="col" className="px-4">{ __( 'Employee', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Policy', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Days', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Available', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Spent', 'erp' ) }</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( ent ) => (
					<tr key={ ent.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
						{ canManage ? (
							<td className="w-10 px-4 align-middle">
								<Checkbox checked={ selected.has( ent.id ) } onCheckedChange={ () => onToggleOne( ent.id ) } aria-label={ sprintf( __( 'Select %s', 'erp' ), ent.employee_name ) } />
							</td>
						) : null }
						<td className="px-4 align-middle font-medium text-foreground">
							{ ent.employee_name || <span className="text-muted-foreground">—</span> }
						</td>
						<td className="px-2 align-middle text-sm text-foreground">{ ent.policy_name }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ ent.days }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ ent.available }</td>
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
