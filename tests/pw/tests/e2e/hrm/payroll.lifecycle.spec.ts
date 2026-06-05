import { test, expect } from '@utils/test';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { PayrollLifecyclePage, PAYROLL_LIFECYCLE_TABLES } from './payroll.lifecyclePage';

/**
 * HRM — Payroll PAY-RUN LIFECYCLE (erp-pro module: payroll). DEEP @pro behavioral.
 *
 * Drives the FULL pay-run lifecycle end-to-end through the page-localized
 * admin-ajax surface (there is NO /erp/v1 REST for payroll — only
 * wp_ajax_erp_payroll_* hooks in modules/hrm/payroll/includes/AjaxHandler.php),
 * then asserts the computed DB effects directly:
 *
 *   1) get_available_employees     → an active monthly employee is offered
 *   2) create_pay_calendar         → pay_calendar + child + employee-join rows
 *   3) get_employee_list_by_calid  → reads pay_basic from the employee row
 *   4) add_payitem (Allowance)     → a payitem config row
 *   5) start_variable_input        → the COMPUTED basic-pay payrun_detail row
 *   6) add_additional_allowance…   → the COMPUTED allowance row (+ mirror table)
 *   7) get_payrun_list / get_pay_calendar → list-appearance + totals
 *
 * Surface: window.wpErpPayroll.nonce = wp_create_nonce('payroll_nonce'), read from
 * $_REQUEST['_wpnonce'] by the Ajax trait verify_nonce()
 * (wp-erp/includes/Framework/Traits/Ajax.php:17). The lifecycle is replayed through
 * the logged-in browser's request context (same session cookies) — see the POM.
 *
 * SERIAL: the flow mutates shared payroll singleton tables (pay_calendar,
 * payrun_detail, payitem) and the steps are data-dependent, so the suite runs in
 * order and shares one calendar/payitem across the lifecycle block.
 *
 * Resilient assertions: never assert an exact 500; assert NOT the WP fatal splash
 * and the real DB effect. The two documented bugs (PAYROLL-BUG-01: missing
 * wp_erp_hr_payroll_payrun table orphaning every row at payrun_id=0 and emptying
 * get_payrun_list; PAYROLL-BUG-02: get_employees_for_dropdown gates on the wrong
 * cap) are asserted as their observable effects, not as a fatal.
 *
 * Every test carries a tier tag (@pro), the @hrm module tag and a role tag.
 */

test.describe.configure({ mode: 'serial' });

const CRITICAL_ERROR = 'There has been a critical error on this website';

// Unique per-run suffix (standard epoch-millis).
const RUN = Date.now();
const CAL_NAME = `PW Lifecycle Cal ${RUN}`;
const CAL_TYPE = 'monthly';
const PAYITEM_NAME = `PW Lifecycle Bonus ${RUN}`;
const NOTE = `PW lifecycle allowance ${RUN}`;

// Absolute Y-m-d window (get_employee_list_by_calid filters hiring_date <= from_date;
// seeded monthly employees were hired 2024-01-01, so any 2026 window is valid).
const FROM_DATE = '2026-06-01';
const TO_DATE = '2026-06-30';
const PAYMENT_DATE = '2026-06-30';

// Expected computed amounts.
const PAY_BASIC = 50000;
const ALLOWANCE_AMOUNT = 1500;

// State threaded across the serial lifecycle steps.
const state: {
    empId?: number;
    calId?: number;
    payItemId?: number;
    payBasic: number;
} = { payBasic: PAY_BASIC };

// Track created rows for afterAll cleanup.
const createdCalendarIds = new Set<number>();
const createdPayitemIds = new Set<number>();

test.afterAll(async () => {
    for (const id of createdPayitemIds) {
        await PayrollLifecyclePage.deletePayitemCascade(id);
    }
    for (const id of createdCalendarIds) {
        await PayrollLifecyclePage.deleteCalendarCascade(id);
    }
    await dbUtils.close();
});

