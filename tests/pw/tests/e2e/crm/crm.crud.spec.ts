import { test, expect, type Page } from '@utils/test';
import { CrmPage } from './crmPage';
import { ApiUtils } from '@utils/apiUtils';
import { data, TEST_PREFIX } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';

/**
 * CRM Happy-Path CRUD specs (UI + DB-verified).
 *
 * Grounded strictly in CrmPage (real view ids) and the live DB schema verified
 * this session:
 *   - wp_erp_peoples.email is a NON-unique KEY (app-level dedup only)
 *   - first_name/last_name columns are varchar(60); the modal caps typing at 30
 *   - wp_erp_crm_contact_group (id,name,description,private,created_at,updated_at)
 *   - wp_erp_crm_contact_subscriber UNIQUE(user_id,group_id)
 *
 * The free CRM has NO REST controller, so every data assertion routes through
 * dbUtils. Unproven selectors (single-view tabs, activities composer) are smoke
 * checks only (#wp-erp visible + no critical error).
 *
 * Implements: CRM-HP-01..15 plus the manager/activities/reports happy paths.
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

// CRM-specific tables not present in dbData.ts — derived from the same prefix.
const PREFIX = process.env.DB_PREFIX ?? 'wp';
const crmTables = {
    contactGroup: `${PREFIX}_erp_crm_contact_group`,
    contactSubscriber: `${PREFIX}_erp_crm_contact_subscriber`,
} as const;

/** Read a fresh person row by its (lowercased) email across any type. */
async function peopleRowsByEmail(email: string): Promise<Array<Record<string, unknown>>> {
    return dbUtils.dbQuery<Record<string, unknown>>(
        `SELECT * FROM ${tables.peoples} WHERE email = ? ORDER BY id DESC`,
        [email.toLowerCase().trim()],
    );
}

async function assertHealthy(page: Page): Promise<void> {
    // #wpbody-content is present on every wp-admin screen (#wp-erp only on the
    // contact/company list views), so this is a reliable "page loaded" smoke check.
    await expect(page.locator('#wpbody-content')).toBeVisible();
    await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
}

