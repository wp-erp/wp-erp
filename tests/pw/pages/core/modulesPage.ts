import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * WP ERP → Modules — admin.php?page=erp-extensions.
 *
 * Selectors verified against the live screen: module names are `h3.title a`
 * inside `.erp-detail`, each module is an `.erp_addon_item_row`, and the three
 * filter groups are `<button class="btn">` inside three sibling `<ul>`s —
 * category (All/Purchased/HRM/CRM/Accounting), status (All/Active/Inactive) and
 * bulk actions (Select All/Activate/Deactivate). "All" appears in TWO groups, so
 * a bare text match is ambiguous — always go through filterButton().
 */
export const moduleSelectors = {
    search: '#plugin-search-input, input[name="s"]',
    selectAll: '#select_all',
    moduleRow: '.erp_addon_item_row',
    proBlock: '#erp_addon_wrap',
    filterBlock: '#filter',
    coreModuleCard: '.module_item',
    moduleTitle: 'h3.title a',
    // Search and the category tabs hide rows with CSS rather than removing them,
    // so anything counting "what the user sees" must filter on :visible.
    moduleTitleVisible: 'h3.title a:visible',
    filterGroup: '#wpbody-content ul',
    moduleCheckbox: '#wpbody-content input[type="checkbox"]',
} as const;

/**
 * Filter buttons carry stable ids — use them. Text/role matching is unreliable
 * here: each button holds BOTH an <img alt="Purchased"> and a <span>Purchased</span>,
 * so its accessible name computes to "Purchased Purchased", and "All" appears in
 * two groups (#all for category, #right_all for status).
 */
export const categoryFilters = { All: '#all', Purchased: '#purchased', HRM: '#hrm', CRM: '#crm', Accounting: '#accounting' } as const;
export const statusFilters = { All: '#right_all', Active: '#active', Inactive: '#inactive' } as const;

/** Bulk actions live in the `.tablenav` bar, which appears only once a module is selected. */
export const bulkActionLabels = ['Select All', 'Activate', 'Deactivate'] as const;



export class ModulesPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-extensions');
    }

    categoryFilter(label: keyof typeof categoryFilters): Locator {
        return this.page.locator(categoryFilters[label]);
    }

    statusFilter(label: keyof typeof statusFilters): Locator {
        return this.page.locator(statusFilters[label]);
    }

    /** The bulk-action bar, shown only after at least one module is ticked. */
    get bulkBar(): Locator {
        return this.page.locator('.tablenav.top');
    }

    get moduleCheckboxes(): Locator {
        return this.page.locator(`${moduleSelectors.moduleRow} input[type="checkbox"]`);
    }

    get searchBox(): Locator {
        return this.page.locator(moduleSelectors.search).first();
    }

    get moduleTitles(): Locator {
        return this.page.locator(moduleSelectors.moduleTitleVisible);
    }

    /** Every module name the user can actually see. */
    async moduleNames(): Promise<string[]> {
        return (await this.moduleTitles.allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async visibleModuleCount(): Promise<number> {
        return this.moduleTitles.count();
    }

    /** The three free core modules the screen always lists. */
    static readonly CORE_MODULES = ['HR Management', 'CR Management', 'Accounting'] as const;

    /** Core module names that are missing from the screen. */
    async missingCoreModules(): Promise<string[]> {
        return this.rendersAll([...ModulesPage.CORE_MODULES]);
    }

    async coreModuleCardCount(): Promise<number> {
        return this.coreModuleCards.count();
    }

    /** Names from the given list that are absent from the markup. */
    async missingFromMarkup(names: string[]): Promise<string[]> {
        const inDom = await this.moduleNamesInDom();
        return names.filter((n) => !inDom.includes(n));
    }

    async moduleRowCountInDom(): Promise<number> {
        return this.page.locator(moduleSelectors.moduleTitle).count();
    }

    async selectFirstModule(): Promise<void> {
        await this.moduleCheckboxes.first().check();
    }

    async deselectFirstModule(): Promise<void> {
        await this.moduleCheckboxes.first().uncheck();
    }

    bulkAction(label: string): Locator {
        return this.bulkBar.getByText(label, { exact: true }).first();
    }

    /** Every module name present in the markup, visible or not. */
    async moduleNamesInDom(): Promise<string[]> {
        return (await this.page.locator(moduleSelectors.moduleTitle).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    /**
     * Reveals the Pro extensions block.
     *
     * The screen hides `#erp_addon_wrap` and `#filter` on DOM-ready and only
     * un-hides them from a `$(window).on('load')` handler bound inside that same
     * ready callback (wp-erp/includes/Admin/views/modules.php:817-823). When
     * `load` has already fired by the time the handler binds, it never runs and
     * every Pro extension stays hidden. Specs that need the Pro list call this
     * so they test the list itself rather than re-failing on that defect.
     */
    async revealProExtensions(): Promise<void> {
        await this.page.evaluate(() => {
            const w = window as unknown as { jQuery?: (s: string) => { show: () => void } };
            w.jQuery?.('#erp_addon_wrap').show();
            w.jQuery?.('#filter').show();
        });
        await this.page.waitForTimeout(300);
    }

    /** True when the Pro block is actually visible to a user. */
    async proExtensionsVisible(): Promise<boolean> {
        return this.page.locator(moduleSelectors.proBlock).isVisible().catch(() => false);
    }

    get coreModuleCards(): Locator {
        return this.page.locator(moduleSelectors.coreModuleCard);
    }

    /** Types into the search box and waits for the list to settle. */
    async search(term: string): Promise<void> {
        // The list is filtered by a jQuery keyup handler. fill() dispatches only
        // input/change, so the handler never runs and the list never narrows —
        // type real key events instead.
        await this.searchBox.click();
        await this.searchBox.press('ControlOrMeta+a');
        await this.searchBox.press('Delete');

        if (term) {
            await this.searchBox.pressSequentially(term, { delay: 60 });
        }

        await this.searchBox.press('Space');
        await this.searchBox.press('Backspace');
        await this.page.waitForTimeout(900);
    }
}
