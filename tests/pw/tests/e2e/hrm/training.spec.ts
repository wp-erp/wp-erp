import { test, expect } from '@utils/test';
import { TrainingPage, trainingTables, TRAINING_POST_TYPE } from './trainingPage';
import { dbUtils } from '@utils/dbUtils';
import { data, TEST_PREFIX } from '@utils/testData';

/**
 * WP ERP Pro — HRM **Training** (module: hr_training).
 *
 * The module is a classic WordPress Custom Post Type (`erp_hr_training`) — it
 * ships NO /erp/v1 REST and NO admin-ajax data layer. Everything (list, create,
 * save) flows through the native WP post editor:
 *   - List:   wp-admin/edit.php?post_type=erp_hr_training (WP_List_Table with
 *             custom columns from TrainingPostType::set_training_column()).
 *   - Create: wp-admin/post-new.php?post_type=erp_hr_training (classic editor +
 *             the #erp-hr-training-meta-box metabox).
 *   - Save:   Publish POSTs to wp-admin/post.php; save_post fires
 *             TrainingPostType::save_training() which (after a cap +
 *             hr_training_meta_action nonce check) update_post_meta's the fields
 *             and wp_redirect's back to the list (line 343).
 *
 * So this spec is UI smoke + DB verification (no REST). Resilient-assertion
 * philosophy (test-plans/_pro-grounding.md):
 *   - UI: assert NOT the WP fatal banner AND the real mount is visible.
 *   - The empty-title save is blocked client-side (force_post_title JS), so we
 *     assert the boundary (msg shown OR no navigation), not an exact status.
 *
 * Every test carries: tier (@pro) + module (@hrm) + role (@admin/@manager/@employee).
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

// A run-unique title prefix so created CPT rows never collide across parallel
// workers / reruns, and so afterAll can clean them up by LIKE.
const RUN = `${TEST_PREFIX}PW Training`;
const uniqueTitle = (): string => `${RUN} ${Date.now()}${Math.floor(Math.random() * 1e4)}`;

test.afterAll(async () => {
    // Best-effort cleanup of every training this spec published (UI flows commit
    // real wp_posts/wp_postmeta rows). The shared dbUtils pool may already be
    // closed by a sibling afterAll; it transparently rebuilds, so swallow errors.
    try {
        await TrainingPage.deleteTrainingsLike(TEST_PREFIX);
    } catch {
        /* best-effort cleanup */
    }
    try {
        await dbUtils.close();
    } catch {
        /* pool may already be closed by a sibling spec */
    }
});

