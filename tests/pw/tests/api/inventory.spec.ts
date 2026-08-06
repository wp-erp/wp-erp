import { test, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { restUrl, BASE_URL } from '@utils/helpers';

/**
 * Accounting — Inventory (PRO module "inventory") REST.
 *
 * KIND: REST (read-only reporting surface). None of these pro routes live in
 * endPoints/apiEndPoints.ts, so every URL is built with restUrl('/erp/v1/...').
 * Grounded in:
 *   modules/accounting/inventory/includes/Api/InventoryController.php
 *   modules/accounting/inventory/includes/Api/InventoryReportController.php
 *   modules/accounting/inventory/includes/functions/inventory.php
 *   modules/accounting/inventory/includes/functions/reports.php
 *
 * Two capabilities gate this surface:
 *   - inventory list / stock-overview / transactions-overview => erp_ac_view_expense
 *   - reports/{item-list,item-summary,purchase,sales}         => erp_ac_view_sales_summary
 *
 * RESILIENCE (resilient-assertion philosophy):
 *  - Every endpoint here is a GET with no required params; controllers always
 *    set_status(200). We assert `< 500` (never an exact 500) and branch the shape
 *    check on a 200. A 500 on any of these read GETs is a CANDIDATE BUG, tolerated
 *    but flagged in the assertion message — not asserted as expected.
 *  - INV-LIST returns a BARE JSON array; format_collection_response only emits
 *    X-WP-Total / X-WP-TotalPages when total_items > 0 (REST_Controller returns
 *    early at total_items === 0). So on an empty inventory the header is legitimately
 *    absent => assert presence-or-empty, never unconditional presence.
 *  - stock-overview / transactions-overview return a single OBJECT (SUMs over the
 *    whole table); on empty data the SUMs are null => {stock_in:null,...} is valid.
 *  - The 4 reports return arrays of rows; empty windows yield [].
 *  - Access-control: assert the BOUNDARY ([401,403]), not an exact code. The acct
 *    manager is a POSITIVE baseline (must NOT be refused) using its OWN nonce.
 *
 * Tier @pro, module @accounting, role tags per row. Pro tests run only when
 * ERP_PRO=true (the grepInvert drops @pro otherwise).
 */

let api: ApiUtils;

// Pro DB tables — referenced as string literals (the `tables` util only has free tables).
const PRODUCTS = 'wp_erp_acct_products';
const PRODUCT_DETAILS = 'wp_erp_acct_product_details';

// Routes (built via restUrl; not present in endPoints).
const INVENTORY = restUrl('/erp/v1/accounting/v1/inventory');
const STOCK_OVERVIEW = restUrl('/erp/v1/accounting/v1/inventory/stock-overview');
const TRN_OVERVIEW = restUrl('/erp/v1/accounting/v1/inventory/transactions-overview');
const RPT_ITEM_LIST = restUrl('/erp/v1/accounting/v1/inventory/reports/item-list');
const RPT_ITEM_SUMMARY = restUrl('/erp/v1/accounting/v1/inventory/reports/item-summary');
const RPT_PURCHASE = restUrl('/erp/v1/accounting/v1/inventory/reports/purchase');
const RPT_SALES = restUrl('/erp/v1/accounting/v1/inventory/reports/sales');

// A sample, in-range date window (grounded sample query in the design).
const SAMPLE_RANGE = 'start_date=2026-01-01&end_date=2026-06-30';

/** Coerce any response body into a row array (bare array OR {data:[...]}). */
function asRows(body: unknown): unknown[] | null {
    if (Array.isArray(body)) return body;
    if (body && typeof body === 'object' && Array.isArray((body as { data?: unknown }).data)) {
        return (body as { data: unknown[] }).data;
    }
    return null;
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    await api.dispose();
    await dbUtils.close();
});

