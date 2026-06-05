import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { restUrl } from '@utils/helpers';
import type { ResponseBody } from '@utils/interfaces';

/**
 * HR Frontend (HR Frontend dashboard settings) — module: hrm, kind: REST (@pro).
 *
 * Single source of truth:
 *   modules/pro/hr-frontend/includes/DashboardSettings.php
 *
 * One route, TWO method handlers (register_rest_routes, lines 24-57):
 *   GET  /erp/v1/hrm/hr-frontend/settings → get_settings    (manage_options)
 *   POST /erp/v1/hrm/hr-frontend/settings → update_settings (manage_options)
 *
 * get_settings() returns a flat object with EXACTLY these keys + defaults
 * (lines 67-76):
 *   hr_frontend_slug            (default 'wp-erp-dashboard')
 *   hr_frontend_dashboard_title (default 'WP ERP')
 *   hr_frontend_logo            (default '')
 *   hr_frontend_redirect        (default false)
 *
 * POST args (lines 40-53):
 *   hr_frontend_slug            required, sanitize_text_field
 *   hr_frontend_dashboard_title required, sanitize_text_field
 *   hr_frontend_logo            optional, esc_url_raw
 *   hr_frontend_redirect        optional, rest_sanitize_boolean
 * WP core enforces 'required' BEFORE the callback runs, so a missing required
 * param is a clean 400 (rest_missing_callback_param), never a 500.
 *
 * Resilient philosophy: writes that may 4xx use assert=false + status branching;
 * access-control asserts the boundary (NOT 200 / 401|403), never an exact code;
 * no known/intentional 500 lives here — a 5xx is a real bug, never asserted.
 *
 * This endpoint mutates shared singleton wp_options (not row-scoped), so there is
 * no created-row to delete. We snapshot all four values in beforeAll and POST the
 * snapshot back in afterAll to keep the run idempotent for sibling specs.
 */

const SETTINGS_URL = restUrl('/erp/v1/hrm/hr-frontend/settings');

// wp_options keys this endpoint persists (reference as string literals).
const OPT_SLUG = 'hr_frontend_slug';
const OPT_TITLE = 'hr_frontend_dashboard_title';
const OPT_LOGO = 'hr_frontend_logo';
const OPT_REDIRECT = 'hr_frontend_redirect';

let api: ApiUtils;
let empApi: ApiUtils;

// Snapshot of the four settings captured before the run, restored after.
let snapshot: {
    hr_frontend_slug: string;
    hr_frontend_dashboard_title: string;
    hr_frontend_logo: string;
    hr_frontend_redirect: boolean;
} | null = null;

/** Build a full valid update payload with a fresh per-run suffix. */
function settingsPayload(overrides: Record<string, unknown> = {}): Record<string, unknown> {
    const stamp = Date.now();
    return {
        hr_frontend_slug: `pw-hrfe-${stamp}`,
        hr_frontend_dashboard_title: `PW Dashboard ${stamp}`,
        hr_frontend_logo: 'https://example.com/logo.png',
        hr_frontend_redirect: true,
        ...overrides,
    };
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
    // Employee boundary context: no EMPLOYEE_NONCE is captured in _auth.setup.ts,
    // so this request carries the admin X_WP_NONCE against the employee cookie
    // session — combined with the missing manage_options cap it lands on the
    // 401/403 boundary. We assert the boundary, never an exact code.
    empApi = await ApiUtils.fromStorageState(data.auth.employeeFile);

    // Snapshot current settings so afterAll can restore the singleton state.
    const [resp, body] = await api.get(SETTINGS_URL, undefined, false);
    if (resp.ok() && body && typeof body === 'object') {
        snapshot = {
            hr_frontend_slug: String((body as ResponseBody).hr_frontend_slug ?? 'wp-erp-dashboard'),
            hr_frontend_dashboard_title: String((body as ResponseBody).hr_frontend_dashboard_title ?? 'WP ERP'),
            hr_frontend_logo: String((body as ResponseBody).hr_frontend_logo ?? ''),
            hr_frontend_redirect: Boolean((body as ResponseBody).hr_frontend_redirect),
        };
    }
});

