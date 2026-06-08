import { mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import 'dotenv/config';

/**
 * Runs once after everything. Writes a small run-info file. Kept best-effort so
 * it never fails a run (e.g. when nothing seeded the IDs).
 */
async function globalTeardown(): Promise<void> {
    try {
        const dir = resolve(process.cwd(), 'playwright');
        mkdirSync(dir, { recursive: true });
        const info = {
            baseUrl: process.env.BASE_URL,
            erpPro: process.env.ERP_PRO,
            wpEnv: process.env.WP_ENV,
            seeded: {
                EMPLOYEE_ID: process.env.EMPLOYEE_ID,
                DEPARTMENT_ID: process.env.DEPARTMENT_ID,
                CONTACT_ID: process.env.CONTACT_ID,
                CUSTOMER_ID: process.env.CUSTOMER_ID,
                INVOICE_ID: process.env.INVOICE_ID,
            },
        };
        writeFileSync(resolve(dir, 'systemInfo.json'), JSON.stringify(info, null, 2));
    } catch {
        /* ignore */
    }
}

export default globalTeardown;
