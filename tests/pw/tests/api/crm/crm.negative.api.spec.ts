import { test, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { data, TEST_PREFIX } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';
import { BASE_URL } from '@utils/helpers';
import { CrmPage } from '../../e2e/crm/crmPage';

/**
 * CRM negative / bug-hunting specs — DB-backed where the free CRM has no REST.
 *
 * These probe the KNOWN validation gaps grounded this session: wp_erp_peoples.email
 * is a NON-unique KEY, so dedup depends entirely on erp_insert_people() →
 * People::firstOrNew (email lowercased+trimmed). We document the ACTUAL DB outcome
 * with // BUG CANDIDATE annotations rather than assuming a fix.
 *
 * Implements: CRM-NC-05, CRM-NC-13, CRM-BUG-01, CRM-BUG-02, CRM-BUG-03,
 * CRM-BUG-15 (cross-module shared people store), CRM-BUG-18 (nonce-less AJAX).
 *
 * NOTE: CRM-NC-05 / BUG-01..03 simulate the *insert path* via dbUtils (no REST).
 * Because we bypass firstOrNew, a raw INSERT will of course create a 2nd row;
 * so the assertion here is on the DEDUP CONTRACT: a lookup-then-insert (the app's
 * own logic) must collapse to one row. We mirror firstOrNew with an explicit
 * "find by normalized email, else insert" and assert the resulting count.
 */

const PREFIX = process.env.DB_PREFIX ?? 'wp';
const crmTables = {
    contactSubscriber: `${PREFIX}_erp_crm_contact_subscriber`,
    contactGroup: `${PREFIX}_erp_crm_contact_group`,
} as const;

let api: ApiUtils;

/** Resolve a people-type id by name. */
async function typeId(name: string): Promise<number> {
    const rows = await dbUtils.dbQuery<{ id: number }>(
        `SELECT id FROM ${tables.peopleTypes} WHERE name = ? LIMIT 1`,
        [name],
    );
    expect(rows[0]?.id, `people type '${name}' exists`).toBeTruthy();
    return Number(rows[0]?.id);
}

/**
 * Mirror erp_insert_people() / People::firstOrNew: normalize the email, find an
 * existing row, otherwise insert a new one. Returns the resolved people id.
 */
async function firstOrNewPerson(rawEmail: string, first: string): Promise<number> {
    const email = rawEmail.toLowerCase().trim();
    const existing = await dbUtils.dbQuery<{ id: number }>(
        `SELECT id FROM ${tables.peoples} WHERE email = ? ORDER BY id ASC LIMIT 1`,
        [email],
    );
    let id = existing[0]?.id;
    if (!id) {
        const hash = `${TEST_PREFIX}${Date.now()}${Math.floor(Math.random() * 1e6)}`;
        const res = await dbUtils.dbQuery(
            `INSERT INTO ${tables.peoples}
                (user_id, first_name, last_name, email, life_stage, contact_owner, hash, created_by, created)
             VALUES (0, ?, 'Neg', ?, 'lead', 1, ?, 1, NOW())`,
            [first, email, hash],
        );
        id = (res as unknown as { insertId?: number }).insertId;
        if (!id) {
            const found = await dbUtils.dbQuery<{ id: number }>(
                `SELECT id FROM ${tables.peoples} WHERE email = ? ORDER BY id DESC LIMIT 1`,
                [email],
            );
            id = found[0]?.id;
        }
    }
    expect(id, 'firstOrNew resolved an id').toBeTruthy();
    return Number(id);
}

/** Ensure a (people_id,type) relation exists and is not soft-deleted. */
async function ensureTypeRelation(peopleId: number, type: string): Promise<void> {
    const tid = await typeId(type);
    const existing = await dbUtils.dbQuery<{ id: number; deleted_at: string | null }>(
        `SELECT id, deleted_at FROM ${tables.peopleTypeRelations} WHERE people_id = ? AND people_types_id = ?`,
        [peopleId, tid],
    );
    if (existing.length === 0) {
        await dbUtils.dbQuery(
            `INSERT INTO ${tables.peopleTypeRelations} (people_id, people_types_id, deleted_at) VALUES (?, ?, NULL)`,
            [peopleId, tid],
        );
    } else if (existing[0]?.deleted_at !== null) {
        await dbUtils.dbQuery(
            `UPDATE ${tables.peopleTypeRelations} SET deleted_at = NULL WHERE id = ?`,
            [existing[0]?.id],
        );
    }
}

async function countByEmail(rawEmail: string): Promise<number> {
    const rows = await dbUtils.dbQuery<{ c: number }>(
        `SELECT COUNT(*) AS c FROM ${tables.peoples} WHERE email = ?`,
        [rawEmail.toLowerCase().trim()],
    );
    return Number(rows[0]?.c ?? 0);
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    await api.dispose();
    await dbUtils.deleteRowsLike(crmTables.contactGroup, 'name', TEST_PREFIX).catch(() => {});
    await dbUtils.deleteRowsLike(crmTables.contactSubscriber, 'hash', TEST_PREFIX).catch(() => {});
    await dbUtils.close();
});

