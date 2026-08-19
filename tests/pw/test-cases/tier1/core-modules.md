# Tier 1 — core-modules

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**4 cases** (3 derived from the harness, 1 hand-written business flows).

## Business flows

#### CORE_MODULES-F1-001 — A Pro module can be deactivated and reactivated

- **Surface:** `admin.php?page=erp-extensions`
- **Tags:** @tier1 @core-modules @pro @flow
- **Steps:**
  1. Deactivate a Pro module (e.g. `attendance`) from the Modules screen.
  2. Confirm its admin screens disappear from the ERP navigation.
  3. Reactivate it.
  4. Confirm the screens return and its data is intact.
- **Expected:** Navigation follows module state in both directions and no data is lost on the round trip.
- **Oracle:** REST `erp_pro/v1/admin/modules` active flags + the rendered ERP nav + the module's row counts before/after.

## Screen & field coverage

#### CORE_MODULES-T1-001 — Modules & Extensions loads and renders its controls

- **Surface:** `admin.php?page=erp-extensions`
- **Tags:** @tier1 @core-modules @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-extensions`.
  3. Confirm the action buttons render: "All", "Purchased", "HRM", "CRM", "Accounting", "Active".
- **Expected:** The screen returns 200, renders its heading "Modules & Extensions", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_MODULES-T1-002 — Save `admin.php?page=erp-extensions` with every field completed

- **Surface:** `admin.php?page=erp-extensions`
- **Tags:** @tier1 @core-modules @crud
- **Steps:**
  1. Open `admin.php?page=erp-extensions`.
  2. Fill all 1 fields with valid data.
  3. Submit with "All".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CORE_MODULES-T1-003 — `admin.php?page=erp-extensions` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-extensions`
- **Tags:** @tier1 @core-modules @labels
- **Steps:**
  1. Open `admin.php?page=erp-extensions`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `select_all` ("Select All") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.
