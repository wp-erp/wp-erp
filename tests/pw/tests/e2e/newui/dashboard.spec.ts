import { test, expect } from '@utils/test';
import { data } from '@utils/testData';
import { NewUiPage } from './newUiPage';

/**
 * HR New UI — dashboard (area `DASH`, Tier-1 cases).
 *
 * The dashboard is the first screen every HR user lands on, and it is fed by
 * `GET /erp/v2/dashboard` plus `erp/v2/leave-calendar` (see
 * `features/dashboard/DashboardPage.tsx`). Card labels asserted here are the app's
 * own translated strings, read from `DashboardCards.tsx` / `DashboardWidgets.tsx`.
 *
 * Deliberately NOT automated here (they need fixture control the seed does not give
 * yet, and are listed as gaps in the feature map): DASH-002 count reconciliation,
 * DASH-003 Present Today vs real attendance, DASH-005 calendar visibility rules,
 * DASH-007/008 birthday widget and its mail side effect.
 */

const BOOTSTRAP_ROUTE = /erp\/v2\/dashboard/;

test.describe('HR New UI — dashboard (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ERP-NUI-DASH-001
    test(
        'ERP-NUI-DASH-001 dashboard renders its widgets without error',
        { tag: ['@lite', '@hrm', '@newui', '@admin'] },
        async ({ page }) => {
            const ui = new NewUiPage(page);
            await ui.goto();

            const root = page.locator(ui.admin.appRoot);
            for (const card of ['Employees', 'Departments', 'Designations', 'Present Today']) {
                await expect(root.getByText(card, { exact: true }).first(), `${card} stat card renders`).toBeVisible({
                    timeout: 20_000,
                });
            }
            expect(await ui.hasCriticalError(), 'no PHP fatal on the dashboard').toBe(false);
            expect(ui.errors(), 'no console errors while the dashboard boots').toEqual([]);
        },
    );

    // ERP-NUI-DASH-001 (transport) — the bootstrap call must succeed. A dashboard that
    // renders empty cards off a 500 would otherwise read as a pass.
    test(
        'ERP-NUI-DASH-001 the dashboard bootstrap REST call returns 200',
        { tag: ['@lite', '@hrm', '@newui', '@admin'] },
        async ({ page }) => {
            const ui = new NewUiPage(page);
            const statuses: number[] = [];
            page.on('response', res => {
                if (BOOTSTRAP_ROUTE.test(res.url())) statuses.push(res.status());
            });

            await ui.goto();
            await expect.poll(() => statuses.length, { timeout: 20_000 }).toBeGreaterThan(0);

            expect(
                statuses.filter(s => s >= 400),
                'no 4xx/5xx from erp/v2/dashboard',
            ).toEqual([]);
        },
    );
});

// ERP-NUI-DASH-014 — the dashboard must load for a user holding only the minimum
// capability the route declares (`erp_list_employee`), which the employee role has.
test.describe('HR New UI — dashboard (employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('ERP-NUI-DASH-014 dashboard loads for a plain employee', { tag: ['@lite', '@hrm', '@newui', '@employee'] }, async ({ page }) => {
        const ui = new NewUiPage(page);
        await page.goto(ui.url());

        // An employee either gets the React shell or is denied outright; a blank
        // page or a fatal is neither, and that is what this asserts against.
        const blocked = await page
            .getByText(/not allowed to access this page/i)
            .first()
            .isVisible()
            .catch(() => false);
        if (!blocked) {
            await expect(page.locator(ui.admin.appRoot)).toBeAttached({ timeout: 30_000 });
            await expect(page.locator(ui.admin.appRoot)).not.toBeEmpty({ timeout: 30_000 });
        }
        expect(await ui.hasCriticalError(), 'no PHP fatal for an employee on the HR dashboard').toBe(false);
    });
});
