/**
 * The shared classification behind the REST permission sweeps.
 *
 * Both the anonymous sweep (`permissions.spec.ts`) and the per-role matrix
 * (`roleMatrix.spec.ts`) ask the same question — did a caller who should be
 * refused get in? — and both have to tell a real refusal apart from the several
 * ways WordPress answers that only LOOK like access. That logic lives here, in
 * one place, so a newly-found public route or known hole is added once.
 */

/** Statuses that mean the door was held shut. */
export const REFUSED = [401, 403];

/**
 * Namespace indexes WordPress serves to anyone — how a client discovers the
 * API. Core behaviour, not ERP's choice, so never a hole.
 */
export const PUBLIC_BY_DESIGN = new Set(['GET /erp/v1', 'GET /erp_pro/v1/admin']);

/**
 * WordPress validates required and typed parameters BEFORE it runs the
 * permission callback, so a route with required args answers 400 to an empty
 * request and never reaches its guard. That 400 proves nothing either way.
 *
 * This distinction is the whole reason the sweeps report an `unproven` list
 * separately. The first anonymous run counted those 400s as holes and produced
 * 15 false positives — "anonymous module activation", "anonymous money
 * transfer" — every one of which answered 401 to a well-formed payload.
 */
export const PARAM_REJECTIONS = ['rest_missing_callback_param', 'rest_invalid_param'];

/**
 * A dispatch-level 500: WordPress found no callable handler for the route. It
 * is returned identically whoever calls it — verified against this build — so
 * it is a broken route registration, not a permission decision.
 */
export const BROKEN_ROUTE = 'rest_invalid_handler';

/**
 * Routes that answer 200 to callers who have not earned any ERP capability —
 * anonymous included — and are tolerated for a stated reason:
 *
 *   - `recruitment/jobs` is a public careers listing by nature (it does expose
 *     department names — noted in COVERAGE, judged low, not filed);
 *   - the `company/*` routes return static enums (genders, marital statuses,
 *     result types, performance ratings) — no site data at all.
 *
 * They are excluded from every deny assertion, at every privilege level.
 */
export const PUBLIC_READS = new Set([
    'GET /erp/v1/hrm/recruitment/jobs',
    'GET /erp/v1/hrm/company/genders',
    'GET /erp/v1/hrm/company/marital-statuses',
    'GET /erp/v1/hrm/company/education-result-types',
    'GET /erp/v1/hrm/company/performance-ratings',
]);

/**
 * Auth holes that are already known and filed. Held out of the sweeps so a
 * standing bug does not mask a new one; each is asserted separately in its own
 * known-defect guard. Both answer 200 to anyone, so they surface at every
 * privilege level. Keyed by a substring of the route pattern.
 */
export const KNOWN_HOLES: Record<string, string> = {
    '/erp/v1/hrm/employees/upload': 'ERP-165 / erp-pro#985 — anonymous file upload',
    '/erp/v1/hrm/docs/dropbox': 'ERP-002 — docs/dropbox reachable unauthenticated',
};

export type Verdict = 'refused' | 'param-unproven' | 'broken' | 'carve-out' | 'answered';

export interface Observation {
    route: string;
    status: number;
    body: string;
}

/**
 * What a single response means for a caller who was supposed to be refused.
 *
 *   - `refused`        the guard held (401/403).
 *   - `param-unproven` a 400 on a parameter — the guard was never reached.
 *   - `broken`         a 500 broken-handler — auth-independent, not a decision.
 *   - `carve-out`      a public index, public read, or already-filed known hole.
 *   - `answered`       none of the above: the caller got IN. This is the hole.
 */
export function classify(route: string, status: number, body: string, pattern: string): Verdict {
    if (PUBLIC_BY_DESIGN.has(route) || PUBLIC_READS.has(route)) return 'carve-out';
    if (Object.keys(KNOWN_HOLES).some((hole) => pattern.includes(hole))) return 'carve-out';
    if (REFUSED.includes(status)) return 'refused';
    if (status === 400 && PARAM_REJECTIONS.some((code) => body.includes(code))) return 'param-unproven';
    if (status === 500 && body.includes(BROKEN_ROUTE)) return 'broken';

    return 'answered';
}

/** A readable multi-line summary for an assertion message. */
export function summarise(label: string, rows: Observation[], total: number): string {
    const lines = rows.map((row) => `  ${row.route} -> ${row.status} ${row.body}`);

    return `${rows.length} of ${total} ${label}:\n${lines.join('\n')}`;
}
