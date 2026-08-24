#!/usr/bin/env node
/*
 * WP ERP Quality Report.
 *
 * Renders `quality-report.html` (branded, uploaded as an artifact) and
 * `quality-report.md` (appended to the GitHub job summary) from the JSON
 * reporter output of the four suites.
 *
 * Inputs (env; every one optional — a missing suite renders as "not run"
 * rather than failing the report):
 *   E2E_RESULT       playwright-report/results-e2e.json
 *   MONEY_RESULT     playwright-report/results-money.json
 *   API_RESULT       playwright-report/results-api.json
 *   LICENSE_RESULT   playwright-report/results-license.json
 *   OUTPUT_FILE      where to write the HTML
 *   SUMMARY_FILE     where to write the markdown
 *   GITHUB_RUN_ID, GITHUB_REF_NAME, GITHUB_REPOSITORY, GITHUB_SERVER_URL, SHA
 *
 * WHY THIS DOES NOT JUST COUNT PASS/FAIL
 * --------------------------------------
 * This suite deliberately contains tests that are SUPPOSED to fail: a
 * `test.fail()` guard pins a filed defect, fails while the bug is open, and
 * flips green the day it is fixed. A report that counted those as failures
 * would show ~11 red every run and train everyone to ignore it; one that
 * counted them as passes would hide the bugs. So each test is placed in one of
 * five buckets, taken from the reporter's own `expectedStatus` vs `status`:
 *
 *   passed    expected  / passed   — a normal green test
 *   guarded   expected  / failed   — a known-defect guard, still failing as documented
 *   FIXED     unexpected/ passed   — a guard that PASSED: the filed bug appears fixed
 *   failed    unexpected/ failed   — a real regression, the only bucket that gates
 *   skipped   skipped             — e.g. an integration with no credentials
 *
 * The `FIXED` bucket is the one worth watching: it is how a closed bug
 * announces itself without anyone re-running it by hand.
 */

'use strict';

const fs = require('fs');
const path = require('path');

const TEMPLATE_PATH = path.join(__dirname, 'quality-report-template.html');
const OUTPUT_FILE = process.env.OUTPUT_FILE || path.join(process.cwd(), 'quality-report.html');
const SUMMARY_FILE = process.env.SUMMARY_FILE || path.join(process.cwd(), 'quality-report.md');

/** The four suites, in the order a reader should meet them. */
const SUITES = [
    { key: 'e2e', label: 'E2E', env: 'E2E_RESULT', fallback: 'playwright-report/results-e2e.json' },
    { key: 'money', label: 'Accounting (money)', env: 'MONEY_RESULT', fallback: 'playwright-report/results-money.json' },
    { key: 'api', label: 'REST API', env: 'API_RESULT', fallback: 'playwright-report/results-api.json' },
    { key: 'license', label: 'Licence limit', env: 'LICENSE_RESULT', fallback: 'playwright-report/results-license.json' },
];

const readJson = filePath => {
    if (!filePath || !fs.existsSync(filePath)) return null;
    try {
        return JSON.parse(fs.readFileSync(filePath, 'utf8'));
    } catch (error) {
        console.warn(`quality-report: could not parse ${filePath} — ${error.message}`);
        return null;
    }
};

const formatDuration = ms => {
    if (!Number.isFinite(ms) || ms <= 0) return '—';
    const h = Math.floor(ms / 3_600_000);
    const m = Math.floor((ms % 3_600_000) / 60_000);
    const s = Math.floor((ms % 60_000) / 1000);
    return [h ? `${h}h` : '', m || h ? `${m}m` : '', `${s}s`].filter(Boolean).join(' ');
};

/** Walks the reporter's nested suites and yields every spec with its file. */
function* walkSpecs(node, file) {
    const currentFile = node.file || file;

    for (const spec of node.specs || []) {
        yield { spec, file: currentFile || spec.file || '' };
    }

    for (const child of node.suites || []) {
        yield* walkSpecs(child, currentFile);
    }
}

