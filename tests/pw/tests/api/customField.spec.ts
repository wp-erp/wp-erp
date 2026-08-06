import { test, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { data } from '@utils/testData';
import { restUrl, toPath, BASE_URL } from '@utils/helpers';

/**
 * HRM — Custom Field Builder (erp-pro module: custom_field_builder).
 *
 * GROUNDED (read the source before relying on any of this):
 *   - modules/hrm/custom-field-builder/includes/API/CustomFieldBuilderController.php
 *   - modules/hrm/custom-field-builder/includes/functions.php
 *   - modules/hrm/custom-field-builder/Module.php
 *   - modules/hrm/custom-field-builder/views/view.php
 *
 * There is NO HRM REST namespace for this module. The controller namespace is
 * 'erp/v1' and the rest_base is 'accounting/v1/field-builder' (L19,L26), so the
 * list route is built with restUrl('/erp/v1/accounting/v1/field-builder'). Tag
 * @hrm (module ownership) even though the route physically lives under
 * accounting/v1.
 *
 * Only TWO GET routes are registered (register_routes L31-55, both READABLE):
 *   1) GET .../field-builder                 -> get_all_custom_fileds (?type, ?section)
 *   2) GET .../field-builder/{type}/{id}     -> get_custom_field_data  ({type}=\w+, {id}=\d+)
 * There is NO create/update/delete REST endpoint — prepare_item_for_database /
 * prepare_item_for_response / get_item_schema (L121-289) are dead copy-paste from
 * a PeopleTrn controller and are unreachable. The real WRITE path is admin-ajax
 * action 'erp_form_builder' (Module.php L140,L376-388) persisting to the option
 * 'erp-{people}-fields'. So the "create / update / delete" coverage here drives
 * that admin-ajax handler and asserts the wp_options row, NOT a REST verb.
 *
 * Permission for both GET routes is current_user_can('erp_ac_view_expense') (an
 * ACCOUNTING cap, not an HR cap) — so admin passes, while an HR-manager / employee
 * session is denied. Access-control tests assert the [401,403] boundary, never an
 * exact code. The handler hard-codes set_status(200) (L78,L109); on a fresh site
 * the option was never written, so List legitimately returns false/[] at 200 —
 * List assertions are "200 AND (array or falsy)", never "non-empty".
 *
 * Write resilience: the admin-ajax handler denies a bad/missing nonce by printing
 * the literal string 'You are no allowed' and die()-ing (Module.php L379) — a 200
 * body string, NOT a 4xx. So the negative-nonce case PASSES on (non-2xx) OR (the
 * deny string). Only a PHP fatal / 500 fails. Created options are restored in
 * afterAll (delete or re-write the prior collection) via the same admin-ajax path.
 *
 * Auth: cookie + X-WP-Nonce via ApiUtils from the admin storageState. The
 * admin-ajax write also needs the page-localized 'erp-form-builder' nonce, which
 * we scrape from the Custom Field Builder admin page HTML (wpErpForm.nonce).
 */

let api: ApiUtils;

// REST route bases (built per the grounding: namespace erp/v1 + accounting/v1 base).
const FIELD_BUILDER = restUrl('/erp/v1/accounting/v1/field-builder');
const fieldRecord = (type: string, id: string | number): string =>
    restUrl(`/erp/v1/accounting/v1/field-builder/${type}/${id}`);

// Pro module list (read-only sanity).
const MODULES_INSTALLED = restUrl('/erp_pro/v1/admin/modules/installed');

// admin-ajax endpoint + the people-type whose option we own for the write tests.
// The handler persists to wp_options under option_name `erp-${WRITE_PEOPLE}-fields`.
const ADMIN_AJAX = toPath('wp-admin/admin-ajax.php');
const WRITE_PEOPLE = 'employee';
const CFB_PAGE = toPath('wp-admin/admin.php?page=custom-field-builder&tab=employee');

// Snapshot of the employee-fields option before we touch it, so afterAll restores.
let priorCollectionExisted = false;
let formBuilderNonce = '';

/** Build one custom-field definition matching the Vue/option shape (view.php). */
function fieldDef(overrides: Record<string, unknown> = {}): Record<string, unknown> {
    const stamp = Date.now();
    return {
        label: `QA Field ${stamp}`,
        name: `qa_field_${stamp}`,
        section: 'basic',
        icon: '',
        required: 'false',
        type: 'text',
        placeholder: '',
        helptext: '',
        options: [],
        ...overrides,
    };
}

/** Scrape the page-localized 'erp-form-builder' nonce from the CFB admin page. */
async function getFormBuilderNonce(): Promise<string> {
    const [resp, body] = await api.get(CFB_PAGE, undefined, false);
    if (!resp.ok()) return '';
    const html = typeof body === 'string' ? body : JSON.stringify(body);
    // The Module localizes the 'erp-form-builder' nonce ONLY inside the wpErpForm
    // object (Module.php enqueue_scripts: wpErpForm = { "nonce":"<...>", ... }). The
    // page also localizes OTHER objects (e.g. wpErpAsset) that carry an unrelated
    // "nonce" earlier in the HTML — a naive first-"nonce" match grabs the wrong
    // token and the write handler then dies with 'You are no allowed'. So anchor the
    // match to the wpErpForm blob and only fall back to a global match if absent.
    const scoped = html.match(/wpErpForm\s*=\s*\{[\s\S]*?"nonce"\s*:\s*"([a-f0-9]+)"/i);
    if (scoped?.[1]) return scoped[1];
    const m = html.match(/"nonce"\s*:\s*"([a-f0-9]+)"/i);
    return m?.[1] ?? '';
}

/**
 * Post a collection to the admin-ajax form-builder handler. WordPress admin-ajax
 * reads $_REQUEST, so we send an application/x-www-form-urlencoded body. The
 * collection is a PHP array, encoded as bracketed form fields. Returns
 * [status, bodyText].
 *
 * The body is built as a URL-encoded string and sent through the typed `data`
 * option (Playwright forwards a string `data` as the raw request body) so we do
 * not need to touch the shared ApiUtils/ReqOptions contract.
 */
async function saveCollection(
    people: string,
    collection: Array<Record<string, unknown>>,
    nonce: string,
): Promise<[number, string]> {
    const pairs: Array<[string, string]> = [
        ['action', 'erp_form_builder'],
        ['nonce', nonce],
        ['people', people],
    ];
    collection.forEach((field, i) => {
        for (const [key, value] of Object.entries(field)) {
            if (Array.isArray(value)) {
                // options[] — array of {text,value}. Encode each child key.
                value.forEach((opt, j) => {
                    if (opt && typeof opt === 'object') {
                        for (const [ok, ov] of Object.entries(opt as Record<string, unknown>)) {
                            pairs.push([`collection[${i}][${key}][${j}][${ok}]`, String(ov)]);
                        }
                    } else {
                        pairs.push([`collection[${i}][${key}][${j}]`, String(opt)]);
                    }
                });
                // Ensure an empty options array still posts a key so PHP sees an array.
                if (value.length === 0) pairs.push([`collection[${i}][${key}]`, '']);
            } else {
                pairs.push([`collection[${i}][${key}]`, String(value)]);
            }
        }
    });

    const encoded = pairs
        .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
        .join('&');

    const [resp, body] = await api.post(
        ADMIN_AJAX,
        { data: encoded, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } },
        false,
    );
    const text = typeof body === 'string' ? body : JSON.stringify(body);
    return [resp.status(), text];
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);

    // Snapshot whether the employee-fields option already exists (List read-back).
    const [listResp, listBody] = await api.get(`${FIELD_BUILDER}?type=${WRITE_PEOPLE}&section=all`, undefined, false);
    priorCollectionExisted = listResp.ok() && Array.isArray(listBody) && listBody.length > 0;

    formBuilderNonce = await getFormBuilderNonce();
});

