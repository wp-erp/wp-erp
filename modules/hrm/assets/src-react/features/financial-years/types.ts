/**
 * Financial-year row shape returned by `GET /erp/v2/financial-years`.
 *
 * A financial year is a row in `erp_hr_financial_years` — the period leave
 * entitlements are scoped to. `id` is `null` for an unsaved row in the editor.
 */
export interface FinancialYear {
	readonly id:          number | null;
	readonly fy_name:     string;
	readonly start_date:  string;
	readonly end_date:    string;
	readonly description: string;
}
