export {};

/**
 * Typed view of the suite's environment (.env / CI env block).
 *
 * Everything WordPress hands us over the environment is a string — including the
 * booleans, which is why HEADLESS/CI/WP_ENV/ERP_PRO are `string` here and are read
 * through `parseBoolean()` rather than compared directly. Keys are declared, not
 * required: the index signature keeps `process.env.ANYTHING` legal (dotenv injects
 * far more than this list), while the named keys give completion and catch typos on
 * the ones the suite actually depends on.
 *
 * Seeded IDs are written back into process.env by the setup chain (_env.setup.ts),
 * so they are legitimately absent before it runs.
 */
declare global {
    namespace NodeJS {
        interface ProcessEnv {
            [key: string]: string | undefined;

            // ── Users (created by _auth.setup.ts) ──────────────────────────────
            ADMIN: string;
            ADMIN_PASSWORD: string;
            ADMIN_EMAIL: string;
            HR_MANAGER: string;
            CRM_MANAGER: string;
            ACC_MANAGER: string;
            EMPLOYEE: string;
            USER_PASSWORD: string;

            // ── REST nonces (captured per role by _auth.setup.ts) ──────────────
            X_WP_NONCE: string;
            HR_MANAGER_NONCE: string;
            CRM_MANAGER_NONCE: string;
            ACC_MANAGER_NONCE: string;

            // ── Site / run mode ────────────────────────────────────────────────
            BASE_URL: string;
            SERVER_URL: string;
            /** 'true' | 'false' — read via parseBoolean(). */
            CI: string;
            /** 'true' | 'false' — read via parseBoolean(). */
            HEADLESS: string;
            /** 'true' | 'false' — skips the setup project chain when true. */
            NO_SETUP: string;
            /** 'true' -> wp-env/Docker, 'false' -> an existing site (Valet). */
            WP_ENV: string;
            /** Filesystem root of the site when WP_ENV=false. */
            WP_ROOT: string;
            SLOWMO: string;

            // ── Pro ────────────────────────────────────────────────────────────
            /** 'true' | 'false' — drives the @pro/@liteOnly grep in the configs. */
            ERP_PRO: string;
            LICENSE_KEY: string;
            ERP_PRO_EMAIL: string;
            ERP_PRO_SUBSCRIPTION: string;
            /** JSON array of module ids, published by _site.setup.ts. */
            ERP_PRO_ACTIVE_MODULES: string;

            // ── Database (dbUtils) ─────────────────────────────────────────────
            DB_HOST_NAME: string;
            DB_USER_NAME: string;
            DB_USER_PASSWORD: string;
            DATABASE: string;
            DB_PORT: string;
            DB_PREFIX: string;

            // ── Seeded fixture IDs — written by the setup chain, absent before it ──
            EMPLOYEE_ID: string;
            DEPARTMENT_ID: string;
            DESIGNATION_ID: string;
            LEAVE_POLICY_ID: string;
            HOLIDAY_ID: string;
            CONTACT_ID: string;
            CRM_COMPANY_ID: string;
            CUSTOMER_ID: string;
            VENDOR_ID: string;
            INVOICE_ID: string;
            ACCT_PRODUCT_ID: string;
        }
    }
}
