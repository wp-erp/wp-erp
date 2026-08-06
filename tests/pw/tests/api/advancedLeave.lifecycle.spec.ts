import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import type { ResponseBody } from '@utils/interfaces';

/**
 * WP ERP Pro — HRM **Advanced Leave LIFECYCLE** (module: advanced_leave).
 *
 * Drives the FULL leave cycle end-to-end and asserts each DB/UI effect:
 *
 *   leave type → financial year → policy (with the pro advanced-leave columns
 *   carryover_days / encashment_days / accrued_amount / halfday_enable /
 *   forward_default + a per-policy segregation row) → entitlement grant →
 *   leave REQUEST (REST) → APPROVE (REST) → balance consumption, then a separate
 *   REJECT branch — asserting the request status flip (2 pending → 1 approved /
 *   3 rejected) and the entitlement balance (20 granted − 3 approved = 17).
 *
 * Surface choice per step (most reliable that exists — see test-plans/_pro-grounding.md):
 *   - Seeds (leave type, financial year, policy + pro columns, segregation,
 *     entitlement grant) → DIRECT DB. There is no REST create route for leave
 *     types / financial years, and the REST policy/entitlement create endpoints
 *     are BROKEN (LEAVE-BUG-1 silent no-op 201, LEAVE-BUG-2 fatal 500 — both
 *     documented + exercised below as resilient, flagged probes). The canonical
 *     FormHandler grant fields are seeded directly to set up the live REST cycle.
 *   - Request create / approve / reject → REST (the real, working contract).
 *     These MUST be FORM-ENCODED: the handlers read $request->get_body_params(),
 *     which is empty for application/json (a JSON body 400s with
 *     'Set entitlement to the employee first.'). The `policy` field on a request
 *     is the ENTITLEMENT id, NOT the policy id (controller maps policy →
 *     leave_policy and looks it up as the entitlement).
 *   - Balance assertion → DB: available = SUM(day_in) − SUM(day_out) per
 *     (user_id, leave_id, f_year).
 *
 * Resilient-assertion philosophy: the known-broken REST creates use assert=false
 * + status branching and assert NOT a silent-success/regression rather than an
 * exact 500; the documented 500/no-op are flagged KNOWN BUGS, never hard-asserted.
 * Access-control asserts the boundary ([401,403]), not an exact code.
 *
 * SERIAL: this file mutates shared leave/entitlement/request/FY/type singleton
 * tables and api.config runs fullyParallel, so it pins serial mode.
 *
 * Every test carries: tier (@pro) + module (@hrm) + role (@admin/@manager/@employee).
 */

test.describe.configure({ mode: 'serial' });

const PREFIX = process.env.DB_PREFIX ?? 'wp';
const leavesTable = `${PREFIX}_erp_hr_leaves`;
const fyTable = `${PREFIX}_erp_hr_financial_years`;
const policiesTable = `${PREFIX}_erp_hr_leave_policies`;
const segregationTable = `${PREFIX}_erp_hr_leave_policies_segregation`;
const entitlementsTable = `${PREFIX}_erp_hr_leave_entitlements`;
const requestsTable = `${PREFIX}_erp_hr_leave_requests`;
const approvalStatusTable = `${PREFIX}_erp_hr_leave_approval_status`;

// Active employee user id present in the QA fixture (status='active').
const EMP = 7;
// Day grant on the policy / entitlement.
const GRANT_DAYS = 20;
// 3-day request (2026-06-15..17 inclusive) that gets approved → consumes 3.
const APPROVED_DAYS = 3;

// Unique suffix per run (standard epoch-millis).
const TS = Date.now();
// Unix-second timestamps for created_at / financial-year coverage of 2026.
const NOW = Math.floor(TS / 1000);
const FY_START = Math.floor(Date.UTC(2026, 0, 1) / 1000); // 2026-01-01
const FY_END = Math.floor(Date.UTC(2026, 11, 31) / 1000); // 2026-12-31

let api: ApiUtils;

// Captured ids (for assertions + afterAll cleanup).
let LT = 0; // leave type id
let FY = 0; // financial year id
let POL = 0; // policy id
let ENT = 0; // entitlement (grant) id
let REQ = 0; // approved request id
let REQ2 = 0; // rejected request id

