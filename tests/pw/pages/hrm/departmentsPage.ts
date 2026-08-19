import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → People → Departments.
 * Modal fields come from `tmpl-erp-new-dept` as captured in harness/forms.json.
 */
export const departmentSelectors = {
    // The screen's own trigger. A text match on "Add New" is unsafe here: the
    // hidden ERP nav also contains "Add New Training".
    addNew: '#erp-new-dept',
    title: '#dept-title',
    description: '#dept-desc',
    lead: '#dept-lead',
    parent: '#dept-parent',
    row: '#wpbody-content table tbody tr',
    // The first cell is a <th class="check-column"> holding the row checkbox, so
    // the name lives in the first <td>.
    rowTitle: '#wpbody-content table tbody tr td:first-of-type',
} as const;

export interface DepartmentInput {
    title: string;
    description?: string;
}

export class DepartmentsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        // sub-section is 'department' (singular) — AdminMenu::people_page() at
        // modules/hrm/includes/Admin/AdminMenu.php:241-260. An unknown slug falls
        // through and renders an empty screen rather than 404ing.
        await this.gotoAdmin('erp-hr', { section: 'people', 'sub-section': 'department' });
    }

    get addNewButton(): Locator {
        return this.page.locator(departmentSelectors.addNew).first();
    }

    get titleField(): Locator {
        return this.page.locator(departmentSelectors.title);
    }

    get descriptionField(): Locator {
        return this.page.locator(departmentSelectors.description);
    }

    async openCreateModal(): Promise<void> {
        await this.openModal(this.addNewButton, departmentSelectors.title);
    }

    async create(input: DepartmentInput): Promise<string> {
        await this.openCreateModal();
        await this.titleField.fill(input.title);
        if (input.description) await this.descriptionField.fill(input.description);
        return this.submitModal();
    }

    /** Attempts a create that is expected to be refused; leaves the modal open. */
    async attemptCreate(input: Partial<DepartmentInput>): Promise<void> {
        await this.openCreateModal();
        await this.titleField.fill(input.title ?? '');
        if (input.description) await this.descriptionField.fill(input.description);
        await this.submitModalExpectingError();
    }

    /** Department names listed on the screen. */
    async names(): Promise<string[]> {
        return (await this.page.locator(departmentSelectors.rowTitle).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasDepartment(title: string): Promise<boolean> {
        return (await this.names()).some((n) => n.includes(title));
    }

    async rowCount(): Promise<number> {
        return this.page.locator(departmentSelectors.row).count();
    }

    /** Total across all pages, from WP's own "N items" counter. */
    async totalItems(): Promise<number> {
        const label = this.page.locator('.displaying-num').first();
        if (!(await label.count())) return this.rowCount();
        return Number(((await label.textContent()) ?? '').replace(/[^0-9]/g, '')) || 0;
    }

    /** Looks for a department across every page of the list. */
    async findAcrossPages(title: string, maxPages = 5): Promise<boolean> {
        for (let pageNo = 1; pageNo <= maxPages; pageNo++) {
            await this.gotoAdmin('erp-hr', { section: 'people', 'sub-section': 'department', paged: pageNo });
            if (await this.hasDepartment(title)) return true;
            if ((await this.rowCount()) === 0) break;
        }
        return false;
    }
}
