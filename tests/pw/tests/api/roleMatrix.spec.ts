import { test, expect, request as playwrightRequest, type APIRequestContext } from '@playwright/test';
import { basicAuth, env, restPath } from '@utils/helpers';
import { erpRouteCalls, type RouteCall } from '@utils/routeTable';
import { type Observation, classify, summarise } from '@utils/permissionOracle';

/**
 * REST — the per-role authorization matrix.
 *
 * The anonymous sweep proves nobody logged-out gets in. This proves the walls
 * BETWEEN the logged-in roles: an HR manager cannot read the accounts, a CRM
 * agent cannot touch payroll, a recruiter cannot leave its hiring corner. That
 * is where privilege escalation lives — a capability check that is present but
 * checks the wrong thing.
 *
 * Two assertions per role, the shape the user asked for:
 *
 *   - **positive** — one route INSIDE the role's own area, proven by hand to
 *     need that role, answers 200. A single canary, not a blanket own-module
 *     sweep: redaction (an employee reads the directory with salary nulled),
 *     per-record visibility and the recruiter's subset-of-HR all make
 *     "everything in my module returns 200" false, and that breadth is the
 *     per-module CRUD layer's job.
 *   - **deny** — every FOREIGN route the role should not reach refuses it
 *     (401/403). Same `classify()` oracle as the anonymous sweep.
 *
 * **The deny sweep sends GET only, and that is a deliberate safety choice, not
 * laziness.** A cross-module READ is where disclosure lives (can a CRM agent
 * read salaries, can an accountant read another team's contacts), and a GET
 * cannot create anything. An earlier all-methods version POSTed empty bodies to
 * the reimbursement self-service and left four junk `people_id = 0` rows behind
 * before this was caught. Where a role IS refused, it is refused on every method
 * — the permission callbacks check a module capability, not a verb — so GET
 * refusal is strong evidence for the whole route. Cross-module WRITE denial is
 * called out as not-covered in COVERAGE rather than pretended.
 *
 * **Own-area boundaries, read off the running site AND its source, not assumed:**
 *
 *   hrManager       owns HRM            · foreign: CRM, Accounting, Pro
 *   crmManager      owns CRM            · foreign: HRM, Accounting, Pro
 *   crmAgent        owns CRM            · foreign: HRM, Accounting, Pro
 *   accountManager  owns Accounting     · foreign: HRM, CRM, Pro
 *   recruiter       owns HRM/recruitment· foreign: the rest of HRM, CRM, Accounting, Pro
 *   employee        reads HRM broadly   · foreign: CRM, Accounting, Pro
 *
 * The employee reads the whole HR module (directory, departments, designations,
 * leave) at 200 with salary redacted; whether that breadth is right is a
 * per-record privacy question recorded in COVERAGE, not a cross-module wall, so
 * HRM is the employee's own area here.
 */

/**
 * Foreign routes that ARE reachable below the owning role, by capability design
 * — verified against the permission callbacks in source, not carved to force a
 * green. Excluded from the deny sweep because refusing them would be the wrong
 * assertion.
 *
 *   - the `accounting/v1/employee*` family (`EmployeesController`,
 *     `EmployeeRequestsController`) gates on `erp_view_list`, which HR manager
 *     and every `employee` hold (`functions-capabilities.php:70,149,222`). This
 *     is the accounting module's HR-facing surface: the employee directory (with
 *     `pay_rate` redacted for non-privileged callers, checked), an employee's own
 *     reimbursement requests, their own transactions. Reimbursement self-service
 *     is the whole point of the feature.
 *   - `utility/get-active-plugins` gates on the same `erp_view_list`.
 *   - `hrm/announcements/my` is self-scoped — it returns the caller's own
 *     announcements (`[]` for someone with none), never anyone else's.
 *
 * Cross-employee data exposure WITHIN these routes (can employee A read
 * employee B's reimbursement) is a per-record question owned by ERP-157 /
 * erp-pro#974, not re-tested here.
 */
const AUTHENTICATED_SHARED = [
    '/erp/v1/utility/get-active-plugins',
    '/erp/v1/accounting/v1/employees',
    '/erp/v1/accounting/v1/employee-requests',
    '/erp/v1/accounting/v1/employee/chart',
    '/erp/v1/hrm/announcements/my',
];

