<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Expenses Transactions', 'erp') }}</h2>
                    <combo-box :options="pages" :hasUrl="true" :placeholder="__('New Transaction', 'erp')" />
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <expenses-stats />

        <transactions-filter :types="filterTypes"  :people="{title: __('Pay to', 'erp'), items: people}" />

        <expenses-list />

        <!-- End .wperp-crm-table -->
    </div>
</template>

<script>
import 'assets/js/plugins/chart.min';
import 'assets/js/status_chart';

import ComboBox from 'admin/components/select/ComboBox.vue';
import ExpensesStats from 'admin/components/transactions/expenses/ExpensesStats.vue';
import ExpensesList from 'admin/components/transactions/expenses/ExpensesList.vue';
import TransactionsFilter from 'admin/components/transactions/TransactionsFilter.vue';
import {mapState} from "vuex";

export default {
    name: 'Expenses',

    components: {
        ComboBox,
        ExpensesStats,
        ExpensesList,
        TransactionsFilter
    },

    data() {
        return {
            pages: [
                { namedRoute: 'ExpenseCreate', name: __('Create Expense', 'erp') },
                { namedRoute: 'CheckCreate', name: __('Create Check', 'erp') },
                { namedRoute: 'BillCreate', name: __('Create Bill', 'erp') },
                { namedRoute: 'PayBillCreate', name: __('Pay Bill', 'erp') }
            ],

            filterTypes:[
                {id: 'expense', name: __('Expense', 'erp')},
                {id: 'bill', name: __('Bill', 'erp')},
                {id: 'pay_bill', name: __('Bill Payment', 'erp')},
                {id: 'check', name: __('Check', 'erp')}
            ]
        };
    },
    created() {
        if(!this.people.length){
            this.$store.dispatch('expense/fetchPeople');
        }
    },
    computed: mapState({
        people: state => state.expense.people
    }),
};
</script>
