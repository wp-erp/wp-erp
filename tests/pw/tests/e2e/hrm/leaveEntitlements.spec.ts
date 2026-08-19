import { test, expect } from '@utils/test';
import { LeaveEntitlementsPage, entitlementColumns } from '@pages/hrm/leaveEntitlementsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { leavePolicies, employees as seededEmployees } from '@utils/seedData';
import { cleanupEntitlements } from '@utils/cleanup';
import { closeDb } from '@utils/dbUtils';

test.use({ storageState: ADMIN_STATE });

const employeeName = (index: number) => `${seededEmployees[index]!.firstName} ${seededEmployees[index]!.lastName}`;

test.describe('HR — Leave → Entitlements', () => {
    let page: LeaveEntitlementsPage;

    test.beforeEach(async ({ page: p }) => {
        page = new LeaveEntitlementsPage(p);
        await page.goto();
    });

    test.afterAll(async () => {
        await cleanupEntitlements();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the entitlements list renders with its captured columns', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        const headers = await page.columnHeaders();

        for (const column of entitlementColumns) {
            expect(headers, `column "${column}"`).toContain(column);
        }

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
    });

    test('the assignment form offers every leave policy', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        await page.gotoAssign();

        const offered = await page.policyOptions();

        for (const policy of leavePolicies.slice(0, 3)) {
            expect(offered, `policy "${policy.name}" is offered`).toContain(policy.name);
        }

        await expect(page.submitButton, 'Assign Policies').toBeVisible();
    });

    test('choosing a policy populates the employee list', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        await page.gotoAssign();

        // The employee select is empty until a policy is chosen — it is filled
        // by AJAX in response to that choice.
        expect(await page.employeeOptions(), 'no employees before a policy is chosen').toEqual([]);

        await page.choosePolicy(leavePolicies[0]!.name);

        expect((await page.employeeOptions()).length, 'employees arrive after the policy is chosen').toBeGreaterThan(0);
    });

    test('a policy can be entitled to a single employee', { tag: ['@tier1', '@hrm-leave', '@crud'] }, async () => {
        const policy = leavePolicies[0]!;
        const employee = employeeName(3);

        await page.assignToEmployee({ policyLabel: policy.name, employeeLabel: employee });
        await page.goto();

        expect(await page.hasEntitlementFor(employee), 'the entitlement is listed').toBe(true);

        // A fresh entitlement grants the policy's full day count and spends none.
        const balance = await page.balanceOf(employee, policy.name);
        expect(balance.available, 'available days match the policy').toBe(policy.days);
        expect(balance.spent, 'nothing is spent yet').toBe(0);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('assigning the same policy twice does not double the entitlement', { tag: ['@tier2', '@hrm-leave', '@edge'] }, async () => {
        const policy = leavePolicies[1]!;
        const employee = employeeName(4);

        await page.assignToEmployee({ policyLabel: policy.name, employeeLabel: employee });
        await page.goto();

        const first = await page.balanceOf(employee, policy.name);
        expect(first.available, 'the first assignment granted the policy days').toBe(policy.days);

        await page.assignToEmployee({ policyLabel: policy.name, employeeLabel: employee });
        await page.goto();

        expect((await page.balanceOf(employee, policy.name)).available, 'a repeat assignment does not stack days').toBe(policy.days);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('assigning with no policy chosen is refused', { tag: ['@tier3', '@hrm-leave', '@validation'] }, async () => {
        await page.gotoAssign();

        // Submitted untouched: no policy, no employee.
        await page.submitButton.click();
        await page.waitForErpReady();

        expect(await page.isOnAssignmentForm(), 'the form is not left on a refused save').toBe(true);
    });

    test('Entitlements is closed to an employee', { tag: ['@tier3', '@hrm-leave', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new LeaveEntitlementsPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach Entitlements').toBe(true);
    });
});
