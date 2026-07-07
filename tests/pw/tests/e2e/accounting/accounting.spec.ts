import { test, expect } from '@utils/test';
import { AccountingPage } from './accountingPage';
import { data } from '@utils/testData';

/**
 * Accounting UI specs — smoke-level on purpose.
 *
 * The admin screen is a Vue SPA (`page=erp-accounting`) with hash routing and no
 * stable DOM ids exposed outside the JS bundle. Per the QA guide we assert that
 * each area mounts, the key heading/region is visible, and there is no PHP fatal
 * — and push transaction depth (double-entry, reconciliation) into the API spec.
 *
 * Tier tag @lite (these areas are all free), module tag @accounting, role tag per
 * describe. @pro specs cover toggleable add-ons (Inventory / Payment Gateway).
 */

test.describe('Accounting SPA — Account Manager', () => {
    test.use({ storageState: data.auth.accManagerFile });

    test('dashboard mounts without a critical error', { tag: ['@lite', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goToDashboard();

        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError(), 'no PHP/JS fatal on dashboard').toBe(false);
    });

    test('customers list loads', { tag: ['@lite', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goToCustomers();

        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        // The customers view renders an "Add New Customer" control once it boots.
        await expect(page.locator(acct.admin.appRoot)).toContainText(/Add New Customer/i, { timeout: 20_000 });
        expect(await acct.hasCriticalError()).toBe(false);
    });

    test('vendors list loads', { tag: ['@lite', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goToVendors();

        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        await expect(page.locator(acct.admin.appRoot)).toContainText(/Add New Vendor/i, { timeout: 20_000 });
        expect(await acct.hasCriticalError()).toBe(false);
    });

    test('sales (invoices) list loads', { tag: ['@lite', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goToSales();

        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError()).toBe(false);
    });

    test('products & services list loads', { tag: ['@lite', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goToProducts();

        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        // Products list view ("Products … Add New") renders its add control on boot.
        await expect(page.locator(acct.admin.appRoot)).toContainText(/Add New/i, { timeout: 20_000 });
        expect(await acct.hasCriticalError()).toBe(false);
    });

    test('reports area loads', { tag: ['@lite', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goToReports();

        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError()).toBe(false);
    });

    // Edge: an unknown hash route should still keep the SPA shell alive (it
    // typically falls back to dashboard / a not-found view rather than crashing).
    test('unknown hash route does not crash the SPA', { tag: ['@lite', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(`${acct.admin.dashboardUrl.replace('#/dashboard', '#/this-route-does-not-exist')}`);

        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError()).toBe(false);
    });
});

test.describe('Accounting admin access', () => {
    test.use({ storageState: data.auth.adminFile });

    test('admin can open the accounting SPA', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goToDashboard();

        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError()).toBe(false);
    });
});

// Negative: an employee (no accounting capability) must not get the SPA. WP shows
// the "Sorry, you are not allowed to access this page." notice instead.
test.describe('Accounting access control — Employee', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('employee is blocked from the accounting page', { tag: ['@lite', '@accounting', '@employee'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await page.goto(acct.admin.dashboardUrl);

        const blocked = page.getByText(/not allowed to access this page/i);
        await expect(blocked.first()).toBeVisible({ timeout: 20_000 });
    });
});

// Pro-only smoke: the Inventory sub-area only exists when the `inventory` module
// is active. When ERP_PRO is off this whole describe is filtered out by tag.
test.describe('Accounting PRO add-ons — Account Manager', () => {
    test.use({ storageState: data.auth.accManagerFile });

    test('inventory area mounts when pro inventory is active', { tag: ['@pro', '@accounting', '@manager'] }, async ({ page }) => {
        const acct = new AccountingPage(page);
        await acct.goto(acct.admin.productsUrl.replace('#/products/product-service', '#/products/inventory'));

        await expect(page.locator(acct.admin.appRoot)).toBeAttached();
        expect(await acct.hasCriticalError()).toBe(false);
    });
});
