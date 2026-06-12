import { test, expect, type Page, type APIRequestContext } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';
import { data } from '@utils/testData';

/**
 * PRO — CRM Deals FULL lifecycle, driven through the REAL admin-ajax handlers.
 *
 * The Deals module ships NO REST controller (`grep register_rest_route` across
 * erp-pro/modules/crm/deals returns nothing — also documented in the sibling
 * tests/api/crm/deals.db.api.spec.ts). Its write layer is WP admin-ajax
 * (action=erp_deals_*) + Eloquent models writing to `wp_erp_crm_deals*`.
 *
 * SURFACE (per _pro-grounding.md surface-preference rule 3 — raw admin-ajax, the
 * only write surface that exists for Deals):
 *   - Every write handler calls `verify_nonce('erp-deals')`
 *     (WeDevs\ERP\Framework\Traits\Ajax::verify_nonce reads $_REQUEST['_wpnonce']
 *     and checks wp_verify_nonce(..., 'erp-deals')). The REST X-WP-Nonce in .env
 *     does NOT authenticate these handlers.
 *   - The required nonce is `wp_create_nonce('erp-deals')`, localized as
 *     `erpDealsGlobal.nonce` and printed inline on the Deals SPA page
 *     (deals/includes/Admin.php L165/245). So we boot the SPA page with the admin
 *     storageState, scrape `window.erpDealsGlobal.nonce`, and POST admin-ajax with
 *     it as `_wpnonce`. The POST uses `page.request` so it carries the same admin
 *     cookies as the page.
 *   - All handlers respond HTTP 200 with a JSON envelope {success, data}; failures
 *     are `success:false` (NOT 4xx/5xx). So assertions branch on `body.success`,
 *     never on HTTP status (resilient-assertion philosophy). Validation failures
 *     put the message in data.msg; a bad nonce puts a plain string in data.
 *
 * SEED DATA (verified live, pipeline_id=1 'Pipeline'):
 *   stages by `order`: id=4 'Proposal Made'(0), id=1 'Lead In'(1),
 *   id=2 'Contact Made'(2), id=3 'Demo Scheduled'(3), id=5 'Negotiations Started'(4).
 *   activity types: 1=Call, 2=Meeting, 3=Task, 4=Deadline, 6=Email, 13=Lunch.
 *   lost_reasons table is EMPTY (no seed) → mark-lost uses free-text deal[lost_reason].
 *   CRM contact id=1 (type contact) exists and is the deal's contact.
 *
 * Pro tables are STRING LITERALS (the `tables` util only has free tables).
 * `order`,`in`,`out` are reserved words → back-ticked in SQL.
 *
 * This file mutates the shared `wp_erp_crm_deals*` singletons across its tests,
 * and they must run in order (create → move → activity → won/lost/reopen → trash),
 * so it is configured `serial`.
 *
 * Every test carries a tier tag (@pro), the @crm module tag and a role tag.
 */

test.describe.configure({ mode: 'serial' });

// ── Pro tables referenced as string literals ───────────────────────────────────
const T = {
    deals: 'wp_erp_crm_deals',
    stageHistory: 'wp_erp_crm_deals_stage_history',
    activities: 'wp_erp_crm_deals_activities',
    stages: 'wp_erp_crm_deals_pipeline_stages',
    pipelines: 'wp_erp_crm_deals_pipelines',
    activityTypes: 'wp_erp_crm_deals_activity_types',
} as const;

// Seed FK targets (verified live). Stage 4 = 'Proposal Made' (order 0, first
// stage); stage 5 = 'Negotiations Started' (order 4, last stage). Contact id=1
// exists as a CRM contact. Activity type 1 = 'Call'.
const FIRST_STAGE = 4;        // order 0
const TARGET_STAGE = 5;       // order 4 (last)
const CONTACT_ID = 1;
const OWNER_ID = 1;           // admin user; owner_id is MANDATORY for managers/admin
const ACTIVITY_TYPE_CALL = 1;

// Unique data per run so created rows are findable and cleanable.
const RUN = Date.now();
const DEAL_TITLE = `LC Deal ${RUN} RUN`;
const ACTIVITY_TITLE = `LC Activity ${RUN} RUN`;
const ACTIVITY_START = '2026-06-10 10:00:00';

const AJAX_URL = toPath('wp-admin/admin-ajax.php');
const DEALS_PAGE = toPath('wp-admin/admin.php?page=erp-crm&section=deals');

