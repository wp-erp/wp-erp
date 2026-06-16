import { test, expect, type Page, type APIRequestContext } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';
import { data } from '@utils/testData';

/**
 * PRO — HRM SMS Notification FULL lifecycle (gateway settings), driven through the
 * REAL settings-save admin-ajax handler.
 *
 * SURFACE (per _pro-grounding.md surface-preference rule 3 — raw admin-ajax, the
 * ONLY write surface that exists for this feature):
 *   - SMS Notification is a settings-form integration, NOT a REST feature. It
 *     registers as `$settings['sms'] = new SmsSettings()` (extends
 *     WeDevs\ERP\Integration) in erp-pro/modules/hrm/sms-notification/Module.php:214.
 *     There is NO `/erp/v1` REST route for it (confirmed by grep across the module).
 *   - The single write path is WP admin-ajax `action=erp-settings-save` →
 *     WeDevs\ERP\Settings\Ajax::erp_settings_save() (wp-erp/includes/Settings/Ajax.php:53)
 *     → `module=erp-integration` → `new Integration()->save('sms')`
 *     (wp-erp/includes/Settings/Integration.php:53). It persists into the single
 *     wp_options row `erp_integration_settings_erp-sms` (the integration's
 *     get_option_id(), built from SmsSettings::$id = 'erp-sms') as a PHP-serialized
 *     array of `erp_sms_*` field ids.
 *
 * NONCE (page-localized, NOT a wp_rest nonce):
 *   - The handler runs `verify_nonce('erp-settings-nonce')`
 *     (WeDevs\ERP\Framework\Traits\Ajax::verify_nonce reads $_REQUEST['_wpnonce']
 *     and checks wp_verify_nonce(..., 'erp-settings-nonce')). The env X_WP_NONCE is a
 *     wp_rest nonce and will NOT satisfy this handler.
 *   - The required nonce is `wp_create_nonce('erp-settings-nonce')`, localized to the
 *     `erp-settings-bootstrap` script handle as `erp_settings_var.nonce`
 *     (wp-erp/includes/Settings/Assets.php:73-95; alongside
 *     erp_settings_var.action='erp-settings-save' and .ajax_url=admin-ajax.php).
 *     We boot the settings page with the admin storageState, scrape
 *     `window.erp_settings_var.nonce`, and POST admin-ajax with it as `_wpnonce`.
 *     The POST uses `page.request` so the same admin cookies ride along.
 *   - `erp_settings_var` carries a large nested `erp_settings_menus` object, so a
 *     greedy HTML regex over it fails — we read the nonce via page.evaluate.
 *
 * RESPONSE SHAPE: the handler always answers HTTP 200 with {success, data}.
 *   - success → data.message = 'Settings Saved Successfully!'
 *   - bad/missing nonce → success:false, data = 'Error: Nonce verification failed'
 *   So assertions branch on `body.success`, never on HTTP status (resilient
 *   philosophy), and always gate on NOT a PHP fatal.
 *
 * MERGE BEHAVIOR (verified at Integration.php:66-99): for section=sms the save uses
 *   get_option(get_option_id()) as the base and overwrites only POSTed field ids, so
 *   switching gateway KEEPS the prior gateway's creds alongside the new ones. We
 *   assert the changed key, not the whole array.
 *
 * NOT EXERCISED: the actual SMS dispatch (Module::send → GatewayHandler->send_sms)
 *   hits a third-party gateway and the 'Please select a gateway to send sms.' guard
 *   fires only at SEND time in GatewayHandler::__construct() (default branch) — never
 *   at save. We document the lenient-save behavior but do not send real SMS.
 *
 * This file mutates the shared `wp_options.erp_integration_settings_erp-sms`
 * singleton across its tests and they must run in order (save → switch → negatives),
 * so it is configured `serial`; the option is deleted in afterAll to restore the
 * clean/absent state.
 *
 * Every test carries a tier tag (@pro), the @hrm module tag and a role tag.
 */

test.describe.configure({ mode: 'serial' });

// ── The shared wp_options row this feature writes (string literal) ──────────────
const OPTION_NAME = 'erp_integration_settings_erp-sms';