/** Buckets one suite's JSON into the five outcomes described at the top. */
function classify(report) {
    const out = {
        ran: Boolean(report),
        passed: 0,
        guarded: 0,
        fixed: 0,
        flaky: 0,
        failed: 0,
        skipped: 0,
        duration: 0,
        failures: [],
        flakyTests: [],
        fixedGuards: [],
        skippedTests: [],
        files: new Set(),
    };

    if (!report) return out;

    out.duration = Number(report.stats?.duration) || 0;

    for (const root of report.suites || []) {
        for (const { spec, file } of walkSpecs(root)) {
            for (const test of spec.tests || []) {
                const expected = test.expectedStatus;
                const status = test.status;
                const where = `${file}${spec.line ? `:${spec.line}` : ''}`;

                if (status === 'skipped') {
                    out.skipped++;
                    out.skippedTests.push({ title: spec.title, where });
                    continue;
                }

                out.files.add(file);

                if (expected === 'failed') {
                    // A known-defect guard. `expected` means it still fails, as
                    // documented; `unexpected` means it PASSED — the bug is fixed.
                    if (status === 'expected') {
                        out.guarded++;
                    } else {
                        out.fixed++;
                        out.fixedGuards.push({ title: spec.title, where });
                    }
                    continue;
                }

                if (status === 'expected') {
                    out.passed++;
                    continue;
                }

                const firstError = String(test.results?.[0]?.error?.message || '')
                    .split('\n')[0]
                    .slice(0, 200);

                // FLAKY is its own bucket, and adding it fixed a real
                // misreport: the reporter emits `expected`, `unexpected`,
                // `flaky` and `skipped`, and everything that was not the first
                // two used to fall into `failed`. So a test that failed once and
                // passed on retry was counted as a hard failure and stamped the
                // whole run FAILED, while Playwright — which does not fail a run
                // for flakiness — reported every job green. Run 32715510520 read
                // exactly that way.
                //
                // It is deliberately counted as NEITHER passed nor failed.
                // Calling it passed would be a fake green, and a flaky @tier1
                // money case is precisely what must not disappear into a pass
                // rate. Calling it failed misstates a run whose jobs are green.
                if (status === 'flaky') {
                    out.flaky++;
                    out.flakyTests.push({ title: spec.title, where, error: firstError });
                    continue;
                }

                out.failed++;
                out.failures.push({ title: spec.title, where, error: firstError });
            }
        }
    }

    return out;
}

const results = SUITES.map(suite => {
    const file = process.env[suite.env] || suite.fallback;
    return { ...suite, ...classify(readJson(file)) };
});

const ran = results.filter(r => r.ran);
const total = key => ran.reduce((sum, r) => sum + r[key], 0);

const totals = {
    passed: total('passed'),
    guarded: total('guarded'),
    fixed: total('fixed'),
    flaky: total('flaky'),
    failed: total('failed'),
    skipped: total('skipped'),
    duration: ran.reduce((max, r) => Math.max(max, r.duration), 0),
};

// Only real regressions gate. Guards failing as documented do not, and neither
// does a flake — the jobs are green — but a flake is NOT folded into the pass
// rate either, because "passed on the second go" is not the same claim as
// "passed", and a flaky tier-1 money case is the last thing that should be
// rounded away.
const gating = totals.failed;
const executed = totals.passed + totals.guarded + totals.fixed + totals.flaky + totals.failed;
/**
 * NEVER ROUNDED UP. A run with 477 clean results out of 478 is 99.79%, and
 * `Math.round` printed that as "100%" — next to a banner admitting one flaky
 * test. Rounding is how a report launders the single result it exists to
 * surface, so the rate is floored and only ever reads 100 when nothing at all
 * flaked, failed or needed a retry.
 */
function rateOf(clean, ofTotal) {
    if (!ofTotal) return '0';
    if (clean === ofTotal) return '100';

    return (Math.floor((clean / ofTotal) * 1000) / 10).toFixed(1);
}

const passRate = rateOf(totals.passed + totals.guarded + totals.fixed, executed);

const status = gating > 0 ? 'FAILED' : ran.length === 0 ? 'NOT RUN' : totals.flaky > 0 ? 'PASSED WITH FLAKES' : 'PASSED';
const statusIcon = gating > 0 ? '❌' : ran.length === 0 ? '⚠️' : totals.flaky > 0 ? '⚠️' : '✅';

const totalTests = executed + totals.skipped;
const totalSpecs = ran.reduce((sum, r) => sum + r.files.size, 0);

