/**
 * Leave-requests table — the paginated row grid for `/leave/requests`.
 *
 * Pure presentation: it receives the resolved rows + the current selection and
 * reports user intent (select, moderate, delete, sort) back to the page via
 * callbacks. Row layout mirrors the Employees table conventions (h-18 rows,
 * px-4 ends). Columns mirror the legacy `LeaveRequestsListTable`: employee,
 * policy, request-for (dates + days), requested-on, available balance, status,
 * approved/rejected-by, and the uploaded documents.
 */

import {
	Checkbox,
} from '@wedevs/plugin-ui';
import {
	Check,
	Paperclip,
	RotateCcw,
	Trash2,
	X,
} from 'lucide-react';
import { applyFilters } from '@wordpress/hooks';
import type { JSX } from 'react';

import { PersonCell } from '@/shared/components/PersonCell';
import { RowActionsMenu, type RowAction } from '@/shared/components/RowActionsMenu';
import { SortHeader } from '@/shared/components/SortHeader';
import { StatusPill } from '@/shared/components/StatusPill';
import { leaveStatusTone } from '@/shared/components/status-tones';
import { HOOKS } from '@/shared/filters';
import { __, sprintf } from '@/shared/i18n';
import { formatDisplayDate } from '@/shared/utils/date';

import type { LeaveRequest, LeaveRequestRowAction } from './types';

/**
 * Long "Year Mon D" date label; "—" when empty, raw slice when unparseable.
 * Parses date-only `YYYY-MM-DD` as a local day so it never shifts back one.
 * @param value
 */
function fmt( value: string | null ): string {
	return formatDisplayDate( value, ( value ?? '' ).slice( 0, 10 ) || '—' );
}

/**
 * Available-balance cell — mirrors the legacy `available` column: a green
 * remaining-days chip, or a red over-drawn "Extra Leave" chip, or an em-dash.
 * @param root0
 * @param root0.available
 * @param root0.extra
 */
function AvailableCell( {
	available,
	extra,
}: {
	available: number;
	extra: number;
} ): JSX.Element {
	if ( extra > 0 ) {
		return (
			<span
				className="text-destructive"
				title={ __( 'Extra Leave', 'erp' ) }
			>
				{ sprintf(
					extra === 1
						? __( '-%s day', 'erp' )
						: __( '-%s days', 'erp' ),
					String( extra )
				) }
			</span>
		);
	}
	if ( available > 0 ) {
		return (
			<span
				className="text-success-on-light"
				title={ __( 'Available Leave', 'erp' ) }
			>
				{ sprintf(
					available === 1
						? __( '%s day', 'erp' )
						: __( '%s days', 'erp' ),
					String( available )
				) }
			</span>
		);
	}
	return <span className="text-muted-foreground">—</span>;
}

interface LeaveRequestsTableProps {
	readonly rows: readonly LeaveRequest[];
	readonly canManage: boolean;
	readonly selected: ReadonlySet< number >;
	readonly allOnPageSelected: boolean;
	/** Active status tab — decides the "Approved By" vs "Rejected By" header. */
	readonly statusFilter: number;
	readonly orderby: string;
	readonly order: 'asc' | 'desc';
	readonly onSort: ( column: string ) => void;
	readonly onToggleAll: () => void;
	readonly onToggleOne: ( id: number ) => void;
	readonly onModerate: (
		action: 'approve' | 'reject',
		request: LeaveRequest
	) => void;
	readonly onDelete: ( request: LeaveRequest ) => void;
}