// ──────────────────────────────────────────────────────────────────────────
// Admin role — full CRUD surface (CRM-HP-01..09, 12..15)
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM CRUD — admin', () => {
    test.use({ storageState: data.auth.adminFile });

    let api: ApiUtils;

    test.beforeAll(async () => {
        api = await ApiUtils.fromStorageState(data.auth.adminFile);
    });

    test.afterAll(async () => {
        await api.dispose();
        // Best-effort cleanup of pw_-prefixed group names created here.
        await dbUtils.deleteRowsLike(crmTables.contactGroup, 'name', TEST_PREFIX).catch(() => {});
    });

    // CRM-HP-01
    test('contacts list loads with Add control', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToContacts();
        await expect(page.locator(crm.contacts.root)).toBeVisible();
        await expect(page.locator(crm.contacts.addNewBtn)).toBeVisible();
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    // CRM-HP-02 + CRM-HP-03
    test('create a contact via the modal (DB-verified) + meta persists', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const contact = data.crm.contact();
        await crm.createContact(contact);

        // Poll: the create AJAX commits just after the modal closes.
        let row: Record<string, unknown> | undefined;
        await expect
            .poll(
                async () => {
                    row = await CrmPage.findTypedPersonByEmail(contact.email, 'contact');
                    return Boolean(row);
                },
                { timeout: 10_000 },
            )
            .toBe(true);
        expect(String(row?.first_name)).toBe(contact.first_name);
        expect(String(row?.email)).toBe(contact.email.toLowerCase());

        // CRM-HP-02: a non-deleted type relation must exist.
        const rel = await dbUtils.dbQuery<{ deleted_at: string | null }>(
            `SELECT r.deleted_at FROM ${tables.peopleTypeRelations} r
                JOIN ${tables.peopleTypes} t ON t.id = r.people_types_id
             WHERE r.people_id = ? AND t.name = 'contact'`,
            [row?.id],
        );
        expect(rel.length, 'a contact type relation exists').toBeGreaterThanOrEqual(1);
        expect(rel.some(r => r.deleted_at === null), 'relation is not soft-deleted').toBe(true);

        // CRM-HP-03: the UI persists life_stage + contact_owner on the peoples row
        // itself (columns), not in peoplemeta (only the DB seeder writes those as meta).
        expect(String(row?.life_stage ?? ''), 'life_stage stored on the row').not.toBe('');
        expect(Number.isNaN(Number(row?.contact_owner)), 'contact_owner is numeric').toBe(false);
        expect(Number(row?.contact_owner), 'an owner is assigned').toBeGreaterThan(0);
    });

    // CRM-HP-04
    test('create a company via the modal (DB-verified)', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const company = data.crm.company();
        await crm.createCompany(company);

        const row = await CrmPage.findTypedPersonByEmail(company.email, 'company');
        expect(row, 'company should be persisted and typed').toBeTruthy();
        // peoples.company silently truncates long names (~30 chars) — assert the
        // stored value is the (possibly truncated) prefix of the input. See BUGS.md.
        const storedCompany = String(row?.company);
        expect(storedCompany.length, 'company name stored').toBeGreaterThan(0);
        expect(company.company.startsWith(storedCompany), 'stored company is a prefix of the input').toBe(true);

        // The same email must NOT also resolve as a contact.
        const asContact = await CrmPage.findTypedPersonByEmail(company.email, 'contact');
        expect(asContact, 'company email should not be typed as a contact').toBeFalsy();
    });

    // CRM-HP-05
    test('created contact appears in the list table', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const contact = data.crm.contact();
        await crm.createContact(contact);

        // Primary proof is the DB row; the table is a soft re-render check.
        const row = await CrmPage.findTypedPersonByEmail(contact.email, 'contact');
        expect(row, 'contact row created').toBeTruthy();
        await expect(page.locator(crm.contacts.table)).toBeVisible();
    });

    // CRM-HP-06 — single-contact view (smoke; tabs unproven)
    test('open a seeded single-contact view', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const ids = await CrmPage.seed(api);
        test.skip(!ids.CONTACT_ID, 'seed did not yield a CONTACT_ID');
        const crm = new CrmPage(page);
        await page.goto(crm.urls.contact(ids.CONTACT_ID!));
        await assertHealthy(page);
        expect(page.url()).toContain(`action=view&id=${ids.CONTACT_ID}`);
    });

    // CRM-HP-07 — single-company view (smoke)
    test('open a seeded single-company view', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const ids = await CrmPage.seed(api);
        test.skip(!ids.CRM_COMPANY_ID, 'seed did not yield a CRM_COMPANY_ID');
        const crm = new CrmPage(page);
        await page.goto(crm.urls.company(ids.CRM_COMPANY_ID!));
        await assertHealthy(page);
    });

    // CRM-HP-08
    test('contact groups page loads with Add control', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToContactGroups();
        await expect(page.locator(crm.groups.addNewBtn)).toBeVisible();
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    // CRM-HP-09
    test('create a contact group (DB-verified)', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const name = `${TEST_PREFIX}group_${Date.now()}`;
        await crm.createContactGroup(name, 'pw seeded group');
        await expect(page.locator(crm.groups.root)).toContainText(name, { timeout: 15_000 });

        const rows = await dbUtils.dbQuery<{ id: number; description: string | null }>(
            `SELECT id, description FROM ${crmTables.contactGroup} WHERE name = ? ORDER BY id DESC`,
            [name],
        );
        expect(rows.length, 'contact group row exists in DB').toBeGreaterThanOrEqual(1);
    });

    // CRM-HP-12 — activities feed (smoke)
    test('activities global feed loads', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.activities);
        await assertHealthy(page);
    });

    // CRM-HP-13 — schedules (smoke)
    test('schedules calendar loads', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.schedules);
        await assertHealthy(page);
    });

    // CRM-HP-14 — reports (smoke)
    test('reports hub loads', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.reports);
        await assertHealthy(page);
    });

    // CRM-HP-15 — soft-delete contract (pure DB)
    test('soft-delete leaves the row and flips the relation', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const ids = await CrmPage.seed(api);
        test.skip(!ids.CONTACT_ID, 'seed did not yield a CONTACT_ID');
        const id = ids.CONTACT_ID!;
        const before = await CrmPage.getPerson(id);
        const email = String(before?.email ?? '');
        expect(email).toContain('@');

        // Visible before trashing.
        expect(await CrmPage.findTypedPersonByEmail(email, 'contact')).toBeTruthy();

        // Simulate Trash: set deleted_at on the type relation.
        await dbUtils.dbQuery(
            `UPDATE ${tables.peopleTypeRelations} r
                JOIN ${tables.peopleTypes} t ON t.id = r.people_types_id
             SET r.deleted_at = NOW()
             WHERE r.people_id = ? AND t.name = 'contact'`,
            [id],
        );

        // findTypedPersonByEmail filters deleted_at IS NULL → now invisible.
        expect(await CrmPage.findTypedPersonByEmail(email, 'contact')).toBeFalsy();
        // But the people row is NOT hard-deleted.
        expect(await CrmPage.getPerson(id), 'row survives soft-delete').toBeTruthy();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// CRM manager role — same screens + create (CRM-HP-10, 11)
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM CRUD — manager', () => {
    test.use({ storageState: data.auth.crmManagerFile });

    // CRM-HP-10
    test('manager reaches the contacts list', { tag: ['@lite', '@crm', '@manager'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToContacts();
        await expect(page.locator(crm.contacts.root)).toBeVisible();
        await expect(page.locator(crm.contacts.addNewBtn)).toBeVisible();
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    // CRM-HP-11 — proves erp_crm_add_contact capability
    test('manager can create a contact (DB-verified)', { tag: ['@lite', '@crm', '@manager'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const contact = data.crm.contact();
        await crm.createContact(contact);

        const rows = await peopleRowsByEmail(contact.email);
        expect(rows.length, 'manager-created contact persisted').toBeGreaterThanOrEqual(1);
        const typed = await CrmPage.findTypedPersonByEmail(contact.email, 'contact');
        expect(typed, 'manager-created contact is typed as contact').toBeTruthy();
    });
});
