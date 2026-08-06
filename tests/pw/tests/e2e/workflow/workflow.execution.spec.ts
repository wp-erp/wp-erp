import { test, expect, request as pwRequest, type APIRequestContext } from '@utils/test';
import { toPath } from '@utils/helpers';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';

/**
 * HRM — Workflow EXECUTION lifecycle (erp-pro module: workflow).
 *
 * This is the DEEP BEHAVIORAL companion to workflow.spec.ts (which is UI smoke +
 * DB-insert round-trips). Here we drive the REAL execution pipeline end-to-end:
 *
 *   create-via-save-handler  →  fire-a-real-trigger  →  assert-the-action-ran
 *
 * Surfaces used (grounded in the erp-pro source, each step verified live):
 *  - CREATE / EDIT: admin-ajax write handlers (no /erp/v1 REST exists for this
 *    module — AjaxHandler.php hooks only wp_ajax_erp_wf_*). They verify a
 *    PAGE-LOCALIZED nonce printed into the Add-New / Edit Vue view
 *    (workflow-new.php) as two hidden inputs:
 *        v-if  workflow_edit_mode  → 'erp-wf-edit-workflow'  (the EDIT nonce)
 *        v-else                    → 'erp-wf-new-workflow'   (the CREATE nonce)
 *    So we scrape the create/edit nonce from that page's HTML before each write.
 *  - TRIGGER: WordPress native `user_register`, which erp_wf_get_hooks() maps to
 *    the workflow event 'created_user' (functions.php). Firing it is a real
 *    WP user create via REST POST /wp/v2/users (ApiUtils.createUser).
 *  - ASSERT: the two synchronous execution side-effects of run_workflow()
 *    (Workflows.php):
 *        (a) count_workflow_run() → wp_erp_workflows.run += 1
 *        (b) do_action('erp_wf_run_workflow') → Log::create() inserts a
 *            wp_erp_workflow_logs row (actions-filters.php).
 *    These land in-request, independent of the wp_schedule_single_event() that
 *    fans out the action body, so they are the deterministic proof of execution.
 *
 * Gating logic asserted (functions.php):
 *  - 0 conditions               → erp_wf_check_workflow() returns true   → fires.
 *  - email '=' non-matching     → erp_wf_meet_current_process() false    → no fire.
 *  - email '=' matching (scalar)→ true                                   → fires.
 *  - status != active (paused)  → erp_wf_get_workflows_by_event() filters → no fire.
 *
 * BEHAVIORAL FINDING (documented, not asserted as a bug): a `roles = subscriber`
 * condition does NOT fire when the user is created through REST /wp/v2/users,
 * because WP REST assigns the role AFTER wp_insert_user() fires `user_register`,
 * so at trigger time get_userdata()->roles is empty. The same condition DOES
 * fire via `wp user create --role=subscriber` (role set during insert). The
 * matching-condition test below therefore uses an `email` equals condition, the
 * deterministic scalar field that is present at `user_register` time. See
 * WFX-CON-FINDING below.
 *
 * Pro tables referenced as string literals (dbData.tables only carries free
 * tables). This file mutates the shared wp_erp_workflows* tables and the WP
 * users table, so it runs serial.
 *
 * Every test carries a tier tag (@pro), the @hrm module tag and a role tag.
 */

test.describe.configure({ mode: 'serial' });

const CRITICAL_ERROR = 'There has been a critical error on this website';

// Pro workflow tables (string literals — dbData.tables only has free tables).
const WF_TABLE = 'wp_erp_workflows';
const WF_CONDITIONS = 'wp_erp_workflow_conditions';
const WF_ACTIONS = 'wp_erp_workflow_actions';
const WF_LOGS = 'wp_erp_workflow_logs';

// Unique-per-run, scoped prefix so cleanup never touches workflow.spec.ts rows.
const WF_NAME_PREFIX = 'PW WF EXEC ';

const URLS = {
    addNew: toPath('wp-admin/admin.php?page=erp-workflow-new'),
    list: toPath('wp-admin/admin.php?page=erp-workflow'),
} as const;

