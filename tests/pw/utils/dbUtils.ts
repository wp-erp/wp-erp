import mysql from 'mysql2/promise';
import { serialize, unserialize } from 'php-serialize';
import { tables } from './dbData';

/**
 * Direct DB access for seeding/cleanup that has no REST endpoint (notably CRM,
 * and php-serialized option blobs). Mirrors the wp-env / site DB config in .env.
 */
function createPool(): mysql.Pool {
    return mysql.createPool({
        host: process.env.DB_HOST_NAME ?? '127.0.0.1',
        user: process.env.DB_USER_NAME ?? 'root',
        password: process.env.DB_USER_PASSWORD ?? 'password',
        database: process.env.DATABASE ?? 'tests-wordpress',
        port: Number(process.env.DB_PORT ?? 9998),
        waitForConnections: true,
        connectionLimit: 10,
        queueLimit: 0,
    });
}

// Lazily (re)create the pool. Specs run in parallel and any one's afterAll may
// close() the shared pool; a sibling spec in the same worker must still work, so
// we transparently rebuild it on next use.
let pool: mysql.Pool | undefined;
function getPool(): mysql.Pool {
    if (!pool) pool = createPool();
    return pool;
}

async function dbQuery<T = any>(sql: string, params: any[] = []): Promise<T[]> {
    const conn = await getPool().getConnection();
    try {
        const [rows] = await conn.execute(sql, params);
        return rows as T[];
    } finally {
        conn.release();
    }
}

async function getOptionValue<T = unknown>(name: string): Promise<T | undefined> {
    const rows = await dbQuery<{ option_value: string }>(`SELECT option_value FROM ${tables.options} WHERE option_name = ? LIMIT 1`, [name]);
    if (rows.length === 0) return undefined;
    const raw = rows[0]!.option_value;
    try {
        return unserialize(raw) as T;
    } catch {
        return raw as unknown as T;
    }
}

async function setOptionValue(name: string, value: unknown): Promise<void> {
    const stored = typeof value === 'string' ? value : serialize(value);
    await dbQuery(
        `INSERT INTO ${tables.options} (option_name, option_value, autoload) VALUES (?, ?, 'yes')
         ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)`,
        [name, stored],
    );
}

/** Merge a partial into an existing (serialized) option array. */
async function updateOptionValue(name: string, partial: Record<string, unknown>): Promise<void> {
    const current = (await getOptionValue<Record<string, unknown>>(name)) ?? {};
    await setOptionValue(name, { ...current, ...partial });
}

async function setUserMeta(userId: number | string, key: string, value: unknown): Promise<void> {
    const stored = typeof value === 'string' ? value : serialize(value);
    await dbQuery(
        `INSERT INTO ${tables.userMeta} (user_id, meta_key, meta_value) VALUES (?, ?, ?)`,
        [userId, key, stored],
    );
}

/** Delete rows from a table where a column matches a LIKE prefix (test cleanup). */
async function deleteRowsLike(table: string, column: string, prefix: string): Promise<void> {
    await dbQuery(`DELETE FROM ${table} WHERE ${column} LIKE ?`, [`${prefix}%`]);
}

async function close(): Promise<void> {
    const current = pool;
    pool = undefined; // next getPool() rebuilds it
    if (current) await current.end();
}

export const dbUtils = {
    dbQuery,
    getOptionValue,
    setOptionValue,
    updateOptionValue,
    setUserMeta,
    deleteRowsLike,
    close,
};
