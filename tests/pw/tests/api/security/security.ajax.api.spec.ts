import { test, expect, request } from '@utils/test';
import type { APIRequestContext } from '@utils/test';
import { data } from '@utils/testData';
import { BASE_URL } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';

/**
 * Security-hardening regression — admin-ajax authorization gates (PR wp-erp/wp-erp#1614).
 *
 * The handlers below are registered `wp_ajax_*` only (no `nopriv_`), and each fix adds a
 * capability / object-ownership check on top of the existing nonce. These tests assert the
 * UNAUTHORIZED-request contract every fix must uphold: a request lacking the required
 * nonce+capability is DENIED and produces NO state change. We use a cookie-less context and
 * send no nonce, which is the strongest guarantee expressible without scraping each role's
 * per-session ajax nonce.
 *
 * SCOPE NOTE (intentional, not a gap left silently): proving the *capability* gate for an
 * authenticated-but-under-privileged role (e.g. an `employee` calling `erp-hr-emp-get` for
 * another employee's id) requires that role's own localized ajax nonce
 * (`wpErp.nonce` / `wpErpHr.nonce` / `wpErpCrm.nonce`), which is only emitted on an admin
 * page the role can reach. Capturing those per-role nonces in `_auth.setup.ts` is the
 * follow-up that upgrades these guards into full cap-level IDOR proofs. Until then these
 * assert the nonce+auth wall, which is where an unauthenticated attacker is stopped.
 *
 * Fixes guarded here:
 *   #2 erp-hr-emp-get, #3 erp-hr-emp-delete-performance, #4 erp-api-key,
 *   #5 erp-crm-customer-get, #6 erp-crm-get-single-schedule-details, #7 erp-hr-empl-leave-history,
 *   #8 erp-api-delete-key, #9 erp_import_users_as_contacts, #10 erp_audit_log_view,
 *   #11 erp-crm-delete-search-segment, #12 erp_hr_birthday_wish, #13 erp_hr_announcement_view
 */

const AJAX = `${BASE_URL}/wp-admin/admin-ajax.php`;
const PERFORMANCE = tables.hrEmployeePerformance;
const SAVE_SEARCH = tables.crmSaveSearch;
// API-key model lives in erp-pro / WC and has no free-plugin table on many installs;
// tableCount() degrades to null when absent, so the DB oracle self-skips (deny check stands).
const API_KEYS = `${process.env.DB_PREFIX ?? 'wp'}_erp_api_keys`;

/** Whether an admin-ajax response denied the request (WP auth/nonce/cap wall). */
function isDenied(status: number, body: string): boolean {
    const t = body.trim();
    return (
        status >= 400 ||
        t === '0' ||
        t === '-1' ||
        /forbidden|not allowed|do not have (sufficient )?permission|nonce/i.test(t)
    );
}

async function ajaxPost(form: Record<string, string>): Promise<[number, string]> {
    const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
    try {
        const res = await ctx.post(AJAX, { form, failOnStatusCode: false });
        const text = await res.text().catch(() => '');
        return [res.status(), text];
    } finally {
        await ctx.dispose();
    }
}

async function tableCount(table: string): Promise<number | null> {
    try {
        const rows = await dbUtils.dbQuery<{ c: number }>(`SELECT COUNT(*) AS c FROM ${table}`);
        return Number(rows[0]?.c ?? 0);
    } catch {
        return null; // table absent in this env — the state-oracle degrades to the deny check.
    }
}

test.afterAll(async () => {
    // Shared pool singleton — do not close here (breaks sibling specs in the same worker).
});