const headline =
    `${totals.passed} of ${totalTests} tests passed ` +
    `(${totals.failed} failed, ${totals.skipped} skipped, ${totals.guarded} known-defect guards` +
    (totals.flaky ? `, ${totals.flaky} flaky ⚠️` : '') +
    ')';

const verdictText =
    gating > 0
        ? `${totals.failed} FAILED · BUILD IS RED · ${passRate}% pass rate`
        : totals.flaky > 0
          ? `NO FAILURES, BUT ${totals.flaky} FLAKY · RETRY-MASKED, NOT CLEAN · ${passRate}% pass rate`
          : `ALL TESTS PASSED · BUILD IS GREEN · ${passRate}% pass rate`;

const verdictClass = gating > 0 ? 'fail' : totals.flaky > 0 ? 'warn' : 'pass';

const repo = process.env.GITHUB_REPOSITORY || 'wp-erp/wp-erp';
const server = process.env.GITHUB_SERVER_URL || 'https://github.com';
const runId = process.env.GITHUB_RUN_ID || '';
const runUrl = runId ? `${server}/${repo}/actions/runs/${runId}` : '';
const branch = process.env.GITHUB_REF_NAME || process.env.GITHUB_HEAD_REF || 'local';
const sha = (process.env.SHA || process.env.GITHUB_SHA || '').slice(0, 7) || '—';
const date = new Date().toISOString().replace('T', ' ').slice(0, 16) + ' UTC';

// ---------------------------------------------------------------------------
// HTML
// ---------------------------------------------------------------------------

const suiteRowsHtml = results
    .map(r => {
        if (!r.ran) {
            return `<tr class="muted"><td>${r.label}</td><td colspan="10">not run</td></tr>`;
        }

        const executed = r.passed + r.guarded + r.fixed + r.flaky + r.failed;
        const rate = rateOf(r.passed + r.guarded + r.fixed, executed);

        return `<tr>
            <td><strong>${r.label}</strong></td>
            <td>${executed + r.skipped}</td>
            <td class="ok">${r.passed}</td>
            <td class="guard">${r.guarded || '—'}</td>
            <td class="fixed">${r.fixed || '—'}</td>
            <td class="${r.flaky ? 'flaky' : 'muted'}">${r.flaky || '—'}</td>
            <td class="${r.failed ? 'bad' : 'ok'}">${r.failed}</td>
            <td class="muted">${r.skipped}</td>
            <td class="muted">${r.files.size}</td>
            <td class="muted">${formatDuration(r.duration)}</td>
            <td class="${rate === '100' ? 'ok' : 'flaky'}">${rate}%</td>
        </tr>`;
    })
    .join('\n');

const specMapHtml = results
    .filter(r => r.ran)
    .map(r => {
        const files = [...r.files].sort();
        const shown = files.map(f => `<code>${escapeHtml(f)}</code>`).join(' ');

        return `<tr><td><strong>${r.label}</strong></td><td>${files.length}</td><td style="text-align:left">${shown}</td></tr>`;
    })
    .join('\n') || '<tr><td colspan="3" class="muted">Nothing ran.</td></tr>';

// A donut drawn with one stroked circle per slice — inline SVG, because the page
// is a self-contained artifact and may not fetch a charting library.
function donut(slices) {
    const total = slices.reduce((sum, s) => sum + s.value, 0);
    if (!total) return '<p class="muted">No results to chart.</p>';

    const r = 70;
    const circumference = 2 * Math.PI * r;
    let offset = 0;

    const rings = slices
        .filter(s => s.value > 0)
        .map(s => {
            const len = (s.value / total) * circumference;
            const dash = `${len} ${circumference - len}`;
            const ring = `<circle cx="100" cy="100" r="${r}" fill="none" stroke="${s.color}"
                stroke-width="30" stroke-dasharray="${dash}" stroke-dashoffset="${-offset}"
                transform="rotate(-90 100 100)"><title>${s.label}: ${s.value}</title></circle>`;
            offset += len;

            return ring;
        })
        .join('');

    return `<svg viewBox="0 0 200 200" width="200" height="200" role="img" aria-label="result mix">${rings}</svg>`;
}

