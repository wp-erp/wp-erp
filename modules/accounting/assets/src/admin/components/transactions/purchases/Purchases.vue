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
        <transactions-filter  :types="filterTypes" :people="{title: 'Vendor', items: vendors}"/>
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
import {mapState} from "vuex";

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
                { namedRoute: 'PurchaseCreate', name: __('Create Purchase', 'erp') },
                { namedRoute: 'PayPurchaseCreate', name: __('Pay Purchase', 'erp') },
                { namedRoute: 'PurchaseOrderCreate', name:  __('Create Purchase Order', 'erp') },
                { namedRoute: 'purchasesReturnList', name:  __('Purchase Return', 'erp') },
            ],

            filterTypes:[{id: 'purchase', name: 'Purchase'}, {id: 'pay_purchase', name: 'Purchase Payment'}],
        };
    },

    computed: mapState({
        vendors: state => state.purchase.vendors
    }),

    created() {
        if(!this.vendors.length){
            this.$store.dispatch('purchase/fetchVendors');
        }
    }

    };
</script>
