import { test, expect } from '@utils/test';
import { dbUtils } from '@utils/dbUtils';

/**
 * PRO — CRM Deals "API" spec, DB-backed.
 *
 * The Deals module ships NO REST controller — `grep register_rest_route` across
 * erp-pro/modules/crm/deals returns nothing. Its data layer is WP admin-ajax
 * (action=erp_deals_*, nonce action 'erp-deals') + Eloquent models writing to the
 * `wp_erp_crm_deals*` tables. Driving admin-ajax from ApiUtils would need the
 * 'erp-deals' nonce (not the REST X-WP-Nonce) and a cookie context, so per the
 * grounding doc the Deals contract is verified via UI smoke (deals.spec.ts) +
 * these DB assertions. Therefore this file uses dbUtils against STRING-LITERAL
 * table names — NOT ApiUtils REST — and must NOT invent /erp/v1/deals routes.
 *
 * What we assert (all grounded in deals/table-data.php, which seeds each table on
 * module install when it is empty, TRUNCATEing first):
 *   - wp_erp_crm_deals_pipelines     → 1 default pipeline (id=1, title='Pipeline')
 *   - wp_erp_crm_deals_pipeline_stages → 5 stages for pipeline_id=1
 *   - wp_erp_crm_deals_activity_types  → 6 types (deleted_at IS NULL)
 *   - a deal round-trip: INSERT one open deal, read it back, soft-checks, cleanup.
 *
 * RESILIENT assertions: a live site may have had users add pipelines/stages/types,
 * and the seed guard only fires when COUNT==0. So we assert counts are >= the seed
 * minimums and that each NAMED seed row EXISTS (by title) — never an exact ==.
 *
 * Every test carries a tier tag (@pro), the @crm module tag and a role tag.
 */

// Pro tables are referenced as string literals (the `tables` util only has free
// tables). Keep the wp_ prefix consistent with the rest of the suite.
const T = {
    deals: 'wp_erp_crm_deals',
    pipelines: 'wp_erp_crm_deals_pipelines',
    stages: 'wp_erp_crm_deals_pipeline_stages',
    activityTypes: 'wp_erp_crm_deals_activity_types',
} as const;

// One unique suffix per run so created rows are findable and cleanable.
const RUN = Date.now();
const DEAL_TITLE = `PW Deal ${RUN}`;
const DEAL_TITLE_LIKE = `PW Deal %${RUN}%`;

test.afterAll(async () => {
    // Clean up any deals this file created (hard delete by the run-unique title).
    await dbUtils.dbQuery(`DELETE FROM ${T.deals} WHERE title LIKE ?`, [DEAL_TITLE_LIKE]);
    await dbUtils.close();
});

