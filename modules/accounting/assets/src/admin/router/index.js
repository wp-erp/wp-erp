import Vue from 'vue'
import Router from 'vue-router'
import Dashboard from 'admin/components/Dashboard.vue'
import ChartOfAccounts from 'admin/components/ChartOfAccounts.vue'
import Customers from 'admin/components/Peoples/Customers.vue'
import Vendors from 'admin/components/Peoples/Vendors.vue'
import Employees from 'admin/components/Peoples/Employees.vue'
import DummyComponent from 'admin/components/DummyComponent.vue'

import InvoiceCreate from 'admin/components/invoice/InvoiceCreate.vue'

Vue.use(Router)

export default new Router({
    routes: [
        {
            path: '/',
            name: 'Dashboard',
            component: Dashboard
        },
        {
            path: '/customers',
            name: 'Customers',
            component: Customers
        },
        {
            path: '/vendors',
            name: 'Vendors',
            component: Vendors
        },
        {
            path: '/employees',
            name: 'Employees',
            component: Employees
        },
        {
            path: '/sales',
            name: 'Sales',
            component: DummyComponent
        },
        {
            path: '/expense',
            name: 'Expenses',
            component: DummyComponent
        },
        {
            path: '/charts',
            name: 'Chart Of Accounts',
            component: ChartOfAccounts
        },
        {
            path: '/bank',
            name: 'Bank Accounts',
            component: DummyComponent
        },
        {
            path: '/journal',
            name: 'Journal Entries',
            component: DummyComponent
        },
        {
            path: '/invoices/new',
            name: 'InvoiceCreate',
            component: InvoiceCreate
        },
    ]
})