// Shared lifecycle state, threaded across the serial tests.
let nonce = '';
let dealId = 0;
let activityId = 0;

// ── admin-ajax helper (local — no shared-util edits) ───────────────────────────
type AjaxEnvelope = { success: boolean; data: unknown };

/**
 * POST an admin-ajax form. Uses `page.request` so the admin storageState cookies
 * ride along (the handlers need the cookie session AND the 'erp-deals' nonce).
 * Returns the parsed JSON envelope. The handlers always answer HTTP 200, so we
 * read the body and let callers branch on `success`.
 */
async function ajaxPost(
    request: APIRequestContext,
    action: string,
    form: Record<string, string>,
): Promise<{ status: number; body: AjaxEnvelope }> {
    const resp = await request.post(AJAX_URL, {
        form: { action, ...form },
        headers: { 'content-type': 'application/x-www-form-urlencoded' },
    });
    const status = resp.status();
    let body: AjaxEnvelope = { success: false, data: null };
    try {
        body = (await resp.json()) as AjaxEnvelope;
    } catch {
        // A PHP fatal would yield non-JSON — keep the default so the assertion
        // below ("not a fatal") surfaces it rather than throwing here.
        const text = await resp.text();
        body = { success: false, data: text };
    }
    return { status, body };
}

/** Pull the human message out of either error envelope shape. */
function errMessage(body: AjaxEnvelope): string {
    if (typeof body.data === 'string') return body.data;
    if (body.data && typeof body.data === 'object') {
        const d = body.data as Record<string, unknown>;
        return String(d.msg ?? d.message ?? '');
    }
    return '';
}

/** A PHP fatal renders this exact string anywhere a handler echoes HTML. */
const CRITICAL_ERROR = 'There has been a critical error on this website';

/** Assert an admin-ajax response is not a PHP fatal (resilient gate). */
function expectNotFatal(envelope: { status: number; body: AjaxEnvelope }): void {
    expect(envelope.status, 'admin-ajax answers HTTP 200 (no fatal)').toBe(200);
    expect(JSON.stringify(envelope.body)).not.toContain(CRITICAL_ERROR);
}

// ── Setup: boot the SPA page once to scrape the page-localized nonce ────────────
test.use({ storageState: data.auth.adminFile });

test.beforeAll(async ({ browser }) => {
    const context = await browser.newContext({ storageState: data.auth.adminFile });
    const page: Page = await context.newPage();
    try {
        await page.goto(DEALS_PAGE, { waitUntil: 'domcontentloaded' });
        // The Deals SPA must not render a PHP fatal, and the nonce must be present.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // Read window.erpDealsGlobal.nonce (localized inline). Fall back to a regex
        // over the raw HTML if the global is not yet on window.
        nonce = await page.evaluate(() => {
            const g = (window as unknown as { erpDealsGlobal?: { nonce?: string } }).erpDealsGlobal;
            return g && typeof g.nonce === 'string' ? g.nonce : '';
        });
        if (!nonce) {
            const html = await page.content();
            const m = html.match(/erpDealsGlobal\s*=\s*\{[^}]*?"nonce":"([a-f0-9]+)"/i);
            nonce = m?.[1] ?? '';
        }
    } finally {
        await page.close();
        await context.close();
    }
    expect(nonce, 'scraped erpDealsGlobal.nonce (action "erp-deals")').toMatch(/^[a-f0-9]{8,}$/);
});