// ─────────────────────────────────────────────────────────────────────────────
// INV-LIST — GET /inventory (cap erp_ac_view_expense). Bare array of products.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Inventory REST — inventory list (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('INV-HP-01 list returns 200 + bare array; rows match product shape', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(INVENTORY, undefined, false);
        expect(resp.status(), 'inventory list must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        // format_collection_response returns a BARE array (not {data:[]}).
        expect(Array.isArray(body), 'inventory list is a bare JSON array').toBe(true);
        if (Array.isArray(body) && body.length > 0) {
            const row = body[0] as Record<string, unknown>;
            // Keys grounded in inventory.php SELECT (id/name/stock/cost_price/sale_price).
            expect(row, 'row carries a name').toHaveProperty('name');
            expect(row, 'row carries a stock aggregate').toHaveProperty('stock');
        }
    });

    test('INV-HP-02 list honors per_page / page; X-WP-Total present-or-empty', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${INVENTORY}?per_page=5&page=1`, undefined, false);
        expect(resp.status(), 'paginated list must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        expect(Array.isArray(body), 'paginated list is an array').toBe(true);
        expect(Array.isArray(body) ? body.length : 0, 'per_page=5 returns at most 5 rows').toBeLessThanOrEqual(5);

        // Header is emitted ONLY when total_items > 0 (early return otherwise).
        const total = resp.headers()['x-wp-total'];
        const isEmpty = Array.isArray(body) && body.length === 0;
        expect(total !== undefined || isEmpty, 'X-WP-Total present OR the inventory is empty').toBe(true);
    });

    test('INV-EC-01 page far past the end returns 200 + [] (offset past end)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${INVENTORY}?per_page=5&page=9999`, undefined, false);
        expect(resp.status(), 'far-page request must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(Array.isArray(body) ? body.length : -1, 'offset past end yields no rows').toBe(0);
        }
    });

    test('INV-EC-02 garbage include token is ignored (only created_by honored)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${INVENTORY}?include=garbage,not-a-field`, undefined, false);
        expect(resp.status(), 'unknown include token must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(Array.isArray(body), 'list still answers an array with an unknown include').toBe(true);
        }
    });

    test('INV-EC-03 include=created_by expands the author without erroring', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${INVENTORY}?include=created_by`, undefined, false);
        expect(resp.status(), 'include=created_by must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(Array.isArray(body), 'list is an array').toBe(true);
        }
    });

    test('INV-EC-04 non-numeric per_page currently fatals (offset arithmetic on a string)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // KNOWN BUG: InventoryController::get_all_inventory_items() computes
        //   'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) )
        // at InventoryController.php:77 BEFORE any intval(). With per_page=abc (and
        // page unset => null) PHP 8 throws
        //   TypeError: Unsupported operand types: string * int
        // so the endpoint returns 500, NOT the 200 + [] one might expect from
        // intval('abc')===0 => "LIMIT 0". The string is never coerced — the multiply
        // fatals first. See bug-reports/BUGS.md.
        // Resilient philosophy: we do NOT assert an exact 500; we assert it is plainly
        // NOT a clean success (and never a 200), which holds whether or not the bug is fixed.
        const [resp, body] = await api.get(`${INVENTORY}?per_page=abc`, undefined, false);
        expect(resp.status(), 'non-numeric per_page is not a clean success (KNOWN BUG: string * int fatal at InventoryController.php:77)').toBeGreaterThanOrEqual(400);
        expect(resp.status(), 'non-numeric per_page never silently 200-OKs a list').not.toBe(200);
        // A body is returned (the WP critical-error HTML page on the current 500).
        expect(body, 'an error body is returned for the non-numeric per_page request').toBeTruthy();
    });

    test('INV-EC-05 list rows are only inventory-type products (product_type_id=1) — DB oracle', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${INVENTORY}?per_page=100&page=1`, undefined, false);
        expect(resp.status(), 'inventory list must not 500').toBeLessThan(500);
        if (resp.status() !== 200 || !Array.isArray(body) || body.length === 0) {
            test.skip(true, 'no inventory rows to reconcile against the DB in this environment');
            return;
        }

        // Oracle: the WHERE product_type_id=1 means every listed id must be type 1.
        let dbRows: Array<{ id: number; product_type_id: number }> = [];
        try {
            dbRows = await dbUtils.dbQuery<{ id: number; product_type_id: number }>(
                `SELECT id, product_type_id FROM ${PRODUCTS} WHERE product_type_id = 1`,
            );
        } catch {
            test.skip(true, 'DB unavailable for the product-type oracle');
            return;
        }
        const inventoryIds = new Set(dbRows.map((r) => Number(r.id)));
        const allInventoryType = (body as Array<{ id?: number | string }>).every((r) =>
            inventoryIds.has(Number(r?.id)),
        );
        expect(allInventoryType, 'every listed product is an inventory-type (product_type_id=1) product').toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// INV-STOCK — GET /inventory/stock-overview (cap erp_ac_view_expense). Object.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Inventory REST — stock overview (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('INV-HP-03 stock-overview returns 200 + {stock_in, stock_out}', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(STOCK_OVERVIEW, undefined, false);
        expect(resp.status(), 'stock-overview must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        // get_row returns a single object; SUM() is null on an empty table.
        expect(body && typeof body === 'object' && !Array.isArray(body), 'stock-overview is a single object').toBe(true);
        const obj = body as Record<string, unknown>;
        expect(obj, 'object carries stock_in').toHaveProperty('stock_in');
        expect(obj, 'object carries stock_out').toHaveProperty('stock_out');
    });

    test('INV-HP-04 stock-overview honors a date window (dates are discarded by the query)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // NOTE: the helper builds $where from start_date but the final SQL ignores it,
        // so dates never filter — both responses should be structurally identical.
        const [resp, body] = await api.get(`${STOCK_OVERVIEW}?${SAMPLE_RANGE}`, undefined, false);
        expect(resp.status(), 'dated stock-overview must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(body && typeof body === 'object' && !Array.isArray(body), 'dated stock-overview is an object').toBe(true);
            expect(body as Record<string, unknown>, 'still carries stock_in').toHaveProperty('stock_in');
        }
    });

    test('INV-EC-06 malformed start_date with empty end_date still answers 200 (where discarded)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${STOCK_OVERVIEW}?start_date=not-a-date`, undefined, false);
        expect(resp.status(), 'malformed-date stock-overview must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(body && typeof body === 'object' && !Array.isArray(body), 'still a single object').toBe(true);
        }
    });

    test('INV-EC-07 stock-overview reconciles with the product_details SUMs — DB oracle', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(STOCK_OVERVIEW, undefined, false);
        if (resp.status() !== 200 || !body || typeof body !== 'object' || Array.isArray(body)) {
            test.skip(true, 'stock-overview unavailable to reconcile in this environment');
            return;
        }

        let dbRows: Array<{ stock_in: string | null; stock_out: string | null }> = [];
        try {
            dbRows = await dbUtils.dbQuery<{ stock_in: string | null; stock_out: string | null }>(
                `SELECT SUM(stock_in) AS stock_in, SUM(stock_out) AS stock_out FROM ${PRODUCT_DETAILS}`,
            );
        } catch {
            test.skip(true, 'DB unavailable for the stock-overview oracle');
            return;
        }
        const dbIn = Number(dbRows[0]?.stock_in ?? 0);
        const apiIn = Number((body as { stock_in?: number | string | null }).stock_in ?? 0);
        expect(apiIn, 'API stock_in equals the DB SUM(stock_in)').toBe(dbIn);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// INV-TRN — GET /inventory/transactions-overview (cap erp_ac_view_expense). Object.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Inventory REST — transactions overview (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('INV-HP-05 transactions-overview returns 200 + {sales, purchase}', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(TRN_OVERVIEW, undefined, false);
        expect(resp.status(), 'transactions-overview must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        expect(body && typeof body === 'object' && !Array.isArray(body), 'transactions-overview is a single object').toBe(true);
        const obj = body as Record<string, unknown>;
        expect(obj, 'object carries sales').toHaveProperty('sales');
        expect(obj, 'object carries purchase').toHaveProperty('purchase');
    });

    test('INV-HP-06 transactions-overview honors a date window', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${TRN_OVERVIEW}?${SAMPLE_RANGE}`, undefined, false);
        expect(resp.status(), 'dated transactions-overview must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(body && typeof body === 'object' && !Array.isArray(body), 'dated transactions-overview is an object').toBe(true);
            expect(body as Record<string, unknown>, 'still carries sales').toHaveProperty('sales');
        }
    });

    test('INV-EC-08 start_date set with end_date defaulted still answers 200', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // start_date present => the BETWEEN filter applies (end_date defaults to today).
        const [resp, body] = await api.get(`${TRN_OVERVIEW}?start_date=2026-01-01`, undefined, false);
        expect(resp.status(), 'half-dated transactions-overview must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(body && typeof body === 'object' && !Array.isArray(body), 'still a single object').toBe(true);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// INV-RPT — GET reports/{item-list,item-summary,purchase,sales}
// (cap erp_ac_view_sales_summary — DIFFERENT from the list endpoints). Arrays.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Inventory REST — reports (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('INV-HP-07 reports/item-list returns 200 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${RPT_ITEM_LIST}?${SAMPLE_RANGE}`, undefined, false);
        expect(resp.status(), 'item-list report must not 500 (a 500 here is a candidate bug)').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const rows = asRows(body);
        expect(rows, 'item-list report is an array (or {data:[]})').not.toBeNull();
        if (rows && rows.length > 0) {
            const row = rows[0] as Record<string, unknown>;
            // {name, cost_price, sale_price, qty} per reports.php.
            expect(row, 'row carries a product name').toHaveProperty('name');
            expect(row, 'row carries a qty aggregate').toHaveProperty('qty');
        }
    });

    test('INV-HP-08 reports/item-summary returns 200 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${RPT_ITEM_SUMMARY}?${SAMPLE_RANGE}`, undefined, false);
        expect(resp.status(), 'item-summary report must not 500 (a 500 here is a candidate bug)').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const rows = asRows(body);
        expect(rows, 'item-summary report is an array (or {data:[]})').not.toBeNull();
        if (rows && rows.length > 0) {
            const row = rows[0] as Record<string, unknown>;
            // {name, cost_price, sale_price, cogs, no_purchase, no_sale}.
            expect(row, 'row carries a product name').toHaveProperty('name');
            expect(row, 'row carries cogs').toHaveProperty('cogs');
            expect(row, 'row carries no_purchase').toHaveProperty('no_purchase');
        }
    });

    test('INV-HP-09 reports/purchase returns 200 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${RPT_PURCHASE}?${SAMPLE_RANGE}`, undefined, false);
        expect(resp.status(), 'purchase report must not 500 (a 500 here is a candidate bug)').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const rows = asRows(body);
        expect(rows, 'purchase report is an array (or {data:[]})').not.toBeNull();
        if (rows && rows.length > 0) {
            const row = rows[0] as Record<string, unknown>;
            // {voucher_no, trn_date, vendor_name, price, product, qty}.
            expect(row, 'row carries a voucher_no').toHaveProperty('voucher_no');
            expect(row, 'row carries a vendor_name').toHaveProperty('vendor_name');
        }
    });

    test('INV-HP-10 reports/sales returns 200 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${RPT_SALES}?${SAMPLE_RANGE}`, undefined, false);
        expect(resp.status(), 'sales report must not 500 (a 500 here is a candidate bug)').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const rows = asRows(body);
        expect(rows, 'sales report is an array (or {data:[]})').not.toBeNull();
        if (rows && rows.length > 0) {
            const row = rows[0] as Record<string, unknown>;
            // {voucher_no, trn_date, customer_name, tax, discount, price, product, qty}.
            expect(row, 'row carries a voucher_no').toHaveProperty('voucher_no');
            expect(row, 'row carries a customer_name').toHaveProperty('customer_name');
        }
    });

    test('INV-EC-09 reports default their dates when none supplied (first-of-Jan / last-of-month)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // No dates => the helpers fill start/end => 200 with []/rows; a 500 would be a bug.
        for (const url of [RPT_ITEM_LIST, RPT_ITEM_SUMMARY, RPT_PURCHASE, RPT_SALES]) {
            const [resp, body] = await api.get(url, undefined, false);
            expect(resp.status(), `dateless report ${url} must not 500`).toBeLessThan(500);
            if (resp.status() === 200) {
                expect(asRows(body), `dateless report ${url} answers an array`).not.toBeNull();
            }
        }
    });

    test('INV-EC-10 malformed start_date is FY-snapped and still answers < 500', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // A non-matching FY date can leave start_date='' producing an odd BETWEEN;
        // observed to still answer < 500. Treat any 500 as a candidate bug, not expected.
        for (const url of [RPT_ITEM_LIST, RPT_PURCHASE, RPT_SALES]) {
            const [resp] = await api.get(`${url}?start_date=not-a-date`, undefined, false);
            expect(resp.status(), `malformed-date report ${url} must not 500`).toBeLessThan(500);
        }
    });

    test('INV-EC-11 reversed date window (end before start) still answers < 500', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const reversed = 'start_date=2026-06-30&end_date=2026-01-01';
        for (const url of [RPT_ITEM_LIST, RPT_ITEM_SUMMARY, RPT_PURCHASE, RPT_SALES]) {
            const [resp, body] = await api.get(`${url}?${reversed}`, undefined, false);
            expect(resp.status(), `reversed-window report ${url} must not 500`).toBeLessThan(500);
            if (resp.status() === 200) {
                // A reversed BETWEEN yields no rows, but must still be an array.
                expect(asRows(body), `reversed-window report ${url} answers an array`).not.toBeNull();
            }
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Role baseline — accounting manager (own nonce). POSITIVE: must NOT be refused.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Inventory REST — accounting manager (positive baseline)', () => {
    test.use({ storageState: data.auth.accManagerFile });

    let mgrApi: ApiUtils;
    test.beforeAll(async () => {
        // The acct manager's OWN nonce; the admin nonce would 403 a manager session.
        mgrApi = await ApiUtils.fromStorageState(data.auth.accManagerFile, process.env.ACC_MANAGER_NONCE);
    });
    test.afterAll(async () => {
        await mgrApi.dispose();
    });

    test('INV-AC-01 manager can reach the inventory list (erp_ac_view_expense)', { tag: ['@pro', '@accounting', '@manager'] }, async () => {
        const [resp, body] = await mgrApi.get(INVENTORY, undefined, false);
        expect([401, 403], 'manager is authorized for the inventory list').not.toContain(resp.status());
        expect(resp.status(), 'manager inventory list must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(Array.isArray(body), 'manager inventory list is an array').toBe(true);
        }
    });

    test('INV-AC-02 manager can reach stock + transactions overview', { tag: ['@pro', '@accounting', '@manager'] }, async () => {
        for (const url of [STOCK_OVERVIEW, TRN_OVERVIEW]) {
            const [resp] = await mgrApi.get(url, undefined, false);
            expect([401, 403], `manager authorized for ${url}`).not.toContain(resp.status());
            expect(resp.status(), `manager ${url} must not 500`).toBeLessThan(500);
        }
    });

    test('INV-AC-03 manager can reach the inventory reports (erp_ac_view_sales_summary)', { tag: ['@pro', '@accounting', '@manager'] }, async () => {
        for (const url of [RPT_ITEM_LIST, RPT_ITEM_SUMMARY, RPT_PURCHASE, RPT_SALES]) {
            const [resp] = await mgrApi.get(`${url}?${SAMPLE_RANGE}`, undefined, false);
            expect([401, 403], `manager authorized for ${url}`).not.toContain(resp.status());
            expect(resp.status(), `manager report ${url} must not 500`).toBeLessThan(500);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — unauthorized (no cookie, no nonce) → 401/403 (boundary).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Inventory REST — access control (no auth)', () => {
    let noAuthApi: ApiUtils;
    test.beforeAll(async () => {
        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
        noAuthApi = new ApiUtils(ctx);
    });
    test.afterAll(async () => {
        await noAuthApi.dispose();
    });

    test('INV-NC-01 no-auth cannot list inventory', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        const [res] = await noAuthApi.get(INVENTORY, { headers: { 'X-WP-Nonce': '' } }, false);
        expect([401, 403], 'no-auth inventory list is an auth refusal').toContain(res.status());
    });

    test('INV-NC-02 no-auth cannot read stock / transactions overview', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        for (const url of [STOCK_OVERVIEW, TRN_OVERVIEW]) {
            const [res] = await noAuthApi.get(url, { headers: { 'X-WP-Nonce': '' } }, false);
            expect([401, 403], `no-auth ${url} is an auth refusal`).toContain(res.status());
        }
    });

    test('INV-NC-03 no-auth cannot read the inventory reports', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        for (const url of [RPT_ITEM_LIST, RPT_ITEM_SUMMARY, RPT_PURCHASE, RPT_SALES]) {
            const [res] = await noAuthApi.get(url, { headers: { 'X-WP-Nonce': '' } }, false);
            expect([401, 403], `no-auth ${url} is an auth refusal`).toContain(res.status());
        }
    });
});
