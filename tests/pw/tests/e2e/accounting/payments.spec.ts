import { test, expect } from '@utils/test';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ADMIN_STATE } from '@utils/authStates';
import { toDate, dateOffset } from '@utils/helpers';
import {
    cleanupInvoices,
    cleanupPayments,
    cleanupBills,
    cleanupPayBills,
    cleanupPayPurchases,
    cleanupPurchases,
    cleanupLedgerOrphans,
    customerIdFor,
    vendorIdFor,
} from '@utils/cleanupAccounting';
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

/**
 * The same product, by id, for the REST purchase seed. Deliberately NOT product
 * 1 — `inventory.spec.ts` owns that one and asserts on its stock, and a purchase
 * is the only thing in ERP that writes `stock_in`.
 */
const PRODUCT_ID_FOR_PURCHASE = 6;

/**
 * Bill payment lives HERE, not in `bills.spec.ts`, because it spends the same
 * Cash ledger these customer payments deposit into. Cash is global state that no
 * customer or vendor scoping can isolate — run in parallel, one file's funding
 * broke the other's "the account is empty" precondition, and one file's cleanup
 * emptied the account the other was about to pay from. Inside one file the cases
 * are serial and the balance is deterministic.
 *
 * It uses its OWN vendor. The first attempt at this fix reused `bills.spec.ts`'s
 * vendor, so both files cleaned the same bills and destroyed each other's rows —
 * trading one collision for another. One party per file, always.
 */
const VENDOR = 'Meridian Office Supplies';
const EXPENSE_ACCOUNT = 'Advertising';

