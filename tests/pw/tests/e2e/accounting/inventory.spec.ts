import { test, expect } from '@utils/test';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { toDate, dateOffset } from '@utils/helpers';
import { cleanupInvoices, cleanupLedgerOrphans, customerIdFor, vendorIdFor } from '@utils/cleanupAccounting';
import { execute, query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Inventory (@pro) — stock movement driven by accounting transactions.
 *
 * The module owns no screen of its own beyond a read-only register: stock moves
 * because a purchase or a sale happened. Every movement is one row in
 * `erp_acct_product_details` carrying `stock_in` / `stock_out` against a
 * transaction, and stock on hand is the difference. Only products of type
 * **Inventory** (`product_type_id = 1`) are tracked at all.
 *
 * The oracle is therefore the stock figure the Inventory screen prints, checked
 * against the movements the transactions should have produced — never the
 * transaction alone.
 */

/** This file owns ONE product and one party each side, so the arithmetic is its own. */
const PRODUCT = 'Analytics Platform — Starter (annual)';
const PRODUCT_ID = 1;
const CUSTOMER = 'Beacon Retail Group';
const VENDOR = 'Pinewood Print & Signage';
const UNIT_PRICE = 1200;

test.describe('Accounting — inventory', () => {
    let page: AccountingPage;
    let customerId = 0;

    /** Kept for the purchase guard, which needs a vendor even though it cannot save. */
    let vendorId = 0;

    /** Stock on hand for this file's product: what came in, less what went out. */
    async function onHand(): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(stock_in), 0) AS in_qty, COALESCE(SUM(stock_out), 0) AS out_qty
               FROM ${prefix()}erp_acct_product_details WHERE product_id = ?`,
            [PRODUCT_ID]
        );

        return Number(rows[0]!.in_qty) - Number(rows[0]!.out_qty);
    }

    /** Removes this product's movement history and the transactions behind it. */
    async function clearMovements(): Promise<void> {
        await execute(`DELETE FROM ${prefix()}erp_acct_product_details WHERE product_id = ?`, [PRODUCT_ID]);
        await execute(`DELETE FROM ${prefix()}erp_acct_product_price WHERE product_id = ?`, [PRODUCT_ID]);
    }

    test.beforeAll(async () => {
        customerId = await customerIdFor(CUSTOMER);
        vendorId = await vendorIdFor(VENDOR);
        void vendorId;
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        page.watchServerErrors();

        await cleanupInvoices(customerId);
        await cleanupLedgerOrphans();
        await clearMovements();
    });

    test.afterAll(async () => {
        await cleanupInvoices(customerId);
        await cleanupLedgerOrphans();
        await clearMovements();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the inventory register lists products with their stock', { tag: ['@tier1', '@pro', '@inventory', '@smoke'] }, async () => {
        await page.gotoRoute('inventory');

        expect(await page.headings(), 'the register renders').toContain('Inventory Products');

        const table = await page.reportText();
        expect(table, 'and lists this product').toContain(PRODUCT);
    });

    test('selling a product takes it out of stock', { tag: ['@tier1', '@pro', '@inventory', '@flow'] }, async () => {
        expect(await onHand(), 'precondition: this product has no movement history').toBe(0);

        await page.gotoRoute('newInvoice');
        await page.pickFromMultiselect('Customer', CUSTOMER);
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineProduct(0, PRODUCT);
        await page.setLineQty(0, 4);
        await page.save();

        const invoices = await query<RowDataPacket[]>(
            `SELECT voucher_no FROM ${prefix()}erp_acct_invoices WHERE customer_id = ? ORDER BY id DESC LIMIT 1`,
            [customerId]
        );
        expect(invoices, 'precondition: the invoice was raised').toHaveLength(1);

        expect(await onHand(), 'four units left stock').toBe(-4);
    });

    test.fail(
        'the purchase form offers the products it demands',
        { tag: ['@tier1', '@pro', '@inventory', '@known-defect'] },
        async ({ page: p }) => {
            // KNOWN DEFECT — ERP-160. New Purchase never requests `/products`,
            // so all three Product/Service pickers are empty, and saving then
            // refuses with "Please select a product." for a product the form
            // never offered. New Purchase Order behaves identically.
            //
            // The precondition asserts the INVOICE form does load products from
            // the same endpoint, so this guard can only fail on the purchase
            // form's own omission.
            await page.gotoRoute('newInvoice');
            const invoiceLine = p.locator('.multiselect').nth(1);
            await invoiceLine.click();
            await p.waitForTimeout(800);
            const invoiceOptions = (await invoiceLine.locator('.multiselect__option').allTextContents())
                .map((t) => t.trim())
                .filter((t) => t && !/Oops|List is empty/i.test(t));
            expect(invoiceOptions.length, 'precondition: the invoice form offers products').toBeGreaterThan(0);

            await page.gotoRoute('newPurchase');
            const purchaseLine = p.locator('.multiselect').nth(1);
            await purchaseLine.click();
            await p.waitForTimeout(800);
            const purchaseOptions = (await purchaseLine.locator('.multiselect__option').allTextContents())
                .map((t) => t.trim())
                .filter((t) => t && !/Oops|List is empty/i.test(t));

            expect(purchaseOptions.length, 'the purchase form offers products too').toBeGreaterThan(0);
        }
    );

    test.fail(
        'buying a product puts it into stock',
        { tag: ['@tier1', '@pro', '@inventory', '@flow', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-160, the consequence that matters for this
            // module: with purchases unrecordable, stock can only ever go DOWN.
            // Nothing in the product can replenish it.
            expect(await onHand(), 'precondition: this product has no movement history').toBe(0);

            expect(
                await page.createPurchase(VENDOR, PRODUCT, 10, UNIT_PRICE, toDate(), dateOffset(30)),
                'the purchase form accepted the entry'
            ).toBe(true);

            expect(await onHand(), 'ten units entered stock').toBe(10);
        }
    );

    // ---- Tier 2 ----------------------------------------------------------

    test('the register prints the stock the movements add up to', { tag: ['@tier2', '@pro', '@inventory'] }, async () => {
        // Sale-only arithmetic, because purchases cannot be recorded at all
        // (ERP-160). Sell four and the register must print -4 — the figure a
        // stock keeper would act on, read off the SCREEN rather than the table
        // behind it.
        await page.gotoRoute('newInvoice');
        await page.pickFromMultiselect('Customer', CUSTOMER);
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineProduct(0, PRODUCT);
        await page.setLineQty(0, 4);
        await page.save();

        expect(await onHand(), 'precondition: the movements net to minus four').toBe(-4);

        await page.gotoRoute('inventory');
        const table = (await page.reportText()).replace(/\s+/g, ' ');
        const row = table.slice(table.indexOf(PRODUCT), table.indexOf(PRODUCT) + 160);

        expect(row, `the register prints -4 for this product, row read: ${row}`).toContain('-4');
    });
    test('the inventory register is closed to an employee', { tag: ['@tier3', '@pro', '@inventory', '@authz'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const p = await context.newPage();
        const asEmployee = new AccountingPage(p);

        await asEmployee.gotoRoute('inventory');

        const reachable = await p.evaluate(() => Boolean((window as unknown as { erp_acct_var?: unknown }).erp_acct_var));

        expect(reachable, 'the accounting app never loads for an employee').toBe(false);

        await context.close();
    });
});
