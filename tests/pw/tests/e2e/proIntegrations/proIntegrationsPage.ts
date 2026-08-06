import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';

/**
 * Feature-isolated page object for the PRO accounting integrations
 * (Payment Gateway + WooCommerce) settings smokes — feature `proIntegrationsAcct`.
 *
 * These two add-ons hook into the legacy server-rendered WP ERP Settings via
 * filters and persist to wp_options; neither exposes a /erp/v1 REST controller.
 * Both are gated on an EXTERNAL plugin/state, so the assertions are deliberately
 * resilient page-mount smokes (no fatal + the real mount), per the @pro grounding.
 *
 * Verified surface (READ from source):
 *   - Mount shell `#erp-settings` — wp-erp includes/Settings/views/settings.php:9.
 *   - The Settings SPA is hash-routed (no history mode); routes are reached as
 *     `...?page=erp-settings#/<route>` — includes/Settings/assets/src/router/index.js
 *     (`/erp-ac` line 89, `/erp-woocommerce` line 160).
 *   - WooCommerce.vue renders inner `#erp-wc-sync` and, when WC is NOT active, a
 *     "Connect with WooCommerce" CTA `#erp-wc-sync-notice-btn`
 *     (wp-erp includes/Settings/assets/src/components/woocommerce/WooCommerce.vue:2,17).
 *   - The pro "Payment" section is appended to the Accounting tab via the
 *     `erp_get_sections_erp-ac` filter — erp-pro modules/accounting/payment-gateway/
 *     includes/PaymentGatewaySettings.php:14-16; its PayPal/Stripe/General field ids
 *     come from Gateways/Paypal.php:48, Gateways/Stripe.php:65, GeneralSettings.php:44.
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

export class ProIntegrationsPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URLs (the Settings SPA is hash-routed; legacy section URL is query form) ──
    readonly urls = {
        // SPA shell root.
        settingsRoot: toPath('wp-admin/admin.php?page=erp-settings#/'),
        // Hash routes (router/index.js — no history mode).
        woocommerce: toPath('wp-admin/admin.php?page=erp-settings#/erp-woocommerce'),
        accounting: toPath('wp-admin/admin.php?page=erp-settings#/erp-ac'),
        // Legacy server-rendered section URL for the pro Payment fields (still resolves).
        paymentSection: toPath('wp-admin/admin.php?page=erp-settings&tab=erp-ac&section=payment'),
    } as const;

    // ── Selectors (real ids/text from the views — see header for sources) ─────────
    readonly sel = {
        // SPA mount shell (settings.php:9, App.vue:2).
        settingsRoot: '#erp-settings',
        // WooCommerce settings inner container (WooCommerce.vue:2).
        wcSync: '#erp-wc-sync',
        // "Connect with WooCommerce" CTA when the WC plugin is not active (WooCommerce.vue:17).
        wcNoticeBtn: '#erp-wc-sync-notice-btn',
        // Admin body wrapper — used to scope text assertions.
        body: '#wpbody-content',
        // Fatal-error oracle target.
        fatalOracle: 'body',
        // Payment-gateway field ids (rendered only when the field form mounts).
        paypalEnable: '#erp_pg_paypal_enable_disable',
        stripeEnable: '#erp_pg_stripe_enable_disable',
        paymentAccountHead: '#erp_pg_payment_account_head',
        // WC>Accounting "Enable Order Sync" radio (rendered when WC+accounting active).
        wcAccountingActive: '#erp_woocommerce_is_accounting_active',
    } as const;

    // ── Navigation helpers ────────────────────────────────────────────────────────

    /** Open the Settings SPA root and wait for the mount shell. */
    async goToSettingsRoot(): Promise<void> {
        await this.page.goto(this.urls.settingsRoot);
        await expect(this.page.locator(this.sel.settingsRoot)).toBeVisible({ timeout: 30_000 });
    }

    /** Open the WooCommerce settings hash route and wait for the mount shell. */
    async goToWooCommerce(): Promise<void> {
        await this.page.goto(this.urls.woocommerce);
        await expect(this.page.locator(this.sel.settingsRoot)).toBeVisible({ timeout: 30_000 });
    }

    /** Open the Accounting settings hash route (host of the pro Payment section). */
    async goToAccounting(): Promise<void> {
        await this.page.goto(this.urls.accounting);
        await expect(this.page.locator(this.sel.settingsRoot)).toBeVisible({ timeout: 30_000 });
    }

    /** Open the legacy server-rendered Payment section URL. */
    async goToPaymentSection(): Promise<void> {
        await this.page.goto(this.urls.paymentSection);
        // Server-rendered; the shell may render attached but not necessarily "visible"
        // depending on the bundle, so callers assert attachment + no-fatal.
        await expect(this.page.locator(this.sel.settingsRoot)).toBeAttached({ timeout: 30_000 });
    }

    // ── Oracles ───────────────────────────────────────────────────────────────────

    /** True if the page shows the WP critical-error screen (PHP/JS fatal). */
    async hasCriticalError(): Promise<boolean> {
        return (await this.page.locator(this.sel.fatalOracle).innerText()).includes(CRITICAL_ERROR);
    }

    /** True if the dependency-gated "Connect with WooCommerce" CTA is present. */
    async hasConnectCta(): Promise<boolean> {
        return (await this.page.locator(this.sel.wcNoticeBtn).count()) > 0;
    }
}

export { CRITICAL_ERROR };
