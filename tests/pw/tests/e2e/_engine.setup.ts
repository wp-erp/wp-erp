import { test as setup, expect } from '@utils/test';
import { exeCommandWpcli } from '@utils/helpers';

/**
 * Pin the HR admin to the legacy (Vue) engine for the main e2e suite.
 *
 * Counterpart to `tests/e2e/newui/_engine.setup.ts`, which pins `react`. The engine is
 * one site-wide option, so whichever suite ran last leaves it set — and this project is
 * deliberately NOT gated by NO_SETUP, so `npm run test:e2e` straight after a newui run
 * flips it back instead of failing on Vue selectors the React screens never render.
 *
 * Required, not defensive: UiEngineResolver serves React by default on a NEW install,
 * which is exactly what CI creates. Measured: with React active, 61 of these specs fail
 * (e.g. #payrun-wrapper not found) and all pass again on `vue`. No-op on a build without
 * the redesign — nothing reads the option.
 */
setup.describe('HR admin engine', () => {
    setup('pin the HR admin to the legacy (Vue) engine', { tag: ['@lite'] }, async () => {
        exeCommandWpcli('option update erp_hr_ui_engine vue');
        expect(exeCommandWpcli('option get erp_hr_ui_engine').trim(), 'HR UI engine pinned to vue').toBe('vue');
    });
});
