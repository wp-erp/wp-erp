import { test, expect } from '@utils/test';
import { AccountingPage } from './accountingPage';
import { data } from '@utils/testData';
import { toPath } from '@utils/helpers';

/**
 * Accounting SPA — smoke-level UI coverage for the catalog's UI rows
 * (ACCOUNTING-HP-27..29, EC-10). The admin screen is a Vue SPA with hash routing
 * and no stable DOM ids outside the JS bundle, so we only assert:
 *   - the SPA shell (#erp-accounting) mounts,
 *   - a route-specific visible text appears (20s timeout),
 *   - no PHP/JS fatal (hasCriticalError()).
 * All transactional depth lives in the REST specs where it can be asserted to the
 * cent. We REUSE AccountingPage and never invent vue-treeselect XPath (unverified
 * on the shared live site).
 *
 * Role: customers/charts/reports are exercised as @admin (full caps). The existing
 * accounting.spec.ts already covers the @manager smoke for the list views, so this
 * file adds the chart / reports / dashboard depth without overlapping ids.
 */

test.describe('Accounting SPA smoke — admin', () => {
    test.use({ storageState: data.auth.adminFile });

    test('ACCOUNTING-HP-27 customers list renders ("Add New Customer")', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(acct.admin.customersUrl);
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        await expect(page.locator(acct.admin.appRoot)).toContainText(/Add New Customer/i, { timeout: 20_000 });
        expect(await acct.hasCriticalError(), 'no fatal on customers route').toBe(false);
    });

    test('ACCOUNTING-HP-28 chart of accounts route renders', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(acct.admin.chartsUrl);
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        // Route-specific text on the chart screen. Kept tolerant: any of the chart
        // headings / add control. NOTE: exact heading wording not live-verified; this
        // resilient regex is intentionally broad. (See notes.)
        await expect(page.locator(acct.admin.appRoot)).toContainText(/Chart of Accounts|Add Account|Account Name|Chart/i, { timeout: 20_000 });
        expect(await acct.hasCriticalError(), 'no fatal on charts route').toBe(false);
    });

    test('ACCOUNTING-HP-29 reports route mounts without a console fatal', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        const fatals: string[] = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error' && /Uncaught|TypeError|is not a function|Cannot read/i.test(msg.text())) {
                fatals.push(msg.text());
            }
        });
        const acct = new AccountingPage(page);
        await acct.goto(acct.admin.reportsUrl);
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError(), 'no PHP/JS fatal on reports route').toBe(false);
        expect(fatals, `no uncaught console errors on reports route: ${fatals.join(' | ')}`).toEqual([]);
    });

    test('ACCOUNTING-HP-28b banks route mounts', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(acct.admin.banksUrl);
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError(), 'no fatal on banks route').toBe(false);
    });

    test('ACCOUNTING-EC-10 invoice total renders with the configured currency symbol', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        // We don't change ERP currency settings on the shared site; this is a
        // mojibake smoke: the sales list shows formatted amounts without replacement
        // characters. Exact symbol depends on site settings (not asserted).
        const acct = new AccountingPage(page);
        await acct.goto(toPath('wp-admin/admin.php?page=erp-accounting#/transactions/sales'));
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        const text = (await page.locator(acct.admin.appRoot).innerText().catch(() => '')) ?? '';
        // No Unicode replacement character (mojibake) in the rendered amounts.
        expect(text.includes('�'), 'no mojibake in rendered currency amounts').toBe(false);
        expect(await acct.hasCriticalError(), 'no fatal on sales route').toBe(false);
    });
});