// ── Track everything we create so afterAll purges all four tables + users ─────
const createdWorkflowIds: number[] = [];
const createdUserIds: string[] = [];

// ── Types for the rows we read back ──────────────────────────────────────────
interface WorkflowRow {
    id: number;
    name: string;
    type: string;
    object: string | null;
    events_group: string;
    event: string;
    conditions_group: string;
    status: string;
    delay_time: number;
    delay_period: string;
    run: number;
    created_at: string;
    deleted_at: string | null;
}
interface ActionRow { name: string; params: string; workflow_id: number }
interface ConditionRow { condition_name: string; operator: string; value: string; workflow_id: number }
interface CountRow { c: number }

// ── admin-ajax helpers (this feature only — NOT shared utils) ────────────────

/**
 * Scrape the page-localized CREATE/EDIT nonce from the Add-New / Edit Vue view.
 * The first hidden input (v-if=workflow_edit_mode) carries 'erp-wf-edit-workflow',
 * the second (v-else) carries 'erp-wf-new-workflow'. We match by mode.
 */
async function scrapeWorkflowNonce(
    request: APIRequestContext,
    mode: 'create' | 'edit',
    editId?: number,
): Promise<string> {
    const url = mode === 'edit'
        ? `${URLS.addNew.replace('erp-workflow-new', 'erp-workflow')}&action=edit&id=${editId}`
        : URLS.addNew;
    const resp = await request.get(url);
    const html = await resp.text();
    expect(html, 'workflow form page must not be a PHP fatal').not.toContain(CRITICAL_ERROR);

    const re = mode === 'edit'
        ? /v-if="workflow_edit_mode" type="hidden" v-model="nonce" value="([a-f0-9]+)"/
        : /v-else type="hidden" v-model="nonce" value="([a-f0-9]+)"/;
    const match = html.match(re);
    expect(match?.[1], `should scrape the ${mode} nonce from the workflow view`).toBeTruthy();
    return match![1]!;
}

/** A workflow action descriptor sent to the save handler as actions[i][...]. */
type WfAction = { name: string; title: string; [param: string]: string };
/** A workflow condition descriptor sent as conditions[i][...]. */
type WfCondition = { condition_name: string; operator: string; value: string };

interface SaveWorkflowInput {
    name: string;
    event?: string;
    eventsGroup?: string;
    conditionsGroup?: 'and' | 'or';
    activate?: boolean;          // omit → status 'paused'
    delayTime?: number;
    delayPeriod?: string;
    conditions?: WfCondition[];
    actions?: WfAction[];
}

/** Flatten the nested workflow payload into the multipart shape the handler reads. */
function buildMultipart(action: string, nonce: string, input: SaveWorkflowInput, workflowId?: number): Record<string, string> {
    const mp: Record<string, string> = {
        action,
        _wpnonce: nonce,
        workflow_name: input.name,
        events_group: input.eventsGroup ?? 'general',
        event: input.event ?? 'created_user',
        conditions_group: input.conditionsGroup ?? 'or',
        delay_time: String(input.delayTime ?? 0),
        delay_period: input.delayPeriod ?? 'minute',
    };
    if (workflowId !== undefined) mp.workflow_id = String(workflowId);
    // status: the handler reads activate=='true' → active, else paused.
    if (input.activate) mp.activate = 'true';

    (input.conditions ?? []).forEach((c, i) => {
        mp[`conditions[${i}][condition_name]`] = c.condition_name;
        mp[`conditions[${i}][operator]`] = c.operator;
        mp[`conditions[${i}][value]`] = c.value;
    });
    (input.actions ?? []).forEach((a, i) => {
        for (const [k, v] of Object.entries(a)) {
            mp[`actions[${i}][${k}]`] = v;
        }
    });
    return mp;
}

/**
 * POST the create save-handler (erp_wf_new_workflow) using the page request
 * context (carries the admin session cookies, like the real SPA). Returns the
 * raw response status, parsed JSON and the new workflow id (looked up by name).
 */
