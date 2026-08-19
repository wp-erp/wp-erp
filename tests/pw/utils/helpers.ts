import { faker } from '@faker-js/faker';

/**
 * Env values arrive as strings; 'false'/'0'/'' must all read as false.
 */
export function parseBoolean(value: string | boolean | undefined): boolean {
    if (typeof value === 'boolean') return value;
    if (value === undefined) return false;
    return ['true', '1', 'yes', 'on'].includes(value.trim().toLowerCase());
}

/** Env var or a fallback. Throws only when a required value is missing. */
export function env(key: string, fallback?: string): string {
    const value = process.env[key];
    if (value === undefined || value === '') {
        if (fallback !== undefined) return fallback;
        throw new Error(`Missing required environment variable: ${key}`);
    }
    return value;
}

/** True when an optional env var carries a usable value. */
export function hasEnv(key: string): boolean {
    const value = process.env[key];
    return value !== undefined && value.trim() !== '';
}

/** ERP admin screens are all admin.php?page=… — this keeps that in one place. */
export function adminPath(page: string, params: Record<string, string | number> = {}): string {
    const query = new URLSearchParams({ page, ...Object.fromEntries(Object.entries(params).map(([k, v]) => [k, String(v)])) });
    return `/wp-admin/admin.php?${query.toString()}`;
}

/** REST url built the ?rest_route= way, which works with or without pretty permalinks. */
export function restPath(route: string, params: Record<string, string | number> = {}): string {
    const query = new URLSearchParams({ rest_route: route.startsWith('/') ? route : `/${route}`, ...Object.fromEntries(Object.entries(params).map(([k, v]) => [k, String(v)])) });
    return `/?${query.toString()}`;
}

/** Basic-Auth header for the REST suite (WP-API/Basic-Auth plugin). */
export function basicAuth(username: string, password: string): { Authorization: string } {
    return { Authorization: `Basic ${Buffer.from(`${username}:${password}`).toString('base64')}` };
}

/** Test data that is unique per run and instantly recognisable in the DB. */
export const PREFIX = 'pwerp';

export function uniqueId(suffix = ''): string {
    return `${PREFIX}_${suffix}${faker.string.nanoid(6)}`.toLowerCase();
}

/**
 * A unique but NAME-SAFE token: letters only.
 *
 * ERP validates person names with `erp_is_valid_name()`
 * (wp-erp/includes/functions.php:3756), which rejects digits, `_` and most
 * punctuation — so `uniqueId()` (which contains both) is refused with the alert
 * "Please provide a valid last name".
 */
export function uniqueName(prefix = 'Qa'): string {
    const letters = 'abcdefghijklmnopqrstuvwxyz';
    let token = '';
    for (let i = 0; i < 7; i++) token += letters[Math.floor(Math.random() * letters.length)];
    return `${prefix}${token.charAt(0).toUpperCase()}${token.slice(1)}`;
}

export function uniqueEmail(suffix = ''): string {
    return `${uniqueId(suffix)}@example.test`;
}

/**
 * YYYY-MM-DD, the format every ERP date field posts.
 *
 * Formatted from LOCAL components, never `toISOString()`. A `Date` built with
 * `setDate()`/`getDay()` carries local calendar fields, and `toISOString()`
 * converts to UTC before slicing — so on any timezone ahead of UTC, a date
 * created between midnight and the offset comes out as the PREVIOUS day. On
 * UTC+6 that turned a local Monday into the Sunday before it, silently, for
 * runs started between 00:00 and 06:00 only. It cost two leave specs and looked
 * exactly like a product defect in working-day counting.
 */
export function toDate(date: Date = new Date()): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

export function dateOffset(days: number, from: Date = new Date()): string {
    const d = new Date(from);
    d.setDate(d.getDate() + days);
    return toDate(d);
}

/**
 * The Monday at least `weeksAhead` weeks from today, as YYYY-MM-DD.
 *
 * Leave day counts exclude non-working days, so a range anchored on an
 * arbitrary offset is not deterministic — Thu–Sat counts 2 days, not 3.
 * Anchoring on Monday makes Mon–Wed reliably three working days.
 */
export function upcomingMonday(weeksAhead = 2): string {
    const date = new Date();
    date.setDate(date.getDate() + weeksAhead * 7);
    // 0 = Sunday … 1 = Monday
    const shift = (8 - date.getDay()) % 7;
    date.setDate(date.getDate() + (shift === 0 ? 7 : shift));
    return toDate(date);
}

/**
 * A DATE column from mysql2, as YYYY-MM-DD.
 *
 * The driver hydrates `DATE`/`DATETIME` columns into JS `Date` objects, so
 * `String(row.trn_date)` yields "Thu Aug 20 2026 00:00:00 GMT+0600 (…)" and a
 * comparison against an ISO string fails for a row that is perfectly correct.
 * Formatting goes through `toDate()` so it inherits the local-components rule.
 */
export function dbDate(value: unknown): string {
    if (value instanceof Date) return toDate(value);
    return String(value ?? '').slice(0, 10);
}

/** N days after the given YYYY-MM-DD. */
export function daysAfter(isoDate: string, days: number): string {
    const date = new Date(`${isoDate}T00:00:00Z`);
    date.setUTCDate(date.getUTCDate() + days);
    return date.toISOString().slice(0, 10);
}

/** Strips ERP's currency formatting so amounts can be compared as numbers. */
export function toNumber(text: string | null | undefined): number {
    if (!text) return 0;
    const cleaned = text.replace(/[^0-9.-]/g, '');
    return cleaned === '' ? 0 : Number(cleaned);
}
