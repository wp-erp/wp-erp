import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';

/**
 * Feature-isolated page object for the PRO accounting **Payment Gateway** settings
 * SAVE lifecycle — feature `acctProSettings` (module: accounting).
 *
 * Surface decision (live-confirmed in Discover):
 *  - The Payment Gateway add-on ships NO /erp/v1 REST route. It hooks into the
 *    legacy server-rendered ERP Settings framework via the `erp_get_sections_erp-ac`
 *    + `erp_settings_acct_section_fields` filters and persists to wp_options. The
 *    ONLY save path is the admin-ajax action `erp-settings-save`.
 *  - That handler (`includes/Settings/Ajax.php::erp_settings_save()`) verifies the
 *    page-localized nonce `erp_settings_var.nonce` (created via
 *    `wp_create_nonce('erp-settings-nonce')` in `includes/Settings/Assets.php:87`).
 *
 * So this POM drives surface (2) UI-driven: `page.goto` the Settings SPA in the
 * real browser session, scrape `window.erp_settings_var.nonce` (coherent with the
 * page's session token), then POST to admin-ajax via `page.request` (which reuses
 * the page's cookies). DB effects are asserted via `dbUtils.getOptionValue`.
 *
 * Each Payment field id persists as its OWN wp_options row because the accounting
 * Settings class runs with `single_option = true`
 * (`includes/Settings/Template.php::save()` line 175 — the single-option branch
 * `update_option( $name, $value )` per id). Checkbox ids normalize to 'yes' when
 * present and 'no' when the key is omitted (Template.php::parse_option_value()
 * checkbox branch, lines 206-214).
 *
 * Field ids are taken verbatim from the pro source:
 *   Gateways/Stripe.php:65-127  (erp_pg_stripe_*)
 *   Gateways/Paypal.php:48-86   (erp_pg_paypal_*)
 *   GeneralSettings.php:40-49   (erp_pg_payment_account_head)
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

/** Shape of the admin-ajax `erp-settings-save` JSON envelope (success or error). */
export interface SaveResult {
    /** HTTP status of the admin-ajax POST (200 for both success and the soft errors). */
    status: number;
    /** Raw response body text (admin-ajax dies with '0' for the unprivileged/anon path). */
    raw: string;
    /** Parsed `{ success, data }` envelope when the body is JSON, else undefined. */
    json?: { success: boolean; data?: unknown };
}

export class AcctProSettingsPage {
    readonly page: Page;

    constructor( page: Page ) {
        this.page = page;
    }

    // ── URLs ────────────────────────────────────────────────────────────────────
    readonly urls = {
        // SPA shell root (settings.php:9 mounts #erp-settings).
        settingsRoot: toPath( 'wp-admin/admin.php?page=erp-settings#/' ),
        // Accounting tab — the host of the pro-registered 'payment' section.
        accounting: toPath( 'wp-admin/admin.php?page=erp-settings&tab=erp-ac' ),
        // admin-ajax endpoint that handles erp-settings-save.
        adminAjax: toPath( 'wp-admin/admin-ajax.php' ),
    } as const;

    readonly sel = {
        settingsRoot: '#erp-settings',
        fatalOracle: 'body',
    } as const;

    // ── Navigation ──────────────────────────────────────────────────────────────

    /**
     * Open the accounting Settings page (server-rendered query form). Returns the
     * page-localized save nonce scraped from `window.erp_settings_var.nonce`, which
     * is coherent with the live browser session token — the same value the SPA's
     * own save uses. Returns '' when the var is absent (e.g. a non-admin role that
     * cannot load the SPA).
     */
    async openAndScrapeNonce(): Promise<string> {
        await this.page.goto( this.urls.accounting, { waitUntil: 'domcontentloaded' } );
        // Best-effort wait for the localized bootstrap var; tolerate its absence
        // (role-boundary pages render the WP "Error" template with no var).
        const nonce = await this.page
            .waitForFunction(
                () => {
                    const w = window as unknown as { erp_settings_var?: { nonce?: string } };
                    return w.erp_settings_var?.nonce ?? '';
                },
                { timeout: 15_000 },
            )
            .then( h => h.jsonValue() as Promise<string> )
            .catch( () => '' );
        return String( nonce ?? '' );
    }

    /** True if the rendered page shows WP's fatal "critical error" splash. */
    async hasCriticalError(): Promise<boolean> {
        const body = ( await this.page.locator( this.sel.fatalOracle ).innerText().catch( () => '' ) ) ?? '';
        return body.includes( CRITICAL_ERROR );
    }

    // ── Save (admin-ajax via the page's browser session) ─────────────────────────

    /**
     * POST a Payment-section save through admin-ajax using the page's own request
     * context (so the live session cookies + scraped nonce stay coherent). `fields`
     * is the flat map of field ids -> values for the chosen sub-section; checkbox
     * ids are simply omitted from the map to exercise the toggle-OFF path.
     */
    async savePaymentSection(
        nonce: string,
        subSubSection: 'stripe' | 'paypal' | 'general',
        fields: Record<string, string>,
    ): Promise<SaveResult> {
        const form: Record<string, string> = {
            action: 'erp-settings-save',
            _wpnonce: nonce,
            module: 'erp-ac',
            section: 'payment',
            sub_sub_section: subSubSection,
            ...fields,
        };

        const resp = await this.page.request.post( this.urls.adminAjax, { form } );
        const raw = await resp.text();
        let json: { success: boolean; data?: unknown } | undefined;
        try {
            json = JSON.parse( raw );
        } catch {
            json = undefined; // admin-ajax may die with the literal '0' for anon/unprivileged
        }
        return { status: resp.status(), raw, json };
    }

    // ── DB oracles (wp_options round-trip) ───────────────────────────────────────

    /** Read a single wp_options value (the pro fields persist one-id-per-option). */
    static async option( name: string ): Promise<string | undefined> {
        const v = await dbUtils.getOptionValue<string>( name );
        return v === undefined ? undefined : String( v );
    }

    /**
     * Assert a save landed by reading the field's own wp_options row. Returns the
     * stored value so callers can branch resiliently rather than hard-asserting.
     */
    static async readBack( name: string ): Promise<string | undefined> {
        return AcctProSettingsPage.option( name );
    }

    /** Convenience expectation: a saved option equals the expected value. */
    static async expectOption( name: string, expected: string ): Promise<void> {
        const actual = await AcctProSettingsPage.option( name );
        expect( actual, `wp_options.${name}` ).toBe( expected );
    }
}

export { CRITICAL_ERROR };
