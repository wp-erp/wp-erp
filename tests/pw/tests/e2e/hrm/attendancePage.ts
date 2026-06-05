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
        // Admin.php mounts the SPA here; admin.js new Vue({ el: '#vue-admin-app' }).
        vueApp: '#vue-admin-app',
        content: '#wpbody-content',
        // In-app controls that surface once the Shifts route renders.
        addControl: 'a:has-text("Add"), button:has-text("Add"), a:has-text("New Shift"), a:has-text("New")',
        listTable: 'table, .erp-list-table, ul.erp-attendance-shifts',
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
     * content wrapper is visible, and the real Vue mount node is attached.
     */
    async expectMountedNoFatal(): Promise<void> {
        await expect(this.page.locator('body')).not.toContainText(AttendancePage.CRITICAL_ERROR);
        await expect(this.page.locator(this.selectors.content)).toBeVisible({ timeout: 30_000 });
        // The Vue mount node is echoed server-side, so it must always be present
        // even before the bundle finishes booting.
        await expect(this.page.locator(this.selectors.vueApp)).toBeAttached({ timeout: 30_000 });
    }
}
