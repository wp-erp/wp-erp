import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * CRM → Contacts (`page=erp-crm&section=contact`).
 *
 * Contacts, customers, vendors AND employees all share one table,
 * `erp_peoples`, distinguished by a row in `erp_people_type_relations`. That
 * makes the people table the oracle for every CRM count — and it is why the
 * HRM specs' cleanup has to remove its people rows too, or CRM figures drift.
 */
export const contactSelectors = {
    addNew: '#erp-customer-new',
    import: '#erp-contact-import-users',

    firstName: '#first_name',
    lastName: '#last_name',
    email: '#erp-crm-new-contact-email',
    lifeStage: '[name="contact[meta][life_stage]"]',
    owner: '#erp-crm-contact-owner-id',
    submit: 'button:has-text("Add New")',

    row: '#wpbody-content table tbody tr',
    name: '#wpbody-content table tbody tr td:nth-child(2)',
    totalItems: '.displaying-num',
} as const;

/** Columns the list renders, captured live. */
export const contactColumns = ['Contact name', 'Email Address', 'Phone', 'Life stage', 'Owner', 'Created At'] as const;

export interface ContactInput {
    firstName: string;
    lastName: string;
    email: string;
}

export class ContactsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-crm', { section: 'contact' });
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

    /** Contact names, stripped of the row-action text the cell also carries. */
    async contactNames(): Promise<string[]> {
        return (await this.page.locator(contactSelectors.name).allTextContents())
            .map((t) => t.replace(/\s+/g, ' ').replace(/(Edit|View|Delete|Show more details).*$/i, '').trim())
            .filter(Boolean);
    }

    async hasContact(name: string): Promise<boolean> {
        return (await this.contactNames()).some((n) => n.includes(name));
    }

    async rowCount(): Promise<number> {
        return this.page.locator(contactSelectors.row).count();
    }

    get addNewButton(): Locator {
        return this.page.locator(contactSelectors.addNew).first();
    }

    /**
     * Creates a contact.
     *
     * Life stage and contact owner are both REQUIRED and both default to empty,
     * so a create that fills only the name and e-mail is silently refused.
     */
    async create(input: ContactInput): Promise<string> {
        await this.goto();
        await this.openModal(this.addNewButton, contactSelectors.firstName);

        await this.page.locator(contactSelectors.firstName).fill(input.firstName);
        await this.page.locator(contactSelectors.lastName).fill(input.lastName);
        await this.page.locator(contactSelectors.email).fill(input.email);
        await this.page.locator(contactSelectors.lifeStage).selectOption({ index: 1 });
        await this.page.locator(contactSelectors.owner).selectOption({ index: 1 });

        this.captureDialogs();
        await this.page.locator(contactSelectors.submit).first().click();
        await this.page.waitForTimeout(2500);

        return this.noticeText();
    }

    async isModalStillOpen(): Promise<boolean> {
        return this.page.locator(contactSelectors.firstName).isVisible().catch(() => false);
    }
}
