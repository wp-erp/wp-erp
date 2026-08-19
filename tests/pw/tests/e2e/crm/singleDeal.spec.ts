import { test, expect } from '@utils/test';
import { SingleDealPage, dealBoxes, composerTabs, timelineTabs } from '@pages/crm/singleDealPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupDeals } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Deal ids and stage ids used here come from the seeded default pipeline. Stage
 * 1 is `Lead In`; stages are addressed on the page by INDEX because the bar's
 * `tooltip-title` is a composed history string, not the stage name.
 */
test.describe('CRM — Single deal @pro', () => {
    let page: SingleDealPage;

    test.beforeAll(async () => {
        await cleanupDeals('pwerp_sd');
    });

    test.beforeEach(async ({ page: p }) => {
        page = new SingleDealPage(p);
    });

    test.afterAll(async () => {
        await cleanupDeals('pwerp_sd');
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the single-deal page renders its boxes, composer and timeline', { tag: ['@tier1', '@pro', '@crm-deals', '@smoke'] }, async () => {
        const title = uniqueId('sd');
        const id = await page.createDealFast(title, 2, 7500);

        await page.gotoDeal(id);

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
        expect(await page.dealTitle(), 'the deal is the one asked for').toBe(title);
        expect(await page.dealValue(), 'and its value is formatted as currency').toContain('7,500');

        const boxes = await page.boxTitles();
        for (const box of dealBoxes) {
            expect(boxes, `sidebar box "${box}"`).toContain(box);
        }

        expect(await page.composerTabLabels(), 'the composer offers its four tabs').toEqual([...composerTabs]);
        expect(await page.timelineTabLabels(), 'and the timeline its six').toEqual([...timelineTabs]);
    });

    test('a note is saved against the deal and appears in its timeline', { tag: ['@tier1', '@pro', '@crm-deals', '@crud'] }, async () => {
        const title = uniqueId('sd');
        const body = `${title} note body`;
        const id = await page.createDealFast(title);

        await page.gotoDeal(id);
        await page.addNote(body);

        const rows = await query<RowDataPacket[]>(
            `SELECT note, created_by FROM ${prefix()}erp_crm_deals_notes WHERE deal_id = ?`,
            [id]
        );

        expect(rows, 'one note row is written').toHaveLength(1);
        expect(String(rows[0]!.note), 'carrying the text typed').toContain(body);
        expect(Number(rows[0]!.created_by), 'and attributed to a real user').toBeGreaterThan(0);

        await page.gotoDeal(id);
        await page.openTimelineTab('Notes');

        expect(await page.timelineText(), 'and the timeline shows it').toContain(body);
    });

    test('a competitor is added to the deal', { tag: ['@tier1', '@pro', '@crm-deals', '@crud'] }, async () => {
        const title = uniqueId('sd');
        const name = `${title} Rival`;
        const id = await page.createDealFast(title);

        await page.gotoDeal(id);

        expect(await page.competitorBoxText(), 'the box starts empty').toContain('No competitor record found');

        await page.addCompetitor({ name, website: 'https://rival.test', strengths: 'price', weaknesses: 'support' });

        const rows = await query<RowDataPacket[]>(
            `SELECT competitor_name, website, strengths, weaknesses FROM ${prefix()}erp_crm_deals_competitors WHERE deal_id = ?`,
            [id]
        );

        expect(rows, 'one competitor row is written').toHaveLength(1);
        expect(String(rows[0]!.competitor_name), 'with the name given').toBe(name);
        expect(String(rows[0]!.strengths), 'and the strengths given').toBe('price');

        await page.gotoDeal(id);
        expect(await page.competitorNames(), 'and it is listed').toContain(name);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('clicking a stage on the bar moves the deal', { tag: ['@tier2', '@pro', '@crm-deals'] }, async () => {
        const title = uniqueId('sd');
        const id = await page.createDealFast(title, 1);

        await page.gotoDeal(id);

        const before = await page.activeStageIndex();
        expect(before, 'precondition: the bar marks the current stage').toBeGreaterThanOrEqual(0);

        const target = before === 0 ? 1 : 0;
        await page.moveToStageIndex(target);

        expect(await page.activeStageIndex(), 'the bar follows the click').toBe(target);

        const stored = await query<RowDataPacket[]>(
            `SELECT s.title AS stage, s.\`order\` AS ord
               FROM ${prefix()}erp_crm_deals d
               JOIN ${prefix()}erp_crm_deals_pipeline_stages s ON s.id = d.stage_id
              WHERE d.id = ?`,
            [id]
        );

        const order = await query<RowDataPacket[]>(
            `SELECT title FROM ${prefix()}erp_crm_deals_pipeline_stages WHERE pipeline_id = 1 ORDER BY \`order\` ASC`
        );

        expect(String(stored[0]!.stage), 'and the deal is stored in the clicked stage').toBe(String(order[target]!.title));
    });

    test.fail(
        'a deal has exactly one open stage-history row, and it is the current stage',
        { tag: ['@tier2', '@pro', '@crm-deals', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-043, filed 2026-07-21 against erp-pro 1.6.0 and
            // NEVER POSTED to GitHub. Re-verified here on 1.7.0: `save_deal()`
            // rebuilds history from an unscoped `PipelineStageModel::get()` and
            // writes an `out = NULL` row for every stage whose `order` is at or
            // below the deal's, so the "exactly one open row" invariant is broken
            // from the moment a deal is created.
            //
            // Distinct from ERP-144 (the seeded `order` value). This case is the
            // COUNT of open rows; ERP-144 is WHICH stages they name.
            //
            // Precondition first — `test.fail()` reports a PASS on any failure.
            const title = uniqueId('sd');
            const id = await page.createDealFast(title, 3);

            await page.gotoDeal(id);
            await page.moveToStageIndex(await page.activeStageIndex());

            const open = await query<RowDataPacket[]>(
                `SELECT h.stage_id, s.title AS stage
                   FROM ${prefix()}erp_crm_deals_stage_history h
                   JOIN ${prefix()}erp_crm_deals_pipeline_stages s ON s.id = h.stage_id
                  WHERE h.deal_id = ? AND h.\`out\` IS NULL`,
                [id]
            );

            const deal = await query<RowDataPacket[]>(`SELECT stage_id FROM ${prefix()}erp_crm_deals WHERE id = ?`, [id]);
            expect(deal, 'precondition: the deal exists').toHaveLength(1);

            expect(open, 'exactly one stage is open').toHaveLength(1);
            expect(Number(open[0]!.stage_id), 'and it is the stage the deal is in').toBe(Number(deal[0]!.stage_id));
        }
    );

    test('a deal can be won and then reopened', { tag: ['@tier2', '@pro', '@crm-deals', '@flow'] }, async () => {
        const title = uniqueId('sd');
        const id = await page.createDealFast(title, 2);

        await page.gotoDeal(id);

        expect(await page.statusButtonLabels(), 'an open deal offers Won, Lost and Trash').toEqual(['Won', 'Lost', 'Trash']);

        await page.clickStatusButton('Won');

        const won = await query<RowDataPacket[]>(`SELECT won_at, lost_at FROM ${prefix()}erp_crm_deals WHERE id = ?`, [id]);
        expect(won[0]!.won_at, 'won_at is stamped').not.toBeNull();
        expect(won[0]!.lost_at, 'and lost_at is not').toBeNull();

        await page.gotoDeal(id);
        expect(await page.hasWonBadge(), 'the page badges it as won').toBe(true);
        expect(await page.statusButtonLabels(), 'and offers Reopen instead of Won/Lost').toEqual(['Reopen', 'Trash']);

        await page.clickStatusButton('Reopen');

        const reopened = await query<RowDataPacket[]>(`SELECT won_at FROM ${prefix()}erp_crm_deals WHERE id = ?`, [id]);
        expect(reopened[0]!.won_at, 'reopening clears won_at').toBeNull();
    });

    test('a trashed deal is soft-deleted and leaves the board', { tag: ['@tier2', '@pro', '@crm-deals', '@flow'] }, async () => {
        const title = uniqueId('sd');
        const id = await page.createDealFast(title, 2);

        await page.gotoDeal(id);
        await page.clickStatusButton('Trash');

        const rows = await query<RowDataPacket[]>(`SELECT deleted_at FROM ${prefix()}erp_crm_deals WHERE id = ?`, [id]);

        expect(rows, 'the row survives — this is a soft delete').toHaveLength(1);
        expect(rows[0]!.deleted_at, 'with deleted_at stamped').not.toBeNull();

        expect(await page.boardShowsDeal(title), 'and the board no longer shows it').toBe(false);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('a single deal is closed to an employee', { tag: ['@tier3', '@pro', '@crm-deals', '@authz'] }, async ({ browser }) => {
        const title = uniqueId('sd');
        const id = await page.createDealFast(title);

        const result = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new SingleDealPage(p);
            await asEmployee.gotoDeal(id);

            return {
                denied: await asEmployee.isAccessDenied(),
                rendered: await asEmployee.isDealVisible(),
            };
        });

        expect(result.denied, 'an employee is refused').toBe(true);
        expect(result.rendered, 'and the deal never renders').toBe(false);
    });

    test.fail(
        'a CRM agent can delete a competitor without the request fatalling',
        { tag: ['@tier3', '@pro', '@crm-deals', '@known-defect', '@authz'] },
        async ({ browser }) => {
            // KNOWN DEFECT — ERP-044, filed 2026-07-21 against erp-pro 1.6.0 and
            // NEVER POSTED to GitHub. The code is unchanged in 1.7.0:
            // `delete_competitor()` passes `CompetitorModel::where(...)->get()` —
            // an Eloquent Collection — to `is_user_can_delete_competitor()`, which
            // reads `$competitor->created_by`. Admins and CRM managers
            // short-circuit on the line above and never reach it; an agent does,
            // and gets an uncaught exception.
            //
            // The agent here deletes a competitor an ADMIN created, so a refusal
            // would be a legitimate ownership answer — what this asserts is only
            // that the module answers rather than fatalling.
            const title = uniqueId('sd');
            const id = await page.createDealFast(title);

            await page.gotoDeal(id);
            await page.addCompetitor({ name: `${title} Rival` });

            const competitors = await query<RowDataPacket[]>(
                `SELECT id FROM ${prefix()}erp_crm_deals_competitors WHERE deal_id = ?`,
                [id]
            );
            expect(competitors, 'precondition: the competitor was created').toHaveLength(1);

            const response = await withRole(browser, 'crmAgent', async (p) => {
                const asAgent = new SingleDealPage(p);
                await asAgent.goto('all-deals');
                return asAgent.callAjax('erp_deals_delete_competitor', { id: Number(competitors[0]!.id) });
            });

            expect(response.status, 'the request is answered, not fatalled').toBe(200);

            const body = response.body;
            expect(typeof body, 'and the answer is JSON, not an error page').toBe('object');
        }
    );

    test('a note carrying a script payload is never executed', { tag: ['@tier3', '@pro', '@crm-deals', '@xss'] }, async () => {
        const title = uniqueId('sd');
        const id = await page.createDealFast(title);
        const payload = `${title}<img src=x onerror="window.__pwerpXss=1">`;

        await page.gotoDeal(id);
        await page.addNote(payload);

        await page.gotoDeal(id);
        await page.openTimelineTab('Notes');

        expect(await page.rendersInjectedScript('__pwerpXss'), 'the payload never becomes executable markup').toBe(false);
    });
});
