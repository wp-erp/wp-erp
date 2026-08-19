# Tier 1 — pro-workflow

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**2 cases** (2 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### PRO_WORKFLOW-T1-001 — Workflows Add New loads and renders its controls

- **Surface:** `admin.php?page=erp-workflow`
- **Tags:** @tier1 @pro-workflow @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-workflow`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Select All, Name Sort descending., Status Sort ascending., Module, Event, Total Runs, Created By, Date Sort ascending..
- **Expected:** The screen returns 200, renders its heading "Workflows Add New", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### PRO_WORKFLOW-T1-002 — Create Workflow loads and renders its controls

- **Surface:** `admin.php?page=erp-workflow-new`
- **Tags:** @tier1 @pro-workflow @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-workflow-new`.
  3. Confirm the action buttons render: "Save & Activate", "Save Only".
- **Expected:** The screen returns 200, renders its heading "Create Workflow", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.
