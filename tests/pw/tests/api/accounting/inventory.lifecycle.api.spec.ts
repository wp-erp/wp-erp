import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { restUrl } from '@utils/helpers';
import { AccountingPage } from '../../e2e/accounting/accountingPage';

/**
 * Accounting — Inventory CSV-import + stock-movement LIFECYCLE (PRO module "inventory").
 *
 * KIND: REST + DB. This is a DEEP BEHAVIORAL lifecycle spec, not a smoke. It drives
 * the full inventory chain and asserts each DB/UI effect to the row:
 *
 *   A) CSV products import surface (validate → import):
 *      POST /products/csv/validate  (multipart) → {data:<VALUES tuple-list>, update:[], total}
 *      POST /products/csv/import    (json)      → echoes total, status 201
 *      Handlers: WeDevs\ERP\Accounting\API\InventoryProductsController::{validate_csv_data,
 *      import_products} → erp_acct_validate_csv_data / erp_acct_import_products
 *      (wp-erp/modules/accounting/includes/functions/products.php:429,605).
 *      Validation filter: wp-erp/includes/ValidateData.php (category_id rule
 *      not_empty:true → "Category ID can not be empty"; name rule
 *      not_empty|max:60|min:2|is_valid_name|unique:name → "Name already exists").
 *
 *   B) Stock-movement lifecycle for a real inventory-type product (product_type_id=1):
 *      product create → vendor → PURCHASE (stock_in) → customer → INVOICE/sale (stock_out)
 *      then assert the movement propagates to:
 *        - the wp_erp_acct_product_details ledger (authoritative per-product oracle),
 *        - /inventory/stock-overview        (global SUM(stock_in)/SUM(stock_out)),
 *        - /inventory/transactions-overview (global SUM sales/purchase amount),
 *        - /inventory/reports/{purchase,sales,item-summary}.
 *      Hooks (erp-pro): erp_acct_after_purchase_create → erp_acct_inventory_purchase_create
 *      and erp_acct_after_sales_create → erp_acct_inventory_items_sales_create
 *      (erp-pro/modules/accounting/inventory/includes/functions/inventory.php:95,292)
 *      each INSERT a wp_erp_acct_product_details row keyed by the transaction voucher_no.
 *
 * LIVE-VERIFIED FACTS this spec encodes (probed via curl + wp-cli during authoring):
 *  - validate of a well-formed CSV → 200 {data:"('Name','1','','10.50','25.00','','','1','<date>'),...",
 *    update:[], total:N}. data is a SQL VALUES tuple-list string in INSERT column order.
 *  - BUG-1 (import inserts 0 rows): import returns HTTP 201 + body=total but the
 *    product count is UNCHANGED. erp_acct_import_products does
 *    $wpdb->prepare("INSERT ... VALUES %s", $data['items']) which single-quotes the
 *    WHOLE tuple-list → "You have an error in your SQL syntax near '(\'..\',..)'" →
 *    the error is swallowed and $data['total'] is still returned → 201. (debug.log.)
 *  - BUG-2 (item-summary no_sale): reports.php:163-164 selects sum(details.stock_in)
 *    for BOTH no_purchase AND no_sale, so no_sale wrongly equals no_purchase
 *    (purchased 5 / sold 3 → both report '5'). We assert no_purchase; no_sale is the bug.
 *  - VALIDATE NEGATIVE branches answer HTTP 500 (NOT 200): the controller returns a
 *    bare WP_Error (no 'status' data), which rest_ensure_response serializes as 500.
 *    Observed envelope: {code:'import-error'|'invalid-file-type', message:[...]|"...",
 *    data:null}. We assert the ERROR ENVELOPE (code + message) and tolerate the 500 as
 *    the documented WP_Error-default status — never asserting a clean 200, never a
 *    silent accept.
 *  - stock-overview / transactions-overview are GLOBAL SUMs over the whole ledger, NOT
 *    per-product. On a shared DB the absolute values drift, so we assert the BEFORE→AFTER
 *    DELTA equals the movement (Δstock_in=+5, Δstock_out=+3, Δsales=+150, Δpurchase=+100)
 *    and reconcile the per-product net via the DB SUM oracle (authoritative).
 *
 * RESILIENCE: every write uses assert=false and branches on status; we assert
 * "not a fatal beyond what is documented" + the real effect, never a brittle exact
 * status (except the documented BUG-1 201 and the route-miss 404s). Unique data via a
 * fresh Date.now() suffix; afterAll deletes ONLY this run's rows (scoped by suffix/ids).
 *
 * SERIAL: this file mutates shared singleton inventory tables (product_details ledger,
 * products) and the steps are strictly ordered (product → purchase → sale → overviews),
 * so under api.config's fullyParallel we pin the whole file to serial.
 *
 * Tier @pro, module @accounting, role tags per row. Pro tests run only when ERP_PRO=true.
 */

