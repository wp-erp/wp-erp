import { test, expect } from '@utils/test';
import { DocumentsPage, documentControls, documentSources } from '@pages/hrm/documentsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupDocuments } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

const CONTENT = 'Confidential: salary review notes written by the automated suite.';

test.describe('HR — Documents @pro', () => {
    let page: DocumentsPage;

    test.beforeAll(async () => {
        await cleanupDocuments();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new DocumentsPage(p);
    });

    test.afterAll(async () => {
        await cleanupDocuments();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the documents screen renders its controls and sources', { tag: ['@tier1', '@hrm-documents', '@smoke'] }, async () => {
        await page.goto();

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
        expect(await page.rendersControls(), `all of ${documentControls.join(', ')}`).toEqual([]);
        expect(await page.rendersSources(), `all of ${documentSources.join(', ')}`).toEqual([]);
    });

    test('a file can be uploaded and is recorded against the tree', { tag: ['@tier1', '@hrm-documents', '@crud'] }, async () => {
        const fileName = `${uniqueId('pwerpDoc')}.txt`;

        await page.goto();
        await page.uploadContent(fileName, CONTENT);

        const rows = await query<RowDataPacket[]>(`SELECT id, dir_name, attachment_id, is_dir FROM ${prefix()}erp_employee_dir_file_relationship WHERE dir_name = ?`, [fileName]);

        expect(rows, 'one row is written for the file').toHaveLength(1);
        expect(Number(rows[0]!.is_dir), 'it is a file, not a folder').toBe(0);
        expect(Number(rows[0]!.attachment_id), 'a WordPress attachment backs it').toBeGreaterThan(0);
        expect(await page.hasEntry(fileName), 'the file is listed').toBe(true);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('an uploaded HR document is not readable without logging in', { tag: ['@tier3', '@hrm-documents', '@security'] }, async () => {
        // KNOWN DEFECT — ERP-142. The module stores documents as ordinary media
        // and hands out `wp_get_attachment_url()`, adding no protection: the file
        // answers an anonymous request with its full contents. That contradicts
        // the per-employee ownership and sharing the module itself implements
        // (`erp_dir_file_share`, the "Shared with me" source).
        //
        // Asserted as it should behave; expected to fail until the file layer
        // enforces what the sharing layer promises.
        test.fail();

        const fileName = `${uniqueId('pwerpDoc')}.txt`;

        await page.goto();
        await page.uploadContent(fileName, CONTENT);

        const rows = await query<RowDataPacket[]>(`SELECT attachment_id FROM ${prefix()}erp_employee_dir_file_relationship WHERE dir_name = ?`, [fileName]);
        expect(rows, 'PRECONDITION: the upload landed (the @crud case is the canary if this breaks)').toHaveLength(1);
        const url = await query<RowDataPacket[]>(`SELECT meta_value FROM ${prefix()}postmeta WHERE post_id = ? AND meta_key = '_wp_attached_file'`, [Number(rows[0]!.attachment_id)]);
        const publicUrl = `/wp-content/uploads/${String(url[0]!.meta_value)}`;

        const response = await page.fetchAnonymously(publicUrl);

        expect(response.status, 'an anonymous request is refused').toBeGreaterThanOrEqual(400);
        expect(response.body, 'the contents are not disclosed').not.toContain('Confidential');
    });

    test('Documents is closed to an employee', { tag: ['@tier3', '@hrm-documents', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new DocumentsPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the company Documents screen').toBe(true);
    });
});
