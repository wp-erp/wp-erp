#!/usr/bin/env node
/*
 * Scores a run against feature-map/feature-map.yml and writes coverage.json (+ a
 * short markdown block for the CI job summary).
 *
 *   node ./utils/generateCoverageReport.js
 *
 * Env (all optional):
 *   FEATURE_MAP   default feature-map/feature-map.yml
 *   SUMMARY_FILE  default summary-report/results.json  (merged summary in CI)
 *   OUTPUT_FILE   default coverage-report/coverage.json
 *   SUMMARY_MD    default coverage-report/coverage.md
 *   ERP_PRO       'true' scores pro features too; otherwise only '[lite]' entries
 *
 * A feature counts as COVERED only when a test with that exact title PASSED or
 * FAILED in the run — a skipped test proves nothing, so it stays uncovered. The
 * number answers "what did we exercise", not "what did we write".
 *
 * This is a standalone script rather than a Playwright teardown project on purpose:
 * CI runs e2e in 4 shards, and a per-shard teardown would score each shard against
 * the whole map and report every other shard's features as gaps. It runs once, in
 * merge-reports, against the merged summary.
 */

'use strict';

const fs = require('fs');
const path = require('path');
const yaml = require('js-yaml');

const featureMapPath = process.env.FEATURE_MAP || 'feature-map/feature-map.yml';
const summaryPath = process.env.SUMMARY_FILE || 'summary-report/results.json';
const outputPath = process.env.OUTPUT_FILE || 'coverage-report/coverage.json';
const summaryMdPath = process.env.SUMMARY_MD || 'coverage-report/coverage.md';
const isPro = String(process.env.ERP_PRO).toLowerCase() === 'true';

if (!fs.existsSync(featureMapPath)) {
    console.error(`no feature map at ${featureMapPath} — nothing to score`);
    process.exit(0);
}

// Absent summary means nothing executed. Report 0%, never "everything passed".
const executed = new Set();
if (fs.existsSync(summaryPath)) {
    const summary = JSON.parse(fs.readFileSync(summaryPath, 'utf8'));
    for (const title of [...(summary.passed_tests || []), ...(summary.failed_tests || [])]) {
        executed.add(String(title));
    }
} else {
    console.warn(`no summary at ${summaryPath} — every feature will score as uncovered`);
}

const pages = yaml.load(fs.readFileSync(featureMapPath, 'utf8')) || [];
const covered = [];
const uncovered = [];
const pageCoverage = {};

for (const page of pages) {
    let total = 0;
    let hit = 0;
    walk(page.features || {}, (label, expected) => {
        const liteOnly = label.includes('[lite]');
        // On a lite run, pro-only features are out of scope, not failures.
        if (!isPro && !liteOnly) return;
        const title = label.replace(' [lite]', '');
        total++;
        if (expected && executed.has(title)) {
            hit++;
            covered.push(title);
        } else {
            uncovered.push(title);
        }
    });
    if (total > 0) pageCoverage[page.page] = round((hit / total) * 100);
}

const totalFeatures = covered.length + uncovered.length;
const pct = totalFeatures ? round((covered.length / totalFeatures) * 100) : 0;
const report = {
    mode: isPro ? 'pro' : 'lite',
    total_features: totalFeatures,
    total_covered_features: covered.length,
    coverage: `${pct}%`,
    page_coverage: pageCoverage,
    covered_features: covered.sort(),
    uncovered_features: uncovered.sort(),
};

writeOut(outputPath, JSON.stringify(report, null, 2));

const rows = Object.entries(pageCoverage)
    .sort((a, b) => a[1] - b[1])
    .map(([page, p]) => `| ${page} | ${p}% |`)
    .join('\n');
writeOut(
    summaryMdPath,
    `### Feature coverage (${report.mode}) — ${report.coverage}\n\n` +
        `${covered.length} of ${totalFeatures} mapped features were exercised ` +
        `(a skipped test counts as uncovered).\n\n` +
        `| Area | Coverage |\n|---|---|\n${rows}\n`
);

console.log(`coverage (${report.mode}): ${report.coverage} — ${covered.length}/${totalFeatures} features`);

function walk(node, visit) {
    for (const [key, value] of Object.entries(node)) {
        if (value !== null && typeof value === 'object') walk(value, visit);
        else visit(key, Boolean(value));
    }
}

function writeOut(file, contents) {
    fs.mkdirSync(path.dirname(file), { recursive: true });
    fs.writeFileSync(file, contents);
}

function round(n) {
    return Math.round(n * 100) / 100;
}
