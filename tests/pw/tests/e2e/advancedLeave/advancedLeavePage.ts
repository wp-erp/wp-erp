import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';

/**
 * Feature-isolated page object for the WP ERP Pro **Advanced Leave** module
 * (erp-pro/modules/pro/advanced-leave).
 *
 * The pro extras (segregation / accrual / carry-forward+encashment / half-day /
 * sandwich rule) are NOT wired to REST. They hook the LEGACY admin POST form and
 * the Leave settings tab:
 *   - Module.php:182        adds the "Enable Sandwich Rule" leave setting.
 *   - Segregation.php:29-32 renders the segregation table on the policy form
 *                           (gated by option erp_pro_seg_leave === 'yes') and
 *                           reads $_POST['segre'] on policy update.
 *   - Accrual/Forward/Halfday each gate their own policy-form-bottom fields on
 *     erp_pro_accrual_leave / erp_pro_carry_encash_leave / erp_pro_half_leave.
 *
 * Selectors are taken verbatim from the rendered views:
 *   - modules/hrm/views/leave/leave-policies.php   (the policy list)
 *   - modules/hrm/views/leave/new-policy.php        (the add/edit policy form)
 *   - advanced-leave/.../Segregation/form.php       (input.segre[name="segre[..]"])
 *   - advanced-leave/.../Accrual/form.php           (accrued-amount / accrued-max-days)
 *   - advanced-leave/.../Forward/form.php           (carryover-* / encashment-* / forward-default)
 *   - advanced-leave/.../Halfday/Halfday.php:54     (enable-halfday checkbox)
 *
 * Because the pro policy-form fields only render when their leave-setting option
 * is 'yes', every pro-field assertion in the spec is RESILIENT (presence checked
 * conditionally; the policy form itself must always mount with no PHP fatal).
 *
 * The pro DB tables are referenced as string literals (utils/dbData.ts ships
 * only free tables). The prefix is derived the same way dbData does so a
 * non-default DB_PREFIX still resolves.
 */

const PREFIX = process.env.DB_PREFIX ?? 'wp';

export const leaveTables = {
    policies: `${PREFIX}_erp_hr_leave_policies`,
    segregation: `${PREFIX}_erp_hr_leave_policies_segregation`,
} as const;

/** Pro leave SETTINGS option ids (Module/Segregation/Accrual/Forward/Halfday). */
export const proLeaveOptions = {
    sandwich: 'erp_pro_sandwich_leave',
    segregation: 'erp_pro_seg_leave',
    accrual: 'erp_pro_accrual_leave',
    carryEncash: 'erp_pro_carry_encash_leave',
    halfday: 'erp_pro_half_leave',
} as const;

