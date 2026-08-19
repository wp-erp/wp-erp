import { execute, prefix, query } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

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
export async function cleanupInvoices(ids: number[] = []): Promise<number> {
    const invoices = `${prefix()}erp_acct_invoices`;

    const targets = ids.length
        ? ids
        : (await query<RowDataPacket[]>(`SELECT id FROM ${invoices}`)).map((row) => Number(row.id));

    if (!targets.length) return 0;

    const list = targets.map(() => '?').join(', ');

    // Children first — nothing here is ON DELETE CASCADE.
    await execute(`DELETE FROM ${prefix()}erp_acct_invoice_details WHERE trn_no IN (${list})`, targets);
    await execute(`DELETE FROM ${prefix()}erp_acct_invoice_account_details WHERE invoice_no IN (${list})`, targets);
    await execute(`DELETE FROM ${prefix()}erp_acct_ledger_details WHERE trn_no IN (${list})`, targets);
    await execute(`DELETE FROM ${prefix()}erp_acct_people_trn_details WHERE voucher_no IN (${list})`, targets);
    await execute(`DELETE FROM ${prefix()}erp_acct_voucher_no WHERE id IN (${list})`, targets);

    const result = await execute(`DELETE FROM ${invoices} WHERE id IN (${list})`, targets);

    return result.affectedRows ?? 0;
}

/** Every accounting transaction table the suite writes to, for a residue check. */
export async function accountingRowCounts(): Promise<Record<string, number>> {
    const tables = [
        'erp_acct_invoices',
        'erp_acct_invoice_details',
        'erp_acct_invoice_account_details',
        'erp_acct_ledger_details',
        'erp_acct_people_trn_details',
    ];

    const counts: Record<string, number> = {};

    for (const table of tables) {
        const rows = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}${table}`);
        counts[table] = Number(rows[0]!.n);
    }

    return counts;
}