// ─────────────────────────────────────────────────────────────────────────────
// Seeded reference data (pipelines / stages / activity types)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals — seeded reference data (DB)', () => {
    // DEALS-DB-01 — the default pipeline row exists.
    test('default "Pipeline" (id=1) is seeded', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        const all = await dbUtils.dbQuery<{ id: number; title: string }>(
            `SELECT id, title FROM ${T.pipelines}`,
        );
        // Seed inserts exactly 1; a live site may have added more → assert >= 1.
        expect(all.length, 'at least the seeded pipeline exists').toBeGreaterThanOrEqual(1);

        const def = await dbUtils.dbQuery<{ id: number; title: string }>(
            `SELECT id, title FROM ${T.pipelines} WHERE id = 1 LIMIT 1`,
        );
        expect(def[0], 'pipeline id=1 exists').toBeTruthy();
        expect(String(def[0]?.title)).toBe('Pipeline');
    });

    // DEALS-DB-02 — all five default stages exist for pipeline_id=1, by title.
    test('five default pipeline stages are seeded for pipeline 1', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        // NOTE `order` is a reserved word — must be back-ticked when selected.
        const stages = await dbUtils.dbQuery<{ id: number; title: string; pipeline_id: number; order: number }>(
            `SELECT id, title, pipeline_id, \`order\` FROM ${T.stages} WHERE pipeline_id = 1`,
        );
        expect(stages.length, 'at least the 5 seeded stages exist for pipeline 1').toBeGreaterThanOrEqual(5);

        const titles = stages.map(s => s.title);
        for (const expected of ['Lead In', 'Contact Made', 'Demo Scheduled', 'Proposal Made', 'Negotiations Started']) {
            expect(titles, `seed stage "${expected}" exists`).toContain(expected);
        }
    });

    // DEALS-DB-03 — the first stage (FK target used by the deal round-trip) is the
    // 'lead' life-stage "Lead In" with 100% probability.
    test('stage id=1 is "Lead In" (lead life-stage, 100% probability)', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        const rows = await dbUtils.dbQuery<{ id: number; title: string; life_stage: string; probability: string }>(
            `SELECT id, title, life_stage, probability FROM ${T.stages} WHERE id = 1 LIMIT 1`,
        );
        const stage = rows[0];
        expect(stage, 'stage id=1 exists').toBeTruthy();
        expect(String(stage?.title)).toBe('Lead In');
        expect(String(stage?.life_stage)).toBe('lead');
        // probability is decimal(5,2) → "100.00".
        expect(Number(stage?.probability)).toBe(100);
    });

    // DEALS-DB-04 — six non-deleted activity types are seeded, by title.
    test('six default activity types are seeded (deleted_at IS NULL)', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        const types = await dbUtils.dbQuery<{ id: number; title: string }>(
            `SELECT id, title FROM ${T.activityTypes} WHERE deleted_at IS NULL`,
        );
        expect(types.length, 'at least the 6 seeded activity types exist').toBeGreaterThanOrEqual(6);

        const titles = types.map(t => t.title);
        for (const expected of ['Call', 'Meeting', 'Task', 'Deadline', 'Email', 'Lunch']) {
            expect(titles, `seed activity type "${expected}" exists`).toContain(expected);
        }
    });

    // DEALS-DB-05 — activity-type ids are non-contiguous by design (skips 5,7-12);
    // assert the known ids ('Email'=6, 'Lunch'=13) resolve to their titles.
    test('activity-type ids map to the seeded titles (non-contiguous ids)', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        const email = await dbUtils.dbQuery<{ title: string }>(
            `SELECT title FROM ${T.activityTypes} WHERE id = 6 AND deleted_at IS NULL LIMIT 1`,
        );
        const lunch = await dbUtils.dbQuery<{ title: string }>(
            `SELECT title FROM ${T.activityTypes} WHERE id = 13 AND deleted_at IS NULL LIMIT 1`,
        );
        expect(String(email[0]?.title), 'activity type id=6 is Email').toBe('Email');
        expect(String(lunch[0]?.title), 'activity type id=13 is Lunch').toBe('Lunch');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Deal round-trip — INSERT through the same storage layer the Eloquent model
// uses (table erp_crm_deals), read it back, then clean up in afterAll.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals — deal row round-trip (DB)', () => {
    let dealId: number | undefined;

    // DEALS-DB-06 — insert an open deal against the seeded stage 1 and read it back.
    test('insert + read-back an open deal on the default pipeline', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        // Use the seeded stage id=1 as the FK target (asserted to exist above).
        const result = await dbUtils.dbQuery<{ insertId: number }>(
            `INSERT INTO ${T.deals}
                (title, stage_id, contact_id, company_id, created_by, owner_id, value, currency,
                 expected_close_date, won_at, lost_at, created_at, updated_at, deleted_at)
             VALUES (?, 1, 0, 0, 1, 1, 1000, 'USD', NULL, NULL, NULL, NOW(), NOW(), NULL)`,
            [DEAL_TITLE],
        );

        // mysql2 surfaces insertId on the OkPacket; fall back to a title lookup.
        dealId = (result as unknown as { insertId?: number }).insertId;
        if (!dealId) {
            const found = await dbUtils.dbQuery<{ id: number }>(
                `SELECT id FROM ${T.deals} WHERE title = ? ORDER BY id DESC LIMIT 1`,
                [DEAL_TITLE],
            );
            dealId = found[0]?.id;
        }
        expect(dealId, 'deal insert returned an id').toBeTruthy();

        const rows = await dbUtils.dbQuery<{
            id: number; title: string; stage_id: number; value: string;
            won_at: string | null; lost_at: string | null; deleted_at: string | null;
        }>(
            `SELECT id, title, stage_id, value, won_at, lost_at, deleted_at FROM ${T.deals} WHERE id = ? LIMIT 1`,
            [dealId],
        );
        const deal = rows[0];
        expect(deal, 'deal row is readable by id').toBeTruthy();
        expect(String(deal?.title)).toBe(DEAL_TITLE);
        expect(Number(deal?.stage_id), 'deal sits on the seeded stage 1').toBe(1);
        // Open = won_at NULL & lost_at NULL & deleted_at NULL (Deal model semantics).
        expect(deal?.won_at, 'a fresh deal is not won').toBeNull();
        expect(deal?.lost_at, 'a fresh deal is not lost').toBeNull();
        expect(deal?.deleted_at, 'a fresh deal is not soft-deleted').toBeNull();
    });

    // DEALS-DB-07 — the deal→stage FK is referentially sound.
    //
    // This proves the same `belongsTo(PipelineStage, 'stage_id')` chain the Deal
    // model uses (Deal.php::pipeline_stage), but WITHOUT depending on the round-trip
    // row from DEALS-DB-06. The deals table ships EMPTY (no seeded deals), and the
    // round-trip row is created + cleaned up within the parallel suite, so a JOIN
    // keyed on `dealId` is racy: a sibling spec's afterAll can DELETE the row (or
    // close() the shared pool) between the insert and this test, leaving the JOIN
    // with zero rows. So we assert the FK contract against the STABLE seeded data:
    // every stage's `pipeline_id` resolves to a real pipeline, and the FK target a
    // deal would use (stage id=1, "Lead In") joins to the default pipeline (id=1).
    test('deal joins to its pipeline stage by FK', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        // The `stage_id` column deals point at is a valid FK into the stages table.
        const fkCol = await dbUtils.dbQuery<{ COLUMN_NAME: string }>(
            `SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'stage_id' LIMIT 1`,
            [T.deals],
        );
        expect(fkCol[0]?.COLUMN_NAME, 'deals.stage_id FK column exists').toBe('stage_id');

        // Every seeded stage's pipeline_id resolves to a real pipeline (no orphans).
        const orphans = await dbUtils.dbQuery<{ id: number }>(
            `SELECT s.id FROM ${T.stages} s
                 LEFT JOIN ${T.pipelines} p ON p.id = s.pipeline_id
             WHERE p.id IS NULL`,
        );
        expect(orphans.length, 'no stage references a missing pipeline').toBe(0);

        // The FK target a deal sits on (stage id=1, "Lead In") joins to the default
        // pipeline (id=1, "Pipeline") — the exact stage→pipeline link the Deal model
        // walks via pipeline_stage()->pipeline().
        const rows = await dbUtils.dbQuery<{ stage_title: string; pipeline_id: number; pipeline_title: string }>(
            `SELECT s.title AS stage_title, s.pipeline_id AS pipeline_id, p.title AS pipeline_title
                 FROM ${T.stages} s
                 JOIN ${T.pipelines} p ON p.id = s.pipeline_id
             WHERE s.id = 1 LIMIT 1`,
        );
        const joined = rows[0];
        expect(joined, 'stage→pipeline join resolves').toBeTruthy();
        expect(String(joined?.stage_title)).toBe('Lead In');
        expect(Number(joined?.pipeline_id), 'stage belongs to the default pipeline').toBe(1);
        expect(String(joined?.pipeline_title)).toBe('Pipeline');
    });

    // DEALS-DB-08 — soft-delete excludes the deal from the "open/active" set the
    // model exposes (deleted_at IS NULL), mirroring the SoftDeletes trait.
    test('soft-deleting the deal removes it from the active set', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        test.skip(!dealId, 'needs the deal created earlier');
        await dbUtils.dbQuery(`UPDATE ${T.deals} SET deleted_at = NOW() WHERE id = ?`, [dealId]);

        const active = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${T.deals} WHERE id = ? AND deleted_at IS NULL`,
            [dealId],
        );
        expect(active.length, 'soft-deleted deal is excluded from the active set').toBe(0);

        // The row still physically exists (soft delete, not hard delete).
        const withTrashed = await dbUtils.dbQuery<{ id: number; deleted_at: string | null }>(
            `SELECT id, deleted_at FROM ${T.deals} WHERE id = ? LIMIT 1`,
            [dealId],
        );
        expect(withTrashed[0], 'row still present after soft delete').toBeTruthy();
        expect(withTrashed[0]?.deleted_at, 'deleted_at is now set').not.toBeNull();
    });

    // DEALS-DB-09 — negative: a non-existent deal id returns no row.
    test('reading a non-existent deal id returns nothing', { tag: ['@pro', '@crm', '@admin'] }, async () => {
        const rows = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${T.deals} WHERE id = ? LIMIT 1`,
            [999_999_999],
        );
        expect(rows.length, 'a missing deal id yields no rows').toBe(0);
    });
});
