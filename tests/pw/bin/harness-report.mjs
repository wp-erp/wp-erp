#!/usr/bin/env node
/**
 * Turns the raw harness JSON captured from the running site into per-area
 * Markdown that a human (and the test-case files) can actually read.
 *
 * Input : harness/*.json + harness/live/*.json  (captured via Playwright MCP)
 * Output: harness/report/<area>.md + harness/report/INDEX.md
 *
 * It invents nothing: every field, label, option and button below was read off
 * the running site. A screen that could not be captured is listed as such.
 */
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const H = (p) => resolve(root, 'harness', p);
const read = (p) => (existsSync(H(p)) ? JSON.parse(readFileSync(H(p), 'utf8')) : null);

const pages = read('pages-server-rendered.json') ?? [];
const forms = read('forms.json') ?? {};
const acct = read('live/accounting-forms.json') ?? [];
const settings = read('live/settings-forms.json') ?? [];
const vue = read('live/vue-pages.json') ?? [];
const crmContact = read('live/crm-contact.json');
const company = read('live/company.json');
const modules = read('modules.json');
const routes = read('routes.json') ?? {};
const tables = read('db-tables.json') ?? {};

mkdirSync(H('report'), { recursive: true });

const esc = (s) => String(s ?? '').replace(/\|/g, '\\|').replace(/\n/g, ' ').trim();

function fieldTable(fields = []) {
    if (!fields.length) return '_No form fields on this screen._\n';
    const lines = ['| Label | Field name | id | Type | Required | Placeholder | Options |', '|---|---|---|---|---|---|---|'];
    for (const f of fields) {
        const opts = f.options?.length ? f.options.slice(0, 12).map((o) => `${o.label || '(blank)'}=${o.value}`).join(', ') + (f.options.length > 12 ? ` … +${f.options.length - 12}` : '') : '';
        lines.push(`| ${esc(f.label) || '—'} | \`${esc(f.name) || '—'}\` | \`${esc(f.id) || '—'}\` | ${esc(f.type)} | ${f.required ? 'yes' : ''} | ${esc(f.placeholder)} | ${esc(opts)} |`);
    }
    return lines.join('\n') + '\n';
}

function screenSection(title, s) {
    const out = [`### ${title}`, ''];
    if (s.url || s.route) out.push(`- **URL:** \`${s.url || s.route}\``);
    if (s.heading) out.push(`- **Heading:** ${esc(s.heading)}`);
    if (s.buttons?.length) out.push(`- **Buttons:** ${s.buttons.map((b) => `\`${esc(b)}\``).join(', ')}`);
    if (s.tabs?.length) out.push(`- **Tabs:** ${s.tabs.map(esc).join(' · ')}`);
    for (const t of s.tables ?? []) if (t.columns?.length) out.push(`- **List columns** (\`${esc(t.cls || t.classes)}\`): ${t.columns.map(esc).join(' · ')}`);
    if (s.empty) out.push(`- **Empty state:** "${esc(s.empty)}"`);
    out.push('', fieldTable(s.fields));
    return out.join('\n');
}

const areas = {
    'core-company-tools-license': pages.filter((p) => /erp-company|erp-tools|erp-license|erp-extensions|page=erp$|custom-field-builder|erp-workflow/.test(p.url)),
    hrm: pages.filter((p) => /page=erp-hr/.test(p.url)),
    crm: pages.filter((p) => /page=erp-crm/.test(p.url)),
    'hrm-training-recruitment-cpt': pages.filter((p) => /post_type=erp_hr/.test(p.url)),
};

const written = [];

for (const [area, list] of Object.entries(areas)) {
    if (!list.length) continue;
    const md = [`# Harness — ${area}`, '', `Captured from the running site at \`localhost:8888\` (wp-erp 1.17.8 + erp-pro 1.7.0).`, '', `${list.length} screens.`, ''];
    for (const s of list) md.push(screenSection(s.url, s), '');
    writeFileSync(H(`report/${area}.md`), md.join('\n'));
    written.push([`${area}.md`, `${list.length} screens`]);
}

// Accounting SPA
if (acct.length) {
    const md = ['# Harness — accounting (Vue SPA)', '', 'Hash-routed app at `admin.php?page=erp-accounting`. Routes walked in-page.', '', `${acct.length} routes.`, ''];
    for (const s of acct) md.push(screenSection(s.route, { ...s, url: `admin.php?page=erp-accounting${s.route}` }), '');
    writeFileSync(H('report/accounting.md'), md.join('\n'));
    written.push(['accounting.md', `${acct.length} routes`]);
}

