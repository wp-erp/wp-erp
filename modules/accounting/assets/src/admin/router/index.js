import Vue from 'vue'
import Router from 'vue-router'
import DummyComponent from 'admin/components/DummyComponent.vue'

import Dashboard from 'admin/components/Dashboard.vue'
import ChartOfAccounts from 'admin/components/ChartOfAccounts.vue'

import Customers from 'admin/components/people/Customers.vue'
import Vendors from 'admin/components/people/Vendors.vue'
import Employees from 'admin/components/people/Employees.vue'
import CustomerDetails from 'admin/components/people/CustomerDetails.vue'

import Products from 'admin/components/products/Products.vue'
import ProductCategory from 'admin/components/product-category/ProductCategory.vue'

import InvoiceCreate from 'admin/components/invoice/InvoiceCreate.vue'

import RecPaymentCreate from 'admin/components/rec-payment/RecPaymentCreate.vue'




Vue.use(Router)

export default new Router({
    routes: [
        {
            path: '/',
            component: Dashboard,
            children: [
                {
                    path : '/dashboard',
                    name : 'Dashboard',
                    component: Dashboard,
                }
            ]
        },
        {
            path: '/customers',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path : '',
                    name : 'Customers',
                    component: Customers,
                },
                {
                    path : 'page/:page',
                    name : 'PaginateCustomers',
                    component: Customers,
                },
                {
                    path : 'view/:id',
                    name : 'CustomerDetails',
                    component: CustomerDetails,
                }
            ]
        },
        {
            path: '/vendors',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'Vendors',
                    component: Vendors,
                },
                {
                    path: 'page/:page',
                    name: 'PaginateVendors',
                    component: Vendors,
                },
            ]
        },
        {
            path: '/employees',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'Employees',
                    component: Employees,
                },
                {
                    path: 'page/:page',
                    name: 'PaginateEmployees',
                    component: Employees,
                },
            ]
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
            name: 'ChartOfAccounts',
            component: ChartOfAccounts
        },
        {
            path: '/bank',
            name: 'BankAccounts',
            component: DummyComponent
        },
        {
            path: '/journal',
            name: 'JournalEntries',
            component: DummyComponent
        },
        {
            path: '/invoices/new',
            name: 'InvoiceCreate',
            component: InvoiceCreate
        },
        {

            path: '/erp_inv_product',
            name: 'Products',
            component: Products
        },
        {
            path: '/erp_inv_product_category',
            name: 'ProductCategory',
            component: ProductCategory
        },

        {
            path: '/payments/new',
            name: 'RecPaymentCreate',
            component: RecPaymentCreate
        },
    ]
})
