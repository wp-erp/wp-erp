# Tier 1 — hrm-attendance

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**2 cases** (1 derived from the harness, 1 hand-written business flows).

## Business flows

#### HRM_ATTENDANCE-F1-001 — Shift assignment then attendance entry appears in the report

- **Surface:** `admin.php?page=erp-hr&section=attendance`
- **Tags:** @tier1 @hrm-attendance @pro @flow
- **Steps:**
  1. Create a shift.
  2. Assign an employee to it (single, then via Assign Bulk Shift).
  3. Record an attendance entry for that employee.
  4. Open the date-based attendance report.
- **Expected:** The entry appears against the right employee and date, and the report totals match the entries recorded.
- **Oracle:** Report figures vs the attendance rows.

## Screen & field coverage

#### HRM_ATTENDANCE-T1-001 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=attendance`
- **Tags:** @tier1 @hrm-attendance @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=attendance`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "More", "Go to", "Filter".
  4. Confirm the list columns render: Date, Attended, Absent, Presence, Actions.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.
