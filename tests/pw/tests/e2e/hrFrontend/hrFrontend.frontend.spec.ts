import { test, expect } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { toPath } from '@utils/helpers';
import { HrFrontendPage } from './hrFrontendPage';
import type { ResponseBody } from '@utils/interfaces';

/**
 * WP ERP Pro — HRM **HR Frontend dashboard** DEEP BEHAVIORAL LIFECYCLE
 * (erp-pro module: hr_frontend).
 *
 * This is the rewrite-rule-driven FRONTEND dashboard page — NOT a WP page with a
 * shortcode. Source of truth (read, not guessed):
 *   - modules/pro/hr-frontend/includes/Rewrites.php   (rules + the access gate)
 *   - modules/pro/hr-frontend/includes/functions.php  (slug/title defaults)
 *   - modules/pro/hr-frontend/templates/dashboard.php (standalone HTML it renders)
 *   - modules/pro/hr-frontend/includes/DashboardSettings.php (REST settings)
 *
 * ARCHITECTURE (live-confirmed). Rewrites.php registers two top rules from option
 * `hr_frontend_slug` (default 'wp-erp'): `^<slug>/?$` and `^<slug>/(.+)/?`, both →
 * index.php?erp_dashboard=true. On template_redirect (erp_dashboard=='true'):
 *   1. not logged in            → 302 wp-login.php?redirect_to=<dashboard>
 *   2. GATE: NOT (an ERP employee row OR erp_hr_manager/manage_options) → 302 /wp-admin/
 *   3. otherwise                → include templates/dashboard.php (standalone, no theme)
 *
 * ACCESS MATRIX (each row live-verified in Discover, see confirmedSteps):
 *   - unauthenticated      → 302 to wp-login.php
 *   - admin (manage_options)        → 200, renders
 *   - HR manager (erp_hr_manager)   → 200, renders
 *   - employee, NO ERP employee row → 302 to /wp-admin/ (the shipped employee
 *                                     storage user is NOT an ERP employee)
 *   - employee, WITH an ERP employee row → 200, renders (userId:4 localized)
 *
 * DRIVING IT: there is no EMPLOYEE_NONCE in .env, so the employee path runs via
 * the page fixture + test.use({ storageState: employeeFile }) and page.goto, with
 * DOM assertions — not REST. admin/hrManager use their storage files. To exercise
 * the genuine EMPLOYEE-renders path, beforeAll inserts a wp_erp_hr_employees row
 * for the employee-storage user (id 4) and afterAll removes it.
 *
 * SHARED STATE: this file mutates the singleton wp_options `hr_frontend_slug` /
 * `hr_frontend_dashboard_title` and the global rewrite_rules map, so it MUST run
 * serial and restores slug='wp-erp' / title='WP ERP' + a hard rewrite flush in
 * afterAll.
 *
 * HRFE-BUG-01 (confirmed live): changing the slug via the REST settings endpoint
 * leaves the new slug URL returning 404 — the rewrite rule is never registered
 * (DashboardSettings::update_settings flushes a stale rule set; the compensating
 * Rewrites::flush_permalink that re-adds the rule is hooked only to the legacy
 * admin-ajax 'erp_after_save_settings' path, NOT the REST handler). It does not
 * self-heal on later loads; only a hard `wp rewrite flush` fixes it. So the spec
 * asserts the REST slug change resiliently (NOT a fatal, 200-or-404), and drives
 * the POSITIVE "new slug renders" assertion via the deterministic wp-cli path.
 *
 * Every test carries: tier (@pro) + module (@hrm) + role (@admin/@manager/@employee/@core).
 */

// Mutates singleton wp_options slug/title + the global rewrite-rules array, and
// the slug-lifecycle tests depend on order → serial.
test.describe.configure({ mode: 'serial' });

// Resolved in beforeAll to the employee-storage user's REAL WP id (never hardcoded —
// CI assigns ids by creation order, so 'employee1' is not necessarily 4).
let EMP_USER_ID = HrFrontendPage.EMP_USER_ID;

// Active slug discovered in beforeAll (default 'wp-erp'); all render asserts use it.
let activeSlug = HrFrontendPage.DEFAULT_SLUG;

