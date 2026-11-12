import { test, expect, request } from '@utils/test';
import type { APIRequestContext, Page } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { BASE_URL, exeCommandWpcli, restUrl, toPath } from '@utils/helpers';

/**
 * Regression specs for the HR / CRM / core security fixes merged for the 1.17.12 cycle.
 *
 *   wp-erp#1676  security audit hardening (contact group sanitize, PDF action nonce, CSV
 *                product lookups prepared, CRM customer_get / segment delete / schedule
 *                details checks, birthday wish, performance delete, leave history)
 *   wp-erp#1677  v1 employee update can't change a more privileged account's email
 *   wp-erp#1669  leave request REST routes scoped to the owner (erp-pro#1094)
 *   wp-erp#1670  HR job data access control (erp-pro#1096, HR part)
 *   wp-erp#1671  department / designation titles sanitized and escaped (erp-pro#1098)
 *   wp-erp#1668  CRM subscription hash not cast to int (erp-pro#1095)
 *   wp-erp#1683  v1 leave policy REST create maps to real fields (erp-pro#1120)
 *   wp-erp#1681  disabled email templates still sent (erp-pro#1108)
 *
 * Every fixture (employees, leave rows, CRM rows, options) is created by one `wp eval` in
 * beforeAll and removed in afterAll; everything is tagged with a per-run `pwhc…` marker.
 *
 * AJAX nonces are minted server-side for the real logged-in session of each role (the
 * storageState's logged_in cookie supplies the session token), which yields exactly the
 * token the role would scrape from its own admin pages. The fixes under test are the
 * capability / ownership checks behind the nonce, not nonce availability.
 */

// One worker, in file order: the fixtures are shared and some specs touch site options.
test.describe.configure({ mode: 'default' });

const ADMIN_AJAX = toPath('wp-admin/admin-ajax.php');
const P = `${process.env.DB_PREFIX ?? 'wp'}_`;
const EMPLOYEE_ID = Number(process.env.EMPLOYEE_USER_ID ?? 5);
const TAGS = (...t: string[]) => ({ tag: ['@lite', '@security', ...t] });
// The HR erp/v1 REST routes are served by WP ERP Pro (2.0.0+), so those tests run on Pro runs only
const PRO_TAGS = (...t: string[]) => ({ tag: ['@pro', '@security', ...t] });

const ACTORS = {
    admin: { file: data.auth.adminFile, uid: 1 },
    hrManager: { file: data.auth.hrManagerFile, uid: Number(process.env.HR_MANAGER_ID ?? 2) },
    crmManager: { file: data.auth.crmManagerFile, uid: Number(process.env.CRM_MANAGER_ID ?? 3) },
    employee: { file: data.auth.employeeFile, uid: EMPLOYEE_ID },
    crmAgent: { file: data.auth.crmAgentFile, uid: Number(process.env.CRM_AGENT_ID ?? 6) },
} as const;
type Actor = keyof typeof ACTORS;
const NONCE_ACTIONS = ['wp_rest', 'wp-erp-hr-nonce', 'wp-erp-crm-nonce', 'erp-hr-empl-leave-history', 'erp-settings-nonce'] as const;
type NonceAction = (typeof NONCE_ACTIONS)[number];

// ─── wp-cli helpers (local copy of the adminScreens pattern) ─────────────────
function php(code: string): string {
    const b64 = Buffer.from(code).toString('base64');
    return exeCommandWpcli(`eval 'eval(base64_decode("${b64}"));'`);
}

/** Run PHP that calls `pw_out( $value )`; returns the JSON it printed. */
function phpJson<T>(code: string): T {
    const out = php(`function pw_out( $v ) { echo "\\n@@PW@@" . wp_json_encode( $v ) . "@@PW@@\\n"; }\n${code}`);
    const m = out.match(/@@PW@@([\s\S]*?)@@PW@@/);
    expect(m, `wp eval printed JSON, got: ${out.slice(0, 500)}`).toBeTruthy();
    return JSON.parse((m as RegExpMatchArray)[1] ?? 'null') as T;
}

const phpStr = (v: unknown) => `json_decode( base64_decode( '${Buffer.from(JSON.stringify(v)).toString('base64')}' ), true )`;

function loggedInCookie(stateFile: string): { name: string; value: string } {
    const state = JSON.parse(readFileSync(stateFile, 'utf8')) as { cookies: { name: string; value: string }[] };
    const c = state.cookies.find(k => k.name.startsWith('wordpress_logged_in_'));
    expect(c, `logged_in cookie in ${stateFile}`).toBeTruthy();
    return { name: (c as { name: string }).name, value: decodeURIComponent((c as { value: string }).value) };
}

// ─── shared state ─────────────────────────────────────────────────────────────
type Seed = {
    tag: string;
    victim: number;
    sub: number;
    priv: number;
    plain: number;
    leave: number;
    leave2: number;
    fy: number;
    created_fy: number;
    policy: number;
    ent_own: number;
    ent_victim: number;
    req_own: number;
    req_victim: number;
    hist_own: number;
    hist_victim: number;
    perf_victim: number;
    perf_sub: number;
    contact_admin: number;
    contact_agent: number;
    ss_admin: number;
    ss_agent: number;
    activity: number;
    group: number;
    sub_a: number;
    sub_b: number;
    hash_a: string;
    hash_b: string;
    dept_raw: number;
    desig_raw: number;
    ann_dept: number;
    ann_desig: number;
    employee_row: Record<string, string | number | null>;
};

let S: Seed;
let nonces: Record<Actor, Record<NonceAction, string>>;
const ctxs: Partial<Record<Actor, APIRequestContext>> = {};
const extraCleanup: string[] = [];

const SECRET = {
    leave: () => `PW-SECRET-LEAVE ${S.tag}`,
    job: () => `PW-SECRET-JOB ${S.tag}`,
    perf: () => `PW-SECRET-PERF ${S.tag}`,
    activity: () => `PW-SECRET-SCHEDULE ${S.tag}`,
};

async function ctx(actor: Actor): Promise<APIRequestContext> {
    if (!ctxs[actor]) {
        ctxs[actor] = await request.newContext({ baseURL: BASE_URL, storageState: ACTORS[actor].file, ignoreHTTPSErrors: true });
    }
    return ctxs[actor] as APIRequestContext;
}

async function ajax(actor: Actor, form: Record<string, string | number>, nonceAction?: NonceAction) {
    const body: Record<string, string> = Object.fromEntries(Object.entries(form).map(([k, v]) => [k, String(v)]));
    if (nonceAction) body._wpnonce = nonces[actor][nonceAction];
    const res = await (await ctx(actor)).post(ADMIN_AJAX, { form: body });
    return { status: res.status(), body: await res.text() };
}

const succeeded = (body: string) => /"success"\s*:\s*true/.test(body);
const refused = (status: number, body: string) =>
    status >= 400 || ['0', '-1'].includes(body.trim()) || /"success"\s*:\s*false/.test(body);

function withQuery(url: string, q: Record<string, string | number>) {
    const qs = new URLSearchParams(Object.entries(q).map(([k, v]) => [k, String(v)])).toString();
    return `${url}${url.includes('?') ? '&' : '?'}${qs}`;
}

