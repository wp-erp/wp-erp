import { test, expect } from '@utils/test';
import { ContactsPage, contactColumns } from '@pages/crm/contactsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { uniqueName, uniqueEmail } from '@utils/helpers';
import { crmContacts } from '@utils/seedData';
import { cleanupCrmContacts } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

test.describe('CRM — Contacts', () => {
    let page: ContactsPage;

    test.beforeAll(async () => {
        await cleanupCrmContacts();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new ContactsPage(p);
    });

    test.afterAll(async () => {
        await cleanupCrmContacts();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the contacts list renders its captured columns', { tag: ['@tier1', '@crm-contacts', '@smoke'] }, async () => {
        await page.goto();

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);

        const headers = await page.columnHeaders();

        for (const column of contactColumns) {
            expect(headers, `column "${column}"`).toContain(column);
        }
    });

    test('the list holds exactly the seeded contacts', { tag: ['@tier1', '@crm-contacts'] }, async () => {
        await page.goto();

        const names = await page.contactNames();

        for (const contact of crmContacts) {
            expect(names, `${contact.firstName} ${contact.lastName} is listed`).toContain(`${contact.firstName} ${contact.lastName}`);
        }

        expect(names, 'and nobody else').toHaveLength(crmContacts.length);
    });

    test('a contact can be created and is stored as a person of type contact', { tag: ['@tier1', '@crm-contacts', '@crud'] }, async () => {
        const firstName = uniqueName('Pwerp');
        const lastName = uniqueName('Contact');
        const email = uniqueEmail();

        await page.create({ firstName, lastName, email });

        // Contacts share `erp_peoples` with customers, vendors and employees;
        // the TYPE is what distinguishes them.
        const rows = await query<RowDataPacket[]>(
            `SELECT p.id, t.name AS type FROM ${prefix()}erp_peoples p
               JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
               JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
              WHERE p.email = ?`,
            [email]
        );

        expect(rows, 'one person row is written').toHaveLength(1);
        expect(rows[0]!.type, 'typed as a CRM contact').toBe('contact');

        await page.goto();
        expect(await page.hasContact(`${firstName} ${lastName}`), 'the contact is listed').toBe(true);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('the contacts REST collection returns a collection', { tag: ['@tier3', '@crm-contacts', '@api'] }, async ({ request }) => {
        // KNOWN DEFECT — ERP-135 / erp-pro#950, still open on this build.
        // `GET erp/v1/crm/contacts` answers with a SINGLE raw contact object and
        // stops: a stray `wp_send_json` inside the loop at
        // `ContactsController.php:378` ends the request on the first row. The
        // client gets one contact, unwrapped, with no total and no pagination.
        //
        // Asserted as it should behave; expected to fail until fixed.
        test.fail();

        const api = new ApiUtils(request);
        const response = await api.get(endPoints.crm.contacts);
        const body = (await response.text()).trim();
        const parsed: unknown = JSON.parse(body);

        expect(Array.isArray(parsed), 'the collection route returns an array').toBe(true);
        expect((parsed as unknown[]).length, 'and holds every seeded contact').toBeGreaterThanOrEqual(crmContacts.length);
    });

    test('CRM is closed to an employee', { tag: ['@tier3', '@crm-contacts', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new ContactsPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach CRM Contacts').toBe(true);
    });
});
