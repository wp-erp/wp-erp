import { test, expect } from '@utils/test';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ADMIN_STATE } from '@utils/authStates';
import { toDate, dateOffset } from '@utils/helpers';
import {
    cleanupExpenses,
    cleanupInvoices,
    cleanupPayments,
    cleanupLedgerOrphans,
    customerIdFor,
    vendorIdFor,
} from '@utils/cleanupAccounting';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Expenses — money leaving the business, and the edit path that is supposed to
 * correct it.
 *
 * An expense posts to two ledgers at once: the expense account is DEBITED and
 * the funding account is CREDITED. That double entry, not the figure printed on
 * the expense screen, is what the trial balance and the income statement are
 * built from — so it is the oracle here, exactly as it is for invoices.
 *
 * The edit path is the reason this file exists. Recording an expense works;
 * correcting one does not, and the two ways it fails are independent of each
 * other (ERP-154, ERP-155).
 */

/**
 * This file OWNS these two parties. Every other accounting spec owns its own —
 * Harbourline/Meridian, Verdant, Bluewave, Sable & Finch/Pinewood — because an
 * unscoped clean in any one of them wiped another's in-flight rows six times
 * over. Kestrel and Skyline are used nowhere else.
 */
const PAYEE = 'Skyline Facilities Ltd.';
const FUNDER = 'Kestrel Manufacturing';

/** Charged to an expense account no other spec touches, for the same reason. */
const ACCOUNT = 'Utilities';
const PRODUCT = 'Training Workshop (per day)';

/** The Cash ledger — the first option "Transaction From" offers. */
const CASH_LEDGER_ID = 7;

const EXPENSE_AMOUNT = 600;

