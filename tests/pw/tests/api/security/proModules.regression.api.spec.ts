import { test, expect, request } from '@utils/test';
import type { APIRequestContext } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { BASE_URL, exeCommandWpcli, restUrl, toPath } from '@utils/helpers';

/**
 * Regression specs for the WP ERP Pro security fixes merged for the 1.7.2 cycle.
 *
 *   erp-pro#1110 / erp-pro#975   workflow AJAX: nonce + erp_workflow_menu_permission, ids absint'ed
 *   erp-pro#1111 / erp-pro#957   HR documents in uploads/erp-documents/<random>/, served by
 *                                admin-post.php?action=erp_doc_download with an access check,
 *                                legacy files moved by erp_doc_secure_existing_files()
 *   erp-pro#1112 / erp-pro#1020  legacy Documents / Attendance / Payroll / Recruitment handlers
 *   erp-pro#1117 / #1114, #1115  workflow conditions whitelist, wp_kses_post bodies, Update Field
 *                                limited to the event's fields (on save and at run time)
 *   erp-pro#1118 / erp-pro#1116  contact documents follow erp_crm_edit_contact (ownership)
 *
 * Nonces: the PRs note that the module nonces (wp-erp-attendance, payroll_nonce, the document
 * nonces, …) are printed for every employee, so a nonce is not an access check. To isolate the
 * capability checks each session's nonces are minted server-side (wp-cli, bound to that
 * session's token) instead of scraped from a page some roles cannot open.
 *
 * Two throw-away ERP employees (pw_owner_*, pw_other_*) get sessions minted the same way.
 * Everything created here is prefixed `pw_` and removed in afterAll.
 */

test.describe.configure({ mode: 'default' });

const PREFIX = process.env.DB_PREFIX ?? 'wp';
const T = (name: string) => `${PREFIX}_${name}`;
const ADMIN_AJAX = toPath('wp-admin/admin-ajax.php');
const ADMIN_POST = toPath('wp-admin/admin-post.php');
const HR_REFERER = toPath('wp-admin/admin.php?page=erp-hr&section=people');
const CRM_REFERER = toPath('wp-admin/admin.php?page=erp-crm&section=contact');
const RUN = Date.now().toString(36);
const NO_PERMISSION = /sufficient permissions|not allowed|Nonce verification failed/i;

const NONCE_ACTIONS = [
    'erp-wf-fetch-workflow',
    'erp-wf-new-workflow',
    'erp-wf-edit-workflow',
    'erp-wf-fetch-users',
    'file_upload_nonce',
    'doc_form_builder_nonce',
    'wp-erp-attendance',
    'payroll_nonce',
    'wp_erp_rec_make_him_employee_nonce',
    'wp_rest',
] as const;
type Nonces = Record<(typeof NONCE_ACTIONS)[number], string> & { uid: number };

type Role = 'admin' | 'hr' | 'crmManager' | 'crmAgent' | 'employee' | 'owner' | 'other';
const STORAGE: Partial<Record<Role, string>> = {
    admin: data.auth.adminFile,
    hr: data.auth.hrManagerFile,
    crmManager: data.auth.crmManagerFile,
    crmAgent: data.auth.crmAgentFile,
    employee: data.auth.employeeFile,
};

// ── wp-cli helpers ──────────────────────────────────────────────────────────
function php(code: string): string {
    const b64 = Buffer.from(code).toString('base64');
    return exeCommandWpcli(`eval 'eval(base64_decode("${b64}"));'`);
}

function phpJson<T>(code: string): T {
    const out = php(code);
    const line = out.split('\n').reverse().find(l => l.trim().startsWith('{') || l.trim().startsWith('['));
    if (!line) throw new Error(`wp eval printed no JSON: ${out}`);
    return JSON.parse(line) as T;
}

const phpIn = (value: unknown) => `$in = json_decode(base64_decode('${Buffer.from(JSON.stringify(value)).toString('base64')}'), true);`;

// ── state shared by the tests (one worker, file runs in order) ──────────────
type Cookie = { name: string; value: string; domain: string; path: string; expires: number; httpOnly: boolean; secure: boolean; sameSite: 'Lax' };
const ctx: Partial<Record<Role | 'anon', APIRequestContext>> = {};
const nonce: Partial<Record<Role, Nonces>> = {};
const ids = {
    owner: 0,
    other: 0,
    agentContact: 0,
    otherContact: Number(process.env.CONTACT_ID ?? 75),
    applicant: 0,
    application: 0,
    workflow: 0,
};
const doc = { attachmentId: 0, dirId: 0, path: '', directUrl: '', name: `pw_doc_${RUN}.txt`, body: `PW-DOC-SECRET-${RUN}` };
const wf = { name: `pw_wf_${RUN}`, secret: `PW-WF-SECRET-${RUN}` };
const applicant = { email: `pw_applicant_${RUN}@example.com` };

function loggedInCookieOf(storageFile: string): string {
    const state = JSON.parse(readFileSync(storageFile, 'utf8')) as { cookies: { name: string; value: string }[] };
    const c = state.cookies.find(k => k.name.startsWith('wordpress_logged_in_'));
    if (!c) throw new Error(`no logged_in cookie in ${storageFile}`);
    return decodeURIComponent(c.value);
}

async function ajax(role: Role | 'anon', form: Record<string, string | number>, headers: Record<string, string> = {}) {
    const res = await ctx[role]!.post(ADMIN_AJAX, {
        form: Object.fromEntries(Object.entries(form).map(([k, v]) => [k, String(v)])),
        headers: { Referer: HR_REFERER, ...headers },
    });
    const text = await res.text();
    let json: any;
    try {
        json = JSON.parse(text);
    } catch {
        json = undefined;
    }
    return { status: res.status(), text, json };
}

async function uploadDoc(role: Role, eid: number, name: string, body: string, referer = HR_REFERER) {
    const res = await ctx[role]!.post(ADMIN_AJAX, {
        multipart: {
            action: 'file_dir_ajax_upload',
            _wpnonce: nonce[role]!.file_upload_nonce,
            employee_id: String(eid),
            parent_id: '0',
            source: 'owned_by_me',
            doc_attachment: { name, mimeType: 'text/plain', buffer: Buffer.from(body) },
        },
        headers: { Referer: referer },
    });
    return { status: res.status(), text: await res.text() };
}

async function docRow(eid: number, name: string, eidType = 'employee') {
    const rows = await dbUtils.dbQuery<{ dir_id: number; attachment_id: number }>(
        `SELECT dir_id, attachment_id FROM ${T('erp_employee_dir_file_relationship')} WHERE eid = ? AND dir_name = ? AND eid_type = ? ORDER BY id DESC LIMIT 1`,
        [eid, name, eidType],
    );
    return rows[0];
}

const download = (role: Role | 'anon', attachmentId: number) =>
    ctx[role]!.get(`${ADMIN_POST}?action=erp_doc_download&file_id=${attachmentId}`, { maxRedirects: 0 });

