# Tier 3 — pro-workflow

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**2 cases** (2 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### PRO_WORKFLOW-T3-001 — `admin.php?page=erp-workflow` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-workflow`
- **Tags:** @tier3 @pro-workflow @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-workflow` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### PRO_WORKFLOW-T3-002 — `admin.php?page=erp-workflow-new` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-workflow-new`
- **Tags:** @tier3 @pro-workflow @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-workflow-new` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
