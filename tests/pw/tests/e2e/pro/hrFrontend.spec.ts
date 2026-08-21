import { test, expect } from '@utils/test';
import { HrFrontendPage, LIVE_SLUG, HR_FRONTEND_SETTINGS_ROUTE } from '@pages/pro/hrFrontendPage';
import { ADMIN_STATE, EMPLOYEE_STATE, CRM_MANAGER_STATE } from '@utils/authStates';
import { closeDb } from '@utils/dbUtils';

test.use({ storageState: ADMIN_STATE });

/**
 * HR Frontend (@pro) — the employee dashboard served on the public site.
 *
 * Nothing here writes a setting. Saving the dashboard settings even once
 * relocates the dashboard for the whole site and, because the update handler
 * only flushes rewrites when it thinks the slug CHANGED, can leave the rewrite
 * rules and the stored slug disagreeing until something else flushes them. That
 * is ERP-159, and reproducing it is a deliberate one-off rather than something
 * a suite should do on every run.
 */
test.describe('HR Frontend @pro', () => {
    let page: HrFrontendPage;

    test.beforeEach(async ({ page: p }) => {
        page = new HrFrontendPage(p);
        page.watchServerErrors();
    });

    test.afterAll(async () => {
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the dashboard is served to an administrator', { tag: ['@tier1', '@pro', '@hr-frontend', '@smoke'] }, async () => {
        const result = await page.openDashboard(LIVE_SLUG);

        expect(result.status, `/${LIVE_SLUG}/ is served`).toBe(200);
        expect(await page.showsDashboard(), 'and the dashboard shell renders').toBe(true);
    });

    test('an ERP employee can reach their own dashboard', { tag: ['@tier1', '@pro', '@hr-frontend', '@flow'] }, async ({ browser }) => {
        // The whole point of the module: self-service for staff who have no
        // business in wp-admin.
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const asEmployee = new HrFrontendPage(await context.newPage());

        const result = await asEmployee.openDashboard(LIVE_SLUG);

        expect(result.status, 'the employee is served the dashboard').toBe(200);
        expect(result.url, 'and is not bounced into wp-admin').not.toContain('/wp-admin');

        await context.close();
    });

    // ---- Tier 2 ----------------------------------------------------------

    test.fail(
        'the slug the settings report is the slug that serves the dashboard',
        { tag: ['@tier2', '@pro', '@hr-frontend', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-159. The same unset option has two different
            // defaults: `get_erp_dashboard_slug()` falls back to `wp-erp` and
            // registers the rewrite, while `DashboardSettings::get_settings()`
            // falls back to `wp-erp-dashboard` and is what the settings screen
            // shows. On a site that has never saved the setting, an
            // administrator is told an address that 404s.
            const { status, settings } = await page.readSettings();

            expect(status, 'precondition: the settings route answers').toBe(200);
            expect(settings, 'precondition: it returns a settings object').not.toBeNull();

            const reported = settings!.hr_frontend_slug;
            const result = await page.openDashboard(reported);

            expect(result.status, `the slug the settings report ("${reported}") serves the dashboard`).toBe(200);
        }
    );

    // ---- Tier 3 ----------------------------------------------------------

    test('an anonymous visitor is sent to log in', { tag: ['@tier3', '@pro', '@hr-frontend', '@authz'] }, async ({ browser }) => {
        // `storageState: undefined` is load bearing — a bare newContext() in a
        // file that declares an admin state comes up AS the admin.
        const context = await browser.newContext({ storageState: undefined });
        const p = await context.newPage();
        const anonymous = new HrFrontendPage(p);

        await anonymous.openDashboard(LIVE_SLUG);

        expect(p.url(), 'the visitor lands on the login screen').toContain('wp-login.php');

        await context.close();
    });

    test('a logged-in non-employee without HR rights is sent away', { tag: ['@tier3', '@pro', '@hr-frontend', '@authz'] }, async ({ browser }) => {
        // A CRM manager holds no ERP employee record and neither `erp_hr_manager`
        // nor `manage_options`, so the dashboard is not theirs to see.
        const context = await browser.newContext({ storageState: CRM_MANAGER_STATE });
        const p = await context.newPage();
        const asCrmManager = new HrFrontendPage(p);

        await asCrmManager.openDashboard(LIVE_SLUG);

        expect(p.url(), 'they are redirected into wp-admin instead').toContain('/wp-admin');

        await context.close();
    });

    test('the dashboard settings are closed to a non-administrator', { tag: ['@tier3', '@pro', '@hr-frontend', '@authz'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const p = await context.newPage();
        await p.goto('http://localhost:8888/wp-admin/profile.php', { waitUntil: 'domcontentloaded' });

        const response = await p.evaluate(async (route) => {
            const w = window as unknown as { wpApiSettings?: { nonce?: string } };
            const r = await fetch(route, { headers: { 'X-WP-Nonce': w.wpApiSettings?.nonce ?? '' } });
            return { status: r.status, body: (await r.text()).slice(0, 160) };
        }, HR_FRONTEND_SETTINGS_ROUTE);

        expect(response.status, 'an employee is refused the settings route').toBe(403);

        await context.close();
    });
});
