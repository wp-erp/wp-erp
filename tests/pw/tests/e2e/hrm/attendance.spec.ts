import { test, expect } from '@utils/test';
import { AttendancePage, attendanceColumns, shiftColumns, assignShiftColumns, type AttendanceScreen } from '@pages/hrm/attendancePage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { ApiUtils } from '@utils/apiUtils';
import { basicAuth, env, uniqueId } from '@utils/helpers';
import { employees as seededEmployees } from '@utils/seedData';
import { cleanupShifts } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/** One shift per time range: the product treats a repeated range as a duplicate. */
const times = {
    day: { startTime: '09:00 am', endTime: '05:00 pm' },
    night: { startTime: '10:00 pm', endTime: '06:00 am' },
    duplicate: { startTime: '08:00 am', endTime: '04:00 pm' },
    full: { startTime: '09:15 am', endTime: '09:15 am' },
} as const;

test.describe('HR — Attendance @pro', () => {
    let page: AttendancePage;

    // Shifts are keyed by name AND by time range, so a leftover from an earlier
    // run makes a later create look like a duplicate. Start from a known state.
    test.beforeAll(async () => {
        await cleanupShifts();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new AttendancePage(p);
    });

    test.afterAll(async () => {
        await cleanupShifts();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    for (const [screen, columns] of [
        ['attendance', attendanceColumns],
        ['shifts', shiftColumns],
        ['assignShiftBulk', assignShiftColumns],
    ] as [AttendanceScreen, readonly string[]][]) {
        test(`the ${screen} screen renders its captured columns`, { tag: ['@tier1', '@hrm-attendance', '@smoke'] }, async () => {
            await page.goto(screen);

            expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);

            const headers = await page.columnHeaders();

            for (const column of columns) {
                expect(headers, `column "${column}"`).toContain(column);
            }
        });
    }

    test('the bulk assign screen lists the seeded employees', { tag: ['@tier1', '@hrm-attendance'] }, async () => {
        await page.goto('assignShiftBulk');

        const employee = `${seededEmployees[2]!.firstName} ${seededEmployees[2]!.lastName}`;

        expect(await page.hasRowFor(employee), `${employee} is offered for a shift`).toBe(true);
    });

    test('a shift can be created and its duration is computed', { tag: ['@tier1', '@hrm-attendance', '@crud'] }, async () => {
        const name = uniqueId('pwerpShift');

        await page.createShift({ name, ...times.day });

        // 09:00 to 17:00 is 8 hours; the column stores SECONDS.
        const rows = await query<RowDataPacket[]>(`SELECT start_time, end_time, duration FROM ${prefix()}erp_attendance_shifts WHERE name = ?`, [name]);

        expect(rows, 'exactly one shift row is written').toHaveLength(1);
        expect(rows[0]!.start_time, 'the start time is stored as given').toBe('09:00:00');
        expect(rows[0]!.end_time, 'the end time is stored as given').toBe('17:00:00');
        expect(Number(rows[0]!.duration), 'eight hours in seconds').toBe(8 * 3600);
        expect(await page.hasRowFor(name), 'the shift is listed').toBe(true);
        expect(await page.rowTexts(), 'the list reports the duration in hours').toContainEqual(expect.stringContaining('08 hour(s)'));
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('a shift across midnight counts only its own hours', { tag: ['@tier2', '@hrm-attendance', '@edge'] }, async () => {
        // 22:00 to 06:00 wraps midnight: `erp_attendance_insert_shift` adds a day
        // to the end when it is not after the start, so this is 8 hours and not
        // a negative span.
        const name = uniqueId('pwerpNight');

        await page.createShift({ name, ...times.night });

        const rows = await query<RowDataPacket[]>(`SELECT duration FROM ${prefix()}erp_attendance_shifts WHERE name = ?`, [name]);

        expect(rows, 'the overnight shift is created').toHaveLength(1);
        expect(Number(rows[0]!.duration), 'ten at night to six in the morning is eight hours').toBe(8 * 3600);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('a duplicate shift is refused', { tag: ['@tier3', '@hrm-attendance', '@validation'] }, async () => {
        // A shift is a duplicate when the NAME matches or the start/end pair
        // matches an active shift (`erp_atts_is_duplicate_shift`).
        const name = uniqueId('pwerpDupShift');

        await page.createShift({ name, ...times.duplicate });
        await page.createShift({ name, ...times.duplicate });

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_attendance_shifts WHERE name = ?`, [name]);

        expect(rows, 'the second attempt writes nothing').toHaveLength(1);
        expect(await page.isShiftFormOpen(), 'the form stays open on refusal').toBe(true);

        await page.closeShiftForm();
    });

    test('a shift of a full day or longer is refused', { tag: ['@tier3', '@hrm-attendance', '@validation'] }, async () => {
        // Equal start and end means the end is pushed a day forward, which makes
        // the shift exactly 24h — the product requires less than 24h.
        const name = uniqueId('pwerpFullDay');

        await page.createShift({ name, ...times.full });

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_attendance_shifts WHERE name = ?`, [name]);

        expect(rows, 'a 24-hour shift is not created').toHaveLength(0);
        expect(await page.isShiftFormOpen(), 'the form stays open on refusal').toBe(true);

        await page.closeShiftForm();
    });

    test('Attendance is closed to an employee', { tag: ['@tier3', '@hrm-attendance', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new AttendancePage(p);
            await asEmployee.goto('shifts');
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the Shifts screen').toBe(true);
    });

    test('the shifts REST route refuses an employee', { tag: ['@tier3', '@hrm-attendance', '@authz', '@api'] }, async ({ request }) => {
        // Attendance guards every route with a capability, unlike payroll — this
        // is the case that proves it rather than assuming it.
        const api = new ApiUtils(request);
        const asEmployee = basicAuth(env('EMPLOYEE', 'pwerp.employee'), env('USER_PASSWORD', ''));

        const response = await api.get('/hrm/attendance/shifts', asEmployee);

        expect(response.status(), 'an employee is refused the shift list').toBeGreaterThanOrEqual(400);
    });
});
