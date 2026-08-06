import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';

/**
 * Feature-isolated page object for the WP ERP Pro **HR Training** module
 * (erp-pro/modules/hrm/hr-training).
 *
 * Unlike most pro HRM modules this one ships NEITHER a /erp/v1 REST controller
 * NOR an admin-ajax data layer: it is a classic WordPress Custom Post Type
 * (`erp_hr_training`) driven entirely through the native WP post editor.
 *   - List screen:   edit.php?post_type=erp_hr_training (WP_List_Table)
 *   - New / edit:    post-new.php?post_type=erp_hr_training (classic editor)
 *   - Persistence:   wp-admin/post.php on Publish → save_post fires
 *                    TrainingPostType::save_training(), which update_post_meta's
 *                    the training fields and wp_redirect's back to the list.
 *
 * The CPT registers with 'query_var' => false and no 'show_in_rest', so it is
 * NOT exposed on /wp/v2 either — every flow here is UI + DB verification.
 *
 * Selectors are taken verbatim from:
 *   - WP core post editor (#post, #titlewrap, #title, #publish, #the-list …)
 *   - TrainingPostType::meta_boxes_cb()  (#erp-hr-training-meta-box body)
 *   - TrainingPostType::set_training_column()  (custom list columns)
 *
 * The CPT (`wp_posts`) + meta (`wp_postmeta`) tables are referenced as string
 * literals built from the same DB_PREFIX dbData.ts uses, so a non-default
 * prefix still resolves.
 */

const PREFIX = process.env.DB_PREFIX ?? 'wp';

export const trainingTables = {
    posts: `${PREFIX}_posts`,
    postMeta: `${PREFIX}_postmeta`,
} as const;

/** Post type slug the module registers (TrainingPostType::erp_training_register_post_types). */
export const TRAINING_POST_TYPE = 'erp_hr_training';

export interface TrainingInput {
    title: string;
    subject?: string;
    frequency?: string;
    description?: string;
    trainingType?: '' | 'all_employee' | 'selected_employee' | 'by_department' | 'by_designation';
    autoAssigned?: boolean;
}

