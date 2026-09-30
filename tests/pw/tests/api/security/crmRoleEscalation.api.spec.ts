import { test, expect, request } from '@utils/test';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';
import { BASE_URL, toPath } from '@utils/helpers';
import { CrmPage } from '../../e2e/crm/crmPage';

/**
 * Privilege-escalation regression specs for the CRM "Make WP User" flow.
 *
 * Reported as erp-pro#872: a user holding the ERP CRM agent role (normally paired
 * with the WordPress subscriber role) could promote a contact they own into a
 * WordPress user with any non-administrator role, Editor included. Because the
 * agent chooses the contact's email address, they could then complete the password
 * setup flow and log in with the escalated privileges.
 *
 * Root cause: erp_get_editable_roles() (includes/functions.php) built its allow-list
 * from core's get_editable_roles() and stripped only `administrator`. Core does not
 * capability-filter that list — it expects the caller to check promote_users /
 * edit_users, which ERP never did. So `customer_role=editor` passed both the
 * allow-list in AjaxHandler::make_wp_user() and erp_crm_make_wp_user() itself.
 *
 * These specs drive the real AJAX endpoint (`erp-crm-make-wp-user`) as the agent,
 * because that is the attacker's actual entry point — asserting on the PHP helpers
 * alone would not prove the HTTP path is closed. Each case checks BOTH that the
 * request is refused AND that no WordPress user was actually created, since a
 * "success: false" response with a user left behind is still an escalation.
 */

const ADMIN_AJAX = toPath('wp-admin/admin-ajax.php');

/**
 * The make-wp-user template is only printed on the contacts list screen
 * (modules/crm/CRM.php: section=contact / sub-section=contacts), not on the CRM
 * dashboard — so the nonce and the role dropdown only exist at this URL.
 */
const CONTACTS_URL = toPath('wp-admin/admin.php?page=erp-crm&section=contact&sub-section=contacts');

/** Roles that grant capabilities a subscriber-level CRM agent does not hold. */
const ESCALATED_ROLES = ['editor', 'author', 'contributor', 'administrator'] as const;

let agentUserId: number;
const seededEmails: string[] = [];

/**
 * Scrape the `erp-crm-make-wp-user` nonce out of the CRM admin page. The modal is a
 * wp.template block rendered inline (modules/crm/views/js-templates/make-wp-user.php),
 * so the nonce field ships with the page HTML even before the modal is opened.
 *
 * The generic `name="_wpnonce"` match is deliberately scoped to the make-wp-user
 * template first: the CRM page carries several unrelated nonce fields, and grabbing
 * the first one yields a token that fails verification for this action.
 */
function scrapeMakeWpUserNonce(html: string): string | undefined {
    // Anchor on the template block, then take the nonce that immediately precedes
    // this action's hidden input. wp_nonce_field() emits `id` before `name`, and the
    // page carries several other nonce fields, so both the scope and the trailing
    // action anchor matter.
    const template = html.match(
        /id="tmpl-erp-make-wp-user"[\s\S]*?name="_wpnonce"\s+value="([a-f0-9]+)"[\s\S]*?value="erp-crm-make-wp-user"/i,
    );
    return template?.[1];
}

/** Look up a WordPress user id by email, or undefined when no account exists. */
async function findWpUserByEmail(email: string): Promise<number | undefined> {
    const rows = await dbUtils.dbQuery<{ ID: number }>(`SELECT ID FROM ${tables.users} WHERE user_email = ? LIMIT 1`, [email]);
    return rows[0]?.ID;
}

/** Read a user's serialized capabilities blob (raw, for substring role checks). */
async function readCapabilities(userId: number): Promise<string> {
    const prefix = process.env.DB_PREFIX ?? 'wp';
    const rows = await dbUtils.dbQuery<{ meta_value: string }>(
        `SELECT meta_value FROM ${tables.userMeta} WHERE user_id = ? AND meta_key = ? LIMIT 1`,
        [userId, `${prefix}_capabilities`],
    );
    return rows[0]?.meta_value ?? '';
}

