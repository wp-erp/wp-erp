import { test, expect } from '@utils/test';
import { DealsPage } from './dealsPage';
import { data } from '@utils/testData';

/**
 * PRO — CRM Deals UI smoke (erp-pro/modules/crm/deals).
 *
 * The Deals data layer is admin-ajax + Eloquent models, NOT REST (no
 * `/erp/v1/deals` route exists). So this UI spec is intentionally smoke-level:
 * it proves the Vue SPA boots into `#erp-deals` with no PHP fatal across each
 * sub-section (Dashboard / All Deals / Activities), and asserts the access-control
 * boundary for a plain employee. The DB seed/round-trip lives in the sibling
 * tests/api/crm/deals.db.api.spec.ts.
 *
 * Resilient-assertion philosophy (per _pro-grounding.md):
 *  - Always assert NOT the critical-error string AND the real mount visible.
 *  - Do not assert Vue component innerText (i18n-driven); assert the stable
 *    `#erp-deals` id + the `.erp-grid-container.erp-deal-page` wrap + a loose
 *    /Deals|Pipeline|Overview/i match on the admin content area.
 *  - For the employee boundary, assert the mount is absent OR a WP "no
 *    permission" notice — never a fatal.
 *
 * Every test carries a tier tag (@pro), the @crm module tag and a role tag.
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

// ──────────────────────────────────────────────────────────────────────────
// Admin role — full Deals surface boots without a fatal
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals UI (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // DEALS-UI-01 — bare deals page mounts the SPA, no PHP fatal.
    test('deals page (section=deals) mounts #erp-deals with no PHP fatal', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const deals = new DealsPage(page);
        await deals.gotoDeals();

        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(deals.selectors.root)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator(deals.selectors.body)).toContainText(/Deals|Pipeline|Overview/i);
    });

    // DEALS-UI-02 — Dashboard sub-section (the Admin.php default) renders the
    // overview shell (funnel/charts enqueue here) with no fatal.
    test('Dashboard sub-section renders the overview shell, no fatal', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const deals = new DealsPage(page);
        await deals.goto(deals.urls.dealsDashboard);

        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(deals.selectors.root)).toBeVisible({ timeout: 30_000 });
        // Outer wrap on the mount node is present exactly once.
        await expect(page.locator(deals.selectors.wrap)).toHaveCount(1);
    });

    // DEALS-UI-03 — All Deals sub-section (pipeline view + new-deal modal) boots.
    test('All Deals sub-section mounts the pipeline view, no fatal', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const deals = new DealsPage(page);
        await deals.goto(deals.urls.dealsAllDeals);

        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(deals.selectors.root)).toBeVisible({ timeout: 30_000 });
    });

    // DEALS-UI-04 — Activities sub-section (<activity-list>) boots.
    test('Activities sub-section mounts the activity list, no fatal', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const deals = new DealsPage(page);
        await deals.goto(deals.urls.dealsActivities);

        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(deals.selectors.root)).toBeVisible({ timeout: 30_000 });
    });

    // DEALS-UI-05 — Settings is a CRM settings tab (direct_link). It is a
    // different shell (erp-settings SPA), so assert no fatal + the settings app
    // mounts; do not require #erp-deals here.
    test('Deals Settings tab loads under erp-settings with no fatal', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const deals = new DealsPage(page);
        await deals.goto(deals.urls.dealsSettings);

        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The settings SPA renders into #wpbody-content; just prove it answered.
        await expect(page.locator(deals.selectors.body)).toBeVisible({ timeout: 30_000 });
    });

    // DEALS-UI-06 — the mount uses the helper's combined no-fatal assertion to
    // cover the wrap class + id + critical-error string in one resilient gate.
    test('helper expectMountedNoFatal passes on the deals page', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const deals = new DealsPage(page);
        await deals.gotoDeals();
        await deals.expectMountedNoFatal();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// CRM manager role — has erp_crm_add_contact, so reaches the same Deals app
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals UI (pro, manager)', () => {
    test.use({ storageState: data.auth.crmManagerFile });

    // DEALS-UI-07 — manager (erp_crm_add_contact cap) can boot the deals SPA.
    test('CRM manager can boot the deals SPA', { tag: ['@pro', '@crm', '@manager'] }, async ({ page }) => {
        const deals = new DealsPage(page);
        await deals.gotoDeals();

        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(deals.selectors.root)).toBeVisible({ timeout: 30_000 });
    });

    // DEALS-UI-08 — manager can also reach the Dashboard sub-section overview.
    test('CRM manager reaches the Dashboard sub-section', { tag: ['@pro', '@crm', '@manager'] }, async ({ page }) => {
        const deals = new DealsPage(page);
        await deals.goto(deals.urls.dealsDashboard);

        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(deals.selectors.root)).toBeVisible({ timeout: 30_000 });
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Negative — a plain employee lacks erp_crm_add_contact, so the Deals app
// must NOT mount. Assert the boundary (no #erp-deals) OR a no-permission
// notice — and never a PHP fatal.
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals access control (pro, employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    // DEALS-UI-09 — employee is denied the deals page (capability gate).
    test('employee cannot mount the deals SPA', { tag: ['@pro', '@crm', '@employee'] }, async ({ page }) => {
        const deals = new DealsPage(page);
        await page.goto(deals.urls.deals);

        // Never a fatal, regardless of how WP denies access.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        // The Deals SPA must not mount for an employee. WP may render a
        // "do not have permission" / "cheating" notice instead — accept either.
        const mountCount = await page.locator(deals.selectors.root).count();
        if (mountCount === 0) {
            expect(mountCount, 'employee gets no #erp-deals mount').toBe(0);
        } else {
            // If the node is in the DOM it must not be a usable, visible app —
            // a denied page shows a WP permission notice somewhere in the body.
            await expect(page.locator('body')).toContainText(
                /do not have permission|sorry, you are not allowed|cheat/i,
            );
        }
    });
});
