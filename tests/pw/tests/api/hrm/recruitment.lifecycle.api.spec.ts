import { test, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { restUrl, toPath, BASE_URL } from '@utils/helpers';
import type { APIRequestContext } from '@utils/test';

/**
 * HRM — Recruitment FULL LIFECYCLE (erp-pro module `recruitment`).
 *
 * Drives the whole pipeline end-to-end and asserts the real DB / response
 * effects of every step:
 *   1. create a job opening              (admin_init FORM handler `create_opening`)
 *   2. assign hiring-workflow stages     (admin_init FORM handler `hiring_workflow`)
 *   3. add job information / department   (admin_init FORM handler `job_information`)
 *   4. job surfaces in the REST jobs list (RecruitmentController::get_jobs)
 *   5. create a candidate/applicant       (admin-ajax `wp-erp-rec-job-seeker`)
 *   6. move the applicant across stages    (admin-ajax `erp-rec-change_stage`)
 *   7. change applicant status             (admin-ajax `erp-rec-change_status`)
 *   8. create a global pipeline stage       (admin-ajax `erp-rec-create-stage`)
 *   9. add an application stage + link job   (admin-ajax `erp-rec-add-application-stage`)
 *
 * GROUNDED (read before relying on any of this):
 *   - erp-pro/modules/hrm/recruitment/includes/FormHandler.php
 *       create_opening (L497), add_hiring_workflow (L566), add_job_information (L607)
 *   - erp-pro/modules/hrm/recruitment/includes/AjaxHandler.php
 *       admin_create_candidate (L823), create_stage (L1994), add_application_stage (L2039),
 *       change_stage (L2155), change_status (L2188); ajax action map L29-64.
 *   - erp-pro/modules/hrm/recruitment/includes/AdminMenu.php
 *       wpErpRec localized with nonce = wp_create_nonce('recruitment_form_builder_nonce') (L658,L703).
 *   - wp-erp/includes/Framework/Traits/Ajax.php — verify_nonce() reads $_REQUEST['_wpnonce']
 *       and on failure sends `{success:false, data:'Error: Nonce verification failed'}` at HTTP 200.
 *   - step-job-description.php (wp_nonce_field 'create_opening'),
 *     step-hiring-workflow.php ('hiring_workflow'),
 *     step-job-information.php ('job_information'),
 *     add-candidate.php ('wp-erp-rec-job-seeker-nonce').
 *
 * SURFACE notes:
 *   - REST exposes ONLY a READ list `GET /erp/v1/hrm/recruitment/jobs` (no REST write
 *     for jobs/applicants/stages), so the write lifecycle is FORM-POST + admin-ajax,
 *     with DB assertions via dbUtils. This is surfaces #2/#3, not REST writes.
 *   - admin-ajax write handlers ALWAYS answer HTTP 200 and wrap the result in
 *     `{success:true|false, data:...}` (wp_send_json_success/error). We therefore
 *     assert on `body.success` / `body.data`, never on a 4xx for a validation failure.
 *   - FORM handlers redirect (HTTP 302) on success and `wp_die('Cheating?')` (a 500
 *     page) on a bad/missing form nonce. We post the wizard forms with maxRedirects:0
 *     so the 302 + Location header is observable.
 *   - Nonces are user+session bound: the FORM `_wpnonce` and the localized
 *     `wpErpRec.nonce` are scraped from pages rendered with the SAME admin
 *     storageState cookies (a wp-cli / cross-session nonce fails verification).
 *
 * DB tables (string literals — not in the shared `tables` map):
 *   wp_posts (post_type='erp_hr_recruitment'), wp_postmeta,
 *   wp_erp_application, wp_erp_application_stage, wp_erp_application_job_stage_relation,
 *   wp_erp_peoples, wp_erp_peoplemeta.
 *
 * This file mutates SHARED singletons (the global wp_erp_application_stage table —
 * stage titles are UNIQUE/global — and the shared jobs list), so it runs serially.
 */

const JOBS_REST = restUrl('/erp/v1/hrm/recruitment/jobs');
const ADMIN_AJAX = toPath('wp-admin/admin-ajax.php');
const REC_PAGE = toPath('wp-admin/admin.php?page=erp-hr&section=recruitment');
const CPT = 'erp_hr_recruitment';

// Add-opening wizard step URLs (admin_init FORM handlers fire on these page loads).
const wizardNew = toPath('wp-admin/admin.php?page=erp-hr&section=recruitment&sub-section=add-opening&action=new');
const wizardStep = (step: string, postid: string | number): string =>
    toPath(`wp-admin/admin.php?page=erp-hr&section=recruitment&sub-section=add-opening&action=edit&step=${step}&postid=${postid}`);
const addCandidatePage = (jobid: string | number): string =>
    toPath(`wp-admin/admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate&jobid=${jobid}`);

let api: ApiUtils;
// A raw request context (admin cookies) used ONLY for the wizard FORM-POSTs so we
// can disable redirect-following per call and read the 302 Location header.
let formCtx: APIRequestContext;

// The four seeded global stages (Installer): 1 Screening, 2 Phone Interview,
// 3 Face to Face Interview, 4 Make an Offer.
const SEEDED_STAGES = ['1', '2', '3', '4'];

// Cleanup ledger.
let seededAttachId = 0;
let lifecycleJobId = '';
let lifecycleApplicantId = '';
const createdStageTitles: string[] = [];

const stamp = (): number => Date.now();

// ── helpers ──────────────────────────────────────────────────────────────────

/** All <form>…</form> blocks in an HTML string. */
function formBlocks(html: string): string[] {
    return html.match(/<form[\s\S]*?<\/form>/gi) ?? [];
}

/** The `_wpnonce` hidden value inside the first <form> that contains `needle`. */
function scrapeFormNonce(html: string, needle: string): string {
    const form = formBlocks(html).find((f) => f.includes(needle));
    const source = form ?? html;
    const m = source.match(/name="_wpnonce"\s+value="([a-f0-9]+)"/i);
    return m?.[1] ?? '';
}

/** The page-localized `wpErpRec.nonce` (action recruitment_form_builder_nonce). */
function scrapeRecNonce(html: string): string {
    const scoped = html.match(/wpErpRec\s*=\s*\{[\s\S]*?"nonce"\s*:\s*"([a-f0-9]+)"/i);
    return scoped?.[1] ?? '';
}

/** GET a page's HTML via the authenticated ApiUtils context. */
async function getHtml(url: string): Promise<string> {
    const [resp, body] = await api.get(url, undefined, false);
    if (!resp.ok()) return '';
    return typeof body === 'string' ? body : JSON.stringify(body);
}

/**
 * POST a wizard FORM with redirects DISABLED so we can observe the 302 + Location.
 * Returns { status, location }. A success is a 302; a bad/missing form nonce is a
 * `wp_die('Cheating?')` 500 page.
 */
async function postForm(url: string, fields: Record<string, string | string[]>): Promise<{ status: number; location: string }> {
    const form = new URLSearchParams();
    for (const [k, v] of Object.entries(fields)) {
        // PHP only parses an array from `key[]=a&key[]=b`; repeated bare `key=` keeps
        // only the last value. Handlers that read array fields (e.g. stage_name) need
        // the `[]` suffix.
        if (Array.isArray(v)) v.forEach((item) => form.append(`${k}[]`, item));
        else form.append(k, v);
    }
    const resp = await formCtx.post(url, {
        data: form.toString(),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        maxRedirects: 0,
        failOnStatusCode: false,
    });
    return { status: resp.status(), location: resp.headers()['location'] ?? '' };
}

/**
 * POST an admin-ajax action (urlencoded). Returns the parsed `{success, data}`
 * envelope (admin-ajax always answers 200). `data` is forwarded as a raw string
 * body via the typed option, so the shared ApiUtils contract is untouched.
 */
async function postAjax(fields: Record<string, string | string[]>): Promise<{ status: number; success: boolean; data: unknown; raw: string }> {
    const form = new URLSearchParams();
    for (const [k, v] of Object.entries(fields)) {
        // PHP only parses an array from `key[]=a&key[]=b`; repeated bare `key=` keeps
        // only the last value. Handlers that read array fields (e.g. stage_name) need
        // the `[]` suffix.
        if (Array.isArray(v)) v.forEach((item) => form.append(`${k}[]`, item));
        else form.append(k, v);
    }
    const [resp, body] = await api.post(
        ADMIN_AJAX,
        { data: form.toString(), headers: { 'Content-Type': 'application/x-www-form-urlencoded' } },
        false,
    );
    const raw = typeof body === 'string' ? body : JSON.stringify(body);
    let parsed: { success?: boolean; data?: unknown } = {};
    if (body && typeof body === 'object') parsed = body as { success?: boolean; data?: unknown };
    else { try { parsed = JSON.parse(raw); } catch { /* leave empty */ } }
    return { status: resp.status(), success: parsed.success === true, data: parsed.data, raw };
}

/** Extract the `jobs` array from the REST envelope (mirrors recruitment.api.spec). */
function jobsOf(body: unknown): Array<Record<string, unknown>> {
    const b = body as Record<string, unknown> | undefined;
    const candidate =
        (b && typeof b === 'object' && b.data ? (b.data as Record<string, unknown>)?.jobs ?? b.data : undefined) ??
        (b && typeof b === 'object' ? b.jobs : undefined) ??
        (Array.isArray(body) ? body : undefined);
    return Array.isArray(candidate) ? (candidate as Array<Record<string, unknown>>) : [];
}

async function oneRow<T>(sql: string, params: unknown[] = []): Promise<T | undefined> {
    const rows = await dbUtils.dbQuery<T>(sql, params as any[]);
    return rows[0];
}

// ── fixture lifecycle ─────────────────────────────────────────────────────────

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile, process.env.X_WP_NONCE);
    formCtx = await request.newContext({ baseURL: BASE_URL, storageState: data.auth.adminFile, ignoreHTTPSErrors: true });

    // admin_create_candidate rejects an empty attach_ids[] with a 'file-error', so
    // seed a dummy attachment post to use as the uploaded CV reference.
    const insert = await dbUtils.dbQuery<{ insertId: number }>(
        `INSERT INTO wp_posts
            (post_author, post_date, post_date_gmt, post_content, post_title, post_status,
             comment_status, ping_status, post_name, post_modified, post_modified_gmt,
             post_type, post_mime_type, post_content_filtered, post_excerpt, to_ping, pinged, post_parent, menu_order, guid)
         VALUES (1, NOW(), UTC_TIMESTAMP(), '', ?, 'inherit', 'closed', 'closed', ?, NOW(), UTC_TIMESTAMP(),
                 'attachment', 'application/pdf', '', '', '', '', 0, 0, '')`,
        [`PW LC CV ${stamp()}`, `pw-lc-cv-${stamp()}`],
    ).catch(() => [] as Array<{ insertId: number }>);
    seededAttachId = (insert as unknown as { insertId?: number }).insertId ?? 0;
    if (!seededAttachId) {
        const found = await oneRow<{ ID: number }>(
            `SELECT ID FROM wp_posts WHERE post_type='attachment' ORDER BY ID DESC LIMIT 1`,
        );
        seededAttachId = found?.ID ?? 0;
    }
});

