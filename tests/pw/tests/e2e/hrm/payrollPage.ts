import { type Page } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';

/**
 * Feature-isolated page object for the WP ERP Pro HRM **Payroll** module.
 *
 * There is NO /erp/v1 REST surface for payroll (verified: the module registers
 * only wp_ajax_erp_payroll_* hooks in AjaxHandler.php; no rest controller). All
 * server data flows through wp-admin/admin-ajax.php, so this POM is UI smoke +
 * DB-truth oriented. The create path (erp_payroll_create_pay_calendar) is guarded
 * by the page-printed `payroll_nonce`, which the REST ApiUtils helper cannot
 * drive — calendars are therefore seeded/asserted directly via dbUtils.dbQuery.
 *
 * Routing (WPERP_VERSION >= 1.4.0, AdminMenu::load_new_menu): payroll is a HR
 * sub-section. The dispatcher (Module.php:343) keys off `sub-section`:
 *   dashboard (default), calendar (Pay Calendar list),
 *   calendar&subpage=add-cal-form (Add/Edit form), payrun (Pay Run List),
 *   bulk-pay-item-edit, reports. Links are built by erp_payroll_get_admin_link()
 *   (functions-payroll.php:900) → add_query_arg(['page'=>'erp-hr','section'=>'payroll'])
 *   then ['sub-section'=>$submenu].
 *
 * The payroll menu capability is `erp_hr_manager` (AdminMenu.php:79), so the HR
 * manager reaches every screen while a plain employee does not.
 *
 * Selectors are taken verbatim from the rendered views under
 * modules/hrm/payroll/includes/Admin/views/*.php. The calendar / add-form /
 * payrun wrappers ship with a `not-loaded` class and are hydrated by Vue/jQuery,
 * so their mounts are asserted with toBeAttached() (they exist in the DOM even
 * before/while the SPA boots); the plain-PHP dashboard mount is asserted visible.
 */

// Pro payroll tables (string literals — dbData.tables only carries free tables).
export const PAYROLL_TABLES = {
    payitem: 'wp_erp_hr_payroll_payitem',
    payCalendar: 'wp_erp_hr_payroll_pay_calendar',
    payCalendarEmployee: 'wp_erp_hr_payroll_pay_calendar_employee',
    calendarTypeSettings: 'wp_erp_hr_payroll_calendar_type_settings',
    payrunDetail: 'wp_erp_hr_payroll_payrun_detail',
} as const;

