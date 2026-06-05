import { test, expect } from '@utils/test';
import { toPath } from '@utils/helpers';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';

/**
 * HRM — Workflow (erp-pro module: workflow). Admin UI smoke + deterministic DB.
 *
 * There is NO /erp/v1 REST surface for this module (verified: AjaxHandler.php
 * hooks only wp_ajax_erp_wf_* actions; Module.php / actions-filters.php register
 * zero rest routes). The create path is admin-ajax (erp_wf_new_workflow) guarded
 * by a page-printed ajax nonce (erp-wf-new-workflow), which the REST ApiUtils
 * helper cannot drive. So this spec is:
 *   (a) UI smoke for the Workflow list + Add-New Vue screens, and
 *   (b) DB-truth assertions against the four wp_erp_workflows* tables via
 *       dbUtils.dbQuery (insert / list / cleanup).
 *
 * Routes (grounded in AdminMenu::admin_menu, WPERP>=1.4.0 branch — submenu of
 * the top-level 'erp' page):
 *   list   : admin.php?page=erp-workflow
 *   add-new: admin.php?page=erp-workflow-new        (Vue app #workflow-app)
 *   edit   : admin.php?page=erp-workflow&action=edit&id={id}  (same Vue app)
 *
 * Capability 'erp_workflow_menu_permission' is granted to erp_hr_manager /
 * erp_crm_manager / erp_ac_manager (Module::create_roles_permissions) and admin.
 * A plain employee lacks it -> WP renders a "not allowed" boundary (assert the
 * boundary, never an exact 500).
 *
 * Selectors are taken verbatim from the rendered views
 * (modules/hrm/workflow/includes/views/workflows.php + workflow-new.php) and the
 * WP_List_Table columns (WorkflowsListTable::get_columns).
 *
 * Every test carries a tier tag (@pro), the @hrm module tag and a role tag.
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

// Pro tables (referenced as string literals — dbData.tables only has free tables).
const WF_TABLE = 'wp_erp_workflows';
const WF_CONDITIONS = 'wp_erp_workflow_conditions';
const WF_ACTIONS = 'wp_erp_workflow_actions';
const WF_LOGS = 'wp_erp_workflow_logs';

const WF_NAME_PREFIX = 'PW WF ';

const URLS = {
    list: toPath('wp-admin/admin.php?page=erp-workflow'),
    listActive: toPath('wp-admin/admin.php?page=erp-workflow&status=active'),
    listPaused: toPath('wp-admin/admin.php?page=erp-workflow&status=paused'),
    listTrash: toPath('wp-admin/admin.php?page=erp-workflow&status=trash'),
    add: toPath('wp-admin/admin.php?page=erp-workflow-new'),
    edit: (id: number | string) => toPath(`wp-admin/admin.php?page=erp-workflow&action=edit&id=${id}`),
} as const;

// Stable DOM (verified in the two view templates + WP_List_Table output).
const SEL = {
    wrap: 'div.wrap',
    listHeading: 'div.wrap > h2',
    addNewH2: 'a.add-new-h2',
    table: 'table.wp-list-table',
    colName: 'table.wp-list-table thead th#name',
    colStatus: 'table.wp-list-table thead th#status',
    colModule: 'table.wp-list-table thead th#events_group',
    colEvent: 'table.wp-list-table thead th#event',
    colRun: 'table.wp-list-table thead th#run',
    colCreatedBy: 'table.wp-list-table thead th#created_by',
    colCreatedAt: 'table.wp-list-table thead th#created_at',
    statusViews: 'ul.subsubsub',
    searchInput: 'input#workflow-search-input',
    // Add-New / Edit Vue app
    app: '#workflow-app',
    form: 'form#workflow-form',
    nameInput: "input[v-model='workflow_name']",
    delayInput: "input[type=number][v-model='delay_time']",
    trigger: 'section#workflow-conditions #trigger',
    saveActivate: "input.button-primary[value='Save & Activate']",
    saveOnly: "input.button-secondary[value='Save Only']",
} as const;

// ── DB helpers (inline — only this feature uses them) ────────────────────────

/** Insert a minimal valid workflow row. 'run' + the datetime cols are NOT NULL
 *  with no default, so the INSERT supplies them explicitly. Returns the new id. */
