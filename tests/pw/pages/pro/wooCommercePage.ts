import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * WooCommerce integration (@pro) — syncs WC orders into ERP as contacts and
 * accounting invoices.
 *
 * Two settings toggles govern it and BOTH default to `yes`
 * (`functions.php:249,264`), so sync is on out of the box. The settings screen
 * lives in the ERP settings SPA under its own WooCommerce tab with four
 * sub-sections (Synchronization, Subscription, CRM, Accounting).
 *
 * The order → invoice flow itself is NOT driven from here: completing a WC
 * order on this PHP-8 build fatals in the invoice PDF path (the ERP-150 /
 * erp-pro#967 `get_magic_quotes_runtime()` sink), so exercising it in-suite
 * would fatal on every run. It is verified manually and recorded in COVERAGE.
 */
export const WC_SYNC_OPTIONS = {
    crm: 'erp_woocommerce_is_crm_active',
    accounting: 'erp_woocommerce_is_accounting_active',
} as const;

export class WooCommercePage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    /** The WooCommerce tab of ERP Settings. */
    async gotoSettings(section = 'crm'): Promise<void> {
        await this.gotoUrl(`/wp-admin/admin.php?page=erp-settings#/erp-woocommerce/${section}`);
        await this.page.waitForTimeout(5000);
    }

    /** The sub-section labels the WooCommerce settings tab paints. */
    async sections(): Promise<string> {
        return this.bodyText();
    }
}
