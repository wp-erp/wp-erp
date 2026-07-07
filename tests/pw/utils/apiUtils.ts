import type { APIRequestContext, APIResponse } from '@playwright/test';
import { request } from '@playwright/test';
import { BASE_URL } from './helpers';
import { endPoints } from './apiEndPoints';
import type { ReqOptions, ResponseBody, Headers } from './interfaces';

/**
 * Thin wrapper over Playwright's APIRequestContext for WP ERP REST.
 *
 * Auth model: cookie + nonce. The context is built from a logged-in admin
 * storageState (cookies), and every write carries `X-WP-Nonce` (set in .env by
 * the auth setup). Verb wrappers return `[response, body]` and assert ok() by
 * default; pass `assert = false` to allow expected failures (e.g. duplicates).
 */
export class ApiUtils {
    readonly request: APIRequestContext;
    private readonly nonce?: string;

    constructor(requestContext: APIRequestContext, nonce?: string) {
        this.request = requestContext;
        this.nonce = nonce;
    }

    /**
     * Build an authed context from a saved role storageState. Pass that role's own
     * X-WP-Nonce — a nonce is tied to the user, so the admin nonce will not
     * authenticate a manager/employee session (the request would be treated as
     * logged-out). Falls back to process.env.X_WP_NONCE (admin) when omitted.
     */
    static async fromStorageState(storageState: string, nonce?: string): Promise<ApiUtils> {
        const ctx = await request.newContext({ baseURL: BASE_URL, storageState, ignoreHTTPSErrors: true });
        return new ApiUtils(ctx, nonce);
    }

    async dispose(): Promise<void> {
        await this.request.dispose();
    }

    private authHeaders(extra: Headers = {}): Headers {
        const nonce = this.nonce ?? process.env.X_WP_NONCE ?? '';
        return { 'Content-Type': 'application/json', ...(nonce ? { 'X-WP-Nonce': nonce } : {}), ...extra };
    }

    /** Merge auth headers into the request options without losing them. */
    private buildOptions(options?: ReqOptions): ReqOptions {
        const { headers, ...rest } = options ?? {};
        return { ...rest, headers: this.authHeaders(headers) };
    }

    private async getResponseBody(response: APIResponse, assert: boolean): Promise<ResponseBody> {
        let body: ResponseBody;
        try {
            body = await response.json();
        } catch {
            body = await response.text();
        }
        if (assert && !response.ok()) {
            throw new Error(`API request failed: ${response.status()} ${response.url()}\n${JSON.stringify(body)}`);
        }
        return body;
    }

    async get(url: string, options?: ReqOptions, assert = true): Promise<[APIResponse, ResponseBody]> {
        const response = await this.request.get(url, this.buildOptions(options));
        return [response, await this.getResponseBody(response, assert)];
    }

    async post(url: string, options?: ReqOptions, assert = true): Promise<[APIResponse, ResponseBody]> {
        const response = await this.request.post(url, this.buildOptions(options));
        return [response, await this.getResponseBody(response, assert)];
    }

    async put(url: string, options?: ReqOptions, assert = true): Promise<[APIResponse, ResponseBody]> {
        const response = await this.request.put(url, this.buildOptions(options));
        return [response, await this.getResponseBody(response, assert)];
    }

    async patch(url: string, options?: ReqOptions, assert = true): Promise<[APIResponse, ResponseBody]> {
        const response = await this.request.patch(url, this.buildOptions(options));
        return [response, await this.getResponseBody(response, assert)];
    }

    async delete(url: string, options?: ReqOptions, assert = true): Promise<[APIResponse, ResponseBody]> {
        const response = await this.request.delete(url, this.buildOptions(options));
        return [response, await this.getResponseBody(response, assert)];
    }

    // ── Generic create helper: POST a payload, return [body, id] ──────────────
    async create(url: string, payload: unknown, assert = true): Promise<[ResponseBody, string]> {
        const [, body] = await this.post(url, { data: payload }, assert);
        return [body, String(body?.id ?? '')];
    }

    // ── WordPress users (used by the auth setup to create role accounts) ──────
    async createUser(payload: Record<string, unknown>): Promise<[ResponseBody, string]> {
        // idempotent: if the username/email exists, look it up and return its id
        const [res, body] = await this.post(endPoints.users, { data: payload }, false);
        if (res.ok()) return [body, String(body?.id ?? '')];
        const username = String(payload.username ?? '');
        const [, list] = await this.get(`${endPoints.users}?search=${encodeURIComponent(username)}&context=edit`, undefined, false);
        const existing = Array.isArray(list) ? list.find((u: any) => u?.username === username || u?.slug === username) : undefined;
        return [existing ?? body, String(existing?.id ?? '')];
    }
}
