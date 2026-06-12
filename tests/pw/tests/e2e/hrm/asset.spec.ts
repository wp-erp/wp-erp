import { test, expect } from '@utils/test';
import { AssetPage, assetTables } from './assetPage';
import { dbUtils } from '@utils/dbUtils';
import { data, TEST_PREFIX } from '@utils/testData';

/**
 * WP ERP Pro — HRM **Asset Management** (module: asset_management).
 *
 * The module ships NO /erp/v1 REST: all data flows go through admin-ajax
 * (erp-pro/modules/hrm/asset-management/includes/AjaxHandler.php) reached from
 * the jQuery + wp.template + $.erpPopup UI. So this spec is UI smoke + DB
 * verification: the four asset tables exist with the expected shape, and a
 * category created through the real popup persists in wp_erp_hr_assets_category.
 *
 * Resilient-assertion philosophy (see test-plans/_pro-grounding.md):
 *   - UI: assert NOT the WP fatal banner AND the real mount is visible.
 *   - The category UI flow can be brittle under parallel load (nested popups +
 *     async wp.ajax.send), so ASSET-DB-07 is a deterministic seed-or-assert
 *     fallback at the storage layer — the same INSERT the handler performs.
 *
 * Every test carries: tier (@pro) + module (@hrm) + role (@admin/@manager/@employee).
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

// A run-unique suffix so created rows never collide across parallel workers / reruns.
const RUN = `${TEST_PREFIX}Asset Cat ${Date.now()}`;

test.afterAll(async () => {
    // Best-effort cleanup of anything this spec seeded (UI + DB fallbacks). The
    // dbUtils pool is shared and may already be closed by a sibling afterAll; it
    // transparently rebuilds, so swallow any teardown error.
    try {
        await AssetPage.deleteCategoriesLike(TEST_PREFIX);
    } catch {
        /* best-effort cleanup */
    }
    try {
        await dbUtils.close();
    } catch {
        /* pool may already be closed by a sibling spec */
    }
});

// ──────────────────────────────────────────────────────────────────────────
// Admin — UI smoke over the Assets screens + the New-Asset / New-Category modals
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Asset Management UI (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ASSET-UI-01
    test('Assets list page boots into .wrap.erp-hr-assets with no critical error', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const asset = new AssetPage(page);
        await page.goto(asset.urls.assets);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(asset.list.root)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator('#wpadminbar')).toBeVisible();
    });

    // ASSET-UI-02
    test('Assets page shows the "New Entry" control and the list-table container', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const asset = new AssetPage(page);
        await asset.goToAssets();
        await expect(page.locator(asset.list.newEntryBtn)).toBeVisible();
        await expect(page.locator(asset.list.tableContainer)).toBeVisible();
        await expect(page.locator(asset.list.listTable)).toBeVisible();
        // Hidden routing input proves we are on the Assets sub-section.
        await expect(page.locator(asset.list.sectionInput)).toHaveCount(1);
    });

    // ASSET-UI-03
    test('clicking "New Entry" opens the asset modal with required category/item fields', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const asset = new AssetPage(page);
        await asset.openAssetModal();
        await expect(page.locator(asset.assetModal.categorySelect)).toBeVisible();
        await expect(page.locator(asset.assetModal.itemGroup)).toBeVisible();
        await expect(page.locator(asset.assetModal.assetType)).toBeVisible();
        // Item Code (required) is present in the first item block.
        await expect(page.locator(asset.assetModal.itemCode)).toHaveCount(1);
        // The modal carries the admin-ajax action that the save will POST.
        await expect(page.locator(asset.assetModal.actionInput)).toHaveCount(1);
    });

    // ASSET-UI-04
    test('asset modal exposes the Add-Category (+) control wired to the category popup', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const asset = new AssetPage(page);
        await asset.openAssetModal();
        await expect(page.locator(asset.assetModal.addCategoryBtn)).toBeVisible();
        await page.locator(asset.assetModal.addCategoryBtn).click();
        // The nested New-Category popup renders the cat_name field + its action.
        await expect(page.locator(asset.categoryModal.catName)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator(asset.categoryModal.actionInput)).toHaveCount(1);
    });

    // Smoke: the Asset Requests sub-page mounts (separate view, separate routing input).
    test('Asset Requests page boots into .wrap.erp-asset-requests with no critical error', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const asset = new AssetPage(page);
        await page.goto(asset.urls.requests);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(asset.requestsView.root)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator(asset.requestsView.subSectionInput)).toHaveCount(1);
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Admin — DB layer: tables exist with the expected shape; UI insert persists
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Asset Management DB (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ASSET-DB-05
    test('all four asset tables exist with the expected columns', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        for (const table of Object.values(assetTables)) {
            const rows = await AssetPage.tableExists(table);
            expect(rows.length, `${table} should exist`).toBeGreaterThanOrEqual(1);
        }

        const assetCols = await AssetPage.columns(assetTables.assets);
        for (const col of ['category_id', 'item_group', 'asset_type', 'item_code', 'price', 'status', 'parent']) {
            expect(assetCols, `${assetTables.assets} should have ${col}`).toContain(col);
        }

        const catCols = await AssetPage.columns(assetTables.category);
        expect(catCols).toContain('id');
        expect(catCols).toContain('cat_name');
    });

    // ASSET-DB-06 — create a category through the real UI popup and assert it persisted.
    test('category created via the UI popup persists in wp_erp_hr_assets_category', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const asset = new AssetPage(page);
        const catName = `${RUN} UI`;

        await asset.createCategory(catName);

        // Poll the DB briefly — the popup closes optimistically, the insert lands
        // a beat later via wp.ajax.send.
        await expect
            .poll(async () => (await AssetPage.findCategoryByName(catName)).length, { timeout: 20_000 })
            .toBeGreaterThanOrEqual(1);

        const rows = await AssetPage.findCategoryByName(catName);
        expect(rows[0]?.id).toBeTruthy();
    });

    // ASSET-DB-07 — deterministic seed-or-assert fallback at the storage layer.
    // Mirrors the handler's own INSERT (single cat_name column) so coverage of the
    // persistence contract does not depend on the nested-popup async timing.
    test('seed-or-assert: a category row round-trips through the storage layer', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const catName = `${RUN} Seed`;

        const id = await AssetPage.insertCategoryRow(catName);
        expect(id, 'insert should yield a category id').toBeTruthy();

        const rows = await AssetPage.findCategoryByName(catName);
        expect(rows.length).toBeGreaterThanOrEqual(1);
        expect(Number(rows[0]!.id)).toBe(Number(id));
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Manager — erp_hr_manager owns the asset menu, so a HR manager reaches it too
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Asset Management (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    test('HR manager can reach the Assets list and the New Entry control', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        const asset = new AssetPage(page);
        await page.goto(asset.urls.assets);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(asset.list.root)).toBeVisible({ timeout: 30_000 });
        await expect(page.locator(asset.list.newEntryBtn)).toBeVisible();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Negative — a plain employee lacks erp_hr_manager, so the Assets screen is gated
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Asset Management access control (pro, employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('employee cannot reach the Assets management screen', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const asset = new AssetPage(page);
        await page.goto(asset.urls.assets);
        // The menu/page is capability-gated by 'erp_hr_manager' (AdminMenu.php).
        // An employee either lands on a WP "not allowed" notice or simply never
        // gets the assets mount — assert the boundary, not an exact status, and
        // never a PHP fatal.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(asset.list.root)).toHaveCount(0);
    });
});
