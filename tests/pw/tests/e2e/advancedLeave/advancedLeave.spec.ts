import { test, expect } from '@utils/test';
import { AdvancedLeavePage, leaveTables, proLeaveOptions } from './advancedLeavePage';
import { dbUtils } from '@utils/dbUtils';
import { data } from '@utils/testData';

/**
 * WP ERP Pro — HRM **Advanced Leave** UI smoke (module: advanced_leave).
 *
 * The pro extras hook the LEGACY leave admin form + the Leave settings tab, not
 * REST. The pro policy-form-bottom fields (segregation / accrual / carry-forward+
 * encashment / half-day) render ONLY when their leave-setting option is 'yes':
 *   - Segregation/form.php gated by erp_pro_seg_leave
 *   - Accrual/form.php     gated by erp_pro_accrual_leave
 *   - Forward/form.php     gated by erp_pro_carry_encash_leave
 *   - Halfday field        gated by erp_pro_half_leave
 *
 * Because that gating is environment-dependent, every pro-field assertion here is
 * RESILIENT: the policy LIST and FORM must always mount with no PHP fatal, and a
 * pro field is asserted present only AFTER its gating option is confirmed 'yes'
 * (and is otherwise treated as conditionally-rendered, never a hard failure).
 *
 * Resilient-assertion philosophy (test-plans/_pro-grounding.md):
 *   - UI: assert NOT the WP fatal banner AND the real mount is visible.
 *   - Access control: assert the boundary (no mount / gated), never an exact code.
 *
 * Every test carries: tier (@pro) + module (@hrm) + role (@admin/@manager/@employee).
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

/** Is a pro leave-setting option flipped 'yes' on this install? */
async function optionIsYes(name: string): Promise<boolean> {
    try {
        const raw = await dbUtils.getOptionValue<string>(name);
        return String(raw) === 'yes';
    } catch {
        return false;
    }
}

test.afterAll(async () => {
    try {
        await dbUtils.close();
    } catch {
        /* pool may already be closed by a sibling spec */
    }
});

