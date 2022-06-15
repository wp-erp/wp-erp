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
        <transactions-filter :types="filterTypes" :people="{title: __('Customer', 'erp'), items: customers}"/>
        <sales-list />
    </div>
</template>

<script>
import ComboBox from 'admin/components/select/ComboBox.vue';
import SalesStats from 'admin/components/transactions/sales/SalesStats.vue';
import SalesList from 'admin/components/transactions/sales/SalesList.vue';
import TransactionsFilter from 'admin/components/transactions/TransactionsFilter.vue';
import {mapState} from "vuex";
import HTTP from 'admin/http';

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
                { namedRoute: 'EstimateCreate', name: __('Create Estimate', 'erp') }
            ],

            filterTypes:[
                { id: 'invoice', name: __('Invoice', 'erp') },
                { id: 'payment', name: __('Receive', 'erp') },
                { id: 'return_payment', name: __('Payment', 'erp') },
                { id: 'estimate', name: __('Estimate', 'erp') }
            ],

            pro_activated: false,
        };
    },

    created() {
        setTimeout(()=>{
            this.pro_activated =  this.$store.state.erp_pro_activated ?  this.$store.state.erp_pro_activated : false
            if(this.pro_activated ){
                this.pages.push({ namedRoute: 'SalesReturnList', name: __('Sales Return', 'erp') })
            }
        }, 200);

        if(!this.customers.length){
            HTTP.get('/people', {
                params: {
                    type    : 'customer',
                    per_page: -1,
                    page    : 1,
                }
            }).then(response => {
                this.$store.dispatch('sales/fillCustomers', response.data);
            });
        }
    },

    computed: mapState({
        customers: state => state.sales.customers
    }),

};
</script>

<style>
</style>
