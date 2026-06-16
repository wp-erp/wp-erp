import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { options, tables } from '@utils/dbData';

/**
 * CORE REST + option happy paths.
 *
 * Core ships NO module-specific free REST beyond WordPress core users
 * (wp/v2/users, users/me, users/{id}). These prove the cookie+nonce ApiUtils path
 * and the single-company / single-modules invariants. Everything option-shaped is
 * asserted through dbUtils (deterministic, no nonce-protected AJAX UI).
 *
 * Auth: ApiUtils from the admin storageState; X-WP-Nonce injected from process.env.
 */

let api: ApiUtils;

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    await api.dispose();
    await dbUtils.close();
});

test.describe('CORE REST — current user & users', () => {
    // HP-16 — currentUser endpoint returns the admin (authed).
    test('HP-16 currentUser returns the authed admin', { tag: ['@lite', '@core', '@admin'] }, async () => {
        // roles/capabilities are only returned in the edit context.
        const [res, body] = await api.get(`${endPoints.currentUser}?context=edit`);
        expect(res.status()).toBe(200);
        expect(body?.id, 'a user id is present').toBeTruthy();
        // The admin account has the administrator role in its capabilities map.
        const roles = (body?.roles ?? []) as string[];
        const caps = (body?.capabilities ?? {}) as Record<string, boolean>;
        const isAdmin = (Array.isArray(roles) && roles.includes('administrator')) || caps.administrator === true || caps.manage_options === true;
        expect(isAdmin, 'currentUser is an administrator').toBe(true);
    });

    // HP-17 — users list returns 200 + an array (guard shape).
    test('HP-17 users list returns 200 and an array', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const [res, body] = await api.get(endPoints.users);
        expect(res.status()).toBe(200);
        expect(Array.isArray(body), 'users list is an array').toBe(true);
        if (Array.isArray(body) && body.length > 0) {
            expect(body[0]?.id, 'first user has an id').toBeTruthy();
        }
    });

    // user(1) read-back (authed) — the primary admin account.
    test('authed read of user(1) returns the record', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const [res, body] = await api.get(endPoints.user(1));
        expect(res.status()).toBe(200);
        expect(String(body?.id ?? '')).toBe('1');
    });
});

test.describe('CORE invariants — options (DB)', () => {
    // erp_modules is the single source of truth for active modules.
    test('erp_modules is present and lists the core modules', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const modules = await dbUtils.getOptionValue<Record<string, unknown> | unknown[]>(options.modules);
        expect(modules, 'erp_modules option exists').toBeTruthy();
        const keys = Array.isArray(modules) ? modules.map(String) : Object.keys(modules ?? {});
        expect(keys).toContain('hrm');
        expect(keys).toContain('crm');
        // Read-only: we never toggle a module on the shared live site.
    });

    // Single-company invariant: _erp_company is one global option (Company::key).
    test('company is a single global option (_erp_company)', { tag: ['@lite', '@core', '@admin'] }, async () => {
        // Count how many option rows match the company key — must be exactly one or
        // zero (WP options are unique by name; this also proves there is no per-user
        // company duplication in the free plugin).
        const rows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${tables.options} WHERE option_name = ?`,
            ['_erp_company'],
        );
        const count = Number(rows[0]?.c ?? 0);
        expect(count, 'at most one _erp_company option row').toBeLessThanOrEqual(1);
    });
});
