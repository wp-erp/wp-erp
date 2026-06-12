import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';
import type { APIResponse } from '@playwright/test';

/**
 * Feature-isolated page object for the WP ERP Pro **Asset Management** module
 * (erp-pro/modules/hrm/asset-management).
 *
 * The module exposes NO /erp/v1 REST surface — every write/read is a WordPress
 * admin-ajax handler (includes/AjaxHandler.php). So this POM drives the real UI
 * (jQuery + wp.template + $.erpPopup) and verifies persistence via dbUtils.
 *
 * Routing (WPERP >= 1.4.0): the module registers through erp_add_menu / erp_add_submenu
 * into the HR app (AdminMenu.php lines 49-78), so pages are reached as
 *   admin.php?page=erp-hr&section=asset&sub-section=<asset|asset-allottment|asset-request>
 * and are capability-gated by 'erp_hr_manager'.
 *
 * Selectors are taken verbatim from the rendered views:
 *   - views/assets.php                         (Assets list + "New Entry")
 *   - views/requests.php                       (Asset Requests list)
 *   - views/js-templates/asset-new.php         (the New-Asset modal body)
 *   - views/js-templates/category-new.php      (the New-Category popup body)
 * and from assets/js/assets.js (the $.erpPopup ids + click handlers).
 *
 * The pro asset DB tables are referenced as string literals (the shared
 * utils/dbData.ts only ships free tables). The prefix is derived the same way
 * dbData does, so a non-default DB_PREFIX still resolves.
 */

const PREFIX = process.env.DB_PREFIX ?? 'wp';

export const assetTables = {
    assets: `${PREFIX}_erp_hr_assets`,
    category: `${PREFIX}_erp_hr_assets_category`,
    request: `${PREFIX}_erp_hr_assets_request`,
    history: `${PREFIX}_erp_hr_assets_history`,
} as const;

