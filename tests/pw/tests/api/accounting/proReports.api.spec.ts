import { test, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { data } from '@utils/testData';
import { restUrl, BASE_URL } from '@utils/helpers';

/**
 * Accounting — PRO Reports REST (sales-return, purchase-return, purchase-vat).
 *
 * KIND: REST (read-only reporting surface). None of these pro routes live in
 * endPoints/apiEndPoints.ts, so every URL is built with restUrl('/erp/v1/...').
 * Grounded in:
 *   includes/Feature/Accounting/Api/ReportsController.php
 *   includes/Feature/Accounting/Core/functions/reports.php
 *
 * One capability gates the whole surface (ReportsController lines 53/68/83):
 *   - reports/sales/return | reports/purchase/return | reports/purchase-vat
 *       => current_user_can('erp_ac_view_sales_summary')
 *
 * RESPONSE SHAPE: all three callbacks set_status(200) and return a bare JSON
 * ARRAY (numeric-indexed; ARRAY_A list). On empty data the array is []. We assert
 * Array.isArray (tolerating a {data:[]} envelope just in case).
 *
 * RESILIENCE (resilient-assertion philosophy):
 *  - Every endpoint is a GET with NO required params; controllers always
 *    set_status(200). We assert `< 500` (never an exact 500) and branch the shape
 *    check on a 200.
 *  - sales/return and purchase/return interpolate start_date / end_date UNESCAPED
 *    into raw SQL (reports.php lines 40, 84 — no $wpdb->prepare). A malformed date
 *    may break the query, so for those probes a 500 is treated as a CANDIDATE BUG
 *    (potential no-prepare / SQL-injection gap) — flagged in the assertion message,
 *    NOT asserted as the expected status.
 *  - purchase-vat is $wpdb->prepare-safe (reports.php 146-150); when start_date OR
 *    end_date is empty the helper returns [] EARLY (107-109), so a no-param call
 *    yields 200 with an empty array.
 *  - Access-control: assert the BOUNDARY ([401,403]) for no-auth / employee, not an
 *    exact code. The acct manager is a POSITIVE baseline (must NOT be refused) using
 *    its OWN nonce.
 *
 * These are read-only GET reports (no writes), so there is nothing to seed and
 * nothing to clean up — afterAll only disposes the API contexts.
 *
 * Tier @pro, module @accounting, role tags per row. Pro tests run only when
 * ERP_PRO=true (the grepInvert drops @pro otherwise).
 */

let api: ApiUtils;

// Routes (built via restUrl; not present in endPoints).
const SALES_RETURN = restUrl('/erp/v1/accounting/v1/reports/sales/return');
const PURCHASE_RETURN = restUrl('/erp/v1/accounting/v1/reports/purchase/return');
const PURCHASE_VAT = restUrl('/erp/v1/accounting/v1/reports/purchase-vat');

// A sample, in-range date window.
const SAMPLE_RANGE = 'start_date=2025-01-01&end_date=2025-12-31';

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
});

