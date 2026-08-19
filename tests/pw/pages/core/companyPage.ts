import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * WP ERP → Company — admin.php?page=erp-company (view) and &action=edit (form).
 * Field names captured from the live edit form.
 */
export const companySelectors = {
    name: '#name, input[name="name"]',
    address1: 'input[name="address[address_1]"]',
    address2: 'input[name="address[address_2]"]',
    city: 'input[name="address[city]"]',
    country: 'select[name="address[country]"]',
    state: 'select[name="address[state]"]',
    zip: 'input[name="address[zip]"]',
    phone: 'input[name="phone"]',
    fax: 'input[name="fax"]',
    mobile: 'input[name="mobile"]',
    website: 'input[name="website"]',
    businessType: 'select[name="business_type"]',
    save: 'input[name="save"], button[name="save"], input[type="submit"]',
    editLink: 'a[href*="action=edit"]',
    createLocation: 'a:has-text("Create New Location"), button:has-text("Create New Location")',
    locationsEmpty: 'text=/No extra locations found/i',
} as const;

export interface CompanyDetails {
    name: string;
    address1: string;
    city: string;
    zip: string;
    phone: string;
    website: string;
}

export class CompanyPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-company');
    }

    async gotoEdit(): Promise<void> {
        await this.gotoAdmin('erp-company', { action: 'edit' });
    }

    get nameField(): Locator {
        return this.page.locator(companySelectors.name).first();
    }

    get address1Field(): Locator {
        return this.page.locator(companySelectors.address1);
    }

    get cityField(): Locator {
        return this.page.locator(companySelectors.city);
    }

    get zipField(): Locator {
        return this.page.locator(companySelectors.zip);
    }

    get phoneField(): Locator {
        return this.page.locator(companySelectors.phone);
    }

    get websiteField(): Locator {
        return this.page.locator(companySelectors.website);
    }

    get countrySelect(): Locator {
        return this.page.locator(companySelectors.country);
    }

    get saveButton(): Locator {
        return this.page.locator(companySelectors.save).first();
    }

    /** Reads the current values out of the edit form. */
    async readForm(): Promise<CompanyDetails> {
        return {
            name: await this.nameField.inputValue(),
            address1: await this.address1Field.inputValue(),
            city: await this.cityField.inputValue(),
            zip: await this.zipField.inputValue(),
            phone: await this.phoneField.inputValue(),
            website: await this.websiteField.inputValue(),
        };
    }

    async fillForm(details: Partial<CompanyDetails>): Promise<void> {
        if (details.name !== undefined) await this.nameField.fill(details.name);
        if (details.address1 !== undefined) await this.address1Field.fill(details.address1);
        if (details.city !== undefined) await this.cityField.fill(details.city);
        if (details.zip !== undefined) await this.zipField.fill(details.zip);
        if (details.phone !== undefined) await this.phoneField.fill(details.phone);
        if (details.website !== undefined) await this.websiteField.fill(details.website);
    }

    async save(): Promise<void> {
        await Promise.all([this.page.waitForLoadState('domcontentloaded'), this.saveButton.click()]);
    }

    /** The company summary rendered on the view screen. */
    async summaryText(): Promise<string> {
        return ((await this.page.locator('.erp-company-single, #wpbody-content').first().textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    get editLink(): Locator {
        return this.page.locator(companySelectors.editLink).first();
    }

    get createLocationButton(): Locator {
        return this.page.locator(companySelectors.createLocation).first();
    }

    /** Values from the given list that the summary does not show. */
    async missingFromSummary(values: string[]): Promise<string[]> {
        const summary = await this.summaryText();
        return values.filter((v) => v && !summary.includes(v));
    }

    /** Country options offered by the address form. */
    async countryOptionCount(): Promise<number> {
        return this.countrySelect.locator('option').count();
    }

    /** Saves the form and returns the notice the product rendered. */
    async saveAndReadNotice(): Promise<string> {
        await this.save();
        return this.noticeText();
    }
}
