# Tier 3 — core-modules

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**1 cases** (1 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### CORE_MODULES-T3-001 — `admin.php?page=erp-extensions` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-extensions`
- **Tags:** @tier3 @core-modules @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-extensions` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