/**
 * `GET /erp/v1/hrm/attendance/shifts/{id}` is guarded only by
 * `fn() => is_user_logged_in()` (`AttendanceController.php:56-64`), while every
 * write method on the same route requires `erp_hr_manager`. So any logged-in
 * user — an account manager, a recruiter, a plain employee — can read a shift's
 * configuration. Low sensitivity (work schedules), so it is RECORDED here and in
 * COVERAGE rather than filed, and held out of the deny sweep as a named known
 * weakness rather than a silent carve-out — the distinction being that this one
 * IS a gap, just a small one.
 */
const KNOWN_WEAK_GUARD = '/erp/v1/hrm/attendance/shifts/';

function isReachableByDesign(call: RouteCall): boolean {
    return (
        AUTHENTICATED_SHARED.some((route) => call.pattern.includes(route)) ||
        call.pattern.includes(KNOWN_WEAK_GUARD)
    );
}

interface RoleUnderTest {
    key: string;
    envUser: string;
    /** True when the route is inside the role's own area — not swept for denial. */
    owns: (call: RouteCall) => boolean;
    /** One route the role MUST reach, proven 200 by hand. */
    canary: string;
}

const PASSWORD = env('USER_PASSWORD');

const ROLES: RoleUnderTest[] = [
    { key: 'hrManager', envUser: 'HR_MANAGER', owns: (c) => c.area === 'hrm', canary: '/erp/v1/hrm/employees' },
    { key: 'crmManager', envUser: 'CRM_MANAGER', owns: (c) => c.area === 'crm', canary: '/erp/v1/crm/contacts' },
    { key: 'crmAgent', envUser: 'CRM_AGENT', owns: (c) => c.area === 'crm', canary: '/erp/v1/crm/contacts' },
    { key: 'accountManager', envUser: 'ACCOUNT_MANAGER', owns: (c) => c.area === 'accounting', canary: '/erp/v1/accounting/v1/invoices' },
    {
        key: 'recruiter',
        envUser: 'RECRUITER',
        // Owns only the recruitment corner of HRM; the rest of HRM is foreign
        // (hrm/employees answers 403 to a recruiter).
        owns: (c) => c.pattern.includes('/recruitment/'),
        // NOT recruitment/jobs — that one is public to everyone. candidates is
        // recruiter-gated.
        canary: '/erp/v1/hrm/recruitment/candidates',
    },
    { key: 'employee', envUser: 'EMPLOYEE', owns: (c) => c.area === 'hrm', canary: '/erp/v1/hrm/employees' },
];

test.describe('REST — per-role authorization matrix', () => {
    for (const role of ROLES) {
        const auth = basicAuth(env(role.envUser), PASSWORD);

        test.describe(role.key, () => {
            let context: APIRequestContext;

            test.beforeAll(async () => {
                context = await playwrightRequest.newContext({ baseURL: env('BASE_URL'), extraHTTPHeaders: auth });
            });

            test.afterAll(async () => {
                await context.dispose();
            });

            test('can reach its own area', { tag: ['@tier2', '@api', '@authz', '@permissions', '@rbac'] }, async () => {
                const response = await context.get(restPath(role.canary));

                expect(
                    response.status(),
                    `the canary ${role.canary} answers 200, got ${response.status()}: ${(await response.text()).slice(0, 120)}`
                ).toBe(200);
            });

            test('cannot read another team’s module', { tag: ['@tier2', '@api', '@authz', '@permissions', '@rbac'] }, async () => {
                const foreign = erpRouteCalls().filter(
                    (call) => call.method === 'GET' && !role.owns(call) && !isReachableByDesign(call)
                );

                expect(foreign.length, 'the sweep actually has foreign GET routes to check').toBeGreaterThan(0);

                const answered: Observation[] = [];
                const unproven: Observation[] = [];

                for (const call of foreign) {
                    const route = `GET ${call.pattern}`;
                    const response = await context.get(restPath(call.path));
                    const status = response.status();
                    const body = (await response.text()).replace(/\s+/g, ' ').slice(0, 160);

                    switch (classify(route, status, body, call.pattern)) {
                        case 'refused':
                        case 'carve-out':
                            break;
                        case 'param-unproven':
                        case 'broken':
                            unproven.push({ route, status, body });
                            break;
                        case 'answered':
                            answered.push({ route, status, body });
                            break;
                    }
                }

                if (unproven.length) {
                    console.log(`[${role.key}] ${summarise('unreachable past validation / broken handler', unproven, foreign.length)}`);
                }

                expect(
                    answered,
                    summarise(`answered ${role.key} on a foreign route instead of refusing`, answered, foreign.length)
                ).toEqual([]);
            });
        });
    }
});
