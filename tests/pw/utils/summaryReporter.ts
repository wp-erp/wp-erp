import { mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import type { Reporter, TestCase, TestResult, FullResult } from '@playwright/test/reporter';

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

/** Writes a small machine-readable run summary to summary-report/results.json. */
export default class SummaryReporter implements Reporter {
    private counts = { total: 0, passed: 0, failed: 0, skipped: 0, flaky: 0, timedOut: 0 };
    private titles = { passed_tests: [] as string[], failed_tests: [] as string[], skipped_tests: [] as string[] };
    private startTime = 0;

    onBegin(): void {
        // Stamp wall-clock start; finalized against the runtime clock in onEnd.
        this.startTime = Date.now();
    }

    onTestEnd(test: TestCase, result: TestResult): void {
        this.counts.total += 1;
        const status = result.status;
        if (status === 'passed') {
            this.counts.passed += 1;
            this.titles.passed_tests.push(test.title);
        } else if (status === 'failed') {
            this.counts.failed += 1;
            this.titles.failed_tests.push(test.title);
        } else if (status === 'skipped') {
            this.counts.skipped += 1;
            this.titles.skipped_tests.push(test.title);
        } else if (status === 'timedOut') {
            // A timeout is a failure: count it under timedOut and failed.
            this.counts.timedOut += 1;
            this.counts.failed += 1;
            this.titles.failed_tests.push(test.title);
        }
        if (result.retry > 0 && status === 'passed') this.counts.flaky += 1;
    }

    onEnd(result: FullResult): void {
        const suiteDuration = Date.now() - this.startTime;
        const dir = resolve(process.cwd(), 'summary-report');
        mkdirSync(dir, { recursive: true });
        // Emit the short keys the suite already relied on, plus the aligned keys
        // the Dokan-style quality report reads (total_tests, suite_duration, etc.).
        writeFileSync(
            resolve(dir, 'results.json'),
            JSON.stringify(
                {
                    status: result.status,
                    ...this.counts,
                    total_tests: this.counts.total,
                    suite_duration: suiteDuration,
                    suite_duration_formatted: formatDuration(suiteDuration),
                    ...this.titles,
                },
                null,
                2
            )
        );
    }
}
