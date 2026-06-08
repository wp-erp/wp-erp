import { test, expect } from '@utils/test';
import { CrmPage } from './crmPage';
import { data } from '@utils/testData';
import { proModuleActive } from '@utils/helpers';

/**
 * CRM Integrations settings pages (PRO) — UI smoke.
 *
 * Route: admin.php?page=erp-crm&section=integration  (per provider append
 * &sub-section=hubspot|mailchimp|salesforce|help_scout). The whole feature is
 * server-rendered WP-admin pages + admin-ajax/wp_options — there is NO /erp/v1
 * REST surface here, so this is a pure UI smoke (do not build a restUrl()).
 *
 * Dispatch (grounded in erp-pro/includes/Admin/Admin.php):
 *   - The 'integration' CRM section is registered by Admin::admin_menu() ONLY when
 *     at least one of hubspot|mailchimp|salesforce|help_scout|awesome_support is
 *     active. The @pro setup activates all modules, so the section exists.
 *   - integration_page() picks $default = first active in
 *     [hubspot, mailchimp, salesforce, help_scout, awesome_support] (=> hubspot),
 *     reads ?sub-section= and fires do_action('erp_crm_'.$sub_section.'_page').
 *   - Each erp_crm_{provider}_page handler renders:
 *       div.wrap > h2 'Integrations' > do_action('erp_crm_integration_menu', ...)
 *       (=> div.erp-custom-menu-container > ul.erp-nav) > not-connected CTA
 *       (button.button-secondary: 'Configure' for mailchimp/hubspot/help_scout,
 *       'Connect Now' for salesforce) OR, when connected, a provider sync form
 *       (form#erp_mailchimp_sync_form / form#erp_helpscout_sync_form).
 *
 * Tests MUST NOT assume the connected branch (no real API keys are saved in QA),
 * so each provider assertion uses the resilient union:
 *   page mounted + no fatal AND (button.button-secondary visible OR sync form visible).
 *
 * Gotchas: awesome_support appears in ul.erp-nav but has no erp_crm_awesome_support_page
 * action (its <li> links to the erp-settings SPA) — treat &sub-section=awesome_support
 * as 'no fatal' only; do not assert a settings form. zendesk is not in this dropdown.
 *
 * Every test carries a tier tag (@pro), the @crm module tag and a role tag.
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

// Providers that DO render a settings surface (have an erp_crm_{provider}_page handler).
const PROVIDERS = ['hubspot', 'mailchimp', 'salesforce', 'help_scout'] as const;

/** The integration PROVIDER nav. erp_crm_integration_menu renders a plain
 *  <ul class="erp-nav"> (NO -primary modifier) in BOTH the connected and
 *  not-connected branches. Under force-pro the page also carries several
 *  module primary navs (ul.erp-nav -primary …) — a bare `ul.erp-nav` is a
 *  strict-mode violation, so scope to the plain (non -primary) provider nav. */
const ERP_NAV = 'ul.erp-nav:not(.-primary)';
/** Resilient "settings surface" union: the not-connected CTA is a plain
 *  <button class="button-secondary"> (Configure / Connect Now), OR — when real
 *  keys happen to be saved — a connected sync form. */
const SETTINGS_SURFACE =
    'button.button-secondary, form#erp_mailchimp_sync_form, form#erp_helpscout_sync_form';

