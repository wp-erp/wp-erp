import { test, expect } from '@utils/test';
import { ADMIN_STATE } from '@utils/authStates';

test.use({ storageState: ADMIN_STATE });

/**
 * The HR admin router (`wp-erp/modules/hrm/includes/Admin/AdminMenu.php:186`).
 *
 * These are plain HTTP status probes — no browser interaction — because the
 * question is only ever "what does the server answer for this route". They are
 * fast enough to cover every section, and a status code cannot be misread the
 * way rendered text can.
 *
 * Written after ERP-148: a submenu registered as redirect-only
 * (`'callback' => ''` plus a `direct_link`) makes the router call
 * `call_user_func('')`, which is fatal on PHP 8.
 */
const hrUrl = (query: string): string => `/wp-admin/admin.php?page=erp-hr${query ? `&${query}` : ''}`;

/** Sections whose `settings` submenu is NOT registered redirect-only. */
const SAFE_SETTINGS_SECTIONS = ['leave', 'asset', 'recruitment', 'training', 'documents'] as const;

test.describe('HR — admin router', () => {
    // ---- Tier 2 — the router's fallbacks -----------------------------------

    test('an unknown sub-section falls back instead of failing', { tag: ['@tier2', '@hrm', '@router'] }, async ({ page }) => {
        // `$sub` is only accepted when it is a registered key, so an unknown one
        // is supposed to fall through to the section's own screen. This is the
        // positive control that makes the known-defect case below specific: the
        // fault is NOT "unknown routes break".
        for (const section of ['payroll', 'attendance', 'leave', 'asset', 'recruitment']) {
            const response = await page.request.get(hrUrl(`section=${section}&sub-section=pwerpNoSuchSubSection`));

            expect(response.status(), `section=${section} with an unknown sub-section`).toBe(200);
        }
    });

    test('an unknown section falls back to the dashboard', { tag: ['@tier2', '@hrm', '@router'] }, async ({ page }) => {
        const response = await page.request.get(hrUrl('section=pwerpNoSuchSection'));

        expect(response.status(), 'an unknown section is not an error').toBe(200);
    });

    test('every section with a real settings screen serves it', { tag: ['@tier2', '@hrm', '@router'] }, async ({ page }) => {
        for (const section of SAFE_SETTINGS_SECTIONS) {
            const response = await page.request.get(hrUrl(`section=${section}&sub-section=settings`));

            expect(response.status(), `section=${section}&sub-section=settings`).toBe(200);
        }
    });

    // ---- Tier 3 — the known defect -----------------------------------------

    test.fail(
        'a redirect-only submenu does not fatal the page',
        { tag: ['@tier3', '@pro', '@hrm', '@router', '@known-defect'] },
        async ({ page }) => {
            // KNOWN DEFECT — ERP-148 / erp-pro#965. Payroll and Attendance both
            // register a `settings` submenu with `'callback' => ''` and a
            // `direct_link`, and `router()` hands that empty string to
            // `call_user_func()` without checking it. The CRM router, given the
            // same registration shape, answers 200 — asserted below as the
            // contrast, so this case cannot pass merely because the site is down.
            const crm = await page.request.get('/wp-admin/admin.php?page=erp-crm&section=deals&sub-section=settings');
            expect(crm.status(), 'precondition: the CRM router handles the same shape').toBe(200);

            for (const section of ['payroll', 'attendance']) {
                const response = await page.request.get(hrUrl(`section=${section}&sub-section=settings`));

                expect(response.status(), `section=${section}&sub-section=settings does not fatal`).toBe(200);
            }
        }
    );
});
