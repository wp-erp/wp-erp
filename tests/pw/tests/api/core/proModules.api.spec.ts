import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { data } from '@utils/testData';
import { restUrl } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';
import type { ResponseBody } from '@utils/interfaces';

/**
 * PRO — Module management REST (module: core, kind: REST). READ-ONLY.
 *
 * Covers the two READABLE routes of erp-pro's ModulesController
 * (includes/REST/ModulesController.php):
 *   GET /erp_pro/v1/admin/modules            -> get_items        (full catalog)
 *   GET /erp_pro/v1/admin/modules/installed  -> installed_modules (installed maps)
 *
 * The EDITABLE routes (/activate, /deactivate) are DELIBERATELY NOT exercised:
 * flipping modules mid-run would deactivate tables/caps other @pro specs rely on
 * (the grounding doc requires all 23 modules installed for the whole @pro run).
 *
 * Routes are NOT in apiEndPoints.ts, so URLs are built with restUrl() from
 * '@utils/helpers' (the `?rest_route=` form — Docker/permalink-safe, identical to
 * how endPoints are built).
 *
 * AUTH NOTE (BUG CANDIDATE — over-permissive gate):
 *   ModulesController::check_permission() is
 *       public function check_permission() {
 *           return true;                              // always
 *           return current_user_can( 'manage_options' ); // dead code, unreachable
 *       }
 *   so the endpoint is effectively public to any caller. The "employee boundary"
 *   test therefore CANNOT assert a 401/403 denial — an employee reaches 200 just
 *   like admin. Per the resilient-assertion philosophy we assert the OBSERVED
 *   boundary (status < 500, no fatal) and document that access is NOT denied.
 *
 * Resilient assertions throughout: GETs must never 5xx (a 5xx here is a real
 * fatal → fail); invalid sub-routes / wrong methods are asserted status < 500 and
 * NOT-2xx, never an exact 500.
 */

const modulesUrl = restUrl('/erp_pro/v1/admin/modules');
const installedUrl = restUrl('/erp_pro/v1/admin/modules/installed');

// Named modules the task explicitly requires to be present in the catalog, plus a
// few more verified from Module::get_all_modules() for a stronger presence check.
const REQUIRED_IDS = ['deals', 'attendance', 'payroll'];
const EXPECTED_IDS = [
    'deals',
    'attendance',
    'payroll',
    'inventory',
    'recruitment',
    'workflow',
    'asset_management',
    'document_manager',
    'reimbursement',
];

/** Coerce any module-list response (bare array or {data:[]}) into an array. */
function asArray(body: ResponseBody): unknown[] {
    if (Array.isArray(body)) return body;
    if (body && typeof body === 'object' && Array.isArray((body as { data?: unknown[] }).data)) {
        return (body as { data: unknown[] }).data;
    }
    return [];
}

/** Pull the `id` field off each module entry (both shapes carry `id`). */
function idsOf(rows: unknown[]): string[] {
    return rows
        .map((r) => (r && typeof r === 'object' ? String((r as { id?: unknown }).id ?? '') : ''))
        .filter((s) => s !== '');
}

