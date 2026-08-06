import { test, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { BASE_URL } from '@utils/helpers';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { options } from '@utils/dbData';

/**
 * CORE REST negatives + nonce/auth gates.
 *
 * Verified live against http://localhost:9999 this session:
 *   - wp/v2/users/me   (no cookie/nonce) → 401   (NC-10/NC-11)
 *   - wp/v2/users/1    (default context, no auth) → 200  (WP exposes author 1 publicly)
 *   - wp/v2/users/1?context=edit (no auth) → 401         (privileged read gated)
 *   - wp/v2/users/99999999 (no auth) → 404               (real 404, see BUG-01 note)
 *
 * For UNAUTHORIZED negatives we build a fresh, cookie-less context via
 * request.newContext({ baseURL, ...data.auth.noAuth }) and send NO X-WP-Nonce.
 */

test.afterAll(async () => {
    await dbUtils.close();
});

test.describe('CORE REST — unauthorized & nonce gates', () => {
    // NC-10 / NC-11 — no cookie + no nonce → 401 on a privileged endpoint.
    test('NC-10/NC-11 unauthenticated currentUser request is 401', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth });
        try {
            const res = await ctx.get(endPoints.currentUser, { failOnStatusCode: false });
            expect(res.status(), 'no-auth users/me must be 401').toBe(401);
        } finally {
            await ctx.dispose();
        }
    });

    // NC-11 (corrected) — user(1) at default context is PUBLIC (WP exposes the
    // author archive user), so a logged-out GET returns 200, NOT 401. The
    // privileged context=edit read IS gated → 401. We assert both actual behaviors.
    test('NC-11 user(1) default context is public (200) but edit context is gated (401)', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth });
        try {
            const pub = await ctx.get(endPoints.user(1), { failOnStatusCode: false });
            // BUG CANDIDATE: wp/v2/users/1 leaks the admin's public profile to anonymous
            // callers (WP core default), so the brief's "user(1) logged-out → 401" does
            // not hold for the default context — only edit context is protected.
            expect(pub.status(), 'public profile read is allowed by WP core').toBe(200);

            const edit = await ctx.get(`${endPoints.user(1)}?context=edit`, { failOnStatusCode: false });
            expect(edit.status(), 'edit-context read of a user must be 401').toBe(401);
        } finally {
            await ctx.dispose();
        }
    });

    // BUG-01 — a non-existent core user. The WP core users endpoint returns a real
    // 404 here (verified live), unlike the HRM employee endpoint which leaks a 200
    // blank record. We assert the actual core behavior (404) and flag the contrast.
    test('BUG-01 non-existent core user returns 404 (contrast w/ HRM 200-blank gap)', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth });
        try {
            const res = await ctx.get(endPoints.user(99999999), { failOnStatusCode: false });
            // BUG CANDIDATE: the related HRM employee endpoint returns 200 + a blank
            // record for an unknown id; the WP core users endpoint here correctly 404s,
            // so the "missing id → blank record" gap is HRM-specific, not core.
            expect(res.status(), 'unknown core user id is a real 404').toBe(404);
        } finally {
            await ctx.dispose();
        }
    });

    // NC-12 — Core has no free write REST; a privileged write (user create) without a
    // cookie/nonce must be rejected by WP's cap/nonce gate (401), and no global option
    // may be side-effected by the rejected anonymous request. We use the unchanged
    // erp_settings_general option as a guard that nothing leaked through.
    test('NC-12 anonymous write is rejected and leaves options untouched', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const before = await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral);

        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth });
        try {
            const res = await ctx.post(endPoints.users, {
                data: { username: 'pw_should_not_exist', email: 'pw_no@example.com', password: 'x' },
                headers: { 'Content-Type': 'application/json' },
                failOnStatusCode: false,
            });
            expect(res.status(), 'anonymous create is rejected (401)').toBe(401);
        } finally {
            await ctx.dispose();
        }

        const after = await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral);
        // The option store is unchanged by the rejected anonymous request.
        expect(JSON.stringify(after ?? null)).toBe(JSON.stringify(before ?? null));
    });

    // NC-10 variant — an authed ApiUtils call still works (proves the negative above
    // is about missing auth, not a broken endpoint).
    test('authed ApiUtils CAN read currentUser (control)', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const api = await ApiUtils.fromStorageState(data.auth.adminFile);
        try {
            const [res] = await api.get(endPoints.currentUser);
            expect(res.status()).toBe(200);
        } finally {
            await api.dispose();
        }
    });
});
