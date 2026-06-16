import { test, expect } from '@utils/test';
import { AccountingPage } from './accountingPage';
import { data } from '@utils/testData';
import { toPath } from '@utils/helpers';

/**
 * Accounting SPA — access-control / validation smoke (UI side of the catalog's
 * authorization rows). REST role-blocking (403/401) is asserted in
 * tests/api/accounting/accounting.negative.api.spec.ts; this file confirms the UI
 * surface: the account manager can reach the write screens, the admin can reach the
 * new-document routes, and an employee is bounced from the whole module.
 *
 * Smoke-level only: we assert the SPA shell mounts (or the WP "not allowed" notice
 * appears) and there is no PHP/JS fatal. We do NOT drive vue-treeselect forms
 * (no stable, live-verified ids).
 */

test.describe('Accounting SPA write routes — admin', () => {
    test.use({ storageState: data.auth.adminFile });

    test('admin can open the New Invoice route', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(acct.admin.invoiceNewUrl);
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError(), 'no fatal on new-invoice route').toBe(false);
    });

    test('admin can open the New Expense route', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(toPath('wp-admin/admin.php?page=erp-accounting#/expenses/new'));
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError(), 'no fatal on new-expense route').toBe(false);
    });

    test('admin can open the Journals route', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(acct.admin.journalsUrl);
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError(), 'no fatal on journals route').toBe(false);
    });
});

test.describe('Accounting SPA — account manager can reach write screens', () => {
    test.use({ storageState: data.auth.accManagerFile });

    test('manager can open the New Invoice route', { tag: ['@lite', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(acct.admin.invoiceNewUrl);
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError(), 'manager: no fatal on new-invoice route').toBe(false);
    });

    test('manager can open the charts (chart of accounts) route', { tag: ['@lite', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(acct.admin.chartsUrl);
        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError(), 'manager: no fatal on charts route').toBe(false);
    });
});

test.describe('Accounting SPA access control — employee is blocked', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('employee is bounced from the accounting dashboard', { tag: ['@lite', '@accounting', '@employee'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await page.goto(acct.admin.dashboardUrl);
        // WP renders "Sorry, you are not allowed to access this page." for a user
        // without any accounting capability. The SPA shell must NOT mount with data.
        const blocked = page.getByText(/not allowed to access this page/i);
        await expect(blocked.first()).toBeVisible({ timeout: 20_000 });
    });

    test('employee is bounced from the new-invoice route', { tag: ['@lite', '@accounting', '@employee'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await page.goto(acct.admin.invoiceNewUrl);
        const blocked = page.getByText(/not allowed to access this page/i);
        await expect(blocked.first()).toBeVisible({ timeout: 20_000 });
    });
});
