#!/usr/bin/env node
/**
 * Generates the tier-by-tier test-case files from the captured harness.
 *
 * Why generate rather than hand-write: the harness holds 900+ real fields across
 * ~150 screens. Hand-writing a case per field invites both omissions and
 * invented field names. Every case below names a field/label/option that was
 * READ OFF THE RUNNING SITE, so a case can never reference something that does
 * not exist.
 *
 * What is NOT generated: business-flow cases (a leave request that consumes an
 * entitlement, an invoice that posts to the right ledger). Those are appended by
 * hand from `flows.mjs` — a generator cannot infer an oracle it was never told.
 *
 * Output: test-cases/tier{1,2,3}/<area>.md
 */
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { FLOWS } from './flows.mjs';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const H = (p) => resolve(root, 'harness', p);
const read = (p) => (existsSync(H(p)) ? JSON.parse(readFileSync(H(p), 'utf8')) : null);

const pages = read('pages-server-rendered.json') ?? [];
const forms = read('forms.json') ?? {};
const acct = read('live/accounting-forms.json') ?? [];
const settings = read('live/settings-forms.json') ?? [];
const vue = (read('live/vue-pages.json') ?? []).filter((v) => !v.error);
const crmContact = read('live/crm-contact.json');
const company = read('live/company.json');

const esc = (s) => String(s ?? '').replace(/\|/g, '\\|').replace(/\s+/g, ' ').trim();

/** Which area a screen belongs to. Drives one file per area per tier. */
function areaOf(url = '') {
    if (/page=erp-settings/.test(url)) return 'settings';
    if (/page=erp-accounting/.test(url)) return 'accounting';
    if (/page=erp-hr.*section=leave/.test(url)) return 'hrm-leave';
    if (/page=erp-hr.*section=payroll/.test(url)) return 'hrm-payroll';
    if (/page=erp-hr.*section=attendance/.test(url)) return 'hrm-attendance';
    if (/page=erp-hr.*section=asset/.test(url)) return 'hrm-assets';
    if (/page=erp-hr.*section=recruitment/.test(url)) return 'hrm-recruitment';
    if (/page=erp-hr.*section=documents/.test(url)) return 'hrm-documents';
    if (/page=erp-hr.*section=report/.test(url)) return 'hrm-reports';
    if (/page=erp-hr/.test(url)) return 'hrm-people';
    if (/post_type=erp_hr_training/.test(url)) return 'hrm-training';
    if (/post_type=erp_hr_questionnaire/.test(url)) return 'hrm-recruitment';
    if (/page=erp-crm/.test(url)) return 'crm';
    if (/page=erp-license/.test(url)) return 'core-license';
    if (/page=erp-tools/.test(url)) return 'core-tools';
    if (/page=erp-company/.test(url)) return 'core-company';
    if (/page=erp-extensions/.test(url)) return 'core-modules';
    if (/page=erp-workflow/.test(url)) return 'pro-workflow';
    if (/custom-field-builder/.test(url)) return 'pro-custom-fields';
    return 'core';
}

/** Where a template form's fields belong. */
function areaOfTemplate(id = '', seenOn = []) {
    if (/crm/.test(id)) return 'crm';
    if (/asset|allotment/.test(id)) return 'hrm-assets';
    if (/leave|holiday/.test(id)) return 'hrm-leave';
    if (/doc-/.test(id)) return 'hrm-documents';
    if (/training/.test(id)) return 'hrm-training';
    if (/employee|employment|dept|desig|address/.test(id)) return 'hrm-people';
    return areaOf(seenOn[0] ?? '');
}

// ---------------------------------------------------------------------------
// Collect every screen and every form into areas
// ---------------------------------------------------------------------------
const areas = new Map();
const area = (k) => {
    if (!areas.has(k)) areas.set(k, { screens: [], formDefs: [] });
    return areas.get(k);
};

for (const p of pages) area(areaOf(p.url)).screens.push({ ...p, kind: 'admin' });
for (const s of acct) area('accounting').screens.push({ ...s, url: `admin.php?page=erp-accounting${s.route}`, kind: 'spa' });
for (const s of settings) area('settings').screens.push({ ...s, url: `admin.php?page=erp-settings${s.route}`, kind: 'spa' });
for (const s of vue) area(areaOf(s.url)).screens.push({ ...s, kind: 'vue' });
if (crmContact) area('crm').screens.push({ ...crmContact, kind: 'vue' });
if (company) area('core-company').screens.push({ ...company, kind: 'vue' });
for (const [id, f] of Object.entries(forms)) if (f.fieldCount) area(areaOfTemplate(id, f.seenOn)).formDefs.push({ id, ...f });