export class AssetPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URLs (admin.php?page=erp-hr routed by &section=asset&sub-section=) ─────
    readonly urls = {
        assets: toPath('wp-admin/admin.php?page=erp-hr&section=asset&sub-section=asset'),
        allotments: toPath('wp-admin/admin.php?page=erp-hr&section=asset&sub-section=asset-allottment'),
        requests: toPath('wp-admin/admin.php?page=erp-hr&section=asset&sub-section=asset-request'),
    } as const;

    // ── Selectors grouped by area (real ids/classes from the views) ───────────
    // Assets list page (views/assets.php).
    readonly list = {
        root: 'div.wrap.erp-hr-assets',
        newEntryBtn: 'h2 a.add-new-h2.asset-new',
        tableContainer: 'div.assets-list-table',
        listTable: 'table.wp-list-table',
        sectionInput: 'input[name="section"][value="asset"]',
        subSectionInput: 'input[name="sub-section"][value="asset"]',
    } as const;

    // Asset Requests page (views/requests.php).
    readonly requestsView = {
        root: 'div.wrap.erp-asset-requests',
        subSectionInput: 'input[name="sub-section"][value="asset-request"]',
    } as const;

    // The New-Asset modal ($.erpPopup id 'erp-hr-new-asset', body = asset-new.php).
    readonly assetModal = {
        root: '#erp-hr-new-asset',
        categorySelect: 'select.asset-category[name="category_id"]',
        addCategoryBtn: 'a.asset-add-category',
        editCategoryBtn: 'a.asset-edit-category',
        itemGroup: 'input[name="item_group"]',
        assetType: 'select.asset-type[name="asset_type"]',
        itemCode: 'input[name="items[1][item_code]"]',
        actionInput: 'input[name="action"]#erp-assets-action[value="erp-hr-assets-new"]',
        submitBtn: '#erp-hr-new-asset .button-primary',
    } as const;

    // The New-Category popup ($.erpPopup id 'asset-category-new', body = category-new.php).
    readonly categoryModal = {
        root: '#asset-category-new',
        catName: '#asset-category-new input[name="cat_name"]',
        actionInput: '#asset-category-new input[name="action"]#erp-assets-action[value="erp-hr-assets-new-category"]',
        submitBtn: '#asset-category-new .button-primary',
    } as const;

    // ── Navigation ────────────────────────────────────────────────────────────
    async goToAssets(): Promise<void> {
        await this.page.goto(this.urls.assets);
        await expect(this.page.locator(this.list.root)).toBeVisible({ timeout: 30_000 });
    }

    async goToRequests(): Promise<void> {
        await this.page.goto(this.urls.requests);
        await expect(this.page.locator(this.requestsView.root)).toBeVisible({ timeout: 30_000 });
    }

    // ── Modal helpers ─────────────────────────────────────────────────────────

    /** Open the New-Asset modal and wait for its category dropdown to render. */
    async openAssetModal(): Promise<void> {
        await this.goToAssets();
        await this.page.locator(this.list.newEntryBtn).click();
        await expect(this.page.locator(this.assetModal.root)).toBeVisible({ timeout: 30_000 });
        await expect(this.page.locator(this.assetModal.categorySelect)).toBeVisible({ timeout: 30_000 });
    }

    /** Open the New-Asset modal, then the nested New-Category popup. */
    async openCategoryPopup(): Promise<void> {
        await this.openAssetModal();
        await this.page.locator(this.assetModal.addCategoryBtn).click();
        await expect(this.page.locator(this.categoryModal.catName)).toBeVisible({ timeout: 30_000 });
    }

    /**
     * Create a category through the real popup. Fills cat_name, submits, and
     * waits for the admin-ajax POST so the row is committed before the caller
     * queries the DB. Returns the name that was submitted.
     */
    async createCategory(catName: string): Promise<string> {
        await this.openCategoryPopup();
        await this.page.locator(this.categoryModal.catName).fill(catName);
        await Promise.all([
            this.page.waitForResponse(
                r => r.url().includes('admin-ajax.php') && r.request().method() === 'POST',
                { timeout: 30_000 },
            ),
            this.page.locator(this.categoryModal.submitBtn).click(),
        ]);
        return catName;
    }

    // ── DB helpers (no REST in the asset module) ──────────────────────────────

    /** SHOW TABLES LIKE — returns the matched rows (length>=1 means it exists). */
    static async tableExists(table: string): Promise<unknown[]> {
        return dbUtils.dbQuery(`SHOW TABLES LIKE '${table}'`);
    }

    /** Column names of a table (lower-cased) for shape assertions. */
    static async columns(table: string): Promise<string[]> {
        const rows = await dbUtils.dbQuery<{ Field: string }>(`SHOW COLUMNS FROM ${table}`);
        return rows.map(r => String(r.Field).toLowerCase());
    }

    /** Look up category ids by exact name (used to confirm a UI insert landed). */
    static async findCategoryByName(catName: string): Promise<{ id: number }[]> {
        return dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${assetTables.category} WHERE cat_name = ? ORDER BY id DESC`,
            [catName],
        );
    }

    /**
     * Seed a category row straight through the storage layer (same INSERT the
     * app's wp_erp_hr_asset_category_insert() does: a single cat_name column).
     * Used as the deterministic fallback when the async popup flow is unavailable.
     * Returns the new id, or undefined if the insert id could not be resolved.
     */
    static async insertCategoryRow(catName: string): Promise<number | undefined> {
        const result = await dbUtils.dbQuery<{ insertId?: number }>(
            `INSERT INTO ${assetTables.category} (cat_name) VALUES (?)`,
            [catName],
        );
        let id = (result as unknown as { insertId?: number }).insertId;
        if (!id) {
            const found = await AssetPage.findCategoryByName(catName);
            id = found[0]?.id;
        }
        return id;
    }

    /** Delete category rows by a name prefix (test cleanup). */
    static async deleteCategoriesLike(prefix: string): Promise<void> {
        await dbUtils.dbQuery(`DELETE FROM ${assetTables.category} WHERE cat_name LIKE ?`, [`${prefix}%`]);
    }

    // ── Lifecycle driver (admin-ajax with page-scraped per-action nonces) ──────
    //
    // The asset module ships NO REST. Every WRITE handler (AjaxHandler.php) checks
    // its OWN web-context nonce, bound to the logged-in session token — a CLI-made
    // nonce will NOT match the cookie session ("You are not allowed!"). So we
    // scrape the real nonce out of the rendered admin page HTML (each lives in a
    // <script type="text/html" id="tmpl-…"> block as <input name="_wpnonce">) and
    // POST admin-ajax through `page.request`, which carries the page's session
    // cookies. Success handlers return JSON {success:true}; failures `die()` a
    // plain string (e.g. 'You are not allowed!'), so callers must read raw text.

    /** The asset admin pages used to source per-action nonces (and the keys are
     *  the template ids that carry each WRITE handler's hidden _wpnonce). */
    readonly templatePages = {
        // section=asset renders the category-new + asset-new templates.
        asset: toPath('wp-admin/admin.php?page=erp-hr&section=asset&sub-section=asset'),
        // people/employee (and my-profile) render the employee request template.
        people: toPath('wp-admin/admin.php?page=erp-hr&section=people&sub-section=employee'),
        // any asset page with a &sub-section renders reply/reject/return/allot.
        requests: toPath('wp-admin/admin.php?page=erp-hr&section=asset&sub-section=asset-request'),
        allotments: toPath('wp-admin/admin.php?page=erp-hr&section=asset&sub-section=asset-allottment'),
    } as const;

    /**
     * Load a page and regex out the hidden `_wpnonce` value that lives inside a
     * specific wp.template `<script id="tmpl-…">` block. Returns '' when the
     * template/nonce is not present (e.g. the screen is capability-gated).
     */
    async scrapeNonce(url: string, templateId: string): Promise<string> {
        await this.page.goto(url, { waitUntil: 'domcontentloaded' });
        const html = await this.page.content();
        const block = new RegExp(`id="${templateId}"[\\s\\S]*?</script>`).exec(html);
        if (!block) return '';
        const nonce = /name="_wpnonce"[^>]*value="([0-9a-fA-F]+)"/.exec(block[0]);
        return nonce ? nonce[1]! : '';
    }

    /**
     * POST an admin-ajax action through the page's request context (session
     * cookies travel automatically). `form` is sent as application/x-www-form-
     * urlencoded — exactly what the jQuery UI submits. Returns the raw APIResponse
     * + the decoded text body (admin-ajax failures are NOT JSON).
     */
    async ajax(form: Record<string, string>): Promise<[APIResponse, string]> {
        const resp = await this.page.request.post(toPath('wp-admin/admin-ajax.php'), {
            form,
            failOnStatusCode: false,
        });
        const text = await resp.text();
        return [resp, text];
    }

    // ── DB lookups for each lifecycle step (no REST) ──────────────────────────

    static async findAssetByCode(itemCode: string): Promise<{ id: number; status: string; allottable: string; asset_type: string; parent: number }[]> {
        return dbUtils.dbQuery(
            `SELECT id, status, allottable, asset_type, parent FROM ${assetTables.assets} WHERE item_code = ? ORDER BY id DESC`,
            [itemCode],
        );
    }

    static async findRequestByGroup(itemGroup: string | number): Promise<{ id: number; user_id: number; item_group: string; item_id: number | null; status: string; allott_id: number | null; given_item_id: number | null; reply_msg: string | null }[]> {
        return dbUtils.dbQuery(
            `SELECT id, user_id, item_group, item_id, status, allott_id, given_item_id, reply_msg FROM ${assetTables.request} WHERE item_group = ? ORDER BY id DESC`,
            [String(itemGroup)],
        );
    }

    static async findHistoryByItem(itemId: string | number): Promise<{ id: number; status: string; category_id: number; item_group: string; item_id: number; allotted_to: number; date_given: string; date_return_real: string | null; return_note: string | null }[]> {
        return dbUtils.dbQuery(
            `SELECT id, status, category_id, item_group, item_id, allotted_to, date_given, date_return_real, return_note FROM ${assetTables.history} WHERE item_id = ? ORDER BY id DESC`,
            [String(itemId)],
        );
    }

    static async assetStatus(id: string | number): Promise<string | undefined> {
        const rows = await dbUtils.dbQuery<{ status: string }>(
            `SELECT status FROM ${assetTables.assets} WHERE id = ? LIMIT 1`,
            [String(id)],
        );
        return rows[0]?.status;
    }

    // ── Cleanup helpers (string-literal pro tables; cascade by created ids) ────

    static async deleteHistoryByItem(itemId: string | number): Promise<void> {
        await dbUtils.dbQuery(`DELETE FROM ${assetTables.history} WHERE item_id = ?`, [String(itemId)]);
    }

    static async deleteRequestsByGroup(itemGroup: string | number): Promise<void> {
        await dbUtils.dbQuery(`DELETE FROM ${assetTables.request} WHERE item_group = ?`, [String(itemGroup)]);
    }

    static async deleteAssetsLikeCode(prefix: string): Promise<void> {
        await dbUtils.dbQuery(`DELETE FROM ${assetTables.assets} WHERE item_code LIKE ?`, [`${prefix}%`]);
    }
}