// ── setup / teardown ────────────────────────────────────────────────────────
test.beforeAll(async () => {
    test.setTimeout(120_000);

    const setup = phpJson<{
        owner: { uid: number; cookies: [string, string][]; exp: number };
        other: { uid: number; cookies: [string, string][]; exp: number };
        agentContact: number;
        applicant: number;
        application: number;
        nonces: Record<string, Nonces>;
        error?: string;
    }>(`${phpIn({
        run: RUN,
        actions: NONCE_ACTIONS,
        applicantEmail: applicant.email,
        agentId: Number(process.env.CRM_AGENT_ID ?? 6),
        sessions: Object.fromEntries(Object.entries(STORAGE).map(([role, file]) => [role, loggedInCookieOf(file as string)])),
    })}
        wp_set_current_user( 1 );
        global $wpdb;
        $out = [];
        try {
            $employee = function ( $login, $last ) {
                $id = erp_hr_employee_create( [
                    'user_email' => $login . '@example.com',
                    'personal'   => [ 'first_name' => 'PW', 'last_name' => $last ],
                    'work'       => [ 'hiring_date' => '2024-01-01', 'status' => 'active', 'type' => 'permanent', 'pay_type' => 'monthly' ],
                ] );
                if ( is_wp_error( $id ) || ! is_numeric( $id ) ) {
                    throw new Exception( 'employee ' . $login . ': ' . ( is_wp_error( $id ) ? $id->get_error_message() : $id ) );
                }
                return (int) $id;
            };
            $session = function ( $uid ) {
                $exp   = time() + 2 * DAY_IN_SECONDS;
                $token = WP_Session_Tokens::get_instance( $uid )->create( $exp );
                return [
                    'uid'     => $uid,
                    'exp'     => $exp,
                    'cookies' => [
                        [ AUTH_COOKIE, wp_generate_auth_cookie( $uid, $exp, 'auth', $token ) ],
                        [ LOGGED_IN_COOKIE, wp_generate_auth_cookie( $uid, $exp, 'logged_in', $token ) ],
                    ],
                ];
            };
            $out['owner'] = $session( $employee( 'pw_owner_' . $in['run'], 'Owner' ) );
            $out['other'] = $session( $employee( 'pw_other_' . $in['run'], 'Other' ) );

            $sessions          = $in['sessions'];
            $sessions['owner'] = $out['owner']['cookies'][1][1];
            $sessions['other'] = $out['other']['cookies'][1][1];

            $out['nonces'] = [];
            foreach ( $sessions as $role => $cookie ) {
                $_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
                $uid = (int) wp_validate_auth_cookie( $cookie, 'logged_in' );
                if ( ! $uid ) {
                    throw new Exception( 'expired session for ' . $role );
                }
                wp_set_current_user( $uid );
                $n = [ 'uid' => $uid ];
                foreach ( $in['actions'] as $a ) {
                    $n[ $a ] = wp_create_nonce( $a );
                }
                $out['nonces'][ $role ] = $n;
            }
            unset( $_COOKIE[ LOGGED_IN_COOKIE ] );
            wp_set_current_user( 1 );

            // CRM contact owned by the CRM agent
            $contact = erp_insert_people( [
                'type'          => 'contact',
                'first_name'    => 'PWContact',
                'last_name'     => 'Agent',
                'email'         => 'pw_contact_' . $in['run'] . '@example.com',
                'contact_owner' => $in['agentId'],
                'life_stage'    => 'lead',
            ] );
            if ( is_wp_error( $contact ) ) {
                throw new Exception( 'contact: ' . $contact->get_error_message() );
            }
            $out['agentContact'] = (int) $contact;

            // A recruitment applicant + application
            $wpdb->insert( $wpdb->prefix . 'erp_peoples', [ 'first_name' => 'pw_applicant', 'last_name' => $in['run'], 'email' => $in['applicantEmail'], 'created_by' => 1, 'created' => current_time( 'mysql' ) ] );
            $out['applicant'] = (int) $wpdb->insert_id;
            $wpdb->insert( $wpdb->prefix . 'erp_application', [ 'job_id' => 0, 'applicant_id' => $out['applicant'], 'stage' => 2, 'apply_date' => current_time( 'mysql' ), 'added_by' => 1, 'status' => 0 ] );
            $out['application'] = (int) $wpdb->insert_id;
        } catch ( \\Throwable $e ) {
            $out['error'] = $e->getMessage();
        }
        echo wp_json_encode( $out );
    `);
    ids.owner = setup.owner?.uid ?? 0;
    ids.other = setup.other?.uid ?? 0;
    expect(setup.error, `fixture setup: ${setup.error}`).toBeUndefined();
    ids.agentContact = setup.agentContact;
    ids.applicant = setup.applicant;
    ids.application = setup.application;
    Object.assign(nonce, setup.nonces);

    const host = new URL(BASE_URL).hostname;
    const minted = (s: { cookies: [string, string][]; exp: number }) => ({
        cookies: s.cookies.map(([name, value]): Cookie => ({ name, value, domain: host, path: '/', expires: s.exp, httpOnly: true, secure: false, sameSite: 'Lax' })),
        origins: [],
    });

    for (const [role, file] of Object.entries(STORAGE)) {
        ctx[role as Role] = await request.newContext({ baseURL: BASE_URL, storageState: file as string, ignoreHTTPSErrors: true });
    }
    ctx.owner = await request.newContext({ baseURL: BASE_URL, storageState: minted(setup.owner), ignoreHTTPSErrors: true });
    ctx.other = await request.newContext({ baseURL: BASE_URL, storageState: minted(setup.other), ignoreHTTPSErrors: true });
    ctx.anon = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
});

test.afterAll(async () => {
    test.setTimeout(120_000);
    try {
        php(`${phpIn({ owner: ids.owner, other: ids.other, contact: ids.agentContact, applicant: ids.applicant, application: ids.application })}
            wp_set_current_user( 1 );
            global $wpdb;
            $rel = $wpdb->prefix . 'erp_employee_dir_file_relationship';

            // documents of the throw-away employees and contacts (files and their random folders)
            $rows = $wpdb->get_results( $wpdb->prepare( "SELECT attachment_id FROM {$rel} WHERE is_dir = 0 AND ( ( eid IN ( %d, %d ) AND eid_type = 'employee' ) OR dir_name LIKE 'pw\\_%%' )", $in['owner'], $in['other'] ) );
            foreach ( $rows as $r ) {
                $file = get_attached_file( $r->attachment_id );
                wp_delete_attachment( $r->attachment_id, true );
                if ( $file && false !== strpos( $file, '/erp-documents/' ) && is_dir( dirname( $file ) ) ) {
                    @rmdir( dirname( $file ) );
                }
            }
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$rel} WHERE ( eid IN ( %d, %d ) AND eid_type = 'employee' ) OR dir_name LIKE 'pw\\_%%'", $in['owner'], $in['other'] ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}erp_dir_file_share WHERE owner_id IN ( %d, %d ) OR shared_with_id IN ( %d, %d )", $in['owner'], $in['other'], $in['owner'], $in['other'] ) );

            // workflows
            $wf = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}erp_workflows WHERE name LIKE 'pw\\_%'" );
            foreach ( $wf as $id ) {
                $wpdb->delete( $wpdb->prefix . 'erp_workflow_conditions', [ 'workflow_id' => $id ] );
                $wpdb->delete( $wpdb->prefix . 'erp_workflow_actions', [ 'workflow_id' => $id ] );
                $wpdb->delete( $wpdb->prefix . 'erp_workflows', [ 'id' => $id ] );
            }

            // payroll / attendance / recruitment rows
            $wpdb->query( "DELETE FROM {$wpdb->prefix}erp_hr_payroll_payitem WHERE payitem LIKE 'pw\\_%'" );
            $wpdb->query( "DELETE FROM {$wpdb->prefix}erp_attendance_shifts WHERE name LIKE 'pw\\_%'" );
            foreach ( [ $in['owner'], $in['other'] ] as $uid ) {
                if ( $uid ) {
                    $wpdb->delete( $wpdb->prefix . 'erp_attendance_log', [ 'user_id' => $uid ] );
                    $wpdb->delete( $wpdb->prefix . 'erp_attendance_date_shift', [ 'user_id' => $uid ] );
                    $wpdb->delete( $wpdb->prefix . 'erp_attendance_shift_user', [ 'user_id' => $uid ] );
                }
            }
            if ( $in['application'] ) {
                $wpdb->delete( $wpdb->prefix . 'erp_application', [ 'id' => $in['application'] ] );
            }
            if ( $in['applicant'] ) {
                $wpdb->delete( $wpdb->prefix . 'erp_peoplemeta', [ 'erp_people_id' => $in['applicant'] ] );
                $wpdb->delete( $wpdb->prefix . 'erp_peoples', [ 'id' => $in['applicant'] ] );
            }

            // contact
            if ( $in['contact'] ) {
                erp_delete_people( [ 'id' => $in['contact'], 'hard' => true, 'type' => 'contact' ] );
            }

            // throw-away users (employees + any user a test created)
            require_once ABSPATH . 'wp-admin/includes/user.php';
            $users = get_users( [ 'search' => 'pw_*', 'search_columns' => [ 'user_login', 'user_email' ], 'fields' => 'ID' ] );
            foreach ( array_unique( array_merge( $users, [ $in['owner'], $in['other'] ] ) ) as $uid ) {
                if ( $uid ) {
                    $wpdb->delete( $wpdb->prefix . 'erp_hr_employees', [ 'user_id' => $uid ] );
                    $wpdb->delete( $wpdb->prefix . 'erp_hr_employee_history', [ 'user_id' => $uid ] );
                    wp_delete_user( $uid );
                }
            }
            echo 'ok';
        `);
    } finally {
        await Promise.all(Object.values(ctx).map(c => c?.dispose()));
        await dbUtils.close();
    }
});

