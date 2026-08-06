import { expect, type Page } from '@utils/test';
import { toPath, restUrl, exeCommandWpcli } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';

/**
 * Feature-isolated page object for the WP ERP Pro **HR Frontend** dashboard
 * (erp-pro module: hr_frontend).
 *
 * Source of truth (read, not guessed):
 *  - modules/pro/hr-frontend/includes/Rewrites.php
 *      __construct() → add_rewrite_rules() registers, from option `hr_frontend_slug`
 *      (functions.php::get_erp_dashboard_slug, default 'wp-erp'):
 *          `^<slug>/?$`     → index.php?erp_dashboard=true
 *          `^<slug>/(.+)/?` → index.php?erp_dashboard=true   (React-Router catch-all)
 *      rewrite_templates() (on template_redirect, when erp_dashboard=='true'):
 *        1. !is_user_logged_in() → wp_redirect( wp_login_url(<dashboard_url>) ) + exit
 *        2. GATE: $is_employee = Employee::where('user_id',current)->exists()
 *                 $has_hr_cap  = current_user_can('erp_hr_manager') || 'manage_options'
 *                 !is_employee && !has_hr_cap → wp_redirect( admin_url() ) + exit
 *        3. else include templates/dashboard.php + exit (standalone HTML, no theme)
 *  - modules/pro/hr-frontend/includes/functions.php
 *      get_erp_dashboard_slug() default 'wp-erp'; get_erp_dashboard_title() default 'WP ERP'
 *  - modules/pro/hr-frontend/templates/dashboard.php
 *      #erp-hr-frontend-root, #erp-hr-frontend-loading ('Loading HR Dashboard...'),
 *      <title> = get_erp_dashboard_title(), window.wpErpHrFrontend{userId,…},
 *      <script src=.../hr-frontend.js?ver=…>
 *  - modules/pro/hr-frontend/includes/DashboardSettings.php
 *      POST /erp/v1/hrm/hr-frontend/settings {hr_frontend_slug,hr_frontend_dashboard_title,…}
 *      → update_option(...) + flush_rewrite_rules() ONLY if slug changed (see HRFE-BUG-01).
 *
 * Surfaces used (per grounding preference):
 *  - #2 UI-driven: page.goto(<slug url>) carries the session cookie, render asserted in DOM.
 *  - DB/wp-cli oracles for the GATE table + the rewrite-rule effect (deterministic).
 *
 * GATE table `wp_erp_hr_employees` and option `hr_frontend_slug` / `rewrite_rules`
 * are referenced as STRING LITERALS (the shared `tables`/`endPoints` utils do not
 * carry these pro names; we must not edit shared utils).
 */
export class HrFrontendPage {
    readonly page: Page;

    constructor( page: Page ) {
        this.page = page;
    }

    // Pro DB table / option names as string literals (not in shared utils).
    static readonly EMP_TABLE = 'wp_erp_hr_employees';
    static readonly OPT_SLUG = 'hr_frontend_slug';
    static readonly OPT_TITLE = 'hr_frontend_dashboard_title';

    // Default slug/title shipped by erp-pro (functions.php).
    static readonly DEFAULT_SLUG = 'wp-erp';
    static readonly DEFAULT_TITLE = 'WP ERP';

    // The shipped employeeStorageState user (login 'employee1', role 'employee',
    // NOT an ERP employee row by default). Confirmed live: wp user get employee1 → 4,
    // and the storage cookie `wp-settings-time-4` belongs to id 4.
    static readonly EMP_USER_ID = 4;

    static readonly CRITICAL_ERROR = 'There has been a critical error on this website';

    // Standalone-template DOM markers (templates/dashboard.php).
    readonly sel = {
        root: '#erp-hr-frontend-root',
        loading: '#erp-hr-frontend-loading',
        loadingText: 'Loading HR Dashboard...',
        bundle: 'script[src*="hr-frontend.js"]',
    } as const;

    /** REST settings endpoint (DashboardSettings::register_rest_routes). */
    static settingsUrl(): string {
        return restUrl( '/erp/v1/hrm/hr-frontend/settings' );
    }

    /** Build the dashboard URL for a slug: BASE_URL/<slug>/. */
    static dashboardUrl( slug: string ): string {
        return toPath( `${slug}/` );
    }

    // ── DB oracle: GATE table (wp_erp_hr_employees) ──────────────────────────

