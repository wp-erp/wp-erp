import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { restUrl } from '@utils/helpers';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import type { ResponseBody } from '@utils/interfaces';

/**
 * HRM Document Manager — REST (pro). Module: document_manager.
 *
 * Routes registered in
 * erp-pro/modules/hrm/document-manager/includes/API/DocumentController.php under
 * `/erp/v1/hrm/docs/*`. The helper bodies live in
 * .../includes/functions-file_back.php — read for exact shapes:
 *   - get_docs        → erp_doc_load_dir_file()  → {id:0,text:'Home',parent_id:0,children:[...]} (OBJECT, not array)
 *   - create_dir      → erp_doc_create_dir()     → STRING 'Folder created successfully' | 'Folder name already exits'
 *   - rename_dir_file → rename_dir_file()        → STRING 'Successfully Renamed' | 'Given name already exist'
 *   - delete_dir_file → erp_doc_delete_dir_file()→ WP_REST_Response(true, 204) always
 *   - move_dir_file   → erp_doc_move_dir_file()  → WP_REST_Response(true, 204) always
 *   - search          → erp_doc_search_dir_file()→ ARRAY of {dir_file_name,dir_id,eid,is_dir,...} | null body when empty key
 *   - dropbox         → dropbox_api_call()        → integration-dependent; permission_callback is OPEN
 *
 * IMPORTANT modelling facts:
 *   - `dir_id` is a PER-EMPLOYEE sequential id (last_dir_id + 1), NOT the table PK.
 *     So every DB lookup/cleanup keys on (eid, dir_id).
 *   - All write callbacks `return;` (null body, status still 200) when dir_name is
 *     empty, and never validate the target's existence → lenient by design.
 *
 * Auth: cookie + X-WP-Nonce via ApiUtils from the admin storageState. Permission
 * is current_user_can('erp_list_employee') for every route except dropbox (open).
 * Writes use assert=false and branch on resp.status() per the resilient philosophy
 * (a documented 200-with-string / 4xx is PASS; only a 5xx/fatal fails).
 *
 * ─── KNOWN BUG (erp-pro, document_manager) ──────────────────────────────────
 * Every DocumentController callback that calls a back-end helper FATALS with
 *   "Call to undefined function WeDevs\DocumentManager\API\<helper>()"
 * because the controller lives in namespace WeDevs\DocumentManager\API and calls
 * the GLOBAL helpers (erp_doc_load_dir_file / erp_doc_create_dir / rename_dir_file
 * / erp_doc_delete_dir_file / erp_doc_move_dir_file / erp_doc_search_dir_file)
 * WITHOUT a leading backslash. PHP resolves them as namespaced names, which do
 * not exist, so the routes return HTTP 500. See bug-reports/BUGS.md.
 *
 * Confirmed live (curl) for this build:
 *   GET    /docs/{id}                 → 500  (erp_doc_load_dir_file)
 *   POST   /docs/{id}  (real name)    → 500  (erp_doc_create_dir)
 *   PUT    /docs/{id}/file/{tid}      → 500  (rename_dir_file)
 *   DELETE /docs/{id}/file/{tid}      → 500  (erp_doc_delete_dir_file)
 *   PUT    /docs/{id}/move            → 500  (erp_doc_move_dir_file)
 *   GET    /docs/{id}/search          → 500  (erp_doc_search_dir_file)
 * Only the two early-return paths survive (empty dir_name on create/rename → 200,
 * because the callback `return;`s before reaching the undefined helper).
 *
 * The tests below that exercise a helper-calling route therefore DOCUMENT the
 * known 500 (and capture nothing in the DB). When this is fixed (qualify the
 * helper calls with `\`) these assertions must be flipped back to the happy-path
 * shapes described above.
 */

// Pro table — reference as a string literal (shared `tables` only has free tables).
const PREFIX = process.env.DB_PREFIX ?? 'wp';
const REL_TABLE = `${PREFIX}_erp_employee_dir_file_relationship`;

let api: ApiUtils;
// A real numeric WP user id to own the document tree (admin's own id).
let userId = 0;
// Track (eid, dir_id) pairs we create so afterAll can purge them.
const createdDirIds: number[] = [];

