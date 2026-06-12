import { test, expect } from '@utils/test';
import { HrmPage } from './hrmPage';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';

/**
 * HRM UI — CRUD happy paths + page-load smokes (HRM-HP-09, 10, 12, 18, 20, 31,
 * 36, 43) and the announcements page smoke.
 *
 * Reuses the existing HrmPage page object for the established department /
 * designation / employee flows (real admin-ajax ids verified in the views). The
 * employee modal mixes select2 widgets, so its UI coverage is a resilient modal
 * smoke; depth lives in the REST specs. Names come from the testData factories,
 * never hard-coded ids.
 */

// ── Admin role ───────────────────────────────────────────────────────────────
test.describe('HRM UI — admin CRUD', () => {
    test.use({ storageState: data.auth.adminFile });

    test('HRM-HP-10 employees list loads without a fatal', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.goToEmployees();
        expect(await hrm.hasCriticalError(), 'employees list must not fatal').toBe(false);
        await expect(page.locator(hrm.admin.employee.addNew), '"Add New" employee trigger visible').toBeVisible();
    });

    test('HRM-HP-09 add-employee modal renders required fields (smoke)', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        const emp = data.hrm.employee();

        await hrm.openAddEmployeeModal();
        await hrm.fillEmployeeBasics({
            first_name: emp.first_name,
            last_name: emp.last_name,
            email: emp.email,
            hiring_date: emp.hiring_date,
        });

        await expect(page.locator(hrm.admin.employee.firstName)).toHaveValue(emp.first_name);
        await expect(page.locator(hrm.admin.employee.lastName)).toHaveValue(emp.last_name);
        await expect(page.locator(hrm.admin.employee.email)).toHaveValue(emp.email);
        await expect(page.locator(hrm.admin.employee.actionHidden), 'create action wired').toHaveCount(1);
    });

    test('HRM-HP-12 create a department via the UI modal', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        const dept = data.hrm.department();

        await hrm.createDepartment({ title: dept.title, description: dept.description });

        await hrm.goToDepartments();
        expect(await hrm.hasCriticalError()).toBe(false);
        await expect(
            page.locator(hrm.admin.department.listRow).filter({ hasText: dept.title }),
        ).toHaveCount(1);
    });

    test('HRM-HP-18 departments list loads with Add New', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.goToDepartments();
        expect(await hrm.hasCriticalError()).toBe(false);
        await expect(page.locator(hrm.admin.department.addNew), '"Add New" department trigger visible').toBeVisible();
    });

    test('HRM-HP-20 create a designation via the UI modal', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        const desig = data.hrm.designation();

        await hrm.createDesignation({ title: desig.title, description: desig.description });

        await hrm.goToDesignations();
        expect(await hrm.hasCriticalError()).toBe(false);
        // Verify via the DB — the admin list paginates once many designations exist.
        const rows = await dbUtils.dbQuery(`SELECT id FROM ${tables.hrDesignations} WHERE title = ?`, [desig.title]);
        expect(rows.length, 'designation persisted').toBeGreaterThanOrEqual(1);
    });

    test('HRM-HP-31 holidays admin page loads', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.goToHolidays();
        expect(await hrm.hasCriticalError(), 'holidays page must not fatal').toBe(false);
        // The leave/holidays screen is a WP list-table page; assert the admin shell.
        await expect(page.locator('#wpadminbar')).toBeVisible();
        await expect(page.locator('.wrap, #erp-page-wrap, #wp-erp').first()).toBeVisible();
    });

    test('HRM-HP-36 announcements admin page loads', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await page.goto(hrm.urls.announcements, { waitUntil: 'domcontentloaded' });
        expect(await hrm.hasCriticalError(), 'announcements page must not fatal').toBe(false);
        await expect(page.locator('#wpadminbar')).toBeVisible();
        await expect(page.locator('.wrap, #erp-page-wrap, #wp-erp').first()).toBeVisible();
    });

    test('HRM-HP-43 headcount report page loads', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.goToHeadcountReport();
        expect(await hrm.hasCriticalError(), 'report page must not fatal').toBe(false);
        await expect(page.locator('.wrap, #erp-page-wrap, #wp-erp').first()).toBeVisible();
    });
});

// ── HR manager role ──────────────────────────────────────────────────────────
test.describe('HRM UI — HR manager', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    test('HR manager reaches the employees list', { tag: ['@lite', '@hrm', '@manager'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.goToEmployees();
        expect(await hrm.hasCriticalError(), 'employees list must not fatal for HR manager').toBe(false);
        await expect(page.locator(hrm.admin.employee.addNew)).toBeVisible();
    });

    test('HR manager can create a department via UI', { tag: ['@lite', '@hrm', '@manager'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        const dept = data.hrm.department();

        await hrm.createDepartment({ title: dept.title, description: dept.description });

        await hrm.goToDepartments();
        await expect(
            page.locator(hrm.admin.department.listRow).filter({ hasText: dept.title }),
        ).toHaveCount(1);
    });
});
