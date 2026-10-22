import { test, expect, request } from '@utils/test';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { BASE_URL, toPath } from '@utils/helpers';

/**
 * Access-control regression specs for the audit-log change viewer (erp-pro#1060, Patchstack).
 *
 * `wp_ajax_erp_audit_log_view` (Admin\Ajax::view_edit_log_changes) renders the old/new
 * value diff of any erp_audit_log row by sequential id. In 1.17.10 its only gate was the
 * `erp-nonce` nonce, which is localized as `wpErp.nonce` on every wp-admin page, so any
 * logged-in user (a Subscriber on profile.php) could walk the ids and read the CRM/HRM
 * change history (names, emails, phones, addresses).
 *
 * Each low-privileged role scrapes its own `erp-nonce` from profile.php (the real attacker
 * path) and must be refused without the diff being rendered. Admin must keep access, and
 * entries without a diff ("add") or missing ids must not raise PHP warnings.
 */

const ADMIN_AJAX = toPath('wp-admin/admin-ajax.php');
const PROFILE_URL = toPath('wp-admin/profile.php');
const PREFIX = process.env.DB_PREFIX ?? 'wp';
const AUDIT_TABLE = `${PREFIX}_erp_audit_log`;
const MARKER = 'pw-audit-1060';
const SECRET = '+1-555-AUDIT-1060';

const php = (value: Record<string, string>) =>
    'a:' + Object.keys(value).length + ':{' + Object.entries(value).map(([k, v]) => `s:${k.length}:"${k}";s:${v.length}:"${v}";`).join('') + '}';
const b64 = (s: string) => Buffer.from(s).toString('base64');

const LOW_PRIVILEGE = [
    ['CRM agent (subscriber + erp_crm_agent)', data.auth.crmAgentFile],
    ['employee', data.auth.employeeFile],
    ['HR manager', data.auth.hrManagerFile],
    ['CRM manager', data.auth.crmManagerFile],
    ['accounting manager', data.auth.accManagerFile],
] as const;

let editLogId = 0;
let addLogId = 0;

async function insertLog(changetype: string, oldValue: string, newValue: string): Promise<number> {
    await dbUtils.dbQuery(
        `INSERT INTO ${AUDIT_TABLE} (component, sub_component, data_id, old_value, new_value, message, changetype, created_by, created_at) VALUES ('CRM', 'Contact', 0, ?, ?, ?, ?, 1, NOW())`,
        [oldValue, newValue, `${MARKER} ${changetype}`, changetype],
    );
    const rows = await dbUtils.dbQuery<{ id: number }>(`SELECT id FROM ${AUDIT_TABLE} WHERE message = ? ORDER BY id DESC LIMIT 1`, [`${MARKER} ${changetype}`]);
    return Number(rows[0]?.id ?? 0);
}

/** `erp-nonce` as localized in `wpErp` — present on every admin page for every logged-in user. */
async function scrapeErpNonce(page: import('@playwright/test').Page): Promise<string | undefined> {
    await page.goto(PROFILE_URL, { waitUntil: 'domcontentloaded' });
    return (await page.content()).match(/var wpErp = \{[^;]*?"nonce":"([a-f0-9]+)"/)?.[1];
}

async function viewLog(page: import('@playwright/test').Page, id: number) {
    const nonce = await scrapeErpNonce(page);
    expect(nonce, 'erp-nonce is printed for this user').toBeTruthy();
    const res = await page.request.post(ADMIN_AJAX, { form: { action: 'erp_audit_log_view', id: String(id), _wpnonce: nonce as string } });
    return { status: res.status(), body: await res.text() };
}

test.beforeAll(async () => {
    editLogId = await insertLog('edit', b64(php({ phone: SECRET, city: 'Old City' })), b64(php({ phone: '+1-555-0000', city: 'New City' })));
    addLogId = await insertLog('add', '', '');
    expect(editLogId, 'edit audit row seeded').toBeGreaterThan(0);
    expect(addLogId, 'add audit row seeded').toBeGreaterThan(0);
});

test.afterAll(async () => {
    await dbUtils.dbQuery(`DELETE FROM ${AUDIT_TABLE} WHERE message LIKE ?`, [`${MARKER}%`]);
    await dbUtils.close();
});

test.describe('Audit log viewer is admin-only (erp-pro#1060)', () => {
    for (const [label, storageState] of LOW_PRIVILEGE) {
        test.describe(label, () => {
            test.use({ storageState });

            test(`${label} cannot read an audit log diff`, { tag: ['@lite', '@security'] }, async ({ page }) => {
                const { status, body } = await viewLog(page, editLogId);
                expect(status).toBeLessThan(500);
                expect(body, 'request refused').toMatch(/"success"\s*:\s*false/);
                expect(body, 'diff is not rendered').not.toContain(SECRET);
                expect(body).not.toContain('audit-log-change-table');
            });
        });
    }

    test.describe('admin', () => {
        test.use({ storageState: data.auth.adminFile });

        test('admin can read an audit log diff', { tag: ['@lite', '@security'] }, async ({ page }) => {
            const { body } = await viewLog(page, editLogId);
            expect(body).toMatch(/"success"\s*:\s*true/);
            expect(body).toContain(SECRET);
        });

        // admin-ajax hides PHP warnings from the response, so this guards the render path only;
        // the foreach-on-string warning itself shows up in debug.log.
        test('entry without a diff still renders', { tag: ['@lite', '@security'] }, async ({ page }) => {
            const { status, body } = await viewLog(page, addLogId);
            expect(status).toBe(200);
            expect(body).toMatch(/"success"\s*:\s*true/);
            expect(body).toContain('audit-log-change-table');
        });

        test('missing id returns a clean error', { tag: ['@lite', '@security'] }, async ({ page }) => {
            const { status, body } = await viewLog(page, 999999999);
            expect(status).toBe(200);
            expect(body).toMatch(/"success"\s*:\s*false/);
            expect(body).not.toMatch(/Warning|Notice|Fatal/);
        });
    });
});

test.describe('Audit log viewer requires authentication', () => {
    test('anonymous request is rejected', { tag: ['@lite', '@security'] }, async () => {
        const ctx = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
        const res = await ctx.post(ADMIN_AJAX, { form: { action: 'erp_audit_log_view', id: String(editLogId) } });
        const body = await res.text();
        expect(body).not.toContain(SECRET);
        await ctx.dispose();
    });
});
