import { test, expect } from '@utils/test';
import { AccountingPage } from '@pages/accounting/accountingPage';
import { ContactsPage } from '@pages/crm/contactsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { cleanupPersonByEmail, personRowCount, personTypes } from '@utils/cleanupAccounting';
import { closeDb, prefix, query } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Core — people, the identity shared by CRM and Accounting.
 *
 * A CRM contact, an accounting customer and a vendor are not three records:
 * they are one row in `erp_peoples` wearing one or more **types**, joined
 * through `erp_people_type_relations`. Everything here is about that seam,
 * because it is the only place in ERP where two modules write the same row.
 *
 * The product's own rules, read from `PeopleModal.vue:236-272` rather than
 * assumed:
 *   - an e-mail already held by a **contact or company** may take on a new
 *     accounting type, after the user confirms an "Import & Update" prompt;
 *   - an e-mail already held by a **customer or vendor** is refused outright.
 *
 * So the invariant under test is one person per e-mail, many types — and the
 * DB is the oracle, since a second `erp_peoples` row is exactly the corruption
 * a list screen would hide behind a `GROUP BY`.
 */

/** This file owns one person and creates them fresh, so nothing seeded is touched. */
const PERSON = {
    firstName: 'Imogen',
    lastName: 'Blackwood',
    email: 'imogen.blackwood@people-spec.test',
};

