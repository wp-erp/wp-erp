import { test, expect } from '@utils/test';
import { AccountingPage, routeHeadings } from '@pages/accounting/accountingPage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { toDate, dateOffset } from '@utils/helpers';
import { cleanupPersonByEmail, cleanupPurchases, seedVendor } from '@utils/cleanupAccounting';
import { closeDb, prefix, query } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Accounting — purchases.
 *
 * A purchase is the vendor-side mirror of an invoice: it credits the vendor,
 * debits a purchase account, and — uniquely in ERP — is the only thing that
 * ever writes `stock_in`. Nothing else can put a product INTO stock.
 *
 * Two filed defects shape everything below, and both are guarded rather than
 * worked around:
 *
 *   - **ERP-160 / erp-pro#978** — New Purchase never loads its product list and
 *     then refuses to save without one, so a purchase CANNOT be raised through
 *     the UI at all. Every purchase here is therefore seeded over REST. That is
 *     a stated limitation of this file, not a preference: the create screen has
 *     no working path to assert against.
 *   - **erp-pro#967** — the REST create answers HTTP 500 while committing the
 *     record in full, because `erp_acct_send_email_on_transaction()` builds a
 *     PDF through a tFPDF copy that calls `get_magic_quotes_runtime()`, removed
 *     in PHP 8.
 *
 * **Cash is not touched here.** Paying a purchase spends the same Cash ledger
 * that `payments.spec.ts` owns, and that file owns it deliberately — so the
 * payment cases live there, beside bill payment, and this file stops at the
 * purchase itself.
 */

/** This file owns a vendor of its own; all four seeded vendors are claimed. */
const VENDOR = { firstName: 'Northgate', lastName: 'Timber Supply', email: 'ap@northgate-timber.test' };
const VENDOR_NAME = `${VENDOR.firstName} ${VENDOR.lastName}`;
const PRODUCT_ID = 1;
const QTY = 3;
const UNIT_PRICE = 100;
const TOTAL = QTY * UNIT_PRICE;