export function LeaveRequestsTable( {
	rows,
	canManage,
	selected,
	allOnPageSelected,
	statusFilter,
	orderby,
	order,
	onSort,
	onToggleAll,
	onToggleOne,
	onModerate,
	onDelete,
}: LeaveRequestsTableProps ): JSX.Element {
	const approverHeader =
		statusFilter === 3
			? __( 'Rejected By', 'erp' )
			: __( 'Approved By', 'erp' );

	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-240 text-left">
				<thead className="border-b border-border bg-card">
					<tr className="h-10">
						{ canManage ? (
							<th scope="col" className="w-10 px-4">
								<Checkbox
									checked={ allOnPageSelected }
									onCheckedChange={ onToggleAll }
									aria-label={ __( 'Select all', 'erp' ) }
								/>
							</th>
						) : null }
						<SortHeader
							label={ __( 'Employee', 'erp' ) }
							sortKey="name"
							orderBy={ orderby }
							order={ order }
							onSort={ onSort }
							ariaLabel={ sprintf( __( 'Sort by %s', 'erp' ), __( 'Employee', 'erp' ) ) }
							first
						/>
						<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
							{ __( 'Leave Type', 'erp' ) }
						</th>
						<SortHeader
							label={ __( 'Duration', 'erp' ) }
							sortKey="start_date"
							orderBy={ orderby }
							order={ order }
							onSort={ onSort }
							ariaLabel={ sprintf( __( 'Sort by %s', 'erp' ), __( 'Duration', 'erp' ) ) }
							first={ false }
						/>
						<SortHeader
							label={ __( 'Days', 'erp' ) }
							sortKey="days"
							orderBy={ orderby }
							order={ order }
							onSort={ onSort }
							ariaLabel={ sprintf( __( 'Sort by %s', 'erp' ), __( 'Days', 'erp' ) ) }
							first={ false }
						/>
						<SortHeader
							label={ __( 'Requested On', 'erp' ) }
							sortKey="created_at"
							orderBy={ orderby }
							order={ order }
							onSort={ onSort }
							ariaLabel={ sprintf( __( 'Sort by %s', 'erp' ), __( 'Requested On', 'erp' ) ) }
							first={ false }
						/>
						<SortHeader
							label={ __( 'Available', 'erp' ) }
							sortKey="available"
							orderBy={ orderby }
							order={ order }
							onSort={ onSort }
							ariaLabel={ sprintf( __( 'Sort by %s', 'erp' ), __( 'Available', 'erp' ) ) }
							first={ false }
						/>
						<SortHeader
							label={ __( 'Status', 'erp' ) }
							sortKey="last_status"
							orderBy={ orderby }
							order={ order }
							onSort={ onSort }
							ariaLabel={ sprintf( __( 'Sort by %s', 'erp' ), __( 'Status', 'erp' ) ) }
							first={ false }
						/>
						<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
							{ __( 'Reason', 'erp' ) }
						</th>
						<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
							{ approverHeader }
						</th>
						<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
							{ __( 'Docs', 'erp' ) }
						</th>
						<th scope="col" className="w-24 px-4">
							<span className="sr-only">
								{ __( 'Actions', 'erp' ) }
							</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{ rows.map( ( req ) => (
						<tr
							key={ req.id }
							className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40"
						>
							{ canManage ? (
								<td className="w-10 px-4 align-middle">
									<Checkbox
										checked={ selected.has( req.id ) }
										onCheckedChange={ () =>
											onToggleOne( req.id )
										}
										aria-label={ sprintf(
											__( 'Select %s', 'erp' ),
											req.name
										) }
									/>
								</td>
							) : null }
							<td className="px-4 align-middle text-sm font-medium text-foreground">
								{ req.name ? (
									<PersonCell
										name={ req.name }
										avatar={ req.avatar }
									/>
								) : (
									<span className="text-muted-foreground">
										—
									</span>
								) }
							</td>
							<td className="whitespace-nowrap px-2 align-middle text-sm text-foreground">
								<span className="inline-flex items-center gap-2">
									<span
										aria-hidden="true"
										className="inline-block size-2.5 shrink-0 rounded-full"
										style={ {
											backgroundColor:
												req.color || 'transparent',
										} }
									/>
									{ req.policy_name }
								</span>
							</td>
							<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">
								{ `${ fmt( req.start_date ) } – ${ fmt(
									req.end_date
								) }` }
							</td>
							<td className="px-2 align-middle text-sm text-foreground">
								{ req.days }
								{ /* A half day says WHICH half — legacy printed Morning /
								     Afternoon in place of the day count. Kept alongside the
								     0.5 rather than replacing it, so the ledger figure stays
								     visible. */ }
								{ req.day_status_label ? (
									<span className="ml-1 text-xs text-muted-foreground">
										{ `(${ req.day_status_label })` }
									</span>
								) : null }
							</td>
							<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">
								{ fmt( req.created_at ) }
							</td>
							<td className="whitespace-nowrap px-2 align-middle text-sm">
								<AvailableCell
									available={ req.available }
									extra={ req.extra_leaves }
								/>
							</td>
							<td className="whitespace-nowrap px-2 align-middle">
								<StatusPill tone={ leaveStatusTone( req.status ) }>
									{ req.status_label }
								</StatusPill>
							</td>
							<td className="max-w-48 px-2 align-middle text-sm text-muted-foreground">
								{ req.reason ? (
									<span
										className="block truncate"
										title={ req.reason }
									>
										{ req.reason }
									</span>
								) : (
									<span className="text-muted-foreground">
										—
									</span>
								) }
							</td>
							<td className="px-2 align-middle text-sm text-muted-foreground">
								{ req.approved_by ? (
									<div className="flex flex-col leading-tight">
										<span className="font-medium text-foreground">
											{ req.approved_by }
										</span>
										{ req.approved_at ? (
											<span className="text-xs">
												{ fmt( req.approved_at ) }
											</span>
										) : null }
										{ req.approver_note ? (
											<span
												className="max-w-48 truncate text-xs italic"
												title={ req.approver_note }
											>
												{ req.approver_note }
											</span>
										) : null }
									</div>
								) : (
									<span className="text-muted-foreground">
										—
									</span>
								) }
							</td>
							<td className="px-2 align-middle text-sm">
								{ req.attachments.length > 0 ? (
									<div className="flex flex-col gap-1">
										{ req.attachments.map( ( file ) => (
											<a
												key={ file.id }
												href={ file.url }
												target="_blank"
												rel="noreferrer"
												className="inline-flex max-w-48 items-center gap-1 truncate text-primary hover:underline"
												title={ file.filename }
											>
												<Paperclip
													size={ 13 }
													aria-hidden="true"
													className="shrink-0"
												/>
												<span className="truncate">
													{ file.filename }
												</span>
											</a>
										) ) }
									</div>
								) : (
									<span className="text-muted-foreground">
										—
									</span>
								) }
							</td>
							<td className="px-4 align-middle">
								{ canManage ? (
									<div className="flex items-center justify-end gap-1">
										{ /* 2 = Pending, 4 = Forwarded (Advanced Leave multilevel).
										     Both are open requests still awaiting a decision, and the
										     legacy list table offers Approve/Reject on both
										     (`LeaveRequestsListTable::column_name()`). Gating on 2
										     alone left a forwarded request with Delete as its only
										     action — the approver it was forwarded to could not act. */ }
										<RowActionsMenu
											label={ sprintf( __( 'Actions for %s', 'erp' ), req.name ) }
											actions={ [
												{ id: 'approve', label: __( 'Approve', 'erp' ), icon: Check, onSelect: () => onModerate( 'approve', req ), hidden: ! ( req.status === 2 || req.status === 4 ) },
												{ id: 'reject', label: __( 'Reject', 'erp' ), icon: X, onSelect: () => onModerate( 'reject', req ), variant: 'destructive', hidden: ! ( req.status === 2 || req.status === 4 ) },
												// Reverse moderation: reject an approved request, or approve a rejected one (legacy list-table behaviour).
												{ id: 'reverse-reject', label: __( 'Reject', 'erp' ), icon: RotateCcw, onSelect: () => onModerate( 'reject', req ), variant: 'destructive', hidden: req.status !== 1 },
												{ id: 'reverse-approve', label: __( 'Approve', 'erp' ), icon: RotateCcw, onSelect: () => onModerate( 'approve', req ), hidden: req.status !== 3 },
												// Pro-appended row actions (Advanced Leave multilevel: Forward).
												...( applyFilters( HOOKS.LEAVE_REQUEST_ROW_ACTIONS, [], { request: req } ) as LeaveRequestRowAction[] ).map( ( action ): RowAction => ( {
													id:       action.id,
													label:    action.label,
													...( action.icon ? { icon: action.icon } : {} ),
													onSelect: () => action.onSelect( req ),
													variant:  action.variant === 'destructive' ? 'destructive' : 'default',
												} ) ),
												{ id: 'delete', label: __( 'Delete', 'erp' ), icon: Trash2, onSelect: () => onDelete( req ), variant: 'destructive' },
											] }
										/>
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