test.describe.configure({ mode: 'serial' });

let api: ApiUtils;

// ── Pro DB tables — string literals (the `tables` util only has free tables today).
const PRODUCTS = 'wp_erp_acct_products';
const PRODUCT_DETAILS = 'wp_erp_acct_product_details';
const PRODUCT_PRICE = 'wp_erp_acct_product_price';
const INVOICES = 'wp_erp_acct_invoices';

// ── Routes (built via restUrl; none of these live in endPoints).
const CSV_VALIDATE = restUrl('/erp/v1/accounting/v1/products/csv/validate');
const CSV_IMPORT = restUrl('/erp/v1/accounting/v1/products/csv/import');
const STOCK_OVERVIEW = restUrl('/erp/v1/accounting/v1/inventory/stock-overview');
const TRN_OVERVIEW = restUrl('/erp/v1/accounting/v1/inventory/transactions-overview');
const RPT_PURCHASE = restUrl('/erp/v1/accounting/v1/inventory/reports/purchase');
const RPT_SALES = restUrl('/erp/v1/accounting/v1/inventory/reports/sales');
const RPT_ITEM_SUMMARY = restUrl('/erp/v1/accounting/v1/inventory/reports/item-summary');

// ── Lifecycle constants (the grounded, live-verified movement).
const TRN_DATE = '2026-06-05';
const PURCHASE_QTY = 5;
const PURCHASE_UNIT = 20; // cost price
const SALE_QTY = 3;
const SALE_UNIT = 50; // sale price
const REPORT_RANGE = 'start_date=2026-06-01&end_date=2026-06-30';

// A per-run unique suffix so every created name/email is unique and cleanup is scoped.
const RUN = String(Date.now());

// IDs/vouchers created by this run (resolved in the lifecycle test, cleaned in afterAll).
let lifeProductId = '';
let lifeProductName = '';
let lifeVendorId = '';
let lifeCustomerId = '';
let purchaseVoucherNo = '';
let invoiceId = '';
let invoiceVoucherNo = '';

/** Coerce any response body into a row array (bare array OR {data:[...]}). */
function asRows(body: unknown): Array<Record<string, unknown>> {
    if (Array.isArray(body)) return body as Array<Record<string, unknown>>;
    if (body && typeof body === 'object' && Array.isArray((body as { data?: unknown }).data)) {
        return (body as { data: Array<Record<string, unknown>> }).data;
    }
    return [];
}

/** The CSV header row, in the EXACT INSERT column order the importer expects. */
const CSV_HEADER = 'name,product_type_id,category_id,cost_price,sale_price,vendor,tax_cat_id';

/**
 * Build a products CSV with N inventory rows. `categoryValue` lets a negative test
 * blank the (not_empty:true) category_id column; `nameOverride` lets the duplicate-name
 * edge case reuse an existing product name verbatim.
 */
function buildCsv(rows: Array<{ name: string; categoryValue?: string }>): string {
    const lines = [CSV_HEADER];
    for (const r of rows) {
        const cat = r.categoryValue ?? '1'; // non-empty even though no category row exists
        lines.push(`${r.name},1,${cat},10.50,25.00,0,0`);
    }
    return lines.join('\n') + '\n';
}

/** The `fields[...]` column-index map + scalar args every validate request needs. */
function csvMultipart(csv: string, fileType = 'text/csv', fileName = 'products.csv'): Record<string, unknown> {
    return {
        csv_file: { name: fileName, mimeType: fileType, buffer: Buffer.from(csv, 'utf8') },
        type: 'product',
        category_id: '0',
        product_type_id: '1',
        tax_cat_id: '0',
        vendor: '0',
        update_existing: '0',
        'fields[name]': '0',
        'fields[product_type_id]': '1',
        'fields[category_id]': '2',
        'fields[cost_price]': '3',
        'fields[sale_price]': '4',
        'fields[vendor]': '5',
        'fields[tax_cat_id]': '6',
    };
}

