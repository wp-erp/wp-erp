# Tier 1 — settings

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**111 cases** (110 derived from the harness, 1 hand-written business flows).

## Business flows

#### SETTINGS-F1-001 — Every settings section persists across a reload

- **Surface:** `admin.php?page=erp-settings`
- **Tags:** @tier1 @settings @flow
- **Steps:**
  1. For each of the 44 captured settings routes, change one field and click "Save Changes".
  2. Reload the route.
- **Expected:** Each changed value is still present after the reload and after a full page refresh.
- **Oracle:** The corresponding `erp_settings_*` option value.

## Screen & field coverage

#### SETTINGS-T1-001 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/general`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/general`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-002 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-003 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-crm`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-004 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-woocommerce`.
  3. Confirm the action buttons render: "Synchronize Orders".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-005 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-ac`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-ac`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-006 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-email`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-007 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-integration`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-integration`.
  3. Confirm the action buttons render: "Configure".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-008 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/workdays`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/workdays`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-009 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-010 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/financial`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/financial`.
  3. Confirm the action buttons render: "+ Add New", "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-011 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/remote_work`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/remote_work`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-012 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/miscellaneous`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/miscellaneous`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-013 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/hr_frontend`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/hr_frontend`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-014 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/recruitment`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/recruitment`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-015 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/payroll`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-016 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-017 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-018 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contact_forms`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-crm/contact_forms`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-019 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-020 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-crm/crm_life_stages`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-crm/crm_life_stages`.
  3. Confirm the action buttons render: "Add more", "Edit", "Delete", "Save", "Cancel".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-021 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-crm/erp_deals`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-crm/erp_deals`.
  3. Confirm the action buttons render: "Add new pipeline", "Add Stage", "Edit Pipeline", "Save", "Cancel".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-022 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`.
  3. Confirm the action buttons render: "Synchronize Orders".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-023 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-024 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/crm`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-woocommerce/crm`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-025 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/accounting`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-woocommerce/accounting`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-026 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`.
  3. Confirm the action buttons render: "Synchronize Orders".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-027 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-028 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-ac/customers`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-ac/customers`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-029 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-ac/currency_option`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-ac/currency_option`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-030 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-ac/opening_balance`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-ac/opening_balance`.
  3. Confirm the action buttons render: "+ Add New", "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-031 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-ac/payment`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-032 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-email/general`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-email/general`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-033 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-email/email_connect`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-email/email_connect`.
  3. Confirm the action buttons render: "Send Test Email", "Save Changes", "Test Connection".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-034 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-email/notification`.
  3. Confirm the action buttons render: "Configure".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-035 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-email/hrm_digest_email`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-email/hrm_digest_email`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-036 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-email/templates`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-email/templates`.
  3. Confirm the action buttons render: "Add New", "Bold", "Italic", "Strikethrough", "Link", "Heading".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-037 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll/payment`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/payroll/payment`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-038 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll/payitem`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-hr/payroll/payitem`.
  3. Confirm the action buttons render: "Add Pay Item", "Update", "Cancel".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-039 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-crm/erp_deals/pipelines`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-crm/erp_deals/pipelines`.
  3. Confirm the action buttons render: "Add new pipeline", "Add Stage", "Edit Pipeline", "Save", "Cancel".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-040 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-crm/erp_deals/activity_types`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-crm/erp_deals/activity_types`.
  3. Confirm the action buttons render: "Add Activity Type", "Edit", "Delete", "Save", "Cancel".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-041 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-crm/erp_deals/lost_reasons`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-crm/erp_deals/lost_reasons`.
  3. Confirm the action buttons render: "Add New", "Save", "Cancel".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-042 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/general`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-ac/payment/general`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-043 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/paypal`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-ac/payment/paypal`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-044 — Settings loads and renders its controls

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier1 @settings @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Settings", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### SETTINGS-T1-045 — Save `admin.php?page=erp-settings#/general` with every field completed

