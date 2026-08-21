import routes from '@harness/routes.json';

/**
 * The live REST route table, turned into something callable.
 *
 * `harness/routes.json` is captured from the running site (289 routes) and
 * holds WordPress's registered PATTERNS, not paths — `(?P<id>[\d]+)` and
 * friends. Nothing can be requested until those are substituted, which is what
 * this module is for.
 */

export interface RouteCall {
    /** The registered pattern, kept for reporting so a failure names the route. */
    pattern: string;
    /** A concrete, requestable path. */
    path: string;
    method: string;
    namespace: string;
    /** Broad area, for grouping the sweep into readable tests. */
    area: 'hrm' | 'crm' | 'accounting' | 'pro' | 'core';
    /** True when the path carries an id this suite invented. */
    synthetic: boolean;
}

/**
 * The id substituted into every parameterised route.
 *
 * Deliberately one that cannot exist. A permission callback runs BEFORE the
 * handler, so a properly guarded route answers 401/403 whether or not the
 * record is there — while an UNGUARDED one falls through to the handler and
 * answers 404, 400 or 200. That difference is the whole oracle: for an
 * unauthorised caller, anything other than 401/403 means the door opened.
 *
 * It also keeps the sweep safe. Every destructive method in the table either
 * carries an id (so it addresses a record that does not exist) or is a create;
 * there is no DELETE without an id — verified against the table, not assumed.
 */
export const ABSENT_ID = '999999';

/**
 * A word for the handful of non-numeric parameters.
 *
 * No hyphen, deliberately: those parameters are declared `[\w]+`, which does
 * not match one. A hyphenated value makes WordPress answer `rest_no_route`,
 * and a 404 from a path that was never a route reads exactly like a permission
 * hole — it cost a full triage pass before the cause was the test's own value.
 */
const ABSENT_SLUG = 'pwabsent';

function substitute(pattern: string): string {
    return pattern.replace(/\(\?P<\w+>([^)]*)\)/g, (_match, expression: string) =>
        /\\d|\[\\d/.test(expression) ? ABSENT_ID : ABSENT_SLUG
    );
}

function areaOf(pattern: string): RouteCall['area'] {
    if (pattern.startsWith('/erp_pro/')) return 'pro';
    if (pattern.includes('/accounting/')) return 'accounting';
    if (pattern.includes('/hrm/')) return 'hrm';
    if (pattern.includes('/crm/')) return 'crm';

    return 'core';
}

/** Every route × every method it registers, ready to call. */
export function routeCalls(): RouteCall[] {
    const table = routes as Record<string, { namespace: string; methods: string[] }>;
    const calls: RouteCall[] = [];

    for (const [pattern, meta] of Object.entries(table)) {
        for (const method of meta.methods) {
            calls.push({
                pattern,
                path: substitute(pattern),
                method,
                namespace: meta.namespace,
                area: areaOf(pattern),
                synthetic: pattern.includes('(?P<'),
            });
        }
    }

    return calls;
}

/** Only the ERP-owned routes: WordPress's own are somebody else's contract. */
export function erpRouteCalls(): RouteCall[] {
    return routeCalls().filter((call) => call.namespace.startsWith('erp'));
}
