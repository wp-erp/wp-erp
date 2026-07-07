/** Shared types for the WP ERP Playwright suite. */

export type Headers = Record<string, string>;

/** Options forwarded to APIRequestContext verbs (JSON REST). */
export interface ReqOptions {
    data?: unknown;
    headers?: Headers;
    params?: Record<string, string | number | boolean>;
    failOnStatusCode?: boolean;
    ignoreHTTPSErrors?: boolean;
    timeout?: number;
}

export type ResponseBody = any;

/** A WP user/role login. */
export interface UserCredentials {
    username: string;
    password: string;
}

/** Map of seeded entity IDs written back to .env via createEnvVar. */
export type IdMap = Record<string, string>;

/** Roles the suite logs in as. */
export type Role = 'admin' | 'hrManager' | 'crmManager' | 'accManager' | 'employee';

export interface AuthFiles {
    admin: string;
    hrManager: string;
    crmManager: string;
    accManager: string;
    employee: string;
}