- **Surface:** `admin.php?page=erp-settings#/general`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/general`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-046 — `admin.php?page=erp-settings#/general` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/general`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/general`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `erp-gen_com_start` ("Company Start Date The date the company officially started.") — type `text`, placeholder "Select date"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-047 — Save `admin.php?page=erp-settings#/erp-crm` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Fill all 13 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-048 — `admin.php?page=erp-settings#/erp-crm` renders all 13 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 13 fields render:
      - `erp-undefined` ("Administrator") — type `checkbox`
      - `erp-undefined` ("Editor") — type `checkbox`
      - `erp-undefined` ("Author") — type `checkbox`
      - `erp-undefined` ("Contributor") — type `checkbox`
      - `erp-undefined` ("Subscriber") — type `checkbox`
      - `erp-undefined` ("HR Manager") — type `checkbox`
      - `erp-undefined` ("Employee") — type `checkbox`
      - `erp-undefined` ("CRM Manager") — type `checkbox`
      - `erp-undefined` ("CRM Agent") — type `checkbox`
      - `erp-undefined` ("Accounting Manager") — type `checkbox`
      - `erp-undefined` ("Customer") — type `checkbox`
      - `erp-undefined` ("Shop manager") — type `checkbox`
      - `erp-undefined` ("Recruiter") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-049 — Save `admin.php?page=erp-settings#/erp-woocommerce` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Synchronize Orders".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-050 — `admin.php?page=erp-settings#/erp-woocommerce` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `btn-rebuild` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-051 — Save `admin.php?page=erp-settings#/erp-ac` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-ac`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-052 — `admin.php?page=erp-settings#/erp-ac` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-ac`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `erp-undefined` ("Contact") — type `checkbox`
      - `erp-undefined` ("Company") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-053 — Save `admin.php?page=erp-settings#/erp-email` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email`.
  2. Fill all 4 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-054 — `admin.php?page=erp-settings#/erp-email` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") — type `input`
      - `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") — type `input`
      - `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") — type `input`
      - `erp-footer_text` ("Footer Text The text apears on each emails footer area.") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-055 — Save `admin.php?page=erp-settings#/erp-hr/leave` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Fill all 8 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-056 — `admin.php?page=erp-settings#/erp-hr/leave` renders all 8 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 8 fields render:
      - `erp-enable_extra_leave` ("Extra Unpaid Leave") — type `checkbox`
      - `erp-enable_auto_leave_policy_assignment_on_type_change` ("Auto Assign Leave Policy") — type `checkbox`
      - `erp-erp_pro_accrual_leave` ("Enable Accrual") — type `checkbox`
      - `erp-erp_pro_carry_encash_leave` ("Enable Carry / Encash") — type `checkbox`
      - `erp-erp_pro_half_leave` ("Enable Half-Day Request") — type `checkbox`
      - `erp-erp_pro_multilevel_approval` ("Enable Multilevel Approval") — type `checkbox`
      - `erp-erp_pro_seg_leave` ("Enable Segregation") — type `checkbox`
      - `erp-erp_pro_sandwich_leave` ("Enable Sandwich Rule") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-057 — Save `admin.php?page=erp-settings#/erp-hr/financial` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-hr/financial`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/financial`.
  2. Fill all 2 fields with valid data.
  3. Submit with "+ Add New".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-058 — `admin.php?page=erp-settings#/erp-hr/financial` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-hr/financial`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/financial`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `dp1787046901139` — type `text`, placeholder "Start date"
      - `dp1787046901140` — type `text`, placeholder "End date"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-059 — Save `admin.php?page=erp-settings#/erp-hr/remote_work` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-hr/remote_work`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/remote_work`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-060 — `admin.php?page=erp-settings#/erp-hr/remote_work` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-hr/remote_work`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/remote_work`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `erp-erp_hr_remote_work_enable` ("Enable Remote Work") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-061 — Save `admin.php?page=erp-settings#/erp-hr/miscellaneous` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-hr/miscellaneous`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/miscellaneous`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-062 — `admin.php?page=erp-settings#/erp-hr/miscellaneous` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-hr/miscellaneous`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/miscellaneous`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `erp-erp_hrm_remove_wp_user` ("Remove WP User") — type `checkbox`
      - `erp-erp_hrm_hide_pay_rate` ("Hide Pay Rate") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-063 — Save `admin.php?page=erp-settings#/erp-hr/hr_frontend` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-hr/hr_frontend`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/hr_frontend`.
  2. Fill all 3 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-064 — `admin.php?page=erp-settings#/erp-hr/hr_frontend` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-hr/hr_frontend`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/hr_frontend`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `erp-hr_frontend_slug` ("HR Dashboard Slug") — type `input`
      - `erp-hr_frontend_dashboard_title` ("HR Dashboard Title") — type `input`
      - `erp-hr_frontend_redirect` ("Redirect to frontend") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-065 — Save `admin.php?page=erp-settings#/erp-hr/recruitment` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-hr/recruitment`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/recruitment`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-066 — `admin.php?page=erp-settings#/erp-hr/recruitment` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-hr/recruitment`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/recruitment`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `erp-recruitment_api_url` ("Global Api") — type `input`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-067 — Save `admin.php?page=erp-settings#/erp-hr/payroll` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-068 — `admin.php?page=erp-settings#/erp-hr/payroll` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `erp_payroll_payment_method_settings` ("Select a method") — type `select`
      - `erp_payroll_payment_bank_settings` ("Select a bank") — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-069 — `erp_payroll_payment_method_settings` ("Select a method") offers its full option set

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll`
- **Tags:** @tier1 @settings @options
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll`.
  2. Read every option of `erp_payroll_payment_method_settings` ("Select a method").
