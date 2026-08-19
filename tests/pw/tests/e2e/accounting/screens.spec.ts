import { test, expect } from '@utils/test';
import { AccountingPage, accountingRoutes, routeHeadings, accountClasses, reportNames, type AccountingRoute } from '@pages/accounting/accountingPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';

test.use({ storageState: ADMIN_STATE });

/**
 * Every accounting screen renders, and does so without a server error.
 *
 * The 5xx assertion is deliberate and new: ERP-147 (erp-pro#964) sat unnoticed
 * through 13 green payroll tests because a fatal inside an AJAX response is
 * invisible to a rendered-body check. Accounting is REST-backed and paints
 * almost everything from XHR, so it is exactly the module where that blind spot
 * would hide the most.
 */
test.describe('Accounting — screens', () => {
    let page: AccountingPage;

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        page.watchServerErrors();
    });

    // ---- Tier 1 ----------------------------------------------------------

    for (const route of Object.keys(accountingRoutes) as AccountingRoute[]) {
        test(`${route} renders`, { tag: ['@tier1', '@accounting', '@smoke'] }, async () => {
            await page.gotoRoute(route);

            expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
            expect(await page.showsHeadingFor(route), `the screen shows "${routeHeadings[route]}"`).toBe(true);
            expect(await page.contentLength(), 'the route painted real content').toBeGreaterThan(150);
            expect(page.serverErrorList(), 'no request during the load returns 5xx').toEqual([]);
        });
    }

    test('the chart of accounts groups every account class', { tag: ['@tier1', '@accounting'] }, async () => {
        await page.gotoRoute('chartOfAccounts');

        expect(await page.chartClasses(), 'all five classes are present').toEqual([...accountClasses]);
    });

    test('the reports screen offers its captured reports', { tag: ['@tier1', '@accounting'] }, async () => {
        await page.gotoRoute('reports');

        expect(await page.rendersAll([...reportNames]), 'every report is listed').toEqual([]);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('accounting is closed to an employee', { tag: ['@tier3', '@accounting', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new AccountingPage(p);
            await asEmployee.gotoRoute('dashboard');
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach Accounting').toBe(true);
    });

    test('accounting is closed to an HR manager', { tag: ['@tier3', '@accounting', '@authz'] }, async ({ browser }) => {
        // HR and Accounting are separate capability domains — an HR manager has
        // no accounting rights, so this is a real separation-of-duties check
        // rather than a repeat of the employee case.
        const denied = await withRole(browser, 'hrManager', async (p) => {
            const asHr = new AccountingPage(p);
            await asHr.gotoRoute('dashboard');
            return asHr.isAccessDenied();
        });

        expect(denied, 'an HR manager cannot reach Accounting').toBe(true);
    });
});
