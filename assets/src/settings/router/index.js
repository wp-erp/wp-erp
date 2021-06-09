import Vue from 'vue';
import Router from 'vue-router';
import GeneralSettings from 'settings/components/general/GeneralSettings.vue';
import HRWorkDays from 'settings/components/hr/workdays/HRWorkDays.vue';
import HRLeave from 'settings/components/hr/leave/HRLeave.vue';
import HRLeaveYears from 'settings/components/hr/leave-years/HRLeaveYears.vue';
import HRMiscellaneous from 'settings/components/hr/miscellaneous/HRMiscellaneous.vue';
import HRFrontend from 'settings/components/hr/hr-frontend/HRFrontend.vue';
import HRRecruitment from 'settings/components/hr/recruitment/HRRecruitment.vue';
import HrPayment from 'settings/components/hr/payroll/HrPayment.vue';
import HrPayItem from 'settings/components/hr/payroll/HrPayItem.vue';
import HRAttendance from 'settings/components/hr/attendance/HRAttendance.vue';

Vue.use(Router);

export default new Router({
    linkActiveClass: 'router-link-active',
    routes: settings.hooks.applyFilters('erp_settings_admin_routes', [
        {
            path: '/',
            component: GeneralSettings,
            children: [
                {
                    path: 'general',
                    name: 'GeneralSettings',
                    component: GeneralSettings,
                    alias: '/'
                }
            ]
        },
        {
            path: '/erp-hr',
            name: 'HR',
            component: {
                render(c) {
                    return c('router-view');
                }
            },
            children: [
                {
                    path: 'workdays',
                    name: 'HRWorkDays',
                    component: HRWorkDays,
                    alias: '/erp-hr'
                },
                {
                    path: 'leave',
                    name: 'HRLeave',
                    component: HRLeave
                },
                {
                    path: 'financial',
                    name: 'HRLeaveYears',
                    component: HRLeaveYears
                },
                {
                    path: 'miscellaneous',
                    name: 'HRMiscellaneous',
                    component: HRMiscellaneous
                },
                {
                    path: 'hr_frontend',
                    name: 'HRFrontend',
                    component: HRFrontend
                },
                {
                    path: 'recruitment',
                    name: 'HRRecruitment',
                    component: HRRecruitment
                },
                {
                    path: 'payroll',
                    name: 'HrPayroll',
                    component: {
                        render(c) {
                            return c('router-view');
                        }
                    },
                    children: [
                        {
                            path: 'payment',
                            name: 'HrPayment',
                            component: HrPayment,
                            alias: '/'
                        },
                        {
                            path: 'payitem',
                            name: 'HrPayItem',
                            component: HrPayItem,
                            alias: 'payitem'
                        },
                    ]
                },
                {
                    path: 'attendance',
                    name: 'HRAttendance',
                    component: HRAttendance
                },
            ]
        }
    ])
});
