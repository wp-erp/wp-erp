<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Purchases Transactions', 'erp') }}</h2>
                    <combo-box
                        :options="pages"
                        :hasUrl="true"
                        :placeholder="__('New Transaction', 'erp')" />
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <purchases-stats />
        <transactions-filter />
        <purchases-list />

    </div>
</template>

<script>
import 'assets/js/plugins/chart.min';
import 'assets/js/status_chart';

import ComboBox from 'admin/components/select/ComboBox.vue';
import PurchasesStats from 'admin/components/transactions/purchases/PurchasesStats.vue';
import PurchasesList from 'admin/components/transactions/purchases/PurchasesList.vue';
import TransactionsFilter from 'admin/components/transactions/TransactionsFilter.vue';

export default {
    name: 'Purchases',

    components: {
        PurchasesStats,
        PurchasesList,
        TransactionsFilter,
        ComboBox
    },

    data() {
        return {
            pages: [
                { namedRoute: 'PurchaseCreate', name: 'Create Purchase' },
                { namedRoute: 'PayPurchaseCreate', name: 'Pay Purchase' },
                { namedRoute: 'PurchaseOrderCreate', name: 'Create Purchase Order' }
            ]
        };
    }
};
</script>
