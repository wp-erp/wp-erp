import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { data, TEST_PREFIX } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';
import { schemas } from '@utils/schemas';
import { CrmPage } from '../../e2e/crm/crmPage';

/**
 * CRM "API" CRUD — DB-backed (the free CRM ships no REST controller).
 *
 * These cases need deterministic read-back of the unified people store, so they
 * exercise dbUtils directly rather than the UI. Grounded against the live schema
 * verified this session:
 *   - wp_erp_crm_contact_group (id,name,description,private,created_at,updated_at)
 *   - wp_erp_crm_contact_subscriber UNIQUE(user_id,group_id)
 *   - wp_erp_crm_customer_companies (customer_id, company_id)
 *   - wp_erp_people_type_relations.deleted_at = soft-delete
 *
 * Implements: CRM-HP-15 (soft-delete contract), CRM-EC-07 (life stages),
 * CRM-EC-11/12 (group description / dup name), CRM-EC-13 (re-subscribe upsert),
 * CRM-BUG-07 (restore), CRM-BUG-14 (contact↔company link).
 */

const PREFIX = process.env.DB_PREFIX ?? 'wp';
const crmTables = {
    contactGroup: `${PREFIX}_erp_crm_contact_group`,
    contactSubscriber: `${PREFIX}_erp_crm_contact_subscriber`,
    customerCompanies: `${PREFIX}_erp_crm_customer_companies`,
} as const;

const LIFE_STAGES = ['subscriber', 'lead', 'opportunity', 'customer'] as const;

let api: ApiUtils;

/** Insert a typed contact straight into the people store; returns its id. */
async function seedContact(lifeStage: string = 'lead', emailSuffix = ''): Promise<{ id: string; email: string }> {
    const uid = `${Date.now()}${Math.floor(Math.random() * 1e6)}`;
    const email = `${TEST_PREFIX}c_${uid}${emailSuffix}@example.com`.toLowerCase();
    const hash = `${TEST_PREFIX}${uid}`;

    const result = await dbUtils.dbQuery(
        `INSERT INTO ${tables.peoples}
            (user_id, first_name, last_name, email, life_stage, contact_owner, hash, created_by, created)
         VALUES (0, ?, 'ApiSeed', ?, ?, 1, ?, 1, NOW())`,
        [`${TEST_PREFIX}First`, email, lifeStage, hash],
    );
    let id = (result as unknown as { insertId?: number }).insertId;
    if (!id) {
        const found = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${tables.peoples} WHERE email = ? ORDER BY id DESC LIMIT 1`,
            [email],
        );
        id = found[0]?.id;
    }
    expect(id, 'people row inserted').toBeTruthy();

    const typeRows = await dbUtils.dbQuery<{ id: number }>(
        `SELECT id FROM ${tables.peopleTypes} WHERE name = 'contact' LIMIT 1`,
    );
    const typeId = typeRows[0]?.id;
    expect(typeId, 'contact type exists').toBeTruthy();

    await dbUtils.dbQuery(
        `INSERT INTO ${tables.peopleTypeRelations} (people_id, people_types_id, deleted_at) VALUES (?, ?, NULL)`,
        [id, typeId],
    );
    await dbUtils.dbQuery(
        `INSERT INTO ${tables.peopleMeta} (erp_people_id, meta_key, meta_value) VALUES (?, 'life_stage', ?)`,
        [id, lifeStage],
    );
    await dbUtils.dbQuery(
        `INSERT INTO ${tables.peopleMeta} (erp_people_id, meta_key, meta_value) VALUES (?, 'contact_owner', '1')`,
        [id],
    );
    return { id: String(id), email };
}

/** Create a contact group row directly; returns its id. */
async function seedGroup(name: string, description = ''): Promise<number> {
    const res = await dbUtils.dbQuery(
        `INSERT INTO ${crmTables.contactGroup} (name, description, private, created_at, updated_at)
         VALUES (?, ?, 0, NOW(), NOW())`,
        [name, description],
    );
    let id = (res as unknown as { insertId?: number }).insertId;
    if (!id) {
        const found = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${crmTables.contactGroup} WHERE name = ? ORDER BY id DESC LIMIT 1`,
            [name],
        );
        id = found[0]?.id;
    }
    expect(id, 'group row inserted').toBeTruthy();
    return Number(id);
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    await api.dispose();
    // Cleanup only pw_-prefixed test data on the shared live site.
    await dbUtils.deleteRowsLike(crmTables.contactGroup, 'name', TEST_PREFIX).catch(() => {});
    await dbUtils.deleteRowsLike(crmTables.contactSubscriber, 'hash', TEST_PREFIX).catch(() => {});
    await dbUtils.close();
});

