import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → People → Designations.
 * Modal fields come from `tmpl-erp-new-desig` as captured in harness/forms.json.
 */
export const designationSelectors = {
    // Trigger id is 'erp-new-designation' — NOT 'erp-new-desig' (that is the
    // js-template id `tmpl-erp-new-desig`, a different thing).
    addNew: '#erp-new-designation',
    title: '#desig-title',
    description: '#desig-desc',
    row: '#wpbody-content table tbody tr',
    // First cell is the check-column <th>; the title is the first <td>.
    rowTitle: '#wpbody-content table tbody tr td:first-of-type',
} as const;

export interface DesignationInput {
    title: string;
    description?: string;
}

export class DesignationsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        // Valid People sub-sections: employee | requests | department | designation | announcement | view
        await this.gotoAdmin('erp-hr', { section: 'people', 'sub-section': 'designation' });
    }

    get addNewButton(): Locator {
        return this.page.locator(designationSelectors.addNew).first();
    }

    get titleField(): Locator {
        return this.page.locator(designationSelectors.title);
    }

    get descriptionField(): Locator {
        return this.page.locator(designationSelectors.description);
    }

    async openCreateModal(): Promise<void> {
        await this.openModal(this.addNewButton, designationSelectors.title);
    }

    async create(input: DesignationInput): Promise<string> {
        await this.openCreateModal();
        await this.titleField.fill(input.title);
        if (input.description) await this.descriptionField.fill(input.description);
        return this.submitModal();
    }

    async attemptCreate(input: Partial<DesignationInput>): Promise<void> {
        await this.openCreateModal();
        await this.titleField.fill(input.title ?? '');
        await this.submitModalExpectingError();
    }

    async names(): Promise<string[]> {
        return (await this.page.locator(designationSelectors.rowTitle).allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    async hasDesignation(title: string): Promise<boolean> {
        return (await this.names()).some((n) => n.includes(title));
    }

    async rowCount(): Promise<number> {
        return this.page.locator(designationSelectors.row).count();
    }

    /**
     * Total across all pages, from WP's own "N items" counter.
     * The list paginates at 20, so a freshly created designation is often not on
     * page 1 — counting visible rows would under-report it.
     */
    async totalItems(): Promise<number> {
        const label = this.page.locator('.displaying-num').first();
        if (!(await label.count())) return this.rowCount();
        const text = (await label.textContent()) ?? '';
        return Number(text.replace(/[^0-9]/g, '')) || 0;
    }

    /** Looks for a designation across every page of the list. */
    async findAcrossPages(title: string, maxPages = 5): Promise<boolean> {
        for (let pageNo = 1; pageNo <= maxPages; pageNo++) {
            await this.gotoAdmin('erp-hr', { section: 'people', 'sub-section': 'designation', paged: pageNo });
            if (await this.hasDesignation(title)) return true;
            if ((await this.rowCount()) === 0) break;
        }
        return false;
    }
}
