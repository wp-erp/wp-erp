import { test, expect } from '@utils/test';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ADMIN_STATE } from '@utils/authStates';
import { toDate, dateOffset } from '@utils/helpers';
import { cleanupInvoices, cleanupPayments, cleanupLedgerOrphans, customerIdFor } from '@utils/cleanupAccounting';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Invoice → payment settlement: the case the tier-1 plan calls
 * `ACCOUNTING-F1-001`, and the one that decides whether the ledger can be
 * trusted.
 *
 * The oracle is the customer's own transaction ledger,
 * `erp_acct_people_trn_details`: an invoice writes a DEBIT for its amount and a
 * payment writes a CREDIT. Settled in full, the two net to zero. That single
 * sum is what "the customer owes nothing" actually means in this schema, and it
 * is far harder to fake than a status label.
 *
 * Payments need FOUR fields, not the two the screen emphasises — Payment Method
 * and Deposit to are also required, and omitting either makes the form refuse
 * without sending anything.
 */
/**
 * This file OWNS this customer — `transactions.spec.ts` owns Verdant Foods.
 * Both files clean up after themselves, and an unscoped clean in either wiped
 * the other's in-flight invoices on a four-worker run. Scoping by customer is
 * what keeps them independent.
 */
const CUSTOMER = 'Harbourline Logistics';
const PRODUCT = 'Custom Dashboard Build';

/** The customer's outstanding balance: debits raised minus credits paid. */
async function outstandingFor(customerId: number): Promise<number> {
    const rows = await query<RowDataPacket[]>(
        `SELECT COALESCE(SUM(debit), 0) AS debits, COALESCE(SUM(credit), 0) AS credits
           FROM ${prefix()}erp_acct_people_trn_details WHERE people_id = ?`,
        [customerId]
    );

    return Number(rows[0]!.debits) - Number(rows[0]!.credits);
}