- **Expected:** The options are exactly: "Cash" (`cash`), "Cheque" (`cheque`), "Bank" (`bank`).
- **Oracle:** UI — `<option>` label/value pairs.

#### SETTINGS-T1-070 — Save `admin.php?page=erp-settings#/erp-hr/attendance` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Fill all 9 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-071 — `admin.php?page=erp-settings#/erp-hr/attendance` renders all 9 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 9 fields render:
      - `erp-grace_before_checkin` ("Grace Before Checkin") — type `input`
      - `erp-grace_after_checkin` ("Grace After Checkin") — type `input`
      - `erp-erp_att_diff_threshhold` ("Threshhold between checkout & checkin") — type `input`
      - `erp-grace_before_checkout` ("Grace Before Checkout") — type `input`
      - `erp-grace_after_checkout` ("Grace After Checkout") — type `input`
      - `erp-enable_self_att` ("Self Attendance") — type `checkbox`
      - `erp-erp_at_enable_ip_restriction` ("IP Restriction") — type `checkbox`
      - `erp-erp_at_whitelisted_ips` ("Whitelisted IP\'s") — type `textarea`
      - `erp-attendance_reminder` ("Attendance Reminder") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-072 — Save `admin.php?page=erp-settings#/erp-crm/contacts` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Fill all 13 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-073 — `admin.php?page=erp-settings#/erp-crm/contacts` renders all 13 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 13 fields render:
      - `erp-undefined` ("Administrator") — type `checkbox`
      - `erp-undefined` ("Editor") — type `checkbox`
      - `erp-undefined` ("Author") — type `checkbox`
      - `erp-undefined` ("Contributor") — type `checkbox`
      - `erp-undefined` ("Subscriber") — type `checkbox`
      - `erp-undefined` ("HR Manager") — type `checkbox`
      - `erp-undefined` ("Employee") — type `checkbox`
      - `erp-undefined` ("CRM Manager") — type `checkbox`
      - `erp-undefined` ("CRM Agent") — type `checkbox`
      - `erp-undefined` ("Accounting Manager") — type `checkbox`
      - `erp-undefined` ("Customer") — type `checkbox`
      - `erp-undefined` ("Shop manager") — type `checkbox`
      - `erp-undefined` ("Recruiter") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-074 — Save `admin.php?page=erp-settings#/erp-crm/subscription` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Fill all 9 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-075 — `admin.php?page=erp-settings#/erp-crm/subscription` renders all 9 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 9 fields render:
      - `erp-is_enabled` ("Enable signup confirmation If you enable this option, your subscribers will first receive a confirmation email after they subscribe. Once they confirm their subscription (via this email), they will be marked as 'subscribed'.") — type `checkbox`
      - `erp-email_subject` ("Email subject") — type `input`
      - `erp-email_content` ("Email content") — type `textarea`
      - `erp-confirm_page_title` ("Confirmation Page") — type `input`
      - `erp-confirm_page_content` — type `textarea`
      - `erp-unsubs_page_title` ("Unsubscribe Page") — type `input`
      - `erp-unsubs_page_content` — type `textarea`
      - `erp-edit_sub_page_title` ("Edit Subscription Page") — type `input`
      - `erp-edit_sub_page_content` — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-076 — Save `admin.php?page=erp-settings#/erp-woocommerce/wc_sync` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Synchronize Orders".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-077 — `admin.php?page=erp-settings#/erp-woocommerce/wc_sync` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `btn-rebuild` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-078 — Save `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-079 — `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `erp-show_wc_signup_option` ("Show signup on checkout") — type `checkbox`
      - `erp-wc_signup_option_label` ("Signup option label") — type `input`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-080 — Save `admin.php?page=erp-settings#/erp-woocommerce/crm` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/crm`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/crm`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-081 — `admin.php?page=erp-settings#/erp-woocommerce/crm` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/crm`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/crm`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `switchRounded` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-082 — Save `admin.php?page=erp-settings#/erp-woocommerce/accounting` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/accounting`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/accounting`.
  2. Fill all 3 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-083 — `admin.php?page=erp-settings#/erp-woocommerce/accounting` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/accounting`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/accounting`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-084 — Save `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Synchronize Orders".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-085 — `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `btn-rebuild` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-086 — Save `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`.
  2. Fill all 2 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-087 — `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `switchRounded` — type `checkbox`
      - `btn-rebuild` — type `button`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-088 — Save `admin.php?page=erp-settings#/erp-ac/customers` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-ac/customers`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/customers`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-089 — `admin.php?page=erp-settings#/erp-ac/customers` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-ac/customers`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/customers`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `erp-undefined` ("Contact") — type `checkbox`
      - `erp-undefined` ("Company") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-090 — Save `admin.php?page=erp-settings#/erp-ac/currency_option` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-ac/currency_option`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/currency_option`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-091 — `admin.php?page=erp-settings#/erp-ac/currency_option` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-ac/currency_option`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/currency_option`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `erp-erp_ac_th_separator` ("Thousand Separator") — type `input`
      - `erp-erp_ac_de_separator` ("Decimal Separator") — type `input`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-092 — Save `admin.php?page=erp-settings#/erp-ac/opening_balance` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-ac/opening_balance`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/opening_balance`.
  2. Fill all 2 fields with valid data.
  3. Submit with "+ Add New".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-093 — `admin.php?page=erp-settings#/erp-ac/opening_balance` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-ac/opening_balance`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/opening_balance`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `dp1787046901141` — type `text`, placeholder "Start date"
      - `dp1787046901142` — type `text`, placeholder "End date"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-094 — Save `admin.php?page=erp-settings#/erp-email/general` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-email/general`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/general`.
  2. Fill all 4 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-095 — `admin.php?page=erp-settings#/erp-email/general` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-email/general`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/general`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") — type `input`
      - `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") — type `input`
      - `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") — type `input`
      - `erp-footer_text` ("Footer Text The text apears on each emails footer area.") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-096 — Save `admin.php?page=erp-settings#/erp-email/email_connect` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-email/email_connect`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/email_connect`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-097 — `admin.php?page=erp-settings#/erp-email/email_connect` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-email/email_connect`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/email_connect`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `switchRounded` — type `checkbox`
      - `erp_wpmail_test_email` ("Test Mail") — type `email`, placeholder "Email here"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-098 — Save `admin.php?page=erp-settings#/erp-email/notification` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Fill all 13 fields with valid data.
  3. Submit with "Configure".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-099 — `admin.php?page=erp-settings#/erp-email/notification` renders all 13 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 13 fields render:
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
      - `switchRounded` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-100 — Save `admin.php?page=erp-settings#/erp-email/hrm_digest_email` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-email/hrm_digest_email`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/hrm_digest_email`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-101 — `admin.php?page=erp-settings#/erp-email/hrm_digest_email` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-email/hrm_digest_email`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/hrm_digest_email`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `switchRounded` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-102 — Save `admin.php?page=erp-settings#/erp-email/templates` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-email/templates`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/templates`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Add New".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-103 — `admin.php?page=erp-settings#/erp-email/templates` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-email/templates`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/templates`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `href` — type `url`, placeholder "Enter a URL…"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-104 — Save `admin.php?page=erp-settings#/erp-hr/payroll/payment` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll/payment`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll/payment`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-105 — `admin.php?page=erp-settings#/erp-hr/payroll/payment` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll/payment`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll/payment`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `erp_payroll_payment_method_settings` ("Select a method") — type `select`
      - `erp_payroll_payment_bank_settings` ("Select a bank") — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-106 — `erp_payroll_payment_method_settings` ("Select a method") offers its full option set

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll/payment`
- **Tags:** @tier1 @settings @options
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll/payment`.
  2. Read every option of `erp_payroll_payment_method_settings` ("Select a method").
