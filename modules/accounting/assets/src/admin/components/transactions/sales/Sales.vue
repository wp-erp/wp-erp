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
        <transactions-filter />
        <sales-list />

    </div>
</template>

<script>
    import ComboBox from 'admin/components/select/ComboBox.vue'
    import SalesStats from 'admin/components/transactions/sales/SalesStats.vue'
    import SalesList from 'admin/components/transactions/sales/SalesList.vue'
    import TransactionsFilter from 'admin/components/transactions/TransactionsFilter.vue'

    export default {
        name: 'Sales',

        components: {
            SalesStats,
            SalesList,
            TransactionsFilter,
            ComboBox,
        },

        data() {
            return {
                pages: [
                    {namedRoute: 'InvoiceCreate', name: 'Create Invoice'},
                    {namedRoute: 'EstimateCreate', name: 'Create Estimate'},
                    {namedRoute: 'RecPaymentCreate', name: 'Receive Payment'},
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
