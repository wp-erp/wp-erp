import { test, expect } from '@utils/test';
import { PaymentGatewayPage } from '@pages/pro/paymentGatewayPage';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ADMIN_STATE } from '@utils/authStates';
import { toDate, dateOffset } from '@utils/helpers';
import { cleanupInvoices, cleanupLedgerOrphans, customerIdFor } from '@utils/cleanupAccounting';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Payment Gateway (@pro).
 *
 * Actually charging a card needs live Stripe/PayPal credentials and would move
 * real money, so no case here submits a payment — the connect/charge flow is
 * out of scope for an automated run by design, and belongs with the
 * credential-gated integration work.
 *
 * What IS testable without a gateway account: the settings surface, and the
 * public invoice page the gateways decorate. That page is where the serious
 * finding lives, so most of this file is about who can read a customer's
 * invoice.
 */
const CUSTOMER = 'Lumen Health Partners';
const PRODUCT = 'Custom Dashboard Build';

test.describe('Payment Gateway @pro', () => {
    let page: PaymentGatewayPage;
    let customerId = 0;

    test.beforeAll(async () => {
        customerId = await customerIdFor(CUSTOMER);
    });

    test.beforeEach(async ({ page: p }) => {
        page = new PaymentGatewayPage(p);
        page.watchServerErrors();
        await cleanupInvoices(customerId);
        await cleanupLedgerOrphans();
    });

    test.afterAll(async () => {
        await cleanupInvoices(customerId);
        await cleanupLedgerOrphans();
        await closeDb();
    });

    /** Raises one invoice for this file's customer and returns its voucher number. */
    async function raiseInvoice(p: import('@playwright/test').Page): Promise<{ voucherNo: number; amount: number }> {
        const accounting = new AccountingPage(p);
        await accounting.gotoRoute('newInvoice');
        await accounting.pickFromMultiselect('Customer', CUSTOMER);
        await accounting.pickDate('Transaction Date', toDate());
        await accounting.pickDate('Due Date', dateOffset(30));
        await accounting.pickLineProduct(0, PRODUCT);
        await accounting.setLineQty(0, 1);
        await accounting.save();

        const rows = await query<RowDataPacket[]>(
            `SELECT voucher_no, amount FROM ${prefix()}erp_acct_invoices WHERE customer_id = ? ORDER BY id DESC LIMIT 1`,
            [customerId]
        );
        expect(rows, 'precondition: an invoice was raised').toHaveLength(1);

        return { voucherNo: Number(rows[0]!.voucher_no), amount: Number(rows[0]!.amount) };
    }

    // ---- Tier 1 ----------------------------------------------------------

    test('the payment settings offer the bundled gateways', { tag: ['@tier1', '@pro', '@payment-gateway', '@smoke'] }, async () => {
        await page.gotoPaymentSettings();

        const body = await page.bodyText();

        expect(body, 'Stripe is offered').toMatch(/Stripe/i);
        expect(body, 'PayPal is offered').toMatch(/PayPal/i);
        expect(page.serverErrorList(), 'and the tab loads without a server error').toEqual([]);
    });

    test("a customer's own invoice link opens their invoice", { tag: ['@tier1', '@pro', '@payment-gateway', '@flow'] }, async ({ browser, page: p }) => {
        // The legitimate flow the gateways sit on: the link in the customer's
        // emailed invoice opens a read-only copy with a Pay button. Read here
        // from a fresh anonymous context, exactly as a customer would.
        const invoice = await raiseInvoice(p);
        const auth = PaymentGatewayPage.forgeAuth(invoice.voucherNo);

        const anon = await browser.newContext({ storageState: undefined });
        const asVisitor = new PaymentGatewayPage(await anon.newPage());

        const result = await asVisitor.openReadonlyInvoice(invoice.voucherNo, auth);

        expect(result.status, 'the link resolves').toBe(200);
        expect(result.text, 'and shows the invoice').toContain(CUSTOMER);

        await anon.close();
    });

    // ---- Tier 3 — the public page's access control -----------------------

    test('a read-only invoice link with a wrong token shows nothing', { tag: ['@tier3', '@pro', '@payment-gateway', '@authz'] }, async ({ browser, page: p }) => {
        // The control that proves the token does SOMETHING — a garbage auth is
        // refused. What it does NOT do is the subject of the guard below.
        const invoice = await raiseInvoice(p);

        const anon = await browser.newContext({ storageState: undefined });
        const asVisitor = new PaymentGatewayPage(await anon.newPage());

        const result = await asVisitor.openReadonlyInvoice(invoice.voucherNo, 'deadbeef');

        expect(result.text, 'a wrong token reveals no invoice').not.toContain(CUSTOMER);

        await anon.close();
    });

    test.fail(
        'a read-only invoice link cannot be forged for an invoice you were not sent',
        { tag: ['@tier3', '@pro', '@payment-gateway', '@security', '@known-defect'] },
        async ({ browser, page: p }) => {
            // KNOWN DEFECT — ERP-161. The `auth` token guarding this public page
            // is `sha256(trans_id . type)` with no secret and no salt
            // (`transactions.php:1659-1670`). trans_id is the invoice's voucher
            // number and type is a small known word ("invoice"), so anyone can
            // compute the token for ANY invoice offline and read a customer's
            // name, address, line items and amount with no account at all.
            //
            // The wrong-token control above proves the endpoint checks SOMETHING;
            // this proves what it checks is worthless. The precondition confirms
            // the reader is genuinely anonymous.
            const invoice = await raiseInvoice(p);
            const forged = PaymentGatewayPage.forgeAuth(invoice.voucherNo);

            const anon = await browser.newContext({ storageState: undefined });
            const visitorPage = await anon.newPage();
            await visitorPage.goto('http://localhost:8888/', { waitUntil: 'domcontentloaded' });

            const loggedIn = await visitorPage.evaluate(async () => (await fetch('/wp-json/wp/v2/users/me')).status);
            expect(loggedIn, 'precondition: the reader is not logged in').toBe(401);

            const asVisitor = new PaymentGatewayPage(visitorPage);
            const result = await asVisitor.openReadonlyInvoice(invoice.voucherNo, forged);

            await anon.close();

            // The defect: a token anyone can compute lets a stranger read the invoice.
            expect(result.text, "a forged token does not disclose a customer's invoice").not.toContain(CUSTOMER);
        }
    );
});