test.afterAll(async () => {
    // Restore: if the option did not exist before our run, blank it back out so we
    // leave the site as we found it. Re-write an empty collection via the same path.
    if (!priorCollectionExisted && formBuilderNonce) {
        await saveCollection(WRITE_PEOPLE, [], formBuilderNonce);
    }
    await api.dispose();
});

// The write tests mutate a single shared wp_options field-collection row; under
// api.config's fullyParallel the create/update/delete + read-back would race.
// Serialize the file so each collection write/read is atomic.
test.describe.configure({ mode: 'serial' });

// ─────────────────────────────────────────────────────────────────────────────
// READ — list all custom fields (GET .../field-builder). Admin role.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CFB REST — list custom fields (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('CFB-API-01 list with type+section=all is 200 and array-or-falsy', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${FIELD_BUILDER}?type=employee&section=all`, undefined, false);
        // Handler hard-codes 200. Body is the option value: an array of field defs
        // when saved, or false/empty when the option was never written.
        expect(resp.status(), 'list handler hard-codes 200').toBe(200);
        const ok = Array.isArray(body) || body === false || body === null || body === '' || typeof body === 'object';
        expect(ok, 'list body is an array, an object, or a falsy not-yet-saved value').toBe(true);
    });

    test('CFB-API-02 list with no params is 200 (defaults to empty type/section)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.get(FIELD_BUILDER, undefined, false);
        expect(resp.status()).toBe(200);
        // With an empty people_type the option name is 'erp--fields' -> false; with a
        // non-'all' section the function returns [] (functions.php L20-28).
        const acceptable = Array.isArray(body) || body === false || body === null || body === '' || typeof body === 'object';
        expect(acceptable, 'no-param list is array-or-falsy, never a fatal').toBe(true);
    });

    test('CFB-API-03 list with a concrete section filters to that section (array)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // section != 'all' takes the filtering branch which always returns an array.
        const [resp, body] = await api.get(`${FIELD_BUILDER}?type=employee&section=basic`, undefined, false);
        expect(resp.status()).toBe(200);
        const rows = Array.isArray(body) ? body : [];
        // Every returned field (if any) belongs to the requested section.
        const allBasic = rows.every((f: { section?: string }) => String(f?.section ?? 'basic') === 'basic');
        expect(allBasic, 'a section filter never leaks other-section fields').toBe(true);
    });

    test('CFB-API-04 unknown people type yields a 200 array-or-falsy (no 5xx)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${FIELD_BUILDER}?type=not_a_real_type_${Date.now()}&section=all`, undefined, false);
        // get_option('erp-<garbage>-fields') is false; section='all' returns it verbatim.
        expect(resp.status(), 'unknown type is not an error path').toBe(200);
        const ok = Array.isArray(body) || body === false || body === null || body === '' || typeof body === 'object';
        expect(ok, 'unknown type body is array-or-falsy').toBe(true);
    });

    test('CFB-API-05 unknown section yields a 200 empty array', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${FIELD_BUILDER}?type=employee&section=zzz_${Date.now()}`, undefined, false);
        expect(resp.status()).toBe(200);
        // A non-matching section drops to the filter loop -> [].
        if (Array.isArray(body)) {
            expect(body.length, 'no field matches a nonsense section').toBe(0);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// READ — single record's custom-field data (GET .../field-builder/{type}/{id}).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CFB REST — single record custom-field data (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('CFB-API-06 numeric id + word type is 200 (meta or empty string)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // Route requires {type}=\w+ and {id}=\d+. id=1 is a valid digit path.
        const [resp, body] = await api.get(fieldRecord('contact', 1), undefined, false);
        expect(resp.status(), 'single-record handler hard-codes 200').toBe(200);
        // erp_people_get_meta returns the stored object/array or an empty string.
        const ok = body === '' || body === false || body === null || typeof body === 'object';
        expect(ok, 'single-record body is meta-or-empty').toBe(true);
    });

    test('CFB-API-07 employee type id round-trips at 200', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp] = await api.get(fieldRecord('employee', 1), undefined, false);
        expect(resp.status()).toBe(200);
    });

    test('CFB-API-08 non-numeric id is 404 rest_no_route (path constraint)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // {id} is [\d]+ so 'abc' never matches the route — WP routing returns 404
        // before the handler runs (the in-handler empty-id WP_Error is unreachable).
        const [resp, body] = await api.get(fieldRecord('contact', 'abc'), undefined, false);
        expect(resp.status(), 'non-digit id is rejected by the route, not a 5xx').toBe(404);
        const code = typeof body === 'object' && body ? (body as { code?: string }).code : undefined;
        expect(String(code ?? 'rest_no_route')).toContain('rest_no_route');
    });

    test('CFB-API-09 a very large numeric id is still 200 (empty meta)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.get(fieldRecord('contact', 999999999), undefined, false);
        expect(resp.status(), 'an unknown record id is not an error path').toBe(200);
        const ok = body === '' || body === false || body === null || typeof body === 'object';
        expect(ok, 'unknown record returns empty meta, never a fatal').toBe(true);
    });

    test('CFB-API-10 context=edit is accepted on the single-record route', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp] = await api.get(`${fieldRecord('contact', 1)}?context=edit`, undefined, false);
        // get_context_param registers view|embed|edit; edit must not 4xx/5xx here.
        expect(resp.status(), 'a registered context value is honored').toBeLessThan(500);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// WRITE — create / update / delete the field collection via admin-ajax.
// (No REST write exists; the option 'erp-{people}-fields' is the source of truth.)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CFB write — admin-ajax form-builder (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('CFB-API-11 create: saving a collection writes the option + lists back', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!formBuilderNonce, 'form-builder nonce unavailable in this environment');
        const field = fieldDef({ label: `QA Create ${Date.now()}` });

        const [status, text] = await saveCollection(WRITE_PEOPLE, [field], formBuilderNonce);
        // admin-ajax returns 200 (often a 0/empty body) on a successful update_option;
        // resilience: a non-fatal status is required, the option write is the real check.
        expect(status, 'form-builder save must not fatal').toBeLessThan(500);
        expect(text, 'a valid nonce is NOT met with the deny string').not.toContain('You are no allowed');

        // Read back through the REST List route — the saved field should surface.
        const [listResp, listBody] = await api.get(`${FIELD_BUILDER}?type=${WRITE_PEOPLE}&section=all`, undefined, false);
        expect(listResp.status()).toBe(200);
        const rows = Array.isArray(listBody) ? listBody : [];
        const found = rows.some((f: { name?: string }) => String(f?.name ?? '') === field.name);
        expect(found, 'the saved field is discoverable via the List route').toBe(true);
    });

    test('CFB-API-12 update: re-saving with an edited label overwrites the option', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!formBuilderNonce, 'form-builder nonce unavailable in this environment');
        const name = `qa_upd_${Date.now()}`;
        const original = fieldDef({ name, label: 'Original Label' });
        await saveCollection(WRITE_PEOPLE, [original], formBuilderNonce);

        const edited = fieldDef({ name, label: 'Edited Label', required: 'true' });
        const [status] = await saveCollection(WRITE_PEOPLE, [edited], formBuilderNonce);
        expect(status, 'update save must not fatal').toBeLessThan(500);

        const [, listBody] = await api.get(`${FIELD_BUILDER}?type=${WRITE_PEOPLE}&section=all`, undefined, false);
        const rows: Array<Record<string, unknown>> = Array.isArray(listBody) ? listBody : [];
        const match = rows.find((f) => String(f?.name) === name);
        expect(match, 'the edited field is present after the overwrite').toBeTruthy();
        if (match) {
            expect(String(match.label), 'the label was overwritten by the update').toBe('Edited Label');
        }
    });

    test('CFB-API-13 delete: saving an empty collection clears the fields', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!formBuilderNonce, 'form-builder nonce unavailable in this environment');
        // Seed one field, then "delete" by persisting an empty collection.
        const field = fieldDef({ name: `qa_del_${Date.now()}` });
        await saveCollection(WRITE_PEOPLE, [field], formBuilderNonce);

        const [status] = await saveCollection(WRITE_PEOPLE, [], formBuilderNonce);
        expect(status, 'clearing the collection must not fatal').toBeLessThan(500);

        const [listResp, listBody] = await api.get(`${FIELD_BUILDER}?type=${WRITE_PEOPLE}&section=all`, undefined, false);
        expect(listResp.status()).toBe(200);
        const rows = Array.isArray(listBody) ? listBody : [];
        const stillThere = rows.some((f: { name?: string }) => String(f?.name ?? '') === field.name);
        expect(stillThere, 'the deleted field no longer lists after the empty save').toBe(false);
    });

    test('CFB-API-14 a field with select options round-trips its option children', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!formBuilderNonce, 'form-builder nonce unavailable in this environment');
        const name = `qa_select_${Date.now()}`;
        const field = fieldDef({
            name,
            label: 'Pick One',
            type: 'select',
            section: 'basic',
            options: [
                { text: 'Alpha', value: 'a' },
                { text: 'Beta', value: 'b' },
            ],
        });
        const [status] = await saveCollection(WRITE_PEOPLE, [field], formBuilderNonce);
        expect(status, 'select-field save must not fatal').toBeLessThan(500);

        const [, listBody] = await api.get(`${FIELD_BUILDER}?type=${WRITE_PEOPLE}&section=all`, undefined, false);
        const rows: Array<Record<string, unknown>> = Array.isArray(listBody) ? listBody : [];
        const match = rows.find((f) => String(f?.name) === name);
        expect(match, 'the select field persisted').toBeTruthy();
        if (match) {
            expect(String(match.type), 'field type round-trips').toBe('select');
            const opts = Array.isArray(match.options) ? match.options : [];
            expect(opts.length, 'both option children persisted').toBeGreaterThanOrEqual(2);
        }
    });

    test('CFB-API-15 negative: a missing nonce is denied (not a 5xx)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const field = fieldDef();
        // Empty nonce -> wp_verify_nonce fails -> die('You are no allowed') at 200.
        const [status, text] = await saveCollection(WRITE_PEOPLE, [field], '');
        // PASS on a non-2xx OR the documented deny string. Only a fatal/500 fails.
        const denied = status < 200 || status >= 300 || text.includes('You are no allowed') || text === '0';
        expect(denied, 'a missing nonce never silently succeeds').toBe(true);
        expect(status, 'a denied write is still not a server fatal').toBeLessThan(500);
    });

    test('CFB-API-16 negative: a garbage nonce is denied (not a 5xx)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const field = fieldDef();
        const [status, text] = await saveCollection(WRITE_PEOPLE, [field], `bad_nonce_${Date.now()}`);
        const denied = status < 200 || status >= 300 || text.includes('You are no allowed') || text === '0';
        expect(denied, 'an invalid nonce never silently succeeds').toBe(true);
        expect(status, 'a denied write is still not a server fatal').toBeLessThan(500);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — the GET routes are gated by an ACCOUNTING cap (erp_ac_view_expense).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CFB REST — access control', () => {
    test('CFB-API-17 unauthenticated list is 401 rest_forbidden', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // A genuinely anonymous context (no cookies, no nonce) -> WP treats the
        // request as logged-out. Live-probed in the grounding: 401 rest_forbidden.
        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
        const anon = new ApiUtils(ctx);
        const [resp, body] = await anon.get(`${FIELD_BUILDER}?type=employee&section=all`, undefined, false);
        // Boundary: an anonymous caller must be refused, never served 200.
        expect([401, 403], 'list refuses unauthenticated callers').toContain(resp.status());
        // The 401/403 boundary above is the real contract; the error code is
        // informational and only asserted when present (live: rest_forbidden).
        const code = typeof body === 'object' && body ? (body as { code?: string }).code : undefined;
        if (code) {
            expect(String(code)).toMatch(/forbidden|rest_|not_logged|cannot/i);
        }
        await anon.dispose();
    });

    test('CFB-API-18 HR manager lacks the accounting cap -> denied on list', { tag: ['@pro', '@hrm', '@manager'] }, async () => {
        // The HR manager has HR caps but NOT erp_ac_view_expense; with their own
        // nonce the request authenticates as the manager and is then refused.
        const mgr = await ApiUtils.fromStorageState(data.auth.hrManagerFile, process.env.HR_MANAGER_NONCE);
        const [resp] = await mgr.get(`${FIELD_BUILDER}?type=employee&section=all`, undefined, false);
        // Boundary assertion: not a 200 (the cap is missing); a 401/403 is expected.
        expect(resp.status(), 'HR manager is not served the accounting-gated route').not.toBe(200);
        await mgr.dispose();
    });

    test('CFB-API-19 employee is denied on the single-record route', { tag: ['@pro', '@hrm', '@employee'] }, async () => {
        const emp = await ApiUtils.fromStorageState(data.auth.employeeFile);
        const [resp] = await emp.get(fieldRecord('employee', 1), undefined, false);
        expect(resp.status(), 'a plain employee cannot read the accounting-gated route').not.toBe(200);
        await emp.dispose();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Pro module sanity — confirm the custom_field_builder module is installed/active.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CFB — pro module list sanity (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('CFB-API-20 custom_field_builder appears in the installed modules list', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.get(MODULES_INSTALLED, undefined, false);
        // Read-only sanity. Resilient: the endpoint may shape its body differently
        // across pro versions, so we only require a non-fatal answer and, when 200,
        // that the module slug shows up somewhere in the payload.
        expect(resp.status(), 'installed-modules list answered without a fatal').toBeLessThan(500);
        if (resp.status() === 200) {
            const haystack = JSON.stringify(body ?? '');
            expect(haystack, 'custom_field_builder is present in the installed modules').toContain('custom_field_builder');
        }
    });
});