const slices = [
    { label: 'Passed', value: totals.passed, color: '#3fb950' },
    { label: 'Known-defect guards', value: totals.guarded, color: '#d29922' },
    { label: 'Guards now passing', value: totals.fixed, color: '#a371f7' },
    { label: 'Flaky', value: totals.flaky, color: '#db6d28' },
    { label: 'Failed', value: totals.failed, color: '#f85149' },
    { label: 'Skipped', value: totals.skipped, color: '#6e7681' },
];

const pieHtml = donut(slices);
const pieLegendHtml = slices
    .filter(s => s.value > 0)
    .map(s => `<div><span class="legend-dot" style="background:${s.color}"></span>${s.label} — <strong>${s.value}</strong></div>`)
    .join('');

const listOrNone = (items, render) =>
    items.length ? `<ul>${items.map(render).join('')}</ul>` : '<p class="muted">None.</p>';

const failuresHtml = listOrNone(
    ran.flatMap(r => r.failures.map(f => ({ ...f, suite: r.label }))),
    f => `<li><strong>${f.suite}</strong> — ${escapeHtml(f.title)}<br><code>${escapeHtml(f.where)}</code><br><span class="muted">${escapeHtml(f.error)}</span></li>`
);

const flakyHtml = listOrNone(
    ran.flatMap(r => r.flakyTests.map(f => ({ ...f, suite: r.label }))),
    f => `<li><strong>${f.suite}</strong> — ${escapeHtml(f.title)}<br><code>${escapeHtml(f.where)}</code><br><span class="muted">first attempt: ${escapeHtml(f.error)}</span></li>`
);

const fixedHtml = listOrNone(
    ran.flatMap(r => r.fixedGuards.map(f => ({ ...f, suite: r.label }))),
    f => `<li><strong>${f.suite}</strong> — ${escapeHtml(f.title)}<br><code>${escapeHtml(f.where)}</code></li>`
);

const skippedHtml = listOrNone(
    ran.flatMap(r => r.skippedTests.map(s => ({ ...s, suite: r.label }))).slice(0, 25),
    s => `<li><strong>${s.suite}</strong> — ${escapeHtml(s.title)}</li>`
);

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
}

const replacements = {
    STATUS: status,
    STATUS_ICON: statusIcon,
    STATUS_CLASS: gating > 0 ? 'failed' : 'passed',
    PASS_RATE: String(passRate),
    TOTAL_PASSED: String(totals.passed),
    TOTAL_GUARDED: String(totals.guarded),
    TOTAL_FIXED: String(totals.fixed),
    TOTAL_FLAKY: String(totals.flaky),
    TOTAL_FAILED: String(totals.failed),
    TOTAL_SKIPPED: String(totals.skipped),
    TOTAL_EXECUTED: String(executed),
    TOTAL_TESTS: String(totalTests),
    TOTAL_SPECS: String(totalSpecs),
    HEADLINE: headline,
    VERDICT_TEXT: verdictText,
    VERDICT_CLASS: verdictClass,
    FAILED_BADGE: totals.failed ? 'bad' : 'ok',
    FLAKY_BADGE: totals.flaky ? 'warn' : 'ok',
    PIE: pieHtml,
    PIE_LEGEND: pieLegendHtml,
    SPEC_MAP: specMapHtml,
    DURATION: formatDuration(totals.duration),
    SUITE_ROWS: suiteRowsHtml,
    FAILURES: failuresHtml,
    FLAKY_LIST: flakyHtml,
    FIXED_GUARDS: fixedHtml,
    SKIPPED_LIST: skippedHtml,
    BRANCH_NAME: branch,
    COMMIT_HASH: sha,
    RUN_LINK: runUrl ? `<a href="${runUrl}">#${runId}</a>` : 'local run',
    DATE: date,
};

if (fs.existsSync(TEMPLATE_PATH)) {
    let html = fs.readFileSync(TEMPLATE_PATH, 'utf8');

    for (const [key, value] of Object.entries(replacements)) {
        html = html.replaceAll(`{{${key}}}`, value);
    }

    fs.mkdirSync(path.dirname(OUTPUT_FILE), { recursive: true });
    fs.writeFileSync(OUTPUT_FILE, html);
    console.log(`quality-report: wrote ${OUTPUT_FILE}`);
} else {
    console.warn(`quality-report: template missing at ${TEMPLATE_PATH} — HTML not written`);
}