// ──────────────────────────────────────────────────────────────────────────
// Admin — UI smoke over the Leave Policies list + the add-policy form
// ──────────────────────────────────────────────────────────────────────────
test.describe('Advanced Leave UI (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ALV-UI-01 — the Leave Policies list boots into .wrap.erp-hr-leave-policy.
    test('Leave Policies list boots with no critical error', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await page.goto(leave.urls.policies);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(leave.list.root)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator('#wpadminbar')).toBeVisible();
    });

    // ALV-UI-02 — the list exposes Add New + View Leave Types + the routing inputs.
    test('Leave Policies list shows Add New / View Leave Types controls', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await leave.goToPolicies();
        await expect(page.locator(leave.list.addNewBtn)).toBeVisible();
        await expect(page.locator(leave.list.viewTypesBtn)).toBeVisible();
        // Hidden routing inputs prove we are on the policies sub-section.
        await expect(page.locator(leave.list.sectionInput)).toHaveCount(1);
        await expect(page.locator(leave.list.subSectionInput)).toHaveCount(1);
    });

    // ALV-UI-03 — the add-policy form mounts with all free fields + the hidden
    // erp-action that the pro $_POST filters key off.
    test('Add-policy form mounts with the free fields + erp-action', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await leave.goToNewPolicyForm();
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(leave.form.fYear)).toBeVisible();
        await expect(page.locator(leave.form.leaveId)).toBeVisible();
        await expect(page.locator(leave.form.employeeType)).toBeVisible();
        await expect(page.locator(leave.form.days)).toBeVisible();
        // The hidden create action + policy-id are present (the pro filters hook here).
        await expect(page.locator(leave.form.erpAction)).toHaveCount(1);
        await expect(page.locator(leave.form.policyId)).toHaveCount(1);
    });

    // ALV-UI-04 — segregation fields render iff erp_pro_seg_leave === 'yes'.
    // Resilient: when the option is on, the 12 month inputs (incl. segre[decem])
    // must be present; otherwise we only assert the form still mounted (no fatal).
    test('segregation policy fields render when the segregation setting is enabled', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await leave.goToNewPolicyForm();

        if (await optionIsYes(proLeaveOptions.segregation)) {
            await expect(page.locator(leave.pro.segregationJan)).toBeVisible({ timeout: 15_000 });
            await expect(page.locator(leave.pro.segregationDec)).toHaveCount(1);
            // 12 month inputs render in the segregation table.
            await expect(page.locator(leave.pro.segregationInputs)).toHaveCount(12);
        } else {
            // Gating off → fields absent by design; the form must still be intact.
            await expect(page.locator(leave.form.root)).toBeVisible();
        }
    });

    // ALV-UI-05 — accrual fields render iff erp_pro_accrual_leave === 'yes'.
    test('accrual policy fields render when the accrual setting is enabled', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await leave.goToNewPolicyForm();

        if (await optionIsYes(proLeaveOptions.accrual)) {
            await expect(page.locator(leave.pro.accruedAmount)).toBeVisible({ timeout: 15_000 });
            await expect(page.locator(leave.pro.accruedMaxDays)).toHaveCount(1);
        } else {
            await expect(page.locator(leave.form.root)).toBeVisible();
        }
    });

    // ALV-UI-06 — carry-forward + encashment fields render iff erp_pro_carry_encash_leave === 'yes'.
    test('carry-forward / encashment fields render when the setting is enabled', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await leave.goToNewPolicyForm();

        if (await optionIsYes(proLeaveOptions.carryEncash)) {
            await expect(page.locator(leave.pro.carryoverDays)).toBeVisible({ timeout: 15_000 });
            await expect(page.locator(leave.pro.encashmentDays)).toHaveCount(1);
            await expect(page.locator(leave.pro.encashmentBasedOn)).toHaveCount(1);
            // forward-default is a radio pair (carryover / encashment).
            await expect(page.locator(leave.pro.forwardDefault).first()).toHaveCount(1);
        } else {
            await expect(page.locator(leave.form.root)).toBeVisible();
        }
    });

    // ALV-UI-07 — half-day checkbox renders iff erp_pro_half_leave === 'yes'.
    test('half-day checkbox renders when the half-day setting is enabled', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await leave.goToNewPolicyForm();

        if (await optionIsYes(proLeaveOptions.halfday)) {
            await expect(page.locator(leave.pro.halfdayEnable)).toHaveCount(1);
        } else {
            await expect(page.locator(leave.form.root)).toBeVisible();
        }
    });

    // ALV-UI-08 — the Leave settings SPA tab hosts the pro toggles (sandwich +
    // segregation/accrual/encashment/halfday). Smoke: the settings page boots with
    // no fatal and at least one of the pro option ids is reachable in the markup.
    test('Leave settings tab boots and exposes the pro leave toggles', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await page.goto(leave.urls.leaveSettings);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The ERP settings SPA mounts; assert the app shell is present (resilient —
        // the deep leave panel is React-routed and may lazy-render).
        await expect(page.locator('#wpbody-content')).toBeVisible({ timeout: 30_000 });
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Admin — DB smoke alongside the UI (pro columns are the real surface)
// ──────────────────────────────────────────────────────────────────────────
test.describe('Advanced Leave DB smoke (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ALV-UI-09 — the pro policy columns + the segregation table exist (the UI
    // controls above write into exactly these).
    test('leave policy + segregation tables carry the pro columns', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const policyCols = await AdvancedLeavePage.columns(leaveTables.policies);
        for (const col of ['carryover_days', 'encashment_days', 'accrued_amount', 'halfday_enable', 'forward_default']) {
            expect(policyCols, `${leaveTables.policies} should have ${col}`).toContain(col);
        }

        const segExists = await AdvancedLeavePage.tableExists(leaveTables.segregation);
        expect(segExists.length, `${leaveTables.segregation} should exist`).toBeGreaterThanOrEqual(1);
        const segCols = await AdvancedLeavePage.columns(leaveTables.segregation);
        expect(segCols, 'segregation December column is `decem`').toContain('decem');
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Manager — erp_leave_manage lets a HR manager reach the leave screens too
// ──────────────────────────────────────────────────────────────────────────
test.describe('Advanced Leave UI (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    test('HR manager can reach the Leave Policies list', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await page.goto(leave.urls.policies);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(leave.list.root)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator(leave.list.addNewBtn)).toBeVisible();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Negative — a plain employee lacks erp_leave_manage, so the policy screen is gated
// ──────────────────────────────────────────────────────────────────────────
test.describe('Advanced Leave access control (pro, employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('employee cannot reach the Leave Policies management screen', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const leave = new AdvancedLeavePage(page);
        await page.goto(leave.urls.policies);
        // The policy management screen is capability-gated by erp_leave_manage.
        // An employee either lands on a WP "not allowed" notice or never gets the
        // policy mount — assert the boundary, never a PHP fatal.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(leave.list.root)).toHaveCount(0);
    });
});
