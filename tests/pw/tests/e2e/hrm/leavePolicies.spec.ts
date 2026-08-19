import { test, expect } from '@utils/test';
import { LeavePoliciesPage, policyColumns } from '@pages/hrm/leavePoliciesPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { leavePolicies as seededPolicies } from '@utils/seedData';
import { cleanupLeavePolicies } from '@utils/cleanup';
import { closeDb } from '@utils/dbUtils';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Leave → Policies', () => {
    let page: LeavePoliciesPage;

    test.beforeEach(async ({ page: p }) => {
        page = new LeavePoliciesPage(p);
        await page.goto();
    });

    test.afterAll(async () => {
        await cleanupLeavePolicies();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the policies list renders the seeded policies', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        expect(await page.totalItems(), 'policies are listed').toBeGreaterThanOrEqual(seededPolicies.length);

        for (const seeded of seededPolicies.slice(0, 3)) {
            expect(await page.hasPolicy(seeded.name), `"${seeded.name}" is listed`).toBe(true);
        }

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
    });

    test('the list renders every captured column', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        const headers = await page.columnHeaders();

        for (const column of policyColumns) {
            expect(headers, `column "${column}"`).toContain(column);
        }
    });

    test('each seeded policy shows the day count it was created with', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        for (const seeded of seededPolicies.slice(0, 3)) {
            expect(await page.daysOf(seeded.name), `"${seeded.name}" day count`).toBe(String(seeded.days));
        }
    });

    test('the create form renders its required fields and leave types', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        await page.gotoCreate();

        await expect(page.yearSelect, 'Year').toBeVisible();
        await expect(page.leaveTypeSelect, 'Leave Type').toBeVisible();
        await expect(page.employeeTypeSelect, 'Employee Type').toBeVisible();
        await expect(page.daysField, 'Days').toBeVisible();
        await expect(page.submitButton, 'Save').toBeVisible();

        // Every seeded leave type is offered as a policy target.
        const offered = await page.leaveTypeOptions();
        for (const seeded of seededPolicies.slice(0, 3)) {
            expect(offered, `leave type "${seeded.name}" is offered`).toContain(seeded.name);
        }
    });

    test('a policy can be created for a distinct employee type', { tag: ['@tier1', '@hrm-leave', '@crud'] }, async () => {
        // The seeded policies all target "All" employee types, so creating for
        // Part Time keeps the (year, leave type, employee type) combination
        // unique — the product refuses an exact duplicate.
        const before = await page.totalItems();

        await page.create({ leaveTypeLabel: seededPolicies[0]!.name, days: '5', employeeTypeValue: 'parttime' });
        await page.goto();

        expect(await page.totalItems(), 'the total grew by exactly one').toBe(before + 1);

        const partTime = await page.typeOf(seededPolicies[0]!.name);
        expect(partTime, 'a Part Time policy now exists').toBeTruthy();
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('the employee-type select offers every employment type', { tag: ['@tier2', '@hrm-leave', '@options'] }, async () => {
        await page.gotoCreate();

        const offered = await page.employeeTypeOptions();

        for (const label of ['All', 'Full Time', 'Part Time', 'On Contract', 'Temporary', 'Trainee']) {
            expect(offered, `employee type "${label}"`).toContain(label);
        }
    });

    test('an exact duplicate policy is refused', { tag: ['@tier2', '@hrm-leave', '@edge'] }, async () => {
        const start = await page.totalItems();

        // The first create MUST succeed — otherwise "the duplicate was refused"
        // would pass simply because neither attempt ever worked.
        await page.create({ leaveTypeLabel: seededPolicies[1]!.name, days: '4', employeeTypeValue: 'contract' });
        await page.goto();

        const afterFirst = await page.totalItems();
        expect(afterFirst, 'the first policy was created').toBe(start + 1);

        // Same year + leave type + employee type — an exact duplicate.
        await page.create({ leaveTypeLabel: seededPolicies[1]!.name, days: '4', employeeTypeValue: 'contract' });
        await page.goto();

        expect(await page.totalItems(), 'the duplicate did not create a second policy').toBe(afterFirst);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('a policy with no leave type is refused', { tag: ['@tier3', '@hrm-leave', '@validation'] }, async () => {
        const before = await page.totalItems();

        await page.gotoCreate();
        await page.daysField.fill('3');
        const alerted = await page.submitRaw();

        expect(alerted === '' || /leave type|required|select/i.test(alerted), `unexpected alert: "${alerted}"`).toBe(true);
        expect(await page.isOnCreateForm(), 'the form is not left on a refused save').toBe(true);

        await page.goto();
        expect(await page.totalItems(), 'no policy was created').toBe(before);
    });

    test('a policy with no day count is refused', { tag: ['@tier3', '@hrm-leave', '@validation'] }, async () => {
        const before = await page.totalItems();

        await page.gotoCreate();
        await page.leaveTypeSelect.selectOption({ label: seededPolicies[0]!.name });
        const alerted = await page.submitRaw();

        expect(alerted === '' || /day|required/i.test(alerted), `unexpected alert: "${alerted}"`).toBe(true);
        expect(await page.isOnCreateForm(), 'the form is not left on a refused save').toBe(true);

        await page.goto();
        expect(await page.totalItems(), 'no policy was created').toBe(before);
    });

    test('Policies is closed to an employee', { tag: ['@tier3', '@hrm-leave', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new LeavePoliciesPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach Leave Policies').toBe(true);
    });
});