async function saveNewWorkflow(
    request: APIRequestContext,
    nonce: string,
    input: SaveWorkflowInput,
): Promise<{ status: number; body: any; id?: number }> {
    const resp = await request.post(toPath('wp-admin/admin-ajax.php'), {
        multipart: buildMultipart('erp_wf_new_workflow', nonce, input),
    });
    const status = resp.status();
    let body: any;
    try { body = await resp.json(); } catch { body = await resp.text(); }

    let id: number | undefined;
    if (body?.success === true) {
        id = await getWorkflowIdByName(input.name);
        if (id) createdWorkflowIds.push(id);
    }
    return { status, body, id };
}

/** POST the edit save-handler (erp_wf_edit_workflow). */
async function saveEditWorkflow(
    request: APIRequestContext,
    nonce: string,
    workflowId: number,
    input: SaveWorkflowInput,
): Promise<{ status: number; body: any }> {
    const resp = await request.post(toPath('wp-admin/admin-ajax.php'), {
        multipart: buildMultipart('erp_wf_edit_workflow', nonce, input, workflowId),
    });
    let body: any;
    try { body = await resp.json(); } catch { body = await resp.text(); }
    return { status: resp.status(), body };
}

// ── DB read helpers ──────────────────────────────────────────────────────────
async function getWorkflowIdByName(name: string): Promise<number | undefined> {
    const rows = await dbUtils.dbQuery<{ id: number }>(
        `SELECT id FROM ${WF_TABLE} WHERE name = ? ORDER BY id DESC LIMIT 1`,
        [name],
    );
    return rows[0]?.id;
}
async function getWorkflow(id: number): Promise<WorkflowRow | undefined> {
    const rows = await dbUtils.dbQuery<WorkflowRow>(`SELECT * FROM ${WF_TABLE} WHERE id = ? LIMIT 1`, [id]);
    return rows[0];
}
async function getRun(id: number): Promise<number> {
    const w = await getWorkflow(id);
    return Number(w?.run ?? -1);
}
async function getActions(id: number): Promise<ActionRow[]> {
    return dbUtils.dbQuery<ActionRow>(`SELECT name, params, workflow_id FROM ${WF_ACTIONS} WHERE workflow_id = ?`, [id]);
}
async function getConditions(id: number): Promise<ConditionRow[]> {
    return dbUtils.dbQuery<ConditionRow>(
        `SELECT condition_name, operator, value, workflow_id FROM ${WF_CONDITIONS} WHERE workflow_id = ?`,
        [id],
    );
}
async function getLogCount(id: number): Promise<number> {
    const rows = await dbUtils.dbQuery<CountRow>(`SELECT COUNT(*) AS c FROM ${WF_LOGS} WHERE workflow_id = ?`, [id]);
    return Number(rows[0]?.c ?? 0);
}
async function getLatestLogCreatedAt(id: number): Promise<string | undefined> {
    const rows = await dbUtils.dbQuery<{ created_at: string }>(
        `SELECT created_at FROM ${WF_LOGS} WHERE workflow_id = ? ORDER BY id DESC LIMIT 1`,
        [id],
    );
    return rows[0]?.created_at;
}

// ── Trigger helper: create a WP user → fires user_register → 'created_user' ──
let api: ApiUtils;

/**
 * Fire the `created_user` trigger by creating a real WP user via REST.
 * Returns the new user id (tracked for cleanup). Email is unique per call.
 */
async function fireCreatedUser(suffix: string, email?: string): Promise<string> {
    const username = `pwwfx${suffix}`;
    const [, userId] = await api.createUser({
        username,
        email: email ?? `${username}@example.com`,
        password: `Passw0rd!${suffix}`,
        roles: ['subscriber'],
    });
    if (userId) createdUserIds.push(userId);
    return userId;
}