/** The Cash ledger — what "Deposit to" and "Transaction From" use by default. */
const CASH_LEDGER_ID = 7;

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
    let vendorId = 0;

    test.beforeAll(async () => {
        customerId = await customerIdFor(CUSTOMER);
        vendorId = await vendorIdFor(VENDOR);

        // Payments first: a receipt points at the invoice it settles.
        await cleanupPayments(customerId);
        await cleanupInvoices(customerId);
        await cleanupPayBills(vendorId);
        await cleanupBills(vendorId);
        await cleanupPayPurchases(vendorId);
        await cleanupPurchases(vendorId);
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
        await cleanupPayBills(vendorId);
        await cleanupBills(vendorId);
        await cleanupPayPurchases(vendorId);
        await cleanupPurchases(vendorId);
        await cleanupLedgerOrphans();
    });

    test.afterAll(async () => {
        await cleanupPayments(customerId);
        await cleanupInvoices(customerId);
        await cleanupPayBills(vendorId);
        await cleanupBills(vendorId);
        await cleanupPayPurchases(vendorId);
        await cleanupPurchases(vendorId);
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

    // ---- the vendor side of the same Cash ledger ---------------------------

    async function cashBalance(): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(debit) - SUM(credit), 0) AS balance
               FROM ${prefix()}erp_acct_ledger_details WHERE ledger_id = ?`,
            [CASH_LEDGER_ID]
        );

        return Number(rows[0]!.balance);
    }

    /** What the vendor is owed: credits raised minus debits paid. */
    async function owedToVendor(): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(credit), 0) AS credits, COALESCE(SUM(debit), 0) AS debits
               FROM ${prefix()}erp_acct_people_trn_details WHERE people_id = ?`,
            [vendorId]
        );

        return Number(rows[0]!.credits) - Number(rows[0]!.debits);
    }

    test('a bill cannot be paid from an account with no funds', { tag: ['@tier2', '@accounting', '@validation', '@money'] }, async () => {
        await page.createBill(VENDOR, EXPENSE_ACCOUNT, 750, toDate(), dateOffset(30));

        expect(await cashBalance(), 'precondition: cash is empty').toBeCloseTo(0, 2);

        await page.payBill(VENDOR, toDate());

        expect(await page.bodyText(), 'the product refuses and says why').toContain('Not enough balance in selected account');

        const payments = await query<RowDataPacket[]>(
            `SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_pay_bill WHERE vendor_id = ?`,
            [vendorId]
        );
        expect(Number(payments[0]!.n), 'and nothing is paid').toBe(0);
        expect(await owedToVendor(), 'so the vendor is still owed the bill').toBeCloseTo(750, 2);
    });

    test('paying a bill in full clears the vendor balance', { tag: ['@tier1', '@accounting', '@flow', '@money'] }, async () => {
        // Money in, then money out. This site seeds no opening balances, so the
        // cash to pay a bill is collected from a customer first — which makes
        // this a real end-to-end cycle rather than a mocked balance.
        await page.createBill(VENDOR, EXPENSE_ACCOUNT, 750, toDate(), dateOffset(30));
        expect(await owedToVendor(), 'precondition: the vendor is owed the bill').toBeCloseTo(750, 2);

        await raiseInvoice(2);
        expect(await page.receivePayment(CUSTOMER, toDate()), 'precondition: cash was collected').toBe(true);
        expect(await cashBalance(), 'precondition: cash is funded').toBeGreaterThanOrEqual(750);

        expect(await page.payBill(VENDOR, toDate()), 'the pay-bill form accepted the entry').toBe(true);

        const payments = await query<RowDataPacket[]>(
            `SELECT voucher_no, amount FROM ${prefix()}erp_acct_pay_bill WHERE vendor_id = ? ORDER BY id DESC LIMIT 1`,
            [vendorId]
        );

        expect(payments, 'a bill payment is written').toHaveLength(1);
        expect(Number(payments[0]!.amount), 'for the bill amount').toBeCloseTo(750, 2);
        expect(await owedToVendor(), 'and the vendor is owed nothing').toBeCloseTo(0, 2);
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

    // ---- the edit path -----------------------------------------------------
    //
    // Raising a payment is only half of the cycle: an amount keyed wrong has to
    // be correctable. These cases follow the correction all the way through,
    // and they split into two independent failures — the screen that cannot
    // load a payment (ERP-153) and the server route that corrupts one when it
    // is asked to (ERP-152). They are kept apart because fixing either leaves
    // the other standing.

    /** Raises an invoice, settles it in full, and returns both voucher numbers. */
    async function settledInvoice(): Promise<{ invoiceVoucher: number; paymentVoucher: number; amount: number }> {
        const invoice = await raiseInvoice();

        expect(await page.receivePayment(CUSTOMER, toDate()), 'precondition: the payment form accepted the entry').toBe(
            true
        );

        const receipts = await query<RowDataPacket[]>(
            `SELECT voucher_no FROM ${prefix()}erp_acct_invoice_receipts WHERE customer_id = ${customerId} ORDER BY id DESC LIMIT 1`
        );
        expect(receipts, 'precondition: a receipt exists to edit').toHaveLength(1);

        return {
            invoiceVoucher: invoice.voucherNo,
            paymentVoucher: Number(receipts[0]!.voucher_no),
            amount: invoice.amount,
        };
    }

    /** The receipt header's own recorded amount. */
    async function receiptAmount(voucherNo: number): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT amount FROM ${prefix()}erp_acct_invoice_receipts WHERE voucher_no = ?`,
            [voucherNo]
        );

        return Number(rows[0]!.amount);
    }

    test('the payment edit screen opens', { tag: ['@tier2', '@accounting', '@flow'] }, async () => {
        // The canary for the two guards below: it proves the route resolves and
        // the component paints, so a failure there is about the payment not
        // being loaded, never about the screen being unreachable.
        const settled = await settledInvoice();

        await page.gotoPaymentEdit(settled.paymentVoucher);

        expect(await page.headings(), 'the edit route renders the payment screen').toContain('Payment');
    });

    test.fail(
        'the payment edit screen loads the payment it names',
        { tag: ['@tier2', '@accounting', '@flow', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-153. `#/payments/{id}/edit` reuses
            // `RecPaymentCreate.vue`, which asks for `GET /invoices/{id}` with
            // the PAYMENT's voucher number. No invoice carries that number, so
            // the response has no line items, the component bails with
            // "Invoice does not exists!" and never calls `setDataForEdit` —
            // which the file does not define in any case. Every field renders
            // empty, and Save then POSTs a brand new payment.
            const settled = await settledInvoice();

            await page.gotoPaymentEdit(settled.paymentVoucher);

            expect(await page.headings(), 'precondition: the edit route rendered').toContain('Payment');

            expect(
                await page.multiselectValue('Customer'),
                'the screen shows the customer whose payment is being edited'
            ).toContain(CUSTOMER);
        }
    );

    test.fail(
        'editing a payment down leaves the cash ledger holding both amounts',
        { tag: ['@tier2', '@accounting', '@money', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-152, and the most damaging thing in this file
            // after ERP-151. `erp_acct_update_payment()` calls
            // `erp_acct_update_payment_line_items( $payment_data, $voucher_no,
            // $invoice_no[$key] )` while the function is declared
            // `( $data, $invoice_no, $voucher_no )` — the last two arguments are
            // swapped, and `$invoice_no[$key]` reads `$item['invoice_id']`,
            // a key the payload never carries. Every UPDATE inside therefore
            // matches nothing, and the ledger write is
            // `erp_acct_insert_payment_data_into_ledger()` — an INSERT — so the
            // edit ADDS a second cash row instead of correcting the first.
            //
            // Everything before the last assertion is a precondition, so this
            // guard can only fail for the reason it names.
            const settled = await settledInvoice();
            const half = Math.round((settled.amount / 2) * 100) / 100;

            expect(await cashBalance(), 'precondition: cash holds the payment as received').toBeCloseTo(
                settled.amount,
                2
            );

            const response = await page.updatePaymentViaRest(settled.paymentVoucher, {
                customerId,
                trnDate: toDate(),
                depositTo: CASH_LEDGER_ID,
                lineItems: [{ invoiceNo: settled.invoiceVoucher, lineTotal: half }],
            });

            expect(response.status, 'precondition: the server accepted the edit').toBe(200);
            expect(await receiptAmount(settled.paymentVoucher), 'precondition: the receipt now records the new amount').toBeCloseTo(
                half,
                2
            );

            // The defect: cash holds the old amount PLUS the new one.
            expect(await cashBalance(), 'cash holds only the corrected amount').toBeCloseTo(half, 2);
        }
    );

    test.fail(
        'editing a payment down puts the difference back on the customer',
        { tag: ['@tier2', '@accounting', '@money', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-152, second face. The update path never touches
            // `erp_acct_people_trn_details` at all — `erp_acct_update_payment()`
            // does not mention it, and the one helper that could
            // (`erp_acct_update_payment_data_in_ledger()`) has no callers
            // anywhere in the plugin. Halve a payment and the customer is still
            // credited with the whole of it, so the money is owed by nobody.
            const settled = await settledInvoice();
            const half = Math.round((settled.amount / 2) * 100) / 100;

            expect(await outstandingFor(customerId), 'precondition: the invoice is settled in full').toBeCloseTo(0, 2);

            const response = await page.updatePaymentViaRest(settled.paymentVoucher, {
                customerId,
                trnDate: toDate(),
                depositTo: CASH_LEDGER_ID,
                lineItems: [{ invoiceNo: settled.invoiceVoucher, lineTotal: half }],
            });

            expect(response.status, 'precondition: the server accepted the edit').toBe(200);
            expect(await receiptAmount(settled.paymentVoucher), 'precondition: the receipt now records the new amount').toBeCloseTo(
                half,
                2
            );

            // The defect: the customer's ledger still shows the original credit.
            expect(await outstandingFor(customerId), 'the customer owes the half that was un-paid').toBeCloseTo(half, 2);
        }
    );

    // ---- the bill-payment edit path ---------------------------------------
    //
    // The money-out mirror of the section above, and the worst of the three edit
    // paths measured so far. It is also the only one with NO screen behind it:
    // the router has no `:id/edit` child for pay-bills and the transaction list
    // offers a bill payment only **Void**, so `PUT /accounting/v1/pay-bills/{id}`
    // is reachable by an API consumer and nobody else. All four guards below are
    // ERP-156 — one function, four ways of being wrong.

    /** Raises one bill, pays it in full, and returns both voucher numbers. */
    async function settledBill(amount = 750): Promise<{ billVoucher: number; payVoucher: number; amount: number }> {
        await raiseInvoice(2);
        expect(await page.receivePayment(CUSTOMER, toDate()), 'precondition: cash was collected').toBe(true);

        expect(await page.createBill(VENDOR, EXPENSE_ACCOUNT, amount, toDate(), dateOffset(30)), 'precondition: a bill exists').toBe(
            true
        );

        const bills = await query<RowDataPacket[]>(
            `SELECT voucher_no FROM ${prefix()}erp_acct_bills WHERE vendor_id = ? ORDER BY id DESC LIMIT 1`,
            [vendorId]
        );
        expect(bills, 'precondition: the bill was written').toHaveLength(1);

        expect(await page.payBill(VENDOR, toDate()), 'precondition: the bill was paid').toBe(true);

        const pays = await query<RowDataPacket[]>(
            `SELECT voucher_no FROM ${prefix()}erp_acct_pay_bill WHERE vendor_id = ? ORDER BY id DESC LIMIT 1`,
            [vendorId]
        );
        expect(pays, 'precondition: the bill payment was written').toHaveLength(1);

        return { billVoucher: Number(bills[0]!.voucher_no), payVoucher: Number(pays[0]!.voucher_no), amount };
    }

    /** What a bill payment records for itself. */
    async function payBillAmount(voucherNo: number): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT amount FROM ${prefix()}erp_acct_pay_bill WHERE voucher_no = ?`,
            [voucherNo]
        );

        return Number(rows[0]!.amount);
    }

    test.fail(
        'editing a bill payment changes the payment itself',
        { tag: ['@tier2', '@accounting', '@money', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-156. `erp_acct_update_pay_bill()`
            // (`pay-bills.php:249`) writes `bill_no` and `type` into
            // `erp_acct_pay_bill`, and that table has neither column. MySQL
            // rejects the whole statement, `$wpdb->update()` returns false
            // without throwing, and the function carries on and COMMITs — so the
            // payment keeps its original amount and date while everything
            // downstream of it moves.
            const settled = await settledBill();
            const reduced = settled.amount / 3;

            expect(await payBillAmount(settled.payVoucher), 'precondition: the payment records the full bill').toBeCloseTo(
                settled.amount,
                2
            );

            const response = await page.updatePayBillViaRest(settled.payVoucher, {
                vendorId,
                trnDate: toDate(),
                depositTo: CASH_LEDGER_ID,
                lines: [{ billNo: settled.billVoucher, amount: reduced }],
            });

            expect(response.status, 'precondition: the server accepted the edit').toBe(200);

            // The defect: the payment still records the old figure.
            expect(await payBillAmount(settled.payVoucher), 'the payment records the amount it was edited to').toBeCloseTo(
                reduced,
                2
            );
        }
    );

    test.fail(
        'editing a bill payment does not hand the money back',
        { tag: ['@tier2', '@accounting', '@money', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-156, and the most damaging face of it.
            // `PayBillsController::update_pay_bill()` sums `$item['total']` for
            // the payment amount, but the product's own pay-bill form sends
            // `amount` and never `total` (`PayBillCreate.vue:305`). An integration
            // built from that create payload therefore makes the server read an
            // undefined key, `array_sum()` returns 0, and
            // `erp_acct_update_pay_bill_data_into_ledger()` writes a cash credit
            // of ZERO — returning the entire payment to cash while the payment
            // record itself still stands.
            const settled = await settledBill();

            const before = await cashBalance();

            const response = await page.updatePayBillViaRest(settled.payVoucher, {
                vendorId,
                trnDate: toDate(),
                depositTo: CASH_LEDGER_ID,
                lines: [{ billNo: settled.billVoucher, amount: settled.amount / 3 }],
                includeTotal: false,
            });

            expect(response.status, 'precondition: the server accepted the edit').toBe(200);

            // The defect: cash goes UP by the whole original payment.
            expect(await cashBalance(), 'cash does not rise when a payment is edited downwards').toBeLessThanOrEqual(
                before
            );
        }
    );

    test.fail(
        'editing a bill payment keeps its bills apart',
        { tag: ['@tier2', '@accounting', '@money', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-156. The line loop in
            // `erp_acct_update_pay_bill()` runs `$wpdb->update()` with the SAME
            // WHERE clause on every pass — `voucher_no = $pay_bill_id` for the
            // details and `trn_no = $pay_bill_id` for the account details, never
            // the individual bill — so each iteration rewrites EVERY row of the
            // payment. After the loop all of them carry the last line's bill and
            // amount, and the other bills lose their payment entirely.
            await raiseInvoice(3);
            expect(await page.receivePayment(CUSTOMER, toDate()), 'precondition: cash was collected').toBe(true);

            expect(await page.createBill(VENDOR, EXPENSE_ACCOUNT, 750, toDate(), dateOffset(30))).toBe(true);
            expect(await page.createBill(VENDOR, EXPENSE_ACCOUNT, 400, toDate(), dateOffset(30))).toBe(true);

            const bills = await query<RowDataPacket[]>(
                `SELECT voucher_no FROM ${prefix()}erp_acct_bills WHERE vendor_id = ? ORDER BY id`,
                [vendorId]
            );
            expect(bills, 'precondition: two bills are outstanding').toHaveLength(2);

            expect(await page.payBill(VENDOR, toDate()), 'precondition: both were paid together').toBe(true);

            const pays = await query<RowDataPacket[]>(
                `SELECT voucher_no FROM ${prefix()}erp_acct_pay_bill WHERE vendor_id = ? ORDER BY id DESC LIMIT 1`,
                [vendorId]
            );
            const payVoucher = Number(pays[0]!.voucher_no);

            const response = await page.updatePayBillViaRest(payVoucher, {
                vendorId,
                trnDate: toDate(),
                depositTo: CASH_LEDGER_ID,
                lines: [
                    { billNo: Number(bills[0]!.voucher_no), amount: 700 },
                    { billNo: Number(bills[1]!.voucher_no), amount: 100 },
                ],
            });

            expect(response.status, 'precondition: the server accepted the edit').toBe(200);

            const applied = await query<RowDataPacket[]>(
                `SELECT bill_no FROM ${prefix()}erp_acct_pay_bill_details WHERE voucher_no = ?`,
                [payVoucher]
            );
            expect(applied, 'precondition: the payment still names two lines').toHaveLength(2);

            // The defect: both lines now point at the same bill.
            expect(
                new Set(applied.map((row) => Number(row.bill_no))).size,
                'the payment still names two different bills'
            ).toBe(2);
        }
    );

    test.fail(
        'editing a bill payment moves the vendor balance with it',
        { tag: ['@tier2', '@accounting', '@money', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-156. `erp_acct_update_pay_bill()` never mentions
            // `erp_acct_people_trn_details`, which the create path does write
            // (`pay-bills.php:213`). The vendor's statement therefore keeps
            // showing the original payment however the payment is edited.
            const settled = await settledBill();
            const reduced = settled.amount / 3;

            expect(await owedToVendor(), 'precondition: the bill was settled in full').toBeCloseTo(0, 2);

            const response = await page.updatePayBillViaRest(settled.payVoucher, {
                vendorId,
                trnDate: toDate(),
                depositTo: CASH_LEDGER_ID,
                lines: [{ billNo: settled.billVoucher, amount: reduced }],
            });

            expect(response.status, 'precondition: the server accepted the edit').toBe(200);

            // The defect: the vendor is still credited with the whole payment.
            expect(await owedToVendor(), 'the vendor is owed the part that is no longer paid').toBeCloseTo(
                settled.amount - reduced,
                2
            );
        }
    );
    // ---- pay purchase ------------------------------------------------------
    //
    // Purchase payment lives here for the same reason bill payment does: it
    // spends the Cash ledger, which is global state this file owns. The
    // purchase it settles has to be SEEDED over REST — New Purchase cannot save
    // at all (ERP-160, erp-pro#978) — and that seed answers 500 while
    // committing (erp-pro#967), which is asserted in `purchases.spec.ts` rather
    // than repeated here. What is tested here is the part that genuinely works:
    // the pay-purchase screen.

    /** Raises one purchase over REST and returns its voucher number. */
    async function raisePurchase(amount: number): Promise<number> {
        await page.createPurchaseViaRest(vendorId, VENDOR, PRODUCT_ID_FOR_PURCHASE, 1, amount, toDate(), dateOffset(30));

        const rows = await query<RowDataPacket[]>(
            `SELECT voucher_no FROM ${prefix()}erp_acct_purchase WHERE vendor_id = ? ORDER BY id DESC LIMIT 1`,
            [vendorId]
        );

        return Number(rows[0]!.voucher_no);
    }

    /** What is still owed on a purchase: credits raised, less debits paid. */
    async function owedOnPurchase(voucherNo: number): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(credit) - SUM(debit), 0) AS due
               FROM ${prefix()}erp_acct_purchase_account_details WHERE purchase_no = ?`,
            [voucherNo]
        );

        return Number(rows[0]!.due);
    }

    test('paying a purchase in full clears its balance and spends the cash', { tag: ['@tier1', '@accounting', '@flow', '@money'] }, async () => {
        const voucherNo = await raisePurchase(300);

        expect(await owedOnPurchase(voucherNo), 'precondition: the purchase is unpaid').toBeCloseTo(300, 2);

        await raiseInvoice(2);
        expect(await page.receivePayment(CUSTOMER, toDate()), 'precondition: cash was collected').toBe(true);

        const funded = await cashBalance();

        expect(funded, 'precondition: cash covers the purchase').toBeGreaterThanOrEqual(300);

        expect(await page.payPurchase(VENDOR, toDate()), 'the pay-purchase form accepted the entry').toBe(true);

        const payments = await query<RowDataPacket[]>(
            `SELECT voucher_no, purchase_no, amount FROM ${prefix()}erp_acct_pay_purchase_details ORDER BY id DESC LIMIT 1`
        );

        expect(payments, 'a purchase payment is written').toHaveLength(1);
        expect(Number(payments[0]!.purchase_no), 'against the purchase it settles').toBe(voucherNo);
        expect(Number(payments[0]!.amount), 'for the full amount').toBeCloseTo(300, 2);

        expect(await owedOnPurchase(voucherNo), 'the purchase is settled').toBeCloseTo(0, 2);
        expect(await cashBalance(), 'and the cash left the account').toBeCloseTo(funded - 300, 2);
    });

    test('a paid purchase is marked Paid on the transactions screen', { tag: ['@tier2', '@accounting', '@flow'] }, async () => {
        const voucherNo = await raisePurchase(300);

        await raiseInvoice(2);
        await page.receivePayment(CUSTOMER, toDate());

        expect(await page.payPurchase(VENDOR, toDate()), 'precondition: the payment was accepted').toBe(true);
        expect(await owedOnPurchase(voucherNo), 'precondition: the purchase is settled').toBeCloseTo(0, 2);

        await page.gotoRoute('purchases');
        const table = (await page.reportText()).replace(/\s+/g, ' ');
        const marker = `#${voucherNo}`;
        const row = table.slice(table.indexOf(marker), table.indexOf(marker) + 220);

        expect(row, `the purchase row reads Paid, row read: ${row}`).toContain('Paid');
    });
});
