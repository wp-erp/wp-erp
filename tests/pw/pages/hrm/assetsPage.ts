import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Assets (`section=asset`), an erp-pro module.
 *
 * Three server-rendered list screens sharing the ERP modal shell. Sub-section
 * slugs are irregular and one is misspelled in the product — `asset`,
 * `asset-allottment` (two t's), `asset-request`. A wrong slug silently renders
 * the Assets list instead of 404ing, which makes a typo look like three screens
 * with identical columns.
 *
 * Like payroll it is AJAX-driven with no REST API: 28 `wp_ajax_erp-hr-asset*`
 * actions and NO capability check in the handler — but unlike payroll the
 * mutating ones verify a form-specific nonce an employee cannot obtain.
 */
export const assetSelectors = {
    newEntry: 'a:has-text("New Entry")',
    addCategory: '.asset-add-category',

    // Scoped to the modal FORM: the asset markup carries a second #item_group in
    // its "extra item" area, so a bare id selector is ambiguous.
    categoryName: '.erp-modal-form #cat_name',
    categorySelect: '.erp-modal-form #category_id',
    itemName: '.erp-modal-form #item_group',
    assetType: '.erp-modal-form #asset_type',
    itemCode: '.erp-modal-form input[name^="items"][name*="item_code"]',

    // The modal carries BOTH submit buttons at once — "Save Asset" and, once the
    // category sub-form is opened, "Save Category". BasePage.submitModal() takes
    // the first primary button, which is always Save Asset, so these are named.
    saveAsset: 'button.button-primary:has-text("Save Asset")',
    saveCategory: 'button.button-primary:has-text("Save Category")',
    alert: '.sweet-alert',

    categoryFilter: '#category',
    filterSubmit: '#filter_category',

    table: '#wpbody-content table',
    row: '#wpbody-content table tbody tr',
} as const;

export const assetScreens = {
    assets: 'asset',
    allotments: 'asset-allottment',
    requests: 'asset-request',
} as const;

export type AssetScreen = keyof typeof assetScreens;

/** Columns each list renders, captured live. */
export const assetColumns = ['Item Name', 'Type', 'Category', 'Reg Date', 'Expiry Date', 'Warranty Till', 'Available/Total'] as const;
export const allotmentColumns = ['Item Name', 'Model No', 'Asset Code', 'Given To', 'Given Date', 'Return Date', 'Status'] as const;
export const assetRequestColumns = ['Employee Name', 'Requested Category', 'Requested Item', 'Request Date', 'Given Item', 'Given Date', 'Status'] as const;

export interface AssetInput {
    categoryLabel: string;
    itemName: string;
    itemCode?: string;
}

export class AssetsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(screen: AssetScreen = 'assets'): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'asset', 'sub-section': assetScreens[screen] });
    }

    async columnHeaders(): Promise<string[]> {
        return [
            ...new Set(
                (await this.page.locator(`${assetSelectors.table} th`).allTextContents())
                    .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
                    .filter(Boolean)
            ),
        ];
    }

    async rowTexts(): Promise<string[]> {
        return (await this.page.locator(assetSelectors.row).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasRowFor(text: string): Promise<boolean> {
        return (await this.rowTexts()).some((row) => row.includes(text));
    }

    get newEntryLink(): Locator {
        return this.page.locator(assetSelectors.newEntry).first();
    }

    /** Category names the asset form offers. */
    async categoryOptions(): Promise<string[]> {
        return (await this.page.locator(assetSelectors.categorySelect).locator('option').allTextContents())
            .map((t) => t.trim())
            .filter((t) => t && !/^-\s*select/i.test(t) && t !== '-1');
    }

    async openNewAsset(): Promise<void> {
        await this.goto('assets');
        await this.openModal(this.newEntryLink, assetSelectors.itemName);
    }

    /**
     * Creates an asset category through the "+" beside the category select,
     * which swaps the modal body for the category form.
     */
    async createCategory(name: string): Promise<string> {
        await this.openNewAsset();
        await this.page.locator(assetSelectors.addCategory).first().click();
        await this.page.locator(assetSelectors.categoryName).waitFor({ state: 'visible', timeout: 10_000 });
        await this.page.locator(assetSelectors.categoryName).fill(name);
        await this.page.locator(assetSelectors.saveCategory).first().click();
        await this.page.waitForTimeout(2500);

        return this.noticeText();
    }

    async createAsset(input: AssetInput): Promise<string> {
        await this.openNewAsset();
        await this.page.locator(assetSelectors.categorySelect).selectOption({ label: input.categoryLabel });
        await this.page.locator(assetSelectors.itemName).fill(input.itemName);

        // Asset Type is required too, and the item rows carry their own required
        // "Item Code" — a missing one blocks submission with no request sent.
        await this.page.locator(assetSelectors.assetType).selectOption({ index: 1 });
        const itemCode = this.page.locator(assetSelectors.itemCode).first();
        if (await itemCode.isVisible().catch(() => false)) {
            await itemCode.fill(input.itemCode ?? `${input.itemName}-01`);
        }

        await this.page.locator(assetSelectors.saveAsset).first().click();
        await this.page.waitForTimeout(2500);

        return this.noticeText();
    }

    /** Fills only the item name, leaving the category on its "- Select -" default. */
    async fillItemOnly(itemName: string): Promise<void> {
        await this.page.locator(assetSelectors.itemName).fill(itemName);
        await this.page.locator(assetSelectors.assetType).selectOption({ index: 1 });

        const itemCode = this.page.locator(assetSelectors.itemCode).first();
        if (await itemCode.isVisible().catch(() => false)) await itemCode.fill(`${itemName}-01`);
    }

    /** Submits the asset form as-is and reports whatever the product said. */
    /**
     * Submits the asset form expecting a refusal, and returns what the product
     * told the user.
     *
     * The message arrives in a sweetalert, not a notice and not a native dialog
     * — `asset_insert` answers HTTP 200 with a bare `die()` string, which the JS
     * renders through swal. Reading only the page body finds nothing and makes a
     * refusal that DID warn the user look silent.
     */
    async submitAssetExpectingRefusal(): Promise<string> {
        this.captureDialogs();
        this.clearDialogs();
        await this.page.locator(assetSelectors.saveAsset).first().click();
        await this.page.waitForTimeout(2500);

        const swal = (await this.page.locator(assetSelectors.alert).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).join(' ');

        return `${await this.lastDialog()} ${swal}`.trim();
    }

    async isAssetFormOpen(): Promise<boolean> {
        return this.page.locator(assetSelectors.itemName).isVisible().catch(() => false);
    }
}
