import Vue from 'vue';
import Router from 'vue-router';
import GeneralSettings from '../components/general/GeneralSettings.vue';

// HRM Components
import HRWorkDays from '../components/hr/workdays/HRWorkDays.vue';
import HRLeave from '../components/hr/leave/HRLeave.vue';
import HRLeaveYears from '../components/hr/leave-years/HRLeaveYears.vue';
import HRMiscellaneous from '../components/hr/miscellaneous/HRMiscellaneous.vue';

// Accounting Components
import AcCustomer from '../components/act/customer/AcCustomer.vue';
import AcCurrency from '../components/act/currency/AcCurrency.vue';
import AcFinancialYears from '../components/act/financial-year/AcFinancialYears.vue';

// CRM Components
import CrmContacts from '../components/crm/contacts/CrmContacts.vue';
import CrmContactForm from '../components/crm/contact-forms/CrmContactForm.vue';
import CrmSubscription from '../components/crm/subscription/CrmSubscription.vue';

// WooCommerce Components
import WooCommerce from '../components/woocommerce/WooCommerce.vue';

// Email Components
import GeneralEmail from '../components/email/general/GeneralEmail.vue';
import EmailConnect from '../components/email/email-connect/EmailConnect.vue';
import SmtpEmail from '../components/email/email-connect/SmtpEmail.vue';
import EmailTemplate from '../components/email/templates/EmailTemplate.vue';
import EmailNotification from '../components/email/notifications/EmailNotification.vue';

// Integration Components
import Integration from '../components/integration/Integration.vue';

// Old seperate extensions' license components
import License from '../components/license/License.vue';

Vue.use(Router);

export default new Router({
    linkActiveClass: 'router-link-active',
    routes: settings.hooks.applyFilters('erp_settings_admin_routes', [
        {
            path     : '/',
            component: GeneralSettings,
            children : [
                {
                    path     : 'general',
                    name     : 'GeneralSettings',
                    component: GeneralSettings,
                    alias    : '/'
                }
            ]
        },

        {
            path     : '/erp-hr',
            name     : 'HR',
            component: {
                render(c) {
                    return c('router-view');
                }
            },
            children: [
                {
                    path     : 'workdays',
                    name     : 'HRWorkDays',
                    component: HRWorkDays,
                    alias    : '/erp-hr'
                },
                {
                    path     : 'leave',
                    name     : 'HRLeave',
                    component: HRLeave
                },
                {
                    path     : 'financial',
                    name     : 'HRLeaveYears',
                    component: HRLeaveYears
                },
                {
                    path     : 'miscellaneous',
                    name     : 'HRMiscellaneous',
                    component: HRMiscellaneous
                }
            ]
        },

        {
            path     : '/erp-ac',
            name     : 'Ac',
            component: {
                render(c) {
                    return c('router-view');
                }
            },
            children: [
                {
                    path     : 'customers',
                    name     : 'AcCustomer',
                    component: AcCustomer,
                    alias    : '/erp-ac'
                },
                {
                    path     : 'currency_option',
                    name     : 'AcCurrency',
                    component: AcCurrency
                },
                {
                    path     : 'opening_balance',
                    name     : 'AcFinancialYears',
                    component: AcFinancialYears
                }
            ]
        },

        {
            path     : '/erp-crm',
            name     : 'Crm',
            component: {
                render(c) {
                    return c('router-view');
                }
            },
            children: [
                {
                    path     : 'contacts',
                    name     : 'CrmContacts',
                    component: CrmContacts,
                    alias    : '/erp-crm'
                },
                {
                    path     : 'contact_forms',
                    name     : 'CrmContactFormLayout',
                    component: {
                        render(c) {
                            return c('router-view');
                        }
                    },
                    children: [
                        {
                            path     : '',
                            component: CrmContactForm,
                        },
                        {
                            path     : ':id', // All forms will be added automatically if added on backend.
                            name     : 'CrmContactForm',
                            component: CrmContactForm,
                        },
                    ]
                },
                {
                    path     : 'subscription',
                    name     : 'CrmSubscription',
                    component: CrmSubscription
                }
            ]
        },

        {
            path      : '/erp-woocommerce',
            name      : 'WooCommerce',
            component : WooCommerce
        },

        {
            path     : '/erp-email',
            name     : 'Email',
            component: {
                render(c) {
                    return c('router-view');
                }
            },
            children: [
                {
                    path     : 'general',
                    name     : 'GeneralEmail',
                    component: GeneralEmail,
                    alias    : '/erp-email'
                },
                {
                    path     : 'email_connect',
                    name     : 'EmailConnect',
                    component: EmailConnect
                },
                {
                    path     : 'smtp',
                    name     : 'SmtpEmail',
                    component: SmtpEmail
                },
                {
                    path     : 'templates',
                    name     : 'EmailTemplate',
                    component: EmailTemplate
                },
                {
                    path     : 'notification',
                    name     : 'EmailNotification',
                    component: EmailNotification,
                },
            ]
        },

        {
            name     : 'erp-integration-root',
            path     : '/erp-integration',
            component: {
                render(c) {
                    return c('router-view');
                }
            },
            children: [
                {
                    path     : '',
                    name     : 'Integration',
                    component: Integration,
                }
            ]
        },

        {
            path     : '/erp-license',
            name     : 'License',
            component: License,
        },
    ])
});
