import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';

/**
 * Feature-isolated page object for the HRM Recruitment (erp-pro) admin screens.
 *
 * The Job Openings list is rendered server-side by
 * modules/hrm/recruitment/includes/Admin/PageRenderer::render_job_table() —
 * a WP_List_Table inside `div.wrap.job-opening-wrap` with a `h1.wp-heading-inline`
 * "Job Openings" heading, an `a.page-title-action` "Add Opening" link, a hidden
 * `<input name="section" value="recruitment">`, the standard `table.wp-list-table`,
 * and the search box id `erp-recruitment-search-search-input`
 * (search_box($text,'erp-recruitment-search') → WP suffixes `-search-input`).
 *
 * The route is `admin.php?page=erp-hr&section=recruitment` (HRM app, &section
 * routed). Selectors are taken verbatim from the rendered view; every consumer
 * test asserts no PHP fatal + a real mount (resilient-assertion philosophy).
 */

export const CRITICAL_ERROR = 'There has been a critical error on this website';

export class RecruitmentPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URLs (admin.php?page=erp-hr routed by &section=recruitment) ────────────
    readonly urls = {
        list: toPath('wp-admin/admin.php?page=erp-hr&section=recruitment'),
        candidates: toPath('wp-admin/admin.php?page=erp-hr&section=recruitment&sub-section=candidates'),
    } as const;

    // ── Selectors (verbatim from PageRenderer::render_job_table) ───────────────
    readonly sel = {
        wrap: 'div.wrap.job-opening-wrap',
        heading: 'h1.wp-heading-inline',
        addOpening: 'a.page-title-action',
        sectionField: 'form input[name="section"][value="recruitment"]',
        table: 'table.wp-list-table',
        searchInput: 'input#erp-recruitment-search-search-input',
    } as const;

    /** Navigate to the Job Openings list and assert the real mount + no fatal. */
    async goToList(): Promise<void> {
        await this.page.goto(this.urls.list);
        await expect(this.page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(this.page.locator(this.sel.wrap)).toBeVisible({ timeout: 30_000 });
    }
}
