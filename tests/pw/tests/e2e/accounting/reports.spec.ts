import { test, expect } from '@utils/test';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ADMIN_STATE } from '@utils/authStates';
import { toDate, dateOffset } from '@utils/helpers';
import {
    cleanupInvoices,
    cleanupPayments,
    cleanupBills,
    cleanupPayBills,
    cleanupLedgerOrphans,
    customerIdFor,
    vendorIdFor,
} from '@utils/cleanupAccounting';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * The accounting reports, checked against figures the suite controls.
 *
 * Every other accounting spec asserts what ONE transaction wrote. These assert
 * what the books SAY, which is the thing an accountant actually relies on and
 * the last place an error can hide: a transaction can post correctly and still
 * be reported wrongly.
 *
 * The strongest assertions here are the ones that need no arithmetic from me,
 * because the product states them itself and they cannot both be right if
 * either is wrong:
 *
 * - the trial balance's own totals row must have debits equal to credits;
 * - the balance sheet prints "Assets = X | Liability + Equity = Y", and X must
 *   equal Y;
 * - the income statement's Profit must equal Income minus Expense;
 * - and the balance sheet's Equity → Profit must equal the income statement's
 *   Profit — two reports computed independently that have to agree.
 *
 * **This file owns the WHOLE ledger for its duration**, which is why it lives in
 * the `accounting_money` project: reports aggregate every transaction on the
 * site, so they cannot be scoped to a customer or a vendor the way the other
 * specs are. Its cleanup is therefore UNSCOPED — the one place in this suite
 * where that is correct rather than a bug — and it is safe only because that
 * project is single-worker and runs after `e2e_tests`.
 */
const CUSTOMER = 'Sable & Finch Consulting';
const VENDOR = 'Pinewood Print & Signage';
const PRODUCT = 'Custom Dashboard Build';
const EXPENSE_ACCOUNT = 'Advertising';
const BILL_AMOUNT = 400;

