import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Documents (`section=documents`), the erp-pro Document Manager.
 *
 * Files are ordinary WordPress media attachments; the folder tree lives in
 * `erp_employee_dir_file_relationship` and sharing in `erp_dir_file_share`.
 * The module hands out `wp_get_attachment_url()` and adds no protection of its
 * own — see the `@security` case in the spec.
 *
 * Dropbox sync is an EXTERNAL integration (`wp-erp-sync-employees-dropbox`,
 * `wp-erp-download-files-from-dropbox`). Nothing here touches it.
 */
export const documentSelectors = {
    fileInput: 'input[type=file]',
    uploadButton: 'button:has-text("Upload"), a:has-text("Upload")',
    createFolder: 'button:has-text("Create Folder"), a:has-text("Create Folder")',
    shareWith: ':text("Share with")',

    row: '#wpbody-content table tbody tr, .erp-doc-file-row',
    listArea: '#wpbody-content',
} as const;

/** Toolbar controls and source tabs the screen renders, captured live. */
export const documentControls = ['Upload', 'Create Folder', 'Move to', 'Delete', 'Share with'] as const;
export const documentSources = ['Owned by me', 'My Dropbox', 'Shared with me'] as const;
export const documentColumns = ['Modified', 'Created by', 'File size', 'Source'] as const;

export class DocumentsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'documents' });
        await this.page.waitForTimeout(2500);
    }

    get fileInput(): Locator {
        return this.page.locator(documentSelectors.fileInput).first();
    }

    /** Uploads a file and waits for the list to show it. */
    /**
     * Uploads content under a caller-chosen name, from memory.
     *
     * A fixed fixture file is the wrong tool here: WordPress DEDUPLICATES an
     * uploaded filename that already exists on disk, so a second run stores
     * `name-1.txt`, then `name-2.txt`, and any assertion on the exact name is
     * only true the first time. Each run therefore uploads its own unique name.
     */
    async uploadContent(fileName: string, content: string): Promise<void> {
        const posted = this.page
            .waitForResponse((r) => (r.request().postData() ?? '').includes('file_dir_ajax_upload'), { timeout: 30_000 })
            .catch(() => undefined);

        await this.fileInput.setInputFiles({ name: fileName, mimeType: 'text/plain', buffer: Buffer.from(content) });
        await posted;
        await this.page.waitForTimeout(2500);

        await this.page
            .waitForFunction((name) => (document.querySelector('#wpbody-content')?.textContent ?? '').includes(name), fileName, { timeout: 20_000 })
            .catch(() => undefined);
    }

    async upload(filePath: string, fileName: string): Promise<void> {
        // The upload posts `file_dir_ajax_upload`; wait for THAT response rather
        // than for the name to appear, so a slow repaint is not mistaken for a
        // failed upload (and vice versa).
        const posted = this.page
            .waitForResponse((r) => (r.request().postData() ?? '').includes('file_dir_ajax_upload'), { timeout: 30_000 })
            .catch(() => undefined);

        await this.fileInput.setInputFiles(filePath);
        await posted;
        await this.page.waitForTimeout(2500);

        await this.page
            .waitForFunction((name) => (document.querySelector('#wpbody-content')?.textContent ?? '').includes(name), fileName, { timeout: 20_000 })
            .catch(() => undefined);
    }

    /** Flattened text of the file/folder area. */
    async listText(): Promise<string> {
        return ((await this.page.locator(documentSelectors.listArea).textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    async hasEntry(name: string): Promise<boolean> {
        return (await this.listText()).includes(name);
    }

    async rendersControls(): Promise<string[]> {
        return this.rendersAll([...documentControls]);
    }

    async rendersSources(): Promise<string[]> {
        return this.rendersAll([...documentSources]);
    }

    /**
     * Fetches a URL with NO credentials at all — a fresh context with no cookies
     * and no auth header, i.e. an anonymous visitor.
     */
    async fetchAnonymously(url: string): Promise<{ status: number; body: string }> {
        const context = await this.page.context().browser()!.newContext({ storageState: { cookies: [], origins: [] } });

        try {
            const response = await context.request.get(url);
            return { status: response.status(), body: await response.text() };
        } finally {
            await context.close();
        }
    }
}
