import { test, expect } from '@utils/test';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { toDate } from '@utils/helpers';
import {
    cleanupExpenses,
    cleanupJournals,
    cleanupLedger,
    cleanupPersonByEmail,
    cleanupTransfers,
    seedBankLedger,
    seedVendor,
} from '@utils/cleanupAccounting';
import { closeDb, execute, prefix, query } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Accounting — journals, transfers and checks.
 *
 * The three money movements that involve no customer or vendor: a journal
 * posts straight to the ledger, a transfer moves money between the company's
 * own accounts, and a check spends from a bank account. Their oracle is the
 * ledger itself, because none of them has a balance elsewhere to read.
 *
 * **A stock install cannot do two of these three.** `Cash` is filed under the
 * Asset chart, and chart 7 — **Bank** — is empty. A transfer needs two accounts
 * to move between, and the check form's `From Account` reads Bank accounts
 * only, so both are dead until a Bank account exists. That is a setup
 * prerequisite rather than a defect, established by creating one and watching
 * both forms come to life; it is written up in COVERAGE.md rather than filed.
 *
 * A CHECK is not its own record: `CheckCreate.vue:391` posts it to `/expenses`
 * with a `check_no`, so a check lands in `erp_acct_expenses`. This file uses
 * its OWN payee for that reason — `expenses.spec.ts` owns Skyline.
 */

const BANK = 'Northwind Operating Account';
const EXPENSE_ACCOUNT = 'Janitorial Expenses';
const OTHER_ACCOUNT = 'Consulting & Accounting';
const PAYEE = { firstName: 'Cedar', lastName: 'Facilities Group', email: 'ap@cedar-facilities.test' };
const PAYEE_NAME = `${PAYEE.firstName} ${PAYEE.lastName}`;