// Whether THIS run inserted the gate row (so afterAll only deletes what it added).
let insertedEmployeeRow = false;

let adminApi: ApiUtils;

test.beforeAll( async () => {
    adminApi = await ApiUtils.fromStorageState( data.auth.adminFile, process.env.X_WP_NONCE );

    // Step 1 — discover the active dashboard slug (default 'wp-erp').
    activeSlug = await HrFrontendPage.getSlug();

    // Step 6 setup — ensure the employee-storage user is an ERP employee so the
    // genuine "employee renders" path is reachable. Resolve the real id first
    // (CI-safe), then idempotent insert.
    EMP_USER_ID = HrFrontendPage.resolveEmployeeUserId();
    const had = await HrFrontendPage.employeeRowExists( EMP_USER_ID );
    if ( ! had ) {
        await HrFrontendPage.insertEmployeeRow( EMP_USER_ID );
        insertedEmployeeRow = true;
    }
} );

test.afterAll( async () => {
    // Remove the gate row we added (Step 7: employee gated out again).
    try {
        if ( insertedEmployeeRow ) {
            await HrFrontendPage.deleteEmployeeRow( EMP_USER_ID );
        }
    } catch {
        /* best-effort cleanup */
    }

    // Restore the shipped slug/title + hard-flush so sibling specs see 'wp-erp'.
    try {
        HrFrontendPage.restoreDefaultsViaCli();
    } catch {
        /* best-effort restore */
    }

    try {
        await adminApi.dispose();
    } catch {
        /* already disposed */
    }
    try {
        await dbUtils.close();
    } catch {
        /* pool may already be closed by a sibling spec */
    }
} );

// ──────────────────────────────────────────────────────────────────────────
// Baseline + render proof (admin) — Steps 1, 3, 8, 9
// ──────────────────────────────────────────────────────────────────────────
test.describe( 'HR Frontend dashboard — render baseline (admin)', () => {
    test.use( { storageState: data.auth.adminFile } );

    test( 'HRFE-FE-01 admin renders the dashboard (manage_options passes the gate)', { tag: [ '@pro', '@hrm', '@admin' ] }, async ( { page } ) => {
        const fe = new HrFrontendPage( page );
        await fe.expectDashboardRenders( activeSlug );

        // Loading shell text from templates/dashboard.php is part of the markup.
        await expect( page.locator( fe.sel.loading ), 'loading shell is part of the standalone template' )
            .toBeAttached();
    } );

    test( 'HRFE-FE-02 admin sub-routes render the same template (React-Router catch-all)', { tag: [ '@pro', '@hrm', '@admin' ] }, async ( { page } ) => {
        const fe = new HrFrontendPage( page );
        // The `^<slug>/(.+)/?` rule routes any sub-path to the same dashboard.
        for ( const sub of [ 'employees', 'leave' ] ) {
            await fe.expectDashboardRenders( `${activeSlug}/${sub}` );
        }
    } );

    test( 'HRFE-FE-03 the React bundle is served (200, non-trivial size)', { tag: [ '@pro', '@hrm', '@core' ] }, async ( { page } ) => {
        // The template enqueues .../hr-frontend.js?ver=…; fetch it through the page
        // session and assert it is actually served (not a 404 placeholder).
        const url = toPath( 'wp-content/plugins/erp-pro/modules/pro/hr-frontend/assets/js/hr-frontend.js' );
        const resp = await page.request.get( url );
        expect( resp.status(), 'hr-frontend.js must not 500' ).toBeLessThan( 500 );
        expect( resp.status(), 'hr-frontend.js bundle is served' ).toBe( 200 );
        const buf = await resp.body();
        expect( buf.byteLength, 'bundle is a real (large) JS file, not a stub' ).toBeGreaterThan( 100_000 );
    } );
} );

