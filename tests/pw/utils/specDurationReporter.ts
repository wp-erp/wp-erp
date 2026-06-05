import type { Reporter, TestCase, TestResult } from '@playwright/test/reporter';

/** Logs the slowest specs at the end of a run (lightweight timing insight). */
export default class SpecDurationReporter implements Reporter {
    private durations: { title: string; ms: number }[] = [];

    onTestEnd(test: TestCase, result: TestResult): void {
        this.durations.push({ title: test.titlePath().slice(1).join(' › '), ms: result.duration });
    }

    onEnd(): void {
        const top = [...this.durations].sort((a, b) => b.ms - a.ms).slice(0, 10);
        if (top.length === 0) return;
        // eslint-disable-next-line no-console
        console.log('\nSlowest specs:');
        for (const d of top) {
            // eslint-disable-next-line no-console
            console.log(`  ${(d.ms / 1000).toFixed(1)}s  ${d.title}`);
        }
    }
}