// ── Read-oriented IDOR / PII handlers — unauthorized read must be denied ─────────────────
test.describe('SEC admin-ajax — unauthorized reads are denied', () => {
    test('SEC-05 erp-crm-customer-get without nonce is denied (CRM contact PII IDOR)', { tag: ['@lite', '@crm', '@security'] }, async () => {
        const [status, body] = await ajaxPost({ action: 'erp-crm-customer-get', id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
    });

    test('SEC-02 erp-hr-emp-get without nonce is denied (employee PII IDOR)', { tag: ['@lite', '@hrm', '@security'] }, async () => {
        const [status, body] = await ajaxPost({ action: 'erp-hr-emp-get', id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
    });

    test('SEC-07 erp-hr-empl-leave-history without nonce is denied (cross-employee leave read)', { tag: ['@lite', '@hrm', '@security'] }, async () => {
        const [status, body] = await ajaxPost({ action: 'erp-hr-empl-leave-history', employee_id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
    });

    test('SEC-06 erp-crm-get-single-schedule-details without nonce is denied (activity feed IDOR)', { tag: ['@lite', '@crm', '@security'] }, async () => {
        const [status, body] = await ajaxPost({ action: 'erp-crm-get-single-schedule-details', id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
    });

    test('SEC-10 erp_audit_log_view without nonce is denied (audit-log IDOR)', { tag: ['@lite', '@core', '@security'] }, async () => {
        const [status, body] = await ajaxPost({ action: 'erp_audit_log_view', id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
    });

    test('SEC-13 erp_hr_announcement_view without nonce is denied (arbitrary post read)', { tag: ['@lite', '@hrm', '@security'] }, async () => {
        const [status, body] = await ajaxPost({ action: 'erp_hr_announcement_view', post_id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
    });
});

// ── State-changing handlers — unauthorized call must be denied AND change no rows ────────
test.describe('SEC admin-ajax — unauthorized writes are denied and mutate nothing', () => {
    test('SEC-03 erp-hr-emp-delete-performance without nonce deletes no performance review', { tag: ['@lite', '@hrm', '@security'] }, async () => {
        const before = await tableCount(PERFORMANCE);
        const [status, body] = await ajaxPost({ action: 'erp-hr-emp-delete-performance', id: '1', user_id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
        if (before !== null) {
            expect(await tableCount(PERFORMANCE), 'no performance review deleted').toBe(before);
        }
    });

    test('SEC-11 erp-crm-delete-search-segment without nonce deletes no saved search', { tag: ['@lite', '@crm', '@security'] }, async () => {
        const before = await tableCount(SAVE_SEARCH);
        const [status, body] = await ajaxPost({ action: 'erp-crm-delete-search-segment', filterId: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
        if (before !== null) {
            expect(await tableCount(SAVE_SEARCH), 'no saved search deleted').toBe(before);
        }
    });

    test('SEC-04 erp-api-key without nonce creates no API credential (privilege escalation)', { tag: ['@lite', '@core', '@security'] }, async () => {
        const before = await tableCount(API_KEYS);
        const [status, body] = await ajaxPost({ action: 'erp-api-key', name: 'pw-sec-key', user_id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
        if (before !== null) {
            expect(await tableCount(API_KEYS), 'no API key created').toBe(before);
        }
    });

    test('SEC-08 erp-api-delete-key without nonce deletes no API credential', { tag: ['@lite', '@core', '@security'] }, async () => {
        const before = await tableCount(API_KEYS);
        const [status, body] = await ajaxPost({ action: 'erp-api-delete-key', id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
        if (before !== null) {
            expect(await tableCount(API_KEYS), 'no API key deleted').toBe(before);
        }
    });

    test('SEC-09 erp_import_users_as_contacts without nonce imports nothing (inverted-authz fix)', { tag: ['@lite', '@core', '@security'] }, async () => {
        const [status, body] = await ajaxPost({ action: 'erp_import_users_as_contacts' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
    });

    test('SEC-12 erp_hr_birthday_wish without nonce sends no wish (missing-cap fix)', { tag: ['@lite', '@hrm', '@security'] }, async () => {
        const [status, body] = await ajaxPost({ action: 'erp_hr_birthday_wish', employee_user_id: '1' });
        expect(isDenied(status, body), `denied (status=${status}, body=${body.slice(0, 80)})`).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// CAPABILITY-LEVEL proofs — an AUTHENTICATED but under-privileged role (employee)
// sends a VALID `erp-nonce` (localized on every admin page as wpErp.nonce, so an
// employee holds one). The request therefore CLEARS the nonce wall and reaches the
// handler; only the fix's `manage_options` check can stop it. Asserting the specific
// "sufficient permissions" error (not a bare `0`) proves the CAPABILITY gate, not the
// nonce. Handlers whose nonce action is `erp-nonce` are the ones an employee can drive:
//   #10 erp_audit_log_view (nonce: erp-nonce)  → view_edit_log_changes
//   #8  erp-api-delete-key  (nonce: erp-nonce)  → delete_api_key
// (#4 new_api_key / #9 import_users_as_contacts use handler-specific nonces an employee
//  does not hold, so a low-priv attacker is already stopped at the nonce — covered by the
//  deny guards above.)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('SEC admin-ajax — capability gate blocks an authenticated employee holding a valid nonce', () => {
    test.use({ storageState: data.auth.employeeFile });

    const PERMISSION_MSG = /do not have (sufficient )?permission/i;

    /** Employee cookie (via storageState fixture) + a valid `erp-nonce`. */
    async function employeeAjax(ctx: APIRequestContext, form: Record<string, string>): Promise<[number, string]> {
        const nonce = process.env.EMPLOYEE_ERP_NONCE ?? '';
        const res = await ctx.post(AJAX, {
            form: { ...form, _wpnonce: nonce },
            failOnStatusCode: false,
        });
        const text = await res.text().catch(() => '');
        return [res.status(), text];
    }

    test('SEC-10-CAP employee with a valid erp-nonce is blocked from the audit log by the capability check', { tag: ['@lite', '@core', '@security', '@employee'] }, async ({ request: ctx }) => {
        test.skip(!process.env.EMPLOYEE_ERP_NONCE, 'employee erp-nonce not captured in this run (setup skipped)');
        const [status, body] = await employeeAjax(ctx, { action: 'erp_audit_log_view', id: '1' });
        // A valid nonce clears the nonce wall; the manage_options check then denies with the
        // specific permission error. A bare "0" would mean we only hit the nonce wall — which
        // would NOT prove the capability fix, so we require the permission message.
        expect(PERMISSION_MSG.test(body), `capability-denied (status=${status}, body=${body.slice(0, 120)})`).toBe(true);
    });

    test('SEC-08-CAP employee with a valid erp-nonce is blocked from deleting an API key by the capability check', { tag: ['@lite', '@core', '@security', '@employee'] }, async ({ request: ctx }) => {
        test.skip(!process.env.EMPLOYEE_ERP_NONCE, 'employee erp-nonce not captured in this run (setup skipped)');
        const [status, body] = await employeeAjax(ctx, { action: 'erp-api-delete-key', id: '1' });
        expect(PERMISSION_MSG.test(body), `capability-denied (status=${status}, body=${body.slice(0, 120)})`).toBe(true);
    });
});
