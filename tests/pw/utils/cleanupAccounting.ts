import { execute, prefix, query } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

/**
 * The people id behind a seeded customer's display name.
 *
 * Accounting specs SCOPE THEIR CLEANUP BY CUSTOMER so two of them can run in
 * parallel without deleting each other's in-flight transactions — the sixth time
 * this class of collision has bitten this suite. Each spec owns one seeded
 * customer and never touches another's.
 */
export async function customerIdFor(name: string): Promise<number> {
    const rows = await query<RowDataPacket[]>(
        `SELECT p.id FROM ${prefix()}erp_peoples p
           JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
           JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
          WHERE t.name = 'customer' AND CONCAT(p.first_name, ' ', p.last_name) = ?`,
        [name]
    );

    if (!rows.length) throw new Error(`no seeded customer named "${name}"`);

    return Number(rows[0]!.id);
}

/**
 * Accounting transactions the suite created, plus every child row.
 *
 * Unlike the CRM tables there is no title to mark, so the scope is the
 * transaction ids themselves: a spec records what it created and hands the ids
 * back. `cleanupInvoices()` with no ids removes ALL invoices, which is only
 * correct because this site seeds none — asserted by the suite's own baseline
 * check rather than assumed, and the parameterised form exists for the day that
 * stops being true.
 */
export async function cleanupInvoices(customerId?: number): Promise<number> {
    const invoices = `${prefix()}erp_acct_invoices`;

    const rows = customerId
        ? await query<RowDataPacket[]>(`SELECT id, voucher_no FROM ${invoices} WHERE customer_id = ?`, [customerId])
        : await query<RowDataPacket[]>(`SELECT id, voucher_no FROM ${invoices}`);

    if (!rows.length) return 0;

    const ids = rows.map((row) => Number(row.id));

    // CHILDREN KEY ON `voucher_no`, NOT ON THE INVOICE'S PRIMARY KEY. The two are
    // equal on a young site and drift apart later — invoice id 102 carries
    // voucher 134 here — so deleting children by `id` quietly stops matching and
    // leaves ledger rows behind that still count toward the customer balance.
    // That is what produced the orphans `cleanupLedgerOrphans()` had to sweep.
    const vouchers = rows.map((row) => Number(row.voucher_no));
    const voucherList = vouchers.map(() => '?').join(', ');

    await execute(`DELETE FROM ${prefix()}erp_acct_invoice_details WHERE trn_no IN (${voucherList})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_invoice_account_details WHERE invoice_no IN (${voucherList})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_ledger_details WHERE trn_no IN (${voucherList})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_people_trn_details WHERE voucher_no IN (${voucherList})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_voucher_no WHERE id IN (${voucherList})`, vouchers);

    const result = await execute(`DELETE FROM ${invoices} WHERE id IN (${ids.map(() => '?').join(', ')})`, ids);

    return result.affectedRows ?? 0;
}

/**
 * Payments (invoice receipts) the suite created, plus their children.
 *
 * Called BEFORE `cleanupInvoices()` — a receipt references the invoice it
 * settles, so removing invoices first strands the receipt rows.
 */
export async function cleanupPayments(customerId?: number): Promise<number> {
    const receipts = `${prefix()}erp_acct_invoice_receipts`;

    const rows = customerId
        ? await query<RowDataPacket[]>(`SELECT voucher_no FROM ${receipts} WHERE customer_id = ?`, [customerId])
        : await query<RowDataPacket[]>(`SELECT voucher_no FROM ${receipts}`);

    const targets = rows.map((row) => Number(row.voucher_no));

    if (!targets.length) return 0;

    const list = targets.map(() => '?').join(', ');

    await execute(`DELETE FROM ${prefix()}erp_acct_invoice_receipts_details WHERE voucher_no IN (${list})`, targets);
    await execute(`DELETE FROM ${prefix()}erp_acct_ledger_details WHERE trn_no IN (${list})`, targets);
    await execute(`DELETE FROM ${prefix()}erp_acct_people_trn_details WHERE voucher_no IN (${list})`, targets);
    await execute(`DELETE FROM ${prefix()}erp_acct_voucher_no WHERE id IN (${list})`, targets);

    const result = await execute(`DELETE FROM ${receipts} WHERE voucher_no IN (${list})`, targets);

    return result.affectedRows ?? 0;
}

/** The people id behind a seeded vendor's display name. */
export async function vendorIdFor(name: string): Promise<number> {
    const rows = await query<RowDataPacket[]>(
        `SELECT p.id FROM ${prefix()}erp_peoples p
           JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
           JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
          WHERE t.name = 'vendor' AND CONCAT(p.first_name, ' ', p.last_name) = ?`,
        [name]
    );

    if (!rows.length) throw new Error(`no seeded vendor named "${name}"`);

    return Number(rows[0]!.id);
}

/**
 * Bills raised against a vendor, plus their children.
 *
 * Children key on `voucher_no`, like every other accounting table — see
 * `cleanupInvoices()` for what happens when that is got wrong.
 */
export async function cleanupBills(vendorId?: number): Promise<number> {
    const bills = `${prefix()}erp_acct_bills`;

    const rows = vendorId
        ? await query<RowDataPacket[]>(`SELECT id, voucher_no FROM ${bills} WHERE vendor_id = ?`, [vendorId])
        : await query<RowDataPacket[]>(`SELECT id, voucher_no FROM ${bills}`);

    if (!rows.length) return 0;

    const vouchers = rows.map((row) => Number(row.voucher_no));
    const list = vouchers.map(() => '?').join(', ');

    await execute(`DELETE FROM ${prefix()}erp_acct_bill_details WHERE trn_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_bill_account_details WHERE bill_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_ledger_details WHERE trn_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_people_trn_details WHERE voucher_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_voucher_no WHERE id IN (${list})`, vouchers);

    const ids = rows.map((row) => Number(row.id));
    const result = await execute(`DELETE FROM ${bills} WHERE id IN (${ids.map(() => '?').join(', ')})`, ids);

    return result.affectedRows ?? 0;
}