// ──────────────────────────────────────────────────────────────────────────
// Access control — unauthenticated + HR manager — Steps 2, 4
// ──────────────────────────────────────────────────────────────────────────
test.describe( 'HR Frontend dashboard — unauthenticated gate', () => {
    test.use( data.auth.noAuth );

    test( 'HRFE-FE-04 unauthenticated visit is redirected to the login page', { tag: [ '@pro', '@hrm', '@core' ] }, async ( { page } ) => {
        const fe = new HrFrontendPage( page );
        const finalUrl = await fe.expectGatedAway( activeSlug );
        // wp_redirect( wp_login_url( <dashboard_url> ) ) → lands on wp-login.php.
        expect( finalUrl, 'unauthenticated user is gated to wp-login.php' ).toContain( 'wp-login.php' );
        expect( finalUrl, 'login redirect carries a redirect_to back to the dashboard' )
            .toContain( 'redirect_to' );
    } );
} );

test.describe( 'HR Frontend dashboard — HR manager render', () => {
    test.use( { storageState: data.auth.hrManagerFile } );

    test( 'HRFE-FE-05 HR manager renders the dashboard (erp_hr_manager passes the gate)', { tag: [ '@pro', '@hrm', '@manager' ] }, async ( { page } ) => {
        const fe = new HrFrontendPage( page );
        await fe.expectDashboardRenders( activeSlug );
    } );
} );

// ──────────────────────────────────────────────────────────────────────────
// Employee gate lifecycle — Steps 5, 6, 7
//
// Ordering matters (serial): the WITH-row test runs while beforeAll's gate row is
// present; the LAST test removes the row and re-asserts the gate, mirroring the
// afterAll cleanup behaviour deterministically.
// ──────────────────────────────────────────────────────────────────────────
test.describe( 'HR Frontend dashboard — employee gate', () => {
    test.use( { storageState: data.auth.employeeFile } );

    test( 'HRFE-FE-06 employee WITH an ERP employee row renders the dashboard (userId localized)', { tag: [ '@pro', '@hrm', '@employee' ] }, async ( { page } ) => {
        // Sanity: the gate row inserted in beforeAll is present.
        expect( await HrFrontendPage.employeeRowExists( EMP_USER_ID ), 'gate row exists for the employee user' ).toBe( true );

        const fe = new HrFrontendPage( page );
        await fe.expectDashboardRenders( activeSlug );

        // dashboard.php localizes userId = current user id (employee-storage user 4).
        const localizedId = await fe.localizedUserId();
        expect( localizedId, 'window.wpErpHrFrontend.userId equals the employee user id' ).toBe( EMP_USER_ID );
    } );

    test( 'HRFE-FE-07 removing the ERP employee row gates the employee out to /wp-admin/', { tag: [ '@pro', '@hrm', '@employee' ] }, async ( { page } ) => {
        // Flip the gate: delete the row → !is_employee && !has_hr_cap → redirect.
        await HrFrontendPage.deleteEmployeeRow( EMP_USER_ID );
        insertedEmployeeRow = false; // afterAll no longer needs to delete it
        expect( await HrFrontendPage.employeeRowExists( EMP_USER_ID ), 'gate row removed' ).toBe( false );

        const fe = new HrFrontendPage( page );
        const finalUrl = await fe.expectGatedAway( activeSlug );
        // wp_redirect( admin_url() ) → lands on /wp-admin/.
        expect( finalUrl, 'gated employee is redirected to wp-admin' ).toContain( '/wp-admin/' );
    } );
} );