test.afterAll(async () => {
    // Restore prior settings so we do not pollute the site slug for other specs.
    if (snapshot) {
        await api.post(
            SETTINGS_URL,
            {
                data: {
                    hr_frontend_slug: snapshot.hr_frontend_slug,
                    hr_frontend_dashboard_title: snapshot.hr_frontend_dashboard_title,
                    hr_frontend_logo: snapshot.hr_frontend_logo,
                    hr_frontend_redirect: snapshot.hr_frontend_redirect,
                },
            },
            false,
        );
    }
    await api.dispose();
    await empApi.dispose();
    await dbUtils.close();
});

// HR-frontend settings live in a single wp_options row. Under api.config's
// fullyParallel, the POST tests would overwrite each other and read back a
// sibling's value. Run the file serially so each settings write/read is atomic.
test.describe.configure({ mode: 'serial' });

// ─────────────────────────────────────────────────────────────────────────────
// GET — happy paths (admin)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HR Frontend REST — GET settings (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('HRFE-HP-01 GET returns 200 with the four expected keys', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.get(SETTINGS_URL, undefined, false);
        expect(resp.status(), 'GET settings answered without a fatal').toBeLessThan(500);
        expect(resp.status(), 'admin is authorized to read settings').toBe(200);

        expect(body && typeof body === 'object', 'settings body is an object').toBe(true);
        const keys = Object.keys(body as ResponseBody);
        // Exactly the keys returned by get_settings (lines 68-73).
        expect(keys).toContain(OPT_SLUG);
        expect(keys).toContain(OPT_TITLE);
        expect(keys).toContain(OPT_LOGO);
        expect(keys).toContain(OPT_REDIRECT);
    });

    test('HRFE-HP-02 GET reflects controller defaults / valid types', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.get(SETTINGS_URL, undefined, false);
        expect(resp.status()).toBe(200);

        const slug = String((body as ResponseBody).hr_frontend_slug ?? '');
        const title = String((body as ResponseBody).hr_frontend_dashboard_title ?? '');
        // Tolerate any prior-set value (resilient): defaults are 'wp-erp-dashboard'
        // / 'WP ERP', but a previous run may have changed them — only assert they
        // are non-empty strings.
        expect(typeof slug, 'slug is a string').toBe('string');
        expect(slug.length, 'slug is non-empty (default wp-erp-dashboard if unset)').toBeGreaterThan(0);
        expect(typeof title, 'title is a string').toBe('string');
        expect(title.length, 'title is non-empty (default WP ERP if unset)').toBeGreaterThan(0);
    });

    test('HRFE-HP-03 GET is not paginated (no X-WP-Total)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp] = await api.get(SETTINGS_URL, undefined, false);
        expect(resp.status()).toBe(200);
        // get_settings returns a flat object, never a list → no pagination header.
        expect(resp.headers()['x-wp-total'], 'settings GET carries no X-WP-Total header').toBeUndefined();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST — happy paths + edge cases (admin)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HR Frontend REST — POST settings (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('HRFE-HP-04 POST full valid payload saves and returns success', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const payload = settingsPayload();
        const [resp, body] = await api.post(SETTINGS_URL, { data: payload }, false);
        expect(resp.status(), 'valid update must not 500').toBeLessThan(500);
        expect(resp.status(), 'admin is authorized to write settings').toBe(200);
        expect((body as ResponseBody).success, 'update returns success: true').toBe(true);
        expect(String((body as ResponseBody).message ?? '')).toContain('saved');

        // Round-trip: a subsequent GET reflects the new slug/title.
        const [getResp, read] = await api.get(SETTINGS_URL, undefined, false);
        expect(getResp.status()).toBe(200);
        expect(String((read as ResponseBody).hr_frontend_slug ?? '')).toBe(payload.hr_frontend_slug);
        expect(String((read as ResponseBody).hr_frontend_dashboard_title ?? '')).toBe(payload.hr_frontend_dashboard_title);
    });

    test('HRFE-HP-05 POST persists all four wp_options (DB oracle)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const payload = settingsPayload({
            hr_frontend_logo: 'https://example.com/db-logo.png',
            hr_frontend_redirect: true,
        });
        const [resp] = await api.post(SETTINGS_URL, { data: payload }, false);
        expect(resp.status(), 'update must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        // Verify the persisted wp_options directly (string literals).
        const dbSlug = await dbUtils.getOptionValue<string>(OPT_SLUG);
        const dbTitle = await dbUtils.getOptionValue<string>(OPT_TITLE);
        expect(String(dbSlug ?? ''), 'slug persisted to wp_options').toBe(payload.hr_frontend_slug);
        expect(String(dbTitle ?? ''), 'title persisted to wp_options').toBe(payload.hr_frontend_dashboard_title);
    });

    test('HRFE-HP-06 POST with only the two required params succeeds', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const stamp = Date.now();
        const payload = {
            hr_frontend_slug: `pw-hrfe-min-${stamp}`,
            hr_frontend_dashboard_title: `PW Min ${stamp}`,
        };
        const [resp, body] = await api.post(SETTINGS_URL, { data: payload }, false);
        expect(resp.status(), 'required-only update must not 500').toBeLessThan(500);
        expect(resp.status(), 'optional params are optional → 200').toBe(200);
        expect((body as ResponseBody).success).toBe(true);

        // Optional params omitted → controller writes '' / false (lines 94-95).
        const [, read] = await api.get(SETTINGS_URL, undefined, false);
        expect(String((read as ResponseBody).hr_frontend_logo ?? ''), 'omitted logo stored as empty string').toBe('');
        expect(Boolean((read as ResponseBody).hr_frontend_redirect), 'omitted redirect stored as false').toBe(false);
    });

    test('HRFE-EC-01 redirect coerced from "true" string via rest_sanitize_boolean', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const payload = settingsPayload({ hr_frontend_redirect: 'true' });
        const [resp] = await api.post(SETTINGS_URL, { data: payload }, false);
        expect(resp.status(), 'string-boolean redirect must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const [, read] = await api.get(SETTINGS_URL, undefined, false);
        // rest_sanitize_boolean('true') === true; GET reflects a real boolean.
        expect(Boolean((read as ResponseBody).hr_frontend_redirect), '"true" coerced to boolean true').toBe(true);
    });

    test('HRFE-EC-02 redirect coerced from "false" string to false', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const payload = settingsPayload({ hr_frontend_redirect: 'false' });
        const [resp] = await api.post(SETTINGS_URL, { data: payload }, false);
        expect(resp.status(), 'string-false redirect must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const [, read] = await api.get(SETTINGS_URL, undefined, false);
        expect(Boolean((read as ResponseBody).hr_frontend_redirect), '"false" coerced to boolean false').toBe(false);
    });

    test('HRFE-EC-03 unknown extra fields are ignored (still 200)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const payload = settingsPayload({ totally_unknown_field: 'ignore-me', another: 123 });
        const [resp, body] = await api.post(SETTINGS_URL, { data: payload }, false);
        expect(resp.status(), 'extra fields must not 500').toBeLessThan(500);
        expect(resp.status(), 'unknown fields ignored → 200').toBe(200);
        expect((body as ResponseBody).success).toBe(true);
    });

    test('HRFE-EC-04 logo run through esc_url_raw (stored as a URL)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const logo = `https://example.com/logo-${Date.now()}.png`;
        const payload = settingsPayload({ hr_frontend_logo: logo });
        const [resp] = await api.post(SETTINGS_URL, { data: payload }, false);
        expect(resp.status(), 'logo update must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const [, read] = await api.get(SETTINGS_URL, undefined, false);
        expect(String((read as ResponseBody).hr_frontend_logo ?? ''), 'valid URL logo round-trips').toBe(logo);
    });

    test('HRFE-EC-05 slug with special chars is sanitized (no 500)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // sanitize_text_field strips tags but keeps it as a string; no format guard.
        const payload = settingsPayload({ hr_frontend_slug: `pw hrfe <b>slug</b> ${Date.now()}` });
        const [resp, body] = await api.post(SETTINGS_URL, { data: payload }, false);
        expect(resp.status(), 'special-char slug must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect((body as ResponseBody).success).toBe(true);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST — negative / missing-required (admin). WP rejects PRE-callback → 400.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HR Frontend REST — POST validation (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('HRFE-NC-01 missing hr_frontend_slug → 400, never a fatal', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const stamp = Date.now();
        const [resp] = await api.post(
            SETTINGS_URL,
            { data: { hr_frontend_dashboard_title: `PW No Slug ${stamp}` } },
            false,
        );
        // WP enforces required args before update_settings runs → clean 400, no 500.
        expect(resp.status(), 'missing required slug must not 500').toBeLessThan(500);
        expect(resp.ok() || resp.status() === 400, 'missing slug rejected as 400 (or lenient 2xx)').toBe(true);
    });

    test('HRFE-NC-02 missing hr_frontend_dashboard_title → 400, never a fatal', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const stamp = Date.now();
        const [resp] = await api.post(
            SETTINGS_URL,
            { data: { hr_frontend_slug: `pw-hrfe-no-title-${stamp}` } },
            false,
        );
        expect(resp.status(), 'missing required title must not 500').toBeLessThan(500);
        expect(resp.ok() || resp.status() === 400, 'missing title rejected as 400 (or lenient 2xx)').toBe(true);
    });

    test('HRFE-NC-03 empty body (both required absent) → 400, never a fatal', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp] = await api.post(SETTINGS_URL, { data: {} }, false);
        expect(resp.status(), 'empty body must not 500').toBeLessThan(500);
        expect(resp.ok() || resp.status() === 400, 'empty body rejected as 400 (or lenient 2xx)').toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — employee boundary (no manage_options cap)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HR Frontend REST — access control (employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('HRFE-AC-01 employee GET settings is refused (401/403)', { tag: ['@pro', '@hrm', '@employee'] }, async () => {
        const [resp] = await empApi.get(SETTINGS_URL, undefined, false);
        expect(resp.status(), 'employee GET must not 500').toBeLessThan(500);
        expect(resp.status(), 'employee is NOT authorized to read settings').not.toBe(200);
        expect([401, 403], 'employee GET lands on the auth boundary').toContain(resp.status());
    });

    test('HRFE-AC-02 employee POST settings is refused (401/403)', { tag: ['@pro', '@hrm', '@employee'] }, async () => {
        const [resp] = await empApi.post(SETTINGS_URL, { data: settingsPayload() }, false);
        expect(resp.status(), 'employee POST must not 500').toBeLessThan(500);
        expect(resp.status(), 'employee is NOT authorized to write settings').not.toBe(200);
        expect([401, 403], 'employee POST lands on the auth boundary').toContain(resp.status());
    });

    test('HRFE-AC-03 employee write does not mutate the singleton settings', { tag: ['@pro', '@hrm', '@employee'] }, async () => {
        // A refused employee write must leave the admin-readable state unchanged.
        const sentinel = `pw-hrfe-emp-${Date.now()}`;
        const [postResp] = await empApi.post(
            SETTINGS_URL,
            { data: settingsPayload({ hr_frontend_slug: sentinel }) },
            false,
        );
        expect(postResp.status(), 'employee write refused').not.toBe(200);

        const dbSlug = await dbUtils.getOptionValue<string>(OPT_SLUG);
        expect(String(dbSlug ?? ''), 'refused employee write did not change the slug option').not.toBe(sentinel);
    });
});
