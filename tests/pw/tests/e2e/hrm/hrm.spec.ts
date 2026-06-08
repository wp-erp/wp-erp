import { test, expect } from '@utils/test';
import { HrmPage } from './hrmPage';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';

/**
 * HRM UI specs (thin, role-scoped).
 *
 * Tags: tier (@lite|@pro) + module (@hrm) + role (@admin|@manager|@employee).
 * All FREE HR admin CRUD runs through admin-ajax with the real ids verified in
 * the views (see hrmPage.ts). Department/Designation have stable DOM ids and a
 * deterministic ajax submit, so those get real create flows. The employee modal
 * mixes select2 widgets, so its UI coverage is a resilient modal smoke; depth is
 * carried by the REST spec (hrm.api.spec.ts).
 *
 * Names are generated per-test via the testData factories — never hard-coded IDs.
 */

// ── Admin role ───────────────────────────────────────────────────────────────
test.describe('HRM — admin UI', () => {
    test.use({ storageState: data.auth.adminFile });

    test('HR dashboard loads without a critical error', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.goToDashboard();
        expect(await hrm.hasCriticalError(), 'HR dashboard must not fatal').toBe(false);
        await expect(page.locator('#wpadminbar')).toBeVisible();
        await expect(page.locator('.erp-nav-container, .wrap, #erp-page-wrap').first()).toBeVisible();
    });

    test('create a department (happy path)', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        const dept = data.hrm.department();

        await hrm.createDepartment({ title: dept.title, description: dept.description });

        // After the ajax create the modal closes and the row appears in the list.
        await hrm.goToDepartments();
        await expect(page.locator(hrm.admin.department.listRow).filter({ hasText: dept.title })).toHaveCount(1);
    });

    test('duplicate department name is rejected (edge/negative)', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        const dept = data.hrm.department();

        // First create succeeds.
        await hrm.createDepartment({ title: dept.title, description: dept.description });

        // Second create with the same title: WP ERP returns
        // "Multiple department with the same name is not allowed." and the list
        // must not gain a second identical row.
        await hrm.goToDepartments();
        await page.click(hrm.admin.department.addNew);
        await page.fill(hrm.admin.department.title, dept.title);
        await page.locator(hrm.admin.department.submit).first().click();

        await hrm.goToDepartments();
        await expect(
            page.locator(hrm.admin.department.listRow).filter({ hasText: dept.title }),
            'duplicate title should not create a second department',
        ).toHaveCount(1);
    });

    test('create a designation (happy path)', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        const desig = data.hrm.designation();

        await hrm.createDesignation({ title: desig.title, description: desig.description });

        // Verify via the DB — the admin list paginates once many designations exist,
        // so a first-page row check is unreliable. The create itself is the contract.
        const rows = await dbUtils.dbQuery(`SELECT id FROM ${tables.hrDesignations} WHERE title = ?`, [desig.title]);
        expect(rows.length, 'designation persisted').toBeGreaterThanOrEqual(1);
    });

    test('add-employee modal opens with required fields (smoke)', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        const emp = data.hrm.employee();

        await hrm.openAddEmployeeModal();
        await hrm.fillEmployeeBasics({
            first_name: emp.first_name,
            last_name: emp.last_name,
            email: emp.email,
            hiring_date: emp.hiring_date,
        });

        // Required ids present + filled (real view fields), action hidden input wired.
        await expect(page.locator(hrm.admin.employee.firstName)).toHaveValue(emp.first_name);
        await expect(page.locator(hrm.admin.employee.lastName)).toHaveValue(emp.last_name);
        await expect(page.locator(hrm.admin.employee.email)).toHaveValue(emp.email);
        await expect(page.locator(hrm.admin.employee.actionHidden)).toHaveCount(1);
    });

    test('submitting an employee with no required fields shows validation (negative)', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.openAddEmployeeModal();

        // Submit empty: native required ids should report invalid and the modal
        // must stay open (first_name still visible), no row added, no fatal.
        await page.locator(hrm.admin.employee.submit).first().click();
        await expect(page.locator(hrm.admin.employee.firstName)).toBeVisible();
        expect(await hrm.hasCriticalError()).toBe(false);
    });

    test('headcount report loads (smoke)', { tag: ['@lite', '@hrm', '@admin'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.goToHeadcountReport();
        expect(await hrm.hasCriticalError()).toBe(false);
        await expect(page.locator('.wrap, #erp-page-wrap').first()).toBeVisible();
    });
});

// ── HR manager role ──────────────────────────────────────────────────────────
test.describe('HRM — HR manager UI', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    test('HR manager can reach the employees list', { tag: ['@lite', '@hrm', '@manager'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.goToEmployees();
        expect(await hrm.hasCriticalError(), 'employees list must not fatal for HR manager').toBe(false);
        await expect(page.locator('#wpadminbar')).toBeVisible();
        await expect(page.locator(hrm.admin.employee.addNew)).toBeVisible();
    });

    test('HR manager can create a department', { tag: ['@lite', '@hrm', '@manager'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        const dept = data.hrm.department();

        await hrm.createDepartment({ title: dept.title, description: dept.description });

        await hrm.goToDepartments();
        await expect(page.locator(hrm.admin.department.listRow).filter({ hasText: dept.title })).toHaveCount(1);
    });
});

// ── Employee role (permission boundary) ──────────────────────────────────────
test.describe('HRM — employee UI', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('employee is blocked from the HR reports page (permission)', { tag: ['@lite', '@hrm', '@employee'] }, async ({ page }) => {
        const hrm = new HrmPage(page);
        await hrm.goToHeadcountReport();

        // Reports require erp_hr_manager; a plain employee must be denied access
        // (WP "you do not have sufficient permissions" / no report content),
        // and there must be no fatal error.
        expect(await hrm.hasCriticalError()).toBe(false);
        const body = (await page.locator('body').textContent()) ?? '';
        expect(
            /do not have sufficient permissions|not allowed|Sorry, you are not allowed/i.test(body),
            'employee must be denied the HR report',
        ).toBe(true);
    });
});