test.describe('Accounting — journals, transfers and checks', () => {
    let page: AccountingPage;
    let bankLedgerId = 0;
    let payeeId = 0;

    /** What a ledger holds: debits raised, less credits paid out. */
    async function ledgerBalance(ledgerId: number): Promise<number> {
        const rows = await query<RowDataPacket[]>(
            // NULL-safe on purpose: a journal writes NULL into the column it is
            // not using, so `SUM(debit) - SUM(credit)` collapses to NULL for any
            // account that only ever took one side.
            `SELECT COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) AS balance
               FROM ${prefix()}erp_acct_ledger_details WHERE ledger_id = ?`,
            [ledgerId]
        );

        return Number(rows[0]!.balance);
    }

    async function ledgerIdFor(name: string): Promise<number> {
        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_acct_ledgers WHERE name = ?`, [name]);

        return Number(rows[0]!.id);
    }

    /** Puts money in an account directly, so a spend case has something to spend. */
    async function fund(ledgerId: number, amount: number): Promise<void> {
        await execute(
            `INSERT INTO ${prefix()}erp_acct_ledger_details (ledger_id, trn_no, particulars, debit, credit, trn_date, created_at, created_by)
             VALUES (?, 999999, 'test opening balance', ?, 0, NOW(), NOW(), 1)`,
            [ledgerId, amount]
        );
    }

    async function clearFunding(): Promise<void> {
        await execute(`DELETE FROM ${prefix()}erp_acct_ledger_details WHERE trn_no = 999999`);
    }

    test.beforeAll(async () => {
        bankLedgerId = await seedBankLedger(BANK);
        payeeId = await seedVendor(PAYEE.firstName, PAYEE.lastName, PAYEE.email);
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        page.watchServerErrors();

        await cleanupJournals();
        await cleanupTransfers();
        await cleanupExpenses(payeeId);
        await clearFunding();
    });

    test.afterAll(async () => {
        await cleanupJournals();
        await cleanupTransfers();
        await cleanupExpenses(payeeId);
        await clearFunding();
        await cleanupLedger(bankLedgerId);
        await cleanupPersonByEmail(PAYEE.email);
        await closeDb();
    });

    // ---- journals ----------------------------------------------------------

    test('the journals screen lists its entries', { tag: ['@tier1', '@accounting', '@journals', '@smoke'] }, async () => {
        await page.gotoRoute('journals');

        // The screen renders its title and its "New Journal Entry" action inside
        // ONE heading node, so this is a contains check, not an equality one.
        expect(await page.showsHeadingFor('journals'), 'the screen renders').toBe(true);

        const columns = await page.reportText();

        for (const column of ['Voucher No.', 'Date', 'Particulars', 'Amount']) {
            expect(columns, `the ${column} column is present`).toContain(column);
        }
    });

    test('a balanced journal posts equal debits and credits', { tag: ['@tier1', '@accounting', '@journals', '@money'] }, async () => {
        const debited = await ledgerIdFor(EXPENSE_ACCOUNT);
        const credited = await ledgerIdFor(OTHER_ACCOUNT);

        await page.createJournal(
            [
                { account: EXPENSE_ACCOUNT, amount: 400, side: 'debit' },
                { account: OTHER_ACCOUNT, amount: 400, side: 'credit' },
            ],
            toDate()
        );

        const journals = await query<RowDataPacket[]>(
            `SELECT voucher_no, voucher_amount FROM ${prefix()}erp_acct_journals ORDER BY id DESC LIMIT 1`
        );

        expect(journals, 'the journal is written').toHaveLength(1);

        const voucherNo = Number(journals[0]!.voucher_no);
        const totals = await query<RowDataPacket[]>(
            `SELECT COALESCE(SUM(debit), 0) AS debits, COALESCE(SUM(credit), 0) AS credits
               FROM ${prefix()}erp_acct_ledger_details WHERE trn_no = ?`,
            [voucherNo]
        );

        expect(Number(totals[0]!.debits), 'the debit side is posted').toBeCloseTo(400, 2);
        expect(
            Number(totals[0]!.debits) - Number(totals[0]!.credits),
            'and the entry balances to zero — the one rule a journal must keep'
        ).toBeCloseTo(0, 2);

        expect(await ledgerBalance(debited), 'the debited account rose').toBeCloseTo(400, 2);
        expect(await ledgerBalance(credited), 'and the credited account fell').toBeCloseTo(-400, 2);
    });

    test('a posted journal appears on the journals screen', { tag: ['@tier2', '@accounting', '@journals', '@flow'] }, async () => {
        await page.createJournal(
            [
                { account: EXPENSE_ACCOUNT, amount: 400, side: 'debit' },
                { account: OTHER_ACCOUNT, amount: 400, side: 'credit' },
            ],
            toDate()
        );

        const journals = await query<RowDataPacket[]>(
            `SELECT voucher_no FROM ${prefix()}erp_acct_journals ORDER BY id DESC LIMIT 1`
        );

        expect(journals, 'precondition: the journal was written').toHaveLength(1);

        await page.gotoRoute('journals');
        const table = (await page.reportText()).replace(/\s+/g, ' ');

        expect(table, 'the entry is listed').toContain(String(Number(journals[0]!.voucher_no)));

        // `400`, NOT `$400.00`. The Journals list is the only accounting list
        // that prints its Amount unformatted — every other one renders currency
        // and two decimals. Asserted as the product actually behaves, and
        // recorded in COVERAGE.md rather than quietly normalised away.
        expect(table, 'at its amount').toContain('400');
    });

    test('an unbalanced journal is refused', { tag: ['@tier3', '@accounting', '@journals', '@validation', '@money'] }, async () => {
        const before = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_journals`);

        await page.createJournal(
            [
                { account: EXPENSE_ACCOUNT, amount: 400, side: 'debit' },
                { account: OTHER_ACCOUNT, amount: 250, side: 'credit' },
            ],
            toDate()
        );

        const after = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_acct_journals`);

        expect(Number(after[0]!.n), 'nothing is posted when the sides disagree').toBe(Number(before[0]!.n));
    });

    // ---- transfers ---------------------------------------------------------

    test('a transfer moves money without creating or destroying any', { tag: ['@tier1', '@accounting', '@transfers', '@money'] }, async () => {
        const cash = await ledgerIdFor('Cash');

        await fund(cash, 1000);

        const cashBefore = await ledgerBalance(cash);
        const bankBefore = await ledgerBalance(bankLedgerId);

        expect(await page.transferMoney('Cash', BANK, 250, toDate()), 'the transfer form accepted the entry').toBe(true);

        const transfers = await query<RowDataPacket[]>(
            `SELECT voucher_no, amount, ac_from, ac_to FROM ${prefix()}erp_acct_transfer_voucher ORDER BY id DESC LIMIT 1`
        );

        expect(transfers, 'a transfer is written').toHaveLength(1);
        expect(Number(transfers[0]!.amount), 'for the amount asked').toBeCloseTo(250, 2);

        const cashAfter = await ledgerBalance(cash);
        const bankAfter = await ledgerBalance(bankLedgerId);

        expect(cashAfter, 'cash is down by the transfer').toBeCloseTo(cashBefore - 250, 2);
        expect(bankAfter, 'the bank account is up by the same').toBeCloseTo(bankBefore + 250, 2);
        expect(cashAfter + bankAfter, 'and the two together are unchanged').toBeCloseTo(cashBefore + bankBefore, 2);
    });

    test('the transfers screen lists what was moved', { tag: ['@tier2', '@accounting', '@transfers', '@flow'] }, async () => {
        const cash = await ledgerIdFor('Cash');

        await fund(cash, 1000);
        expect(await page.transferMoney('Cash', BANK, 250, toDate()), 'precondition: the transfer went through').toBe(true);

        await page.gotoRoute('transfers');
        const table = (await page.reportText()).replace(/\s+/g, ' ');

        expect(table, 'the destination account is named').toContain(BANK);
        expect(table, 'and the amount is shown').toContain('250.00');
    });

    // ---- checks ------------------------------------------------------------

    test('a check spends from the bank account it is drawn on', { tag: ['@tier1', '@accounting', '@checks', '@money'] }, async () => {
        const expenseLedger = await ledgerIdFor(EXPENSE_ACCOUNT);

        await fund(bankLedgerId, 1000);

        const bankBefore = await ledgerBalance(bankLedgerId);
        const expenseBefore = await ledgerBalance(expenseLedger);

        expect(
            await page.createCheck(PAYEE_NAME, '10041', BANK, EXPENSE_ACCOUNT, 175, toDate()),
            'the check form accepted the entry'
        ).toBe(true);

        const checks = await query<RowDataPacket[]>(
            `SELECT voucher_no, amount, check_no FROM ${prefix()}erp_acct_expenses WHERE people_id = ? ORDER BY id DESC LIMIT 1`,
            [payeeId]
        );

        expect(checks, 'the check is written as an expense').toHaveLength(1);
        expect(Number(checks[0]!.amount), 'for its amount').toBeCloseTo(175, 2);
        expect(String(checks[0]!.check_no), 'carrying its check number').toBe('10041');

        expect(await ledgerBalance(bankLedgerId), 'the bank account paid it out').toBeCloseTo(bankBefore - 175, 2);
        expect(await ledgerBalance(expenseLedger), 'and the expense account carries it').toBeCloseTo(expenseBefore + 175, 2);
    });

    // ---- authorization -----------------------------------------------------

    test('journals are closed to an employee', { tag: ['@tier3', '@accounting', '@journals', '@authz'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const p = await context.newPage();
        const asEmployee = new AccountingPage(p);

        await asEmployee.gotoRoute('journals');

        expect(
            await p.evaluate(() => Boolean((window as unknown as { erp_acct_var?: unknown }).erp_acct_var)),
            'the accounting app never loads for an employee'
        ).toBe(false);

        await context.close();
    });
});
