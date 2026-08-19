import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Training, the erp-pro hr-training module.
 *
 * Not an ERP screen at all: trainings are a WordPress custom post type
 * (`erp_hr_training`) edited in the CLASSIC editor, listed by `edit.php`, with
 * the module's own fields in an "HR Training Options" metabox. Assignments are
 * stored in meta rather than a table — `erp_employee_training` on the user, and
 * `erp_training_completed_employee` / `erp_training_incompleted_employee` on the
 * training post. The module ships no tables of its own.
 */
export const trainingSelectors = {
    list: 'table.wp-list-table',
    row: 'table.wp-list-table tbody tr',
    title: '#title',
    // Anchored on NAME, not id: the product's id is `traning-subject`
    // (misspelled) while the posted name is `training_subject`.
    subject: '[name="training_subject"]',
    type: '[name="training_type"]',
    frequency: '[name="training_frequency"]',
    publish: '#publish',
} as const;

/** Columns the list renders, captured live. */
export const trainingColumns = ['Title', 'Training Subject', 'Description', 'Duration', 'Participants'] as const;

export interface TrainingInput {
    title: string;
    subject?: string;
}

export class TrainingPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoUrl('/wp-admin/edit.php?post_type=erp_hr_training');
        await this.page.waitForTimeout(1200);
    }

    async gotoNew(): Promise<void> {
        await this.gotoUrl('/wp-admin/post-new.php?post_type=erp_hr_training');
        await this.page.locator(trainingSelectors.title).waitFor({ state: 'visible', timeout: 15_000 });
    }

    async columnHeaders(): Promise<string[]> {
        return [
            ...new Set(
                (await this.page.locator(`${trainingSelectors.list} th`).allTextContents())
                    .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
                    .filter(Boolean)
            ),
        ];
    }

    async rowTexts(): Promise<string[]> {
        return (await this.page.locator(trainingSelectors.row).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasRowFor(text: string): Promise<boolean> {
        return (await this.rowTexts()).some((row) => row.includes(text));
    }

    get publishButton(): Locator {
        return this.page.locator(trainingSelectors.publish);
    }

    /** Creates a training through the classic editor and waits for the save. */
    async create(input: TrainingInput): Promise<void> {
        await this.gotoNew();
        await this.page.locator(trainingSelectors.title).fill(input.title);

        if (input.subject) {
            await this.page.locator(trainingSelectors.subject).fill(input.subject);
        }

        await this.publishButton.click();
        await this.page.waitForLoadState('domcontentloaded');
        await this.page.waitForTimeout(1500);
    }

    /** Posts a training AJAX action with the current session. */
    async postAjax(action: string, form: Record<string, string> = {}): Promise<{ status: number; body: string }> {
        const response = await this.page.request.post('/wp-admin/admin-ajax.php', { form: { action, ...form } });

        return { status: response.status(), body: await response.text() };
    }
}