- **Expected:** The options are exactly: "Cash" (`cash`), "Cheque" (`cheque`), "Bank" (`bank`).
- **Oracle:** UI — `<option>` label/value pairs.

#### SETTINGS-T1-107 — Save `admin.php?page=erp-settings#/erp-ac/payment/paypal` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/paypal`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/paypal`.
  2. Fill all 5 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-108 — `admin.php?page=erp-settings#/erp-ac/payment/paypal` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/paypal`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/paypal`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `erp-erp_pg_paypal_enable_disable` ("Enable/Disable") — type `checkbox`
      - `erp-erp_pg_paypal_title` ("Title This is the title which user see on payment options") — type `input`
      - `erp-erp_pg_paypal_description` ("Description This is the description which user see on payment options") — type `input`
      - `erp-erp_pg_paypal_receiver_email` ("Paypal Email Please enter your PayPal email address") — type `input`
      - `erp-erp_pg_paypal_sandbox` ("Paypal Sandbox") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### SETTINGS-T1-109 — Save `admin.php?page=erp-settings#/erp-ac/payment/stripe` with every field completed

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier1 @settings @crud
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Fill all 8 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### SETTINGS-T1-110 — `admin.php?page=erp-settings#/erp-ac/payment/stripe` renders all 8 fields with their labels

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier1 @settings @labels
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 8 fields render:
      - `erp-erp_pg_stripe_enable_disable` ("Enable/Disable") — type `checkbox`
      - `erp-erp_pg_stripe_title` ("Title This is the title which user see on payment options") — type `input`
      - `erp-erp_pg_stripe_description` ("Description This is the description which user see on payment options") — type `input`
      - `erp-erp_pg_stripe_live_secret_key` ("Live Secret Key Enter your Stripe Live Secret Key") — type `input`
      - `erp-erp_pg_stripe_live_publishable_key` ("Live Publishable Key Enter your Stripe Live Publishable Key") — type `input`
      - `erp-erp_pg_stripe_enable_testmode` ("Test Mode") — type `checkbox`
      - `erp-erp_pg_stripe_test_secret_key` ("Test Secret Key Enter your Stripe Test Secret Key") — type `input`
      - `erp-erp_pg_stripe_test_publishable_key` ("Test Publishable Key Enter your Stripe Test Publishable Key") — type `input`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.
