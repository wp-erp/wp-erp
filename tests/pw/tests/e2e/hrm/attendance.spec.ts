import { test, expect } from '@utils/test';
import { AttendancePage } from './attendancePage';
import { data } from '@utils/testData';

/**
 * HRM Attendance (PRO) UI smoke. The Attendance admin screen is a hash-routed
 * Vue SPA mounted by Admin.php into <div class="wrap"><div id="vue-admin-app">
 * (admin.js: new Vue({ el: '#vue-admin-app', router })). Routes: '/', '/shifts',
 * '/assign-shift-bulk', '/exim'.
 *
 * Resilient-assertion philosophy (see _pro-grounding.md): we assert the real Vue
 * mount + WP content wrapper are present and that there is NO PHP "critical
 * error" splash, rather than scraping brittle in-app markup. Once the SPA boots
 * we additionally check the in-app text mentions Shift/Attendance/Assign.
 *
 * Every test carries a tier tag (@pro), the @hrm module tag and a role tag.
 */

const CRITICAL_ERROR = AttendancePage.CRITICAL_ERROR;

// ─────────────────────────────────────────────────────────────────────────────
// Admin — the SPA mounts on each route with no fatal
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Attendance UI (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('ATT-UI-01 attendance base route mounts the Vue app, no fatal', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const att = new AttendancePage(page);
        await att.goto('base');
        await att.expectMountedNoFatal();
    });

    test('ATT-UI-02 shifts route renders without a critical error', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const att = new AttendancePage(page);
        await att.goto('shifts');
        await att.expectMountedNoFatal();
        // Once the Shifts route boots, the in-app heading/nav mentions Shift/Attendance.
        await expect(page.locator(att.selectors.content)).toContainText(/Shift|Attendance|Assign/i, { timeout: 30_000 });
    });

    test('ATT-UI-03 shifts screen exposes a list/add affordance', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const att = new AttendancePage(page);
        await att.goto('shifts');
        await att.expectMountedNoFatal();
        // The Shifts list renders its column headers ("Shift Name" / "Start Time") and an
        // item count even when empty. Assert that list UI by TEXT, not by element
        // selectors: force-pro keeps every module active, so #wpbody-content also holds
        // HIDDEN cross-module nav links (e.g. "Add Opening" from recruitment) that match
        // a:has-text("Add") and made a .first()/toBeVisible() resolve to a hidden link.
        await expect(page.locator(att.selectors.content))
            .toContainText(/Shift Name|Start Time|Add New Shift/i, { timeout: 30_000 });
    });

    test('ATT-UI-04 assign-shift-bulk route mounts, no fatal', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const att = new AttendancePage(page);
        await att.goto('assignShiftBulk');
        await att.expectMountedNoFatal();
    });

    test('ATT-UI-05 import/export (exim) route mounts, no fatal', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const att = new AttendancePage(page);
        await att.goto('exim');
        await att.expectMountedNoFatal();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// HR manager — should reach the same screens (cap erp_hr_manager)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Attendance UI (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    test('ATT-UI-06 HR manager reaches the shifts screen without a fatal', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        const att = new AttendancePage(page);
        await att.goto('shifts');
        // The menu/page is gated by erp_hr_manager; the manager must NOT see a fatal.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(att.selectors.content)).toBeVisible({ timeout: 30_000 });
    });
});
