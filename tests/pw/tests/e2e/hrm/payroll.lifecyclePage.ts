import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';

/**
 * Feature-isolated page object for the WP ERP Pro **Payroll pay-run lifecycle**.
 *
 * The whole pay-run lifecycle is admin-ajax driven (no /erp/v1 REST surface — the
 * payroll module registers only `wp_ajax_erp_payroll_*` hooks in
 * modules/hrm/payroll/includes/AjaxHandler.php). The protected writes verify a
 * PAGE-LOCALIZED nonce: `var wpErpPayroll.nonce = wp_create_nonce('payroll_nonce')`
 * (AdminMenu.php:318/495), checked via the Ajax trait's verify_nonce(), which reads
 * `$_REQUEST['_wpnonce']` against the action `payroll_nonce`
 * (wp-erp/includes/Framework/Traits/Ajax.php:17-21). The REST ApiUtils helper cannot
 * carry that page nonce, so this POM:
 *
 *   1) navigates the logged-in browser context to admin.php?page=erp-hr&section=payroll,
 *   2) reads window.wpErpPayroll.nonce + window.wpErpPayroll.ajaxurl from the page,
 *   3) replays the lifecycle through page.request (the same session cookies),
 *   4) asserts the computed DB effects via dbUtils.dbQuery against string-literal
 *      pro tables (dbData.tables only carries free tables).
 *
 * Lifecycle (each step grounded + live-verified against the wp-env site):
 *   get_available_employees → create_pay_calendar (+ assign employee)
 *   → get_employee_list_by_calid → add_payitem (Allowance)
 *   → start_variable_input (basic-pay row) → add_additional_allowance_deduction
 *   → get_payrun_list / get_pay_calendar.
 *
 * Nonce notes per handler (read from the source):
 *   - create_pay_calendar  : NO verify_nonce (only the logged-in cookie is needed).
 *   - get_employee_list_by_calid / get_pay_calendar / get_payrun_list : NO verify_nonce.
 *   - get_available_employees / add_payitem / start_variable_input /
 *     add_additional_allowance_deduction : REQUIRE wpErpPayroll.nonce (payroll_nonce).
 */

// Pro payroll tables (string literals — dbData.tables carries only free tables).
export const PAYROLL_LIFECYCLE_TABLES = {
    payitem: 'wp_erp_hr_payroll_payitem',
    payCalendar: 'wp_erp_hr_payroll_pay_calendar',
    payCalendarEmployee: 'wp_erp_hr_payroll_pay_calendar_employee',
    calendarTypeSettings: 'wp_erp_hr_payroll_calendar_type_settings',
    payrunDetail: 'wp_erp_hr_payroll_payrun_detail',
    additionalAllowanceDeduction: 'wp_erp_hr_payroll_additional_allowance_deduction',
    // Intentionally referenced by the handlers but ABSENT in this install
    // (PAYROLL-BUG-01): Installer.php:166 defines it, force-pro activate_modules()
    // did not create it, so start_variable_input's INSERT silently no-ops.
    payrun: 'wp_erp_hr_payroll_payrun',
} as const;

/** Shape of a wp_send_json_success/_error envelope. */
export interface AjaxEnvelope<T = unknown> {
    success: boolean;
    data: T;
}

export class PayrollLifecyclePage {
    readonly page: Page;

    /** Cached page-localized values, populated by bootstrap(). */
    private nonce = '';
    private ajaxurl = '';

    constructor(page: Page) {
        this.page = page;
    }

    readonly urls = {
        // Slug MUST be admin.php?page=erp-hr&section=payroll (WPERP >= 1.4.0). The
        // legacy erp-hr-payroll-* slug 403s ("not allowed") on this install.
        payroll: toPath('wp-admin/admin.php?page=erp-hr&section=payroll'),
    } as const;

    // ── Bootstrap: scrape the page-localized nonce + ajaxurl ────────────────────

