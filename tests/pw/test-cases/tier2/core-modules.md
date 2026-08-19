# Tier 2 — core-modules

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**2 cases** (2 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### CORE_MODULES-T2-001 — `select_all` ("Select All") accepts its edge values

- **Surface:** `admin.php?page=erp-extensions`
- **Tags:** @tier2 @core-modules @edge
- **Steps:**
  1. Open `admin.php?page=erp-extensions`.
  2. Save with `select_all` ("Select All") set to checked.
  3. Save with `select_all` ("Select All") set to unchecked.
  4. Save with `select_all` ("Select All") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_MODULES-T2-002 — `admin.php?page=erp-extensions` saves with only its required fields

- **Surface:** `admin.php?page=erp-extensions`
- **Tags:** @tier2 @core-modules @edge
- **Steps:**
  1. Open `admin.php?page=erp-extensions`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
