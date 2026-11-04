import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { restUrl } from '@utils/helpers';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';

/**
 * HRM Document Manager — REST (pro). Module: document_manager.
 *
 * The v1 document routes (`/erp/v1/hrm/docs/*`, DocumentController.php) are no
 * longer registered since erp-pro cc3af5ebf ("check nonce and access on the
 * legacy document AJAX actions"). Every one of them used to fatal (their helper
 * file was never loaded, so each call hit an undefined function → HTTP 500),
 * nothing in the product called them, and the Dropbox route answered logged-out
 * visitors using the company token. The Documents UI uses the legacy admin-ajax
 * actions instead.
 *
 * These specs lock that in: every former route now answers 404 `rest_no_route`
 * for an admin and for a logged-out visitor, and a POST to the old create-folder
 * route writes nothing.
 */

const PREFIX = process.env.DB_PREFIX ?? 'wp';
const REL_TABLE = `${PREFIX}_erp_employee_dir_file_relationship`;

let api: ApiUtils;
let userId = 1;

type Method = 'get' | 'post' | 'put' | 'delete';

const routes = (uid: number): Array<{ id: string; method: Method; path: string; body?: Record<string, unknown> }> => [
    { id: 'GET docs tree', method: 'get', path: `/erp/v1/hrm/docs/${uid}` },
    {
        id: 'POST create folder',
        method: 'post',
        path: `/erp/v1/hrm/docs/${uid}`,
        body: { employee_id: uid, parent_id: 0, dir_name: `pw_folder_${Date.now()}` },
    },
    {
        id: 'PUT rename',
        method: 'put',
        path: `/erp/v1/hrm/docs/${uid}/file/1`,
        body: { employee_id: uid, parent_id: 0, dir_name: 'pw_ren', type: 'folder' },
    },
    { id: 'DELETE file', method: 'delete', path: `/erp/v1/hrm/docs/${uid}/file/1` },
    { id: 'PUT move', method: 'put', path: `/erp/v1/hrm/docs/${uid}/move`, body: { employee_id: uid, parent_id: 0, selectedDirFile: [1] } },
    { id: 'GET search', method: 'get', path: `/erp/v1/hrm/docs/${uid}/search?search_key=pw` },
    { id: 'GET dropbox', method: 'get', path: '/erp/v1/hrm/docs/dropbox' },
];

async function call(client: ApiUtils, method: Method, path: string, body?: Record<string, unknown>) {
    const url = restUrl(path);
    const opts = body ? { data: body } : undefined;
    return method === 'get'
        ? client.get(url, opts, false)
        : method === 'post'
          ? client.post(url, opts, false)
          : method === 'put'
            ? client.put(url, opts, false)
            : client.delete(url, opts, false);
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
    const [meResp, me] = await api.get(endPoints.currentUser, undefined, false);
    if (meResp.ok() && me?.id) userId = Number(me.id);
});

test.afterAll(async () => {
    try {
        await dbUtils.dbQuery(`DELETE FROM ${REL_TABLE} WHERE eid = ? AND dir_name LIKE 'pw_%'`, [userId]);
    } catch {
        /* best-effort cleanup */
    }
    await dbUtils.close();
    await api.dispose();
});

test.describe('HRM docs REST — v1 routes are not registered (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // Route ids are fixed; the user id is only known after beforeAll, so resolve inside the test.
    for (const id of routes(0).map(r => r.id)) {
        test(`DOC-API-${id} → 404 rest_no_route`, { tag: ['@pro', '@hrm', '@admin'] }, async () => {
            const r = routes(userId).find(x => x.id === id)!;
            const [resp, body] = await call(api, r.method, r.path, r.body);
            expect(resp.status(), `${r.method.toUpperCase()} ${r.path} is gone`).toBe(404);
            expect(body?.code, 'WP REST reports no such route').toBe('rest_no_route');
        });
    }

    test('DOC-API-EC-01 POST to the old create-folder route writes no row', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const name = `pw_folder_${Date.now()}`;
        const before = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        const [resp] = await api.post(
            restUrl(`/erp/v1/hrm/docs/${userId}`),
            { data: { employee_id: userId, parent_id: 0, dir_name: name } },
            false,
        );
        expect(resp.status()).toBe(404);
        const after = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        expect(Number(after[0]?.c), 'no folder row was created').toBe(Number(before[0]?.c));
    });
});

test.describe('HRM docs REST — v1 routes are not registered (logged out)', () => {
    test('DOC-API-AC-01 logged-out docs and dropbox requests get 404, not data', { tag: ['@pro', '@hrm', '@employee'] }, async () => {
        // No nonce → WP REST ignores the auth cookie and treats the request as logged out.
        const anon = await ApiUtils.fromStorageState(data.auth.employeeFile, '');
        try {
            for (const r of routes(userId).filter(x => x.id === 'GET docs tree' || x.id === 'GET dropbox')) {
                const [resp] = await call(anon, r.method, r.path, r.body);
                expect(resp.status(), `${r.path} is not served to visitors`).toBe(404);
            }
        } finally {
            await anon.dispose();
        }
    });
});
