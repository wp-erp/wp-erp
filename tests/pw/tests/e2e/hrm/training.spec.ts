import { test, expect } from '@utils/test';
import { TrainingPage, trainingColumns } from '@pages/hrm/trainingPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupTrainings } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Training @pro', () => {
    let page: TrainingPage;

    test.beforeAll(async () => {
        await cleanupTrainings();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new TrainingPage(p);
    });

    test.afterAll(async () => {
        await cleanupTrainings();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the training list renders its captured columns', { tag: ['@tier1', '@hrm-training', '@smoke'] }, async () => {
        await page.goto();

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
        await page.expectHeading('Training');

        const headers = await page.columnHeaders();

        for (const column of trainingColumns) {
            expect(headers, `column "${column}"`).toContain(column);
        }
    });

    test('a training can be created and is listed with its subject', { tag: ['@tier1', '@hrm-training', '@crud'] }, async () => {
        const title = uniqueId('pwerpTraining');
        const subject = 'Workplace safety';

        await page.create({ title, subject });

        const posts = await query<RowDataPacket[]>(`SELECT ID, post_type, post_status FROM ${prefix()}posts WHERE post_title = ?`, [title]);

        expect(posts, 'exactly one training post is created').toHaveLength(1);
        expect(posts[0]!.post_type, 'stored as the training post type').toBe('erp_hr_training');

        // The module's own fields live in postmeta, not a table.
        const meta = await query<RowDataPacket[]>(`SELECT meta_value FROM ${prefix()}postmeta WHERE post_id = ? AND meta_key = 'training_subject'`, [Number(posts[0]!.ID)]);

        expect(meta, 'the subject is stored as post meta').toHaveLength(1);
        expect(meta[0]!.meta_value, 'the subject entered is the subject stored').toBe(subject);

        await page.goto();
        expect(await page.hasRowFor(title), 'the training is listed').toBe(true);
        expect(await page.hasRowFor(subject), 'the list shows its subject column').toBe(true);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('Training is closed to an employee', { tag: ['@tier3', '@hrm-training', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new TrainingPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the Training list').toBe(true);
    });

    test('the training headcount endpoint requires a capability', { tag: ['@tier3', '@hrm-training', '@authz'] }, async ({ browser }) => {
        // KNOWN GAP — `erp_training_employee_count` is the one training AJAX
        // action with NEITHER a capability check NOR a nonce (`Ajax.php:28`); the
        // other four check `erp_list_employee`. It answers any logged-in user
        // with a headcount for the departments asked about.
        //
        // Deliberately NOT filed: the response is a bare count with no names, no
        // e-mails and no PII, so the disclosure is a departmental headcount. It
        // is carried here so it flips the moment a check is added — or the moment
        // the endpoint starts returning more than a number.
        test.fail();

        const response = await withRole(browser, 'employee', async (p) => {
            return new TrainingPage(p).postAjax('erp_training_employee_count', { 'departments[]': '1' });
        });

        expect(response.body, 'an employee is refused the headcount').not.toMatch(/"success"\s*:\s*true/);
    });
});
