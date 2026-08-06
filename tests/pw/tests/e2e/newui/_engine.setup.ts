import { test as setup, expect } from '@utils/test';
import { exeCommandWpcli } from '@utils/helpers';

/**
 * Pin the HR admin to the React engine for the New UI suite.
 *
 * Counterpart to the `vue` pin in `_site.setup.ts`: the engine is a single site-wide
 * option (`UiEngineResolver::SITE_OPTION`), so the two suites cannot share one site
 * concurrently — which is why the New UI runs from its own config (`newui.config.ts`)
 * and its own CI job rather than as another project alongside the legacy specs.
 *
 * Fails loudly when the option will not take: on a build WITHOUT the redesign there is
 * no React engine to test, and a newui run there should stop here rather than produce a
 * wall of "React root never mounted" failures that look like product bugs.
 */
setup.describe('HR New UI engine', () => {
    setup('pin the HR admin to the React engine', { tag: ['@lite'] }, async () => {
        exeCommandWpcli('option update erp_hr_ui_engine react');
        expect(exeCommandWpcli('option get erp_hr_ui_engine').trim(), 'HR UI engine pinned to react').toBe('react');
    });
});
