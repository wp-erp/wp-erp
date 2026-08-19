import { test, expect } from '@utils/test';
import { LeaveCalendarPage } from '@pages/hrm/leaveCalendarPage';
import { LeaveRequestsPage } from '@pages/hrm/leaveRequestsPage';
import { LeaveEntitlementsPage } from '@pages/hrm/leaveEntitlementsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { upcomingMonday, daysAfter } from '@utils/helpers';
import { withRole } from '@utils/roles';
import { leavePolicies, employees as seededEmployees, departments } from '@utils/seedData';
import { cleanupEntitlements, cleanupLeaveRequests } from '@utils/cleanup';
import { closeDb } from '@utils/dbUtils';

test.use({ storageState: ADMIN_STATE });

const employeeName = (index: number) => `${seededEmployees[index]!.firstName} ${seededEmployees[index]!.lastName}`;

test.describe('HR — Leave → Calendar', () => {
    let calendar: LeaveCalendarPage;
    let requests: LeaveRequestsPage;
    let entitlements: LeaveEntitlementsPage;

    test.beforeEach(async ({ page }) => {
        calendar = new LeaveCalendarPage(page);
        requests = new LeaveRequestsPage(page);
        entitlements = new LeaveEntitlementsPage(page);
    });

    // Scoped to this spec's own people — see the note in leaveRequests.spec.ts.
    const touched = [15, 16, 17, 20, 21].map(employeeName);

    test.afterAll(async () => {
        await cleanupLeaveRequests(touched);
        await cleanupEntitlements(touched);
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the calendar renders with its navigation and view controls', { tag: ['@tier1', '@hrm-leave', '@smoke'] }, async () => {
        await calendar.goto();

        expect(await calendar.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
        await calendar.expectHeading('Calendar');
        expect(await calendar.hasRendered(), 'fullCalendar mounts on #erp-hr-calendar').toBe(true);
        expect(await calendar.hasControls(), 'prev/next/today and the month/week/day views all render').toBe(true);
        expect(await calendar.currentMonth(), 'the title names a month and year').toMatch(/^[A-Z][a-z]+ \d{4}$/);
    });

    test('the filter offers every department', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        await calendar.goto();

        const options = await calendar.departmentOptions();

        for (const department of departments) {
            expect(options, `department "${department.title}"`).toContain(department.title);
        }
    });

    // ---- Tier 2: what the calendar actually draws -------------------------

    test('an approved leave appears on the calendar and a pending one does not', { tag: ['@tier2', '@hrm-leave', '@flow'] }, async () => {
        const policy = leavePolicies[0]!;
        const pending = employeeName(20);
        const approved = employeeName(21);
        const monday = upcomingMonday(13);

        for (const employee of [pending, approved]) {
            await entitlements.assignToEmployee({ policyLabel: policy.name, employeeLabel: employee });
            await requests.create({ employeeLabel: employee, from: monday, to: daysAfter(monday, 1) });
        }

        await requests.goto();
        await requests.approve(approved);

        await calendar.goto();
        await calendar.showMonthOf(monday);

        // The view is built from `status => 1` only, so this is the whole rule:
        // approval is what puts a leave on the calendar.
        expect(await calendar.hasEventFor(approved), 'the approved leave is drawn').toBe(true);
        expect(await calendar.hasEventFor(pending), 'a pending leave is not drawn').toBe(false);
    });

    test('every approved leave in a department survives that department own filter', { tag: ['@tier2', '@hrm-leave', '@filter'] }, async () => {
        // KNOWN DEFECT — ERP-139. `erp_hr_get_leave_requests()` builds the
        // department clause as `$wpdb->prepare(" AND request.user_id in (%s)",
        // implode(', ', $user_ids))` (functions-leave.php:1445). `%s` quotes the
        // whole list as ONE string, so the SQL reads `IN ('21, 22, 23')` and
        // MySQL casts that to the integer 21 — only the FIRST employee of the
        // department is ever matched. Asserted as it should behave; expected to
        // fail until fixed.
        test.fail();

        const policy = leavePolicies[0]!;
        const colleagues = [employeeName(15), employeeName(16)]; // both Customer Success
        const monday = upcomingMonday(15);

        for (const employee of colleagues) {
            await entitlements.assignToEmployee({ policyLabel: policy.name, employeeLabel: employee });
            await requests.create({ employeeLabel: employee, from: monday, to: daysAfter(monday, 1) });
            await requests.goto();
            await requests.approve(employee);
        }

        // Requested with `department` alone. The filter FORM would also send
        // `designation=-1`, which the view turns into designation 1 and which
        // masks this defect behind ERP-138 — see that test.
        await calendar.goto();
        await calendar.goto({ department: await calendar.departmentId('Customer Success') });
        await calendar.showMonthOf(monday);

        for (const employee of colleagues) {
            expect(await calendar.hasEventFor(employee), `${employee} is drawn under their own department`).toBe(true);
        }
    });

    test('a department filter excludes another department leave', { tag: ['@tier2', '@hrm-leave', '@filter'] }, async () => {
        // KNOWN DEFECT — ERP-138. Filtering by a department shows OTHER
        // departments' approved leave. Two faults compound:
        //   1. the "- Select Designation -" sentinel is `-1`, and the view runs
        //      it through `absint()` (calendar.php:3), which yields 1 — so a
        //      department-only filter silently becomes department + designation 1;
        //   2. when that pair matches no employee, `erp_hr_get_leave_requests()`
        //      adds NO where clause at all (`if ( $users->count() )`,
        //      functions-leave.php:1433) and returns EVERY approved leave.
        // A filter that matches nobody must show nothing, never everything.
        test.fail();

        const policy = leavePolicies[0]!;
        const outsider = employeeName(17); // Customer Success
        const monday = upcomingMonday(17);

        await entitlements.assignToEmployee({ policyLabel: policy.name, employeeLabel: outsider });
        await requests.create({ employeeLabel: outsider, from: monday, to: daysAfter(monday, 1) });
        await requests.goto();
        await requests.approve(outsider);

        await calendar.goto();
        const url = await calendar.filterByDepartment('Marketing');
        expect(url, 'the filter round-trips into the query string').toContain('department=');

        await calendar.showMonthOf(monday);

        expect(await calendar.hasEventFor(outsider), "a Customer Success leave is not shown under Marketing's filter").toBe(false);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('the calendar is closed to an employee', { tag: ['@tier3', '@hrm-leave', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new LeaveCalendarPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the Calendar screen').toBe(true);
    });
});
