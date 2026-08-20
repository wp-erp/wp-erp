import { test, expect } from '@utils/test';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ADMIN_STATE } from '@utils/authStates';
import { toDate, dateOffset, dbDate } from '@utils/helpers';
import { cleanupBills, cleanupPayBills, cleanupLedgerOrphans, vendorIdFor } from '@utils/cleanupAccounting';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * The vendor side: bill → pay bill.
 *
 * It mirrors the customer side but is NOT a copy of it, and the differences are
 * the whole reason this file exists rather than a parameterised version of
 * `payments.spec.ts`:
 *
 * - A bill's line items are **ledger accounts**, not products — the vendor side
 *   charges an expense account directly.
 * - The signs invert. A bill CREDITS the vendor (we owe them) and a payment
 *   DEBITS them, the opposite of an invoice and its receipt.
 * - The line amount is `input[name="amount"]` and its grand total recomputes on
 *   **keyup** only, so it must be typed.
 * - Paying a bill needs **funds**, and this site seeds no opening balances.
 *
 * **Everything that touches the Cash ledger lives in `payments.spec.ts`,
 * including bill PAYMENT.** Cash is global state that no customer or vendor
 * scoping can isolate: with these files running in parallel, one spec's funding
 * broke another's "account is empty" precondition and one spec's cleanup emptied
 * the account another was about to pay from. Within a single file the cases run
 * serially and the balance is deterministic. This file therefore covers bill
 * CREATION and its validation only.
 *
 * **This file owns Bluewave Cloud Services** — the other accounting specs own
 * Verdant Foods, Harbourline Logistics and Kestrel Manufacturing.
 */
const VENDOR = 'Bluewave Cloud Services';
const EXPENSE_ACCOUNT = 'Advertising';

