#!/usr/bin/env node
/* eslint-disable */
// Creates/ensures the admin user inside the wp-env container from .env values.
require('dotenv/config');
const { execSync } = require('node:child_process');

const ADMIN = process.env.ADMIN || 'admin';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'password';
const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'wordpress@example.com';

function wp(cmd) {
    return execSync(`npx wp-env run tests-cli -- wp ${cmd}`, { stdio: 'inherit' });
}

try {
    wp(`user get ${ADMIN}`);
    console.log(`Admin "${ADMIN}" already exists.`);
} catch {
    wp(`user create ${ADMIN} ${ADMIN_EMAIL} --role=administrator --user_pass=${ADMIN_PASSWORD}`);
    console.log(`Created admin "${ADMIN}".`);
}