test.describe('Accounting — invoice settlement', () => {
    let page: AccountingPage;

    let customerId = 0;

    test.beforeAll(async () => {
        customerId = await customerIdFor(CUSTOMER);

        // Payments first: a receipt points at the invoice it settles.
        await cleanupPayments(customerId);
        await cleanupInvoices(customerId);
        await cleanupLedgerOrphans();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        page.watchServerErrors();

        // Per TEST, not just per file. The payment screen lists EVERY outstanding
        // invoice for the customer and pre-fills each with its full balance, so a
        // balance left behind by an earlier case is silently settled by the next
        // one — which made three of these cases read as ledger defects on the
        // first run. Each case starts from a customer who owes nothing.
        await cleanupPayments(customerId);
        await cleanupInvoices(customerId);
        await cleanupLedgerOrphans();
    });

    test.afterAll(async () => {
        await cleanupPayments(customerId);
        await cleanupInvoices(customerId);
        await cleanupLedgerOrphans();
        await closeDb();
    });

    /** Raises one invoice and returns its id and amount. */
    async function raiseInvoice(qty = 1): Promise<{ id: number; voucherNo: number; amount: number; customerId: number }> {
        await page.gotoRoute('newInvoice');
        await page.pickFromMultiselect('Customer', CUSTOMER);
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineProduct(0, PRODUCT);
        await page.setLineQty(0, qty);
        await page.save();

        const rows = await query<RowDataPacket[]>(
            `SELECT id, voucher_no, amount, customer_id FROM ${prefix()}erp_acct_invoices WHERE customer_id = ${customerId} ORDER BY id DESC LIMIT 1`
        );

        expect(rows, 'precondition: an invoice was raised').toHaveLength(1);

        return {
            id: Number(rows[0]!.id),
            voucherNo: Number(rows[0]!.voucher_no),
            amount: Number(rows[0]!.amount),
            customerId: Number(rows[0]!.customer_id),
        };
    }

    // ---- Tier 1 ----------------------------------------------------------

    test('paying an invoice in full clears the customer balance', { tag: ['@tier1', '@accounting', '@flow', '@money'] }, async () => {
        const invoice = await raiseInvoice();

        expect(await outstandingFor(invoice.customerId), 'the invoice leaves the customer owing its amount').toBeCloseTo(
            invoice.amount,
            2
        );

        expect(await page.receivePayment(CUSTOMER, toDate()), 'the payment form accepted the entry').toBe(true);

        const receipts = await query<RowDataPacket[]>(
            `SELECT voucher_no, amount FROM ${prefix()}erp_acct_invoice_receipts WHERE customer_id = ${customerId} ORDER BY voucher_no DESC LIMIT 1`
        );

        expect(receipts, 'a receipt is written').toHaveLength(1);
        expect(Number(receipts[0]!.amount), 'for the invoice amount').toBeCloseTo(invoice.amount, 2);

        expect(await outstandingFor(invoice.customerId), 'and the customer now owes nothing').toBeCloseTo(0, 2);
    });

    test('a payment is applied to the invoice it settles', { tag: ['@tier1', '@accounting', '@flow', '@money'] }, async () => {
        const invoice = await raiseInvoice();

        expect(await page.receivePayment(CUSTOMER, toDate()), 'the payment form accepted the entry').toBe(true);

        const receipts = await query<RowDataPacket[]>(
            `SELECT voucher_no FROM ${prefix()}erp_acct_invoice_receipts WHERE customer_id = ${customerId} ORDER BY voucher_no DESC LIMIT 1`
        );
        expect(receipts, 'precondition: a receipt exists').toHaveLength(1);

        const applied = await query<RowDataPacket[]>(
            `SELECT invoice_no, amount FROM ${prefix()}erp_acct_invoice_receipts_details WHERE voucher_no = ?`,
            [Number(receipts[0]!.voucher_no)]
        );

        expect(applied, 'the receipt names one invoice').toHaveLength(1);

        // `invoice_no` on a receipt line is the invoice's VOUCHER number, not its
        // primary key. The two diverge — voucher numbers come from a sequence
        // shared across every transaction type, so invoice id 52 carries voucher
        // 64 — and comparing against `id` fails on a perfectly correct row.
        expect(Number(applied[0]!.invoice_no), 'the invoice just raised').toBe(invoice.voucherNo);
        expect(Number(applied[0]!.amount), 'for its full amount').toBeCloseTo(invoice.amount, 2);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('a partial payment leaves exactly the remainder outstanding', { tag: ['@tier2', '@accounting', '@flow', '@money'] }, async () => {
        // ACCOUNTING-F1-002: the case that catches rounding drift, which is the
        // failure mode that quietly corrupts a ledger over time.
        const invoice = await raiseInvoice();
        const half = Math.round((invoice.amount / 2) * 100) / 100;

        expect(await page.receivePayment(CUSTOMER, toDate(), [half]), 'the payment form accepted the entry').toBe(true);

        const remainder = await outstandingFor(invoice.customerId);

        expect(remainder, 'the customer still owes exactly the unpaid half').toBeCloseTo(invoice.amount - half, 2);
        expect(remainder, 'and that is more than nothing').toBeGreaterThan(0);
    });

    test.fail(
        'a payment covering two invoices credits the customer with the full amount',
        { tag: ['@tier2', '@accounting', '@flow', '@money', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-151 / erp-pro#968, and the most serious thing
            // found in Accounting. `erp_acct_insert_payment_data()` resets
            // `$total = 0` INSIDE its line-item loop (`rec-payments.php:170`), so
            // `$payment_data['amount']` ends up holding only the LAST line's
            // total — and that is what gets credited to the customer ledger at
            // `:196`. Pay two invoices in full and the customer is credited for
            // one of them.
            //
            // Preconditions assert the parts that ARE correct, so this guard can
            // only fail for the reason it names: the receipt records the full
            // payment and both invoices are settled.
            const first = await raiseInvoice(1);
            const second = await raiseInvoice(2);
            const total = first.amount + second.amount;

            expect(await outstandingFor(first.customerId), 'precondition: both invoices are outstanding').toBeCloseTo(total, 2);

            expect(await page.receivePayment(CUSTOMER, toDate()), 'the payment form accepted the entry').toBe(true);

            const receipt = await query<RowDataPacket[]>(
                `SELECT voucher_no, amount FROM ${prefix()}erp_acct_invoice_receipts WHERE customer_id = ${customerId} ORDER BY voucher_no DESC LIMIT 1`
            );
            expect(receipt, 'precondition: a receipt was written').toHaveLength(1);
            expect(Number(receipt[0]!.amount), 'precondition: the receipt records the FULL payment').toBeCloseTo(total, 2);

            const applied = await query<RowDataPacket[]>(
                `SELECT amount FROM ${prefix()}erp_acct_invoice_receipts_details WHERE voucher_no = ?`,
                [Number(receipt[0]!.voucher_no)]
            );
            expect(applied, 'precondition: both invoices are on the receipt').toHaveLength(2);

            // The defect: the ledger credit is the last line, not the sum.
            expect(await outstandingFor(first.customerId), 'the customer owes nothing after paying in full').toBeCloseTo(
                0,
                2
            );
            void second;
        }
    );

    // ---- Tier 3 ----------------------------------------------------------

    test('a payment with no method or deposit account is refused', { tag: ['@tier3', '@accounting', '@validation'] }, async () => {
        await raiseInvoice();

        await page.gotoRoute('newPayment');
        await page.pickFromMultiselect('Customer', CUSTOMER);
        await page.pickDate('Payment Date', toDate());

        const before = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_invoice_receipts WHERE customer_id = ${customerId}`);
        await page.save();

        const body = await page.bodyText();
        expect(
            body.includes('Payment Method is required') || body.includes('Deposit Account is required'),
            'the form names the missing fields'
        ).toBe(true);

        const after = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_invoice_receipts WHERE customer_id = ${customerId}`);
        expect(Number(after[0]!.n), 'and no receipt is written').toBe(Number(before[0]!.n));
    });

    test.fail(
        'receiving a payment answers with success, not a server error',
        { tag: ['@tier2', '@accounting', '@flow', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-150 / erp-pro#967, confirmed to extend BEYOND
            // invoices. The original report said the same fatal probably breaks
            // other voucher types but that it was untested; this is the test.
            // `POST erp/v1/accounting/v1/payments` answers 500 from the same
            // `get_magic_quotes_runtime()` call in erp-pdf-invoice, while the
            // receipt itself commits correctly.
            await raiseInvoice();

            expect(await page.receivePayment(CUSTOMER, toDate()), 'precondition: the payment form accepted the entry').toBe(true);

            const receipts = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_invoice_receipts WHERE customer_id = ${customerId}`);
            expect(Number(receipts[0]!.n), 'precondition: the receipt was written despite the response').toBeGreaterThan(0);

            expect(page.serverErrorList(), 'the payment answers without a server error').toEqual([]);
        }
    );
});