// ──────────────────────────────────────────────────────────────────────────
// Admin role — full integration surface
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // INTG-UI-01 — section mounts with no PHP fatal (default sub-section = hubspot).
    test('integration section mounts with no fatal (default sub-section)', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.integrations);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator('#wpbody-content')).toBeVisible();
        // div.wrap > h2 'Integrations' is emitted by every provider page handler.
        await expect(page.locator('div.wrap h2').first()).toContainText(/Integrations/i);
    });

    // INTG-UI-02 — provider nav renders every active integration tab.
    test('provider nav (ul.erp-nav) renders all active integration tabs', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        // Only providers whose module is active render a nav tab; assert exactly those.
        const activeProviders = PROVIDERS.filter((p) => proModuleActive(p));
        test.skip(activeProviders.length === 0, 'no CRM integration provider module is active in this env');
        const crm = new CrmPage(page);
        await page.goto(crm.urls.integrations);
        await expect(page.locator(ERP_NAV)).toBeVisible();
        // Each active provider tab <a data-key="..."> renders (Module.php line 1011).
        // awesome_support is NOT rendered here in this force-pro build, so it is not asserted.
        for (const key of activeProviders) {
            expect(await page.locator(`${ERP_NAV} a[data-key="${key}"]`).count()).toBeGreaterThanOrEqual(1);
        }
    });

    // INTG-UI-03..06 — each provider sub-section renders a settings surface.
    for (const provider of PROVIDERS) {
        test(`${provider} sub-section renders a provider settings surface`, { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
            test.skip(!proModuleActive(provider), `needs the ${provider} integration module (inactive in this env)`);
            const crm = new CrmPage(page);
            await page.goto(`${crm.urls.integrations}&sub-section=${provider}`);

            // No PHP fatal, real admin mount, and the Integrations chrome.
            await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
            await expect(page.locator('#wpbody-content')).toBeVisible();
            await expect(page.locator('div.wrap h2').first()).toContainText(/Integrations/i);
            await expect(page.locator(ERP_NAV)).toBeVisible();

            // Resilient union: not-connected Configure/Connect Now CTA OR (if real
            // keys happen to be saved) a connected sync form. Do not assume either.
            await expect(page.locator(SETTINGS_SURFACE).first()).toBeVisible();
        });
    }

    // INTG-UI-07 — awesome_support sub-section: no settings handler exists, so only
    // assert "no fatal" + the integration chrome (do NOT expect a settings form/CTA).
    test('awesome_support sub-section mounts without a fatal (no handler)', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(`${crm.urls.integrations}&sub-section=awesome_support`);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator('#wpbody-content')).toBeVisible();
    });

    // INTG-UI-08 — an unknown sub-section must not fatal (is_active() guard skips
    // the do_action), it just renders the empty integration page chrome.
    test('unknown sub-section is handled without a fatal', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(`${crm.urls.integrations}&sub-section=not_a_real_provider_${Date.now()}`);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator('#wpbody-content')).toBeVisible();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// CRM manager role — passes erp_crm_get_manager_role() cap, should mount the page
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations (pro, manager)', () => {
    test.use({ storageState: data.auth.crmManagerFile });

    test('manager can mount the integration section', { tag: ['@pro', '@crm', '@manager'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.integrations);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(ERP_NAV)).toBeVisible();
        await expect(page.locator('div.wrap h2').first()).toContainText(/Integrations/i);
    });

    test('manager can open the mailchimp sub-section', { tag: ['@pro', '@crm', '@manager'] }, async ({ page }) => {
        test.skip(!proModuleActive('mailchimp'), 'needs the mailchimp integration module (inactive in this env)');
        const crm = new CrmPage(page);
        await page.goto(`${crm.urls.integrations}&sub-section=mailchimp`);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(ERP_NAV)).toBeVisible();
        await expect(page.locator(SETTINGS_SURFACE).first()).toBeVisible();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Negative — a plain employee lacks the CRM manager cap on the section
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations access control (employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('employee does not reach the integration settings', { tag: ['@pro', '@crm', '@employee'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(`${crm.urls.integrations}&sub-section=mailchimp`);
        // The section is gated by erp_crm_get_manager_role(); an employee should not
        // see the integration nav. Assert the access boundary, not an exact code —
        // either the nav is absent (permission notice / redirect) and, in all cases,
        // there is no PHP fatal.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(ERP_NAV)).toHaveCount(0);
    });
});
