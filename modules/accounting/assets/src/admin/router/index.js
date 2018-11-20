import Vue from 'vue'
import Router from 'vue-router'
import Dashboard from 'admin/components/Dashboard.vue'
import ChartOfAccounts from 'admin/components/ChartOfAccounts.vue'
import Customers from 'admin/components/Peoples/Customers.vue'
import Vendors from 'admin/components/Peoples/Vendors.vue'
import Employees from 'admin/components/Peoples/Employees.vue'

Vue.use(Router)

export default new Router({
    routes: [
        {
            path: '/erp-accounting',
            name: 'Dashboard',
            component: Dashboard
        },
        {
            path: '/erp-accounting-charts',
            name: 'ChartOfAccounts',
            component: ChartOfAccounts
        },
        {
            path: '/erp-accounting-customers',
            name: 'Customers',
            component: Customers
        },
        {
            path: '/erp-accounting-vendors',
            name: 'Vendors',
            component: Vendors
        },
        {
            path: '/erp-accounting-employees',
            name: 'Employees',
            component: Employees
        },
    ]
})
