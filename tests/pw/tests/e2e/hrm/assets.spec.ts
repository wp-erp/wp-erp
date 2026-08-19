import { test, expect } from '@utils/test';
import { AssetsPage, assetColumns, allotmentColumns, assetRequestColumns, type AssetScreen } from '@pages/hrm/assetsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupAssets } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Assets @pro', () => {
    let page: AssetsPage;

    test.beforeAll(async () => {
        await cleanupAssets();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AssetsPage(p);
    });

    test.afterAll(async () => {
        await cleanupAssets();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    for (const [screen, columns] of [
        ['assets', assetColumns],
        ['allotments', allotmentColumns],
        ['requests', assetRequestColumns],
    ] as [AssetScreen, readonly string[]][]) {
        test(`the ${screen} screen renders its captured columns`, { tag: ['@tier1', '@hrm-assets', '@smoke'] }, async () => {
            await page.goto(screen);

            expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);

            const headers = await page.columnHeaders();

            for (const column of columns) {
                expect(headers, `column "${column}"`).toContain(column);
            }
        });
    }

    test('an asset category can be created', { tag: ['@tier1', '@hrm-assets', '@crud'] }, async () => {
        const name = uniqueId('pwerpCat');

        await page.createCategory(name);

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_hr_assets_category WHERE cat_name = ?`, [name]);

        expect(rows, 'exactly one category row is written').toHaveLength(1);
    });

    test('an asset is stored as a group with one item per code', { tag: ['@tier1', '@hrm-assets', '@crud'] }, async () => {
        const category = uniqueId('pwerpCat');
        const item = uniqueId('pwerpAsset');

        await page.createCategory(category);
        await page.createAsset({ categoryLabel: category, itemName: item, itemCode: `${item}-01` });

        // One asset is TWO rows: the group (parent 0) and a child per item code,
        // which is what the list's "Available/Total" counts.
        const rows = await query<RowDataPacket[]>(`SELECT id, parent, item_code, status FROM ${prefix()}erp_hr_assets WHERE item_group = ? ORDER BY id`, [item]);

        expect(rows, 'a group row and one item row').toHaveLength(2);
        expect(Number(rows[0]!.parent), 'the first row is the group').toBe(0);
        expect(Number(rows[1]!.parent), 'the item belongs to the group').toBe(Number(rows[0]!.id));
        expect(rows[1]!.item_code, 'the item carries its code').toBe(`${item}-01`);
        expect(rows[1]!.status, 'a new item is in stock').toBe('stock');

        await page.goto('assets');
        expect(await page.hasRowFor(item), 'the asset is listed').toBe(true);
        expect(await page.rowTexts(), 'one of one available').toContainEqual(expect.stringContaining('1/1'));
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('the new-asset form offers the categories that exist', { tag: ['@tier2', '@hrm-assets'] }, async () => {
        const category = uniqueId('pwerpCat');

        await page.createCategory(category);
        await page.openNewAsset();

        expect(await page.categoryOptions(), 'the new category is selectable').toContain(category);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('an asset with no category is refused', { tag: ['@tier3', '@hrm-assets', '@validation'] }, async () => {
        // `asset_insert` refuses `category_id === '-1'` outright
        // (`AjaxHandler.php:70`), and the select defaults to that.
        const item = uniqueId('pwerpNoCat');

        await page.openNewAsset();
        await page.fillItemOnly(item);
        const message = await page.submitAssetExpectingRefusal();

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_hr_assets WHERE item_group = ?`, [item]);

        expect(rows, 'nothing is written without a category').toHaveLength(0);
        expect(message, 'the product says a category is required').toMatch(/select a category|required/i);
    });

    test('Assets is closed to an employee', { tag: ['@tier3', '@hrm-assets', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new AssetsPage(p);
            await asEmployee.goto('assets');
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the Assets screen').toBe(true);
    });
});
