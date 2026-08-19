import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * ERP Pro licence screen — admin.php?page=erp-license.
 *
 * Selectors and strings below were read from
 * erp-pro/includes/templates/license-form.php,
 * erp-pro/includes/templates/license-page-extension.php:260 and
 * erp-pro/includes/Admin/Update.php:1350-1400 / 1280-1340. Nothing here is
 * guessed; the error strings are the product's own copy.
 */
export const licenseSelectors = {
    email: '#email',
    licenseKey: '#license_key',
    subscriptionType: '#subscription-type',
    submit: '#submit',
    deactivate: 'button[name="deactivate_license"]',

    // ---- activated state (includes/templates/license-page-extension.php) ----
    activatedSection: '.erp_pro-extension_section',
    detailsHeading: '.license_details h3',
    detailItem: '.license_details .single_item',
    syncButton: 'button.sync-btn',
    upgradeLink: 'a.upgrade-btn',
    includedExtensions: '.included_extension',
    includedExtensionsHeading: '.included_extension h3',
    extraExtensions: '.purchases-extension',
    extensionItem: '.extension_item',
    // Scope to the INCLUDED list: the screen also renders a separate
    // "Extra Extension Purchased" block, so an unscoped selector counts both.
    extensionName: '.included_extension .extension_item .extension_name h4 a',
    extraExtensionName: '.purchases-extension .extension_item .extension_name h4 a',
} as const;

/** Product copy, verbatim. Specs assert against these, never a paraphrase. */
export const licenseMessages = {
    activated: 'License activated successfully.',
    deactivated: 'License deactivated successfully.',
    emptyEmail: 'Empty email address',
    emptyKey: 'Empty license key',
    expired: 'Your license key expired on',
    disabled: 'Your license key has been disabled.',
    missing: "Invalid license. License doesn't exist.",
    notActiveForUrl: 'Your license is not active for this URL.',
    itemMismatch: 'This appears to be an invalid license key for',
    noActivationsLeft: 'Your license key has reached its activation limit.',
    missingUrl: 'URL not provided',
    keyMismatch: 'License is not valid for this product.',
    generic: 'An error occurred, please try again.',
} as const;

export const subscriptionTypes = [
    'starter_monthly',
    'starter_yearly',
    'growth_monthly',
    'growth_yearly',
    'scale_monthly',
    'scale_yearly',
    'lifetime_business',
    'lifetime_enterprise',
    'monthly',
    'yearly',
] as const;

export class LicensePage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-license');
    }

    get emailField(): Locator {
        return this.page.locator(licenseSelectors.email);
    }

    get keyField(): Locator {
        return this.page.locator(licenseSelectors.licenseKey);
    }

    get subscriptionField(): Locator {
        return this.page.locator(licenseSelectors.subscriptionType);
    }

    get submitButton(): Locator {
        return this.page.locator(licenseSelectors.submit);
    }

    get deactivateButton(): Locator {
        return this.page.locator(licenseSelectors.deactivate);
    }

    /** True when the site currently holds an activated licence. */
    async isActivated(): Promise<boolean> {
        return this.deactivateButton.isVisible().catch(() => false);
    }

    // ---- activated state ---------------------------------------------------

    get activatedSection(): Locator {
        return this.page.locator(licenseSelectors.activatedSection);
    }

    get detailsHeading(): Locator {
        return this.page.locator(licenseSelectors.detailsHeading);
    }

    get syncButton(): Locator {
        return this.page.locator(licenseSelectors.syncButton);
    }

    get upgradeLink(): Locator {
        return this.page.locator(licenseSelectors.upgradeLink);
    }

    get includedExtensionsHeading(): Locator {
        return this.page.locator(licenseSelectors.includedExtensionsHeading);
    }

    get extensionNames(): Locator {
        return this.page.locator(licenseSelectors.extensionName);
    }

    /**
     * The three "single_item" cards: Subscription Status, Renewal Date and
     * Number of user. Returned as flattened text so a spec can assert the
     * values the licence server actually returned.
     */
    async licenceDetailCards(): Promise<string[]> {
        const items = this.page.locator(licenseSelectors.detailItem);
        const count = await items.count();
        const out: string[] = [];

        for (let i = 0; i < count; i++) {
            out.push(((await items.nth(i).textContent()) ?? '').replace(/\s+/g, ' ').trim());
        }

        return out;
    }

    /** Names in the "Included Extensions" block. */
    async listedExtensions(): Promise<string[]> {
        return (await this.extensionNames.allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    /** Names in the separate "Extra Extension Purchased" block. */
    async listedExtraExtensions(): Promise<string[]> {
        return (await this.page.locator(licenseSelectors.extraExtensionName).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    /**
     * The licence result notice, specifically — NOT `.notice` generally.
     * WP ERP renders an Appsero opt-in banner as the first `.notice` on this
     * screen, and reading that instead made a successful activation look failed.
     */
    async licenseNotice(): Promise<string> {
        const notices = this.page.locator('#wpbody-content .notice, #wpbody-content .updated, #wpbody-content .error');
        const count = await notices.count();

        for (let i = 0; i < count; i++) {
            const text = ((await notices.nth(i).textContent()) ?? '').replace(/\s+/g, ' ').trim();
            if (/licen[cs]e|Empty email address|Empty license key/i.test(text) && !/diagnostic data|Appsero/i.test(text)) {
                return text;
            }
        }

        return '';
    }

    async activate(email: string, key: string, subscriptionType: string): Promise<string> {
        await this.emailField.fill(email);
        await this.keyField.fill(key);
        await this.subscriptionField.selectOption(subscriptionType);
        await Promise.all([this.page.waitForLoadState('domcontentloaded'), this.submitButton.click()]);
        return this.licenseNotice();
    }

    /** Submits the form as-is — used by the empty-field negative cases. */
    async submitRaw(): Promise<string> {
        await Promise.all([this.page.waitForLoadState('domcontentloaded'), this.submitButton.click()]);
        return this.licenseNotice();
    }

    // ---- reads the licence spec needs, kept out of the spec ----------------

    /** Joined text of the three detail cards. */
    async detailSummary(): Promise<string> {
        return (await this.licenceDetailCards()).join(' | ');
    }

    async detailCardCount(): Promise<number> {
        return this.page.locator(licenseSelectors.detailItem).count();
    }

    /** Every value offered by the subscription-type select. */
    async subscriptionOptionValues(): Promise<string[]> {
        const options = await this.subscriptionField.locator('option').all();
        return (await Promise.all(options.map((o) => o.getAttribute('value')))).map((v) => v ?? '');
    }

    /** Reloads the screen and waits for it to settle. */
    async reload(): Promise<void> {
        await this.page.reload({ waitUntil: 'domcontentloaded' });
        await this.waitForErpReady();
    }

    /** True when the rendered page leaks the licence key to the current role. */
    async exposesLicenseKey(key: string): Promise<boolean> {
        const body = (await this.page.locator('body').textContent()) ?? '';
        return body.includes(key);
    }

    /** Extensions listed as Included that are not in the purchased set. */
    async includedButNotGranted(): Promise<string[]> {
        const included = await this.listedExtensions();
        const purchased = await this.listedExtraExtensions();
        return included.filter((name) => !purchased.includes(name));
    }

    async deactivate(): Promise<string> {
        if (!(await this.isActivated())) return '';
        await Promise.all([this.page.waitForLoadState('domcontentloaded'), this.deactivateButton.click()]);
        return this.licenseNotice();
    }
}
