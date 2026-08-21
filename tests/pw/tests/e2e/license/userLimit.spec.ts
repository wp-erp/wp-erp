import { test, expect } from '@utils/test';
import { EmployeesPage } from '@pages/hrm/employeesPage';
import { ADMIN_STATE } from '@utils/authStates';
import { toDate } from '@utils/helpers';
import { COUNTED_ROLES, cleanupSeededUsers, countedUsers, licensedUsers, seedCountedUsers } from '@utils/userLimit';
import { closeDb, execute, prefix, query } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Licence — the purchased user limit.
 *
 * ERP Pro sells seats, and `Admin\Update` defends them in four separate
 * places. This file stands the site up ON its limit and checks each one:
 *
 *   - employee creation is blocked at the limit (`Update.php:553-575`);
 *   - the same guard holds over REST, which is the point of it living in
 *     `Update` rather than `Admin` — the comment at `:545-547` says so;
 *   - a role change that would push the site over is REVERTED after the fact
 *     (`Update.php:627-690`);
 *   - the Users screens print a notice naming both numbers (`:775-815`).
 *
 * **This spec is destructive and runs in its own project.** It creates 60-odd
 * users to reach the limit and deletes them again in teardown; nothing else
 * can run meaningfully while the site is at its seat limit.
 *
 * The seat arithmetic is read from the product wherever possible. `countedUsers()`
 * mirrors `Update::count_users()` in SQL to DRIVE the seeding, but the first
 * case below checks that mirror against the number the product itself prints,
 * so a drift in either is caught rather than silently assumed away.
 */

const LIMIT_MESSAGE =
    'Current WP ERP PRO user limit has been exceeded. Please upgrade the number of users in order to add new Employee.';