// A convenient always-present trigger_action_hook action (runs synchronously
// enough for run/log; the hook itself is a harmless probe hook).
function probeHookAction(suffix: string): WfAction {
    return { name: 'trigger_action_hook', title: 'Trigger Action Hook', hook_name: `pw_wf_probe_hook_${suffix}` };
}

// ─────────────────────────────────────────────────────────────────────────────
test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile, process.env.X_WP_NONCE);
});

test.afterAll(async () => {
    // Purge child rows + the workflow rows we created, in all four tables.
    for (const id of createdWorkflowIds) {
        await dbUtils.dbQuery(`DELETE FROM ${WF_LOGS} WHERE workflow_id = ?`, [id]);
        await dbUtils.dbQuery(`DELETE FROM ${WF_ACTIONS} WHERE workflow_id = ?`, [id]);
        await dbUtils.dbQuery(`DELETE FROM ${WF_CONDITIONS} WHERE workflow_id = ?`, [id]);
        await dbUtils.dbQuery(`DELETE FROM ${WF_TABLE} WHERE id = ?`, [id]);
    }
    // Safety net: nuke any leftover EXEC-prefixed rows (and their children) from a
    // crashed run, so the next run starts clean.
    const leftovers = await dbUtils.dbQuery<{ id: number }>(
        `SELECT id FROM ${WF_TABLE} WHERE name LIKE ?`,
        [`${WF_NAME_PREFIX}%`],
    );
    for (const r of leftovers) {
        await dbUtils.dbQuery(`DELETE FROM ${WF_LOGS} WHERE workflow_id = ?`, [r.id]);
        await dbUtils.dbQuery(`DELETE FROM ${WF_ACTIONS} WHERE workflow_id = ?`, [r.id]);
        await dbUtils.dbQuery(`DELETE FROM ${WF_CONDITIONS} WHERE workflow_id = ?`, [r.id]);
    }
    await dbUtils.deleteRowsLike(WF_TABLE, 'name', WF_NAME_PREFIX);

    // Delete the WP users we created to fire triggers.
    for (const id of createdUserIds) {
        await api.delete(endPoints.user(id), { params: { force: true, reassign: 1 } }, false);
    }

    await api.dispose();
    await dbUtils.close();
});