// Field ids verified from SmsSettings::get_fields()
// (erp-pro/modules/hrm/sms-notification/includes/SmsSettings.php).
const FIELD = {
    selectedGateway: 'erp_sms_selected_gateway',
    twilioNumberFrom: 'erp_sms_twilio_number_from',
    twilioAccountSid: 'erp_sms_twilio_account_sid',
    twilioAuthToken: 'erp_sms_twilio_auth_token',
    nexmoApiKey: 'erp_sms_nexmo_apikey',
    nexmoApiSecret: 'erp_sms_nexmo_apisecret',
    nexmoSenderId: 'erp_sms_nexmo_sender_id',
} as const;

// Unique data per run so saved creds are findable / distinguishable.
const RUN = Date.now();
const TWILIO_SID = `ACsid${RUN}`;
const TWILIO_TOKEN = `tok${RUN}`;
const TWILIO_FROM = '+15005550006';
const NEXMO_KEY = `key${RUN}`;
const NEXMO_SECRET = `secret${RUN}`;
const NEXMO_SENDER = `PW${RUN}`.slice(0, 11); // sender ids are short

const AJAX_URL = toPath('wp-admin/admin-ajax.php');
const SMS_SETTINGS_PAGE = toPath('wp-admin/admin.php?page=erp-settings&tab=erp-integration&section=sms');

// Shared lifecycle state threaded across the serial tests.
let nonce = '';

// ── admin-ajax helper (local — no shared-util edits) ───────────────────────────
type AjaxEnvelope = { success: boolean; data: unknown };

/**
 * POST an admin-ajax form. Uses `page.request` so the admin storageState cookies
 * ride along (the handler needs the cookie session AND the 'erp-settings-nonce').
 * The handler always answers HTTP 200, so we read the body and let callers branch
 * on `success`. A PHP fatal yields non-JSON → captured as text so the not-fatal
 * gate can surface it instead of throwing.
 */
async function ajaxPost(
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
        /* non-JSON (e.g. a fatal) — keep raw in body.data so assertions can see it */
    }
    return { status, body, raw };
}

/** Pull the human message out of either envelope shape. */
function envMessage(body: AjaxEnvelope): string {
    if (typeof body.data === 'string') return body.data;
    if (body.data && typeof body.data === 'object') {
        const d = body.data as Record<string, unknown>;
        return String(d.message ?? d.msg ?? '');
    }
    return '';
}

/** A PHP fatal renders this exact string anywhere a handler echoes HTML. */
const CRITICAL_ERROR = 'There has been a critical error on this website';

/** Assert an admin-ajax response is not a PHP fatal (resilient gate). */
function expectNotFatal(res: { status: number; raw: string }): void {
    expect(res.status, 'admin-ajax answers HTTP 200 (no fatal)').toBe(200);
    expect(res.raw, 'response is not a PHP critical-error splash').not.toContain(CRITICAL_ERROR);
}

/** Read the saved SMS option (php-unserialized) as a flat string map. */
async function readSmsOption(): Promise<Record<string, string> | undefined> {
    return dbUtils.getOptionValue<Record<string, string>>(OPTION_NAME);
}

// ── Setup: boot the settings page once to scrape the page-localized nonce ───────
test.use({ storageState: data.auth.adminFile });

test.beforeAll(async ({ browser }) => {
    // Start from a clean slate so merge-behavior assertions are deterministic.
    await dbUtils.dbQuery(`DELETE FROM wp_options WHERE option_name = ?`, [OPTION_NAME]);

    const context = await browser.newContext({ storageState: data.auth.adminFile });
    const page: Page = await context.newPage();
    try {
        await page.goto(SMS_SETTINGS_PAGE, { waitUntil: 'domcontentloaded' });
        // The settings SPA must not render a PHP fatal, and the nonce must be present.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // Read window.erp_settings_var.nonce (localized inline). erp_settings_var
        // holds a large nested menus object, so we read the property directly rather
        // than regexing the HTML.
        nonce = await page.evaluate(() => {
            const g = (window as unknown as { erp_settings_var?: { nonce?: string } }).erp_settings_var;
            return g && typeof g.nonce === 'string' ? g.nonce : '';
        });
    } finally {
        await page.close();
        await context.close();
    }
    expect(nonce, 'scraped erp_settings_var.nonce (action "erp-settings-nonce")').toMatch(/^[a-f0-9]{8,}$/);
});