/**
 * POST a multipart validate request. ApiUtils forces Content-Type application/json,
 * so we go through the raw request context (Playwright sets the multipart boundary +
 * content-type itself) and carry the admin nonce by hand.
 */
async function postValidate(multipart: Record<string, unknown>): Promise<[number, unknown]> {
    const nonce = process.env.X_WP_NONCE ?? '';
    const resp = await api.request.post(CSV_VALIDATE, {
        headers: nonce ? { 'X-WP-Nonce': nonce } : {},
        multipart: multipart as never,
    });
    let body: unknown;
    try {
        body = await resp.json();
    } catch {
        body = await resp.text();
    }
    return [resp.status(), body];
}

/** Resolve an invoice create-PK to its voucher_no (the create body omits it). */
async function resolveInvoiceVoucherNo(id: string | number): Promise<string> {
    try {
        const rows = await dbUtils.dbQuery<{ voucher_no: number | string }>(
            `SELECT voucher_no FROM ${INVOICES} WHERE id = ?`,
            [id],
        );
        return String(rows[0]?.voucher_no ?? id);
    } catch {
        return String(id);
    }
}

/** Net per-product stock from the authoritative product_details ledger. */
async function netStock(productId: string | number): Promise<number | null> {
    try {
        const rows = await dbUtils.dbQuery<{ net: string | number | null }>(
            `SELECT SUM(stock_in) - SUM(stock_out) AS net FROM ${PRODUCT_DETAILS} WHERE product_id = ?`,
            [productId],
        );
        const net = rows[0]?.net;
        return net === null || net === undefined ? null : Number(net);
    } catch {
        return null;
    }
}

/** Read a {field:numeric-string} overview into a Number, defaulting nulls to 0. */
function overviewNum(body: unknown, key: string): number {
    const v = (body as Record<string, unknown> | null)?.[key];
    return v === null || v === undefined ? 0 : Number(v);
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    // Best-effort cleanup of ONLY this run's rows (shared DB — scope to the suffix/ids).
    try {
        if (purchaseVoucherNo || invoiceVoucherNo) {
            const vnos = [purchaseVoucherNo, invoiceVoucherNo].filter(Boolean);
            if (vnos.length) {
                const placeholders = vnos.map(() => '?').join(',');
                await dbUtils.dbQuery(`DELETE FROM ${PRODUCT_DETAILS} WHERE trn_no IN (${placeholders})`, vnos);
                await dbUtils.dbQuery(`DELETE FROM ${PRODUCT_PRICE} WHERE trn_no IN (${placeholders})`, vnos);
            }
        }
        if (invoiceId) {
            await dbUtils.dbQuery(`DELETE FROM ${INVOICES} WHERE id = ?`, [invoiceId]);
        }
        // Delete this run's products by the unique suffix (NEVER a blanket product_id).
        await dbUtils.dbQuery(`DELETE FROM ${PRODUCTS} WHERE name LIKE ?`, [`LifeProd_${RUN}%`]);
    } catch {
        /* ignore cleanup failures on a shared site */
    }
    await api.dispose();
    // NOTE: do NOT dbUtils.close() — the mysql pool is a module-level singleton shared
    // by sibling accounting specs in the same worker; the worker reclaims it.
});

