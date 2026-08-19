import { test, expect } from '@utils/test';
import { PayrollPage, payrollSelectors, payrunColumns, bulkPayItemColumns, payCalendarTypes, type PayrollScreen } from '@pages/hrm/payrollPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupPayCalendars } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Payroll @pro', () => {
    let page: PayrollPage;

    test.beforeEach(async ({ page: p }) => {
        page = new PayrollPage(p);
    });

    test.afterAll(async () => {
        await cleanupPayCalendars();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    for (const screen of ['dashboard', 'calendar', 'payrun', 'bulkPayItemEdit', 'reports'] as PayrollScreen[]) {
        test(`the ${screen} screen loads`, { tag: ['@tier1', '@hrm-payroll', '@smoke'] }, async () => {
            await page.goto(screen);

            expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
            await page.expectHeading('HR');
        });
    }

    test.fail(
        'the payroll screens load without a server error',
        { tag: ['@tier1', '@pro', '@hrm-payroll', '@known-defect'] },
        async ({ page: p }) => {
            // KNOWN DEFECT — ERP-147 / erp-pro#964. `AjaxHandler::get_payrun()`
            // calls `cal_days_in_month()` unguarded, and the `calendar` PHP
            // extension is optional — absent from the official WordPress Docker
            // image — so the dashboard's chart endpoint 500s on every load and
            // the "Payroll History of Current Month" panel stays blank.
            //
            // This is the case the suite was MISSING: payroll was 13/13 green
            // while 29 of these fatals piled up in debug.log, because
            // `hasNoPhpFatal()` reads rendered body text and cannot see a fatal
            // inside an AJAX response.
            const watched = new PayrollPage(p);
            watched.watchServerErrors();

            await watched.goto('dashboard');
            await p.waitForTimeout(4000);

            expect(await watched.hasNoPhpFatal(), 'precondition: the page itself renders').toBe(true);
            expect(watched.serverErrorList(), 'no request during the load returns 5xx').toEqual([]);
        }
    );

    test('the pay run list renders its captured columns', { tag: ['@tier1', '@hrm-payroll'] }, async () => {
        await page.goto('payrun');

        const headers = await page.columnHeaders(payrollSelectors.payrunTable);

        for (const column of payrunColumns) {
            expect(headers, `column "${column}"`).toContain(column);
        }
    });

    test('the bulk pay item edit list renders its captured columns', { tag: ['@tier1', '@hrm-payroll'] }, async () => {
        await page.goto('bulkPayItemEdit');

        const headers = await page.columnHeaders(payrollSelectors.bulkEditTable);

        for (const column of bulkPayItemColumns) {
            expect(headers, `column "${column}"`).toContain(column);
        }
    });

    test('a pay calendar can be created and is stored', { tag: ['@tier1', '@hrm-payroll', '@crud'] }, async () => {
        const name = uniqueId('pwerpCal');

        await page.createCalendar({ name, type: 'Monthly' });

        // DB oracle: the screen is a Vue app whose list can render from cache,
        // so the row itself is the evidence the calendar exists.
        const rows = await query<RowDataPacket[]>(`SELECT pay_calendar_name, pay_calendar_type FROM ${prefix()}erp_hr_payroll_pay_calendar WHERE pay_calendar_name = ?`, [name]);

        expect(rows, 'exactly one calendar row is written').toHaveLength(1);
        expect(rows[0]!.pay_calendar_type, 'the chosen type is stored').toBe('monthly');
        expect(await page.hasCalendar(name), 'the new calendar is listed').toBe(true);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('the calendar type list excludes daily and contract', { tag: ['@tier2', '@hrm-payroll', '@edge'] }, async () => {
        // A pay calendar may not be daily or contract: those two are removed
        // from the employee pay types in
        // `erp_payroll_get_pay_calendar_types_dropdown_raw()`.
        await page.gotoNewCalendar();

        const options = await page.calendarTypeOptions();

        expect(options, 'exactly the four supported types').toEqual([...payCalendarTypes]);
        expect(options, 'daily is not a pay calendar type').not.toContain('Daily');
        expect(options, 'contract is not a pay calendar type').not.toContain('Contract');
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('Payroll is closed to an employee', { tag: ['@tier3', '@hrm-payroll', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new PayrollPage(p);
            await asEmployee.goto('payrun');
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the Pay Run screen').toBe(true);
    });

    test('the salary roster is not readable by an employee', { tag: ['@tier3', '@hrm-payroll', '@authz', '@security'] }, async ({ browser }) => {
        // KNOWN DEFECT — ERP-004 / erp-pro#? (filed 2026-07-02 against 1.6.0,
        // still open on 1.7.0, re-verified this run). `AjaxHandler.php` registers
        // 67 `wp_ajax_erp_payroll_*` actions and contains exactly ONE
        // `current_user_can` call in the whole file, so `erp_payroll_get_employee_list`
        // answers any logged-in user with every employee's name, e-mail and
        // pay_rate. Asserted as it should behave; expected to fail until fixed.
        test.fail();

        const response = await withRole(browser, 'employee', async (p) => {
            return new PayrollPage(p).postAjax('erp_payroll_get_employee_list');
        });

        expect(response.body, 'an employee is refused the salary roster').not.toMatch(/"success"\s*:\s*true/);
        expect(response.body, 'no pay rate is disclosed').not.toMatch(/pay_rate/);
    });

    test('a second calendar of the same type is refused', { tag: ['@tier2', '@hrm-payroll', '@validation'] }, async () => {
        // Only ONE calendar may exist per type: `create_pay_calendar` counts rows
        // of that type first and refuses (`AjaxHandler.php:458`).
        //
        // Driven through the endpoint rather than the form, for two reasons: the
        // rule belongs to the endpoint, and the FORM cannot build a biweekly
        // calendar at all — its employee picker only offers staff whose own pay
        // type matches the calendar, and every seeded employee is monthly.
        const first = await page.postAjax('erp_payroll_create_pay_calendar', { cal_name: uniqueId('pwerpDup'), cal_type: 'biweekly' });
        expect(first.body, 'the first calendar of a type is accepted').toMatch(/"success"\s*:\s*true/);

        const second = await page.postAjax('erp_payroll_create_pay_calendar', { cal_name: uniqueId('pwerpDup'), cal_type: 'biweekly' });
        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_hr_payroll_pay_calendar WHERE pay_calendar_type = ?`, ['biweekly']);

        expect(second.body, 'the product names the collision').toMatch(/calendar type already exist/i);
        expect(rows, 'the type still has exactly one calendar').toHaveLength(1);
    });

    test('an employee cannot create a pay calendar', { tag: ['@tier3', '@hrm-payroll', '@authz', '@security'] }, async ({ browser }) => {
        // KNOWN DEFECT — ERP-004, the mutation half. `create_pay_calendar` has
        // neither a capability check nor a nonce, so any logged-in user can write
        // a payroll pay calendar. Re-verified on 1.7.0: the row is created.
        test.fail();

        const name = uniqueId('pwerpAuthz');

        // 'hourly' is used by no other case here. The type matters: only one
        // calendar may exist per type, so re-using a type another test created
        // would get this refused by the DUPLICATE guard and read as "fixed" —
        // a pass for entirely the wrong reason.
        const response = await withRole(browser, 'employee', async (p) => {
            return new PayrollPage(p).postAjax('erp_payroll_create_pay_calendar', { cal_name: name, cal_type: 'hourly' });
        });

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_hr_payroll_pay_calendar WHERE pay_calendar_name = ?`, [name]);

        expect(response.body, 'the refusal is about permission, not a duplicate type').not.toMatch(/calendar type already exist/i);
        expect(response.body, 'the request is refused').not.toMatch(/"success"\s*:\s*true/);
        expect(rows, 'nothing is written to the pay calendar table').toHaveLength(0);
    });
});