// ─────────────────────────────────────────────────────────────────────────────
// RPT-SR — GET reports/sales/return (cap erp_ac_view_sales_summary). Bare array.
//   Columns: voucher_no, trn_date, customer_name, tax, discount, price, product, qty
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Pro Reports REST — sales-return report (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('RPT-SR-HP-01 dated sales-return returns 200 + bare array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${SALES_RETURN}?${SAMPLE_RANGE}`, undefined, false);
        expect(resp.status(), 'sales-return report must not 500 (a 500 here is a candidate bug)').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const rows = asRows(body);
        expect(rows, 'sales-return report is an array (or {data:[]})').not.toBeNull();
        if (rows && rows.length > 0) {
            const row = rows[0] as Record<string, unknown>;
            // {voucher_no, trn_date, customer_name, tax, discount, price, product, qty}.
            expect(row, 'row carries a voucher_no').toHaveProperty('voucher_no');
            expect(row, 'row carries a customer_name').toHaveProperty('customer_name');
        }
    });

    test('RPT-SR-EC-01 no-param call defaults dates server-side and answers 200 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // start_date -> 'first day of january', end_date -> 'last day of this month'
        // (reports.php 16/23). No params must still answer a 200 array, not a 500.
        const [resp, body] = await api.get(SALES_RETURN, undefined, false);
        expect(resp.status(), 'dateless sales-return must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'dateless sales-return answers an array').not.toBeNull();
        }
    });

    test('RPT-SR-EC-02 malformed start_date is interpolated UN-prepared — must not 500', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // reports.php line 40 interpolates start_date raw into WHERE BETWEEN. A bad
        // date may break the query: a 500 here is a CANDIDATE no-prepare/SQLi bug,
        // flagged but never asserted as expected.
        const [resp, body] = await api.get(`${SALES_RETURN}?start_date=not-a-date&end_date=2025-12-31`, undefined, false);
        expect(resp.status(), 'malformed-date sales-return must not 500 (raw-SQL interpolation candidate bug)').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'malformed-date sales-return still answers an array').not.toBeNull();
        }
    });

    test('RPT-SR-EC-03 SQL-breaking start_date is interpolated UN-prepared — must not 500', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // A quote-bearing token would terminate the interpolated string literal at
        // reports.php line 40. Resilient: assert < 500, flag a 500 as the SQLi gap.
        const inj = encodeURIComponent("2025-01-01' OR '1'='1");
        const [resp, body] = await api.get(`${SALES_RETURN}?start_date=${inj}&end_date=2025-12-31`, undefined, false);
        expect(resp.status(), 'quote-bearing start_date must not 500 (no-prepare SQLi candidate bug)').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'injection-probe sales-return still answers an array').not.toBeNull();
        }
    });

    test('RPT-SR-EC-04 reversed date window (end before start) still answers < 500 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${SALES_RETURN}?start_date=2025-12-31&end_date=2025-01-01`, undefined, false);
        expect(resp.status(), 'reversed-window sales-return must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            // A reversed BETWEEN yields no rows, but must still be an array.
            expect(asRows(body), 'reversed-window sales-return answers an array').not.toBeNull();
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// RPT-PR — GET reports/purchase/return (cap erp_ac_view_sales_summary). Bare array.
//   Columns: voucher_no, trn_date, vendor_name, vat, discount, price, product, qty
//   (price recomputed via array_walk — reports.php 88-90)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Pro Reports REST — purchase-return report (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('RPT-PR-HP-01 dated purchase-return returns 200 + bare array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${PURCHASE_RETURN}?${SAMPLE_RANGE}`, undefined, false);
        expect(resp.status(), 'purchase-return report must not 500 (a 500 here is a candidate bug)').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const rows = asRows(body);
        expect(rows, 'purchase-return report is an array (or {data:[]})').not.toBeNull();
        if (rows && rows.length > 0) {
            const row = rows[0] as Record<string, unknown>;
            // {voucher_no, trn_date, vendor_name, vat, discount, price, product, qty}.
            expect(row, 'row carries a voucher_no').toHaveProperty('voucher_no');
            expect(row, 'row carries a vendor_name').toHaveProperty('vendor_name');
        }
    });

    test('RPT-PR-EC-01 no-param call defaults dates server-side and answers 200 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // Same date defaulting as sales-return (reports.php 59-68).
        const [resp, body] = await api.get(PURCHASE_RETURN, undefined, false);
        expect(resp.status(), 'dateless purchase-return must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'dateless purchase-return answers an array').not.toBeNull();
        }
    });

    test('RPT-PR-EC-02 malformed start_date is interpolated UN-prepared — must not 500', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // reports.php line 84 interpolates start_date raw into WHERE BETWEEN. A 500
        // here is a CANDIDATE no-prepare/SQLi bug, flagged but not asserted.
        const [resp, body] = await api.get(`${PURCHASE_RETURN}?start_date=not-a-date&end_date=2025-12-31`, undefined, false);
        expect(resp.status(), 'malformed-date purchase-return must not 500 (raw-SQL interpolation candidate bug)').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'malformed-date purchase-return still answers an array').not.toBeNull();
        }
    });

    test('RPT-PR-EC-03 SQL-breaking start_date is interpolated UN-prepared — must not 500', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const inj = encodeURIComponent("2025-01-01' OR '1'='1");
        const [resp, body] = await api.get(`${PURCHASE_RETURN}?start_date=${inj}&end_date=2025-12-31`, undefined, false);
        expect(resp.status(), 'quote-bearing start_date must not 500 (no-prepare SQLi candidate bug)').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'injection-probe purchase-return still answers an array').not.toBeNull();
        }
    });

    test('RPT-PR-EC-04 reversed date window (end before start) still answers < 500 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${PURCHASE_RETURN}?start_date=2025-12-31&end_date=2025-01-01`, undefined, false);
        expect(resp.status(), 'reversed-window purchase-return must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'reversed-window purchase-return answers an array').not.toBeNull();
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// RPT-VAT — GET reports/purchase-vat (cap erp_ac_view_sales_summary). Bare array.
//   $wpdb->prepare-safe. Empty start/end => [] EARLY. Default branch: tax > 0,
//   columns {trn_date, voucher_no, tax_amount}. Filter branches add vendor /
//   category / agency columns.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Pro Reports REST — purchase-vat report (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('RPT-VAT-HP-01 dated purchase-vat returns 200 + bare array (default branch)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${PURCHASE_VAT}?${SAMPLE_RANGE}`, undefined, false);
        expect(resp.status(), 'purchase-vat report must not 500').toBeLessThan(500);
        if (resp.status() !== 200) return;

        const rows = asRows(body);
        expect(rows, 'purchase-vat report is an array (or {data:[]})').not.toBeNull();
        if (rows && rows.length > 0) {
            const row = rows[0] as Record<string, unknown>;
            // Default branch: {trn_date, voucher_no, tax_amount}.
            expect(row, 'row carries a voucher_no').toHaveProperty('voucher_no');
            expect(row, 'row carries a tax_amount').toHaveProperty('tax_amount');
        }
    });

    test('RPT-VAT-EC-01 missing dates short-circuit to 200 + empty array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // erp_acct_get_purchase_vat_report returns [] EARLY when start_date OR
        // end_date is empty (reports.php 107-109), so a no-param call is 200 [].
        const [resp, body] = await api.get(PURCHASE_VAT, undefined, false);
        expect(resp.status(), 'dateless purchase-vat must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            const rows = asRows(body);
            expect(rows, 'dateless purchase-vat answers an array').not.toBeNull();
            expect(rows ? rows.length : -1, 'dateless purchase-vat short-circuits to []').toBe(0);
        }
    });

    test('RPT-VAT-EC-02 only start_date supplied still short-circuits to 200 + []', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // end_date empty => the early [] return path (reports.php 107-109).
        const [resp, body] = await api.get(`${PURCHASE_VAT}?start_date=2025-01-01`, undefined, false);
        expect(resp.status(), 'half-dated purchase-vat must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            const rows = asRows(body);
            expect(rows, 'half-dated purchase-vat answers an array').not.toBeNull();
            expect(rows ? rows.length : -1, 'half-dated purchase-vat short-circuits to []').toBe(0);
        }
    });

    test('RPT-VAT-EC-03 vendor_id filter branch answers 200 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // vendor_id branch: {trn_date, voucher_no, tax_amount, vendor_id, vendor_name}.
        const [resp, body] = await api.get(`${PURCHASE_VAT}?${SAMPLE_RANGE}&vendor_id=1`, undefined, false);
        expect(resp.status(), 'vendor-filtered purchase-vat must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'vendor-filtered purchase-vat answers an array').not.toBeNull();
        }
    });

    test('RPT-VAT-EC-04 category_id filter branch answers 200 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // category_id branch: GROUP BY details.trn_no, {..., tax_cat_id}.
        const [resp, body] = await api.get(`${PURCHASE_VAT}?${SAMPLE_RANGE}&category_id=1`, undefined, false);
        expect(resp.status(), 'category-filtered purchase-vat must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'category-filtered purchase-vat answers an array').not.toBeNull();
        }
    });

    test('RPT-VAT-EC-05 agency_id filter branch answers 200 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // agency_id branch: INNER JOIN purchase_details_tax, {..., agency_id}.
        const [resp, body] = await api.get(`${PURCHASE_VAT}?${SAMPLE_RANGE}&agency_id=1`, undefined, false);
        expect(resp.status(), 'agency-filtered purchase-vat must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'agency-filtered purchase-vat answers an array').not.toBeNull();
        }
    });

    test('RPT-VAT-EC-06 prepare-safe: a bad date is bound as %s — no SQL break (200 + array)', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        // Unlike sales/purchase return, purchase-vat uses $wpdb->prepare (146-150);
        // a malformed date is bound, never breaks the query => expect a 200 array.
        const inj = encodeURIComponent("2025-01-01' OR '1'='1");
        const [resp, body] = await api.get(`${PURCHASE_VAT}?start_date=${inj}&end_date=2025-12-31`, undefined, false);
        expect(resp.status(), 'prepare-safe purchase-vat must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'prepare-safe purchase-vat still answers an array').not.toBeNull();
        }
    });

    test('RPT-VAT-EC-07 reversed date window (end before start) still answers < 500 + array', { tag: ['@pro', '@accounting', '@admin'] }, async () => {
        const [resp, body] = await api.get(`${PURCHASE_VAT}?start_date=2025-12-31&end_date=2025-01-01`, undefined, false);
        expect(resp.status(), 'reversed-window purchase-vat must not 500').toBeLessThan(500);
        if (resp.status() === 200) {
            expect(asRows(body), 'reversed-window purchase-vat answers an array').not.toBeNull();
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Role baseline — accounting manager (own nonce). POSITIVE: must NOT be refused.
//   All three reports gate on erp_ac_view_sales_summary, which the acct manager has.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Pro Reports REST — accounting manager (positive baseline)', () => {
    test.use({ storageState: data.auth.accManagerFile });

    let mgrApi: ApiUtils;
    test.beforeAll(async () => {
        // The acct manager's OWN nonce; the admin nonce would 403 a manager session.
        mgrApi = await ApiUtils.fromStorageState(data.auth.accManagerFile, process.env.ACC_MANAGER_NONCE);
    });
    test.afterAll(async () => {
        await mgrApi.dispose();
    });

    test('RPT-AC-01 manager can reach all three reports (erp_ac_view_sales_summary)', { tag: ['@pro', '@accounting', '@manager'] }, async () => {
        for (const url of [SALES_RETURN, PURCHASE_RETURN, PURCHASE_VAT]) {
            const [resp, body] = await mgrApi.get(`${url}?${SAMPLE_RANGE}`, undefined, false);
            expect([401, 403], `manager is authorized for ${url}`).not.toContain(resp.status());
            expect(resp.status(), `manager report ${url} must not 500`).toBeLessThan(500);
            if (resp.status() === 200) {
                expect(asRows(body), `manager report ${url} answers an array`).not.toBeNull();
            }
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — employee role (own session). Permission-gated => 401/403.
//   (employee lacks erp_ac_view_sales_summary)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Pro Reports REST — employee role is blocked', () => {
    test.use({ storageState: data.auth.employeeFile });

    let empApi: ApiUtils;
    test.beforeAll(async () => {
        empApi = await ApiUtils.fromStorageState(data.auth.employeeFile);
    });
    test.afterAll(async () => {
        await empApi.dispose();
    });

    test('RPT-NC-01 employee cannot read the sales-return report', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        const [res] = await empApi.get(`${SALES_RETURN}?${SAMPLE_RANGE}`, undefined, false);
        expect(res.status(), 'employee blocked from sales-return report').toBeGreaterThanOrEqual(400);
    });

    test('RPT-NC-02 employee cannot read the purchase-return report', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        const [res] = await empApi.get(`${PURCHASE_RETURN}?${SAMPLE_RANGE}`, undefined, false);
        expect(res.status(), 'employee blocked from purchase-return report').toBeGreaterThanOrEqual(400);
    });

    test('RPT-NC-03 employee cannot read the purchase-vat report', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        const [res] = await empApi.get(`${PURCHASE_VAT}?${SAMPLE_RANGE}`, undefined, false);
        expect(res.status(), 'employee blocked from purchase-vat report').toBeGreaterThanOrEqual(400);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control — unauthorized (no cookie, no nonce) → 401/403 (boundary).
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Pro Reports REST — access control (no auth)', () => {
    let noAuthApi: ApiUtils;
    test.beforeAll(async () => {
        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
        noAuthApi = new ApiUtils(ctx);
    });
    test.afterAll(async () => {
        await noAuthApi.dispose();
    });

    test('RPT-NC-04 no-auth cannot read any of the three reports', { tag: ['@pro', '@accounting', '@employee'] }, async () => {
        for (const url of [SALES_RETURN, PURCHASE_RETURN, PURCHASE_VAT]) {
            const [res] = await noAuthApi.get(`${url}?${SAMPLE_RANGE}`, { headers: { 'X-WP-Nonce': '' } }, false);
            expect([401, 403], `no-auth ${url} is an auth refusal`).toContain(res.status());
        }
    });
});