// Settings SPA
if (settings.length) {
    const md = ['# Harness — settings (Vue SPA)', '', 'Hash-routed app at `admin.php?page=erp-settings`. Routes discovered by crawling the in-page links.', '', `${settings.length} routes.`, ''];
    for (const s of settings) md.push(screenSection(s.route, { ...s, url: `admin.php?page=erp-settings${s.route}`, tabs: undefined }), '');
    writeFileSync(H('report/settings.md'), md.join('\n'));
    written.push(['settings.md', `${settings.length} routes`]);
}

// Vue pages harvested through iframes + direct navigation
const vueAll = [...vue.filter((v) => !v.error), ...(crmContact ? [crmContact] : []), ...(company ? [company] : [])];
if (vueAll.length) {
    const md = ['# Harness — Vue screens (payroll, attendance, deals, CRM, company)', '', `${vueAll.length} screens.`, ''];
    for (const s of vueAll) md.push(screenSection(s.url, s), '');
    const failed = vue.filter((v) => v.error);
    if (failed.length) md.push('## Not captured', '', ...failed.map((f) => `- \`${f.url}\` — ${f.error}`), '');
    writeFileSync(H('report/vue-screens.md'), md.join('\n'));
    written.push(['vue-screens.md', `${vueAll.length} screens`]);
}

// Modal / template forms
const formIds = Object.keys(forms).sort();
if (formIds.length) {
    const md = ['# Harness — modal & template forms', '', 'ERP renders most create/edit forms from `<script type="text/html">` templates that only', 'reach the DOM when a modal opens. These were parsed straight out of the templates, so the', 'field list is complete rather than whatever happened to be open.', '', `${formIds.length} templates, ${formIds.reduce((a, k) => a + forms[k].fieldCount, 0)} fields.`, ''];
    for (const id of formIds) {
        const f = forms[id];
        if (!f.fieldCount) continue;
        md.push(`### \`${id}\``, '', `- **Seen on:** ${f.seenOn.slice(0, 3).map((u) => `\`${u}\``).join(', ')}${f.seenOn.length > 3 ? ` (+${f.seenOn.length - 3})` : ''}`);
        if (f.buttons?.length) md.push(`- **Buttons:** ${f.buttons.map((b) => `\`${esc(b)}\``).join(', ')}`);
        md.push('', fieldTable(f.fields), '');
    }
    writeFileSync(H('report/forms.md'), md.join('\n'));
    written.push(['forms.md', `${formIds.length} templates`]);
}

// Index
const routeCount = Object.keys(routes).length;
const tableCount = Object.keys(tables).length;
const idx = [
    '# WP ERP — site harness',
    '',
    'Everything below was **captured from the running site**, not written from memory:',
    'Playwright MCP drove `localhost:8888` as `admin`, and each screen was read for its',
    'headings, tabs, list columns, buttons, empty states and every form field (name, id,',
    'label, type, required flag, placeholder and select options).',
    '',
    '## What is in here',
    '',
    '| File | Contents |',
    '|---|---|',
    ...written.map(([f, c]) => `| [\`report/${f}\`](report/${f}) | ${c} |`),
    `| \`nav.json\` | ERP admin menu + section nav (${(read('nav.json') ?? []).length} entries) |`,
    `| \`routes.json\` | live REST route table (${routeCount} routes: \`erp/v1\`, \`erp_pro/v1/admin\`) |`,
    `| \`db-tables.json\` | ${tableCount} \`wp_erp*\` tables with their columns |`,
    `| \`modules.json\` | ${modules?.counts?.free ?? '?'} free modules, ${modules?.counts?.pro ?? '?'} pro (${modules?.counts?.proActive ?? '?'} active) + licence seats |`,
    `| \`forms.json\` | ${formIds.length} modal/template form definitions |`,
    '',
    '## Coverage of the harvest itself',
    '',
    `- Server-rendered admin screens read: **${pages.length}**`,
    `- Accounting SPA routes walked: **${acct.length}**`,
    `- Settings SPA routes walked: **${settings.length}**`,
    `- Vue screens read live: **${vueAll.length}**`,
    `- Modal/template forms parsed: **${formIds.length}** (${formIds.reduce((a, k) => a + forms[k].fieldCount, 0)} fields)`,
    '',
    '### Not yet harvested — stated, not hidden',
    '',
    '- Front-end surfaces: HR Frontend dashboard, the public recruitment apply form, CRM contact forms.',
    '- Screens that need seeded data to render their real controls (an empty pay run, an empty',
    '  invoice list) were captured in their empty state only.',
    '- The 7 externally-authenticated integrations (Salesforce, HubSpot, Mailchimp, Zendesk,',
    '  HelpScout, Gravity Forms, Awesome Support) were captured at their settings screen only —',
    '  their connected flows need sandbox credentials.',
    '',
];
writeFileSync(H('report/INDEX.md'), idx.join('\n'));

console.log(`harness report written: ${written.length + 1} files`);
for (const [f, c] of written) console.log(`  report/${f}  — ${c}`);
