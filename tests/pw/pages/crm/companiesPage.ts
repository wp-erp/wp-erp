import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * CRM → Contacts → Companies
 * (`page=erp-crm&section=contact&sub-section=companies`).
 *
 * A company is a row in the SAME `erp_peoples` table as contacts, customers,
 * vendors and employees, distinguished by the `company` people type. Its form
 * has a single name field (`contact[main][company]`) where a contact has first
 * and last names — the only real difference between the two screens.
 */
export const companySelectors = {
    addNew: '#erp-company-new',

    name: '#company',
    email: '#erp-crm-new-contact-email',
    phone: '[name="contact[main][phone]"]',
    lifeStage: '[name="contact[meta][life_stage]"]',
    owner: '#erp-crm-contact-owner-id',
    submit: 'button.button-primary:has-text("Add New")',

    row: '#wpbody-content table tbody tr',
    name_cell: '#wpbody-content table tbody tr td:nth-child(2)',
    emptyState: 'td:has-text("No result found")',
} as const;

/** Columns the list renders, captured live. */
export const companyColumns = ['Company name', 'Email Address', 'Phone', 'Life stage', 'Owner', 'Created At'] as const;

export interface CompanyInput {
    name: string;
    email: string;
    phone?: string;
}

export class CompaniesPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-crm', { section: 'contact', 'sub-section': 'companies' });
        await this.page.waitForTimeout(1500);
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

    /** Company names, stripped of the row-action text the cell also carries. */
    async companyNames(): Promise<string[]> {
        return (await this.page.locator(companySelectors.name_cell).allTextContents())
            .map((t) => t.replace(/\s+/g, ' ').replace(/(Edit|View|Delete|Show more details).*$/i, '').trim())
            .filter(Boolean);
    }

    async hasCompany(name: string): Promise<boolean> {
        return (await this.companyNames()).some((n) => n.includes(name));
    }

    async isEmpty(): Promise<boolean> {
        return (await this.page.locator(companySelectors.emptyState).count()) > 0;
    }

    get addNewButton(): Locator {
        return this.page.locator(companySelectors.addNew).first();
    }

    /**
     * Creates a company.
     *
     * Name, e-mail, life stage and contact owner are all required and the two
     * selects default to empty, so a create that fills only name and e-mail is
     * refused with the modal left open.
     */
    async create(input: CompanyInput): Promise<string> {
        await this.goto();
        await this.openModal(this.addNewButton, companySelectors.name);

        await this.page.locator(companySelectors.name).fill(input.name);
        await this.page.locator(companySelectors.email).fill(input.email);

        if (input.phone) {
            await this.page.locator(companySelectors.phone).fill(input.phone);
        }

        await this.page.locator(companySelectors.lifeStage).selectOption({ index: 1 });
        await this.page.locator(companySelectors.owner).selectOption({ index: 1 });

        this.captureDialogs();
        await this.page.locator(companySelectors.submit).first().click();
        await this.page.waitForTimeout(2500);

        return this.noticeText();
    }

    /** Fills only the name and e-mail, leaving the required selects empty. */
    async createWithoutRequiredSelects(input: CompanyInput): Promise<void> {
        await this.goto();
        await this.openModal(this.addNewButton, companySelectors.name);

        await this.page.locator(companySelectors.name).fill(input.name);
        await this.page.locator(companySelectors.email).fill(input.email);

        this.captureDialogs();
        this.clearDialogs();
        await this.page.locator(companySelectors.submit).first().click();
        await this.page.waitForTimeout(2000);
    }

    /** Fields the browser's own constraint validation is rejecting. */
    async invalidFieldCount(): Promise<number> {
        return this.page.locator('.erp-modal-form :invalid').count();
    }

    /** The browser's validation message for the life-stage select. */
    async lifeStageValidationMessage(): Promise<string> {
        return this.page
            .locator(companySelectors.lifeStage)
            .evaluate((el) => (el as HTMLSelectElement).validationMessage)
            .catch(() => '');
    }

    async isModalStillOpen(): Promise<boolean> {
        return this.page.locator(companySelectors.name).isVisible().catch(() => false);
    }
}