test.describe('Accounting — purchases', () => {
    let page: AccountingPage;
    let vendorId = 0;

    /** The newest purchase raised against this file's vendor. */
    async function latestPurchase(): Promise<RowDataPacket | undefined> {
        const rows = await query<RowDataPacket[]>(
            `SELECT id, voucher_no, amount FROM ${prefix()}erp_acct_purchase WHERE vendor_id = ? ORDER BY id DESC LIMIT 1`,
            [vendorId]
        );

        return rows[0];
    }

    /** What is still owed on a purchase: credits raised, less debits paid. */
    async function outstanding(voucherNo: number): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(credit) - SUM(debit), 0) AS due
               FROM ${prefix()}erp_acct_purchase_account_details WHERE purchase_no = ?`,
            [voucherNo]
        );

        return Number(rows[0]!.due);
    }

    /** Net stock movement this purchase produced for its product. */
    async function stockMoved(voucherNo: number): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(stock_in) - SUM(stock_out), 0) AS moved
               FROM ${prefix()}erp_acct_product_details WHERE trn_no = ?`,
            [voucherNo]
        );

        return Number(rows[0]!.moved);
    }

    test.beforeAll(async () => {
        vendorId = await seedVendor(VENDOR.firstName, VENDOR.lastName, VENDOR.email);
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        page.watchServerErrors();

        await cleanupPurchases(vendorId);
    });

    test.afterAll(async () => {
        await cleanupPurchases(vendorId);
        await cleanupPersonByEmail(VENDOR.email);
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the purchases screen lists its transactions', { tag: ['@tier1', '@accounting', '@purchases', '@smoke'] }, async () => {
        await page.gotoRoute('purchases');

        expect(await page.headings(), 'the screen renders').toContain(routeHeadings.purchases);

        const columns = await page.reportText();

        for (const column of ['Voucher No', 'Type', 'Trn Date', 'Due Date', 'Balance', 'Total', 'Status']) {
            expect(columns, `the ${column} column is present`).toContain(column);
        }
    });

    test('a purchase credits the vendor and moves stock', { tag: ['@tier1', '@accounting', '@purchases', '@money'] }, async () => {
        await page.createPurchaseViaRest(vendorId, VENDOR_NAME, PRODUCT_ID, QTY, UNIT_PRICE, toDate(), dateOffset(30));

        const purchase = await latestPurchase();

        expect(purchase, 'the purchase was committed').toBeDefined();
        expect(Number(purchase!.amount), 'for the line total').toBe(TOTAL);

        const voucherNo = Number(purchase!.voucher_no);

        expect(await outstanding(voucherNo), 'the whole amount is owed to the vendor').toBe(TOTAL);
        expect(await stockMoved(voucherNo), 'and the product entered stock').toBe(QTY);
    });

    test('a purchase posts a balanced double entry', { tag: ['@tier1', '@accounting', '@purchases', '@money'] }, async () => {
        await page.createPurchaseViaRest(vendorId, VENDOR_NAME, PRODUCT_ID, QTY, UNIT_PRICE, toDate(), dateOffset(30));

        const purchase = await latestPurchase();

        expect(purchase, 'precondition: the purchase was committed').toBeDefined();

        const rows = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(debit), 0) AS debits, COALESCE(SUM(credit), 0) AS credits
               FROM ${prefix()}erp_acct_ledger_details WHERE trn_no = ?`,
            [Number(purchase!.voucher_no)]
        );

        expect(Number(rows[0]!.debits), 'the purchase account is debited the full amount').toBe(TOTAL);
        expect(
            Number(rows[0]!.debits) - Number(rows[0]!.credits),
            'and nothing is left unbalanced against it'
        ).toBe(TOTAL);
    });

    test.fail(
        'creating a purchase answers with success, not a server error',
        { tag: ['@tier1', '@accounting', '@purchases', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — erp-pro#967. The purchase commits in full and then
            // the request dies building its PDF e-mail, so the caller is told
            // the create failed. The precondition asserts the record DID land,
            // which is the whole point: this guard can only fail on the status.
            const response = await page.createPurchaseViaRest(
                vendorId,
                VENDOR_NAME,
                PRODUCT_ID,
                QTY,
                UNIT_PRICE,
                toDate(),
                dateOffset(30)
            );

            expect(await latestPurchase(), 'precondition: the purchase was committed anyway').toBeDefined();

            expect(response.status, `the API reports the success it actually had, body: ${response.body}`).toBeLessThan(300);
        }
    );

    // ---- Tier 2 ----------------------------------------------------------

    test('a raised purchase shows on the screen as unpaid', { tag: ['@tier2', '@accounting', '@purchases', '@flow'] }, async () => {
        await page.createPurchaseViaRest(vendorId, VENDOR_NAME, PRODUCT_ID, QTY, UNIT_PRICE, toDate(), dateOffset(30));

        const purchase = await latestPurchase();

        expect(purchase, 'precondition: the purchase was committed').toBeDefined();

        await page.gotoRoute('purchases');
        const table = (await page.reportText()).replace(/\s+/g, ' ');
        const marker = `#${Number(purchase!.voucher_no)}`;
        const row = table.slice(table.indexOf(marker), table.indexOf(marker) + 200);

        expect(row, `the row names the vendor, row read: ${row}`).toContain(VENDOR_NAME);
        expect(row, 'and prints the amount owed').toContain('300.00');
    });

    test('the pay-purchase screen offers the vendor their outstanding purchase', { tag: ['@tier2', '@accounting', '@purchases', '@flow'] }, async ({ page: p }) => {
        await page.createPurchaseViaRest(vendorId, VENDOR_NAME, PRODUCT_ID, QTY, UNIT_PRICE, toDate(), dateOffset(30));

        const purchase = await latestPurchase();

        expect(purchase, 'precondition: the purchase was committed').toBeDefined();

        await page.gotoRoute('newPayPurchase');

        expect(await page.pickFromMultiselect('Vendor', VENDOR_NAME), 'the vendor is selectable').toBe(true);

        await p.waitForTimeout(2500);
        const due = (await page.reportText()).replace(/\s+/g, ' ');

        expect(due, 'the outstanding purchase is listed').toContain(`#${Number(purchase!.voucher_no)}`);
        expect(due, 'at its full balance').toContain('300.00');
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('purchases are closed to an employee', { tag: ['@tier3', '@accounting', '@purchases', '@authz'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const p = await context.newPage();
        const asEmployee = new AccountingPage(p);

        await asEmployee.gotoRoute('purchases');

        expect(
            await p.evaluate(() => Boolean((window as unknown as { erp_acct_var?: unknown }).erp_acct_var)),
            'the accounting app never loads for an employee'
        ).toBe(false);

        await context.close();
    });
});
