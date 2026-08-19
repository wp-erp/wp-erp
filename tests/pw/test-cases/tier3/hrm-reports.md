# Tier 3 — hrm-reports

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**7 cases** (7 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_REPORTS-T3-001 — `admin.php?page=erp-hr&section=report` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=report`
- **Tags:** @tier3 @hrm-reports @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=report` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_REPORTS-T3-002 — `admin.php?page=erp-hr&section=report&sub-section=report&type=age-profile` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=age-profile`
- **Tags:** @tier3 @hrm-reports @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=report&sub-section=report&type=age-profile` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_REPORTS-T3-003 — `admin.php?page=erp-hr&section=report&sub-section=report&type=salary-history` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=salary-history`
- **Tags:** @tier3 @hrm-reports @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=report&sub-section=report&type=salary-history` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_REPORTS-T3-004 — `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Tags:** @tier3 @hrm-reports @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_REPORTS-T3-005 — `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier3 @hrm-reports @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_REPORTS-T3-006 — `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`
- **Tags:** @tier3 @hrm-reports @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_REPORTS-T3-007 — `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier3 @hrm-reports @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
