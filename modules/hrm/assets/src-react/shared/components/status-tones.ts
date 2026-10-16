/**
 * Status -> `StatusPill` tone, for the statuses that are shown on more than
 * one screen. One mapping per kind of status, so an employee, a leave request
 * or a People request reads the same in a table, a card and a profile header.
 */

import type { StatusTone } from './StatusPill';

/** Employee status (the Employees table status cell and every profile header). */
export function employeeStatusTone( status: string | null | undefined ): StatusTone {
	switch ( status ) {
		case 'active':
			return 'success';
		case 'terminated':
		case 'resigned':
			return 'danger';
		default:
			// inactive, deceased, unknown.
			return 'neutral';
	}
}

/**
 * Leave request status code (the Leave Requests list and every employee leave
 * tab): 1 approved, 3 rejected, 4 forwarded, anything else is still pending.
 */
export function leaveStatusTone( code: number | null | undefined ): StatusTone {
	switch ( code ) {
		case 1:
			return 'success';
		case 3:
			return 'danger';
		case 4:
			// Forwarded (Advanced Leave multilevel): still open, but not Pending.
			return 'info';
		default:
			return 'warning';
	}
}

/** Resignation / Remote Work request status: pending, approved or rejected. */
export function requestStatusTone( status: string | null | undefined ): StatusTone {
	switch ( status ) {
		case 'approved':
			return 'success';
		case 'rejected':
			return 'danger';
		case 'pending':
			return 'warning';
		default:
			return 'neutral';
	}
}