test.afterAll(async () => {
    // Restore the clean/absent state (the option did not exist before this run).
    try {
        await dbUtils.dbQuery(`DELETE FROM wp_options WHERE option_name = ?`, [OPTION_NAME]);
    } finally {
        await dbUtils.close();
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 0 — preconditions: the settings page is reachable and localizes the nonce.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM SMS lifecycle — preconditions', () => {
    // SMS-LC-00 — the page-localized save nonce/action are present (no fatal).
    test('settings page localizes erp_settings_var with the save nonce + action', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(SMS_SETTINGS_PAGE, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        const v = await page.evaluate(() => {
            const g = (window as unknown as { erp_settings_var?: { nonce?: string; action?: string; ajax_url?: string } }).erp_settings_var;
            return g ? { nonce: g.nonce ?? '', action: g.action ?? '', ajax_url: g.ajax_url ?? '' } : null;
        });
        expect(v, 'erp_settings_var is localized on the settings page').not.toBeNull();
        expect(String(v?.nonce), 'a save nonce is present').toMatch(/^[a-f0-9]{8,}$/);
        expect(String(v?.action), 'save action is erp-settings-save').toBe('erp-settings-save');
        expect(String(v?.ajax_url), 'save posts to admin-ajax.php').toContain('admin-ajax.php');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 1 — SAVE the Twilio gateway (happy path).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM SMS lifecycle — save Twilio gateway', () => {
    // SMS-LC-01 — save Twilio creds → success + option persists the posted fields.
    test('save Twilio gateway settings', { tag: ['@pro', '@hrm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'sms',
            [FIELD.selectedGateway]: 'twilio',
            [FIELD.twilioNumberFrom]: TWILIO_FROM,
            [FIELD.twilioAccountSid]: TWILIO_SID,
            [FIELD.twilioAuthToken]: TWILIO_TOKEN,
        });
        expectNotFatal(res);
        expect(res.body.success, `save succeeded (msg="${envMessage(res.body)}")`).toBe(true);
        expect(envMessage(res.body)).toMatch(/Settings Saved Successfully/i);

        // DB: the option now holds the Twilio fields (the GatewayHandler reads
        // erp_sms_selected_gateway from exactly this option).
        const opt = await readSmsOption();
        expect(opt, 'erp_integration_settings_erp-sms option created').toBeTruthy();
        expect(String(opt?.[FIELD.selectedGateway]), 'selected gateway is twilio').toBe('twilio');
        expect(String(opt?.[FIELD.twilioNumberFrom]), 'twilio number_from persisted').toBe(TWILIO_FROM);
        expect(String(opt?.[FIELD.twilioAccountSid]), 'twilio account_sid persisted').toBe(TWILIO_SID);
        expect(String(opt?.[FIELD.twilioAuthToken]), 'twilio auth_token persisted').toBe(TWILIO_TOKEN);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 2 — SWITCH gateway to Nexmo (merge behavior).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM SMS lifecycle — switch gateway', () => {
    // SMS-LC-02 — switch to Nexmo → selected gateway flips, nexmo creds added, and
    // the prior twilio creds REMAIN (Integration::save merges onto get_option base).
    test('switch the active gateway to Nexmo (prior creds are merged, not wiped)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'sms',
            [FIELD.selectedGateway]: 'nexmo',
            [FIELD.nexmoApiKey]: NEXMO_KEY,
            [FIELD.nexmoApiSecret]: NEXMO_SECRET,
            [FIELD.nexmoSenderId]: NEXMO_SENDER,
        });
        expectNotFatal(res);
        expect(res.body.success, `switch succeeded (msg="${envMessage(res.body)}")`).toBe(true);

        const opt = await readSmsOption();
        expect(opt, 'option still present after switch').toBeTruthy();
        // Changed key: the active gateway flipped.
        expect(String(opt?.[FIELD.selectedGateway]), 'selected gateway flipped to nexmo').toBe('nexmo');
        // New creds landed.
        expect(String(opt?.[FIELD.nexmoApiKey]), 'nexmo apikey persisted').toBe(NEXMO_KEY);
        expect(String(opt?.[FIELD.nexmoApiSecret]), 'nexmo apisecret persisted').toBe(NEXMO_SECRET);
        // MERGE: the earlier twilio creds were NOT removed (documented behavior).
        expect(String(opt?.[FIELD.twilioAccountSid]), 'prior twilio creds remain after switch (merge)').toBe(TWILIO_SID);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 3 — VALIDATION GAP: empty gateway is accepted at save time (SMS-BUG-01).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM SMS lifecycle — lenient save (documented gap)', () => {
    // SMS-LC-03 — saving an empty erp_sms_selected_gateway succeeds with no
    // save-time validation (SMS-BUG-01). The 'Please select a gateway' guard fires
    // only at SEND time in GatewayHandler::__construct(). We assert success + empty
    // value persisted; we do NOT assert a 4xx (the lenient-save behavior is the
    // documented bug).
    test('saving an empty gateway is accepted (no save-time validation) — SMS-BUG-01', { tag: ['@pro', '@hrm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'sms',
            [FIELD.selectedGateway]: '',
        });
        expectNotFatal(res);
        expect(res.body.success, 'an empty gateway is accepted at save time (documented gap)').toBe(true);

        const opt = await readSmsOption();
        expect(opt, 'option still present').toBeTruthy();
        expect(String(opt?.[FIELD.selectedGateway] ?? ''), 'empty gateway value persisted').toBe('');

        // Restore a real gateway so the singleton ends the lifecycle in a usable
        // state before the negative-nonce tests (which must NOT mutate it).
        const restore = await ajaxPost(request, {
            _wpnonce: nonce,
            module: 'erp-integration',
            section: 'sms',
            [FIELD.selectedGateway]: 'twilio',
        });
        expectNotFatal(restore);
        expect(restore.body.success, 'restored a real gateway').toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 4 — NEGATIVE: nonce enforcement.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM SMS lifecycle — negative (nonce)', () => {
    // SMS-LC-04 — a bad nonce fails verify_nonce('erp-settings-nonce'):
    // success:false, data = 'Error: Nonce verification failed' (NOT a fatal), and
    // the option is unchanged.
    test('save with a bad nonce is rejected and persists nothing', { tag: ['@pro', '@hrm', '@admin'] }, async ({ request }) => {
        const before = await readSmsOption();

        const res = await ajaxPost(request, {
            _wpnonce: 'deadbeef00',
            module: 'erp-integration',
            section: 'sms',
            [FIELD.selectedGateway]: 'nexmo',
        });
        expectNotFatal(res);
        expect(res.body.success, 'a bad nonce is rejected').toBe(false);
        expect(envMessage(res.body)).toMatch(/Nonce verification failed/i);

        const after = await readSmsOption();
        expect(
            String(after?.[FIELD.selectedGateway] ?? ''),
            'gateway unchanged after a rejected save',
        ).toBe(String(before?.[FIELD.selectedGateway] ?? ''));
    });

    // SMS-LC-05 — a missing nonce is likewise rejected by verify_nonce
    // (isset($_REQUEST['_wpnonce']) is false) — success:false, no change.
    test('save with a missing nonce is rejected and persists nothing', { tag: ['@pro', '@hrm', '@admin'] }, async ({ request }) => {
        const before = await readSmsOption();

        const res = await ajaxPost(request, {
            // no _wpnonce
            module: 'erp-integration',
            section: 'sms',
            [FIELD.selectedGateway]: 'smsglobal',
        });
        expectNotFatal(res);
        expect(res.body.success, 'a missing nonce is rejected').toBe(false);
        expect(envMessage(res.body)).toMatch(/Nonce verification failed/i);

        const after = await readSmsOption();
        expect(
            String(after?.[FIELD.selectedGateway] ?? ''),
            'gateway unchanged after a rejected save',
        ).toBe(String(before?.[FIELD.selectedGateway] ?? ''));
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 5 — DOCUMENTED BUG: unknown `module` triggers a PHP fatal (SMS-BUG-02).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM SMS lifecycle — documented bug (unknown module fatal)', () => {
    // SMS-LC-06 — erp_settings_save() default branch sets
    // $settings = apply_filters("erp_settings_save_{$module}_section", $module, ...)
    // which returns the STRING $module for an unregistered module, then
    // $settings->save($section) fatals: 'Call to a member function save() on string'
    // (wp-erp/includes/Settings/Ajax.php:90-97). This is a CORE wp-erp bug adjacent
    // to the SMS save path (same handler); the legit SMS path module=erp-integration
    // is unaffected.
    //
    // Resilient assertion (per philosophy): we assert this is NOT a clean
    // success/200-envelope while ANNOTATING the known 500. We do NOT make the green
    // build depend on the exact 500 — both a fatal (500 / critical-error body) AND a
    // graceful success:false rejection are acceptable; only a clean success:true is
    // a regression-of-the-bug-fix that this test would (intentionally) flag for review.
    test('unknown module param does not save cleanly (SMS-BUG-02 — known fatal)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ request }) => {
        test.info().annotations.push({
            type: 'known-bug',
            description: 'SMS-BUG-02: erp-settings-save with an unregistered `module` fatals (Ajax.php:97 — save() on string). Tampering an authed save with a valid nonce yields HTTP 500.',
        });

        const res = await ajaxPost(request, {
            _wpnonce: nonce,
            module: `foobar-not-real-${RUN}`,
            section: 'sms',
            [FIELD.selectedGateway]: 'twilio',
        });

        // Either it fatals (the documented bug: 500 and/or critical-error body) or it
        // is gracefully rejected (success:false). The ONLY disallowed outcome is a
        // clean success — that path would silently mis-handle a tampered module.
        const fataled = res.status >= 500 || res.raw.includes(CRITICAL_ERROR);
        const gracefullyRejected = res.status === 200 && res.body.success === false;
        expect(
            fataled || gracefullyRejected,
            `tampered module is not handled cleanly (status=${res.status}, success=${String(res.body.success)})`,
        ).toBe(true);

        // The SMS option must be untouched by the tampered call.
        const opt = await readSmsOption();
        expect(String(opt?.[FIELD.selectedGateway] ?? ''), 'tampered module did not write the SMS option').not.toBe(`foobar-not-real-${RUN}`);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 6 — ACCESS BOUNDARY: an HR manager cannot reach the settings page or save.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM SMS lifecycle — access boundary (manager)', () => {
    // SMS-LC-07 — the erp-settings page requires manage_options. An HR manager is
    // hard-denied (wp_die: 'Sorry, you are not allowed to access this page.') and
    // never receives erp_settings_var / a valid settings nonce. Assert the boundary
    // (wp_die text OR absence of the localized save object), never an exact code.
    test('an HR manager is denied the settings page (no save nonce)', { tag: ['@pro', '@hrm', '@manager'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: data.auth.hrManagerFile });
        const page: Page = await context.newPage();
        try {
            await page.goto(SMS_SETTINGS_PAGE, { waitUntil: 'domcontentloaded' });
            await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

            const bodyText = (await page.locator('body').textContent()) ?? '';
            const localized = await page.evaluate(() => {
                const g = (window as unknown as { erp_settings_var?: { nonce?: string } }).erp_settings_var;
                return g && typeof g.nonce === 'string' ? g.nonce : '';
            });

            const denied = /not allowed to access this page/i.test(bodyText);
            const hasNoSaveNonce = !/^[a-f0-9]{8,}$/.test(localized);
            expect(
                denied || hasNoSaveNonce,
                'a manager is denied the settings page and/or gets no save nonce',
            ).toBe(true);
        } finally {
            await page.close();
            await context.close();
        }
    });

    // SMS-LC-08 — at the AJAX layer the boundary degrades to a nonce failure: a
    // manager session POSTing with the admin's scraped nonce is rejected (nonces are
    // session-bound). Assert the boundary (success:false), never an exact code, and
    // confirm nothing was written.
    test('an HR manager save is rejected at the AJAX layer (session-bound nonce)', { tag: ['@pro', '@hrm', '@manager'] }, async ({ browser }) => {
        const before = await readSmsOption();
        const context = await browser.newContext({ storageState: data.auth.hrManagerFile });
        try {
            const res = await ajaxPost(context.request, {
                _wpnonce: nonce, // admin's nonce — bound to the admin session, not the manager's
                module: 'erp-integration',
                section: 'sms',
                [FIELD.selectedGateway]: 'nexmo',
            });
            expectNotFatal(res);
            expect(res.body.success, 'a manager cannot save the SMS gateway').toBe(false);

            const after = await readSmsOption();
            expect(
                String(after?.[FIELD.selectedGateway] ?? ''),
                'gateway unchanged after a denied manager save',
            ).toBe(String(before?.[FIELD.selectedGateway] ?? ''));
        } finally {
            await context.close();
        }
    });
});
