# Tier 1 — core-tools

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**11 cases** (11 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### CORE_TOOLS-T1-001 — General Misc. Status Audit Log Danger Zone loads and renders its controls

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier1 @core-tools @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-tools`.
  3. Confirm the action buttons render: "Save Changes".
- **Expected:** The screen returns 200, renders its heading "General Misc. Status Audit Log Danger Zone", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_TOOLS-T1-002 — General Misc. Status Audit Log Danger Zone loads and renders its controls

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier1 @core-tools @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-tools&tab=misc`.
  3. Confirm the action buttons render: "Send Email".
- **Expected:** The screen returns 200, renders its heading "General Misc. Status Audit Log Danger Zone", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_TOOLS-T1-003 — General Misc. Status Audit Log Danger Zone loads and renders its controls

- **Surface:** `admin.php?page=erp-tools&tab=status`
- **Tags:** @tier1 @core-tools @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-tools&tab=status`.
  3. Confirm the action buttons render: "Copy for support".
  4. Confirm the list columns render: WP ERP, ERP Settings, WordPress environment, Server environment, Database, Post Type Counts, Security, Active plugins (6), Theme.
- **Expected:** The screen returns 200, renders its heading "General Misc. Status Audit Log Danger Zone", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_TOOLS-T1-004 — General Misc. Status Audit Log Danger Zone loads and renders its controls

- **Surface:** `admin.php?page=erp-tools&tab=audit-log`
- **Tags:** @tier1 @core-tools @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-tools&tab=audit-log`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "General Misc. Status Audit Log Danger Zone", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_TOOLS-T1-005 — General Misc. Status Audit Log Danger Zone loads and renders its controls

- **Surface:** `admin.php?page=erp-tools&tab=danger-zone`
- **Tags:** @tier1 @core-tools @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-tools&tab=danger-zone`.
  3. Confirm the action buttons render: "Resetting...", "Reset Now".
- **Expected:** The screen returns 200, renders its heading "General Misc. Status Audit Log Danger Zone", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_TOOLS-T1-006 — Save `admin.php?page=erp-tools` with every field completed

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier1 @core-tools @crud
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Fill all 25 fields with valid data.
  3. Submit with "Save Changes".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CORE_TOOLS-T1-007 — `admin.php?page=erp-tools` renders all 25 fields with their labels

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier1 @core-tools @labels
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 25 fields render:
      - `menu[]` ("Dashboard") — type `checkbox`
      - `menu[]` ("Posts") — type `checkbox`
      - `menu[]` ("Media") — type `checkbox`
      - `menu[]` ("Pages") — type `checkbox`
      - `menu[]` ("Comments") — type `checkbox`
      - `menu[]` ("Email Log") — type `checkbox`
      - `menu[]` ("Training") — type `checkbox`
      - `menu[]` ("WooCommerce") — type `checkbox`
      - `menu[]` ("Products") — type `checkbox`
      - `menu[]` ("Payments") — type `checkbox`
      - `menu[]` ("Analytics") — type `checkbox`
      - `menu[]` ("Marketing") — type `checkbox`
      - `menu[]` ("Appearance") — type `checkbox`
      - `menu[]` ("Plugins") — type `checkbox`
      - `menu[]` ("Users") — type `checkbox`
      - `menu[]` ("Tools") — type `checkbox`
      - `menu[]` ("Settings") — type `checkbox`
      - `admin_menu[]` ("WordPress Logo") — type `checkbox`
      - `admin_menu[]` ("Site Name") — type `checkbox`
      - `admin_menu[]` ("Updates") — type `checkbox`
      - `admin_menu[]` ("Comments") — type `checkbox`
      - `admin_menu[]` ("New Posts") — type `checkbox`
      - `admin_menu[]` ("New Transaction") — type `checkbox`
      - `admin_menu[]` ("WP ERP") — type `checkbox`
      - `erp_admin_menu` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CORE_TOOLS-T1-008 — Save `admin.php?page=erp-tools&tab=misc` with every field completed

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier1 @core-tools @crud
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=misc`.
  2. Fill all 4 fields with valid data.
  3. Submit with "Send Email".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CORE_TOOLS-T1-009 — `admin.php?page=erp-tools&tab=misc` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier1 @core-tools @labels
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=misc`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `to` ("To *") — type `email`, placeholder "recipient@domain.com"
      - `from` ("From") — type `text`
      - `body` ("Message") — type `textarea`, placeholder "Leave blank to send default texts"
      - `erp_send_test_email` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CORE_TOOLS-T1-010 — Save `admin.php?page=erp-tools&tab=danger-zone` with every field completed

- **Surface:** `admin.php?page=erp-tools&tab=danger-zone`
- **Tags:** @tier1 @core-tools @crud
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=danger-zone`.
  2. Fill all 1 fields with valid data (required: `erp_reset_confirmation`).
  3. Submit with "Resetting...".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CORE_TOOLS-T1-011 — `admin.php?page=erp-tools&tab=danger-zone` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-tools&tab=danger-zone`
- **Tags:** @tier1 @core-tools @labels
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=danger-zone`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `erp_reset_confirmation` — type `text`, **required**, placeholder "Type here"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.
