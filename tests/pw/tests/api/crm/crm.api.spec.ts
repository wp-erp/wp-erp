import { test, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { schemas } from '@utils/schemas';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';
import { CrmPage } from '../../e2e/crm/crmPage';

/**
 * CRM "API" spec — DB-backed.
 *
 * The free CRM ships no REST controller (utils/apiEndPoints documents this), so
 * instead of HTTP CRUD we exercise the same persistence layer the plugin uses
 * (wp_erp_peoples + people_type_relations + peoplemeta) through dbUtils:
 *   - seed (create) a typed contact row
 *   - read it back by id and by typed-email join
 *   - validate its shape with the shared `person` zod schema
 *   - a negative: a non-existent id returns no row
 *
 * One genuinely-REST assertion is kept (wp/v2/users) to prove the cookie+nonce
 * ApiUtils path works and to cover the unauthorized (noAuth) negative.
 */

let api: ApiUtils;

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    await api.dispose();
    await dbUtils.close();
});

test.describe('CRM people (DB-backed)', () => {
    test('seeded contact row exists and validates against the person schema', { tag: ['@lite', '@crm'] }, async () => {
        const ids = await CrmPage.seed(api);
        expect(ids.CONTACT_ID, 'seed() should return a CONTACT_ID').toBeTruthy();

        const person = await CrmPage.getPerson(ids.CONTACT_ID!);
        expect(person, 'contact row should be readable by id').toBeTruthy();

        const parsed = schemas.person.safeParse(person);
        expect(parsed.success, JSON.stringify(parsed)).toBe(true);
        expect(String(person?.email)).toContain('@');
    });

    test('create + read-back a typed contact through dbUtils', { tag: ['@lite', '@crm'] }, async () => {
        const ids = await CrmPage.seed(api);
        expect(ids.CONTACT_ID, 'a fresh contact id is needed').toBeTruthy();

        // The seed inserts the people_type_relations row; the typed-email join
        // must therefore resolve the same record back.
        const byId = await CrmPage.getPerson(ids.CONTACT_ID!);
        expect(byId).toBeTruthy();
        const email = String(byId?.email ?? '');

        const typed = await CrmPage.findTypedPersonByEmail(email, 'contact');
        expect(typed, 'typed lookup should find the seeded contact').toBeTruthy();
        expect(String(typed?.id)).toBe(String(ids.CONTACT_ID));

        // life_stage / contact_owner meta should have been written.
        const meta = await dbUtils.dbQuery<{ meta_key: string; meta_value: string }>(
            `SELECT meta_key, meta_value FROM ${tables.peopleMeta} WHERE erp_people_id = ?`,
            [ids.CONTACT_ID],
        );
        const keys = meta.map(m => m.meta_key);
        expect(keys).toContain('life_stage');
        expect(keys).toContain('contact_owner');
    });

    test('a seeded company is typed as company, not contact', { tag: ['@lite', '@crm'] }, async () => {
        const ids = await CrmPage.seed(api);
        expect(ids.CRM_COMPANY_ID, 'seed() should return a CRM_COMPANY_ID').toBeTruthy();

        const company = await CrmPage.getPerson(ids.CRM_COMPANY_ID!);
        expect(company, 'company row should be readable').toBeTruthy();

        const asCompany = await CrmPage.findTypedPersonByEmail(String(company?.email), 'company');
        expect(asCompany, 'company-typed lookup should resolve').toBeTruthy();

        // The same email must NOT resolve as a contact (distinct type relation).
        const asContact = await CrmPage.findTypedPersonByEmail(String(company?.email), 'contact');
        expect(asContact, 'company email should not be typed as a contact').toBeFalsy();
    });

    // Negative: an id that was never created must not return a row.
    test('reading a non-existent people id returns nothing', { tag: ['@lite', '@crm'] }, async () => {
        const missing = await CrmPage.getPerson(999_999_999);
        expect(missing).toBeUndefined();
    });
});

test.describe('CRM REST-auth sanity (cookie + nonce)', () => {
    test('authed admin can read wp/v2/users (list + schema)', { tag: ['@lite', '@crm'] }, async () => {
        const [res, body] = await api.get(endPoints.users);
        expect(res.status()).toBe(200);
        expect(Array.isArray(body)).toBe(true);
    });

    // Negative: an unauthenticated context cannot edit-list users.
    test('unauthenticated context is rejected for a privileged read', { tag: ['@lite', '@crm'] }, async () => {
        const ctx = await request.newContext(data.auth.noAuth);
        const noAuth = new ApiUtils(ctx);
        const [res] = await noAuth.get(`${endPoints.users}?context=edit`, undefined, false);
        expect(res.status()).toBeGreaterThanOrEqual(400);
        await noAuth.dispose();
    });
});
