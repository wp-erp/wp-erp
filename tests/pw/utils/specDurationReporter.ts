import { mkdirSync, writeFileSync } from 'node:fs';
import { relative, resolve } from 'node:path';
import type { Reporter, TestCase, TestResult } from '@playwright/test/reporter';

/**
 * Logs the slowest specs at the end of a run, and writes the per-file totals to
 * `playwright/spec-durations.json`.
 *
 * That file is the raw material for balanced sharding: CI uploads one per shard,
 * `aggregateSpecDurations.js` merges them, and the merged result is committed as
 * `utils/shard-durations.json` — the baseline `getShardSpecs.js` bin-packs from.
 * Paths are recorded relative to `tests/e2e` so they match what getShardSpecs
 * discovers on disk.
 */
export default class SpecDurationReporter implements Reporter {
    private durations: { title: string; ms: number }[] = [];
    private byFile = new Map<string, { ms: number; tests: number }>();

    onTestEnd(test: TestCase, result: TestResult): void {
        this.durations.push({ title: test.titlePath().slice(1).join(' › '), ms: result.duration });

        const file = relative(resolve(process.cwd(), 'tests', 'e2e'), test.location.file).split('\\').join('/');
        // Setup/teardown projects and anything outside tests/e2e (api specs run from
        // their own config) are not shardable by getShardSpecs — skip them.
        if (file.startsWith('..')) return;
        const cur = this.byFile.get(file) ?? { ms: 0, tests: 0 };
        cur.ms += result.duration;
        cur.tests += 1;
        this.byFile.set(file, cur);
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

        if (this.byFile.size === 0) return;
        const specs = [...this.byFile.entries()]
            .map(([file, v]) => ({ file, ms: v.ms, tests: v.tests }))
            .sort((a, b) => b.ms - a.ms);
        const dir = resolve(process.cwd(), 'playwright');
        mkdirSync(dir, { recursive: true });
        writeFileSync(resolve(dir, 'spec-durations.json'), JSON.stringify({ specs }, null, 2));
    }
}
