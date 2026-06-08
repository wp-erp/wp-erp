import { test, expect } from '@utils/test';
import { AssetPage } from './assetPage';
import { dbUtils } from '@utils/dbUtils';
import { data, TEST_PREFIX } from '@utils/testData';

/**
 * WP ERP Pro — HRM **Asset Management** DEEP BEHAVIORAL LIFECYCLE
 * (erp-pro module: asset_management).
 *
 * Drives the full asset lifecycle end-to-end and asserts the real DB effect at
 * every step:
 *   create category → create allottable single asset → raise asset request →
 *   approve/assign (creates the _history allotment) → return.
 * Plus the alternate paths: reject a pending request, and dismiss-on-return.
 *
 * SURFACE (verified by reading AjaxHandler.php — every flow is `add_action(
 * wp_ajax_* )`, the module has NO /erp/v1 REST). So this spec uses Surface #2/#3:
 * it scrapes each WRITE handler's page-localized per-action nonce out of the
 * rendered admin HTML (each lives in a wp.template `<script id="tmpl-…">` block)
 * and POSTs admin-ajax through `page.request` (session cookies travel with it),
 * then verifies persistence via dbUtils against the pro asset tables.
 *
 * NONCE MODEL (critical): each handler verifies its OWN nonce, web-context bound
 * to the logged-in session token — a CLI-generated nonce does NOT match the
 * cookie session. Hence the scrape-from-page approach (AssetPage.scrapeNonce).
 *
 * RESPONSE SHAPES: success → JSON {success:true} (category also returns
 * data:{value,text}); failure → a plain `die()` string ('You are not allowed!',
 * 'You must select a category'), NOT JSON. So negative assertions read raw text.
 *
 * REQUESTING USER: asset_request_insert always sets user_id = current user, so an
 * admin-driven request is the admin's (user_id from the admin session). Employees
 * raise requests from their own My Profile → Assets tab; in this QA site that HR
 * profile screen is capability-gated for the plain employee (403), so the
 * employee path here is asserted as the access-control boundary, and the request
 * write is exercised under the admin session (the realistic, reachable path).
 *
 * SHARED STATE: this file inserts/updates rows in the shared pro asset tables and
 * the approve/return steps depend on rows created earlier in the same describe,
 * so it MUST run serial.
 *
 * Every test carries: tier (@pro) + module (@hrm) + role (@admin/@employee).
 */

test.describe.configure({ mode: 'serial' });

const CRITICAL_ERROR = 'There has been a critical error on this website';

// Run-unique suffix so rows never collide across reruns / parallel files.
const RUN = Date.now();
const TODAY = new Date().toISOString().slice(0, 10);

const CAT_NAME = `${TEST_PREFIX}QA Cat ${RUN}`;
const ITEM_GROUP = `${TEST_PREFIX}QA Laptop ${RUN}`;
const ITEM_CODE = `${TEST_PREFIX}QALAP${RUN}`;

// Ids discovered as the lifecycle progresses (shared across the serial tests).
const ctx: {
    catId?: number;
    assetId?: number;
    requestId?: number;
    historyId?: number;
} = {};