// ---------------------------------------------------------------------------
// Case builders
// ---------------------------------------------------------------------------
const counters = {};
function id(areaKey, tier) {
    const k = `${areaKey}|${tier}`;
    counters[k] = (counters[k] ?? 0) + 1;
    return `${areaKey.toUpperCase().replace(/-/g, '_')}-T${tier}-${String(counters[k]).padStart(3, '0')}`;
}

function caseBlock(c) {
    return [
        `#### ${c.id} — ${c.title}`,
        '',
        `- **Surface:** \`${c.surface}\``,
        `- **Tags:** ${c.tags.join(' ')}`,
        c.pre ? `- **Preconditions:** ${c.pre}` : null,
        `- **Steps:**`,
        ...c.steps.map((s, i) => `  ${i + 1}. ${s}`),
        `- **Expected:** ${c.expected}`,
        `- **Oracle:** ${c.oracle}`,
        '',
    ].filter((x) => x !== null).join('\n');
}

/** Fields worth asserting on. Drops WP chrome (screen options, bulk action, paging). */
const NOISE_NAME = /^(wp_screen_options|screen-options|action2?$|paged|s$|_wpnonce|_wp_http_referer|bulk_action|employee_id\[\]|cb-select)/;
const NOISE_ID = /^(bulk-action-selector|current-page-selector|cb-select-all|screen-options|doaction|search-submit)/;

function usable(f) {
    if (!f) return false;
    if (NOISE_NAME.test(f.name || '')) return false;
    if (NOISE_ID.test(f.id || '')) return false;
    if (!f.name && !f.id) return false;
    if (/-hide$/.test(f.name || '')) return false;
    return true;
}

const fieldRef = (f) => `\`${f.name || f.id}\`${f.label ? ` ("${esc(f.label)}")` : ''}`;

/** Tier 2 edge probes chosen by field type — each is a real, checkable input. */
function edgeProbes(f) {
    const t = (f.type || '').toLowerCase();
    if (t === 'email') return ['an address with a plus tag (`a+b@example.test`)', 'an address at the 254-character RFC limit', 'unicode in the local part'];
    if (t === 'number') return ['0', 'a negative value', 'a decimal where an integer is expected', 'a value far above any sane maximum (`999999999`)'];
    if (t === 'url') return ['a scheme-less host (`example.test`)', 'an `https://` URL with a query string and a fragment'];
    if (t === 'date' || /date/i.test(f.name || '') || /date/i.test(f.id || '')) return ['today', 'a leap day (29 Feb)', 'a date before the company financial-year start', 'a far-future date'];
    if (t === 'select') return [`the first real option`, `the last option`, 'leaving the placeholder option selected'];
    if (t === 'checkbox') return ['checked', 'unchecked', 'toggled twice and saved'];
    if (t === 'textarea') return ['a 5,000-character body', 'text containing newlines and emoji'];
    return ['a single character', 'a 255-character value', 'a value with leading and trailing whitespace', 'a value containing `&`, `<`, `"` and an emoji'];
}