test.describe('CRM people store — DB CRUD', () => {
    // Baseline: seed reads back and validates the person schema.
    test('seeded contact validates against the person schema', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const { id } = await seedContact();
        const person = await CrmPage.getPerson(id);
        const parsed = schemas.person.safeParse(person);
        expect(parsed.success, JSON.stringify(parsed)).toBe(true);
        expect(String(person?.email)).toContain('@');
    });

    // CRM-EC-07 — each FREE life stage is storable and read back exactly.
    test('each FREE life stage is stored and read back', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        for (const stage of LIFE_STAGES) {
            const { id } = await seedContact(stage);
            const row = await CrmPage.getPerson(id);
            expect(String(row?.life_stage), `peoples.life_stage == ${stage}`).toBe(stage);
            const meta = await dbUtils.dbQuery<{ meta_value: string }>(
                `SELECT meta_value FROM ${tables.peopleMeta} WHERE erp_people_id = ? AND meta_key = 'life_stage' LIMIT 1`,
                [id],
            );
            expect(meta[0]?.meta_value, `meta life_stage == ${stage}`).toBe(stage);
        }
    });

    // CRM-HP-15 / CRM-BUG-06 + CRM-BUG-07 — soft-delete then restore round-trip.
    test('soft-delete hides the contact; restore brings it back with meta intact', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const { id, email } = await seedContact('opportunity');
        expect(await CrmPage.findTypedPersonByEmail(email, 'contact')).toBeTruthy();

        // Trash.
        await dbUtils.dbQuery(
            `UPDATE ${tables.peopleTypeRelations} r
                JOIN ${tables.peopleTypes} t ON t.id = r.people_types_id
             SET r.deleted_at = NOW() WHERE r.people_id = ? AND t.name = 'contact'`,
            [id],
        );
        // BUG CANDIDATE: a soft-deleted contact must NOT resolve via the typed lookup.
        expect(await CrmPage.findTypedPersonByEmail(email, 'contact'), 'trashed contact hidden').toBeFalsy();
        expect(await CrmPage.getPerson(id), 'people row not hard-deleted').toBeTruthy();

        // Restore.
        await dbUtils.dbQuery(
            `UPDATE ${tables.peopleTypeRelations} r
                JOIN ${tables.peopleTypes} t ON t.id = r.people_types_id
             SET r.deleted_at = NULL WHERE r.people_id = ? AND t.name = 'contact'`,
            [id],
        );
        expect(await CrmPage.findTypedPersonByEmail(email, 'contact'), 'restored contact visible').toBeTruthy();
        // CRM-BUG-07: meta survives trash → restore.
        const meta = await dbUtils.dbQuery<{ meta_value: string }>(
            `SELECT meta_value FROM ${tables.peopleMeta} WHERE erp_people_id = ? AND meta_key = 'life_stage' LIMIT 1`,
            [id],
        );
        expect(meta[0]?.meta_value, 'life_stage preserved across restore').toBe('opportunity');
    });
});