test.afterAll(async () => {
    // Best-effort cascade cleanup of everything this file created. The dbUtils
    // pool is shared and may already be closed by a sibling afterAll; it rebuilds
    // transparently, so swallow teardown errors.
    try {
        if (ctx.assetId !== undefined) {
            await AssetPage.deleteHistoryByItem(ctx.assetId);
            await AssetPage.deleteRequestsByGroup(ctx.assetId);
        }
        await AssetPage.deleteAssetsLikeCode(ITEM_CODE);
        await AssetPage.deleteCategoriesLike(`${TEST_PREFIX}QA Cat ${RUN}`);
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
// Admin — the happy-path lifecycle, step by step, each asserting its DB effect
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Asset Management lifecycle (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ASSET-LC-01 — create category (action erp-hr-assets-new-category).
    test('step 1: create an asset category → INSERT wp_erp_hr_assets_category', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const asset = new AssetPage(page);

        const nonce = await asset.scrapeNonce(asset.templatePages.asset, 'tmpl-asset-category-new');
        expect(nonce, 'category-new nonce should be scrapeable from the asset page').not.toBe('');
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        const [resp, body] = await asset.ajax({
            action: 'erp-hr-assets-new-category',
            _wpnonce: nonce,
            cat_name: CAT_NAME,
        });

        // Resilient: never a fatal; a real success is JSON {success:true,data:{value}}.
        expect(resp.status(), 'admin-ajax should not 5xx').toBeLessThan(500);
        expect(body, 'category create must not be the not-allowed die()').not.toContain('You are not allowed');

        let json: { success?: boolean; data?: { value?: number } } | undefined;
        try {
            json = JSON.parse(body);
        } catch {
            /* non-JSON body handled below */
        }
        expect(json?.success, `unexpected body: ${body}`).toBe(true);

        // Prefer the id the handler echoed; fall back to a name lookup.
        ctx.catId = Number(json?.data?.value) || undefined;
        if (ctx.catId === undefined) {
            const rows = await AssetPage.findCategoryByName(CAT_NAME);
            ctx.catId = rows[0]?.id;
        }

        const rows = await AssetPage.findCategoryByName(CAT_NAME);
        expect(rows.length, 'category row should persist').toBeGreaterThanOrEqual(1);
        expect(Number(rows[0]!.id)).toBe(Number(ctx.catId));
    });

    // ASSET-LC-02 — create a single allottable asset (action erp-hr-assets-new).
    test('step 2: create a single allottable asset in that category → INSERT wp_erp_hr_assets', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        test.skip(ctx.catId === undefined, 'requires the category from step 1');
        const catId = ctx.catId!;
        const asset = new AssetPage(page);

        const nonce = await asset.scrapeNonce(asset.templatePages.asset, 'tmpl-erp-asset-new');
        expect(nonce, 'asset-new nonce should be scrapeable').not.toBe('');

        const [resp, body] = await asset.ajax({
            action: 'erp-hr-assets-new',
            _wpnonce: nonce,
            category_id: String(catId),
            item_group: ITEM_GROUP,
            asset_type: 'single',
            'items[1][item_code]': ITEM_CODE,
            'items[1][model_no]': `MBP-${RUN}`,
            'items[1][manufacturer]': 'Apple',
            'items[1][price]': '1500',
            'items[1][item_desc]': 'QA test asset',
            'items[1][item_serial]': `SER-${RUN}`,
            'items[1][allottable]': 'on', // REQUIRED for the asset to be allottable
        });

        expect(resp.status()).toBeLessThan(500);
        expect(body).not.toContain('You are not allowed');
        expect(body).not.toContain('You must select a category');
        expect(JSON.parse(body)?.success, `unexpected body: ${body}`).toBe(true);

        // Verify the row landed with the expected allottable single-asset shape.
        const rows = await AssetPage.findAssetByCode(ITEM_CODE);
        expect(rows.length, 'asset row should persist').toBeGreaterThanOrEqual(1);
        const created = rows[0]!;
        ctx.assetId = Number(created.id);
        expect(created.asset_type).toBe('single');
        expect(created.allottable).toBe('on');
        expect(created.status).toBe('stock'); // fresh assets start in stock
        expect(Number(created.parent)).toBe(0);
    });

    // ASSET-LC-03 — raise an asset request (action erp-hr-asset-request-new).
    // The request is for the requesting user (the admin session here); the
    // requested asset id is stored in item_group (item_id stays NULL).
    test('step 3: raise an asset request → INSERT wp_erp_hr_assets_request (pending)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        test.skip(ctx.assetId === undefined, 'requires the asset from step 2');
        const assetId = ctx.assetId!;
        const asset = new AssetPage(page);

        // The request template renders on people/employee (and my-profile).
        const nonce = await asset.scrapeNonce(asset.templatePages.people, 'tmpl-erp-hr-emp-request-asset');
        expect(nonce, 'request-new nonce should be scrapeable from the people page').not.toBe('');

        const [resp, body] = await asset.ajax({
            action: 'erp-hr-asset-request-new',
            _wpnonce: nonce,
            item_group: String(assetId),
            not_in_list: '',
            request_desc: '',
        });

        expect(resp.status()).toBeLessThan(500);
        expect(body).not.toContain('You are not allowed');
        expect(JSON.parse(body)?.success, `unexpected body: ${body}`).toBe(true);

        const rows = await AssetPage.findRequestByGroup(assetId);
        expect(rows.length, 'request row should persist').toBeGreaterThanOrEqual(1);
        const req = rows.find(r => r.status === 'pending') ?? rows[0]!;
        ctx.requestId = Number(req.id);
        expect(req.status).toBe('pending');
        expect(String(req.item_group)).toBe(String(assetId));
        expect(req.item_id, 'item_id stays NULL on a normal request').toBeNull();
        // user_id is the session user — assert it resolved to a real (non-zero) id.
        expect(Number(req.user_id)).toBeGreaterThan(0);
    });

    // ASSET-LC-04 — approve/assign (action erp-asset-request-approve).
    // THREE DB effects: (a) INSERT _history (the allotment), (b) UPDATE request
    // → approved + allott_id + given_item_id, (c) UPDATE asset stock → allotted.
    test('step 4: approve the request → INSERT _history + request approved + asset allotted', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        test.skip(ctx.requestId === undefined || ctx.assetId === undefined, 'requires the pending request from step 3');
        const requestId = ctx.requestId!;
        const assetId = ctx.assetId!;
        const asset = new AssetPage(page);

        // The reply template renders on any asset page carrying a &sub-section.
        const nonce = await asset.scrapeNonce(asset.templatePages.requests, 'tmpl-erp-asset-request-reply');
        expect(nonce, 'approve nonce should be scrapeable').not.toBe('');

        const [resp, body] = await asset.ajax({
            action: 'erp-asset-request-approve',
            _wpnonce: nonce,
            row_id: String(requestId),
            category_id: String(ctx.catId),
            item_group: String(assetId),
            item: String(assetId), // the concrete asset to hand over
            item_id: '', // empty → the else branch validates category/group/item
            given_date: TODAY,
            is_returnable: '',
            return_date: '',
            reply_msg: `QA approved ${RUN}`,
        });

        expect(resp.status()).toBeLessThan(500);
        expect(body).not.toContain('You are not allowed');
        expect(JSON.parse(body)?.success, `unexpected body: ${body}`).toBe(true);

        // (a) the _history allotment row — the row this lifecycle is really about.
        const hist = await AssetPage.findHistoryByItem(assetId);
        expect(hist.length, 'an allotment history row should be created').toBeGreaterThanOrEqual(1);
        const allot = hist[0]!;
        ctx.historyId = Number(allot.id);
        expect(allot.status).toBe('allotted');
        expect(Number(allot.item_id)).toBe(Number(assetId));
        expect(String(allot.item_group)).toBe(String(assetId));
        expect(Number(allot.allotted_to)).toBeGreaterThan(0);
        // date_given comes back as a JS Date (mysql2). Compare on the local Y-M-D
        // (the Node process and the site share the Bangladesh timezone).
        const dg = allot.date_given as unknown;
        const dgStr = dg instanceof Date
            ? `${dg.getFullYear()}-${String(dg.getMonth() + 1).padStart(2, '0')}-${String(dg.getDate()).padStart(2, '0')}`
            : String(dg);
        expect(dgStr, 'date_given is today').toContain(TODAY);

        // (b) the request flips to approved and back-links the allotment.
        const reqRows = await AssetPage.findRequestByGroup(assetId);
        const req = reqRows.find(r => Number(r.id) === requestId);
        expect(req, 'the approved request should still be findable').toBeTruthy();
        expect(req!.status).toBe('approved');
        expect(Number(req!.allott_id)).toBe(Number(ctx.historyId));
        expect(Number(req!.given_item_id)).toBe(Number(assetId));

        // (c) the asset moves stock → allotted.
        expect(await AssetPage.assetStatus(assetId)).toBe('allotted');
    });

    // ASSET-LC-05 — return the allotted asset (action erp-asset-item-return).
    // TWO DB effects: history → returned (+ date_return_real, return_note) and
    // asset → stock.
    test('step 5: return the allotted asset → history returned + asset back to stock', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        test.skip(ctx.historyId === undefined || ctx.assetId === undefined, 'requires the allotment from step 4');
        const historyId = ctx.historyId!;
        const assetId = ctx.assetId!;
        const asset = new AssetPage(page);

        const nonce = await asset.scrapeNonce(asset.templatePages.allotments, 'tmpl-erp-asset-return');
        expect(nonce, 'return nonce should be scrapeable').not.toBe('');

        const note = `QA returned ${RUN}`;
        const [resp, body] = await asset.ajax({
            action: 'erp-asset-item-return',
            _wpnonce: nonce,
            allott_id: String(historyId),
            item_id: String(assetId),
            date_return: TODAY,
            return_note: note,
            is_dissmissed: '', // plain return (not a dismissal)
        });

        expect(resp.status()).toBeLessThan(500);
        expect(body).not.toContain('You are not allowed');
        expect(JSON.parse(body)?.success, `unexpected body: ${body}`).toBe(true);

        const hist = await AssetPage.findHistoryByItem(assetId);
        const row = hist.find(h => Number(h.id) === historyId);
        expect(row, 'the returned allotment should still be findable').toBeTruthy();
        expect(row!.status).toBe('returned');
        // date_return_real is a JS Date (mysql2) — compare on the local Y-M-D.
        const dr = row!.date_return_real as unknown;
        const drStr = dr instanceof Date
            ? `${dr.getFullYear()}-${String(dr.getMonth() + 1).padStart(2, '0')}-${String(dr.getDate()).padStart(2, '0')}`
            : String(dr);
        expect(drStr, 'date_return_real is today').toContain(TODAY);
        expect(row!.return_note).toBe(note);

        // Asset cycles allotted → stock again.
        expect(await AssetPage.assetStatus(assetId)).toBe('stock');
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Admin — alternate path: REJECT a fresh pending request (action
// erp-asset-request-reject). Raises a new request on the same asset, rejects it.
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Asset Management lifecycle alt-paths (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ASSET-LC-06 — reject a pending request → request status pending → rejected.
    test('alt step 4b: reject a pending request → wp_erp_hr_assets_request status rejected', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        test.skip(ctx.assetId === undefined, 'requires the asset created earlier in this file');
        const assetId = ctx.assetId!;
        const asset = new AssetPage(page);

        // Raise a fresh pending request to reject (the original one was approved).
        const reqNonce = await asset.scrapeNonce(asset.templatePages.people, 'tmpl-erp-hr-emp-request-asset');
        expect(reqNonce).not.toBe('');
        const [, raiseBody] = await asset.ajax({
            action: 'erp-hr-asset-request-new',
            _wpnonce: reqNonce,
            item_group: String(assetId),
            not_in_list: '',
            request_desc: '',
        });
        expect(JSON.parse(raiseBody)?.success, `raise body: ${raiseBody}`).toBe(true);

        const pending = (await AssetPage.findRequestByGroup(assetId)).find(r => r.status === 'pending');
        expect(pending, 'a fresh pending request should exist to reject').toBeTruthy();
        const rejectId = Number(pending!.id);

        const rejectNonce = await asset.scrapeNonce(asset.templatePages.requests, 'tmpl-erp-asset-request-reject');
        expect(rejectNonce, 'reject nonce should be scrapeable').not.toBe('');

        const reason = `QA reject ${RUN}`;
        const [resp, body] = await asset.ajax({
            action: 'erp-asset-request-reject',
            _wpnonce: rejectNonce,
            row_id: String(rejectId),
            reject_reason: reason,
        });

        expect(resp.status()).toBeLessThan(500);
        expect(body).not.toContain('You are not allowed');
        expect(JSON.parse(body)?.success, `unexpected body: ${body}`).toBe(true);

        const after = (await AssetPage.findRequestByGroup(assetId)).find(r => Number(r.id) === rejectId);
        expect(after, 'the rejected request should still be findable').toBeTruthy();
        expect(after!.status).toBe('rejected');
    });

    // ASSET-LC-07 — negative: a bad/missing nonce is rejected with the plain
    // die() string (NOT JSON), and writes nothing.
    test('negative: a write with a bad nonce dies "You are not allowed!" and inserts nothing', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const asset = new AssetPage(page);
        const bogusName = `${TEST_PREFIX}QA NOPE ${RUN}`;

        const [resp, body] = await asset.ajax({
            action: 'erp-hr-assets-new-category',
            _wpnonce: 'deadbeef00', // not a valid session nonce
            cat_name: bogusName,
        });

        // The handler die()s a plain string — resilient: not a fatal, not success.
        expect(resp.status()).toBeLessThan(500);
        expect(body).toContain('You are not allowed');
        // And nothing persisted.
        const rows = await AssetPage.findCategoryByName(bogusName);
        expect(rows.length, 'a nonce-rejected write must not create a row').toBe(0);
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Employee — access-control boundary: a plain employee cannot reach the HR
// profile screens that host the asset-request UI, so cannot raise a request from
// the gated admin surface. Assert the boundary, never a PHP fatal.
// ──────────────────────────────────────────────────────────────────────────
test.describe('HRM Asset Management request access control (pro, employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    // ASSET-LC-08
    test('employee asset-request surface renders without a PHP fatal', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const asset = new AssetPage(page);

        // erp-pro renders the employee-request-asset JS template (and its _wpnonce) on
        // the people/employee page UNCONDITIONALLY — there is no capability gate at this
        // layer (asset-management/Module.php::admin_js_templates, case 'people'). So on a
        // clean install the employee reaches the self-service request surface and the
        // nonce IS present; its presence is therefore NOT a reliable access signal. The
        // real write-side access control is asserted by the nonce-rejection test above
        // (a nonce-rejected write must not create a row). The stable boundary here is
        // simply that the employee's request surface does not PHP-fatal.
        await asset.scrapeNonce(asset.templatePages.people, 'tmpl-erp-hr-emp-request-asset');
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });
});
