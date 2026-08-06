import type { ConsoleMessage } from '@playwright/test';
import type { Page } from '@utils/test';
import { expect } from '@utils/test';
import { toPath } from '@utils/helpers';

/**
 * Page object for the HR admin **React** engine (the New UI).
 *
 * The screen is a React Router v7 hash router mounted on a single shell node that
 * `AdminMenu.php` prints empty:
 *
 *     <div id="erp-hr-app" class="erp-hr-react-root"></div>
 *
 * The app ships **no `data-testid` anywhere** (verified: 0 occurrences under
 * `modules/hrm/assets/src-react`), so the stable oracle for "this route resolved to
 * its own screen" is the TopBar page title, which `TopBar/PageTitle.tsx` renders from
 * the matched route's `handle.title`. ROUTES below is transcribed from
 * `app/router.tsx` — the route table itself, not the test-case document — so a route
 * renamed in the app makes these specs fail rather than quietly pass.
 *
 * Engine note: `UiEngineResolver` decides React vs legacy Vue per user, and a site
 * that UPGRADED into the redesign keeps the legacy engine until someone opts in. The
 * suite therefore pins the engine site-wide (`erp_hr_ui_engine=react`) in the setup
 * chain rather than trusting the install-aware default — otherwise these specs would
 * pass or fail depending on how the site came into existence.
 */

/** Route path -> the title its `handle.title` renders in the TopBar. */
export const ROUTES: ReadonlyArray<{ path: string; title: string }> = [
    { path: '/employees', title: 'Employees' },
    { path: '/employees/new', title: 'Add New Employee' },
    { path: '/departments', title: 'Departments' },
    { path: '/designations', title: 'Designations' },
    { path: '/org-chart', title: 'Org Chart' },
    { path: '/announcements', title: 'Announcements' },
    { path: '/leave/requests', title: 'Leave Requests' },
    { path: '/requests', title: 'Requests' },
    { path: '/leave/types', title: 'Leave Types' },
    { path: '/leave/policies', title: 'Leave Policies' },
    { path: '/leave/entitlements', title: 'Leave Entitlements' },
    { path: '/leave/financial-years', title: 'Financial Years' },
    { path: '/leave/calendar', title: 'Leave Calendar' },
    { path: '/leave/holidays', title: 'Holidays' },
    { path: '/leave/unpaid', title: 'Unpaid Leaves' },
    { path: '/leave/forward', title: 'Forward Leaves' },
    { path: '/reports/age-profile', title: 'Age Profile' },
    { path: '/reports/gender-profile', title: 'Gender Profile' },
    { path: '/reports/headcount', title: 'Head Count' },
    { path: '/reports/salary-history', title: 'Salary History' },
    { path: '/reports/years-of-service', title: 'Years of Service' },
    { path: '/reports/leaves', title: 'Leaves' },
    { path: '/help', title: 'Help' },
];

/**
 * `:id`-bearing routes, resolved against a seeded employee at run time.
 *
 * NOTE the oracle differs from ROUTES above. The employee profile screens replace the
 * TopBar title slot with a contextual header ("Back to People" + the employee's name),
 * so `handle.title` is never rendered on them — measured, not assumed:
 *   /employees        -> "… Help | Employees | Export | Add new employee …"
 *   /employees/19     -> "… Help | Back to People | Madelyn Haley | Active …"
 * Asserting a title there would fail against a perfectly healthy screen. These routes
 * are proven by their own chrome instead.
 *
 * The four profile variants are genuinely different layouts — measured on the running
 * app, so each gets a marker that only it renders, and a variant quietly serving another
 * variant's layout FAILS rather than passing as "a profile screen appeared":
 *   :id            "Back to People" + the Overview…Documents tab strip
 *   :id/profile    same chrome, plus a Permission tab
 *   :id/profile-v2 "Employees / Employee Profile" breadcrumb + "Basic Information"
 *   :id/profile-v3 the same breadcrumb but stat tiles, no "Basic Information"
 *   :id/profile-v4 no breadcrumb; "Personal Information" + a reduced tab set
 */
export const ID_ROUTES: ReadonlyArray<{
    path: (id: string) => string;
    /** Text that must be visible on this route and identifies it uniquely. */
    expect: string[];
    /** Text that must NOT be visible — separates look-alike variants. */
    absent?: string[];
}> = [
    { path: id => `/employees/${id}`, expect: ['Back to People', 'Overview', 'Documents'] },
    { path: id => `/employees/${id}/profile`, expect: ['Back to People', 'Overview', 'Permission'] },
    { path: id => `/employees/${id}/profile-v2`, expect: ['Employee Profile', 'Basic Information'] },
    { path: id => `/employees/${id}/profile-v3`, expect: ['Employee Profile', 'Tenure'], absent: ['Basic Information'] },
    { path: id => `/employees/${id}/profile-v4`, expect: ['Personal Information'], absent: ['Employee Profile'] },
    { path: id => `/employees/${id}/edit`, expect: ['Edit Employee'] },
];