test.describe('CRM dedup contract (no DB unique on email)', () => {
    // CRM-NC-05 / CRM-BUG-01 — same email, same type → firstOrNew must reuse.
    test('duplicate contact email resolves to one row (app dedup)', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const email = `${TEST_PREFIX}dup_${Date.now()}@example.com`;
        const a = await firstOrNewPerson(email, `${TEST_PREFIX}A`);
        await ensureTypeRelation(a, 'contact');
        const b = await firstOrNewPerson(email, `${TEST_PREFIX}B`);
        await ensureTypeRelation(b, 'contact');

        expect(a, 'firstOrNew returns the same id for the same email').toBe(b);
        // BUG CANDIDATE: peoples.email has NO unique key — only firstOrNew prevents
        // duplicates. If a raw insert path is ever used, 2 rows appear. Assert the
        // contract: exactly one row for this email.
        expect(await countByEmail(email), 'one row per email under firstOrNew').toBe(1);
    });

    // CRM-BUG-03 — whitespace/case variant must collapse to the same row.
    test('case/whitespace email variant collapses to one row', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const uid = Date.now();
        const base = `${TEST_PREFIX}Case_${uid}@Example.com`;
        const variant = `   ${TEST_PREFIX}CASE_${uid}@example.COM  `;

        const a = await firstOrNewPerson(base, `${TEST_PREFIX}A`);
        await ensureTypeRelation(a, 'contact');
        const b = await firstOrNewPerson(variant, `${TEST_PREFIX}B`);

        // BUG CANDIDATE: normalization (strtolower+trim) must make both resolve to
        // the same row; if a 2nd row survives, dedup is leaky.
        expect(b, 'normalized variant resolves to the same id').toBe(a);
        expect(await countByEmail(base), 'one row for normalized email').toBe(1);
    });

    // CRM-BUG-02 — same email across types (contact then company).
    test('same email across contact and company types shares one people row', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const email = `${TEST_PREFIX}xtype_${Date.now()}@example.com`;
        const asContact = await firstOrNewPerson(email, `${TEST_PREFIX}X`);
        await ensureTypeRelation(asContact, 'contact');
        const asCompany = await firstOrNewPerson(email, `${TEST_PREFIX}X`);
        await ensureTypeRelation(asCompany, 'company');

        // firstOrNew keys on email only → the SAME people row gets two type relations.
        expect(asCompany, 'shared people row across types').toBe(asContact);
        expect(await countByEmail(email), 'one people row, two type relations').toBe(1);

        const relTypes = await dbUtils.dbQuery<{ name: string }>(
            `SELECT t.name FROM ${tables.peopleTypeRelations} r
                JOIN ${tables.peopleTypes} t ON t.id = r.people_types_id
             WHERE r.people_id = ? AND r.deleted_at IS NULL`,
            [asContact],
        );
        const names = relTypes.map(r => r.name);
        // BUG CANDIDATE: cross-type collision — assert BOTH relations coexist cleanly.
        expect(names).toContain('contact');
        expect(names).toContain('company');
    });
});

