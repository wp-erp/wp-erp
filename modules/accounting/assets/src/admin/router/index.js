import Vue              from 'vue';
import Sales            from 'admin/components/sales/Sales.vue';
import Router           from 'vue-router';
import People           from 'admin/components/people/People.vue';
import Expenses         from 'admin/components/expenses/Expenses.vue';
import Products         from 'admin/components/products/Products.vue';
import Employees        from 'admin/components/people/Employees.vue';
import Dashboard        from 'admin/components/Dashboard.vue';
import PeopleDetails    from 'admin/components/people/PeopleDetails.vue';
import InvoiceCreate    from 'admin/components/invoice/InvoiceCreate.vue';
import DummyComponent   from 'admin/components/DummyComponent.vue';
import ChartOfAccounts  from 'admin/components/ChartOfAccounts.vue';
import ProductCategory  from 'admin/components/product-category/ProductCategory.vue';
import RecPaymentCreate from 'admin/components/rec-payment/RecPaymentCreate.vue';

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
                    component: People,
                },
                {
                    path : 'page/:page',
                    name : 'PaginateCustomers',
                    component: People,
                },
                {
                    path : 'view/:id',
                    name : 'CustomerDetails',
                    component: PeopleDetails,
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
                    component: People,
                },
                {
                    path : 'view/:id',
                    name : 'VendorDetails',
                    component: PeopleDetails,
                },
                {
                    path: 'page/:page',
                    name: 'PaginateVendors',
                    component: People,
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
            component: Sales
        },
        {
            path: '/expenses',
            name: 'Expenses',
            component: Expenses
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