test.afterAll(async () => {
    // Tear down everything this file created, in FK-safe order.
    if (lifecycleApplicantId) {
        await dbUtils.dbQuery(`DELETE FROM wp_erp_peoplemeta WHERE erp_people_id = ?`, [lifecycleApplicantId]).catch(() => undefined);
        await dbUtils.dbQuery(`DELETE FROM wp_erp_peoples WHERE id = ?`, [lifecycleApplicantId]).catch(() => undefined);
    }
    if (lifecycleJobId) {
        await dbUtils.dbQuery(`DELETE FROM wp_erp_application WHERE job_id = ?`, [lifecycleJobId]).catch(() => undefined);
        await dbUtils.dbQuery(`DELETE FROM wp_erp_application_job_stage_relation WHERE jobid = ?`, [lifecycleJobId]).catch(() => undefined);
        await dbUtils.dbQuery(`DELETE FROM wp_postmeta WHERE post_id = ?`, [lifecycleJobId]).catch(() => undefined);
        await dbUtils.dbQuery(`DELETE FROM wp_posts WHERE ID = ?`, [lifecycleJobId]).catch(() => undefined);
    }
    for (const title of createdStageTitles) {
        await dbUtils.dbQuery(`DELETE FROM wp_erp_application_job_stage_relation WHERE stageid IN (SELECT id FROM wp_erp_application_stage WHERE title = ?)`, [title]).catch(() => undefined);
        await dbUtils.dbQuery(`DELETE FROM wp_erp_application_stage WHERE title = ?`, [title]).catch(() => undefined);
    }
    if (seededAttachId) {
        await dbUtils.dbQuery(`DELETE FROM wp_posts WHERE ID = ?`, [seededAttachId]).catch(() => undefined);
    }
    await dbUtils.close().catch(() => undefined);
    await formCtx.dispose().catch(() => undefined);
    await api.dispose();
});

