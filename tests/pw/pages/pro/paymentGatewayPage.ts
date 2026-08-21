import { createHash } from 'crypto';
import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * Payment Gateway (@pro) — Stripe and PayPal buttons on a customer's invoice.
 *
 * The gateways decorate the PUBLIC read-only invoice page, the same
 * `?query=readonly_invoice&trans_id=…&auth=…` URL a customer follows from their
 * emailed invoice. That page's `auth` token is `sha256(trans_id . type)` with
 * no secret (ERP-161), which is why the helper below can forge it — the point
 * of the security case, not a shortcut around one.
 */
export const READONLY_INVOICE_BASE = 'http://localhost:8888/?query=readonly_invoice';

/** Gateway secret fields, declared `type: text` by the module — the masking note. */
export const gatewaySecretFieldIds = [
    'erp-erp_pg_stripe_live_secret_key',
    'erp-erp_pg_stripe_test_secret_key',
] as const;

export class PaymentGatewayPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    /** ERP Settings → Accounting → Payment, where the gateways are configured. */
    async gotoPaymentSettings(): Promise<void> {
        await this.gotoUrl('/wp-admin/admin.php?page=erp-settings#/erp-ac/payment');
        await this.page.waitForTimeout(6000);
    }

    /**
     * The `auth` token the product would generate for a transaction — and, since
     * it is only `sha256(id . type)`, the token anyone can generate for it.
     */
    static forgeAuth(transId: number, type = 'invoice'): string {
        return createHash('sha256').update(`${transId}${type}`).digest('hex');
    }

    /** Opens a customer's read-only invoice at a given id with a supplied auth token. */
    async openReadonlyInvoice(transId: number, auth: string): Promise<{ status: number; text: string }> {
        const response = await this.page.goto(`${READONLY_INVOICE_BASE}&trans_id=${transId}&auth=${auth}`, {
            waitUntil: 'domcontentloaded',
        });
        await this.page.waitForTimeout(800);

        return {
            status: response?.status() ?? 0,
            text: (await this.page.locator('body').innerText()).replace(/\s+/g, ' '),
        };
    }
}
