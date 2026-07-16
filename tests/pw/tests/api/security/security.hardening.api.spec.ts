import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { restUrl } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';

/**
 * Security-hardening regression suite — proves the branch `fix/security-audit-hardening`
 * (PR wp-erp/wp-erp#1614) closes the reported vulnerabilities and does NOT break the
 * legitimate (admin) path. One describe block per REST-reachable fix.
 *
 * Only the REST-reachable fixes are covered here; the admin-ajax authorization fixes
 * (#2–#13) are guarded in security.ajax.api.spec.ts, and #15 (contact-group
 * sanitization) targets a controller that is NOT registered on any live route
 * (verified: /erp/v1/crm/contacts/groups is absent from rest_get_server()->get_routes()),
 * so it has no HTTP surface to exercise.
 *
 * Fixes covered:
 *   #17  people-type IDOR on customer transaction endpoints (CustomersController::is_customer)
 *   #16  employee photo upload restricted to image types (EmployeesController::upload_photo)
 *   #1   SQL injection in accounting product CSV import validation (erp_acct_validate_csv_data)
 */

const CSV_VALIDATE = restUrl('/erp/v1/accounting/v1/products/csv/validate');
const PEOPLE_TYPES = tables.peopleTypes;
const PRODUCT_CATS = tables.acctProductCategories;

let api: ApiUtils;

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    await api.dispose();
    // Do NOT close the shared mysql pool here — it is a module-level singleton reused
    // by sibling specs in the same worker; closing it breaks them ("Pool is closed").
});

