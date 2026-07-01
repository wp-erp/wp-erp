/**
 * Leave-types list table: name + description columns, a header checkbox for
 * bulk selection and a per-row Edit / Delete action menu. Presentational —
 * selection state and the edit/delete handlers are owned by `LeaveTypesPage`.
 */

import {
	Button,
	Checkbox,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { MoreVertical, Pencil, Trash2 } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

import type { LeaveType } from './types';

interface LeaveTypesTableProps {
	readonly rows:            readonly LeaveType[];
	readonly canManage:       boolean;
	readonly selected:        Set< number >;
	readonly allPageSelected: boolean;
	readonly onToggleAll:     () => void;
	readonly onToggleOne:     ( id: number ) => void;
	readonly onEdit:          ( type: LeaveType ) => void;
	readonly onDelete:        ( type: LeaveType ) => void;
}

export function LeaveTypesTable( {
	rows,
	canManage,
	selected,
	allPageSelected,
	onToggleAll,
	onToggleOne,
	onEdit,
	onDelete,
}: LeaveTypesTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-[40rem] text-left">
			<thead className="border-b border-border bg-card">
				<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
					{ canManage ? (
						<th scope="col" className="w-10 px-4">
							<Checkbox
								checked={ allPageSelected }
								onCheckedChange={ onToggleAll }
								aria-label={ __( 'Select all on this page', 'erp' ) }
							/>
						</th>
					) : null }
					<th scope="col" className="px-4">{ __( 'Leave Type', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Description', 'erp' ) }</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( type ) => (
					<tr key={ type.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
						{ canManage ? (
							<td className="px-4 align-middle">
								<Checkbox
									checked={ selected.has( type.id ) }
									onCheckedChange={ () => onToggleOne( type.id ) }
									aria-label={ sprintf( __( 'Select %s', 'erp' ), type.name ) }
								/>
							</td>
						) : null }
						<td className="px-4 align-middle text-sm font-medium text-foreground">{ type.name }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">
							{ type.description ? (
								<span className="line-clamp-1">{ type.description }</span>
							) : (
								<span className="text-muted-foreground">—</span>
							) }
						</td>
						<td className="px-4 align-middle">
							{ canManage ? (
								<div className="flex justify-end">
									<DropdownMenu>
										<DropdownMenuTrigger
											render={
												<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), type.name ) }>
													<MoreVertical size={ 16 } aria-hidden="true" />
												</Button>
											}
										/>
										<DropdownMenuContent align="end" className="min-w-44">
											<DropdownMenuItem className="gap-2" onClick={ () => onEdit( type ) }>
												<Pencil size={ 14 } aria-hidden="true" />
												{ __( 'Edit', 'erp' ) }
											</DropdownMenuItem>
											<DropdownMenuItem
												variant="destructive"
												className="gap-2"
												onClick={ () => onDelete( type ) }
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
