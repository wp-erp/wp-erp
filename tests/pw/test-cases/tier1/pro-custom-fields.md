# Tier 1 — pro-custom-fields

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**2 cases** (2 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### PRO_CUSTOM_FIELDS-T1-001 — Custom Field Builder loads and renders its controls

- **Surface:** `admin.php?page=custom-field-builder`
- **Tags:** @tier1 @pro-custom-fields @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=custom-field-builder`.
  3. Confirm the action buttons render: "Add New Field", "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Custom Field Builder", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### PRO_CUSTOM_FIELDS-T1-002 — Custom Field Builder loads and renders its controls

- **Surface:** `admin.php?page=custom-field-builder`
- **Tags:** @tier1 @pro-custom-fields @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=custom-field-builder`.
  3. Confirm the action buttons render: "Add New Field", "Save Changes".
- **Expected:** The screen returns 200, renders its heading "Custom Field Builder", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.