    /**
     * Navigate to the payroll page and read window.wpErpPayroll.{nonce,ajaxurl}.
     * Returns true when the localized object was found (the current user can reach
     * the page and the script enqueued); false otherwise (caller asserts the
     * boundary). Confirms the page rendered without a PHP fatal.
     */
    async bootstrap(): Promise<boolean> {
        await this.page.goto(this.urls.payroll, { waitUntil: 'domcontentloaded' });
        await expect(this.page.locator('body')).not.toContainText(
            'There has been a critical error on this website',
        );

        const localized = await this.page.evaluate(() => {
            const w = window as unknown as {
                wpErpPayroll?: { nonce?: string; ajaxurl?: string };
            };
            return {
                nonce: w.wpErpPayroll?.nonce ?? '',
                ajaxurl: w.wpErpPayroll?.ajaxurl ?? '',
            };
        });

        this.nonce = String(localized.nonce ?? '');
        this.ajaxurl =
            String(localized.ajaxurl ?? '') || toPath('wp-admin/admin-ajax.php');
        return this.nonce !== '';
    }

    get pageNonce(): string {
        return this.nonce;
    }

    // ── admin-ajax transport (uses the page's session cookies) ──────────────────

    /**
     * POST an admin-ajax action as application/x-www-form-urlencoded through the
     * page's request context (so it carries the logged-in cookies). Arrays are
     * encoded with PHP-style bracket keys (e.g. empids[]=15, empidlist[0][id]=15).
     * `withNonce` appends &_wpnonce=<page nonce> for the protected handlers.
     */
    async ajax<T = unknown>(
        action: string,
        fields: Record<string, string | number> = {},
        withNonce = false,
    ): Promise<{ status: number; body: AjaxEnvelope<T> | null; raw: string }> {
        const params = new URLSearchParams();
        params.set('action', action);
        if (withNonce) params.set('_wpnonce', this.nonce);
        for (const [k, v] of Object.entries(fields)) {
            params.set(k, String(v));
        }

        const resp = await this.page.request.post(this.ajaxurl, {
            headers: { 'content-type': 'application/x-www-form-urlencoded' },
            data: params.toString(),
        });
        const raw = await resp.text();
        let body: AjaxEnvelope<T> | null = null;
        try {
            body = JSON.parse(raw) as AjaxEnvelope<T>;
        } catch {
            body = null;
        }
        return { status: resp.status(), body, raw };
    }

    /**
     * Like ajax() but sends a raw urlencoded string verbatim so callers can pass
     * repeated bracket keys the URLSearchParams set() API cannot model (the
     * empidlist[0][id] / empidlist[0][pay_basic] pair). Always carries the cookies.
     */
    async ajaxRaw<T = unknown>(
        rawBody: string,
    ): Promise<{ status: number; body: AjaxEnvelope<T> | null; raw: string }> {
        const resp = await this.page.request.post(this.ajaxurl, {
            headers: { 'content-type': 'application/x-www-form-urlencoded' },
            data: rawBody,
        });
        const raw = await resp.text();
        let body: AjaxEnvelope<T> | null = null;
        try {
            body = JSON.parse(raw) as AjaxEnvelope<T>;
        } catch {
            body = null;
        }
        return { status: resp.status(), body, raw };
    }

    // ── Lifecycle steps (confirmed actions/payloads) ────────────────────────────

    /** Step 1 — active employees not yet in any calendar (monthly pay_type). */
    async getAvailableEmployees(payType = 'monthly') {
        return this.ajax<Record<string, string>>(
            'erp_payroll_get_available_employees',
            { pay_type: payType },
            true,
        );
    }

    /**
     * Step 2 — create a pay calendar and assign employees in one call. No nonce.
     * empids MUST be non-empty (the handler builds an INSERT...VALUES loop and
     * SQL-errors on empty). cal_type is unique per calendar (handler-enforced).
     */
    async createPayCalendar(args: {
        calName: string;
        calType: string;
        empIds: number[];
        payDayMode?: number;
    }) {
        const parts = [
            'action=erp_payroll_create_pay_calendar',
            `cal_name=${encodeURIComponent(args.calName)}`,
            `cal_type=${encodeURIComponent(args.calType)}`,
            `paydaymode=${args.payDayMode ?? 1}`,
        ];
        for (const id of args.empIds) parts.push(`empids[]=${encodeURIComponent(String(id))}`);
        return this.ajaxRaw<string>(parts.join('&'));
    }