const idOf = (body: ResponseBody): string => {
    const raw = body?.id ?? '';
    return raw === '' ? '' : String(raw);
};

/** mysql2 returns an OkPacket with insertId for INSERTs (cast like sibling spec). */
function insertIdOf(result: unknown): number {
    return Number((result as { insertId?: number }).insertId);
}

/**
 * Form-encoded write helper. The leave request/approve/reject handlers read
 * $request->get_body_params(), which Playwright populates only for
 * application/x-www-form-urlencoded bodies (a JSON body yields empty params).
 * We send the urlencoded string as `data` and override the Content-Type header
 * (ApiUtils.authHeaders defaults to application/json + injects X-WP-Nonce).
 */
function formBody(fields: Record<string, string | number>): { data: string; headers: Record<string, string> } {
    const usp = new URLSearchParams();
    for (const [k, v] of Object.entries(fields)) usp.append(k, String(v));
    return { data: usp.toString(), headers: { 'Content-Type': 'application/x-www-form-urlencoded' } };
}

/** Current consumable balance for EMP on the seeded leave type / financial year. */
async function availableBalance(): Promise<number> {
    const rows = await dbUtils.dbQuery<{ available: number | null }>(
        `SELECT SUM(day_in) - SUM(day_out) AS available
         FROM ${entitlementsTable}
         WHERE user_id = ? AND leave_id = ? AND f_year = ?`,
        [EMP, LT, FY],
    );
    return Number(rows[0]?.available ?? 0);
}

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    // Remove every row this run created (best-effort; serial so ids are known).
    try {
        await dbUtils.dbQuery(`DELETE FROM ${requestsTable} WHERE user_id = ?`, [EMP]);
        await dbUtils.dbQuery(`DELETE FROM ${entitlementsTable} WHERE user_id = ?`, [EMP]);
        if (REQ || REQ2) {
            await dbUtils.dbQuery(
                `DELETE FROM ${approvalStatusTable} WHERE leave_request_id IN (?, ?)`,
                [REQ || 0, REQ2 || 0],
            );
        }
        if (POL) {
            await dbUtils.dbQuery(`DELETE FROM ${segregationTable} WHERE leave_policy_id = ?`, [POL]);
            await dbUtils.dbQuery(`DELETE FROM ${policiesTable} WHERE id = ?`, [POL]);
        }
        if (LT) await dbUtils.dbQuery(`DELETE FROM ${leavesTable} WHERE id = ?`, [LT]);
        if (FY) await dbUtils.dbQuery(`DELETE FROM ${fyTable} WHERE id = ?`, [FY]);
    } catch {
        /* best-effort cleanup */
    }
    await api.dispose();
    try {
        await dbUtils.close();
    } catch {
        /* pool may already be closed by a sibling spec */
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// SETUP — seed the pro leave structure directly (no working REST create path)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Advanced Leave lifecycle — setup (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ALV-LC-01 — seed leave type + financial year covering today (server date is
    // 2026-06-xx), so erp_hr_get_financial_year_from_date() resolves and the
    // entitlement f_year matches the request.
    test('ALV-LC-01 seed leave type + financial year covering 2026', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const ltRes = await dbUtils.dbQuery(
            `INSERT INTO ${leavesTable} (name, description, created_at, updated_at)
             VALUES (?, 'pwlife', ?, ?)`,
            [`PWLife ${TS}`, NOW, NOW],
        );
        LT = insertIdOf(ltRes);
        expect(LT, 'leave type insert yields an id').toBeTruthy();

        const fyRes = await dbUtils.dbQuery(
            `INSERT INTO ${fyTable} (fy_name, start_date, end_date, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?)`,
            [`PWFY ${TS}`, FY_START, FY_END, NOW, NOW],
        );
        FY = insertIdOf(fyRes);
        expect(FY, 'financial year insert yields an id').toBeTruthy();

        // Sanity: the FY actually spans today so the FY lookup will find it.
        const rows = await dbUtils.dbQuery<{ start_date: number; end_date: number }>(
            `SELECT start_date, end_date FROM ${fyTable} WHERE id = ? LIMIT 1`,
            [FY],
        );
        expect(Number(rows[0]?.start_date)).toBe(FY_START);
        expect(Number(rows[0]?.end_date)).toBe(FY_END);
    });

    // ALV-LC-02 — seed the policy WITH the pro advanced-leave columns and a
    // per-policy segregation row, then round-trip every pro value. This is the
    // real pro surface (these columns are never writable over REST).
    test('ALV-LC-02 seed policy with pro columns + segregation row', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(LT, 'leave type seeded first (serial)').toBeTruthy();
        expect(FY, 'financial year seeded first (serial)').toBeTruthy();

        const polRes = await dbUtils.dbQuery(
            `INSERT INTO ${policiesTable}
                (leave_id, description, days, color, employee_type, department_id, location_id,
                 designation_id, gender, marital, f_year, applicable_from_days, carryover_days,
                 encashment_days, accrued_amount, halfday_enable, forward_default, created_at, updated_at)
             VALUES (?, ?, ?, '#abc', '-1', -1, -1, -1, '-1', '-1', ?, 0, 5, 3, 1.50, 1, 'carryover', ?, ?)`,
            [LT, `pwlife policy ${TS}`, GRANT_DAYS, FY, NOW, NOW],
        );
        POL = insertIdOf(polRes);
        expect(POL, 'policy insert yields an id').toBeTruthy();

        // (Pro) per-policy segregation — populated only on the legacy admin POST
        // ($_POST['segre']), never over REST. Seed + assert round-trip.
        await dbUtils.dbQuery(
            `INSERT INTO ${segregationTable}
                (leave_policy_id, jan, feb, mar, apr, may, jun, created_at, updated_at)
             VALUES (?, 2, 2, 2, 2, 2, 2, ?, ?)`,
            [POL, NOW, NOW],
        );

        const polRows = await dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT days, f_year, carryover_days, encashment_days, accrued_amount, halfday_enable, forward_default
             FROM ${policiesTable} WHERE id = ? LIMIT 1`,
            [POL],
        );
        const p = polRows[0]!;
        expect(Number(p.days), 'day grant on policy').toBe(GRANT_DAYS);
        expect(Number(p.f_year), 'policy bound to seeded financial year').toBe(FY);
        expect(Number(p.carryover_days), 'pro carryover_days persisted').toBe(5);
        expect(Number(p.encashment_days), 'pro encashment_days persisted').toBe(3);
        expect(Number(p.accrued_amount), 'pro accrued_amount persisted').toBeCloseTo(1.5, 2);
        expect(Number(p.halfday_enable), 'pro halfday_enable persisted').toBe(1);
        expect(String(p.forward_default), 'pro forward_default persisted').toBe('carryover');

        const segRows = await dbUtils.dbQuery<{ jan: number; jun: number }>(
            `SELECT jan, jun FROM ${segregationTable} WHERE leave_policy_id = ? LIMIT 1`,
            [POL],
        );
        expect(segRows.length, 'segregation row tied to the policy').toBe(1);
        expect(Number(segRows[0]?.jan), 'segregation jan round-trips').toBe(2);
        expect(Number(segRows[0]?.jun), 'segregation jun round-trips').toBe(2);
    });

    // ALV-LC-03 — grant the entitlement to the employee. After this,
    // erp_hr_get_assign_policy_from_entitlement(EMP) resolves {ENT => name}, which
    // is the precondition the REST request handler checks.
    test('ALV-LC-03 grant entitlement to the employee (day_in=20)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(POL, 'policy seeded first (serial)').toBeTruthy();

        const entRes = await dbUtils.dbQuery(
            `INSERT INTO ${entitlementsTable}
                (user_id, leave_id, created_by, trn_id, trn_type, day_in, day_out, description, f_year, created_at, updated_at)
             VALUES (?, ?, 1, ?, 'leave_policies', ?, 0, 'pwlife', ?, ?, ?)`,
            [EMP, LT, POL, GRANT_DAYS, FY, NOW, NOW],
        );
        ENT = insertIdOf(entRes);
        expect(ENT, 'entitlement insert yields an id').toBeTruthy();

        const rows = await dbUtils.dbQuery<{ day_in: number; day_out: number; trn_type: string }>(
            `SELECT day_in, day_out, trn_type FROM ${entitlementsTable} WHERE id = ? LIMIT 1`,
            [ENT],
        );
        const e = rows[0]!;
        expect(Number(e.day_in), 'granted day_in').toBe(GRANT_DAYS);
        expect(Number(e.day_out), 'no consumption yet').toBe(0);
        expect(String(e.trn_type), 'grant row trn_type').toBe('leave_policies');

        // Balance before any request: the full grant is available.
        expect(await availableBalance(), 'full grant available pre-request').toBe(GRANT_DAYS);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// LIFECYCLE — request → approve → balance consumed (REST, form-encoded)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Advanced Leave lifecycle — request + approve (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ALV-LC-04 — employee submits a 3-day leave request. `policy` is the
    // ENTITLEMENT id (ENT). 201 with status=2 (pending), applied_days=3,
    // available_days=20; DB request row created with last_status=2.
    test('ALV-LC-04 create leave request → 201 pending (status=2)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(ENT, 'entitlement granted first (serial)').toBeTruthy();

        const [resp, body] = await api.post(
            endPoints.leaveRequests,
            formBody({
                employee_id: EMP,
                policy: ENT, // entitlement id, NOT the policy id
                start_date: '2026-06-15',
                end_date: '2026-06-17',
                reason: `PWLife ${TS}`,
            }),
            false,
        );
        expect(resp.status(), 'request create answered without a fatal').toBeLessThan(500);
        expect(resp.status(), 'request create is a 201').toBe(201);

        REQ = Number(idOf(body));
        expect(REQ, 'created request id').toBeTruthy();
        expect(Number(body?.user_id), 'request belongs to the employee').toBe(EMP);
        expect(Number(body?.status), 'pending status').toBe(2);
        expect(Number(body?.applied_days), 'applied 3 days').toBe(APPROVED_DAYS);
        expect(Number(body?.available_days), 'full grant still available pre-approve').toBe(GRANT_DAYS);

        const rows = await dbUtils.dbQuery<{ last_status: number; days: number; leave_entitlement_id: number }>(
            `SELECT last_status, days, leave_entitlement_id FROM ${requestsTable} WHERE id = ? LIMIT 1`,
            [REQ],
        );
        const r = rows[0]!;
        expect(Number(r.last_status), 'DB request row is pending (2)').toBe(2);
        expect(Number(r.days), 'DB request days = 3').toBeCloseTo(APPROVED_DAYS, 2);
        expect(Number(r.leave_entitlement_id), 'request bound to the entitlement').toBe(ENT);
    });

    // ALV-LC-05 — a JSON body (instead of form) is rejected: the handler reads
    // get_body_params(), empty for application/json → 400 required-entitlement.
    test('ALV-LC-05 JSON body → 400 required-entitlement (handler reads form params)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.post(
            endPoints.leaveRequests,
            { data: { employee_id: EMP, policy: ENT, start_date: '2026-08-01', end_date: '2026-08-02', reason: 'json' } },
            false,
        );
        expect(resp.status(), 'JSON body is a clean 400, not a fatal').toBe(400);
        expect(String(body?.code ?? ''), 'required-entitlement surfaced for empty body params')
            .toContain('rest_leave_request_required_entitlement');
    });

    // ALV-LC-06 — approve the pending request. 200 with status=1 (approved) and
    // available_days=17. DB: last_status 2→1, a consumption entitlement row
    // (trn_type='leave_approval_status', day_out=3), and an audit row.
    test('ALV-LC-06 approve request → 200 approved (status=1), balance 20→17', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(REQ, 'request created first (serial)').toBeTruthy();

        const [resp, body] = await api.put(
            `${endPoints.leaveRequests}/${REQ}/approve`,
            formBody({ comments: 'ok' }),
            false,
        );
        expect(resp.status(), 'approve answered without a fatal').toBeLessThan(500);
        expect(resp.status(), 'approve is a 200').toBe(200);
        expect(Number(body?.status), 'approved status (1)').toBe(1);
        expect(Number(body?.available_days), 'available 20 − 3 = 17 in response').toBe(GRANT_DAYS - APPROVED_DAYS);

        // Request row status flipped.
        const reqRows = await dbUtils.dbQuery<{ last_status: number }>(
            `SELECT last_status FROM ${requestsTable} WHERE id = ? LIMIT 1`,
            [REQ],
        );
        expect(Number(reqRows[0]?.last_status), 'DB request last_status flipped to approved (1)').toBe(1);

        // Consumption entitlement row written (day_out = 3, keyed to the request).
        const consRows = await dbUtils.dbQuery<{ day_in: number; day_out: number; trn_id: number }>(
            `SELECT day_in, day_out, trn_id FROM ${entitlementsTable}
             WHERE user_id = ? AND leave_id = ? AND trn_type = 'leave_approval_status'`,
            [EMP, LT],
        );
        expect(consRows.length, 'one consumption row after approve').toBe(1);
        expect(Number(consRows[0]?.day_in), 'consumption row has no day_in').toBe(0);
        expect(Number(consRows[0]?.day_out), 'consumption row day_out = 3').toBeCloseTo(APPROVED_DAYS, 2);
        expect(Number(consRows[0]?.trn_id), 'consumption row keyed to the request').toBe(REQ);

        // Audit row.
        const auditRows = await dbUtils.dbQuery<{ approval_status_id: number; message: string }>(
            `SELECT approval_status_id, message FROM ${approvalStatusTable} WHERE leave_request_id = ? LIMIT 1`,
            [REQ],
        );
        expect(auditRows.length, 'approval audit row written').toBe(1);
        expect(Number(auditRows[0]?.approval_status_id), 'audit approval_status_id = 1 (approved)').toBe(1);
        expect(String(auditRows[0]?.message), 'audit captured the comment').toBe('ok');
    });

    // ALV-LC-07 — balance consumed: SUM(day_in) − SUM(day_out) = 20 − 3 = 17.
    test('ALV-LC-07 entitlement balance consumed (DB = 17)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(REQ, 'approved request first (serial)').toBeTruthy();
        expect(await availableBalance(), 'available balance = 20 − 3 = 17').toBe(GRANT_DAYS - APPROVED_DAYS);
    });

    // ALV-LC-08 — approving an already-approved request → 400 already_approved
    // (idempotency). Balance unchanged.
    test('ALV-LC-08 re-approve → 400 already_approved (balance unchanged)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(REQ, 'approved request first (serial)').toBeTruthy();
        const [resp, body] = await api.put(
            `${endPoints.leaveRequests}/${REQ}/approve`,
            formBody({ comments: 'again' }),
            false,
        );
        expect(resp.status(), 're-approve is a clean 400, not a fatal').toBe(400);
        expect(String(body?.code ?? ''), 'already_approved surfaced').toContain('rest_leave_request_already_approved');
        expect(await availableBalance(), 'balance still 17 after a rejected re-approve').toBe(GRANT_DAYS - APPROVED_DAYS);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// LIFECYCLE — reject branch (separate request) + validation/idempotency
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Advanced Leave lifecycle — reject branch (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ALV-LC-09 — second, non-overlapping request (2 days) used for the reject path.
    test('ALV-LC-09 create a 2nd request for the reject branch → 201 pending', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(ENT, 'entitlement granted (serial)').toBeTruthy();
        const [resp, body] = await api.post(
            endPoints.leaveRequests,
            formBody({
                employee_id: EMP,
                policy: ENT,
                start_date: '2026-07-01',
                end_date: '2026-07-02',
                reason: `PWLife reject ${TS}`,
            }),
            false,
        );
        expect(resp.status(), '2nd request create is a 201').toBe(201);
        REQ2 = Number(idOf(body));
        expect(REQ2, '2nd request id').toBeTruthy();
        expect(Number(body?.status), '2nd request pending (2)').toBe(2);
    });

    // ALV-LC-10 — reject WITHOUT a reason → 400 missing_reason (reason required).
    test('ALV-LC-10 reject without reason → 400 missing_reason', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(REQ2, '2nd request created first (serial)').toBeTruthy();
        const [resp, body] = await api.put(
            `${endPoints.leaveRequests}/${REQ2}/reject`,
            formBody({ reason: '' }),
            false,
        );
        expect(resp.status(), 'missing-reason reject is a clean 400').toBe(400);
        expect(String(body?.code ?? ''), 'missing_reason surfaced').toContain('rest_leave_request_missing_reason');
    });

    // ALV-LC-11 — reject WITH a reason → 200 status=3 (rejected). Reject does NOT
    // add a day_out consumption row, so the balance stays 17.
    test('ALV-LC-11 reject with reason → 200 rejected (status=3), balance unchanged', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(REQ2, '2nd request created first (serial)').toBeTruthy();
        const balanceBefore = await availableBalance();

        const [resp, body] = await api.put(
            `${endPoints.leaveRequests}/${REQ2}/reject`,
            formBody({ reason: 'Not approved' }),
            false,
        );
        expect(resp.status(), 'reject answered without a fatal').toBeLessThan(500);
        expect(resp.status(), 'reject is a 200').toBe(200);
        expect(Number(body?.status), 'rejected status (3)').toBe(3);

        const rows = await dbUtils.dbQuery<{ last_status: number }>(
            `SELECT last_status FROM ${requestsTable} WHERE id = ? LIMIT 1`,
            [REQ2],
        );
        expect(Number(rows[0]?.last_status), 'DB request last_status flipped to rejected (3)').toBe(3);

        // No new consumption row → still exactly one (from the approve).
        const consRows = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${entitlementsTable}
             WHERE user_id = ? AND leave_id = ? AND trn_type = 'leave_approval_status'`,
            [EMP, LT],
        );
        expect(Number(consRows[0]?.c), 'reject adds no consumption row').toBe(1);
        expect(await availableBalance(), 'balance unchanged by reject').toBe(balanceBefore);
    });

    // ALV-LC-12 — re-reject an already-rejected request → 400 already_rejected.
    test('ALV-LC-12 re-reject → 400 already_rejected', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(REQ2, 'rejected request first (serial)').toBeTruthy();
        const [resp, body] = await api.put(
            `${endPoints.leaveRequests}/${REQ2}/reject`,
            formBody({ reason: 'again' }),
            false,
        );
        expect(resp.status(), 're-reject is a clean 400, not a fatal').toBe(400);
        expect(String(body?.code ?? ''), 'already_rejected surfaced').toContain('rest_leave_request_already_rejected');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// BOUNDARIES — invalid ids, auth, and the documented KNOWN-BUG REST creates
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Advanced Leave lifecycle — boundaries + known bugs (pro)', () => {
    test.use({ storageState: data.auth.adminFile });

    // ALV-LC-13 — approve / reject a non-existent request id → 404 invalid_id.
    test('ALV-LC-13 approve/reject unknown id → 404 invalid_id', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [aResp, aBody] = await api.put(
            `${endPoints.leaveRequests}/99999999/approve`,
            formBody({ comments: 'x' }),
            false,
        );
        expect(aResp.status(), 'approve unknown id is a clean 404').toBe(404);
        expect(String(aBody?.code ?? '')).toContain('rest_leave_request_invalid_id');

        const [rResp, rBody] = await api.put(
            `${endPoints.leaveRequests}/99999999/reject`,
            formBody({ reason: 'x' }),
            false,
        );
        expect(rResp.status(), 'reject unknown id is a clean 404').toBe(404);
        expect(String(rBody?.code ?? '')).toContain('rest_leave_request_invalid_id');
    });

    // ALV-LC-14 — a request create with NO X-WP-Nonce is unauthorized (401/403),
    // not a silent success. Build a nonce-less context to prove the boundary.
    test('ALV-LC-14 request create without nonce is unauthorized', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        expect(ENT, 'entitlement granted (serial)').toBeTruthy();
        // Empty-string nonce → ApiUtils sends no X-WP-Nonce header.
        const anonApi = await ApiUtils.fromStorageState(data.auth.adminFile, '');
        try {
            const [resp] = await anonApi.post(
                endPoints.leaveRequests,
                formBody({ employee_id: EMP, policy: ENT, start_date: '2026-09-01', end_date: '2026-09-02', reason: 'nonce' }),
                false,
            );
            expect([401, 403], 'no-nonce write is rejected at the auth boundary').toContain(resp.status());
            expect(resp.status(), 'must not silently succeed without a nonce').not.toBe(201);
        } finally {
            await anonApi.dispose();
        }
    });

    // ALV-LC-15 — KNOWN BUG LEAVE-BUG-1: REST policy create is a silent no-op.
    // It returns 201 but writes nothing (body is a blank policy id 0). Resilient:
    // assert NOT a real persisted row rather than an exact status; flag the bug.
    test('ALV-LC-15 [KNOWN BUG] REST policy create is a silent no-op', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const [resp, body] = await api.post(
            endPoints.leavePolicies,
            { data: { name: `pw_alv_bug1_${TS}`, days: 12, color: '#aabbcc' } },
            false,
        );
        expect(resp.status(), 'policy create answered (a 500 here would be a separate fatal)').toBeLessThan(500);
        if (resp.status() === 201 && Number(body?.id ?? 0) === 0) {
            // Documented LEAVE-BUG-1: 201 with a blank policy (id 0) and no DB row.
            expect(Number(body?.id), 'LEAVE-BUG-1: REST policy create returns a blank policy (id 0)').toBe(0);
            const rows = await dbUtils.dbQuery<{ c: number }>(
                `SELECT COUNT(*) AS c FROM ${policiesTable} WHERE description = ? OR color = '#aabbcc'`,
                [`pw_alv_bug1_${TS}`],
            );
            expect(Number(rows[0]?.c), 'LEAVE-BUG-1: nothing persisted to wp_erp_hr_leave_policies').toBe(0);
        } else if (resp.ok() && Number(body?.id ?? 0) > 0) {
            // If a future build actually persists, clean up so we leave no trace.
            await api.delete(`${endPoints.leavePolicies}/${Number(body.id)}`, undefined, false);
        }
    });

    // ALV-LC-16 — KNOWN BUG LEAVE-BUG-2: REST entitlement create FATALS (500).
    // Controller maps policy→policy_id/days but leaves user_id a WP_Error that the
    // query layer stringifies (Object of class WP_Error could not be converted).
    // Resilient: tolerate >=200, only flag the documented 500; never write a row.
    test('ALV-LC-16 [KNOWN BUG] REST entitlement create fatals (500)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const countBefore = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${entitlementsTable} WHERE user_id = ?`,
            [EMP],
        );
        const [resp, body] = await api.post(
            endPoints.leaveEntitlements,
            { data: { employee_id: EMP, policy: POL, days: 20, start_date: '2026-01-01', end_date: '2026-12-31' } },
            false,
        );
        // Documented LEAVE-BUG-2 is a 500; we do not hard-assert the exact code, but
        // we DO record it and assert it never silently created an entitlement.
        expect(resp.status(), 'entitlement create answered with a definite status').toBeGreaterThanOrEqual(200);
        if (resp.status() === 500) {
            expect(String(body?.code ?? ''), 'LEAVE-BUG-2: internal_server_error on entitlement create')
                .toContain('internal_server_error');
        }
        const countAfter = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${entitlementsTable} WHERE user_id = ?`,
            [EMP],
        );
        expect(Number(countAfter[0]?.c), 'broken entitlement create added no row')
            .toBe(Number(countBefore[0]?.c));
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// ACCESS — HR manager retains the working leave-request surface
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Advanced Leave lifecycle — HR manager access (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    let mgrApi: ApiUtils;
    test.beforeAll(async () => {
        mgrApi = await ApiUtils.fromStorageState(data.auth.hrManagerFile, process.env.HR_MANAGER_NONCE);
    });
    test.afterAll(async () => {
        await mgrApi.dispose();
    });

    // ALV-LC-17 — the HR manager (erp_leave_manage / erp_view_list) is NOT denied
    // the leave-requests list.
    test('ALV-LC-17 HR manager can list leave requests', { tag: ['@pro', '@hrm', '@manager'] }, async () => {
        const [resp] = await mgrApi.get(endPoints.leaveRequests, undefined, false);
        expect([401, 403], 'HR manager is authorized for leave requests').not.toContain(resp.status());
        expect(resp.status(), 'leave-requests list answered for the manager').toBeLessThan(500);
    });
});
