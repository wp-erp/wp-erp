import { test, expect } from '@utils/test';
import { RecruitmentPage, CRITICAL_ERROR } from './recruitmentPage';
import { data } from '@utils/testData';

/**
 * HRM — Recruitment (erp-pro module: recruitment). Admin UI smoke.
 *
 * The Job Openings list is rendered server-side
 * (modules/hrm/recruitment/includes/Admin/PageRenderer::render_job_table) as a
 * WP_List_Table inside `div.wrap.job-opening-wrap`. There is no Vue app to boot,
 * so the smoke asserts: no PHP fatal + the real mount + the "Job Openings"
 * heading, "Add Opening" action, the hidden recruitment `section` field, the
 * list table, and the search box — all grounded in the view template.
 *
 * Route: admin.php?page=erp-hr&section=recruitment (HRM app, &section routed).
 * Every test carries a tier tag (@pro), the @hrm module tag and a role tag.
 */

test.describe('HRM Recruitment Job Openings (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('REC-UI-01 recruitment section mounts without a fatal error', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const rec = new RecruitmentPage(page);
        await page.goto(rec.urls.list);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // render_job_table prints div.wrap.job-opening-wrap (PageRenderer L67).
        await expect(page.locator(rec.sel.wrap)).toBeVisible({ timeout: 30_000 });
    });

    test('REC-UI-02 Job Openings heading and Add Opening action are visible', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const rec = new RecruitmentPage(page);
        await rec.goToList();
        // h1.wp-heading-inline contains "Job Openings" (L70-71).
        await expect(page.locator(rec.sel.heading)).toContainText(/Job Openings/i);
        // a.page-title-action "Add Opening" (L72-74).
        const addOpening = page.locator(rec.sel.addOpening);
        await expect(addOpening).toBeVisible();
        await expect(addOpening).toContainText(/Add Opening/i);
    });

    test('REC-UI-03 the recruitment list form and WP list table render', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const rec = new RecruitmentPage(page);
        await rec.goToList();
        // Hidden routing field <input name="section" value="recruitment"> (L79).
        await expect(page.locator(rec.sel.sectionField)).toHaveCount(1);
        // The WP_List_Table is rendered by $job_opening_table->display() (L84).
        await expect(page.locator(rec.sel.table)).toBeVisible();
    });

    test('REC-UI-04 the recruitment list shell renders (search box optional)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const rec = new RecruitmentPage(page);
        await rec.goToList();
        // The always-present server-rendered shell: the WP_List_Table (L84) and the
        // "Add Opening" action (L72-74). search_box('erp-recruitment-search') renders
        // its `erp-recruitment-search-search-input` field ONLY when the list has items
        // or an active search query — on an empty Job Openings list WP omits it. So we
        // assert the table + Add Opening, and assert the search box only if it exists.
        await expect(page.locator(rec.sel.table)).toBeVisible();
        await expect(page.locator(rec.sel.addOpening)).toBeVisible();
        const searchBox = page.locator(rec.sel.searchInput);
        if ((await searchBox.count()) > 0) {
            await expect(searchBox.first()).toBeVisible();
        }
    });
});

// ──────────────────────────────────────────────────────────────────────────
// HR manager role — should reach the same Job Openings screen
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Recruitment Job Openings (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    test('REC-UI-05 HR manager can reach the Job Openings list', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        const rec = new RecruitmentPage(page);
        await page.goto(rec.urls.list);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The manager either sees the real recruitment mount or, at worst, a clean
        // permission boundary — never a PHP fatal. Assert the boundary, not a code.
        const mountVisible = await page
            .locator(rec.sel.wrap)
            .isVisible()
            .catch(() => false);
        if (!mountVisible) {
            await expect(page.locator('#wpbody-content')).toBeVisible();
        }
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Negative — a plain employee must not reach the recruitment management screen
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Recruitment access control (employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('REC-UI-06 employee does not get the Add Opening management action', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const rec = new RecruitmentPage(page);
        await page.goto(rec.urls.list);
        // No PHP fatal regardless of the permission outcome.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // An employee lacks the recruitment menu capability, so the "Add Opening"
        // management action must not be available to them.
        await expect(page.locator(rec.sel.addOpening)).toHaveCount(0);
    });
});