/** Tabs the my-profile screen renders — its screen-level discriminator. */
export const PROFILE_TABS = ['Overview', 'Job', 'Leave', 'Notes', 'Performance', 'Documents'] as const;

/** Redirect routes: hash in -> hash the router lands on. */
export const REDIRECTS: ReadonlyArray<{ from: string; to: string }> = [
    { from: '/dashboard', to: '/' },
    { from: '/reports', to: '/reports/age-profile' },
    { from: '/holidays', to: '/leave/holidays' },
];

/**
 * Console noise that is not the product's fault. Kept deliberately short — the point
 * of the console oracle is to catch ERP errors, and a broad filter would swallow them.
 */
const IGNORED_CONSOLE = [/favicon\.ico/i, /Download the React DevTools/i];

export class NewUiPage {
    readonly page: Page;
    private readonly consoleErrors: string[] = [];

    constructor(page: Page) {
        this.page = page;
        this.page.on('console', (msg: ConsoleMessage) => {
            if (msg.type() !== 'error') return;
            const text = msg.text();
            if (IGNORED_CONSOLE.some(re => re.test(text))) return;
            this.consoleErrors.push(text);
        });
    }

    readonly admin = {
        /** The shell node AdminMenu.php prints; React mounts into it. */
        appRoot: '#erp-hr-app.erp-hr-react-root',
        /** Legacy Vue engine markers — must be ABSENT on a React run. */
        legacyRoot: '#erp-hr, .erp-hr-page-wrap',
        notFoundHeading: /Page not found/i,
        base: 'wp-admin/admin.php?page=erp-hr',
    };

    /** Absolute URL for a hash route ('' = the index route). */
    url(hash = ''): string {
        return toPath(`${this.admin.base}${hash ? `#${hash}` : ''}`);
    }

    /** Hard-navigate to a hash route and wait for the React shell to mount. */
    async goto(hash = ''): Promise<void> {
        await this.page.goto(this.url(hash));
        await expect(this.page.locator(this.admin.appRoot)).toBeAttached({ timeout: 30_000 });
        // The shell node is printed empty by PHP, so "attached" proves nothing about
        // React. Wait for it to have actual rendered content.
        await expect(this.page.locator(this.admin.appRoot)).not.toBeEmpty({ timeout: 30_000 });
    }

    /**
     * Client-side navigation (no document load), so Back/Forward and route-transition
     * specs exercise the router rather than the server.
     */
    async navigateInApp(hash: string): Promise<void> {
        await this.page.evaluate(h => {
            window.location.hash = h;
        }, hash);
    }

    /** The route title the TopBar shows for the active route. */
    async expectTitle(title: string): Promise<void> {
        await expect(this.page.locator(this.admin.appRoot).getByText(title, { exact: true }).first()).toBeVisible({
            timeout: 20_000,
        });
    }

    /**
     * Assert the screen renders every marker in `expect` and none in `absent`, and that
     * it is not the router's not-found view. Used where the TopBar title slot is taken
     * over by a contextual header.
     */
    async expectMarkers(markers: readonly string[], absent: readonly string[] = []): Promise<void> {
        const root = this.page.locator(this.admin.appRoot);
        for (const marker of markers) {
            await expect(root.getByText(marker, { exact: true }).first(), `"${marker}" renders`).toBeVisible({
                timeout: 20_000,
            });
        }
        for (const marker of absent) {
            await expect(root.getByText(marker, { exact: true }), `"${marker}" does not render`).toHaveCount(0);
        }
        await expect(root.getByText(this.admin.notFoundHeading)).toHaveCount(0);
    }

    /** Assert the my-profile screen: its full tab strip, and not the not-found view. */
    async expectProfileScreen(): Promise<void> {
        await this.expectMarkers(PROFILE_TABS);
    }

    /** Console errors seen since this page object was constructed. */
    errors(): string[] {
        return [...this.consoleErrors];
    }

    /** True when the document carries a PHP/WP fatal. */
    async hasCriticalError(): Promise<boolean> {
        const body =
            (await this.page
                .locator('body')
                .innerText()
                .catch(() => '')) ?? '';
        return /critical error|Fatal error|There has been a critical error/i.test(body);
    }
}
