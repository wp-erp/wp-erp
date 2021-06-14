import Vue from 'vue';
import Router from 'vue-router';
import GeneralSettings from 'settings/components/general/GeneralSettings.vue';

// HRM Components
import HRWorkDays from 'settings/components/hr/workdays/HRWorkDays.vue';
import HRLeave from 'settings/components/hr/leave/HRLeave.vue';
import HRLeaveYears from 'settings/components/hr/leave-years/HRLeaveYears.vue';
import HRMiscellaneous from 'settings/components/hr/miscellaneous/HRMiscellaneous.vue';
import HRFrontend from 'settings/components/hr/hr-frontend/HRFrontend.vue';
import HRRecruitment from 'settings/components/hr/recruitment/HRRecruitment.vue';
import HRRemoteWork from 'settings/components/hr/remote-work/HRRemoteWork.vue';
import HrPayment from 'settings/components/hr/payroll/HrPayment.vue';
import HrPayItem from 'settings/components/hr/payroll/HrPayItem.vue';
import HRAttendance from 'settings/components/hr/attendance/HRAttendance.vue';

// AC Components
import AcCustomer from 'settings/components/act/customer/AcCustomer.vue';
import AcCurrency from 'settings/components/act/currency/AcCurrency.vue';
import AcPaymentGeneral from 'settings/components/act/payment/AcPaymentGeneral.vue';
import AcPaymentPaypal from 'settings/components/act/payment/AcPaymentPaypal.vue';
import AcPaymentStripe from 'settings/components/act/payment/AcPaymentStripe.vue';
import AcFinancialYears from 'settings/components/act/financial-year/AcFinancialYears.vue';

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
                    path: 'remote_work',
                    name: 'HRRemoteWork',
                    component: HRRemoteWork
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
        },

        {
            path: '/erp-ac',
            name: 'Ac',
            component: {
                render(c) {
                    return c('router-view');
                }
            },
            children: [
                {
                    path: 'customers',
                    name: 'AcCustomer',
                    component: AcCustomer,
                    alias: '/erp-ac'
                },
                {
                    path: 'currency_option',
                    name: 'AcCurrency',
                    component: AcCurrency
                },
                {
                    path: 'opening_balance',
                    name: 'AcFinancialYears',
                    component: AcFinancialYears
                },
                {
                    path: 'payment',
                    name: 'AcPayment',
                    component: {
                        render(c) {
                            return c('router-view');
                        }
                    },
                    children: [
                        {
                            path: 'general',
                            name: 'AcPaymentGeneral',
                            component: AcPaymentGeneral,
                            alias: '/'
                        },
                        {
                            path: 'paypal',
                            name: 'AcPaymentPaypal',
                            component: AcPaymentPaypal
                        },
                        {
                            path: 'stripe',
                            name: 'AcPaymentStripe',
                            component: AcPaymentStripe
                        },
                    ]
                },
            ]
        }
    ])
});