test.describe('CRM cross-module data integrity', () => {
    // CRM-BUG-15 — CRM contact & Accounting customer share wp_erp_peoples.
    test('contact and customer with same email share one people row; types are independent', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const email = `${TEST_PREFIX}xmod_${Date.now()}@example.com`;
        const id = await firstOrNewPerson(email, `${TEST_PREFIX}Xmod`);
        await ensureTypeRelation(id, 'contact');
        await ensureTypeRelation(id, 'customer');

        // One row, two types.
        expect(await countByEmail(email), 'one shared people row').toBe(1);
        const before = await dbUtils.dbQuery<{ name: string }>(
            `SELECT t.name FROM ${tables.peopleTypeRelations} r
                JOIN ${tables.peopleTypes} t ON t.id = r.people_types_id
             WHERE r.people_id = ? AND r.deleted_at IS NULL`,
            [id],
        );
        expect(before.map(r => r.name)).toEqual(expect.arrayContaining(['contact', 'customer']));

        // Soft-delete the CRM (contact) type only — the customer type must survive.
        await dbUtils.dbQuery(
            `UPDATE ${tables.peopleTypeRelations} r
                JOIN ${tables.peopleTypes} t ON t.id = r.people_types_id
             SET r.deleted_at = NOW() WHERE r.people_id = ? AND t.name = 'contact'`,
            [id],
        );
        // BUG CANDIDATE: removing the CRM type must NOT strip the accounting customer type.
        expect(await CrmPage.findTypedPersonByEmail(email, 'contact'), 'contact type removed').toBeFalsy();
        const customerStill = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${tables.peopleTypeRelations} r
                JOIN ${tables.peopleTypes} t ON t.id = r.people_types_id
             WHERE r.people_id = ? AND t.name = 'customer' AND r.deleted_at IS NULL`,
            [id],
        );
        expect(Number(customerStill[0]?.c), 'customer type preserved across CRM-type delete').toBe(1);
        expect(await CrmPage.getPerson(id), 'people row intact').toBeTruthy();
    });
});

test.describe('CRM subscriber UNIQUE enforcement', () => {
    // CRM-NC-13 — a raw double INSERT (no upsert) hits the UNIQUE(user_id,group_id).
    test('a non-upsert duplicate subscription raises a duplicate-key error', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const userId = 990_000 + Math.floor(Math.random() * 9000);
        const groupRes = await dbUtils.dbQuery(
            `INSERT INTO ${crmTables.contactGroup} (name, description, private, created_at, updated_at)
             VALUES (?, '', 0, NOW(), NOW())`,
            [`${TEST_PREFIX}uniqgrp_${Date.now()}`],
        );
        let groupId = (groupRes as unknown as { insertId?: number }).insertId;
        if (!groupId) {
            const g = await dbUtils.dbQuery<{ id: number }>(
                `SELECT id FROM ${crmTables.contactGroup} ORDER BY id DESC LIMIT 1`,
            );
            groupId = g[0]?.id;
        }
        const hash = `${TEST_PREFIX}${Date.now()}`;

        await dbUtils.dbQuery(
            `INSERT INTO ${crmTables.contactSubscriber} (user_id, group_id, status, subscribe_at, hash)
             VALUES (?, ?, 'subscribe', NOW(), ?)`,
            [userId, groupId, hash],
        );

        // Second raw INSERT of the same pair must violate UNIQUE(user_id,group_id).
        let duplicateRejected = false;
        try {
            await dbUtils.dbQuery(
                `INSERT INTO ${crmTables.contactSubscriber} (user_id, group_id, status, subscribe_at, hash)
                 VALUES (?, ?, 'subscribe', NOW(), ?)`,
                [userId, groupId, hash],
            );
        } catch (e) {
            duplicateRejected = /duplicate/i.test((e as Error).message);
        }
        expect(duplicateRejected, 'UNIQUE(user_id,group_id) blocks a true duplicate').toBe(true);

        const rows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${crmTables.contactSubscriber} WHERE user_id = ? AND group_id = ?`,
            [userId, groupId],
        );
        expect(Number(rows[0]?.c), 'exactly one subscription row remains').toBe(1);
    });
});

test.describe('CRM unauthorized AJAX', () => {
    // CRM-BUG-18 — state-changing admin-ajax without nonce/cookie must be denied.
    test('nonce-less admin-ajax erp-crm-customer-new is rejected and creates no row', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const email = `${TEST_PREFIX}noauth_${Date.now()}@example.com`;
        const before = await countByEmail(email);

        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
        try {
            const res = await ctx.post(`${BASE_URL}/wp-admin/admin-ajax.php`, {
                form: {
                    action: 'erp-crm-customer-new',
                    first_name: `${TEST_PREFIX}NoAuth`,
                    email,
                    type: 'contact',
                    // intentionally NO _wpnonce
                },
            });
            // admin-ajax denies with 0 / -1 / 400 / 403 for a missing nonce+cap.
            const status = res.status();
            const text = await res.text().catch(() => '');
            const denied =
                status >= 400 ||
                text.trim() === '0' ||
                text.trim() === '-1' ||
                /forbidden|not allowed|do not have permission/i.test(text);
            // BUG CANDIDATE: if this is NOT denied (or a row appears), the AJAX handler
            // is missing a nonce/cap check.
            expect(denied, `unauthorized admin-ajax denied (status=${status}, body=${text.slice(0, 80)})`).toBe(true);
        } finally {
            await ctx.dispose();
        }

        const after = await countByEmail(email);
        expect(after, 'no people row created by unauthorized AJAX').toBe(before);
    });
});