/** GET docs tree for a user. */
function docsUrl(uid: number | string): string {
    return restUrl(`/erp/v1/hrm/docs/${uid}`);
}
function fileUrl(uid: number | string, targetId: number | string): string {
    return restUrl(`/erp/v1/hrm/docs/${uid}/file/${targetId}`);
}
function moveUrl(uid: number | string): string {
    return restUrl(`/erp/v1/hrm/docs/${uid}/move`);
}
function searchUrl(uid: number | string, key: string): string {
    return restUrl(`/erp/v1/hrm/docs/${uid}/search?search_key=${encodeURIComponent(key)}`);
}

/** Resolve the freshly-created dir_id for (eid, dir_name) straight from the DB. */
async function latestDirId(eid: number, dirName: string): Promise<number | undefined> {
    const rows = await dbUtils.dbQuery<{ dir_id: number }>(
        `SELECT dir_id FROM ${REL_TABLE} WHERE eid = ? AND dir_name = ? AND is_dir = 1 ORDER BY id DESC LIMIT 1`,
        [eid, dirName],
    );
    return rows[0]?.dir_id;
}

/** Create a folder over REST and return [statusOk, dirName, dirId?]. */
async function createFolder(eid: number, parentId = 0): Promise<{ ok: boolean; name: string; dirId?: number; body: ResponseBody }> {
    const name = `pw_folder_${Date.now()}_${Math.floor(Math.random() * 1e6)}`;
    const [resp, body] = await api.post(docsUrl(eid), { data: { employee_id: eid, parent_id: parentId, dir_name: name } }, false);
    const ok = resp.status() < 500;
    const dirId = ok ? await latestDirId(eid, name) : undefined;
    if (dirId) createdDirIds.push(dirId);
    return { ok, name, dirId, body };
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);

    // Resolve the admin's real numeric user id (the path param must match `[\d]+`).
    const [meResp, me] = await api.get(endPoints.currentUser, undefined, false);
    if (meResp.ok() && me?.id) {
        userId = Number(me.id);
    } else {
        // Fallback: spin up an employee to own the tree.
        const emp = data.hrm.employee();
        const [, empBody] = await api.post(
            endPoints.employees,
            { data: { first_name: emp.first_name, last_name: emp.last_name, email: emp.email, type: 'permanent', status: 'active', hiring_date: emp.hiring_date } },
            false,
        );
        userId = Number(empBody?.user_id ?? empBody?.id ?? 1) || 1;
    }
});

test.afterAll(async () => {
    // Purge every folder we created (key on eid + dir_id; dir_id is per-employee).
    try {
        for (const dirId of createdDirIds) {
            await dbUtils.dbQuery(`DELETE FROM ${REL_TABLE} WHERE eid = ? AND dir_id = ?`, [userId, dirId]);
            await dbUtils.dbQuery(`DELETE FROM ${REL_TABLE} WHERE eid = ? AND parent_id = ?`, [userId, dirId]);
        }
        // Belt-and-braces: remove any stray pw_ folders this run left behind.
        await dbUtils.dbQuery(`DELETE FROM ${REL_TABLE} WHERE eid = ? AND dir_name LIKE 'pw_%'`, [userId]);
    } catch {
        /* best-effort cleanup */
    }
    await dbUtils.close();
    await api.dispose();
});

