import { test, expect, type Page, type APIRequestContext } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';
import { data } from '@utils/testData';

/**
 * PRO — CRM Integrations provider connect / settings-save LIFECYCLE, driven
 * through the ONE real save path every provider settings form uses:
 *
 *   admin-ajax  action=erp-settings-save  module=erp-integration  section=<key>
 *
 * Handler chain (free side, reached by all pro integrations):
 *   wp-erp/includes/Settings/Ajax.php::erp_settings_save()
 *     -> verify_nonce('erp-settings-nonce')         (field: _wpnonce)
 *     -> current_user_can('manage_options')         (NOT relaxed to crm/hr/ac mgr)
 *     -> (new WeDevs\ERP\Settings\Integration())->save($section)   (Integration.php:53)
 *        -> matches strtolower($integration_key) == $section
 *        -> update_option( $integration->get_option_id(), <fields> )
 *
 * Persisted to wp_options under `erp_integration_settings_<integration->id>`:
 *   - Help Scout : erp_integration_settings_helpscout
 *   - Zendesk    : erp_integration_settings_zendesk
 *   - Mailchimp  : erp_integration_settings_mailchimp-integration
 *   - HubSpot    : erp_integration_settings_hubspot-integration
 *
 * Provider section keys (erp_integration_classes filter, lowercase):
 *   mailchimp, hubspot, salesforce, helpscout, zendesk.
 *
 * LOCAL vs EXTERNAL (what this spec can exercise without third-party creds):
 *   - Help Scout & Zendesk register NO `<option_id>_filter` hook  => pure LOCAL
 *     persistence. Both save end-to-end (success:true) and the row is written.
 *   - Mailchimp & HubSpot register a `<option_id>_filter` that runs
 *     new Mailchimp/Hubspot($api_key)->is_connected() against the live API. A
 *     fake/empty key => is_connected()=false => WP_Error 'Invalid API key. Enter
 *     correct one!' => send_error, and NO option is written. This is the LOCAL
 *     validation/connect branch (the success/persist branch needs a real key and
 *     is skip-only below).
 *   - Salesforce settings form has NO savable fields (OAuth status block only);
 *     connect is an external OAuth redirect. No wp_options write via this path.
 *
 * SURFACE (per _pro-grounding.md surface-preference rule 3 — raw admin-ajax, the
 * only write surface this settings form has):
 *   - The save nonce `erp-settings-nonce` is localized as
 *     window.erp_settings_var.nonce on admin.php?page=erp-settings
 *     (wp-erp/includes/Settings/Assets.php). It is SESSION/USER bound, so a
 *     wp-cli-generated nonce will NOT match the storageState cookie session
 *     (live-confirmed: wp-cli nonce => 'Nonce verification failed'; scraped page
 *     nonce => success). We therefore boot that page with the admin storageState
 *     and scrape erp_settings_var.nonce before POSTing.
 *   - The handler always answers HTTP 200 with {success, data}; success returns
 *     data.message, errors return a plain string in data. Assertions branch on
 *     body.success, never on HTTP status (resilient-assertion philosophy) — the
 *     ONE exception is the documented unknown-module 500 fatal (BUG-INTG-01).
 *
 * This file mutates shared wp_options singletons
 * (erp_integration_settings_helpscout / _zendesk) and re-saves them across its
 * tests, so it is configured `serial`.
 *
 * Every test carries a tier tag (@pro), the @crm module tag and a role tag.
 */

test.describe.configure({ mode: 'serial' });

// ── Pro option names (wp_options) as string literals ───────────────────────────
const OPT = {
    helpscout: 'erp_integration_settings_helpscout',
    zendesk: 'erp_integration_settings_zendesk',
    mailchimp: 'erp_integration_settings_mailchimp-integration',
    hubspot: 'erp_integration_settings_hubspot-integration',
} as const;

// ── Endpoints ──────────────────────────────────────────────────────────────────
const AJAX_URL = toPath('wp-admin/admin-ajax.php');
const SETTINGS_PAGE = toPath('wp-admin/admin.php?page=erp-settings');