test.describe('Core — people', () => {
    let page: AccountingPage;
    let contacts: ContactsPage;

    /** An e-mail that is already a vendor, for the refusal case. */
    async function seededVendorEmail(): Promise<string> {
        const rows = await query<RowDataPacket[]>(
            `SELECT p.email
               FROM ${prefix()}erp_peoples p
               JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
               JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
              WHERE t.name = 'vendor'
              LIMIT 1`
        );

        return String(rows[0]!.email);
    }

    test.beforeEach(async ({ page: p }) => {
        page = new AccountingPage(p);
        contacts = new ContactsPage(p);
        page.watchServerErrors();

        await cleanupPersonByEmail(PERSON.email);
    });

    test.afterAll(async () => {
        await cleanupPersonByEmail(PERSON.email);
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('a CRM contact and an accounting customer are one person, not two', { tag: ['@tier1', '@free', '@people', '@flow'] }, async () => {
        await contacts.create(PERSON);

        expect(await personTypes(PERSON.email), 'precondition: the contact exists').toEqual(['contact']);

        await page.openPersonModal('customers');
        await page.fillPerson(PERSON);
        await page.submitPerson();

        expect(await page.confirmCrmImport(), 'the CRM e-mail is recognised and offered for import').toBe(true);

        expect(await personRowCount(PERSON.email), 'still a single person').toBe(1);
        expect(await personTypes(PERSON.email), 'now holding both types').toContain('customer');
        expect(await personTypes(PERSON.email), 'without losing the CRM type').toContain('contact');
    });

    test('a person with two types is listed once by each module', { tag: ['@tier1', '@free', '@people'] }, async ({ page: p }) => {
        await contacts.create(PERSON);
        await page.openPersonModal('customers');
        await page.fillPerson(PERSON);
        await page.submitPerson();
        await page.confirmCrmImport();

        expect(await personTypes(PERSON.email), 'precondition: the person carries both types').toContain('customer');

        await page.gotoRoute('customers');
        const customers = (await p.locator('#wpbody-content').innerText()).replace(/\s+/g, ' ');

        expect(
            (customers.match(new RegExp(PERSON.lastName, 'g')) ?? []).length,
            'exactly one row on the customers list'
        ).toBe(1);

        await contacts.goto();

        expect(await contacts.hasContact(`${PERSON.firstName} ${PERSON.lastName}`), 'and still a CRM contact').toBe(true);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test.fail(
        'converting a contact writes the new type once',
        { tag: ['@tier2', '@free', '@people', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-162 (erp-pro#980). The one save writes the
            // `customer` relation TWICE: `erp_insert_people()` assigns the type
            // (`functions-people.php:733-737`), then `erp_acct_insert_people()`
            // inserts the same pair again unconditionally
            // (`modules/accounting/includes/functions/people.php:118-129`), and
            // the table has no unique key on (people_id, people_types_id).
            //
            // The preconditions below assert the person and the conversion both
            // happened, so this guard can only fail on the duplicate itself.
            await contacts.create(PERSON);
            await page.openPersonModal('customers');
            await page.fillPerson(PERSON);
            await page.submitPerson();

            expect(await page.confirmCrmImport(), 'precondition: the import prompt was accepted').toBe(true);

            const types = await personTypes(PERSON.email);

            expect(types, 'precondition: the customer type was attached').toContain('customer');

            expect(types.filter((t) => t === 'customer'), 'the customer type is held once').toHaveLength(1);
        }
    );

    test('dropping the accounting type leaves the CRM contact standing', { tag: ['@tier2', '@free', '@people', '@flow'] }, async ({ page: p }) => {
        await contacts.create(PERSON);
        await page.openPersonModal('customers');
        await page.fillPerson(PERSON);
        await page.submitPerson();
        await page.confirmCrmImport();

        expect(await personTypes(PERSON.email), 'precondition: the person is a customer').toContain('customer');

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_peoples WHERE email = ?`, [PERSON.email]);
        const peopleId = Number(rows[0]!.id);

        await page.gotoRoute('customers');
        const deleted = await p.evaluate(async (id) => {
            const v = (window as unknown as { erp_acct_var: { rest: { root: string; version: string; nonce: string } } }).erp_acct_var;
            const response = await fetch(`${v.rest.root}${v.rest.version}/accounting/v1/customers/${id}`, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': v.rest.nonce },
            });

            return response.status;
        }, peopleId);

        expect(deleted, 'the customer was removed').toBeLessThan(300);
        expect(await personRowCount(PERSON.email), 'the person survives').toBe(1);
        expect(await personTypes(PERSON.email), 'as a CRM contact only').toEqual(['contact']);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('a vendor cannot be signed up a second time as a customer', { tag: ['@tier3', '@free', '@people', '@validation'] }, async () => {
        const email = await seededVendorEmail();
        const before = await personRowCount(email);

        await page.openPersonModal('customers');
        await page.fillPerson({ firstName: 'Clashing', lastName: 'Entry', email });
        await page.submitPerson();

        expect(await page.personModalErrors(), 'the modal says why, naming both types').toContain(
            'Email already exists as customer/vendor'
        );
        expect(await page.personModalIsOpen(), 'and holds the form open').toBe(true);
        expect(await personRowCount(email), 'no second person is created').toBe(before);
    });

    test('an empty submit is refused and creates nobody', { tag: ['@tier3', '@free', '@people', '@validation'] }, async ({ page: p }) => {
        // The three starred inputs carry the HTML `required` attribute, so the
        // BROWSER stops the submit and Vue's own validator never runs. That is
        // why this asserts on constraint validation rather than on the
        // "First name is required" strings in `PeopleModal.vue:313-321` — those
        // are unreachable from the UI, and asserting them would have been a
        // test written from the source instead of from the product.
        const before = await query<RowDataPacket[]>(`SELECT COUNT(*) AS c FROM ${prefix()}erp_peoples`);

        await page.openPersonModal('customers');
        await page.submitPerson();

        expect(await page.personModalIsOpen(), 'the modal stays open').toBe(true);
        expect(
            await p.locator('input:invalid:visible').count(),
            'the browser marks the empty required fields invalid'
        ).toBeGreaterThan(0);

        const after = await query<RowDataPacket[]>(`SELECT COUNT(*) AS c FROM ${prefix()}erp_peoples`);

        expect(Number(after[0]!.c), 'and nobody was created').toBe(Number(before[0]!.c));
    });
});
