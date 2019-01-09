<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Sales Transactions</h2>
                    <combo-box 
                        :options="pages"
                        :hasUrl="true"
                        placeholder="New Transaction" />
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <sales-stats />
        <sales-filter />
        <sales-list />

    </div>
</template>

<script>
import 'assets/js/plugins/chart.min';
import 'assets/js/status_chart';

import ComboBox from 'admin/components/select/ComboBox.vue';
import SalesStats from 'admin/components/transactions/sales/SalesStats.vue';
import SalesFilter from 'admin/components/transactions/sales/SalesFilter.vue';
import SalesList from 'admin/components/transactions/sales/SalesList.vue';

export default {
    name: 'Sales',

    components: {
        SalesStats,
        SalesList,
        SalesFilter,
        ComboBox,
    },

    data() {
        return {
            pages: [
                { namedRoute: 'InvoiceCreate', name: 'Invoice' },
                { namedRoute: 'RecPaymentCreate', name: 'Payment' },
            ],
        };
    },

    created() {
        // this.$root.$on('comboSelected', (selected) => {
        //     console.log(selected);
        // });
    },

    methods: {},
};
</script>

<style scoped>
</style>
