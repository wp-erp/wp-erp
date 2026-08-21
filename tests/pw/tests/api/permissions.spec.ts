import { test, expect, request as playwrightRequest } from '@playwright/test';
import { env, restPath } from '@utils/helpers';
import { erpRouteCalls } from '@utils/routeTable';
import { closeDb, execute, prefix } from '@utils/dbUtils';
import { type Observation, classify, summarise } from '@utils/permissionOracle';

/**
 * REST — who is allowed in (anonymous).
 *
 * A sweep of every ERP-owned route the running site registers (289 patterns,
 * ~502 route/method pairs, from `harness/routes.json`), called with no
 * credentials at all. Not one `erp/v1` or `erp_pro/v1` route is meant to serve
 * a stranger.
 *
 * **The oracle** — a WordPress `permission_callback` runs BEFORE the handler,
 * so a guarded route answers 401/403 whether or not the record exists, while an
 * unguarded one falls through and answers 404/400/200. Every parameterised
 * route is called with an id that cannot exist, so anything but a refusal is
 * the door opening. The several responses that only LOOK like access —
 * parameter 400s, broken-handler 500s, public indexes, already-filed holes —
 * are told apart by `classify()` in `@utils/permissionOracle`, shared with the
 * per-role matrix.
 *
 * **Safety** — every DELETE in the table carries an id parameter (checked, not
 * assumed), so a wrongly-allowed destructive call addresses a record that is
 * not there; the worst an unguarded route does is create an empty row, which
 * the sweep reports rather than hides.
 */

const AREAS = ['hrm', 'crm', 'accounting', 'pro', 'core'] as const;

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
                    const response = await context.fetch(restPath(call.path), { method: call.method, data: {} });
                    const status = response.status();
                    const body = (await response.text()).replace(/\s+/g, ' ').slice(0, 160);

                    switch (classify(route, status, body, call.pattern)) {
                        case 'refused':
                        case 'carve-out':
                            break;
                        case 'param-unproven':
                        case 'broken':
                            unproven.push({ route, status, body });
                            break;
                        case 'answered':
                            answered.push({ route, status, body });
                            break;
                    }
                }
            } finally {
                await context.dispose();
            }

            if (unproven.length) {
                console.log(`[${area}] ${summarise('could not be reached past parameter validation', unproven, calls.length)}`);
            }

            expect(answered, summarise('answered a stranger instead of refusing', answered, calls.length)).toEqual([]);
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