    /** Step 3 — calendar employee list (provides pay_basic). No nonce. */
    async getEmployeeListByCalId(args: {
        calId: number;
        prId?: number;
        fromDate: string;
        toDate: string;
    }) {
        return this.ajax<Array<Record<string, string>>>(
            'erp_payroll_get_employee_list_by_calid',
            {
                calid: args.calId,
                prid: args.prId ?? 0,
                from_date: args.fromDate,
                to_date: args.toDate,
            },
        );
    }

    /**
     * Step 4 — GENERATE the pay-run: inserts the computed basic-pay row into
     * payrun_detail (pay_item_id=-1, pay_item_amount=pay_basic, add_or_deduct=1).
     * payrunid=0 → INSERT branch. Requires the nonce. Single-employee variant.
     */
    async startVariableInput(args: {
        calId: number;
        payRunId?: number;
        paymentDate: string;
        fromDate: string;
        toDate: string;
        empId: number;
        payBasic: number;
    }) {
        const raw = [
            'action=erp_payroll_start_variable_input',
            `_wpnonce=${encodeURIComponent(this.nonce)}`,
            `calid=${args.calId}`,
            `payrunid=${args.payRunId ?? 0}`,
            `payment_date=${args.paymentDate}`,
            `from_date=${args.fromDate}`,
            `to_date=${args.toDate}`,
            `empidlist[0][id]=${args.empId}`,
            `empidlist[0][pay_basic]=${args.payBasic}`,
            'specify_pay_item=false',
        ].join('&');
        return this.ajaxRaw<{ prun: number; msg: string }>(raw);
    }

    /**
     * Step 5 — add a pay item (config). amounttype is auto-set by paytype
     * (Allowance→1, Deduction→0, Tax→2). Requires the nonce.
     */
    async addPayItem(args: { payType: string; payItem: string; amountType?: string }) {
        return this.ajax<string>(
            'erp_payroll_add_payitem',
            {
                paytype: args.payType,
                payitem: args.payItem,
                amounttype: args.amountType ?? '',
            },
            true,
        );
    }

    /**
     * Step 6 — add an additional allowance (additional_info=1) / deduction
     * (deduct_info=1) to the employee in the pay-run. Writes a row to BOTH
     * payrun_detail (allowance/deduction column = amount) and the mirror
     * additional_allowance_deduction table. Requires the nonce.
     */
    async addAdditionalAllowanceDeduction(args: {
        empId: number;
        payRunId?: number;
        calId: number;
        paymentDate: string;
        additionalInfo: 0 | 1;
        deductInfo: 0 | 1;
        note: string;
        payItem: number;
        payItemAmount: number;
    }) {
        return this.ajax<string>(
            'erp_payroll_add_additional_allowance_deduction',
            {
                eid: args.empId,
                payrunid: args.payRunId ?? 0,
                calid: args.calId,
                payment_date: args.paymentDate,
                additional_info: args.additionalInfo,
                deduct_info: args.deductInfo,
                note: args.note,
                pay_item: args.payItem,
                pay_item_amount: args.payItemAmount,
            },
            true,
        );
    }

    /** Step 7a — pay-run list (empty due to the missing payrun table). No nonce. */
    async getPayrunList() {
        return this.ajax<Array<Record<string, unknown>>>('erp_payroll_get_payrun_list');
    }

    /** Step 7b — pay-calendar list (carries cal_emp_number). No nonce. */
    async getPayCalendar() {
        return this.ajax<Array<Record<string, string>>>('erp_payroll_get_pay_calendar');
    }

    // ── DB assertion / cleanup helpers ──────────────────────────────────────────

    /** True if a table exists in the current schema. */
    static async tableExists(table: string): Promise<boolean> {
        const rows = await dbUtils.dbQuery<Record<string, number>>(
            `SELECT COUNT(*) AS c FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?`,
            [table],
        );
        return Number(Object.values(rows[0] ?? { c: 0 })[0]) === 1;
    }

