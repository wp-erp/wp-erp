import { test, expect } from '@utils/test';
import { data } from '@utils/testData';
import { exeCommandWpcli, toPath } from '@utils/helpers';

/**
 * Regression specs for admin-screen and email fixes merged for the 1.17.12 cycle.
 *
 *   wp-erp#1689 / erp-pro#965  HR router fatals on redirect-only submenus (empty callback)
 *   wp-erp#1688 / wp-erp#1687  dropdown items highlighted by sub-section only
 *   wp-erp#1686 / erp-pro#244  payroll/attendance/document/asset emails missing from Settings > Emails
 *   erp-pro#1053 / erp-pro#1121 set-screen-option callback registered with 2 of 3 args -> ArgumentCountError
 *   erp-pro#870 / erp-pro#845  licence form styling: readable values, sized button, scoped selectors
 *   wp-erp#1615 / wp-erp#1684  CRM contact form integrations never initialised (hooked to plugins_loaded)
 *   wp-erp#1573 / erp-pro#241  Email::send() ignores a disabled template
 *   wp-erp#1532 / wp-erp#1685  erp_months_dropdown() month names never translated
 *
 * Server-side behaviour that has no UI of its own is driven through wp-cli
 * (`wp eval`), the same channel the setup chain uses.
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

/** Run PHP through wp-cli without fighting shell quoting. */
function php(code: string): string {
    const b64 = Buffer.from(code).toString('base64');
    return exeCommandWpcli(`eval 'eval(base64_decode("${b64}"));'`);
}

function phpJson<T>(code: string): T {
    const out = php(code);
    const line = out.split('\n').reverse().find(l => l.trim().startsWith('{') || l.trim().startsWith('['));
    expect(line, `wp eval printed JSON, got: ${out}`).toBeTruthy();
    return JSON.parse(line as string) as T;
}

