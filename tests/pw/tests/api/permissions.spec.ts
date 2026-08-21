import { test, expect, request as playwrightRequest } from '@playwright/test';
import { env, restPath } from '@utils/helpers';
import { erpRouteCalls } from '@utils/routeTable';
import { closeDb, execute, prefix } from '@utils/dbUtils';

/**
 * REST — who is allowed in.
 *
 * A sweep of every ERP-owned route the running site registers (289 patterns,
 * 502 route/method pairs, read from `harness/routes.json`), called by someone
 * who should not be able to use it.
 *
 * **The oracle, and why it holds.** A WordPress `permission_callback` runs
 * BEFORE the handler. So a guarded route answers 401 or 403 whether or not the
 * record exists — while an unguarded one falls through to its handler and
 * answers 404, 400 or 200. Every parameterised route here is called with an id
 * that cannot exist, which means **anything other than 401/403 is the door
 * opening**, not a missing record.
 *
 * This is the seam that produced the two Critical bugs in this cycle
 * (erp-pro#974, erp-pro#975): endpoints reachable with no capability check at
 * all. A per-route sweep is the cheapest way to find the rest of them.
 *
 * **Safety.** Nothing here can destroy data: every DELETE in the table carries
 * an id parameter — checked against the table rather than assumed — so a
 * destructive call that is wrongly allowed addresses a record that is not
 * there. The worst an unguarded route can do is create an empty row, and the
 * sweep reports that rather than hiding it.
 */

/** Statuses that mean the door was held shut. */
const REFUSED = [401, 403];

/**
 * Routes that are public on purpose.
 *
 * WordPress serves a namespace index for every registered namespace — it is how
 * a client discovers the API — and that is core behaviour, not ERP's choice.
 */
const PUBLIC_BY_DESIGN = new Set(['GET /erp/v1', 'GET /erp_pro/v1/admin']);

/**
 * WordPress validates required and typed parameters BEFORE it runs the
 * permission callback, so a route with required args answers 400 to an empty
 * request and never reaches its guard. That 400 proves nothing either way.
 *
 * This distinction is the whole reason this file reports two lists instead of
 * one. The first version of this sweep counted those 400s as holes and produced
 * **15 false positives**, including "anonymous module activation" and
 * "anonymous money transfer". Both were re-probed with well-formed payloads and
 * both answered 401 with nothing written. The guards were fine; the oracle was
 * not.
 */
const PARAM_REJECTIONS = ['rest_missing_callback_param', 'rest_invalid_param'];

/**
 * A dispatch-level 500: WordPress could not find a callable handler for the
 * route. It is returned identically to admin and anonymous — verified against
 * this build — so it is a broken route registration, not a permission hole.
 * Reported, never counted as a hole. Candidate minor bug in its own right.
 */
const BROKEN_ROUTE = 'rest_invalid_handler';

/**
 * Anonymous reads that are known and tolerated, each for a stated reason. These
 * were triaged by hand and are NOT holes; anything not on this list that answers
 * a stranger fails the sweep.
 *
 *   - `recruitment/jobs` is a public careers listing by nature (it does expose
 *     department names — noted in COVERAGE, judged low, not filed).
 *   - the `company/*` routes return static enums (genders, marital statuses,
 *     result types, performance ratings) — no site data at all.
 */
const TOLERATED_ANON_READS = new Set([
    'GET /erp/v1/hrm/recruitment/jobs',
    'GET /erp/v1/hrm/company/genders',
    'GET /erp/v1/hrm/company/marital-statuses',
    'GET /erp/v1/hrm/company/education-result-types',
    'GET /erp/v1/hrm/company/performance-ratings',
]);

/**
 * Auth holes that are already known and filed. Held out of the main sweep so a
 * standing bug does not mask a NEW one, and each is asserted separately in its
 * own known-defect guard below — the day it is fixed, that guard flips and the
 * sweep is unaffected. Keyed by a substring of the route pattern.
 */
const KNOWN_HOLES: Record<string, string> = {
    '/erp/v1/hrm/employees/upload': 'ERP-165 / erp-pro#985 — anonymous file upload',
    '/erp/v1/hrm/docs/dropbox': 'ERP-002 — docs/dropbox reachable unauthenticated',
};

const AREAS = ['hrm', 'crm', 'accounting', 'pro', 'core'] as const;

interface Observation {
    route: string;
    status: number;
    body: string;
}

function describe(label: string, rows: Observation[], total: number): string {
    const lines = rows.map((row) => `  ${row.route} -> ${row.status} ${row.body}`);

    return `${rows.length} of ${total} ${label}:\n${lines.join('\n')}`;
}

