import { test, expect } from '@utils/test';
import { DealsPage, seededStages, activityListColumns, dashboardBoxes } from '@pages/crm/dealsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupDeals } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * A seeded demo contact, used read-only as the deal's counterparty. Deals
 * require either a contact or a company, and a fresh site has no CRM companies
 * at all (`seedData.crmCompanies` builds accounting customers, not companies),
 * so a contact is the only counterparty available without creating one.
 */
const SEEDED_CONTACT = 'Elena';

test.describe('CRM — Deals @pro', () => {
    let page: DealsPage;

    test.beforeAll(async () => {
        await cleanupDeals('pwerp_deal');
    });

    test.beforeEach(async ({ page: p }) => {
        page = new DealsPage(p);
    });

    test.afterAll(async () => {
        await cleanupDeals('pwerp_deal');
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the deals board renders every seeded pipeline stage', { tag: ['@tier1', '@pro', '@crm-deals', '@smoke'] }, async () => {
        await page.goto('all-deals');

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);

        const painted = await page.boardStageOrder();

        for (const stage of seededStages) {
            expect(painted, `stage "${stage}" has a column`).toContain(stage);
        }
    });

    test('the deals dashboard renders its statistic boxes and funnel', { tag: ['@tier1', '@pro', '@crm-deals'] }, async () => {
        await page.goto('dashboard');

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
        expect(await page.rendersAll([...dashboardBoxes]), 'every statistic box renders').toEqual([]);

        const funnel = await page.funnelStageOrder();
        expect(funnel, 'the funnel lists a row per stage').toHaveLength(seededStages.length);
    });

    test('the activities screen renders its columns and its empty state', { tag: ['@tier1', '@pro', '@crm-deals'] }, async () => {
        await page.goto('activities');

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
        expect(await page.rendersAll([...activityListColumns]), 'every column header renders').toEqual([]);
        expect(await page.rendersAll(['No activities found']), 'and the empty state, since no deal activity is seeded').toEqual([]);
    });

    test('a deal is created from the board and stored against its contact', { tag: ['@tier1', '@pro', '@crm-deals', '@crud'] }, async () => {
        const title = uniqueId('Deal');

        await page.goto('all-deals');
        const alert = await page.createDeal({ contactSearch: SEEDED_CONTACT, title, value: '4200', stage: 'Contact Made' });

        expect(alert, 'the module confirms through sweetalert').toContain('Deal created successfully');
        await page.dismissAlert();

        const rows = await query<RowDataPacket[]>(
            `SELECT d.title, d.value, d.currency, d.contact_id, d.created_by, d.owner_id, s.title AS stage
               FROM ${prefix()}erp_crm_deals d
               JOIN ${prefix()}erp_crm_deals_pipeline_stages s ON s.id = d.stage_id
              WHERE d.title = ?`,
            [title]
        );

        expect(rows, 'exactly one deal row is written').toHaveLength(1);
        expect(Number(rows[0]!.value), 'the value is stored as entered').toBe(4200);
        expect(rows[0]!.stage, 'in the stage picked in the modal').toBe('Contact Made');
        expect(Number(rows[0]!.contact_id), 'attached to a contact').toBeGreaterThan(0);
        expect(Number(rows[0]!.owner_id), 'and owned by a real user').toBeGreaterThan(0);

        await page.goto('all-deals');
        expect(await page.hasDealOnBoard(title), 'and the board shows it').toBe(true);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('the board paints stage columns in the pipeline order the database holds', { tag: ['@tier2', '@pro', '@crm-deals'] }, async () => {
        // The deterministic half of the stage-order question: whatever `order`
        // says, the board must agree with it. Whether that stored order is the
        // right one is a separate case below.
        const stored = await query<RowDataPacket[]>(
            `SELECT title FROM ${prefix()}erp_crm_deals_pipeline_stages WHERE pipeline_id = 1 ORDER BY \`order\` ASC`
        );

        await page.goto('all-deals');

        expect(await page.boardStageOrder(), 'the board follows the stored order').toEqual(stored.map((row) => String(row.title)));
    });

    test.fail(
        'the default pipeline runs Lead In → … → Negotiations Started',
        { tag: ['@tier2', '@pro', '@crm-deals', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — the shipped seed in `modules/crm/deals/table-data.php`
            // gives "Proposal Made" `order = 0` while Lead In..Negotiations Started
            // get 1,2,3,4. Every fresh install therefore opens its sales funnel at
            // the fourth stage.
            //
            // Precondition first: `test.fail()` reports a PASS on ANY failure, so
            // without this a missing pipeline would look like the defect.
            const stages = await query<RowDataPacket[]>(
                `SELECT title FROM ${prefix()}erp_crm_deals_pipeline_stages WHERE pipeline_id = 1`
            );
            expect(stages, 'precondition: the default pipeline has its five seeded stages').toHaveLength(5);

            await page.goto('all-deals');

            expect(await page.boardStageOrder(), 'the funnel runs in sales order').toEqual([...seededStages]);
        }
    );

    test('a new deal opens at the first stage of the pipeline', { tag: ['@tier2', '@pro', '@crm-deals'] }, async () => {
        // The canary for the case above: it asserts what the product DOES, so a
        // broken modal cannot make the known-defect guard pass for free.
        await page.goto('all-deals');
        await page.openNewDealModal();

        const order = await page.modalStageOrder();

        expect(order, 'the modal offers every stage').toHaveLength(seededStages.length);
        expect(await page.defaultStage(), 'and pre-selects the first one').toBe(order[0]);
    });

    test('saving a deal writes its stage history', { tag: ['@tier2', '@pro', '@crm-deals'] }, async () => {
        const title = uniqueId('Deal');

        await page.goto('all-deals');
        await page.createDeal({ contactSearch: SEEDED_CONTACT, title, value: '900', stage: 'Lead In' });
        await page.dismissAlert();

        const history = await query<RowDataPacket[]>(
            `SELECT s.title AS stage, h.in_amount
               FROM ${prefix()}erp_crm_deals_stage_history h
               JOIN ${prefix()}erp_crm_deals d ON d.id = h.deal_id
               JOIN ${prefix()}erp_crm_deals_pipeline_stages s ON s.id = h.stage_id
              WHERE d.title = ?
              ORDER BY h.id`,
            [title]
        );

        expect(history.length, 'the deal records stage history').toBeGreaterThan(0);
        expect(history.map((row) => String(row.stage)), 'ending at the stage the deal was created in').toContain('Lead In');
    });

    test.fail(
        'a deal records only the stages it has actually passed through',
        { tag: ['@tier2', '@pro', '@crm-deals', '@known-defect'] },
        async () => {
            // KNOWN DEFECT, and the one that turns the stage-order fault above
            // from cosmetic into wrong data: `Deals::save_deal()` walks the
            // pipeline in `order` sequence and writes a history row for every
            // stage up to the deal's own. Because the seed gives "Proposal Made"
            // `order = 0`, a deal created at "Lead In" is recorded as having
            // already reached Proposal Made — the fourth stage of the funnel.
            //
            // `deals_progress_by_stages()` (Statistics.php:197) counts deals per
            // stage straight out of this table, so the dashboard funnel inherits
            // the error.
            //
            // Related but NOT the same defect: ERP-043 / the unscoped
            // `PipelineStageModel::get()` that leaks stages from OTHER pipelines.
            // This one stays wrong even with a single pipeline.
            const title = uniqueId('Deal');

            await page.goto('all-deals');
            await page.createDeal({ contactSearch: SEEDED_CONTACT, title, value: '500', stage: 'Lead In' });
            await page.dismissAlert();

            const history = await query<RowDataPacket[]>(
                `SELECT s.title AS stage
                   FROM ${prefix()}erp_crm_deals_stage_history h
                   JOIN ${prefix()}erp_crm_deals d ON d.id = h.deal_id
                   JOIN ${prefix()}erp_crm_deals_pipeline_stages s ON s.id = h.stage_id
                  WHERE d.title = ?`,
                [title]
            );

            expect(history.length, 'precondition: the deal recorded some history at all').toBeGreaterThan(0);

            const reached = history.map((row) => String(row.stage));
            const ahead = seededStages.slice(seededStages.indexOf('Lead In') + 1);

            for (const stage of ahead) {
                expect(reached, `a deal at Lead In has not reached "${stage}"`).not.toContain(stage);
            }
        }
    );

    test('the board offers its status and owner filters', { tag: ['@tier2', '@pro', '@crm-deals'] }, async () => {
        await page.goto('all-deals');

        expect(await page.filterLabels(), 'both filters render with their defaults').toEqual(['Open deals', 'All Owners']);
        expect(await page.statusFilterOptions(), 'and the status filter offers every deal state').toEqual([
            'Open deals',
            'Won deals',
            'Lost deals',
            'Trashed deals',
        ]);
    });

    test('the pipeline switcher is hidden while only one pipeline exists', { tag: ['@tier2', '@pro', '@crm-deals'] }, async () => {
        const pipelines = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_crm_deals_pipelines`);

        test.skip(pipelines.length !== 1, `this site has ${pipelines.length} pipelines, not the seeded one`);

        await page.goto('all-deals');

        expect(await page.hasPipelineFilter(), 'nothing to switch between, so no switcher').toBe(false);
    });

    test('ERP Settings lists the same stages as the board', { tag: ['@tier2', '@pro', '@crm-deals', '@settings'] }, async () => {
        await page.gotoSettings();
        const inSettings = await page.settingsStageOrder();

        await page.goto('all-deals');
        const onBoard = await page.boardStageOrder();

        expect(inSettings, 'settings and board agree').toEqual(onBoard);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('a deal with no contact and no company is refused', { tag: ['@tier3', '@pro', '@crm-deals', '@validation'] }, async () => {
        const title = uniqueId('Deal');

        await page.goto('all-deals');
        await page.openNewDealModal();
        await page.fillDealTitle(title);
        await page.save();

        // The refusal is the CLIENT's: `saveData()` sets `input-error` and
        // returns before any AJAX. The server carries the same rule ("Either
        // contact or company name is required") but is never asked, so claiming
        // the product refused it would overstate what was tested.
        expect(await page.isModalStillOpen(), 'the modal stays open').toBe(true);
        expect(await page.invalidFieldCount(), 'and the counterparty fields are marked invalid').toBeGreaterThan(0);

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_crm_deals WHERE title = ?`, [title]);
        expect(rows, 'nothing is written').toHaveLength(0);
    });

    test('the deal title is auto-filled from the contact', { tag: ['@tier2', '@pro', '@crm-deals'] }, async () => {
        // There is no such thing as an untitled deal from this modal: picking a
        // counterparty fills the title with "<company or name> deal". The
        // originally planned case — "a deal with no title is refused" — does not
        // exist as behaviour, and asserting it would have been asserting a rule
        // the product does not have.
        await page.goto('all-deals');
        await page.openNewDealModal();

        expect(await page.dealTitleValue(), 'the box starts empty').toBe('');
        expect(await page.selectContact(SEEDED_CONTACT), 'precondition: the contact search works').toBe(true);

        expect(await page.dealTitleValue(), 'and is filled from the contact once one is picked').toMatch(/ deal$/);
    });

    test.fail(
        'a title typed before the contact is picked survives',
        { tag: ['@tier2', '@pro', '@crm-deals', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — the auto-fill above is a plain watcher on the
            // selected contact (`new-deal-modal/index.js:325`) with no "only if
            // empty" guard, so it overwrites a title the user has already typed.
            // Reproduced 3/3.
            //
            // Precondition first: `test.fail()` reports a PASS on ANY failure, so
            // a broken contact search would otherwise look like the defect.
            const typed = uniqueId('Deal');

            await page.goto('all-deals');
            await page.openNewDealModal();
            await page.fillDealTitle(typed);

            expect(await page.dealTitleValue(), 'precondition: the typed title lands in the box').toBe(typed);
            expect(await page.selectContact(SEEDED_CONTACT), 'precondition: the contact search works').toBe(true);

            expect(await page.dealTitleValue(), 'the typed title is kept').toBe(typed);
        }
    );

    test('the server refuses a deal with an empty title', { tag: ['@tier3', '@pro', '@crm-deals', '@validation'] }, async () => {
        // Reachable only through the AJAX endpoint: the modal always supplies a
        // title of its own, so the server-side rule has no UI path.
        await page.goto('all-deals');

        const contacts = await query<RowDataPacket[]>(
            `SELECT p.id FROM ${prefix()}erp_peoples p
               JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
               JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
              WHERE t.name = 'contact' LIMIT 1`
        );
        expect(contacts, 'precondition: a CRM contact exists to attach').toHaveLength(1);

        const before = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_crm_deals`);

        const response = await page.callAjax('erp_deals_save_deal', {
            deal: { title: '', contact_id: Number(contacts[0]!.id), stage_id: 1 },
        });

        const body = response.body as { success?: boolean; data?: { msg?: string } };

        expect(body.success, 'the save is refused').toBe(false);
        expect(body.data?.msg ?? '', 'and says why').toMatch(/title is required/i);

        const after = await query<RowDataPacket[]>(`SELECT COUNT(*) AS n FROM ${prefix()}erp_crm_deals`);
        expect(Number(after[0]!.n), 'and no deal is created').toBe(Number(before[0]!.n));
    });

    test('the server refuses a deal whose stage does not exist', { tag: ['@tier3', '@pro', '@crm-deals', '@validation'] }, async () => {
        // Straight at `erp_deals_save_deal`, bypassing the modal — the client
        // never offers an invalid stage, so this rule can only be reached
        // through the AJAX endpoint the module exposes.
        const title = uniqueId('Deal');

        await page.goto('all-deals');

        const contacts = await query<RowDataPacket[]>(
            `SELECT p.id FROM ${prefix()}erp_peoples p
               JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
               JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
              WHERE t.name = 'contact' LIMIT 1`
        );
        expect(contacts, 'precondition: a CRM contact exists to attach').toHaveLength(1);

        const response = await page.callAjax('erp_deals_save_deal', {
            deal: { title, contact_id: Number(contacts[0]!.id), stage_id: 999999 },
        });

        const body = response.body as { success?: boolean; data?: { msg?: string } };

        expect(body.success, 'the save is refused').toBe(false);
        expect(body.data?.msg ?? '', 'and says why').toMatch(/pipline stage|pipeline stage/i);

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_crm_deals WHERE title = ?`, [title]);
        expect(rows, 'nothing is written').toHaveLength(0);
    });

    test('deals are closed to an employee', { tag: ['@tier3', '@pro', '@crm-deals', '@authz'] }, async ({ browser }) => {
        const result = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new DealsPage(p);
            await asEmployee.goto('all-deals');

            return {
                denied: await asEmployee.isAccessDenied(),
                // The module's nonce is localized only on its own screens. An
                // employee who cannot load one cannot call any of the 41
                // `erp_deals_*` handlers, none of which check a capability.
                globals: await asEmployee.localizedGlobals(),
            };
        });

        expect(result.denied, 'an employee cannot reach the deals board').toBe(true);
        expect(result.globals, 'and is never handed the module nonce').toBeNull();
    });

    test('pipeline administration is closed to a CRM agent', { tag: ['@tier3', '@pro', '@crm-deals', '@authz'] }, async ({ browser }) => {
        const refusal = await withRole(browser, 'crmAgent', async (p) => {
            const asAgent = new DealsPage(p);
            await asAgent.goto('all-deals');

            // An agent DOES get the board and therefore a valid module nonce.
            // What stops them is the `manage_options` check inside the pipeline
            // handlers — so this asserts the handler's own guard, holding the
            // nonce, rather than the menu's.
            const globals = await asAgent.localizedGlobals();
            const save = await asAgent.callAjax('erp_deals_save_stage', { stage: { title: 'pwerp-agent-stage', pipeline_id: 1 } });
            const remove = await asAgent.callAjax('erp_deals_delete_pipeline', { id: 1 });

            return { globals, save, remove };
        });

        expect(refusal.globals, 'precondition: the agent really does hold a module nonce').not.toBeNull();

        for (const [label, response] of [
            ['save_stage', refusal.save],
            ['delete_pipeline', refusal.remove],
        ] as const) {
            const body = response.body as { success?: boolean };
            expect(body.success, `${label} is refused for an agent`).toBe(false);
        }

        const stages = await query<RowDataPacket[]>(
            `SELECT id FROM ${prefix()}erp_crm_deals_pipeline_stages WHERE title = 'pwerp-agent-stage'`
        );
        expect(stages, 'and no stage is created').toHaveLength(0);

        const pipelines = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_crm_deals_pipelines WHERE id = 1`);
        expect(pipelines, 'and the pipeline still exists').toHaveLength(1);
    });

    test('a deal title carrying a script payload is never executed', { tag: ['@tier3', '@pro', '@crm-deals', '@xss'] }, async () => {
        const marker = uniqueId('Deal');
        const payload = `${marker}<img src=x onerror="window.__pwerpXss=1">`;

        await page.goto('all-deals');
        await page.createDeal({ contactSearch: SEEDED_CONTACT, title: payload });
        await page.dismissAlert();

        await page.goto('all-deals');

        expect(await page.rendersInjectedScript('__pwerpXss'), 'the payload never becomes executable markup').toBe(false);
    });
});
