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
        <transactions-filter />
        <sales-list />

    </div>
</template>

<script>
import ComboBox from 'admin/components/select/ComboBox.vue';
import SalesStats from 'admin/components/transactions/sales/SalesStats.vue';
import SalesList from 'admin/components/transactions/sales/SalesList.vue';
import TransactionsFilter from 'admin/components/transactions/TransactionsFilter.vue';

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
                { namedRoute: 'InvoiceCreate', name: 'Create Invoice' },
                { namedRoute: 'RecPaymentCreate', name: 'Receive Payment' },
                { namedRoute: 'EstimateCreate', name: 'Create Estimate' }
            ]
        };
    }

};
</script>

<style scoped>
</style>
