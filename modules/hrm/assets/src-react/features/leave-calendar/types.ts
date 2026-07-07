/**
 * Calendar event shape returned by `GET /erp/v2/leave-calendar`.
 *
 * `type`: 'leave' (an employee leave request), 'holiday' (a company holiday),
 * 'weekend' (a derived weekly-off background block).
 */
export interface CalendarEvent {
	readonly id:             number;
	readonly type:           'leave' | 'holiday' | 'weekend';
	readonly title:          string;
	/** Employee whose leave this is (present on leave events in the "all" scope). */
	readonly employee_name?: string;
	readonly start:          string | null;
	readonly end:            string | null;
	readonly color:          string;
	readonly reason?:        string;
	readonly status?:        number;
	readonly background?:    boolean;
	readonly user_id?:       number | null;
}