/**
 * Expenses raised against a payee, with their line items and ledger entries.
 *
 * The orphan sweep at the end is not defensive padding: editing an expense
 * re-inserts its line items with no `trn_no` at all (ERP-154), so rows keyed to
 * nothing accumulate on every edit and no voucher-scoped delete can reach them.
 */
export async function cleanupExpenses(peopleId?: number): Promise<number> {
    const expenses = `${prefix()}erp_acct_expenses`;

    const rows = peopleId
        ? await query<RowDataPacket[]>(`SELECT id, voucher_no FROM ${expenses} WHERE people_id = ?`, [peopleId])
        : await query<RowDataPacket[]>(`SELECT id, voucher_no FROM ${expenses}`);

    await execute(`DELETE FROM ${prefix()}erp_acct_expense_details WHERE trn_no = 0 OR trn_no IS NULL`);

    if (!rows.length) return 0;

    const vouchers = rows.map((row) => Number(row.voucher_no));
    const list = vouchers.map(() => '?').join(', ');

    await execute(`DELETE FROM ${prefix()}erp_acct_expense_details WHERE trn_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_ledger_details WHERE trn_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_people_trn_details WHERE voucher_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_voucher_no WHERE id IN (${list})`, vouchers);

    const ids = rows.map((row) => Number(row.id));
    const result = await execute(`DELETE FROM ${expenses} WHERE id IN (${ids.map(() => '?').join(', ')})`, ids);

    return result.affectedRows ?? 0;
}

/** Bill payments made to a vendor. Run BEFORE `cleanupBills()`. */
export async function cleanupPayBills(vendorId?: number): Promise<number> {
    const payBills = `${prefix()}erp_acct_pay_bill`;

    const rows = vendorId
        ? await query<RowDataPacket[]>(`SELECT id, voucher_no FROM ${payBills} WHERE vendor_id = ?`, [vendorId])
        : await query<RowDataPacket[]>(`SELECT id, voucher_no FROM ${payBills}`);

    if (!rows.length) return 0;

    const vouchers = rows.map((row) => Number(row.voucher_no));
    const list = vouchers.map(() => '?').join(', ');

    await execute(`DELETE FROM ${prefix()}erp_acct_pay_bill_details WHERE voucher_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_ledger_details WHERE trn_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_people_trn_details WHERE voucher_no IN (${list})`, vouchers);
    await execute(`DELETE FROM ${prefix()}erp_acct_voucher_no WHERE id IN (${list})`, vouchers);

    const ids = rows.map((row) => Number(row.id));
    const result = await execute(`DELETE FROM ${payBills} WHERE id IN (${ids.map(() => '?').join(', ')})`, ids);

    return result.affectedRows ?? 0;
}

/**
 * Ledger rows whose parent transaction is already gone.
 *
 * `cleanupInvoices()` and `cleanupPayments()` derive their ids FROM the parent
 * tables, so anything that removed an invoice or receipt without its children
 * leaves `people_trn_details` and `ledger_details` rows that neither function
 * can ever find again. Those orphans keep counting toward the customer balance:
 * six of them made a fresh 1,800 invoice read as 10,800 outstanding, which looks
 * exactly like a ledger defect.
 *
 * Safe to run unconditionally on this install because the seed creates NO
 * accounting transactions — verified at baseline (invoices 0, ledger_details 0).
 * If that ever changes, scope it.
 */
export async function cleanupLedgerOrphans(): Promise<number> {
    const invoices = `${prefix()}erp_acct_invoices`;
    const receipts = `${prefix()}erp_acct_invoice_receipts`;

    // Compared against `voucher_no` on BOTH sides. The children key on the
    // voucher number, so an earlier version of this sweep — which compared
    // against the invoice PRIMARY KEY — treated every legitimate ledger row as an
    // orphan and deleted it. Running in parallel, it emptied another spec's
    // customer ledger mid-test and looked like the product failing to post.
    const bills = `${prefix()}erp_acct_bills`;
    const payBills = `${prefix()}erp_acct_pay_bill`;

    const live = `(SELECT voucher_no FROM ${invoices}) UNION (SELECT voucher_no FROM ${receipts})
                  UNION (SELECT voucher_no FROM ${bills}) UNION (SELECT voucher_no FROM ${payBills})`;

    const people = await execute(`DELETE FROM ${prefix()}erp_acct_people_trn_details WHERE voucher_no NOT IN (${live})`);
    const ledger = await execute(`DELETE FROM ${prefix()}erp_acct_ledger_details WHERE trn_no NOT IN (${live})`);

    return (people.affectedRows ?? 0) + (ledger.affectedRows ?? 0);
}

/** Every accounting transaction table the suite writes to, for a residue check. */
export async function accountingRowCounts(): Promise<Record<string, number>> {
    const tables = [
        'erp_acct_invoices',
        'erp_acct_invoice_details',
        'erp_acct_invoice_account_details',
        'erp_acct_ledger_details',
        'erp_acct_people_trn_details',
        'erp_acct_invoice_receipts',
        'erp_acct_invoice_receipts_details',
    ];

    const counts: Record<string, number> = {};

    for (const table of tables) {
        const rows = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}${table}`);
        counts[table] = Number(rows[0]!.n);
    }

    return counts;
}
