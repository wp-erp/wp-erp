import { test, expect } from '@utils/test';
import { WorkflowPage, workflowAjaxActions } from '@pages/pro/workflowPage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { execute, query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Workflow (@pro).
 *
 * The module is driven entirely by `admin-ajax.php`, and its six handlers are
 * where the interesting behaviour is. Five of them verify a nonce. One —
 * `erp_wf_fetch_workflow`, the handler that returns a workflow's whole
 * definition including its email actions — verifies nothing at all.
 *
 * A workflow's actions carry recipients, subjects and message bodies, so this
 * file seeds one with recognisable content and asserts who can read it back.
 */
const WORKFLOW_NAME = 'Confidential offboarding alert';
const SECRET_RECIPIENT = 'board@northwind.internal';
const SECRET_SUBJECT = 'Executive salary bands';

test.describe('Workflow @pro', () => {
    let page: WorkflowPage;
    let workflowId = 0;

    test.beforeAll(async () => {
        await execute(`DELETE FROM ${prefix()}erp_workflow_actions`);
        await execute(`DELETE FROM ${prefix()}erp_workflow_conditions`);
        await execute(`DELETE FROM ${prefix()}erp_workflows`);

        await execute(
            `INSERT INTO ${prefix()}erp_workflows
                (name, type, object, events_group, event, conditions_group, status, delay_time, delay_period, run, created_at, updated_at, created_by)
             VALUES (?, 'hrm', 'employee', 'hrm', 'erp_hr_employee_new', 'and', 'active', 0, 'hours', 0, NOW(), NOW(), 1)`,
            [WORKFLOW_NAME]
        );

        const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_workflows ORDER BY id DESC LIMIT 1`);
        workflowId = Number(rows[0]!.id);

        // A send-email action whose contents are recognisable in a response body.
        const params = `a:3:{s:2:"to";s:${SECRET_RECIPIENT.length}:"${SECRET_RECIPIENT}";s:7:"subject";s:${SECRET_SUBJECT.length}:"${SECRET_SUBJECT}";s:7:"message";s:38:"CONFIDENTIAL - internal comp review Q3";}`;

        await execute(
            `INSERT INTO ${prefix()}erp_workflow_actions (name, params, extra, workflow_id) VALUES ('send_email', ?, '', ?)`,
            [params, workflowId]
        );
    });

    test.beforeEach(async ({ page: p }) => {
        page = new WorkflowPage(p);
        page.watchServerErrors();
    });

    test.afterAll(async () => {
        await execute(`DELETE FROM ${prefix()}erp_workflow_actions`);
        await execute(`DELETE FROM ${prefix()}erp_workflow_conditions`);
        await execute(`DELETE FROM ${prefix()}erp_workflows`);
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the workflow screen lists the workflows on file', { tag: ['@tier1', '@pro', '@workflow', '@smoke'] }, async () => {
        await page.goto();

        expect(await page.bodyText(), 'the seeded workflow is listed').toContain(WORKFLOW_NAME);
        expect(page.serverErrorList(), 'and the screen loads without a server error').toEqual([]);
    });

    test('the Add New screen renders the workflow builder', { tag: ['@tier1', '@pro', '@workflow'] }, async () => {
        await page.gotoNew();

        const body = await page.bodyText();

        expect(body, 'the builder asks for a name').toMatch(/Workflow Name|Name/i);
        expect(page.serverErrorList(), 'and renders without a server error').toEqual([]);
    });

    // ---- Tier 3 — who may reach the ajax handlers ------------------------

    test('the workflow screen is closed to an employee', { tag: ['@tier3', '@pro', '@workflow', '@authz'] }, async ({ browser }) => {
        // The canary: the SCREEN is properly gated on
        // `erp_workflow_menu_permission`, which is what makes the handler case
        // below a finding rather than an expected level of access.
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const asEmployee = new WorkflowPage(await context.newPage());

        await asEmployee.goto();

        expect(await asEmployee.isAccessDenied(), 'an employee is refused the workflow screen').toBe(true);

        await context.close();
    });

    test('the write handlers refuse a request with no nonce', { tag: ['@tier3', '@pro', '@workflow', '@authz'] }, async () => {
        // The page has to be ON the site before `ajaxWithoutNonce()` can post a
        // RELATIVE url — from `about:blank` the fetch throws "Failed to parse
        // URL", which looks nothing like a permission result.
        await page.goto();

        // The positive control for the guard: five of the six handlers do check.
        for (const { action, guarded } of workflowAjaxActions) {
            if (!guarded) continue;

            const response = await page.ajaxWithoutNonce(action, { workflow_name: 'x', conditions_group: 'and' });

            expect(response.body, `${action} refuses a request with no nonce`).toContain('Nonce verification failed');
        }
    });

    test('an anonymous visitor cannot reach the workflow handlers', { tag: ['@tier3', '@pro', '@workflow', '@authz'] }, async ({ browser }) => {
        // `storageState: undefined` is load bearing. A bare `browser.newContext()`
        // inside a file that declares `test.use({ storageState: ADMIN_STATE })`
        // comes up as the ADMIN, and this case then "passed" by reading a
        // workflow it was entitled to read — an anonymous test that was quietly
        // testing an administrator. The assertion below is only meaningful with
        // the override, and the login check is what proves it took effect.
        const context = await browser.newContext({ storageState: undefined });
        const p = await context.newPage();
        await p.goto('http://localhost:8888/', { waitUntil: 'domcontentloaded' });

        const loggedIn = await p.evaluate(async () => (await fetch('/wp-json/wp/v2/users/me')).status);
        expect(loggedIn, 'precondition: this context really is logged out').toBe(401);

        const anonymous = new WorkflowPage(p);
        const response = await anonymous.ajaxWithoutNonce('erp_wf_fetch_workflow', { id: String(workflowId) });

        expect(response.body, 'the handler is registered for logged-in users only').not.toContain(WORKFLOW_NAME);

        await context.close();
    });

    test.fail(
        'reading a workflow requires permission to see workflows',
        { tag: ['@tier3', '@pro', '@workflow', '@authz', '@known-defect'] },
        async ({ browser }) => {
            // KNOWN DEFECT — ERP-158. `AjaxHandler::fetch_workflow()` is the one
            // handler of six that calls neither `verify_nonce()` nor
            // `current_user_can()`. It reads `$_REQUEST['id']` and returns the
            // whole workflow — including its actions, which carry email
            // recipients, subjects and message bodies.
            //
            // The canary above proves the SCREEN is gated, so an employee
            // reaching this is a hole in the handler, not the intended model.
            const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
            const p = await context.newPage();
            await p.goto('http://localhost:8888/wp-admin/profile.php', { waitUntil: 'domcontentloaded' });

            const asEmployee = new WorkflowPage(p);
            const response = await asEmployee.ajaxWithoutNonce('erp_wf_fetch_workflow', { id: String(workflowId) });

            await context.close();

            expect(response.body, "an employee cannot read another team's workflow").not.toContain(SECRET_RECIPIENT);
        }
    );
});
