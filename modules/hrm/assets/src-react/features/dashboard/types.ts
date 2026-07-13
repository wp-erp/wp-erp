/**
 * Response shape for `GET /erp/v2/dashboard` — the HR Overview landing page.
 *
 * Mirrors the legacy `views/dashboard.php` widgets (summary badges, who-is-out,
 * birthdays, upcoming holidays, latest announcements). Read-only.
 */

export interface DashboardSummary {
	readonly total_employees:    number;
	readonly total_departments:  number;
	readonly total_designations: number;
	readonly pending_requests:   number;
}

export interface DashboardPerson {
	readonly user_id:    number;
	readonly name:       string;
	readonly avatar_url: string;
}

export interface OnLeavePerson extends DashboardPerson {
	readonly start_date: string | null;
	readonly end_date:   string | null;
	/** Which bucket this row belongs to (legacy "This Month" / "Next Month"). */
	readonly period:        'this_month' | 'next_month';
	/** 1 = full day, 2 = Morning half-day, 3 = Afternoon half-day. */
	readonly day_status_id: number;
	/** Localised half-day label (empty for full-day rows). */
	readonly day_status:    string;
}

export interface BirthdayPerson extends DashboardPerson {
	readonly date_of_birth: string | null;
}

export interface DashboardHoliday {
	readonly id:          number;
	readonly title:       string;
	readonly start:       string | null;
	readonly end:         string | null;
	readonly description: string | null;
}

export interface DashboardAnnouncement {
	readonly id:    number;
	readonly title: string;
	readonly date:  string | null;
	/** Per-user read state (managers always see `true`). */
	readonly read:  boolean;
}

/** A contractual / trainee employee whose job period is about to end. */
export interface AboutToEndPerson {
	readonly user_id:  number;
	readonly name:     string;
	readonly end_date: string | null;
}

export interface AboutToEnd {
	readonly contract: readonly AboutToEndPerson[];
	readonly trainee:  readonly AboutToEndPerson[];
}

export interface HeadcountTrendPoint {
	readonly month: string;
	readonly count: number;
}

export interface DepartmentDatum {
	readonly name:  string;
	readonly count: number;
}

export interface DashboardCharts {
	readonly headcount_trend: readonly HeadcountTrendPoint[];
	readonly gender:          { readonly male: number; readonly female: number; readonly other: number };
	readonly departments:     readonly DepartmentDatum[];
	readonly leave_status:    { readonly approved: number; readonly pending: number; readonly rejected: number };
}

/** A single figure inside a pro widget (e.g. "Open Openings: 5"). */
export interface DashboardProStat {
	readonly label: string;
	readonly value: number | string;
	/** Render as a full-width highlighted box (e.g. recruitment "Open Openings"). */
	readonly featured?: boolean;
}

/** A list row inside a pro widget (e.g. a recent candidate). */
export interface DashboardProItem {
	readonly label:       string;
	readonly meta?:       string;
	readonly to?:         string;
	/** Optional secondary line under the label (e.g. a designation). */
	readonly sub?:        string;
	/** Optional avatar for people rows. */
	readonly avatar_url?: string;
	/** Optional status pill text + tone (e.g. Approved / Pending / Rejected). */
	readonly status?:     string;
	readonly tone?:       'success' | 'warning' | 'destructive' | 'muted';
}

/**
 * A pro-module dashboard widget. Pro modules append these to `pro_widgets` via
 * the `erp_hr_v2_dashboard` PHP filter; the dashboard renders them generically
 * (a stats row and/or an item list) under "Upcoming Holidays".
 */
export interface DashboardProWidget {
	readonly id:     string;
	readonly title:  string;
	/** Stable icon id mapped to a lucide icon (default: a generic mark). */
	readonly icon?:  string;
	/** Optional "view all" route for the card header. */
	readonly to?:    string;
	readonly stats?: readonly DashboardProStat[];
	readonly items?: readonly DashboardProItem[];
	/** Optional sub-heading above the item list (e.g. "Recent Requests"). */
	readonly itemsTitle?: string;
	/** Empty-state text when there is nothing to show. */
	readonly empty?: string;
}

export interface DashboardData {
	readonly is_hr_manager:      boolean;
	readonly summary:            DashboardSummary;
	readonly on_leave:           readonly OnLeavePerson[];
	readonly about_to_end?:      AboutToEnd;
	readonly birthdays_today:    readonly BirthdayPerson[];
	readonly birthdays_upcoming: readonly BirthdayPerson[];
	readonly holidays_upcoming:  readonly DashboardHoliday[];
	readonly announcements:      readonly DashboardAnnouncement[];
	readonly charts:             DashboardCharts;
	readonly pro_widgets?:       readonly DashboardProWidget[];
}