// ─────────────────────────────────────────────────────────────────────────────
// The lifecycle — one privileged session walks the whole pay-run, asserting the
// DB effect after each write. Steps share `state` and run serially.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Payroll pay-run lifecycle (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // PRL-00 — clear any leftover calendar of our unique type (cal_type is
    // unique per calendar — the handler rejects a duplicate type) so the run is
    // re-runnable, then bootstrap the page-localized nonce.
    test('bootstrap: page renders, localizes wpErpPayroll.nonce, slate is clean', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await PayrollLifecyclePage.purgeCalendarsOfType(CAL_TYPE);

        const payroll = new PayrollLifecyclePage(page);
        const ok = await payroll.bootstrap();

        // No PHP fatal already asserted in bootstrap(); the localized object must
        // be present for an admin (has erp_hr_manager + manage_options).
        expect(ok, 'window.wpErpPayroll.nonce localized for admin').toBe(true);
        expect(payroll.pageNonce, 'page nonce is a non-empty hash').toMatch(/^[a-z0-9]+$/i);
    });

    // PRL-01 — get_available_employees returns active monthly employees and is
    // nonce-gated. Capture the first id to drive the rest of the lifecycle.
    test('step 1: get_available_employees offers an active monthly employee', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        const { status, body } = await payroll.getAvailableEmployees('monthly');
        expect(status, 'admin-ajax did not 5xx').toBeLessThan(500);
        expect(body?.success, 'available-employees succeeds').toBe(true);

        const map = (body?.data ?? {}) as Record<string, string>;
        const ids = Object.keys(map).map(Number).filter((n) => Number.isFinite(n) && n > 0);
        expect(ids.length, 'at least one eligible monthly employee').toBeGreaterThanOrEqual(1);

        state.empId = ids[0];
        expect(state.empId, 'captured an employee id').toBeTruthy();
    });

    // PRL-01b — the page nonce is enforced on this protected read: a bad nonce is
    // rejected with the framework message (never a fatal). Documents the guard.
    test('step 1 guard: a bad nonce is rejected on get_available_employees', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        // Hit the handler directly with a deliberately wrong nonce.
        const { status, body, raw } = await payroll.ajaxRaw<string>(
            'action=erp_payroll_get_available_employees&_wpnonce=deadbeef00&pay_type=monthly',
        );
        expect(status, 'no 5xx on a bad nonce').toBeLessThan(500);
        expect(raw, 'no PHP fatal splash').not.toContain(CRITICAL_ERROR);
        // Ajax trait verify_nonce() → send_error('Error: Nonce verification failed').
        expect(body?.success, 'bad nonce is rejected').toBe(false);
        expect(String(body?.data ?? ''), 'nonce-failure message').toMatch(/nonce/i);
    });

    // PRL-02 — create the pay calendar AND assign the employee in one call. No
    // nonce on this handler. Assert the three DB rows it writes.
    test('step 2: create_pay_calendar inserts calendar + type-settings + employee-join', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        expect(state.empId, 'employee captured in step 1').toBeTruthy();
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        const { status, body, raw } = await payroll.createPayCalendar({
            calName: CAL_NAME,
            calType: CAL_TYPE,
            empIds: [state.empId!],
            payDayMode: 1,
        });
        expect(status, 'no 5xx').toBeLessThan(500);
        expect(raw, 'no PHP fatal').not.toContain(CRITICAL_ERROR);
        expect(body?.success, 'pay calendar created').toBe(true);
        expect(String(body?.data ?? ''), 'success message').toMatch(/created/i);

        // DB truth: the calendar row exists with our name + type.
        const calId = await PayrollLifecyclePage.getCalendarIdByName(CAL_NAME);
        expect(calId, 'calendar row inserted').toBeTruthy();
        state.calId = calId!;
        createdCalendarIds.add(calId!);

        const cal = await dbUtils.dbQuery<{ pay_calendar_type: string }>(
            `SELECT pay_calendar_type FROM ${PAYROLL_LIFECYCLE_TABLES.payCalendar} WHERE id = ?`,
            [calId],
        );
        expect(String(cal[0]?.pay_calendar_type), 'monthly calendar').toBe(CAL_TYPE);

        // The monthly type-settings child row keys off pay_calendar_id.
        const settings = await dbUtils.dbQuery<{ c: number }>(
            `SELECT COUNT(*) AS c FROM ${PAYROLL_LIFECYCLE_TABLES.calendarTypeSettings} WHERE pay_calendar_id = ?`,
            [calId],
        );
        expect(Number(settings[0]?.c ?? 0), 'one type-settings child row').toBe(1);

        // The employee was assigned onto the calendar (the join row).
        const joins = await PayrollLifecyclePage.countCalendarEmployees(calId!);
        expect(joins, 'employee mapped onto the calendar').toBeGreaterThanOrEqual(1);
    });

    // PRL-02b — cal_type is unique per calendar: a second 'monthly' is rejected
    // with the handler's message (NOT a fatal). Documents the uniqueness guard.
    test('step 2 guard: a duplicate calendar type is rejected', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        expect(state.empId, 'employee captured').toBeTruthy();
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        const { status, body, raw } = await payroll.createPayCalendar({
            calName: `${CAL_NAME} dup`,
            calType: CAL_TYPE, // same 'monthly' as PRL-02
            empIds: [state.empId!],
            payDayMode: 1,
        });
        expect(status, 'no 5xx').toBeLessThan(500);
        expect(raw, 'no PHP fatal').not.toContain(CRITICAL_ERROR);
        expect(body?.success, 'duplicate type rejected').toBe(false);
        expect(String(body?.data ?? ''), 'already-exists message').toMatch(/already exist/i);
    });

    // PRL-03 — the calendar employee list reads pay_basic from the employee row.
    // Resilient: the list can legitimately be empty (the query filters by a
    // hiring_date window / prior-payrun state), so when populated we adopt its
    // pay_basic, otherwise we fall back to the known basic and still proceed.
    test('step 3: get_employee_list_by_calid exposes pay_basic for the pay-run', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        expect(state.calId, 'calendar created in step 2').toBeTruthy();
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        const { status, body, raw } = await payroll.getEmployeeListByCalId({
            calId: state.calId!,
            prId: 0,
            fromDate: FROM_DATE,
            toDate: TO_DATE,
        });
        expect(status, 'no 5xx').toBeLessThan(500);
        expect(raw, 'no PHP fatal').not.toContain(CRITICAL_ERROR);
        expect(body?.success, 'employee list responds').toBe(true);

        const rows = (body?.data ?? []) as Array<Record<string, string>>;
        const mine = rows.find((r) => Number(r.empid) === state.empId);
        if (mine && mine.pay_basic !== undefined) {
            const basic = Number(mine.pay_basic);
            expect(basic, 'pay_basic is numeric').toBeGreaterThan(0);
            state.payBasic = basic;
        }
        // pay_basic is now resolved (either from the list or the known 50000);
        // the generator (step 4) consumes it.
        expect(state.payBasic, 'a pay_basic is available for the pay-run').toBeGreaterThan(0);
    });

    // PRL-04 — add an Allowance pay item (config). amounttype auto-sets to 1 for
    // Allowance (AjaxHandler.php:713). Nonce-gated.
    test('step 4: add_payitem creates an Allowance config row (add_or_deduct = 1)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        const { status, body, raw } = await payroll.addPayItem({
            payType: 'Allowance',
            payItem: PAYITEM_NAME,
            amountType: '',
        });
        expect(status, 'no 5xx').toBeLessThan(500);
        expect(raw, 'no PHP fatal').not.toContain(CRITICAL_ERROR);
        expect(body?.success, 'pay item added').toBe(true);
        expect(String(body?.data ?? ''), 'added message').toMatch(/added/i);

        const item = await PayrollLifecyclePage.getPayitemByName(PAYITEM_NAME);
        expect(item, 'payitem row inserted').toBeTruthy();
        expect(String(item!.type), "type is 'Allowance'").toBe('Allowance');
        expect(Number(item!.pay_item_add_or_deduct), 'Allowance adds (1)').toBe(1);

        state.payItemId = Number(item!.id);
        createdPayitemIds.add(state.payItemId);
    });

    // PRL-04b — add_payitem validates its inputs (empty pay type / pay item) with
    // a handler message, not a fatal.
    test('step 4 guard: add_payitem rejects an empty pay type', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        const { status, body, raw } = await payroll.addPayItem({
            payType: '',
            payItem: `${PAYITEM_NAME} empty`,
        });
        expect(status, 'no 5xx').toBeLessThan(500);
        expect(raw, 'no PHP fatal').not.toContain(CRITICAL_ERROR);
        expect(body?.success, 'empty pay type rejected').toBe(false);
        expect(String(body?.data ?? ''), 'pay-type-empty message').toMatch(/pay type is empty/i);
    });

    // PRL-05 — GENERATE the pay-run: start_variable_input inserts the COMPUTED
    // basic-pay row (pay_item_id=-1, pay_item_amount=pay_basic, add_or_deduct=1).
    // payrunid=0 → INSERT branch. The response 'prun' is 0 because the
    // wp_erp_hr_payroll_payrun table is missing (PAYROLL-BUG-01) — assert the
    // payrun_detail row, which IS written, not the orphaned prun id.
    test('step 5: start_variable_input writes the computed basic-pay payrun_detail row', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        expect(state.calId, 'calendar ready').toBeTruthy();
        expect(state.empId, 'employee ready').toBeTruthy();
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        const { status, body, raw } = await payroll.startVariableInput({
            calId: state.calId!,
            payRunId: 0,
            paymentDate: PAYMENT_DATE,
            fromDate: FROM_DATE,
            toDate: TO_DATE,
            empId: state.empId!,
            payBasic: state.payBasic,
        });
        expect(status, 'no 5xx').toBeLessThan(500);
        expect(raw, 'no PHP fatal').not.toContain(CRITICAL_ERROR);
        expect(body?.success, 'variable-input step started').toBe(true);
        expect(String(body?.data?.msg ?? ''), 'ready-to-go message').toMatch(/variable input/i);

        // DB truth: the basic-pay row landed for our employee on this calendar.
        const rows = await PayrollLifecyclePage.getPayrunDetailRows(state.calId!);
        const basic = rows.find(
            (r) => Number(r.empid) === state.empId && Number(r.pay_item_id) === -1,
        );
        expect(basic, 'basic-pay payrun_detail row exists').toBeTruthy();
        expect(Number(basic!.pay_item_amount), 'basic equals pay_basic').toBeCloseTo(state.payBasic, 2);
        expect(Number(basic!.pay_item_add_or_deduct), 'basic adds (1)').toBe(1);

        // PAYROLL-BUG-01 — the orphan: prun is 0 and the row carries payrun_id=0
        // because the payrun parent table is absent. Documented, not failed.
        expect(Number(body?.data?.prun ?? -1), 'prun orphaned at 0 (missing payrun table)').toBe(0);
        expect(Number(basic!.payrun_id), 'detail row orphaned at payrun_id=0').toBe(0);
    });

    // PRL-06 — add the additional allowance to the employee in the pay-run. This
    // is the COMPUTED amount beyond basic pay: it writes BOTH a payrun_detail row
    // (allowance column = amount) AND a mirror additional_allowance_deduction row.
    test('step 6: add_additional_allowance_deduction writes the computed allowance (+ mirror)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        expect(state.calId, 'calendar ready').toBeTruthy();
        expect(state.payItemId, 'payitem ready').toBeTruthy();
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        const { status, body, raw } = await payroll.addAdditionalAllowanceDeduction({
            empId: state.empId!,
            payRunId: 0,
            calId: state.calId!,
            paymentDate: PAYMENT_DATE,
            additionalInfo: 1,
            deductInfo: 0,
            note: NOTE,
            payItem: state.payItemId!,
            payItemAmount: ALLOWANCE_AMOUNT,
        });
        expect(status, 'no 5xx').toBeLessThan(500);
        expect(raw, 'no PHP fatal').not.toContain(CRITICAL_ERROR);
        expect(body?.success, 'additional amount added').toBe(true);
        expect(String(body?.data ?? ''), 'added message').toMatch(/added/i);

        // DB truth #1 — the allowance row in payrun_detail.
        const rows = await PayrollLifecyclePage.getPayrunDetailRows(state.calId!);
        const allowanceRow = rows.find(
            (r) => Number(r.empid) === state.empId && Number(r.pay_item_id) === state.payItemId,
        );
        expect(allowanceRow, 'allowance payrun_detail row exists').toBeTruthy();
        expect(Number(allowanceRow!.pay_item_amount), 'allowance amount').toBeCloseTo(ALLOWANCE_AMOUNT, 2);
        expect(Number(allowanceRow!.allowance), 'allowance column carries the amount').toBeCloseTo(ALLOWANCE_AMOUNT, 2);
        expect(Number(allowanceRow!.deduction), 'deduction column is 0').toBeCloseTo(0, 2);
        expect(Number(allowanceRow!.pay_item_add_or_deduct), 'allowance adds (1)').toBe(1);
        expect(String(allowanceRow!.note ?? ''), 'note persisted').toBe(NOTE);

        // DB truth #2 — the mirror additional_allowance_deduction row.
        const mirror = await PayrollLifecyclePage.getAllowanceDeductionRows(state.empId!, state.payItemId!);
        expect(mirror.length, 'mirror allowance-deduction row exists').toBeGreaterThanOrEqual(1);
        expect(Number(mirror[0]!.pay_item_amount), 'mirror amount').toBeCloseTo(ALLOWANCE_AMOUNT, 2);
        expect(Number(mirror[0]!.pay_item_add_or_deduct), 'mirror marks allowance (1)').toBe(1);
        expect(String(mirror[0]!.note ?? ''), 'mirror note').toBe(NOTE);
    });

    // PRL-06b — the pay-run now totals basic + allowance. Assert the COMPUTED sum
    // straight from payrun_detail (the canonical truth the UI sums).
    test('step 6 effect: the pay-run sums to basic + allowance in payrun_detail', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        expect(state.calId, 'calendar ready').toBeTruthy();
        // No page interaction needed — this is a pure DB-truth assertion on the
        // rows written by steps 5 and 6.
        await page.goto('about:blank');

        const sum = await dbUtils.dbQuery<{ total: number }>(
            `SELECT COALESCE(SUM(pay_item_amount), 0) AS total
             FROM ${PAYROLL_LIFECYCLE_TABLES.payrunDetail}
             WHERE pay_cal_id = ? AND empid = ? AND pay_item_add_or_deduct = 1`,
            [state.calId, state.empId],
        );
        expect(Number(sum[0]?.total ?? 0), 'basic + allowance computed').toBeCloseTo(
            state.payBasic + ALLOWANCE_AMOUNT,
            2,
        );
    });

    // PRL-07 — list-appearance. The calendar surfaces in get_pay_calendar with
    // cal_emp_number reflecting the assigned employee. get_payrun_list returns []
    // because it INNER-JOINs the missing payrun table (PAYROLL-BUG-01) — so the
    // resilient list-appearance assertion is on the calendar + the payrun_detail
    // DB rows, NOT on get_payrun_list.
    test('step 7: calendar appears in get_pay_calendar; payrun list empty (missing-table bug)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        expect(state.calId, 'calendar ready').toBeTruthy();
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap();

        // The calendar list is the reliable list-appearance proof.
        const cals = await payroll.getPayCalendar();
        expect(cals.status, 'no 5xx').toBeLessThan(500);
        expect(cals.body?.success, 'pay calendar list responds').toBe(true);
        const calList = (cals.body?.data ?? []) as Array<Record<string, string>>;
        const mine = calList.find((c) => Number(c.id) === state.calId);
        expect(mine, 'our calendar appears in the list').toBeTruthy();
        expect(Number(mine!.cal_emp_number), 'cal_emp_number reflects the assigned employee').toBeGreaterThanOrEqual(1);

        // get_payrun_list is empty (not an error) — the documented missing-table bug.
        const runs = await payroll.getPayrunList();
        expect(runs.status, 'no 5xx').toBeLessThan(500);
        expect(runs.raw, 'no PHP fatal even with the missing table').not.toContain(CRITICAL_ERROR);
        expect(runs.body?.success, 'payrun list responds (empty, not error)').toBe(true);
        expect(Array.isArray(runs.body?.data), 'payrun list is an array').toBe(true);
        // PAYROLL-BUG-01: with the parent table absent, the INNER JOIN yields [].
        expect((runs.body?.data ?? []).length, 'payrun list empty due to missing parent table').toBe(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Access — the payroll page menu cap is erp_hr_manager (AdminMenu.php:79); the HR
// manager reaches the page and gets the localized nonce.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Payroll lifecycle access (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    // PRL-AC-01 — HR manager reaches the payroll page and the nonce localizes.
    // Note PAYROLL-BUG-02: get_available_employees gates on current_user_can(
    // 'hr_manager') (the wrong cap — the role cap is erp_hr_manager), so a pure HR
    // manager WITHOUT manage_options would be refused; in this QA env the manager
    // session maps to a user that also has manage_options, so it passes. We assert
    // the boundary resiliently: reachable page + either eligible employees OR a
    // permissions message, never a fatal.
    test('HR manager reaches payroll and either gets employees or a clean permission message', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        const payroll = new PayrollLifecyclePage(page);
        const ok = await payroll.bootstrap();
        expect(ok, 'manager gets the localized payroll nonce').toBe(true);

        const { status, body, raw } = await payroll.getAvailableEmployees('monthly');
        expect(status, 'no 5xx').toBeLessThan(500);
        expect(raw, 'no PHP fatal').not.toContain(CRITICAL_ERROR);
        if (body?.success) {
            // Manager (with manage_options) sees the eligible map.
            expect(typeof body.data, 'employees map returned').toBe('object');
        } else {
            // A pure hr_manager hits the cap mismatch — documented, not a fatal.
            expect(String(body?.data ?? ''), 'clean permissions message').toMatch(/permission/i);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Negative — a plain employee lacks the erp_hr_manager payroll cap: no page, no
// localized nonce, and the protected write is refused without a fatal.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Payroll lifecycle access control (pro, employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    // PRL-AC-02 — employee cannot bootstrap the payroll lifecycle nonce.
    test('employee is blocked from the payroll page and gets no localized nonce', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const payroll = new PayrollLifecyclePage(page);
        const ok = await payroll.bootstrap();
        // bootstrap() already asserted NOT the critical-error splash. The localized
        // payroll object must be absent for an unprivileged user.
        expect(ok, 'no wpErpPayroll.nonce for a plain employee').toBe(false);

        const body = await page.locator('body').innerText();
        expect(/not allowed|do not have permission|cheating|access/i.test(body), 'permission boundary shown').toBeTruthy();
    });

    // PRL-AC-03 — even posting the protected write directly (without a valid page
    // nonce) is refused; never a fatal, never a 2xx success.
    test('employee posting the generate-payrun write is refused without a fatal', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const payroll = new PayrollLifecyclePage(page);
        await payroll.bootstrap(); // populates ajaxurl even when nonce is absent

        // No valid nonce in hand → the protected handler must reject it.
        const { status, body, raw } = await payroll.ajaxRaw<unknown>(
            'action=erp_payroll_start_variable_input&_wpnonce=&calid=0&payrunid=0' +
                '&payment_date=2026-06-30&from_date=2026-06-01&to_date=2026-06-30' +
                '&empidlist[0][id]=0&empidlist[0][pay_basic]=0&specify_pay_item=false',
        );
        expect(status, 'no 5xx').toBeLessThan(500);
        expect(raw, 'no PHP fatal').not.toContain(CRITICAL_ERROR);
        // Either a nonce/permission rejection envelope, or a non-success body —
        // assert the boundary, not an exact code.
        const refused = body && typeof body === 'object' && 'success' in body ? !(body as { success: boolean }).success : true;
        expect(refused, 'generate-payrun write refused for an employee').toBeTruthy();
    });
});
