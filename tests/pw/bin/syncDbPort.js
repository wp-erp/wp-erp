#!/usr/bin/env node
/* eslint-disable */
// Syncs DB_PORT in .env to the wp-env DEVELOPMENT MySQL container's host port.
//
// The dev MySQL port is now PINNED to 1001 via "mysqlPort" in .wp-env.json (tests MySQL
// is left random, so there is no dev/tests collision). This script reads the live
// dev-MySQL host port from Docker and writes it into .env anyway, so DB_PORT stays
// correct even if someone overrides the port locally (.wp-env.override.json) or runs an
// older config — dbUtils (mysql2) and the CRM/DB seeders then connect to the right port.
//
// Runs automatically after `npm run start:env` (npm `poststart:env` lifecycle) and
// can be run on demand with `npm run db:port`.
require('dotenv/config');
const { execSync } = require('node:child_process');
const { existsSync, readFileSync, writeFileSync } = require('node:fs');
const { resolve } = require('node:path');

const ENV_PATH = resolve(process.cwd(), '.env');

function devMysqlPort() {
    let out;
    try {
        out = execSync('docker ps --format "{{.Names}} {{.Ports}}"', { encoding: 'utf8' });
    } catch {
        return null; // Docker not running / not the wp-env provider
    }
    // The dev MySQL container is "*-mysql-1" (NOT "*-tests-mysql-1").
    const line = out
        .split('\n')
        .find((l) => /-mysql-1\b/.test(l) && !/-tests-mysql-1\b/.test(l));
    if (!line) return null;
    // e.g. "...-mysql-1 0.0.0.0:52779->3306/tcp, [::]:52779->3306/tcp"
    const m = line.match(/(?:0\.0\.0\.0|127\.0\.0\.1):(\d+)->3306\/tcp/);
    return m ? m[1] : null;
}

function upsertEnv(key, value) {
    let content = existsSync(ENV_PATH) ? readFileSync(ENV_PATH, 'utf8') : '';
    const line = `${key}=${value}`;
    if (new RegExp(`^${key}=.*$`, 'm').test(content)) {
        content = content.replace(new RegExp(`^${key}=.*$`, 'm'), line);
    } else {
        content += (content.endsWith('\n') || content === '' ? '' : '\n') + line + '\n';
    }
    writeFileSync(ENV_PATH, content);
}

const port = devMysqlPort();
if (!port) {
    console.warn(
        '[syncDbPort] Could not detect the wp-env dev MySQL port from Docker. ' +
            'Leaving DB_PORT as-is — set it manually if DB-backed specs fail.',
    );
    process.exit(0);
}

upsertEnv('DB_PORT', port);
console.log(`[syncDbPort] DB_PORT set to ${port} (wp-env dev MySQL).`);
