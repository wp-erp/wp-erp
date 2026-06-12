import { mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import type { Reporter, Suite, FullResult } from '@playwright/test/reporter';

/** Formats a millisecond duration as '1h 2m 3s' (omits leading zero units). */
function formatDuration(ms: number): string {
    const totalSeconds = Math.floor(ms / 1000);
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;
    const parts: string[] = [];
    if (h > 0) parts.push(`${h}h`);
    if (h > 0 || m > 0) parts.push(`${m}m`);
    parts.push(`${s}s`);
    return parts.join(' ');
}

/**
 * Writes a machine-readable run summary to summary-report/results.json — the input for
 * the Dokan-style quality report (generateQualityReport.js).
 *
 * Counts FINAL per-test outcomes via TestCase.outcome(), NOT per-attempt result.status.
 * A FLAKY test (failed an attempt, then passed on retry) is counted as flaky AND as a
 * pass — NEVER as a failure. The previous per-attempt counting incremented `failed` for
 * the failed attempt of a flaky test, so a green run with retries reported failed > 0 and
 * the quality-report banner wrongly read "Tests failed".
 *
 * Emits the short keys the suite relied on (total/passed/failed/skipped/flaky) plus the
 * aligned keys the quality report reads (total_tests / suite_duration[_formatted]).
 */
export default class SummaryReporter implements Reporter {
    private suite!: Suite;
    private startTime = 0;

    onBegin(_config: unknown, suite: Suite): void {
        this.suite = suite;
        // Stamp wall-clock start; finalized against the runtime clock in onEnd.
        this.startTime = Date.now();
    }

    onEnd(result: FullResult): void {
        const counts = { total: 0, passed: 0, failed: 0, skipped: 0, flaky: 0 };
        const passed_tests: string[] = [];
        const failed_tests: string[] = [];
        const skipped_tests: string[] = [];

        for (const test of this.suite.allTests()) {
            // outcome() is the FINAL verdict after all retries (one per test, not per
            // attempt): 'skipped' | 'expected' | 'unexpected' | 'flaky'.
            switch (test.outcome()) {
                case 'expected':
                    counts.passed += 1;
                    passed_tests.push(test.title);
                    break;
                case 'flaky':
                    // Eventually passed → a pass, plus a flaky tally. NEVER a failure.
                    counts.passed += 1;
                    counts.flaky += 1;
                    passed_tests.push(test.title);
                    break;
                case 'unexpected':
                    counts.failed += 1;
                    failed_tests.push(test.title);
                    break;
                case 'skipped':
                    counts.skipped += 1;
                    skipped_tests.push(test.title);
                    break;
            }
        }
        counts.total = counts.passed + counts.failed + counts.skipped;

        const suiteDuration = Date.now() - this.startTime;
        const dir = resolve(process.cwd(), 'summary-report');
        mkdirSync(dir, { recursive: true });
        writeFileSync(
            resolve(dir, 'results.json'),
            JSON.stringify(
                {
                    status: result.status,
                    ...counts,
                    total_tests: counts.total,
                    suite_duration: suiteDuration,
                    suite_duration_formatted: formatDuration(suiteDuration),
                    passed_tests,
                    failed_tests,
                    skipped_tests,
                },
                null,
                2
            )
        );
    }
}
