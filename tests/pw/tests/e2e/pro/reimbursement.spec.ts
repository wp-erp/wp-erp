import { test, expect } from '@utils/test';
import { ReimbursementPage } from '@pages/pro/reimbursementPage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { toDate } from '@utils/helpers';
import { execute, query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Reimbursement (@pro) — money an employee claims back from the company.
 *
 * The interesting surface here is not the form, it is WHO may act on a claim.
 * Every route the module registers — read, create and update alike — is guarded
 * by a single capability, `erp_view_list`, which `erp_hr_map_meta_caps()` grants
 * to every `employee`. Nothing anywhere checks whether the caller owns the claim
 * they are touching.
 *
 * The three `test.fail()` guards below are all ERP-157, and they are written
 * from the employee's own session with the `wp_rest` nonce WordPress prints for
 * anyone who can load `profile.php` — nothing is borrowed from an administrator.
 */
const REQUESTS = 'erp_acct_reimburse_requests';

test.describe('Reimbursement @pro', () => {
    async function clearRequests(): Promise<void> {
        await execute(`DELETE FROM ${prefix()}erp_acct_reimburse_request_details`).catch(() => undefined);
        await execute(`DELETE FROM ${prefix()}${REQUESTS}`);
    }

    async function requestRow(reference: string): Promise<RowDataPacket | undefined> {
        const rows = await query<RowDataPacket[]>(
            `SELECT id, people_id, amount_total, status, reference FROM ${prefix()}${REQUESTS} WHERE reference = ?`,
            [reference]
        );

        return rows[0];
    }

    test.beforeEach(async () => {
        await clearRequests();
    });

    test.afterAll(async () => {
        await clearRequests();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('a reimbursement claim is raised and listed', { tag: ['@tier1', '@pro', '@reimbursement', '@crud'] }, async ({ page: p }) => {
        const page = new ReimbursementPage(p);

        const created = await page.createRequest({ reference: 'ADMIN-CLAIM', amount: 500, trnDate: toDate() });

        expect(created.status, 'the claim is accepted').toBe(201);

        const row = await requestRow('ADMIN-CLAIM');
        expect(row, 'and stored').toBeDefined();
        expect(Number(row!.amount_total), 'for the amount claimed').toBeCloseTo(500, 2);

        const listed = await page.listRequests();
        expect(listed.status, 'the list reads back').toBe(200);
        expect(listed.references, 'and contains the claim').toContain('ADMIN-CLAIM');
    });

    // ---- Tier 3 — who may act on a claim ---------------------------------

    test('an employee cannot reach the accounting screens', { tag: ['@tier3', '@pro', '@reimbursement', '@authz'] }, async ({ browser }) => {
        // The canary for the three guards below. It proves the employee session
        // really is low-privileged — without it, a guard that "fails" would be
        // indistinguishable from a test accidentally running as an administrator.
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const p = await context.newPage();

        await p.goto('/wp-admin/admin.php?page=erp-accounting', { waitUntil: 'domcontentloaded' });
        await p.waitForTimeout(1500);

        const reachable = await p.evaluate(() => Boolean((window as unknown as { erp_acct_var?: unknown }).erp_acct_var));
        expect(reachable, 'the accounting app never loads for an employee').toBe(false);

        await context.close();
    });

    test.fail(
        'an employee cannot raise a claim straight into the payable state',
        { tag: ['@tier3', '@pro', '@reimbursement', '@authz', '@known-defect'] },
        async ({ browser }) => {
            // KNOWN DEFECT — ERP-157. Every route is guarded by `erp_view_list`,
            // which every employee holds, and `erp_acct_reimb_insert_request()`
            // defaults a new claim's status to 2 — "Awaiting Payment". An
            // employee can therefore file a claim for any figure and it arrives
            // already queued for payment, with no approval step in between.
            const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
            const page = new ReimbursementPage(await context.newPage());

            const created = await page.createRequest({ reference: 'EMP-SELF', amount: 4242, trnDate: toDate() });

            const row = await requestRow('EMP-SELF');

            await context.close();

            // The defect: it was accepted.
            expect(created.status, 'an employee is refused the create route').not.toBe(201);
            expect(row, 'and no claim is written').toBeUndefined();
        }
    );

    test.fail(
        "an employee cannot read other people's claims",
        { tag: ['@tier3', '@pro', '@reimbursement', '@authz', '@known-defect'] },
        async ({ browser, page: p }) => {
            // KNOWN DEFECT — ERP-157. `get_employee_reimb_requests()` takes an
            // optional `people_id` filter and applies no default, so the list
            // route returns every claim on the site to any employee who asks.
            const admin = new ReimbursementPage(p);
            expect(
                (await admin.createRequest({ reference: 'SOMEONE-ELSE', amount: 500, trnDate: toDate() })).status,
                'precondition: another person has a claim on file'
            ).toBe(201);

            const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
            const asEmployee = new ReimbursementPage(await context.newPage());

            const listed = await asEmployee.listRequests();

            await context.close();

            // The defect: the other person's claim comes back.
            expect(listed.references, "an employee does not see another person's claim").not.toContain('SOMEONE-ELSE');
        }
    );

    test.fail(
        "an employee cannot alter another person's claim",
        { tag: ['@tier3', '@pro', '@reimbursement', '@authz', '@money', '@known-defect'] },
        async ({ browser, page: p }) => {
            // KNOWN DEFECT — ERP-157, and the sharpest face of it.
            // `update_employee_reimb_request()` performs no ownership check, and
            // `erp_acct_reimb_update_request()` rewrites the amount and forces
            // the status back to "Awaiting Payment". One employee can therefore
            // raise another's claim to any figure and leave it queued for payment.
            const admin = new ReimbursementPage(p);
            expect(
                (await admin.createRequest({ reference: 'VICTIM', amount: 500, trnDate: toDate() })).status,
                'precondition: another person has a claim on file'
            ).toBe(201);

            const target = await requestRow('VICTIM');
            expect(target, 'precondition: the claim exists').toBeDefined();
            expect(Number(target!.amount_total), 'precondition: for 500').toBeCloseTo(500, 2);

            const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
            const asEmployee = new ReimbursementPage(await context.newPage());

            await asEmployee.updateRequest(Number(target!.id), {
                reference: 'HIJACKED',
                amount: 9999,
                trnDate: toDate(),
            });

            await context.close();

            const after = await query<RowDataPacket[]>(
                `SELECT amount_total, reference FROM ${prefix()}${REQUESTS} WHERE id = ?`,
                [Number(target!.id)]
            );

            // The defect: the claim was rewritten by someone who does not own it.
            expect(Number(after[0]!.amount_total), "another person's claim is untouched").toBeCloseTo(500, 2);
        }
    );
});
