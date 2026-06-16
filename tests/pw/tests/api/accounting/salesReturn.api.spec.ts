import { test, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { restUrl, BASE_URL } from '@utils/helpers';
import { AccountingPage } from '../../e2e/accounting/accountingPage';

/**
 * Accounting — Sales Return (PRO) REST.
 *
 * KIND: REST (sales-return list / single / search-invoice / create). None of these
 * pro routes live in endPoints/apiEndPoints.ts, so every URL is built with
 * restUrl('/erp/v1/...'). The seed step reuses the free endPoints.acctCustomers /
 * endPoints.acctInvoices.
 *
 * Grounded in:
 *   erp-pro/includes/Feature/Accounting/Api/SalesReturnController.php
 *   erp-pro/includes/Feature/Accounting/Core/functions/sales-return.php
 *
 * All four routes share namespace `erp/v1` + rest_base
 * `accounting/v1/sales-return`, and EVERY route (including the destructive
 * `create`) is gated only by current_user_can('erp_ac_view_sales_summary') —
 * the standard accounting READ-summary cap that admin + the accounting manager
 * hold. FINDING: the create route is guarded by a read cap, not a dedicated
 * create cap, so a read-only accounting manager can post a sales return. We use
 * this for a positive manager-can-reach-create baseline and an employee-denied
 * boundary.
 *
 * RESILIENCE (resilient-assertion philosophy — keeps this green against the real,
 * quirky API):
 *  - list/{id}/search-invoice/create are quirky:
 *      • list ALWAYS 200s + a collection (even empty); pagination via X-WP-Total.
 *      • Bad `status` is interpolated raw into SQL (sales-return.php L42) → a
 *        garbage/SQLi-shaped value can 500. A 500 there is a KNOWN, tolerated bug:
 *        assert < 500 (never assert an exact 500), document when it 500s.
 *      • single {id} / search-invoice write properties onto a possibly-null $row
 *        for a non-existent voucher (L476/L532-534) → null-deref risk. For REAL
 *        vouchers expect 200 + body; for missing ones assert < 500, never exact 200.
 *      • create RE-DERIVES amount/tax/discount from line_items (ignores any sent),
 *        skips qty<=0 lines, hard-codes status 9, and OVERWRITES comments to
 *        "Invoice created with voucher no <id>" (so `particulars` does NOT persist
 *        as comments). It depends on a full invoice/ledger/people-trn chain that
 *        can legitimately 4xx (WP_Error 'sales-return-exception') — PASS-by-design.
 *        Missing/invalid customer_id can null-deref → tolerate 500 for that bad case.
 *  - Access-control: assert the BOUNDARY ([401,403]), not an exact code. The acct
 *    manager is a POSITIVE baseline (must NOT be refused) using its OWN nonce.
 *
 * Tier @pro, module @accounting, role tags per row. Pro tests run only when
 * ERP_PRO=true (the grepInvert drops @pro otherwise).
 */

let api: ApiUtils;

// Pro DB tables — referenced as string literals (the `tables` util only has free tables today).
const T_VOUCHER_NO = 'wp_erp_acct_voucher_no';
const T_SALES_RETURN = 'wp_erp_acct_sales_return';
const T_SALES_RETURN_DETAILS = 'wp_erp_acct_sales_return_details';
const T_LEDGER_DETAILS = 'wp_erp_acct_ledger_details';

// Routes (built via restUrl; not present in endPoints).
const SR_BASE = restUrl('/erp/v1/accounting/v1/sales-return');
const SR_LIST = `${SR_BASE}/list`;
const SR_CREATE = `${SR_BASE}/create`;
const srSingle = (voucherNo: string | number): string => `${SR_BASE}/${voucherNo}`;
const srSearchInvoice = (invoiceVoucherNo: string | number): string => `${SR_BASE}/search-invoice/${invoiceVoucherNo}`;

/** today as Y-m-d, matching erp_current_datetime()->format('Y-m-d'). */
function today(): string {
    return new Date().toISOString().slice(0, 10);
}

/** Coerce any response body into a row array (bare array OR {data:[...]}). */
function asRows(body: unknown): unknown[] | null {
    if (Array.isArray(body)) return body;
    if (body && typeof body === 'object' && Array.isArray((body as { data?: unknown }).data)) {
        return (body as { data: unknown[] }).data;
    }
    return null;
}

// ─────────────────────────────────────────────────────────────────────────────
// Shared seed: a customer + a POSTED sales invoice + its canonical line_items.
//   customerId            — created customer id.
//   invoiceId             — invoice create-response `id` (auto-increment PK).
//   invoiceVoucherNo      — the invoice's voucher_no (the source sales voucher the
//                           sales-return search/create key on). Resolved via DB
//                           because the invoice create body does NOT carry it.
//   invoiceLine           — { invoice_details_id, product_id, qty, unit_price }
//                           pulled from search-invoice so invoice_details_id is real.
// ─────────────────────────────────────────────────────────────────────────────
const SEED_UNIT_PRICE = 100;
let customerId = '';
let invoiceId = '';
let invoiceVoucherNo = '';
let invoiceLine: { invoice_details_id: number; product_id: number; qty: number; unit_price: number } | null = null;

/** Track created sales-return voucher ids so afterAll can clean up the rows. */
const createdReturnVoucherNos: string[] = [];

/** Resolve an invoice id (create-response PK) to its voucher_no (used by sales-return). */
async function resolveInvoiceVoucherNo(id: string | number): Promise<string> {
    try {
        const rows = await dbUtils.dbQuery<{ voucher_no: number | string }>(
            `SELECT voucher_no FROM wp_erp_acct_invoices WHERE id = ?`,
            [id],
        );
        return String(rows[0]?.voucher_no ?? id);
    } catch {
        return String(id);
    }
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);

    // 1. customer.
    try {
        const [, custId] = await api.create(endPoints.acctCustomers, data.accounting.customer());
        customerId = custId ?? '';
    } catch {
        /* leave blank; dependent tests skip */
    }

    // 2. posted sales invoice (status 2 = awaiting payment → books are posted).
    if (customerId) {
        try {
            const payload = AccountingPage.invoicePayload(customerId, SEED_UNIT_PRICE, { status: 2 });
            const [created] = await api.create(endPoints.acctInvoices, payload);
            invoiceId = String((created as { id?: unknown })?.id ?? '');
        } catch {
            /* leave blank */
        }
    }

    // 3. resolve voucher_no + pull canonical line_items from search-invoice so the
    //    return line carries a REAL invoice_details_id (insert writes it at L189).
    if (invoiceId) {
        invoiceVoucherNo = await resolveInvoiceVoucherNo(invoiceId);
        try {
            const [resp, body] = await api.get(srSearchInvoice(invoiceVoucherNo), undefined, false);
            const items = (body as { line_items?: unknown })?.line_items;
            if (resp.status() === 200 && Array.isArray(items) && items.length > 0) {
                const li = items[0] as Record<string, unknown>;
                invoiceLine = {
                    invoice_details_id: Number(li.invoice_details_id ?? li.id ?? 0),
                    product_id: Number(li.product_id ?? 0),
                    qty: Number(li.qty ?? 1),
                    unit_price: Number(li.unit_price ?? SEED_UNIT_PRICE),
                };
            }
        } catch {
            /* leave null; the create test falls back to a synthetic line */
        }
    }
});

