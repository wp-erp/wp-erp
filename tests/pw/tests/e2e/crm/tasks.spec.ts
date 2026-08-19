import { test, expect } from '@utils/test';
import { TasksPage, taskColumns } from '@pages/crm/tasksPage';
import { ActivitiesPage } from '@pages/crm/activitiesPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupCrmActivities } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

async function seededContactId(): Promise<number> {
    const rows = await query<RowDataPacket[]>(
        `SELECT p.id FROM ${prefix()}erp_peoples p
           JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
           JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
          WHERE t.name = 'contact' ORDER BY p.id LIMIT 1`
    );

    return Number(rows[0]!.id);
}

test.describe('CRM — Tasks', () => {
    let page: TasksPage;
    let contactId: number;

    test.beforeAll(async () => {
        await cleanupCrmActivities('pwerp task');
        contactId = await seededContactId();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new TasksPage(p);
    });

    test.afterAll(async () => {
        await cleanupCrmActivities('pwerp task');
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the tasks list renders its captured columns', { tag: ['@tier1', '@crm-tasks', '@smoke'] }, async () => {
        await page.goto();

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);

        const headers = await page.columnHeaders();

        for (const column of taskColumns) {
            expect(headers, `column "${column}"`).toContain(column);
        }
    });

    test('a task can be created from a contact feed', { tag: ['@tier1', '@crm-tasks', '@crud'] }, async ({ page: p }) => {
        // KNOWN DEFECT — ERP-143. "Create Task" is rendered `disabled` and never
        // enables: it is bound to `:disabled="!isValid"`, `isValid` needs
        // `feedData.message`, and that is only set by a `trix-change` listener
        // attached once in `activate()` to `find('trix-editor').get(0)`
        // (`crm-app.js:194`) — which is not the editor the user types into.
        // Measured live: typing fires 16 trix-change events, exactly one editor
        // exists, and the model still never updates. Schedule is broken the same
        // way; New Note on the same feed works.
        //
        // Asserted as it should behave; expected to fail until the binding is fixed.
        test.fail();

        const feed = new ActivitiesPage(p);
        await feed.gotoContact(contactId);
        await feed.fillTask(`pwerp task ${uniqueId('t')}`);

        expect(await feed.canCreateTask(), 'a completed task form can be submitted').toBe(true);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('CRM tasks are closed to an employee', { tag: ['@tier3', '@crm-tasks', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new TasksPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach CRM Tasks').toBe(true);
    });
});