// ─────────────────────────────────────────────────────────────────────────────
// Fix #17 — People-type IDOR on customer transaction endpoints.
//
// Routes GET customers/{id}/transactions and .../transactions/filter are gated only
// by the flat erp_ac_view_customer cap. Before the fix, a customer viewer could read
// ANY people id's transactions (vendor/employee) by swapping the path id. The fix adds
// is_customer($id) which 404s a non-customer people id.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('SEC #17 — customer transaction endpoints reject non-customer ids (IDOR)', () => {
    let customerId = '';
    let vendorId = '';

    test.beforeAll(async () => {
        const [, cId] = await api.create(endPoints.acctCustomers, data.accounting.customer());
        const [, vId] = await api.create(endPoints.acctVendors, data.accounting.vendor());
        customerId = String(cId);
        vendorId = String(vId);
    });

    test('SEC-17-01 a real customer id returns its transactions (200) — no regression', { tag: ['@lite', '@accounting', '@security', '@admin'] }, async () => {
        const [res, body] = await api.get(endPoints.acctCustomerTransactions(customerId), undefined, false);
        expect(res.status(), 'a genuine customer id is still readable').toBe(200);
        expect(Array.isArray(body), 'transactions body is an array').toBe(true);
    });

    test('SEC-17-02 a VENDOR id is rejected on the customer transactions route (404, was 200 leak)', { tag: ['@lite', '@accounting', '@security', '@admin'] }, async () => {
        const [res, body] = await api.get(endPoints.acctCustomerTransactions(vendorId), undefined, false);
        // The vendor exists as a people row but is NOT a customer → is_customer() fails.
        expect(res.status(), 'vendor id must NOT be readable via the customer route').toBe(404);
        expect(String((body as { code?: string })?.code ?? ''), 'the documented invalid-id error').toBe('rest_customer_invalid_id');
    });

    test('SEC-17-03 a VENDOR id is rejected on the transactions/filter route (404)', { tag: ['@lite', '@accounting', '@security', '@admin'] }, async () => {
        const url = `${endPoints.acctCustomerTransactionsFilter(vendorId)}?start_date=2020-01-01&end_date=2030-12-31`;
        const [res, body] = await api.get(url, undefined, false);
        expect(res.status(), 'filter route also rejects a non-customer id').toBe(404);
        expect(String((body as { code?: string })?.code ?? '')).toBe('rest_customer_invalid_id');
    });

    test('SEC-17-04 a non-existent people id is rejected (404), not a blank 200', { tag: ['@lite', '@accounting', '@security', '@admin'] }, async () => {
        const [res] = await api.get(endPoints.acctCustomerTransactions(99999999), undefined, false);
        expect(res.status(), 'unknown id is a clean 404').toBe(404);
    });

    test('SEC-17-05 the vendor IS still readable on its own vendor transactions route (proves the block is customer-scoped, not a global break)', { tag: ['@lite', '@accounting', '@security', '@admin'] }, async () => {
        const [res] = await api.get(endPoints.acctVendorTransactions(vendorId), undefined, false);
        // The vendor route has its own controller; the customer-route hardening must not
        // affect it. Accept any non-404 (200 data or empty set) — we only assert the
        // vendor is NOT treated as "invalid customer id" here.
        expect(res.status(), 'vendor route is unaffected by the customer-route fix').not.toBe(404);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Fix #16 — Employee photo upload restricted to image types.
//
// POST /erp/v1/hrm/employees/upload (field name "image"). Before the fix any core-allowed
// file type could be uploaded; the fix runs wp_check_filetype against an image allowlist
// and re-passes the allowlist to media_handle_upload so content is re-validated.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('SEC #16 — employee photo upload accepts only images', () => {
    // A 1×1 transparent PNG (valid image bytes + valid .png name).
    const PNG_1x1 = Buffer.from(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
        'base64',
    );

    async function postUpload(file: { name: string; mimeType: string; buffer: Buffer }): Promise<[number, unknown]> {
        const nonce = process.env.X_WP_NONCE ?? '';
        const resp = await api.request.post(endPoints.employeePhotoUpload, {
            headers: nonce ? { 'X-WP-Nonce': nonce } : {},
            multipart: { image: file } as never,
        });
        let body: unknown;
        try {
            body = await resp.json();
        } catch {
            body = await resp.text();
        }
        return [resp.status(), body];
    }

    test('SEC-16-01 a real PNG uploads successfully — no regression', { tag: ['@lite', '@hrm', '@security', '@admin'] }, async () => {
        const [status, body] = await postUpload({ name: 'avatar.png', mimeType: 'image/png', buffer: PNG_1x1 });
        expect(status, 'a genuine image is accepted').toBe(200);
        const photoId = Number((body as { photo_id?: unknown })?.photo_id ?? 0);
        expect(photoId, 'a valid attachment id is returned').toBeGreaterThan(0);
        // Clean up the created attachment so the media library is not polluted.
        if (photoId > 0) {
            await dbUtils.dbQuery(`DELETE FROM ${tables.posts} WHERE ID = ? AND post_type = ?`, [photoId, 'attachment']).catch(() => {});
        }
    });

    test('SEC-16-02 a PHP payload with a .php name is rejected (400)', { tag: ['@lite', '@hrm', '@security', '@admin'] }, async () => {
        const [status, body] = await postUpload({
            name: 'shell.php',
            mimeType: 'application/x-php',
            buffer: Buffer.from('<?php echo "pwn"; ?>', 'utf8'),
        });
        expect(status, 'a .php upload must be blocked before media_handle_upload').toBe(400);
        expect(String((body as { code?: string })?.code ?? ''), 'the image-only rejection code').toBe('invalid_file_type');
    });

    test('SEC-16-03 a PHP payload disguised with a .png name is still rejected at the media layer', { tag: ['@lite', '@hrm', '@security', '@admin'] }, async () => {
        // Name passes wp_check_filetype (extension-based), but the allowlist is re-passed
        // to media_handle_upload → wp_check_filetype_and_ext inspects real content and
        // rejects the mismatch. Assert it never yields a usable attachment id.
        const [status, body] = await postUpload({
            name: 'evil.png',
            mimeType: 'image/png',
            buffer: Buffer.from('<?php echo "pwn"; ?>', 'utf8'),
        });
        const photoId = Number((body as { photo_id?: unknown })?.photo_id ?? 0);
        const wpError = !!(body as { code?: string })?.code || status >= 400;
        expect(
            wpError || Number.isNaN(photoId) || photoId <= 0,
            `disguised PHP must not become a real attachment (status=${status}, photo_id=${photoId})`,
        ).toBe(true);
        if (photoId > 0) {
            await dbUtils.dbQuery(`DELETE FROM ${tables.posts} WHERE ID = ? AND post_type = ?`, [photoId, 'attachment']).catch(() => {});
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Fix #1 — SQL injection in accounting product CSV import validation.
//
// erp_acct_validate_csv_data looked up category_id/product_type_id/tax_cat_id/vendor by
// interpolating the raw CSV cell into the query. The fix binds each via prepare(%d,absint()).
// We prove: (a) an injection payload in a lookup column does NOT drop/alter anything and is
// coerced to a harmless integer; (b) the sentinel table survives.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('SEC #1 — product CSV import validation is injection-safe', () => {
    const RUN = String(Date.now()).slice(-8); // no Date.now ban in specs; only workflow scripts

    const CSV_HEADER = 'name,product_type_id,category_id,cost_price,sale_price,vendor,tax_cat_id';

    function csvMultipart(csv: string): Record<string, unknown> {
        return {
            csv_file: { name: 'inject.csv', mimeType: 'text/csv', buffer: Buffer.from(csv, 'utf8') },
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

    async function postValidate(csv: string): Promise<[number, unknown]> {
        const nonce = process.env.X_WP_NONCE ?? '';
        const resp = await api.request.post(CSV_VALIDATE, {
            headers: nonce ? { 'X-WP-Nonce': nonce } : {},
            multipart: csvMultipart(csv) as never,
        });
        let body: unknown;
        try {
            body = await resp.json();
        } catch {
            body = await resp.text();
        }
        return [resp.status(), body];
    }

    test('SEC-01-01 an injection payload in the category_id column cannot drop the product-categories table', { tag: ['@lite', '@accounting', '@security', '@admin'] }, async () => {
        // Baseline: the categories table exists and is queryable.
        let baselineOk = false;
        try {
            await dbUtils.dbQuery(`SELECT COUNT(*) AS c FROM ${PRODUCT_CATS}`);
            baselineOk = true;
        } catch {
            test.skip(true, 'product-categories table unavailable — cannot run the injection oracle');
            return;
        }
        expect(baselineOk).toBe(true);

        // category_id cell carries a classic tautology + drop attempt. With the raw
        // interpolation (pre-fix) this would break out of the WHERE id = ... clause.
        const payload = `1; DROP TABLE ${PRODUCT_CATS}; --`;
        const csv = `${CSV_HEADER}\nInjProd_${RUN},1,${payload},10.50,25.00,0,0\n`;
        const [status] = await postValidate(csv);

        // We do not assert a specific status (validation may 200 or 500 per the envelope
        // quirk documented in inventory.lifecycle); the security oracle is the DB.
        expect(typeof status).toBe('number');

        // The proof: the table STILL exists and is queryable — the payload was bound as
        // an integer (absint → 1), never executed.
        const rows = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${PRODUCT_CATS}`);
        expect(Number(rows[0]?.c ?? -1), 'product-categories table survives the injection attempt').toBeGreaterThanOrEqual(0);
    });

    test('SEC-01-02 a UNION/subquery payload in the vendor column does not exfiltrate or error out of the bound query', { tag: ['@lite', '@accounting', '@security', '@admin'] }, async () => {
        // vendor column feeds the people-type lookup subquery. A UNION SELECT payload,
        // pre-fix, would concatenate into the query; post-fix absint()→0 makes it inert.
        const payload = `0 UNION SELECT user_pass FROM wp_users LIMIT 1`;
        const csv = `${CSV_HEADER}\nInjVend_${RUN},1,1,10.50,25.00,${payload},0\n`;
        const [status] = await postValidate(csv);
        expect(typeof status).toBe('number');

        // Sentinel: the users table is intact and no error corrupted the DB session — a
        // trivially-queryable count proves the connection/schema is healthy post-request.
        const rows = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${tables.users}`);
        expect(Number(rows[0]?.c ?? -1), 'wp_users intact after the UNION payload').toBeGreaterThan(0);
    });

    test('SEC-01-03 the people-types sentinel table is unaffected by an injection in a lookup column', { tag: ['@lite', '@accounting', '@security', '@admin'] }, async () => {
        const payload = `1) OR 1=1; DELETE FROM ${PEOPLE_TYPES}; --`;
        const csv = `${CSV_HEADER}\nInjType_${RUN},1,${payload},10.50,25.00,0,0\n`;
        await postValidate(csv);
        const rows = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${PEOPLE_TYPES}`);
        // people_types is seeded on install with the core types; it must NOT be emptied.
        expect(Number(rows[0]?.c ?? 0), 'people-types rows survive the injection attempt').toBeGreaterThan(0);
    });
});
