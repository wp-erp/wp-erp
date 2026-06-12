import { test, expect } from '@utils/test';
import { CustomFieldPage } from './customFieldPage';
import { data } from '@utils/testData';

/**
 * HRM — Custom Field Builder UI smoke (erp-pro module: custom_field_builder).
 *
 * GROUNDED in modules/hrm/custom-field-builder/{Module.php, views/view.php}:
 *   - Page slug is 'custom-field-builder' (parent 'erp') -> admin.php?page=custom-field-builder
 *     (NOT 'erp-custom-field-builder'). Admin.php's own admin_menu hook is
 *     COMMENTED OUT; the menu is added via the 'erp_submenu_page' action that
 *     AdminMenu fires (Module.php L138, L218-227).
 *   - People-type tab via &tab=employee|contact|company|customer|vendor. Tabs are
 *     gated by the active modules (view.php L15-29).
 *   - The screen is a jQuery + Vue hybrid: #people-field-parent is the Vue mount,
 *     #poststuff is the WP metabox root. Not an SPA route — use explicit waits.
 *   - add_submenu_page uses the 'manage_options' capability, so HR-manager AND a
 *     plain employee (neither has manage_options) are blocked. Access-control
 *     tests assert the boundary (no real mount / not-allowed notice), never an
 *     exact status, and always assert NOT the critical-error banner.
 *
 * Every test carries a tier tag (@pro), the @hrm module tag and a role tag.
 */

const CRITICAL_ERROR = CustomFieldPage.CRITICAL_ERROR;

// ─────────────────────────────────────────────────────────────────────────────
// Admin — the Custom Field Builder page mounts and exposes its controls.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Custom Field Builder UI (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('CFB-UI-01 page loads with the heading + metabox root, no fatal', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await cfb.goToBase();
        await expect(page.locator(cfb.sel.heading)).toContainText(cfb.headingText());
        await expect(page.locator(cfb.sel.poststuff)).toBeVisible();
    });

    test('CFB-UI-02 the people-type tab bar renders', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await cfb.goToBase();
        await expect(page.locator(cfb.sel.tabBar)).toBeVisible();
        // At least one people tab must render (modules are active in the @pro setup).
        await expect(page.locator(cfb.sel.tab).first()).toBeVisible();
        const count = await page.locator(cfb.sel.tab).count();
        expect(count, 'at least one people-type tab is present').toBeGreaterThan(0);
    });

    test('CFB-UI-03 a tab is marked active and its href points back to the page', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await cfb.goToBase();
        // The view marks the current tab with nav-tab-active.
        await expect(page.locator(cfb.sel.activeTab)).toHaveCount(1);
        // Every tab links back to ?page=custom-field-builder&tab=...
        const firstHref = await page.locator(cfb.sel.tab).first().getAttribute('href');
        expect(String(firstHref ?? '')).toContain('page=custom-field-builder');
        expect(String(firstHref ?? '')).toContain('tab=');
    });

    test('CFB-UI-04 the Employee tab mounts the Vue field collection container', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await cfb.goToTab('employee');
        await cfb.waitForVueMount();
        // The Employee tab should be the active one.
        await expect(page.locator(cfb.sel.activeTab)).toContainText(/Employee/i);
    });

    test('CFB-UI-05 the Add New Field button is present', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await cfb.goToTab('employee');
        await cfb.waitForVueMount();
        const addBtn = page.locator(cfb.sel.addNewField);
        await expect(addBtn).toBeVisible();
        await expect(addBtn).toContainText(/Add New Field/i);
    });

    test('CFB-UI-06 the Save Fields postbox + Save Changes button are present', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await cfb.goToTab('employee');
        await expect(page.locator(cfb.sel.savePostbox)).toBeVisible();
        const saveBtn = page.locator(cfb.sel.saveFields);
        await expect(saveBtn).toBeVisible();
        await expect(saveBtn).toContainText(/Save Changes/i);
    });

    test('CFB-UI-07 the single-field Vue template is printed into the page', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await cfb.goToTab('employee');
        // The <script type="text/template" id="single-field-template"> ships the row UI.
        await expect(page.locator(cfb.sel.singleFieldTemplate)).toBeAttached();
    });

    test('CFB-UI-08 the success notice element exists (hidden until a save)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await cfb.goToTab('employee');
        // #notice-save-success is in the DOM; it is shown by JS only after a save.
        await expect(page.locator(cfb.sel.successNotice)).toBeAttached();
    });

    test('CFB-UI-09 clicking Add New Field adds a field row to the collection', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await cfb.goToTab('employee');
        await cfb.waitForVueMount();
        const before = await page.locator(cfb.sel.singleField).count();
        await page.locator(cfb.sel.addNewField).click();
        // Vue appends a <single-field> row; the count should grow (give Vue a beat).
        await expect
            .poll(async () => page.locator(cfb.sel.singleField).count(), { timeout: 15_000 })
            .toBeGreaterThan(before);
        // Adding a row must never surface a PHP fatal.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    test('CFB-UI-10 every confirmed people-type tab loads without a fatal', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        // Visit each tab the active-module gate may expose. A tab that is not active
        // simply renders the default page; none may fatal or lose the metabox root.
        for (const people of ['employee', 'contact', 'company', 'customer', 'vendor']) {
            await cfb.goToTab(people);
            await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
            await expect(page.locator(cfb.sel.poststuff)).toBeVisible();
            await expect(page.locator(cfb.sel.heading)).toContainText(cfb.headingText());
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — the page is gated by 'manage_options'. Neither the HR manager
// nor a plain employee has that cap, so neither reaches the builder.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Custom Field Builder access control (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    test('CFB-UI-11 HR manager (no manage_options) is blocked from the builder', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await page.goto(cfb.urls.employee);
        // Never a fatal.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The Vue field mount must NOT render for a user without manage_options.
        await expect(page.locator(cfb.sel.fieldParent)).toHaveCount(0);
        const body = await page.locator('body').innerText();
        const blocked = /not allowed|do not have (?:sufficient )?permission|cheating/i.test(body);
        expect(blocked, 'manager lands on a not-allowed boundary, not the builder').toBe(true);
    });
});

test.describe('Custom Field Builder access control (pro, employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('CFB-UI-12 employee (no manage_options) is blocked from the builder', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const cfb = new CustomFieldPage(page);
        await page.goto(cfb.urls.employee);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The Add New Field control and the Vue mount must be absent.
        await expect(page.locator(cfb.sel.addNewField)).toHaveCount(0);
        await expect(page.locator(cfb.sel.fieldParent)).toHaveCount(0);
        const body = await page.locator('body').innerText();
        expect(/not allowed|do not have (?:sufficient )?permission|cheating/i.test(body)).toBe(true);
    });
});