test.describe('Accounting — reports', () => {
    let page: AccountingPage;
    let customerId = 0;
    let vendorId = 0;

    /** Every accounting transaction on the site, removed. See the note above. */
    async function clearTheBooks(): Promise<void> {
        await cleanupPayments();
        await cleanupInvoices();
        await cleanupPayBills();
        await cleanupBills();
        await cleanupLedgerOrphans();
    }

    /** Raises one invoice and returns the revenue it recorded. */
    async function raiseInvoice(): Promise<number> {
        await page.gotoRoute('newInvoice');
        await page.pickFromMultiselect('Customer', CUSTOMER);
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineProduct(0, PRODUCT);
        await page.setLineQty(0, 1);
        await page.save();

        const rows = await query<RowDataPacket[]>(
            `SELECT amount FROM ${prefix()}erp_acct_invoices WHERE customer_id = ? ORDER BY id DESC LIMIT 1`,
            [customerId]
        );

        expect(rows, 'precondition: the invoice was raised').toHaveLength(1);

        return Number(rows[0]!.amount);
    }

    test.beforeAll(async () => {
        customerId = await customerIdFor(CUSTOMER);
        vendorId = await vendorIdFor(VENDOR);
        void vendorId;
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        page.watchServerErrors();
        await clearTheBooks();
    });

    test.afterAll(async () => {
        await clearTheBooks();
        await closeDb();
    });

    // ---- Tier 1 — an empty ledger ------------------------------------------

    test('an empty ledger reports zero everywhere', { tag: ['@tier1', '@accounting', '@reports'] }, async () => {
        // The baseline that makes every other case here meaningful: if the
        // reports showed stale figures on an empty ledger, a seeded number
        // matching later would prove nothing.
        await page.gotoRoute('trialBalance');

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);

        const totals = await page.trialBalanceTotals();

        expect(totals.debit, 'nothing is debited').toBeCloseTo(0, 2);
        expect(totals.credit, 'nothing is credited').toBeCloseTo(0, 2);
        expect(page.serverErrorList(), 'and the report loads without a server error').toEqual([]);
    });

    // ---- Tier 1 — the invariants -------------------------------------------

    test('the trial balance balances', { tag: ['@tier1', '@accounting', '@reports', '@money'] }, async () => {
        const revenue = await raiseInvoice();
        await page.createBill(VENDOR, EXPENSE_ACCOUNT, BILL_AMOUNT, toDate(), dateOffset(30));

        await page.gotoRoute('trialBalance');

        const totals = await page.trialBalanceTotals();

        // The invariant the whole ledger rests on.
        expect(totals.debit, 'debits equal credits').toBeCloseTo(totals.credit, 2);

        // And the totals are the figures the suite put in, not an accident of
        // both sides being zero.
        expect(totals.debit, 'and total the seeded revenue plus expense').toBeCloseTo(revenue + BILL_AMOUNT, 2);
    });

    test('the trial balance attributes each amount to the right account', { tag: ['@tier1', '@accounting', '@reports', '@money'] }, async () => {
        const revenue = await raiseInvoice();
        await page.createBill(VENDOR, EXPENSE_ACCOUNT, BILL_AMOUNT, toDate(), dateOffset(30));

        await page.gotoRoute('trialBalance');

        expect(await page.reportFigure('Accounts Receivable'), 'the invoice is receivable').toBeCloseTo(revenue, 2);
        expect(await page.reportFigure('Sales Revenue'), 'and recorded as revenue').toBeCloseTo(revenue, 2);
        expect(await page.reportFigure('Accounts Payable'), 'the bill is payable').toBeCloseTo(BILL_AMOUNT, 2);
        expect(await page.reportFigure(EXPENSE_ACCOUNT), 'and charged to its expense account').toBeCloseTo(BILL_AMOUNT, 2);
    });

    test('the income statement reports profit as income minus expense', { tag: ['@tier1', '@accounting', '@reports', '@money'] }, async () => {
        const revenue = await raiseInvoice();
        await page.createBill(VENDOR, EXPENSE_ACCOUNT, BILL_AMOUNT, toDate(), dateOffset(30));

        await page.gotoRoute('incomeStatement');

        const income = await page.reportFigure('Total Income');
        const expense = await page.reportFigure('Total Expense');
        const profit = await page.reportFigure('Profit');

        expect(income, 'income is the invoice raised').toBeCloseTo(revenue, 2);
        expect(expense, 'expense is the bill raised').toBeCloseTo(BILL_AMOUNT, 2);
        expect(profit, 'and profit is the difference').toBeCloseTo(income - expense, 2);
    });

    test('the balance sheet balances', { tag: ['@tier1', '@accounting', '@reports', '@money'] }, async () => {
        await raiseInvoice();
        await page.createBill(VENDOR, EXPENSE_ACCOUNT, BILL_AMOUNT, toDate(), dateOffset(30));

        await page.gotoRoute('balanceSheet');

        // The accounting equation, taken from the line the report prints itself
        // rather than recomputed here — so this asserts what the accountant is
        // shown, not what I think the numbers should be.
        const equation = await page.balanceSheetEquation();

        expect(equation.assets, 'assets equal liabilities plus equity').toBeCloseTo(equation.liabilitiesPlusEquity, 2);
        expect(equation.assets, 'and are not merely both zero').toBeGreaterThan(0);
    });

    // ---- Tier 2 — the reports must agree with each other --------------------

    test('the balance sheet and the income statement agree on profit', { tag: ['@tier2', '@accounting', '@reports', '@money'] }, async () => {
        // Two reports, computed independently from the same ledger. If either
        // aggregation is wrong they diverge, and neither report on its own would
        // reveal it — which is exactly the class of error that reaches a
        // year-end.
        await raiseInvoice();
        await page.createBill(VENDOR, EXPENSE_ACCOUNT, BILL_AMOUNT, toDate(), dateOffset(30));

        await page.gotoRoute('incomeStatement');
        const statementProfit = await page.reportFigure('Profit');

        await page.gotoRoute('balanceSheet');
        const equityProfit = await page.reportFigure('Profit');

        expect(equityProfit, "the balance sheet's equity carries the income statement's profit").toBeCloseTo(
            statementProfit,
            2
        );
        expect(statementProfit, 'and it is a real figure, not zero').toBeGreaterThan(0);
    });

    test('an expense with no revenue is reported as a loss, and still balances', { tag: ['@tier2', '@accounting', '@reports', '@money'] }, async () => {
        // An expense with no revenue. The product does NOT print a negative
        // profit — it switches the line to **Loss**, and carries it into equity
        // as a DEBIT. My first version of this case asserted a "Profit" line was
        // still printed and failed against entirely correct behaviour; the
        // product was right and the assumption was mine.
        await page.createBill(VENDOR, EXPENSE_ACCOUNT, BILL_AMOUNT, toDate(), dateOffset(30));

        await page.gotoRoute('trialBalance');
        const totals = await page.trialBalanceTotals();

        expect(totals.debit, 'the trial balance still balances').toBeCloseTo(totals.credit, 2);
        expect(totals.debit, 'and carries the bill').toBeCloseTo(BILL_AMOUNT, 2);

        await page.gotoRoute('incomeStatement');

        expect(await page.reportFigure('Total Expense'), 'the expense is reported').toBeCloseTo(BILL_AMOUNT, 2);
        expect(await page.reportHas('Loss'), 'and the result is reported as a loss, not a profit').toBe(true);
        expect(await page.reportFigure('Loss'), 'of the whole expense').toBeCloseTo(BILL_AMOUNT, 2);

        await page.gotoRoute('balanceSheet');

        // Liability Cr 400 + Equity Dr 400 nets to the Assets figure of 0 — the
        // equation holds through a loss, which is the point of the case.
        const equation = await page.balanceSheetEquation();

        expect(equation.assets, 'and the balance sheet still balances').toBeCloseTo(equation.liabilitiesPlusEquity, 2);
        expect(await page.reportFigure('Loss'), 'with the loss carried into equity').toBeCloseTo(BILL_AMOUNT, 2);
    });

    // ---- Tier 3 ------------------------------------------------------------

    test('every report screen loads without a server error', { tag: ['@tier1', '@accounting', '@reports'] }, async () => {
        for (const route of ['trialBalance', 'incomeStatement', 'balanceSheet', 'ledgerReport'] as const) {
            page.clearServerErrors();
            await page.gotoRoute(route);

            expect(await page.hasNoPhpFatal(), `${route} renders without a fatal`).toBe(true);
            expect(page.serverErrorList(), `${route} loads without a server error`).toEqual([]);
        }
    });
});
