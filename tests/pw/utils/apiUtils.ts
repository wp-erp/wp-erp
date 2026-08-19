/**
 * REST client for seeding, teardown and the API suite.
 *
 * Every call goes through `?rest_route=` so the suite works whether or not the
 * site has pretty permalinks, and authenticates with Basic-Auth (the
 * WP-API/Basic-Auth plugin wp-env installs).
 */
import { APIRequestContext, APIResponse } from '@playwright/test';
import { basicAuth, env, restPath } from '@utils/helpers';
import { endPoints } from '@utils/apiEndPoints';
import { ErpLicenseStatus, ErpUserCount } from '@utils/interfaces';

export type Auth = Record<string, string>;

export const adminAuth: Auth = basicAuth(env('ADMIN', 'admin'), env('ADMIN_PASSWORD', 'password'));

export class ApiUtils {
    constructor(private readonly request: APIRequestContext) {}

    /** Releases the underlying request context. Always call it in a finally. */
    async dispose(): Promise<void> {
        await this.request.dispose();
    }

    // ---- primitives -------------------------------------------------------

    async get(route: string, auth: Auth = adminAuth, params: Record<string, string | number> = {}): Promise<APIResponse> {
        return this.request.get(restPath(route, params), { headers: auth });
    }

    async post(route: string, data: unknown, auth: Auth = adminAuth): Promise<APIResponse> {
        return this.request.post(restPath(route), { headers: auth, data: data as Record<string, unknown> });
    }

    async put(route: string, data: unknown, auth: Auth = adminAuth): Promise<APIResponse> {
        return this.request.put(restPath(route), { headers: auth, data: data as Record<string, unknown> });
    }

    async delete(route: string, auth: Auth = adminAuth): Promise<APIResponse> {
        return this.request.delete(restPath(route), { headers: auth });
    }

    /** GET returning parsed JSON, throwing on a non-2xx so seeding fails loudly. */
    async getJson<T>(route: string, auth: Auth = adminAuth, params: Record<string, string | number> = {}): Promise<T> {
        const response = await this.get(route, auth, params);
        if (!response.ok()) {
            throw new Error(`GET ${route} -> ${response.status()} ${await response.text()}`);
        }
        return (await response.json()) as T;
    }

    async postJson<T>(route: string, data: unknown, auth: Auth = adminAuth): Promise<T> {
        const response = await this.post(route, data, auth);
        if (!response.ok()) {
            throw new Error(`POST ${route} -> ${response.status()} ${await response.text()}`);
        }
        return (await response.json()) as T;
    }

    // ---- test-helper endpoints -------------------------------------------

    private testKey(): Record<string, string> {
        return { erp_test_key: env('ERP_TEST_KEY', 'erp-pw-local') };
    }

    async licenseState(): Promise<{ license: unknown; license_status: ErpLicenseStatus | false }> {
        return this.getJson(endPoints.testHelper.license, adminAuth, this.testKey());
    }

    /** The product's own user count — the number the 100-seat rule enforces on. */
    async userCount(): Promise<ErpUserCount> {
        return this.getJson(endPoints.testHelper.userCount, adminAuth, this.testKey());
    }

    async fireCron(hook: string): Promise<APIResponse> {
        return this.request.post(restPath(endPoints.testHelper.cron, { ...this.testKey(), hook }), { headers: adminAuth });
    }

    async roleNotice(userId: number): Promise<{ notice: string | false }> {
        return this.getJson(endPoints.testHelper.notices, adminAuth, { ...this.testKey(), user_id: userId });
    }

    /** Writes allow-listed options (woocommerce_*, erp_*, blog*) via the helper. */
    async setOptions(options: Record<string, unknown>): Promise<{ written: string[]; refused: string[] }> {
        const response = await this.request.post(restPath(endPoints.testHelper.options, this.testKey()), { headers: adminAuth, data: { options } });
        if (!response.ok()) throw new Error(`setOptions -> ${response.status()} ${await response.text()}`);
        const result = (await response.json()) as { written: string[]; refused: string[] };
        if (result.refused.length) throw new Error(`setOptions refused (not on the allow-list): ${result.refused.join(', ')}`);
        return result;
    }

    async getOptions(names: string[]): Promise<Record<string, unknown>> {
        return this.getJson(endPoints.testHelper.options, adminAuth, { ...this.testKey(), names: names.join(',') });
    }

    async flushCache(): Promise<void> {
        await this.request.post(restPath(endPoints.testHelper.flushCache, this.testKey()), { headers: adminAuth });
    }

    async phpErrors(): Promise<{ lines: string[]; fatal?: string[] }> {
        return this.getJson(endPoints.testHelper.phpErrors, adminAuth, this.testKey());
    }

    // ---- ERP Pro modules --------------------------------------------------

    async activeModules(): Promise<unknown> {
        return this.getJson(endPoints.pro.modules);
    }

    async activateModule(module: string): Promise<APIResponse> {
        return this.post(endPoints.pro.activateModule, { module });
    }

    async deactivateModule(module: string): Promise<APIResponse> {
        return this.post(endPoints.pro.deactivateModule, { module });
    }

    // ---- user seeding (test-helper mu-plugin) -----------------------------
    //
    // WP core's /wp/v2/users rejects ERP roles with rest_user_invalid_role
    // because they are absent from get_editable_roles(); the helper endpoints
    // call wp_insert_user directly.

    async seedUser(data: { login: string; email: string; password: string; role: string }): Promise<{ id: number; created: boolean; roles: string[] }> {
        return this.request
            .post(restPath(endPoints.testHelper.seedUser, this.testKey()), { headers: adminAuth, data })
            .then(async (response) => {
                if (!response.ok()) throw new Error(`seedUser ${data.login} -> ${response.status()} ${await response.text()}`);
                return response.json() as Promise<{ id: number; created: boolean; roles: string[] }>;
            });
    }

    async seedUsersBulk(data: { prefix: string; role: string; count: number; password?: string }): Promise<{ created: number; ids: number[] }> {
        const response = await this.request.post(restPath(endPoints.testHelper.seedUsersBulk, this.testKey()), { headers: adminAuth, data, timeout: 300_000 });
        if (!response.ok()) throw new Error(`seedUsersBulk -> ${response.status()} ${await response.text()}`);
        return response.json() as Promise<{ created: number; ids: number[] }>;
    }

    async cleanupUsers(prefix: string): Promise<{ deleted: number }> {
        const response = await this.request.post(restPath(endPoints.testHelper.cleanupUsers, this.testKey()), { headers: adminAuth, data: { prefix }, timeout: 300_000 });
        if (!response.ok()) throw new Error(`cleanupUsers ${prefix} -> ${response.status()} ${await response.text()}`);
        return response.json() as Promise<{ deleted: number }>;
    }

    async deleteUser(id: number, reassign = 1): Promise<APIResponse> {
        return this.request.delete(restPath(endPoints.wp.user(id), { force: 'true', reassign }), { headers: adminAuth });
    }
}
