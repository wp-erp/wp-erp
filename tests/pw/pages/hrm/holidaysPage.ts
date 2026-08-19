import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Leave → Holidays (`section=leave&sub-section=holidays`).
 * Modal fields come from `tmpl-erp-hr-holiday-js-tmp`.
 */
export const holidaySelectors = {
    addNew: '#erp-hr-new-holiday',
    title: '#erp-hr-holiday-title',
    startDate: '#erp-hr-holiday-start',
    isRange: '#erp-hr-holiday-range',
    endDate: '#erp-hr-holiday-end',
    description: '#erp-hr-holiday-description',

    row: '#wpbody-content table tbody tr',
    rowTitle: '#wpbody-content table tbody tr td:first-of-type',
    totalItems: '.displaying-num',
    bulkAction: '#bulk-action-selector-top',
} as const;

/** Columns the list renders, verified live. */
export const holidayColumns = ['Title', 'Start Date', 'End Date', 'Duration', 'Description'] as const;

export interface HolidayInput {
    title: string;
    startDate: string;
    endDate?: string;
    description?: string;
}

export class HolidaysPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'leave', 'sub-section': 'holidays' });
    }

    get addNewButton(): Locator {
        return this.page.locator(holidaySelectors.addNew);
    }

    get titleField(): Locator {
        return this.page.locator(holidaySelectors.title);
    }

    get startDateField(): Locator {
        return this.page.locator(holidaySelectors.startDate);
    }

    get endDateField(): Locator {
        return this.page.locator(holidaySelectors.endDate);
    }

    get descriptionField(): Locator {
        return this.page.locator(holidaySelectors.description);
    }

    get rangeCheckbox(): Locator {
        return this.page.locator(holidaySelectors.isRange);
    }

    async columnHeaders(): Promise<string[]> {
        return (await this.page.locator('#wpbody-content table thead th').allTextContents())
            .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
            .filter(Boolean);
    }

    async rowCount(): Promise<number> {
        return this.page.locator(holidaySelectors.row).count();
    }

    async totalItems(): Promise<number> {
        const label = this.page.locator(holidaySelectors.totalItems).first();
        if (!(await label.count())) return this.rowCount();
        return Number(((await label.textContent()) ?? '').replace(/[^0-9]/g, '')) || 0;
    }

    async names(): Promise<string[]> {
        return (await this.page.locator(holidaySelectors.rowTitle).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasHoliday(title: string): Promise<boolean> {
        return (await this.names()).some((n) => n.includes(title));
    }

    async findAcrossPages(title: string, maxPages = 5): Promise<boolean> {
        for (let pageNo = 1; pageNo <= maxPages; pageNo++) {
            await this.gotoAdmin('erp-hr', { section: 'leave', 'sub-section': 'holidays', paged: pageNo });
            if (await this.hasHoliday(title)) return true;
            if ((await this.rowCount()) === 0) break;
        }
        return false;
    }

    /** The start-date cell for a holiday, as rendered. */
    async startDateOf(title: string): Promise<string> {
        const row = this.page.locator(holidaySelectors.row).filter({ hasText: title }).first();
        return ((await row.locator('td').nth(1).textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    /** The duration cell for a holiday, e.g. "1 day" / "3 days". */
    async durationOf(title: string): Promise<string> {
        const row = this.page.locator(holidaySelectors.row).filter({ hasText: title }).first();
        return ((await row.locator('td').nth(3).textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    async openCreateModal(): Promise<void> {
        await this.openModal(this.addNewButton, holidaySelectors.title);
    }

    async fillHoliday(input: HolidayInput): Promise<void> {
        await this.titleField.fill(input.title);
        await this.fillDate(holidaySelectors.startDate, input.startDate);

        if (input.endDate) {
            // The end-date field only applies once the holiday is a range.
            if (!(await this.rangeCheckbox.isChecked())) await this.rangeCheckbox.check();
            await this.fillDate(holidaySelectors.endDate, input.endDate);
        }

        if (input.description) await this.descriptionField.fill(input.description);
    }

    async create(input: HolidayInput): Promise<string> {
        await this.openCreateModal();
        await this.fillHoliday(input);
        return this.submitModal();
    }

    /** Submits expecting refusal; returns the product's alert text, if any. */
    async attemptCreate(input: Partial<HolidayInput>): Promise<string> {
        this.captureDialogs();
        this.clearDialogs();

        await this.openCreateModal();
        if (input.title !== undefined) await this.titleField.fill(input.title);
        if (input.startDate) await this.fillDate(holidaySelectors.startDate, input.startDate);
        await this.submitModalExpectingError();

        return this.lastDialog();
    }
}
