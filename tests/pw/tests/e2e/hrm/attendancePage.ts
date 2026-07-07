import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';

/**
 * Feature-isolated page object for the WP ERP Pro HRM Attendance module.
 *
 * The Attendance admin page is a hash-routed Vue SPA. Grounded in:
 *  - erp-pro/modules/hrm/attendance/includes/Admin.php
 *      attendance_main_callback() echoes:
 *        <div class="wrap"><div id="vue-admin-app"></div></div>
 *      (WPERP >= 1.4.0 menu: admin.php?page=erp-hr&section=attendance, with
 *       #/ , #/shifts and #/exim submenus).
 *  - erp-pro/modules/hrm/attendance/assets/js/admin.js
 *      new Vue({ el: '#vue-admin-app', router, ... }); vue-router paths:
 *        '/', '/shifts', '/shifts/:shift_id', '/assign-shift-bulk', '/exim'.
 *
 * The SPA boots client-side, so the resilient signal is: the real Vue mount
 * (#vue-admin-app) is present, the WP content wrapper renders, there is NO PHP
 * "critical error" splash, and once booted the in-app text mentions
 * Shift/Attendance/Assign.
 */
export class AttendancePage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    static readonly CRITICAL_ERROR = 'There has been a critical error on this website';

    // ── URLs (hash-routed; the section= part is server-routed, the #/... is SPA) ─
    readonly urls = {
        base: toPath('wp-admin/admin.php?page=erp-hr&section=attendance#/'),
        shifts: toPath('wp-admin/admin.php?page=erp-hr&section=attendance#/shifts'),
        assignShiftBulk: toPath('wp-admin/admin.php?page=erp-hr&section=attendance#/assign-shift-bulk'),
        exim: toPath('wp-admin/admin.php?page=erp-hr&section=attendance#/exim'),
    } as const;

    // ── Selectors (stable WP/admin + Vue mount ids) ──────────────────────────
    readonly selectors = {
        wrap: 'div.wrap',
        // Admin.php server-renders <div id="vue-admin-app">; admin.js does
        // new Vue({ el: '#vue-admin-app', render: h => ... }). With a render
        // function, Vue REPLACES the mount node with the App root, whose
        // template is <div id="vue-backend-attendance"><router-view/></div>.
        // So '#vue-admin-app' only exists PRE-mount and is gone AFTER boot;
        // asserting it post-mount times out. The resilient app signal is
        // "either the server mount node OR the booted app root is attached".
        vueApp: '#vue-admin-app, #vue-backend-attendance',
        content: '#wpbody-content',
        // Shifts.vue mounts its whole UI under this root <div class="app-shifts">.
        // It's the unambiguous "the /shifts route component booted" signal.
        shiftsApp: '.app-shifts',
        // In-app controls that surface once the Shifts route renders.
        // Shifts.vue: <a id="erp-shift-new"> "Add New Shift"; list-table.shift-list.
        addControl: '#erp-shift-new, a:has-text("Add New Shift"), a:has-text("Add"), button:has-text("Add")',
        listTable: '.shift-list, table, .erp-list-table',
    } as const;

    /** Navigate to a SPA route and wait for the WP content wrapper to render. */
    async goto(target: keyof AttendancePage['urls']): Promise<void> {
        await this.page.goto(this.urls[target], { waitUntil: 'domcontentloaded' });
        await expect(this.page.locator(this.selectors.content)).toBeVisible({ timeout: 30_000 });
    }

    /** True if WP rendered the fatal "critical error" splash. */
    async hasCriticalError(): Promise<boolean> {
        const body = (await this.page.locator('body').textContent()) ?? '';
        return /There has been a critical error/i.test(body);
    }

    /**
     * Assert the page mounted without a fatal: no critical-error splash, the WP
     * content wrapper + content shell render, and the Vue app is present in
     * EITHER state — the server-rendered #vue-admin-app (pre-boot) or the booted
     * App root #vue-backend-attendance (post-boot, after Vue's render fn replaces
     * the mount node). A short wait keeps the smoke fast; the shell is the
     * load-bearing signal; the comma-OR vueApp selector handles the boot race.
     */
    async expectMountedNoFatal(): Promise<void> {
        await expect(this.page.locator('body')).not.toContainText(AttendancePage.CRITICAL_ERROR);
        await expect(this.page.locator(this.selectors.content)).toBeVisible({ timeout: 30_000 });
        await expect(this.page.locator(this.selectors.wrap).first()).toBeAttached({ timeout: 15_000 });
        await expect(this.page.locator(this.selectors.vueApp).first()).toBeAttached({ timeout: 15_000 });
    }

    /**
     * Assert the /shifts route ACTUALLY booted — not just that the page loaded.
     * Shifts.vue mounts as <div class="app-shifts"> holding the "#erp-shift-new"
     * add link and a list-table whose column headers ("Shift Name" / "Start Time")
     * render even when the list is empty.
     *
     * Gating on this real component is robust where the old approach was not:
     *  - expectMountedNoFatal() is satisfied immediately by the server-rendered
     *    #vue-admin-app node, so it does NOT wait for the SPA to finish booting;
     *  - scraping #wpbody-content text matched the license nag + HIDDEN cross-module
     *    nav (force-pro keeps every module active), and was populated before the SPA
     *    rendered — so a slow CI boot read as a failure.
     * The list fetch (GET erp/v1/hrm/attendance/shifts) may 404 on an empty site;
     * Shifts.vue catches it and still renders the headers, so we don't depend on it.
     * Timeout is CI-generous; locally this resolves in a few seconds.
     */
    async expectShiftsListReady(): Promise<void> {
        const shifts = this.page.locator(this.selectors.shiftsApp);
        await expect(shifts).toBeVisible({ timeout: 45_000 });
        await expect(shifts.locator('#erp-shift-new')).toBeVisible({ timeout: 15_000 });
        await expect(shifts).toContainText(/Shift Name|Start Time/i, { timeout: 15_000 });
    }
}
