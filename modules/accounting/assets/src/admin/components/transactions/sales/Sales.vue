<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Sales Transactions', 'erp') }}</h2>
                    <combo-box
                        :options="pages"
                        :hasUrl="true"
                        :placeholder="__('New Transaction', 'erp')" />
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <sales-stats />
        <transactions-filter :types="filterTypes" :people="{title: 'Customer', items: customers}"/>
        <sales-list />

    </div>
</template>

<script>
import ComboBox from 'admin/components/select/ComboBox.vue';
import SalesStats from 'admin/components/transactions/sales/SalesStats.vue';
import SalesList from 'admin/components/transactions/sales/SalesList.vue';
import TransactionsFilter from 'admin/components/transactions/TransactionsFilter.vue';
import {mapState} from "vuex";

export default {
    name: 'Sales',

    components: {
        SalesStats,
        SalesList,
        TransactionsFilter,
        ComboBox
    },

    data() {
        return {
            pages: [
                { namedRoute: 'InvoiceCreate', name: __('Create Invoice', 'erp') },
                { namedRoute: 'RecPaymentCreate', name: __('Receive Payment', 'erp') },
                { namedRoute: 'EstimateCreate', name: __('Create Estimate', 'erp') },
                { namedRoute: 'SalesReturnLists', name: __('Sales Return', 'erp') },
            ],
            filterTypes:[{id: 'invoice', name: 'Invoice'}, {id: 'payment', name: 'Payment'}, {id: 'estimate', name: 'Estimate'}],

        };
    },
    created() {
        if(!this.customers.length){
            this.$store.dispatch('sales/fillCustomers', []);
        }
    },
    computed: mapState({
        customers: state => state.sales.customers
    }),

};
</script>

<style>
</style>
