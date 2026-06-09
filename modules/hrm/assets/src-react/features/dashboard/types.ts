/**
 * Response shape for `GET /erp/v2/dashboard` — the HR Overview landing page.
 *
 * Mirrors the legacy `views/dashboard.php` widgets (summary badges, who-is-out,
 * birthdays, upcoming holidays, latest announcements). Read-only.
 */

export interface DashboardSummary {
	readonly total_employees:      number;
	readonly total_departments:    number;
	readonly total_designations:   number;
	readonly headcount_this_month: number;
	readonly pending_requests:     number;
}

export interface DashboardPerson {
	readonly user_id:    number;
	readonly name:       string;
	readonly avatar_url: string;
}

export interface OnLeavePerson extends DashboardPerson {
	readonly start_date: string | null;
	readonly end_date:   string | null;
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
}

/** A list row inside a pro widget (e.g. a recent candidate). */
export interface DashboardProItem {
	readonly label: string;
	readonly meta?: string;
	readonly to?:   string;
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
	/** Empty-state text when there is nothing to show. */
	readonly empty?: string;
}

export interface DashboardData {
	readonly is_hr_manager:      boolean;
	readonly summary:            DashboardSummary;
	readonly on_leave:           readonly OnLeavePerson[];
	readonly birthdays_today:    readonly BirthdayPerson[];
	readonly birthdays_upcoming: readonly BirthdayPerson[];
	readonly holidays_upcoming:  readonly DashboardHoliday[];
	readonly announcements:      readonly DashboardAnnouncement[];
	readonly charts:             DashboardCharts;
	readonly pro_widgets?:       readonly DashboardProWidget[];
}
