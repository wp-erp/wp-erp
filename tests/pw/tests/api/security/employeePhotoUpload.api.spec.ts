import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';

/**
 * POST /erp/v1/hrm/employees/upload is open to every employee, so it must only take images.
 * The HR v1 routes are served by WP ERP Pro, so these run on Pro runs only.
 * Also guards the regression where passing upload overrides dropped `test_form => false`
 * and every upload failed with "Invalid form submission".
 */

// 1x1 transparent PNG
const PNG_1x1 = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', 'base64');

let api: ApiUtils;

async function upload(file: { name: string; mimeType: string; buffer: Buffer }): Promise<[number, Record<string, unknown>]> {
    const nonce = process.env.X_WP_NONCE ?? '';
    const res = await api.request.post(endPoints.employeePhotoUpload, {
        headers: nonce ? { 'X-WP-Nonce': nonce } : {},
        multipart: { image: file },
    });
    const body = (await res.json().catch(() => ({}))) as Record<string, unknown>;
    return [res.status(), body];
}

async function removeAttachment(id: number) {
    if (id > 0) {
        await dbUtils.dbQuery(`DELETE FROM ${tables.posts} WHERE ID = ? AND post_type = 'attachment'`, [id]).catch(() => {});
    }
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    await api.dispose();
});

test.describe('Employee photo upload only accepts images', () => {
    test('a PNG uploads and returns an attachment id', { tag: ['@pro', '@hrm', '@security'] }, async () => {
        const [status, body] = await upload({ name: 'avatar.png', mimeType: 'image/png', buffer: PNG_1x1 });
        const photoId = Number(body.photo_id ?? 0);
        expect(status).toBe(200);
        expect(photoId).toBeGreaterThan(0);
        await removeAttachment(photoId);
    });

    test('a .php file is rejected', { tag: ['@pro', '@hrm', '@security'] }, async () => {
        const [status, body] = await upload({ name: 'shell.php', mimeType: 'application/x-php', buffer: Buffer.from('<?php echo 1;') });
        expect(status).toBe(400);
        expect(body.code).toBe('rest_photo_not_image');
    });

    test('a .pdf file is rejected', { tag: ['@pro', '@hrm', '@security'] }, async () => {
        const [status, body] = await upload({ name: 'doc.pdf', mimeType: 'application/pdf', buffer: Buffer.from('%PDF-1.4\n%%EOF') });
        expect(status).toBe(400);
        expect(body.code).toBe('rest_photo_not_image');
    });

    test('a non-image disguised as .png does not become an attachment', { tag: ['@pro', '@hrm', '@security'] }, async () => {
        const [status, body] = await upload({ name: 'evil.png', mimeType: 'image/png', buffer: Buffer.from('<?php echo 1;') });
        const photoId = Number(body.photo_id ?? 0);
        await removeAttachment(photoId);
        expect(status).toBe(400);
        expect(photoId).toBe(0);
    });
});