function buildArea(key, data) {
    const t1 = [];
    const t2 = [];
    const t3 = [];

    // ---- Tier 1: every screen loads, renders its controls, no PHP error ----
    for (const s of data.screens) {
        const cols = (s.tables ?? []).flatMap((t) => t.columns ?? []).filter(Boolean);
        t1.push({
            id: id(key, 1), title: `${esc(s.heading) || s.url} loads and renders its controls`,
            surface: s.url, tags: ['@tier1', `@${key}`, '@smoke'],
            steps: [
                `Sign in as an administrator.`,
                `Open \`${s.url}\`.`,
                s.buttons?.length ? `Confirm the action buttons render: ${s.buttons.slice(0, 6).map((b) => `"${esc(b)}"`).join(', ')}.` : `Confirm the screen body renders.`,
                cols.length ? `Confirm the list columns render: ${[...new Set(cols)].slice(0, 10).map(esc).join(', ')}.` : null,
            ].filter(Boolean),
            expected: `The screen returns 200, renders its heading${s.heading ? ` "${esc(s.heading)}"` : ''}, and shows the controls above.`,
            oracle: 'UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.',
        });

        if (s.empty) {
            t2.push({
                id: id(key, 2), title: `${esc(s.heading) || s.url} shows its empty state when there is no data`,
                surface: s.url, tags: ['@tier2', `@${key}`, '@empty-state'],
                pre: 'The underlying list has no rows (filter to a value that matches nothing).',
                steps: [`Open \`${s.url}\`.`, 'Apply a filter or search that matches no record.'],
                expected: `The empty-state message "${esc(s.empty)}" renders instead of an empty table body.`,
                oracle: 'UI — exact empty-state copy.',
            });
        }
    }

    // ---- Field-level cases from the real forms ----
    const formSources = [
        ...data.formDefs.map((f) => ({ label: `modal form \`${f.id}\``, surface: f.seenOn[0], fields: f.fields, buttons: f.buttons })),
        ...data.screens.filter((s) => (s.fields ?? []).some(usable)).map((s) => ({ label: `\`${s.url}\``, surface: s.url, fields: s.fields, buttons: s.buttons })),
    ];

    for (const src of formSources) {
        const fields = (src.fields ?? []).filter(usable);
        if (!fields.length) continue;

        const required = fields.filter((f) => f.required);
        const optional = fields.filter((f) => !f.required);

        // Tier 1 — one happy-path save per form, filling every field it has.
        t1.push({
            id: id(key, 1), title: `Save ${src.label} with every field completed`,
            surface: src.surface, tags: ['@tier1', `@${key}`, '@crud'],
            steps: [
                `Open ${src.label}.`,
                `Fill all ${fields.length} fields with valid data${required.length ? ` (required: ${required.map((f) => fieldRef(f)).join(', ')})` : ''}.`,
                src.buttons?.length ? `Submit with "${esc(src.buttons.find((b) => /save|create|add|submit|update/i.test(b)) ?? src.buttons[0])}".` : 'Submit the form.',
            ],
            expected: 'The record is created, a success notice renders, and the new row appears in the list.',
            oracle: 'UI success notice + list row, confirmed by the matching REST/DB row.',
        });

        // Tier 1 — label/visibility assertion for every field. This is the
        // "every field and label" requirement, made checkable.
        t1.push({
            id: id(key, 1), title: `${src.label} renders all ${fields.length} fields with their labels`,
            surface: src.surface, tags: ['@tier1', `@${key}`, '@labels'],
            steps: [`Open ${src.label}.`, 'For each field below, assert it is present and its label reads exactly as captured.'],
            expected: `All ${fields.length} fields render:\n${fields.map((f) => `      - ${fieldRef(f)} — type \`${esc(f.type)}\`${f.required ? ', **required**' : ''}${f.placeholder ? `, placeholder "${esc(f.placeholder)}"` : ''}`).join('\n')}`,
            oracle: 'UI — field presence, associated `<label>` text, input type and required flag.',
        });

        // Tier 1 — select option sets, verbatim.
        for (const f of fields.filter((x) => x.options?.length > 1)) {
            t1.push({
                id: id(key, 1), title: `${fieldRef(f)} offers its full option set`,
                surface: src.surface, tags: ['@tier1', `@${key}`, '@options'],
                steps: [`Open ${src.label}.`, `Read every option of ${fieldRef(f)}.`],
                expected: `The options are exactly: ${f.options.slice(0, 25).map((o) => `"${esc(o.label)}" (\`${esc(o.value)}\`)`).join(', ')}${f.options.length > 25 ? ` … and ${f.options.length - 25} more (see harness)` : ''}.`,
                oracle: 'UI — `<option>` label/value pairs.',
            });
        }

        // Tier 2 — per-field edge probes.
        for (const f of fields) {
            t2.push({
                id: id(key, 2), title: `${fieldRef(f)} accepts its edge values`,
                surface: src.surface, tags: ['@tier2', `@${key}`, '@edge'],
                steps: [`Open ${src.label}.`, ...edgeProbes(f).map((p) => `Save with ${fieldRef(f)} set to ${p}.`)],
                expected: 'Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.',
                oracle: 'UI echo after reload + the stored value in REST/DB.',
            });
        }

        // Tier 2 — optional fields really are optional.
        if (optional.length) {
            t2.push({
                id: id(key, 2), title: `${src.label} saves with only its required fields`,
                surface: src.surface, tags: ['@tier2', `@${key}`, '@edge'],
                steps: [`Open ${src.label}.`, required.length ? `Fill only: ${required.map((f) => fieldRef(f)).join(', ')}.` : 'Leave every optional field blank.', 'Submit.'],
                expected: `The record saves. The ${optional.length} optional fields store as empty/default and the detail view renders without an error.`,
                oracle: 'REST/DB row + detail-view render.',
            });
        }

        // Tier 3 — required-field validation, one case per required field.
        for (const f of required) {
            t3.push({
                id: id(key, 3), title: `${fieldRef(f)} is enforced as required`,
                surface: src.surface, tags: ['@tier3', `@${key}`, '@validation'],
                steps: [`Open ${src.label}.`, `Fill every field EXCEPT ${fieldRef(f)}.`, 'Submit.'],
                expected: 'Submission is blocked with a message identifying that field. No record is created.',
                oracle: 'UI validation message + REST/DB row count unchanged.',
            });
        }

        // Tier 3 — injection / type abuse across text inputs.
        const textish = fields.filter((f) => ['text', 'textarea', 'email', 'url', 'search'].includes((f.type || '').toLowerCase()));
        if (textish.length) {
            t3.push({
                id: id(key, 3), title: `${src.label} neutralises script and HTML in its text fields`,
                surface: src.surface, tags: ['@tier3', `@${key}`, '@security'],
                steps: [`Open ${src.label}.`, `Set each of ${textish.length} text fields to \`<script>alert(1)</script>\` and to \`"><img src=x onerror=alert(1)>\`.`, 'Save, then open the list and the detail view.'],
                expected: 'The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.',
                oracle: 'UI — rendered text is escaped; a page dialog would fail the test.',
            });
        }

        // Tier 3 — malformed values per typed field.
        for (const f of fields.filter((x) => ['email', 'url', 'number', 'date'].includes((x.type || '').toLowerCase()))) {
            t3.push({
                id: id(key, 3), title: `${fieldRef(f)} rejects a malformed value`,
                surface: src.surface, tags: ['@tier3', `@${key}`, '@validation'],
                steps: [`Open ${src.label}.`, `Set ${fieldRef(f)} to a value of the wrong shape (e.g. \`not-an-${esc(f.type)}\`).`, 'Submit.'],
                expected: 'The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.',
                oracle: 'UI message + REST status code + DB row count.',
            });
        }
    }

    // ---- Tier 3 — authorization, per screen, per role ----
    const ROLES = ['erp_hr_manager', 'erp_crm_manager', 'erp_crm_agent', 'erp_ac_manager', 'erp_recruiter', 'employee', 'a logged-out visitor'];
    for (const s of data.screens.slice(0, 12)) {
        t3.push({
            id: id(key, 3), title: `\`${s.url}\` is closed to roles that do not own it`,
            surface: s.url, tags: ['@tier3', `@${key}`, '@authz'],
            steps: [`For each of ${ROLES.join(', ')}, open \`${s.url}\` directly by URL.`, 'Record the outcome for each role.'],
            expected: 'A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.',
            oracle: 'HTTP status + rendered body per role; capability checked against the role definition.',
        });
    }

    return { t1, t2, t3 };
}

