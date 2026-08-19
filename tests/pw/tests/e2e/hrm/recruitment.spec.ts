import { test, expect } from '@utils/test';
import { RecruitmentPage, jobOpeningColumns, candidateColumns, stageColumns, defaultStages, type RecruitmentScreen } from '@pages/hrm/recruitmentPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupJobOpenings } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Recruitment @pro', () => {
    let page: RecruitmentPage;

    test.beforeAll(async () => {
        await cleanupJobOpenings();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new RecruitmentPage(p);
    });

    test.afterAll(async () => {
        await cleanupJobOpenings();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    for (const screen of ['jobOpening', 'addOpening', 'candidates', 'addCandidate', 'stages', 'calendar', 'reports', 'aiSettings'] as RecruitmentScreen[]) {
        test(`the ${screen} screen loads`, { tag: ['@tier1', '@hrm-recruitment', '@smoke'] }, async () => {
            await page.goto(screen);

            expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
            await page.expectHeading('HR');
        });
    }

    for (const [screen, columns] of [
        ['jobOpening', jobOpeningColumns],
        ['candidates', candidateColumns],
        ['stages', stageColumns],
    ] as [RecruitmentScreen, readonly string[]][]) {
        test(`the ${screen} list renders its captured columns`, { tag: ['@tier1', '@hrm-recruitment'] }, async () => {
            await page.goto(screen);

            const headers = await page.columnHeaders();

            for (const column of columns) {
                expect(headers, `column "${column}"`).toContain(column);
            }
        });
    }

    test('the module installs its four default hiring stages', { tag: ['@tier1', '@hrm-recruitment'] }, async () => {
        await page.goto('stages');

        const rows = (await page.rowTexts()).join(' | ');

        for (const stage of defaultStages) {
            expect(rows, `stage "${stage}"`).toContain(stage);
        }
    });

    test('the first wizard step publishes the opening as a post', { tag: ['@tier1', '@hrm-recruitment', '@crud'] }, async () => {
        const title = uniqueId('pwerpJob');

        await page.createOpening(title);

        // A job opening is a WordPress post, not an ERP table row.
        const posts = await query<RowDataPacket[]>(`SELECT ID, post_type, post_status FROM ${prefix()}posts WHERE post_title = ?`, [title]);

        expect(posts, 'exactly one post is created').toHaveLength(1);
        expect(posts[0]!.post_type, 'stored as the recruitment post type').toBe('erp_hr_recruitment');
        expect(posts[0]!.post_status, 'published straight away').toBe('publish');
        expect(await page.isOnWizardStep('hiring_workflow'), 'the wizard advances to the hiring workflow').toBe(true);
    });

    test('an opening abandoned at step one is published but not listed', { tag: ['@tier2', '@hrm-recruitment', '@edge'] }, async () => {
        // Step one publishes the post, but the Job Opening list INNER JOINs
        // postmeta on `_expire_date` (`functions-recruitment.php:604`), and that
        // meta is only written by a LATER wizard step. So an opening abandoned
        // here is a live published post that the admin screen never shows.
        // Asserted as the product behaves; recorded in COVERAGE.md, not filed —
        // it needs a product decision on whether step one should publish at all.
        const title = uniqueId('pwerpJob');

        await page.createOpening(title);

        const posts = await query<RowDataPacket[]>(`SELECT ID FROM ${prefix()}posts WHERE post_title = ? AND post_status = 'publish'`, [title]);
        const meta = await query<RowDataPacket[]>(`SELECT meta_id FROM ${prefix()}postmeta WHERE post_id = ? AND meta_key = '_expire_date'`, [Number(posts[0]!.ID)]);

        expect(posts, 'the post is published').toHaveLength(1);
        expect(meta, 'no expiry meta is written at step one').toHaveLength(0);

        await page.goto('jobOpening');
        expect(await page.hasRowFor(title), 'and so it is absent from the list').toBe(false);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('the wizard cannot be advanced without a job title', { tag: ['@tier2', '@hrm-recruitment', '@validation'] }, async () => {
        await page.goto('addOpening');

        expect(await page.isNextEnabled(), 'Next starts disabled on an empty form').toBe(false);

        await page.typeOpeningTitle(uniqueId('pwerpJob'));

        expect(await page.isNextEnabled(), 'typing a title enables Next').toBe(true);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('the AI settings screen renders its key field without calling out', { tag: ['@tier3', '@hrm-recruitment', '@needs-external'] }, async () => {
        // The screen is LOADED only. No generation is triggered anywhere in this
        // suite: that would be a real outbound request to a paid Gemini endpoint,
        // and the test mu-plugin blocks only wordpress.org.
        await page.goto('aiSettings');

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
        expect(await page.hasAiKeyField(), 'the API key field is offered').toBe(true);
    });

    test('Recruitment is closed to an employee', { tag: ['@tier3', '@hrm-recruitment', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new RecruitmentPage(p);
            await asEmployee.goto('jobOpening');
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the Job Opening screen').toBe(true);
    });
});