// ──────────────────────────────────────────────────────────────────────────
// Slug-change lifecycle — Step 10 + HRFE-BUG-01
//
// (a) REST slug change: asserted resiliently — NOT a fatal, success:true, but the
//     new slug URL may 404 (HRFE-BUG-01 — rule never registered by the REST path).
// (b) wp-cli slug change: the deterministic path that DOES register the rule, used
//     for the positive "new slug renders" assertion.
// ──────────────────────────────────────────────────────────────────────────
test.describe( 'HR Frontend dashboard — slug change lifecycle (admin)', () => {
    test.use( { storageState: data.auth.adminFile } );

    test( 'HRFE-FE-08 REST slug change returns success but the new slug is NOT immediately reachable (HRFE-BUG-01)', { tag: [ '@pro', '@hrm', '@admin' ] }, async ( { page } ) => {
        const stamp = Date.now();
        const newSlug = `pw-hrfe-rest-${stamp}`;

        const [ resp, body ] = await adminApi.post(
            HrFrontendPage.settingsUrl(),
            { data: { hr_frontend_slug: newSlug, hr_frontend_dashboard_title: `PW Probe Dash ${stamp}` } },
            false,
        );
        // The settings write itself must succeed and never fatal.
        expect( resp.status(), 'REST settings update must not 500' ).toBeLessThan( 500 );
        expect( resp.status(), 'admin is authorized to update settings' ).toBe( 200 );
        expect( ( body as ResponseBody ).success, 'update returns success:true' ).toBe( true );

        // The option DID change (update_option ran)…
        expect( await HrFrontendPage.getSlug(), 'slug option reflects the REST change' ).toBe( newSlug );

        // …but the rewrite rule was NOT registered by the REST path (the bug). Visit
        // the new slug and assert resiliently: NOT a WP fatal, and the dashboard does
        // NOT render (404 / no #erp-hr-frontend-root). We do NOT assert an exact 404 —
        // we document the bug as "new slug not reachable after a REST-only change".
        await page.goto( HrFrontendPage.dashboardUrl( newSlug ), { waitUntil: 'domcontentloaded' } );
        const html = ( await page.locator( 'body' ).textContent() ) ?? '';
        expect( html, 'no WP critical-error splash on the unreachable new slug' )
            .not.toContain( HrFrontendPage.CRITICAL_ERROR );
        await expect( page.locator( '#erp-hr-frontend-root' ), 'HRFE-BUG-01: REST-only slug change does NOT register the rewrite rule, so the new slug does not render the dashboard' )
            .toHaveCount( 0 );

        // Confirm the rule really is absent (DB oracle), pinning the bug precisely.
        expect( HrFrontendPage.rewriteRuleExists( newSlug ), 'HRFE-BUG-01: no rewrite rule for the new slug after a REST-only change' )
            .toBe( false );
    } );

    test( 'HRFE-FE-09 wp-cli slug change registers the rule and the new slug renders the dashboard', { tag: [ '@pro', '@hrm', '@admin' ] }, async ( { page } ) => {
        const stamp = Date.now();
        const newSlug = `pw-hrfe-cli-${stamp}`;

        // Deterministic path: option update + rewrite flush re-runs add_rewrite_rules().
        HrFrontendPage.setSlugViaCli( newSlug );

        // Rule now present (DB oracle).
        expect( HrFrontendPage.rewriteRuleExists( newSlug ), 'rewrite rule registered for the new slug after a hard flush' )
            .toBe( true );

        // And the new slug URL renders the standalone dashboard for the admin.
        const fe = new HrFrontendPage( page );
        await fe.expectDashboardRenders( newSlug );

        // The old default slug no longer routes the dashboard (the rule moved):
        // its rewrite rule is gone, so the standalone template never mounts there.
        // expectGatedAway already asserts #erp-hr-frontend-root has count 0.
        await fe.expectGatedAway( HrFrontendPage.DEFAULT_SLUG );
        expect( HrFrontendPage.rewriteRuleExists( HrFrontendPage.DEFAULT_SLUG ), 'default-slug rule removed after switching to the new slug' )
            .toBe( false );
    } );

    test( 'HRFE-FE-10 afterAll-style restore returns the default slug to a rendering state', { tag: [ '@pro', '@hrm', '@admin' ] }, async ( { page } ) => {
        // Mirror the teardown deterministically inside the run so the assertion is
        // visible: restoring slug='wp-erp' + flush makes the default slug render again.
        HrFrontendPage.restoreDefaultsViaCli();
        expect( await HrFrontendPage.getSlug(), 'slug restored to the shipped default' ).toBe( HrFrontendPage.DEFAULT_SLUG );
        expect( HrFrontendPage.rewriteRuleExists( HrFrontendPage.DEFAULT_SLUG ), 'default-slug rewrite rule restored' ).toBe( true );

        const fe = new HrFrontendPage( page );
        await fe.expectDashboardRenders( HrFrontendPage.DEFAULT_SLUG );
    } );
} );