test.describe('Accounting — bills and bill payments', () => {
    let page: AccountingPage;
    let vendorId = 0;

    /** What the vendor is owed: credits raised minus debits paid. */
    async function owedToVendor(): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(credit), 0) AS credits, COALESCE(SUM(debit), 0) AS debits
               FROM ${prefix()}erp_acct_people_trn_details WHERE people_id = ?`,
            [vendorId]
        );

        return Number(rows[0]!.credits) - Number(rows[0]!.debits);
    }

    test.beforeAll(async () => {
        vendorId = await vendorIdFor(VENDOR);
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        page.watchServerErrors();

        // Payments before their parents, both sides, then the orphan sweep.
        await cleanupPayBills(vendorId);
        await cleanupBills(vendorId);
        await cleanupLedgerOrphans();
    });

    test.afterAll(async () => {
        await cleanupPayBills(vendorId);
        await cleanupBills(vendorId);
        await cleanupLedgerOrphans();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('a bill is created and credited to the vendor', { tag: ['@tier1', '@accounting', '@crud', '@flow'] }, async () => {
        const billDate = toDate();
        const dueDate = dateOffset(30);

        expect(
            await page.createBill(VENDOR, EXPENSE_ACCOUNT, 750, billDate, dueDate),
            'the bill form accepted the entry'
        ).toBe(true);

        const bills = await query<RowDataPacket[]>(
            `SELECT id, voucher_no, vendor_name, trn_date, due_date, amount FROM ${prefix()}erp_acct_bills WHERE vendor_id = ? ORDER BY id DESC`,
            [vendorId]
        );

        expect(bills, 'exactly one bill is written').toHaveLength(1);
        expect(String(bills[0]!.vendor_name), 'against the chosen vendor').toBe(VENDOR);
        expect(dbDate(bills[0]!.trn_date), 'with the bill date chosen').toBe(billDate);
        expect(dbDate(bills[0]!.due_date), 'and the due date chosen').toBe(dueDate);
        expect(Number(bills[0]!.amount), 'for the amount entered').toBeCloseTo(750, 2);

        // A bill CREDITS the vendor — the mirror of an invoice debiting a customer.
        expect(await owedToVendor(), 'and the vendor is owed that amount').toBeCloseTo(750, 2);
    });

    test('a bill posts a balanced double entry', { tag: ['@tier1', '@accounting', '@flow', '@money'] }, async () => {
        await page.createBill(VENDOR, EXPENSE_ACCOUNT, 750, toDate(), dateOffset(30));

        const bills = await query<RowDataPacket[]>(
            `SELECT voucher_no, amount FROM ${prefix()}erp_acct_bills WHERE vendor_id = ? ORDER BY id DESC LIMIT 1`,
            [vendorId]
        );
        expect(bills, 'precondition: the bill was created').toHaveLength(1);

        const voucher = Number(bills[0]!.voucher_no);

        // Same split as an invoice, mirrored: the expense debit lands in
        // `ledger_details`, the payable credit in `bill_account_details`.
        const expense = await query<RowDataPacket[]>(
            `SELECT debit, credit FROM ${prefix()}erp_acct_ledger_details WHERE trn_no = ?`,
            [voucher]
        );
        const payable = await query<RowDataPacket[]>(
            `SELECT debit, credit FROM ${prefix()}erp_acct_bill_account_details WHERE bill_no = ?`,
            [voucher]
        );

        expect(expense.length, 'the bill posts an expense entry').toBeGreaterThan(0);
        expect(payable.length, 'and a payable entry').toBeGreaterThan(0);

        const sum = (rows: RowDataPacket[], column: 'debit' | 'credit'): number =>
            rows.reduce((total, row) => total + Number(row[column]), 0);

        const debits = sum(expense, 'debit') + sum(payable, 'debit');
        const credits = sum(expense, 'credit') + sum(payable, 'credit');

        expect(debits, 'debits equal credits across the posting').toBeCloseTo(credits, 2);
        expect(debits, 'and the posting is for the bill amount').toBeCloseTo(Number(bills[0]!.amount), 2);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('a bill with no vendor is refused', { tag: ['@tier3', '@accounting', '@validation'] }, async () => {
        await page.gotoRoute('newBill');
        await page.pickDate('Bill Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineAccount(0, EXPENSE_ACCOUNT);
        await page.setLineAmount(0, 500);
        await page.save();

        expect(await page.bodyText(), 'the form names the missing field').toContain('People Name is required');

        const bills = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_bills WHERE vendor_id = ?`, [
            vendorId,
        ]);
        expect(Number(bills[0]!.n), 'and nothing is written').toBe(0);
    });

    test('a bill with no amount never leaves the browser', { tag: ['@tier3', '@accounting', '@validation'] }, async () => {
        // The refusal here is the BROWSER's, not the product's, and saying
        // otherwise would overstate what was tested. Picking an account sets
        // `:required` on that line's amount (`BillCreate.vue:73`), so constraint
        // validation blocks the submit and the server is never asked — no request
        // is sent and the Vue error panel never renders.
        //
        // Same shape as the CRM companies life-stage case. The product's own
        // "Total amount can't be zero" rule is real but sits behind this, and is
        // reachable only on a line with no account at all.
        await page.gotoRoute('newBill');
        await page.pickFromMultiselect('Pay To', VENDOR);
        await page.pickDate('Bill Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineAccount(0, EXPENSE_ACCOUNT);
        await page.save();

        expect(await page.invalidAmountCount(), 'the browser marks the amount invalid').toBeGreaterThan(0);
        expect(await page.amountValidationMessage(), 'and says why').toMatch(/fill out this field/i);

        const bills = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_bills WHERE vendor_id = ?`, [
            vendorId,
        ]);
        expect(Number(bills[0]!.n), 'and nothing is written').toBe(0);
    });

    test.fail(
        'creating a bill answers with success, not a server error',
        { tag: ['@tier1', '@accounting', '@flow', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-150 / erp-pro#967, now confirmed to cover the
            // WHOLE transaction family: invoices, payments, bills and pay-bills
            // all answer 500 from the same `get_magic_quotes_runtime()` call in
            // erp-pdf-invoice, while the record itself commits correctly.
            expect(
                await page.createBill(VENDOR, EXPENSE_ACCOUNT, 750, toDate(), dateOffset(30)),
                'precondition: the bill form accepted the entry'
            ).toBe(true);

            const bills = await query<RowDataPacket[]>(
                `SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_bills WHERE vendor_id = ?`,
                [vendorId]
            );
            expect(Number(bills[0]!.n), 'precondition: the bill was written despite the response').toBeGreaterThan(0);

            expect(page.serverErrorList(), 'the create answers without a server error').toEqual([]);
        }
    );
});