// ---------------------------------------------------------------------------
// Markdown, for the GitHub job summary (GH strips CSS, so this is plain)
// ---------------------------------------------------------------------------

const md = [];

md.push(`## ${statusIcon} WP ERP QA — Quality Report`);
md.push('');
md.push(`Branch \`${branch}\` · Commit \`${sha}\` · ${date}${runUrl ? ` · [run #${runId}](${runUrl})` : ''}`);
md.push('');
md.push(`> **${statusIcon} ${status}** — ${headline}`);
md.push(`> pass rate **${passRate}%** of the ${executed} tests run · ${formatDuration(totals.duration)}`);
md.push('');
md.push('### 📊 Key Metrics');
md.push('');
md.push('| Total | Passed | Guards | Fixed | Flaky | Failed | Skipped | Specs |');
md.push('|---:|---:|---:|---:|---:|---:|---:|---:|');
md.push(
    `| ${totalTests} | ${totals.passed} | ${totals.guarded} | ${totals.fixed || '—'} | ` +
        `${totals.flaky || '—'} | ${totals.failed} | ${totals.skipped} | ${totalSpecs} |`
);
md.push('');
md.push('### 🧪 Test Suites');
md.push('');
md.push('| Suite | Total | Passed | Guarded | Fixed | Flaky | Failed | Skipped | Specs | Duration | Pass rate |');
md.push('|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|');

for (const r of results) {
    if (!r.ran) {
        md.push(`| ${r.label} | — | — | — | — | — | — | — | — | — | not run |`);
        continue;
    }

    const ex = r.passed + r.guarded + r.fixed + r.flaky + r.failed;
    md.push(
        `| ${r.label} | ${ex + r.skipped} | ${r.passed} | ${r.guarded || '—'} | ${r.fixed || '—'} | ` +
            `${r.flaky || '—'} | ${r.failed} | ${r.skipped} | ${r.files.size} | ${formatDuration(r.duration)} | ` +
            `${rateOf(r.passed + r.guarded + r.fixed, ex)}% |`
    );
}

md.push('');

if (totals.flaky) {
    md.push('### ⚠️ Flaky Tests');
    md.push('');
    md.push('Passed on retry — a green run here is retry-masked, not clean. Each failed first and only passed on a second attempt, so it is counted on its own and deliberately **not** folded into the pass rate.');
    md.push('');
    for (const r of ran) {
        for (const f of r.flakyTests) md.push(`- **${r.label}** — ${f.title} \`${f.where}\`  \n  first attempt: ${f.error}`);
    }
    md.push('');
}

if (totals.fixed) {
    md.push('### 🎉 Guards that passed — these filed bugs look fixed');
    md.push('');
    md.push('A `test.fail()` guard passing means the defect it pins no longer reproduces. Verify, then close the issue and drop the guard.');
    md.push('');
    for (const r of ran) {
        for (const f of r.fixedGuards) md.push(`- **${r.label}** — ${f.title} \`${f.where}\``);
    }
    md.push('');
}

if (totals.failed) {
    md.push('### ❌ Failures');
    md.push('');
    for (const r of ran) {
        for (const f of r.failures) {
            md.push(`- **${r.label}** — ${f.title}`);
            md.push(`  \`${f.where}\``);
            if (f.error) md.push(`  > ${f.error}`);
        }
    }
    md.push('');
}

md.push('<details><summary>What the columns mean</summary>');
md.push('');
md.push('- **Passed** — normal green tests.');
md.push('- **Guarded** — `test.fail()` tests pinning a filed defect. They fail on purpose while the bug is open and are **not** a regression; they flip to *Fixed* the day it is resolved.');
md.push('- **Fixed** — a guard that passed. The filed bug appears fixed: confirm, close it, remove the guard.');
md.push('- **Failed** — the only bucket that gates a run.');
md.push('- **Skipped** — declined with a recorded reason, e.g. an integration with no sandbox credentials. Never counted as a pass.');
md.push('');
md.push('</details>');

fs.mkdirSync(path.dirname(SUMMARY_FILE), { recursive: true });
fs.writeFileSync(SUMMARY_FILE, md.join('\n'));
console.log(`quality-report: wrote ${SUMMARY_FILE}`);

// Never gate the workflow on report generation itself.
process.exit(0);
