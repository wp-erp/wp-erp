import { test, expect } from '@utils/test';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ADMIN_STATE } from '@utils/authStates';
import { toDate, dateOffset, dbDate } from '@utils/helpers';
import { cleanupInvoices } from '@utils/cleanupAccounting';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * The money path: creating an invoice and checking what it writes.
 *
 * Dates are set through `pickDate()`, which drives the calendar, because typing
 * a date never reaches the form's model (ERP-149 / erp-pro#966). That is a
 * workaround in the harness, not a silence — the defect has its own guard at the
 * bottom of this file.
 *
 * A seeded customer and a seeded product are used read-only; the suite creates
 * only the transaction, and removes it plus its five child tables afterwards.
 */
const CUSTOMER = 'Verdant Foods';
const PRODUCT = 'Custom Dashboard Build';

test.describe('Accounting — transactions', () => {
    let page: AccountingPage;

    test.beforeAll(async () => {
        await cleanupInvoices();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        page.watchServerErrors();
    });

    test.afterAll(async () => {
        await cleanupInvoices();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('an invoice is created and stored against its customer', { tag: ['@tier1', '@accounting', '@crud', '@flow'] }, async () => {
        const trnDate = toDate();
        const dueDate = dateOffset(30);

        await page.gotoRoute('newInvoice');
        expect(await page.pickFromMultiselect('Customer', CUSTOMER), 'precondition: the customer picker works').toBe(true);

        await page.pickDate('Transaction Date', trnDate);
        await page.pickDate('Due Date', dueDate);

        expect(await page.dateValue('Transaction Date'), 'the calendar filled the transaction date').toBe(trnDate);
        expect(await page.dateValue('Due Date'), 'and the due date').toBe(dueDate);

        expect(await page.pickLineProduct(0, PRODUCT), 'precondition: the product picker works').toBe(true);
        await page.setLineQty(0, 2);

        await page.save();

        const invoices = await query<RowDataPacket[]>(
            `SELECT id, customer_name, trn_date, due_date, amount, status FROM ${prefix()}erp_acct_invoices ORDER BY id DESC`
        );

        expect(invoices, 'exactly one invoice is written').toHaveLength(1);

        const invoice = invoices[0]!;
        expect(String(invoice.customer_name), 'against the chosen customer').toBe(CUSTOMER);
        expect(dbDate(invoice.trn_date), 'with the transaction date chosen').toBe(trnDate);
        expect(dbDate(invoice.due_date), 'and the due date chosen').toBe(dueDate);
        expect(Number(invoice.amount), 'and a non-zero amount').toBeGreaterThan(0);
    });

    test('an invoice line records the quantity and unit price it charged', { tag: ['@tier1', '@accounting', '@flow'] }, async () => {
        await page.gotoRoute('newInvoice');
        await page.pickFromMultiselect('Customer', CUSTOMER);
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineProduct(0, PRODUCT);
        await page.setLineQty(0, 3);
        await page.save();

        const invoices = await query<RowDataPacket[]>(`SELECT id, amount FROM ${prefix()}erp_acct_invoices ORDER BY id DESC LIMIT 1`);
        expect(invoices, 'precondition: the invoice was created').toHaveLength(1);

        const lines = await query<RowDataPacket[]>(
            `SELECT qty, unit_price, item_total FROM ${prefix()}erp_acct_invoice_details WHERE trn_no = ?`,
            [Number(invoices[0]!.id)]
        );

        expect(lines, 'one line item is written').toHaveLength(1);
        expect(Number(lines[0]!.qty), 'with the quantity entered').toBe(3);
        expect(Number(lines[0]!.unit_price), 'and a unit price taken from the product').toBeGreaterThan(0);

        // The arithmetic the customer is billed on — the line must agree with
        // itself before anything downstream can be trusted.
        expect(Number(lines[0]!.item_total), 'and a line total of qty x unit price').toBeCloseTo(
            Number(lines[0]!.qty) * Number(lines[0]!.unit_price),
            2
        );
        expect(Number(invoices[0]!.amount), 'which is what the invoice totals to').toBeCloseTo(Number(lines[0]!.item_total), 2);
    });

    test('an invoice posts a balanced double entry to the ledger', { tag: ['@tier1', '@accounting', '@flow', '@money'] }, async () => {
        await page.gotoRoute('newInvoice');
        await page.pickFromMultiselect('Customer', CUSTOMER);
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineProduct(0, PRODUCT);
        await page.setLineQty(0, 1);
        await page.save();

        const invoices = await query<RowDataPacket[]>(`SELECT id, amount FROM ${prefix()}erp_acct_invoices ORDER BY id DESC LIMIT 1`);
        expect(invoices, 'precondition: the invoice was created').toHaveLength(1);

        const invoiceId = Number(invoices[0]!.id);

        // The double entry SPANS TWO TABLES, which is the thing to know before
        // writing an oracle here: the receivable (debit) lands in
        // `invoice_account_details` and the income (credit) in `ledger_details`.
        // Summing either one alone looks unbalanced and reads like a defect —
        // my first version of this case asserted exactly that and was wrong.
        const receivable = await query<RowDataPacket[]>(
            `SELECT debit, credit FROM ${prefix()}erp_acct_invoice_account_details WHERE invoice_no = ?`,
            [invoiceId]
        );
        const income = await query<RowDataPacket[]>(
            `SELECT debit, credit FROM ${prefix()}erp_acct_ledger_details WHERE trn_no = ?`,
            [invoiceId]
        );

        expect(receivable.length, 'the invoice posts a receivable entry').toBeGreaterThan(0);
        expect(income.length, 'and an income entry').toBeGreaterThan(0);

        const sum = (rows: RowDataPacket[], column: 'debit' | 'credit'): number =>
            rows.reduce((total, row) => total + Number(row[column]), 0);

        const debits = sum(receivable, 'debit') + sum(income, 'debit');
        const credits = sum(receivable, 'credit') + sum(income, 'credit');

        // The invariant every accounting system lives or dies by.
        expect(debits, 'debits equal credits across the posting').toBeCloseTo(credits, 2);
        expect(debits, 'and the posting is for the invoice amount').toBeCloseTo(Number(invoices[0]!.amount), 2);
    });

    test('an invoice is recorded against the customer ledger', { tag: ['@tier1', '@accounting', '@flow', '@money'] }, async () => {
        await page.gotoRoute('newInvoice');
        await page.pickFromMultiselect('Customer', CUSTOMER);
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineProduct(0, PRODUCT);
        await page.setLineQty(0, 1);
        await page.save();

        const invoices = await query<RowDataPacket[]>(
            `SELECT id, customer_id, amount FROM ${prefix()}erp_acct_invoices ORDER BY id DESC LIMIT 1`
        );
        expect(invoices, 'precondition: the invoice was created').toHaveLength(1);

        // `erp_acct_people_trn_details`, NOT `erp_acct_people_trn` — both tables
        // exist and only the `_details` one is written on invoice creation
        // (`transactions.php:1735`). Querying the shorter name returns zero rows
        // and looks exactly like a missing ledger entry.
        const people = await query<RowDataPacket[]>(
            `SELECT people_id, debit, credit FROM ${prefix()}erp_acct_people_trn_details WHERE voucher_no = ?`,
            [Number(invoices[0]!.id)]
        );

        expect(people, "the customer's transaction ledger gains a row").toHaveLength(1);
        expect(Number(people[0]!.people_id), 'for the customer on the invoice').toBe(Number(invoices[0]!.customer_id));
        expect(Number(people[0]!.debit), 'debited for the invoice amount').toBeCloseTo(Number(invoices[0]!.amount), 2);
    });

    test.fail(
        'creating an invoice answers with success, not a server error',
        { tag: ['@tier1', '@accounting', '@flow', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-150 / erp-pro#967. `erp-pdf-invoice 1.2.1`
            // calls `get_magic_quotes_runtime()`, removed in PHP 8.0, while
            // building the invoice PDF on the `erp_acct_new_transaction_sales`
            // hook. The invoice commits first, so it is created correctly and
            // the screen looks fine — but the REST call answers 500 and the
            // customer never receives the invoice email.
            //
            // Precondition: the invoice really is created, which is what makes
            // this a response/email defect rather than a failed create.
            await page.gotoRoute('newInvoice');
            await page.pickFromMultiselect('Customer', CUSTOMER);
            await page.pickDate('Transaction Date', toDate());
            await page.pickDate('Due Date', dateOffset(30));
            await page.pickLineProduct(0, PRODUCT);
            await page.setLineQty(0, 1);
            await page.save();

            const invoices = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_acct_invoices ORDER BY id DESC LIMIT 1`);
            expect(invoices, 'precondition: the invoice was created despite the response').toHaveLength(1);

            expect(page.serverErrorList(), 'the create answers without a server error').toEqual([]);
        }
    );

    // ---- Tier 3 ----------------------------------------------------------

    test('an invoice with no customer is refused', { tag: ['@tier3', '@accounting', '@validation'] }, async () => {
        await page.gotoRoute('newInvoice');
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineProduct(0, PRODUCT);
        await page.setLineQty(0, 1);

        const before = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_invoices`);
        await page.save();

        expect(await page.bodyText(), 'the form names the missing field').toContain('Customer Name is required');

        const after = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_invoices`);
        expect(Number(after[0]!.n), 'and nothing is written').toBe(Number(before[0]!.n));
    });

    test('an invoice with no line item is refused', { tag: ['@tier3', '@accounting', '@validation'] }, async () => {
        await page.gotoRoute('newInvoice');
        await page.pickFromMultiselect('Customer', CUSTOMER);
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));

        const before = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_invoices`);
        await page.save();

        const body = await page.bodyText();
        expect(
            body.includes('select a product') || body.includes("Total amount can't be zero"),
            'the form refuses an empty invoice'
        ).toBe(true);

        const after = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_invoices`);
        expect(Number(after[0]!.n), 'and nothing is written').toBe(Number(before[0]!.n));
    });

    test.fail(
        'a date typed into the form is the date the form uses',
        { tag: ['@tier3', '@accounting', '@validation', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-149 / erp-pro#966. `Datepicker.vue:86` emits to
            // the parent only when the field is EMPTIED, so a typed date updates
            // the visible input and nothing else. The form then reports the date
            // as missing while displaying it.
            //
            // Precondition first: the calendar path is proven to work by the
            // tier-1 cases above, so a failure here is the typing path alone.
            const trnDate = toDate();
            const dueDate = dateOffset(30);

            await page.gotoRoute('newInvoice');
            expect(await page.pickFromMultiselect('Customer', CUSTOMER), 'precondition: the customer picker works').toBe(true);

            await page.typeDate('Transaction Date', trnDate);
            await page.typeDate('Due Date', dueDate);

            expect(await page.dateValue('Transaction Date'), 'precondition: the typed date is in the box').toBe(trnDate);

            await page.pickLineProduct(0, PRODUCT);
            await page.setLineQty(0, 1);
            await page.save();

            expect(await page.bodyText(), 'the form does not claim the date is missing').not.toContain('Transaction Date is required');
        }
    );
});
