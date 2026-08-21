import { execute, prefix, query } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

/**
 * Helpers for the licensed-user-limit spec.
 *
 * ERP Pro sells seats, and `Admin\Update` enforces them in four places: it
 * blocks employee creation (`Update.php:553-575`), strips ERP roles from the
 * role dropdown, reverts a role assignment that pushes the site over
 * (`Update.php:627-690`), and prints a notice on the Users screens
 * (`Update.php:775-815`).
 *
 * Testing any of that means standing the site up ON the limit, which is what
 * these helpers do. They write users straight to the tables — 68 of them per
 * run — because doing it through the UI would take longer than the tests.
 */

/** Marks every user this suite creates, so cleanup can find them all again. */
export const SEEDED_LOGIN_PREFIX = 'pwlimit_';

/**
 * The roles that count against the limit, for the modules active on this site.
 *
 * Mirrors `Update::get_counted_roles()` for the HRM + CRM + Accounting set.
 * `erp_recruiter` is deliberately absent — it is not a counted role, and the
 * spec asserts that.
 */
export const COUNTED_ROLES = ['erp_crm_manager', 'erp_crm_agent', 'erp_ac_manager', 'erp_hr_manager', 'employee'] as const;

/**
 * How many users count against the licence right now.
 *
 * A SQL mirror of `Update::count_users()`: users holding any counted role,
 * never administrators, minus employees who are not `active` — a terminated
 * employee gives their seat back (`Update.php:526-539`, `881-902`).
 *
 * Mirroring product logic in a test is normally how you get a wrong oracle, so
 * the spec cross-checks this number against the one the PRODUCT prints on its
 * own limit notice rather than trusting it alone.
 */
export async function countedUsers(): Promise<number> {
    const likes = COUNTED_ROLES.map(() => 'm.meta_value LIKE ?').join(' OR ');
    const rows = await query<RowDataPacket[]>(
        `SELECT COUNT(*) AS n
           FROM ${prefix()}users u
           JOIN ${prefix()}usermeta m ON m.user_id = u.ID AND m.meta_key = '${prefix()}capabilities'
          WHERE (${likes})
            AND m.meta_value NOT LIKE '%"administrator"%'
            AND u.ID NOT IN (
                SELECT user_id FROM ${prefix()}erp_hr_employees
                 WHERE status <> 'active' OR deleted_at IS NOT NULL
            )`,
        COUNTED_ROLES.map((role) => `%"${role}"%`)
    );

    return Number(rows[0]!.n);
}

/** The seats the licence actually bought, read from the stored status object. */
export async function licensedUsers(): Promise<number> {
    const rows = await query<RowDataPacket[]>(
        `SELECT option_value FROM ${prefix()}options WHERE option_name = 'erp_pro_license_status'`
    );

    const match = /users";i:(\d+);/.exec(String(rows[0]?.option_value ?? ''));

    return match ? Number(match[1]) : 0;
}

/** Adds plain WordPress users in a counted role until the count reaches `target`. */
export async function seedCountedUsers(target: number, role = 'employee'): Promise<number> {
    const capabilities = `a:1:{s:${role.length}:"${role}";b:1;}`;
    let created = 0;

    for (let current = await countedUsers(); current < target; current++) {
        const login = `${SEEDED_LOGIN_PREFIX}${current}_${created}`;
        const inserted = await execute(
            `INSERT INTO ${prefix()}users (user_login, user_pass, user_nicename, user_email, user_registered, display_name)
             VALUES (?, '', ?, ?, NOW(), ?)`,
            [login, login, `${login}@limit-spec.test`, login]
        );

        const userId = Number(inserted.insertId);

        await execute(
            `INSERT INTO ${prefix()}usermeta (user_id, meta_key, meta_value) VALUES (?, '${prefix()}capabilities', ?)`,
            [userId, capabilities]
        );
        await execute(`INSERT INTO ${prefix()}usermeta (user_id, meta_key, meta_value) VALUES (?, '${prefix()}user_level', '0')`, [
            userId,
        ]);

        created++;
    }

    return created;
}

/**
 * Removes every user this suite created, and any ERP employee row behind them.
 *
 * Matches on the e-mail domain as well as the login prefix: the seeded users
 * carry the prefix, but the employee the spec creates through the real HR
 * screen is named by a human, and only its address marks it as ours.
 */
export async function cleanupSeededUsers(): Promise<number> {
    const rows = await query<RowDataPacket[]>(
        `SELECT ID FROM ${prefix()}users WHERE user_login LIKE ? OR user_email LIKE '%@limit-spec.test'`,
        [`${SEEDED_LOGIN_PREFIX}%`]
    );

    if (!rows.length) return 0;

    const ids = rows.map((row) => Number(row.ID));
    const list = ids.map(() => '?').join(', ');

    await execute(`DELETE FROM ${prefix()}erp_hr_employees WHERE user_id IN (${list})`, ids);
    await execute(`DELETE FROM ${prefix()}usermeta WHERE user_id IN (${list})`, ids);

    const result = await execute(`DELETE FROM ${prefix()}users WHERE ID IN (${list})`, ids);

    return result.affectedRows ?? 0;
}

/**
 * NOTE — the hour-long cache behind the non-active employee list is NOT a
 * problem on this install, and no flush helper exists for that reason.
 *
 * `Update::erp_hr_get_employees()` caches under `erp-pro-get-employees-count`
 * for `HOUR_IN_SECONDS`, which would mean a terminated employee keeps holding
 * a seat for up to an hour. But `wp_cache_*` is per-REQUEST unless a
 * persistent object cache is installed, and this site has no
 * `object-cache.php` drop-in — verified with `wp_using_ext_object_cache()`,
 * which reports `per-request`. So each request recomputes and a freed seat is
 * visible immediately.
 *
 * On a production site running Redis or Memcached the lag would be real. That
 * is recorded in COVERAGE.md as untested rather than asserted here, because
 * this environment cannot reproduce it.
 */