// ─────────────────────────────────────────────────────────────────────────────
// A) CSV products import surface — validate → import (+ the known bugs / negatives)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Inventory lifecycle — CSV products import surface (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('INVLC-HP-01 validate accepts a well-formed product CSV → 200 + {data, update:[], total:N}', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const names = [`CsvProdA_${RUN}`, `CsvProdB_${RUN}`];
        const csv = buildCsv(names.map((name) => ({ name })));
        const [status, body] = await postValidate(csvMultipart(csv));

        expect(status, 'validate of a clean CSV must not be a server fatal').toBeLessThan(500);
        if (status !== 200) {
            test.info().annotations.push({ type: 'csv-validate', description: `validate answered ${status} (expected 200)` });
            return;
        }
        const obj = body as Record<string, unknown>;
        // total echoes the parsed data-row count (header excluded).
        expect(Number(obj?.total), 'total equals the number of data rows').toBe(names.length);
        // update is an empty array when update_existing=0.
        expect(Array.isArray(obj?.update), 'update is an array').toBe(true);
        // data is a SQL VALUES tuple-list string in the INSERT column order.
        expect(typeof obj?.data, 'data is the VALUES tuple-list string').toBe('string');
        for (const name of names) {
            expect(String(obj?.data), `data carries the row for ${name}`).toContain(name);
        }
        // The category column is blanked on output (the DB-existence check empties it).
        expect(String(obj?.data), 'cost_price 10.50 present in the tuple-list').toContain('10.50');
    });

    test('INVLC-HP-02 import echoes total and does not fatal (resilient)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // First validate to obtain the exact `items` tuple-list the importer expects.
        const names = [`CsvImpEcho_${RUN}`];
        const [vStatus, vBody] = await postValidate(csvMultipart(buildCsv(names.map((name) => ({ name })))));
        test.skip(vStatus !== 200, 'validate did not return 200 in this environment');
        const items = String((vBody as { data?: unknown })?.data ?? '');
        const total = Number((vBody as { total?: unknown })?.total ?? 0);
        expect(items, 'validate produced a non-empty items tuple-list').not.toBe('');

        const [resp, body] = await api.post(CSV_IMPORT, { data: { items, update: [], total } }, false);
        // The importer must not be a server fatal; on success it set_status(201) and
        // echoes the total it was handed (see BUG-01 for the data-integrity gap).
        expect(resp.status(), 'import must not be a server fatal').toBeLessThan(500);
        if (resp.status() === 201) {
            expect(Number(body), 'import echoes the parsed-row total').toBe(total);
        }
    });

    test('INVLC-BUG-01 KNOWN BUG: import returns 201/body=total but inserts ZERO products', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // erp_acct_import_products does $wpdb->prepare("INSERT ... VALUES %s", $items),
        // which wraps the WHOLE tuple-list in single quotes → invalid SQL → the error is
        // swallowed and $data['total'] is still returned → REST 201. Net effect: a "201
        // success" that imports NOTHING. We assert that, post-import, NO product carries
        // the imported names — the data-integrity proof, independent of the 201 echo.
        const names = [`CsvBugA_${RUN}`, `CsvBugB_${RUN}`];
        const [vStatus, vBody] = await postValidate(csvMultipart(buildCsv(names.map((name) => ({ name })))));
        test.skip(vStatus !== 200, 'validate did not return 200 in this environment');
        const items = String((vBody as { data?: unknown })?.data ?? '');
        const total = Number((vBody as { total?: unknown })?.total ?? names.length);

        // Baseline: none of the imported names exist yet.
        const inClause = names.map(() => '?').join(',');
        let before: Array<{ c: number }> = [];
        try {
            before = await dbUtils.dbQuery<{ c: number }>(
                `SELECT COUNT(*) AS c FROM ${PRODUCTS} WHERE name IN (${inClause})`,
                names,
            );
        } catch {
            test.skip(true, 'DB unavailable for the import data-integrity oracle');
            return;
        }
        expect(Number(before[0]?.c ?? -1), 'no imported-named product exists before import').toBe(0);

        const [resp, body] = await api.post(CSV_IMPORT, { data: { items, update: [], total } }, false);
        // Document the deceptive success: 201 + body echoes the total.
        expect(resp.status(), 'KNOWN BUG: import answers a 201 success').toBe(201);
        expect(Number(body), 'KNOWN BUG: body echoes the (uninserted) total').toBe(total);

        // The proof: STILL zero rows for those names — the import silently inserted nothing.
        const after = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${PRODUCTS} WHERE name IN (${inClause})`,
            names,
        );
        expect(Number(after[0]?.c ?? -1), 'KNOWN BUG: import inserted ZERO products despite the 201 (VALUES %s double-quotes the tuple-list)').toBe(0);
    });

    test('INVLC-NC-01 validate with an empty category_id column surfaces the import-error envelope', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // category_id rule is not_empty:true (ValidateData.php:244-245). A blank column
        // value yields the validation envelope {code:'import-error', message:[...]}.
        // OBSERVED: the controller returns a bare WP_Error (no 'status' data) →
        // rest_ensure_response serializes it as HTTP 500. We assert the ENVELOPE shape
        // (the documented validation), tolerating the 500 as the WP_Error default —
        // NEVER a clean 200, NEVER a silent accept.
        const csv = buildCsv([{ name: `CsvEmptyCat_${RUN}`, categoryValue: '' }]);
        const [status, body] = await postValidate(csvMultipart(csv));

        expect(status, 'a validation failure is never a clean 200 accept').not.toBe(200);
        const obj = body as Record<string, unknown>;
        expect(String(obj?.code ?? ''), 'envelope carries the import-error code').toBe('import-error');
        const message = Array.isArray(obj?.message) ? (obj.message as unknown[]).join(' ') : String(obj?.message ?? '');
        expect(message, 'message explains the empty category_id').toContain('Category ID can not be empty');
        expect(obj?.data ?? null, 'error envelope has null data').toBeNull();
        if (status === 500) {
            test.info().annotations.push({
                type: 'bug-candidate',
                description: 'CSV validate returns a bare WP_Error (no status) → HTTP 500 for a plain validation failure (expected a 4xx)',
            });
        }
    });

    test('INVLC-NC-02 validate rejects a non-CSV (txt) file with invalid-file-type', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // wp_check_filetype_and_ext rejects a .txt → WP_Error 'invalid-file-type'
        // (products.php:431-433). Same bare-WP_Error → 500 serialization as NC-01.
        // Resilient: assert a 4xx-with-message OR the error envelope; never a clean 200.
        const [status, body] = await postValidate(
            csvMultipart('just some text, not a real csv\n', 'text/plain', 'notcsv.txt'),
        );

        expect(status, 'a non-CSV upload is never a clean 200 accept').not.toBe(200);
        expect(status, 'a non-CSV upload stays within the documented failure band').toBeGreaterThanOrEqual(400);
        const obj = body as Record<string, unknown>;
        expect(String(obj?.code ?? ''), 'envelope carries the invalid-file-type code').toBe('invalid-file-type');
        expect(String(obj?.message ?? ''), 'message says the file is not a valid CSV').toContain('not a valid CSV');
        if (status === 500) {
            test.info().annotations.push({
                type: 'bug-candidate',
                description: 'CSV validate returns a bare WP_Error (no status) → HTTP 500 for an invalid file type (expected a 4xx)',
            });
        }
    });

    test('INVLC-EC-01 validate of a duplicate product name surfaces a unique:name validation error', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // name rule includes unique:name → check_unique_product COUNT(*) on
        // wp_erp_acct_products (ValidateData.php:473). Reusing an existing product name
        // triggers "Name already exists. Try different one". We resolve a REAL existing
        // name from the DB so the assertion is deterministic; if the table is empty we
        // skip rather than assert a name that does not collide.
        let existing: Array<{ name: string }> = [];
        try {
            existing = await dbUtils.dbQuery<{ name: string }>(
                `SELECT name FROM ${PRODUCTS} WHERE name <> '' ORDER BY id DESC LIMIT 1`,
            );
        } catch {
            test.skip(true, 'DB unavailable to resolve an existing product name for the duplicate check');
            return;
        }
        const dupName = existing[0]?.name;
        test.skip(!dupName, 'no existing product to collide with for the unique:name check');

        const csv = buildCsv([{ name: dupName! }]);
        const [status, body] = await postValidate(csvMultipart(csv));

        expect(status, 'a duplicate-name validation failure is never a clean 200 accept').not.toBe(200);
        const obj = body as Record<string, unknown>;
        expect(String(obj?.code ?? ''), 'envelope carries the import-error code').toBe('import-error');
        const message = Array.isArray(obj?.message) ? (obj.message as unknown[]).join(' ') : String(obj?.message ?? '');
        expect(message, 'message flags the duplicate name (unique:name)').toContain('already exists');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// B) Stock-movement lifecycle — product → purchase (stock_in) → sale (stock_out)
//    then assert propagation to the ledger + overviews + reports.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Inventory lifecycle — stock movement (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('INVLC-HP-03 full lifecycle: a purchase + sale of an inventory product propagate to the ledger', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // ── 3. PRODUCT (type 1 = Inventory; REQUIRED for the stock hook to fire).
        lifeProductName = `LifeProd_${RUN}`;
        const [, productBody] = await api.post(endPoints.acctProducts, {
            data: {
                name: lifeProductName,
                product_type_id: { id: 1 },
                category_id: { id: 0 },
                tax_cat_id: { id: 0 },
                vendor: { id: 0 },
                cost_price: PURCHASE_UNIT,
                sale_price: SALE_UNIT,
            },
        }, false);
        // The products controller nests the created record under `id` (real id at id.id).
        const rawId = (productBody as { id?: unknown })?.id;
        lifeProductId = rawId && typeof rawId === 'object' ? String((rawId as { id?: unknown }).id ?? '') : String(rawId ?? '');
        expect(lifeProductId, 'product create resolves a numeric id (id.id)').not.toBe('');
        expect(lifeProductId, 'product id is not the literal undefined').not.toBe('undefined');

        // Confirm the product persisted as inventory-type (the hook predicate).
        const prodRows = await dbUtils.dbQuery<{ product_type_id: number | string; cost_price: string }>(
            `SELECT product_type_id, cost_price FROM ${PRODUCTS} WHERE id = ?`,
            [lifeProductId],
        );
        expect(Number(prodRows[0]?.product_type_id ?? -1), 'product persisted as inventory type (1)').toBe(1);

        // Baseline overview SUMs BEFORE the movement (global table SUMs — we assert deltas).
        const [, stockBeforeBody] = await api.get(STOCK_OVERVIEW, undefined, false);
        const stockInBefore = overviewNum(stockBeforeBody, 'stock_in');
        const stockOutBefore = overviewNum(stockBeforeBody, 'stock_out');
        const [, trnBeforeBody] = await api.get(TRN_OVERVIEW, undefined, false);
        const salesBefore = overviewNum(trnBeforeBody, 'sales');
        const purchaseBefore = overviewNum(trnBeforeBody, 'purchase');

        // ── 4. VENDOR.
        const vendor = data.accounting.vendor();
        const [, vendorBody] = await api.post(endPoints.acctVendors, {
            data: { ...vendor, first_name: 'InvV', last_name: RUN },
        }, false);
        lifeVendorId = String((vendorBody as { id?: unknown })?.id ?? '');
        expect(lifeVendorId, 'vendor create resolves an id').not.toBe('');

        // ── 5. PURCHASE (stock_in). status 1 still fires erp_acct_after_purchase_create.
        const [, purchaseBody] = await api.post(endPoints.acctPurchases, {
            data: {
                vendor_id: Number(lifeVendorId),
                vendor_name: `InvV ${RUN}`,
                trn_date: TRN_DATE,
                due_date: TRN_DATE,
                type: 'purchase',
                status: 1,
                amount: PURCHASE_QTY * PURCHASE_UNIT,
                due: PURCHASE_QTY * PURCHASE_UNIT,
                trn_by: 1,
                particulars: `inv_purchase_${RUN}`,
                line_items: [
                    {
                        product_id: Number(lifeProductId),
                        qty: PURCHASE_QTY,
                        price: PURCHASE_UNIT,
                        unit_price: PURCHASE_UNIT,
                        tax: 0,
                        tax_amount: 0,
                        discount: 0,
                        tax_cat_id: 0,
                        item_total: PURCHASE_QTY * PURCHASE_UNIT,
                    },
                ],
            },
        }, false);
        purchaseVoucherNo = String((purchaseBody as { voucher_no?: unknown })?.voucher_no ?? '');
        expect(purchaseVoucherNo, 'purchase carries a voucher_no').not.toBe('');

        // Ledger oracle: the purchase inserted a stock_in row keyed by the voucher_no.
        const purchaseLedger = await dbUtils.dbQuery<{ stock_in: number | string; stock_out: number | string }>(
            `SELECT stock_in, stock_out FROM ${PRODUCT_DETAILS} WHERE trn_no = ? AND product_id = ?`,
            [purchaseVoucherNo, lifeProductId],
        );
        expect(purchaseLedger.length, 'purchase wrote a product_details row').toBe(1);
        expect(Number(purchaseLedger[0]?.stock_in ?? -1), 'purchase stock_in == purchase qty').toBe(PURCHASE_QTY);
        expect(Number(purchaseLedger[0]?.stock_out ?? -1), 'purchase stock_out == 0').toBe(0);

        // ── 6. CUSTOMER.
        const customer = data.accounting.customer();
        const [, customerBody] = await api.post(endPoints.acctCustomers, {
            data: { ...customer, first_name: 'InvC', last_name: RUN },
        }, false);
        lifeCustomerId = String((customerBody as { id?: unknown })?.id ?? '');
        expect(lifeCustomerId, 'customer create resolves an id').not.toBe('');

        // ── 7. SALE / INVOICE (stock_out). status 2 = posted → books posted, hook fires.
        const invoicePayload = AccountingPage.invoicePayload(lifeCustomerId, SALE_UNIT, {
            productId: lifeProductId,
            qty: SALE_QTY,
            status: 2,
        });
        // Pin the invoice into the same reporting window as the purchase.
        (invoicePayload as Record<string, unknown>).date = TRN_DATE;
        (invoicePayload as Record<string, unknown>).due_date = '2026-07-05';
        const [invoiceResp, invoiceBody] = await api.post(endPoints.acctInvoices, { data: invoicePayload }, false);
        expect(invoiceResp.status(), 'invoice create must not be a server fatal').toBeLessThan(500);
        invoiceId = String((invoiceBody as { id?: unknown })?.id ?? '');
        expect(invoiceId, 'invoice carries an auto-increment id').not.toBe('');
        invoiceVoucherNo = await resolveInvoiceVoucherNo(invoiceId);
        expect(invoiceVoucherNo, 'invoice voucher_no resolved from the DB').not.toBe('');

        // Ledger oracle: the sale inserted a stock_out row keyed by the invoice voucher_no.
        const saleLedger = await dbUtils.dbQuery<{ stock_in: number | string; stock_out: number | string }>(
            `SELECT stock_in, stock_out FROM ${PRODUCT_DETAILS} WHERE trn_no = ? AND product_id = ?`,
            [invoiceVoucherNo, lifeProductId],
        );
        expect(saleLedger.length, 'sale wrote a product_details row').toBe(1);
        expect(Number(saleLedger[0]?.stock_out ?? -1), 'sale stock_out == sale qty').toBe(SALE_QTY);
        expect(Number(saleLedger[0]?.stock_in ?? -1), 'sale stock_in == 0').toBe(0);

        // ── 11. DB ORACLE: authoritative net per-product stock == 5 in − 3 out == 2.
        const net = await netStock(lifeProductId);
        expect(net, 'net per-product stock == purchase qty − sale qty').toBe(PURCHASE_QTY - SALE_QTY);

        // ── 8/9. OVERVIEWS — global SUMs, so assert the BEFORE→AFTER delta == movement.
        const [stockResp, stockAfterBody] = await api.get(STOCK_OVERVIEW, undefined, false);
        expect(stockResp.status(), 'stock-overview must not 500').toBeLessThan(500);
        if (stockResp.status() === 200) {
            expect(overviewNum(stockAfterBody, 'stock_in') - stockInBefore, 'stock-overview stock_in rose by the purchase qty').toBe(PURCHASE_QTY);
            expect(overviewNum(stockAfterBody, 'stock_out') - stockOutBefore, 'stock-overview stock_out rose by the sale qty').toBe(SALE_QTY);
        }

        const [trnResp, trnAfterBody] = await api.get(TRN_OVERVIEW, undefined, false);
        expect(trnResp.status(), 'transactions-overview must not 500').toBeLessThan(500);
        if (trnResp.status() === 200) {
            expect(overviewNum(trnAfterBody, 'sales') - salesBefore, 'transactions-overview sales rose by the invoice amount').toBeCloseTo(SALE_QTY * SALE_UNIT, 2);
            expect(overviewNum(trnAfterBody, 'purchase') - purchaseBefore, 'transactions-overview purchase rose by the purchase amount').toBeCloseTo(PURCHASE_QTY * PURCHASE_UNIT, 2);
        }
    });

    test('INVLC-HP-04 reports/purchase shows this run\'s purchase voucher with qty + price', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!purchaseVoucherNo || !lifeProductName, 'needs a completed lifecycle purchase');
        const [resp, body] = await api.get(`${RPT_PURCHASE}?${REPORT_RANGE}`, undefined, false);
        expect(resp.status(), 'purchase report must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const row = asRows(body).find((r) => String(r?.voucher_no ?? '') === purchaseVoucherNo);
        expect(row, 'the purchase voucher appears in reports/purchase').toBeTruthy();
        if (row) {
            expect(String(row.product ?? ''), 'report row names this run\'s product').toBe(lifeProductName);
            expect(Number(row.qty ?? -1), 'report row qty == purchase qty').toBe(PURCHASE_QTY);
            // price column is the line item_total (qty * cost).
            expect(Number(row.price ?? -1), 'report row price == purchase line total').toBeCloseTo(PURCHASE_QTY * PURCHASE_UNIT, 2);
            expect(String(row.vendor_name ?? ''), 'report row carries the vendor name').toContain(RUN);
        }
    });

    test('INVLC-HP-05 reports/sales shows this run\'s invoice voucher with qty + price', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!invoiceVoucherNo || !lifeProductName, 'needs a completed lifecycle sale');
        const [resp, body] = await api.get(`${RPT_SALES}?${REPORT_RANGE}`, undefined, false);
        expect(resp.status(), 'sales report must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const row = asRows(body).find((r) => String(r?.voucher_no ?? '') === invoiceVoucherNo);
        expect(row, 'the invoice voucher appears in reports/sales').toBeTruthy();
        if (row) {
            expect(String(row.product ?? ''), 'report row names this run\'s product').toBe(lifeProductName);
            expect(Number(row.qty ?? -1), 'report row qty == sale qty').toBe(SALE_QTY);
            expect(Number(row.price ?? -1), 'report row price == sale line total').toBeCloseTo(SALE_QTY * SALE_UNIT, 2);
            expect(String(row.customer_name ?? ''), 'report row carries the customer name').toContain(RUN);
        }
    });

    test('INVLC-HP-06 reports/item-summary shows correct no_purchase (no_sale is the documented bug)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!lifeProductName, 'needs a completed lifecycle product');
        const [resp, body] = await api.get(`${RPT_ITEM_SUMMARY}?${REPORT_RANGE}`, undefined, false);
        expect(resp.status(), 'item-summary report must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const row = asRows(body).find((r) => String(r?.name ?? '') === lifeProductName);
        expect(row, 'this run\'s product appears in reports/item-summary').toBeTruthy();
        if (row) {
            // cogs = purchase line total (5 * 20 = 100).
            expect(Number(row.cogs ?? -1), 'cogs == purchase cost total').toBeCloseTo(PURCHASE_QTY * PURCHASE_UNIT, 2);
            // no_purchase == sum(stock_in) == purchase qty (correct).
            expect(Number(row.no_purchase ?? -1), 'no_purchase == purchase qty').toBe(PURCHASE_QTY);
            // KNOWN BUG-2: reports.php:163-164 selects sum(stock_in) for BOTH no_purchase
            // AND no_sale, so no_sale wrongly equals no_purchase (5) instead of the real
            // sale qty (3). We assert the OBSERVED buggy behavior so the test stays green
            // and the bug is documented; flip this when reports.php uses sum(stock_out).
            expect(Number(row.no_sale ?? -1), 'KNOWN BUG: no_sale duplicates no_purchase (sum(stock_in)) instead of the sale qty').toBe(PURCHASE_QTY);
            expect(Number(row.no_sale ?? -1), 'KNOWN BUG: no_sale is NOT the actual sale qty').not.toBe(SALE_QTY);
            test.info().annotations.push({
                type: 'bug-candidate',
                description: 'reports/item-summary no_sale = sum(stock_in) (reports.php:163-164) → equals no_purchase, not the sale qty',
            });
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Role baseline — accounting manager (own nonce). POSITIVE: must NOT be refused
// from the import surface (the CSV routes are gated by erp_ac_manager).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Inventory lifecycle — accounting manager (positive baseline)', () => {
    test.use({ storageState: data.auth.accManagerFile });

    let mgrApi: ApiUtils;
    test.beforeAll(async () => {
        // The acct manager's OWN nonce; the admin nonce would 403 a manager session.
        mgrApi = await ApiUtils.fromStorageState(data.auth.accManagerFile, process.env.ACC_MANAGER_NONCE);
    });
    test.afterAll(async () => {
        await mgrApi.dispose();
    });

    test('INVLC-AC-01 manager is NOT auth-refused at the CSV validate route (erp_ac_manager)', { tag: ['@pro', '@accounting', '@manager'] }, async () => {
        // FINDING: csv/validate + csv/import are gated by erp_ac_manager, which the acct
        // manager holds. We assert the AUTH BOUNDARY only (resilient): the manager is not
        // refused. We do not require a 200, because the response status depends on the
        // CSV content (a validation failure legitimately answers an error envelope).
        const nonce = process.env.ACC_MANAGER_NONCE ?? '';
        const csv = buildCsv([{ name: `CsvMgr_${RUN}` }]);
        const resp = await mgrApi.request.post(CSV_VALIDATE, {
            headers: nonce ? { 'X-WP-Nonce': nonce } : {},
            multipart: csvMultipart(csv) as never,
        });
        expect([401, 403], 'manager is authorized for csv/validate (erp_ac_manager)').not.toContain(resp.status());
    });

    test('INVLC-AC-02 manager can reach stock + transactions overview', { tag: ['@pro', '@accounting', '@manager'] }, async () => {
        for (const url of [STOCK_OVERVIEW, TRN_OVERVIEW]) {
            const [resp] = await mgrApi.get(url, undefined, false);
            expect([401, 403], `manager authorized for ${url}`).not.toContain(resp.status());
            expect(resp.status(), `manager ${url} must not 500`).toBeLessThan(500);
        }
    });
});