test.afterAll(async () => {
    // Clean up everything this run created: child rows first, then the deal(s).
    try {
        if (dealId) {
            await dbUtils.dbQuery(`DELETE FROM ${T.stageHistory} WHERE deal_id = ?`, [dealId]);
            await dbUtils.dbQuery(`DELETE FROM ${T.activities} WHERE deal_id = ?`, [dealId]);
        }
        // Belt-and-suspenders: remove any stray rows by the run-unique title.
        const stray = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${T.deals} WHERE title LIKE ?`,
            [`%${RUN}%RUN%`],
        );
        for (const row of stray) {
            await dbUtils.dbQuery(`DELETE FROM ${T.stageHistory} WHERE deal_id = ?`, [row.id]);
            await dbUtils.dbQuery(`DELETE FROM ${T.activities} WHERE deal_id = ?`, [row.id]);
        }
        await dbUtils.dbQuery(`DELETE FROM ${T.deals} WHERE title LIKE ?`, [`%${RUN}%RUN%`]);
    } finally {
        await dbUtils.close();
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// Preconditions — the seed FK targets the lifecycle leans on actually exist.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals lifecycle — preconditions', () => {
    // DEALS-LC-00 — the stages, contact and activity type used below are present.
    test('seed pipeline stages, contact and activity type exist', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        const firstStage = await dbUtils.dbQuery<{ id: number; order: number }>(
            `SELECT id, \`order\` FROM ${T.stages} WHERE id = ? LIMIT 1`,
            [FIRST_STAGE],
        );
        expect(firstStage[0], 'first stage (id=4, "Proposal Made") exists').toBeTruthy();
        expect(Number(firstStage[0]?.order), 'first stage is order 0').toBe(0);

        const targetStage = await dbUtils.dbQuery<{ id: number; order: number }>(
            `SELECT id, \`order\` FROM ${T.stages} WHERE id = ? LIMIT 1`,
            [TARGET_STAGE],
        );
        expect(targetStage[0], 'target stage (id=5, "Negotiations Started") exists').toBeTruthy();
        expect(Number(targetStage[0]?.order), 'target stage is the last order (4)').toBe(4);

        const actType = await dbUtils.dbQuery<{ title: string }>(
            `SELECT title FROM ${T.activityTypes} WHERE id = ? AND deleted_at IS NULL LIMIT 1`,
            [ACTIVITY_TYPE_CALL],
        );
        expect(String(actType[0]?.title), 'activity type id=1 is "Call"').toBe('Call');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 1 — create a deal in the default pipeline.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals lifecycle — create', () => {
    // DEALS-LC-01 — create a deal (title+stage+contact+owner+value) → success.
    test('create a deal in the default pipeline', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[title]': DEAL_TITLE,
            'deal[stage_id]': String(FIRST_STAGE),
            'deal[contact_id]': String(CONTACT_ID),
            'deal[owner_id]': String(OWNER_ID),
            'deal[value]': '5000',
        });
        expectNotFatal(res);
        expect(res.body.success, `create succeeded (msg="${errMessage(res.body)}")`).toBe(true);

        const created = (res.body.data as { deal?: { id?: number } })?.deal;
        dealId = Number(created?.id ?? 0);
        expect(dealId, 'create returned a deal id').toBeGreaterThan(0);

        // DB: the row exists with the posted fields.
        const rows = await dbUtils.dbQuery<{
            title: string; stage_id: number; contact_id: number; owner_id: number;
            value: string; won_at: string | null; lost_at: string | null; deleted_at: string | null;
        }>(
            `SELECT title, stage_id, contact_id, owner_id, value, won_at, lost_at, deleted_at
                 FROM ${T.deals} WHERE id = ? LIMIT 1`,
            [dealId],
        );
        const deal = rows[0];
        expect(deal, 'deal row is in wp_erp_crm_deals').toBeTruthy();
        expect(String(deal?.title)).toBe(DEAL_TITLE);
        expect(Number(deal?.stage_id), 'deal sits on the first stage (4)').toBe(FIRST_STAGE);
        expect(Number(deal?.contact_id), 'deal links to contact 1').toBe(CONTACT_ID);
        expect(Number(deal?.owner_id), 'owner_id persisted').toBe(OWNER_ID);
        expect(Number(deal?.value), 'value persisted').toBe(5000);
        expect(deal?.won_at, 'fresh deal is not won').toBeNull();
        expect(deal?.lost_at, 'fresh deal is not lost').toBeNull();
        expect(deal?.deleted_at, 'fresh deal is not trashed').toBeNull();
    });

    // DEALS-LC-02 — on create, exactly one stage-history row for the start stage,
    // with `out` NULL (stage 4 is order 0, so no intervening rows are inserted).
    test('create inserts exactly one open stage-history row for the start stage', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        test.skip(!dealId, 'needs the deal created in DEALS-LC-01');

        const history = await dbUtils.dbQuery<{ stage_id: number; out: string | null }>(
            `SELECT stage_id, \`out\` FROM ${T.stageHistory} WHERE deal_id = ? ORDER BY id`,
            [dealId],
        );
        expect(history.length, 'exactly one history row on create (first stage = order 0)').toBe(1);
        expect(Number(history[0]?.stage_id), 'history row is the start stage').toBe(FIRST_STAGE);
        expect(history[0]?.out, 'the open stage has out = NULL').toBeNull();
    });

    // DEALS-LC-03 — DEALS-OWNER-NULL quirk: omitting owner_id as an admin/manager
    // returns success:false with the generic save message (NOT a fatal). The SPA
    // always sends owner_id, so end users don't hit it — documented as a known
    // constraint, asserted as a graceful 200/success:false (never a 500).
    test('create omitting owner_id fails gracefully (DEALS-OWNER-NULL quirk)', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[title]': `NOOWNER ${RUN} RUN`,
            'deal[stage_id]': String(FIRST_STAGE),
            'deal[contact_id]': String(CONTACT_ID),
        });
        expectNotFatal(res);
        expect(res.body.success, 'omitting owner_id does not save').toBe(false);
        expect(errMessage(res.body)).toMatch(/Could not save the deal/i);

        // And nothing was inserted for that title.
        const stray = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${T.deals} WHERE title = ? LIMIT 1`,
            [`NOOWNER ${RUN} RUN`],
        );
        expect(stray.length, 'no row persisted when owner_id was omitted').toBe(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 2 — move the deal to a later-order stage.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals lifecycle — move stage', () => {
    // DEALS-LC-04 — move to stage 5 → success + deals.stage_id updates.
    test('move the deal to a later stage', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!dealId, 'needs the deal created earlier');

        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[id]': String(dealId),
            'deal[stage_id]': String(TARGET_STAGE),
            'deal[owner_id]': String(OWNER_ID),
        });
        expectNotFatal(res);
        expect(res.body.success, `move succeeded (msg="${errMessage(res.body)}")`).toBe(true);

        const rows = await dbUtils.dbQuery<{ stage_id: number }>(
            `SELECT stage_id FROM ${T.deals} WHERE id = ? LIMIT 1`,
            [dealId],
        );
        expect(Number(rows[0]?.stage_id), 'deal moved to the target stage (5)').toBe(TARGET_STAGE);
    });

    // DEALS-LC-05 — stage-history reflects the move: the old current-stage row
    // (4) gets a non-null `out`; new open rows are inserted for every intervening
    // stage by order up to the target (ids 1,2,3,5). Total = 5 rows.
    test('move rebuilds stage history (old stage closed, intervening stages opened)', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        test.skip(!dealId, 'needs the deal moved in DEALS-LC-04');

        const total = await dbUtils.dbQuery<{ cnt: number }>(
            `SELECT COUNT(*) AS cnt FROM ${T.stageHistory} WHERE deal_id = ?`,
            [dealId],
        );
        expect(Number(total[0]?.cnt), 'history has a row per stage up to+including the target').toBe(5);

        // The prior current stage (4) is now closed (out IS NOT NULL).
        const closed = await dbUtils.dbQuery<{ cnt: number }>(
            `SELECT COUNT(*) AS cnt FROM ${T.stageHistory} WHERE deal_id = ? AND stage_id = ? AND \`out\` IS NOT NULL`,
            [dealId, FIRST_STAGE],
        );
        expect(Number(closed[0]?.cnt), 'old current stage (4) was closed with an out timestamp').toBe(1);

        // The new current stage (5) is open (out IS NULL).
        const open = await dbUtils.dbQuery<{ cnt: number }>(
            `SELECT COUNT(*) AS cnt FROM ${T.stageHistory} WHERE deal_id = ? AND stage_id = ? AND \`out\` IS NULL`,
            [dealId, TARGET_STAGE],
        );
        expect(Number(open[0]?.cnt), 'new current stage (5) is open with out NULL').toBe(1);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 3 — add an activity to the deal.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals lifecycle — add activity', () => {
    // DEALS-LC-06 — save_activity → success + a row in wp_erp_crm_deals_activities.
    test('add an activity to the deal', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!dealId, 'needs the deal created earlier');

        const res = await ajaxPost(request, 'erp_deals_save_activity', {
            _wpnonce: nonce,
            'activity[type]': String(ACTIVITY_TYPE_CALL),
            'activity[title]': ACTIVITY_TITLE,
            'activity[deal_id]': String(dealId),
            'activity[assigned_to_id]': String(OWNER_ID),
            'activity[start]': ACTIVITY_START,
            'activity[is_start_time_set]': '1',
        });
        expectNotFatal(res);
        expect(res.body.success, `save_activity succeeded (msg="${errMessage(res.body)}")`).toBe(true);

        const created = (res.body.data as { activity?: { id?: number } })?.activity;
        activityId = Number(created?.id ?? 0);
        expect(activityId, 'save_activity returned an activity id').toBeGreaterThan(0);

        const rows = await dbUtils.dbQuery<{
            type: string; title: string; deal_id: number; assigned_to_id: number;
            start: string; done_at: string | null;
        }>(
            `SELECT type, title, deal_id, assigned_to_id, start, done_at
                 FROM ${T.activities} WHERE id = ? LIMIT 1`,
            [activityId],
        );
        const act = rows[0];
        expect(act, 'activity row is in wp_erp_crm_deals_activities').toBeTruthy();
        expect(Number(act?.type), 'activity type is Call (1)').toBe(ACTIVITY_TYPE_CALL);
        expect(String(act?.title)).toBe(ACTIVITY_TITLE);
        expect(Number(act?.deal_id), 'activity is linked to the deal').toBe(dealId);
        expect(Number(act?.assigned_to_id), 'activity is assigned to the owner').toBe(OWNER_ID);
        expect(act?.done_at, 'a fresh activity is not done').toBeNull();

        const cnt = await dbUtils.dbQuery<{ cnt: number }>(
            `SELECT COUNT(*) AS cnt FROM ${T.activities} WHERE deal_id = ?`,
            [dealId],
        );
        expect(Number(cnt[0]?.cnt), 'exactly one activity on the deal').toBe(1);
    });

    // DEALS-LC-07 — negative: save_activity missing `start` → success:false with
    // 'Invalid start date' (NOT a fatal). Validation order in Deals::save_activity
    // is type → title → deal_id → assigned_to_id → start.
    test('save_activity without a start date is rejected', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!dealId, 'needs the deal created earlier');

        const res = await ajaxPost(request, 'erp_deals_save_activity', {
            _wpnonce: nonce,
            'activity[type]': String(ACTIVITY_TYPE_CALL),
            'activity[title]': `${ACTIVITY_TITLE} (no start)`,
            'activity[deal_id]': String(dealId),
            'activity[assigned_to_id]': String(OWNER_ID),
        });
        expectNotFatal(res);
        expect(res.body.success, 'missing start does not save the activity').toBe(false);
        expect(errMessage(res.body)).toMatch(/Invalid start date/i);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 4 — won / lost / reopen. Each re-saves the deal at its current stage.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals lifecycle — won / lost / reopen', () => {
    // DEALS-LC-08 — mark WON → won_at set, lost_at NULL, lost_reason_id NULL.
    test('mark the deal won', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!dealId, 'needs the deal created earlier');

        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[id]': String(dealId),
            'deal[stage_id]': String(TARGET_STAGE),
            'deal[owner_id]': String(OWNER_ID),
            'deal[won]': 'true',
        });
        expectNotFatal(res);
        expect(res.body.success, `mark-won succeeded (msg="${errMessage(res.body)}")`).toBe(true);

        const rows = await dbUtils.dbQuery<{ won_at: string | null; lost_at: string | null; lost_reason_id: number | null }>(
            `SELECT won_at, lost_at, lost_reason_id FROM ${T.deals} WHERE id = ? LIMIT 1`,
            [dealId],
        );
        expect(rows[0]?.won_at, 'won_at is set').not.toBeNull();
        expect(rows[0]?.lost_at, 'lost_at stays NULL when won').toBeNull();
        expect(rows[0]?.lost_reason_id, 'lost_reason_id stays NULL when won').toBeNull();
    });

    // DEALS-LC-09 — mark LOST with a free-text reason (lost_reasons is empty, so a
    // lost_reason_id would 'Invalid lost reason'). lost_at set, won_at cleared,
    // lost_reason persisted, lost_reason_id NULL, comment persisted.
    test('mark the deal lost with a free-text reason', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!dealId, 'needs the deal created earlier');

        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[id]': String(dealId),
            'deal[stage_id]': String(TARGET_STAGE),
            'deal[owner_id]': String(OWNER_ID),
            'deal[lost_reason]': 'Budget too high',
            'deal[lost_reason_comment]': 'Client chose competitor',
        });
        expectNotFatal(res);
        expect(res.body.success, `mark-lost succeeded (msg="${errMessage(res.body)}")`).toBe(true);

        const rows = await dbUtils.dbQuery<{
            won_at: string | null; lost_at: string | null;
            lost_reason: string | null; lost_reason_id: number | null; lost_reason_comment: string | null;
        }>(
            `SELECT won_at, lost_at, lost_reason, lost_reason_id, lost_reason_comment
                 FROM ${T.deals} WHERE id = ? LIMIT 1`,
            [dealId],
        );
        expect(rows[0]?.lost_at, 'lost_at is set').not.toBeNull();
        expect(rows[0]?.won_at, 'won_at is cleared when lost').toBeNull();
        expect(String(rows[0]?.lost_reason), 'free-text lost_reason persisted').toBe('Budget too high');
        expect(rows[0]?.lost_reason_id, 'lost_reason_id stays NULL for free-text reason').toBeNull();
        expect(String(rows[0]?.lost_reason_comment), 'lost_reason_comment persisted').toBe('Client chose competitor');
    });

    // DEALS-LC-10 — negative: a lost_reason_id pointing at a non-existent reason
    // (table is empty) → success:false 'Invalid lost reason' (NOT a fatal).
    test('mark-lost with a non-existent lost_reason_id is rejected', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!dealId, 'needs the deal created earlier');

        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[id]': String(dealId),
            'deal[stage_id]': String(TARGET_STAGE),
            'deal[owner_id]': String(OWNER_ID),
            'deal[lost_reason_id]': '999999',
        });
        expectNotFatal(res);
        expect(res.body.success, 'a bogus lost_reason_id does not save').toBe(false);
        expect(errMessage(res.body)).toMatch(/Invalid lost reason/i);
    });

    // DEALS-LC-11 — reopen → won_at, lost_at, lost_reason, lost_reason_id all NULL.
    test('reopen the deal clears won/lost state', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!dealId, 'needs the deal created earlier');

        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[id]': String(dealId),
            'deal[stage_id]': String(TARGET_STAGE),
            'deal[owner_id]': String(OWNER_ID),
            'deal[reopen]': 'true',
        });
        expectNotFatal(res);
        expect(res.body.success, `reopen succeeded (msg="${errMessage(res.body)}")`).toBe(true);

        const rows = await dbUtils.dbQuery<{
            won_at: string | null; lost_at: string | null;
            lost_reason: string | null; lost_reason_id: number | null;
        }>(
            `SELECT won_at, lost_at, lost_reason, lost_reason_id FROM ${T.deals} WHERE id = ? LIMIT 1`,
            [dealId],
        );
        expect(rows[0]?.won_at, 'won_at cleared on reopen').toBeNull();
        expect(rows[0]?.lost_at, 'lost_at cleared on reopen').toBeNull();
        expect(rows[0]?.lost_reason, 'lost_reason cleared on reopen').toBeNull();
        expect(rows[0]?.lost_reason_id, 'lost_reason_id cleared on reopen').toBeNull();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 5 — delete the activity (behavioral cleanup).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals lifecycle — delete activity', () => {
    // DEALS-LC-12 — delete_activity → success + the row is removed.
    test('delete the activity', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!activityId, 'needs the activity created in DEALS-LC-06');

        const res = await ajaxPost(request, 'erp_deals_delete_activity', {
            _wpnonce: nonce,
            id: String(activityId),
        });
        expectNotFatal(res);
        expect(res.body.success, `delete_activity succeeded (msg="${errMessage(res.body)}")`).toBe(true);

        const cnt = await dbUtils.dbQuery<{ cnt: number }>(
            `SELECT COUNT(*) AS cnt FROM ${T.activities} WHERE id = ?`,
            [activityId],
        );
        expect(Number(cnt[0]?.cnt), 'activity row is gone after delete').toBe(0);
    });

    // DEALS-LC-13 — negative: delete_activity with no id → 'Invalid operation'.
    test('delete_activity with no id is rejected', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, 'erp_deals_delete_activity', { _wpnonce: nonce });
        expectNotFatal(res);
        expect(res.body.success, 'missing id is rejected').toBe(false);
        expect(errMessage(res.body)).toMatch(/Invalid operation/i);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Step 6 — trash the deal (soft delete).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals lifecycle — trash', () => {
    // DEALS-LC-14 — delete_deal action=trash → deleted_at set, row still present.
    test('trash the deal (soft delete)', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!dealId, 'needs the deal created earlier');

        const res = await ajaxPost(request, 'erp_deals_delete_deal', {
            _wpnonce: nonce,
            'deal[id]': String(dealId),
            'deal[action]': 'trash',
        });
        expectNotFatal(res);
        expect(res.body.success, `trash succeeded (msg="${errMessage(res.body)}")`).toBe(true);

        const rows = await dbUtils.dbQuery<{ id: number; deleted_at: string | null }>(
            `SELECT id, deleted_at FROM ${T.deals} WHERE id = ? LIMIT 1`,
            [dealId],
        );
        expect(rows[0], 'row still physically present after a soft delete').toBeTruthy();
        expect(rows[0]?.deleted_at, 'deleted_at is now set (soft delete)').not.toBeNull();

        // It is excluded from the active (deleted_at IS NULL) set.
        const active = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${T.deals} WHERE id = ? AND deleted_at IS NULL`,
            [dealId],
        );
        expect(active.length, 'trashed deal is excluded from the active set').toBe(0);
    });

    // DEALS-LC-15 — negative: delete_deal with an invalid action → 'Invalid operation'.
    test('delete_deal with an invalid action is rejected', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        test.skip(!dealId, 'needs the deal created earlier');

        const res = await ajaxPost(request, 'erp_deals_delete_deal', {
            _wpnonce: nonce,
            'deal[id]': String(dealId),
            'deal[action]': 'bogus',
        });
        expectNotFatal(res);
        expect(res.body.success, 'an unknown action is rejected').toBe(false);
        expect(errMessage(res.body)).toMatch(/Invalid operation/i);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Negative — nonce + field validation on the create handler.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals lifecycle — negative (validation + nonce)', () => {
    // DEALS-LC-16 — a bad nonce fails verify_nonce('erp-deals'): success:false,
    // data is the plain string 'Error: Nonce verification failed' (NOT a fatal).
    test('save_deal with a bad nonce fails nonce verification', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: 'BADNONCE',
            'deal[title]': `BADNONCE ${RUN} RUN`,
            'deal[stage_id]': String(FIRST_STAGE),
            'deal[contact_id]': String(CONTACT_ID),
            'deal[owner_id]': String(OWNER_ID),
        });
        expectNotFatal(res);
        expect(res.body.success, 'bad nonce is rejected').toBe(false);
        expect(errMessage(res.body)).toMatch(/Nonce verification failed/i);

        // Nothing was created.
        const stray = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${T.deals} WHERE title = ? LIMIT 1`,
            [`BADNONCE ${RUN} RUN`],
        );
        expect(stray.length, 'no deal created on a bad nonce').toBe(0);
    });

    // DEALS-LC-17 — missing title (with a valid contact) → 'Deal title is required'.
    test('save_deal without a title is rejected', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[stage_id]': String(FIRST_STAGE),
            'deal[contact_id]': String(CONTACT_ID),
            'deal[owner_id]': String(OWNER_ID),
        });
        expectNotFatal(res);
        expect(res.body.success, 'a titleless deal is rejected').toBe(false);
        expect(errMessage(res.body)).toMatch(/Deal title is required/i);
    });

    // DEALS-LC-18 — invalid stage id (with valid title+contact) → 'Invalid pipline stage'.
    test('save_deal with an invalid stage is rejected', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[title]': `BADSTAGE ${RUN} RUN`,
            'deal[stage_id]': '999999',
            'deal[contact_id]': String(CONTACT_ID),
            'deal[owner_id]': String(OWNER_ID),
        });
        expectNotFatal(res);
        expect(res.body.success, 'an invalid stage is rejected').toBe(false);
        // The handler ships the message with the source's "pipline" spelling.
        expect(errMessage(res.body)).toMatch(/Invalid pipl?ine stage/i);
    });

    // DEALS-LC-19 — neither contact nor company → 'Either contact or company name is required'.
    test('save_deal with neither contact nor company is rejected', { tag: ['@pro', '@crm', '@admin'] }, async ({ request }) => {
        const res = await ajaxPost(request, 'erp_deals_save_deal', {
            _wpnonce: nonce,
            'deal[title]': `NOPEOPLE ${RUN} RUN`,
            'deal[stage_id]': String(FIRST_STAGE),
            'deal[owner_id]': String(OWNER_ID),
        });
        expectNotFatal(res);
        expect(res.body.success, 'a deal with no people is rejected').toBe(false);
        expect(errMessage(res.body)).toMatch(/Either contact or company name is required/i);
    });
});
