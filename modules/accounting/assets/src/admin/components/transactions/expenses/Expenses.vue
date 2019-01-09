<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Expenses Transactions</h2>
                    <combo-box :options="pages" :hasUrl="true" placeholder="New Transaction" />
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <expenses-stats />

        <!-- Start .header-section -->
        <div class="content-header-section separator wperp-has-border-top">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Transactions</h2>
                </div>
                <div class="wperp-col">
                    <form class="wperp-form form--inline">
                        <div class="wperp-has-dropdown">
                            <a class="wperp-btn btn--default dropdown-trigger filter-button">
                                <span><i class="flaticon-search-segment"></i>Filters</span>
                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right wperp-filter-container">
                                <div class="wperp-panel wperp-panel-default wperp-filter-panel">
                                    <h3>Filter</h3>
                                    <div class="wperp-panel-body">
                                        <h3>Date</h3>
                                        <div class="form-fields">
                                            <div class="start-date has-addons">
                                                <input class="form-field" type="date" placeholder="16-11-18">
                                                <span class="flaticon-calendar"></span>
                                            </div>
                                            <span class="label-to">To</span>
                                            <div class="end-date has-addons">
                                                <input class="form-field" type="date" placeholder="16-11-18">
                                                <span class="flaticon-calendar"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wperp-panel-footer">
                                        <input type="reset" value="Cancel" class="wperp-btn btn--default">
                                        <input type="submit" value="Submit" class="wperp-btn btn--primary">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wperp-import-wrapper display-inline-block">
                            <a class="wperp-btn btn--default" href="#" title="Import"><span class="flaticon-import"></span></a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <!-- End .header-section -->

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
// import PurchaseCreate from 'admin/components/purchase/PurchaseCreate.vue';
// import PayPurchaseCreate from 'admin/components/pay-purchase/PayPurchaseCreate.vue';

export default {
    name: 'Expenses',

    components: {
        ExpensesStats,
        ComboBox,
        ExpensesList,
    },

    data() {
        return {
            pages: [
                { namedRoute: 'ExpenseCreate', name: 'Expense' },
                { namedRoute: 'PurchaseCreate', name: 'Purchase' },
                { namedRoute: 'PayPurchaseCreate', name: 'Pay Purchase' },
                { namedRoute: 'BillCreate', name: 'Bill' },
                { namedRoute: 'PayBillCreate', name: 'Pay Bill' },
            ],
        };
    },

    created() {
        // this.$root.$on('comboSelected', (selected) => {
        //     console.log(selected);
        // });
    }
};
</script>

<style scoped>
</style>
