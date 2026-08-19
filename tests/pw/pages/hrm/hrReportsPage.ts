import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Reports (`section=report&sub-section=report&type=…`), nine read-only
 * screens rendered server-side with jQuery Flot charts.
 *
 * These are the suite's best reconciliation surface: their numbers must agree
 * with the seeded company, so the oracle is the seed data rather than the screen
 * describing itself.
 */
export const hrReportTypes = {
    ageProfile: 'age-profile',
    genderProfile: 'gender-profile',
    headcount: 'headcount',
    yearsOfService: 'years-of-service',
    salaryHistory: 'salary-history',
    leaves: 'leaves',
    assets: 'asset-report',
    attendanceByDate: 'attendance-report',
    attendanceByEmployee: 'att-report-employee',
} as const;

export type HrReport = keyof typeof hrReportTypes;

/** Columns each report renders, captured live. */
export const headcountColumns = ['Name', 'Hire Date', 'Job Title', 'Department', 'Location', 'Status'] as const;
export const salaryHistoryColumns = ['Employee', 'Date', 'Pay Rate', 'Pay type', 'Employee ID'] as const;
export const attendanceByDateColumns = ['Date', 'Total', 'Present', 'Leave', 'Absent', 'Comment'] as const;

export class HrReportsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(report: HrReport): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'report', 'sub-section': 'report', type: hrReportTypes[report] });
        await this.page.waitForTimeout(1200);
    }

    async columnHeaders(): Promise<string[]> {
        return [
            ...new Set(
                (await this.page.locator('#wpbody-content table th').allTextContents())
                    .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
                    .filter(Boolean)
            ),
        ];
    }

    /** First-column values, i.e. the row subject of most of these reports. */
    async firstColumn(): Promise<string[]> {
        return (await this.page.locator('#wpbody-content table tbody tr td:first-of-type').allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async rowCount(): Promise<number> {
        return this.page.locator('#wpbody-content table tbody tr').count();
    }

    /** Flattened table text — used to reconcile figures against the seed. */
    async tableText(): Promise<string> {
        return (await this.page.locator('#wpbody-content table').allTextContents()).join(' ').replace(/\s+/g, ' ');
    }

    /** The row for a named subject, flattened. */
    async rowFor(name: string): Promise<string> {
        const row = this.page.locator('#wpbody-content table tbody tr').filter({ hasText: name }).first();
        return ((await row.textContent().catch(() => '')) ?? '').replace(/\s+/g, ' ').trim();
    }
}
