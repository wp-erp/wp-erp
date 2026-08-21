import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * CRM reports — Activity, Customer and Growth.
 *
 * All three are plain PHP screens under `page=erp-crm&section=reports&type=…`,
 * rendered server-side into one table. There is no REST behind them, so the
 * table text IS the oracle.
 *
 * The Activity report maps activity types through a fixed `switch`
 * (`views/reports/activity-report.php:10-28`) that recognises only `email`,
 * `log_activity`, `tasks` and `new_note`; every other type is dropped and never
 * reaches the Total. Its date range defaults to `$start = false`, which means
 * NO date filter at all rather than "today" — the whole history is counted.
 */
export const crmReports = ['activity-report', 'customer-report', 'growth-report'] as const;
export type CrmReport = (typeof crmReports)[number];

/** The heading each report paints, captured live. */
export const crmReportHeadings: Record<CrmReport, string> = {
    'activity-report': 'Activity Report',
    'customer-report': 'Customer Report',
    'growth-report': 'Growth Report',
};

/** Activity types the Activity report is able to display. */
export const reportedActivityTypes = ['email', 'log_activity', 'tasks', 'new_note'] as const;

export class CrmReportsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(report: CrmReport): Promise<void> {
        await this.gotoAdmin('erp-crm', { section: 'reports', type: report });
        await this.page.waitForTimeout(2000);
    }

    /** Flattened text of the report table. */
    async tableText(): Promise<string> {
        const table = this.page.locator('#wpbody-content table').first();
        if (!(await table.count())) return '';

        return (await table.innerText()).replace(/\s+/g, ' ').trim();
    }

    /**
     * The count printed under a column label.
     *
     * These tables are MATRICES, not label/value pairs: the life stages are
     * column HEADERS and the figures sit in a following row keyed by period
     * ("All", "August"). Reading the first number after the word — which is what
     * a flattened-text regex does — returns the wrong cell or nothing at all.
     * This maps the header to its index and reads that column.
     */
    async countFor(label: string): Promise<number> {
        const table = this.page.locator('#wpbody-content table').first();
        if (!(await table.count())) return NaN;

        const headers = (await table.locator('thead th, thead td, tr').first().allTextContents())
            .join(' ')
            .split(/\s{2,}|\n|\t/)
            .map((h) => h.trim())
            .filter(Boolean);

        const grid = await table.locator('tr').evaluateAll((rows) =>
            rows.map((row) =>
                Array.from(row.querySelectorAll('th, td')).map((cell) => (cell.textContent ?? '').replace(/\s+/g, ' ').trim())
            )
        );

        const labelRow = grid.find((row) => row.some((cell) => cell.toLowerCase() === label.toLowerCase()));
        if (!labelRow) return NaN;

        const column = labelRow.findIndex((cell) => cell.toLowerCase() === label.toLowerCase());

        // Two table shapes are in play. The Activity report is label/value PAIRS
        // ("Notes | 3"), so the figure sits beside the label in the same row.
        // The Customer and Growth reports are MATRICES, with the life stages as
        // column headers and the figures in a period row below. Handle the pair
        // shape first, then fall back to reading the column.
        const beside = labelRow[column + 1];
        if (beside !== undefined && /^\d+$/.test(beside)) return Number(beside);

        const dataRow = grid.find((row) => row !== labelRow && /^\d+$/.test(row[column] ?? ''));

        void headers;

        return dataRow ? Number(dataRow[column]) : NaN;
    }
}