async function rest(actor: Actor, method: string, route: string, opts: { query?: Record<string, string | number>; body?: Record<string, unknown> } = {}) {
    let url = restUrl(`/erp/v1/${route}`);
    if (opts.query) url = withQuery(url, opts.query);
    const res = await (await ctx(actor)).fetch(url, {
        method,
        headers: { 'X-WP-Nonce': nonces[actor].wp_rest, ...(opts.body ? { 'Content-Type': 'application/json' } : {}) },
        ...(opts.body ? { data: JSON.stringify(opts.body) } : {}),
    });
    const text = await res.text();
    let json: unknown = undefined;
    try {
        json = JSON.parse(text);
    } catch {
        /* not json */
    }
    return { status: res.status(), text, json: json as any };
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
async function q(sql: string, params: unknown[] = []): Promise<any[]> {
    return dbUtils.dbQuery(sql, params);
}

// ─── seed / cleanup ───────────────────────────────────────────────────────────
const SEED_PHP = String.raw`
wp_set_current_user( 1 );
global $wpdb;
$p   = $wpdb->prefix;
$tag = 'pwhc' . substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyz' ), 0, 6 );
$now = time();
$emp_uid = EMPLOYEE_UID;
$o   = [ 'tag' => $tag, 'created_fy' => 0 ];

$o['employee_row'] = $wpdb->get_row( $wpdb->prepare( "SELECT department, designation, pay_rate, pay_type, type, status, employee_id FROM {$p}erp_hr_employees WHERE user_id = %d", $emp_uid ), ARRAY_A );

foreach ( [ 'victim', 'sub', 'priv', 'plain' ] as $slug ) {
    $id = erp_hr_employee_create( [
        'user_email' => "{$tag}_{$slug}@example.com",
        'personal'   => [ 'first_name' => 'Pw' . ucfirst( $slug ), 'last_name' => ucfirst( $tag ) ],
        'work'       => [ 'hiring_date' => '2024-01-01', 'status' => 'active', 'type' => 'permanent' ],
    ] );
    if ( ! is_numeric( $id ) ) { pw_out( [ 'error' => "employee $slug: $id" ] ); return; }
    $o[ $slug ] = (int) $id;
}
$wpdb->update( "{$p}erp_hr_employees", [ 'reporting_to' => $emp_uid ], [ 'user_id' => $o['sub'] ] );
( new WP_User( $o['priv'] ) )->add_role( 'editor' );

// Leave: two leave types, a financial year (reuse the current one if any), policy, entitlements, requests
foreach ( [ 'leave' => 'Pw leave ', 'leave2' => 'Pw leave two ' ] as $k => $n ) {
    $wpdb->insert( "{$p}erp_hr_leaves", [ 'name' => $n . $tag, 'description' => '', 'created_at' => $now, 'updated_at' => $now ] );
    $o[ $k ] = (int) $wpdb->insert_id;
}
$fy = erp_hr_get_financial_year_from_date();
if ( empty( $fy ) ) {
    $y = (int) erp_current_datetime()->format( 'Y' );
    $wpdb->insert( "{$p}erp_hr_financial_years", [
        'fy_name' => "pw $tag", 'start_date' => erp_current_datetime()->modify( "$y-01-01 00:00:00" )->getTimestamp(),
        'end_date' => erp_current_datetime()->modify( "$y-12-31 23:59:59" )->getTimestamp(), 'description' => '',
        'created_by' => 1, 'updated_by' => 1, 'created_at' => $now, 'updated_at' => $now,
    ] );
    $o['fy'] = (int) $wpdb->insert_id;
    $o['created_fy'] = 1;
} else {
    $o['fy'] = (int) ( is_object( $fy ) ? $fy->id : $fy );
}
$wpdb->insert( "{$p}erp_hr_leave_policies", [
    'leave_id' => $o['leave'], 'description' => "pw $tag", 'days' => 10, 'color' => '#fafafa', 'employee_type' => '-1',
    'department_id' => -1, 'location_id' => -1, 'designation_id' => -1, 'gender' => '-1', 'marital' => '-1',
    'f_year' => $o['fy'], 'created_at' => $now, 'updated_at' => $now,
] );
$o['policy'] = (int) $wpdb->insert_id;
foreach ( [ 'ent_own' => $emp_uid, 'ent_victim' => $o['victim'] ] as $k => $uid ) {
    $wpdb->insert( "{$p}erp_hr_leave_entitlements", [
        'user_id' => $uid, 'leave_id' => $o['leave'], 'created_by' => 1, 'trn_id' => $o['policy'], 'trn_type' => 'leave_policies',
        'day_in' => 10, 'day_out' => 0, 'description' => "pw $tag", 'f_year' => $o['fy'], 'created_at' => $now, 'updated_at' => $now,
    ] );
    $o[ $k ] = (int) $wpdb->insert_id;
}
$start = erp_current_datetime()->modify( 'tomorrow' )->setTime( 0, 0, 0 )->getTimestamp();
foreach ( [ 'req_own' => [ $emp_uid, $o['ent_own'], 2, "pw own leave $tag" ], 'req_victim' => [ $o['victim'], $o['ent_victim'], 1, "PW-SECRET-LEAVE $tag" ] ] as $k => $r ) {
    $wpdb->insert( "{$p}erp_hr_leave_requests", [
        'user_id' => $r[0], 'leave_id' => $o['leave'], 'leave_entitlement_id' => $r[1], 'day_status_id' => 1, 'days' => 1,
        'start_date' => $start, 'end_date' => $start + DAY_IN_SECONDS - 1, 'reason' => $r[3], 'last_status' => $r[2],
        'created_by' => $r[0], 'created_at' => $now, 'updated_at' => $now,
    ] );
    $o[ $k ] = (int) $wpdb->insert_id;
}

// Job history + performance reviews
foreach ( [ 'hist_own' => [ $emp_uid, "pw own job $tag" ], 'hist_victim' => [ $o['victim'], "PW-SECRET-JOB $tag" ] ] as $k => $h ) {
    $wpdb->insert( "{$p}erp_hr_employee_history", [ 'user_id' => $h[0], 'module' => 'job', 'category' => 'Pw', 'type' => 'Pw', 'comment' => $h[1], 'data' => '', 'date' => current_time( 'mysql' ) ] );
    $o[ $k ] = (int) $wpdb->insert_id;
}
foreach ( [ 'perf_victim' => $o['victim'], 'perf_sub' => $o['sub'] ] as $k => $uid ) {
    $wpdb->insert( "{$p}erp_hr_employee_performance", [
        'employee_id' => $uid, 'reporting_to' => 0, 'job_knowledge' => '', 'work_quality' => '', 'attendance' => '', 'communication' => '',
        'dependablity' => '', 'reviewer' => 0, 'comments' => "PW-SECRET-PERF $tag", 'type' => 'reviews', 'performance_date' => current_time( 'mysql' ),
    ] );
    $o[ $k ] = (int) $wpdb->insert_id;
}

// CRM: contacts owned by admin / by the agent, saved segments, a schedule, a group with pending subscribers
$contact_type = (int) $wpdb->get_var( "SELECT id FROM {$p}erp_people_types WHERE name = 'contact'" );
foreach ( [ 'contact_admin' => 1, 'contact_agent' => AGENT_UID ] as $k => $owner ) {
    $wpdb->insert( "{$p}erp_peoples", [
        'first_name' => 'Pw', 'last_name' => ucfirst( $tag ) . ( 1 === $owner ? 'Admin' : 'Agent' ), 'email' => "{$tag}_{$k}@example.com",
        'phone' => "+1-555-$k", 'life_stage' => 'lead', 'contact_owner' => $owner, 'hash' => '', 'created_by' => 1, 'created' => current_time( 'mysql' ),
    ] );
    $o[ $k ] = (int) $wpdb->insert_id;
    $wpdb->insert( "{$p}erp_people_type_relations", [ 'people_id' => $o[ $k ], 'people_types_id' => $contact_type ] );
}
foreach ( [ 'ss_admin' => 1, 'ss_agent' => AGENT_UID ] as $k => $uid ) {
    $wpdb->insert( "{$p}erp_crm_save_search", [ 'user_id' => $uid, 'type' => 'contact', 'global' => 0, 'search_name' => "pw $k $tag", 'search_val' => 'first_name[]=pw', 'created_at' => current_time( 'mysql' ), 'updated_at' => current_time( 'mysql' ) ] );
    $o[ $k ] = (int) $wpdb->insert_id;
}
$wpdb->insert( "{$p}erp_crm_customer_activities", [
    'user_id' => $o['contact_admin'], 'type' => 'log_activity', 'message' => "PW-SECRET-SCHEDULE $tag", 'email_subject' => '', 'log_type' => 'meeting',
    'start_date' => current_time( 'mysql' ), 'end_date' => current_time( 'mysql' ), 'created_by' => 1, 'extra' => '', 'sent_notification' => 0,
    'created_at' => current_time( 'mysql' ), 'updated_at' => current_time( 'mysql' ),
] );
$o['activity'] = (int) $wpdb->insert_id;
$wpdb->insert( "{$p}erp_crm_contact_group", [ 'name' => "pw group $tag", 'description' => '', 'private' => 0, 'created_at' => current_time( 'mysql' ), 'updated_at' => current_time( 'mysql' ) ] );
$o['group'] = (int) $wpdb->insert_id;
foreach ( [ 'a' => 'contact_admin', 'b' => 'contact_agent' ] as $k => $c ) {
    // Letter-led hashes: under the old (int) cast, MySQL matched every one of them against 0
    $hash = 'pw' . $k . substr( str_shuffle( str_repeat( 'abcdefghijklmnopqrstuvwxyz', 3 ) ), 0, 37 );
    $wpdb->insert( "{$p}erp_crm_contact_subscriber", [ 'user_id' => $o[ $c ], 'group_id' => $o['group'], 'status' => 'unconfirmed', 'subscribe_at' => null, 'unsubscribe_at' => null, 'hash' => $hash ] );
    $o[ "sub_$k" ] = (int) $wpdb->insert_id;
    $o[ "hash_$k" ] = $hash;
}

// Legacy unsanitised department / designation titles (stored before the fix), used by announcements
$wpdb->insert( "{$p}erp_hr_depts", [ 'title' => "PwDept $tag <img src=x id=pwxss{$tag}dept>", 'description' => '', 'lead' => 0, 'parent' => 0, 'status' => 1, 'created_at' => current_time( 'mysql' ), 'updated_at' => current_time( 'mysql' ) ] );
$o['dept_raw'] = (int) $wpdb->insert_id;
$wpdb->insert( "{$p}erp_hr_designations", [ 'title' => "PwDesig $tag <img src=x id=pwxss{$tag}desig>", 'description' => '', 'status' => 1, 'created_at' => current_time( 'mysql' ), 'updated_at' => current_time( 'mysql' ) ] );
$o['desig_raw'] = (int) $wpdb->insert_id;
foreach ( [ 'ann_dept' => [ 'by_department', '_announcement_department', $o['dept_raw'] ], 'ann_desig' => [ 'by_designation', '_announcement_designation', $o['desig_raw'] ] ] as $k => $a ) {
    $pid = wp_insert_post( [ 'post_type' => 'erp_hr_announcement', 'post_title' => "Pw announcement $k $tag", 'post_status' => 'publish', 'post_content' => 'pw' ] );
    update_post_meta( $pid, '_announcement_type', $a[0] );
    update_post_meta( $pid, $a[1], [ $a[2] ] );
    $o[ $k ] = (int) $pid;
}

// Nonces for each role's real session (the logged_in cookie carries the session token)
$n = [];
foreach ( NONCE_REQ as $actor => $r ) {
    $_COOKIE[ $r['cookie_name'] ] = $r['cookie'];
    wp_set_current_user( $r['uid'] );
    foreach ( NONCE_ACTIONS as $a ) { $n[ $actor ][ $a ] = wp_create_nonce( $a ); }
}
$o['nonces'] = $n;
pw_out( $o );
`;

function cleanupPhp(s: Partial<Seed>): string {
    return String.raw`
wp_set_current_user( 1 );
require_once ABSPATH . 'wp-admin/includes/user.php';
global $wpdb;
$p = $wpdb->prefix;
$s = ${phpStr(s)};
$id = function ( $k ) use ( $s ) { return isset( $s[ $k ] ) ? (int) $s[ $k ] : 0; };
foreach ( [ 'victim', 'sub', 'priv', 'plain' ] as $k ) {
    if ( $id( $k ) ) { erp_employee_delete( $id( $k ), true ); if ( get_userdata( $id( $k ) ) ) { wp_delete_user( $id( $k ) ); } }
}
$del = function ( $table, $col, $ids ) use ( $wpdb, $p ) {
    $ids = array_filter( array_map( 'intval', (array) $ids ) );
    if ( $ids ) { $wpdb->query( "DELETE FROM {$p}{$table} WHERE {$col} IN (" . implode( ',', $ids ) . ')' ); }
};
$del( 'erp_hr_leave_requests', 'id', [ $id( 'req_own' ), $id( 'req_victim' ) ] );
$del( 'erp_hr_leave_entitlements', 'leave_id', [ $id( 'leave' ), $id( 'leave2' ) ] );
$del( 'erp_hr_leave_policies', 'leave_id', [ $id( 'leave' ), $id( 'leave2' ) ] );
$del( 'erp_hr_leaves', 'id', [ $id( 'leave' ), $id( 'leave2' ) ] );
if ( $id( 'created_fy' ) ) { $del( 'erp_hr_financial_years', 'id', [ $id( 'fy' ) ] ); }
$del( 'erp_hr_employee_history', 'id', [ $id( 'hist_own' ), $id( 'hist_victim' ) ] );
$del( 'erp_hr_employee_performance', 'id', [ $id( 'perf_victim' ), $id( 'perf_sub' ) ] );
$del( 'erp_crm_save_search', 'id', [ $id( 'ss_admin' ), $id( 'ss_agent' ) ] );
$del( 'erp_crm_customer_activities', 'id', [ $id( 'activity' ) ] );
$del( 'erp_crm_contact_subscriber', 'group_id', [ $id( 'group' ) ] );
$del( 'erp_crm_contact_group', 'id', [ $id( 'group' ) ] );
$del( 'erp_people_type_relations', 'people_id', [ $id( 'contact_admin' ), $id( 'contact_agent' ) ] );
$del( 'erp_peoples', 'id', [ $id( 'contact_admin' ), $id( 'contact_agent' ) ] );
$del( 'erp_hr_depts', 'id', [ $id( 'dept_raw' ) ] );
$del( 'erp_hr_designations', 'id', [ $id( 'desig_raw' ) ] );
foreach ( [ 'ann_dept', 'ann_desig' ] as $k ) { if ( $id( $k ) ) { wp_delete_post( $id( $k ), true ); } }
if ( ! empty( $s['tag'] ) ) {
    $like = '%' . $wpdb->esc_like( $s['tag'] ) . '%';
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$p}erp_hr_depts WHERE title LIKE %s", $like ) );
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$p}erp_hr_designations WHERE title LIKE %s", $like ) );
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$p}erp_crm_contact_group WHERE name LIKE %s", $like ) );
}
if ( ! empty( $s['employee_row'] ) ) {
    $wpdb->update( "{$p}erp_hr_employees", $s['employee_row'], [ 'user_id' => EMPLOYEE_UID ] );
}
pw_out( [ 'ok' => true ] );
`.replace(/EMPLOYEE_UID/g, String(EMPLOYEE_ID));
}

test.beforeAll(async () => {
    test.setTimeout(120_000);
    const nonceReq = Object.fromEntries(
        (Object.keys(ACTORS) as Actor[]).map(a => {
            const c = loggedInCookie(ACTORS[a].file);
            return [a, { uid: ACTORS[a].uid, cookie_name: c.name, cookie: c.value }];
        }),
    );
    const code = SEED_PHP.replace(/EMPLOYEE_UID/g, String(EMPLOYEE_ID))
        .replace(/AGENT_UID/g, String(ACTORS.crmAgent.uid))
        .replace('NONCE_REQ', phpStr(nonceReq))
        .replace('NONCE_ACTIONS', phpStr(NONCE_ACTIONS));
    const out = phpJson<Seed & { nonces: typeof nonces; error?: string }>(code);
    if (out.error) {
        // Partial seed: clean what exists before failing.
        php(cleanupPhp(out));
    }
    expect(out.error, 'fixtures seeded').toBeUndefined();
    nonces = out.nonces;
    S = out;
});

test.afterAll(async () => {
    test.setTimeout(120_000);
    for (const c of Object.values(ctxs)) await c?.dispose();
    for (const code of extraCleanup) php(code);
    if (S) phpJson(cleanupPhp(S));
    await dbUtils.close();
});

// ═════════════════════════════════════════════════════════════════════════════
// wp-erp#1676 — security audit hardening
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Security audit hardening (wp-erp#1676)', () => {
    test('REST contact group create/update store sanitised text and update returns the group', TAGS('@crm', '@admin'), async () => {
        const created = await rest('admin', 'POST', 'crm/contacts/groups', {
            body: { name: `pw grp ${S.tag} <script>alert(1)</script>`, description: `pw desc <img src=x onerror=alert(1)> ${S.tag}` },
        });
        expect(created.status, created.text).toBeLessThan(300);
        const rows = await q(`SELECT id, name, description FROM ${P}erp_crm_contact_group WHERE name LIKE ? ORDER BY id DESC LIMIT 1`, [`%pw grp ${S.tag}%`]);
        expect(rows.length, 'group row stored').toBe(1);
        expect(rows[0].name).not.toContain('<script');
        expect(rows[0].description).not.toContain('<img');
        expect(rows[0].name).toContain(`pw grp ${S.tag}`);

        const updated = await rest('admin', 'PUT', `crm/contacts/groups/${rows[0].id}`, {
            body: { name: `pw grp ${S.tag} renamed <b>bold</b>`, description: 'pw' },
        });
        expect(updated.status, updated.text).toBe(201);
        // Before the fix the response body was an undefined variable (null).
        // Before the fix the body was an undefined variable (null); now it is the save result.
        expect(updated.json, 'update returns the save result').toBeTruthy();
        const after = await q(`SELECT name FROM ${P}erp_crm_contact_group WHERE id = ?`, [rows[0].id]);
        expect(after[0].name).toBe(`pw grp ${S.tag} renamed bold`);
    });

    test('PDF extension notice links carry the erp-pdf-action nonce and an unsigned install request is ignored', TAGS('@accounting', '@admin'), async () => {
        const state = phpJson<{ active: boolean; exists: boolean; dismissed: string | false; acct: boolean }>(`
            pw_out( [ 'active' => is_plugin_active( 'erp-pdf-invoice/wp-erp-pdf.php' ), 'exists' => file_exists( WP_PLUGIN_DIR . '/erp-pdf-invoice' ),
                'dismissed' => get_option( 'pdf-notice-dismissed' ), 'acct' => wperp()->modules->is_module_active( 'accounting' ) ] );
        `);
        test.skip(!state.acct, 'Accounting module inactive, the PDF notice is not registered');
        test.skip(state.active || state.exists, 'erp-pdf-invoice already present on this site');
        test.skip(state.dismissed === 'hide', 'PDF notice dismissed on this site');

        // Unsigned link (the pre-fix format) must not install anything.
        const res = await (await ctx('admin')).get(toPath('wp-admin/index.php?erp-pdf=install'));
        const html = await res.text();
        const link = html.match(/class="updated notice is-dismissible notice-pdf"[\s\S]*?<a href="([^"]+)"/)?.[1]?.replace(/&amp;/g, '&');
        expect(link, 'PDF notice rendered').toBeTruthy();
        expect(link).toMatch(/erp-pdf=install/);
        expect(link, 'notice link is nonce-signed').toMatch(/_wpnonce=[a-f0-9]{10}/);

        const after = phpJson<{ exists: boolean }>(`pw_out( [ 'exists' => file_exists( WP_PLUGIN_DIR . '/erp-pdf-invoice' ) ] );`);
        if (after.exists) extraCleanup.push(`delete_plugins( [ 'erp-pdf-invoice/wp-erp-pdf.php' ] ); pw_out( 1 );`);
        expect(after.exists, 'unsigned ?erp-pdf=install did not install the plugin').toBe(false);
    });

    test('product CSV import validation binds ids as integers', TAGS('@accounting', '@admin'), async () => {
        const r = phpJson<{ seen: string[] }>(String.raw`
            wp_set_current_user( 1 );
            $f = tempnam( sys_get_temp_dir(), 'pw' );
            file_put_contents( $f, "name,category_id,product_type_id,tax_cat_id,vendor\npw_csv_prod,0 OR 1=1,0 OR 1=1,0 OR 1=1,0 OR 1=1\n" );
            $seen = [];
            add_filter( 'query', function ( $q ) use ( &$seen ) {
                if ( preg_match( '/erp_acct_product_categories|erp_acct_product_types|erp_acct_tax_categories|people_types_id = 4/', $q ) ) { $seen[] = preg_replace( '/\s+/', ' ', $q ); }
                return $q;
            } );
            @erp_acct_validate_csv_data( [ 'csv_file' => [ 'tmp_name' => $f, 'name' => 'pw.csv' ], 'type' => 'product', 'update_existing' => 0,
                'fields' => [ 'name' => 0, 'category_id' => 1, 'product_type_id' => 2, 'tax_cat_id' => 3, 'vendor' => 4 ] ] );
            unlink( $f );
            pw_out( [ 'seen' => $seen ] );
        `);
        expect(r.seen.length, 'all four id lookups ran').toBe(4);
        for (const sql of r.seen) {
            expect(sql, 'injected SQL is not concatenated').not.toContain('OR 1=1');
            expect(sql).toMatch(/\.?id = 0( |$)/);
        }
    });

    test('CRM agent cannot load another owner\'s contact through customer_get', TAGS('@crm', '@agent'), async () => {
        const { status, body } = await ajax('crmAgent', { action: 'erp-crm-customer-get', id: S.contact_admin }, 'wp-erp-crm-nonce');
        expect(refused(status, body), body.slice(0, 200)).toBe(true);
        expect(body).not.toContain(`${S.tag}_contact_admin@example.com`);
    });

    test('CRM agent can load an own contact and the CRM manager any contact', TAGS('@crm', '@agent', '@manager'), async () => {
        const own = await ajax('crmAgent', { action: 'erp-crm-customer-get', id: S.contact_agent }, 'wp-erp-crm-nonce');
        expect(succeeded(own.body), own.body.slice(0, 200)).toBe(true);
        expect(own.body).toContain(`${S.tag}_contact_agent@example.com`);

        const mgr = await ajax('crmManager', { action: 'erp-crm-customer-get', id: S.contact_admin }, 'wp-erp-crm-nonce');
        expect(succeeded(mgr.body), mgr.body.slice(0, 200)).toBe(true);
        expect(mgr.body).toContain(`${S.tag}_contact_admin@example.com`);
    });

    test('CRM agent cannot delete a saved segment owned by someone else', TAGS('@crm', '@agent'), async () => {
        const { status, body } = await ajax('crmAgent', { action: 'erp-crm-delete-search-segment', filterId: S.ss_admin }, 'wp-erp-crm-nonce');
        expect(refused(status, body), body.slice(0, 200)).toBe(true);
        const rows = await q(`SELECT id FROM ${P}erp_crm_save_search WHERE id = ?`, [S.ss_admin]);
        expect(rows.length, 'admin segment still exists').toBe(1);
    });

    test('CRM agent can still delete an own saved segment', TAGS('@crm', '@agent'), async () => {
        const { body } = await ajax('crmAgent', { action: 'erp-crm-delete-search-segment', filterId: S.ss_agent }, 'wp-erp-crm-nonce');
        expect(succeeded(body), body.slice(0, 200)).toBe(true);
        const rows = await q(`SELECT id FROM ${P}erp_crm_save_search WHERE id = ?`, [S.ss_agent]);
        expect(rows.length, 'agent segment deleted').toBe(0);
    });

    for (const actor of ['employee', 'hrManager'] as const) {
        test(`${actor} (no CRM role) cannot read CRM schedule details`, TAGS('@crm', actor === 'employee' ? '@employee' : '@manager'), async () => {
            const { status, body } = await ajax(actor, { action: 'erp-crm-get-single-schedule-details', id: S.activity }, 'wp-erp-crm-nonce');
            expect(refused(status, body), body.slice(0, 200)).toBe(true);
            expect(body).not.toContain(SECRET.activity());
        });
    }

    test('CRM agent can read schedule details', TAGS('@crm', '@agent'), async () => {
        const { body } = await ajax('crmAgent', { action: 'erp-crm-get-single-schedule-details', id: S.activity }, 'wp-erp-crm-nonce');
        expect(succeeded(body), body.slice(0, 200)).toBe(true);
        expect(body).toContain(SECRET.activity());
    });

    test('employee cannot trigger the birthday wish email', TAGS('@hrm', '@employee'), async () => {
        const { status, body } = await ajax('employee', { action: 'erp_hr_birthday_wish', employee_user_id: S.victim }, 'wp-erp-hr-nonce');
        expect(refused(status, body), body.slice(0, 200)).toBe(true);
        expect(body).not.toContain('Email sent');
    });

    test('HR manager can trigger the birthday wish email', TAGS('@hrm', '@manager'), async () => {
        const { body } = await ajax('hrManager', { action: 'erp_hr_birthday_wish', employee_user_id: S.victim }, 'wp-erp-hr-nonce');
        expect(succeeded(body), body.slice(0, 200)).toBe(true);
    });

    test('performance delete checks the review owner, not the posted user_id', TAGS('@hrm', '@employee'), async () => {
        // employee1 manages `sub`, so a spoofed user_id=sub used to authorise deleting anyone's review.
        const { status, body } = await ajax('employee', { action: 'erp-hr-emp-delete-performance', id: S.perf_victim, user_id: S.sub }, 'wp-erp-hr-nonce');
        expect(refused(status, body), body.slice(0, 200)).toBe(true);
        const rows = await q(`SELECT id FROM ${P}erp_hr_employee_performance WHERE id = ?`, [S.perf_victim]);
        expect(rows.length, 'victim review still exists').toBe(1);
    });

    test('a reporting manager can delete a direct report\'s review and HR can delete any', TAGS('@hrm', '@employee', '@manager'), async () => {
        const own = await ajax('employee', { action: 'erp-hr-emp-delete-performance', id: S.perf_sub }, 'wp-erp-hr-nonce');
        expect(succeeded(own.body), own.body.slice(0, 200)).toBe(true);
        expect((await q(`SELECT id FROM ${P}erp_hr_employee_performance WHERE id = ?`, [S.perf_sub])).length).toBe(0);

        const hr = await ajax('hrManager', { action: 'erp-hr-emp-delete-performance', id: S.perf_victim }, 'wp-erp-hr-nonce');
        expect(succeeded(hr.body), hr.body.slice(0, 200)).toBe(true);
        expect((await q(`SELECT id FROM ${P}erp_hr_employee_performance WHERE id = ?`, [S.perf_victim])).length).toBe(0);
    });

    test('employee cannot read another employee\'s leave history', TAGS('@hrm', '@employee'), async () => {
        const { status, body } = await ajax('employee', { action: 'erp-hr-empl-leave-history', employee_id: S.victim }, 'erp-hr-empl-leave-history');
        expect(refused(status, body), body.slice(0, 200)).toBe(true);
        expect(body).not.toContain(SECRET.leave());
    });

    test('employee can read own leave history and HR any', TAGS('@hrm', '@employee', '@manager'), async () => {
        const own = await ajax('employee', { action: 'erp-hr-empl-leave-history', employee_id: EMPLOYEE_ID }, 'erp-hr-empl-leave-history');
        expect(succeeded(own.body), own.body.slice(0, 200)).toBe(true);
        const hr = await ajax('hrManager', { action: 'erp-hr-empl-leave-history', employee_id: S.victim }, 'erp-hr-empl-leave-history');
        expect(succeeded(hr.body), hr.body.slice(0, 200)).toBe(true);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// wp-erp#1677 — v1 employee update vs. privileged account email
// ═════════════════════════════════════════════════════════════════════════════
test.describe('v1 employee update cannot hijack a privileged login email (wp-erp#1677)', () => {
    const emailOf = async (uid: number) => (await q(`SELECT user_email FROM ${P}users WHERE ID = ?`, [uid]))[0]?.user_email as string;

    test('HR manager cannot change the email of an employee who is also an editor', PRO_TAGS('@hrm', '@manager'), async () => {
        const before = await emailOf(S.priv);
        const res = await rest('hrManager', 'PUT', `hrm/employees/${S.priv}`, { body: { email: `${S.tag}_hijack@example.com` } });
        expect(res.status, res.text).toBe(403);
        expect(res.json?.code).toBe('rest_cannot_change_email');
        expect(await emailOf(S.priv), 'email unchanged').toBe(before);
    });

    test('HR manager can still change a plain employee\'s email; admin can change the editor\'s', PRO_TAGS('@hrm', '@manager', '@admin'), async () => {
        const plain = await rest('hrManager', 'PUT', `hrm/employees/${S.plain}`, { body: { email: `${S.tag}_plain2@example.com` } });
        expect(plain.status, plain.text).toBeLessThan(300);
        expect(await emailOf(S.plain)).toBe(`${S.tag}_plain2@example.com`);

        const priv = await rest('admin', 'PUT', `hrm/employees/${S.priv}`, { body: { email: `${S.tag}_priv2@example.com` } });
        expect(priv.status, priv.text).toBeLessThan(300);
        expect(await emailOf(S.priv)).toBe(`${S.tag}_priv2@example.com`);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// wp-erp#1669 — leave requests REST scoped to owner (erp-pro#1094)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Leave request REST routes are owner-scoped (wp-erp#1669, erp-pro#1094)', () => {
    // Pro's v1 list gives an employee only "who is out": approved upcoming leave, whatever `type` says
    test('employee list holds approved leave only, never a pending request', PRO_TAGS('@hrm', '@employee'), async () => {
        const res = await rest('employee', 'GET', 'hrm/leaves/requests', { query: { per_page: 100 } });
        expect(res.status, res.text).toBe(200);
        const rows = res.json as any[];
        expect(rows.map(r => Number(r.id))).not.toContain(S.req_own);
        expect(rows.every(r => Number(r.status) === 1), 'approved rows only').toBe(true);
        expect(res.text).not.toContain(SECRET.leave());
    });

    test('employee fetching another employee\'s request by id gets no private fields', PRO_TAGS('@hrm', '@employee'), async () => {
        const res = await rest('employee', 'GET', `hrm/leaves/requests/${S.req_victim}`);
        expect(res.text).not.toContain(SECRET.leave());
        // An approved colleague request is readable as who and when only
        if (res.status === 200) {
            expect(res.json.reason).toBe('');
            expect(res.json.comments).toBe('');
            expect(res.json.policy_name).toBe('');
        } else {
            expect([401, 403, 404], res.text).toContain(res.status);
        }
        const own = await rest('employee', 'GET', `hrm/leaves/requests/${S.req_own}`);
        expect(own.status, own.text).toBe(200);
        expect(Number(own.json.id)).toBe(S.req_own);
    });

    test('employee "upcoming" view of colleagues exposes dates only', PRO_TAGS('@hrm', '@employee'), async () => {
        const res = await rest('employee', 'GET', 'hrm/leaves/requests', { query: { type: 'upcoming', per_page: 100 } });
        expect(res.status, res.text).toBe(200);
        const victim = (res.json as any[]).find(r => Number(r.user_id) === S.victim);
        expect(victim, 'approved colleague leave listed for the who-is-out widget').toBeTruthy();
        expect(victim.start_date).toBeTruthy();
        expect(victim.end_date).toBeTruthy();
        expect([victim.reason, victim.comments, victim.policy_name, victim.applied_on]).toEqual(['', '', '', '']);
        expect(victim.attachments).toEqual([]);
        expect(res.text).not.toContain(SECRET.leave());
    });

    test('HR manager still sees and fetches every request', PRO_TAGS('@hrm', '@manager'), async () => {
        const list = await rest('hrManager', 'GET', 'hrm/leaves/requests', { query: { per_page: 100 } });
        expect(list.status, list.text).toBe(200);
        const ids = (list.json as any[]).map(r => Number(r.id));
        expect(ids).toEqual(expect.arrayContaining([S.req_own, S.req_victim]));
        const one = await rest('hrManager', 'GET', `hrm/leaves/requests/${S.req_victim}`);
        expect(one.status, one.text).toBe(200);
        expect(one.json.reason).toBe(SECRET.leave());
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// wp-erp#1670 — HR job data access control (erp-pro#1096, HR part)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('HR job data access control (wp-erp#1670, erp-pro#1096)', () => {
    test('employee cannot read another employee\'s job history entry', TAGS('@hrm', '@employee'), async () => {
        const { status, body } = await ajax('employee', { action: 'erp_hr_employee_get_job_history', history_id: S.hist_victim }, 'wp-erp-hr-nonce');
        expect(refused(status, body), body.slice(0, 200)).toBe(true);
        expect(body).not.toContain(SECRET.job());
    });

    test('employee can read own job history and HR any', TAGS('@hrm', '@employee', '@manager'), async () => {
        const own = await ajax('employee', { action: 'erp_hr_employee_get_job_history', history_id: S.hist_own }, 'wp-erp-hr-nonce');
        expect(succeeded(own.body), own.body.slice(0, 200)).toBe(true);
        const hr = await ajax('hrManager', { action: 'erp_hr_employee_get_job_history', history_id: S.hist_victim }, 'wp-erp-hr-nonce');
        expect(succeeded(hr.body), hr.body.slice(0, 200)).toBe(true);
        expect(hr.body).toContain(SECRET.job());
    });

    test('employee self-update via REST cannot change HR-managed job fields', PRO_TAGS('@hrm', '@employee'), async () => {
        const before = (await q(`SELECT pay_rate, department, status FROM ${P}erp_hr_employees WHERE user_id = ?`, [EMPLOYEE_ID]))[0];
        const res = await rest('employee', 'PUT', `hrm/employees/${EMPLOYEE_ID}`, {
            body: { pay_rate: 987654, department: S.dept_raw, status: 'terminated' },
        });
        expect(res.status, res.text).toBeLessThan(300);
        const after = (await q(`SELECT pay_rate, department, status FROM ${P}erp_hr_employees WHERE user_id = ?`, [EMPLOYEE_ID]))[0];
        expect(Number(after.pay_rate), 'pay rate unchanged').toBe(Number(before.pay_rate));
        expect(Number(after.department), 'department unchanged').toBe(Number(before.department));
        expect(after.status, 'status unchanged').toBe(before.status);
    });

    test('HR manager can still change job fields via REST', PRO_TAGS('@hrm', '@manager'), async () => {
        const res = await rest('hrManager', 'PUT', `hrm/employees/${S.plain}`, { body: { pay_rate: 4321 } });
        expect(res.status, res.text).toBeLessThan(300);
        const row = (await q(`SELECT pay_rate FROM ${P}erp_hr_employees WHERE user_id = ?`, [S.plain]))[0];
        expect(Number(row.pay_rate)).toBe(4321);
    });

    // Pro's v1 entitlement routes are HR only; employees read their balances through erp/v2
    test('employee cannot read leave entitlements over v1', PRO_TAGS('@hrm', '@employee'), async () => {
        const list = await rest('employee', 'GET', 'hrm/leaves/entitlements', { query: { per_page: 100 } });
        expect([401, 403], list.text).toContain(list.status);

        const victim = await rest('employee', 'GET', `hrm/leaves/entitlements/${S.ent_victim}`);
        expect([401, 403], victim.text).toContain(victim.status);
    });

    test('HR manager can read any entitlement', PRO_TAGS('@hrm', '@manager'), async () => {
        const res = await rest('hrManager', 'GET', `hrm/leaves/entitlements/${S.ent_victim}`);
        expect(res.status, res.text).toBe(200);
        expect(Number(res.json.id)).toBe(S.ent_victim);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// wp-erp#1671 — department / designation titles (erp-pro#1098)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Department and designation titles are sanitised and escaped (wp-erp#1671, erp-pro#1098)', () => {
    // The REST schema already sanitised `title`, so drive the shared writers the fix hardened
    // (REST, AJAX and CSV import all funnel through erp_hr_create_department/designation).
    test('department and designation writers store plain text', TAGS('@hrm', '@admin'), async () => {
        const r = phpJson<Record<string, { title: string; description: string } | string>>(`
            wp_set_current_user( 1 );
            $out = [];
            foreach ( [ 'dept' => 'erp_hr_create_department', 'desig' => 'erp_hr_create_designation' ] as $k => $fn ) {
                $id = $fn( [ 'title' => "pw ${S.tag} $k <img src=x onerror=alert(1)>", 'description' => "pw <script>alert(1)</script> ${S.tag}" ] );
                if ( is_wp_error( $id ) ) { $out[ $k ] = $id->get_error_message(); continue; }
                $m = 'dept' === $k ? \\WeDevs\\ERP\\HRM\\Models\\Department::find( $id ) : \\WeDevs\\ERP\\HRM\\Models\\Designation::find( $id );
                $out[ $k ] = [ 'title' => $m->title, 'description' => $m->description ];
            }
            pw_out( $out );
        `);
        for (const k of ['dept', 'desig']) {
            const row = r[k] as { title: string; description: string };
            expect(typeof row, `${k} created: ${JSON.stringify(r[k])}`).toBe('object');
            expect(row.title).toContain(`pw ${S.tag} ${k}`);
            expect(row.title).not.toContain('<img');
            expect(row.description).not.toContain('<script');
        }
    });

    test.describe('admin list screens', () => {
        test.use({ storageState: data.auth.adminFile });

        async function assertEscaped(page: Page, url: string, markerId: string, text: string) {
            const res = await page.goto(toPath(url), { waitUntil: 'domcontentloaded' });
            expect(res?.status()).toBeLessThan(500);
            await expect(page.locator(`#${markerId}`), 'stored markup is not rendered as HTML').toHaveCount(0);
            await expect(page.getByText(text).first()).toBeVisible();
        }

        test('designation list escapes a stored title', TAGS('@hrm', '@admin'), async ({ page }) => {
            await assertEscaped(page, 'wp-admin/admin.php?page=erp-hr&section=people&sub-section=designation', `pwxss${S.tag}desig`, `PwDesig ${S.tag}`);
        });

        test('announcement list escapes department and designation names', TAGS('@hrm', '@admin'), async ({ page }) => {
            await assertEscaped(page, 'wp-admin/admin.php?page=erp-hr&section=people&sub-section=announcement', `pwxss${S.tag}dept`, `PwDept ${S.tag}`);
            await expect(page.locator(`#pwxss${S.tag}desig`)).toHaveCount(0);
            await expect(page.getByText(`PwDesig ${S.tag}`).first()).toBeVisible();
        });
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// wp-erp#1668 — CRM subscription hash (erp-pro#1095)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('CRM subscription confirm matches hashes as strings (wp-erp#1668, erp-pro#1095)', () => {
    const statusOf = async (id: number) => (await q(`SELECT status FROM ${P}erp_crm_contact_subscriber WHERE id = ?`, [id]))[0]?.status as string;
    let pageUrl = '';

    test.beforeAll(() => {
        const r = phpJson<{ url: string | false }>(`
            $id = absint( erp_get_option( 'page_id', 'erp_settings_erp-crm_subscription', 0 ) );
            pw_out( [ 'url' => $id ? get_permalink( $id ) : false ] );
        `);
        pageUrl = r.url || '';
    });

    test('a non-numeric subscription-id does not confirm every letter-led hash', TAGS('@crm', '@guest'), async () => {
        test.skip(!pageUrl, 'CRM subscription page not configured');
        const guest = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
        for (const id of ['abc', 'zzz:yyy']) {
            const res = await guest.get(withQuery(pageUrl, { 'erp-subscription-action': 'confirm', 'subscription-id': id }));
            expect(res.status()).toBeLessThan(500);
        }
        await guest.dispose();
        expect(await statusOf(S.sub_a)).toBe('unconfirmed');
        expect(await statusOf(S.sub_b)).toBe('unconfirmed');
    });

    test('the real hash still confirms exactly that subscription', TAGS('@crm', '@guest'), async () => {
        test.skip(!pageUrl, 'CRM subscription page not configured');
        const guest = await request.newContext({ baseURL: BASE_URL, ...data.auth.noAuth, ignoreHTTPSErrors: true });
        const res = await guest.get(withQuery(pageUrl, { 'erp-subscription-action': 'confirm', 'subscription-id': S.hash_b }));
        expect(res.status()).toBeLessThan(500);
        await guest.dispose();
        expect(await statusOf(S.sub_b)).toBe('subscribe');
        expect(await statusOf(S.sub_a)).toBe('unconfirmed');
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// wp-erp#1683 — v1 leave policy REST create (erp-pro#1120)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('v1 leave policy REST create maps to real fields (wp-erp#1683, erp-pro#1120)', () => {
    test('unknown leave type is rejected with 400', PRO_TAGS('@hrm', '@admin'), async () => {
        const byId = await rest('admin', 'POST', 'hrm/leaves/policies', { body: { leave_id: 999999, days: 5 } });
        expect(byId.status, byId.text).toBe(400);
        expect(byId.json?.code).toBe('rest_policy_invalid_leave');
        const byName = await rest('admin', 'POST', 'hrm/leaves/policies', { body: { name: `pw no such leave ${S.tag}`, days: 5 } });
        expect(byName.status, byName.text).toBe(400);
    });

    test('valid leave_id + days creates a real policy row that the list returns', PRO_TAGS('@hrm', '@admin'), async () => {
        const res = await rest('admin', 'POST', 'hrm/leaves/policies', { body: { leave_id: S.leave2, days: 7, f_year: S.fy, description: `pw ${S.tag}` } });
        expect(res.status, res.text).toBe(201);
        const id = Number(res.json.id);
        expect(id).toBeGreaterThan(0);
        expect(Number(res.json.leave_id)).toBe(S.leave2);
        expect(Number(res.json.days)).toBe(7);
        expect(Number(res.json.f_year)).toBe(S.fy);

        const row = (await q(`SELECT leave_id, days, f_year FROM ${P}erp_hr_leave_policies WHERE id = ?`, [id]))[0];
        expect(row, 'policy row stored').toBeTruthy();
        expect(Number(row.leave_id)).toBe(S.leave2);
        expect(Number(row.days)).toBe(7);
        expect(Number(row.f_year)).toBe(S.fy);

        const list = await rest('admin', 'GET', 'hrm/leaves/policies', { query: { per_page: 100 } });
        expect(list.status, list.text).toBe(200);
        const listed = (list.json as any[]).find(p => Number(p.id) === id);
        expect(listed, 'new policy is listed').toBeTruthy();
        expect(Number(listed.leave_id)).toBe(S.leave2);
        expect(Number(listed.days)).toBe(7);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// wp-erp#1681 — email template enable state (erp-pro#1108)
// ═════════════════════════════════════════════════════════════════════════════
test.describe('Email template enable state (wp-erp#1681, erp-pro#1108)', () => {
    const EMAIL = 'GovtHolidayReminder';
    const OPT = 'erp_email_settings_govt-holiday-reminder';
    let backup: unknown;

    const readOpt = () => phpJson<{ v: Record<string, string> | false }>(`pw_out( [ 'v' => get_option( '${OPT}' ) ] );`).v;
    const writeOpt = (v: unknown) =>
        phpJson(`$v = ${phpStr(v)}; null === $v ? delete_option( '${OPT}' ) : update_option( '${OPT}', $v ); pw_out( 1 );`);

    test.beforeAll(() => {
        backup = phpJson<{ v: unknown }>(`pw_out( [ 'v' => get_option( '${OPT}', null ) ] );`).v;
    });

    test.afterAll(() => {
        writeOpt(backup ?? null);
    });

    test('a template without a saved state is listed as enabled', TAGS('@settings', '@admin'), async () => {
        writeOpt({ subject: 'pw', heading: 'pw', body: 'pw' });
        const { body } = await ajax('admin', { action: 'erp_get_email_templates' }, 'erp-settings-nonce');
        expect(succeeded(body), body.slice(0, 200)).toBe(true);
        const all = JSON.parse(body).data as any;
        const list: any[] = Array.isArray(all) ? all.flatMap((g: any) => (Array.isArray(g) ? g : g?.emails ?? [g])) : Object.values(all).flat(2);
        const row = JSON.stringify(list).match(new RegExp(`\\{[^{}]*"option_id":"${OPT}"[^{}]*\\}`))?.[0];
        expect(row, `${OPT} listed`).toBeTruthy();
        expect(JSON.parse(row as string).is_enabled).toBe('yes');
    });

    test('a template without a saved state is still sent, an explicit "no" blocks the recipient', TAGS('@settings', '@admin'), async () => {
        const r = phpJson<{ unset: string; no: string }>(`
            $email = wperp()->emailer->get_email( '${EMAIL}' );
            $res = [];
            foreach ( [ 'unset' => [ 'subject' => 'pw' ], 'no' => [ 'subject' => 'pw', 'is_enable' => 'no' ] ] as $k => $v ) {
                update_option( '${OPT}', $v );
                remove_all_filters( 'erp_email_recipient_' . $email->id );
                filter_enabled_email( $email );
                $res[ $k ] = (string) apply_filters( 'erp_email_recipient_' . $email->id, 'pw@example.com', $email );
            }
            pw_out( $res );
        `);
        expect(r.unset, 'missing state keeps the recipient').toBe('pw@example.com');
        expect(r.no, 'explicit no empties the recipient').toBe('');
    });

    test('toggling status stores explicit yes/no', TAGS('@settings', '@admin'), async () => {
        writeOpt({ subject: 'pw', heading: 'pw', body: 'pw', is_enable: 'yes' });
        const off = await ajax('admin', { action: 'erp_update_email_status', option_id: OPT, option_value: 'no' }, 'erp-settings-nonce');
        expect(succeeded(off.body), off.body.slice(0, 200)).toBe(true);
        expect((readOpt() as Record<string, string>).is_enable).toBe('no');

        const on = await ajax('admin', { action: 'erp_update_email_status', option_id: OPT, option_value: 'yes' }, 'erp-settings-nonce');
        expect(succeeded(on.body)).toBe(true);
        expect((readOpt() as Record<string, string>).is_enable).toBe('yes');
    });

    test('saving a template stores "no" and keeps the state when none is sent', TAGS('@settings', '@admin'), async () => {
        writeOpt({ subject: 'pw', heading: 'pw', body: 'pw', is_enable: 'yes' });
        const save = (extra: Record<string, string>) =>
            ajax('admin', { action: 'erp_update_email_template', id: OPT, subject: `pw ${S.tag}`, heading: 'pw', body: 'pw body', ...extra }, 'erp-settings-nonce');

        expect(succeeded((await save({ is_enable: 'no' })).body)).toBe(true);
        expect((readOpt() as Record<string, string>).is_enable).toBe('no');

        expect(succeeded((await save({})).body)).toBe(true);
        const v = readOpt() as Record<string, string>;
        expect(v.is_enable, 'state kept when the request omits it').toBe('no');
        expect(v.subject).toBe(`pw ${S.tag}`);
    });

    test('legacy bulk enable/disable save is ignored for a non-admin', TAGS('@settings', '@manager'), async () => {
        writeOpt({ subject: 'pw', heading: 'pw', body: 'pw', is_enable: 'yes' });
        // admin_init runs on admin-ajax.php before the action is dispatched.
        await ajax('hrManager', { action: 'pw_noop', save_email_enable_or_disable: 'save_email_enable_or_disable' }, 'erp-settings-nonce');
        expect((readOpt() as Record<string, string>).is_enable, 'HR manager could not disable templates').toBe('yes');
    });

    test('legacy bulk save by admin stores explicit yes/no per template', TAGS('@settings', '@admin'), async () => {
        // The handler rewrites every template registered in the web request (CRM ones included,
        // which wp-cli does not load), so snapshot every erp_email_settings_* option and put
        // the table back exactly, dropping options the save created.
        const snap = phpJson<{ ids: string[]; snap: Record<string, unknown> }>(String.raw`
            global $wpdb;
            $snap = [];
            foreach ( $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'erp\_email\_settings\_%'" ) as $n ) { $snap[ $n ] = get_option( $n ); }
            $ids = [];
            foreach ( wperp()->emailer->get_emails() as $e ) { $ids[] = $e->get_option_id(); }
            pw_out( [ 'ids' => $ids, 'snap' => (object) $snap ] );
        `);
        const restore = String.raw`
            global $wpdb;
            $snap = ${phpStr(snap.snap)};
            foreach ( $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'erp\_email\_settings\_%'" ) as $n ) {
                if ( ! array_key_exists( $n, $snap ) ) { delete_option( $n ); }
            }
            foreach ( $snap as $n => $v ) { update_option( $n, $v ); }
            pw_out( 1 );
        `;
        try {
            const listed = await ajax('admin', { action: 'erp_get_email_templates' }, 'erp-settings-nonce');
            const webIds = [...listed.body.matchAll(/"option_id":"([^"]+)"/g)].map(m => m[1] ?? '').filter(Boolean);
            const checked = [...new Set([...snap.ids, ...webIds])].filter(id => id !== OPT);

            const form: Record<string, string> = { action: 'pw_noop', save_email_enable_or_disable: 'save_email_enable_or_disable' };
            for (const id of checked) form[`isEnableEmail[${id}]`] = 'on';
            await ajax('admin', form, 'erp-settings-nonce');

            const states = phpJson<Record<string, string | null>>(`
                $o = []; foreach ( ${phpStr([OPT, ...checked])} as $id ) { $v = get_option( $id ); $o[ $id ] = is_array( $v ) && isset( $v['is_enable'] ) ? $v['is_enable'] : null; }
                pw_out( $o );
            `);
            expect(states[OPT], 'unchecked template stored as no').toBe('no');
            const notYes = checked.filter(id => states[id] !== 'yes');
            expect(notYes, 'checked templates stored as yes').toEqual([]);
        } finally {
            phpJson(restore);
        }
    });

    test('1.17.12 updater marks templates without a state as enabled', TAGS('@settings', '@admin'), async () => {
        const r = phpJson<{ registered: boolean; legacy: string | null; off: string | null }>(String.raw`
            $legacy = 'erp_email_settings_pw-legacy-${S.tag}';
            $off    = 'erp_email_settings_pw-off-${S.tag}';
            // The updater touches every template option: snapshot them and put them back afterwards.
            global $wpdb;
            $snap = [];
            foreach ( $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'erp\_email\_settings\_%'" ) as $name ) { $snap[ $name ] = get_option( $name ); }
            update_option( $legacy, [ 'subject' => 'pw' ] );
            update_option( $off, [ 'subject' => 'pw', 'is_enable' => 'no' ] );
            $prop = new ReflectionProperty( '\WeDevs\ERP\Updates', 'updates' );
            $prop->setAccessible( true );
            $map  = $prop->getValue();
            $file = WPERP_INCLUDES . '/Updates/update-1.17.12.php';
            if ( file_exists( $file ) ) { include $file; }
            $out = [ 'registered' => isset( $map['1.17.12'] ), 'legacy' => get_option( $legacy )['is_enable'] ?? null, 'off' => get_option( $off )['is_enable'] ?? null ];
            delete_option( $legacy );
            delete_option( $off );
            foreach ( $snap as $name => $v ) { update_option( $name, $v ); }
            pw_out( $out );
        `);
        expect(r.registered, '1.17.12 registered in Updates').toBe(true);
        expect(r.legacy).toBe('yes');
        expect(r.off).toBe('no');
    });
});