export class AdvancedLeavePage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URLs (admin.php?page=erp-hr routed by &section=leave&sub-section=) ─────
    readonly urls = {
        policies: toPath('wp-admin/admin.php?page=erp-hr&section=leave&sub-section=policies'),
        // erp_hr_new_policy_url(): the add-policy form (functions-leave.php:2985).
        newPolicy: toPath('wp-admin/admin.php?page=erp-hr&section=leave&sub-section=policies&action=new'),
        entitlements: toPath('wp-admin/admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements'),
        // SPA settings tab that hosts the pro leave-setting checkboxes.
        leaveSettings: toPath('wp-admin/admin.php?page=erp-settings#/erp-hr/leave'),
    } as const;

    // ── Policy LIST page (leave-policies.php) ─────────────────────────────────
    readonly list = {
        root: 'div.wrap.erp-hr-leave-policy',
        addNewBtn: '#erp-leave-policy-new',
        viewTypesBtn: '#erp-leave-name-new',
        sectionInput: 'input[name="section"][value="leave"]',
        subSectionInput: 'input[name="sub-section"][value="policies"]',
        listTable: 'table.wp-list-table',
    } as const;

    // ── Add/Edit policy FORM (new-policy.php) ─────────────────────────────────
    readonly form = {
        root: 'form.leave-policy-form',
        fYear: 'select[name="f-year"]',
        leaveId: 'select[name="leave-id"]',
        employeeType: 'select[name="employee_type"]',
        days: 'input[name="days"]',
        applicableFrom: 'input[name="applicable-from"]',
        color: 'input.erp-color-picker[name="color"]',
        applyForNewUsers: 'input[name="apply-for-new-users"]',
        erpAction: 'input[name="erp-action"][value="hr-leave-policy-create"]',
        policyId: 'input[name="policy-id"]',
    } as const;

    // ── Pro policy-form-bottom fields (gated by their leave-setting option) ───
    readonly pro = {
        // Segregation table — 12 month inputs (Segregation/form.php).
        segregationJan: 'input.segre[name="segre[jan]"]',
        segregationDec: 'input.segre[name="segre[decem]"]', // note: December column is `decem`
        segregationInputs: 'input.segre',
        // Accrual (Accrual/form.php).
        accruedAmount: 'input[name="accrued-amount"]',
        accruedMaxDays: 'input[name="accrued-max-days"]',
        // Carry-forward / encashment (Forward/form.php).
        carryoverDays: 'input[name="carryover-days"]',
        carryoverUsesLimit: 'input[name="carryover-uses-limit"]',
        encashmentDays: 'input[name="encashment-days"]',
        encashmentBasedOn: 'select[name="encashment-based-on"]',
        forwardDefault: 'input[name="forward-default"]',
        // Half-day (Halfday.php:54-71).
        halfdayEnable: 'input[name="enable-halfday"]',
    } as const;

    // ── Navigation ────────────────────────────────────────────────────────────
    async goToPolicies(): Promise<void> {
        await this.page.goto(this.urls.policies);
        await expect(this.page.locator(this.list.root)).toBeVisible({ timeout: 30_000 });
    }

    async goToNewPolicyForm(): Promise<void> {
        await this.page.goto(this.urls.newPolicy);
        await expect(this.page.locator(this.form.root)).toBeVisible({ timeout: 30_000 });
    }

    // ── DB helpers (the real pro surface lives on the policy columns) ─────────

    /** SHOW TABLES LIKE — returns matched rows (length>=1 means it exists). */
    static async tableExists(table: string): Promise<unknown[]> {
        return dbUtils.dbQuery(`SHOW TABLES LIKE '${table}'`);
    }

    /** Column names of a table (lower-cased) for shape assertions. */
    static async columns(table: string): Promise<string[]> {
        const rows = await dbUtils.dbQuery<{ Field: string }>(`SHOW COLUMNS FROM ${table}`);
        return rows.map(r => String(r.Field).toLowerCase());
    }

    /**
     * Seed a policy row straight through the storage layer with the pro columns
     * populated (the same columns the legacy admin form filters write). Returns
     * the new id, or undefined if the insert id could not be resolved.
     */
    static async insertProPolicyRow(opts: {
        leaveId: number;
        days: number;
        carryoverDays: number;
        encashmentDays: number;
        accruedAmount: number;
        halfdayEnable: number;
        forwardDefault?: 'encashment' | 'carryover';
    }): Promise<number | undefined> {
        const result = await dbUtils.dbQuery<{ insertId?: number }>(
            `INSERT INTO ${leaveTables.policies}
                (leave_id, days, carryover_days, carryover_uses_limit, encashment_days,
                 encashment_based_on, forward_default, applicable_from_days, accrued_amount,
                 accrued_max_days, halfday_enable, created_at)
             VALUES (?, ?, ?, 0, ?, 'pay_rate', ?, 0, ?, 0, ?, ?)`,
            [
                opts.leaveId,
                opts.days,
                opts.carryoverDays,
                opts.encashmentDays,
                opts.forwardDefault ?? 'encashment',
                opts.accruedAmount,
                opts.halfdayEnable,
                Math.floor(Date.now() / 1000),
            ],
        );
        return (result as unknown as { insertId?: number }).insertId;
    }

    /** Read the pro columns of a policy row by id. */
    static async getPolicyRow(id: number): Promise<Record<string, unknown> | undefined> {
        const rows = await dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT * FROM ${leaveTables.policies} WHERE id = ? LIMIT 1`,
            [id],
        );
        return rows[0];
    }

    /** Seed one segregation row for a policy (the prepare_update_data UPDATE target). */
    static async insertSegregationRow(leavePolicyId: number, decem = 0): Promise<number | undefined> {
        const result = await dbUtils.dbQuery<{ insertId?: number }>(
            `INSERT INTO ${leaveTables.segregation} (leave_policy_id, decem) VALUES (?, ?)`,
            [leavePolicyId, decem],
        );
        return (result as unknown as { insertId?: number }).insertId;
    }

    /** Read a segregation row by its owning policy id. */
    static async getSegregationRow(leavePolicyId: number): Promise<Record<string, unknown> | undefined> {
        const rows = await dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT * FROM ${leaveTables.segregation} WHERE leave_policy_id = ? LIMIT 1`,
            [leavePolicyId],
        );
        return rows[0];
    }

    /** Cleanup: drop any policy/segregation rows seeded directly by this spec. */
    static async deletePolicyRow(id: number): Promise<void> {
        await dbUtils.dbQuery(`DELETE FROM ${leaveTables.segregation} WHERE leave_policy_id = ?`, [id]);
        await dbUtils.dbQuery(`DELETE FROM ${leaveTables.policies} WHERE id = ?`, [id]);
    }
}
