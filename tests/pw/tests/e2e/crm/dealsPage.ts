import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';

/**
 * Feature-isolated page object for the WP ERP **Pro** Deals module
 * (erp-pro/modules/crm/deals).
 *
 * The Deals data layer is admin-ajax + Eloquent models, NOT REST — there is no
 * `/erp/v1/deals` route (verified: `grep register_rest_route` in the deals module
 * returns nothing). So this POM only models the UI surface; the seed/round-trip
 * assertions live in the sibling DB spec (tests/api/crm/deals.db.api.spec.ts).
 *
 * Routing (WPERP >= 1.4.0): Deals registers via `erp_add_menu` INTO the CRM app,
 * so it is reached as `admin.php?page=erp-crm&section=deals` with a
 * `&sub-section=` selecting the in-app view
 * (deals/includes/Admin.php::load_new_menu() + load_new_scripts() which defaults
 * `$_GET['sub-section']` to 'dashboard'):
 *   - dashboard (default) → <overview> + funnel/charts
 *   - all-deals           → <pipeline-view> / <single-deal> / <new-deal-modal>
 *   - activities          → <activity-list>
 *   - settings            → a direct link to erp-settings#/erp-crm/erp_deals
 *
 * Mount (deals/views/deals.php L2-3, the >=1.4.0 branch):
 *   <div class="wrap"><div class="erp-grid-container erp-deal-page" id="erp-deals" v-cloak>
 * The stable id `#erp-deals` is what existing crm.spec.ts already asserts on.
 */
export class DealsPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URLs (admin.php?page=erp-crm routed by &section=deals&sub-section=) ─────
    readonly urls = {
        // Bare deals page (no sub-section → Admin.php defaults to 'dashboard').
        deals: toPath('wp-admin/admin.php?page=erp-crm&section=deals'),
        dealsDashboard: toPath('wp-admin/admin.php?page=erp-crm&section=deals&sub-section=dashboard'),
        dealsAllDeals: toPath('wp-admin/admin.php?page=erp-crm&section=deals&sub-section=all-deals'),
        dealsActivities: toPath('wp-admin/admin.php?page=erp-crm&section=deals&sub-section=activities'),
        // Settings is a CRM settings tab (direct_link in load_new_menu, cap create_users).
        dealsSettings: toPath('wp-admin/admin.php?page=erp-settings&tab=erp-crm&section=erp_deals'),
    } as const;

    // ── Selectors (verbatim from deals/views/deals.php and the admin chrome) ───
    readonly selectors = {
        // Stable SPA mount id (deals.php L3). Present in BOTH version branches.
        root: '#erp-deals',
        // Outer wrapper class on the same node.
        wrap: '.erp-grid-container.erp-deal-page',
        // WP admin content area — used for resilient text assertions.
        body: '#wpbody-content',
        // The in-app deals sub-nav lives in the shared CRM page heading/nav.
        pageHeading: '#wpbody-content h2, #wpbody-content .nav-tab-wrapper',
    } as const;

    /** A PHP fatal renders this exact string — every UI test asserts its ABSENCE. */
    readonly criticalError = 'There has been a critical error on this website';

    // ── Navigation ────────────────────────────────────────────────────────────

    /** Open a deals URL and wait for the WP admin shell to render. */
    async goto(url: string): Promise<void> {
        await this.page.goto(url);
        await expect(this.page.locator(this.selectors.body)).toBeVisible({ timeout: 30_000 });
    }

    async gotoDeals(): Promise<void> {
        await this.goto(this.urls.deals);
    }

    /**
     * Assert the Deals SPA booted: no PHP fatal + the real `#erp-deals` mount is
     * on screen. Vue strings are i18n-driven, so we assert the stable id and the
     * outer wrap class rather than component innerText.
     */
    async expectMountedNoFatal(): Promise<void> {
        await expect(this.page.locator('body')).not.toContainText(this.criticalError);
        await expect(this.page.locator(this.selectors.root)).toBeVisible({ timeout: 30_000 });
        await expect(this.page.locator(this.selectors.wrap)).toHaveCount(1);
    }
}
