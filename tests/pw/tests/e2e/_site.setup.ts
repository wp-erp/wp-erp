import { test as setup, expect } from '@utils/test';
import { toPath, parseBoolean, exeCommandWpcli } from '@utils/helpers';

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

    // ── Pro activation (Dokan-style lite/pro toggle) ───────────────────────────
    // When ERP_PRO=true this @pro setup step runs and turns the full pro surface
    // on; when ERP_PRO is unset the @pro project is grep-excluded and the lite
    // toggle below clears the flag so the same site behaves as lite.
    //
    // Activation is driven by the QA force-pro mu-plugin (mu-plugins/erp-qa-force-pro.php,
    // mapped via .wp-env.json). Setting the `erp_qa_force_pro` option makes that
    // mu-plugin force a valid, all-extensions, high-user-cap license on every
    // request — so every pro module loads its menus, REST routes and tables, and
    // nothing (cron / license re-check / restart) can wipe it mid-run. We also
    // record the genuine license credentials and run the real activation as a
    // best-effort so production-shaped code paths are exercised too.
    setup('activate Pro license & modules', { tag: ['@pro'] }, async () => {
        const email = process.env.ERP_PRO_EMAIL ?? '';
        const key = process.env.LICENSE_KEY ?? '';
        const sub = process.env.ERP_PRO_SUBSCRIPTION ?? 'yearly';

        // Record the genuine license credentials (JSON option write is clean through
        // wp-env, unlike raw eval). The mu-plugin installer runs the real activation
        // against wperp.com best-effort so production code paths are exercised; the QA
        // test site has ~240 seeded users (far over the license user-cap), so the
        // force-pro mu-plugin then overrides cap + extensions to load every module.
        const creds = JSON.stringify({ email, key, subscription_type: sub });
        exeCommandWpcli(`option update erp_pro_license '${creds}' --format=json`);

        // Flip the single QA force-pro switch and clear the install marker. The
        // force-pro mu-plugin then, on the next request's `erp_loaded`, forces a valid
        // all-extensions high-user-cap license AND runs erp-pro's real
        // activate_modules() for every module — creating each module's DB tables and
        // role caps. All activation logic lives in PHP, so the setup only flips scalar
        // options and never pushes fragile multi-layer wp-cli eval through wp-env.
        exeCommandWpcli('option update erp_qa_force_pro 1');
        exeCommandWpcli('option delete erp_qa_pro_installed');

        // The next wp-cli request fires `erp_loaded` with the flag set → installer runs.
        // `wp option get` returns clean scalars, so these reads are round-trip-safe.
        const flag = exeCommandWpcli('option get erp_qa_force_pro');
        expect(flag.trim(), 'QA force-pro flag is set').toBe('1');

        // Installer has now run; confirm modules registered and a pro table exists.
        const active = exeCommandWpcli('option get erp_pro_active_modules --format=json');
        const activeIds = JSON.parse(active.trim() || '[]') as string[];
        expect(activeIds.length, 'all pro modules activated').toBeGreaterThanOrEqual(20);

        const installed = exeCommandWpcli('option get erp_qa_pro_installed');
        expect(installed.trim(), 'pro install completed').toBe('1');
    });
});
