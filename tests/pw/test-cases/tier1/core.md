# Tier 1 — core

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**1 cases** (1 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### CORE-T1-001 — Overview What's New var HW_config = { selector: '#erp-headway-icon', trigger: '#erp-headway-btn', account: '7vbOM7' }; + A Book a Call Feedback Contact Support loads and renders its controls

- **Surface:** `admin.php?page=erp`
- **Tags:** @tier1 @core @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp`.
  3. Confirm the action buttons render: "What's New", "Subscribe".
- **Expected:** The screen returns 200, renders its heading "Overview What's New var HW_config = { selector: '#erp-headway-icon', trigger: '#erp-headway-btn', account: '7vbOM7' }; + A Book a Call Feedback Contact Support", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.
