import { test, expect } from '@utils/test';
import { data } from '@utils/testData';
import { NewUiPage, ROUTES, ID_ROUTES, REDIRECTS } from './newUiPage';

/**
 * HR New UI — app shell, routing and the engine switch.
 *
 * Automates the Tier-1 cases of area `SHELL` from the Lead-QA manual suite
 * (test-cases/2026-07-30-erp-new-ui-full.md §Area 1). Case IDs are carried in the
 * test titles so a failure maps straight back to the manual case, and the coverage
 * feature map keys off those same titles.
 *
 * Every assertion is grounded in the app source, not the prose: the route table and
 * its titles come from `app/router.tsx`, the mount node from `AdminMenu.php`, the
 * not-found copy from the router's own `NotFound` component.
 */

test.describe('HR New UI — shell & routing (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ERP-NUI-SHELL-001
    test(
        'ERP-NUI-SHELL-001 HR admin mounts as a React SPA and renders the dashboard',
        { tag: ['@lite', '@hrm', '@newui', '@admin'] },
        async ({ page }) => {
            const ui = new NewUiPage(page);
            await ui.goto();

            await expect(page.locator(ui.admin.appRoot)).toBeVisible();
            expect(await ui.hasCriticalError(), 'no PHP fatal on the HR dashboard').toBe(false);
            expect(ui.errors(), 'no console errors on first paint').toEqual([]);
        },
    );

    // ERP-NUI-SHELL-002 — every static route in app/router.tsx resolves to its own
    // screen. The TopBar title is the discriminator, so a route that silently falls
    // back to the dashboard fails here instead of passing as "it rendered something".
    for (const route of ROUTES) {
        test(
            `ERP-NUI-SHELL-002 route ${route.path} resolves to its own screen`,
            { tag: ['@lite', '@hrm', '@newui', '@admin'] },
            async ({ page }) => {
                const ui = new NewUiPage(page);
                await ui.goto(route.path);

                await ui.expectTitle(route.title);
                expect(await ui.hasCriticalError(), `no PHP fatal on ${route.path}`).toBe(false);
            },
        );
    }

    // ERP-NUI-SHELL-002 (parameterised routes) — resolved against the seeded employee.
    // The profile screens take over the TopBar title slot with a "Back to People"
    // header, so they are proven by their tab strip; /edit still renders a title.
    for (const route of ID_ROUTES) {
        const sample = route.path(':id');
        test(
            `ERP-NUI-SHELL-002 route ${sample} resolves for a real employee`,
            { tag: ['@lite', '@hrm', '@newui', '@admin'] },
            async ({ page }) => {
                const employeeId = process.env.EMPLOYEE_ID;
                test.skip(!employeeId, 'needs a seeded employee (EMPLOYEE_ID from _env.setup)');

                const ui = new NewUiPage(page);
                await ui.goto(route.path(String(employeeId)));

                await ui.expectMarkers(route.expect, route.absent);
                expect(await ui.hasCriticalError(), `no PHP fatal on ${sample}`).toBe(false);
            },
        );
    }

    // ERP-NUI-SHELL-002 — /my-profile is the same profile chrome bound to the CURRENT
    // user, so identity is the discriminator that separates it from /employees/:id.
    test(
        'ERP-NUI-SHELL-002 route /my-profile resolves to the current user',
        { tag: ['@lite', '@hrm', '@newui', '@admin'] },
        async ({ page }) => {
            const ui = new NewUiPage(page);
            await ui.goto('/my-profile');

            await ui.expectProfileScreen();
            const adminEmail = process.env.ADMIN_EMAIL;
            if (adminEmail) {
                await expect(
                    page.locator(ui.admin.appRoot).getByText(adminEmail, { exact: false }).first(),
                    'my-profile shows the signed-in user, not another employee',
                ).toBeVisible({ timeout: 20_000 });
            }
            expect(await ui.hasCriticalError(), 'no PHP fatal on /my-profile').toBe(false);
        },
    );

    // ERP-NUI-SHELL-003 — the six report routes are distinct screens, not one screen
    // reached six ways. Asserted as six different titles from six different hashes.
    test(
        'ERP-NUI-SHELL-003 the six report routes are distinct screens',
        { tag: ['@lite', '@hrm', '@newui', '@admin'] },
        async ({ page }) => {
            const ui = new NewUiPage(page);
            const reports = ROUTES.filter(r => r.path.startsWith('/reports/'));
            expect(reports, 'the router still declares six report routes').toHaveLength(6);

            for (const report of reports) {
                await ui.goto(report.path);
                await ui.expectTitle(report.title);
            }
            expect(new Set(reports.map(r => r.title)).size, 'each report route has its own title').toBe(6);
        },
    );

    // ERP-NUI-SHELL-004 — the false-pass trap from the manual suite: under React the
    // legacy `&section=` query parameter is meaningless. The screen must stay the
    // React shell; it must NOT render the legacy Vue page for that section.
    for (const section of ['people', 'payroll', 'leave']) {
        test(
            `ERP-NUI-SHELL-004 &section=${section} is a no-op under React`,
            { tag: ['@lite', '@hrm', '@newui', '@admin'] },
            async ({ page }) => {
                const ui = new NewUiPage(page);
                await page.goto(`${ui.url()}&section=${section}`);

                await expect(page.locator(ui.admin.appRoot)).toBeAttached();
                await expect(page.locator(ui.admin.appRoot)).not.toBeEmpty({ timeout: 30_000 });
                expect(await page.locator(ui.admin.legacyRoot).count(), 'no legacy Vue engine markup is rendered alongside React').toBe(0);
            },
        );
    }

    // ERP-NUI-SHELL-006 — a hash route pasted into a fresh tab lands on that screen,
    // not on the dashboard. Uses a hard navigation, i.e. no client-side history.
    test(
        'ERP-NUI-SHELL-006 deep-linking a hash route lands on that screen',
        { tag: ['@lite', '@hrm', '@newui', '@admin'] },
        async ({ page }) => {
            const ui = new NewUiPage(page);
            await ui.goto('/leave/entitlements');

            await ui.expectTitle('Leave Entitlements');
            expect(new URL(page.url()).hash, 'the deep-linked hash survives the boot').toContain('/leave/entitlements');
        },
    );

    // ERP-NUI-SHELL-007 — Back/Forward move between routes client-side. The document
    // must not reload: a marker set on window survives a hash change, not a reload.
    test(
        'ERP-NUI-SHELL-007 Back and Forward move between routes without a full reload',
        { tag: ['@lite', '@hrm', '@newui', '@admin'] },
        async ({ page }) => {
            const ui = new NewUiPage(page);
            await ui.goto('/employees');
            await ui.expectTitle('Employees');

            await page.evaluate(() => {
                (window as unknown as { __pwNoReload?: boolean }).__pwNoReload = true;
            });

            await ui.navigateInApp('/departments');
            await ui.expectTitle('Departments');

            await page.goBack();
            await ui.expectTitle('Employees');
            await page.goForward();
            await ui.expectTitle('Departments');

            const survived = await page.evaluate(() => (window as unknown as { __pwNoReload?: boolean }).__pwNoReload === true);
            expect(survived, 'history navigation stayed client-side (no document reload)').toBe(true);
        },
    );

    // ERP-NUI-SHELL-009 — unknown hash shows the in-app not-found view. The router's
    // own NotFound component, not a blank shell and not a crash.
    test(
        'ERP-NUI-SHELL-009 an unknown hash route shows the in-app Page not found',
        { tag: ['@lite', '@hrm', '@newui', '@admin'] },
        async ({ page }) => {
            const ui = new NewUiPage(page);
            await ui.goto('/this-route-does-not-exist');

            await expect(page.locator(ui.admin.appRoot).getByText(ui.admin.notFoundHeading).first()).toBeVisible({
                timeout: 20_000,
            });
            expect(await ui.hasCriticalError(), 'an unknown route is not a fatal').toBe(false);
        },
    );

    // ERP-NUI-SHELL-010 — a malformed :id must not reach the API raw. The screen may
    // show an error or an empty state; what it must not do is crash or issue a request
    // carrying the junk id.
    test(
        'ERP-NUI-SHELL-010 a malformed employee id is not passed to the API raw',
        { tag: ['@lite', '@hrm', '@newui', '@admin'] },
        async ({ page }) => {
            const ui = new NewUiPage(page);
            const junk = 'not-an-id';
            const apiCalls: string[] = [];
            page.on('request', req => {
                if (req.url().includes('erp/v') && req.url().includes(junk)) apiCalls.push(req.url());
            });

            await ui.goto(`/employees/${junk}`);

            expect(await ui.hasCriticalError(), 'a malformed id is not a fatal').toBe(false);
            expect(apiCalls, 'no REST call carries the malformed id').toEqual([]);
        },
    );

    // Redirect routes declared in router.tsx.
    for (const redirect of REDIRECTS) {
        test(
            `ERP-NUI-SHELL-002 ${redirect.from} redirects to ${redirect.to}`,
            { tag: ['@lite', '@hrm', '@newui', '@admin'] },
            async ({ page }) => {
                const ui = new NewUiPage(page);
                await ui.goto(redirect.from);

                await expect.poll(() => new URL(page.url()).hash.replace(/^#/, ''), { timeout: 20_000 }).toBe(redirect.to);
            },
        );
    }
});