// ─────────────────────────────────────────────────────────────────────────────
// HR router — redirect-only submenus (erp-pro#965)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HR router renders redirect-only submenus (erp-pro#965)', () => {
    test.use({ storageState: data.auth.adminFile });

    for (const section of ['payroll', 'attendance']) {
        test(`erp-hr ${section} settings sub-section renders without a fatal`, { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
            const res = await page.goto(toPath(`wp-admin/admin.php?page=erp-hr&section=${section}&sub-section=settings`));
            expect(res?.status(), 'no HTTP 500').toBeLessThan(500);
            await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
            await expect(page.locator('body')).not.toContainText('Fatal error');
            // The HR nav and the admin footer both render, i.e. the request ran to completion.
            await expect(page.locator('.erp-nav-container ul.erp-nav.-primary').first()).toBeVisible();
            await expect(page.locator('#wpfooter')).toBeAttached();
        });
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// Dropdown highlight follows sub-section only (wp-erp#1687)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HR dropdown highlights only the current sub-section (wp-erp#1687)', () => {
    test.use({ storageState: data.auth.adminFile });

    const activeDropdownItems = (page: import('@playwright/test').Page) => page.locator('ul.erp-nav-dropdown > li.active');

    /** The nav prints each dropdown twice (inline + overflow copy), so compare distinct links. */
    const activeHrefs = async (page: import('@playwright/test').Page) =>
        [...new Set(await activeDropdownItems(page).evaluateAll(els => els.map(e => e.querySelector('a')?.getAttribute('href') ?? '')))];

    test('Attendance > Shifts highlights Shifts only, not Attendance', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(toPath('wp-admin/admin.php?page=erp-hr&section=attendance&sub-section=shifts'));
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        // Before the fix the Attendance item (slug == parent section) was highlighted here too.
        const hrefs = await activeHrefs(page);
        expect(hrefs, 'exactly one highlighted dropdown entry').toHaveLength(1);
        expect(hrefs[0]).toMatch(/shifts/);
    });

    test('a section page without sub-section highlights no dropdown item', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(toPath('wp-admin/admin.php?page=erp-hr&section=attendance'));
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        await expect(activeDropdownItems(page)).toHaveCount(0);
        // The top-level tab itself stays highlighted.
        await expect(page.locator('ul.erp-nav.-primary > li.active > a').first()).toHaveAttribute('href', /section=attendance/);
    });

    test('the active class is not duplicated on the matching item', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        await page.goto(toPath('wp-admin/admin.php?page=erp-hr&section=attendance&sub-section=attendance'));
        expect(await activeHrefs(page), 'one highlighted dropdown entry').toHaveLength(1);
        const classes = await activeDropdownItems(page).evaluateAll(els => els.map(e => e.className));
        for (const c of classes) {
            expect(c.match(/\bactive\b/g)?.length, `class="${c}"`).toBe(1);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Settings > Emails lists every HR template (erp-pro#244)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Settings > Emails lists pro HR templates (erp-pro#244)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('payslip, attendance, document and asset templates are listed under HR', { tag: ['@pro', '@settings', '@admin'] }, async ({ page }) => {
        await page.goto(toPath('wp-admin/admin.php?page=erp-settings#/erp-email/general'), { waitUntil: 'domcontentloaded' });
        const nonce = await page.evaluate(() => (window as unknown as { erp_settings_var?: { nonce: string } }).erp_settings_var?.nonce);
        expect(nonce, 'erp_settings_var.nonce is localized').toBeTruthy();

        const res = await page.request.post(toPath('wp-admin/admin-ajax.php'), { form: { action: 'erp_get_email_templates', _wpnonce: nonce as string } });
        expect(res.ok()).toBeTruthy();
        const body = await res.json();
        expect(body.success).toBe(true);

        const hrIds: string[] = (body.data.hrm ?? []).map((e: { id: string }) => e.id);
        // Every registered template must land in some group — none silently dropped.
        const registered = phpJson<string[]>('echo wp_json_encode( array_keys( wperp()->emailer->get_emails() ) );');
        const listed = [...(body.data.hrm ?? []), ...(body.data.crm ?? []), ...(body.data.acct ?? [])].map((e: { id: string }) => e.id);
        expect(registered.filter(id => !listed.includes(id)), 'registered templates missing from the list').toEqual([]);

        for (const id of ['EmailPayslip', 'AttendanceReminder', 'DmFileShareNotification', 'New_Asset_Request', 'New_Asset_Approve', 'Asset_Request_Reject', 'EmployeeAssetOverdue']) {
            if (registered.includes(id)) {
                expect(hrIds, `${id} listed under HR`).toContain(id);
            }
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Screen options save (erp-pro#1121)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Saving a custom screen option does not fatal (erp-pro#1121)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('POSTing a *_per_page screen option completes', { tag: ['@pro', '@hrm', '@admin'] }, async ({ page }) => {
        // Any non-core option runs the deprecated `set-screen-option` filter, which Payroll and
        // Recruitment hook. Registered with 2 accepted args for a 3-arg callback it was a fatal.
        await page.goto(toPath('wp-admin/users.php'));
        const nonce = await page.locator('#screenoptionnonce').getAttribute('value');
        expect(nonce, 'screen options nonce on users.php').toBeTruthy();

        const res = await page.request.post(toPath('wp-admin/users.php'), {
            form: {
                screenoptionnonce: nonce as string,
                'wp_screen_options[option]': 'pw_regression_per_page',
                'wp_screen_options[value]': '25',
                'screen-options-apply': 'Apply',
            },
            maxRedirects: 0,
        });
        expect(res.status(), 'no HTTP 500').toBeLessThan(500);
        expect(await res.text()).not.toContain(CRITICAL_ERROR);
    });

    test('payroll and recruitment register set_screen with three args', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const args = phpJson<Record<string, number>>(`
            global $wp_filter;
            $out = [];
            foreach ( $wp_filter['set-screen-option']->callbacks ?? [] as $prio => $cbs ) {
                foreach ( $cbs as $cb ) {
                    if ( is_array( $cb['function'] ) && 'set_screen' === $cb['function'][1] ) {
                        $class = is_object( $cb['function'][0] ) ? get_class( $cb['function'][0] ) : $cb['function'][0];
                        $out[ $class ] = $cb['accepted_args'];
                    }
                }
            }
            echo wp_json_encode( $out );
        `);
        for (const [cls, n] of Object.entries(args)) {
            expect(n, `${cls}::set_screen accepted_args`).toBe(3);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Licence form styling (erp-pro#845)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Licence form is readable and scoped (erp-pro#845)', () => {
    test('field values are dark 14px, button sized, and other inputs untouched', { tag: ['@pro', '@admin'] }, async ({ page }) => {
        // Render the template directly: the QA site forces an active licence, which shows the
        // details screen instead of the form.
        const html = php(`
            $errors = []; $email = 'pw@example.com'; $key = 'pw-licence-key-0001'; $subscription_type = 'growth_yearly';
            $file = WP_PLUGIN_DIR . '/erp-pro/includes/templates/license-form.php';
            if ( ! file_exists( $file ) ) { echo 'NO_TEMPLATE'; return; }
            include $file;
        `);
        test.skip(html.includes('NO_TEMPLATE'), 'licence template not present in this erp-pro build');

        await page.setContent(
            `<!doctype html><html><body>${html}<p id="outside"><input type="email" id="other-email" value="x@y.z"><select id="other-select"><option>A</option></select></p></body></html>`,
        );

        const style = (sel: string) =>
            page.locator(sel).evaluate(el => {
                const s = getComputedStyle(el);
                const r = el.getBoundingClientRect();
                return { color: s.color, fontSize: s.fontSize, width: r.width, height: r.height };
            });

        for (const sel of ['.license_page-wrap input[type="email"]', '.license_page-wrap input[type="text"]', '.license_page-wrap select']) {
            const s = await style(sel);
            expect(s.color, `${sel} value colour`).not.toBe('rgb(181, 192, 195)');
            expect(parseFloat(s.fontSize), `${sel} font-size`).toBeGreaterThanOrEqual(14);
        }

        const btn = await style('.license_page-wrap input[type="submit"]');
        expect(btn.height, 'button height').toBeGreaterThanOrEqual(36);
        expect(btn.width, 'button fits its label').toBeGreaterThan(105);

        // Selectors used to leak: `input[type="email"]` and bare `select` were unscoped.
        for (const sel of ['#other-email', '#other-select']) {
            const s = await style(sel);
            expect(s.color, `${sel} not styled by the licence form`).not.toBe('rgb(181, 192, 195)');
            expect(s.fontSize, `${sel} keeps its own size`).not.toBe('12px');
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Server-side behaviour via wp-cli
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CRM contact form integrations load (wp-erp#1684)', () => {
    test('erp_crm_contact_forms() runs once CRM is loaded', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const r = phpJson<{ crmActive: boolean; hooked: boolean; integrationLoaded: boolean }>(`
            echo wp_json_encode( [
                'crmActive'         => wperp()->modules->is_module_active( 'crm' ),
                'hooked'            => false !== has_action( 'erp_crm_loaded', 'erp_crm_contact_forms' ),
                // erp_crm_contact_forms() instantiates these unconditionally; nothing else loads them.
                'integrationLoaded' => class_exists( '\\\\WeDevs\\\\ERP\\\\CRM\\\\ContactForms\\\\CF7', false ) && class_exists( '\\\\WeDevs\\\\ERP\\\\CRM\\\\ContactForms\\\\NinjaForms', false ),
            ] );
        `);
        test.skip(!r.crmActive, 'CRM module inactive');
        expect(r.integrationLoaded, 'CF7 and Ninja Forms integrations were initialised during load').toBe(true);
    });
});

test.describe('Disabled email templates are not sent (erp-pro#241)', () => {
    test('Email::send() returns false and wp_mail is never called for a disabled template', { tag: ['@lite', '@settings', '@admin'] }, async () => {
        const r = phpJson<{ disabled: boolean; disabledMails: number; enabled: boolean; enabledMails: number }>(`
            $email  = wperp()->emailer->get_email( 'NewEmployeeWelcome' );
            $opt_id = $email->get_option_id();
            $backup = get_option( $opt_id, null );
            $sent   = 0;
            add_filter( 'pre_wp_mail', function () use ( &$sent ) { $sent++; return true; } );

            update_option( $opt_id, array_merge( (array) $backup, [ 'is_enable' => 'no' ] ) );
            $disabled      = $email->send( 'pw@example.com', 'PW', 'PW body', '', [] );
            $disabledMails = $sent;

            update_option( $opt_id, array_merge( (array) $backup, [ 'is_enable' => 'yes' ] ) );
            $enabled = $email->send( 'pw@example.com', 'PW', 'PW body', '', [] );

            null === $backup ? delete_option( $opt_id ) : update_option( $opt_id, $backup );
            echo wp_json_encode( [ 'disabled' => (bool) $disabled, 'disabledMails' => $disabledMails, 'enabled' => (bool) $enabled, 'enabledMails' => $sent - $disabledMails ] );
        `);
        expect(r.disabled, 'send() on a disabled template').toBe(false);
        expect(r.disabledMails, 'mails sent for a disabled template').toBe(0);
        expect(r.enabledMails, 'enabled template still sends').toBe(1);
    });
});

test.describe('Month dropdown is localised (wp-erp#1685)', () => {
    test('erp_months_dropdown() uses the site locale month names', { tag: ['@lite', '@hrm', '@admin'] }, async () => {
        const r = phpJson<{ jan: string; dec: string }>(`
            global $wp_locale;
            $wp_locale->month['01'] = 'PW-Enero';
            $wp_locale->month['12'] = 'PW-Diciembre';
            $m = erp_months_dropdown();
            echo wp_json_encode( [ 'jan' => $m[1], 'dec' => $m[12] ] );
        `);
        expect(r.jan).toBe('PW-Enero');
        expect(r.dec).toBe('PW-Diciembre');
    });
});
