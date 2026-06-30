/**
 * Holidays list table: selectable rows with title, date (or range), duration,
 * description and a per-row Edit / Delete menu. Presentational — selection and
 * row actions are driven from the page.
 */

import { Button, Checkbox, DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@wedevs/plugin-ui';
import { MoreVertical, Pencil, Trash2 } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

import { fmt } from './holidays-format';
import type { Holiday } from './types';

interface HolidaysTableProps {
	readonly rows:        readonly Holiday[];
	readonly canManage:   boolean;
	readonly selectedIds: readonly number[];
	readonly allSelected: boolean;
	readonly onToggleAll: () => void;
	readonly onToggleRow: ( id: number ) => void;
	readonly onEdit:      ( holiday: Holiday ) => void;
	readonly onDelete:    ( holiday: Holiday ) => void;
}

export function HolidaysTable( {
	rows,
	canManage,
	selectedIds,
	allSelected,
	onToggleAll,
	onToggleRow,
	onEdit,
	onDelete,
}: HolidaysTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-[40rem] text-left">
			<thead className="border-b border-border bg-card">
				<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
					{ canManage ? (
						<th scope="col" className="w-10 px-4">
							<Checkbox
								checked={ allSelected }
								onCheckedChange={ onToggleAll }
								aria-label={ __( 'Select all holidays', 'erp' ) }
							/>
						</th>
					) : null }
					<th scope="col" className="px-4">{ __( 'Title', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Date', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Duration', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Description', 'erp' ) }</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( holiday ) => (
					<tr key={ holiday.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
						{ canManage ? (
							<td className="w-10 px-4 align-middle">
								<Checkbox
									checked={ selectedIds.includes( holiday.id ) }
									onCheckedChange={ () => onToggleRow( holiday.id ) }
									aria-label={ sprintf( __( 'Select %s', 'erp' ), holiday.title ) }
								/>
							</td>
						) : null }
						<td className="px-4 align-middle font-medium text-foreground">{ holiday.title }</td>
						<td className="whitespace-nowrap px-2 align-middle text-sm text-foreground">
							{ holiday.range
								? `${ fmt( holiday.start ) } – ${ fmt( holiday.end ) }`
								: fmt( holiday.start ) }
						</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">
							{ sprintf(
								/* translators: %d: number of days */
								holiday.duration === 1 ? __( '%d day', 'erp' ) : __( '%d days', 'erp' ),
								holiday.duration
							) }
						</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">
							{ holiday.description ? (
								<span className="line-clamp-1">{ holiday.description }</span>
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
												<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), holiday.title ) }>
													<MoreVertical size={ 16 } aria-hidden="true" />
												</Button>
											}
										/>
										<DropdownMenuContent align="end" className="min-w-44">
											<DropdownMenuItem className="gap-2" onClick={ () => onEdit( holiday ) }>
												<Pencil size={ 14 } aria-hidden="true" />
												{ __( 'Edit', 'erp' ) }
											</DropdownMenuItem>
											<DropdownMenuItem
												variant="destructive"
												className="gap-2"
												onClick={ () => onDelete( holiday ) }
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
