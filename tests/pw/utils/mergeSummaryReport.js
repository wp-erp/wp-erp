/**
 * mergeSummaryReport.js
 *
 * Plain-Node (CommonJS) merge script for WP ERP QA Playwright shards.
 *
 * Recursively scans ARTIFACTS_DIR for `results.json` files whose path contains
 * REPORT_MATCH, then merges the per-shard summaries into one combined summary.
 *
 * Merge math (mirrors Dokan's mergeSummaryReport.ts):
 *   - total_tests/passed/failed/skipped/flaky  -> SUM across shards
 *   - suite_duration                           -> MAX across shards
 *     (shards run in parallel, so wall-clock ~= the longest shard)
 *   - status                                   -> 'failed' if ANY shard failed,
 *                                                 else 'passed'
 *
 * Tolerant by design: missing/malformed files are skipped, and a run with zero
 * matching results logs a warning but exits 0 (does NOT hard-fail).
 *
 * Env contract:
 *   ARTIFACTS_DIR  - root dir to scan          (default './all-reports')
 *   REPORT_MATCH   - substring path filter     (default 'test-artifact-e2e')
 *   MERGED_OUTPUT  - merged JSON output path    (default <ARTIFACTS_DIR>/merged-summary.json)
 */

'use strict';

const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = process.env.ARTIFACTS_DIR || './all-reports';
const REPORT_MATCH = process.env.REPORT_MATCH || 'test-artifact-e2e';
const MERGED_OUTPUT = process.env.MERGED_OUTPUT || path.join(ARTIFACTS_DIR, 'merged-summary.json');

/**
 * Format a millisecond duration as `1h 2m 3s` (same style as Dokan's
 * getFormattedDuration). Zero-valued leading units are omitted.
 *
 * @param {number} milliseconds
 * @returns {string}
 */
function formatDuration(milliseconds) {
    const ms = Number(milliseconds) || 0;
    const hours = Math.floor(ms / (1000 * 60 * 60));
    const minutes = Math.floor((ms / (1000 * 60)) % 60);
    const seconds = Math.floor((ms / 1000) % 60);
    return `${hours < 1 ? '' : hours + 'h '}${minutes < 1 ? '' : minutes + 'm '}${seconds < 1 ? '' : seconds + 's'}`;
}

/**
 * Recursively collect every `results.json` whose full path contains
 * REPORT_MATCH. Unreadable directories are skipped silently.
 *
 * @param {string} dir
 * @param {string[]} found
 * @returns {string[]}
 */
function findResults(dir, found) {
    let entries;
    try {
        entries = fs.readdirSync(dir);
    } catch (err) {
        // Directory missing or unreadable — nothing to collect here.
        return found;
    }

    for (const entry of entries) {
        const fullPath = path.join(dir, entry);
        let stat;
        try {
            stat = fs.statSync(fullPath);
        } catch (err) {
            continue; // Skip anything we can't stat (broken symlink, race, etc.).
        }

        if (stat.isDirectory()) {
            findResults(fullPath, found);
        } else if (entry === 'results.json' && fullPath.includes(REPORT_MATCH)) {
            found.push(fullPath);
        }
    }

    return found;
}

/**
 * Parse and merge the collected per-shard summaries.
 *
 * @param {string[]} reportPaths
 * @returns {object} merged summary
 */
function mergeReports(reportPaths) {
    const merged = {
        status: 'passed',
        total_tests: 0,
        passed: 0,
        failed: 0,
        skipped: 0,
        flaky: 0,
        suite_duration: 0,
        suite_duration_formatted: '',
        shards: 0,
    };

    for (const reportPath of reportPaths) {
        let report;
        try {
            report = JSON.parse(fs.readFileSync(reportPath, 'utf8'));
        } catch (err) {
            console.warn(`Skipping unreadable/malformed report: ${reportPath} (${err.message})`);
            continue;
        }

        if (!report || typeof report !== 'object') {
            console.warn(`Skipping non-object report: ${reportPath}`);
            continue;
        }

        // SUM the per-shard counts. Missing fields default to 0.
        merged.total_tests += Number(report.total_tests) || 0;
        merged.passed += Number(report.passed) || 0;
        merged.failed += Number(report.failed) || 0;
        merged.skipped += Number(report.skipped) || 0;
        merged.flaky += Number(report.flaky) || 0;

        // MAX the suite duration — shards run in parallel, so the wall-clock
        // duration is approximated by the longest-running shard.
        merged.suite_duration = Math.max(merged.suite_duration, Number(report.suite_duration) || 0);

        // ANY failing shard fails the whole run.
        if (report.status === 'failed') {
            merged.status = 'failed';
        }

        merged.shards += 1;
    }

    merged.suite_duration_formatted = formatDuration(merged.suite_duration);

    return merged;
}

function main() {
    const reportPaths = findResults(ARTIFACTS_DIR, []);

    if (reportPaths.length === 0) {
        console.warn(
            `Warning: no results.json found under '${ARTIFACTS_DIR}' matching '${REPORT_MATCH}'. Nothing to merge.`
        );
        process.exit(0);
    }

    const merged = mergeReports(reportPaths);

    if (merged.shards === 0) {
        console.warn('Warning: all matched results.json files were unreadable/malformed. Nothing merged.');
        process.exit(0);
    }

    try {
        fs.mkdirSync(path.dirname(MERGED_OUTPUT), { recursive: true });
    } catch (err) {
        // Best-effort; writeFileSync below will surface a real failure.
    }
    fs.writeFileSync(MERGED_OUTPUT, JSON.stringify(merged, null, 2), 'utf8');

    console.log(
        `Merged ${merged.shards} shard(s) -> ${MERGED_OUTPUT} | status=${merged.status} ` +
            `total=${merged.total_tests} passed=${merged.passed} failed=${merged.failed} ` +
            `skipped=${merged.skipped} flaky=${merged.flaky} duration=${merged.suite_duration_formatted}`
    );
}

main();

module.exports = { formatDuration, findResults, mergeReports };
