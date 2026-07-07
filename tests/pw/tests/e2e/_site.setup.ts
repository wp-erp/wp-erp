import { test as setup, expect } from '@utils/test';
import { toPath, parseBoolean, exeCommandWpcli, createEnvVar } from '@utils/helpers';

/**
 * Site-level readiness that applies in BOTH wp-env and external-site modes.
 * Per-module fixtures are delegated to each module's seed() (in _env.setup).
 */
setup.describe('ERP site readiness', () => {
    setup('activate CRM & Accounting modules', { tag: ['@lite'] }, async () => {
        // WP ERP enables only HRM by default. Activate CRM + Accounting so their
        // REST routes and features load (their DB tables already exist from
        // plugin activation). Uses WP ERP's own activate_modules() for correctness.
        exeCommandWpcli('eval \'wperp()->modules->activate_modules(["crm","accounting"]);\'');

        // Dokan-style lite/pro toggle: when this run is NOT a pro run, clear the
        // QA force-pro flag so the mu-plugin leaves the site as lite (pro modules
        // dormant). The @pro step below re-enables it for pro runs.
        if (!parseBoolean(process.env.ERP_PRO)) {
            exeCommandWpcli('eval \'delete_option("erp_qa_force_pro");\'');
        }
    });

    setup('site responds and WP ERP admin is reachable', { tag: ['@lite'] }, async ({ page }) => {
        const resp = await page.goto(toPath('wp-login.php'));
        expect(resp?.status() ?? 200, 'login page should load').toBeLessThan(400);
    });

    setup('pro flag sanity', { tag: ['@pro'] }, async () => {
        expect(parseBoolean(process.env.ERP_PRO), 'ERP_PRO must be true for @pro runs').toBe(true);
    });

    // ── Pro activation — separate, explicit, trackable @pro steps (Dokan-style) ─
    // Mirrors Dokan's _site.setup.ts: a distinct block of @pro steps that activate
    // the Pro plugin, set the license, activate the modules, then verify — each its
    // own named setup() so it renders as a discrete node in the Playwright report
    // (the "separate, trackable pro setup"). When ERP_PRO is unset these @pro steps
    // are grep-excluded and the lite toggle above clears the flag so the same site
    // behaves as lite.
    //
    // ONE wp-erp-specific divergence from Dokan: erp-pro's license is user-cap-gated
    // and periodically re-checked, and this QA site seeds ~240 users (over the cap),
    // so a plainly-written license is rejected and Pro modules would never load. The
    // erp-qa-force-pro.php mu-plugin (mapped via .wp-env.json) forges a valid,
    // all-extensions, high-cap license at the `option_erp_pro_license_status` READ
    // layer so nothing (cron / re-check / restart) can wipe it mid-run, and runs
    // erp-pro's real activate_modules() once on `erp_loaded`. Dokan needs no such
    // shim because its license has no user-cap gate.

    setup('activate erp-pro plugin', { tag: ['@pro'] }, async () => {
        try {
            exeCommandWpcli('plugin activate erp-pro');
        } catch {
            /* already active, or auto-activated via the wp-env plugins[] (override/ci) */
        }
        const status = exeCommandWpcli('plugin get erp-pro --field=status');
        expect(status.trim(), 'erp-pro must be active for @pro runs').toBe('active');
    });

    setup('set erp pro license', { tag: ['@pro'] }, async () => {
        const email = process.env.ERP_PRO_EMAIL ?? '';
        const key = process.env.LICENSE_KEY ?? '';
        const sub = process.env.ERP_PRO_SUBSCRIPTION ?? 'yearly';

        // Record the genuine credentials (clean JSON option write through wp-env). The
        // mu-plugin installer best-effort runs the real activation against wperp.com so
        // production license code paths run; the force-filter validates regardless.
        const creds = JSON.stringify({ email, key, subscription_type: sub });
        exeCommandWpcli(`option update erp_pro_license '${creds}' --format=json`);

        // Flip the QA force-pro switch and clear the install marker so the mu-plugin
        // (re)installs every module on the next `erp_loaded`. Setup only flips scalar
        // options — never fragile multi-layer wp-cli eval through wp-env.
        exeCommandWpcli('option update erp_qa_force_pro 1');
        exeCommandWpcli('option delete erp_qa_pro_installed');

        const flag = exeCommandWpcli('option get erp_qa_force_pro');
        expect(flag.trim(), 'QA force-pro flag is set').toBe('1');
    });

    setup('activate all erp pro modules', { tag: ['@pro'] }, async () => {
        // Each wp-cli call re-bootstraps WP and fires `erp_loaded`; with the flag set
        // and the marker cleared above, the mu-plugin runs erp-pro's real
        // activate_modules() for the full set (creating each module's tables + caps).
        // This read both triggers and verifies it.
        //
        // NOTE: not every pro module self-activates on a clean site — accounting/CRM and
        // the third-party integrations (woocommerce, deals, reimbursement, hubspot,
        // mailchimp, …) only register when WooCommerce or their host plugin is present.
        // So assert the HRM-pro modules the e2e suite actually drives are active, not a
        // brittle total count.
        const active = exeCommandWpcli('option get erp_pro_active_modules --format=json');
        const activeIds = JSON.parse(active.trim() || '[]') as string[];
        expect(activeIds.length, 'pro modules activated').toBeGreaterThan(0);
        for (const id of ['attendance', 'asset_management']) {
            expect(activeIds, `${id} module active`).toContain(id);
        }

        // Publish the active-module list so @pro specs can SKIP (not fail) when their
        // module is inactive in this env (e.g. woocommerce/awesome_support need a host
        // plugin). Read via helpers.proModuleActive(). Applies on the NEXT run too
        // (workers load .env at start), and immediately for this run's later projects.
        createEnvVar('ERP_PRO_ACTIVE_MODULES', activeIds.join(','));
    });

    setup('verify pro install completed', { tag: ['@pro'] }, async () => {
        // The one-time installer (mu-plugin, on erp_loaded) ran activate_modules() so
        // each active module's DB tables + role caps now exist; the marker proves it.
        const installed = exeCommandWpcli('option get erp_qa_pro_installed');
        expect(installed.trim(), 'pro install completed (tables + caps created)').toBe('1');
    });
});
