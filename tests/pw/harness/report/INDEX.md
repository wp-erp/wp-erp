# WP ERP — site harness

Everything below was **captured from the running site**, not written from memory:
Playwright MCP drove `localhost:8888` as `admin`, and each screen was read for its
headings, tabs, list columns, buttons, empty states and every form field (name, id,
label, type, required flag, placeholder and select options).

## What is in here

| File | Contents |
|---|---|
| [`report/core-company-tools-license.md`](report/core-company-tools-license.md) | 12 screens |
| [`report/hrm.md`](report/hrm.md) | 33 screens |
| [`report/crm.md`](report/crm.md) | 10 screens |
| [`report/hrm-training-recruitment-cpt.md`](report/hrm-training-recruitment-cpt.md) | 4 screens |
| [`report/accounting.md`](report/accounting.md) | 31 routes |
| [`report/settings.md`](report/settings.md) | 44 routes |
| [`report/vue-screens.md`](report/vue-screens.md) | 18 screens |
| [`report/forms.md`](report/forms.md) | 46 templates |
| `nav.json` | ERP admin menu + section nav (79 entries) |
| `routes.json` | live REST route table (289 routes: `erp/v1`, `erp_pro/v1/admin`) |
| `db-tables.json` | 143 `wp_erp*` tables with their columns |
| `modules.json` | 3 free modules, 23 pro (22 active) + licence seats |
| `forms.json` | 46 modal/template form definitions |

## Coverage of the harvest itself

- Server-rendered admin screens read: **59**
- Accounting SPA routes walked: **31**
- Settings SPA routes walked: **44**
- Vue screens read live: **18**
- Modal/template forms parsed: **46** (257 fields)

### Not yet harvested — stated, not hidden

- Front-end surfaces: HR Frontend dashboard, the public recruitment apply form, CRM contact forms.
- Screens that need seeded data to render their real controls (an empty pay run, an empty
  invoice list) were captured in their empty state only.
- The 7 externally-authenticated integrations (Salesforce, HubSpot, Mailchimp, Zendesk,
  HelpScout, Gravity Forms, Awesome Support) were captured at their settings screen only —
  their connected flows need sandbox credentials.