async function insertWorkflow(name: string, status: 'active' | 'paused' = 'active'): Promise<number | undefined> {
    const result = await dbUtils.dbQuery<{ insertId: number }>(
        `INSERT INTO ${WF_TABLE}
            (name, type, object, events_group, event, conditions_group, status, delay_time, delay_period, run, created_at, updated_at, created_by)
         VALUES (?, 'auto', '', 'hrm', 'created_employee', 'or', ?, 0, 'minute', 0, NOW(), NOW(), 1)`,
        [name, status],
    );
    let id = (result as unknown as { insertId?: number }).insertId;
    if (!id) {
        const found = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${WF_TABLE} WHERE name = ? ORDER BY id DESC LIMIT 1`,
            [name],
        );
        id = found[0]?.id;
    }
    return id;
}

async function getWorkflowsByName(name: string): Promise<Record<string, unknown>[]> {
    return dbUtils.dbQuery<Record<string, unknown>>(
        `SELECT * FROM ${WF_TABLE} WHERE name = ?`,
        [name],
    );
}

// Track ids we create so afterAll can purge their child rows too.
const createdIds: number[] = [];

test.afterAll(async () => {
    // Remove the explicit ids from every table (the child tables key off workflow_id).
    for (const id of createdIds) {
        await dbUtils.dbQuery(`DELETE FROM ${WF_TABLE} WHERE id = ?`, [id]);
        await dbUtils.dbQuery(`DELETE FROM ${WF_CONDITIONS} WHERE workflow_id = ?`, [id]);
        await dbUtils.dbQuery(`DELETE FROM ${WF_ACTIONS} WHERE workflow_id = ?`, [id]);
        await dbUtils.dbQuery(`DELETE FROM ${WF_LOGS} WHERE workflow_id = ?`, [id]);
    }
    // Safety net: nuke any leftover PW-prefixed workflow rows from prior runs.
    await dbUtils.deleteRowsLike(WF_TABLE, 'name', WF_NAME_PREFIX);
    await dbUtils.close();
});

// ─────────────────────────────────────────────────────────────────────────────
// Admin — UI smoke for the Workflow list + Add-New screens
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow UI (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // WF-UI-01 — list page loads with the WP list table mounted, no fatal.
    test('workflow list page loads with the list table mounted', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(URLS.list);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(SEL.wrap).first()).toBeVisible();
        await expect(page.locator(SEL.listHeading)).toContainText(/Workflows/i);
        await expect(page.locator(SEL.table)).toBeVisible();
    });

    // WF-UI-02 — Add New control links to ?page=erp-workflow-new.
    test('list page exposes the Add New control to the add screen', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(URLS.list);
        const addNew = page.locator(SEL.addNewH2);
        await expect(addNew).toBeVisible();
        await expect(addNew).toHaveAttribute('href', /page=erp-workflow-new/);
    });

    // WF-UI-03 — list table renders the expected columns.
    test('list table renders the expected columns', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(URLS.list);
        await expect(page.locator(SEL.table)).toBeVisible();
        // Columns are addressed by their WP_List_Table th id (stable).
        await expect(page.locator(SEL.colName)).toBeVisible();
        await expect(page.locator(SEL.colStatus)).toBeVisible();
        await expect(page.locator(SEL.colModule)).toBeVisible();
        await expect(page.locator(SEL.colEvent)).toBeVisible();
        await expect(page.locator(SEL.colRun)).toBeVisible();
        await expect(page.locator(SEL.colCreatedBy)).toBeVisible();
        await expect(page.locator(SEL.colCreatedAt)).toBeVisible();
    });

    // WF-UI-04 — status sub-views (All / Active / Paused / Trash) render with counts.
    test('status sub-views render with live counts', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(URLS.list);
        const views = page.locator(SEL.statusViews);
        await expect(views).toBeVisible();
        await expect(views).toContainText(/All/i);
        await expect(views).toContainText(/Active/i);
        await expect(views).toContainText(/Paused/i);
        await expect(views).toContainText(/Trash/i);
    });

    // WF-UI-05 — the Active / Paused / Trash filtered views all load without a fatal.
    test('filtered status views load without a fatal', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        for (const url of [URLS.listActive, URLS.listPaused, URLS.listTrash]) {
            await page.goto(url);
            await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
            await expect(page.locator(SEL.table)).toBeVisible();
        }
    });

    // WF-UI-06 — the search box is present (search_box label 'Search Workflow').
    test('the workflow search box is present', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(URLS.list);
        await expect(page.locator(SEL.searchInput)).toBeVisible();
    });

    // WF-UI-07 — Add-New screen mounts the Vue app + the workflow form, no fatal.
    test('Add New screen mounts the workflow Vue app', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(URLS.add);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(SEL.app)).toBeVisible();
        await expect(page.locator(SEL.form)).toBeAttached();
        // The mount removes v-cloak once Vue boots; the name input becomes visible.
        await expect(page.locator(SEL.nameInput)).toBeVisible({ timeout: 15_000 });
        await expect(page.locator('body')).toContainText(/Create Workflow/i);
    });

    // WF-UI-08 — Add-New screen shows the Save controls + the Trigger section.
    test('Add New screen shows the Save controls and Trigger section', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(URLS.add);
        await expect(page.locator(SEL.nameInput)).toBeVisible({ timeout: 15_000 });
        await expect(page.locator(SEL.saveActivate)).toBeVisible();
        await expect(page.locator(SEL.saveOnly)).toBeVisible();
        await expect(page.locator(SEL.trigger)).toBeVisible();
    });

    // WF-UI-09 — Edit screen for a DB-seeded workflow boots the same Vue app, no fatal.
    test('Edit screen boots the Vue app for a seeded workflow', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const name = `${WF_NAME_PREFIX}${Date.now()}`;
        const id = await insertWorkflow(name);
        expect(id, 'seed insert should return an id').toBeTruthy();
        if (id) createdIds.push(id);

        await page.goto(URLS.edit(id!));
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(SEL.app)).toBeVisible();
        await expect(page.locator('body')).toContainText(/Edit Workflow/i);
    });

    // WF-UI-10 — a DB-seeded workflow is discoverable in the rendered list table.
    test('a seeded workflow appears in the list table', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        const name = `${WF_NAME_PREFIX}${Date.now()}-list`;
        const id = await insertWorkflow(name);
        expect(id).toBeTruthy();
        if (id) createdIds.push(id);

        await page.goto(URLS.list);
        await expect(page.locator(SEL.table)).toBeVisible();
        // The name column prints <strong>{name}</strong>; assert it renders.
        await expect(page.locator(SEL.table)).toContainText(name, { timeout: 15_000 });
    });

    // WF-UI-11 — best-effort save attempt on the Add-New form must not fatal.
    // PASS if the page redirects to the list (success) OR the form stays mounted
    // (client-side validation). FAIL only on a PHP fatal banner.
    test('save attempt on the Add New form does not fatal', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(URLS.add);
        await expect(page.locator(SEL.nameInput)).toBeVisible({ timeout: 15_000 });
        await page.locator(SEL.nameInput).fill(`${WF_NAME_PREFIX}${Date.now()}-ui`);
        await page.locator(SEL.saveOnly).click();
        // Either it navigated to the list, or the Vue form is still on screen — both ok.
        await page.waitForTimeout(2_000);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        const onList = await page.locator(SEL.table).count();
        const stillForm = await page.locator(SEL.app).count();
        expect(onList + stillForm).toBeGreaterThan(0);
        // Clean up any row the save may have created under our prefix.
        const rows = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${WF_TABLE} WHERE name LIKE ?`,
            [`${WF_NAME_PREFIX}%-ui`],
        );
        for (const r of rows) createdIds.push(r.id);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Admin — DB-truth: the four tables exist, and insert/list round-trips
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow DB (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // WF-DB-01 — all four workflow tables exist.
    test('the four workflow tables exist', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        for (const table of [WF_TABLE, WF_CONDITIONS, WF_ACTIONS, WF_LOGS]) {
            const rows = await dbUtils.dbQuery<Record<string, number>>(
                `SELECT COUNT(*) AS c FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = ?`,
                [table],
            );
            const count = Number(Object.values(rows[0] ?? { c: 0 })[0]);
            expect(count, `${table} should exist`).toBe(1);
        }
    });

    // WF-DB-02 — wp_erp_workflows carries the grounded schema columns.
    test('wp_erp_workflows has the expected columns', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const cols = await dbUtils.dbQuery<{ COLUMN_NAME: string }>(
            `SELECT COLUMN_NAME FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ?`,
            [WF_TABLE],
        );
        const names = cols.map((c) => String((c as Record<string, unknown>).COLUMN_NAME ?? (c as Record<string, unknown>).column_name).toLowerCase());
        for (const expected of [
            'id', 'name', 'type', 'object', 'events_group', 'event',
            'conditions_group', 'status', 'delay_time', 'delay_period', 'run',
            'created_at', 'updated_at', 'deleted_at', 'created_by',
        ]) {
            expect(names, `column ${expected}`).toContain(expected);
        }
    });

    // WF-DB-03 — insert a workflow and assert exactly one matching row reads back.
    test('insert a workflow row and read it back', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const name = `${WF_NAME_PREFIX}${Date.now()}-db`;
        const id = await insertWorkflow(name, 'active');
        expect(id, 'insert should return an id').toBeTruthy();
        if (id) createdIds.push(id);

        const rows = await getWorkflowsByName(name);
        expect(rows.length).toBe(1);
        expect(String(rows[0]!.name)).toBe(name);
        expect(String(rows[0]!.status)).toBe('active');
        expect(String(rows[0]!.events_group)).toBe('hrm');
        expect(String(rows[0]!.conditions_group)).toBe('or');
        // Soft-delete column starts NULL (Workflow model uses SoftDeletes).
        expect(rows[0]!.deleted_at).toBeNull();
    });

    // WF-DB-04 — a paused workflow persists its status (status-view fidelity).
    test('a paused workflow persists status = paused', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const name = `${WF_NAME_PREFIX}${Date.now()}-paused`;
        const id = await insertWorkflow(name, 'paused');
        expect(id).toBeTruthy();
        if (id) createdIds.push(id);

        const rows = await getWorkflowsByName(name);
        expect(rows.length).toBe(1);
        expect(String(rows[0]!.status)).toBe('paused');
    });

    // WF-DB-05 — deleting the row removes it (cleanup path is real).
    test('deleting a workflow row removes it', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const name = `${WF_NAME_PREFIX}${Date.now()}-del`;
        const id = await insertWorkflow(name);
        expect(id).toBeTruthy();

        await dbUtils.dbQuery(`DELETE FROM ${WF_TABLE} WHERE id = ?`, [id]);
        const rows = await getWorkflowsByName(name);
        expect(rows.length).toBe(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Manager — erp_hr_manager has the cap, so reaches the Workflow screens
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow access (pro, manager)', () => {
    test.use({ storageState: data.auth.hrManagerFile });

    // WF-AC-01 — HR manager can open the Workflow list (has the cap).
    test('HR manager can reach the workflow list', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        await page.goto(URLS.list);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(SEL.table)).toBeVisible();
        await expect(page.locator(SEL.listHeading)).toContainText(/Workflows/i);
    });

    // WF-AC-02 — HR manager can open the Add-New Vue screen.
    test('HR manager can reach the Add New screen', { tag: ['@pro', '@hrm', '@manager'] }, async ({ page }) => {
        await page.goto(URLS.add);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator(SEL.app)).toBeVisible();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Negative — a plain employee lacks erp_workflow_menu_permission
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Workflow access control (pro, employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    // WF-AC-03 — employee is blocked from the Workflow list (assert the boundary,
    // not an exact status). WP renders "Sorry, you are not allowed..." and the
    // real list mount (table.wp-list-table) must NOT appear.
    test('employee is blocked from the workflow list', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        const resp = await page.goto(URLS.list);
        // Never a fatal.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The list table must not render for an unprivileged user.
        await expect(page.locator(SEL.table)).toHaveCount(0);
        const status = resp?.status() ?? 0;
        const body = await page.locator('body').innerText();
        const blockedByText = /not allowed|do not have permission|cheating/i.test(body);
        // Boundary: either WP returned a 4xx OR rendered the not-allowed notice.
        expect(blockedByText || status === 403 || status >= 400).toBeTruthy();
    });

    // WF-AC-04 — employee is blocked from the Add-New screen too.
    test('employee is blocked from the Add New screen', { tag: ['@pro', '@hrm', '@employee'] }, async ({ page }) => {
        await page.goto(URLS.add);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        // The Vue workflow app must not mount for an unprivileged user.
        await expect(page.locator(SEL.app)).toHaveCount(0);
        const body = await page.locator('body').innerText();
        expect(/not allowed|do not have permission|cheating/i.test(body)).toBeTruthy();
    });
});