    /** True if an ERP employee row exists for the given WP user id. */
    static async employeeRowExists( userId: number ): Promise<boolean> {
        const rows = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${HrFrontendPage.EMP_TABLE} WHERE user_id = ? LIMIT 1`,
            [ userId ],
        );
        return rows.length > 0;
    }

    /**
     * Resolve the REAL WP user id of the employee-storage user (login from
     * process.env.EMPLOYEE, default 'employee1'). NEVER hardcode it: a fresh CI
     * install assigns ids by creation order, so 'employee1' is not necessarily 4
     * (it is typically 5, with 4 being acc_manager1). Falls back to the static
     * default only if wp-cli can't resolve it.
     */
    static resolveEmployeeUserId(): number {
        const username = process.env.EMPLOYEE ?? 'employee1';
        try {
            const id = Number( exeCommandWpcli( `user get ${username} --field=ID` ).trim() );
            return Number.isFinite( id ) && id > 0 ? id : HrFrontendPage.EMP_USER_ID;
        } catch {
            return HrFrontendPage.EMP_USER_ID;
        }
    }

    /**
     * Insert the minimal ERP employee row that flips a plain WP user from
     * gated-out (302 → /wp-admin/) to dashboard-renders (200). Confirmed-live
     * payload from the recipe (verbatim columns/values).
     */
    static async insertEmployeeRow( userId: number ): Promise<void> {
        // Idempotent: do not duplicate if a prior run left a row.
        if ( await HrFrontendPage.employeeRowExists( userId ) ) return;
        await dbUtils.dbQuery(
            `INSERT INTO ${HrFrontendPage.EMP_TABLE}
                (user_id, designation, department, location, hiring_source, hiring_date,
                 termination_date, date_of_birth, reporting_to, pay_type, type, status)
             VALUES (?, 0, 0, 0, 'direct', '2024-01-01',
                 '0000-00-00', '0000-00-00', 0, 'monthly', 'permanent', 'active')`,
            [ userId ],
        );
    }

    /** Remove the ERP employee row (afterAll cleanup → user gated out again). */
    static async deleteEmployeeRow( userId: number ): Promise<void> {
        await dbUtils.dbQuery(
            `DELETE FROM ${HrFrontendPage.EMP_TABLE} WHERE user_id = ?`,
            [ userId ],
        );
    }

    // ── wp-cli oracle: slug option + rewrite rules (deterministic) ───────────

    /** Read the active dashboard slug from wp_options (default 'wp-erp'). */
    static async getSlug(): Promise<string> {
        const raw = await dbUtils.getOptionValue<string>( HrFrontendPage.OPT_SLUG );
        const slug = String( raw ?? '' ).trim();
        return slug.length ? slug : HrFrontendPage.DEFAULT_SLUG;
    }

    /**
     * Deterministic slug-change path used for the POSITIVE "new slug renders"
     * assertion. `wp option update` + `wp rewrite flush` re-runs init →
     * Rewrites::add_rewrite_rules() with the new slug, then persists the rule —
     * so unlike the REST path (HRFE-BUG-01) the new slug URL reliably resolves.
     */
    static setSlugViaCli( slug: string ): void {
        exeCommandWpcli( `option update ${HrFrontendPage.OPT_SLUG} ${slug}` );
        exeCommandWpcli( 'rewrite flush' );
    }

    /** Restore slug + title to the shipped defaults and hard-flush the rules. */
    static restoreDefaultsViaCli(): void {
        exeCommandWpcli( `option update ${HrFrontendPage.OPT_SLUG} ${HrFrontendPage.DEFAULT_SLUG}` );
        exeCommandWpcli( `option update ${HrFrontendPage.OPT_TITLE} "${HrFrontendPage.DEFAULT_TITLE}"` );
        exeCommandWpcli( 'rewrite flush' );
    }

    /** True if a rewrite rule mentioning the given slug exists (rewrite_rules option). */
    static rewriteRuleExists( slug: string ): boolean {
        const csv = exeCommandWpcli( 'rewrite list --format=csv' );
        // A rule line looks like `^<slug>/?$,index.php?erp_dashboard=true,…`.
        return csv.split( '\n' ).some( line => line.includes( `^${slug}/` ) );
    }

    // ── UI-driven render (Surface #2) ────────────────────────────────────────

    /**
     * Visit the dashboard URL for `slug` and assert it RENDERS the standalone
     * template: the React root + loading shell + bundle present, no fatal, and
     * (best-effort) the localized window.wpErpHrFrontend object is present.
     *
     * Returns the navigation response so callers can branch on HTTP status when a
     * gate/redirect is in play (page.goto follows redirects; the final response
     * status is what we inspect).
     */
    async expectDashboardRenders( slug: string ): Promise<void> {
        await this.page.goto( HrFrontendPage.dashboardUrl( slug ), { waitUntil: 'domcontentloaded' } );

        // Never a WP fatal on an authorized render.
        const body = ( await this.page.locator( 'body' ).textContent() ) ?? '';
        expect( body, 'authorized render shows no WP critical-error splash' )
            .not.toContain( HrFrontendPage.CRITICAL_ERROR );

        // The standalone dashboard template mounts these (templates/dashboard.php).
        await expect( this.page.locator( this.sel.root ), 'React root #erp-hr-frontend-root is present' )
            .toBeAttached( { timeout: 30_000 } );
        await expect( this.page.locator( this.sel.bundle ), 'hr-frontend.js bundle is enqueued' )
            .toBeAttached( { timeout: 30_000 } );

        // window.wpErpHrFrontend localized object (userId etc.) is present.
        const localized = await this.page.evaluate( () => {
            const w = window as unknown as { wpErpHrFrontend?: { userId?: number } };
            return w.wpErpHrFrontend ?? null;
        } );
        expect( localized, 'window.wpErpHrFrontend localized object is present' ).not.toBeNull();
    }

    /** Read window.wpErpHrFrontend.userId from a rendered dashboard. */
    async localizedUserId(): Promise<number | null> {
        return this.page.evaluate( () => {
            const w = window as unknown as { wpErpHrFrontend?: { userId?: number } };
            const id = w.wpErpHrFrontend?.userId;
            return typeof id === 'number' ? id : null;
        } );
    }

    /**
     * Visit the dashboard URL and assert it is GATED (the standalone template does
     * NOT render). page.goto follows the 302, landing on wp-login.php (unauth) or
     * /wp-admin/ (no-cap); in either case #erp-hr-frontend-root must be absent.
     */
    async expectGatedAway( slug: string ): Promise<string> {
        await this.page.goto( HrFrontendPage.dashboardUrl( slug ), { waitUntil: 'domcontentloaded' } );
        const finalUrl = this.page.url();

        const body = ( await this.page.locator( 'body' ).textContent() ) ?? '';
        expect( body, 'a gate redirect must not surface a WP fatal' )
            .not.toContain( HrFrontendPage.CRITICAL_ERROR );

        // Dashboard markers must be absent on the redirect target.
        await expect( this.page.locator( this.sel.root ), 'gated user never sees the dashboard root' )
            .toHaveCount( 0 );

        return finalUrl;
    }
}