// A PHP fatal renders this exact string wherever a handler echoes HTML.
const CRITICAL_ERROR = 'There has been a critical error on this website';
// The error message both Mailchimp & HubSpot _filter hooks return for a bad key.
const INVALID_KEY_MSG = 'Invalid API key. Enter correct one!';

// Unique data per run so created rows are findable and the saved values are
// unambiguously ours. Standard epoch-millis suffix.
const RUN = Date.now();

// Shared lifecycle state, threaded across the serial tests.
let nonce = '';
// Captured prior option blobs so afterAll can restore (not just delete) shared state.
const priorOptions: Record<string, unknown> = {};

// ── admin-ajax helper (local — no shared-util edits) ───────────────────────────
type AjaxEnvelope = { success: boolean; data: unknown };

/**
 * POST an admin-ajax form using `request` (the admin storageState cookies ride
 * along, and the handler needs BOTH the cookie session AND the erp-settings
 * nonce). Returns the HTTP status and parsed JSON envelope. Success handlers
 * answer HTTP 200, so callers branch on `success`; a non-JSON body (PHP fatal)
 * is preserved as a string in data so the "not a fatal" assertion can surface it.
 */
async function settingsSave(
    request: APIRequestContext,
    form: Record<string, string>,
): Promise<{ status: number; body: AjaxEnvelope; raw: string }> {
    const resp = await request.post(AJAX_URL, {
        form: { action: 'erp-settings-save', ...form },
        headers: { 'content-type': 'application/x-www-form-urlencoded' },
    });
    const status = resp.status();
    const raw = await resp.text();
    let body: AjaxEnvelope = { success: false, data: raw };
    try {
        body = JSON.parse(raw) as AjaxEnvelope;
    } catch {
        // Non-JSON (e.g. a PHP fatal) — keep raw text in data for the fatal assert.
    }
    return { status, body, raw };
}

/** Pull the human message out of either envelope shape (string or {message}). */
function envelopeMessage(body: AjaxEnvelope): string {
    if (typeof body.data === 'string') return body.data;
    if (body.data && typeof body.data === 'object') {
        const d = body.data as Record<string, unknown>;
        return String(d.message ?? d.msg ?? '');
    }
    return '';
}

/** Assert an admin-ajax response is HTTP 200 and not a PHP fatal (resilient gate). */
function expectNotFatal(res: { status: number; raw: string }): void {
    expect(res.status, 'erp-settings-save answers HTTP 200 (no fatal)').toBe(200);
    expect(res.raw).not.toContain(CRITICAL_ERROR);
}

// ── Setup: boot the settings page once to scrape the page-localized nonce ───────
test.use({ storageState: data.auth.adminFile });

