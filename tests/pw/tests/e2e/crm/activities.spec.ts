import { test, expect } from '@utils/test';
import { ActivitiesPage, composerTabs, feedFilters } from '@pages/crm/activitiesPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupCrmActivities } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/** A seeded contact to hang activities on — resolved once, by type. */
async function seededContactId(): Promise<number> {
    const rows = await query<RowDataPacket[]>(
        `SELECT p.id FROM ${prefix()}erp_peoples p
           JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
           JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
          WHERE t.name = 'contact' AND p.email LIKE '%@%' ORDER BY p.id LIMIT 1`
    );

    return Number(rows[0]!.id);
}

test.describe('CRM — Activities', () => {
    let page: ActivitiesPage;
    let contactId: number;

    test.beforeAll(async () => {
        await cleanupCrmActivities('pwerp note');
        contactId = await seededContactId();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new ActivitiesPage(p);
    });

    test.afterAll(async () => {
        await cleanupCrmActivities('pwerp note');
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the contact feed renders its composer tabs and filters', { tag: ['@tier1', '@crm-activities', '@smoke'] }, async () => {
        await page.gotoContact(contactId);

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
        expect(await page.rendersComposerTabs(), `all of ${composerTabs.join(', ')}`).toEqual([]);
        expect(await page.rendersFeedFilters(), `all of ${feedFilters.join(', ')}`).toEqual([]);
    });

    test('a note is logged against the contact and appears in the feed', { tag: ['@tier1', '@crm-activities', '@crud'] }, async () => {
        const text = `pwerp note ${uniqueId('n')}`;

        await page.gotoContact(contactId);
        await page.addNote(text);

        // The activity is a row typed by the composer tab that wrote it.
        const rows = await query<RowDataPacket[]>(`SELECT type, user_id, message FROM ${prefix()}erp_crm_customer_activities WHERE message LIKE ?`, [`%${text}%`]);

        expect(rows, 'one activity row is written').toHaveLength(1);
        expect(rows[0]!.type, 'typed as a note').toBe('new_note');
        expect(Number(rows[0]!.user_id), 'attached to the contact it was written on').toBe(contactId);
        expect(await page.hasActivity(text), 'and it shows in the feed').toBe(true);
    });

    test('a contact with no activity says so', { tag: ['@tier1', '@crm-activities'] }, async () => {
        await cleanupCrmActivities('pwerp note');
        await page.gotoContact(contactId);

        expect(await page.isEmpty(), 'the empty feed states it plainly').toBe(true);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('the contact feed is closed to an employee', { tag: ['@tier3', '@crm-activities', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new ActivitiesPage(p);
            await asEmployee.gotoAdmin('erp-crm', { section: 'contact', action: 'view', id: contactId });
            return asEmployee.isAccessDenied();
        });

        expect(denied, "an employee cannot read a contact's activity feed").toBe(true);
    });
});
