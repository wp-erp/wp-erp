import { test, expect } from '@utils/test';
import { WooCommercePage, WC_SYNC_OPTIONS } from '@pages/pro/wooCommercePage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { getOption, closeDb } from '@utils/dbUtils';

test.use({ storageState: ADMIN_STATE });

/**
 * WooCommerce integration (@pro).
 *
 * This covers the module's configuration surface and its default state. The
 * order-sync flow it exists for is verified MANUALLY, not here: completing a WC
 * order on this PHP-8 build fatals in the invoice PDF generation path — the
 * same `get_magic_quotes_runtime()` sink as ERP-150 / erp-pro#967 — so driving
 * an order to completion in-suite would throw a fatal on every run. What was
 * measured by hand is in COVERAGE and was added to #967 as a third surface.
 */
test.describe('WooCommerce integration @pro', () => {
    let page: WooCommercePage;

    test.beforeEach(async ({ page: p }) => {
        page = new WooCommercePage(p);
        page.watchServerErrors();
    });

    test.afterAll(async () => {
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the WooCommerce settings tab renders its sub-sections', { tag: ['@tier1', '@pro', '@woocommerce', '@smoke'] }, async () => {
        await page.gotoSettings('crm');

        const body = await page.sections();

        expect(body, 'the Synchronization sub-section is present').toMatch(/Synchronization/i);
        expect(body, 'the CRM sub-section is present').toMatch(/CRM/i);
        expect(body, 'the Accounting sub-section is present').toMatch(/Accounting/i);
        expect(page.serverErrorList(), 'and the tab loads without a server error').toEqual([]);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('order sync to CRM and Accounting is on by default', { tag: ['@tier2', '@pro', '@woocommerce'] }, async () => {
        // Both `erp_wc_is_crm_sync_active()` and `..._accounting_sync_active()`
        // read `erp_get_option(id, false, 'yes')`, so an untouched site syncs.
        // These options are unset until the settings are saved, and the default
        // is what governs behaviour — assert the default, not a written value.
        const crm = await getOption(WC_SYNC_OPTIONS.crm);
        const accounting = await getOption(WC_SYNC_OPTIONS.accounting);

        expect(crm === null || crm === 'yes', `CRM sync defaults on (stored: ${JSON.stringify(crm)})`).toBe(true);
        expect(
            accounting === null || accounting === 'yes',
            `Accounting sync defaults on (stored: ${JSON.stringify(accounting)})`
        ).toBe(true);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('the WooCommerce settings tab is closed to an employee', { tag: ['@tier3', '@pro', '@woocommerce', '@authz'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const p = await context.newPage();
        const asEmployee = new WooCommercePage(p);

        await asEmployee.gotoSettings('crm');

        const body = (await p.locator('body').innerText()).replace(/\s+/g, ' ');

        expect(
            /not have sufficient permissions|not allowed|Sorry, you are not allowed/i.test(body) ||
                !body.includes('Synchronization'),
            'an employee never reaches the WooCommerce settings'
        ).toBe(true);

        await context.close();
    });
});