test.describe('REST — who is allowed in', () => {
    /**
     * Anonymous callers first, because this rule has no exceptions to argue
     * about: not one `erp/v1` or `erp_pro/v1` route is meant to serve a
     * stranger. (The one genuinely public ERP surface — the read-only invoice
     * link — is not a REST route; it is a query-string page, and it has its own
     * bug, erp-pro#979.)
     */
    for (const area of AREAS) {
        test(`no ${area} route answers a stranger`, { tag: ['@tier3', '@api', '@authz', '@permissions'] }, async () => {
            const calls = erpRouteCalls().filter((call) => call.area === area);

            test.skip(calls.length === 0, `no ${area} routes are registered on this build`);

            const context = await playwrightRequest.newContext({ baseURL: env('BASE_URL') });
            const answered: Observation[] = [];
            const unproven: Observation[] = [];

            try {
                for (const call of calls) {
                    const route = `${call.method} ${call.pattern}`;

                    if (PUBLIC_BY_DESIGN.has(route)) continue;

                    const response = await context.fetch(restPath(call.path), { method: call.method, data: {} });
                    const status = response.status();

                    if (REFUSED.includes(status)) continue;

                    const body = (await response.text()).replace(/\s+/g, ' ').slice(0, 160);
                    const observation = { route, status, body };

                    // 400 on a parameter is WordPress stopping short of the
                    // guard, not the guard letting go. Recorded, never counted
                    // as a pass and never counted as a hole.
                    if (status === 400 && PARAM_REJECTIONS.some((code) => body.includes(code))) {
                        unproven.push(observation);
                        continue;
                    }

                    // A broken handler answers everyone the same way; it is not
                    // a permission decision. Recorded, not a hole.
                    if (status === 500 && body.includes(BROKEN_ROUTE)) {
                        unproven.push(observation);
                        continue;
                    }

                    // The known upload hole and the tolerated public reads are
                    // triaged; they must not fail the sweep.
                    if (Object.keys(KNOWN_HOLES).some((hole) => call.pattern.includes(hole)) || TOLERATED_ANON_READS.has(route)) {
                        continue;
                    }

                    answered.push(observation);
                }
            } finally {
                await context.dispose();
            }

            if (unproven.length) {
                console.log(`[${area}] ${describe('could not be reached past parameter validation', unproven, calls.length)}`);
            }

            expect(answered, describe('answered a stranger instead of refusing', answered, calls.length)).toEqual([]);
        });
    }

    test.fail(
        'the employee upload endpoint refuses a stranger',
        { tag: ['@tier3', '@api', '@authz', '@permissions', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-165 / erp-pro#985. `POST /erp/v1/hrm/employees/upload`
            // answers 200 to an anonymous multipart request and creates a media
            // attachment (`post_author = 0`), because its permission callback
            // treats "no user" as "an employee" (`0 === 0`). A tiny valid PNG is
            // enough to prove it; the guard flips green when the callback bails on
            // a logged-out request. Nothing is cleaned up here because the guard
            // is expected to be REFUSED — if it ever passes (401/403) no
            // attachment is created; while the defect stands, the create is the
            // failure this test is documenting.
            // Empty headers, deliberately: `api.config.ts` sets a default
            // `Content-Type: application/json`, and a manually-created context
            // inherits it — which turns the multipart body into an "Invalid JSON"
            // 400 and would make this guard fail for the WRONG reason (a reason
            // that would never clear when the bug is fixed). `{}` lets Playwright
            // set the multipart content-type itself.
            const context = await playwrightRequest.newContext({ baseURL: env('BASE_URL'), extraHTTPHeaders: {} });

            try {
                // 1x1 PNG, base64 — no fixture file needed.
                const pngBase64 =
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
                const png = Buffer.from(pngBase64, 'base64');

                const response = await context.fetch(restPath('/erp/v1/hrm/employees/upload'), {
                    method: 'POST',
                    multipart: {
                        image: { name: 'pw-anon.png', mimeType: 'image/png', buffer: png },
                    },
                });

                const status = response.status();
                const body = await response.text();

                // While the defect stands the upload SUCCEEDS, so this guard
                // creates a real attachment every run. Remove it, or the suite
                // pollutes the media library on every CI pass. When the bug is
                // fixed the response is 401/403 with no `photo_id` and this is a
                // no-op.
                const photoId = Number(JSON.parse(body || '{}').photo_id);

                if (Number.isInteger(photoId) && photoId > 0) {
                    await execute(`DELETE FROM ${prefix()}postmeta WHERE post_id = ?`, [photoId]);
                    await execute(`DELETE FROM ${prefix()}posts WHERE ID = ? AND post_author = 0`, [photoId]);
                }

                expect([401, 403], `an anonymous upload is refused, got ${status}: ${body.slice(0, 120)}`).toContain(status);
            } finally {
                await context.dispose();
                await closeDb();
            }
        }
    );

    test.fail(
        'the dropbox docs endpoint refuses a stranger',
        { tag: ['@tier3', '@api', '@authz', '@permissions', '@known-defect'] },
        async () => {
            // KNOWN DEFECT — ERP-002 (already filed, Open). `GET
            // /erp/v1/hrm/docs/dropbox` answers 200 to an anonymous request: the
            // ERP permission callback is bypassed and the handler goes on to call
            // the Dropbox API, which fails on Dropbox's side with a 400 wrapped in
            // that 200. Same response for admin and anonymous, so the ERP guard is
            // simply absent. This sweep re-found it independently, which is the
            // point of running it. The guard flips when ERP refuses the caller
            // before reaching Dropbox.
            const context = await playwrightRequest.newContext({ baseURL: env('BASE_URL') });

            try {
                const response = await context.fetch(restPath('/erp/v1/hrm/docs/dropbox'), { method: 'GET' });

                expect(
                    [401, 403],
                    `an anonymous dropbox read is refused, got ${response.status()}: ${(await response.text()).slice(0, 120)}`
                ).toContain(response.status());
            } finally {
                await context.dispose();
            }
        }
    );
});