// ──────────────────────────────────────────────────────────────────────────
// Admin — list screen + create flow through the native WP post editor
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Training CPT (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // TRN-UI-01
    test('Training CPT list page loads with the Add New control and no PHP fatal', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await page.goto(training.urls.list);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(training.list.body)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator(training.list.heading)).toBeVisible();
        // "Create Training" / "Add New Training" page-title action.
        await expect(page.locator(training.list.addNewBtn).first()).toBeVisible();
        await expect(page.locator('#wpadminbar')).toBeVisible();
    });

    // TRN-UI-02
    test('list table shows the pro custom columns and drops the default Date column', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await training.goToList();
        await expect(page.locator(training.list.table)).toBeVisible({ timeout: 30_000 });

        // Custom columns from set_training_column(). WP_List_Table prints each
        // column header in BOTH thead and tfoot, so every th.column-* legitimately
        // appears twice — assert the first header is visible (and the pair count).
        await expect(page.locator(training.list.colSubject).first()).toBeVisible();
        await expect(page.locator(training.list.colSubject)).toHaveCount(2);
        await expect(page.locator(training.list.colDescription).first()).toBeVisible();
        await expect(page.locator(training.list.colDuration).first()).toBeVisible();
        await expect(page.locator(training.list.colParticipant).first()).toBeVisible();

        const header = page.locator(`${training.list.table} thead`);
        await expect(header).toContainText(/Training Subject/i);
        await expect(header).toContainText(/Description/i);
        await expect(header).toContainText(/Duration/i);
        await expect(header).toContainText(/Participant/i);

        // unset( $column['date'] ) — the default Date column must be absent.
        await expect(page.locator(`${training.list.table} th.column-date`)).toHaveCount(0);
    });

    // TRN-UI-03
    test('new-training editor mounts with title field, HR Training Options metabox and nonce', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await page.goto(training.urls.new);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        await expect(page.locator(training.editor.form)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator(training.editor.title)).toBeVisible();
        await expect(page.locator(training.editor.metaBox)).toBeVisible();

        // Metabox body fields (TrainingPostType::meta_boxes_cb).
        await expect(page.locator(training.editor.subject)).toHaveCount(1);
        await expect(page.locator(training.editor.trainingType)).toHaveCount(1);
        await expect(page.locator(training.editor.frequency)).toHaveCount(1);
        await expect(page.locator(training.editor.description)).toHaveCount(1);

        // Hidden security nonce required by save_training() (wp_nonce_field). The
        // metabox wp_nonce_field is printed twice in this build, so the input
        // legitimately appears twice — assert it is attached, not exactly one.
        await expect(page.locator(training.editor.nonce).first()).toBeAttached();
        await expect(page.locator(training.editor.nonce)).toHaveCount(2);
    });

    // TRN-UI-04
    test('create a training via the WP post editor and assert it appears in the list', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        const title = uniqueTitle();

        await training.createTraining({
            title,
            subject: `PW Safety Skill ${Date.now()}`,
            frequency: '3 days',
            description: 'pw seeded training',
        });

        // save_training() wp_redirect's to edit.php?post_type=erp_hr_training (line 343).
        await expect(page).toHaveURL(/post_type=erp_hr_training/);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        // The new row is visible in the WP_List_Table body.
        await expect(page.locator(training.list.rows)).toContainText(title, { timeout: 30_000 });
    });

    // TRN-UI-05
    test('created training meta persists in wp_postmeta (subject + frequency)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        const title = uniqueTitle();
        const subject = `PW Subject ${Date.now()}`;
        const frequency = '5 days';

        await training.createTraining({ title, subject, frequency, description: 'pw meta check' });
        await expect(page).toHaveURL(/post_type=erp_hr_training/);

        // Resolve the post id by title (poll: the redirect lands a beat before the
        // row is fully visible to a fresh query under parallel load).
        await expect
            .poll(async () => await TrainingPage.postIdByTitle(title), { timeout: 20_000 })
            .toBeTruthy();

        const postId = await TrainingPage.postIdByTitle(title);
        expect(postId, 'created training has a post id').toBeTruthy();

        // update_post_meta loop (save_training lines 319-321) wrote the fields.
        expect(await TrainingPage.getMeta(postId!, 'training_subject')).toBe(subject);
        expect(await TrainingPage.getMeta(postId!, 'training_frequency')).toBe(frequency);
        expect(await TrainingPage.getMeta(postId!, 'description')).toBe('pw meta check');
    });

    // TRN-UI-06
    test('created training row exists in wp_posts with the correct post_type/status', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        const title = uniqueTitle();

        await training.createTraining({ title, subject: 'row check', frequency: '1 day' });
        await expect(page).toHaveURL(/post_type=erp_hr_training/);

        await expect
            .poll(async () => (await TrainingPage.findPostByTitle(title)).length, { timeout: 20_000 })
            .toBeGreaterThanOrEqual(1);

        const rows = await TrainingPage.findPostByTitle(title);
        expect(rows.length, 'exactly one CPT row for the unique title').toBe(1);
        expect(rows[0]!.post_type).toBe(TRAINING_POST_TYPE);
        expect(rows[0]!.post_status).toBe('publish');
    });

    // TRN-UI-07
    test('set Assign To = All Employees and save without error; training_type meta persists', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        const title = uniqueTitle();

        await training.createTraining({
            title,
            subject: 'all-employee training',
            frequency: '2 days',
            trainingType: 'all_employee',
        });

        await expect(page).toHaveURL(/post_type=erp_hr_training/);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        await expect
            .poll(async () => await TrainingPage.postIdByTitle(title), { timeout: 20_000 })
            .toBeTruthy();
        const postId = await TrainingPage.postIdByTitle(title);

        // assign_training_to_employees() writes training_type via update_post_meta (line 406).
        expect(await TrainingPage.getMeta(postId!, 'training_type')).toBe('all_employee');
    });

    // TRN-UI-08
    test('Assign To select offers the documented options', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await training.goToNew();

        const values = await page.locator(`${training.editor.trainingType} option`).evaluateAll(
            opts => opts.map(o => (o as HTMLOptionElement).value),
        );

        // assign_type map (meta_boxes_cb lines 125-131).
        for (const expected of ['', 'all_employee', 'selected_employee', 'by_department', 'by_designation']) {
            expect(values, `Assign To should offer "${expected || '(placeholder)'}"`).toContain(expected);
        }
    });

    // TRN-UI-09
    test('empty-title save is blocked by client-side required validation', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await training.goToNew();
        const startUrl = page.url();

        // Leave #title empty; force_post_title JS (lines 638-657) must stop submit.
        await page.locator(training.editor.publishBtn).click();

        // The boundary: either the "Title is required." message appears OR we never
        // navigated to the list. Assert resiliently (no exact status / no fatal).
        const requiredMsg = page.locator(training.editor.titleRequiredMsg);
        const blocked = await requiredMsg
            .waitFor({ state: 'visible', timeout: 8_000 })
            .then(() => true)
            .catch(() => false);

        if (!blocked) {
            // Fallback: confirm we did NOT get redirected to the CPT list.
            expect(page.url(), 'empty-title submit did not navigate to the list').toBe(startUrl);
        } else {
            await expect(requiredMsg).toContainText(/Title is required/i);
        }

        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        // No empty-title training was created in this run.
        const blanks = await dbUtils.dbQuery<{ ID: number }>(
            `SELECT ID FROM ${trainingTables.posts}
             WHERE post_type = ? AND post_status = 'publish' AND TRIM(post_title) = ''`,
            [TRAINING_POST_TYPE],
        );
        expect(blanks.length, 'no blank-title training was published').toBe(0);
    });

    // TRN-UI-10
    test("selecting 'Selected Employee' reveals the employees select and hides the others", { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await training.goToNew();

        await page.locator(training.editor.trainingType).selectOption('selected_employee');

        // toggle JS (meta_boxes_cb lines 220-249) shows only the matching field.
        await expect(page.locator(training.editor.selectedEmployeeField)).toBeVisible({ timeout: 15_000 });
        await expect(page.locator(training.editor.employees)).toHaveCount(1);
        await expect(page.locator(training.editor.byDepartmentField)).toBeHidden();
        await expect(page.locator(training.editor.byDesignationField)).toBeHidden();
    });

    // TRN-UI-11
    test('auto-assign checkbox persists when checked', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        const title = uniqueTitle();

        await training.createTraining({
            title,
            subject: 'auto-assign training',
            frequency: '7 days',
            autoAssigned: true,
        });

        await expect(page).toHaveURL(/post_type=erp_hr_training/);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        await expect
            .poll(async () => await TrainingPage.postIdByTitle(title), { timeout: 20_000 })
            .toBeTruthy();
        const postId = await TrainingPage.postIdByTitle(title);

        // save_training() stores the checkbox value 'yes' (lines 316, 319-321).
        expect(await TrainingPage.getMeta(postId!, 'auto_assigned')).toBe('yes');
    });

    // TRN-UI-12 — additional edge: 'By Department' reveals the departments select.
    test("selecting 'By Department' reveals the departments select and hides the others", { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await training.goToNew();

        await page.locator(training.editor.trainingType).selectOption('by_department');

        await expect(page.locator(training.editor.byDepartmentField)).toBeVisible({ timeout: 15_000 });
        await expect(page.locator(training.editor.departments)).toHaveCount(1);
        await expect(page.locator(training.editor.selectedEmployeeField)).toBeHidden();
        await expect(page.locator(training.editor.byDesignationField)).toBeHidden();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Admin — DB layer: the CPT/meta tables exist and back the storage contract
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Training DB (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // TRN-DB-01
    test('the wp_posts and wp_postmeta tables backing the CPT exist', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        for (const table of Object.values(trainingTables)) {
            const rows = await TrainingPage.tableExists(table);
            expect(rows.length, `${table} should exist`).toBeGreaterThanOrEqual(1);
        }
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Manager — erp_hr_manager owns every training post cap (capabilities map),
// so a HR manager reaches the list and the editor too.
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Training (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    test('HR manager can reach the Training list with the Add New control', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await page.goto(training.urls.list);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(training.list.body)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator(training.list.addNewBtn).first()).toBeVisible();
    });

    test('HR manager can open the new-training editor with the metabox', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await page.goto(training.urls.new);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(training.editor.form)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator(training.editor.metaBox)).toBeVisible();
        // The metabox nonce is printed twice in this build (see TRN-UI-03).
        await expect(page.locator(training.editor.nonce).first()).toBeAttached();
        await expect(page.locator(training.editor.nonce)).toHaveCount(2);
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Negative — a plain employee lacks erp_hr_manager. The CPT maps every post cap
// to erp_hr_manager (register lines 54-64), so WP gates the screen.
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Training access control (pro, employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('employee cannot reach the Training list screen', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await page.goto(training.urls.list);
        // WP renders "Sorry, you are not allowed to access this page." for a user
        // without erp_hr_manager. Assert the boundary, not an exact status, and
        // never a PHP fatal: the list table must NOT mount.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(training.list.table)).toHaveCount(0);
        await expect(page.locator('body')).toContainText(/not allowed to access this page|Cheatin/i);
    });

    test('employee cannot open the new-training editor', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const training = new TrainingPage(page);
        await page.goto(training.urls.new);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The classic editor form for this CPT must not mount for an employee.
        await expect(page.locator(training.editor.metaBox)).toHaveCount(0);
        await expect(page.locator('body')).toContainText(/not allowed|Cheatin/i);
    });
});
