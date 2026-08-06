#!/usr/bin/env node
/*
 * Resolves the spec list for a given Playwright shard using a duration baseline.
 *
 * Usage: node getShardSpecs.js <shardIndex> <shardTotal>
 *
 * Strategy:
 *   1. Read tests/pw/utils/shard-durations.json (committed baseline of spec → ms).
 *   2. Discover every *.spec.ts under tests/e2e (so newly-added specs are not lost).
 *   3. Greedy bin-pack longest-first into N bins.
 *   4. Print spec paths (one per line, relative to tests/pw) for the requested shard.
 *
 * If the baseline is missing, exits 0 with no output so the workflow can fall
 * back to Playwright's built-in --shard.
 */

'use strict';

const fs = require('fs');
const path = require('path');

const [, , shardIndexArg, shardTotalArg] = process.argv;
const shardIndex = parseInt(shardIndexArg, 10);
const shardTotal = parseInt(shardTotalArg, 10);
if (!shardIndex || !shardTotal || shardIndex < 1 || shardIndex > shardTotal) {
    console.error(`usage: getShardSpecs.js <shardIndex> <shardTotal>  (got ${shardIndexArg}/${shardTotalArg})`);
    process.exit(2);
}

const pwRoot = path.resolve(__dirname, '..');
const baselinePath = path.join(__dirname, 'shard-durations.json');
const e2eRoot = path.join(pwRoot, 'tests', 'e2e');

if (!fs.existsSync(baselinePath)) {
    // No baseline → fall back to alphabetical sharding upstream.
    process.exit(0);
}

const baseline = JSON.parse(fs.readFileSync(baselinePath, 'utf8'));
const baselineByFile = new Map();
for (const entry of baseline.specs || []) {
    baselineByFile.set(entry.file, entry.ms || 0);
}

// Discover all spec files (so additions are caught even before baseline updates).
function walkSpecs(dir, base = 'tests/e2e') {
    const out = [];
    for (const name of fs.readdirSync(dir)) {
        const full = path.join(dir, name);
        const stat = fs.statSync(full);
        if (stat.isDirectory()) {
            out.push(...walkSpecs(full, path.join(base, name)));
        } else if (name.endsWith('.spec.ts')) {
            // Path relative to e2e/ root, matching summaryReporter file paths.
            const rel = path.relative(e2eRoot, full);
            out.push(rel);
        }
    }
    return out;
}

if (!fs.existsSync(e2eRoot)) {
    console.error(`e2e dir not found: ${e2eRoot}`);
    process.exit(0);
}

const allSpecs = walkSpecs(e2eRoot)
    .map(file => ({
        file,
        // Default newly-added (unmeasured) specs to the global mean so they
        // don't all stack in a single bin.
        ms: baselineByFile.has(file) ? baselineByFile.get(file) : null,
    }));

const measured = allSpecs.filter(s => s.ms !== null);
const meanMs = measured.length
    ? measured.reduce((a, b) => a + b.ms, 0) / measured.length
    : 0;
for (const s of allSpecs) {
    if (s.ms === null) s.ms = meanMs;
}

// Sort longest-first
allSpecs.sort((a, b) => b.ms - a.ms);

// Greedy bin-pack — assign each spec to the currently-lightest bin.
const bins = Array.from({ length: shardTotal }, () => ({ ms: 0, files: [] }));
for (const spec of allSpecs) {
    bins.sort((a, b) => a.ms - b.ms);
    bins[0].ms += spec.ms;
    bins[0].files.push(spec.file);
}
// After sorting, bins are no longer in original order. Make assignment stable
// by re-sorting deterministically (descending by total ms, then by first file).
bins.sort((a, b) => b.ms - a.ms || a.files[0].localeCompare(b.files[0]));

const myBin = bins[shardIndex - 1];
// Sort files within the bin alphabetically for stable test order in CI logs.
myBin.files.sort();

// Print paths relative to tests/pw, which is what `npx playwright test` expects
// when invoked from tests/pw cwd.
for (const f of myBin.files) {
    console.log(path.posix.join('tests', 'e2e', f.split(path.sep).join('/')));
}
