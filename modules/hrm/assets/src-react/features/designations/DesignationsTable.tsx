/**
 * Designations list table: sortable Name / Employees columns, per-page select
 * checkboxes and a row actions menu (Edit / Delete). Pure presentation — all
 * state and handlers come from `DesignationsPage`.
 */

import {
	Button,
	Checkbox,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { ArrowDown, ArrowUp, ArrowUpDown, MoreVertical, Pencil, Trash2 } from 'lucide-react';
import type { JSX } from 'react';
import { Link } from 'react-router-dom';

import { EmployeeAvatarStack } from '@/shared/components/EmployeeAvatarStack';
import { __, sprintf } from '@/shared/i18n';

import type { Designation } from './types';

export type SortKey = 'title' | 'total_employees';

interface DesignationsTableProps {
	readonly rows:         readonly Designation[];
	readonly canManage:    boolean;
	readonly selected:     ReadonlySet< number >;
	readonly allChecked:   boolean;
	readonly sort:         { key: SortKey; dir: 'asc' | 'desc' };
	readonly onToggleAll:  () => void;
	readonly onToggleOne:  ( id: number ) => void;
	readonly onToggleSort: ( key: SortKey ) => void;
	readonly onEdit:       ( designation: Designation ) => void;
	readonly onDelete:     ( designation: Designation ) => void;
}

export function DesignationsTable( {
	rows,
	canManage,
	selected,
	allChecked,
	sort,
	onToggleAll,
	onToggleOne,
	onToggleSort,
	onEdit,
	onDelete,
}: DesignationsTableProps ): JSX.Element {
	function sortIcon( key: SortKey ): JSX.Element {
		if ( sort.key !== key ) {
			return <ArrowUpDown size={ 12 } aria-hidden="true" />;
		}
		return sort.dir === 'asc'
			? <ArrowUp size={ 12 } aria-hidden="true" />
			: <ArrowDown size={ 12 } aria-hidden="true" />;
	}

	return (
		<table className="w-full text-left">
			<thead className="border-b border-border bg-card">
				<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
					{ canManage ? (
						<th scope="col" className="w-10 px-4">
							<Checkbox checked={ allChecked } onCheckedChange={ onToggleAll } aria-label={ __( 'Select all', 'erp' ) } />
						</th>
					) : null }
					<th scope="col" className="px-2">
						<button type="button" onClick={ () => onToggleSort( 'title' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
							{ __( 'Name', 'erp' ) }{ sortIcon( 'title' ) }
						</button>
					</th>
					<th scope="col" className="px-2">
						<button type="button" onClick={ () => onToggleSort( 'total_employees' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
							{ __( 'Employees', 'erp' ) }{ sortIcon( 'total_employees' ) }
						</button>
					</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( desig ) => (
					<tr key={ desig.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
						{ canManage ? (
							<td className="px-4 align-middle">
								<Checkbox checked={ selected.has( desig.id ) } onCheckedChange={ () => onToggleOne( desig.id ) } aria-label={ sprintf( __( 'Select %s', 'erp' ), desig.title ) } />
							</td>
						) : null }
						<td className="px-2 align-middle text-sm">
							<Link
								to={ `/employees?designation_id=${ desig.id }` }
								className="font-medium text-foreground hover:text-primary hover:underline"
							>
								{ desig.title }
							</Link>
							{ desig.description ? (
								<div className="truncate text-xs text-muted-foreground">{ desig.description }</div>
							) : null }
						</td>
						<td className="px-2 align-middle text-sm text-foreground">
							<EmployeeAvatarStack people={ desig.employees } total={ desig.total_employees } />
						</td>
						<td className="px-4 align-middle">
							{ canManage ? (
								<div className="flex justify-end">
									<DropdownMenu>
										<DropdownMenuTrigger
											render={
												<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), desig.title ) }>
													<MoreVertical size={ 16 } aria-hidden="true" />
												</Button>
											}
										/>
										<DropdownMenuContent align="end" className="min-w-44">
											<DropdownMenuItem className="gap-2" onClick={ () => onEdit( desig ) }>
												<Pencil size={ 14 } aria-hidden="true" />
												{ __( 'Edit', 'erp' ) }
											</DropdownMenuItem>
											<DropdownMenuItem
												variant="destructive"
												className="gap-2"
												onClick={ () => onDelete( desig ) }
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
	);
}