test.describe('CRM make-wp-user privilege escalation (erp-pro#872)', () => {
    test.use({ storageState: data.auth.crmAgentFile });

    test.beforeAll(async () => {
        const rows = await dbUtils.dbQuery<{ ID: number }>(`SELECT ID FROM ${tables.users} WHERE user_login = ? LIMIT 1`, [
            data.users.crmAgent.username,
        ]);
        agentUserId = Number(rows[0]?.ID ?? process.env.CRM_AGENT_ID ?? 0);
        expect(agentUserId, 'CRM agent user was created by the auth setup').toBeGreaterThan(0);
    });

    test.afterAll(async () => {
        // Remove any user/contact the specs created so reruns start clean.
        for (const email of seededEmails) {
            const userId = await findWpUserByEmail(email);
            if (userId) {
                await dbUtils.dbQuery(`DELETE FROM ${tables.userMeta} WHERE user_id = ?`, [userId]);
                await dbUtils.dbQuery(`DELETE FROM ${tables.users} WHERE ID = ?`, [userId]);
            }
            await dbUtils.dbQuery(`DELETE FROM ${tables.peoples} WHERE email = ?`, [email]);
        }
        await dbUtils.close();
    });

    /**
     * Seed a contact owned by the agent (ownership matters: erp_crm_edit_contact maps
     * to do_not_allow for an agent unless contact_owner matches), then fire the
     * make-wp-user AJAX request as that agent with the requested role.
     */
    async function attemptPromotion(
        page: import('@playwright/test').Page,
        role: string,
    ): Promise<{ status: number; body: string; email: string }> {
        const contact = data.crm.contact();
        seededEmails.push(contact.email);

        const contactId = await CrmPage.insertContactRow({
            first_name: contact.first_name,
            last_name: contact.last_name,
            email: contact.email,
            contact_owner: agentUserId,
        });
        expect(contactId, 'agent-owned contact was seeded').toBeTruthy();

        await page.goto(CONTACTS_URL, { waitUntil: 'domcontentloaded' });
        const nonce = scrapeMakeWpUserNonce(await page.content());
        expect(nonce, 'make-wp-user nonce is present on the CRM page').toBeTruthy();

        // page.request rides the agent's own cookies, so this is a genuine
        // authenticated request from the low-privileged account.
        const res = await page.request.post(ADMIN_AJAX, {
            form: {
                action: 'erp-crm-make-wp-user',
                id: String(contactId),
                type: 'contact',
                customer_email: contact.email,
                customer_role: role,
                _wpnonce: nonce as string,
            },
        });

        return { status: res.status(), body: await res.text(), email: contact.email };
    }

    for (const role of ESCALATED_ROLES) {
        test(`agent cannot promote a contact to ${role}`, { tag: ['@lite', '@crm', '@security'] }, async ({ page }) => {
            const { status, body, email } = await attemptPromotion(page, role);

            // The handler answers 200 with {"success":false,...}; a hard 4xx or a
            // bare -1/0 from admin-ajax is an equally valid refusal.
            const denied =
                status >= 400 ||
                body.trim() === '0' ||
                body.trim() === '-1' ||
                /"success"\s*:\s*false/i.test(body) ||
                /not allowed|do not have permission|sufficient permissions/i.test(body);
            expect(denied, `promotion to ${role} was refused (body: ${body.slice(0, 200)})`).toBe(true);

            // The refusal must be real: no account may exist for that email.
            const userId = await findWpUserByEmail(email);
            expect(userId, `no WordPress user was created for the ${role} attempt`).toBeUndefined();
        });
    }

    test('agent can still promote a contact to subscriber', { tag: ['@lite', '@crm', '@security'] }, async ({ page }) => {
        // The fix must not break the legitimate flow: subscriber grants nothing the
        // agent does not already hold, so it stays allowed.
        const { body, email } = await attemptPromotion(page, 'subscriber');
        expect(/"success"\s*:\s*true/i.test(body), `subscriber promotion succeeded (body: ${body.slice(0, 200)})`).toBe(true);

        const userId = await findWpUserByEmail(email);
        expect(userId, 'subscriber account was created').toBeTruthy();

        const caps = await readCapabilities(userId as number);
        expect(caps).toContain('subscriber');
        expect(caps).not.toContain('editor');
    });

    test('the role dropdown offers no role above the agent', { tag: ['@lite', '@crm', '@security'] }, async ({ page }) => {
        // Server-rendered by erp_dropdown_roles() → erp_get_editable_roles(), so the
        // markup itself proves the allow-list is capability-filtered rather than
        // merely hidden client-side.
        await page.goto(CONTACTS_URL, { waitUntil: 'domcontentloaded' });
        const html = await page.content();

        const select = html.match(/<select[^>]+name="customer_role"[\s\S]*?<\/select>/i)?.[0];
        // erp_dropdown_roles() emits single-quoted attributes, but wp_kses may
        // normalize them, so the option matcher below accepts either form.
        expect(select, 'customer_role dropdown is rendered on the CRM page').toBeTruthy();

        const offered = [...(select as string).matchAll(/value='([^']+)'|value="([^"]+)"/g)].map(m => m[1] ?? m[2]).filter(Boolean);
        expect(offered.length, 'dropdown lists at least one assignable role').toBeGreaterThan(0);

        for (const role of ESCALATED_ROLES) {
            expect(offered, `dropdown does not offer ${role} to a CRM agent`).not.toContain(role);
        }
    });
});

test.describe('CRM make-wp-user requires authentication', () => {
    test('anonymous make-wp-user request is rejected', { tag: ['@lite', '@crm', '@security'] }, async () => {
        const ctx = await request.newContext({
            baseURL: BASE_URL,
            ...data.auth.noAuth,
            ignoreHTTPSErrors: true,
        });
        const res = await ctx.post(ADMIN_AJAX, {
            form: {
                action: 'erp-crm-make-wp-user',
                id: '1',
                type: 'contact',
                customer_email: 'anon_escalation@example.com',
                customer_role: 'editor',
            },
        });
        const body = await res.text();
        const denied = res.status() >= 400 || body.trim() === '0' || body.trim() === '-1' || /"success"\s*:\s*false/i.test(body);
        expect(denied, `anonymous request refused (body: ${body.slice(0, 200)})`).toBe(true);

        const userId = await findWpUserByEmail('anon_escalation@example.com');
        expect(userId, 'no account created for the anonymous attempt').toBeUndefined();
        await ctx.dispose();
        await dbUtils.close();
    });
});