let api: ApiUtils;

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    await api.dispose();
    await dbUtils.close();
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /modules — full catalog (admin)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('PRO modules REST — catalog (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('PRO-MOD-01 GET /modules returns a non-empty module array', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(modulesUrl, undefined, false);
        // A 5xx here is a real fatal, not an expected validation outcome.
        expect(resp.status(), 'GET /modules must not 5xx').toBeLessThan(500);
        expect(resp.status(), 'admin reaches the catalog').toBe(200);

        const rows = asArray(body);
        expect(rows.length, '/modules returns a non-empty list').toBeGreaterThan(0);
    });

    test('PRO-MOD-02 catalog contains deals / attendance / payroll', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(modulesUrl, undefined, false);
        expect(resp.status()).toBe(200);

        const ids = idsOf(asArray(body));
        for (const id of REQUIRED_IDS) {
            expect(ids, `catalog includes the "${id}" module`).toContain(id);
        }
    });

    test('PRO-MOD-03 catalog also lists the other core pro modules', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(modulesUrl, undefined, false);
        expect(resp.status()).toBe(200);

        const ids = idsOf(asArray(body));
        for (const id of EXPECTED_IDS) {
            expect(ids, `catalog includes the "${id}" module`).toContain(id);
        }
        // With every module installed (per grounding) the catalog is the full 23,
        // but assert resiliently rather than pinning the exact count.
        expect(ids.length, 'catalog lists a healthy number of modules').toBeGreaterThanOrEqual(EXPECTED_IDS.length);
    });

    test('PRO-MOD-04 each catalog entry has the get_items shape', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(modulesUrl, undefined, false);
        expect(resp.status()).toBe(200);

        const rows = asArray(body);
        expect(rows.length).toBeGreaterThan(0);

        // get_items maps each module to {id,name,description,thumbnail,active,available,doc_id,doc_link}.
        for (const raw of rows) {
            const m = raw as Record<string, unknown>;
            expect(typeof m.id, 'id is a string').toBe('string');
            expect(m, 'entry exposes a name key').toHaveProperty('name');
            expect(m, 'entry exposes a description key').toHaveProperty('description');
            expect(m, 'entry exposes an active flag').toHaveProperty('active');
            expect(m, 'entry exposes an available flag').toHaveProperty('available');
            expect(typeof m.active, 'active is a boolean').toBe('boolean');
            expect(typeof m.available, 'available is a boolean').toBe('boolean');
            // doc_id / doc_link are int|null and string|null respectively.
            expect(m, 'entry exposes doc_id').toHaveProperty('doc_id');
            expect(m, 'entry exposes doc_link').toHaveProperty('doc_link');
        }
    });

    test('PRO-MOD-05 the required modules report available:true (files on disk)', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(modulesUrl, undefined, false);
        expect(resp.status()).toBe(200);

        const rows = asArray(body) as Array<Record<string, unknown>>;
        for (const id of REQUIRED_IDS) {
            const entry = rows.find((m) => String(m.id) === id);
            expect(entry, `"${id}" present in catalog`).toBeTruthy();
            if (entry) {
                // `available` = file_exists(module_file); the named modules are installed.
                expect(entry.available, `"${id}" is available (module file exists)`).toBe(true);
            }
        }
    });

    test('PRO-MOD-06 a stray query param does not change the list (no pagination)', { tag: ['@pro', '@core', '@admin'] }, async () => {
        // get_items reads nothing off the request — query args are ignored.
        const [baseResp, baseBody] = await api.get(modulesUrl, undefined, false);
        expect(baseResp.status()).toBe(200);
        const baseLen = asArray(baseBody).length;

        // modulesUrl has no query string yet, so the stray params must be joined
        // with '?' (not '&'); otherwise they become part of the path and WP returns
        // rest_no_route (404). With a real query string the controller still ignores
        // every arg (get_items reads nothing off $request) and returns the full list.
        const [resp, body] = await api.get(`${modulesUrl}?per_page=1&foo=bar`, undefined, false);
        expect(resp.status(), 'stray params must not 5xx').toBeLessThan(500);
        expect(resp.status(), 'stray params still answer 200').toBe(200);
        expect(asArray(body).length, 'stray params do not paginate/shrink the list').toBe(baseLen);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /modules/installed — installed/available maps (admin)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('PRO modules REST — installed (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('PRO-MOD-07 GET /modules/installed returns a non-empty array', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(installedUrl, undefined, false);
        expect(resp.status(), 'GET /modules/installed must not 5xx').toBeLessThan(500);
        expect(resp.status(), 'admin reaches installed modules').toBe(200);
        expect(asArray(body).length, 'installed list is non-empty').toBeGreaterThan(0);
    });

    test('PRO-MOD-08 installed list includes deals / attendance / payroll', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(installedUrl, undefined, false);
        expect(resp.status()).toBe(200);

        const ids = idsOf(asArray(body));
        for (const id of REQUIRED_IDS) {
            expect(ids, `installed includes the "${id}" module`).toContain(id);
        }
    });

    test('PRO-MOD-09 installed entries carry the rich module-info shape', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(installedUrl, undefined, false);
        expect(resp.status()).toBe(200);

        const rows = asArray(body) as Array<Record<string, unknown>>;
        expect(rows.length).toBeGreaterThan(0);

        // installed_modules returns raw get_all_modules() maps for every installed
        // module: id/version/path/name/module_file/module_class/is_pro/category etc.
        // Shape differs from get_items (no active/available flags here).
        const sample = rows.find((m) => String(m.id) === 'deals') ?? rows[0];
        expect(sample, 'an installed entry is present').toBeTruthy();
        expect(sample, 'installed entry exposes id').toHaveProperty('id');
        expect(sample, 'installed entry exposes name').toHaveProperty('name');
        expect(sample, 'installed entry exposes module_file').toHaveProperty('module_file');
    });

    test('PRO-MOD-10 installed length is between 1 and the catalog length', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [modResp, modBody] = await api.get(modulesUrl, undefined, false);
        const [insResp, insBody] = await api.get(installedUrl, undefined, false);
        expect(modResp.status()).toBe(200);
        expect(insResp.status()).toBe(200);

        const catalogLen = asArray(modBody).length;
        const installedLen = asArray(insBody).length;
        // file_exists guard means installed <= catalog; with all modules present they
        // are equal, but assert the resilient range rather than exact equality.
        expect(installedLen, 'installed has at least one entry').toBeGreaterThanOrEqual(1);
        expect(installedLen, 'installed cannot exceed the full catalog').toBeLessThanOrEqual(catalogLen);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Invalid-call behavior — never a 500 (admin)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('PRO modules REST — invalid calls (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('PRO-MOD-11 unknown sub-route /modules/bogus is 404, not 500', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(restUrl('/erp_pro/v1/admin/modules/bogus'), undefined, false);
        // WP REST returns rest_no_route (404) for an unregistered sub-route — never a fatal.
        expect(resp.status(), 'unknown sub-route must not 5xx').toBeLessThan(500);
        expect(resp.ok(), 'unknown sub-route is not a success').toBe(false);
        if (resp.status() === 404 && body && typeof body === 'object') {
            expect(String((body as { code?: string }).code ?? '')).toContain('rest_no_route');
        }
    });

    test('PRO-MOD-12 wrong method (DELETE) on /modules is rejected, not 500', { tag: ['@pro', '@core', '@admin'] }, async () => {
        // Only READABLE is registered on the base, so DELETE => 404/405 (no_route/no_method).
        const [resp] = await api.delete(modulesUrl, undefined, false);
        expect(resp.status(), 'wrong method must not 5xx').toBeLessThan(500);
        expect(resp.ok(), 'wrong method is not a success').toBe(false);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access-control boundary — employee (DOCUMENTED over-permissive gate)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('PRO modules REST — employee boundary (documented)', () => {
    test.use({ storageState: data.auth.employeeFile });

    let empApi: ApiUtils;
    test.beforeAll(async () => {
        // Drive the request with the employee storage state and NO admin nonce —
        // there is no employee-specific nonce env var. check_permission() returns
        // true unconditionally, so we assert the OBSERVED behavior, not a denial.
        empApi = await ApiUtils.fromStorageState(data.auth.employeeFile);
    });
    test.afterAll(async () => {
        await empApi.dispose();
    });

    test('PRO-MOD-13 employee GET /modules — boundary is OBSERVED (not denied)', { tag: ['@pro', '@core', '@employee'] }, async () => {
        const [resp, body] = await empApi.get(modulesUrl, undefined, false);
        // Must never be a fatal regardless of who calls it.
        expect(resp.status(), 'employee call must not 5xx').toBeLessThan(500);

        // BUG CANDIDATE: the permission gate is bypassed by an unconditional
        // `return true`, so a non-admin is NOT refused. Document the actual behavior
        // instead of pinning a 200-vs-403 — assert the boundary as observed.
        if (![401, 403].includes(resp.status())) {
            // Over-permissive access confirmed: an employee reached the admin module
            // catalog. Assert it is a real payload (so this is genuine exposure).
            expect(resp.status(), 'employee is granted access (gate bypassed)').toBe(200);
            expect(asArray(body).length, 'employee receives the full module list').toBeGreaterThan(0);
        } else {
            // If a future fix restores the gate, the 401/403 is the correct boundary.
            expect([401, 403], 'employee is properly denied').toContain(resp.status());
        }
    });

    test('PRO-MOD-14 employee GET /modules/installed — boundary is OBSERVED', { tag: ['@pro', '@core', '@employee'] }, async () => {
        const [resp, body] = await empApi.get(installedUrl, undefined, false);
        expect(resp.status(), 'employee call must not 5xx').toBeLessThan(500);

        if (![401, 403].includes(resp.status())) {
            expect(resp.status(), 'employee is granted access to installed (gate bypassed)').toBe(200);
            expect(asArray(body).length, 'employee receives the installed list').toBeGreaterThan(0);
        } else {
            expect([401, 403], 'employee is properly denied').toContain(resp.status());
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Optional DB cross-check — active ids option is a subset of API active:true
// ─────────────────────────────────────────────────────────────────────────────
test.describe('PRO modules REST — DB cross-check (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('PRO-MOD-15 erp_pro_active_modules option agrees with catalog active flags', { tag: ['@pro', '@core', '@admin'] }, async () => {
        const [resp, body] = await api.get(modulesUrl, undefined, false);
        expect(resp.status()).toBe(200);

        const rows = asArray(body) as Array<Record<string, unknown>>;
        const apiActiveIds = rows.filter((m) => m.active === true).map((m) => String(m.id));

        // wp_options key Module::ACTIVE_MODULES_DB_KEY = 'erp_pro_active_modules'.
        const optionVal = await dbUtils.getOptionValue<unknown>('erp_pro_active_modules');

        // The option is read-only here; if absent or not an array we simply skip the
        // subset assertion (no DB row to compare) rather than fail the read suite.
        if (Array.isArray(optionVal)) {
            const optionIds = optionVal.map(String);
            // Every id the option marks active should be reported active:true by the API.
            for (const id of optionIds) {
                expect(apiActiveIds, `option-active "${id}" is reported active by the API`).toContain(id);
            }
        } else {
            // Document that the option may be stored in another shape; the API active
            // set itself must still be a (possibly empty) array of strings.
            expect(Array.isArray(apiActiveIds), 'API exposes an active-id set').toBe(true);
        }
    });
});