test.beforeAll(async ({ browser }) => {
    const context = await browser.newContext({ storageState: data.auth.adminFile });
    const page: Page = await context.newPage();
    try {
        await page.goto(SETTINGS_PAGE, { waitUntil: 'domcontentloaded' });
        // The settings page must not render a PHP fatal, and the nonce must exist.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // Read window.erp_settings_var.nonce (localized inline). Fall back to a
        // regex over raw HTML if the global is not yet on window.
        nonce = await page.evaluate(() => {
            const g = (window as unknown as { erp_settings_var?: { nonce?: string } }).erp_settings_var;
            return g && typeof g.nonce === 'string' ? g.nonce : '';
        });
        if (!nonce) {
            const html = await page.content();
            const m = html.match(/erp_settings_var\s*=\s*\{[\s\S]*?"nonce"\s*:\s*"([a-f0-9]+)"/i);
            nonce = m?.[1] ?? '';
        }
    } finally {
        await page.close();
        await context.close();
    }
    expect(nonce, 'scraped erp_settings_var.nonce (action "erp-settings-nonce")').toMatch(/^[a-f0-9]{8,}$/);

    // Capture prior values of the shared option rows we overwrite, so afterAll
    // can restore them instead of just deleting (other suites may rely on them).
    priorOptions[OPT.helpscout] = await dbUtils.getOptionValue(OPT.helpscout);
    priorOptions[OPT.zendesk] = await dbUtils.getOptionValue(OPT.zendesk);
});

test.afterAll(async () => {
    // Restore the shared option rows to their pre-run state (delete if they were
    // absent before, otherwise write the captured blob back). Mailchimp/HubSpot
    // are never written by these tests (validation short-circuits), so nothing
    // to clean there.
    try {
        for (const name of [OPT.helpscout, OPT.zendesk] as const) {
            const prior = priorOptions[name];
            if (prior === undefined) {
                await dbUtils.dbQuery('DELETE FROM wp_options WHERE option_name = ?', [name]);
            } else {
                await dbUtils.setOptionValue(name, prior);
            }
        }
    } finally {
        await dbUtils.close();
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// Help Scout — pure LOCAL persistence (no _filter hook, no external call).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations — Help Scout connect (pro, admin)', () => {
    // INTG-CON-01 — save app_id/app_secret/callback_uri persists to wp_options.
    test('Help Scout: save settings persists to wp_options[helpscout]', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const appId = `appid_${RUN}`;
        const appSecret = `secret_${RUN}`;
        const callback = 'http://localhost:9999/erp-helpscout/api';

        const res = await settingsSave(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'helpscout',
            helpscout_app_id: appId,
            helpscout_app_secret: appSecret,
            helpscout_callback_uri: callback,
        });

        expectNotFatal(res);
        expect(res.body.success, `Help Scout save succeeded (msg="${envelopeMessage(res.body)}")`).toBe(true);
        expect(envelopeMessage(res.body)).toMatch(/Settings Saved Successfully/i);

        // DB: the serialized option blob holds exactly our three unique values.
        const opt = await dbUtils.getOptionValue<Record<string, string>>(OPT.helpscout);
        expect(opt, 'erp_integration_settings_helpscout row exists').toBeTruthy();
        expect(opt?.helpscout_app_id).toBe(appId);
        expect(opt?.helpscout_app_secret).toBe(appSecret);
        expect(opt?.helpscout_callback_uri).toBe(callback);
    });

    // INTG-CON-02 — re-save with NEW unique values overwrites the prior option.
    test('Help Scout: re-save overwrites the prior option (idempotent update)', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const appId = `appid_${RUN}_v2`;
        const appSecret = `secret_${RUN}_v2`;
        const callback = `http://localhost:9999/erp-helpscout/api?v=${RUN}`;

        const res = await settingsSave(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'helpscout',
            helpscout_app_id: appId,
            helpscout_app_secret: appSecret,
            helpscout_callback_uri: callback,
        });

        expectNotFatal(res);
        expect(res.body.success, `Help Scout re-save succeeded (msg="${envelopeMessage(res.body)}")`).toBe(true);

        // The single option row now reflects the v2 values (overwrite, not append).
        const opt = await dbUtils.getOptionValue<Record<string, string>>(OPT.helpscout);
        expect(opt?.helpscout_app_id).toBe(appId);
        expect(opt?.helpscout_app_secret).toBe(appSecret);
        expect(opt?.helpscout_callback_uri).toBe(callback);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Zendesk — pure LOCAL persistence (no _filter hook, no external call).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations — Zendesk connect (pro, admin)', () => {
    // INTG-CON-03 — save subdomain/login_email/password persists all three.
    test('Zendesk: save settings persists all three fields to wp_options[zendesk]', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const subdomain = `mysub${RUN}.zendesk.com`;
        const email = `qa${RUN}@example.com`;
        const password = `pass_${RUN}`;

        const res = await settingsSave(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'zendesk',
            zendesk_subdomain: subdomain,
            zendesk_login_email: email,
            zendesk_password: password,
        });

        expectNotFatal(res);
        expect(res.body.success, `Zendesk save succeeded (msg="${envelopeMessage(res.body)}")`).toBe(true);
        expect(envelopeMessage(res.body)).toMatch(/Settings Saved Successfully/i);

        const opt = await dbUtils.getOptionValue<Record<string, string>>(OPT.zendesk);
        expect(opt, 'erp_integration_settings_zendesk row exists').toBeTruthy();
        expect(opt?.zendesk_subdomain).toBe(subdomain);
        expect(opt?.zendesk_login_email).toBe(email);
        expect(opt?.zendesk_password).toBe(password);
    });

    // INTG-CON-04 — a malformed email still persists: Zendesk has no _filter
    // validation, so the save layer is lenient. Document that behavior (edge),
    // do not assert a rejection that the code does not perform.
    test('Zendesk: malformed email still persists (no _filter validation — lenient)', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const subdomain = `lenient${RUN}.zendesk.com`;
        const malformed = `not-an-email-${RUN}`;
        const password = `pass_${RUN}_x`;

        const res = await settingsSave(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'zendesk',
            zendesk_subdomain: subdomain,
            zendesk_login_email: malformed,
            zendesk_password: password,
        });

        expectNotFatal(res);
        // No validation hook => save succeeds despite the bad email.
        expect(res.body.success, `Zendesk lenient save succeeded (msg="${envelopeMessage(res.body)}")`).toBe(true);

        const opt = await dbUtils.getOptionValue<Record<string, string>>(OPT.zendesk);
        // The malformed value is persisted as-is (lenient local behavior).
        expect(opt?.zendesk_login_email).toBe(malformed);
        expect(opt?.zendesk_subdomain).toBe(subdomain);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Mailchimp — LOCAL validation branch (the _filter rejects a bad/empty key and
// writes NO option). The valid-key success/persist branch needs a real key.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations — Mailchimp connect validation (pro, admin)', () => {
    // INTG-CON-05 — invalid api_key => 'Invalid API key. Enter correct one!' and
    // NO option is written (WP_Error short-circuits before update_option).
    test('Mailchimp: invalid api_key returns the validation error and writes NO option', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const before = await dbUtils.getOptionValue(OPT.mailchimp);

        const res = await settingsSave(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'mailchimp',
            api_key: `fakekey_${RUN}-us1`,
        });

        expectNotFatal(res);
        expect(res.body.success, 'Mailchimp invalid key is rejected (success:false)').toBe(false);
        expect(envelopeMessage(res.body)).toContain(INVALID_KEY_MSG);

        // DB: the option is not newly created (and not mutated if it pre-existed).
        const after = await dbUtils.getOptionValue(OPT.mailchimp);
        expect(after).toEqual(before);
    });

    // INTG-CON-06 — an EMPTY (but posted) api_key still hits is_connected('') =>
    // false => same validation error. The isset() early-return does NOT fire
    // because the empty field IS posted.
    test('Mailchimp: empty api_key still hits validation (Invalid API key)', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const before = await dbUtils.getOptionValue(OPT.mailchimp);

        const res = await settingsSave(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'mailchimp',
            api_key: '',
        });

        expectNotFatal(res);
        expect(res.body.success, 'Mailchimp empty key is rejected (success:false)').toBe(false);
        expect(envelopeMessage(res.body)).toContain(INVALID_KEY_MSG);

        const after = await dbUtils.getOptionValue(OPT.mailchimp);
        expect(after).toEqual(before);
    });

    // INTG-CON-07 — saving a VALID Mailchimp key (success/persist branch) needs a
    // real external account. Skip-only — its local validation IS covered above.
    test('Mailchimp: valid api_key persists (success branch)', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        test.skip(true, 'needs external Mailchimp credentials (is_connected() calls the live API)');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// HubSpot — LOCAL validation branch (mirror of Mailchimp).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations — HubSpot connect validation (pro, admin)', () => {
    // INTG-CON-08 — invalid api_key => 'Invalid API key. Enter correct one!' and
    // NO option is written.
    test('HubSpot: invalid api_key returns the validation error and writes NO option', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const before = await dbUtils.getOptionValue(OPT.hubspot);

        const res = await settingsSave(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'hubspot',
            api_key: `fakehubspot_${RUN}`,
        });

        expectNotFatal(res);
        expect(res.body.success, 'HubSpot invalid key is rejected (success:false)').toBe(false);
        expect(envelopeMessage(res.body)).toContain(INVALID_KEY_MSG);

        const after = await dbUtils.getOptionValue(OPT.hubspot);
        expect(after).toEqual(before);
    });

    // INTG-CON-09 — saving a VALID HubSpot key needs a real external account.
    test('HubSpot: valid api_key persists (success branch)', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        test.skip(true, 'needs external HubSpot credentials (is_connected() calls the live API)');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Salesforce — no savable settings form (OAuth status block + external redirect);
// no wp_options write occurs via erp-settings-save. UI-smoke / skip only.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations — Salesforce connect (pro, admin)', () => {
    // INTG-CON-10 — the Salesforce settings section renders without a fatal; its
    // connect is an external OAuth redirect, so there is no local save to assert.
    test('Salesforce: settings section renders (no local save — OAuth redirect)', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        await page.goto(toPath('wp-admin/admin.php?page=erp-crm&section=integration&sub-section=salesforce'), { waitUntil: 'domcontentloaded' });
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator('#wpbody-content')).toBeVisible();
        await expect(page.locator('div.wrap h2').first()).toContainText(/Integrations/i);
    });

    // INTG-CON-11 — the real external OAuth connect (login.salesforce.com /
    // api.wperp.com) cannot run in CI.
    test('Salesforce: complete external OAuth connect', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        test.skip(true, 'needs external Salesforce credentials (OAuth redirect to login.salesforce.com / api.wperp.com)');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — the save endpoint's nonce gate.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations — save access control (pro, admin)', () => {
    // INTG-CON-12 — a save POST WITHOUT _wpnonce is rejected at verify_nonce, and
    // no option is written. Assert the boundary (rejected), not an exact code —
    // the handler answers 200 with success:false here.
    test('save without _wpnonce is rejected (nonce verification) and writes nothing', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const before = await dbUtils.getOptionValue<Record<string, string>>(OPT.zendesk);

        const res = await settingsSave(request, {
            // intentionally NO _wpnonce
            module: 'erp-integration',
            section: 'zendesk',
            zendesk_subdomain: `nononce${RUN}.zendesk.com`,
        });

        expectNotFatal(res);
        expect(res.body.success, 'missing nonce is rejected (success:false)').toBe(false);
        expect(envelopeMessage(res.body)).toMatch(/Nonce verification failed/i);

        // The bad subdomain must NOT have been persisted.
        const after = await dbUtils.getOptionValue<Record<string, string>>(OPT.zendesk);
        expect(after?.zendesk_subdomain ?? '').not.toBe(`nononce${RUN}.zendesk.com`);
        expect(after).toEqual(before);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// KNOWN BUG — BUG-INTG-01: erp-settings-save fatals (HTTP 500) on an unknown
// `module` value. Documented with an explicit 500/fatal assertion (the
// resilient-philosophy exception for a known, logged defect). The provider tests
// above always send module=erp-integration and are unaffected.
//   Defect: wp-erp/includes/Settings/Ajax.php:97 — the default switch branch sets
//   $settings = apply_filters("erp_settings_save_{$module}_section", $module, ...)
//   which returns the string $module (no handler), then $settings->save() fatals:
//   'Uncaught Error: Call to a member function save() on string'.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Integrations — known bug: unknown module fatal (pro, admin)', () => {
    // INTG-CON-13 — documents BUG-INTG-01. This is the ONE assertion that pins an
    // exact 500 (a known logged bug), per the resilient-assertion exception.
    test('BUG-INTG-01: unknown module value fatals the save endpoint (HTTP 500)', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const res = await settingsSave(request, {
            _wpnonce: nonce,
            module: `not-a-real-module-${RUN}`,
            section: 'mailchimp',
        });
        // KNOWN BUG: a valid nonce + unknown module => save() on string => fatal.
        // Documented as an explicit 500 (resilient-philosophy exception). If this
        // ever returns 200 the free-side bug was fixed — update this assertion.
        expect(res.status, 'BUG-INTG-01: unknown module 500 fatal (Ajax.php:97 save() on string)').toBe(500);
    });
});
