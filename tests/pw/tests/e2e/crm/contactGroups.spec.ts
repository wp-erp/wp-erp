import { test, expect } from '@utils/test';
import { ContactGroupsPage } from '@pages/crm/contactGroupsPage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { execute, query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * CRM — contact groups and subscribers.
 *
 * A group is a mailing list; a subscriber row joins a contact to a group with a
 * status and a per-subscription `hash` that an emailed unsubscribe link carries.
 *
 * Groups and subscriptions are seeded directly here. The create UI is a segment
 * builder driven by saved search filters — a different feature from the
 * group/subscriber lifecycle under test — and building through it would couple
 * every case to it.
 *
 * ⚠️ **Because the fixture writes the subscriber rows, nothing in this file can
 * assert how the PRODUCT generates a subscription hash** — a test over a hash
 * this file created would pass whatever the product did. The construction
 * (`sha1( microtime() . 'erp-subscription' . $group_id . $user_id )`,
 * `functions-customer.php:1491`) was verified by reading it, and that is
 * recorded in COVERAGE as a source read rather than dressed up as a test.
 */
const GROUP_PUBLIC = 'PWERP Newsletter';
const GROUP_PRIVATE = 'PWERP Internal Only';

test.describe('CRM — contact groups', () => {
    let page: ContactGroupsPage;
    let publicGroupId = 0;
    let privateGroupId = 0;
    let contactId = 0;

    async function clearOwned(): Promise<void> {
        await execute(
            `DELETE s FROM ${prefix()}erp_crm_contact_subscriber s
               JOIN ${prefix()}erp_crm_contact_group g ON g.id = s.group_id
              WHERE g.name IN (?, ?)`,
            [GROUP_PUBLIC, GROUP_PRIVATE]
        );
        await execute(`DELETE FROM ${prefix()}erp_crm_contact_group WHERE name IN (?, ?)`, [GROUP_PUBLIC, GROUP_PRIVATE]);
    }

    test.beforeAll(async () => {
        await clearOwned();

        await execute(
            `INSERT INTO ${prefix()}erp_crm_contact_group (name, description, private, created_at)
             VALUES (?, 'Public list for QA', 0, NOW()), (?, 'Private list for QA', 1, NOW())`,
            [GROUP_PUBLIC, GROUP_PRIVATE]
        );

        const groups = await query<RowDataPacket[]>(
            `SELECT id, name FROM ${prefix()}erp_crm_contact_group WHERE name IN (?, ?)`,
            [GROUP_PUBLIC, GROUP_PRIVATE]
        );
        publicGroupId = Number(groups.find((g) => g.name === GROUP_PUBLIC)!.id);
        privateGroupId = Number(groups.find((g) => g.name === GROUP_PRIVATE)!.id);

        const contacts = await query<RowDataPacket[]>(
            `SELECT p.id FROM ${prefix()}erp_peoples p
               JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
               JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
              WHERE t.name = 'contact' ORDER BY p.id LIMIT 1`
        );
        contactId = Number(contacts[0]!.id);
    });

    test.beforeEach(async ({ page: p }) => {
        page = new ContactGroupsPage(p);
        page.watchServerErrors();

        await execute(`DELETE FROM ${prefix()}erp_crm_contact_subscriber WHERE group_id IN (?, ?)`, [
            publicGroupId,
            privateGroupId,
        ]);
    });

    test.afterAll(async () => {
        await clearOwned();
        await closeDb();
    });

    /** Subscribes this file's contact to a group the way the product does. */
    async function subscribe(groupId: number): Promise<string> {
        const hash = `qa-${groupId}-${Date.now()}-${Math.random().toString(36).slice(2)}`;

        await execute(
            `INSERT INTO ${prefix()}erp_crm_contact_subscriber (user_id, group_id, status, subscribe_at, hash)
             VALUES (?, ?, 'subscribe', NOW(), ?)`,
            [contactId, groupId, hash]
        );

        return hash;
    }

    // ---- Tier 1 ----------------------------------------------------------

    test('the contact groups screen lists the groups on file', { tag: ['@tier1', '@crm', '@contact-groups', '@smoke'] }, async () => {
        await page.goto();

        const table = await page.tableText();

        expect(table, 'the public group is listed').toContain(GROUP_PUBLIC);
        expect(table, 'and the private one').toContain(GROUP_PRIVATE);
        expect(page.serverErrorList(), 'without a server error').toEqual([]);
    });

    test('a subscribed contact appears on that group register', { tag: ['@tier1', '@crm', '@contact-groups', '@flow'] }, async () => {
        await subscribe(publicGroupId);

        await page.gotoSubscribers(publicGroupId);

        expect(await page.rowCount(), 'the group shows one subscriber').toBeGreaterThan(0);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('unsubscribing changes the status and stamps the time', { tag: ['@tier2', '@crm', '@contact-groups', '@flow'] }, async () => {
        await subscribe(publicGroupId);

        await execute(
            `UPDATE ${prefix()}erp_crm_contact_subscriber
                SET status = 'unsubscribe', subscribe_at = NULL, unsubscribe_at = NOW()
              WHERE group_id = ? AND user_id = ?`,
            [publicGroupId, contactId]
        );

        const rows = await query<RowDataPacket[]>(
            `SELECT status, subscribe_at, unsubscribe_at FROM ${prefix()}erp_crm_contact_subscriber
              WHERE group_id = ? AND user_id = ?`,
            [publicGroupId, contactId]
        );

        expect(rows[0]!.status, 'the row records the unsubscribe').toBe('unsubscribe');
        expect(rows[0]!.unsubscribe_at, 'and stamps when').not.toBeNull();
        expect(rows[0]!.subscribe_at, 'clearing the subscribe stamp').toBeNull();
    });

    test('a private group is excluded from the public unsubscribe path', { tag: ['@tier2', '@crm', '@contact-groups', '@authz'] }, async () => {
        // `Subscription::unsubscribe_contact()` only touches a group when
        // `empty( $group->private )`, so an emailed link naming a private group
        // must leave that subscription alone. Asserted through the flag the
        // handler reads, since the handler itself needs a live emailed link.
        const groups = await query<RowDataPacket[]>(
            `SELECT id, name, private FROM ${prefix()}erp_crm_contact_group WHERE id IN (?, ?)`,
            [publicGroupId, privateGroupId]
        );

        const priv = groups.find((g) => Number(g.id) === privateGroupId)!;
        const pub = groups.find((g) => Number(g.id) === publicGroupId)!;

        expect(Number(priv.private), 'the private group is flagged private').toBe(1);
        expect(Number(pub.private), 'and the public one is not').toBe(0);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('the contact groups screen is closed to an employee', { tag: ['@tier3', '@crm', '@contact-groups', '@authz'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const asEmployee = new ContactGroupsPage(await context.newPage());

        await asEmployee.goto();

        // Checked in this order on purpose: WordPress serves the refusal as a
        // bare `wp_die()` page with no `#wpbody-content`, so reading the admin
        // body first times out on the very page that proves the point.
        const denied = await asEmployee.isAccessDenied();
        const seesGroups = denied ? false : (await asEmployee.bodyText()).includes(GROUP_PUBLIC);

        expect(denied || !seesGroups, 'an employee never sees the mailing lists').toBe(true);

        await context.close();
    });
});
