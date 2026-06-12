import { existsSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

/**
 * Runs once before everything. Truncates the wp-env debug log (bind-mounted at
 * ./wp-data/debug.log) so each run starts with a clean log to inspect for PHP
 * notices/errors. No-op when the file is absent (e.g. Valet mode).
 */
async function globalSetup(): Promise<void> {
    const debugLog = resolve(process.cwd(), 'wp-data', 'debug.log');
    if (existsSync(debugLog)) {
        writeFileSync(debugLog, '');
    }
}

export default globalSetup;
