/**
 * Direct MySQL access. Two jobs only:
 *   1. Oracles the UI cannot show (serialized options, raw ERP table rows).
 *   2. Bulk seeding that would take hours through the UI — chiefly the 99 users
 *      the 100/101-user licence test needs.
 *
 * Anything a spec can assert through the UI or REST is asserted there instead;
 * the DB is the check of last resort, not the default.
 */
import mysql, { Pool, RowDataPacket, ResultSetHeader } from 'mysql2/promise';
import { unserialize, serialize } from 'php-serialize';
import { env } from '@utils/helpers';

/** Anything mysql2 will bind as a parameter. */
export type SqlValue = string | number | boolean | null | Date;

let pool: Pool | undefined;

export function db(): Pool {
    if (!pool) {
        pool = mysql.createPool({
            host: env('DB_HOST', '127.0.0.1'),
            port: Number(env('DB_PORT', '8889')),
            user: env('DB_USER', 'root'),
            password: env('DB_PASSWORD', 'password'),
            database: env('DB_NAME', 'wordpress'),
            waitForConnections: true,
            connectionLimit: 10,
        });
    }
    return pool;
}

export function prefix(): string {
    return env('DB_PREFIX', 'wp_');
}

export async function closeDb(): Promise<void> {
    if (pool) {
        await pool.end();
        pool = undefined;
    }
}

export async function query<T extends RowDataPacket[]>(sql: string, values: SqlValue[] = []): Promise<T> {
    const [rows] = await db().query<T>(sql, values);
    return rows;
}

export async function execute(sql: string, values: SqlValue[] = []): Promise<ResultSetHeader> {
    const [result] = await db().execute<ResultSetHeader>(sql, values);
    return result;
}

/** Reads a WP option, unserializing PHP payloads (licence status, settings). */
export async function getOption<T = unknown>(name: string): Promise<T | null> {
    const rows = await query<RowDataPacket[]>(`SELECT option_value FROM ${prefix()}options WHERE option_name = ? LIMIT 1`, [name]);
    if (!rows.length) return null;

    const raw = String(rows[0]!.option_value);
    try {
        // strict:false is required — WP stores several ERP options as serialized
        // stdClass (e.g. erp_pro_license_status), which php-serialize refuses to
        // rebuild in strict mode because stdClass is not in the JS scope.
        return unserialize(raw, {}, { strict: false }) as T;
    } catch {
        return raw as unknown as T;
    }
}

export async function setOption(name: string, value: unknown): Promise<void> {
    const stored = typeof value === 'string' ? value : serialize(value);
    await execute(`INSERT INTO ${prefix()}options (option_name, option_value, autoload) VALUES (?, ?, 'yes') ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)`, [name, stored]);
}

export async function deleteOption(name: string): Promise<void> {
    await execute(`DELETE FROM ${prefix()}options WHERE option_name = ?`, [name]);
}

/** Count of users holding any of the given roles, excluding administrators. */
export async function countUsersByRole(roles: string[]): Promise<number> {
    if (!roles.length) return 0;

    const like = roles.map(() => `um.meta_value LIKE ?`).join(' OR ');
    const values = roles.map((r) => `%"${r}"%`);

    const rows = await query<RowDataPacket[]>(
        `SELECT COUNT(DISTINCT u.ID) AS total
           FROM ${prefix()}users u
           JOIN ${prefix()}usermeta um ON um.user_id = u.ID AND um.meta_key = '${prefix()}capabilities'
          WHERE (${like}) AND um.meta_value NOT LIKE '%"administrator"%'`,
        values
    );

    return Number(rows[0]?.total ?? 0);
}

/** Every user this suite created, by login prefix — the cleanup handle. */
export async function findSeededUserIds(loginPrefix: string): Promise<number[]> {
    const rows = await query<RowDataPacket[]>(`SELECT ID FROM ${prefix()}users WHERE user_login LIKE ?`, [`${loginPrefix}%`]);
    return rows.map((r) => Number(r.ID));
}

/** Row count of an ERP table — used as a post-condition oracle. */
export async function tableCount(table: string, where = '1=1', values: SqlValue[] = []): Promise<number> {
    const rows = await query<RowDataPacket[]>(`SELECT COUNT(*) AS total FROM ${prefix()}${table} WHERE ${where}`, values);
    return Number(rows[0]?.total ?? 0);
}