    /** Resolve a pay-calendar id by exact name (most recent). */
    static async getCalendarIdByName(name: string): Promise<number | undefined> {
        const rows = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${PAYROLL_LIFECYCLE_TABLES.payCalendar}
             WHERE pay_calendar_name = ? ORDER BY id DESC LIMIT 1`,
            [name],
        );
        return rows[0]?.id;
    }

    /** Read every payrun_detail row for a calendar (the lifecycle's DB truth). */
    static async getPayrunDetailRows(calId: number): Promise<Array<Record<string, unknown>>> {
        return dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT payrun_id, pay_cal_id, empid, pay_item_id, pay_item_amount,
                    allowance, deduction, pay_item_add_or_deduct, note
             FROM ${PAYROLL_LIFECYCLE_TABLES.payrunDetail}
             WHERE pay_cal_id = ? ORDER BY id ASC`,
            [calId],
        );
    }

    /** Read the mirror additional_allowance_deduction rows for an employee+item. */
    static async getAllowanceDeductionRows(
        empId: number,
        payItemId: number,
    ): Promise<Array<Record<string, unknown>>> {
        return dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT pay_item_id, pay_item_amount, empid, payrun_id, pay_item_add_or_deduct, note
             FROM ${PAYROLL_LIFECYCLE_TABLES.additionalAllowanceDeduction}
             WHERE empid = ? AND pay_item_id = ? ORDER BY id DESC`,
            [empId, payItemId],
        );
    }

    /** Look up a payitem row by exact name. */
    static async getPayitemByName(name: string): Promise<Record<string, unknown> | undefined> {
        const rows = await dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT id, type, payitem, pay_item_add_or_deduct
             FROM ${PAYROLL_LIFECYCLE_TABLES.payitem}
             WHERE payitem = ? ORDER BY id DESC LIMIT 1`,
            [name],
        );
        return rows[0];
    }

    /** Count calendar-employee join rows for a calendar. */
    static async countCalendarEmployees(calId: number): Promise<number> {
        const rows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${PAYROLL_LIFECYCLE_TABLES.payCalendarEmployee}
             WHERE pay_calendar_id = ?`,
            [calId],
        );
        return Number(rows[0]?.c ?? 0);
    }

    /**
     * Pre-delete any existing calendar of a given type (the type is unique per
     * calendar — a second one of the same type returns success:false). Keeps the
     * lifecycle re-runnable. Cascades to the child + payrun_detail rows.
     */
    static async purgeCalendarsOfType(calType: string): Promise<void> {
        const rows = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${PAYROLL_LIFECYCLE_TABLES.payCalendar} WHERE pay_calendar_type = ?`,
            [calType],
        );
        for (const r of rows) {
            await PayrollLifecyclePage.deleteCalendarCascade(r.id);
        }
    }

    /** Remove a calendar plus all of its lifecycle child rows. */
    static async deleteCalendarCascade(calId: number): Promise<void> {
        await dbUtils.dbQuery(
            `DELETE FROM ${PAYROLL_LIFECYCLE_TABLES.payrunDetail} WHERE pay_cal_id = ?`,
            [calId],
        );
        await dbUtils.dbQuery(
            `DELETE FROM ${PAYROLL_LIFECYCLE_TABLES.payCalendarEmployee} WHERE pay_calendar_id = ?`,
            [calId],
        );
        await dbUtils.dbQuery(
            `DELETE FROM ${PAYROLL_LIFECYCLE_TABLES.calendarTypeSettings} WHERE pay_calendar_id = ?`,
            [calId],
        );
        await dbUtils.dbQuery(
            `DELETE FROM ${PAYROLL_LIFECYCLE_TABLES.payCalendar} WHERE id = ?`,
            [calId],
        );
    }

    /** Remove a payitem (and its mirror allowance/deduction rows) by id. */
    static async deletePayitemCascade(payItemId: number): Promise<void> {
        await dbUtils.dbQuery(
            `DELETE FROM ${PAYROLL_LIFECYCLE_TABLES.additionalAllowanceDeduction} WHERE pay_item_id = ?`,
            [payItemId],
        );
        await dbUtils.dbQuery(
            `DELETE FROM ${PAYROLL_LIFECYCLE_TABLES.payitem} WHERE id = ?`,
            [payItemId],
        );
    }
}
