/**
 * Announcements list table: title + excerpt, recipient avatar stack, author,
 * date, and a per-row actions menu (Edit / Restore + Trash / Delete). All
 * mutations are delegated to the page via callbacks; rendering only.
 */

import {
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { MoreVertical, Pencil, RotateCcw, Trash2 } from 'lucide-react';
import type { JSX } from 'react';

import { EmployeeAvatarStack } from '@/shared/components/EmployeeAvatarStack';
import { __, sprintf } from '@/shared/i18n';

import { fmt } from './announcements-format';
import type { Announcement } from './types';

interface AnnouncementsTableProps {
	readonly rows:      ReadonlyArray< Announcement >;
	readonly canManage: boolean;
	readonly onEdit:    ( row: Announcement ) => void;
	readonly onRestore: ( row: Announcement ) => void;
	readonly onDelete:  ( row: Announcement ) => void;
}

export function AnnouncementsTable( { rows, canManage, onEdit, onRestore, onDelete }: AnnouncementsTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-[40rem] text-left">
			<thead className="border-b border-border bg-muted/40">
				<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
					<th scope="col" className="px-4">{ __( 'Title', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2">{ __( 'Recipients', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2">{ __( 'Author', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2">{ __( 'Date', 'erp' ) }</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( row ) => (
					<tr key={ row.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
						<td className="max-w-md px-4 align-middle">
							<div className="truncate font-medium text-foreground">{ row.title || __( '(no title)', 'erp' ) }</div>
							{ row.excerpt ? (
								<div className="truncate text-xs text-muted-foreground">{ row.excerpt }</div>
							) : null }
						</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">
							<EmployeeAvatarStack people={ row.recipients_preview } total={ row.recipient_count } />
						</td>
						<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">{ row.author || '—' }</td>
						<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">{ fmt( row.date ) }</td>
						<td className="px-4 align-middle">
							{ canManage ? (
								<div className="flex justify-end">
									<DropdownMenu>
										<DropdownMenuTrigger
											render={
												<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), row.title ) }>
													<MoreVertical size={ 16 } aria-hidden="true" />
												</Button>
											}
										/>
										<DropdownMenuContent align="end" className="min-w-44">
											{ row.status === 'trash' ? (
												<DropdownMenuItem className="gap-2" onClick={ () => onRestore( row ) }>
													<RotateCcw size={ 14 } aria-hidden="true" />
													{ __( 'Restore', 'erp' ) }
												</DropdownMenuItem>
											) : (
												<DropdownMenuItem className="gap-2" onClick={ () => onEdit( row ) }>
													<Pencil size={ 14 } aria-hidden="true" />
													{ __( 'Edit', 'erp' ) }
												</DropdownMenuItem>
											) }
											<DropdownMenuItem
												variant="destructive"
												className="gap-2"
												onClick={ () => onDelete( row ) }
											>
												<Trash2 size={ 14 } aria-hidden="true" />
												{ row.status === 'trash' ? __( 'Delete permanently', 'erp' ) : __( 'Trash', 'erp' ) }
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
