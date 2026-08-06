import { test, expect } from '@utils/test';
import { DocumentPage } from './documentPage';
import { data } from '@utils/testData';

/**
 * HRM Document Manager UI smoke (pro). Module: document_manager.
 *
 * The Documents screen (admin.php?page=erp-hr&section=documents) is a Vue app
 * mounted into #primary.file-primary.content-area. We assert the real app
 * containers render and no PHP fatal fires — depth lives in the REST api spec.
 * Every test carries a tier tag (@pro), the @hrm module tag and a role tag.
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

// ──────────────────────────────────────────────────────────────────────────
// Admin — the file browser boots
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Documents (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('documents page boots and mounts the file browser', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const doc = new DocumentPage(page);
        await doc.goto();
        // No PHP fatal and the real Vue mounts are present.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(doc.sel.primary)).toBeVisible();
        // #file_folder_wrapper ships with `.not-loaded` (display:none) and only
        // reveals after the Vue tree loads, so assert it is attached, not visible.
        await expect(page.locator(doc.sel.wrapper)).toBeAttached();
    });

    test('file-operation toolbar and source/search controls render', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const doc = new DocumentPage(page);
        await doc.goto();
        await expect(page.locator(doc.sel.operationControl)).toBeVisible();
        await expect(page.locator(doc.sel.source)).toBeVisible();
        await expect(page.locator(doc.sel.searchInput)).toBeVisible();
        // The Create Folder control exists in the DOM (it is v-show-gated, so we
        // assert presence rather than visibility to stay resilient to Vue state).
        await expect(page.locator(doc.sel.createFolderBtn)).toHaveCount(1);
    });

    test('breadcrumb and folder-list containers are present', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const doc = new DocumentPage(page);
        await doc.goto();
        await expect(page.locator(doc.sel.breadcrumb)).toHaveCount(1);
        await expect(page.locator(doc.sel.folderList)).toHaveCount(1);
        await expect(page.locator(doc.sel.checkboxAll)).toBeVisible();
    });

    test('typing in the search box does not crash the app', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const doc = new DocumentPage(page);
        await doc.goto();
        await page.locator(doc.sel.searchInput).fill(`pw_${Date.now()}`);
        // The Vue search binds on keyup.enter; pressing it must not fatal.
        await page.locator(doc.sel.searchInput).press('Enter');
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The wrapper is `.not-loaded` (display:none) until the tree loads; the
        // always-visible #primary mount node proves the app shell survived.
        await expect(page.locator(doc.sel.wrapper)).toBeAttached();
        await expect(page.locator(doc.sel.primary)).toBeVisible();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// HR manager — should reach the same screen
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Documents (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    test('HR manager can reach the documents file browser', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        const doc = new DocumentPage(page);
        await page.goto(doc.urls.documents);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The manager has erp_list_employee, so the mount renders.
        await expect(page.locator(doc.sel.primary)).toBeVisible({ timeout: 30_000 });
    });
});