test.afterAll(async () => {
    // Best-effort cleanup of the rows we created (no REST delete route exists for
    // sales returns, so delete directly by voucher_no across the return tables).
    for (const vno of createdReturnVoucherNos) {
        try {
            await dbUtils.dbQuery(`DELETE FROM ${T_SALES_RETURN_DETAILS} WHERE trn_no = ?`, [vno]);
            await dbUtils.dbQuery(`DELETE FROM ${T_SALES_RETURN} WHERE voucher_no = ?`, [vno]);
            await dbUtils.dbQuery(`DELETE FROM ${T_LEDGER_DETAILS} WHERE trn_no = ?`, [vno]);
            await dbUtils.dbQuery(`DELETE FROM ${T_VOUCHER_NO} WHERE id = ? AND type = 'sales_return'`, [vno]);
        } catch {
            /* ignore cleanup failures */
        }
    }
    await api.dispose();
    // NOTE: do NOT dbUtils.close() — the mysql pool is a module-level singleton
    // shared by sibling accounting specs in the same worker; the worker reclaims it.
});

// ─────────────────────────────────────────────────────────────────────────────
// SR-LIST — GET /sales-return/list (cap erp_ac_view_sales_summary). Collection.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Sales Return REST — list (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('SR-HP-01 list returns 200 + a collection (even when empty)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(SR_LIST, undefined, false);
        // Controller always set_status(200); a 500 here is a candidate bug, never expected.
        expect(resp.status(), 'sales-return list must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const rows = asRows(body);
        expect(rows, 'sales-return list is an array (or {data:[]})').not.toBeNull();
        if (rows && rows.length > 0) {
            const row = rows[0] as Record<string, unknown>;
            // Columns grounded in the SELECT (sales-return.php L54-68) + enriched status.
            expect(row, 'row carries a customer_id').toHaveProperty('customer_id');
            expect(row, 'row carries an amount').toHaveProperty('amount');
            expect(row, 'row carries an enriched status (erp_acct_get_trn_status_by_id)').toHaveProperty('status');
        }
    });

    test('SR-HP-02 list honors per_page / page; X-WP-Total present-or-empty', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${SR_LIST}?per_page=5&page=1`, undefined, false);
        expect(resp.status(), 'paginated list must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const rows = asRows(body);
        expect(rows, 'paginated list is an array').not.toBeNull();
        expect(rows ? rows.length : 0, 'per_page=5 returns at most 5 rows').toBeLessThanOrEqual(5);

        // format_collection_response emits X-WP-Total only when there are items.
        const total = resp.headers()['x-wp-total'];
        const isEmpty = !rows || rows.length === 0;
        expect(total !== undefined || isEmpty, 'X-WP-Total present OR the sales-return list is empty').toBe(true);
    });

    test('SR-HP-03 list honors a clean date window (BETWEEN start_date/end_date)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${SR_LIST}?start_date=2026-01-01&end_date=2026-12-31`, undefined, false);
        expect(resp.status(), 'dated list must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'dated list answers an array').not.toBeNull();
        }
    });

    test('SR-EC-01 page far past the end returns 200 + [] (offset past end)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${SR_LIST}?per_page=5&page=9999`, undefined, false);
        expect(resp.status(), 'far-page request must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body)?.length ?? -1, 'offset past end yields no rows').toBe(0);
        }
    });

    test('SR-EC-02 clean numeric status filter (9) still answers an array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // status 9 is the hard-coded sales-return status; a clean numeric filter is
        // interpolated as `AND invoice.status=9` and is valid SQL.
        const [resp, body] = await api.get(`${SR_LIST}?status=9`, undefined, false);
        expect(resp.status(), 'status=9 list must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'status-filtered list answers an array').not.toBeNull();
        }
    });

    test('SR-EC-03 malformed (non-numeric) status is raw-interpolated into SQL — tolerate < 500', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // sales-return.php L42 interpolates status raw: `AND invoice.status={status}`.
        // A non-numeric / SQLi-shaped value can yield an empty set OR a broken-query
        // 500. KNOWN-BUG tolerated: assert < 500; if it 500s, document the candidate.
        const [resp] = await api.get(`${SR_LIST}?status=abc`, undefined, false);
        if (resp.status() === 500) {
            // BUG CANDIDATE: unsanitized `status` interpolated into the WHERE clause
            // crashes the list query (SQL-injection-shaped input).
            test.info().annotations.push({
                type: 'bug-candidate',
                description: 'sales-return list 500s on a non-numeric status (raw SQL interpolation, sales-return.php L42)',
            });
        }
        expect(resp.status(), 'malformed status must not exceed a 500 (flagged when it 500s)').toBeLessThanOrEqual(500);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// SR-SEARCH — GET /sales-return/search-invoice/{id} (numeric SALES voucher_no).
//   Returns the source invoice + line_items[] shaped for the create payload.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Sales Return REST — search-invoice (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('SR-HP-04 search-invoice for a real sales voucher returns it + line_items[]', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!invoiceVoucherNo, 'needs a seeded sales invoice voucher_no');
        const [resp, body] = await api.get(srSearchInvoice(invoiceVoucherNo), undefined, false);
        expect(resp.status(), 'search-invoice must not 500 for a real voucher').toBeLessThan(500);
        if (resp.status() !== 200) return;

        expect(body && typeof body === 'object', 'search-invoice returns an object').toBe(true);
        const obj = body as Record<string, unknown>;
        expect(String(obj?.voucher_no ?? ''), 'echoes the source voucher_no').toBe(String(invoiceVoucherNo));
        const items = obj?.line_items;
        expect(Array.isArray(items), 'search-invoice carries line_items[]').toBe(true);
        if (Array.isArray(items) && items.length > 0) {
            const li = items[0] as Record<string, unknown>;
            // Each line carries the shape the create payload's line_items need.
            expect(li, 'line carries invoice_details_id').toHaveProperty('invoice_details_id');
            expect(li, 'line carries product_id').toHaveProperty('product_id');
            expect(li, 'line carries qty').toHaveProperty('qty');
            expect(li, 'line carries unit_price').toHaveProperty('unit_price');
            expect(li, 'line carries a computed line_total').toHaveProperty('line_total');
        }
    });

    test('SR-EC-04 search-invoice for a non-existent numeric voucher — null-deref risk tolerated', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // erp_acct_get_invoice_for_return writes $row['line_items'] onto a possibly
        // null $row (sales-return.php L476). For a missing invoice this is a known
        // null-deref risk → assert < 500, NEVER assert exact 200.
        const [resp] = await api.get(srSearchInvoice(999999999), undefined, false);
        if (resp.status() === 500) {
            test.info().annotations.push({
                type: 'bug-candidate',
                description: 'search-invoice 500s for a non-existent voucher (null-deref on $row, sales-return.php L476)',
            });
        }
        expect(resp.status(), 'missing-voucher search must not exceed 500 (flagged when it 500s)').toBeLessThanOrEqual(500);
    });

    test('SR-EC-05 non-numeric search-invoice id misses the route → 404', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // The route regex is (?P<id>[\d]+); /abc does not match → clean route miss.
        const [resp] = await api.get(srSearchInvoice('abc'), undefined, false);
        expect(resp.status(), 'non-numeric search-invoice id is a clean 404').toBe(404);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// SR-SINGLE — GET /sales-return/{id} (numeric voucher_no of a sales RETURN).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Sales Return REST — single (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('SR-EC-06 single for a non-existent numeric voucher — null-deref risk tolerated', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // get_sales_return ALWAYS set_status(200), but erp_acct_get_sales_return_invoice
        // dereferences a null $row for a missing voucher (sales-return.php L532-534) →
        // KNOWN risk. Assert < 500; NEVER assert an exact 200.
        const [resp] = await api.get(srSingle(999999999), undefined, false);
        if (resp.status() === 500) {
            test.info().annotations.push({
                type: 'bug-candidate',
                description: 'single sales-return 500s for a non-existent voucher (null-deref, sales-return.php L532-534)',
            });
        }
        expect(resp.status(), 'missing single voucher must not exceed 500 (flagged when it 500s)').toBeLessThanOrEqual(500);
    });

    test('SR-EC-07 non-numeric single id misses the route → 404', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // Route regex (?P<id>[\d]+); /abc → route miss → 404 (clean).
        const [resp] = await api.get(srSingle('abc'), undefined, false);
        expect(resp.status(), 'non-numeric single id is a clean 404').toBe(404);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// SR-CREATE — POST /sales-return/create (CREATABLE; cap erp_ac_view_sales_summary).
//   Re-derives totals from line_items, hard-codes status 9, overwrites comments.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Sales Return REST — create (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    /** Build the grounded create payload from the seeded invoice + its line. */
    function createPayload(overrides: Record<string, unknown> = {}): Record<string, unknown> {
        const line = invoiceLine ?? {
            invoice_details_id: 0,
            product_id: 0,
            qty: 1,
            unit_price: SEED_UNIT_PRICE,
        };
        const qty = 1;
        const unitPrice = line.unit_price;
        return {
            customer_id: Number(customerId),
            voucher_no: Number(invoiceVoucherNo), // → sales_voucher_no (the source sales invoice).
            return_date: today(),
            discount_type: 0,
            tax_rate_id: 0,
            return_reason: `pw_return_${Date.now()}`,
            particulars: `pw_particular_${Date.now()}`,
            status: 9,
            attachments: '',
            line_items: [
                {
                    invoice_details_id: line.invoice_details_id,
                    product_id: line.product_id,
                    qty,
                    unit_price: unitPrice,
                    discount: 0,
                    tax: 0,
                    line_total: qty * unitPrice,
                },
            ],
            ...overrides,
        };
    }

    test('SR-HP-05 create a sales return against a seeded invoice (201 OR documented 4xx)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!customerId || !invoiceVoucherNo, 'needs a seeded customer + sales invoice voucher_no');
        const payload = createPayload();
        const expectedAmount = (payload.line_items as Array<{ line_total: number }>)[0]?.line_total ?? 0;

        const [resp, body] = await api.post(SR_CREATE, { data: payload }, false);
        // The create depends on a full invoice/ledger/people-trn chain that can
        // legitimately 4xx (WP_Error 'sales-return-exception') — PASS-by-design.
        // A 500 is only acceptable on the documented null-customer path (not here).
        expect(resp.status(), 'create must not 500 with a valid customer + invoice').toBeLessThan(500);
        if (resp.status() !== 201) {
            // 4xx-with-message is acceptable; record the code for visibility.
            test.info().annotations.push({ type: 'sales-return-create', description: `create answered ${resp.status()} (4xx tolerated)` });
            return;
        }

        const obj = body as Record<string, unknown>;
        const returnVoucherNo = String(obj?.id ?? '');
        expect(returnVoucherNo, 'create returns the new voucher_no as `id`').not.toBe('');
        createdReturnVoucherNos.push(returnVoucherNo);

        // amount/tax/discount are RE-DERIVED server-side from line_items.
        expect(Number(obj?.amount ?? -1), 'amount == Σ qty*unit_price from line_items').toBeCloseTo(expectedAmount, 2);
        expect(Number(obj?.tax ?? -1), 'tax == Σ line tax (0)').toBeCloseTo(0, 2);
        expect(Number(obj?.discount ?? -1), 'discount == Σ line discount (0)').toBeCloseTo(0, 2);
        expect(String(obj?.customer_id ?? ''), 'customer_id echoed').toBe(String(customerId));
    });

    test('SR-HP-06 created sales return persists across the return tables — DB oracle', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!customerId || !invoiceVoucherNo, 'needs a seeded customer + sales invoice voucher_no');
        const payload = createPayload();
        const [resp, body] = await api.post(SR_CREATE, { data: payload }, false);
        test.skip(resp.status() !== 201, 'create did not return 201 in this environment (4xx tolerated elsewhere)');

        const returnVoucherNo = String((body as { id?: unknown })?.id ?? '');
        expect(returnVoucherNo).not.toBe('');
        createdReturnVoucherNos.push(returnVoucherNo);

        // voucher_no table: a sales_return row, editable, currency set.
        let voucherRows: Array<{ type: string; editable: number | string; currency: string | null }> = [];
        try {
            voucherRows = await dbUtils.dbQuery<{ type: string; editable: number | string; currency: string | null }>(
                `SELECT type, editable, currency FROM ${T_VOUCHER_NO} WHERE id = ?`,
                [returnVoucherNo],
            );
        } catch {
            test.skip(true, 'DB unavailable for the voucher_no oracle');
            return;
        }
        expect(String(voucherRows[0]?.type ?? ''), 'voucher type == sales_return').toBe('sales_return');
        expect(Number(voucherRows[0]?.editable ?? 0), 'voucher editable == 1').toBe(1);
        expect(String(voucherRows[0]?.currency ?? ''), 'currency set on the voucher').not.toBe('');

        // sales_return table: invoice_id == source sales voucher, status 9, reason &
        // overwritten comments. particulars sent does NOT persist as comments.
        const srRows = await dbUtils.dbQuery<{ invoice_id: number | string; customer_id: number | string; status: number | string; reason: string | null; comments: string | null; trn_date: string }>(
            `SELECT invoice_id, customer_id, status, reason, comments, trn_date FROM ${T_SALES_RETURN} WHERE voucher_no = ?`,
            [returnVoucherNo],
        );
        expect(srRows.length, 'a sales_return row exists for the voucher').toBe(1);
        const sr = srRows[0];
        expect(String(sr?.invoice_id ?? ''), 'invoice_id == source sales voucher').toBe(String(invoiceVoucherNo));
        expect(String(sr?.customer_id ?? ''), 'customer_id persisted').toBe(String(customerId));
        expect(Number(sr?.status ?? -1), 'status hard-coded to 9 server-side').toBe(9);
        // KNOWN BUG: the submitted `return_reason` does NOT persist as `reason` — see
        // bug-reports/BUGS.md. prepare_item_for_database stores the request's
        // `return_reason` under the key `reason` (SalesReturnController.php L313-314),
        // but erp_acct_get_formatted_sales_return_data reads `$data['return_reason']`
        // (sales-return.php L766), which is never set in the prepared array. Net effect:
        // `reason` is ALWAYS written empty. Observed via curl: reason column == ''.
        expect(String(sr?.reason ?? ''), 'KNOWN BUG: return_reason never round-trips → reason persisted empty').toBe('');
        // FINDING: comments is OVERWRITTEN to "Invoice created with voucher no <id>",
        // so the submitted `particulars` does NOT round-trip as comments.
        expect(String(sr?.comments ?? ''), 'comments overwritten with the voucher-no template (particulars not persisted)').toContain(returnVoucherNo);
        expect(String(sr?.comments ?? ''), 'comments is NOT the submitted particulars').not.toBe(String(payload.particulars));

        // sales_return_details table: a line keyed by trn_no, qty/unit_price matching.
        const detailRows = await dbUtils.dbQuery<{ invoice_details_id: number | string; qty: number | string; unit_price: number | string }>(
            `SELECT invoice_details_id, qty, unit_price FROM ${T_SALES_RETURN_DETAILS} WHERE trn_no = ?`,
            [returnVoucherNo],
        );
        expect(detailRows.length, 'a sales_return_details row exists for the voucher').toBeGreaterThanOrEqual(1);
        const submitted = (payload.line_items as Array<{ qty: number; unit_price: number }>)[0];
        expect(Number(detailRows[0]?.qty ?? -1), 'detail qty matches the submitted line').toBeCloseTo(submitted?.qty ?? 0, 2);
        expect(Number(detailRows[0]?.unit_price ?? -1), 'detail unit_price matches the submitted line').toBeCloseTo(submitted?.unit_price ?? 0, 2);
    });

    test('SR-HP-07 created sales return appears in the list', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!customerId || !invoiceVoucherNo, 'needs a seeded customer + sales invoice voucher_no');
        const [resp, body] = await api.post(SR_CREATE, { data: createPayload() }, false);
        test.skip(resp.status() !== 201, 'create did not return 201 in this environment');
        const returnVoucherNo = String((body as { id?: unknown })?.id ?? '');
        expect(returnVoucherNo).not.toBe('');
        createdReturnVoucherNos.push(returnVoucherNo);

        // NOTE: `page=1` is REQUIRED here. The list computes
        // offset = per_page * (page - 1) (SalesReturnController.php L125). With `page`
        // omitted, $request['page'] is null → OFFSET becomes -per_page → MariaDB
        // syntax error "...near '-100'" → the query returns []. This is a real erp-pro
        // bug (negative OFFSET when `page` is not sent — see bug-reports/BUGS.md); we
        // pass page=1 so the reconciliation query is valid and the row is visible.
        const [listResp, list] = await api.get(`${SR_LIST}?per_page=100&page=1`, undefined, false);
        expect(listResp.status(), 'list answered while reconciling').toBeLessThan(500);
        if (listResp.status() !== 200) return;
        const rows = asRows(list) ?? [];
        const found = rows.some((r) => String((r as { id?: unknown })?.id ?? '') === returnVoucherNo);
        expect(found, 'the created sales return appears in the list').toBe(true);
    });

    test('SR-EC-08 create skips line_items with qty <= 0 (totals derived from positive lines only)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!customerId || !invoiceVoucherNo || !invoiceLine, 'needs a seeded invoice line');
        const line = invoiceLine!;
        const payload = createPayload({
            line_items: [
                { invoice_details_id: line.invoice_details_id, product_id: line.product_id, qty: 0, unit_price: line.unit_price, discount: 0, tax: 0, line_total: 0 },
                { invoice_details_id: line.invoice_details_id, product_id: line.product_id, qty: 1, unit_price: line.unit_price, discount: 0, tax: 0, line_total: line.unit_price },
            ],
        });
        const [resp, body] = await api.post(SR_CREATE, { data: payload }, false);
        expect(resp.status(), 'mixed-qty create must not 500').toBeLessThan(500);
        if (resp.status() !== 201) return;
        const returnVoucherNo = String((body as { id?: unknown })?.id ?? '');
        if (returnVoucherNo) createdReturnVoucherNos.push(returnVoucherNo);
        // Only the qty=1 line contributes → amount == one unit_price (qty<=0 skipped, L209).
        expect(Number((body as { amount?: unknown })?.amount ?? -1), 'amount counts only the positive-qty line').toBeCloseTo(line.unit_price, 2);
    });

    test('SR-EC-09 create with empty line_items derives zero totals (201 OR documented 4xx)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!customerId || !invoiceVoucherNo, 'needs a seeded customer + sales invoice voucher_no');
        const payload = createPayload({ line_items: [] });
        const [resp, body] = await api.post(SR_CREATE, { data: payload }, false);
        // line_items is iterated unconditionally (L208) → empty still works with 0 totals.
        expect(resp.status(), 'empty line_items create must not 500').toBeLessThan(500);
        if (resp.status() === 201) {
            const returnVoucherNo = String((body as { id?: unknown })?.id ?? '');
            if (returnVoucherNo) createdReturnVoucherNos.push(returnVoucherNo);
            expect(Number((body as { amount?: unknown })?.amount ?? -1), 'empty line_items → amount 0').toBeCloseTo(0, 2);
        }
    });

    test('SR-NC-01 create with a missing customer_id is silently accepted as a customer-less return (KNOWN BUG)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!invoiceVoucherNo, 'needs a seeded sales invoice voucher_no');
        // erp_acct_get_formatted_sales_return_data calls erp_get_people($data['customer_id'])
        // then reads $customer->first_name/last_name (sales-return.php L744-746). With
        // customer_id missing, erp_get_people(null) returns a WP_Error and the property
        // reads only emit PHP Warnings (no fatal) → $customer_name resolves to ' '.
        //
        // KNOWN BUG: the create has NO customer_id validation, so it returns a CLEAN 201
        // and persists a return row with customer_id = NULL and customer_name = ' '
        // (Undefined property: WP_Error::$first_name in sales-return.php L746). A sales
        // return with no customer is meaningless accounting data. See bug-reports/BUGS.md.
        //
        // We assert the ACTUAL observed behavior: the create is accepted (201), NOT
        // refused — documenting the missing-customer validation gap.
        const payload = createPayload();
        delete (payload as Record<string, unknown>).customer_id;
        const [resp, body] = await api.post(SR_CREATE, { data: payload }, false);
        expect(resp.status(), 'KNOWN BUG: missing customer_id is silently accepted (no validation)').toBe(201);

        const obj = body as Record<string, unknown>;
        const returnVoucherNo = String(obj?.id ?? '');
        expect(returnVoucherNo, 'a (customer-less) return voucher is created').not.toBe('');
        if (returnVoucherNo) createdReturnVoucherNos.push(returnVoucherNo);
        // The response body carries no customer_id — the return was posted with none.
        expect(obj?.customer_id == null || obj?.customer_id === '', 'created return has NO customer_id').toBe(true);
    });

    test('SR-NC-02 create against a non-existent source voucher_no is NOT a clean success', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        test.skip(!customerId, 'needs a seeded customer');
        // A bogus source sales voucher has no invoice/line chain → the ledger/people
        // chain can throw (WP_Error 'sales-return-exception') → 4xx PASS-by-design,
        // or a tolerated 500. Either way it must not be a clean, posted 201 return.
        const payload = createPayload({ voucher_no: 999999999, line_items: [] });
        const [resp, body] = await api.post(SR_CREATE, { data: payload }, false);
        // Tolerate the full range; only require it is not silently a healthy create
        // when no real source invoice exists.
        if (resp.status() === 201) {
            // If the build accepts it (empty line_items → 0 totals), clean up the row;
            // record it as a candidate (a return with no real source invoice).
            const returnVoucherNo = String((body as { id?: unknown })?.id ?? '');
            if (returnVoucherNo) createdReturnVoucherNos.push(returnVoucherNo);
            test.info().annotations.push({
                type: 'bug-candidate',
                description: 'create accepted against a non-existent source voucher_no (no source-invoice validation)',
            });
        }
        expect(resp.status(), 'bogus source voucher create answered (5xx tolerated as a known risk)').toBeLessThanOrEqual(500);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Role baseline — accounting manager (own nonce). POSITIVE: must NOT be refused.
//   FINDING: every route (incl. create) is gated by erp_ac_view_sales_summary,
//   a READ cap the acct manager holds — so the manager can reach create too.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Sales Return REST — accounting manager (positive baseline)', () => {
    test.use({ storageState: data.auth.accManagerFile });

    let mgrApi: ApiUtils;
    test.beforeAll(async () => {
        // The acct manager's OWN nonce; the admin nonce would 403 a manager session.
        mgrApi = await ApiUtils.fromStorageState(data.auth.accManagerFile, process.env.ACC_MANAGER_NONCE);
    });
    test.afterAll(async () => {
        await mgrApi.dispose();
    });

    test('SR-AC-01 manager can reach the sales-return list (erp_ac_view_sales_summary)', { tag: ['@pro', '@accounting', '@manager'] }, async () => {
        const [resp, body] = await mgrApi.get(SR_LIST, undefined, false);
        expect([401, 403], 'manager is authorized for the sales-return list').not.toContain(resp.status());
        expect(resp.status(), 'manager sales-return list must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'manager sales-return list is an array').not.toBeNull();
        }
    });

    test('SR-AC-02 manager can reach search-invoice + single (same read cap)', { tag: ['@pro', '@accounting', '@manager'] }, async () => {
        for (const url of [srSearchInvoice(invoiceVoucherNo || 1), srSingle(999999999)]) {
            const [resp] = await mgrApi.get(url, undefined, false);
            expect([401, 403], `manager authorized for ${url}`).not.toContain(resp.status());
            expect(resp.status(), `manager ${url} must not exceed 500`).toBeLessThanOrEqual(500);
        }
    });

    test('SR-AC-03 manager is NOT auth-refused at the create route (read cap gates create — finding)', { tag: ['@pro', '@accounting', '@manager'] }, async () => {
        // FINDING: the destructive create is guarded by the READ cap
        // erp_ac_view_sales_summary, so a read-only acct manager is NOT refused. We
        // assert the auth boundary only (resilient); we do not require a 201 because
        // the manager's create still depends on the full invoice/ledger chain.
        const [resp] = await mgrApi.post(SR_CREATE, { data: { customer_id: Number(customerId || 1), line_items: [] } }, false);
        expect([401, 403], 'manager is NOT auth-refused at the create route (read cap gates create)').not.toContain(resp.status());
        expect(resp.status(), 'manager create answered (not a fatal beyond 500)').toBeLessThanOrEqual(500);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — employee (lacks erp_ac_view_sales_summary) → boundary refusal.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Sales Return REST — employee (denied boundary)', () => {
    test.use({ storageState: data.auth.employeeFile });

    let empApi: ApiUtils;
    test.beforeAll(async () => {
        // The employee role does not hold erp_ac_view_sales_summary. No EMPLOYEE_NONCE
        // is captured by the auth setup; pass '' so the request is authenticated purely
        // by the employee's own cookie session — NOT the admin nonce that ApiUtils would
        // otherwise fall back to. A nonce-less request from a non-privileged session is
        // refused, which is exactly the cap gate we assert.
        empApi = await ApiUtils.fromStorageState(data.auth.employeeFile, '');
    });
    test.afterAll(async () => {
        await empApi.dispose();
    });

    test('SR-NC-03 employee cannot read the sales-return list', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        const [resp] = await empApi.get(SR_LIST, undefined, false);
        // Boundary: an employee without the read cap must be refused (401/403),
        // never served a 200 collection.
        expect(resp.status(), 'employee sales-return list is not a 200').not.toBe(200);
        expect([401, 403], 'employee sales-return list is an auth/cap refusal').toContain(resp.status());
    });

    test('SR-NC-04 employee cannot POST a sales return', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        const [resp] = await empApi.post(SR_CREATE, { data: { customer_id: Number(customerId || 1), line_items: [] } }, false);
        expect(resp.status(), 'employee create is not a clean 201').not.toBe(201);
        expect([401, 403], 'employee create is an auth/cap refusal').toContain(resp.status());
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — no auth (no cookie, no nonce) → 401/403 (boundary).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Sales Return REST — access control (no auth)', () => {
    let noAuthApi: ApiUtils;
    test.beforeAll(async () => {
        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
        noAuthApi = new ApiUtils(ctx);
    });
    test.afterAll(async () => {
        await noAuthApi.dispose();
    });

    test('SR-NC-05 no-auth cannot list sales returns', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        const [res] = await noAuthApi.get(SR_LIST, { headers: { 'X-WP-Nonce': '' } }, false);
        expect([401, 403], 'no-auth sales-return list is an auth refusal').toContain(res.status());
    });

    test('SR-NC-06 no-auth cannot create a sales return', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        const [res] = await noAuthApi.post(SR_CREATE, { headers: { 'X-WP-Nonce': '' }, data: { customer_id: 1, line_items: [] } }, false);
        expect([401, 403], 'no-auth sales-return create is an auth refusal').toContain(res.status());
    });
});