// ---------------------------------------------------------------------------
// Emit
// ---------------------------------------------------------------------------
for (const tier of [1, 2, 3]) mkdirSync(resolve(root, 'test-cases', `tier${tier}`), { recursive: true });

const TIER_INTENT = {
    1: 'Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.',
    2: 'Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.',
    3: 'Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.',
};

const summary = [];

for (const [key, data] of [...areas.entries()].sort()) {
    const built = buildArea(key, data);
    const flows = FLOWS[key] ?? {};

    for (const tier of [1, 2, 3]) {
        const generated = built[`t${tier}`];
        const handWritten = flows[`t${tier}`] ?? [];
        if (!generated.length && !handWritten.length) continue;

        const md = [
            `# Tier ${tier} — ${key}`,
            '',
            TIER_INTENT[tier],
            '',
            `Every field, label and option named below was captured from the running site`,
            `(wp-erp 1.17.8 + erp-pro 1.7.0 at \`localhost:8888\`) — see \`harness/report/\`.`,
            '',
            `**${generated.length + handWritten.length} cases** (${generated.length} derived from the harness, ${handWritten.length} hand-written business flows).`,
            '',
        ];

        if (handWritten.length) {
            md.push('## Business flows', '', ...handWritten.map(caseBlock));
        }
        if (generated.length) {
            md.push('## Screen & field coverage', '', ...generated.map(caseBlock));
        }

        writeFileSync(resolve(root, 'test-cases', `tier${tier}`, `${key}.md`), md.join('\n'));
    }

    summary.push({
        area: key,
        screens: data.screens.length,
        forms: data.formDefs.length,
        t1: built.t1.length + (flows.t1?.length ?? 0),
        t2: built.t2.length + (flows.t2?.length ?? 0),
        t3: built.t3.length + (flows.t3?.length ?? 0),
    });
}

const total = summary.reduce((a, s) => ({ t1: a.t1 + s.t1, t2: a.t2 + s.t2, t3: a.t3 + s.t3 }), { t1: 0, t2: 0, t3: 0 });

console.log('area'.padEnd(22), 'scr', 'frm', ' T1', ' T2', ' T3');
for (const s of summary) console.log(s.area.padEnd(22), String(s.screens).padStart(3), String(s.forms).padStart(3), String(s.t1).padStart(3), String(s.t2).padStart(3), String(s.t3).padStart(3));
console.log('TOTAL'.padEnd(22), ''.padStart(3), ''.padStart(3), String(total.t1).padStart(3), String(total.t2).padStart(3), String(total.t3).padStart(3), ` = ${total.t1 + total.t2 + total.t3} cases`);
