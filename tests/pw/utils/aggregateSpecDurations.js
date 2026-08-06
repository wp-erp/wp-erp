#!/usr/bin/env node
/*
 * Merges per-shard spec-durations.json files into a single baseline file.
 *
 * Usage: node aggregateSpecDurations.js <reportsDir> <outputPath>
 *
 *   reportsDir   directory containing per-shard test-artifact-* folders
 *                (matches actions/download-artifact pattern in CI)
 *   outputPath   path to write the merged baseline
 *
 * Each shard's spec-durations.json has its own subset of specs; we sum the
 * duration where a spec ran on multiple shards (shouldn't normally happen).
 */

'use strict';

const fs = require('fs');
const path = require('path');

const [, , reportsDir, outputPath] = process.argv;
if (!reportsDir || !outputPath) {
    console.error('usage: aggregateSpecDurations.js <reportsDir> <outputPath>');
    process.exit(2);
}

if (!fs.existsSync(reportsDir)) {
    console.error(`reportsDir does not exist: ${reportsDir}`);
    process.exit(1);
}

const merged = new Map();
const shardFiles = [];

function walk(dir) {
    for (const name of fs.readdirSync(dir)) {
        const full = path.join(dir, name);
        const stat = fs.statSync(full);
        if (stat.isDirectory()) {
            walk(full);
        } else if (name === 'spec-durations.json') {
            shardFiles.push(full);
        }
    }
}
walk(reportsDir);

if (shardFiles.length === 0) {
    console.error('no spec-durations.json files found; skipping');
    process.exit(0);
}

for (const f of shardFiles) {
    try {
        const r = JSON.parse(fs.readFileSync(f, 'utf8'));
        for (const entry of r.specs || []) {
            const cur = merged.get(entry.file) || { ms: 0, tests: 0 };
            cur.ms += entry.ms || 0;
            cur.tests += entry.tests || 0;
            merged.set(entry.file, cur);
        }
    } catch (e) {
        console.warn(`skipping malformed ${f}: ${e.message}`);
    }
}

const specs = Array.from(merged.entries())
    .map(([file, v]) => ({ file, ms: v.ms, tests: v.tests }))
    .sort((a, b) => b.ms - a.ms);

const out = { generatedAt: new Date().toISOString(), shards: shardFiles.length, specs };
fs.mkdirSync(path.dirname(outputPath), { recursive: true });
fs.writeFileSync(outputPath, JSON.stringify(out, null, 2));
console.log(`wrote ${specs.length} specs from ${shardFiles.length} shards → ${outputPath}`);