test.describe('Accounting — expenses', () => {
    let page: AccountingPage;

    let payeeId = 0;
    let funderId = 0;

    test.beforeAll(async () => {
        payeeId = await vendorIdFor(PAYEE);
        funderId = await customerIdFor(FUNDER);
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        page.watchServerErrors();

        await cleanupExpenses(payeeId);
        await cleanupPayments(funderId);
        await cleanupInvoices(funderId);
        await cleanupLedgerOrphans();
    });

    test.afterAll(async () => {
        await cleanupExpenses(payeeId);
        await cleanupPayments(funderId);
        await cleanupInvoices(funderId);
        await cleanupLedgerOrphans();
        await closeDb();
    });

    /**
     * The Cash ledger's balance.
     *
     * Every assertion here compares two readings of this rather than an absolute
     * figure. Cash is global — every accounting spec deposits into it and spends
     * out of it — so only the CHANGE across one operation is this file's to
     * claim, whatever else ran before it.
     */
    async function cashBalance(): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(debit) - SUM(credit), 0) AS balance
               FROM ${prefix()}erp_acct_ledger_details WHERE ledger_id = ?`,
            [CASH_LEDGER_ID]
        );

        return Number(rows[0]!.balance);
    }

    /**
     * Collects cash, so there is something to spend.
     *
     * This site seeds no opening balances and the product refuses an expense
     * larger than the funding account holds, so the money has to be earned
     * first. That makes every case below a real end-to-end cycle rather than a
     * mocked balance.
     */
    async function fundCash(): Promise<void> {
        await page.gotoRoute('newInvoice');
        await page.pickFromMultiselect('Customer', FUNDER);
        await page.pickDate('Transaction Date', toDate());
        await page.pickDate('Due Date', dateOffset(30));
        await page.pickLineProduct(0, PRODUCT);
        await page.setLineQty(0, 3);
        await page.save();

        expect(await page.receivePayment(FUNDER, toDate()), 'precondition: cash was collected').toBe(true);
    }

    /** Funds cash, raises one expense, and returns its voucher number. */
    async function raiseExpense(amount = EXPENSE_AMOUNT): Promise<number> {
        await fundCash();

        expect(await page.createExpense(PAYEE, ACCOUNT, amount, toDate()), 'the expense form accepted the entry').toBe(
            true
        );

        const rows = await query<RowDataPacket[]>(
            `SELECT voucher_no FROM ${prefix()}erp_acct_expenses WHERE people_id = ? ORDER BY id DESC LIMIT 1`,
            [payeeId]
        );

        expect(rows, 'precondition: an expense was recorded').toHaveLength(1);

        return Number(rows[0]!.voucher_no);
    }

    /** What the books say the expense account carries for this voucher. */
    async function ledgerFor(voucherNo: number): Promise<{ ledgerId: number; debit: number; credit: number }[]> {
        const rows = await query<RowDataPacket[]>(
            `SELECT ledger_id, debit, credit FROM ${prefix()}erp_acct_ledger_details WHERE trn_no = ?`,
            [voucherNo]
        );

        return rows.map((row) => ({
            ledgerId: Number(row.ledger_id),
            debit: Number(row.debit),
            credit: Number(row.credit),
        }));
    }

    // ---- Tier 1 ----------------------------------------------------------

    test('an expense is charged to its account and paid out of cash', { tag: ['@tier1', '@accounting', '@flow', '@money'] }, async () => {
        await fundCash();

        const before = await cashBalance();

        expect(await page.createExpense(PAYEE, ACCOUNT, EXPENSE_AMOUNT, toDate()), 'the expense form accepted the entry').toBe(
            true
        );

        const expenses = await query<RowDataPacket[]>(
            `SELECT voucher_no, amount FROM ${prefix()}erp_acct_expenses WHERE people_id = ? ORDER BY id DESC LIMIT 1`,
            [payeeId]
        );

        expect(expenses, 'an expense is recorded').toHaveLength(1);
        expect(Number(expenses[0]!.amount), 'for the amount entered').toBeCloseTo(EXPENSE_AMOUNT, 2);

        const voucherNo = Number(expenses[0]!.voucher_no);
        const entries = await ledgerFor(voucherNo);

        expect(entries, 'the expense posts a double entry').toHaveLength(2);
        expect(
            entries.some((e) => e.ledgerId !== CASH_LEDGER_ID && e.debit === EXPENSE_AMOUNT),
            'the expense account is debited'
        ).toBe(true);
        expect(
            entries.some((e) => e.ledgerId === CASH_LEDGER_ID && e.credit === EXPENSE_AMOUNT),
            'and cash is credited'
        ).toBe(true);

        expect(await cashBalance(), 'so cash falls by exactly the expense').toBeCloseTo(before - EXPENSE_AMOUNT, 2);
    });

    // ---- Tier 2 — the edit path ------------------------------------------

    test('the expense edit screen loads the expense it names', { tag: ['@tier2', '@accounting', '@flow'] }, async ({ page: p }) => {
        // The canary for the three guards below. Unlike the payment edit screen
        // (ERP-153), this one genuinely loads its record — which is what makes
        // the single missing field below attributable rather than ambient.
        const voucherNo = await raiseExpense();

        await page.gotoExpenseEdit(voucherNo);

        expect(await page.multiselectValue('Pay To'), 'the payee is filled in').toContain(PAYEE);
        expect(
            await p.locator('table tbody tr input[name="amount"]').first().inputValue(),
            'and so is the amount charged'
        ).toContain(String(EXPENSE_AMOUNT));
    });

    test.fail(
        'the expense edit screen carries the expense date',
        { tag: ['@tier2', '@accounting', '@flow', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-155. `ExpensesController::prepare_item_for_response()`
            // publishes the transaction date as `date`, while
            // `ExpenseCreate.vue:322` reads `expense.trn_date` — a key the
            // response does not contain. The field is left undefined, so the
            // date renders empty on every expense edit.
            const voucherNo = await raiseExpense();

            await page.gotoExpenseEdit(voucherNo);

            expect(await page.multiselectValue('Pay To'), 'precondition: the screen loaded the expense').toContain(PAYEE);

            expect(await page.dateValue('Expense Date'), 'the date the expense was recorded with').toBe(toDate());
        }
    );

    test.fail(
        'an expense edit that changes nothing can be saved',
        { tag: ['@tier2', '@accounting', '@flow', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-155, and the face of it a user meets: because
            // the date never loads, pressing Update without touching anything is
            // refused for a field they never edited.
            const voucherNo = await raiseExpense();

            await page.gotoExpenseEdit(voucherNo);

            expect(await page.multiselectValue('Pay To'), 'precondition: the screen loaded the expense').toContain(PAYEE);

            await page.save('Update');

            expect(await page.bodyText(), 'the save is not refused over a field that was never touched').not.toContain(
                'Transaction Date is required'
            );
        }
    );

    test.fail(
        'editing an expense down moves the ledger with it',
        { tag: ['@tier2', '@accounting', '@money', '@known-defect'] },
        async ({ page: p }) => {
            // KNOWN DEFECT — ERP-154, and the most damaging of the three.
            // `erp_acct_update_expense()` (`expenses.php:361`) updates the
            // expense header and rewrites its line items, and touches
            // `erp_acct_ledger_details` NOWHERE. The books keep the original
            // figure while the expense screen shows the new one.
            // `erp_acct_update_expense_data_into_ledger()` exists at `:688` to do
            // exactly this job and has no callers anywhere in the plugin.
            const voucherNo = await raiseExpense();
            const reduced = EXPENSE_AMOUNT / 2;

            const before = await cashBalance();

            expect(
                (await ledgerFor(voucherNo)).some((e) => e.ledgerId === CASH_LEDGER_ID && e.credit === EXPENSE_AMOUNT),
                'precondition: cash was credited the original expense'
            ).toBe(true);

            // The date has to be re-picked because it never loads — ERP-155. That
            // is a workaround for a DIFFERENT defect, and without it this edit
            // could not be saved at all.
            await page.gotoExpenseEdit(voucherNo);
            await page.pickDate('Expense Date', toDate());
            await page.setLineAmount(0, reduced, { replace: true });
            await page.save('Update');

            const header = await query<RowDataPacket[]>(
                `SELECT amount FROM ${prefix()}erp_acct_expenses WHERE voucher_no = ?`,
                [voucherNo]
            );
            expect(Number(header[0]!.amount), 'precondition: the expense now records the smaller figure').toBeCloseTo(
                reduced,
                2
            );

            // The defect: the money never came back.
            expect(await cashBalance(), 'cash is restored by the half that is no longer spent').toBeCloseTo(
                before + reduced,
                2
            );
            void p;
        }
    );

    test.fail(
        'an edited expense keeps its line items',
        { tag: ['@tier2', '@accounting', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-154, second face. The update deletes the old
            // line items and re-inserts them WITHOUT `trn_no`
            // (`expenses.php:414-426`), while the insert path sets it. Every
            // screen finds an expense's lines by joining
            // `expense_details.trn_no = expenses.voucher_no`, so after one edit
            // the expense has no lines at all — and the orphaned rows can never
            // be reached again.
            const voucherNo = await raiseExpense();

            await page.gotoExpenseEdit(voucherNo);
            await page.pickDate('Expense Date', toDate());
            await page.setLineAmount(0, EXPENSE_AMOUNT / 2, { replace: true });
            await page.save('Update');

            const header = await query<RowDataPacket[]>(
                `SELECT amount FROM ${prefix()}erp_acct_expenses WHERE voucher_no = ?`,
                [voucherNo]
            );
            expect(Number(header[0]!.amount), 'precondition: the edit was accepted').toBeCloseTo(EXPENSE_AMOUNT / 2, 2);

            const lines = await query<RowDataPacket[]>(
                `SELECT id FROM ${prefix()}erp_acct_expense_details WHERE trn_no = ?`,
                [voucherNo]
            );

            expect(lines, 'the expense still has the line it was edited to carry').toHaveLength(1);
        }
    );

    // ---- Tier 3 ----------------------------------------------------------

    test('an expense larger than the funding account is refused', { tag: ['@tier3', '@accounting', '@validation', '@money'] }, async () => {
        const before = await cashBalance();

        expect(
            await page.createExpense(PAYEE, ACCOUNT, before + 10_000, toDate()),
            'the form accepted the entry and submitted it'
        ).toBe(true);

        expect(await page.bodyText(), 'the product refuses and says why').toContain(
            'Not enough balance in selected account'
        );

        const expenses = await query<RowDataPacket[]>(
            `SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_expenses WHERE people_id = ?`,
            [payeeId]
        );

        expect(Number(expenses[0]!.n), 'and nothing is spent').toBe(0);
        expect(await cashBalance(), 'so cash is untouched').toBeCloseTo(before, 2);
    });
});
