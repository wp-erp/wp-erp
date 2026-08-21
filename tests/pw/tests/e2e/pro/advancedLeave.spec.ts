import { test, expect } from '@utils/test';
import { AdvancedLeavePage, advancedLeaveToggles, advancedLeaveAjax } from '@pages/pro/advancedLeavePage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { getOption, setOption, closeDb } from '@utils/dbUtils';

test.use({ storageState: ADMIN_STATE });

/**
 * Advanced Leave (@pro).
 *
 * Five optional behaviours, all OFF by default, each gating itself in its own
 * constructor on a `get_option()`. A feature that is off registers nothing —
 * no menu, no ajax handler, no form field — so "the screen 404s" and "the
 * feature is disabled" look identical from the outside. Every case here states
 * which state it is asserting.
 *
 * This module is also the counter-example to the two before it: unlike
 * Workflow's `fetch_workflow` and Reimbursement's routes, **every** handler here
 * checks a nonce AND `erp_leave_manage`. The tier-3 cases below are green on
 * purpose — they hold that behaviour in place.
 */
test.describe('Advanced Leave @pro', () => {
    let page: AdvancedLeavePage;

    /** Restores every toggle to its default so no other spec inherits a changed site. */
    async function resetToggles(): Promise<void> {
        for (const { option } of advancedLeaveToggles) await setOption(option, 'no');
        await setOption('enable_extra_leave', 'no');
    }

    test.beforeEach(async ({ page: p }) => {
        page = new AdvancedLeavePage(p);
        page.watchServerErrors();
    });

    test.afterAll(async () => {
        await resetToggles();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('leave settings offer every advanced-leave behaviour', { tag: ['@tier1', '@pro', '@advanced-leave', '@smoke'] }, async () => {
        await page.gotoLeaveSettings();

        const body = await page.bodyText();

        for (const { label } of advancedLeaveToggles) {
            expect(body, `"${label}" is offered`).toContain(label);
        }

        expect(page.serverErrorList(), 'and the tab loads without a server error').toEqual([]);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('a toggle is stored where the feature reads it', { tag: ['@tier2', '@pro', '@advanced-leave', '@crud'] }, async () => {
        // Each feature reads a BARE `get_option()`, not ERP's nested settings
        // array, so this asserts the two halves actually meet. They do — but the
        // pairing is easy to break and invisible from the screen when it is.
        await resetToggles();
        await page.gotoLeaveSettings();

        await page.setToggle('Enable Half-Day Request', true);

        expect(await getOption('erp_pro_half_leave'), 'the toggle writes the option the module reads').toBe('yes');

        await page.gotoLeaveSettings();
        expect(await page.isToggleOn('Enable Half-Day Request'), 'and the screen reflects it on reload').toBe(true);
    });

    test('a feature registers its screen only once enabled', { tag: ['@tier2', '@pro', '@advanced-leave', '@flow'] }, async () => {
        // Carry / Encash adds a "Forward Leaves" screen under HR → Leave, and
        // registers NOTHING while it is off. Asserting both directions is what
        // separates "correctly hidden" from "broken and missing".
        await resetToggles();
        await page.gotoLeave();

        expect(await page.leaveSubNav(), 'while off, the screen is not offered').not.toContain('Forward Leaves');

        await page.gotoLeaveSettings();
        await page.setToggle('Enable Carry / Encash', true);
        expect(await getOption('erp_pro_carry_encash_leave'), 'precondition: the toggle took').toBe('yes');

        await page.gotoLeave();

        expect(await page.leaveSubNav(), 'once on, the screen appears').toContain('Forward Leaves');
    });

    test('unpaid leave tracking registers its screen only once enabled', { tag: ['@tier2', '@pro', '@advanced-leave', '@flow'] }, async () => {
        // The Unpaid feature gates on `enable_extra_leave` — the CORE "Extra
        // Unpaid Leave" setting, not a toggle of its own. Worth pinning: the
        // name in the module and the label on screen do not match.
        await resetToggles();
        await page.gotoLeave();

        expect(await page.leaveSubNav(), 'while off, the screen is not offered').not.toContain('Unpaid Leaves');

        await setOption('enable_extra_leave', 'yes');
        await page.gotoLeave();

        expect(await page.leaveSubNav(), 'once on, the screen appears').toContain('Unpaid Leaves');
    });

    // ---- Tier 3 — the module's own guards --------------------------------

    test('every advanced-leave handler refuses a request with no nonce', { tag: ['@tier3', '@pro', '@advanced-leave', '@authz'] }, async () => {
        // Green on purpose. Two Pro modules audited just before this one shipped
        // handlers with no guard at all, so this holds the correct behaviour in
        // place rather than assuming it stays.
        await setOption('erp_pro_half_leave', 'yes');
        await setOption('erp_pro_multilevel_approval', 'yes');
        await setOption('enable_extra_leave', 'yes');

        await page.gotoLeave();

        for (const { action } of advancedLeaveAjax) {
            const response = await page.ajaxWithoutNonce(action);

            expect(
                /Nonce verification failed|sufficient permissions|-1|^0$/i.test(response.body.trim()),
                `${action} refuses a request with no nonce, answered: ${response.body.slice(0, 80)}`
            ).toBe(true);
        }
    });

    test('the unpaid leave screen is closed to an employee', { tag: ['@tier3', '@pro', '@advanced-leave', '@authz'] }, async ({ browser }) => {
        await setOption('enable_extra_leave', 'yes');

        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const asEmployee = new AdvancedLeavePage(await context.newPage());

        await asEmployee.open('/wp-admin/admin.php?page=erp-hr&section=leave&sub-section=unpaid-leave');

        const body = await asEmployee.bodyText();

        expect(
            (await asEmployee.isAccessDenied()) || !body.includes('Unpaid Leaves'),
            'an employee never reaches the unpaid leave register'
        ).toBe(true);

        await context.close();
    });
});