// ═════════════════════════════════════════════════════════════════════════════
// Workflow AJAX access control (erp-pro#1110 / erp-pro#975)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Workflow AJAX requires nonce + erp_workflow_menu_permission (erp-pro#1110, erp-pro#975)', () => {
    test('HR manager can create a workflow', { tag: ['@pro', '@workflow', '@hrManager', '@security'] }, async () => {
        const res = await ajax('hr', {
            action: 'erp_wf_new_workflow',
            _wpnonce: nonce.hr!['erp-wf-new-workflow'],
            workflow_name: wf.name,
            // Created User rules are administrator-only, so the HR manager builds on Created Employee
            events_group: 'hrm',
            event: 'created_employee',
            conditions_group: 'or',
            activate: 'false',
            delay_time: 0,
            delay_period: 'minute',
            // erp-pro#1117 payload: extra column, unknown operator, markup in value
            'conditions[0][condition_name]': 'first_name',
            'conditions[0][operator]': '<script>',
            'conditions[0][value]': '<b>pw</b>',
            'conditions[0][parent_id]': 4242,
            'actions[0][name]': 'send_email',
            'actions[0][title]': 'Send Email',
            'actions[0][subject]': `${wf.secret} <i>subject</i>`,
            'actions[0][message]': '<p><strong>Hello</strong> <a href="https://example.com">link</a></p><script>alert(1)</script><img src=x onerror="alert(2)">',
            'actions[0][send_itself]': 'true',
        });
        expect(res.json?.success, res.text).toBe(true);

        const rows = await dbUtils.dbQuery<{ id: number }>(`SELECT id FROM ${T('erp_workflows')} WHERE name = ?`, [wf.name]);
        expect(rows.length, 'workflow row saved').toBe(1);
        ids.workflow = Number(rows[0]!.id);
    });

    for (const role of ['employee', 'crmAgent', 'other'] as const) {
        test(`${role} cannot fetch a workflow even with a valid nonce`, { tag: ['@pro', '@workflow', `@${role}`, '@security'] }, async () => {
            expect(ids.workflow).toBeGreaterThan(0);
            const res = await ajax(role, { action: 'erp_wf_fetch_workflow', _wpnonce: nonce[role]!['erp-wf-fetch-workflow'], id: ids.workflow });
            expect(res.status).toBeLessThan(500);
            expect(res.json?.success, res.text).toBe(false);
            expect(res.text, 'no workflow data leaked').not.toContain(wf.secret);
            expect(res.text).not.toContain(wf.name);
        });
    }

    test('fetching a workflow without a nonce is refused, even for the HR manager', { tag: ['@pro', '@workflow', '@hrManager', '@security'] }, async () => {
        const res = await ajax('hr', { action: 'erp_wf_fetch_workflow', id: ids.workflow });
        expect(res.json?.success, res.text).toBe(false);
        expect(res.text).not.toContain(wf.secret);
    });

    test('HR manager fetches the workflow; a missing id is a clean error', { tag: ['@pro', '@workflow', '@hrManager', '@security'] }, async () => {
        const ok = await ajax('hr', { action: 'erp_wf_fetch_workflow', _wpnonce: nonce.hr!['erp-wf-fetch-workflow'], id: ids.workflow });
        expect(ok.json?.success, ok.text).toBe(true);
        expect(ok.text).toContain(wf.secret);

        const missing = await ajax('hr', { action: 'erp_wf_fetch_workflow', _wpnonce: nonce.hr!['erp-wf-fetch-workflow'], id: 999999999 });
        expect(missing.status, 'no fatal').toBe(200);
        expect(missing.json?.success, missing.text).toBe(false);
        expect(missing.text).toMatch(/Workflow not found/);
    });

    test('employee cannot create or edit workflows with valid nonces', { tag: ['@pro', '@workflow', '@employee', '@security'] }, async () => {
        const name = `pw_wf_emp_${RUN}`;
        const created = await ajax('employee', {
            action: 'erp_wf_new_workflow',
            _wpnonce: nonce.employee!['erp-wf-new-workflow'],
            workflow_name: name,
            events_group: 'general',
            event: 'created_user',
            conditions_group: 'or',
            delay_time: 0,
            delay_period: 'minute',
        });
        expect(created.json?.success, created.text).toBe(false);
        expect(await dbUtils.dbQuery(`SELECT id FROM ${T('erp_workflows')} WHERE name = ?`, [name])).toHaveLength(0);

        const edited = await ajax('employee', {
            action: 'erp_wf_edit_workflow',
            _wpnonce: nonce.employee!['erp-wf-edit-workflow'],
            workflow_id: ids.workflow,
            workflow_name: `pw_wf_hijacked_${RUN}`,
            events_group: 'general',
            event: 'created_user',
            conditions_group: 'or',
            delay_time: 0,
            delay_period: 'minute',
        });
        expect(edited.json?.success, edited.text).toBe(false);
        const [row] = await dbUtils.dbQuery<{ name: string }>(`SELECT name FROM ${T('erp_workflows')} WHERE id = ?`, [ids.workflow]);
        expect(row!.name, 'workflow untouched').toBe(wf.name);
    });

    test('employee cannot list employees / contacts / CRM users through the workflow lookups', { tag: ['@pro', '@workflow', '@employee', '@security'] }, async () => {
        for (const action of ['erp_wf_get_employees', 'erp_wf_get_contacts', 'erp_wf_get_crm_users']) {
            const res = await ajax('employee', { action, _wpnonce: nonce.employee!['erp-wf-fetch-users'], s: '' });
            expect(res.json?.success, `${action}: ${res.text}`).toBe(false);
            expect(res.text, `${action} message`).toMatch(NO_PERMISSION);
        }
        const hr = await ajax('hr', { action: 'erp_wf_get_employees', _wpnonce: nonce.hr!['erp-wf-fetch-users'], s: '' });
        expect(hr.json?.success, hr.text).toBe(true);
        expect(Array.isArray(hr.json?.data)).toBe(true);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// Workflow input sanitizing + Update Field guard (erp-pro#1117 / #1114, #1115)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Workflow input is sanitized and Update Field is limited (erp-pro#1117, erp-pro#1114, erp-pro#1115)', () => {
    test('saved condition keeps only whitelisted columns and a known operator', { tag: ['@pro', '@workflow', '@hrManager', '@security'] }, async () => {
        expect(ids.workflow).toBeGreaterThan(0);
        const rows = await dbUtils.dbQuery<{ condition_name: string; operator: string; value: string; parent_id: number | null }>(
            `SELECT condition_name, operator, value, parent_id FROM ${T('erp_workflow_conditions')} WHERE workflow_id = ?`,
            [ids.workflow],
        );
        expect(rows).toHaveLength(1);
        expect(rows[0]!.condition_name).toBe('first_name');
        expect(rows[0]!.operator, 'unknown operator falls back to =').toBe('=');
        expect(rows[0]!.value).toBe('pw');
        expect(Number(rows[0]!.parent_id ?? 0), 'injected parent_id not stored').not.toBe(4242);
    });

    test('saved Send Email body is wp_kses_post-filtered, subject is plain text', { tag: ['@pro', '@workflow', '@hrManager', '@security'] }, async () => {
        const [row] = await dbUtils.dbQuery<{ name: string; params: string }>(`SELECT name, params FROM ${T('erp_workflow_actions')} WHERE workflow_id = ?`, [ids.workflow]);
        expect(row!.name).toBe('send_email');
        expect(row!.params).not.toMatch(/<script/i);
        expect(row!.params).not.toMatch(/onerror/i);
        expect(row!.params, 'formatting survives').toContain('<strong>Hello</strong>');
        expect(row!.params, 'links survive').toContain('href="https://example.com"');
        expect(row!.params).toContain(wf.secret);
        expect(row!.params, 'subject tags stripped').not.toContain('<i>');
    });

    for (const field of ['role', 'user_pass']) {
        test(`Update Field "${field}" on Created User is rejected on save`, { tag: ['@pro', '@workflow', '@admin', '@security'] }, async () => {
            const name = `pw_wf_${field}_${RUN}`;
            const res = await ajax('admin', {
                action: 'erp_wf_new_workflow',
                _wpnonce: nonce.admin!['erp-wf-new-workflow'],
                workflow_name: name,
                events_group: 'general',
                event: 'created_user',
                conditions_group: 'or',
                activate: 'false',
                delay_time: 0,
                delay_period: 'minute',
                'actions[0][name]': 'update_field',
                'actions[0][title]': 'Update Field',
                'actions[0][field_name]': field,
                'actions[0][field_value]': field === 'role' ? 'administrator' : 'pw-owned',
            });
            expect(res.json?.success, res.text).toBe(false);
            expect(res.text).toMatch(/can not be updated by a workflow/);
            expect(await dbUtils.dbQuery(`SELECT id FROM ${T('erp_workflows')} WHERE name = ?`, [name]), 'nothing saved').toHaveLength(0);

            const edit = await ajax('admin', {
                action: 'erp_wf_edit_workflow',
                _wpnonce: nonce.admin!['erp-wf-edit-workflow'],
                workflow_id: ids.workflow,
                workflow_name: wf.name,
                events_group: 'general',
                event: 'created_user',
                conditions_group: 'or',
                delay_time: 0,
                delay_period: 'minute',
                'actions[0][name]': 'update_field',
                'actions[0][field_name]': field,
                'actions[0][field_value]': 'administrator',
            });
            expect(edit.json?.success, edit.text).toBe(false);
            const [act] = await dbUtils.dbQuery<{ name: string }>(`SELECT name FROM ${T('erp_workflow_actions')} WHERE workflow_id = ?`, [ids.workflow]);
            expect(act?.name, 'existing actions untouched').toBe('send_email');
        });
    }

    test('Update Field "first_name" on Created User is still accepted', { tag: ['@pro', '@workflow', '@admin', '@security'] }, async () => {
        const name = `pw_wf_first_name_${RUN}`;
        const res = await ajax('admin', {
            action: 'erp_wf_new_workflow',
            _wpnonce: nonce.admin!['erp-wf-new-workflow'],
            workflow_name: name,
            events_group: 'general',
            event: 'created_user',
            conditions_group: 'or',
            activate: 'false',
            delay_time: 0,
            delay_period: 'minute',
            'actions[0][name]': 'update_field',
            'actions[0][title]': 'Update Field',
            'actions[0][field_name]': 'first_name',
            'actions[0][field_value]': 'PW',
        });
        expect(res.json?.success, res.text).toBe(true);
        expect(await dbUtils.dbQuery(`SELECT id FROM ${T('erp_workflows')} WHERE name = ?`, [name])).toHaveLength(1);
    });

    test('a stored "role=administrator" Update Field action does not run; first_name still does', { tag: ['@pro', '@workflow', '@admin', '@security'] }, async () => {
        // Simulates a row saved before the fix (it never goes through the AJAX validation).
        const result = phpJson<{ roles: string[]; first_name: string }>(`${phpIn({ login: `pw_wfuser_${RUN}` })}
            $uid = wp_insert_user( [ 'user_login' => $in['login'], 'user_email' => $in['login'] . '@example.com', 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ] );
            $run = function ( $field, $value ) use ( $uid ) {
                do_action( 'erp_wf_update_field_action', [
                    'action'     => (object) [ 'params' => serialize( [ 'field_name' => $field, 'field_value' => $value ] ) ],
                    'event_data' => [ 'event' => 'created_user', 'object' => 'user', 'id' => $uid ],
                ] );
                clean_user_cache( $uid );
            };
            $run( 'role', 'administrator' );
            $run( 'user_pass', 'pw-owned' );
            $run( 'first_name', 'PWRenamed' );
            $u = get_userdata( $uid );
            echo wp_json_encode( [ 'roles' => array_values( $u->roles ), 'first_name' => $u->first_name ] );
        `);
        expect(result.roles, 'role not escalated').toEqual(['subscriber']);
        expect(result.first_name, 'offered field still updated').toBe('PWRenamed');
    });

    test('Send Email filters subject and body when the mail is sent (covers rows saved before the fix)', { tag: ['@pro', '@workflow', '@admin', '@security'] }, async () => {
        const mail = phpJson<{ to: string; subject: string; message: string }>(`
            $sent = null;
            add_filter( 'pre_wp_mail', function ( $short, $atts ) use ( &$sent ) { $sent = $atts; return true; }, 10, 2 );
            do_action( 'erp_wf_send_email_action', [
                'action'     => (object) [ 'params' => serialize( [
                    'subject'     => 'PW <b>subject</b><script>x()</script>',
                    'message'     => '<p><strong>Hi [first_name]</strong></p><script>alert(1)</script><img src=x onerror="alert(2)">',
                    'send_itself' => 'true',
                ] ) ],
                'event_data' => [ 'event' => 'created_user', 'object' => 'user', 'id' => 0, 'email' => 'pw_wf_mail@example.com', 'first_name' => 'PW' ],
            ] );
            echo wp_json_encode( $sent ? [ 'to' => is_array( $sent['to'] ) ? implode( ',', $sent['to'] ) : $sent['to'], 'subject' => $sent['subject'], 'message' => $sent['message'] ] : [ 'to' => '', 'subject' => '', 'message' => '' ] );
        `);
        expect(mail.to, 'mail was sent').toContain('pw_wf_mail@example.com');
        expect(mail.subject).not.toMatch(/<|script/i);
        expect(mail.message).not.toMatch(/<script/i);
        expect(mail.message).not.toMatch(/onerror/i);
        expect(mail.message).toContain('<strong>Hi PW</strong>');
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// HR document storage + permission checked download (erp-pro#1111 / erp-pro#957)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('HR documents are stored privately and served through erp_doc_download (erp-pro#1111, erp-pro#957)', () => {
    test('HR manager uploads a document for an employee into uploads/erp-documents/<random>/', { tag: ['@pro', '@documents', '@hrManager', '@security'] }, async () => {
        const res = await uploadDoc('hr', ids.owner, doc.name, doc.body);
        expect(res.text, 'upload succeeded').toMatch(/"success"\s*:\s*true/);
        expect(res.text, 'upload response links the endpoint').toContain('erp_doc_download');
        expect(res.text).not.toContain('/wp-content/uploads/');

        const row = await docRow(ids.owner, doc.name);
        expect(row, 'relationship row').toBeTruthy();
        doc.attachmentId = Number(row!.attachment_id);
        doc.dirId = Number(row!.dir_id);

        const info = phpJson<{ path: string; url: string; htaccess: boolean }>(`${phpIn({ id: doc.attachmentId })}
            $p = get_attached_file( $in['id'] );
            echo wp_json_encode( [ 'path' => $p, 'url' => wp_get_attachment_url( $in['id'] ), 'htaccess' => file_exists( erp_doc_get_storage_dir() . '.htaccess' ) ] );
        `);
        expect(info.path).toMatch(/\/uploads\/erp-documents\/[a-f0-9]{32}\/pw_doc_/);
        expect(info.htaccess, 'deny-all .htaccess written').toBe(true);
        doc.path = info.path;
        doc.directUrl = info.url;
    });

    test('the owner and HR can download; the endpoint streams the file safely', { tag: ['@pro', '@documents', '@employee', '@hrManager', '@admin', '@security'] }, async () => {
        for (const role of ['owner', 'hr', 'admin'] as const) {
            const res = await download(role, doc.attachmentId);
            expect(res.status(), `${role} status`).toBe(200);
            expect(await res.text(), `${role} body`).toBe(doc.body);
            expect(res.headers()['x-content-type-options']).toBe('nosniff');
            expect(res.headers()['content-disposition']).toContain(doc.name);
        }
    });

    for (const role of ['other', 'employee', 'crmAgent', 'crmManager'] as const) {
        test(`${role} cannot download another employee's document`, { tag: ['@pro', '@documents', `@${role}`, '@security'] }, async () => {
            const res = await download(role, doc.attachmentId);
            expect(res.status()).toBe(403);
            expect(await res.text()).not.toContain(doc.body);
        });
    }

    test('anonymous visitors are sent to the login page', { tag: ['@pro', '@documents', '@guest', '@security'] }, async () => {
        const res = await download('anon', doc.attachmentId);
        expect(res.status()).toBe(302);
        expect(res.headers()['location']).toContain('wp-login.php');
        expect(await res.text()).not.toContain(doc.body);
    });

    test('a folder share grants download to that employee only', { tag: ['@pro', '@documents', '@employee', '@security'] }, async () => {
        await dbUtils.dbQuery(
            `INSERT INTO ${T('erp_dir_file_share')} (owner_id, shared_with_id, dir_file_id, source, eid_type, details, created_at, updated_at) VALUES (?, ?, ?, 'local', 'employee', '', NOW(), NOW())`,
            [ids.owner, ids.other, doc.dirId],
        );
        try {
            const shared = await download('other', doc.attachmentId);
            expect(shared.status()).toBe(200);
            expect(await shared.text()).toBe(doc.body);

            const notShared = await download('employee', doc.attachmentId);
            expect(notShared.status()).toBe(403);
        } finally {
            await dbUtils.dbQuery(`DELETE FROM ${T('erp_dir_file_share')} WHERE owner_id = ? AND shared_with_id = ?`, [ids.owner, ids.other]);
        }
    });

    test('the raw file is not served from the uploads folder', { tag: ['@pro', '@documents', '@guest', '@security'] }, async () => {
        expect(doc.directUrl).toMatch(/\/wp-content\/uploads\/erp-documents\/[a-f0-9]{32}\//);
        const res = await ctx.anon!.get(doc.directUrl, { maxRedirects: 0 });
        expect(res.status(), 'Apache honours the deny-all .htaccess').toBe(403);
        expect(await res.text()).not.toContain(doc.body);
    });

    test('listings hand out the endpoint URL, not the uploads URL', { tag: ['@pro', '@documents', '@employee', '@security'] }, async () => {
        const res = await ajax('owner', { action: 'wp-erp-rec-get-files-folders', _wpnonce: nonce.owner!.doc_form_builder_nonce, employee_id: ids.owner, dir_id: 0, source: 'owned_by_me' });
        expect(res.json?.success, res.text).toBe(true);
        const file = (res.json.data.files as { dir_file_name: string; attactment_url: string }[]).find(f => f.dir_file_name === doc.name);
        expect(file, 'owner sees the file').toBeTruthy();
        expect(file!.attactment_url).toContain(`admin-post.php?action=erp_doc_download&file_id=${doc.attachmentId}`);
        expect(file!.attactment_url).not.toContain('/wp-content/uploads/');
    });

    test('documents uploaded before the fix are moved into the protected folder', { tag: ['@pro', '@documents', '@admin', '@security'] }, async () => {
        const name = `pw_legacy_${RUN}.txt`;
        const body = `PW-LEGACY-${RUN}`;
        const legacy = phpJson<{ id: number; path: string; url: string; secured: string }>(`${phpIn({ owner: ids.owner, name, body })}
            global $wpdb;
            wp_set_current_user( 1 );
            $secured = get_option( 'erp_doc_files_secured' );
            $up = wp_upload_bits( $in['name'], null, $in['body'], '2024/01' );
            $id = wp_insert_attachment( [ 'post_mime_type' => 'text/plain', 'post_title' => $in['name'], 'post_status' => 'inherit' ], $up['file'] );
            $next = (int) $wpdb->get_var( $wpdb->prepare( "SELECT MAX(dir_id) FROM {$wpdb->prefix}erp_employee_dir_file_relationship WHERE eid = %d", $in['owner'] ) ) + 1;
            $wpdb->insert( $wpdb->prefix . 'erp_employee_dir_file_relationship', [ 'eid' => $in['owner'], 'dir_id' => $next, 'dir_name' => $in['name'], 'attachment_id' => $id, 'parent_id' => 0, 'is_dir' => 0, 'eid_type' => 'employee', 'created_by' => 1, 'created_at' => current_time( 'mysql' ) ] );
            // Re-arm the one-time migration, starting just below this attachment so only it is in the batch.
            delete_option( 'erp_doc_files_secured' );
            delete_transient( 'erp_doc_securing_files' );
            update_option( 'erp_doc_files_secured_last_id', $id - 1, false );
            echo wp_json_encode( [ 'id' => $id, 'path' => get_attached_file( $id ), 'url' => wp_get_attachment_url( $id ), 'secured' => (string) $secured ] );
        `);
        expect(legacy.path).toMatch(/\/uploads\/2024\/01\//);

        try {
            const before = await ctx.anon!.get(legacy.url, { maxRedirects: 0 });
            expect(await before.text(), 'legacy file is public before the move').toBe(body);

            // admin_init (fired by admin-ajax.php) runs erp_doc_secure_existing_files()
            for (let i = 0; i < 3; i++) {
                await ctx.admin!.get(`${ADMIN_AJAX}?action=pw_noop`);
                if ((await dbUtils.getOptionValue('erp_doc_files_secured')) !== undefined) break;
            }
            const moved = phpJson<{ path: string; exists: boolean; old: boolean; secured: string }>(`${phpIn({ id: legacy.id, old: legacy.path })}
                $p = get_attached_file( $in['id'] );
                echo wp_json_encode( [ 'path' => $p, 'exists' => file_exists( $p ), 'old' => file_exists( $in['old'] ), 'secured' => (string) get_option( 'erp_doc_files_secured' ) ] );
            `);
            expect(moved.path).toMatch(/\/uploads\/erp-documents\/[a-f0-9]{32}\//);
            expect(moved.exists).toBe(true);
            expect(moved.old, 'old public copy gone').toBe(false);
            expect(moved.secured, 'migration marked finished').toBe('1');

            const after = await ctx.anon!.get(legacy.url, { maxRedirects: 0 });
            expect(after.status(), 'old public URL no longer serves it').toBe(404);
            expect(await after.text()).not.toContain(body);

            const owner = await download('owner', legacy.id);
            expect(await owner.text(), 'still served through the endpoint').toBe(body);
        } finally {
            php(`${phpIn({ secured: legacy.secured })}
                if ( '' !== $in['secured'] ) { update_option( 'erp_doc_files_secured', $in['secured'], false ); }
                delete_option( 'erp_doc_files_secured_last_id' );
            `);
        }
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// Legacy document handlers (erp-pro#1112) + contact ownership (erp-pro#1118)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Legacy document handlers check the employee (erp-pro#1112, erp-pro#1020)', () => {
    test('another employee cannot list, create in, rename or delete in someone else\'s documents', { tag: ['@pro', '@documents', '@employee', '@security'] }, async () => {
        expect(doc.dirId).toBeGreaterThan(0);
        const n = nonce.other!.doc_form_builder_nonce;

        const list = await ajax('other', { action: 'wp-erp-rec-get-files-folders', _wpnonce: n, employee_id: ids.owner, dir_id: 0, source: 'owned_by_me' });
        expect(list.json?.success, list.text).toBe(false);
        expect(list.text).not.toContain(doc.name);

        const mkdir = await ajax('other', { action: 'wp-erp-rec-createDir', _wpnonce: n, employee_id: ids.owner, parent_id: 0, dirName: `pw_intruder_${RUN}`, source: 'owned_by_me' });
        expect(mkdir.json?.success, mkdir.text).toBe(false);

        const rename = await ctx.other!.get(
            `${ADMIN_AJAX}?action=wp-erp-rec-renameDirFile&_wpnonce=${n}&employee_id=${ids.owner}&parent_id=0&target_dir_id=${doc.dirId}&dirName=pw_renamed_${RUN}&dir_file_type=file&source=owned_by_me`,
            { headers: { Referer: HR_REFERER } },
        );
        expect(await rename.text()).toMatch(/"success"\s*:\s*false/);

        const del = await ajax('other', { action: 'wp-erp-rec-deleteDirFile', _wpnonce: n, employee_id: ids.owner, parent_id: 0, selected_dir_file_id: JSON.stringify([String(doc.dirId)]), source: 'owned_by_me' });
        expect(del.json?.success, del.text).toBe(false);

        const rows = await dbUtils.dbQuery<{ dir_name: string }>(`SELECT dir_name FROM ${T('erp_employee_dir_file_relationship')} WHERE eid = ? AND eid_type = 'employee'`, [ids.owner]);
        expect(rows.map(r => r.dir_name)).toContain(doc.name);
        expect(rows.map(r => r.dir_name)).not.toContain(`pw_intruder_${RUN}`);
        expect(rows.map(r => r.dir_name)).not.toContain(`pw_renamed_${RUN}`);
    });

    test('a request without the document nonce is refused', { tag: ['@pro', '@documents', '@employee', '@security'] }, async () => {
        const res = await ajax('owner', { action: 'wp-erp-rec-get-files-folders', employee_id: ids.owner, dir_id: 0, source: 'owned_by_me' });
        expect(res.json?.success, res.text).toBe(false);
        expect(res.text).toMatch(/Nonce verification failed/);
        expect(res.text).not.toContain(doc.name);
    });

    test('the owner and HR manager can still manage the documents', { tag: ['@pro', '@documents', '@employee', '@hrManager', '@security'] }, async () => {
        const mine = await ajax('owner', { action: 'wp-erp-rec-createDir', _wpnonce: nonce.owner!.doc_form_builder_nonce, employee_id: ids.owner, parent_id: 0, dirName: `pw_dir_own_${RUN}`, source: 'owned_by_me' });
        expect(mine.json?.success, mine.text).toBe(true);
        const hr = await ajax('hr', { action: 'wp-erp-rec-get-files-folders', _wpnonce: nonce.hr!.doc_form_builder_nonce, employee_id: ids.owner, dir_id: 0, source: 'owned_by_me' });
        expect(hr.json?.success, hr.text).toBe(true);
        expect(hr.text).toContain(doc.name);
        expect(hr.text).toContain(`pw_dir_own_${RUN}`);
    });

    test('another employee cannot move items in someone else\'s documents', { tag: ['@pro', '@documents', '@employee', '@security'] }, async () => {
        // moveNow has no nonce check yet (the shipped hr-frontend bundle does not send one),
        // so the employee check is the only guard.
        const res = await ajax('other', { action: 'wp-erp-doc-moveNow', employee_id: ids.owner, parent_id: 0, new_parent_id: 4242, selectedDirFile: JSON.stringify([doc.dirId]) });
        expect(res.json?.success, res.text).toBe(false);
        expect(res.text).toMatch(NO_PERMISSION);
        const [row] = await dbUtils.dbQuery<{ parent_id: number }>(`SELECT parent_id FROM ${T('erp_employee_dir_file_relationship')} WHERE eid = ? AND dir_id = ? AND eid_type = 'employee'`, [ids.owner, doc.dirId]);
        expect(Number(row!.parent_id), 'item not moved').toBe(0);
    });

    test('hr-frontend treats a refused document action as an error', { tag: ['@pro', '@documents', '@hrFrontend', '@employee'] }, async () => {
        // erp-pro#1112 changed src/utils/api.js (ensureSuccess + moveNow nonce) but did not rebuild
        // assets/js/hr-frontend.js, which is what the site loads. Activates once the bundle is rebuilt.
        const res = await ctx.anon!.get(toPath('wp-content/plugins/erp-pro/modules/pro/hr-frontend/assets/js/hr-frontend.js'));
        expect(res.status()).toBe(200);
        const bundle = await res.text();
        // Pre-change build: renameItem returns axios' response untouched, so `success:false` reads as done.
        const rawRename = /return await [\w$]+(\.[\w$]+)*\.get\([\w$]+\(\),\{params:\{action:"wp-erp-rec-renameDirFile"/;
        const rawMove = /append\("selectedDirFile",JSON\.stringify\([\w$]+\)\);return await/;
        test.skip(rawRename.test(bundle) || rawMove.test(bundle), 'shipped hr-frontend bundle predates the api.js change (erp-pro#1112 did not rebuild assets/js/hr-frontend.js)');
        expect(bundle, 'renameItem response is checked').not.toMatch(rawRename);
        expect(bundle, 'moveItems appends the nonce before posting').not.toMatch(rawMove);
    });

    test('the v1 hrm/docs REST routes are no longer registered', { tag: ['@pro', '@documents', '@guest', '@security'] }, async () => {
        for (const route of [`/erp/v1/hrm/docs/${ids.owner}`, `/erp/v1/hrm/docs/${ids.owner}/file/1`]) {
            const res = await ctx.anon!.get(restUrl(route));
            expect(res.status(), route).toBe(404);
            expect((await res.json()).code, route).toBe('rest_no_route');
        }
    });
});

test.describe('Contact documents follow contact ownership (erp-pro#1118, erp-pro#1116)', () => {
    const crm = { Referer: CRM_REFERER };

    test('CRM agent cannot list or create folders on a contact they do not own', { tag: ['@pro', '@documents', '@crm', '@crmAgent', '@security'] }, async () => {
        const n = nonce.crmAgent!.doc_form_builder_nonce;
        const list = await ajax('crmAgent', { action: 'wp-erp-rec-get-files-folders', _wpnonce: n, employee_id: ids.otherContact, dir_id: 0, source: 'owned_by_me' }, crm);
        expect(list.json?.success, list.text).toBe(false);
        expect(list.text).toMatch(NO_PERMISSION);

        const dirName = `pw_agent_foreign_${RUN}`;
        const mkdir = await ajax('crmAgent', { action: 'wp-erp-rec-createDir', _wpnonce: n, employee_id: ids.otherContact, parent_id: 0, dirName, source: 'owned_by_me' }, crm);
        expect(mkdir.json?.success, mkdir.text).toBe(false);
        expect(await dbUtils.dbQuery(`SELECT id FROM ${T('erp_employee_dir_file_relationship')} WHERE dir_name = ?`, [dirName])).toHaveLength(0);
    });

    test('CRM agent can work on their own contact', { tag: ['@pro', '@documents', '@crm', '@crmAgent', '@security'] }, async () => {
        const n = nonce.crmAgent!.doc_form_builder_nonce;
        const dirName = `pw_agent_own_${RUN}`;
        const mkdir = await ajax('crmAgent', { action: 'wp-erp-rec-createDir', _wpnonce: n, employee_id: ids.agentContact, parent_id: 0, dirName, source: 'owned_by_me' }, crm);
        expect(mkdir.json?.success, mkdir.text).toBe(true);
        const list = await ajax('crmAgent', { action: 'wp-erp-rec-get-files-folders', _wpnonce: n, employee_id: ids.agentContact, dir_id: 0, source: 'owned_by_me' }, crm);
        expect(list.json?.success, list.text).toBe(true);
        expect(list.text).toContain(dirName);
    });

    test('CRM manager can work on any contact; an employee cannot via a contact Referer', { tag: ['@pro', '@documents', '@crm', '@crmManager', '@employee', '@security'] }, async () => {
        const mgr = await ajax('crmManager', { action: 'wp-erp-rec-get-files-folders', _wpnonce: nonce.crmManager!.doc_form_builder_nonce, employee_id: ids.otherContact, dir_id: 0, source: 'owned_by_me' }, crm);
        expect(mgr.json?.success, mgr.text).toBe(true);

        const emp = await ajax('employee', { action: 'wp-erp-rec-get-files-folders', _wpnonce: nonce.employee!.doc_form_builder_nonce, employee_id: ids.agentContact, dir_id: 0, source: 'owned_by_me' }, crm);
        expect(emp.json?.success, emp.text).toBe(false);
        expect(emp.text).not.toContain(`pw_agent_own_${RUN}`);
    });

    test('a contact document downloads for the owning agent and CRM manager only', { tag: ['@pro', '@documents', '@crm', '@crmAgent', '@crmManager', '@security'] }, async () => {
        const name = `pw_contact_doc_${RUN}.txt`;
        const body = `PW-CONTACT-DOC-${RUN}`;
        const up = await uploadDoc('crmAgent', ids.agentContact, name, body, CRM_REFERER);
        expect(up.text, 'agent uploads to own contact').toMatch(/"success"\s*:\s*true/);
        const row = await docRow(ids.agentContact, name, 'contact');
        expect(row).toBeTruthy();

        for (const role of ['crmAgent', 'crmManager'] as const) {
            const res = await download(role, Number(row!.attachment_id));
            expect(res.status(), role).toBe(200);
            expect(await res.text()).toBe(body);
        }
        for (const role of ['employee', 'hr'] as const) {
            const res = await download(role, Number(row!.attachment_id));
            expect(res.status(), role).toBe(403);
            expect(await res.text()).not.toContain(body);
        }

        // An agent cannot upload to a contact they do not own.
        const foreign = await uploadDoc('crmAgent', ids.otherContact, `pw_contact_foreign_${RUN}.txt`, body, CRM_REFERER);
        expect(foreign.text).toMatch(/"success"\s*:\s*false/);
        expect(await docRow(ids.otherContact, `pw_contact_foreign_${RUN}.txt`, 'contact')).toBeUndefined();
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// Attendance (erp-pro#1112)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Legacy attendance endpoints are hardened (erp-pro#1112, erp-pro#1020)', () => {
    const rest = (role: Role) => ({ 'X-WP-Nonce': nonce[role]!.wp_rest });

    test('the unauthenticated export_to_csv table dump is gone', { tag: ['@pro', '@attendance', '@guest', '@security'] }, async () => {
        const res = await ctx.anon!.get(toPath('?submit_export=1&erp_att_db_table=shifts'), { maxRedirects: 0 });
        const headers = res.headers();
        expect(headers['content-type'] ?? '').not.toContain('csv');
        expect(headers['content-disposition'] ?? '').not.toContain('erp_attendance_shifts');
        expect(await res.text()).not.toMatch(/^\s*"?id"?\s*,\s*"?name"?/);
    });

    test('an employee cannot read another employee\'s attendance logs / reports over REST', { tag: ['@pro', '@attendance', '@employee', '@security'] }, async () => {
        for (const route of [`/erp/v1/hrm/attendance/logs/${ids.owner}`, `/erp/v1/hrm/attendance/reports/${ids.owner}`]) {
            const res = await ctx.other!.get(restUrl(route), { headers: rest('other') });
            expect([401, 403], `${route} -> ${res.status()}`).toContain(res.status());
        }
        const own = await ctx.other!.get(restUrl(`/erp/v1/hrm/attendance/logs/${ids.other}`), { headers: rest('other') });
        expect(own.status(), 'own logs').toBe(200);
        const hr = await ctx.hr!.get(restUrl(`/erp/v1/hrm/attendance/logs/${ids.owner}`), { headers: rest('hr') });
        expect(hr.status(), 'HR reads anyone').toBe(200);
    });

    test('employee-id helper routes are HR only', { tag: ['@pro', '@attendance', '@employee', '@hrManager', '@security'] }, async () => {
        const emp = await ctx.employee!.get(restUrl('/erp/v1/hrm/attendance/employees_user_id'), { headers: rest('employee') });
        expect([401, 403]).toContain(emp.status());
        const hr = await ctx.hr!.get(restUrl('/erp/v1/hrm/attendance/employees_user_id'), { headers: rest('hr') });
        expect(hr.status()).toBe(200);
    });

    test('an employee cannot punch for someone else or back-date a punch', { tag: ['@pro', '@attendance', '@employee', '@security'] }, async () => {
        const forOther = await ctx.other!.post(restUrl('/erp/v1/hrm/attendance/logs'), { headers: rest('other'), data: { user_id: ids.owner } });
        expect([401, 403]).toContain(forOther.status());
        const backdated = await ctx.other!.post(restUrl('/erp/v1/hrm/attendance/logs'), { headers: rest('other'), data: { user_id: ids.other, timestamp: 1700000000 } });
        expect([401, 403]).toContain(backdated.status());
        const logs = await dbUtils.dbQuery(`SELECT id FROM ${T('erp_attendance_log')} WHERE user_id IN (?, ?)`, [ids.owner, ids.other]);
        expect(logs, 'no attendance written').toHaveLength(0);
    });

    test('shift and date-wide AJAX actions are HR only', { tag: ['@pro', '@attendance', '@employee', '@hrManager', '@security'] }, async () => {
        const shiftName = `pw_shift_${RUN}`;
        const shift = { action: 'erp_new_shift_save', shiftName, startTime: '09:00:00', endTime: '17:00:00' };

        const emp = await ajax('employee', { ...shift, nonce: nonce.employee!['wp-erp-attendance'] });
        expect(emp.json?.success, emp.text).toBe(false);
        expect(emp.text).toMatch(NO_PERMISSION);
        expect(await dbUtils.dbQuery(`SELECT id FROM ${T('erp_attendance_shifts')} WHERE name = ?`, [shiftName]), 'no shift created').toHaveLength(0);

        const byDate = await ajax('employee', { action: 'erp_get_att_by_date', nonce: nonce.employee!['wp-erp-attendance'], date: '2024-01-01' });
        expect(byDate.json?.success, byDate.text).toBe(false);

        const hr = await ajax('hr', { ...shift, nonce: nonce.hr!['wp-erp-attendance'] });
        // save_new_shift answers nothing on success (admin-ajax prints "0"); the row is the evidence.
        expect(hr.text).not.toMatch(NO_PERMISSION);
        expect(await dbUtils.dbQuery(`SELECT id FROM ${T('erp_attendance_shifts')} WHERE name = ?`, [shiftName])).toHaveLength(1);
    });

    test('IP whitelist ignores a spoofed Client-IP header', { tag: ['@pro', '@attendance', '@employee', '@security'] }, async () => {
        const allowed = '203.0.113.77';
        const before = {
            enabled: await dbUtils.getOptionValue<string>('erp_at_enable_ip_restriction'),
            ips: await dbUtils.getOptionValue<string>('erp_at_whitelisted_ips'),
        };
        await dbUtils.setOptionValue('erp_at_enable_ip_restriction', 'yes');
        await dbUtils.setOptionValue('erp_at_whitelisted_ips', allowed);
        try {
            // Client-IP only: wp-env's Apache (official WordPress image) runs mod_remoteip with
            // X-Forwarded-For trusted from private ranges, so on this stack XFF rewrites REMOTE_ADDR
            // before PHP runs and cannot be used to test the plugin.
            const res = await ajax('other', { action: 'erp_att_save_self_attendance', _wpnonce: nonce.other!['wp-erp-attendance'] }, { 'Client-IP': allowed });
            expect(res.json?.success, res.text).toBe(false);
            expect(res.text).toMatch(/not allowed to check in or out from this IP/);
            const logs = await dbUtils.dbQuery(`SELECT id FROM ${T('erp_attendance_log')} WHERE user_id = ?`, [ids.other]);
            expect(logs, 'no punch recorded').toHaveLength(0);
        } finally {
            for (const [key, value] of [['erp_at_enable_ip_restriction', before.enabled], ['erp_at_whitelisted_ips', before.ips]] as const) {
                if (value === undefined) {
                    await dbUtils.dbQuery(`DELETE FROM ${T('options')} WHERE option_name = ?`, [key]);
                } else {
                    await dbUtils.setOptionValue(key, value);
                }
            }
        }
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// Payroll (erp-pro#1112)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Payroll AJAX actions are HR only (erp-pro#1112, erp-pro#1020)', () => {
    const payitem = `pw_payitem_${RUN}`;

    test('HR manager can add a pay item; an employee with the payroll nonce cannot', { tag: ['@pro', '@payroll', '@employee', '@hrManager', '@security'] }, async () => {
        const emp = await ajax('employee', { action: 'erp_payroll_add_payitem', _wpnonce: nonce.employee!.payroll_nonce, paytype: 'Allowance', payitem: `${payitem}_emp`, amounttype: '1' });
        expect(emp.json?.success, emp.text).toBe(false);
        expect(emp.text).toMatch(NO_PERMISSION);
        expect(await dbUtils.dbQuery(`SELECT id FROM ${T('erp_hr_payroll_payitem')} WHERE payitem = ?`, [`${payitem}_emp`])).toHaveLength(0);

        const hr = await ajax('hr', { action: 'erp_payroll_add_payitem', _wpnonce: nonce.hr!.payroll_nonce, paytype: 'Allowance', payitem, amounttype: '1' });
        expect(hr.json?.success, hr.text).toBe(true);
    });

    test('an employee cannot read payroll data (handler had no nonce or capability check)', { tag: ['@pro', '@payroll', '@employee', '@crmAgent', '@security'] }, async () => {
        for (const role of ['employee', 'crmAgent'] as const) {
            const res = await ajax(role, { action: 'erp_payroll_get_payitem' });
            expect(res.json?.success, `${role}: ${res.text}`).toBe(false);
            expect(res.text).toMatch(NO_PERMISSION);
            expect(res.text).not.toContain(payitem);
        }
        const hr = await ajax('hr', { action: 'erp_payroll_get_payitem' });
        expect(hr.json?.success, hr.text).toBe(true);
        expect(hr.text).toContain(payitem);
    });

    test('the pay-calendar employee dropdown works for the HR manager role', { tag: ['@pro', '@payroll', '@hrManager', '@employee', '@security'] }, async () => {
        // It checked a non-existent `hr_manager` capability, so only admins could use it.
        const hr = await ajax('hr', { action: 'erp_payroll_get_available_employees', _wpnonce: nonce.hr!.payroll_nonce, pay_type: 'monthly' });
        expect(hr.json?.success, hr.text).toBe(true);
        expect(typeof hr.json?.data, 'id => name map (or [] when empty)').toBe('object');
        expect(Object.keys(hr.json.data), 'unassigned monthly employee listed').toContain(String(ids.owner));

        const emp = await ajax('employee', { action: 'erp_payroll_get_available_employees', _wpnonce: nonce.employee!.payroll_nonce, pay_type: 'monthly' });
        expect(emp.json?.success, emp.text).toBe(false);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// Recruitment (erp-pro#1112)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Legacy recruitment actions need recruitment access (erp-pro#1112, erp-pro#1020)', () => {
    for (const role of ['employee', 'crmAgent'] as const) {
        test(`${role} cannot read the candidate report or ratings`, { tag: ['@pro', '@recruitment', `@${role}`, '@security'] }, async () => {
            const report = await ajax(role, { action: 'erp-rec-get-candidate-report', jobid: 0 });
            expect(report.json?.success, report.text).toBe(false);
            expect(report.text).toMatch(NO_PERMISSION);
            expect(report.text, 'applicant data not leaked').not.toContain(applicant.email);

            const rating = await ajax(role, { action: 'wp-erp-rec-get-applicationAvgRating', application_id: ids.application });
            expect(rating.json?.success, rating.text).toBe(false);
        });
    }

    test('HR manager reads the candidate report', { tag: ['@pro', '@recruitment', '@hrManager', '@security'] }, async () => {
        const report = await ajax('hr', { action: 'erp-rec-get-candidate-report', jobid: 0 });
        expect(report.json?.success, report.text).toBe(true);
        expect(report.text).toContain(applicant.email);
    });

    test('the public application form action stays open to visitors', { tag: ['@pro', '@recruitment', '@guest', '@security'] }, async () => {
        const res = await ajax('anon', { action: 'wp-erp-rec-job-seeker-creation' });
        expect(res.status).toBeLessThan(500);
        expect(res.text, 'not refused by the recruitment permission gate').not.toMatch(/sufficient permissions/);
    });

    test('candidate CSV export is refused for an employee and works for HR', { tag: ['@pro', '@recruitment', '@employee', '@hrManager', '@security'] }, async () => {
        const url = toPath('wp-admin/admin.php?page=erp-hr&section=recruitment&func=candidate-report-csv&jobid=0');
        const emp = await ctx.employee!.get(url, { maxRedirects: 0 });
        const empBody = await emp.text();
        expect(empBody).toMatch(/sufficient permissions/);
        expect(empBody).not.toContain(applicant.email);
        expect(emp.headers()['content-type'] ?? '').not.toContain('csv');

        const hr = await ctx.hr!.get(url, { maxRedirects: 0 });
        expect(await hr.text()).toContain(applicant.email);
    });

    test('hiring is refused for an employee and a duplicate email does not mark the candidate hired', { tag: ['@pro', '@recruitment', '@employee', '@hrManager', '@security'] }, async () => {
        const form = (role: Role, email: string) => ({
            _wpnonce: nonce[role]!.wp_erp_rec_make_him_employee_nonce,
            submit: 'Submit',
            applicant_id: String(ids.applicant),
            first_name: 'PW',
            last_name: `Hire${RUN}`,
            email,
            work_hiring_date: '2024-01-01',
            work_status: 'active',
            employment_type: 'permanent',
            work_pay_type: 'monthly',
        });
        const url = toPath('wp-admin/admin.php?page=erp-hr&section=recruitment');
        const hired = async () =>
            dbUtils.dbQuery(`SELECT meta_id FROM ${T('erp_peoplemeta')} WHERE erp_people_id = ? AND meta_key = 'status' AND meta_value = 'hired'`, [ids.applicant]);

        const emp = await ctx.employee!.post(url, { form: form('employee', `pw_hire_emp_${RUN}@example.com`), maxRedirects: 0 });
        expect(await emp.text()).toMatch(/sufficient permissions/);
        expect(await hired(), 'employee could not hire').toHaveLength(0);
        expect(await dbUtils.dbQuery(`SELECT ID FROM ${T('users')} WHERE user_email = ?`, [`pw_hire_emp_${RUN}@example.com`])).toHaveLength(0);

        const dup = await ctx.hr!.post(url, { form: form('hr', 'employee1@example.com'), maxRedirects: 0 });
        expect(dup.status(), 'no redirect to a phantom employee').not.toBe(302);
        expect(await dup.text()).toMatch(/already exist/);
        expect(await hired(), 'candidate not marked hired').toHaveLength(0);
    });
});