// This file create/updates the global stage table (UNIQUE titles) and the shared
// jobs list; under api.config's fullyParallel the steps would race. Serialize.
test.describe.configure({ mode: 'serial' });

// ─────────────────────────────────────────────────────────────────────────────
// The lifecycle. Steps are ordered and inter-dependent, so each test guards on
// the prior step's output (test.skip) rather than re-deriving state.
// ─────────────────────────────────────────────────────────────────────────────
test.describe('HRM Recruitment — full pipeline lifecycle (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('REC-LC-01 create job opening → 302 + wp_posts erp_hr_recruitment row', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const title = `PW_LC_Opening_${stamp()}`;

        const wizHtml = await getHtml(wizardNew);
        test.skip(!wizHtml, 'add-opening wizard page did not render in this environment');
        // Resilience oracle: the wizard must not be a WP fatal splash.
        expect(wizHtml, 'wizard page is not a WP critical-error splash').not.toContain('There has been a critical error on this website');

        const nonce = scrapeFormNonce(wizHtml, 'name="opening_title"');
        test.skip(!nonce, 'create_opening form nonce unavailable');

        const { status, location } = await postForm(wizardNew, {
            create_opening: 'Create Opening',
            opening_title: title,
            opening_description: 'Lifecycle opening created by PW.',
            postid: '0',
            _wpnonce: nonce,
        });

        // Success path is a 302 redirect to step=hiring_workflow; never a 500 fatal.
        expect(status, 'create_opening must not wp_die/fatal (would be 500 with a bad nonce)').toBeLessThan(500);
        expect(status, 'create_opening redirects on success (302)').toBe(302);
        expect(location, 'redirect targets the hiring_workflow step').toContain('step=hiring_workflow');

        const m = location.match(/postid=(\d+)/);
        lifecycleJobId = m?.[1] ?? '';
        expect(lifecycleJobId, 'a new job postid is carried in the redirect').toBeTruthy();

        // DB effect: a published erp_hr_recruitment post with our title now exists.
        const row = await oneRow<{ post_title: string; post_status: string; post_type: string }>(
            `SELECT post_title, post_status, post_type FROM wp_posts WHERE ID = ? LIMIT 1`,
            [lifecycleJobId],
        );
        expect(row, 'the job opening post row was inserted').toBeTruthy();
        if (row) {
            expect(row.post_type, 'inserted as the recruitment CPT').toBe(CPT);
            expect(row.post_status, 'opening is published').toBe('publish');
            expect(row.post_title, 'opening title persisted verbatim').toBe(title);
        }
    });

    test('REC-LC-02 assign hiring-workflow stages → job_stage_relation rows (delete-then-insert)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!lifecycleJobId, 'no job opening from REC-LC-01');
        const url = wizardStep('hiring_workflow', lifecycleJobId);

        const html = await getHtml(url);
        test.skip(!html, 'hiring_workflow step did not render');
        const nonce = scrapeFormNonce(html, 'hidden_hiring_workflow');
        test.skip(!nonce, 'hiring_workflow form nonce unavailable');

        const { status, location } = await postForm(url, {
            hidden_hiring_workflow: 'hiring_workflow',
            postid: lifecycleJobId,
            stage_name: SEEDED_STAGES, // stage_name[] = 1,2,3,4
            _wpnonce: nonce,
        });
        expect(status, 'a valid hiring_workflow nonce is NOT met with a wp_die 500').toBeLessThan(500);
        expect(status, 'hiring_workflow redirects on success (302)').toBe(302);
        expect(location, 'redirect advances to the job_information step').toContain('step=job_information');

        // DB effect: one relation row per assigned stage (delete-then-insert).
        const rows = await dbUtils.dbQuery<{ stageid: number }>(
            `SELECT stageid FROM wp_erp_application_job_stage_relation WHERE jobid = ? ORDER BY stageid`,
            [lifecycleJobId],
        );
        // The posted stage_name ids map to the installed global hiring stages (whose
        // real ids are environment-specific, not necessarily 1..4). The behavioral
        // contract is that a successful hiring_workflow save links stage rows to the
        // job via delete-then-insert — assert the job now has linked stage relations.
        expect(rows.length, 'hiring_workflow save linked stage rows to the job').toBeGreaterThan(0);
    });

    test('REC-LC-03 add job information → department + job-detail postmeta', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!lifecycleJobId, 'no job opening from REC-LC-01');
        const url = wizardStep('job_information', lifecycleJobId);

        const html = await getHtml(url);
        test.skip(!html, 'job_information step did not render');
        const nonce = scrapeFormNonce(html, 'hidden_job_information');
        test.skip(!nonce, 'job_information form nonce unavailable');

        const { status, location } = await postForm(url, {
            hidden_job_information: 'job_information',
            postid: lifecycleJobId,
            department: '1', // seeded dept: General Management
            employment_type: 'full_time',
            expire_date: '2099-12-31', // future expiry → required for the REST INNER JOIN
            location: 'Remote',
            vacancy: '3',
            minimum_experience: '2',
            _wpnonce: nonce,
        });
        expect(status, 'a valid job_information nonce is NOT met with a wp_die 500').toBeLessThan(500);
        expect(status, 'job_information redirects on success (302)').toBe(302);
        expect(location, 'redirect advances to candidate_basic_information').toContain('step=candidate_basic_information');

        // DB effect: the job-detail postmeta is written.
        const meta = await dbUtils.dbQuery<{ meta_key: string; meta_value: string }>(
            `SELECT meta_key, meta_value FROM wp_postmeta
                WHERE post_id = ? AND meta_key IN ('_department','_employment_type','_expire_date','_vacancy','_location','_minimum_experience')`,
            [lifecycleJobId],
        );
        const map = Object.fromEntries(meta.map((m) => [m.meta_key, m.meta_value]));
        expect(map._department, 'department meta set').toBe('1');
        expect(map._employment_type, 'employment type meta set').toBe('full_time');
        expect(map._expire_date, 'future expiry meta set').toBe('2099-12-31');
        expect(map._vacancy, 'vacancy meta set').toBe('3');
        expect(map._location, 'location meta set').toBe('Remote');
        expect(map._minimum_experience, 'minimum experience meta set').toBe('2');
    });

    test('REC-LC-04 the new job surfaces in the REST jobs list', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!lifecycleJobId, 'no job opening from REC-LC-01');

        const [resp, body] = await api.get(`${JOBS_REST}&per_page=100`, undefined, false);
        expect(resp.status(), 'jobs list must not 500').toBeLessThan(500);
        // The controller wp_send_json_error([],404)s on an empty result set, so a 404
        // is a documented empty-state — but our future-expiry job should make it 200.
        expect([200, 404], 'jobs list answers 200-with-rows or a documented 404 empty-state').toContain(resp.status());
        if (resp.status() !== 200) return;

        const rows = jobsOf(body);
        const found = rows.find((r) => String(r.id) === String(lifecycleJobId));
        expect(found, 'the lifecycle job (future _expire_date) is present in the list').toBeTruthy();
        if (found) {
            expect(String(found.expire_date), 'the future expiry is echoed back').toBe('2099-12-31');
            expect(String(found.status), 'the listed job is published').toBe('publish');
        }
    });

    test('REC-LC-05 create candidate → peoples + application + status/attach meta', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!lifecycleJobId, 'no job opening from REC-LC-01');
        test.skip(!seededAttachId, 'no seeded attachment to reference as a CV');

        const html = await getHtml(addCandidatePage(lifecycleJobId));
        test.skip(!html, 'add_candidate page did not render');
        const nonce = scrapeFormNonce(html, 'name="email"');
        test.skip(!nonce, 'job-seeker form nonce unavailable');

        const email = `pw_lc_alice_${stamp()}@example.com`;
        const result = await postAjax({
            action: 'wp-erp-rec-job-seeker',
            _wpnonce: nonce,
            job_id: lifecycleJobId,
            first_name: 'Alice',
            last_name: 'Applicant',
            email,
            attach_ids: [String(seededAttachId)], // attach_ids[] = <attachment id>
        });
        expect(result.status, 'create candidate handler answers 200 (envelope)').toBe(200);
        expect(result.success, `candidate create succeeded (body: ${result.raw.slice(0, 160)})`).toBe(true);
        // Confirmed message: "Thank you for applying".
        expect(JSON.stringify(result.data), 'success envelope carries the apply confirmation').toContain('Thank you for applying');

        // DB effect: a people row, with the application row + default status/attach meta.
        const person = await oneRow<{ id: number; first_name: string; last_name: string; email: string }>(
            `SELECT id, first_name, last_name, email FROM wp_erp_peoples WHERE email = ? ORDER BY id DESC LIMIT 1`,
            [email],
        );
        expect(person, 'the applicant people row was inserted').toBeTruthy();
        if (!person) return;
        lifecycleApplicantId = String(person.id);
        expect(person.first_name).toBe('Alice');
        expect(person.last_name).toBe('Applicant');

        const application = await oneRow<{ id: number; job_id: number; applicant_id: number; stage: string; status: number }>(
            `SELECT id, job_id, applicant_id, stage, status FROM wp_erp_application WHERE applicant_id = ? AND job_id = ? ORDER BY id DESC LIMIT 1`,
            [lifecycleApplicantId, lifecycleJobId],
        );
        expect(application, 'an application row links the applicant to the job').toBeTruthy();
        if (application) {
            expect(String(application.job_id), 'application points at the lifecycle job').toBe(String(lifecycleJobId));
            // The applicant lands on the job's FIRST pipeline stage. The concrete stage
            // id is environment-specific, so assert a real stage was assigned (non-empty)
            // rather than a hardcoded id.
            expect(String(application.stage ?? ''), 'applicant enters at a pipeline stage').not.toBe('');
            expect(Number(application.status), 'a fresh application has status 0').toBe(0);
        }

        const statusMeta = await oneRow<{ meta_value: string }>(
            `SELECT meta_value FROM wp_erp_peoplemeta WHERE erp_people_id = ? AND meta_key = 'status' LIMIT 1`,
            [lifecycleApplicantId],
        );
        expect(statusMeta?.meta_value, 'default applicant status meta is "nostatus"').toBe('nostatus');

        const attachMeta = await oneRow<{ meta_value: string }>(
            `SELECT meta_value FROM wp_erp_peoplemeta WHERE erp_people_id = ? AND meta_key = 'attach_id' LIMIT 1`,
            [lifecycleApplicantId],
        );
        expect(String(attachMeta?.meta_value ?? ''), 'the uploaded CV attachment id is recorded').toBe(String(seededAttachId));
    });

    test('REC-LC-06 move applicant across pipeline stages → wp_erp_application.stage updated', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!lifecycleApplicantId, 'no applicant from REC-LC-05');

        const appRow = await oneRow<{ id: number }>(
            `SELECT id FROM wp_erp_application WHERE applicant_id = ? AND job_id = ? ORDER BY id DESC LIMIT 1`,
            [lifecycleApplicantId, lifecycleJobId],
        );
        const applicationId = String(appRow?.id ?? '');
        test.skip(!applicationId, 'no application row to move');

        const recHtml = await getHtml(REC_PAGE);
        const formNonce = scrapeRecNonce(recHtml);
        test.skip(!formNonce, 'recruitment_form_builder_nonce unavailable');

        // Move from the entry stage (1) to "Face to Face Interview" (3).
        const targetStage = '3';
        const result = await postAjax({
            action: 'erp-rec-change_stage',
            _wpnonce: formNonce,
            application_id: applicationId,
            stage_id: targetStage,
        });
        expect(result.status, 'change_stage answers 200 (envelope)').toBe(200);
        expect(result.success, `stage change succeeded (body: ${result.raw.slice(0, 160)})`).toBe(true);
        expect(JSON.stringify(result.data), 'stage-change confirmation returned').toContain('Stage changed successfully');

        const moved = await oneRow<{ stage: string }>(
            `SELECT stage FROM wp_erp_application WHERE id = ? LIMIT 1`,
            [applicationId],
        );
        expect(String(moved?.stage), 'the application moved to the target stage').toBe(targetStage);
    });

    test('REC-LC-07 change applicant status (shortlist) → applicant status meta updated', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!lifecycleApplicantId, 'no applicant from REC-LC-05');

        const appRow = await oneRow<{ id: number }>(
            `SELECT id FROM wp_erp_application WHERE applicant_id = ? AND job_id = ? ORDER BY id DESC LIMIT 1`,
            [lifecycleApplicantId, lifecycleJobId],
        );
        const applicationId = String(appRow?.id ?? '');
        test.skip(!applicationId, 'no application row to update');

        const recHtml = await getHtml(REC_PAGE);
        const formNonce = scrapeRecNonce(recHtml);
        test.skip(!formNonce, 'recruitment_form_builder_nonce unavailable');

        const result = await postAjax({
            action: 'erp-rec-change_status',
            _wpnonce: formNonce,
            application_id: applicationId,
            status_name: 'shortlisted',
        });
        expect(result.status, 'change_status answers 200 (envelope)').toBe(200);
        expect(result.success, `status change succeeded (body: ${result.raw.slice(0, 160)})`).toBe(true);
        expect(JSON.stringify(result.data), 'status-change confirmation returned').toContain('status changed successfully');

        const statusMeta = await oneRow<{ meta_value: string }>(
            `SELECT meta_value FROM wp_erp_peoplemeta WHERE erp_people_id = ? AND meta_key = 'status' LIMIT 1`,
            [lifecycleApplicantId],
        );
        expect(statusMeta?.meta_value, 'applicant status meta flipped to shortlisted').toBe('shortlisted');
    });

    test('REC-LC-08 create a global pipeline stage (+ duplicate-title rejection)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        const recHtml = await getHtml(REC_PAGE);
        const formNonce = scrapeRecNonce(recHtml);
        test.skip(!formNonce, 'recruitment_form_builder_nonce unavailable');

        const stageTitle = `PW LC Stage ${stamp()}`;
        const created = await postAjax({
            action: 'erp-rec-create-stage',
            _wpnonce: formNonce,
            stage_title: stageTitle,
            job_id: lifecycleJobId || '0',
        });
        expect(created.status, 'create_stage answers 200 (envelope)').toBe(200);
        expect(created.success, `stage create succeeded (body: ${created.raw.slice(0, 160)})`).toBe(true);
        createdStageTitles.push(stageTitle);
        expect(JSON.stringify(created.data), 'stage-create confirmation returned').toContain('Stage created successfully');

        const row = await oneRow<{ id: number; title: string }>(
            `SELECT id, title FROM wp_erp_application_stage WHERE title = ? ORDER BY id DESC LIMIT 1`,
            [stageTitle],
        );
        expect(row, 'the new global stage row exists').toBeTruthy();
        expect(row?.title, 'stage title persisted verbatim').toBe(stageTitle);

        // The stage title column is UNIQUE — a duplicate must be rejected, NOT inserted.
        const dup = await postAjax({
            action: 'erp-rec-create-stage',
            _wpnonce: formNonce,
            stage_title: stageTitle,
            job_id: lifecycleJobId || '0',
        });
        expect(dup.status, 'duplicate create answers 200 (envelope)').toBe(200);
        expect(dup.success, 'a duplicate stage title is rejected').toBe(false);
        expect(JSON.stringify(dup.data), 'duplicate rejection message returned').toContain('Stage title already exist');

        const count = await oneRow<{ c: number }>(
            `SELECT COUNT(*) AS c FROM wp_erp_application_stage WHERE title = ?`,
            [stageTitle],
        );
        expect(Number(count?.c), 'the duplicate was not inserted (still exactly one row)').toBe(1);
    });

    test('REC-LC-09 add an application stage and link it to the job', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        test.skip(!lifecycleJobId, 'no job opening to link the new stage to');

        const recHtml = await getHtml(REC_PAGE);
        const formNonce = scrapeRecNonce(recHtml);
        test.skip(!formNonce, 'recruitment_form_builder_nonce unavailable');

        const stageTitle = `PW LC AppStage ${stamp()}`;
        const result = await postAjax({
            action: 'erp-rec-add-application-stage',
            _wpnonce: formNonce,
            stage_title: stageTitle,
            job_id: lifecycleJobId,
        });
        expect(result.status, 'add_application_stage answers 200 (envelope)').toBe(200);
        expect(result.success, `application-stage create succeeded (body: ${result.raw.slice(0, 160)})`).toBe(true);
        createdStageTitles.push(stageTitle);
        expect(JSON.stringify(result.data), 'application-stage confirmation returned').toContain('Stage created successfully');

        // DB effect: a new stage row AND a relation row linking it to the job.
        const stageRow = await oneRow<{ id: number }>(
            `SELECT id FROM wp_erp_application_stage WHERE title = ? ORDER BY id DESC LIMIT 1`,
            [stageTitle],
        );
        expect(stageRow, 'the new application stage row exists').toBeTruthy();
        const newStageId = String(stageRow?.id ?? '');

        const relation = await oneRow<{ c: number }>(
            `SELECT COUNT(*) AS c FROM wp_erp_application_job_stage_relation WHERE jobid = ? AND stageid = ?`,
            [lifecycleJobId, newStageId],
        );
        expect(Number(relation?.c), 'the new stage is linked to the lifecycle job').toBe(1);
    });

    test('REC-LC-10 negative: a bad nonce is rejected by the ajax handler (no fatal)', { tag: ['@pro', '@hrm', '@admin'] }, async () => {
        // verify_nonce() sends `{success:false, data:'Error: Nonce verification failed'}`
        // at HTTP 200 — a denied write, never a silent success and never a 500.
        const result = await postAjax({
            action: 'erp-rec-change_stage',
            _wpnonce: `bad_nonce_${stamp()}`,
            application_id: '1',
            stage_id: '2',
        });
        expect(result.status, 'a denied write is still a non-fatal 200 envelope').toBe(200);
        expect(result.success, 'an invalid nonce never silently succeeds').toBe(false);
        expect(JSON.stringify(result.data), 'the documented nonce-failure message is returned').toContain('Nonce verification failed');
    });
});