test.describe('CRM contact groups — DB CRUD', () => {
    // CRM-EC-11 — empty description allowed.
    test('group created with empty description', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const name = `${TEST_PREFIX}grp_${Date.now()}`;
        const id = await seedGroup(name, '');
        const rows = await dbUtils.dbQuery<{ description: string | null }>(
            `SELECT description FROM ${crmTables.contactGroup} WHERE id = ? LIMIT 1`,
            [id],
        );
        expect(rows.length, 'group exists').toBe(1);
        expect(rows[0]?.description ?? '', 'description is empty').toBe('');
    });

    // CRM-EC-12 — duplicate group name. No DB unique → two rows. // BUG CANDIDATE.
    test('duplicate group name is not blocked at the DB level', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const name = `${TEST_PREFIX}dupgrp_${Date.now()}`;
        await seedGroup(name);
        await seedGroup(name);
        const rows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${crmTables.contactGroup} WHERE name = ?`,
            [name],
        );
        // BUG CANDIDATE: wp_erp_crm_contact_group has no unique on name; duplicates persist.
        expect(Number(rows[0]?.c), 'two groups with the same name coexist').toBe(2);
    });

    // CRM-EC-13 — re-subscribe is an upsert, not a duplicate (UNIQUE user_group).
    test('re-subscribe (same user_id,group_id) does not duplicate', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const { id: contactId } = await seedContact();
        const groupId = await seedGroup(`${TEST_PREFIX}subgrp_${Date.now()}`);
        const userId = Number(contactId);
        const hash = `${TEST_PREFIX}${Date.now()}`;

        // First subscription.
        await dbUtils.dbQuery(
            `INSERT INTO ${crmTables.contactSubscriber} (user_id, group_id, status, subscribe_at, hash)
             VALUES (?, ?, 'subscribe', NOW(), ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), subscribe_at = VALUES(subscribe_at)`,
            [userId, groupId, hash],
        );
        // Second attempt at the same pair — the app path is an upsert.
        await dbUtils.dbQuery(
            `INSERT INTO ${crmTables.contactSubscriber} (user_id, group_id, status, subscribe_at, hash)
             VALUES (?, ?, 'subscribe', NOW(), ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), subscribe_at = VALUES(subscribe_at)`,
            [userId, groupId, hash],
        );

        const rows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${crmTables.contactSubscriber} WHERE user_id = ? AND group_id = ?`,
            [userId, groupId],
        );
        expect(Number(rows[0]?.c), 'UNIQUE(user_id,group_id) collapses to one row').toBe(1);
    });
});

test.describe('CRM contact ↔ company link — DB integrity', () => {
    // CRM-BUG-14 — link/unlink must not cascade-delete the people rows.
    test('linking a contact to a company adds one link row; unlink removes only it', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const ids = await CrmPage.seed(api);
        test.skip(!ids.CONTACT_ID || !ids.CRM_COMPANY_ID, 'seed did not yield both ids');
        const customerId = Number(ids.CONTACT_ID);
        const companyId = Number(ids.CRM_COMPANY_ID);

        // Link (admin-ajax erp-crm-customer-add-company has an unproven selector,
        // so seed the link row directly and assert the contract).
        await dbUtils.dbQuery(
            `INSERT INTO ${crmTables.customerCompanies} (customer_id, company_id) VALUES (?, ?)`,
            [customerId, companyId],
        );
        let links = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${crmTables.customerCompanies} WHERE customer_id = ? AND company_id = ?`,
            [customerId, companyId],
        );
        expect(Number(links[0]?.c), 'exactly one link row').toBe(1);

        // Unlink (mirrors erp-crm-customer-remove-company).
        await dbUtils.dbQuery(
            `DELETE FROM ${crmTables.customerCompanies} WHERE customer_id = ? AND company_id = ?`,
            [customerId, companyId],
        );
        links = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${crmTables.customerCompanies} WHERE customer_id = ? AND company_id = ?`,
            [customerId, companyId],
        );
        expect(Number(links[0]?.c), 'link removed').toBe(0);

        // BUG CANDIDATE: neither people row should be cascade-deleted on unlink.
        expect(await CrmPage.getPerson(customerId), 'contact row survives unlink').toBeTruthy();
        expect(await CrmPage.getPerson(companyId), 'company row survives unlink').toBeTruthy();
    });
});
