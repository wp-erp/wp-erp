import { test, expect } from '@utils/test';
import { CompaniesPage, companyColumns } from '@pages/crm/companiesPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueName, uniqueEmail } from '@utils/helpers';
import { cleanupCrmCompanies } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

test.describe('CRM — Companies', () => {
    let page: CompaniesPage;

    test.beforeAll(async () => {
        await cleanupCrmCompanies();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new CompaniesPage(p);
    });

    test.afterAll(async () => {
        await cleanupCrmCompanies();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the companies list renders its captured columns', { tag: ['@tier1', '@crm-companies', '@smoke'] }, async () => {
        await page.goto();

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);

        const headers = await page.columnHeaders();

        for (const column of companyColumns) {
            expect(headers, `column "${column}"`).toContain(column);
        }
    });

    test('a company can be created and is stored as a person of type company', { tag: ['@tier1', '@crm-companies', '@crud'] }, async () => {
        const name = uniqueName('PwerpCo');
        const email = uniqueEmail();

        await page.create({ name, email, phone: '+1 555 0100' });

        // Companies share `erp_peoples` with contacts, customers and vendors —
        // the people TYPE is the only thing that separates them.
        const rows = await query<RowDataPacket[]>(
            `SELECT p.id, p.company, t.name AS type FROM ${prefix()}erp_peoples p
               JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
               JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
              WHERE p.email = ?`,
            [email]
        );

        expect(rows, 'one person row is written').toHaveLength(1);
        expect(rows[0]!.type, 'typed as a CRM company, not a contact').toBe('company');
        expect(rows[0]!.company, 'the name is stored in the company column').toBe(name);

        await page.goto();
        expect(await page.hasCompany(name), 'the company is listed').toBe(true);
    });

    test('the list is empty until a company exists', { tag: ['@tier1', '@crm-companies'] }, async () => {
        // The seeded demo data contains NO companies: `seedData.crmCompanies` is
        // consumed by `seedAccountingPeople()` to create accounting CUSTOMERS,
        // despite its name. Nothing in the seed creates a person of type
        // `company`, so this screen starts genuinely empty.
        await cleanupCrmCompanies();
        await page.goto();

        expect(await page.isEmpty(), 'no companies are seeded').toBe(true);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('a company with no life stage or owner never leaves the browser', { tag: ['@tier3', '@crm-companies', '@validation'] }, async () => {
        // The refusal here is the BROWSER's, not the product's: life stage and
        // contact owner carry `required`, so constraint validation blocks the
        // submit and no request is ever sent. Verified — no AJAX fires, three
        // fields report invalid, and the select's own message reads "Please
        // select an item in the list." Saying "the product refuses it" would be
        // wrong; the server is never asked.
        const name = uniqueName('PwerpCo');
        const email = uniqueEmail();

        await page.createWithoutRequiredSelects({ name, email });

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_peoples WHERE email = ?`, [email]);

        expect(rows, 'nothing is written').toHaveLength(0);
        expect(await page.isModalStillOpen(), 'the form stays open').toBe(true);
        expect(await page.invalidFieldCount(), 'the browser marks the required fields invalid').toBeGreaterThan(0);
        expect(await page.lifeStageValidationMessage(), 'and says why').toMatch(/select an item/i);
    });

    test('CRM companies are closed to an employee', { tag: ['@tier3', '@crm-companies', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new CompaniesPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach CRM Companies').toBe(true);
    });
});
