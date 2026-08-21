import { test, expect } from '@utils/test';
import { CrmReportsPage, crmReports, crmReportHeadings } from '@pages/crm/crmReportsPage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { execute, query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * CRM reports — Activity, Customer, Growth.
 *
 * These are server-rendered tables with no REST behind them, so the table text
 * IS the oracle, checked against the data it claims to summarise.
 *
 * The life-stage counts come from the `life_stage` COLUMN on
 * `erp_peoples` — not from the `life_stage` peoplemeta key, which is null for
 * every seeded contact. Asserting against the meta would have compared the
 * report to the wrong store and read as a product fault.
 */
const NOTE_MESSAGE = 'PWERP report oracle note';

test.describe('CRM — reports', () => {
    let page: CrmReportsPage;

    /** Contacts grouped by the life stage the reports actually read. */
    async function lifeStageCounts(): Promise<Record<string, number>> {
        const rows = await query<RowDataPacket[]>(
            `SELECT p.life_stage AS stage, COUNT(*) AS c
               FROM ${prefix()}erp_peoples p
               JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
               JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
              WHERE t.name = 'contact' AND p.life_stage IS NOT NULL
              GROUP BY p.life_stage`
        );

        return Object.fromEntries(rows.map((row) => [String(row.stage), Number(row.c)]));
    }

    async function clearOwnedActivities(): Promise<void> {
        await execute(`DELETE FROM ${prefix()}erp_crm_customer_activities WHERE message = ?`, [NOTE_MESSAGE]);
    }

    test.beforeEach(async ({ page: p }) => {
        page = new CrmReportsPage(p);
        page.watchServerErrors();
        await clearOwnedActivities();
    });

    test.afterAll(async () => {
        await clearOwnedActivities();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    for (const report of crmReports) {
        test(`the ${report} renders`, { tag: ['@tier1', '@crm', '@crm-reports', '@smoke'] }, async () => {
            await page.goto(report);

            expect(await page.bodyText(), 'the report paints its heading').toContain(crmReportHeadings[report]);
            expect(page.serverErrorList(), 'without a server error').toEqual([]);
        });
    }

    // ---- Tier 2 ----------------------------------------------------------

    test('the customer report counts contacts by the life stage they hold', { tag: ['@tier2', '@crm', '@crm-reports'] }, async () => {
        const stages = await lifeStageCounts();

        await page.goto('customer-report');

        for (const [stage, count] of Object.entries(stages)) {
            const label = stage.charAt(0).toUpperCase() + stage.slice(1);
            expect(await page.countFor(label), `${label} is reported as ${count}`).toBe(count);
        }
    });

    test('the growth report agrees with the customer report', { tag: ['@tier2', '@crm', '@crm-reports'] }, async () => {
        // Two reports computed independently from the same contacts. If either
        // aggregation drifts they disagree, and neither alone would show it —
        // the same reasoning as the accounting balance-sheet/income-statement
        // cross-check.
        const stages = await lifeStageCounts();

        await page.goto('growth-report');

        for (const [stage, count] of Object.entries(stages)) {
            const label = stage.charAt(0).toUpperCase() + stage.slice(1);
            expect(await page.countFor(label), `growth reports ${label} as ${count} too`).toBe(count);
        }
    });

    test('the activity report counts an activity of a type it displays', { tag: ['@tier2', '@crm', '@crm-reports', '@flow'] }, async () => {
        // `new_note` is one of the four types the report's `switch` recognises.
        // Asserting with a type it displays is the point: a type it drops would
        // make this pass for the wrong reason and prove nothing about counting.
        const before = await page.goto('activity-report').then(() => page.countFor('Total'));

        await execute(
            `INSERT INTO ${prefix()}erp_crm_customer_activities (user_id, type, message, created_by, created_at, updated_at)
             SELECT p.id, 'new_note', ?, 1, NOW(), NOW()
               FROM ${prefix()}erp_peoples p
               JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
               JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
              WHERE t.name = 'contact' ORDER BY p.id LIMIT 1`,
            [NOTE_MESSAGE]
        );

        await page.goto('activity-report');

        expect(await page.countFor('Notes'), 'the note is counted under Notes').toBeGreaterThanOrEqual(1);
        expect(await page.countFor('Total'), 'and the Total rises by one').toBe((Number.isNaN(before) ? 0 : before) + 1);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('the reports are closed to an employee', { tag: ['@tier3', '@crm', '@crm-reports', '@authz'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const asEmployee = new CrmReportsPage(await context.newPage());

        await asEmployee.goto('customer-report');

        // Denial first: the refusal is a bare `wp_die()` page with no
        // `#wpbody-content`, so reading the admin body there times out.
        const denied = await asEmployee.isAccessDenied();
        const sawReport = denied ? false : (await asEmployee.bodyText()).includes('Customer Report');

        expect(denied || !sawReport, 'an employee never reaches the CRM reports').toBe(true);

        await context.close();
    });
});
