/**
 * Holidays list table: selectable rows with title, date (or range), duration,
 * description and a per-row Edit / Delete menu. Presentational — selection and
 * row actions are driven from the page.
 */

import { Button, Checkbox, DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@wedevs/plugin-ui';
import { ChevronDown, ChevronUp, ChevronsUpDown, MoreVertical, Pencil, Trash2 } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

import { fmt } from './holidays-format';
import type { Holiday } from './types';

type SortColumn = 'title' | 'start';

interface HolidaysTableProps {
	readonly rows:        readonly Holiday[];
	readonly canManage:   boolean;
	readonly selectedIds: readonly number[];
	readonly allSelected: boolean;
	readonly orderby:     SortColumn;
	readonly order:       'asc' | 'desc';
	readonly onSort:      ( column: SortColumn ) => void;
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
	orderby,
	order,
	onSort,
	onToggleAll,
	onToggleRow,
	onEdit,
	onDelete,
}: HolidaysTableProps ): JSX.Element {
	// Raw sort-header button (DS Button is not used for table sort headers).
	function SortButton( { column, label }: { column: SortColumn; label: string } ): JSX.Element {
		const active = orderby === column;
		const Icon   = ! active ? ChevronsUpDown : order === 'asc' ? ChevronUp : ChevronDown;
		return (
			<button
				type="button"
				onClick={ () => onSort( column ) }
				aria-label={ sprintf( __( 'Sort by %s', 'erp' ), label ) }
				className={ [
					'inline-flex items-center gap-1 uppercase transition-colors',
					active ? 'text-foreground' : 'hover:text-foreground',
				].join( ' ' ) }
			>
				{ label }
				<Icon size={ 13 } strokeWidth={ 2 } aria-hidden="true" className={ active ? 'text-primary' : 'opacity-60' } />
			</button>
		);
	}

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
					<th scope="col" className="px-4"><SortButton column="title" label={ __( 'Title', 'erp' ) } /></th>
					<th scope="col" className="px-2"><SortButton column="start" label={ __( 'Date', 'erp' ) } /></th>
					<th scope="col" className="px-2">{ __( 'Duration', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Description', 'erp' ) }</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( holiday ) => (
					<tr key={ holiday.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
						{ canManage ? (
							<td className="w-10 px-4 align-middle">
								<Checkbox
									checked={ selectedIds.includes( holiday.id ) }
									onCheckedChange={ () => onToggleRow( holiday.id ) }
									aria-label={ sprintf( __( 'Select %s', 'erp' ), holiday.title ) }
								/>
							</td>
						) : null }
						<td className="px-4 align-middle text-sm font-medium text-foreground">{ holiday.title }</td>
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
