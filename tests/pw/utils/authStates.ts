/**
 * One saved storageState per actor. `_auth.setup.ts` writes them; specs read
 * them through `test.use({ storageState })` or `browser.newContext()`.
 */
const dir = 'playwright/.auth';

export const ADMIN_STATE = `${dir}/admin.json`;
export const HR_MANAGER_STATE = `${dir}/hrManager.json`;
export const CRM_MANAGER_STATE = `${dir}/crmManager.json`;
export const CRM_AGENT_STATE = `${dir}/crmAgent.json`;
export const CRM_AGENT_2_STATE = `${dir}/crmAgent2.json`;
export const ACCOUNT_MANAGER_STATE = `${dir}/accountManager.json`;
export const RECRUITER_STATE = `${dir}/recruiter.json`;
export const EMPLOYEE_STATE = `${dir}/employee.json`;

/** Actor definitions used by the auth setup and by authorization specs. */
export const actors = {
    admin: { envUser: 'ADMIN', envPass: 'ADMIN_PASSWORD', role: 'administrator', state: ADMIN_STATE },
    hrManager: { envUser: 'HR_MANAGER', envPass: 'USER_PASSWORD', role: 'erp_hr_manager', state: HR_MANAGER_STATE },
    crmManager: { envUser: 'CRM_MANAGER', envPass: 'USER_PASSWORD', role: 'erp_crm_manager', state: CRM_MANAGER_STATE },
    crmAgent: { envUser: 'CRM_AGENT', envPass: 'USER_PASSWORD', role: 'erp_crm_agent', state: CRM_AGENT_STATE },
    /**
     * A SECOND agent, so agent-vs-agent visibility can be tested at all. One
     * agent can only answer "can I see my own data"; the question that matters
     * — can an agent reach another agent's contacts and deals — needs two.
     */
    crmAgent2: { envUser: 'CRM_AGENT_2', envPass: 'USER_PASSWORD', role: 'erp_crm_agent', state: CRM_AGENT_2_STATE },
    accountManager: { envUser: 'ACCOUNT_MANAGER', envPass: 'USER_PASSWORD', role: 'erp_ac_manager', state: ACCOUNT_MANAGER_STATE },
    recruiter: { envUser: 'RECRUITER', envPass: 'USER_PASSWORD', role: 'erp_recruiter', state: RECRUITER_STATE },
    employee: { envUser: 'EMPLOYEE', envPass: 'USER_PASSWORD', role: 'employee', state: EMPLOYEE_STATE },
} as const;

export type ActorKey = keyof typeof actors;
