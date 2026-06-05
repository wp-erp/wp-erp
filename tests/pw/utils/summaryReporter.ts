import { mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import type { Reporter, TestCase, TestResult, FullResult } from '@playwright/test/reporter';

/** Writes a small machine-readable run summary to summary-report/results.json. */
export default class SummaryReporter implements Reporter {
    private counts = { total: 0, passed: 0, failed: 0, skipped: 0, flaky: 0, timedOut: 0 };

    onTestEnd(_test: TestCase, result: TestResult): void {
        this.counts.total += 1;
        const status = result.status;
        if (status === 'passed') this.counts.passed += 1;
        else if (status === 'failed') this.counts.failed += 1;
        else if (status === 'skipped') this.counts.skipped += 1;
        else if (status === 'timedOut') this.counts.timedOut += 1;
        if (result.retry > 0 && status === 'passed') this.counts.flaky += 1;
    }

    onEnd(result: FullResult): void {
        const dir = resolve(process.cwd(), 'summary-report');
        mkdirSync(dir, { recursive: true });
        writeFileSync(resolve(dir, 'results.json'), JSON.stringify({ status: result.status, ...this.counts }, null, 2));
    }
}
