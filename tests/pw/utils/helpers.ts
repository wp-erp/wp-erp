import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { execSync } from 'node:child_process';
import { expect, type Page } from '@playwright/test';
import 'dotenv/config';

/**
 * Shared, plugin-agnostic helpers for the WP ERP Playwright suite.
 * Env vars are always strings, so booleans go through {@link parseBoolean}.
 */

export function parseBoolean(value: string | undefined, defaultValue = false): boolean {
    if (value === undefined || value === '') return defaultValue;
    const v = value.trim().toLowerCase();
    return v === 'true' || v === '1' || v === 'yes' || v === 'on';
}

export const BASE_URL = process.env.BASE_URL ?? 'http://localhost:9999';

/** REST root. `?rest_route=` form works without pretty permalinks (Docker-safe). */
export const SERVER_URL = process.env.SERVER_URL ?? `${BASE_URL}/?rest_route=`;

/** Build an absolute site URL: toPath('wp-admin/admin.php?page=erp-hr'). */
export function toPath(...segments: string[]): string {
    const path = segments.join('/').replace(/^\/+/, '');
    return `${BASE_URL.replace(/\/+$/, '')}/${path}`;
}

/** Build a full REST URL from a route like '/erp/v1/hrm/employees'. */
export function restUrl(route: string): string {
    const r = route.startsWith('/') ? route : `/${route}`;
    return `${SERVER_URL}${r}`;
}

/** Path to the repo-root .env that setup specs read & write. */
const ENV_PATH = resolve(process.cwd(), '.env');

/**
 * Upsert a KEY=value into process.env AND the .env file, so IDs/nonces seeded by
 * the setup chain are visible to later test projects (which read process.env).
 */
export function createEnvVar(key: string, value: string): void {
    process.env[key] = value;
    let content = existsSync(ENV_PATH) ? readFileSync(ENV_PATH, 'utf8') : '';
    const re = new RegExp(`^${key}=.*$`, 'gm');
    if (re.test(content)) {
        content = content.replace(re, `${key}=${value}`);
    } else {
        const sep = content.length === 0 || content.endsWith('\n') ? '' : '\n';
        content = `${content}${sep}${key}=${value}\n`;
    }
    writeFileSync(ENV_PATH, content);
}

/**
 * Run a wp-cli command. Routes through `wp-env run` in Docker mode, or a local
 * `wp` binary against WP_ROOT when WP_ENV=false (e.g. a Valet site).
 */
export function exeCommandWpcli(command: string): string {
    const useWpEnv = parseBoolean(process.env.WP_ENV, true);
    const wpRoot = process.env.WP_ROOT;
    const full = useWpEnv
        ? `npx wp-env run cli -- wp ${command}`
        : `wp ${wpRoot ? `--path="${wpRoot}"` : ''} ${command}`;
    return execSync(full, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] }).trim();
}

/**
 * Ensure a WP user exists with the given role, via wp-cli. Used for role accounts
 * because some WP ERP roles (e.g. erp_hr_manager) are excluded from
 * get_editable_roles(), so the REST users endpoint refuses to assign them.
 * Returns the user ID.
 */
export function ensureUser(username: string, email: string, role: string, password: string): string {
    try {
        return exeCommandWpcli(`user create ${username} ${email} --role=${role} --user_pass=${password} --porcelain`).trim();
    } catch {
        const id = exeCommandWpcli(`user get ${username} --field=ID`).trim();
        if (id) {
            exeCommandWpcli(`user set-role ${id} ${role}`);
        }
        return id;
    }
}

/** Log a user into wp-admin and (optionally) persist the session as storageState. */
export async function login(
    page: Page,
    username: string = process.env.ADMIN ?? 'admin',
    password: string = process.env.ADMIN_PASSWORD ?? 'password',
    storageStatePath?: string,
): Promise<void> {
    await page.goto(toPath('wp-login.php'));
    await page.locator('#user_login').fill(username);
    await page.locator('#user_pass').fill(password);
    await page.locator('#wp-submit').click();
    await expect(page.locator('#wpadminbar')).toBeVisible({ timeout: 30_000 });
    if (storageStatePath) {
        await page.context().storageState({ path: storageStatePath });
    }
}

/**
 * Read the WP REST nonce from a logged-in admin page (WP ERP admin screens
 * localize `wpApiSettings`). Used to authenticate REST writes via X-WP-Nonce.
 */
export async function getApiNonce(page: Page, landing = 'wp-admin/admin.php?page=erp-hr'): Promise<string> {
    // Read the wp_rest nonce from wpApiSettings on an ERP admin page the current
    // user can actually reach. A role-scoped manager (e.g. erp_ac_manager) is not
    // authorized for the HR page, so the caller must point this at that role's own
    // module page (erp-accounting / erp-crm), else wpApiSettings is absent → ''.
    await page.goto(toPath(landing));
    const nonce = await page.evaluate(() => {
        const w = window as unknown as { wpApiSettings?: { nonce?: string } };
        return w.wpApiSettings?.nonce ?? '';
    });
    return String(nonce ?? '');
}

export const helpers = {
    parseBoolean,
    toPath,
    restUrl,
    createEnvVar,
    exeCommandWpcli,
    ensureUser,
    login,
    getApiNonce,
    BASE_URL,
    SERVER_URL,
};
