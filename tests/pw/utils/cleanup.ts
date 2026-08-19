/**
 * Removes records the suite itself created, so the demo site stays presentable
 * and repeated runs do not drift (lists paginating further each time, counts
 * creeping up).
 *
 * Deliberately narrow: it only ever matches the suite's own tokens — the
 * `pwerp` prefix from `helpers.uniqueId`, and the `Qa*`/`Diag*` person names the
 * HRM specs generate. Seeded demo data (Northwind Analytics) is never touched.
 */
import { execute, prefix, query } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

/**
 * The unambiguous marker for a suite-created person is the e-mail domain:
 * `helpers.uniqueEmail()` always mints `@example.test`, while every seeded demo
 * employee is `@northwind-analytics.test`. Matching on names was tried and was
 * wrong — `uniqueName('Hire')` yields "HireZvoxuve", not "QaHire…".
 */
const SUITE_EMAIL_DOMAIN = '%@example.test';

export async function cleanupDepartments(): Promise<number> {
    const result = await execute(`DELETE FROM ${prefix()}erp_hr_depts WHERE title LIKE 'pwerp%' OR title LIKE 'DiagDept%'`);
    return result.affectedRows ?? 0;
}

export async function cleanupDesignations(): Promise<number> {
    const result = await execute(`DELETE FROM ${prefix()}erp_hr_designations WHERE title LIKE 'pwerp%'`);
    return result.affectedRows ?? 0;
}

/** Employees the suite hired, plus their WordPress users. */
export async function cleanupEmployees(): Promise<number> {
    const rows = await query<RowDataPacket[]>(
        `SELECT DISTINCT u.ID FROM ${prefix()}users u
           JOIN ${prefix()}erp_hr_employees e ON e.user_id = u.ID
          WHERE u.user_email LIKE ?`,
        [SUITE_EMAIL_DOMAIN]
    );

    let removed = 0;

    for (const row of rows) {
        const id = Number(row.ID);
        await execute(`DELETE FROM ${prefix()}erp_hr_employees WHERE user_id = ?`, [id]);
        await execute(`DELETE FROM ${prefix()}usermeta WHERE user_id = ?`, [id]);
        await execute(`DELETE FROM ${prefix()}users WHERE ID = ?`, [id]);
        removed++;
    }

    // Users seeded straight onto the example.test domain by earlier probes.
    await execute(`DELETE FROM ${prefix()}erp_hr_employees WHERE user_id NOT IN (SELECT ID FROM ${prefix()}users)`);

    return removed;
}

/** Holidays the suite created. Seeded demo holidays are named from seedData. */
export async function cleanupHolidays(): Promise<number> {
    const result = await execute(
        `DELETE FROM ${prefix()}erp_hr_holiday WHERE title LIKE 'Holiday%' OR title LIKE 'Range%' OR title LIKE 'Leap%' OR title LIKE 'Xss%' OR title LIKE 'Datecheck%'`
    );
    return result.affectedRows ?? 0;
}

/**
 * Leave policies the suite created, matched on the marker written into their
 * description. Seeded demo policies carry their own descriptions from seedData.
 */
export async function cleanupLeavePolicies(): Promise<number> {
    const result = await execute(`DELETE FROM ${prefix()}erp_hr_leave_policies WHERE description = ?`, ['Created by the automated suite.']);
    return result.affectedRows ?? 0;
}

/**
 * Entitlements the suite assigned — and the deduction rows its approvals wrote.
 *
 * `wp_erp_hr_leave_entitlements` is a LEDGER, not one row per entitlement:
 * granting writes `day_in = <policy days>` with our marker in `description`,
 * and approving a request writes a SECOND row with `day_out = <days approved>`
 * and `description = 'Approved'`. "Available" is the difference. Deleting only
 * the marked rows therefore leaves the deductions behind, and the next run sees
 * a balance of 17 where it expects 20.
 *
 * So: find every (user_id, leave_id) pair the suite granted, then clear the
 * whole ledger for those pairs.
 */
export async function cleanupEntitlements(): Promise<number> {
    const pairs = await query<RowDataPacket[]>(
        `SELECT DISTINCT user_id, leave_id FROM ${prefix()}erp_hr_leave_entitlements WHERE description = ?`,
        ['Assigned by the automated suite.']
    );

    let removed = 0;

    for (const pair of pairs) {
        const result = await execute(`DELETE FROM ${prefix()}erp_hr_leave_entitlements WHERE user_id = ? AND leave_id = ?`, [
            Number(pair.user_id),
            Number(pair.leave_id),
        ]);
        removed += result.affectedRows ?? 0;
    }

    return removed;
}

/** Leave requests the suite raised, matched on the marker in `reason`. */
export async function cleanupLeaveRequests(): Promise<number> {
    const result = await execute(`DELETE FROM ${prefix()}erp_hr_leave_requests WHERE reason = ?`, ['Raised by the automated suite.']);
    return result.affectedRows ?? 0;
}

export async function cleanupAll(): Promise<Record<string, number>> {
    return {
        departments: await cleanupDepartments(),
        designations: await cleanupDesignations(),
        employees: await cleanupEmployees(),
        holidays: await cleanupHolidays(),
        leavePolicies: await cleanupLeavePolicies(),
        entitlements: await cleanupEntitlements(),
        leaveRequests: await cleanupLeaveRequests(),
    };
}