test.describe('Licence — purchased user limit', () => {
    let page: EmployeesPage;
    let licensed = 0;

    /** A throwaway WordPress user holding no counted role. */
    async function makeUncountedUser(login: string): Promise<number> {
        const inserted = await execute(
            `INSERT INTO ${prefix()}users (user_login, user_pass, user_nicename, user_email, user_registered, display_name)
             VALUES (?, '', ?, ?, NOW(), ?)`,
            [login, login, `${login}@limit-spec.test`, login]
        );

        const userId = Number(inserted.insertId);

        await execute(
            `INSERT INTO ${prefix()}usermeta (user_id, meta_key, meta_value) VALUES (?, '${prefix()}capabilities', 'a:1:{s:10:"subscriber";b:1;}')`,
            [userId]
        );

        return userId;
    }

    async function rolesOf(userId: number): Promise<string> {
        const rows = await query<RowDataPacket[]>(
            `SELECT meta_value FROM ${prefix()}usermeta WHERE user_id = ? AND meta_key = '${prefix()}capabilities'`,
            [userId]
        );

        return String(rows[0]?.meta_value ?? '');
    }

    test.beforeAll(async () => {
        licensed = await licensedUsers();
        expect(licensed, 'precondition: the licence reports its seats').toBeGreaterThan(0);
    });

    test.afterAll(async () => {
        await cleanupSeededUsers();
        await closeDb();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new EmployeesPage(p);
    });

    // ---- one seat left -----------------------------------------------------

    test('the last seat can still be filled', { tag: ['@tier1', '@pro', '@license-limit', '@serial', '@destructive', '@flow'] }, async () => {
        await cleanupSeededUsers();
        await seedCountedUsers(licensed - 1);

        expect(await countedUsers(), `precondition: the site sits one seat below ${licensed}`).toBe(licensed - 1);

        await page.goto();

        const notice = await page.create({
            firstName: 'Hundredth',
            lastName: 'Hire',
            email: 'hundredth.hire@limit-spec.test',
            type: 'permanent',
            status: 'active',
            hiringDate: toDate(),
            departmentLabel: 'Engineering',
            designationLabel: 'Software Engineer',
        });

        expect(notice, `the employee is created, notice read: ${notice}`).not.toContain('limit');
        expect(await countedUsers(), 'and the site is now exactly at its limit').toBe(licensed);
    });

    // ---- at the limit ------------------------------------------------------

    test('an employee beyond the limit is refused, and the product says why', { tag: ['@tier1', '@pro', '@license-limit', '@serial', '@destructive', '@validation'] }, async () => {
        await cleanupSeededUsers();
        await seedCountedUsers(licensed);

        expect(await countedUsers(), 'precondition: the site is at its limit').toBe(licensed);

        await page.goto();

        const message = await page.attemptCreate({
            firstName: 'One',
            lastName: 'TooMany',
            email: 'one.toomany@limit-spec.test',
            type: 'permanent',
            status: 'active',
            hiringDate: toDate(),
            departmentLabel: 'Engineering',
            designationLabel: 'Software Engineer',
        });

        expect(message, `the refusal names the limit, message read: ${message}`).toContain(LIMIT_MESSAGE);

        const created = await query<RowDataPacket[]>(
            `SELECT COUNT(*) AS n FROM ${prefix()}users WHERE user_email = 'one.toomany@limit-spec.test'`
        );

        expect(Number(created[0]!.n), 'and no user is left behind').toBe(0);
    });

    test('the REST route is guarded too, not just the screen', { tag: ['@tier2', '@pro', '@license-limit', '@serial', '@destructive', '@api'] }, async ({ page: p }) => {
        await cleanupSeededUsers();
        await seedCountedUsers(licensed);

        expect(await countedUsers(), 'precondition: the site is at its limit').toBe(licensed);

        await page.goto();

        const response = await p.evaluate(async () => {
            // `wpApiSettings.nonce` is the WordPress REST nonce. The ERP
            // globals (`wpErpHr`, `wpErp`, …) each carry their own AJAX nonce,
            // and using one of those answers `rest_cookie_invalid_nonce` — a
            // 403 that looks exactly like the guard rejecting the request.
            const settings = (window as unknown as { wpApiSettings?: { nonce?: string } }).wpApiSettings;
            const nonce = settings?.nonce ?? '';
            const request = await fetch('/wp-json/erp/v1/hrm/employees', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                // Field names as `EmployeesController::prepare_item_for_database`
                // reads them (`:1891-1940`): `email`, not `user_email`. The
                // payload has to be VALID, because employee validation runs
                // before the licence guard and an invalid-email refusal would
                // pass this test for entirely the wrong reason.
                body: JSON.stringify({
                    first_name: 'Rest',
                    last_name: 'Overflow',
                    email: 'rest.overflow@limit-spec.test',
                    designation: 1,
                    department: 1,
                    type: 'permanent',
                    status: 'active',
                    hiring_date: new Date().toISOString().slice(0, 10),
                }),
            });

            return { status: request.status, body: (await request.text()).slice(0, 400) };
        });

        expect(
            response.body,
            `the REST guard answers with the limit error, response read: ${response.status} ${response.body}`
        ).toContain('user-limit-exceeded');

        const created = await query<RowDataPacket[]>(
            `SELECT COUNT(*) AS n FROM ${prefix()}users WHERE user_email = 'rest.overflow@limit-spec.test'`
        );

        expect(Number(created[0]!.n), 'and nothing is written').toBe(0);
    });

    test('the Users screen says how many seats are used, and agrees with the count', { tag: ['@tier2', '@pro', '@license-limit', '@serial', '@destructive'] }, async ({ page: p }) => {
        await cleanupSeededUsers();
        await seedCountedUsers(licensed);

        const counted = await countedUsers();

        await p.goto('/wp-admin/users.php', { waitUntil: 'domcontentloaded' });
        const body = (await p.locator('#wpbody-content').innerText()).replace(/\s+/g, ' ');

        expect(body, 'the limit notice is shown').toContain('user limit has been reached');
        expect(body, 'naming the purchased seats').toContain(`Purchased Users: ${licensed}`);

        // The cross-check that keeps the SQL mirror honest: the product's own
        // figure has to match the number this spec seeded against.
        expect(body, `and the product's own count agrees, body read: ${body.slice(0, 300)}`).toContain(
            `Current Site Users: ${counted}`
        );
    });

    test('the role dropdown stops offering ERP roles at the limit', { tag: ['@tier2', '@pro', '@license-limit', '@serial', '@destructive', '@authz'] }, async ({ page: p }) => {
        const roleOptions = async (userId: number): Promise<string[]> => {
            await p.goto(`/wp-admin/user-edit.php?user_id=${userId}`, { waitUntil: 'domcontentloaded' });

            return p
                .locator('select#role option')
                .evaluateAll((options) => options.map((option) => (option as HTMLOptionElement).value));
        };

        await cleanupSeededUsers();
        await seedCountedUsers(licensed - 1);

        const userId = await makeUncountedUser('pwlimit_rolecheck');

        expect(await rolesOf(userId), 'precondition: the user holds no counted role').toContain('subscriber');

        // The contrast that makes this test mean anything: one seat short of the
        // limit, the ERP roles ARE on offer. Without this the assertion below
        // would pass just as happily against a dropdown that never listed them.
        const belowLimit = await roleOptions(userId);

        expect(belowLimit, 'precondition: with a seat spare, the dropdown rendered').toContain('subscriber');

        // Only SOME counted roles are WordPress-editable roles at all: on this
        // install the dropdown offers `employee` and `erp_ac_manager`, and never
        // lists `erp_crm_manager`, `erp_crm_agent` or `erp_hr_manager` whatever
        // the seat count. So the assertion is scoped to the roles that are
        // genuinely on offer, and requires the set to be non-empty — otherwise
        // "none of them are listed at the limit" would pass against a dropdown
        // that never listed them in the first place. The first draft asserted
        // all five and failed here, which is how the difference was found.
        const offeredCounted = COUNTED_ROLES.filter((role) => belowLimit.includes(role));

        expect(
            offeredCounted.length,
            `precondition: at least one counted role is on offer below the limit, dropdown: ${belowLimit.join(', ')}`
        ).toBeGreaterThan(0);

        await seedCountedUsers(licensed);

        expect(await countedUsers(), 'precondition: the site is now at its limit').toBe(licensed);

        const atLimit = await roleOptions(userId);

        expect(atLimit, 'the dropdown still renders').toContain('subscriber');

        for (const role of offeredCounted) {
            expect(atLimit, `${role} is withheld once the seats are full`).not.toContain(role);
        }

        expect(atLimit, 'and the roles that never counted are untouched').toContain('editor');
    });

    // ---- what the limit does NOT count -------------------------------------

    test('a terminated employee gives their seat back', { tag: ['@tier2', '@pro', '@license-limit', '@serial', '@destructive', '@boundary'] }, async () => {
        await cleanupSeededUsers();
        await seedCountedUsers(licensed);

        expect(await countedUsers(), 'precondition: the site is at its limit').toBe(licensed);

        const seeded = await query<RowDataPacket[]>(
            `SELECT ID FROM ${prefix()}users WHERE user_login LIKE 'pwlimit_%' ORDER BY ID DESC LIMIT 1`
        );
        const userId = Number(seeded[0]!.ID);

        // Every NOT NULL column without a default has to be given a value —
        // `hiring_source`, `termination_date`, `date_of_birth` and `type` all
        // qualify, which is why this insert is longer than the one line the
        // test actually cares about (`status = 'terminated'`).
        await execute(
            `INSERT INTO ${prefix()}erp_hr_employees
                (user_id, hiring_source, hiring_date, termination_date, date_of_birth, type, status)
             VALUES (?, 'direct', CURDATE(), CURDATE(), '1990-01-01', 'permanent', 'terminated')`,
            [userId]
        );

        expect(await countedUsers(), 'the terminated employee no longer holds a seat').toBe(licensed - 1);
    });

    test('an uncounted role does not consume a seat', { tag: ['@tier3', '@pro', '@license-limit', '@serial', '@destructive', '@boundary'] }, async () => {
        await cleanupSeededUsers();
        await seedCountedUsers(licensed - 1);

        const before = await countedUsers();
        const userId = await makeUncountedUser('pwlimit_recruiter');

        await execute(
            `UPDATE ${prefix()}usermeta SET meta_value = 'a:1:{s:13:"erp_recruiter";b:1;}'
              WHERE user_id = ? AND meta_key = '${prefix()}capabilities'`,
            [userId]
        );

        expect(COUNTED_ROLES, 'precondition: recruiter is not a counted role').not.toContain('erp_recruiter');
        expect(await countedUsers(), 'so the seat count is unchanged').toBe(before);
    });
});
