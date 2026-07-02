/**
 * Response shapes for the read-only `erp/v2/reports/*` endpoints.
 *
 * Each mirrors a legacy reporting view (modules/hrm/views/reporting/*) or the
 * `LeaveReportEmployeeBased` WP_List_Table. Reports are free + read-only.
 */

/** One catalogue entry from `GET /reports` (mirrors erp_hr_get_reports()). */
export interface ReportCatalogueItem {
	readonly key:         string;
	readonly title:       string;
	readonly description: string;
}

/** One department row in the age-profile report. */
export interface AgeProfileRow {
	readonly department:  string;
	readonly under_18:    number;
	readonly age_18_25:   number;
	readonly age_26_35:   number;
	readonly age_36_45:   number;
	readonly age_46_55:   number;
	readonly age_56_65:   number;
	readonly age_65_plus: number;
}

export interface AgeProfileResponse {
	readonly rows: readonly AgeProfileRow[];
}

/** One gender row in the gender-profile report. */
export interface GenderProfileRow {
	readonly gender:     string;
	readonly count:      number;
	readonly percentage: string;
}

/** Per-department gender counts (mirrors the legacy "By Department" table + bar). */
export interface GenderDepartmentRow {
	readonly department: string;
	readonly male:       number;
	readonly female:     number;
	readonly other:      number;
}

export interface GenderProfileResponse {
	readonly rows:           readonly GenderProfileRow[];
	readonly by_department?: readonly GenderDepartmentRow[];
}

/** A single point in the headcount-by-month series. */
export interface HeadcountPoint {
	readonly month: string;
	readonly count: number;
}

/** An active employee row in the headcount list. */
export interface HeadcountEmployee {
	readonly user_id:     number;
	readonly employee_id: string | null;
	readonly name:        string;
	readonly avatar:      string | null;
	readonly hire_date:   string | null;
	readonly designation: string | null;
	readonly department:  string | null;
	readonly location:    string | null;
	readonly status:      string | null;
}

export interface HeadcountResponse {
	readonly chart:       readonly HeadcountPoint[];
	readonly total:       number;
	readonly employees:   readonly HeadcountEmployee[];
	readonly years:       readonly number[];
	readonly departments: ReadonlyArray< { id: number; label: string } >;
	readonly filters:     {
		readonly year:       string;
		readonly department: number | null;
	};
}

/** One compensation row in the salary-history report. */
export interface SalaryHistoryRow {
	readonly user_id:     number;
	readonly employee_id: string | null;
	readonly name:        string;
	readonly avatar:      string | null;
	readonly date:        string | null;
	readonly pay_rate:    string | null;
	readonly pay_type:    string | null;
}

export interface SalaryHistoryResponse {
	readonly rows: readonly SalaryHistoryRow[];
}

/** One person within a years-of-service day group. */
export interface ServicePerson {
	readonly user_id:     number;
	readonly name:        string;
	readonly avatar:      string | null;
	readonly hiring_date: string | null;
	readonly years:       number;
}

export interface ServiceDay {
	readonly day:    number;
	readonly people: readonly ServicePerson[];
}

export interface ServiceMonth {
	readonly month:      number;
	readonly month_name: string;
	readonly days:       readonly ServiceDay[];
}

export interface YearsOfServiceResponse {
	readonly months: readonly ServiceMonth[];
}

/** A leave-matrix column (one per leave policy/type). */
export interface LeaveReportColumn {
	readonly leave_id: number;
	readonly name:     string;
}

/** A single matrix cell — spent / entitled days, or null when no entitlement. */
export interface LeaveReportCell {
	readonly spent: number;
	readonly days:  number;
}

export interface LeaveReportRow {
	readonly user_id: number;
	readonly name:    string;
	readonly avatar:  string | null;
	readonly cells:   Record< string, LeaveReportCell | null >;
}

export interface LeaveReportResponse {
	readonly columns:        readonly LeaveReportColumn[];
	readonly rows:           readonly LeaveReportRow[];
	readonly current_f_year: number;
}

/** Filter pickers for the leaves report (`GET /reports/leaves/form-options`). */
export interface LeaveReportFormOptions {
	readonly financial_years:  ReadonlyArray< { id: number; label: string } >;
	readonly designations:     ReadonlyArray< { id: number; label: string } >;
	readonly departments:      ReadonlyArray< { id: number; label: string } >;
	readonly employment_types: ReadonlyArray< { value: string; label: string } >;
	readonly current_f_year:   number;
}

/** Filter state for the leaves report. */
export interface LeaveReportFilters {
	readonly filter_year:            string;
	readonly filter_designation:     number;
	readonly filter_department:      number;
	readonly filter_employment_type: string;
	readonly start:                  string;
	readonly end:                    string;
	readonly page:                   number;
	readonly perPage:                number;
}