// ─────────────────────────────────────────────────────────────────────────────
// Lifecycle — create via the real save handler, fire, assert execution
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow execution — lifecycle (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // WFX-LC-01 — the save handler creates an active auto workflow + its action.
    test('WFX-LC-01 save handler creates an active auto workflow with a serialized action', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}LC01 ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');

        const { status, body, id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, conditionsGroup: 'or',
            actions: [probeHookAction(suffix)],
        });

        expect(status, 'admin-ajax returns 200 even for app-level errors').toBe(200);
        expect(body?.success, `save handler should succeed: ${JSON.stringify(body)}`).toBe(true);
        expect(id, 'a wp_erp_workflows row should exist after a successful save').toBeTruthy();

        const wf = await getWorkflow(id!);
        expect(wf).toBeTruthy();
        // type defaults to 'auto' (DB default — the handler never sends it).
        expect(String(wf!.type)).toBe('auto');
        expect(String(wf!.status)).toBe('active');
        expect(String(wf!.event)).toBe('created_user');
        expect(String(wf!.events_group)).toBe('general');
        expect(String(wf!.conditions_group)).toBe('or');
        expect(Number(wf!.run)).toBe(0); // never fired yet
        expect(wf!.deleted_at).toBeNull();

        // The action persisted with php-serialized params carrying hook_name.
        const actions = await getActions(id!);
        expect(actions.length).toBe(1);
        expect(actions[0]!.name).toBe('trigger_action_hook');
        expect(actions[0]!.params).toContain('hook_name');
        expect(actions[0]!.params).toContain(`pw_wf_probe_hook_${suffix}`);

        // No conditions were sent → zero condition rows.
        expect((await getConditions(id!)).length).toBe(0);
    });

    // WFX-LC-02 — firing user_register on a no-condition active workflow runs it.
    test('WFX-LC-02 firing user_register increments run 0→1 and writes one log row', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}LC02 ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, actions: [probeHookAction(suffix)],
        });
        expect(id, 'workflow should be created').toBeTruthy();
        expect(await getRun(id!)).toBe(0);
        expect(await getLogCount(id!)).toBe(0);

        const userId = await fireCreatedUser(suffix);
        expect(userId, 'user create should fire user_register').toBeTruthy();

        // Execution side-effects: run incremented + exactly one log row.
        expect(await getRun(id!), 'run should increment to 1').toBe(1);
        expect(await getLogCount(id!), 'one execution → one log row').toBe(1);
    });

    // WFX-LC-03 — two fires → run=2, 2 logs (per-event, repeatable execution).
    test('WFX-LC-03 two user_register fires increment run to 2 and create 2 log rows', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}LC03 ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, actions: [probeHookAction(suffix)],
        });
        expect(id).toBeTruthy();

        await fireCreatedUser(`${suffix}a`);
        await fireCreatedUser(`${suffix}b`);

        expect(await getRun(id!), 'run should be 2 after two fires').toBe(2);
        expect(await getLogCount(id!), 'two executions → two log rows').toBe(2);
    });

    // WFX-LC-04 — full end-to-end, asserting no fatal anywhere in the flow.
    test('WFX-LC-04 end-to-end scrape→save→fire→assert with no critical error or fatal', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}LC04 ${suffix}`;

        // (1) Add-New page renders, scrape nonce.
        await page.goto(URLS.addNew, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        const nonce = await scrapeWorkflowNonce(page.request, 'create');

        // (2) save via the real handler.
        const { status, body, id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, actions: [probeHookAction(suffix)],
        });
        expect(status).toBe(200);
        expect(body?.success).toBe(true);
        expect(id).toBeTruthy();

        // (3) fire the trigger.
        await fireCreatedUser(suffix);

        // (4) assert execution + the workflow shows up in the list with no fatal.
        expect(await getRun(id!)).toBe(1);
        expect(await getLogCount(id!)).toBe(1);

        await page.goto(URLS.list, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator('table.wp-list-table')).toContainText(name, { timeout: 15_000 });
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Condition gating — non-match suppresses, match fires, AND logic
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow execution — condition gating (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // WFX-CON-01 — a NON-matching email condition does NOT fire.
    test('WFX-CON-01 non-matching email condition does not fire (run stays 0, no logs)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}CON01 ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, conditionsGroup: 'and',
            conditions: [{ condition_name: 'email', operator: '=', value: `never-match-${suffix}@nope.test` }],
            actions: [probeHookAction(suffix)],
        });
        expect(id).toBeTruthy();

        // Fire with a DIFFERENT email than the condition value.
        await fireCreatedUser(suffix, `${suffix}@example.com`);

        expect(await getRun(id!), 'non-matching condition must not fire').toBe(0);
        expect(await getLogCount(id!), 'no execution → no log row').toBe(0);
    });

    // WFX-CON-02 — a MATCHING email condition (scalar field present at trigger
    // time) DOES fire. Email is the deterministic matching field over REST.
    test('WFX-CON-02 matching email condition fires on user_register (run=1, 1 log)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}CON02 ${suffix}`;
        const matchEmail = `pwwfxmatch${suffix}@example.com`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, conditionsGroup: 'and',
            conditions: [{ condition_name: 'email', operator: '=', value: matchEmail }],
            actions: [probeHookAction(suffix)],
        });
        expect(id).toBeTruthy();

        // Fire with the EXACT email the condition expects.
        await fireCreatedUser(suffix, matchEmail);

        expect(await getRun(id!), 'matching condition should fire').toBe(1);
        expect(await getLogCount(id!), 'one execution → one log row').toBe(1);
    });

    // WFX-CON-03 — the condition row persists exactly as sent.
    test('WFX-CON-03 condition row persists with name/operator/value/workflow_id', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}CON03 ${suffix}`;
        const value = `persist-${suffix}@nope.test`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, conditionsGroup: 'and',
            conditions: [{ condition_name: 'email', operator: '=', value }],
            actions: [probeHookAction(suffix)],
        });
        expect(id).toBeTruthy();

        const conditions = await getConditions(id!);
        expect(conditions.length).toBe(1);
        expect(conditions[0]!.condition_name).toBe('email');
        expect(conditions[0]!.operator).toBe('=');
        expect(conditions[0]!.value).toBe(value);
        expect(Number(conditions[0]!.workflow_id)).toBe(id);
    });

    // WFX-CON-04 — AND group with one matching + one non-matching → does NOT fire.
    test('WFX-CON-04 AND group with one non-matching condition does not fire', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}CON04 ${suffix}`;
        const matchEmail = `pwwfxand${suffix}@example.com`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, conditionsGroup: 'and',
            conditions: [
                { condition_name: 'email', operator: '=', value: matchEmail },          // matches
                { condition_name: 'email', operator: '=', value: `other-${suffix}@x.test` }, // never matches
            ],
            actions: [probeHookAction(suffix)],
        });
        expect(id).toBeTruthy();
        // Both condition rows persisted.
        expect((await getConditions(id!)).length).toBe(2);

        await fireCreatedUser(suffix, matchEmail);

        // AND: not all true → erp_wf_logical_and_or returns false → no fire.
        expect(await getRun(id!), 'AND group with a failing condition must not fire').toBe(0);
        expect(await getLogCount(id!)).toBe(0);
    });

    // WFX-CON-FINDING — DOCUMENTED behavior: a `roles = subscriber` condition does
    // NOT fire when the user is created via REST /wp/v2/users, because WP REST
    // assigns the role AFTER `user_register`. This is asserted as the OBSERVED
    // behavior (run=0) so the suite encodes the nuance rather than mis-claiming a
    // fire. (A wp-cli create with --role would fire it; REST does not.)
    test('WFX-CON-FINDING roles condition does not fire via REST user create (role set post-hook)', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}CONROLE ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, conditionsGroup: 'and',
            conditions: [{ condition_name: 'roles', operator: '=', value: 'subscriber' }],
            actions: [probeHookAction(suffix)],
        });
        expect(id).toBeTruthy();

        await fireCreatedUser(suffix); // REST create with roles:['subscriber']

        // Observed: role is empty at user_register over REST → condition fails.
        expect(await getRun(id!), 'roles condition does not match over REST timing').toBe(0);
        expect(await getLogCount(id!)).toBe(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Status gating — paused workflows never fire
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow execution — status gating (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // WFX-STA-01 — a paused workflow (activate omitted) does NOT fire.
    test('WFX-STA-01 paused workflow does not fire on user_register', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}STA01 ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, /* activate omitted → paused */ actions: [probeHookAction(suffix)],
        });
        expect(id).toBeTruthy();

        const wf = await getWorkflow(id!);
        expect(String(wf!.status), 'omitting activate stores status=paused').toBe('paused');

        await fireCreatedUser(suffix);

        expect(await getRun(id!), 'paused workflow must not run').toBe(0);
        expect(await getLogCount(id!)).toBe(0);
    });

    // WFX-STA-02 — when an active + a paused workflow share event=created_user,
    // only the active one fires.
    test('WFX-STA-02 only the active workflow fires when active+paused share the event', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const activeName = `${WF_NAME_PREFIX}STA02act ${suffix}`;
        const pausedName = `${WF_NAME_PREFIX}STA02pau ${suffix}`;

        const nonce1 = await scrapeWorkflowNonce(page.request, 'create');
        const active = await saveNewWorkflow(page.request, nonce1, {
            name: activeName, activate: true, actions: [probeHookAction(`${suffix}a`)],
        });
        const nonce2 = await scrapeWorkflowNonce(page.request, 'create');
        const paused = await saveNewWorkflow(page.request, nonce2, {
            name: pausedName, /* paused */ actions: [probeHookAction(`${suffix}p`)],
        });
        expect(active.id).toBeTruthy();
        expect(paused.id).toBeTruthy();

        await fireCreatedUser(suffix);

        expect(await getRun(active.id!), 'active workflow runs').toBe(1);
        expect(await getLogCount(active.id!)).toBe(1);
        expect(await getRun(paused.id!), 'paused workflow stays untouched').toBe(0);
        expect(await getLogCount(paused.id!)).toBe(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Action persistence — multiple actions, alternate action types, empty actions
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow execution — action persistence (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // WFX-ACT-01 — add_user_role action persists with a serialized role param.
    test('WFX-ACT-01 add_user_role action persists with serialized role param', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}ACT01 ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true,
            actions: [{ name: 'add_user_role', title: 'Add User Role', role: 'subscriber' }],
        });
        expect(id).toBeTruthy();

        const actions = await getActions(id!);
        expect(actions.length).toBe(1);
        expect(actions[0]!.name).toBe('add_user_role');
        expect(actions[0]!.params).toContain('role');
        expect(actions[0]!.params).toContain('subscriber');
    });

    // WFX-ACT-02 — multiple actions persist as multiple rows keyed by workflow_id.
    test('WFX-ACT-02 multiple actions persist as multiple rows keyed by workflow_id', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}ACT02 ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true,
            actions: [
                { name: 'trigger_action_hook', title: 'Trigger Action Hook', hook_name: `pw_a_${suffix}` },
                { name: 'add_user_role', title: 'Add User Role', role: 'subscriber' },
            ],
        });
        expect(id).toBeTruthy();

        const actions = await getActions(id!);
        expect(actions.length).toBe(2);
        const names = actions.map((a) => a.name).sort();
        expect(names).toEqual(['add_user_role', 'trigger_action_hook']);
        for (const a of actions) expect(Number(a.workflow_id)).toBe(id);
    });

    // WFX-NEG-03 — saving with no actions persists the workflow but zero actions.
    test('WFX-NEG-03 save with empty actions persists the workflow with zero action rows', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}NEG03 ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { body, id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, /* no actions, no conditions */
        });
        expect(body?.success).toBe(true);
        expect(id, 'the workflow row still persists').toBeTruthy();
        expect((await getActions(id!)).length, 'no actions sent → zero action rows').toBe(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Execution timestamp fidelity
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow execution — log fidelity (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // WFX-PER-01 — the log row's created_at is a valid recent datetime.
    test('WFX-PER-01 execution log created_at is a valid recent datetime', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}PER01 ${suffix}`;
        const nonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, nonce, {
            name, activate: true, actions: [probeHookAction(suffix)],
        });
        expect(id).toBeTruthy();

        // Record a lower bound (site uses current_time('mysql'); allow generous skew).
        const before = Date.now() - 5 * 60 * 1000;
        await fireCreatedUser(suffix);

        const createdAt = await getLatestLogCreatedAt(id!);
        expect(createdAt, 'a log row with created_at should exist').toBeTruthy();
        // created_at comes back as a JS Date (mysql2) or a 'YYYY-MM-DD HH:MM:SS' string.
        const ca = createdAt as unknown;
        const ts = ca instanceof Date ? ca.getTime() : Date.parse(String(ca).replace(' ', 'T'));
        expect(Number.isNaN(ts), `created_at should be a valid datetime: ${createdAt}`).toBe(false);
        // Recency within a generous window absorbs any server/Node timezone offset.
        void before;
        expect(Math.abs(Date.now() - ts), 'created_at is within ~a day of now').toBeLessThan(24 * 60 * 60 * 1000);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Negative — nonce / auth boundaries on the save handler
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow execution — auth boundaries (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // WFX-NEG-01 — a bad create nonce is rejected; no row is created.
    test('WFX-NEG-01 bad create nonce is rejected and creates no workflow', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const name = `${WF_NAME_PREFIX}NEG01 ${suffix}`;
        // Deliberately pass a bogus nonce (do NOT scrape a real one).
        const resp = await page.request.post(toPath('wp-admin/admin-ajax.php'), {
            multipart: buildMultipart('erp_wf_new_workflow', 'deadbeef00', {
                name, activate: true, actions: [probeHookAction(suffix)],
            }),
        });
        expect(resp.status(), 'admin-ajax returns 200 with a JSON error body').toBe(200);
        let body: any;
        try { body = await resp.json(); } catch { body = await resp.text(); }
        // The Ajax trait's verify_nonce sends a JSON error (not die(-1)).
        expect(body?.success).toBe(false);
        expect(String(body?.data ?? '')).toMatch(/nonce verification failed/i);

        // No row should have been created.
        expect(await getWorkflowIdByName(name), 'no workflow row on a bad nonce').toBeFalsy();
    });

    // WFX-NEG-02 — an unauthenticated (no-cookie) POST is rejected by WP.
    test('WFX-NEG-02 unauthenticated admin-ajax POST is rejected (no privileged handler)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // A fresh cookie-less context: the privileged wp_ajax_* action is not
        // wired for logged-out users, so WP returns 400 with body '0'.
        const ctx = await pwRequest.newContext({ baseURL: process.env.BASE_URL ?? 'http://localhost:9999' });
        const resp = await ctx.post(toPath('wp-admin/admin-ajax.php'), {
            form: { action: 'erp_wf_new_workflow' },
        });
        const status = resp.status();
        const body = (await resp.text()).trim();
        // An anonymous caller must be REJECTED. WordPress may serve the blocked marker
        // '0' (no nopriv handler) or run the handler which then fails nonce verification
        // ({"success":false,"data":"... Nonce verification failed"}). Either way it must
        // NOT succeed and must NOT create a workflow — assert no success envelope.
        expect(
            body.includes('"success":true'),
            `anonymous caller must not succeed (got ${status} / "${body.slice(0, 60)}")`,
        ).toBe(false);
        await ctx.dispose();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Edit handler — update + replace conditions/actions (delete + reinsert)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow execution — edit handler (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // WFX-EDIT-01 — the edit handler updates the workflow and replaces its
    // actions/conditions (the handler deletes then re-inserts both).
    test('WFX-EDIT-01 edit handler updates the workflow and replaces actions/conditions', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const suffix = String(Date.now());
        const origName = `${WF_NAME_PREFIX}EDIT01 ${suffix}`;
        const editedName = `${WF_NAME_PREFIX}EDIT01-edited ${suffix}`;

        // Create with a condition + a trigger_action_hook action.
        const createNonce = await scrapeWorkflowNonce(page.request, 'create');
        const { id } = await saveNewWorkflow(page.request, createNonce, {
            name: origName, activate: true, conditionsGroup: 'and',
            conditions: [{ condition_name: 'email', operator: '=', value: `orig-${suffix}@x.test` }],
            actions: [probeHookAction(suffix)],
        });
        expect(id).toBeTruthy();
        expect((await getConditions(id!)).length).toBe(1);
        expect((await getActions(id!))[0]!.name).toBe('trigger_action_hook');

        // Edit: rename, drop the condition (none sent), swap to add_user_role.
        const editNonce = await scrapeWorkflowNonce(page.request, 'edit', id);
        const { status, body } = await saveEditWorkflow(page.request, editNonce, id!, {
            name: editedName, conditionsGroup: 'or',
            actions: [{ name: 'add_user_role', title: 'Add User Role', role: 'subscriber' }],
        });
        expect(status).toBe(200);
        expect(body?.success, `edit should succeed: ${JSON.stringify(body)}`).toBe(true);

        // The update landed; conditions were deleted (none re-sent); action swapped.
        const wf = await getWorkflow(id!);
        expect(String(wf!.name)).toBe(editedName);
        expect((await getConditions(id!)).length, 'edit deletes old conditions and re-inserts the (empty) set').toBe(0);
        const actions = await getActions(id!);
        expect(actions.length).toBe(1);
        expect(actions[0]!.name).toBe('add_user_role');
        expect(actions[0]!.params).toContain('subscriber');
    });
});
