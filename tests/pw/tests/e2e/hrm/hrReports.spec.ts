import { test, expect } from '@utils/test';
import { HrReportsPage, hrReportTypes, headcountColumns, salaryHistoryColumns, attendanceByDateColumns, type HrReport } from '@pages/hrm/hrReportsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { employees as seededEmployees, leavePolicies, departments } from '@utils/seedData';
import { closeDb } from '@utils/dbUtils';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Reports', () => {
    let page: HrReportsPage;

    test.beforeEach(async ({ page: p }) => {
        page = new HrReportsPage(p);
    });

    test.afterAll(async () => {
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    for (const report of Object.keys(hrReportTypes) as HrReport[]) {
        test(`the ${report} report loads`, { tag: ['@tier1', '@hrm-reports', '@smoke'] }, async () => {
            await page.goto(report);

            expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
            await page.expectHeading('HR');
        });
    }

    for (const [report, columns] of [
        ['headcount', headcountColumns],
        ['salaryHistory', salaryHistoryColumns],
        ['attendanceByDate', attendanceByDateColumns],
    ] as [HrReport, readonly string[]][]) {
        test(`the ${report} report renders its captured columns`, { tag: ['@tier1', '@hrm-reports'] }, async () => {
            await page.goto(report);

            const headers = await page.columnHeaders();

            for (const column of columns) {
                expect(headers, `column "${column}"`).toContain(column);
            }
        });
    }

    // ---- Tier 2: the figures must reconcile with the seeded company --------

    test('headcount lists every seeded employee', { tag: ['@tier2', '@hrm-reports', '@flow'] }, async () => {
        await page.goto('headcount');

        const names = await page.firstColumn();

        for (const employee of seededEmployees) {
            expect(names, `${employee.firstName} ${employee.lastName} is counted`).toContain(`${employee.firstName} ${employee.lastName}`);
        }

        // The suite's own `erp_employee` actor has no department or hire date and
        // is legitimately absent, so the report holds exactly the seeded staff.
        expect(names, 'no one else is counted').toHaveLength(seededEmployees.length);
    });

    test('salary history reports each employee at their seeded pay rate', { tag: ['@tier2', '@hrm-reports', '@flow'] }, async () => {
        await page.goto('salaryHistory');

        // Spot-check across the salary range rather than all 24: the top, the
        // bottom and one in the middle of the seeded band.
        for (const index of [0, 1, 3, 7]) {
            const employee = seededEmployees[index]!;
            const row = await page.rowFor(`${employee.firstName} ${employee.lastName}`);

            expect(row, `${employee.firstName} ${employee.lastName} appears`).not.toBe('');
            expect(row, `their pay rate reads ${employee.payRate}`).toContain(employee.payRate);
            expect(row, 'and the seeded monthly pay type').toContain('monthly');
        }
    });

    test('the leaves report has a column for every leave policy', { tag: ['@tier2', '@hrm-reports'] }, async () => {
        await page.goto('leaves');

        const headers = await page.columnHeaders();

        for (const policy of leavePolicies) {
            expect(headers, `column for "${policy.name}"`).toContain(policy.name);
        }
    });

    test('the age profile breaks the company down by department', { tag: ['@tier2', '@hrm-reports'] }, async () => {
        await page.goto('ageProfile');

        const rows = await page.firstColumn();

        for (const department of departments) {
            expect(rows, `department "${department.title}"`).toContain(department.title);
        }
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('HR reports are closed to an employee', { tag: ['@tier3', '@hrm-reports', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new HrReportsPage(p);
            await asEmployee.goto('headcount');
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the headcount report').toBe(true);
    });
});
