import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';

/**
 * Feature-isolated page object for the HRM Document Manager (pro).
 *
 * The screen is a Vue app rendered by
 * erp-pro/modules/hrm/document-manager/includes/admin/views/view-file-system-list.php
 * and mounted on `admin.php?page=erp-hr&section=documents`
 * (Module.php registers the 'documents' sub-section). All ids below are taken
 * verbatim from that view; the file-browser toolbar buttons (#btn_create_folder,
 * #btn_moveto_folder, #btn_delete_folder, #btn_share) are `v-show`-gated, so the
 * smoke specs assert the always-present container ids and avoid timing on the
 * Vue-conditional controls.
 */
export class DocumentPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URL (admin.php?page=erp-hr&section=documents) ─────────────────────────
    readonly urls = {
        documents: toPath('wp-admin/admin.php?page=erp-hr&section=documents'),
    } as const;

    // ── Selectors (real ids/classes from the view) ────────────────────────────
    readonly sel = {
        // Always-present structural mounts.
        primary: '#primary.file-primary.content-area',
        wrapper: '#file_folder_wrapper',
        operationControl: '#dir-file-operation-control',
        source: '#source',
        searchInput: '#search_input',
        breadcrumb: '#file_browser_breadcrumb_list',
        checkboxAll: '#checkbox_all',
        checkAll: '#checkall',
        folderList: '#file_folder_list',
        emptyState: '.no_file_folder_wrapper',
        // v-show-gated toolbar controls (presence in DOM, not visibility).
        createFolderBtn: '#btn_create_folder',
        moveToBtn: '#btn_moveto_folder',
        deleteBtn: '#btn_delete_folder',
        shareBtn: '#btn_share',
    } as const;

    // ── Navigation ────────────────────────────────────────────────────────────
    async goto(): Promise<void> {
        await this.page.goto(this.urls.documents);
        await expect(this.page.locator(this.sel.primary)).toBeVisible({ timeout: 30_000 });
    }
}
