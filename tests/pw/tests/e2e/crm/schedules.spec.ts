import { test, expect } from '@utils/test';
import { SchedulesPage, calendarViews, scheduleScopes } from '@pages/crm/schedulesPage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { execute, query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * CRM schedules.
 *
 * A schedule is an activity of type `log_activity` whose `start_date` is in the
 * FUTURE — a past one is a log, not a schedule, and the product draws that line
 * at `functions-customer.php:756-758`. Ownership is `created_by`, which drives
 * the My / All scopes.
 *
 * Creating one from a contact feed is broken (ERP-143 / erp-pro#958, the same
 * disabled-submit defect that blocks Create Task); `tasks.spec.ts` guards the
 * task half, and the guard here covers the schedule half so both flip green
 * together when the trix listener is fixed.
 */
const UPCOMING = 'PWERP upcoming schedule';
const PAST = 'PWERP past schedule';

test.describe('CRM — schedules', () => {
    let page: SchedulesPage;
    let contactId = 0;

    async function clearOwned(): Promise<void> {
        await execute(`DELETE FROM ${prefix()}erp_crm_customer_activities WHERE message IN (?, ?)`, [UPCOMING, PAST]);
    }

    /** Writes a schedule the way the product stores one. */
    async function seedSchedule(message: string, daysFromNow: number, createdBy = 1): Promise<void> {
        await execute(
            `INSERT INTO ${prefix()}erp_crm_customer_activities
                (user_id, type, message, log_type, start_date, end_date, created_by, created_at, updated_at)
             VALUES (?, 'log_activity', ?, 'meeting',
                     DATE_ADD(NOW(), INTERVAL ? DAY), DATE_ADD(NOW(), INTERVAL ? DAY), ?, NOW(), NOW())`,
            [contactId, message, daysFromNow, daysFromNow, createdBy]
        );
    }

    test.beforeAll(async () => {
        const contacts = await query<RowDataPacket[]>(
            `SELECT p.id FROM ${prefix()}erp_peoples p
               JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
               JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
              WHERE t.name = 'contact' ORDER BY p.id LIMIT 1`
        );
        contactId = Number(contacts[0]!.id);
    });

    test.beforeEach(async ({ page: p }) => {
        page = new SchedulesPage(p);
        page.watchServerErrors();
        await clearOwned();
    });

    test.afterAll(async () => {
        await clearOwned();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the schedules calendar renders with its views and scopes', { tag: ['@tier1', '@crm', '@crm-schedules', '@smoke'] }, async () => {
        await page.goto();

        expect(await page.hasCalendar(), 'the calendar is drawn').toBe(true);

        const body = await page.bodyText();
        for (const view of calendarViews) {
            expect(body, `the ${view} view is offered`).toContain(view);
        }
        for (const scope of scheduleScopes) {
            expect(body, `the ${scope} scope is offered`).toContain(scope);
        }

        expect(page.serverErrorList(), 'without a server error').toEqual([]);
    });

    test('an upcoming schedule is painted on the calendar', { tag: ['@tier1', '@crm', '@crm-schedules', '@flow'] }, async () => {
        await page.goto();
        const before = (await page.eventTitles()).length;

        await seedSchedule(UPCOMING, 2);

        await page.goto();
        const after = await page.eventTitles();

        // The calendar titles an event from its log_type and time, never from
        // the activity message — so the oracle is "one more event, of the type
        // seeded", not the message text.
        expect(after.length, 'one more event is drawn').toBe(before + 1);
        expect(after.join(' | '), 'and it is the meeting that was scheduled').toMatch(/Meeting/i);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('a schedule is a log_activity dated in the future', { tag: ['@tier2', '@crm', '@crm-schedules'] }, async () => {
        // The product's own rule: `type = log_activity` AND `start_date > now`
        // is a SCHEDULE; the same type dated in the past is a log. Seed one of
        // each and assert the split, so a change to that boundary is caught.
        await seedSchedule(UPCOMING, 3);
        await seedSchedule(PAST, -3);

        const rows = await query<RowDataPacket[]>(
            `SELECT message, start_date > NOW() AS upcoming
               FROM ${prefix()}erp_crm_customer_activities
              WHERE message IN (?, ?) AND type = 'log_activity'`,
            [UPCOMING, PAST]
        );

        expect(rows, 'both were stored as log_activity').toHaveLength(2);
        expect(Number(rows.find((r) => r.message === UPCOMING)!.upcoming), 'the future one is a schedule').toBe(1);
        expect(Number(rows.find((r) => r.message === PAST)!.upcoming), 'the past one is not').toBe(0);
    });

    test("another user's schedule stays off my calendar", { tag: ['@tier2', '@crm', '@crm-schedules', '@authz'] }, async () => {
        // The calendar opens on **My Schedules**, which scopes by `created_by`
        // (`:2623`, `:2656`). A schedule belonging to someone else must not be
        // drawn there. Measured as a delta against the same view, so a calendar
        // that happened to be empty cannot make this pass by accident.
        await page.goto();
        const mine = (await page.eventTitles()).length;

        await seedSchedule(UPCOMING, 2, 999);

        const rows = await query<RowDataPacket[]>(
            `SELECT created_by FROM ${prefix()}erp_crm_customer_activities WHERE message = ?`,
            [UPCOMING]
        );
        expect(Number(rows[0]!.created_by), 'precondition: it belongs to another user').toBe(999);

        await page.goto();

        expect(await page.eventTitles(), 'my calendar is unchanged').toHaveLength(mine);
    });

    test('my own schedule is drawn on my calendar', { tag: ['@tier2', '@crm', '@crm-schedules', '@flow'] }, async () => {
        // The positive control for the scoping case above: the same seed, owned
        // by ME, does appear. Without it, "not drawn" could just mean the
        // calendar never draws anything.
        await page.goto();
        const before = (await page.eventTitles()).length;

        await seedSchedule(UPCOMING, 2, 1);

        await page.goto();

        expect(await page.eventTitles(), 'my own schedule is drawn').toHaveLength(before + 1);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test.fail(
        'a schedule can be created from a contact',
        { tag: ['@tier3', '@crm', '@crm-schedules', '@known-defect'] },
        async ({ page: p }) => {
            // KNOWN DEFECT — ERP-143 / erp-pro#958, the schedule half of the same
            // fault `tasks.spec.ts` guards for tasks: the submit is bound to
            // `:disabled="!isValid"`, `isValid` needs `feedData.message`, and the
            // `trix-change` listener that would set it is attached once to a
            // different editor instance (`crm-app.js:194`). The button never
            // enables, so no schedule can be created from a contact.
            await p.goto(
                `/wp-admin/admin.php?page=erp-crm&section=contact&sub-section=contacts&action=view&id=${contactId}`,
                { waitUntil: 'domcontentloaded' }
            );
            await p.waitForTimeout(3000);

            const scheduleTab = p.locator('a, button').filter({ hasText: /^\s*Schedule\s*$/i }).first();
            if (await scheduleTab.count()) {
                await scheduleTab.click();
                await p.waitForTimeout(1200);
            }

            const editor = p.locator('trix-editor').first();
            expect(await editor.count(), 'precondition: the feed editor is present').toBeGreaterThan(0);

            await editor.click();
            await p.keyboard.type('QA schedule from a contact');
            await p.waitForTimeout(800);

            // The submit is an `<input type="submit">`, NOT a `<button>` — a
            // button-only locator finds nothing and the guard then fails on its
            // own precondition, which would keep "failing" after the defect was
            // fixed and prove nothing.
            const submit = p.locator('#wpbody-content input[type="submit"][value*="Create Schedule" i]').first();
            expect(await submit.count(), 'precondition: the Create Schedule control is present').toBeGreaterThan(0);

            expect(await submit.isEnabled(), 'the submit enables once a message is typed').toBe(true);
        }
    );

    test('the schedules calendar is closed to an employee', { tag: ['@tier3', '@crm', '@crm-schedules', '@authz'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const asEmployee = new SchedulesPage(await context.newPage());

        await asEmployee.goto();

        const denied = await asEmployee.isAccessDenied();
        const sawCalendar = denied ? false : await asEmployee.hasCalendar();

        expect(denied || !sawCalendar, 'an employee never reaches the CRM calendar').toBe(true);

        await context.close();
    });
});
