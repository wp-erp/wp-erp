import { test as setup } from '@utils/test';
import { parseBoolean, exeCommandWpcli } from '@utils/helpers';

/**
 * Provisions the wp-env site: activate plugins, pretty permalinks, timezone.
 * Skipped entirely in external-site mode (WP_ENV=false, e.g. Valet) — those
 * sites are assumed already provisioned.
 */
setup.describe('local site provisioning', () => {
    setup.skip(!parseBoolean(process.env.WP_ENV, true), 'wp-env only — skipped for external sites');

    setup('activate plugins, permalinks & timezone', { tag: ['@lite'] }, async () => {
        // Lite provisioning only. erp-pro is activated as an explicit, trackable
        // @pro step in _site.setup.ts (Dokan-style), not silently here.
        exeCommandWpcli('plugin activate wp-erp');
        exeCommandWpcli('rewrite structure "/%postname%/" --hard');
        exeCommandWpcli('rewrite flush --hard');
        exeCommandWpcli('option update timezone_string "UTC"');
    });
});