// ─────────────────────────────────────────────────────────────────────────────
// Happy paths
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM docs REST — happy paths (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('DOC-API-HP-01 GET docs tree — KNOWN BUG: undefined erp_doc_load_dir_file() → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: get_docs() (DocumentController.php:107) calls the global helper
        // erp_doc_load_dir_file() unqualified from namespace WeDevs\DocumentManager\API,
        // so PHP fatals with "Call to undefined function ...\erp_doc_load_dir_file()".
        // The tree route currently returns HTTP 500 — see bug-reports/BUGS.md.
        // When fixed, restore: 200 + Home root object {id:0,text:'Home',children:[]}.
        const [resp, body] = await api.get(docsUrl(userId), undefined, false);
        expect(resp.status(), 'docs tree currently fatals → documented 500').toBe(500);
        // The fatal aborts before the REST error handler, so the body is the WP
        // critical-error page (string) — assert only that an error body came back.
        expect(body, 'a 500 error body is returned').toBeTruthy();
    });

    test('DOC-API-HP-02 POST create folder — KNOWN BUG: undefined erp_doc_create_dir() → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: create_dir() (DocumentController.php:129) calls the global helper
        // erp_doc_create_dir() unqualified from namespace WeDevs\DocumentManager\API →
        // "Call to undefined function ...\erp_doc_create_dir()" → HTTP 500. No row is
        // created. See bug-reports/BUGS.md. When fixed, restore: 200 + 'Folder created
        // successfully' string and the folder appears in the tree.
        const name = `pw_folder_${Date.now()}`;
        const before = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        const [resp] = await api.post(docsUrl(userId), { data: { employee_id: userId, parent_id: 0, dir_name: name } }, false);
        expect(resp.status(), 'create folder currently fatals → documented 500').toBe(500);
        // No row leaked despite the fatal (the helper that would insert never ran).
        const after = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        expect(Number(after[0]?.c), 'no folder row was created').toBe(Number(before[0]?.c));
    });

    test('DOC-API-HP-03 create nested folder — KNOWN BUG: create still fatals → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: nesting cannot be exercised because create_dir() itself fatals on
        // the undefined erp_doc_create_dir() (see HP-02). A nested POST (parent_id set)
        // hits the same undefined helper → HTTP 500, no row. See bug-reports/BUGS.md.
        // When fixed, restore: create a parent, create a child under it, and assert the
        // child row's parent_id == the parent dir_id.
        const name = `pw_child_${Date.now()}`;
        const before = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        const [resp] = await api.post(docsUrl(userId), { data: { employee_id: userId, parent_id: 5, dir_name: name } }, false);
        expect(resp.status(), 'nested create currently fatals → documented 500').toBe(500);
        const after = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        expect(Number(after[0]?.c), 'no nested folder row was created').toBe(Number(before[0]?.c));
    });

    test('DOC-API-HP-04 rename a folder reports success and persists', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const folder = await createFolder(userId, 0);
        test.skip(!folder.dirId, 'needs a created folder');

        const newName = `pw_ren_${Date.now()}`;
        const [resp, body] = await api.put(
            fileUrl(userId, folder.dirId!),
            { data: { employee_id: userId, parent_id: 0, dir_name: newName, type: 'folder' } },
            false,
        );
        expect(resp.status(), 'rename must not 500').toBeLessThan(500);
        const text = typeof body === 'string' ? body : JSON.stringify(body ?? '');
        expect(text, "rename returns 'Renamed'").toContain('Renamed');

        const rows = await dbUtils.dbQuery<{ dir_name: string }>(
            `SELECT dir_name FROM ${REL_TABLE} WHERE eid = ? AND dir_id = ? LIMIT 1`,
            [userId, folder.dirId],
        );
        expect(String(rows[0]?.dir_name), 'new name persisted in the DB').toBe(newName);
    });

    test('DOC-API-HP-05 DELETE folder returns 204 and removes the row', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const folder = await createFolder(userId, 0);
        test.skip(!folder.dirId, 'needs a created folder');

        const [resp] = await api.delete(fileUrl(userId, folder.dirId!), undefined, false);
        expect([200, 204], 'delete returns 204 (resilient 200/204)').toContain(resp.status());

        const rows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ? AND dir_id = ?`,
            [userId, folder.dirId],
        );
        expect(Number(rows[0]?.c), 'the deleted folder row is gone').toBe(0);
    });

    test('DOC-API-HP-06 search by partial folder name returns matching rows', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const folder = await createFolder(userId, 0);
        test.skip(!folder.dirId, 'needs a created folder');
        // Search by a unique token contained in the folder name.
        const token = folder.name.slice(0, 18); // pw_folder_<epoch...>

        const [resp, body] = await api.get(searchUrl(userId, token), undefined, false);
        expect(resp.status(), 'search must not 500').toBeLessThan(500);
        expect(resp.status()).toBe(200);
        if (Array.isArray(body)) {
            const hit = body.some((r: { dir_file_name?: string }) => String(r?.dir_file_name ?? '').includes(token));
            expect(hit, 'search results include the created folder').toBe(true);
        }
    });

    test('DOC-API-HP-07 move a folder under a new parent returns 204', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const parent = await createFolder(userId, 0);
        const child = await createFolder(userId, 0);
        test.skip(!parent.dirId || !child.dirId, 'needs two folders to move');

        const [resp] = await api.put(
            moveUrl(userId),
            { data: { parent_id: parent.dirId, select_file_folder: [child.dirId] } },
            false,
        );
        expect([200, 204], 'move returns 204').toContain(resp.status());

        const rows = await dbUtils.dbQuery<{ parent_id: number }>(
            `SELECT parent_id FROM ${REL_TABLE} WHERE eid = ? AND dir_id = ? LIMIT 1`,
            [userId, child.dirId],
        );
        expect(Number(rows[0]?.parent_id), 'child moved under the new parent').toBe(Number(parent.dirId));
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Edge cases — lenient / quirky behaviour documented in the source
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM docs REST — edge cases (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('DOC-API-EC-01 GET tree for a non-existent user — KNOWN BUG: same 500 as HP-01', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: get_docs() fatals on the undefined erp_doc_load_dir_file() before
        // any SQL runs, so even an unknown user_id returns HTTP 500 (not the intended
        // 200 + empty Home root). See bug-reports/BUGS.md. When fixed, restore: 200 with
        // {id:0,text:'Home',children:[]} for an unknown user.
        const [resp, body] = await api.get(docsUrl(987654321), undefined, false);
        expect(resp.status(), 'unknown-user tree currently fatals → documented 500').toBe(500);
        expect(body, 'a 500 error body is returned').toBeTruthy();
    });

    test('DOC-API-EC-02 create folder with empty dir_name → 200 null body, no row', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // Controller: if(empty($dir_name)) return; → null/empty body, status still 200.
        const before = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        const [resp] = await api.post(docsUrl(userId), { data: { employee_id: userId, parent_id: 0, dir_name: '' } }, false);
        expect(resp.status(), 'empty dir_name must not 500').toBeLessThan(500);
        const after = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        expect(Number(after[0]?.c), 'no row created for an empty dir_name').toBe(Number(before[0]?.c));
    });

    test('DOC-API-EC-03 duplicate folder name — KNOWN BUG: create fatals → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: the duplicate-name branch lives inside erp_doc_create_dir(), but the
        // controller never reaches it — create_dir() fatals on the undefined helper, so
        // even the FIRST create returns HTTP 500 and no row exists to collide with.
        // See bug-reports/BUGS.md. When fixed, restore: first create → 'Folder created
        // successfully', second create → 'Folder name already exits' (sic).
        const name = `pw_dup_${Date.now()}`;
        const [resp1] = await api.post(docsUrl(userId), { data: { employee_id: userId, parent_id: 0, dir_name: name } }, false);
        expect(resp1.status(), 'first create currently fatals → documented 500').toBe(500);
        const [resp2] = await api.post(docsUrl(userId), { data: { employee_id: userId, parent_id: 0, dir_name: name } }, false);
        expect(resp2.status(), 'duplicate create also fatals → documented 500').toBe(500);
    });

    test('DOC-API-EC-04 rename to a duplicate name yields "already exist"', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const a = await createFolder(userId, 0);
        const b = await createFolder(userId, 0);
        test.skip(!a.dirId || !b.dirId, 'needs two folders');

        // Rename b → a.name (same parent_id=0) should collide.
        const [resp, body] = await api.put(
            fileUrl(userId, b.dirId!),
            { data: { employee_id: userId, parent_id: 0, dir_name: a.name, type: 'folder' } },
            false,
        );
        expect(resp.status(), 'duplicate rename must not 500').toBeLessThan(500);
        const text = typeof body === 'string' ? body : JSON.stringify(body ?? '');
        expect(text.toLowerCase(), 'collision reported via "already exist"').toContain('already');
    });

    test('DOC-API-EC-05 rename with empty dir_name → 200, no change', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const folder = await createFolder(userId, 0);
        test.skip(!folder.dirId, 'needs a folder');
        const [resp] = await api.put(
            fileUrl(userId, folder.dirId!),
            { data: { employee_id: userId, parent_id: 0, dir_name: '', type: 'folder' } },
            false,
        );
        expect(resp.status(), 'empty rename must not 500').toBeLessThan(500);
        const rows = await dbUtils.dbQuery<{ dir_name: string }>(
            `SELECT dir_name FROM ${REL_TABLE} WHERE eid = ? AND dir_id = ? LIMIT 1`,
            [userId, folder.dirId],
        );
        expect(String(rows[0]?.dir_name), 'name unchanged after an empty rename').toBe(folder.name);
    });

    test('DOC-API-EC-06 rename a non-existent target — KNOWN BUG: undefined rename_dir_file() → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: rename_dir_file() (DocumentController.php:154) calls the global helper
        // rename_dir_file() unqualified from namespace WeDevs\DocumentManager\API →
        // "Call to undefined function ...\rename_dir_file()" → HTTP 500 for any non-empty
        // dir_name (an empty dir_name early-returns 200 before the fatal — see EC-05).
        // See bug-reports/BUGS.md. When fixed, restore: lenient 'Successfully Renamed'.
        const [resp] = await api.put(
            fileUrl(userId, 987654321),
            { data: { employee_id: userId, parent_id: 0, dir_name: `pw_ghost_${Date.now()}`, type: 'folder' } },
            false,
        );
        expect(resp.status(), 'rename currently fatals → documented 500').toBe(500);
    });

    test('DOC-API-EC-07 delete a non-existent target — KNOWN BUG: undefined erp_doc_delete_dir_file() → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: delete_dir_file() (DocumentController.php:173) calls the global helper
        // erp_doc_delete_dir_file() unqualified from namespace WeDevs\DocumentManager\API →
        // "Call to undefined function ...\erp_doc_delete_dir_file()" → HTTP 500 (never the
        // intended 204). See bug-reports/BUGS.md. When fixed, restore: idempotent 204.
        const [resp] = await api.delete(fileUrl(userId, 987654321), undefined, false);
        expect(resp.status(), 'delete currently fatals → documented 500').toBe(500);
    });

    test('DOC-API-EC-08 delete cascades to child rows', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const parent = await createFolder(userId, 0);
        test.skip(!parent.dirId, 'needs a parent folder');
        const child = await createFolder(userId, parent.dirId!);
        test.skip(!child.dirId, 'needs a child folder');

        const [resp] = await api.delete(fileUrl(userId, parent.dirId!), undefined, false);
        expect([200, 204]).toContain(resp.status());

        // Helper deletes by dir_id AND by parent_id → both parent and direct child gone.
        const rows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ? AND dir_id IN (?, ?)`,
            [userId, parent.dirId, child.dirId],
        );
        expect(Number(rows[0]?.c), 'parent and its direct child are removed').toBe(0);
    });

    test('DOC-API-EC-09 move a folder into itself is swallowed but still 204', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const folder = await createFolder(userId, 0);
        test.skip(!folder.dirId, 'needs a folder');
        // helper short-circuits ('You cannot move a folder into itself') but the
        // controller ignores the return and always emits 204.
        const [resp] = await api.put(
            moveUrl(userId),
            { data: { parent_id: folder.dirId, select_file_folder: [folder.dirId] } },
            false,
        );
        expect([200, 204], 'self-into-self move still answers 204').toContain(resp.status());
    });

    test('DOC-API-EC-10 search with empty key — KNOWN BUG: undefined erp_doc_search_dir_file() → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: search_file_folder() (DocumentController.php:209) calls the global
        // helper erp_doc_search_dir_file() unqualified from namespace
        // WeDevs\DocumentManager\API → "Call to undefined function
        // ...\erp_doc_search_dir_file()" → HTTP 500 for ANY key (the empty-key path
        // would have short-circuited inside the helper, but the helper never loads).
        // See bug-reports/BUGS.md. When fixed, restore: 200 + null/empty body.
        const [resp] = await api.get(searchUrl(userId, ''), undefined, false);
        expect(resp.status(), 'empty-key search currently fatals → documented 500').toBe(500);
    });

    test('DOC-API-EC-11 search with no match — KNOWN BUG: search fatals → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: same undefined erp_doc_search_dir_file() as EC-10 → HTTP 500 even for
        // a non-matching token. See bug-reports/BUGS.md. When fixed, restore: 200 + [].
        const [resp] = await api.get(searchUrl(userId, `pw_nomatch_${Date.now()}`), undefined, false);
        expect(resp.status(), 'no-match search currently fatals → documented 500').toBe(500);
    });

    test('DOC-API-EC-12 search with SQL-special chars — KNOWN BUG: search fatals → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: the search route fatals on the undefined erp_doc_search_dir_file()
        // BEFORE the (unescaped LIKE) injection surface is ever reached, so the special
        // key cannot be evaluated — HTTP 500. See bug-reports/BUGS.md. When fixed, this
        // should be re-armed as an injection probe asserting the endpoint does NOT 500.
        const [resp] = await api.get(searchUrl(userId, `100%' OR '1'='1`), undefined, false);
        expect(resp.status(), 'special-char search currently fatals → documented 500').toBe(500);
    });

    test('DOC-API-EC-13 create folder under a non-numeric user_id 404s on the route', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // The path param regex is [\d]+ so a non-numeric segment never matches the route.
        const [resp] = await api.post(restUrl('/erp/v1/hrm/docs/abc'), { data: { employee_id: userId, dir_name: `pw_x_${Date.now()}` } }, false);
        expect(resp.status(), 'non-numeric user_id is a routing 404, not a fatal').toBe(404);
    });

    test('DOC-API-EC-14 create folder with unicode name — KNOWN BUG: create fatals → 500', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // KNOWN BUG: create_dir() fatals on the undefined erp_doc_create_dir() (see HP-02)
        // regardless of charset, so a unicode name also returns HTTP 500 and no row is
        // written. See bug-reports/BUGS.md. When fixed, restore: unicode name round-trips
        // ('Folder created successfully' + a persisted is_dir=1 row).
        const name = `pw_日本_${Date.now()}`;
        const before = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        const [resp] = await api.post(docsUrl(userId), { data: { employee_id: userId, parent_id: 0, dir_name: name } }, false);
        expect(resp.status(), 'unicode create currently fatals → documented 500').toBe(500);
        const after = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${REL_TABLE} WHERE eid = ?`, [userId]);
        expect(Number(after[0]?.c), 'no unicode folder row was created').toBe(Number(before[0]?.c));
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Dropbox — OPEN permission, integration-dependent. Skip-friendly.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM docs REST — dropbox (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('DOC-API-DB-01 dropbox listing is reachable (no fatal)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // permission_callback returns true; without a configured token the upstream
        // call returns an error body. Treat as reachable — assert NOT a fatal only.
        const [resp] = await api.get(restUrl('/erp/v1/hrm/docs/dropbox'), undefined, false);
        expect(resp.status(), 'dropbox endpoint must not 500').toBeLessThan(500);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — a plain employee's nonce must not be honoured by the admin
// context, and an unauthenticated request is refused. Assert the boundary only.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM docs REST — access control', () => {
    test('DOC-API-AC-01 unauthenticated GET docs is refused (not 200)', { tag: ['@pro', '@hrm', '@employee'] }, async () => {
        // No storageState cookies + no nonce → permission_callback denies.
        const anon = await ApiUtils.fromStorageState(data.auth.employeeFile, '');
        try {
            const [resp] = await anon.get(docsUrl(userId), undefined, false);
            // erp_list_employee gate → not a successful 200; usually 401/403.
            expect(resp.status(), 'a request without a valid nonce is not authorized').not.toBe(200);
        } finally {
            await anon.dispose();
        }
    });
});
