import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * CRM → Tasks (`page=erp-crm&section=task`).
 *
 * A read-only roster of task activities. Tasks themselves are created from a
 * contact's activity feed (see `ActivitiesPage`), and are rows of type `tasks`
 * in `erp_crm_customer_activities`.
 */
export const taskSelectors = {
    row: '#wpbody-content table tbody tr',
    table: '#wpbody-content table',
} as const;

/** Columns the list renders, captured live. */
export const taskColumns = ['Task', 'Contact', 'Assigned By'] as const;

export class TasksPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-crm', { section: 'task' });
        await this.page.waitForTimeout(1500);
    }

    async columnHeaders(): Promise<string[]> {
        return [
            ...new Set(
                (await this.page.locator(`${taskSelectors.table} th`).allTextContents())
                    .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
                    .filter(Boolean)
            ),
        ];
    }

    async rowTexts(): Promise<string[]> {
        return (await this.page.locator(taskSelectors.row).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasTask(text: string): Promise<boolean> {
        return (await this.rowTexts()).some((row) => row.includes(text));
    }
}