export class PayrollPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URLs (admin.php?page=erp-hr&section=payroll routed by &sub-section=) ────
    readonly urls = {
        dashboard: toPath('wp-admin/admin.php?page=erp-hr&section=payroll'),
        dashboardExplicit: toPath('wp-admin/admin.php?page=erp-hr&section=payroll&sub-section=dashboard'),
        calendar: toPath('wp-admin/admin.php?page=erp-hr&section=payroll&sub-section=calendar'),
        addCalendar: toPath('wp-admin/admin.php?page=erp-hr&section=payroll&sub-section=calendar&subpage=add-cal-form'),
        payrun: toPath('wp-admin/admin.php?page=erp-hr&section=payroll&sub-section=payrun'),
        reports: toPath('wp-admin/admin.php?page=erp-hr&section=payroll&sub-section=reports'),
        bulkPayItem: toPath('wp-admin/admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit'),
        // Settings is a direct link (AdminMenu.php:132) to the ERP settings SPA.
        settings: toPath('wp-admin/admin.php?page=erp-settings#/erp-hr/payroll'),
    } as const;

    // ── Selectors grouped by screen (real ids/classes from the views) ──────────
    readonly dashboard = {
        // payrun-overview.php:1
        root: '#payrun-overview-wrapper',
        heading: '#payrun-overview-wrapper > h2',
        // overview KPI cards (payrun-overview.php:70,83,96) + optional checklist/expense card.
        badge: '.badge-wrap',
        // setup checklist (payrun-overview.php:10) — only shown until all steps are done.
        checklist: '.checklist',
    } as const;

    readonly calendar = {
        // pay-calendar.php:1
        root: '#pay-calendar-wrapper',
        heading: '#pay-calendar-wrapper > h1',
        addNewBtn: "#pay-calendar-wrapper a.button.button-primary",
    } as const;

    readonly addForm = {
        // pay-calendar-creation-form.php:8
        root: '#pay-calendar-add-edit-wrapper',
        heading: '#pay-calendar-add-edit-wrapper > h1',
        nameInput: "input[v-model='cal_name']",
        typeSelect: "select[v-model='cal_type']",
        addEmployeeBtn: 'button.open_modal',
        empDept: '#emp_dept',
        empDesig: '#emp_desig',
        empName: '#emp_name',
    } as const;

    readonly payrun = {
        // payrun.php:1
        root: '#payrun-wrapper',
        heading: '#payrun-wrapper > h1',
        // hidden form inputs that dispatch the WP_List_Table (payrun.php:6-8)
        hiddenPage: "#payrun-wrapper input[name='page'][value='erp-hr']",
        hiddenSection: "#payrun-wrapper input[name='section'][value='payroll']",
        hiddenSubSection: "#payrun-wrapper input[name='sub-section'][value='payrun']",
        listTable: '#payrun-wrapper table.wp-list-table',
    } as const;

    // ERP top-nav (erp_add_menu / erp_add_submenu render the left menu + sub-nav).
    readonly nav = {
        // The HR app left menu lives in #erp-menu (.erp-nav). The active page heading
        // and sub-nav links are addressed by text.
        leftMenu: '#erp-menu, .erp-nav',
        payrollLink: "a:has-text('Payroll')",
        payCalendarLink: "a:has-text('Pay Calendar')",
        payRunListLink: "a:has-text('Pay Run List')",
    } as const;

    // ── Navigation helpers ─────────────────────────────────────────────────────
    async goToDashboard(): Promise<void> {
        await this.page.goto(this.urls.dashboard);
    }

    async goToCalendar(): Promise<void> {
        await this.page.goto(this.urls.calendar);
    }

    async goToAddCalendar(): Promise<void> {
        await this.page.goto(this.urls.addCalendar);
    }

    async goToPayrun(): Promise<void> {
        await this.page.goto(this.urls.payrun);
    }

    // ── DB helpers (no REST in payroll) ────────────────────────────────────────

    /** True if a given table exists in the current schema. */
    static async tableExists(table: string): Promise<boolean> {
        const rows = await dbUtils.dbQuery<Record<string, number>>(
            `SELECT COUNT(*) AS c FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?`,
            [table],
        );
        return Number(Object.values(rows[0] ?? { c: 0 })[0]) === 1;
    }

    /** Lower-cased column names of a table. */
    static async columnsOf(table: string): Promise<string[]> {
        const cols = await dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT COLUMN_NAME FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ?`,
            [table],
        );
        return cols.map((c) =>
            String((c as Record<string, unknown>).COLUMN_NAME ?? (c as Record<string, unknown>).column_name).toLowerCase(),
        );
    }

    /** Count seeded payitems of a given type (Allowance|Deduction|Tax|Non-Taxable Payments). */
    static async payitemCountByType(type: string): Promise<number> {
        const rows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${PAYROLL_TABLES.payitem} WHERE type = ?`,
            [type],
        );
        return Number(rows[0]?.c ?? 0);
    }

    /** Total seeded payitems. */
    static async payitemTotal(): Promise<number> {
        const rows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${PAYROLL_TABLES.payitem}`,
        );
        return Number(rows[0]?.c ?? 0);
    }

    /**
     * Insert a pay-calendar row (one type allowed per calendar — uniqueness is
     * enforced in the ajax handler, not the schema, so the DB will accept any
     * type; we suffix the name for run-uniqueness). Mirrors the columns the
     * create_pay_calendar ajax writes (AjaxHandler.php:463-473). Returns the id.
     */
    static async insertPayCalendar(name: string, type = 'monthly'): Promise<number | undefined> {
        const result = await dbUtils.dbQuery<{ insertId: number }>(
            `INSERT INTO ${PAYROLL_TABLES.payCalendar} (pay_calendar_name, pay_calendar_type) VALUES (?, ?)`,
            [name, type],
        );
        let id = (result as unknown as { insertId?: number }).insertId;
        if (!id) {
            const found = await dbUtils.dbQuery<{ id: number }>(
                `SELECT id FROM ${PAYROLL_TABLES.payCalendar} WHERE pay_calendar_name = ? ORDER BY id DESC LIMIT 1`,
                [name],
            );
            id = found[0]?.id;
        }
        return id;
    }

    /** Insert the type-settings child row alongside a calendar (AjaxHandler.php:493-508). */
    static async insertCalendarTypeSettings(calendarId: number): Promise<void> {
        await dbUtils.dbQuery(
            `INSERT INTO ${PAYROLL_TABLES.calendarTypeSettings}
                (pay_calendar_id, pay_day, custom_month_day, pay_day_mode)
             VALUES (?, 0, 0, 0)`,
            [calendarId],
        );
    }

    /** Map an employee onto a calendar (AjaxHandler.php:513-521). */
    static async insertCalendarEmployee(calendarId: number, empId: number): Promise<void> {
        await dbUtils.dbQuery(
            `INSERT INTO ${PAYROLL_TABLES.payCalendarEmployee} (pay_calendar_id, empid) VALUES (?, ?)`,
            [calendarId, empId],
        );
    }

    static async getPayCalendarsByName(name: string): Promise<Record<string, unknown>[]> {
        return dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT * FROM ${PAYROLL_TABLES.payCalendar} WHERE pay_calendar_name = ?`,
            [name],
        );
    }

    /** Remove a calendar and its child rows (the cleanup path remove_calendar uses). */
    static async deletePayCalendar(id: number): Promise<void> {
        await dbUtils.dbQuery(`DELETE FROM ${PAYROLL_TABLES.payCalendar} WHERE id = ?`, [id]);
        await dbUtils.dbQuery(`DELETE FROM ${PAYROLL_TABLES.payCalendarEmployee} WHERE pay_calendar_id = ?`, [id]);
        await dbUtils.dbQuery(`DELETE FROM ${PAYROLL_TABLES.calendarTypeSettings} WHERE pay_calendar_id = ?`, [id]);
    }
}