export class TrainingPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URLs (native WP CPT routes, NOT admin.php?page=… and NOT restUrl) ──────
    readonly urls = {
        list: toPath(`wp-admin/edit.php?post_type=${TRAINING_POST_TYPE}`),
        new: toPath(`wp-admin/post-new.php?post_type=${TRAINING_POST_TYPE}`),
    } as const;

    // ── List screen selectors (WP_List_Table + custom columns) ────────────────
    readonly list = {
        body: '#wpbody-content',
        heading: '.wrap h1.wp-heading-inline',
        addNewBtn: 'a.page-title-action',
        form: '#posts-filter',
        table: 'table.wp-list-table.posts',
        rows: '#the-list',
        noItems: 'tr.no-items td.colspanchange',
        // Custom columns from set_training_column().
        colSubject: 'th.column-training_subject',
        colDescription: 'th.column-description',
        colDuration: 'th.column-duration',
        colParticipant: 'th.column-participant',
        participantLink: 'a.training_participant',
    } as const;

    // ── New/edit editor selectors (#post form + #erp-hr-training-meta-box) ─────
    readonly editor = {
        form: '#post',
        titleWrap: '#titlewrap',
        title: '#title',
        titlePrompt: '#title-prompt-text',
        titleRequiredMsg: '#title-required-msj',
        publishBox: '#major-publishing-actions',
        publishBtn: '#publishing-action #publish',
        metaBox: '#erp-hr-training-meta-box',
        metaData: '.wp-erp-training-meta-data',
        subject: '#traning-subject',
        subjectInput: 'input[name="training_subject"]',
        trainingType: '#training_type',
        employees: '#employees',
        departments: '#departments',
        designations: '#designations',
        selectedEmployeeField: '.selected_employee_field',
        byDepartmentField: '.by_department_field',
        byDesignationField: '.by_designation_field',
        frequency: '#erp-training-frequency',
        frequencyInput: 'input[name="training_frequency"]',
        autoAssigned: 'input[name="auto_assigned"]',
        description: '#description',
        descriptionTextarea: 'textarea[name="description"]',
        nonce: 'input#hr_training_meta_action_nonce',
    } as const;

    // ── Navigation ────────────────────────────────────────────────────────────
    async goToList(): Promise<void> {
        await this.page.goto(this.urls.list);
        await expect(this.page.locator(this.list.body)).toBeVisible({ timeout: 30_000 });
    }

    async goToNew(): Promise<void> {
        await this.page.goto(this.urls.new);
        await expect(this.page.locator(this.editor.form)).toBeVisible({ timeout: 30_000 });
        await expect(this.page.locator(this.editor.title)).toBeVisible({ timeout: 30_000 });
    }

    /**
     * Create a training through the real WP post editor: fill the title +
     * metabox fields, Publish, and wait to land back on the CPT list (the
     * save_training() handler wp_redirect's there). Returns the title used.
     *
     * The publish submit triggers a full navigation, so we wait on the list URL
     * rather than an ajax response.
     */
    async createTraining(input: TrainingInput): Promise<string> {
        await this.goToNew();

        await this.page.locator(this.editor.title).fill(input.title);
        if (input.subject !== undefined) {
            await this.page.locator(this.editor.subject).fill(input.subject);
        }
        if (input.frequency !== undefined) {
            await this.page.locator(this.editor.frequency).fill(input.frequency);
        }
        if (input.description !== undefined) {
            await this.page.locator(this.editor.description).fill(input.description);
        }
        if (input.trainingType !== undefined) {
            await this.page.locator(this.editor.trainingType).selectOption(input.trainingType);
        }
        if (input.autoAssigned) {
            await this.page.locator(this.editor.autoAssigned).check();
        }

        await Promise.all([
            this.page.waitForURL(/post_type=erp_hr_training/, { timeout: 45_000 }),
            this.page.locator(this.editor.publishBtn).click(),
        ]);

        return input.title;
    }

    // ── DB helpers (no REST / ajax in the training module) ────────────────────

    /** SHOW TABLES LIKE — length>=1 means the table exists. */
    static async tableExists(table: string): Promise<unknown[]> {
        return dbUtils.dbQuery(`SHOW TABLES LIKE '${table}'`);
    }

    /** Find the CPT post row(s) by exact title (verifies post_type + status). */
    static async findPostByTitle(
        title: string,
    ): Promise<{ ID: number; post_status: string; post_type: string }[]> {
        return dbUtils.dbQuery<{ ID: number; post_status: string; post_type: string }>(
            `SELECT ID, post_status, post_type FROM ${trainingTables.posts}
             WHERE post_title = ? AND post_type = ? ORDER BY ID DESC`,
            [title, TRAINING_POST_TYPE],
        );
    }

    /** Resolve the post id for a created training by its title. */
    static async postIdByTitle(title: string): Promise<number | undefined> {
        const rows = await TrainingPage.findPostByTitle(title);
        return rows[0]?.ID;
    }

    /** Read a single meta value for a post (training_subject, training_frequency, …). */
    static async getMeta(postId: number, key: string): Promise<string | undefined> {
        const rows = await dbUtils.dbQuery<{ meta_value: string }>(
            `SELECT meta_value FROM ${trainingTables.postMeta}
             WHERE post_id = ? AND meta_key = ? LIMIT 1`,
            [postId, key],
        );
        return rows[0]?.meta_value;
    }

    /** All meta rows for a post, as a key→value map (serialized values left raw). */
    static async getAllMeta(postId: number): Promise<Record<string, string>> {
        const rows = await dbUtils.dbQuery<{ meta_key: string; meta_value: string }>(
            `SELECT meta_key, meta_value FROM ${trainingTables.postMeta} WHERE post_id = ?`,
            [postId],
        );
        const map: Record<string, string> = {};
        for (const r of rows) map[r.meta_key] = r.meta_value;
        return map;
    }

    /** Delete the training posts (and their meta) this run created, by title prefix. */
    static async deleteTrainingsLike(prefix: string): Promise<void> {
        const rows = await dbUtils.dbQuery<{ ID: number }>(
            `SELECT ID FROM ${trainingTables.posts}
             WHERE post_type = ? AND post_title LIKE ?`,
            [TRAINING_POST_TYPE, `${prefix}%`],
        );
        for (const r of rows) {
            await dbUtils.dbQuery(`DELETE FROM ${trainingTables.postMeta} WHERE post_id = ?`, [r.ID]);
            await dbUtils.dbQuery(`DELETE FROM ${trainingTables.posts} WHERE ID = ?`, [r.ID]);
        }
    }
}
